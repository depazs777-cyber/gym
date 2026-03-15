<h2>Withholding Rules Configuration</h2>
<div class="alert alert-info">
    Global and Gym-specific tax rules. These rules determine how ReteIVA and ReteICA are calculated on purchases.
</div>

<div class="card">
    <div class="card-body">
        <table class="table">
            <thead>
                <tr>
                    <th>Tax</th>
                    <th>Scope</th>
                    <th>Base Field</th>
                    <th>Rate</th>
                    <th>Min Base</th>
                    <th>Applies To</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rules as $r): ?>
                <tr>
                    <td><?= $r['tax_type'] ?></td>
                    <td><?= $r['gym_id'] ? 'Gym Specific' : 'Global' ?></td>
                    <td><?= $r['base_field'] ?></td>
                    <td>
                        <?= $r['rate'] ?>
                        <?= $r['rate_unit'] == 'PERCENT' ? '%' : '‰' ?>
                    </td>
                    <td>$<?= number_format($r['min_base_amount'], 0) ?></td>
                    <td><?= $r['applies_to'] ?></td>
                    <td><?= $r['is_active'] ? 'Active' : 'Inactive' ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <!-- Creating rules is restricted to DB seeding or Super Admin for now as per plan simplicity -->
        <!-- Logic exists in DB migration to seed defaults -->
    </div>
</div>
