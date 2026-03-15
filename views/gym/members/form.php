<?php require_once APP_ROOT . '/views/layouts/gym-header.php'; ?>
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Registrar Miembro</h1>
        <a href="<?php echo URL_ROOT; ?>/member" class="btn btn-sm btn-outline-secondary">Regresar</a>
    </div>

    <div class="card shadow p-4 col-md-8 mx-auto">
        <form action="<?php echo URL_ROOT; ?>/member/create" method="POST">
            <div class="mb-3">
                <label>Identificación (DNI/Cédula)</label>
                <input type="text" name="identificacion" class="form-control" required>
            </div>
            <div class="row mb-3">
                <div class="col">
                    <label>Nombre(s)</label>
                    <input type="text" name="nombre" class="form-control" required>
                </div>
                <div class="col">
                    <label>Apellidos</label>
                    <input type="text" name="apellidos" class="form-control" required>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control">
                </div>
                <div class="col">
                    <label>Teléfono</label>
                    <input type="text" name="telefono" class="form-control">
                </div>
            </div>
            <!-- Omitimos subida de foto y otras cosas por simplicidad del ejemplo -->
            <button type="submit" class="btn btn-primary">Registrar y Generar QR</button>
        </form>
    </div>
<?php require_once APP_ROOT . '/views/layouts/footer.php'; ?>
