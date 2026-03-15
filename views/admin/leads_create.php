<?php defined('APP_NAME') or exit('No direct script access allowed'); ?>
<div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
    <h2 style="margin: 0;">Add New Lead</h2>
    <a href="<?= url("/admin/leads") ?>" class="btn btn-secondary">Back</a>
</div>

<div class="card" style="max-width: 500px;">
    <form action="<?= url("/admin/leads/store") ?>" method="POST">
        <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= $_SESSION[CSRF_TOKEN_NAME] ?>">

        <div class="form-group">
            <label>Name</label>
            <input type="text" name="name" required>
        </div>
        <div class="form-group">
            <label>Phone</label>
            <input type="text" name="phone" required>
        </div>
        <div class="form-group">
            <label>Type</label>
            <select name="customer_type">
                <option value="SMALL_GYM">Small Gym</option>
                <option value="MEDIUM_GYM">Medium Gym</option>
                <option value="PREMIUM">Premium</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Create Lead</button>
    </form>
</div>
