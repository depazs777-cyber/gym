<?php require_once APP_ROOT . '/views/layouts/gym-header.php'; ?>
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Pagos de Membresías</h1>
        <a href="<?php echo URL_ROOT; ?>/payment/create" class="btn btn-sm btn-primary">Registrar Pago</a>
    </div>
    <?php Helpers::flash('payment_msg'); ?>
    <!-- Aquí iría la tabla del historial de pagos, simplificada para el demo -->
<?php require_once APP_ROOT . '/views/layouts/footer.php'; ?>
