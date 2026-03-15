<?php defined('APP_NAME') or exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?> - Login</title>
    <link rel="stylesheet" href="<?= url('/assets/css/theme.css') ?>">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background-color: var(--gray-100);
        }
        .login-card {
            width: 100%;
            max-width: 400px;
            padding: 2.5rem;
            border-radius: 1rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            background: white;
            text-align: center;
        }
        .brand-logo {
            font-size: 2rem;
            font-weight: 800;
            color: var(--primary);
            margin-bottom: 0.5rem;
            letter-spacing: -1px;
        }
        .brand-subtitle {
            color: var(--gray-500);
            margin-bottom: 2rem;
            font-size: 0.95rem;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="brand-logo"><?= APP_NAME ?></div>
        <div class="brand-subtitle">Gym Management SaaS</div>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error" style="text-align: left; margin-bottom: 1.5rem;">
                <?= $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <form action="<?= url("/login") ?>" method="POST">
            <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= $_SESSION[CSRF_TOKEN_NAME] ?>">

            <div class="mb-3" style="text-align: left;">
                <label for="email" class="mb-1" style="display:block; font-weight:500; font-size:0.9rem;">Email Address</label>
                <input type="email" id="email" name="email" class="form-control" required placeholder="you@example.com">
            </div>

            <div class="mb-4" style="text-align: left;">
                <label for="password" class="mb-1" style="display:block; font-weight:500; font-size:0.9rem;">Password</label>
                <input type="password" id="password" name="password" class="form-control" required placeholder="••••••••">
            </div>

            <button type="submit" class="btn btn-primary btn-block" style="width: 100%; padding: 0.75rem; font-size: 1rem;">Sign In</button>
        </form>

        <div class="mt-4 text-muted" style="font-size: 0.85rem;">
            &copy; <?= date('Y') ?> Prompt Maestro
        </div>
    </div>
</body>
</html>
