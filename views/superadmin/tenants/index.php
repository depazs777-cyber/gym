<?php require_once APP_ROOT . '/views/layouts/superadmin-header.php'; ?>
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Gimnasios (Tenants)</h1>
        <a href="<?php echo URL_ROOT; ?>/superadmin/tenant/create" class="btn btn-sm btn-primary">Nuevo Gimnasio</a>
    </div>

    <?php Helpers::flash('tenant_msg'); ?>

    <div class="table-responsive">
        <table class="table table-striped table-sm">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Plan</th>
                    <th>Precio Personalizado</th>
                    <th>Vencimiento</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tenants as $t): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($t->nombre); ?></td>
                        <td><?php echo $t->plan_nombre; ?></td>
                        <td><?php echo isset($t->precio_personalizado) && $t->precio_personalizado ? '$'.number_format($t->precio_personalizado, 2) : 'No'; ?></td>
                        <td><?php echo $t->fecha_vencimiento; ?></td>
                        <td><span class="badge bg-<?php echo $t->estado == 'activo' ? 'success' : 'danger'; ?>"><?php echo ucfirst($t->estado); ?></span></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php require_once APP_ROOT . '/views/layouts/footer.php'; ?>
