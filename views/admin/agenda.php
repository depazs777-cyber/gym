<?php defined('APP_NAME') or exit('No direct script access allowed'); ?>
<div class="page-header">
    <h2>My Agenda</h2>
</div>

<div class="card">
    <?php if (empty($agenda)): ?>
        <p>No pending calls scheduled for today (or past due).</p>
    <?php else: ?>
        <table class="table" style="width: 100%;">
            <thead>
                <tr>
                    <th>Time</th>
                    <th>Lead</th>
                    <th>Phone</th>
                    <th>Gym</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($agenda as $item): ?>
                <tr>
                    <td>
                         <?php 
                            $isOverdue = strtotime($item['next_followup']) < time();
                            $style = $isOverdue ? 'color: var(--danger); font-weight: bold;' : 'color: var(--primary); font-weight: bold;';
                        ?>
                        <span style="<?= $style ?>"><?= date('M d, H:i', strtotime($item['next_followup'])) ?></span>
                    </td>
                    <td><?= htmlspecialchars($item['name']) ?></td>
                    <td><?= htmlspecialchars($item['phone']) ?></td>
                    <td><?= htmlspecialchars($item['gym_name'] ?? '-') ?></td>
                    <td>
                        <a href="<?= url('/admin/leads') ?>?search=<?= urlencode($item['phone']) ?>" class="btn btn-sm btn-primary">Go to Lead</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
