<?php require_once APP_ROOT . '/views/layouts/gym-header.php'; ?>
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Dashboard</h1>
    </div>
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card text-white bg-primary shadow">
                <div class="card-body">
                    <h5 class="card-title">Miembros Totales</h5>
                    <p class="card-text display-4"><?php echo isset($members) ? count($members) : 0; ?></p>
                </div>
            </div>
        </div>
    </div>
<?php require_once APP_ROOT . '/views/layouts/footer.php'; ?>
