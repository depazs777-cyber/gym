<?php require_once APP_ROOT . '/views/layouts/gym-header.php'; ?>
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Comprobantes Contables</h1>
        <a href="<?php echo URL_ROOT; ?>/accounting/form" class="btn btn-sm btn-primary">Nuevo Asiento Manual</a>
    </div>
    <?php Helpers::flash('accounting_msg'); ?>
    <!-- Historial de comprobantes contables -->
<?php require_once APP_ROOT . '/views/layouts/footer.php'; ?>
