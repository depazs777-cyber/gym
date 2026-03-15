<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($title) ? $title : SITE_NAME; ?> - Super Admin</title>
    <link href="<?php echo URL_ROOT; ?>/assets/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome o estilos locales aquí -->
    <style>
        body { padding-top: 56px; }
        .sidebar { height: calc(100vh - 56px); position: fixed; top: 56px; left: 0; padding: 20px 0; background-color: #f8f9fa; }
        .main-content { margin-left: 200px; padding: 20px; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?php echo URL_ROOT; ?>/dashboard"><?php echo SITE_NAME; ?> Admin</a>
            <div class="d-flex">
                <a href="<?php echo URL_ROOT; ?>/auth/logout" class="btn btn-outline-light btn-sm">Cerrar Sesión</a>
            </div>
        </div>
    </nav>
    <div class="container-fluid">
        <div class="row">
            <?php require_once 'sidebar.php'; ?>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
