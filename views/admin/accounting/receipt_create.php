<div class="page-header">
    <h2>Create Cash Receipt (RC)</h2>
    <a href="<?= url('/admin/accounting/orders') ?>" class="btn btn-secondary btn-sm">Back to Orders</a>
</div>

<div class="card" style="max-width: 800px; margin: 0 auto;">
    <div class="card-body">
        <form action="<?= url('/admin/accounting/receipts/store') ?>" method="POST">
            <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= $_SESSION[CSRF_TOKEN_NAME] ?>">

            <?php if ($order): ?>
                <input type="hidden" name="sales_order_id" value="<?= $order['id'] ?>">
                <input type="hidden" name="gym_id" value="<?= $order['gym_id'] ?>">

                <div class="alert alert-info">
                    <strong>Collecting for Order #<?= $order['id'] ?></strong><br>
                    Customer: <?= htmlspecialchars($order['gym_id']) ?> (Need Gym Name logic here if strict, but ID ok for now)<br>
                    Total Due: $<?= number_format($order['total'], 2) ?>
                </div>
            <?php else: ?>
                <div class="form-group mb-3">
                    <label>Select Gym (Customer)</label>
                    <select name="gym_id" class="form-control" required>
                        <option value="">Select...</option>
                        <?php foreach ($gyms as $gym): ?>
                            <option value="<?= $gym['id'] ?>"><?= htmlspecialchars($gym['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label>Payment Method</label>
                        <select name="payment_method" class="form-control" required>
                            <option value="CASH">Cash</option>
                            <option value="TRANSFER">Bank Transfer</option>
                            <option value="CARD">Credit/Debit Card</option>
                            <option value="CHECK">Check</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label>Amount Received</label>
                        <input type="number" step="0.01" name="amount" class="form-control" value="<?= $order ? $order['total'] : '' ?>" required>
                    </div>
                </div>
            </div>

            <div class="form-group mb-3">
                <label>Reference / Transaction ID</label>
                <input type="text" name="reference" class="form-control" placeholder="e.g. 12345678">
            </div>

            <div class="form-group mb-3">
                <label>Concept / Description</label>
                <input type="text" name="concept" class="form-control" value="<?= $order ? 'Payment for Order #' . $order['id'] : '' ?>" required>
            </div>

            <div class="form-group mb-3">
                <label>Internal Notes</label>
                <textarea name="notes" class="form-control" rows="3"></textarea>
            </div>

            <button type="submit" class="btn btn-primary">Generate Receipt & Activate</button>
        </form>
    </div>
</div>
