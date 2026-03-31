<?php
$title = 'Alta de Nuevo Gimnasio';
ob_start();
?>

<div class="mb-4">
    <a href="<?= BASE_URL ?>/admin/gyms">← Volver al Listado</a>
</div>

<div class="card" style="max-width: 600px; margin: 0 auto;">
    <h2>Registrar Nuevo Cliente (Tenant)</h2>
    <p class="text-muted">Esto creará el espacio de trabajo para el nuevo gimnasio.</p>

    <form action="<?= BASE_URL ?>/admin/gyms/store" method="POST">
        <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= $_SESSION[CSRF_TOKEN_NAME] ?? '' ?>">

        <label>Nombre Comercial del Gimnasio</label>
        <input type="text" name="name" required placeholder="Ej. Iron Forge Fitness">

        <label>Dominio / Subdominio Identificador</label>
        <input type="text" name="domain" placeholder="ironforge.gym.com">

        <label>Plan SaaS Contratado</label>
        <select name="plan_id" required>
            <option value="">Seleccione un plan</option>
            <?php foreach ($plans as $plan): ?>
                <option value="<?= $plan['id'] ?>"><?= $plan['name'] ?> - $<?= number_format($plan['price'], 0) ?></option>
            <?php endforeach; ?>
        </select>

        <button type="submit" class="mt-4" style="width: 100%;">Registrar Cliente SaaS</button>
    </form>
</div>

<?php
$content = ob_get_clean();
include VIEW_PATH . '/layouts/main.php';
?>
