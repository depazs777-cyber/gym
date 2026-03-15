<?php require_once APP_ROOT . '/views/layouts/superadmin-header.php'; ?>
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Crear Plan</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="<?php echo URL_ROOT; ?>/plan" class="btn btn-sm btn-outline-secondary">Regresar</a>
        </div>
    </div>

    <div class="card shadow p-4 col-md-8 mx-auto">
        <form action="<?php echo URL_ROOT; ?>/plan/create" method="POST">
            <div class="mb-3">
                <label>Nombre del Plan</label>
                <input type="text" name="nombre" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Precio (Mensual)</label>
                <input type="number" step="0.01" name="precio" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Máximo de Miembros</label>
                <input type="number" name="max_miembros" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Descripción</label>
                <textarea name="descripcion" class="form-control" rows="3"></textarea>
            </div>
            <div class="mb-3">
                <label>Estado</label>
                <select name="estado" class="form-control">
                    <option value="activo">Activo</option>
                    <option value="inactivo">Inactivo</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Guardar Plan</button>
        </form>
    </div>
<?php require_once APP_ROOT . '/views/layouts/footer.php'; ?>
