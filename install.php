<?php
// SCRIPT DE INSTALACIÓN
// Verifica si ya está instalado
if (file_exists('config/database.php') && filesize('config/database.php') > 0) {
    die("El sistema ya está instalado. Por seguridad, elimina o bloquea este archivo.");
}

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db_host = $_POST['db_host'] ?? 'localhost';
    $db_name = $_POST['db_name'] ?? '';
    $db_user = $_POST['db_user'] ?? '';
    $db_pass = $_POST['db_pass'] ?? '';

    try {
        // Conectar a la base de datos
        $pdo = new PDO("mysql:host=$db_host;charset=utf8mb4", $db_user, $db_pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Crear base de datos si no existe
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE `$db_name`");

        // Leer y ejecutar database.sql
        $sql = file_get_contents('sql/database.sql');
        if (!$sql) throw new Exception("No se pudo leer sql/database.sql");
        $pdo->exec($sql);

        // Leer y ejecutar seeds.sql
        $seeds = file_get_contents('sql/seeds.sql');
        if (!$seeds) throw new Exception("No se pudo leer sql/seeds.sql");
        $pdo->exec($seeds);

        // Escape variables para escribirlas con seguridad en el archivo PHP
        $safe_host = var_export($db_host, true);
        $safe_user = var_export($db_user, true);
        $safe_pass = var_export($db_pass, true);
        $safe_name = var_export($db_name, true);

        // Crear archivo config/database.php
        $config_content = <<<EOT
<?php
class Database {
    private \$host = $safe_host;
    private \$user = $safe_user;
    private \$pass = $safe_pass;
    private \$dbname = $safe_name;
    private \$dbh;
    private \$stmt;

    public function __construct() {
        \$dsn = 'mysql:host=' . \$this->host . ';dbname=' . \$this->dbname . ';charset=utf8mb4';
        \$options = array(
            PDO::ATTR_PERSISTENT => true,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ
        );
        try {
            \$this->dbh = new PDO(\$dsn, \$this->user, \$this->pass, \$options);
        } catch(PDOException \$e) {
            die("Error de base de datos");
        }
    }

    public function query(\$sql) { \$this->stmt = \$this->dbh->prepare(\$sql); }
    public function bind(\$param, \$value, \$type = null) { \$this->stmt->bindValue(\$param, \$value, \$type); }
    public function execute() { return \$this->stmt->execute(); }
    public function resultSet() { \$this->execute(); return \$this->stmt->fetchAll(); }
    public function single() { \$this->execute(); return \$this->stmt->fetch(); }
    public function rowCount() { return \$this->stmt->rowCount(); }
    public function lastInsertId() { return \$this->dbh->lastInsertId(); }
}
EOT;

        if (file_put_contents('config/database.php', $config_content) === false) {
            throw new Exception("No se pudo escribir en config/database.php. Verifica permisos.");
        }

        $message = "Instalación exitosa. Base de datos creada y sembrada. Archivo de configuración generado.";
        $message .= "<br><br><strong>SIGUIENTE PASO:</strong> Crea el usuario Super Admin ejecutando en tu terminal:<br>";
        $message .= "<code>php create-admin.php --interactive</code>";
        $messageType = "success";

    } catch (Exception $e) {
        $message = "Error durante la instalación: " . $e->getMessage();
        $messageType = "danger";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalación FitManager</title>
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white text-center">
                        <h4>Instalación FitManager</h4>
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
                                    <label>Host BD</label>
                                    <input type="text" name="db_host" class="form-control" value="localhost" required>
                                </div>
                                <div class="mb-3">
                                    <label>Nombre BD</label>
                                    <input type="text" name="db_name" class="form-control" value="fitmanager" required>
                                </div>
                                <div class="mb-3">
                                    <label>Usuario BD</label>
                                    <input type="text" name="db_user" class="form-control" value="root" required>
                                </div>
                                <div class="mb-3">
                                    <label>Contraseña BD</label>
                                    <input type="password" name="db_pass" class="form-control">
                                </div>
                                <button type="submit" class="btn btn-primary w-100">Instalar</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
