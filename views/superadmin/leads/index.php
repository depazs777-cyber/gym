<?php require_once APP_ROOT . '/views/layouts/superadmin-header.php'; ?>
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">CRM Leads</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="<?php echo URL_ROOT; ?>/superadmin/lead/create" class="btn btn-sm btn-primary">Nuevo Lead</a>
        </div>
    </div>

    <?php Helpers::flash('lead_msg'); ?>

    <!-- Para drag and drop se necesita librería (e.g. SortableJS). Usamos vista de tabla de forma nativa por simplicidad -->
    <div class="table-responsive">
        <table class="table table-striped table-sm">
            <thead>
                <tr>
                    <th>Gimnasio</th>
                    <th>Contacto</th>
                    <th>Email</th>
                    <th>Teléfono</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($leads as $lead): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($lead->nombre_gym); ?></td>
                        <td><?php echo htmlspecialchars($lead->contacto); ?></td>
                        <td><?php echo htmlspecialchars($lead->email); ?></td>
                        <td><?php echo htmlspecialchars($lead->telefono); ?></td>
                        <td>
                            <span class="badge bg-<?php
                                echo $lead->estado == 'nuevo' ? 'primary' :
                                    ($lead->estado == 'contactado' ? 'info' :
                                    ($lead->estado == 'ganado' ? 'success' : 'secondary'));
                            ?>"><?php echo ucfirst(str_replace('_', ' ', $lead->estado)); ?></span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php require_once APP_ROOT . '/views/layouts/footer.php'; ?>
