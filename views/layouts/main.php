<?php defined('APP_NAME') or exit('No direct script access allowed'); ?>
<?php require VIEW_PATH . '/partials/layout_start.php'; ?>

    <?php require VIEW_PATH . '/partials/sidebar.php'; ?>

    <div class="main-content">
        <?php require VIEW_PATH . '/partials/topbar.php'; ?>

        <div class="content-body">
            <?php if (in_array($_SESSION['user_role'] ?? '', ['CALL_CENTER', 'SUPER_ADMIN', 'MARKETING', 'VENDEDOR'])): ?>
                <div id="globalMotivationSlider" class="motivation-slider" style="display:none;"></div>
            <?php endif; ?>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
            <?php endif; ?>
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-error"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
            <?php endif; ?>

            <!-- View Content Injection -->
            <?php include VIEW_PATH . '/' . $childView . '.php'; ?>
        </div>
    </div>

<?php require VIEW_PATH . '/partials/layout_end.php'; ?>
