<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

echo "Starting SaaS Plans Upgrade Migration (SQLite Compatible)...\n";

$pdo = new Database()->getConnection();
$driver = getenv('DB_DRIVER') ?: 'mysql';

function addColumn($pdo, $table, $column, $definition) {
    try {
        $sql = "ALTER TABLE $table ADD COLUMN $column $definition";
        $pdo->exec($sql);
        echo "Added column $column to $table.\n";
    } catch (Exception $e) {
        $msg = $e->getMessage();
        if (strpos($msg, 'duplicate column') !== false || strpos($msg, 'Duplicate column') !== false) {
             echo "Column $column already exists in $table.\n";
        } else {
             // SQLite specific: Cannot add UNIQUE column directly
             if (strpos($definition, 'UNIQUE') !== false && strpos($msg, 'Cannot add a UNIQUE column') !== false) {
                 echo "SQLite limitation: Cannot add UNIQUE column $column directly. Adding without UNIQUE constraint, then creating index.\n";
                 $defNoUnique = str_replace('UNIQUE', '', $definition);
                 $pdo->exec("ALTER TABLE $table ADD COLUMN $column $defNoUnique");
                 $pdo->exec("CREATE UNIQUE INDEX idx_{$table}_{$column} ON $table($column)");
                 echo "Added column $column and UNIQUE index.\n";
             } else {
                 echo "Error adding $column to $table: " . $msg . "\n";
             }
        }
    }
}

// 1. Upgrade saas_plans
addColumn($pdo, 'saas_plans', 'code', 'VARCHAR(50) UNIQUE DEFAULT NULL');
addColumn($pdo, 'saas_plans', 'is_archived', 'TINYINT(1) DEFAULT 0');
addColumn($pdo, 'saas_plans', 'merged_into_plan_id', 'INT DEFAULT NULL');

// Backfill codes if empty
try {
    $stmt = $pdo->query("SELECT id, name FROM saas_plans WHERE code IS NULL");
    while ($row = $stmt->fetch()) {
        $code = strtoupper(str_replace(' ', '_', $row['name']));
        $upd = $pdo->prepare("UPDATE saas_plans SET code = ? WHERE id = ?");
        try {
            $upd->execute([$code, $row['id']]);
            echo "Updated plan {$row['id']} with code $code\n";
        } catch(Exception $e) {
            $code = $code . '_' . $row['id'];
            $upd->execute([$code, $row['id']]);
            echo "Updated plan {$row['id']} with code $code (fallback)\n";
        }
    }
} catch (Exception $e) {
    echo "Warning during backfill: " . $e->getMessage() . "\n";
}

// 2. Upgrade gyms
addColumn($pdo, 'gyms', 'saas_plan_id', 'INT DEFAULT NULL');
addColumn($pdo, 'gyms', 'subscription_period_months_snapshot', 'INT DEFAULT 1');

// Backfill gyms saas_plan_id
try {
    $stmt = $pdo->query("SELECT id, subscription_plan_code FROM gyms WHERE saas_plan_id IS NULL");
    while ($gym = $stmt->fetch()) {
        $code = $gym['subscription_plan_code'];
        if ($code) {
            $planId = null;
            if ($code == 'ANNUAL') {
                $s = $pdo->prepare("SELECT id FROM saas_plans WHERE name LIKE '%Anual%' LIMIT 1");
                $s->execute();
                $planId = $s->fetchColumn();
            } elseif ($code == 'MONTHLY') {
                $s = $pdo->prepare("SELECT id FROM saas_plans WHERE name LIKE '%Mensual%' LIMIT 1");
                $s->execute();
                $planId = $s->fetchColumn();
            }
            
            if ($planId) {
                $u = $pdo->prepare("UPDATE gyms SET saas_plan_id = ? WHERE id = ?");
                $u->execute([$planId, $gym['id']]);
                echo "Linked Gym {$gym['id']} to Plan $planId\n";
            }
        }
    }
} catch (Exception $e) {
    echo "Warning during gym backfill: " . $e->getMessage() . "\n";
}

// 3. Upgrade saas_payments
addColumn($pdo, 'saas_payments', 'saas_plan_id', 'INT DEFAULT NULL');
addColumn($pdo, 'saas_payments', 'plan_name_snapshot', 'VARCHAR(255) DEFAULT NULL');

echo "SaaS Plans Upgrade Migration Complete.\n";
