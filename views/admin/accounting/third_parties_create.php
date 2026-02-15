<div class="page-header">
    <h2>Create Third Party (Provider/Customer)</h2>
    <a href="<?= url('/admin/accounting/third-parties') ?>" class="btn btn-secondary btn-sm">Cancel</a>
</div>

<div class="card" style="max-width: 800px;">
    <div class="card-body">
        <form action="<?= url('/admin/accounting/third-parties/store') ?>" method="POST">
            <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= $_SESSION[CSRF_TOKEN_NAME] ?>">

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label>Relationship Type</label>
                        <select name="third_type" class="form-control" required>
                            <option value="PROVEEDOR">Provider (Vendor)</option>
                            <option value="CLIENTE">Customer</option>
                            <option value="MIXTO">Mixed</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label>Person Type</label>
                        <select name="person_type" class="form-control" required>
                            <option value="JURIDICA">Juridical (Company)</option>
                            <option value="NATURAL">Natural (Individual)</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group mb-3">
                        <label>Document Type</label>
                        <select name="document_type" class="form-control">
                            <option value="NIT">NIT</option>
                            <option value="CC">Cédula (CC)</option>
                            <option value="CE">Cédula Extranjería</option>
                            <option value="PASSPORT">Passport</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="form-group mb-3">
                        <label>Document Number (DV will be calc if needed)</label>
                        <input type="text" name="document_number" class="form-control" required>
                    </div>
                </div>
            </div>

            <div class="form-group mb-3">
                <label>Full Name / Company Name</label>
                <input type="text" name="full_name_or_company" class="form-control" required>
            </div>

            <div class="form-group mb-3">
                <label>Trade Name (Optional)</label>
                <input type="text" name="trade_name" class="form-control">
            </div>

            <hr>
            <h4>Tax Responsibilities (RUT)</h4>
            
            <div class="row">
                <div class="col-md-6">
                     <div class="form-group mb-3">
                        <label>Economic Activity?</label>
                        <select name="has_economic_activity" class="form-control">
                            <option value="1">Yes</option>
                            <option value="0">No (No Activity)</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                     <div class="form-group mb-3">
                        <label>CIIU Activity Code</label>
                        <input type="text" name="rut_activity_code" class="form-control" placeholder="e.g. 6201">
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label>Responsible for IVA?</label>
                        <select name="vat_responsible" class="form-control">
                            <option value="NO">No</option>
                            <option value="YES">Yes</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label>Responsible for ICA?</label>
                        <select name="ica_responsible" class="form-control">
                            <option value="NO">No</option>
                            <option value="YES">Yes</option>
                        </select>
                    </div>
                </div>
            </div>

            <hr>
            <h4>Contact Info</h4>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label>Phone</label>
                        <input type="text" name="phone" class="form-control">
                    </div>
                </div>
            </div>
            <div class="form-group mb-3">
                <label>Address</label>
                <input type="text" name="address" class="form-control">
            </div>

            <button type="submit" class="btn btn-primary">Create Third Party</button>
        </form>
    </div>
</div>
