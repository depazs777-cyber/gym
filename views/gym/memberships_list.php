<?php defined('APP_NAME') or exit('No direct script access allowed'); ?>
<h2>Memberships</h2>
<div style="margin-bottom: 1rem;">
    <a href="<?= url("/gym/memberships?status=active") ?>" class="btn" style="background-color: <?= $currentTab === 'active' ? '#007bff' : '#6c757d' ?>">Active</a>
    <a href="<?= url("/gym/memberships?status=expiring") ?>" class="btn" style="background-color: <?= $currentTab === 'expiring' ? '#ffc107' : '#6c757d' ?>">Expiring Soon</a>
    <a href="<?= url("/gym/memberships?status=expired") ?>" class="btn" style="background-color: <?= $currentTab === 'expired' ? '#dc3545' : '#6c757d' ?>">Expired</a>
</div>

<div class="card">
    <table>
        <thead>
            <tr>
                <th>Client</th>
                <th>Plan</th>
                <th>Start</th>
                <th>End</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($memberships as $m): ?>
            <tr>
                <td><?= htmlspecialchars($m['client_name']) ?></td>
                <td><?= htmlspecialchars($m['plan_name']) ?></td>
                <td><?= htmlspecialchars($m['start_date']) ?></td>
                <td><?= htmlspecialchars($m['end_date'] ?? 'N/A') ?></td>
                <td><?= htmlspecialchars($m['status']) ?></td>
                <td>
                    <a href="<?= url("/gym/memberships/create?client_id=" . $m['client_id']) ?>" class="btn" style="background-color: #28a745; padding: 0.25rem 0.5rem;">Renew</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
