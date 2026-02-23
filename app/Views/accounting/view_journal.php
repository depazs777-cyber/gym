<?php
$title = 'Detalle de Asiento #' . $entry['id'];
ob_start();
?>

<div class="mb-4">
    <a href="<?= BASE_URL ?>/accounting">← Volver al Libro Diario</a>
</div>

<div class="card">
    <div class="flex justify-between mb-4">
        <div>
            <h2>Asiento #<?= $entry['id'] ?></h2>
            <p class="text-muted"><?= $entry['entry_date'] ?> - <?= $entry['description'] ?></p>
        </div>
        <div>
            <span class="badge badge-<?= $entry['status'] ?>"><?= strtoupper($entry['status']) ?></span>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Código</th>
                <th>Cuenta</th>
                <th>Descripción</th>
                <th class="text-right">Débito</th>
                <th class="text-right">Crédito</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $totalDebit = 0;
            $totalCredit = 0;
            foreach ($lines as $line):
                $totalDebit += $line['debit'];
                $totalCredit += $line['credit'];
            ?>
            <tr>
                <td><?= $line['code'] ?></td>
                <td><?= $line['account_name'] ?></td>
                <td><?= $line['description'] ?></td>
                <td class="text-right"><?= number_format($line['debit'], 2) ?></td>
                <td class="text-right"><?= number_format($line['credit'], 2) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr style="font-weight: bold; background: rgba(255,255,255,0.05);">
                <td colspan="3" class="text-right">TOTALES</td>
                <td class="text-right"><?= number_format($totalDebit, 2) ?></td>
                <td class="text-right"><?= number_format($totalCredit, 2) ?></td>
            </tr>
        </tfoot>
    </table>
</div>

<?php
$content = ob_get_clean();
include VIEW_PATH . '/layouts/main.php';
?>
