<h2>Purchases</h2>
<div class="actions mb-3">
    <a href="/gym/accounting/purchases/create" class="btn btn-primary">New Purchase (FCI/DS)</a>
</div>

<div class="card">
    <div class="card-body">
        <table class="table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Type</th>
                    <th>#</th>
                    <th>Provider</th>
                    <th>Total Gross</th>
                    <th>Retentions</th>
                    <th>Net Payable</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($purchases as $p): ?>
                <?php
                    $totalRet = $p['reteiva_value'] + $p['reteica_value'] + $p['other_retentions'];
                ?>
                <tr>
                    <td><?= $p['issue_date'] ?></td>
                    <td><span class="badge badge-info"><?= $p['doc_type'] ?></span></td>
                    <td><?= $p['doc_number'] ?></td>
                    <td><?= htmlspecialchars($p['provider_name']) ?></td>
                    <td>$<?= number_format($p['total_gross'], 2) ?></td>
                    <td class="text-danger">-$<?= number_format($totalRet, 2) ?></td>
                    <td class="font-weight-bold">$<?= number_format($p['total_payable'], 2) ?></td>
                    <td>
                        <?php if($p['status'] === 'PENDING'): ?>
                            <span class="badge badge-warning">Pending</span>
                        <?php elseif($p['status'] === 'PAID'): ?>
                            <span class="badge badge-success">Paid</span>
                        <?php else: ?>
                            <span class="badge badge-secondary"><?= $p['status'] ?></span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if($p['status'] === 'PENDING'): ?>
                            <a href="/gym/accounting/expenses/create?purchase_id=<?= $p['id'] ?>" class="btn btn-sm btn-success">Pay (CE)</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
