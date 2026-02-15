<h2>Third Parties</h2>
<div class="actions" style="margin-bottom: 1rem;">
    <a href="/gym/accounting/third-parties/create" class="btn btn-primary">Create New Third Party</a>
</div>

<div class="card">
    <div class="card-body">
        <table class="table">
            <thead>
                <tr>
                    <th>Name / Company</th>
                    <th>Document</th>
                    <th>Type</th>
                    <th>Economic Activity</th>
                    <th>VAT Resp.</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($parties as $p): ?>
                <tr>
                    <td><?= htmlspecialchars($p['full_name_or_company']) ?></td>
                    <td><?= htmlspecialchars($p['document_type'] . ' ' . $p['document_number']) ?></td>
                    <td><?= $p['person_type'] ?> (<?= $p['third_type'] ?>)</td>
                    <td>
                        <?php if ($p['has_economic_activity']): ?>
                            <span class="badge badge-success">Yes</span>
                        <?php else: ?>
                            <span class="badge badge-secondary">No</span>
                        <?php endif; ?>
                    </td>
                    <td><?= $p['vat_responsible'] ?></td>
                    <td>
                        <a href="/gym/accounting/third-parties/edit?id=<?= $p['id'] ?>" class="btn btn-sm btn-info">Edit</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
