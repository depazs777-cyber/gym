<?php
// SCRIPT DE CREACIÓN DE ADMIN MAESTRO (SOPORTA CLI Y WEB)

require_once 'config/constants.php';
if (!file_exists('config/database.php')) {
    die("Error: El sistema no está instalado. Ejecuta install.php primero.\n");
}
require_once 'config/database.php';

$is_cli = php_sapi_name() === 'cli';
$message = '';
$messageType = '';

function createAdmin($name, $email, $username, $password) {
    try {
        $db = new Database();

        // Verificar si ya existe
        $db->query("SELECT id FROM users WHERE email = :email OR username = :username");
        $db->bind(':email', $email);
        $db->bind(':username', $username);
        $db->execute();

        if ($db->rowCount() > 0) {
            return ["success" => false, "msg" => "Error: Ya existe un usuario con ese email o username."];
        }

        // Obtener ID del rol Super Admin
        $db->query("SELECT id FROM roles WHERE nombre = 'Super Admin' LIMIT 1");
        $rol = $db->single();
        if (!$rol) {
            return ["success" => false, "msg" => "Error: No se encontró el rol 'Super Admin' en la base de datos. Ejecutaste seeds.sql?"];
        }

        // Insertar usuario
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        $db->query("INSERT INTO users (rol_id, nombre_completo, email, username, password, estado) VALUES (:rol_id, :nombre, :email, :username, :password, 'activo')");
        $db->bind(':rol_id', $rol->id);
        $db->bind(':nombre', $name);
        $db->bind(':email', $email);
        $db->bind(':username', $username);
        $db->bind(':password', $hashed_password);

        if ($db->execute()) {
            return ["success" => true, "msg" => "¡Super Admin creado exitosamente!"];
        } else {
            return ["success" => false, "msg" => "Error al crear el usuario en la base de datos."];
        }
    } catch (Exception $e) {
        return ["success" => false, "msg" => "Error de base de datos: " . $e->getMessage()];
    }
}

if ($is_cli) {
    $options = getopt("", ["name:", "email:", "username:", "password:", "interactive"]);

    if (isset($options['interactive'])) {
        echo "=== CREACIÓN DE SUPER ADMIN ===\n";

        echo "Nombre completo: ";
        $name = trim(fgets(STDIN));

        echo "Email: ";
        $email = trim(fgets(STDIN));

        echo "Username: ";
        $username = trim(fgets(STDIN));

        echo "Contraseña: ";
        $password = trim(fgets(STDIN));
    } else {
        $name = $options['name'] ?? null;
        $email = $options['email'] ?? null;
        $username = $options['username'] ?? null;
        $password = $options['password'] ?? null;
    }

    if (!$name || !$email || !$username || !$password) {
        echo "Error: Faltan parametros.\nUso: php create-admin.php --name=\"Nombre\" --email=\"email@ejemplo.com\" --username=\"admin\" --password=\"pass\"\nO usa el modo interactivo: php create-admin.php --interactive\n";
        return;
    }

    $result = createAdmin($name, $email, $username, $password);
    echo $result['msg'] . "\n";
    if ($result['success']) {
        echo "Por seguridad, debes eliminar el archivo create-admin.php\n";
    }
    return;
}

// MODO WEB

// Check if a super admin already exists to prevent abuse
$db = new Database();
$db->query("SELECT count(*) as count FROM users u JOIN roles r ON u.rol_id = r.id WHERE r.nombre = 'Super Admin'");
$adminCount = $db->single();

if ($adminCount && $adminCount->count > 0) {
    die("Ya existe un Super Admin registrado. Por seguridad, no se pueden crear más desde el navegador. Elimina este archivo.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (!$name || !$email || !$username || !$password) {
        $message = "Todos los campos son obligatorios.";
        $messageType = "danger";
    } else {
        $result = createAdmin($name, $email, $username, $password);
        if ($result['success']) {
            $message = $result['msg'] . " <br><br><strong>IMPORTANTE:</strong> Elimina este archivo (`create-admin.php`) por seguridad.<br><br><a href='" . URL_ROOT . "/auth/login' class='btn btn-success mt-3'>Ir al Login</a>";
            $messageType = "success";
        } else {
            $message = $result['msg'];
            $messageType = "danger";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Super Admin - FitManager</title>
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-dark text-white text-center">
                        <h4>Crear Super Administrador</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($message): ?>
                            <div class="alert alert-<?php echo $messageType; ?>">
                                <?php echo $message; ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($messageType !== 'success'): ?>
                            <form method="POST">
                                <div class="mb-3">
                                    <label>Nombre Completo</label>
                                    <input type="text" name="name" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label>Correo Electrónico</label>
                                    <input type="email" name="email" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label>Usuario (Username)</label>
                                    <input type="text" name="username" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label>Contraseña</label>
                                    <input type="password" name="password" class="form-control" required>
                                </div>
                                <button type="submit" class="btn btn-primary w-100">Crear Administrador</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
