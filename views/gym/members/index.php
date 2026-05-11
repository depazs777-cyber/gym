<?php require_once APP_ROOT . '/views/layouts/gym-header.php'; ?>
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Miembros del Gimnasio</h1>
        <a href="<?php echo URL_ROOT; ?>/gym/member/create" class="btn btn-sm btn-primary">Nuevo Miembro</a>
    </div>

    <?php Helpers::flash('member_msg'); ?>

    <div class="table-responsive">
        <table class="table table-striped table-sm">
            <thead>
                <tr>
                    <th>Identificación</th>
                    <th>Nombre Completo</th>
                    <th>Teléfono</th>
                    <th>Vencimiento Membresía</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($members as $m): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($m->identificacion); ?></td>
                        <td><?php echo htmlspecialchars($m->nombre . ' ' . $m->apellidos); ?></td>
                        <td><?php echo htmlspecialchars($m->telefono); ?></td>
                        <td><?php echo $m->fecha_vencimiento ?: 'Sin membresía'; ?></td>
                        <td><span class="badge bg-<?php echo $m->estado == 'activo' ? 'success' : 'danger'; ?>"><?php echo ucfirst($m->estado); ?></span></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php require_once APP_ROOT . '/views/layouts/footer.php'; ?>
