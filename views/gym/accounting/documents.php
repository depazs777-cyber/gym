<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Accounting Documents</h1>
    <div class="btn-group">
        <a href="/gym/accounting?type=RC" class="btn btn-outline-primary">Receipts (RC)</a>
        <a href="/gym/accounting?type=CE" class="btn btn-outline-danger">Expenses (CE)</a>
        <a href="/gym/accounting?type=FC" class="btn btn-outline-success">Invoices (FC)</a>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label>Type</label>
                <select name="type" class="form-select">
                    <option value="ALL" <?php echo $type === 'ALL' ? 'selected' : ''; ?>>All Types</option>
                    <option value="RC" <?php echo $type === 'RC' ? 'selected' : ''; ?>>Recibo de Caja (RC)</option>
                    <option value="CE" <?php echo $type === 'CE' ? 'selected' : ''; ?>>Comprobante Egreso (CE)</option>
                    <option value="FC" <?php echo $type === 'FC' ? 'selected' : ''; ?>>Factura Venta (FC)</option>
                    <option value="DS" <?php echo $type === 'DS' ? 'selected' : ''; ?>>Documento Soporte (DS)</option>
                </select>
            </div>
            <div class="col-md-3">
                <label>Start Date</label>
                <input type="date" name="start_date" class="form-control" value="<?php echo $startDate; ?>">
            </div>
            <div class="col-md-3">
                <label>End Date</label>
                <input type="date" name="end_date" class="form-control" value="<?php echo $endDate; ?>">
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Number</th>
                    <th>Third Party</th>
                    <th>Description</th>
                    <th class="text-end">Total</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($documents)): ?>
                    <tr><td colspan="8" class="text-center py-4">No documents found for this period.</td></tr>
                <?php else: ?>
                    <?php foreach ($documents as $doc): ?>
                        <tr>
                            <td><?php echo $doc['issue_date']; ?></td>
                            <td>
                                <span class="badge bg-secondary"><?php echo $doc['doc_type']; ?></span>
                            </td>
                            <td><?php echo htmlspecialchars($doc['doc_number_full'] ?? $doc['id']); ?></td>
                            <td><?php echo htmlspecialchars($doc['third_party_name'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars(substr($doc['description'] ?? '', 0, 50)); ?>...</td>
                            <td class="text-end fw-bold">
                                $<?php echo number_format($doc['total_net'], 2); ?>
                            </td>
                            <td>
                                <span class="badge bg-<?php echo $doc['status'] === 'POSTED' ? 'success' : 'warning'; ?>">
                                    <?php echo $doc['status']; ?>
                                </span>
                            </td>
                            <td>
                                <a href="/gym/accounting/view/<?php echo $doc['id']; ?>" class="btn btn-sm btn-info text-white">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
