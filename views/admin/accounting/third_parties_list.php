<div class="page-header d-flex justify-content-between align-items-center">
    <h2>Third Parties (SaaS Providers & Customers)</h2>
    <a href="<?= url('/admin/accounting/third-parties/create') ?>" class="btn btn-primary">Add Third Party</a>
</div>

<div class="card">
    <div class="card-body">
        <table class="table">
            <thead>
                <tr>
                    <th>Document</th>
                    <th>Name / Reason</th>
                    <th>Type</th>
                    <th>RUT Info</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($thirdParties)): ?>
                    <tr><td colspan="6" class="text-center text-muted">No records found.</td></tr>
                <?php else: ?>
                    <?php foreach ($thirdParties as $tp): ?>
                    <tr>
                        <td><?= htmlspecialchars($tp['document_type'] . ' ' . $tp['document_number']) ?></td>
                        <td>
                            <strong><?= htmlspecialchars($tp['full_name_or_company']) ?></strong>
                            <?php if($tp['trade_name']): ?>
                                <br><small><?= htmlspecialchars($tp['trade_name']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($tp['person_type']) ?> <br> <small><?= htmlspecialchars($tp['third_type']) ?></small></td>
                        <td>
                            <?php if ($tp['vat_responsible'] === 'YES') echo '<span class="badge badge-info">IVA</span> '; ?>
                            <?php if ($tp['ica_responsible'] === 'YES') echo '<span class="badge badge-warning">ICA</span> '; ?>
                            <?php if ($tp['has_economic_activity']): ?>
                                <small>Code: <?= htmlspecialchars($tp['rut_activity_code']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td><?= $tp['status'] ?></td>
                        <td>
                            <a href="#" class="btn btn-sm btn-secondary">Edit</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
