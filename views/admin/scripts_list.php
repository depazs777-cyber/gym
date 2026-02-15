<?php defined('APP_NAME') or exit('No direct script access allowed'); ?>
<div class="page-header">
    <h2>Call Scripts</h2>
    <button class="btn btn-primary" onclick="document.getElementById('scriptModal').style.display='flex'">Add Script</button>
</div>

<div class="card">
    <table>
        <thead>
            <tr>
                <th>Title</th>
                <th>Target</th>
                <th>Objective</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($scripts as $s): ?>
            <tr>
                <td><?= htmlspecialchars($s['title']) ?></td>
                <td><?= htmlspecialchars($s['customer_type']) ?></td>
                <td><?= htmlspecialchars($s['objective']) ?></td>
                <td><?= $s['is_active'] ? 'Active' : 'Inactive' ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div id="scriptModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>New Script</h3>
            <button class="btn btn-sm btn-secondary" onclick="document.getElementById('scriptModal').style.display='none'">&times;</button>
        </div>
        <form action="<?= url("/admin/scripts/store") ?>" method="POST">
            <div class="modal-body">
                <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= $_SESSION[CSRF_TOKEN_NAME] ?>">
                <div class="form-group"><label>Title</label><input type="text" name="title" required></div>
                <div class="form-group"><label>Type</label>
                    <select name="customer_type">
                        <option value="SMALL_GYM">Small Gym</option>
                        <option value="MEDIUM_GYM">Medium Gym</option>
                        <option value="PREMIUM">Premium</option>
                    </select>
                </div>
                <div class="form-group"><label>Objective</label><input type="text" name="objective" required></div>
                <div class="form-group"><label>Body</label><textarea name="script_body" rows="6" required></textarea></div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>
