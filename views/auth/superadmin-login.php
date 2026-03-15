<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Super Admin</title>
    <link href="<?php echo URL_ROOT; ?>/assets/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-dark d-flex align-items-center" style="height: 100vh;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-4">
                <div class="card shadow">
                    <div class="card-header text-center bg-primary text-white">
                        <h4>FitManager SaaS Admin</h4>
                    </div>
                    <div class="card-body">
                        <?php Helpers::flash('login_error'); ?>
                        <form action="<?php echo URL_ROOT; ?>/auth/login" method="POST">
                            <input type="hidden" name="csrf_token" value="<?php echo Session::generateCsrfToken(); ?>">
                            <div class="mb-3">
                                <label for="username" class="form-label">Usuario</label>
                                <input type="text" name="username" class="form-control" required autofocus>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Contraseña</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">Ingresar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
