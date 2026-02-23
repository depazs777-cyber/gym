<?php
$title = 'Dashboard General';
ob_start();
?>

<div class="grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
    <div class="card">
        <h3>👥 Miembros Activos</h3>
        <p style="font-size: 2em; color: var(--primary);"><?= $stats['active_members'] ?></p>
    </div>
    <div class="card">
        <h3>💰 Ingresos Mes</h3>
        <p style="font-size: 2em; color: #FFC107;">$ <?= number_format($stats['monthly_revenue'], 0, ',', '.') ?></p>
    </div>
    <div class="card">
        <h3>🔑 Accesos Hoy</h3>
        <p style="font-size: 2em; color: var(--accent);"><?= $stats['daily_access'] ?></p>
    </div>
</div>

<div class="card mt-4">
    <h3>Acciones Rápidas</h3>
    <div class="flex">
        <button onclick="location.href='<?= BASE_URL ?>/members/create'">Nuevo Afiliado</button>
        <button onclick="location.href='<?= BASE_URL ?>/accounting/receipts/create'">Registrar Pago</button>
        <button onclick="location.href='<?= BASE_URL ?>/access'">Escanear QR</button>
    </div>
</div>

<?php
$content = ob_get_clean();
include VIEW_PATH . '/layouts/main.php';
?>
