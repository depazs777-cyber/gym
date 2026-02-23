<?php
$title = 'Control de Acceso (Simulador)';
ob_start();
?>

<div class="mb-4">
    <a href="<?= BASE_URL ?>/dashboard">← Volver al Dashboard</a>
</div>

<div class="card" style="max-width: 500px; margin: 0 auto; text-align: center;">
    <h2>Escanear Código QR</h2>
    <p>Simulación de lectura de token UUID</p>

    <div style="background: rgba(0,0,0,0.5); padding: 20px; border-radius: 8px; margin-bottom: 20px;">
        <input type="text" id="token-input" placeholder="Pegue aquí el UUID del token" style="font-family: monospace; text-align: center;">
        <button onclick="scanToken()" class="mt-4" style="width: 100%;">Validar Ingreso</button>
    </div>

    <div id="result-area" style="display: none; padding: 15px; border-radius: 8px; font-weight: bold;"></div>
</div>

<script>
async function scanToken() {
    const token = document.getElementById('token-input').value;
    const resultArea = document.getElementById('result-area');

    resultArea.style.display = 'none';

    if (!token) {
        alert('Ingrese un token');
        return;
    }

    try {
        const formData = new FormData();
        formData.append('token', token);
        formData.append('<?= CSRF_TOKEN_NAME ?>', '<?= $_SESSION[CSRF_TOKEN_NAME] ?? '' ?>');

        const response = await fetch('<?= BASE_URL ?>/access/scan', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        resultArea.style.display = 'block';
        if (data.allowed) {
            resultArea.style.background = 'rgba(76, 175, 80, 0.2)';
            resultArea.style.color = '#81C784';
            resultArea.innerText = '✅ ACCESO CONCEDIDO';
        } else {
            resultArea.style.background = 'rgba(244, 67, 54, 0.2)';
            resultArea.style.color = '#E57373';
            resultArea.innerText = '❌ DENEGADO: ' + data.reason;
        }

    } catch (e) {
        console.error(e);
        alert('Error de conexión');
    }
}
</script>

<?php
$content = ob_get_clean();
include VIEW_PATH . '/layouts/main.php';
?>
