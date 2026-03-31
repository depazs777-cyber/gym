<?php
$title = 'Nuevo Prospecto CRM';
ob_start();
?>

<div class="mb-4">
    <a href="<?= BASE_URL ?>/admin/leads">← Volver al Embudo</a>
</div>

<div class="card" style="max-width: 600px; margin: 0 auto;">
    <h2>Registrar Nuevo Prospecto (Lead)</h2>
    <p class="text-muted">Ingresa los datos del gimnasio interesado en adquirir el software SaaS.</p>

    <form action="<?= BASE_URL ?>/admin/leads/store" method="POST">
        <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= $_SESSION[CSRF_TOKEN_NAME] ?? '' ?>">

        <label>Nombre del Gimnasio (Empresa prospecto)</label>
        <input type="text" name="company_name" required placeholder="Ej. Muscle Gym">

        <label>Persona de Contacto (Dueño/Administrador)</label>
        <input type="text" name="contact_name" required placeholder="Ej. Pedro Ramirez">

        <div class="flex" style="gap: 10px;">
            <div style="flex:1;">
                <label>Email de Contacto</label>
                <input type="email" name="email" required placeholder="contacto@gym.com">
            </div>
            <div style="flex:1;">
                <label>Teléfono (WhatsApp)</label>
                <input type="text" name="phone" placeholder="300...">
            </div>
        </div>

        <label>Notas / Intereses (Opcional)</label>
        <textarea name="notes" rows="3" placeholder="El gimnasio tiene 3 sedes y necesita migrar de software..."></textarea>

        <button type="submit" class="mt-4" style="width: 100%;">Crear Lead en Embudo</button>
    </form>
</div>

<?php
$content = ob_get_clean();
include VIEW_PATH . '/layouts/main.php';
?>
