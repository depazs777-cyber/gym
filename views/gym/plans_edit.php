<?php defined('APP_NAME') or exit('No direct script access allowed'); ?>
<h2>Edit Plan: <?= htmlspecialchars($plan['name']) ?></h2>
<div class="card" style="max-width: 600px;">
    <form action="<?= url("/gym/plans/update") ?>" method="POST">
        <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= $_SESSION[CSRF_TOKEN_NAME] ?>">
        <input type="hidden" name="id" value="<?= $plan['id'] ?>">

        <div class="form-group">
            <label for="name">Plan Name</label>
            <input type="text" id="name" name="name" value="<?= htmlspecialchars($plan['name']) ?>" required>
        </div>
        
        <div class="form-group">
            <label>Type</label>
            <input type="text" value="<?= $plan['type'] === 'TIME' ? 'Time Based' : 'Session Based' ?>" disabled style="background-color: #eee;">
            <small>Type cannot be changed.</small>
        </div>

        <?php if ($plan['type'] === 'TIME'): ?>
        <div class="form-group">
            <label for="duration">Duration (Days)</label>
            <input type="number" id="duration" name="duration" value="<?= htmlspecialchars($plan['duration_days']) ?>">
        </div>
        <?php else: ?>
        <div class="form-group">
            <label for="sessions">Number of Sessions</label>
            <input type="number" id="sessions" name="sessions" value="<?= htmlspecialchars($plan['sessions_count']) ?>">
        </div>
        <?php endif; ?>

        <div class="form-group">
            <label for="price">Price</label>
            <input type="number" step="0.01" id="price" name="price" value="<?= htmlspecialchars($plan['price']) ?>" required>
        </div>

        <div class="form-group">
            <label for="status">Status</label>
            <select id="status" name="status">
                <option value="active" <?= $plan['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                <option value="inactive" <?= $plan['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
            </select>
        </div>

        <button type="submit" class="btn">Update Plan</button>
        <a href="<?= url("/gym/plans") ?>" class="btn" style="background-color: #6c757d;">Cancel</a>
    </form>
</div>
