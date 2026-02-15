<?php defined('APP_NAME') or exit('No direct script access allowed'); ?>
<div class="card" style="max-width: 400px; margin: auto; padding: 2rem; border: 1px solid #ccc; font-family: monospace;">
    <div style="text-align: center;">
        <?php if (!empty($receipt['branding_logo'])): ?>
            <img src="<?= url($receipt['branding_logo']) ?>" alt="Logo" style="max-width: 150px; margin-bottom: 10px;">
        <?php endif; ?>
        
        <h3 style="margin: 0;"><?= htmlspecialchars($receipt['gym_name']) ?></h3>
        
        <?php 
            $details = $receipt['contact_details'];
            if (!empty($details['nit'])) echo '<div>NIT: ' . htmlspecialchars($details['nit']) . '</div>';
            if (!empty($details['address'])) echo '<div>' . htmlspecialchars($details['address']) . '</div>';
            if (!empty($details['city'])) echo '<div>' . htmlspecialchars($details['city']) . '</div>';
            if (!empty($details['phone'])) echo '<div>Tel: ' . htmlspecialchars($details['phone']) . '</div>';
            if (!empty($details['email'])) echo '<div>Email: ' . htmlspecialchars($details['email']) . '</div>';
        ?>
        
        <hr style="border-top: 1px dashed #333;">
        <h4>RECEIPT #<?= htmlspecialchars(str_pad($receipt['consecutive_number'], 6, '0', STR_PAD_LEFT)) ?></h4>
    </div>
    
    <p><strong>Date:</strong> <?= htmlspecialchars($receipt['payment_date']) ?></p>
    <p><strong>Client:</strong> <?= htmlspecialchars($receipt['client_name']) ?></p>
    <p><strong>ID:</strong> <?= htmlspecialchars($receipt['client_id_num']) ?></p>
    
    <hr style="border-top: 1px dashed #333;">
    
    <p><strong>Plan:</strong> <?= htmlspecialchars($receipt['plan_name']) ?></p>
    <?php if ($receipt['start_date']): ?>
        <p><small>Valid: <?= $receipt['start_date'] ?> to <?= $receipt['end_date'] ?? 'Sessions' ?></small></p>
    <?php endif; ?>
    
    <div style="display: flex; justify-content: space-between; font-size: 1.2rem; font-weight: bold; margin-top: 1rem;">
        <span>Total:</span>
        <span>$<?= number_format($receipt['amount'], 2) ?></span>
    </div>
    
    <p><strong>Method:</strong> <?= htmlspecialchars($receipt['payment_method']) ?></p>
    <p><small>Cashier: <?= htmlspecialchars($receipt['cashier_name']) ?></small></p>
    
    <?php if (!empty($details['message'])): ?>
        <hr style="border-top: 1px dashed #333;">
        <div style="text-align: center; margin-top: 10px;">
            <small><?= nl2br(htmlspecialchars($details['message'])) ?></small>
        </div>
    <?php endif; ?>
    
    <hr>
    <div style="text-align: center;" class="no-print">
        <button class="btn" onclick="window.print()">Print Receipt</button>
        <a href="<?= url("/gym/dashboard") ?>" class="btn" style="background-color: #6c757d;">Back</a>
    </div>
</div>

<style>
@media print {
    .no-print, .sidebar, .header {
        display: none !important;
    }
    body, .main-content, .content {
        margin: 0;
        padding: 0;
        background: white;
    }
    .card {
        box-shadow: none;
        border: none;
    }
}
</style>
