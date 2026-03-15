<?php defined('APP_NAME') or exit('No direct script access allowed'); ?>
<h2>Reports Dashboard</h2>

<div style="margin-bottom: 1rem;">
    <a href="<?= url("/gym/reports?filter=today") ?>" class="btn" style="background-color: <?= $filter === 'today' ? '#007bff' : '#6c757d' ?>">Today</a>
    <a href="<?= url("/gym/reports?filter=month") ?>" class="btn" style="background-color: <?= $filter === 'month' ? '#007bff' : '#6c757d' ?>">This Month</a>
    <a href="<?= url("/gym/reports?filter=all") ?>" class="btn" style="background-color: <?= $filter === 'all' ? '#007bff' : '#6c757d' ?>">All Time</a>
</div>

<div class="row" style="display: flex; gap: 20px;">
    <div class="card" style="flex: 1;">
        <h3>Income (<?= ucfirst($filter) ?>)</h3>
        <p style="font-size: 2rem; font-weight: bold;">$<?= number_format($income, 2) ?></p>
    </div>
    <div class="card" style="flex: 1;">
        <h3>Active Clients</h3>
        <p style="font-size: 2rem; font-weight: bold;"><?= $activeClients ?></p>
    </div>
    <div class="card" style="flex: 1;">
        <h3>Expiring Soon (7 Days)</h3>
        <p style="font-size: 2rem; font-weight: bold; color: orange;"><?= $expiring ?></p>
    </div>
</div>

<div class="card">
    <h3>Export Data</h3>
    <button class="btn" onclick="alert('Export feature coming soon!')">Download CSV</button>
</div>
