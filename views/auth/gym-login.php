<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo $tenant->nombre; ?></title>
    <link href="<?php echo URL_ROOT; ?>/assets/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center" style="height: 100vh;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-4">
                <div class="text-center mb-4">
                    <?php if ($tenant->logo): ?>
                        <img src="<?php echo URL_ROOT; ?>/assets/uploads/logos-gimnasios/<?php echo $tenant->logo; ?>" alt="Logo" class="img-fluid" style="max-height: 100px;">
                    <?php else: ?>
                        <h2><?php echo $tenant->nombre; ?></h2>
                    <?php endif; ?>
                </div>
                <div class="card shadow">
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
                                <button type="submit" class="btn btn-primary">Entrar al Gimnasio</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
