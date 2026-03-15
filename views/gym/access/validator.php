<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validador QR - <?php echo $tenant->nombre; ?></title>
    <link href="<?php echo URL_ROOT; ?>/assets/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #222; color: #fff; }
        .validador-box { max-width: 600px; margin: 50px auto; background: #333; padding: 30px; border-radius: 10px; box-shadow: 0 0 20px rgba(0,0,0,0.5); text-align: center; }
        #resultado { font-size: 1.5em; margin-top: 20px; font-weight: bold; padding: 20px; border-radius: 5px; }
        .success { background-color: #28a745; color: white; }
        .error { background-color: #dc3545; color: white; }
    </style>
</head>
<body>
    <div class="validador-box">
        <h2>Punto de Acceso: <?php echo $tenant->nombre; ?></h2>
        <p>Apunta el lector al código QR o ingresa el UUID manualmente.</p>

        <input type="text" id="token_input" class="form-control form-control-lg text-center" placeholder="UUID del QR" autofocus>

        <div id="resultado" style="display:none;"></div>
        <div id="miembro_info" class="mt-3"></div>

        <div class="mt-4">
            <a href="<?php echo URL_ROOT; ?>/dashboard" class="btn btn-outline-light">Regresar al Sistema</a>
        </div>
    </div>

    <script>
        document.getElementById('token_input').addEventListener('keypress', function (e) {
            if (e.key === 'Enter') {
                let token = this.value.trim();
                if(token === '') return;

                // Petición AJAX Fetch
                let formData = new FormData();
                formData.append('token', token);

                fetch('<?php echo URL_ROOT; ?>/access/validate', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    let resultDiv = document.getElementById('resultado');
                    let infoDiv = document.getElementById('miembro_info');

                    resultDiv.style.display = 'block';
                    if(data.success) {
                        resultDiv.className = 'success';
                        resultDiv.innerText = 'ACCESO PERMITIDO - BIENVENIDO';
                        infoDiv.innerHTML = '<h3>' + data.member.nombre + '</h3>';
                        // Aquí podría ir sonido de "Beep" exitoso
                    } else {
                        resultDiv.className = 'error';
                        resultDiv.innerText = 'ACCESO DENEGADO: ' + data.message;
                        infoDiv.innerHTML = '';
                        // Aquí podría ir sonido de error
                    }

                    // Limpiar input
                    this.value = '';
                    this.focus();

                    // Ocultar mensaje después de 3 segundos
                    setTimeout(() => {
                        resultDiv.style.display = 'none';
                        infoDiv.innerHTML = '';
                    }, 3000);
                })
                .catch(error => {
                    console.error('Error:', error);
                });
            }
        });

        // Mantener el foco en el input para lectores de pistola
        document.addEventListener('click', () => {
             document.getElementById('token_input').focus();
        });
    </script>
</body>
</html>
