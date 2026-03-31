<?php
$title = 'Contabilidad - Libro Diario';
ob_start();
?>

<div class="flex justify-between mb-4">
    <h2>Movimientos Recientes</h2>
    <button onclick="document.getElementById('modal-receipt').style.display='block'">+ Nuevo Recibo de Caja</button>
</div>

<div class="card">
    <table>
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Descripción</th>
                <th>Módulo</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($entries as $entry): ?>
            <tr>
                <td><?= $entry['entry_date'] ?></td>
                <td><?= $entry['description'] ?></td>
                <td><?= strtoupper($entry['source_module'] ?? 'MANUAL') ?></td>
                <td><span class="badge badge-<?= $entry['status'] ?>"><?= strtoupper($entry['status']) ?></span></td>
                <td>
                    <a href="<?= BASE_URL ?>/accounting/view?id=<?= $entry['id'] ?>">Ver</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Modal Simple para crear Recibo -->
<div id="modal-receipt" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.8); z-index:1000;">
    <div class="card" style="width:400px; margin: 10% auto; position:relative;">
        <span onclick="document.getElementById('modal-receipt').style.display='none'" style="position:absolute; right:15px; top:10px; cursor:pointer;">✖</span>
        <h3>Nuevo Recibo de Caja</h3>
        <form action="<?= BASE_URL ?>/accounting/receipts/store" method="POST">
            <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= $_SESSION[CSRF_TOKEN_NAME] ?? '' ?>">

            <label>Concepto</label>
            <input type="text" name="concept" required>

            <label>Valor</label>
            <input type="number" name="amount" required>

            <button type="submit">Guardar</button>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
include VIEW_PATH . '/layouts/main.php';
?>
