<?php defined('APP_NAME') or exit('No direct script access allowed'); ?>
<div class="page-header" style="display: flex; justify-content: space-between; align-items: center;">
    <h2>Finance Reports</h2>
    <div style="font-size: 0.9rem; color: var(--text-muted);"><?= date('l, F j, Y') ?></div>
</div>

<!-- KPIs -->
<div class="grid-3" style="margin-bottom: 2rem;">
    <div class="card">
        <div style="color: var(--text-muted); font-size: 0.9rem;">Income Today</div>
        <div style="font-size: 2rem; font-weight: 700; color: var(--success);">$<?= number_format($stats['income_today'], 2) ?></div>
    </div>
    <div class="card">
        <div style="color: var(--text-muted); font-size: 0.9rem;">Income Month</div>
        <div style="font-size: 2rem; font-weight: 700; color: var(--primary);">$<?= number_format($stats['income_month'], 2) ?></div>
    </div>
    <div class="card">
        <div style="color: var(--text-muted); font-size: 0.9rem;">Income Year</div>
        <div style="font-size: 2rem; font-weight: 700; color: var(--primary);">$<?= number_format($stats['income_year'], 2) ?></div>
    </div>
</div>

<div class="grid-3" style="margin-bottom: 2rem;">
    <div class="card">
        <div style="color: var(--text-muted); font-size: 0.9rem;">Renewals (Month)</div>
        <div style="font-size: 2rem; font-weight: 700; color: var(--primary);"><?= $stats['renewals_month'] ?></div>
    </div>
    <div class="card">
        <div style="color: var(--text-muted); font-size: 0.9rem;">Expiring (15 days)</div>
        <div style="font-size: 2rem; font-weight: 700; color: var(--warning);"><?= $stats['expiring'] ?></div>
    </div>
    <div class="card">
        <div style="color: var(--text-muted); font-size: 0.9rem;">Expired</div>
        <div style="font-size: 2rem; font-weight: 700; color: var(--danger);"><?= $stats['expired'] ?></div>
    </div>
</div>

<!-- Filters & Actions -->
<div class="card" style="margin-bottom: 1rem;">
    <form method="GET" action="<?= url('/admin/reports-finance') ?>" style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: flex-end;">
        <div>
            <label>Start Date</label>
            <input type="date" name="start_date" class="form-control" value="<?= $filters['start'] ?>">
        </div>
        <div>
            <label>End Date</label>
            <input type="date" name="end_date" class="form-control" value="<?= $filters['end'] ?>">
        </div>
        <button type="submit" class="btn btn-primary">Filter</button>
        
        <a href="<?= url('/admin/reports-finance/export') ?>?start_date=<?= $filters['start'] ?>&end_date=<?= $filters['end'] ?>" class="btn btn-secondary" target="_blank">Export CSV</a>
    </form>
</div>

<!-- Payments Table -->
<div class="card" style="padding: 0; overflow-x: auto;">
    <table class="table" style="width: 100%;">
        <thead>
            <tr>
                <th>Date</th>
                <th>Gym</th>
                <th>Amount</th>
                <th>Period</th>
                <th>Method</th>
                <th>Reference</th>
                <th>User</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($payments)): ?>
            <tr><td colspan="7" style="text-align: center; padding: 2rem;">No records found.</td></tr>
            <?php else: ?>
                <?php foreach ($payments as $p): ?>
                <tr>
                    <td><?= date('Y-m-d H:i', strtotime($p['payment_date'])) ?></td>
                    <td><?= htmlspecialchars($p['gym_name']) ?></td>
                    <td>$<?= number_format($p['amount'], 2) ?></td>
                    <td><?= htmlspecialchars($p['period_months'] ?? 1) ?> Months</td>
                    <td>-</td>
                    <td><?= htmlspecialchars($p['reference'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($p['user_name'] ?? 'System') ?></td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
