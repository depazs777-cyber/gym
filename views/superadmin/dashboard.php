<?php require_once APP_ROOT . '/views/layouts/superadmin-header.php'; ?>
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Dashboard Super Admin</h1>
    </div>

    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card text-white bg-primary shadow">
                <div class="card-body">
                    <h5 class="card-title">Gimnasios Activos</h5>
                    <p class="card-text display-4"><?php echo count($tenants); ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-4">
            <div class="card text-white bg-success shadow">
                <div class="card-body">
                    <h5 class="card-title">Leads Registrados</h5>
                    <p class="card-text display-4"><?php echo count($leads); ?></p>
                </div>
            </div>
        </div>
    </div>
<?php require_once APP_ROOT . '/views/layouts/footer.php'; ?>
