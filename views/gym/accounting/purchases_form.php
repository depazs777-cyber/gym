<h2>New Purchase (Factura / Doc. Soporte)</h2>

<form action="/gym/accounting/purchases/store" method="POST" id="purchaseForm">
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
    <input type="hidden" name="tax_snapshot" id="taxSnapshot">

    <div class="row">
        <!-- HEADER -->
        <div class="col-md-8">
            <div class="card mb-3">
                <div class="card-header">Document Details</div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <label>Third Party</label>
                            <select name="third_party_id" id="thirdPartyId" class="form-control" required onchange="calculateTaxes()">
                                <option value="">-- Select Provider --</option>
                                <?php foreach ($providers as $p): ?>
                                    <option value="<?= $p['id'] ?>"
                                        data-person-type="<?= $p['person_type'] ?>"
                                        data-economic="<?= $p['has_economic_activity'] ?>"
                                        data-vat="<?= $p['vat_responsible'] ?>"
                                    >
                                        <?= htmlspecialchars($p['full_name_or_company']) ?> (<?= $p['document_number'] ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="form-text text-muted">
                                <a href="/gym/accounting/third-parties/create" target="_blank">+ New Third Party</a>
                            </small>
                        </div>
                        <div class="col-md-3">
                            <label>Type</label>
                            <select name="doc_type" class="form-control" required>
                                <option value="FCI">Factura de Compra (FCI)</option>
                                <option value="DS">Documento Soporte (DS)</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label>Doc Number</label>
                            <input type="text" name="doc_number" class="form-control" required>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-md-4">
                            <label>Issue Date</label>
                            <input type="date" name="issue_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- AMOUNTS -->
        <div class="col-md-4">
            <div class="card mb-3">
                <div class="card-header">Amounts (Base)</div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Subtotal (Base)</label>
                        <input type="number" step="0.01" name="subtotal" id="subtotal" class="form-control" value="0" oninput="calculateTaxes()">
                    </div>
                    <div class="form-group">
                        <label>IVA Rate (%)</label>
                        <input type="number" step="0.01" name="iva_rate" id="ivaRate" class="form-control" value="0" oninput="calculateTaxes()">
                    </div>
                    <div class="form-group">
                        <label>IVA Value</label>
                        <input type="number" step="0.01" name="iva_value" id="ivaValue" class="form-control" readonly>
                    </div>
                    <div class="form-group">
                        <label>Other Taxes (+)</label>
                        <input type="number" step="0.01" name="other_taxes" id="otherTaxes" class="form-control" value="0" oninput="calculateTaxes()">
                    </div>
                    <hr>
                    <div class="form-group">
                        <label><strong>Total Gross</strong></label>
                        <input type="number" step="0.01" name="total_gross" id="totalGross" class="form-control font-weight-bold" readonly>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- WITHHOLDINGS CALCULATION UI -->
    <div class="card mb-3 border-danger">
        <div class="card-header text-danger">Withholdings (Retenciones)</div>
        <div class="card-body">
            <div id="retentionFeedback" class="alert alert-light" style="font-size: 0.9rem;">
                Select a provider and enter amounts to calculate taxes.
            </div>

            <div class="row">
                <div class="col-md-4">
                    <label>ReteIVA (-)</label>
                    <input type="number" step="0.01" name="reteiva_value" id="reteivaValue" class="form-control" readonly>
                    <small id="reteivaInfo" class="text-muted"></small>
                </div>
                <div class="col-md-4">
                    <label>ReteICA (-)</label>
                    <input type="number" step="0.01" name="reteica_value" id="reteicaValue" class="form-control" readonly>
                    <small id="reteicaInfo" class="text-muted"></small>
                </div>
                <div class="col-md-4">
                    <label>Other Retentions (-)</label>
                    <input type="number" step="0.01" name="other_retentions" id="otherRetentions" class="form-control" value="0" oninput="calculateTotalPayable()">
                </div>
            </div>
        </div>
    </div>

    <!-- TOTAL PAYABLE -->
    <div class="card mb-3 bg-light">
        <div class="card-body text-center">
            <h3>Net Payable: <span id="displayTotalPayable">$0.00</span></h3>
            <input type="hidden" name="total_payable" id="totalPayable">
        </div>
    </div>

    <button type="submit" class="btn btn-lg btn-success btn-block">Register Purchase</button>
</form>

<!-- JS Logic for Taxes -->
<script>
const rules = <?= json_encode($rules) ?>;

function calculateTaxes() {
    const subtotal = parseFloat(document.getElementById('subtotal').value) || 0;
    const ivaRate = parseFloat(document.getElementById('ivaRate').value) || 0;
    const otherTaxes = parseFloat(document.getElementById('otherTaxes').value) || 0;
    const providerSelect = document.getElementById('thirdPartyId');
    const selectedOption = providerSelect.options[providerSelect.selectedIndex];

    // 1. Calculate Gross
    const ivaValue = subtotal * (ivaRate / 100);
    document.getElementById('ivaValue').value = ivaValue.toFixed(2);

    const totalGross = subtotal + ivaValue + otherTaxes;
    document.getElementById('totalGross').value = totalGross.toFixed(2);

    // 2. Determine Profile
    if (!selectedOption.value) {
        document.getElementById('retentionFeedback').innerText = "Select a provider.";
        updateRetentionUI(0, 0, "Wait", "Wait");
        calculateTotalPayable();
        return;
    }

    const profile = {
        personType: selectedOption.getAttribute('data-person-type'), // NATURAL, JURIDICA
        hasActivity: selectedOption.getAttribute('data-economic') == '1',
        vatResponsible: selectedOption.getAttribute('data-vat') // YES, NO
    };

    let reteIvaVal = 0;
    let reteIcaVal = 0;
    let reteIvaDesc = "N/A";
    let reteIcaDesc = "N/A";
    let appliedRules = [];

    // 3. Apply Rules
    rules.forEach(rule => {
        // Filter by logic
        // TODO: Detailed logic implementation matching PHP enum checks
        // Simplified for this view:

        let applicable = true;

        // Check Min Base
        let baseVal = 0;
        if (rule.base_field === 'IVA') baseVal = ivaValue;
        else if (rule.base_field === 'SUBTOTAL') baseVal = subtotal;
        else baseVal = totalGross;

        if (baseVal < rule.min_base_amount) applicable = false;

        // Check Third Party Type Filter
        if (applicable && rule.third_party_type !== 'ANY') {
             if (rule.third_party_type === 'NO_ECONOMIC' && profile.hasActivity) applicable = false;
             if (rule.third_party_type === 'JURIDICA' && profile.personType !== 'JURIDICA') applicable = false;
             if (rule.third_party_type === 'NATURAL' && profile.personType !== 'NATURAL') applicable = false;
        }

        // Apply
        if (applicable) {
            let val = 0;
            if (rule.rate_unit === 'PERCENT') {
                val = baseVal * (rule.rate / 100);
            } else { // PER_MILLE
                val = baseVal * (rule.rate / 1000);
            }

            if (rule.tax_type === 'RETEIVA') {
                // Usually ReteIVA applies if Provider is Simplificado (VAT NO) and We are Common?
                // Or if Provider is Authorretenedor?
                // Logic is complex in Colombia.
                // Assuming Config Rule dictates "If Rule Matches, Apply It".
                // If multiple rules match same tax_type, we sum them? Usually distinct.
                // Let's take the first match or specific logic.
                // Simple: Additive.
                reteIvaVal += val;
                reteIvaDesc = `${rule.rate}% of ${rule.base_field}`;
                appliedRules.push(rule);
            }
            if (rule.tax_type === 'RETEICA') {
                reteIcaVal += val;
                reteIcaDesc = `${rule.rate}‰ of ${rule.base_field}`;
                appliedRules.push(rule);
            }
        }
    });

    updateRetentionUI(reteIvaVal, reteIcaVal, reteIvaDesc, reteIcaDesc);
    document.getElementById('taxSnapshot').value = JSON.stringify(appliedRules);
    calculateTotalPayable();
}

function updateRetentionUI(iva, ica, ivaDesc, icaDesc) {
    document.getElementById('reteivaValue').value = iva.toFixed(2);
    document.getElementById('reteivaInfo').innerText = ivaDesc;

    document.getElementById('reteicaValue').value = ica.toFixed(2);
    document.getElementById('reteicaInfo').innerText = icaDesc;
}

function calculateTotalPayable() {
    const totalGross = parseFloat(document.getElementById('totalGross').value) || 0;
    const rIva = parseFloat(document.getElementById('reteivaValue').value) || 0;
    const rIca = parseFloat(document.getElementById('reteicaValue').value) || 0;
    const otherRet = parseFloat(document.getElementById('otherRetentions').value) || 0;

    const payable = totalGross - rIva - rIca - otherRet;

    document.getElementById('totalPayable').value = payable.toFixed(2);
    document.getElementById('displayTotalPayable').innerText = '$' + payable.toFixed(2);
}
</script>
