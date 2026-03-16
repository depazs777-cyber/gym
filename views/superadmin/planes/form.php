<?php require_once APP_ROOT . '/views/layouts/superadmin-header.php';
$isEditing = isset($plan);
$action = $isEditing ? URL_ROOT . '/plan/edit/' . $plan->id : URL_ROOT . '/plan/create';
?>
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2"><?php echo $isEditing ? 'Editar Plan' : 'Crear Plan'; ?></h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="<?php echo URL_ROOT; ?>/plan" class="btn btn-sm btn-outline-secondary">Regresar</a>
        </div>
    </div>

    <div class="card shadow p-4 col-md-8 mx-auto">
        <form action="<?php echo $action; ?>" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo Session::generateCsrfToken(); ?>">
            <div class="mb-3">
                <label>Nombre del Plan</label>
                <input type="text" name="nombre" class="form-control" value="<?php echo $isEditing ? htmlspecialchars($plan->nombre) : ''; ?>" required>
            </div>
            <div class="mb-3">
                <label>Precio (Mensual)</label>
                <input type="number" step="0.01" name="precio" class="form-control" value="<?php echo $isEditing ? $plan->precio : ''; ?>" required>
            </div>
            <div class="mb-3">
                <label>Máximo de Miembros</label>
                <input type="number" name="max_miembros" class="form-control" value="<?php echo $isEditing ? $plan->max_miembros : ''; ?>" required>
            </div>
            <div class="mb-3">
                <label>Descripción</label>
                <textarea name="descripcion" class="form-control" rows="3"><?php echo $isEditing ? htmlspecialchars($plan->descripcion) : ''; ?></textarea>
            </div>
            <div class="mb-3">
                <label>Estado</label>
                <select name="estado" class="form-control">
                    <option value="activo" <?php echo ($isEditing && $plan->estado == 'activo') ? 'selected' : ''; ?>>Activo</option>
                    <option value="inactivo" <?php echo ($isEditing && $plan->estado == 'inactivo') ? 'selected' : ''; ?>>Inactivo</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary"><?php echo $isEditing ? 'Actualizar Plan' : 'Guardar Plan'; ?></button>
        </form>
    </div>
<?php require_once APP_ROOT . '/views/layouts/footer.php'; ?>
