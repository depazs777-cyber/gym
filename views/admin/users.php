<?php defined('APP_NAME') or exit('No direct script access allowed'); ?>
<h2>Internal SaaS Users</h2>
<button class="btn" onclick="openModal('createModal')">Add New User</button>

<div class="card">
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
            <tr>
                <td><?= htmlspecialchars($user['name']) ?></td>
                <td><?= htmlspecialchars($user['email']) ?></td>
                <td><span class="badge"><?= htmlspecialchars($user['role']) ?></span></td>
                <td>
                    <?php if ($user['status'] === 'active'): ?>
                        <span style="color: green; font-weight: bold;">Active</span>
                    <?php else: ?>
                        <span style="color: red; font-weight: bold;">Inactive</span>
                    <?php endif; ?>
                </td>
                <td>
                    <button class="btn" style="background-color: #6c757d; padding: 0.25rem 0.5rem;" onclick='openEditModal(<?= json_encode($user) ?>)'>Edit</button>

                    <form action="<?= url("/admin/users/toggle") ?>" method="POST" style="display:inline;">
                        <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= $_SESSION[CSRF_TOKEN_NAME] ?>">
                        <input type="hidden" name="id" value="<?= $user['id'] ?>">
                        <?php if ($user['status'] === 'active'): ?>
                            <button type="submit" class="btn" style="background-color: #dc3545; padding: 0.25rem 0.5rem;" onclick="return confirm('Disable this user?')">Disable</button>
                        <?php else: ?>
                            <button type="submit" class="btn" style="background-color: #28a745; padding: 0.25rem 0.5rem;">Enable</button>
                        <?php endif; ?>
                    </form>

                    <button class="btn" style="background-color: #ffc107; color: black; padding: 0.25rem 0.5rem;" onclick="openResetModal(<?= $user['id'] ?>)">Reset Pass</button>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Create Modal -->
<div id="createModal" class="modal" style="display:none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5);">
    <div class="card" style="margin: 10% auto; width: 400px; padding: 2rem;">
        <h3>Create Internal User</h3>
        <form action="<?= url("/admin/users/create") ?>" method="POST">
            <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= $_SESSION[CSRF_TOKEN_NAME] ?>">

            <div class="form-group">
                <label>Name</label>
                <input type="text" name="name" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required>
            </div>
            <div class="form-group">
                <label>Role</label>
                <select name="role" required>
                    <option value="SUPER_ADMIN">SUPER_ADMIN</option>
                    <option value="VENDEDOR">VENDEDOR</option>
                    <option value="MARKETING">MARKETING</option>
                    <option value="CALL_CENTER">CALL_CENTER</option>
                    <option value="FINANZAS">FINANZAS</option>
                    <option value="SOPORTE">SOPORTE</option>
                    <option value="DEV">DEV</option>
                    <option value="SEGURIDAD">SEGURIDAD</option>
                </select>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>

            <button type="submit" class="btn">Create</button>
            <button type="button" class="btn" style="background-color: #6c757d;" onclick="closeModal('createModal')">Cancel</button>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="modal" style="display:none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5);">
    <div class="card" style="margin: 10% auto; width: 400px; padding: 2rem;">
        <h3>Edit User</h3>
        <form action="<?= url("/admin/users/update") ?>" method="POST">
            <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= $_SESSION[CSRF_TOKEN_NAME] ?>">
            <input type="hidden" name="id" id="edit_id">

            <div class="form-group">
                <label>Name</label>
                <input type="text" name="name" id="edit_name" required>
            </div>
            <div class="form-group">
                <label>Role</label>
                <select name="role" id="edit_role" required>
                    <option value="SUPER_ADMIN">SUPER_ADMIN</option>
                    <option value="VENDEDOR">VENDEDOR</option>
                    <option value="MARKETING">MARKETING</option>
                    <option value="CALL_CENTER">CALL_CENTER</option>
                    <option value="FINANZAS">FINANZAS</option>
                    <option value="SOPORTE">SOPORTE</option>
                    <option value="DEV">DEV</option>
                    <option value="SEGURIDAD">SEGURIDAD</option>
                </select>
            </div>

            <button type="submit" class="btn">Update</button>
            <button type="button" class="btn" style="background-color: #6c757d;" onclick="closeModal('editModal')">Cancel</button>
        </form>
    </div>
</div>

<!-- Reset Pass Modal -->
<div id="resetModal" class="modal" style="display:none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5);">
    <div class="card" style="margin: 10% auto; width: 400px; padding: 2rem;">
        <h3>Reset Password</h3>
        <form action="<?= url("/admin/users/reset-pass") ?>" method="POST">
            <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= $_SESSION[CSRF_TOKEN_NAME] ?>">
            <input type="hidden" name="id" id="reset_id">

            <div class="form-group">
                <label>New Password</label>
                <input type="password" name="password" required>
            </div>

            <button type="submit" class="btn">Reset</button>
            <button type="button" class="btn" style="background-color: #6c757d;" onclick="closeModal('resetModal')">Cancel</button>
        </form>
    </div>
</div>

<script>
function openModal(id) { document.getElementById(id).style.display = 'block'; }
function closeModal(id) { document.getElementById(id).style.display = 'none'; }

function openEditModal(user) {
    document.getElementById('edit_id').value = user.id;
    document.getElementById('edit_name').value = user.name;
    document.getElementById('edit_role').value = user.role;
    openModal('editModal');
}

function openResetModal(id) {
    document.getElementById('reset_id').value = id;
    openModal('resetModal');
}
</script>
