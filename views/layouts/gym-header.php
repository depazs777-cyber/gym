<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($title) ? $title : SITE_NAME; ?> - <?php echo isset($tenant) ? $tenant->nombre : ''; ?></title>
    <link href="<?php echo URL_ROOT; ?>/assets/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { padding-top: 56px; }
        .sidebar { height: calc(100vh - 56px); position: fixed; top: 56px; left: 0; padding: 20px 0; background-color: #e9ecef; }
        .main-content { margin-left: 200px; padding: 20px; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?php echo URL_ROOT; ?>/gym/dashboard">
                <?php if (isset($tenant) && $tenant->logo): ?>
                    <img src="<?php echo URL_ROOT; ?>/assets/uploads/logos-gimnasios/<?php echo $tenant->logo; ?>" height="30" alt="">
                <?php endif; ?>
                <?php echo isset($tenant) ? $tenant->nombre : SITE_NAME; ?>
            </a>
            <div class="d-flex">
                <span class="navbar-text me-3 text-white">Hola, <?php echo Auth::user()->username; ?></span>
                <a href="<?php echo URL_ROOT; ?>/auth/logout" class="btn btn-outline-light btn-sm">Cerrar Sesión</a>
            </div>
        </div>
    </nav>
    <div class="container-fluid">
        <div class="row">
            <?php require_once 'sidebar-gym.php'; ?>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
