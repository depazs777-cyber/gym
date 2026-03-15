<?php
define('APP_NAME', 'PROMPT_MAESTRO');
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

$db = Database::getInstance()->getConnection();

echo "Checking if we can delete the row with id=0 in memberships...\n";
// Sometimes SQLite stores a row with rowid=0 or user inserted 0.
$stmt = $db->query("SELECT count(*) FROM memberships WHERE id = 0");
if ($stmt->fetchColumn() > 0) {
    echo "Found row with id=0 in memberships. Deleting it...\n";
    $db->exec("DELETE FROM memberships WHERE id = 0");
    echo "Deleted.\n";
} else {
    echo "No row with id=0 in memberships.\n";
}

echo "Checking payments...\n";
$stmt = $db->query("SELECT count(*) FROM payments WHERE id = 0");
if ($stmt->fetchColumn() > 0) {
    echo "Found row with id=0 in payments. Deleting it...\n";
    $db->exec("DELETE FROM payments WHERE id = 0");
    echo "Deleted.\n";
} else {
    echo "No row with id=0 in payments.\n";
}

echo "Done cleaning 0 IDs.\n";
