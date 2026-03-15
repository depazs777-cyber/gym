<?php defined('APP_NAME') or exit('No direct script access allowed'); ?>
<div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <h2 style="margin: 0;">Manage Gyms</h2>
    <a href="<?= url("/admin/gyms/create") ?>" class="btn btn-primary">Add New Gym</a>
</div>

<div class="card">
    <table class="table">
        <thead>
            <tr>
                <th>Name</th>
                <th>License</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($gyms as $gym): ?>
            <tr>
                <td>
                    <div style="font-weight: 600;"><?= htmlspecialchars($gym['name']) ?></div>
                    <small style="color: var(--text-muted);">ID: <?= $gym['id'] ?></small>
                </td>
                <td>
                    <?= htmlspecialchars($gym['license_start']) ?>
                    <span style="color: var(--text-muted);">&rarr;</span>
                    <?= htmlspecialchars($gym['license_end']) ?>
                </td>
                <td>
                    <?php
                        $statusClass = match($gym['status']) {
                            'active' => 'badge-success',
                            'expired', 'suspended' => 'badge-danger',
                            'expiring' => 'badge-warning',
                            default => 'badge-neutral'
                        };
                    ?>
                    <span class="badge <?= $statusClass ?>"><?= ucfirst($gym['status']) ?></span>
                </td>
                <td>
                    <div style="display: flex; gap: 0.5rem;">
                        <a href="<?= url('/admin/gyms/edit') ?>?id=<?= $gym['id'] ?>" class="btn btn-sm btn-secondary">Edit</a>
                        <button class="btn btn-sm btn-primary" onclick="openCredsModal(<?= $gym['id'] ?>, '<?= htmlspecialchars($gym['name']) ?>')">Admin</button>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Create Admin Modal -->
<div id="credsModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Create Admin for <span id="gym_name_span"></span></h3>
            <button class="btn btn-sm btn-secondary" onclick="document.getElementById('credsModal').style.display='none'">&times;</button>
        </div>
        <form action="<?= url("/admin/gyms/create-admin") ?>" method="POST">
            <div class="modal-body">
                <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= $_SESSION[CSRF_TOKEN_NAME] ?>">
                <input type="hidden" name="gym_id" id="gym_id_input">

                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <div style="display: flex; gap: 10px;">
                        <input type="text" name="password" id="gen_password" class="form-control" required>
                        <button type="button" class="btn btn-secondary" onclick="generatePass()">Gen</button>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('credsModal').style.display='none'">Cancel</button>
                <button type="submit" class="btn btn-primary">Create Credentials</button>
            </div>
        </form>
    </div>
</div>

<script>
function openCredsModal(id, name) {
    document.getElementById('gym_id_input').value = id;
    document.getElementById('gym_name_span').innerText = name;
    document.getElementById('credsModal').style.display = 'flex'; // Flex for centering
}

function generatePass() {
    const chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%";
    let pass = "";
    for(let i=0; i<10; i++) {
        pass += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    document.getElementById('gen_password').value = pass;
}
</script>
