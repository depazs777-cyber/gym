<?php defined('APP_NAME') or exit('No direct script access allowed'); ?>
<div class="row align-items-center mb-4">
    <div class="col-md-6">
        <h2 style="margin: 0;">Global Dashboard</h2>
        <div style="font-size: 0.9rem; color: var(--text-muted);"><?= date('l, F j, Y') ?></div>
    </div>
    <div class="col-md-6 text-right">
        <!-- Optional actions here -->
    </div>
</div>

<div class="row" style="display:flex; gap: 20px; flex-wrap: wrap;">
    <div class="col-md-3" style="flex: 1; min-width: 200px;">
        <div class="card">
            <div class="card-body text-center">
                <div style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 0.5rem;">Active Gyms</div>
                <div style="font-size: 2.5rem; font-weight: 700; color: var(--primary);">
                    <?= $stats['active_gyms'] ?? 0 ?> <span style="font-size:1rem; color:#999;">/ <?= $stats['total_gyms'] ?? 0 ?></span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3" style="flex: 1; min-width: 200px;">
        <div class="card">
            <div class="card-body text-center">
                <div style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 0.5rem;">Total Revenue</div>
                <div style="font-size: 2.5rem; font-weight: 700; color: var(--success);">$<?= number_format($stats['revenue'] ?? 0) ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3" style="flex: 1; min-width: 200px;">
        <div class="card">
            <div class="card-body text-center">
                <div style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 0.5rem;">Pending Renewals</div>
                <div style="font-size: 2.5rem; font-weight: 700; color: <?= ($stats['renewals'] > 0) ? '#ffc107' : 'var(--success)' ?>;"><?= $stats['renewals'] ?? 0 ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3" style="flex: 1; min-width: 200px;">
        <div class="card">
            <div class="card-body text-center">
                <div style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 0.5rem;">System Status</div>
                <div style="font-size: 1.5rem; font-weight: 600; color: var(--success); margin-top: 10px;">Operational</div>
            </div>
        </div>
    </div>
</div>
