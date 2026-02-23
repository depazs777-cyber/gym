<?php
$title = 'Iniciar Sesión';
ob_start();
?>

<div style="display: flex; justify-content: center; align-items: center; height: 100vh;">
    <div class="card" style="width: 350px;">
        <h2 class="text-center">Bienvenido</h2>
        <?php if (isset($error)): ?>
            <div style="color: #ff6b6b; margin-bottom: 10px; text-align: center;"><?= $error ?></div>
        <?php endif; ?>

        <form action="<?= BASE_URL ?>/login" method="POST">
            <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= $_SESSION[CSRF_TOKEN_NAME] ?? '' ?>">

            <label>Email</label>
            <input type="email" name="email" required placeholder="admin@gym.com">

            <label>Contraseña</label>
            <input type="password" name="password" required placeholder="********">

            <button type="submit" style="width: 100%; margin-top: 10px;">Ingresar</button>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
include VIEW_PATH . '/layouts/main.php';
?>
