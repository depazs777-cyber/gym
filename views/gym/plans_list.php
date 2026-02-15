<?php defined('APP_NAME') or exit('No direct script access allowed'); ?>
<h2>Plans</h2>
<a href="<?= url("/gym/plans/create") ?>" class="btn" style="margin-bottom: 1rem;">Create New Plan</a>

<div class="card">
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Type</th>
                <th>Duration (Days)</th>
                <th>Sessions</th>
                <th>Price</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($plans as $plan): ?>
            <tr>
                <td><?= htmlspecialchars($plan['name']) ?></td>
                <td><?= htmlspecialchars($plan['type']) ?></td>
                <td><?= htmlspecialchars($plan['duration_days']) ?></td>
                <td><?= htmlspecialchars($plan['sessions_count']) ?></td>
                <td><?= htmlspecialchars(number_format($plan['price'], 2)) ?></td>
                <td><?= htmlspecialchars($plan['status']) ?></td>
                <td>
                    <a href="<?= url("/gym/plans/edit?id=" . $plan['id']) ?>" class="btn" style="background-color: #6c757d;">Edit</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
