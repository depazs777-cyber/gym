<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Gestión Gimnasio' ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/theme.css">
</head>
<body>

<div class="app-container">
    <?php if (isset($_SESSION['user_id'])): ?>
    <aside class="sidebar">
        <div class="logo mb-4">
            <h2>Gym SaaS</h2>
            <small class="text-muted">
                <?php
                    if ($_SESSION['role'] === 'super_admin') {
                        echo 'Gestión Global (Maestro)';
                    } else {
                        echo $_SESSION['gym_name'] ?? 'Mi Gimnasio';
                    }
                ?>
            </small>
        </div>
        <nav>
            <ul style="list-style: none; padding: 0;">

                <?php if ($_SESSION['role'] === 'super_admin' || $_SESSION['role'] === 'sales'): ?>
                    <!-- Menú Matriz / Vendedor SaaS -->
                    <li class="mb-4"><a href="<?= BASE_URL ?>/admin/dashboard">🌐 Panel Maestro</a></li>
                    <li class="mb-4"><a href="<?= BASE_URL ?>/admin/gyms">🏢 Gimnasios (Clientes)</a></li>
                    <li class="mb-4"><a href="<?= BASE_URL ?>/admin/leads">💼 CRM Ventas (Leads)</a></li>
                    <li class="mb-4"><a href="<?= BASE_URL ?>/admin/billing">💸 Facturación SaaS</a></li>
                <?php else: ?>
                    <!-- Menú Cliente Gym -->
                    <li class="mb-4"><a href="<?= BASE_URL ?>/dashboard">📊 Panel del Gym</a></li>
                    <li class="mb-4"><a href="<?= BASE_URL ?>/accounting">💰 Contabilidad y Pagos</a></li>
                    <li class="mb-4"><a href="<?= BASE_URL ?>/members">👥 Afiliados</a></li>
                    <li class="mb-4"><a href="<?= BASE_URL ?>/access">🔑 Control Acceso</a></li>
                <?php endif; ?>

                <li class="mb-4" style="margin-top: 50px;"><a href="<?= BASE_URL ?>/logout" style="color: #ff6b6b;">🚪 Cerrar Sesión</a></li>
            </ul>
        </nav>
    </aside>
    <?php endif; ?>

    <main class="main-content">
        <?php if (isset($_SESSION['user_id'])): ?>
        <header class="flex justify-between mb-4">
            <h1><?= $title ?? 'Panel' ?></h1>
            <div class="user-info">
                <span>Hola, <?= $_SESSION['user_name'] ?? 'Usuario' ?></span>
            </div>
        </header>
        <?php endif; ?>

        <div class="content">
            <?= $content ?? '' ?>
        </div>
    </main>
</div>

</body>
</html>
