<?php require_once APP_ROOT . '/views/layouts/gym-header.php'; ?>
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Renovar Membresía</h1>
        <a href="<?php echo URL_ROOT; ?>/payment" class="btn btn-sm btn-outline-secondary">Regresar</a>
    </div>

    <div class="card shadow p-4 col-md-8 mx-auto">
        <form action="<?php echo URL_ROOT; ?>/payment/create" method="POST">
            <div class="mb-3">
                <label>Seleccionar Miembro</label>
                <select name="member_id" class="form-select" required>
                    <option value="">Buscar miembro...</option>
                    <?php foreach ($members as $m): ?>
                        <option value="<?php echo $m->id; ?>"><?php echo htmlspecialchars($m->nombre . ' ' . $m->apellidos); ?> (<?php echo $m->identificacion; ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label>Monto a Pagar ($)</label>
                <input type="number" step="0.01" name="monto" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Meses a Renovar</label>
                <input type="number" name="meses" class="form-control" value="1" min="1" required>
            </div>
            <button type="submit" class="btn btn-primary">Registrar Pago y Generar Asiento Contable</button>
        </form>
    </div>
<?php require_once APP_ROOT . '/views/layouts/footer.php'; ?>
