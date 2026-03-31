<?php
$title = 'Reportes Financieros';
ob_start();
?>

<div class="mb-4">
    <a href="<?= BASE_URL ?>/accounting">← Volver a Contabilidad</a>
</div>

<h2>Seleccione un Reporte</h2>

<div class="grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
    <div class="card text-center">
        <h3>📊 Balance General</h3>
        <p>Estado de Situación Financiera (Activo, Pasivo, Patrimonio)</p>
        <button onclick="location.href='<?= BASE_URL ?>/accounting/reports/balance'">Ver Reporte</button>
    </div>
    <div class="card text-center">
        <h3>📉 Estado de Resultados</h3>
        <p>Pérdidas y Ganancias (Ingresos, Gastos, Costos)</p>
        <button onclick="location.href='<?= BASE_URL ?>/accounting/reports/income'">Ver Reporte</button>
    </div>
</div>

<?php
$content = ob_get_clean();
include VIEW_PATH . '/layouts/main.php';
?>
