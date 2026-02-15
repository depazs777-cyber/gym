<?php defined('APP_NAME') or exit('No direct script access allowed'); ?>
<div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <h2 style="margin: 0;">New Membership</h2>
    <a href="<?= url("/gym/clients") ?>" class="btn btn-secondary">Back to Clients</a>
</div>

<div class="card" style="max-width: 900px; margin: 0 auto;">
    <h3 class="card-title">For: <?= htmlspecialchars($client['name']) ?></h3>
    
    <form action="<?= url("/gym/memberships/store") ?>" method="POST" id="saleForm">
        <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= $_SESSION[CSRF_TOKEN_NAME] ?>">
        <input type="hidden" name="client_id" value="<?= $client['id'] ?>">

        <!-- Config Data for JS -->
        <input type="hidden" id="cfg_annual_days" value="<?= $gymConfig['config_annual_days'] ?? 360 ?>">
        <input type="hidden" id="cfg_renewal_mode" value="<?= $gymConfig['config_renewal_mode'] ?? 'CONTINUE' ?>">
        
        <!-- Last Membership Data -->
        <div class="alert alert-neutral" style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <?php if ($lastMembership): ?>
                    <strong>Last Membership:</strong> Ends: <span id="last_end_date"><?= $lastMembership['end_date'] ?></span> 
                    (Status: <?= ucfirst($lastMembership['status']) ?>)
                <?php else: ?>
                    <strong>Status:</strong> New Client (No previous history)
                    <span id="last_end_date" style="display:none;"></span>
                <?php endif; ?>
            </div>
        </div>

        <div class="grid-2">
            <div>
                <div class="form-group">
                    <label for="plan_id">Select Plan</label>
                    <select id="plan_id" name="plan_id" required onchange="calculate()">
                        <option value="">-- Select a Plan --</option>
                        <?php foreach ($plans as $plan): ?>
                        <option value="<?= $plan['id'] ?>" 
                                data-price="<?= $plan['price'] ?>"
                                data-type="<?= $plan['type'] ?>"
                                data-days="<?= $plan['duration_days'] ?>"
                                data-sessions="<?= $plan['sessions_count'] ?>">
                            <?= htmlspecialchars($plan['name']) ?> - $<?= number_format($plan['price'], 2) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Purchase Mode</label>
                    <div style="display: flex; gap: 1.5rem;">
                        <label style="display: flex; align-items: center; font-weight: 400;">
                            <input type="radio" name="purchase_mode" value="PERIODIC" checked onchange="setMode('PERIODIC')" style="width: auto; margin-right: 0.5rem;"> 
                            Periodic (Months/Days)
                        </label>
                        <label style="display: flex; align-items: center; font-weight: 400;">
                            <input type="radio" name="purchase_mode" value="ANNUAL" onchange="setMode('ANNUAL')" style="width: auto; margin-right: 0.5rem;"> 
                            Annual
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label for="multiplier">Quantity (Periods)</label>
                    <input type="number" id="multiplier" name="multiplier" value="1" min="1" onchange="calculate()">
                </div>

                <div class="form-group">
                    <label for="start_date">Start Date</label>
                    <input type="date" id="start_date" name="start_date" value="<?= date('Y-m-d') ?>" required onchange="calculate()">
                    <div style="margin-top: 0.5rem; font-size: 0.85rem;">
                        <a href="#" onclick="setStartDate('TODAY'); return false;">Today</a>
                        <span id="btnContinue" style="display:none;"> | <a href="#" onclick="setStartDate('CONTINUE'); return false;">Continue from Last</a></span>
                    </div>
                </div>
            </div>

            <div style="background: var(--bg-body); padding: 1.5rem; border-radius: var(--radius-md); border: 1px solid var(--border-color);">
                <h4 style="margin-top: 0;">Summary</h4>
                <p><strong>Plan Type:</strong> <span id="disp_type">-</span></p>
                <p><strong>New Validity:</strong> <br> 
                   <span id="disp_start" style="font-weight: 600; color: var(--text-main);">-</span> to <span id="disp_end" style="font-weight: 600; color: var(--text-main);">-</span>
                </p>
                <p><strong>Total Days/Sessions:</strong> <span id="disp_total_units">-</span></p>
                <hr style="border-top: 1px solid var(--border-color); margin: 1rem 0;">
                
                <div class="form-group">
                    <label>Discount ($)</label>
                    <input type="number" id="discount" name="discount" value="0" step="0.01" onchange="calculate()">
                </div>

                <div style="font-size: 2rem; font-weight: 700; text-align: right; color: var(--primary); margin-top: 1rem;">
                    $<span id="disp_price">0.00</span>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label for="payment_method">Payment Method</label>
            <select id="payment_method" name="payment_method">
                <option value="Cash">Cash</option>
                <option value="Card">Card</option>
                <option value="Transfer">Transfer</option>
            </select>
        </div>

        <div class="form-group">
            <label for="notes">Notes</label>
            <textarea id="notes" name="notes" rows="2"></textarea>
        </div>

        <div style="text-align: right; margin-top: 2rem; display: flex; gap: 1rem; justify-content: flex-end;">
            <a href="<?= url("/gym/clients") ?>" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">Process Payment</button>
        </div>
    </form>
</div>

<script>
// Keep existing JS logic
const todayStr = new Date().toISOString().split('T')[0];
const lastEndStr = document.getElementById('last_end_date').innerText.trim();
const cfgRenewal = document.getElementById('cfg_renewal_mode').value;
const cfgAnnualDays = parseInt(document.getElementById('cfg_annual_days').value) || 360;

if (lastEndStr && new Date(lastEndStr) >= new Date(todayStr)) {
    document.getElementById('btnContinue').style.display = 'inline';
}

function setStartDate(mode) {
    const startInput = document.getElementById('start_date');
    if (mode === 'TODAY') {
        startInput.value = todayStr;
    } else if (mode === 'CONTINUE' && lastEndStr) {
        const last = new Date(lastEndStr);
        last.setDate(last.getDate() + 1); 
        startInput.value = last.toISOString().split('T')[0];
    }
    calculate();
}

function setMode(mode) {
    if (mode === 'ANNUAL') {
        document.getElementById('multiplier').value = 12;
    } else {
        document.getElementById('multiplier').value = 1;
    }
    calculate();
}

function calculate() {
    const planSelect = document.getElementById('plan_id');
    const option = planSelect.options[planSelect.selectedIndex];
    
    if (!option.value) return;

    const priceBase = parseFloat(option.getAttribute('data-price')) || 0;
    const type = option.getAttribute('data-type');
    const daysBase = parseInt(option.getAttribute('data-days')) || 0;
    const sessionsBase = parseInt(option.getAttribute('data-sessions')) || 0;

    const mode = document.querySelector('input[name="purchase_mode"]:checked').value;
    const multiplier = parseInt(document.getElementById('multiplier').value) || 1;
    const discount = parseFloat(document.getElementById('discount').value) || 0;
    const startVal = document.getElementById('start_date').value;

    let totalPrice = 0;
    let totalUnits = 0; 
    let endDate = '';

    totalPrice = (priceBase * multiplier) - discount;
    if (totalPrice < 0) totalPrice = 0;

    if (type === 'TIME') {
        let daysToAdd = 0;
        if (mode === 'ANNUAL') {
            const ratio = multiplier / 12; 
            daysToAdd = Math.round(ratio * cfgAnnualDays);
        } else {
            daysToAdd = daysBase * multiplier;
        }
        
        totalUnits = daysToAdd + " Days";
        
        if (startVal) {
            const start = new Date(startVal);
            start.setDate(start.getDate() + daysToAdd);
            endDate = start.toISOString().split('T')[0];
        }

    } else {
        totalUnits = (sessionsBase * multiplier) + " Sessions";
        if (startVal) {
            const start = new Date(startVal);
            start.setDate(start.getDate() + 365);
            endDate = start.toISOString().split('T')[0];
        }
    }

    document.getElementById('disp_type').innerText = type;
    document.getElementById('disp_price').innerText = totalPrice.toFixed(2);
    document.getElementById('disp_start').innerText = startVal;
    document.getElementById('disp_end').innerText = endDate;
    document.getElementById('disp_total_units').innerText = totalUnits;
}
</script>
