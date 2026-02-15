<?php defined('APP_NAME') or exit('No direct script access allowed'); ?>
<div class="page-header">
    <h2>Call Logs</h2>
</div>

<div class="card">
    <table class="table" style="width: 100%;">
        <thead>
            <tr>
                <th>Date</th>
                <th>Lead</th>
                <th>Outcome</th>
                <th>Duration</th>
                <th>Script</th>
                <th>Notes</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($logs as $log): ?>
            <tr>
                <td><?= date('M d, H:i', strtotime($log['call_start'])) ?></td>
                <td>
                    <?= htmlspecialchars($log['lead_name']) ?><br>
                    <small><?= htmlspecialchars($log['phone']) ?></small>
                </td>
                <td>
                    <?php
                        $outcomes = [
                            'WON' => 'bg-success', 'INTERESTED' => 'bg-primary', 'DNC' => 'bg-danger'
                        ];
                        $bg = $outcomes[$log['outcome']] ?? 'bg-secondary';
                    ?>
                    <span class="badge <?= $bg ?>"><?= $log['outcome'] ?></span>
                </td>
                <td>
                    <?php 
                        $m = floor($log['duration_seconds'] / 60);
                        $s = $log['duration_seconds'] % 60;
                        echo sprintf('%02d:%02d', $m, $s);
                    ?>
                </td>
                <td><?= htmlspecialchars($log['script_title'] ?? '-') ?></td>
                <td><?= htmlspecialchars($log['notes']) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
