<?php defined('APP_NAME') or exit('No direct script access allowed'); ?>
<h2>Receipts & Payments</h2>

<div class="card">
    <form method="GET" action="<?= url("/gym/payments") ?>" style="display: flex; gap: 10px; margin-bottom: 1rem;">
        <input type="date" name="from" value="<?= htmlspecialchars($_GET['from'] ?? '') ?>">
        <input type="date" name="to" value="<?= htmlspecialchars($_GET['to'] ?? '') ?>">
        <button type="submit" class="btn">Filter</button>
    </form>

    <table>
        <thead>
            <tr>
                <th>Receipt #</th>
                <th>Date</th>
                <th>Client</th>
                <th>Amount</th>
                <th>Method</th>
                <th>Cashier</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($payments as $pay): ?>
            <tr>
                <td><?= htmlspecialchars(str_pad($pay['consecutive_number'], 6, '0', STR_PAD_LEFT)) ?></td>
                <td><?= htmlspecialchars($pay['payment_date']) ?></td>
                <td><?= htmlspecialchars($pay['client_name']) ?></td>
                <td>$<?= number_format($pay['amount'], 2) ?></td>
                <td><?= htmlspecialchars($pay['payment_method']) ?></td>
                <td><?= htmlspecialchars($pay['cashier_name']) ?></td>
                <td>
                    <a href="<?= url("/gym/payments/receipt?id=" . $pay['id']) ?>" class="btn" style="background-color: #17a2b8; padding: 0.25rem 0.5rem;">View</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
