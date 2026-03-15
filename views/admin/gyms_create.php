<h2>Create New Gym (Sale)</h2>
<div class="card" style="max-width: 800px;">
    <form action="<?= url("/admin/gyms/store") ?>" method="POST" id="createGymForm">
        <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= $_SESSION[CSRF_TOKEN_NAME] ?>">

        <div class="row">
            <div class="col-md-6">
                <h3>Gym Details</h3>
                <div class="form-group mb-3">
                    <label for="name">Gym Name</label>
                    <input type="text" id="name" name="name" class="form-control" required>
                </div>

                <hr>
                <h3>Gym Admin Account</h3>
                <div class="form-group mb-3">
                    <label for="admin_email">Admin Email</label>
                    <input type="email" id="admin_email" name="admin_email" class="form-control" required>
                </div>
                <div class="form-group mb-3">
                    <label for="admin_password">Admin Password</label>
                    <input type="password" id="admin_password" name="admin_password" class="form-control" required>
                </div>
            </div>

            <div class="col-md-6" style="border-left: 1px solid #eee; padding-left: 20px;">
                <h3>Subscription Plan</h3>

                <div class="form-group mb-3">
                <div class="form-group mb-3">
                    <label>Plan</label>
                    <select name="plan_id" id="plan_select" class="form-control" onchange="updatePricing()" required>
                        <option value="">Select Plan...</option>
                        <?php foreach ($plans as $p): ?>
                            <option value="<?= $p['id'] ?>"
                                    data-name="<?= $p['name'] ?>"
                                    data-price="<?= $p['current_price'] ?>"
                                    data-period="<?= $p['period_months'] ?>">
                                <?= $p['name'] ?> - $<?= number_format($p['current_price']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group mb-3" id="period_group" style="display:none;">
                    <label>Duration / Period</label>
                    <select name="period_multiplier" id="period_select" class="form-control" onchange="updatePricing()">
                        <!-- JS populated -->
                    </select>
                </div>

                <div class="form-group mb-3">
                    <label>License Start Date</label>
                    <input type="date" id="license_start" name="license_start" class="form-control" value="<?= date('Y-m-d') ?>" required onchange="calculateEndDate()">
                </div>
                <div class="form-group mb-3">
                    <label>License End Date (Calculated)</label>
                    <input type="text" id="license_end_display" class="form-control" readonly disabled>
                    <input type="hidden" name="license_end" id="license_end">
                </div>

                <!-- Discount Section -->
                <?php if ($_SESSION['user_role'] === 'SUPER_ADMIN'): ?>
                <div class="form-group mb-3" style="margin-top: 20px;">
                    <label><input type="checkbox" id="apply_discount" name="apply_discount" onclick="toggleDiscount()"> Apply Discount (Super Admin)</label>
                </div>
                <div id="discount_section" style="display:none; background: #f8f9fa; padding: 10px; border-radius: 4px;">
                    <div class="form-group mb-3">
                        <label>Discount Type</label>
                        <select name="discount_type" id="discount_type" class="form-control" onchange="updatePricing()">
                            <option value="FIXED">Fixed Amount ($)</option>
                            <option value="PERCENT">Percentage (%)</option>
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label>Value</label>
                        <input type="number" name="discount_value" id="discount_value" class="form-control" value="0" min="0" onchange="updatePricing()">
                    </div>
                    <div class="form-group mb-3">
                        <label>Reason (Required)</label>
                        <input type="text" name="discount_reason" id="discount_reason" class="form-control">
                    </div>
                </div>
                <?php endif; ?>

                <div style="margin-top: 20px; text-align: right;">
                    <small>Subtotal: <span id="subtotal_display">$0</span></small><br>
                    <small>Discount: <span id="discount_display" style="color: red;">$0</span></small>
                    <h2 style="color: var(--success); margin: 5px 0;">Total: <span id="total_display">$0</span></h2>
                    <input type="hidden" name="amount_total" id="amount_total">
                </div>
            </div>
        </div>

        <div style="margin-top: 2rem; display: flex; gap: 1rem;">
            <button type="submit" class="btn btn-primary">Register Sale & Create Gym</button>
            <a href="<?= url("/admin/gyms") ?>" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<script>
function updatePricing() {
    const planSelect = document.getElementById('plan_select');
    const periodSelect = document.getElementById('period_select');
    const discountCheck = document.getElementById('apply_discount');

    if (planSelect.selectedIndex === 0) return;

    const opt = planSelect.options[planSelect.selectedIndex];
    const unitPrice = parseFloat(opt.dataset.price);
    const planName = opt.dataset.name;
    const basePeriodMonths = parseInt(opt.dataset.period);

    // Populate Period Dropdown if empty or changed logic
    // Logic: If Annual (period=12), options: 1 Year, 2 Years...
    // If Monthly (period=1), options: 1 Month, 3 Months, 6 Months, 12 Months

    // We check if we need to repopulate
    const currentPlanType = planName.includes('Anual') ? 'ANNUAL' : 'MONTHLY';
    if (periodSelect.dataset.lastType !== currentPlanType) {
        periodSelect.innerHTML = '';
        if (currentPlanType === 'ANNUAL') {
            for(let i=1; i<=5; i++) {
                let op = document.createElement('option');
                op.value = i;
                op.text = i + (i===1 ? ' Year' : ' Years');
                periodSelect.appendChild(op);
            }
        } else {
            [1, 3, 6, 12].forEach(m => {
                let op = document.createElement('option');
                op.value = m;
                op.text = m + ' Months';
                periodSelect.appendChild(op);
            });
        }
        periodSelect.dataset.lastType = currentPlanType;
        document.getElementById('period_group').style.display = 'block';
    }

    const multiplier = parseInt(periodSelect.value) || 1;
    let subtotal = 0;

    if (currentPlanType === 'ANNUAL') {
        // Multiplier is Years. Price is per Year.
        subtotal = unitPrice * multiplier;
    } else {
        // Multiplier is Months. Price is per Month.
        subtotal = unitPrice * multiplier;
    }

    // Discount
    let discount = 0;
    if (discountCheck && discountCheck.checked) {
        const type = document.getElementById('discount_type').value;
        const val = parseFloat(document.getElementById('discount_value').value) || 0;

        if (type === 'FIXED') {
            discount = val;
        } else {
            discount = subtotal * (val / 100);
        }
    }

    const total = Math.max(0, subtotal - discount);

    // Update UI
    document.getElementById('subtotal_display').innerText = '$' + subtotal.toLocaleString();
    document.getElementById('discount_display').innerText = '-$' + discount.toLocaleString();
    document.getElementById('total_display').innerText = '$' + total.toLocaleString();
    document.getElementById('amount_total').value = total;

    calculateEndDate();
}

function calculateEndDate() {
    const startInput = document.getElementById('license_start').value;
    const planSelect = document.getElementById('plan_select');
    const periodSelect = document.getElementById('period_select');

    if (!startInput || planSelect.selectedIndex === 0) return;

    const opt = planSelect.options[planSelect.selectedIndex];
    const planName = opt.dataset.name;
    const currentPlanType = planName.includes('Anual') ? 'ANNUAL' : 'MONTHLY';
    const multiplier = parseInt(periodSelect.value) || 1;

    const date = new Date(startInput);

    if (currentPlanType === 'ANNUAL') {
        date.setFullYear(date.getFullYear() + multiplier);
    } else {
        date.setMonth(date.getMonth() + multiplier);
    }

    // ISO Date
    const dateStr = date.toISOString().split('T')[0];
    document.getElementById('license_end').value = dateStr;
    document.getElementById('license_end_display').value = dateStr;
}

function toggleDiscount() {
    const sec = document.getElementById('discount_section');
    sec.style.display = sec.style.display === 'none' ? 'block' : 'none';
    updatePricing();
}
</script>
