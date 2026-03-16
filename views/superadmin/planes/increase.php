<?php require_once APP_ROOT . '/views/layouts/superadmin-header.php'; ?>
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Aumento Masivo de Planes</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="<?php echo URL_ROOT; ?>/superadmin/plan" class="btn btn-sm btn-outline-secondary">Regresar</a>
        </div>
    </div>

    <div class="card shadow p-4 col-md-10 mx-auto">
        <div class="alert alert-warning">
            <strong>Advertencia:</strong> Esta acción aumentará el precio base de todos los planes de suscripción.
            Puedes seleccionar gimnasios específicos que estarán exentos de este aumento (mantendrán su precio actual como "Precio Personalizado").
        </div>

        <form action="<?php echo URL_ROOT; ?>/superadmin/plan/bulkIncrease" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo Session::generateCsrfToken(); ?>">
            <div class="mb-4 row">
                <label class="col-sm-4 col-form-label fw-bold">Porcentaje de Aumento (%)</label>
                <div class="col-sm-4">
                    <input type="number" step="0.01" name="porcentaje" class="form-control" placeholder="Ej: 20" required>
                </div>
            </div>

            <h5 class="mt-4 mb-3 border-bottom pb-2">Excepciones (Checklist de Gimnasios)</h5>
            <p class="text-muted small">Selecciona los gimnasios que NO deben sufrir este aumento.</p>

            <div class="table-responsive">
                <table class="table table-sm table-hover border">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 50px;" class="text-center">Exento</th>
                            <th>Gimnasio</th>
                            <th>Plan Actual</th>
                            <th>Precio Base Plan</th>
                            <th>Precio Personalizado Actual</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($tenants)): ?>
                            <tr><td colspan="5" class="text-center text-muted">No hay gimnasios registrados.</td></tr>
                        <?php else: ?>
                            <?php foreach ($tenants as $t): ?>
                                <tr>
                                    <td class="text-center">
                                        <input class="form-check-input" type="checkbox" name="excepciones[]" value="<?php echo $t->id; ?>">
                                    </td>
                                    <td><?php echo htmlspecialchars($t->nombre); ?></td>
                                    <td><?php echo htmlspecialchars($t->plan_nombre); ?></td>
                                    <td>$<?php echo number_format($t->precio, 2); ?></td>
                                    <td>
                                        <?php if (isset($t->precio_personalizado) && $t->precio_personalizado): ?>
                                            <span class="badge bg-info text-dark">$<?php echo number_format($t->precio_personalizado, 2); ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">Ninguno</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="mt-4 text-end">
                <button type="submit" class="btn btn-danger" onclick="return confirm('¿Estás seguro de aplicar este aumento masivo?');">
                    Aplicar Aumento a Todos
                </button>
            </div>
        </form>
    </div>
<?php require_once APP_ROOT . '/views/layouts/footer.php'; ?>