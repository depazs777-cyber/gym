<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Document Detail: <?php echo htmlspecialchars($doc['doc_type'] . ' - ' . ($doc['doc_number_full'] ?? $doc['id'])); ?></h1>
    <a href="/gym/accounting" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left"></i> Back
    </a>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header bg-light d-flex justify-content-between">
                <strong>Items / Concepts</strong>
                <span><?php echo $doc['issue_date']; ?></span>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped mb-0">
                    <thead>
                        <tr>
                            <th>Concept</th>
                            <th class="text-end">Qty</th>
                            <th class="text-end">Price</th>
                            <th class="text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($lines as $line): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($line['concept']); ?></td>
                                <td class="text-end"><?php echo number_format($line['quantity'], 2); ?></td>
                                <td class="text-end"><?php echo number_format($line['unit_price'], 2); ?></td>
                                <td class="text-end"><?php echo number_format($line['line_total'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <td colspan="3" class="text-end fw-bold">Total Net:</td>
                            <td class="text-end fw-bold">$<?php echo number_format($doc['total_net'], 2); ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <h6>Notes / Description</h6>
                <p><?php echo nl2br(htmlspecialchars($doc['description'] ?? '')); ?></p>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card mb-3">
            <div class="card-header">Third Party Info</div>
            <div class="card-body">
                <h5><?php echo htmlspecialchars($doc['third_party_name'] ?? 'Unknown'); ?></h5>
                <p class="mb-1">
                    <strong>ID:</strong> <?php echo htmlspecialchars($doc['tp_doc_type'] . ' ' . $doc['doc_number']); ?>
                </p>
                <?php if (!empty($doc['address'])): ?>
                    <p class="mb-1"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($doc['address']); ?></p>
                <?php endif; ?>
                <?php if (!empty($doc['phone'])): ?>
                    <p class="mb-1"><i class="fas fa-phone"></i> <?php echo htmlspecialchars($doc['phone']); ?></p>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">Metadata</div>
            <div class="card-body">
                <p><strong>Status:</strong> <?php echo $doc['status']; ?></p>
                <p><strong>Created At:</strong> <?php echo $doc['created_at']; ?></p>
                <p><strong>Created By ID:</strong> <?php echo $doc['created_by_user_id']; ?></p>
            </div>
        </div>
    </div>
</div>
