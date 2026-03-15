<?php defined('APP_NAME') or exit('No direct script access allowed'); ?>
<h2>Gym Settings & Branding</h2>

<div class="card" style="max-width: 800px;">
    <form action="<?= url("/gym/settings/update") ?>" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= $_SESSION[CSRF_TOKEN_NAME] ?>">

        <div class="form-group">
            <label for="name">Gym Commercial Name</label>
            <input type="text" id="name" name="name" value="<?= htmlspecialchars($gym['name']) ?>" required>
        </div>

        <div class="form-group">
            <label for="branding_logo">Logo (PNG, JPG, WEBP - Max 2MB)</label>
            <?php if (!empty($gym['branding_logo'])): ?>
                <div style="margin-bottom: 10px;">
                    <img src="<?= url($gym['branding_logo']) ?>" alt="Gym Logo" style="max-height: 100px;">
                </div>
            <?php endif; ?>
            <input type="file" id="branding_logo" name="branding_logo" accept=".png, .jpg, .jpeg, .webp">
        </div>

        <hr>
        <h3>Configuration</h3>
        <div class="form-group">
             <label for="config_annual_days">Annual Days Definition (for "Yearly" plans)</label>
             <select name="config_annual_days" id="config_annual_days">
                 <option value="360" <?= ($gym['config_annual_days'] == 360) ? 'selected' : '' ?>>360 Days (Commercial Year)</option>
                 <option value="365" <?= ($gym['config_annual_days'] == 365) ? 'selected' : '' ?>>365 Days (Calendar Year)</option>
             </select>
        </div>
        <div class="form-group">
             <label for="config_renewal_mode">Default Renewal Mode</label>
             <select name="config_renewal_mode" id="config_renewal_mode">
                 <option value="CONTINUE" <?= ($gym['config_renewal_mode'] === 'CONTINUE') ? 'selected' : '' ?>>Continue from last expiration</option>
                 <option value="TODAY" <?= ($gym['config_renewal_mode'] === 'TODAY') ? 'selected' : '' ?>>Restart from today</option>
             </select>
        </div>
        <div class="form-group">
             <label for="config_deduct_session">Deduct Session on Check-in?</label>
             <select name="config_deduct_session" id="config_deduct_session">
                 <option value="1" <?= ($gym['config_deduct_session'] == 1) ? 'selected' : '' ?>>Yes</option>
                 <option value="0" <?= ($gym['config_deduct_session'] == 0) ? 'selected' : '' ?>>No (Manual control)</option>
             </select>
        </div>
        <div class="form-group">
             <label for="config_warning_days">Warning Threshold (Days before expiry)</label>
             <input type="number" id="config_warning_days" name="config_warning_days" value="<?= htmlspecialchars($gym['config_warning_days'] ?? 3) ?>" min="1">
        </div>

        <hr>
        <h3>Receipt Information</h3>

        <div class="form-group">
            <label for="nit">NIT / Tax ID</label>
            <input type="text" id="nit" name="nit" value="<?= htmlspecialchars($contactInfo['nit'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label for="address">Address</label>
            <input type="text" id="address" name="address" value="<?= htmlspecialchars($contactInfo['address'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label for="city">City</label>
            <input type="text" id="city" name="city" value="<?= htmlspecialchars($contactInfo['city'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label for="phone">Phone</label>
            <input type="text" id="phone" name="phone" value="<?= htmlspecialchars($contactInfo['phone'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($contactInfo['email'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label for="message">Receipt Footer Message</label>
            <textarea id="message" name="message" rows="3"><?= htmlspecialchars($contactInfo['message'] ?? '') ?></textarea>
        </div>

        <button type="submit" class="btn">Save Settings</button>
    </form>
</div>
