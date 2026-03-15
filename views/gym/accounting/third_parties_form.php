<?php
$isEdit = ($mode === 'edit');
$p = $party ?? [];
?>
<h2><?= $isEdit ? 'Edit' : 'Create' ?> Third Party</h2>

<form action="/gym/accounting/third-parties/store" method="POST">
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
    <?php if ($isEdit): ?>
        <input type="hidden" name="id" value="<?= $p['id'] ?>">
    <?php endif; ?>

    <!-- Type Selection -->
    <div class="card mb-3">
        <div class="card-header">Basic Classification</div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <label>Third Party Type</label>
                    <select name="third_type" class="form-control" required>
                        <option value="PROVEEDOR" <?= ($p['third_type']??'') == 'PROVEEDOR' ? 'selected' : '' ?>>Proveedor</option>
                        <option value="CLIENTE" <?= ($p['third_type']??'') == 'CLIENTE' ? 'selected' : '' ?>>Cliente</option>
                        <option value="MIXTO" <?= ($p['third_type']??'') == 'MIXTO' ? 'selected' : '' ?>>Mixto</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label>Person Type</label>
                    <select name="person_type" class="form-control" required>
                        <option value="JURIDICA" <?= ($p['person_type']??'') == 'JURIDICA' ? 'selected' : '' ?>>Jurídica</option>
                        <option value="NATURAL" <?= ($p['person_type']??'') == 'NATURAL' ? 'selected' : '' ?>>Natural</option>
                    </select>
                </div>
                <div class="col-md-4" style="display:flex; align-items:center;">
                    <div class="form-check mt-4">
                        <input class="form-check-input" type="checkbox" name="no_economic_activity" id="noActivity" value="1"
                               <?= ($p['has_economic_activity']??1) == 0 ? 'checked' : '' ?> onchange="toggleActivity()">
                        <label class="form-check-label" for="noActivity">
                            Without Economic Activity (No RUT)
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Identification -->
    <div class="card mb-3">
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <label>Doc Type</label>
                    <select name="document_type" class="form-control">
                        <option value="NIT" <?= ($p['document_type']??'') == 'NIT' ? 'selected' : '' ?>>NIT</option>
                        <option value="CC" <?= ($p['document_type']??'') == 'CC' ? 'selected' : '' ?>>CC</option>
                        <option value="CE" <?= ($p['document_type']??'') == 'CE' ? 'selected' : '' ?>>CE</option>
                        <option value="PASSPORT" <?= ($p['document_type']??'') == 'PASSPORT' ? 'selected' : '' ?>>Passport</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label>Document Number</label>
                    <input type="text" name="document_number" class="form-control" value="<?= $p['document_number']??'' ?>" required>
                </div>
                <div class="col-md-5">
                    <label>Full Name / Company Name</label>
                    <input type="text" name="full_name_or_company" class="form-control" value="<?= $p['full_name_or_company']??'' ?>" required>
                </div>
            </div>
            <div class="row mt-2">
                 <div class="col-md-6">
                    <label>Trade Name (Optional)</label>
                    <input type="text" name="trade_name" class="form-control" value="<?= $p['trade_name']??'' ?>">
                 </div>
            </div>
        </div>
    </div>

    <!-- RUT & Tax Info -->
    <div class="card mb-3" id="taxSection">
        <div class="card-header">Tax Information (RUT)</div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <label>VAT Responsible</label>
                    <select name="vat_responsible" class="form-control">
                        <option value="NO" <?= ($p['vat_responsible']??'') == 'NO' ? 'selected' : '' ?>>No</option>
                        <option value="YES" <?= ($p['vat_responsible']??'') == 'YES' ? 'selected' : '' ?>>Yes</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label>ICA Responsible</label>
                    <select name="ica_responsible" class="form-control">
                        <option value="NO" <?= ($p['ica_responsible']??'') == 'NO' ? 'selected' : '' ?>>No</option>
                        <option value="YES" <?= ($p['ica_responsible']??'') == 'YES' ? 'selected' : '' ?>>Yes</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label>Activity Code (CIIU)</label>
                    <input type="text" name="rut_activity_code" class="form-control" value="<?= $p['rut_activity_code']??'' ?>">
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md-4">
                    <label>ReteIVA Applicable?</label>
                    <select name="reteiva_applicable" class="form-control">
                        <option value="UNKNOWN" <?= ($p['reteiva_applicable']??'') == 'UNKNOWN' ? 'selected' : '' ?>>Unknown (Use Rules)</option>
                        <option value="YES" <?= ($p['reteiva_applicable']??'') == 'YES' ? 'selected' : '' ?>>Force YES</option>
                        <option value="NO" <?= ($p['reteiva_applicable']??'') == 'NO' ? 'selected' : '' ?>>Force NO</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Contact -->
    <div class="card mb-3">
        <div class="card-header">Contact</div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" value="<?= $p['email']??'' ?>">
                </div>
                <div class="col-md-6">
                    <label>Phone</label>
                    <input type="text" name="phone" class="form-control" value="<?= $p['phone']??'' ?>">
                </div>
            </div>
             <div class="row mt-2">
                <div class="col-md-6">
                    <label>Address</label>
                    <input type="text" name="address" class="form-control" value="<?= $p['address']??'' ?>">
                </div>
                <div class="col-md-6">
                    <label>City</label>
                    <input type="text" name="city" class="form-control" value="<?= $p['city']??'' ?>">
                </div>
            </div>
        </div>
    </div>

    <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Update' : 'Create' ?></button>
    <a href="/gym/accounting/third-parties" class="btn btn-secondary">Cancel</a>
</form>

<script>
function toggleActivity() {
    const isNoActivity = document.getElementById('noActivity').checked;
    const taxSection = document.getElementById('taxSection');
    const inputs = taxSection.querySelectorAll('input, select');

    if (isNoActivity) {
        taxSection.style.opacity = '0.5';
        inputs.forEach(i => i.disabled = true);
    } else {
        taxSection.style.opacity = '1';
        inputs.forEach(i => i.disabled = false);
    }
}
// Init
toggleActivity();
</script>
