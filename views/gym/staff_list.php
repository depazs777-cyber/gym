<?php defined('APP_NAME') or exit('No direct script access allowed'); ?>
<h2>Gym Staff</h2>
<button class="btn" onclick="document.getElementById('addStaffModal').style.display='block'">Add Staff</button>

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
            <?php foreach ($users as $u): ?>
            <tr>
                <td><?= htmlspecialchars($u['name']) ?></td>
                <td><?= htmlspecialchars($u['email']) ?></td>
                <td><?= htmlspecialchars($u['role']) ?></td>
                <td><?= htmlspecialchars($u['status']) ?></td>
                <td>
                    <form action="<?= url("/gym/staff/toggle") ?>" method="POST" style="display:inline;">
                        <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= $_SESSION[CSRF_TOKEN_NAME] ?>">
                        <input type="hidden" name="id" value="<?= $u['id'] ?>">
                        <?php if ($u['status'] === 'active'): ?>
                            <button class="btn" style="background-color: #dc3545; padding: 0.25rem 0.5rem;" onclick="return confirm('Disable?')">Disable</button>
                        <?php else: ?>
                            <button class="btn" style="background-color: #28a745; padding: 0.25rem 0.5rem;">Enable</button>
                        <?php endif; ?>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div id="addStaffModal" class="modal" style="display:none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5);">
    <div class="card" style="margin: 10% auto; width: 400px; padding: 2rem;">
        <h3>Add Staff</h3>
        <form action="<?= url("/gym/staff/store") ?>" method="POST">
            <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= $_SESSION[CSRF_TOKEN_NAME] ?>">
            <div class="form-group"><label>Name</label><input type="text" name="name" required></div>
            <div class="form-group"><label>Email</label><input type="email" name="email" required></div>
            <div class="form-group"><label>Password</label><input type="password" name="password" required></div>
            <div class="form-group">
                <label>Role</label>
                <select name="role">
                    <option value="RECEPCION">Reception</option>
                    <option value="ENTRENADOR">Trainer</option>
                    <option value="CONSULTA_REPORTES">Reporter</option>
                </select>
            </div>
            <button type="submit" class="btn">Create</button>
            <button type="button" class="btn" style="background-color: #6c757d;" onclick="document.getElementById('addStaffModal').style.display='none'">Cancel</button>
        </form>
    </div>
</div>
