<?php
$title = 'Nuevo Plan SaaS';
ob_start();
?>

<div class="mb-4">
    <a href="<?= BASE_URL ?>/admin/plans">← Volver al Listado</a>
</div>

<div class="card" style="max-width: 600px; margin: 0 auto;">
    <h2>Crear Plan de Suscripción (SaaS)</h2>
    <p class="text-muted">Define los límites y el precio del plan a vender a gimnasios.</p>

    <form action="<?= BASE_URL ?>/admin/plans/store" method="POST">
        <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= $_SESSION[CSRF_TOKEN_NAME] ?? '' ?>">

        <label>Nombre del Plan</label>
        <input type="text" name="name" required placeholder="Ej. Plan Pro Mensual">

        <label>Precio de Venta ($)</label>
        <input type="number" name="price" step="0.01" required placeholder="Ej. 100000">

        <label>Límite Máximo de Afiliados (Por Gimnasio)</label>
        <input type="number" name="max_members" value="500" required>

        <button type="submit" class="mt-4" style="width: 100%;">Guardar Plan</button>
    </form>
</div>

<?php
$content = ob_get_clean();
include VIEW_PATH . '/layouts/main.php';
?>
