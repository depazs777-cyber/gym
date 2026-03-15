<?php defined('APP_NAME') or exit('No direct script access allowed'); ?>
<div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <h2 style="margin: 0;">Billing & Licenses</h2>
    <div style="font-size: 0.9rem; color: var(--text-muted);"><?= date('l, F j, Y') ?></div>
</div>

<!-- Annual Plan Pricing -->
<div class="card" style="margin-bottom: 2rem; border-left: 5px solid var(--primary);">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h3 style="margin: 0;">Annual Plan Pricing</h3>
            <p style="margin: 5px 0 0; font-size: 1.1rem;">
                Current Price: <strong style="color: var(--success);">$<?= number_format($annualPlan['current_price'] ?? 0, 0) ?> COP</strong>
            </p>
        </div>

        <?php if (isset($scheduledChange) && $scheduledChange): ?>
            <div style="text-align: right;">
                <span class="badge badge-warning" style="margin-bottom: 5px; display: inline-block;">Scheduled Change</span>
                <div>
                    New Price: <strong>$<?= number_format($scheduledChange['new_price'], 0) ?></strong>
                </div>
                <div style="font-size: 0.9rem; color: var(--text-muted);">
                    Effective: <?= $scheduledChange['effective_date'] ?><br>
                    Notify: <?= $scheduledChange['notify_date'] ?>
                </div>
                <?php if ($_SESSION['user_role'] === 'SUPER_ADMIN'): ?>
                    <form action="<?= url('/admin/billing/cancel-increase') ?>" method="POST" style="margin-top: 5px;">
                        <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= $_SESSION[CSRF_TOKEN_NAME] ?>">
                        <input type="hidden" name="change_id" value="<?= $scheduledChange['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-danger">Cancel Increase</button>
                    </form>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <?php if ($_SESSION['user_role'] === 'SUPER_ADMIN'): ?>
                <button class="btn btn-primary" onclick="document.getElementById('priceModal').style.display='block'">Schedule Increase</button>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<div class="grid-3" style="margin-bottom: 2rem;">
    <div class="card">
        <div style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 0.5rem;">Active Licenses</div>
        <div style="font-size: 2rem; font-weight: 700; color: var(--success);"><?= $stats['active'] ?></div>
    </div>
    <div class="card">
        <div style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 0.5rem;">Expiring Soon (15 days)</div>
        <div style="font-size: 2rem; font-weight: 700; color: var(--warning);"><?= $stats['expiring'] ?></div>
    </div>
    <div class="card">
        <div style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 0.5rem;">Expired</div>
        <div style="font-size: 2rem; font-weight: 700; color: var(--danger);"><?= $stats['expired'] ?></div>
    </div>
</div>

<div class="card" style="padding: 0; overflow: hidden;">
    <table>
        <thead>
            <tr>
                <th>Gym Name</th>
                <th>License Start</th>
                <th>License End</th>
                <th>Status</th>
                <th>Days Remaining</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($gyms as $gym): ?>
            <tr>
                <td>
                    <div style="font-weight: 600;"><?= htmlspecialchars($gym['name']) ?></div>
                    <small style="color: var(--text-muted);">ID: <?= $gym['id'] ?></small>
                </td>
                <td><?= htmlspecialchars($gym['license_start']) ?></td>
                <td><?= htmlspecialchars($gym['license_end']) ?></td>
                <td>
                    <?php
                        $statusClass = 'badge-neutral';
                        if ($gym['status'] === 'active') $statusClass = 'badge-success';
                        elseif ($gym['status'] === 'suspended') $statusClass = 'badge-danger';
                        elseif ($gym['days_remaining'] < 0) $statusClass = 'badge-danger'; // Expired
                    ?>
                    <span class="badge <?= $statusClass ?>"><?= ucfirst($gym['status']) ?></span>
                </td>
                <td>
                    <?php if ($gym['days_remaining'] < 0): ?>
                        <span style="color: var(--danger); font-weight: 600;">Expired (<?= abs($gym['days_remaining']) ?> days ago)</span>
                    <?php elseif ($gym['days_remaining'] <= 15): ?>
                        <span style="color: var(--warning); font-weight: 600;"><?= $gym['days_remaining'] ?> days left</span>
                    <?php else: ?>
                        <span style="color: var(--success);"><?= $gym['days_remaining'] ?> days</span>
                    <?php endif; ?>
                </td>
                <td>
                    <!-- Actions for Finance -->
                    <button class="btn btn-sm btn-primary" onclick="openRenewModal(<?= $gym['id'] ?>, '<?= htmlspecialchars($gym['name']) ?>', '<?= $gym['license_end'] ?>')">Renew</button>
                    <button class="btn btn-sm btn-secondary" onclick="openHistoryModal(<?= $gym['id'] ?>, '<?= htmlspecialchars($gym['name']) ?>')">History</button>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Schedule Price Increase Modal -->
<div id="priceModal" class="modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; overflow-y: auto;">
    <div class="modal-content card" style="margin: 5% auto; width: 90%; max-width: 500px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
            <h2>Schedule Price Increase</h2>
            <span onclick="document.getElementById('priceModal').style.display='none'" style="cursor: pointer; font-size: 1.5rem;">&times;</span>
        </div>

        <form method="POST" action="<?= url('/admin/billing/schedule-increase') ?>">
            <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= $_SESSION[CSRF_TOKEN_NAME] ?>">

            <div class="alert alert-info">
                This will schedule a price increase for the <strong>Annual Plan</strong>.
                Existing contracts remain unchanged until their next renewal after the Effective Date.
                Notifications will be sent 1 month prior.
            </div>

            <div class="form-group">
                <label>New Price (COP)</label>
                <input type="number" name="new_price" class="form-control" required min="0">
            </div>

            <div class="form-group">
                <label>Effective Date</label>
                <!-- Default to next month 1st -->
                <input type="date" name="effective_date" class="form-control" required value="<?= date('Y-m-01', strtotime('+1 month')) ?>">
                <small style="color: var(--text-muted);">New price applies to renewals from this date.</small>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%;">Schedule Update</button>
        </form>
    </div>
</div>

<!-- Renew Modal -->
<div id="renewModal" class="modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; overflow-y: auto;">
    <div class="modal-content card" style="margin: 5% auto; width: 90%; max-width: 500px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
            <h2>Renew License</h2>
            <span onclick="document.getElementById('renewModal').style.display='none'" style="cursor: pointer; font-size: 1.5rem;">&times;</span>
        </div>

        <form id="renewForm" method="POST" action="<?= url('/admin/billing/renew') ?>">
            <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= $_SESSION[CSRF_TOKEN_NAME] ?>">
            <input type="hidden" name="gym_id" id="renew_gym_id">

            <p><strong>Gym:</strong> <span id="renew_gym_name"></span></p>
            <p><strong>Current Expiry:</strong> <span id="renew_current_end"></span></p>
            <p><strong>New Expiry:</strong> <span id="renew_new_end" style="color: var(--success); font-weight: bold;"></span></p>

            <div class="form-group">
                <label>Renewal Period</label>
                <select name="period_type" id="period_type" class="form-control" onchange="calculateNewDate()">
                    <option value="MONTHLY" data-months="1">Monthly (1 Month)</option>
                    <option value="QUARTERLY" data-months="3">Quarterly (3 Months)</option>
                    <option value="SEMI_ANNUAL" data-months="6">Semi-Annual (6 Months)</option>
                    <option value="ANNUAL" data-months="12" selected>Annual (12 Months)</option>
                </select>
                <input type="hidden" name="period_months" id="period_months" value="12">
            </div>

            <div class="form-group">
                <label>Amount (COP)</label>
                <input type="number" name="amount" class="form-control" placeholder="0.00" required>
            </div>

            <div class="form-group">
                <label>Payment Method</label>
                <select name="method" class="form-control" required>
                    <option value="transfer">Bank Transfer</option>
                    <option value="cash">Cash</option>
                    <option value="card">Card</option>
                </select>
            </div>

            <div class="form-group">
                <label>Reference / Transaction ID</label>
                <input type="text" name="reference" class="form-control" placeholder="e.g. TX-123456">
            </div>

            <div class="form-group">
                <label>Notes</label>
                <textarea name="notes" class="form-control" rows="2"></textarea>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%;">Confirm Renewal</button>
        </form>
    </div>
</div>

<!-- History Modal -->
<div id="historyModal" class="modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; overflow-y: auto;">
    <div class="modal-content card" style="margin: 5% auto; width: 90%; max-width: 800px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
            <h2>History: <span id="hist_gym_name"></span></h2>
            <span onclick="document.getElementById('historyModal').style.display='none'" style="cursor: pointer; font-size: 1.5rem;">&times;</span>
        </div>

        <div id="historyContent" style="max-height: 400px; overflow-y: auto;">
            Loading...
        </div>
    </div>
</div>

<script>
let currentEndDateObj;
const saasPlans = <?= json_encode($plans) ?>;

function openRenewModal(id, name, endDate) {
    document.getElementById('renewModal').style.display = 'block';
    document.getElementById('renew_gym_id').value = id;
    document.getElementById('renew_gym_name').innerText = name;
    document.getElementById('renew_current_end').innerText = endDate;

    currentEndDateObj = new Date(endDate);
    calculateNewDate();
}

function calculateNewDate() {
    const sel = document.getElementById('period_type');
    const type = sel.value;
    const months = parseInt(sel.options[sel.selectedIndex].dataset.months);
    document.getElementById('period_months').value = months;

    // Calculate Price
    let price = 0;
    if (type === 'ANNUAL') {
        const p = saasPlans.find(x => x.name === 'Anual');
        if (p) price = p.current_price;
    } else if (type === 'MONTHLY') {
        const p = saasPlans.find(x => x.name === 'Mensual');
        if (p) price = p.current_price;
    } else if (type === 'QUARTERLY') {
        const p = saasPlans.find(x => x.name === 'Mensual');
        if (p) price = p.current_price * 3;
    } else if (type === 'SEMI_ANNUAL') {
        const p = saasPlans.find(x => x.name === 'Mensual');
        if (p) price = p.current_price * 6;
    }
    document.getElementsByName('amount')[0].value = price;

    const today = new Date();
    // If expired, start from today. Else start from current end.
    let baseDate = (currentEndDateObj < today) ? today : currentEndDateObj;

    // Copy date
    const newDate = new Date(baseDate);
    // Add months
    newDate.setMonth(newDate.getMonth() + months);

    document.getElementById('renew_new_end').innerText = newDate.toISOString().split('T')[0];
}

function openHistoryModal(id, name) {
    document.getElementById('historyModal').style.display = 'block';
    document.getElementById('hist_gym_name').innerText = name;
    document.getElementById('historyContent').innerText = 'Loading...';

    fetch('<?= url('/admin/billing/history') ?>?gym_id=' + id)
        .then(res => res.json())
        .then(data => {
            let html = '<h3>Payments</h3><table class="table" style="width:100%; font-size:0.9rem;"><thead><tr><th>Date</th><th>Amount</th><th>Method</th><th>Ref</th></tr></thead><tbody>';
            if(data.payments.length === 0) html += '<tr><td colspan="4">No payments found</td></tr>';
            data.payments.forEach(p => {
                html += `<tr>
                    <td>${p.payment_date}</td>
                    <td>${p.amount}</td>
                    <td>${p.method}</td>
                    <td>${p.reference || '-'}</td>
                </tr>`;
            });
            html += '</tbody></table>';

            html += '<h3 style="margin-top:1rem;">Renewals</h3><table class="table" style="width:100%; font-size:0.9rem;"><thead><tr><th>Renewed At</th><th>Old End</th><th>New End</th><th>Notes</th></tr></thead><tbody>';
            if(data.renewals.length === 0) html += '<tr><td colspan="4">No renewals found</td></tr>';
            data.renewals.forEach(r => {
                html += `<tr>
                    <td>${r.renewed_at}</td>
                    <td>${r.old_end_date}</td>
                    <td>${r.new_end_date}</td>
                    <td>${r.notes || '-'}</td>
                </tr>`;
            });
            html += '</tbody></table>';

            document.getElementById('historyContent').innerHTML = html;
        });
}
</script>
