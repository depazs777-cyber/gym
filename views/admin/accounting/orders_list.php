<div class="page-header d-flex justify-content-between align-items-center">
    <h2>Sales Orders (Contracts)</h2>
</div>

<div class="card">
    <div class="card-body">
        <table class="table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Gym (Customer)</th>
                    <th>Plan</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($orders)): ?>
                    <tr><td colspan="6" class="text-center text-muted">No pending orders found.</td></tr>
                <?php else: ?>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?= $order['created_at'] ?></td>
                            <td><strong><?= htmlspecialchars($order['gym_name']) ?></strong></td>
                            <td><?= htmlspecialchars($order['plan_name'] ?? 'N/A') ?></td>
                            <td>$<?= number_format($order['total'], 2) ?></td>
                            <td>
                                <?php
                                    $badge = match($order['status']) {
                                        'PAID' => 'success',
                                        'PENDING_PAYMENT' => 'warning',
                                        'PARTIAL' => 'info',
                                        'CANCELLED' => 'danger',
                                        default => 'secondary'
                                    };
                                ?>
                                <span class="badge badge-<?= $badge ?>"><?= $order['status'] ?></span>
                            </td>
                            <td>
                                <?php if ($order['status'] !== 'PAID'): ?>
                                    <a href="<?= url('/admin/accounting/receipts/create?order_id=' . $order['id']) ?>" class="btn btn-sm btn-success">Collect Payment (RC)</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
