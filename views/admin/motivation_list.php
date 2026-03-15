<?php defined('APP_NAME') or exit('No direct script access allowed'); ?>
<div class="page-header">
    <h2>Daily Motivation</h2>
    <button class="btn btn-primary" onclick="document.getElementById('motivModal').style.display='flex'">Add Post</button>
</div>

<div class="card">
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Quote</th>
                <th>Image</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($posts as $p): ?>
            <tr>
                <td><?= htmlspecialchars($p['show_date']) ?></td>
                <td><?= htmlspecialchars($p['quote_text']) ?></td>
                <td>
                    <?php if($p['image_url']): ?><a href="<?= htmlspecialchars($p['image_url']) ?>" target="_blank">View</a><?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div id="motivModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>New Motivation</h3>
            <button class="btn btn-sm btn-secondary" onclick="document.getElementById('motivModal').style.display='none'">&times;</button>
        </div>
        <form action="<?= url("/admin/motivation/store") ?>" method="POST">
            <div class="modal-body">
                <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= $_SESSION[CSRF_TOKEN_NAME] ?>">
                <div class="form-group"><label>Date</label><input type="date" name="show_date" required></div>
                <div class="form-group"><label>Quote</label><textarea name="quote_text" required></textarea></div>
                <div class="form-group"><label>Image URL</label><input type="text" name="image_url"></div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>
