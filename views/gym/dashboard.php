<?php defined('APP_NAME') or exit('No direct script access allowed'); ?>
<div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <h2 style="margin: 0;">Gym Dashboard</h2>
    <div style="font-size: 0.9rem; color: var(--text-muted);"><?= date('l, F j, Y') ?></div>
</div>

<div class="grid-4">
    <div class="card">
        <div style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 0.5rem;">Total Clients</div>
        <div style="font-size: 2rem; font-weight: 700; color: var(--primary);"><?= $stats['total_clients'] ?? 0 ?></div>
    </div>
    <div class="card">
        <div style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 0.5rem;">Active Clients</div>
        <div style="font-size: 2rem; font-weight: 700; color: var(--success);"><?= $stats['active_clients'] ?? 0 ?></div>
    </div>
    <!-- Add more KPIs here if available -->
</div>
