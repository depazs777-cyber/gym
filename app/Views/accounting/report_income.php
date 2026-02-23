<?php
$title = 'Estado de Resultados';
ob_start();
?>
<div class="mb-4 flex justify-between">
    <a href="<?= BASE_URL ?>/accounting/reports">← Volver a Reportes</a>
    <button onclick="window.print()">Imprimir</button>
</div>

<div class="card" style="max-width: 800px; margin: 0 auto;">
    <h2 class="text-center mb-4">Estado de Resultados</h2>
    <p class="text-center text-muted">A la fecha: <?= date('d/m/Y') ?></p>

    <table>
        <!-- Ingresos -->
        <thead>
            <tr style="background: rgba(76, 175, 80, 0.1);">
                <th colspan="2">INGRESOS OPERACIONALES</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($revenue as $acc): ?>
            <tr>
                <td><?= $acc['code'] ?> - <?= $acc['name'] ?></td>
                <td class="text-right">$ <?= number_format($acc['balance'], 2) ?></td>
            </tr>
            <?php endforeach; ?>
            <tr style="font-weight: bold;">
                <td>TOTAL INGRESOS</td>
                <td class="text-right">$ <?= number_format($total_revenue, 2) ?></td>
            </tr>
        </tbody>

        <!-- Gastos -->
        <thead>
            <tr style="background: rgba(244, 67, 54, 0.1);">
                <th colspan="2">GASTOS OPERACIONALES</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($expenses as $acc): ?>
            <tr>
                <td><?= $acc['code'] ?> - <?= $acc['name'] ?></td>
                <td class="text-right">$ <?= number_format($acc['balance'], 2) ?></td>
            </tr>
            <?php endforeach; ?>
            <tr style="font-weight: bold;">
                <td>TOTAL GASTOS</td>
                <td class="text-right">$ <?= number_format($total_expenses, 2) ?></td>
            </tr>
        </tbody>

        <!-- Costos -->
        <?php if ($total_costs > 0): ?>
        <thead>
            <tr style="background: rgba(255, 152, 0, 0.1);">
                <th colspan="2">COSTOS DE VENTAS</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($costs as $acc): ?>
            <tr>
                <td><?= $acc['code'] ?> - <?= $acc['name'] ?></td>
                <td class="text-right">$ <?= number_format($acc['balance'], 2) ?></td>
            </tr>
            <?php endforeach; ?>
            <tr style="font-weight: bold;">
                <td>TOTAL COSTOS</td>
                <td class="text-right">$ <?= number_format($total_costs, 2) ?></td>
            </tr>
        </tbody>
        <?php endif; ?>

        <!-- Resultado -->
        <tfoot>
            <tr style="font-size: 1.2em; border-top: 2px solid white;">
                <td><strong>UTILIDAD / PÉRDIDA NETA</strong></td>
                <td class="text-right" style="color: <?= $net_income >= 0 ? '#4CAF50' : '#F44336' ?>">
                    <strong>$ <?= number_format($net_income, 2) ?></strong>
                </td>
            </tr>
        </tfoot>
    </table>
</div>
<?php
$content = ob_get_clean();
include VIEW_PATH . '/layouts/main.php';
?>
