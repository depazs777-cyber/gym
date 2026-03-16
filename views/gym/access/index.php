<?php require_once APP_ROOT . '/views/layouts/gym-header.php'; ?>
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Control de Acceso</h1>
        <a href="<?php echo URL_ROOT; ?>/gym/access/validator" class="btn btn-sm btn-primary">Abrir Validador</a>
    </div>
    <div class="card">
        <div class="card-body">
            <p>Desde aquí puede abrir la ventana del validador de códigos QR para el control de torniquetes o recepción.</p>
        </div>
    </div>
<?php require_once APP_ROOT . '/views/layouts/footer.php'; ?>
