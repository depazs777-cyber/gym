<?php
$isEdit = $isEdit ?? false;
$tp = $tp ?? [];
$action = $isEdit ? '/gym/third_parties/update' : '/gym/third_parties/store';
$title = $isEdit ? 'Edit Third Party' : 'Create New Third Party';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><?php echo $title; ?></h1>
    <a href="/gym/third_parties" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left"></i> Back
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form action="<?php echo $action; ?>" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <?php if ($isEdit): ?>
                <input type="hidden" name="id" value="<?php echo $tp['id']; ?>">
            <?php endif; ?>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Person Type</label>
                    <select name="type_persona" class="form-select" id="type_persona">
                        <option value="NATURAL" <?php echo ($tp['type_persona'] ?? '') === 'NATURAL' ? 'selected' : ''; ?>>Natural Person</option>
                        <option value="JURIDICA" <?php echo ($tp['type_persona'] ?? '') === 'JURIDICA' ? 'selected' : ''; ?>>Legal Entity (Company)</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <div class="form-check mt-4">
                        <input class="form-check-input" type="checkbox" name="is_provider" id="is_provider" <?php echo !empty($tp['is_provider']) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="is_provider">Is Provider?</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_client" id="is_client" <?php echo !empty($tp['is_client']) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="is_client">Is Client?</label>
                    </div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-3">
                    <label class="form-label">Doc Type</label>
                    <select name="doc_type" class="form-select">
                        <option value="NIT" <?php echo ($tp['doc_type'] ?? '') === 'NIT' ? 'selected' : ''; ?>>NIT</option>
                        <option value="CC" <?php echo ($tp['doc_type'] ?? '') === 'CC' ? 'selected' : ''; ?>>Cedula</option>
                        <option value="CE" <?php echo ($tp['doc_type'] ?? '') === 'CE' ? 'selected' : ''; ?>>Cedula Extranjeria</option>
                        <option value="PASSPORT" <?php echo ($tp['doc_type'] ?? '') === 'PASSPORT' ? 'selected' : ''; ?>>Passport</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Document Number</label>
                    <input type="text" name="doc_number" class="form-control" required value="<?php echo htmlspecialchars($tp['doc_number'] ?? ''); ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">DV (Digito Verif)</label>
                    <input type="text" name="dv" class="form-control" maxlength="1" value="<?php echo htmlspecialchars($tp['dv'] ?? ''); ?>">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Name / Legal Name (Razon Social)</label>
                    <input type="text" name="name" class="form-control" required value="<?php echo htmlspecialchars($tp['name'] ?? ''); ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Trade Name (Nombre Comercial)</label>
                    <input type="text" name="trade_name" class="form-control" value="<?php echo htmlspecialchars($tp['trade_name'] ?? ''); ?>">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($tp['email'] ?? ''); ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($tp['phone'] ?? ''); ?>">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Address</label>
                    <input type="text" name="address" class="form-control" value="<?php echo htmlspecialchars($tp['address'] ?? ''); ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">City</label>
                    <input type="text" name="city" class="form-control" value="<?php echo htmlspecialchars($tp['city'] ?? ''); ?>">
                </div>
            </div>

            <h5 class="mt-4">Tax Information</h5>
            <div class="row mb-3">
                <div class="col-md-4">
                    <div class="form-check mt-3">
                        <input class="form-check-input" type="checkbox" name="rut_required" id="rut_required" <?php echo !empty($tp['rut_required']) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="rut_required">RUT Required?</label>
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">ReteIVA %</label>
                    <input type="number" step="0.01" name="reteiva_percent" class="form-control" value="<?php echo htmlspecialchars($tp['reteiva_percent'] ?? '0'); ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">ReteICA %</label>
                    <input type="number" step="0.01" name="reteica_percent" class="form-control" value="<?php echo htmlspecialchars($tp['reteica_percent'] ?? '0'); ?>">
                </div>
            </div>

            <div class="d-flex justify-content-end mt-4">
                <button type="submit" class="btn btn-primary btn-lg">
                    <?php echo $isEdit ? 'Update Third Party' : 'Create Third Party'; ?>
                </button>
            </div>
        </form>
    </div>
</div>
