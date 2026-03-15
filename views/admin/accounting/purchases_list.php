<div class="page-header d-flex justify-content-between align-items-center">
    <h2>Purchases & Expenses (SaaS)</h2>
    <div>
        <a href="<?= url('/admin/accounting/purchases/create') ?>" class="btn btn-primary">New Purchase (FCI/DS)</a>
        <!-- <a href="<?= url('/admin/accounting/expenses/create') ?>" class="btn btn-secondary">New Expense Voucher (CE)</a> -->
    </div>
</div>

<div class="card">
    <div class="card-body">
        <table class="table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Ref / Number</th>
                    <th>Provider</th>
                    <th>Total Gross</th>
                    <th>Withholdings</th>
                    <th>Total Payable</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($purchases)): ?>
                    <tr><td colspan="9" class="text-center text-muted">No records found.</td></tr>
                <?php else: ?>
                    <?php foreach ($purchases as $p): ?>
                    <tr>
                        <td><?= htmlspecialchars($p['issue_date']) ?></td>
                        <td><span class="badge badge-info"><?= htmlspecialchars($p['doc_type']) ?></span></td>
                        <td><?= htmlspecialchars($p['doc_number']) ?></td>
                        <td>
                            <strong><?= htmlspecialchars($p['provider_name'] ?? 'Unknown') ?></strong>
                            <br><small><?= htmlspecialchars($p['provider_doc'] ?? '') ?></small>
                        </td>
                        <td>$<?= number_format($p['total_gross'], 2) ?></td>
                        <td>
                            <?php if($p['reteiva_value'] > 0): ?> <small>ReteIVA: $<?= number_format($p['reteiva_value']) ?></small><br><?php endif; ?>
                            <?php if($p['reteica_value'] > 0): ?> <small>ReteICA: $<?= number_format($p['reteica_value']) ?></small><?php endif; ?>
                        </td>
                        <td><strong>$<?= number_format($p['total_payable'], 2) ?></strong></td>
                        <td><?= htmlspecialchars($p['status']) ?></td>
                        <td>
                            <button class="btn btn-sm btn-secondary">View</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
