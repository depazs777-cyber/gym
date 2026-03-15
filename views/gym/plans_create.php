<?php defined('APP_NAME') or exit('No direct script access allowed'); ?>
<h2>Create Plan</h2>
<div class="card" style="max-width: 600px;">
    <form action="<?= url("/gym/plans/store") ?>" method="POST">
        <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= $_SESSION[CSRF_TOKEN_NAME] ?>">
        <div class="form-group">
            <label for="name">Plan Name</label>
            <input type="text" id="name" name="name" required>
        </div>
        <div class="form-group">
            <label for="type">Type</label>
            <select id="type" name="type" required onchange="toggleFields()">
                <option value="TIME">Time Based (Days)</option>
                <option value="SESSIONS">Session Based</option>
            </select>
        </div>
        <div class="form-group" id="duration_group">
            <label for="duration">Duration (Days)</label>
            <input type="number" id="duration" name="duration" value="30">
        </div>
        <div class="form-group" id="sessions_group" style="display:none;">
            <label for="sessions">Number of Sessions</label>
            <input type="number" id="sessions" name="sessions" value="0">
        </div>
        <div class="form-group">
            <label for="price">Price</label>
            <input type="number" step="0.01" id="price" name="price" required>
        </div>

        <button type="submit" class="btn">Create Plan</button>
        <a href="<?= url("/gym/plans") ?>" class="btn" style="background-color: #6c757d;">Cancel</a>
    </form>
</div>

<script>
function toggleFields() {
    const type = document.getElementById('type').value;
    if (type === 'TIME') {
        document.getElementById('duration_group').style.display = 'block';
        document.getElementById('sessions_group').style.display = 'none';
    } else {
        document.getElementById('duration_group').style.display = 'none';
        document.getElementById('sessions_group').style.display = 'block';
    }
}
</script>
