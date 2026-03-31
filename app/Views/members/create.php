<?php
$title = 'Nuevo Afiliado';
ob_start();
?>

<div class="mb-4">
    <a href="<?= BASE_URL ?>/members">← Volver</a>
</div>

<div class="card" style="max-width: 600px; margin: 0 auto;">
    <h2>Registrar Nuevo Afiliado</h2>

    <form action="<?= BASE_URL ?>/members/store" method="POST">
        <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= $_SESSION[CSRF_TOKEN_NAME] ?? '' ?>">

        <div class="flex" style="gap: 10px;">
            <div style="flex:1;">
                <label>Tipo Doc.</label>
                <select name="doc_type">
                    <option value="CC">Cédula</option>
                    <option value="TI">Tarjeta Identidad</option>
                    <option value="CE">Cédula Extranjería</option>
                    <option value="PAS">Pasaporte</option>
                </select>
            </div>
            <div style="flex:2;">
                <label>Número Documento</label>
                <input type="text" name="doc_number" required>
            </div>
        </div>

        <label>Nombre Completo</label>
        <input type="text" name="name" required>

        <label>Email</label>
        <input type="email" name="email" required>

        <label>Teléfono</label>
        <input type="text" name="phone">

        <button type="submit" class="mt-4">Registrar y Generar QR</button>
    </form>
</div>

<?php
$content = ob_get_clean();
include VIEW_PATH . '/layouts/main.php';
?>
