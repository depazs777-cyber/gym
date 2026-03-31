<?php
$title = 'Panel Maestro SaaS';
ob_start();
?>

<div class="grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
    <div class="card text-center" style="border-left: 4px solid var(--primary);">
        <h3>🏢 Gimnasios Clientes</h3>
        <p style="font-size: 2em; color: var(--primary);"><?= $stats['total_gyms'] ?></p>
        <small class="text-muted">Instancias Activas</small>
    </div>

    <div class="card text-center" style="border-left: 4px solid #F44336;">
        <h3>⚠️ Pagos Vencidos</h3>
        <p style="font-size: 2em; color: #F44336;"><?= $stats['expired_gyms'] ?></p>
        <small class="text-muted">Suscripciones Expiradas</small>
    </div>

    <div class="card text-center" style="border-left: 4px solid #FFC107;">
        <h3>💰 MRR Proyectado</h3>
        <p style="font-size: 2em; color: #FFC107;">$ <?= number_format($stats['mrr'] ?? 0, 0, ',', '.') ?></p>
        <small class="text-muted">Ingreso Mensual Recurrente</small>
    </div>

    <div class="card text-center" style="border-left: 4px solid var(--accent);">
        <h3>🎯 Leads Abiertos</h3>
        <p style="font-size: 2em; color: var(--accent);"><?= $stats['total_leads'] ?></p>
        <small class="text-muted">CRM de Ventas</small>
    </div>
</div>

<div class="grid mt-4" style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">

    <!-- Últimos Clientes -->
    <div class="card">
        <h3>Últimos Gimnasios Afiliados</h3>
        <table>
            <thead>
                <tr>
                    <th>Gimnasio</th>
                    <th>Plan Base</th>
                    <th>Vencimiento Corte</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($latestGyms as $gym): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($gym['name']) ?></strong></td>
                    <td><?= htmlspecialchars($gym['plan_name'] ?? 'Sin Plan') ?></td>
                    <td>
                        <?php if (strtotime($gym['valid_until']) < time()): ?>
                            <span style="color: #F44336; font-weight: bold;"><?= date('d/m/Y', strtotime($gym['valid_until'])) ?> (Moroso)</span>
                        <?php else: ?>
                            <span style="color: #4CAF50;"><?= date('d/m/Y', strtotime($gym['valid_until'])) ?></span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($latestGyms)): ?>
                <tr>
                    <td colspan="3" class="text-center text-muted">Aún no hay clientes registrados.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <div class="text-center mt-4">
            <button onclick="location.href='<?= BASE_URL ?>/admin/gyms'">Ver Todos los Clientes</button>
        </div>
    </div>

    <!-- Resumen Embudo -->
    <div class="card">
        <h3>Salud del Embudo CRM</h3>
        <ul style="list-style:none; padding:0;">
            <?php foreach ($funnel as $item): ?>
                <li style="margin-bottom: 10px; display: flex; justify-content: space-between; border-bottom: 1px solid var(--border); padding-bottom: 5px;">
                    <span><?= strtoupper(str_replace('_', ' ', $item['status'])) ?></span>
                    <strong style="color: var(--accent);"><?= $item['count'] ?> Leads</strong>
                </li>
            <?php endforeach; ?>
            <?php if (empty($funnel)): ?>
                <li class="text-center text-muted">El embudo está vacío.</li>
            <?php endif; ?>
        </ul>
        <div class="text-center mt-4">
            <button onclick="location.href='<?= BASE_URL ?>/admin/leads'">Ir al CRM</button>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include VIEW_PATH . '/layouts/main.php';
?>
