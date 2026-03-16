<?php require_once APP_ROOT . '/views/layouts/superadmin-header.php'; ?>
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Planes de Suscripción</h1>
        <div class="btn-toolbar mb-2 mb-md-0 gap-2">
            <a href="<?php echo URL_ROOT; ?>/plan/bulkIncrease" class="btn btn-sm btn-warning fw-bold text-dark">
                <i class="fa fa-arrow-up"></i> Aumento Masivo
            </a>
            <a href="<?php echo URL_ROOT; ?>/plan/create" class="btn btn-sm btn-primary">Crear Plan</a>
        </div>
    </div>

    <?php Helpers::flash('plan_msg'); ?>

    <div class="table-responsive">
        <table class="table table-striped table-sm">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Precio</th>
                    <th>Max Miembros</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($plans as $plan): ?>
                    <tr>
                        <td><?php echo $plan->id; ?></td>
                        <td><?php echo htmlspecialchars($plan->nombre); ?></td>
                        <td>$<?php echo number_format($plan->precio, 2); ?></td>
                        <td><?php echo $plan->max_miembros; ?></td>
                        <td><span class="badge bg-<?php echo $plan->estado == 'activo' ? 'success' : 'secondary'; ?>"><?php echo ucfirst($plan->estado); ?></span></td>
                        <td>
                            <a href="<?php echo URL_ROOT; ?>/plan/edit/<?php echo $plan->id; ?>" class="btn btn-sm btn-outline-primary">Editar</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php require_once APP_ROOT . '/views/layouts/footer.php'; ?>
