<?php require_once APP_ROOT . '/views/layouts/superadmin-header.php'; ?>
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Registrar Gimnasio</h1>
        <a href="<?php echo URL_ROOT; ?>/tenant" class="btn btn-sm btn-outline-secondary">Regresar</a>
    </div>

    <div class="card shadow p-4 col-md-8 mx-auto">
        <form action="<?php echo URL_ROOT; ?>/tenant/create" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo Session::generateCsrfToken(); ?>">
            <div class="mb-3">
                <label>Nombre del Gimnasio</label>
                <input type="text" name="nombre" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Plan Suscripción</label>
                <select name="plan_id" class="form-select" required>
                    <option value="">Seleccione un plan</option>
                    <?php foreach ($plans as $plan): ?>
                        <option value="<?php echo $plan->id; ?>"><?php echo $plan->nombre; ?> - $<?php echo $plan->precio; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label>Fecha de Vencimiento (Próximo Pago)</label>
                <input type="date" name="fecha_vencimiento" class="form-control" value="<?php echo date('Y-m-d', strtotime('+1 month')); ?>" required>
            </div>
            <div class="mb-3">
                <label>Estado</label>
                <select name="estado" class="form-select">
                    <option value="activo">Activo</option>
                    <option value="suspendido">Suspendido</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Crear Gimnasio</button>
        </form>
    </div>
<?php require_once APP_ROOT . '/views/layouts/footer.php'; ?>
