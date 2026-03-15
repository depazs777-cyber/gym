<div class="page-header">
    <h2>Record Purchase (FCI / DS)</h2>
    <a href="<?= url('/admin/accounting/purchases') ?>" class="btn btn-secondary btn-sm">Cancel</a>
</div>

<div class="card">
    <div class="card-body">
        <form action="<?= url('/admin/accounting/purchases/store') ?>" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= $_SESSION[CSRF_TOKEN_NAME] ?>">

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label>Document Type</label>
                        <select name="doc_type" class="form-control" required>
                            <option value="FCI">FCI - Factura Compra Interna</option>
                            <option value="DS">DS - Documento Soporte</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label>Provider (Third Party)</label>
                        <select name="third_party_id" class="form-control" required>
                            <option value="">Select Provider...</option>
                            <?php foreach ($providers as $tp): ?>
                                <option value="<?= $tp['id'] ?>"><?= htmlspecialchars($tp['full_name_or_company']) ?> (<?= $tp['document_number'] ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group mb-3">
                        <label>Document Number (Real Invoice #)</label>
                        <input type="text" name="doc_number" class="form-control" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group mb-3">
                        <label>Issue Date</label>
                        <input type="date" name="issue_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group mb-3">
                        <label>Due Date</label>
                        <input type="date" name="due_date" class="form-control">
                    </div>
                </div>
            </div>

            <hr>
            <h4>Amounts (Taxes calculated automatically based on Rules)</h4>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label>Subtotal (Base)</label>
                        <input type="number" step="0.01" name="subtotal" id="subtotal" class="form-control" required onchange="calcTotal()">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label>IVA Amount</label>
                        <input type="number" step="0.01" name="iva_value" id="iva_value" class="form-control" value="0" onchange="calcTotal()">
                    </div>
                </div>
            </div>

            <div class="form-group mb-3">
                <label>Other Taxes</label>
                <input type="number" step="0.01" name="other_taxes" id="other_taxes" class="form-control" value="0" onchange="calcTotal()">
            </div>

            <div class="alert alert-info">
                <strong>Gross Total:</strong> $<span id="gross_display">0.00</span><br>
                <small>Withholdings will be calculated on submit based on Provider settings and System Rules.</small>
            </div>
            <input type="hidden" name="total_gross" id="total_gross">

            <button type="submit" class="btn btn-primary">Save Purchase</button>
        </form>
    </div>
</div>

<script>
function calcTotal() {
    const sub = parseFloat(document.getElementById('subtotal').value) || 0;
    const iva = parseFloat(document.getElementById('iva_value').value) || 0;
    const other = parseFloat(document.getElementById('other_taxes').value) || 0;
    const total = sub + iva + other;

    document.getElementById('gross_display').innerText = total.toFixed(2);
    document.getElementById('total_gross').value = total.toFixed(2);
}
</script>
