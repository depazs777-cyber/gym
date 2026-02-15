<?php defined('APP_NAME') or exit('No direct script access allowed'); ?>
<div class="page-header">
    <h2>Edit Gym: <?= htmlspecialchars($gym['name']) ?></h2>
</div>

<div class="card" style="max-width: 600px;">
    <form action="<?= url('/admin/gyms/update') ?>" method="POST">
        <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= $_SESSION[CSRF_TOKEN_NAME] ?>">
        <input type="hidden" name="id" value="<?= $gym['id'] ?>">

        <div class="form-group mb-3">
            <label>Gym Name</label>
            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($gym['name']) ?>" required>
        </div>

        <div class="form-group mb-3">
            <label>License Start</label>
            <input type="date" name="license_start" class="form-control" value="<?= htmlspecialchars($gym['license_start']) ?>" required>
        </div>

        <div class="form-group mb-3">
            <label>License End</label>
            <input type="date" name="license_end" class="form-control" value="<?= htmlspecialchars($gym['license_end']) ?>" required>
        </div>

        <div class="form-group mb-3">
            <label>Status</label>
            <select name="status" class="form-control" required>
                <option value="active" <?= $gym['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                <option value="expiring" <?= $gym['status'] === 'expiring' ? 'selected' : '' ?>>Expiring</option>
                <option value="expired" <?= $gym['status'] === 'expired' ? 'selected' : '' ?>>Expired</option>
                <option value="suspended" <?= $gym['status'] === 'suspended' ? 'selected' : '' ?>>Suspended</option>
            </select>
        </div>

        <div style="margin-top: 1.5rem; display: flex; gap: 1rem;">
            <button type="submit" class="btn btn-primary">Save Changes</button>
            <a href="<?= url('/admin/gyms') ?>" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
