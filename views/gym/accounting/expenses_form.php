<h2>Create Expense Voucher (Comprobante Egreso)</h2>
<div class="card">
    <div class="card-header">
        Paying Purchase: <?= $purchase['doc_type'] ?> #<?= $purchase['doc_number'] ?> 
        (<?= htmlspecialchars($purchase['provider_name']) ?>)
    </div>
    <div class="card-body">
        <form action="/gym/accounting/expenses/store" method="POST">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <input type="hidden" name="purchase_id" value="<?= $purchase['id'] ?>">

            <div class="row">
                <div class="col-md-6">
                    <div class="alert alert-warning">
                        <strong>Net Payable Amount:</strong> 
                        $<?= number_format($purchase['total_payable'], 2) ?>
                    </div>
                </div>
                <div class="col-md-6">
                     <label>Consecutive #</label>
                     <input type="number" name="consecutive_number" class="form-control" value="<?= $consecutive ?>" required>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <label>Amount to Pay</label>
                    <input type="number" step="0.01" name="amount" class="form-control" value="<?= $purchase['total_payable'] ?>" required>
                    <small>Currently defaulting to full payment.</small>
                </div>
                <div class="col-md-6">
                    <label>Payment Date</label>
                    <input type="date" name="payment_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-md-6">
                    <label>Method</label>
                    <select name="payment_method" class="form-control">
                        <option value="CASH">Cash</option>
                        <option value="TRANSFER">Transfer</option>
                        <option value="CHECK">Check</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label>Notes</label>
                    <textarea name="notes" class="form-control"></textarea>
                </div>
            </div>

            <button type="submit" class="btn btn-success mt-4">Generate Voucher</button>
            <a href="/gym/accounting/purchases" class="btn btn-secondary mt-4">Cancel</a>
        </form>
    </div>
</div>
