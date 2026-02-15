<?php defined('APP_NAME') or exit('No direct script access allowed'); ?>
<div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <h2 style="margin: 0;">Attendance Check-in</h2>
</div>

<div class="grid-2">
    <div class="card">
        <h3 class="card-title">Manual Entry / Scan</h3>
        <form action="<?= url("/gym/attendance/verify") ?>" method="POST">
            <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= $_SESSION[CSRF_TOKEN_NAME] ?>">
            
            <div class="form-group">
                <label for="identification">Client Identification / QR Code</label>
                <input type="text" id="identification" name="identification" autofocus required autocomplete="off" placeholder="Scan QR or type ID..." style="font-size: 1.2rem; padding: 1rem;">
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1rem; font-size: 1.1rem;">Verify Access</button>
        </form>
    </div>

    <div class="card">
        <h3 class="card-title">Recent Access Logs</h3>
        <a href="<?= url("/gym/attendance") ?>" class="btn btn-secondary btn-sm" style="margin-bottom: 1rem;">View Full Logs</a>
        <!-- Could inject a mini list here if controller provided data, currently independent -->
        <p style="color: var(--text-muted);">Check full logs for detailed history.</p>
    </div>
</div>
