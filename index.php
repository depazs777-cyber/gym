<?php

// Load Bootstrap Configuration
require_once 'config/constants.php';
require_once 'config/session.php';

// Check if installation exists or redirect to install.php
// Simplistic check for demo purposes
if (!file_exists('config/database.php')) {
    header('Location: install.php');
    exit();
}

// Load Database
require_once 'config/database.php';

// Load Core Libraries
require_once 'core/Helpers.php';
require_once 'core/Router.php';
require_once 'core/Controller.php';
require_once 'core/Model.php';
require_once 'core/View.php';
require_once 'core/Auth.php';
require_once 'core/Tenant.php';

// Start Session
Session::start();

// Init Core Router Object
$init = new Router();
