<?php defined('APP_NAME') or exit('No direct script access allowed'); ?>
<div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <h2 style="margin: 0;">Attendance Logs</h2>
    <a href="<?= url("/gym/attendance/checkin") ?>" class="btn btn-primary">Check-in Console</a>
</div>

<div class="card" style="padding: 0; overflow: hidden;">
    <table>
        <thead>
            <tr>
                <th>Time</th>
                <th>Client</th>
                <th>Access</th>
                <th>Reason/Details</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($logs as $log): ?>
            <tr>
                <td><?= htmlspecialchars($log['access_time']) ?></td>
                <td>
                    <div style="font-weight: 600;"><?= htmlspecialchars($log['client_name']) ?></div>
                    <small style="color: var(--text-muted);"><?= htmlspecialchars($log['identification']) ?></small>
                </td>
                <td>
                    <?php if ($log['access_granted']): ?>
                        <span class="badge badge-success">GRANTED</span>
                    <?php else: ?>
                        <span class="badge badge-danger">DENIED</span>
                    <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($log['rejection_reason'] ?? '-') ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
