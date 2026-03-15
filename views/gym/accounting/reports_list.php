<h2>Accounting Reports: Withholding & Purchases</h2>

<form action="" method="GET" class="mb-4">
    <div class="row">
        <div class="col-md-4">
            <label>Start Date</label>
            <input type="date" name="start_date" class="form-control" value="<?= $startDate ?>">
        </div>
        <div class="col-md-4">
            <label>End Date</label>
            <input type="date" name="end_date" class="form-control" value="<?= $endDate ?>">
        </div>
        <div class="col-md-4" style="display:flex; align-items:flex-end; gap:10px;">
            <button type="submit" class="btn btn-primary">Filter</button>
            <a href="/gym/accounting/reports/export?start_date=<?= $startDate ?>&end_date=<?= $endDate ?>" class="btn btn-success" target="_blank">Export CSV</a>
        </div>
    </div>
</form>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card text-center text-white bg-info">
            <div class="card-body">
                <h5>Total ReteIVA</h5>
                <h3>$<?= number_format($totals['total_reteiva'] ?? 0, 2) ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center text-white bg-warning">
            <div class="card-body">
                <h5>Total ReteICA</h5>
                <h3>$<?= number_format($totals['total_reteica'] ?? 0, 2) ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center text-white bg-secondary">
            <div class="card-body">
                <h5>Other Retentions</h5>
                <h3>$<?= number_format($totals['total_other'] ?? 0, 2) ?></h3>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <table class="table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Doc #</th>
                    <th>Provider</th>
                    <th>Gross</th>
                    <th>ReteIVA</th>
                    <th>ReteICA</th>
                    <th>Others</th>
                    <th>Net Payable</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $r): ?>
                <tr>
                    <td><?= $r['issue_date'] ?></td>
                    <td><?= $r['doc_type'] ?> <?= $r['doc_number'] ?></td>
                    <td>
                        <?= htmlspecialchars($r['full_name_or_company']) ?>
                        <br><small><?= $r['document_number'] ?></small>
                    </td>
                    <td>$<?= number_format($r['total_gross'], 2) ?></td>
                    <td class="text-danger">-$<?= number_format($r['reteiva_value'], 2) ?></td>
                    <td class="text-danger">-$<?= number_format($r['reteica_value'], 2) ?></td>
                    <td class="text-danger">-$<?= number_format($r['other_retentions'], 2) ?></td>
                    <td class="font-weight-bold">$<?= number_format($r['total_payable'], 2) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
