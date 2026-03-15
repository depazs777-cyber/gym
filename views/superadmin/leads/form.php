<?php require_once APP_ROOT . '/views/layouts/superadmin-header.php'; ?>
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Crear Lead</h1>
        <a href="<?php echo URL_ROOT; ?>/lead" class="btn btn-sm btn-outline-secondary">Regresar</a>
    </div>

    <div class="card shadow p-4 col-md-8 mx-auto">
        <form action="<?php echo URL_ROOT; ?>/lead/create" method="POST">
            <div class="mb-3">
                <label>Nombre del Gimnasio</label>
                <input type="text" name="nombre_gym" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Nombre del Contacto</label>
                <input type="text" name="contacto" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Teléfono</label>
                <input type="text" name="telefono" class="form-control">
            </div>
            <div class="mb-3">
                <label>Estado</label>
                <select name="estado" class="form-select">
                    <option value="nuevo">Nuevo</option>
                    <option value="contactado">Contactado</option>
                    <option value="en_negociacion">En Negociación</option>
                    <option value="ganado">Ganado</option>
                    <option value="perdido">Perdido</option>
                </select>
            </div>
            <div class="mb-3">
                <label>Notas</label>
                <textarea name="notas" class="form-control" rows="3"></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Guardar Lead</button>
        </form>
    </div>
<?php require_once APP_ROOT . '/views/layouts/footer.php'; ?>
