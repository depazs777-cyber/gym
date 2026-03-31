<?php
$title = 'Página No Encontrada';
ob_start();
?>

<div class="card text-center" style="max-width: 400px; margin: 10% auto;">
    <h1 style="color: var(--accent); font-size: 4em; margin: 0;">404</h1>
    <h2>Página No Encontrada</h2>
    <p>Lo sentimos, la página que buscas no existe o ha sido movida.</p>
    <br>
    <a href="<?= BASE_URL ?>/dashboard" class="button">Volver al Inicio</a>
</div>

<?php
$content = ob_get_clean();
include VIEW_PATH . '/layouts/main.php';
?>
