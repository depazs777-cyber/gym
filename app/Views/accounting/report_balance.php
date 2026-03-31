<?php
$title = 'Balance General';
ob_start();
?>
<div class="mb-4 flex justify-between">
    <a href="<?= BASE_URL ?>/accounting/reports">← Volver a Reportes</a>
    <button onclick="window.print()">Imprimir</button>
</div>

<div class="card">
    <h2 class="text-center mb-4">Balance General</h2>
    <p class="text-center text-muted">A la fecha: <?= date('d/m/Y') ?></p>

    <div style="display: flex; gap: 20px;">
        <!-- Activos -->
        <div style="flex: 1;">
            <h3 style="border-bottom: 2px solid var(--primary);">ACTIVOS</h3>
            <table>
                <?php foreach ($assets as $acc): ?>
                <tr>
                    <td><?= $acc['code'] ?> - <?= $acc['name'] ?></td>
                    <td class="text-right">$ <?= number_format($acc['balance'], 2) ?></td>
                </tr>
                <?php endforeach; ?>
                <tfoot>
                    <tr style="font-weight: bold;">
                        <td>TOTAL ACTIVOS</td>
                        <td class="text-right">$ <?= number_format($total_assets, 2) ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Pasivo + Patrimonio -->
        <div style="flex: 1;">
            <h3 style="border-bottom: 2px solid #FFC107;">PASIVOS</h3>
            <table>
                <?php foreach ($liabilities as $acc): ?>
                <tr>
                    <td><?= $acc['code'] ?> - <?= $acc['name'] ?></td>
                    <td class="text-right">$ <?= number_format($acc['balance'], 2) ?></td>
                </tr>
                <?php endforeach; ?>
                <tfoot>
                    <tr style="font-weight: bold;">
                        <td>TOTAL PASIVOS</td>
                        <td class="text-right">$ <?= number_format($total_liabilities, 2) ?></td>
                    </tr>
                </tfoot>
            </table>

            <h3 style="border-bottom: 2px solid var(--accent); margin-top: 20px;">PATRIMONIO</h3>
            <table>
                <?php foreach ($equity as $acc): ?>
                <tr>
                    <td><?= $acc['code'] ?> - <?= $acc['name'] ?></td>
                    <td class="text-right">$ <?= number_format($acc['balance'], 2) ?></td>
                </tr>
                <?php endforeach; ?>
                <tfoot>
                    <tr style="font-weight: bold;">
                        <td>TOTAL PATRIMONIO</td>
                        <td class="text-right">$ <?= number_format($total_equity, 2) ?></td>
                    </tr>
                </tfoot>
            </table>

            <div class="mt-4 p-2" style="background: rgba(255,255,255,0.1); border-radius: 4px;">
                <div class="flex justify-between" style="font-weight: bold;">
                    <span>TOTAL PASIVO + PATRIMONIO</span>
                    <span>$ <?= number_format($total_liabilities + $total_equity, 2) ?></span>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
include VIEW_PATH . '/layouts/main.php';
?>
