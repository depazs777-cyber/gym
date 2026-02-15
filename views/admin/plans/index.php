
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>SaaS Plans Management</h1>
    <?php if ($canManage): ?>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createPlanModal">
            <i class="fas fa-plus"></i> New Plan
        </button>
    <?php endif; ?>
</div>

<!-- Plans Grid -->
<div class="row">
    <?php foreach ($plans as $plan): ?>
        <div class="col-md-4 mb-4">
            <div class="card h-100 <?php echo $plan['is_archived'] ? 'border-secondary bg-light' : ($plan['is_active'] ? 'border-primary' : 'border-warning'); ?>">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><?php echo htmlspecialchars($plan['name']); ?></h5>
                    <?php if ($plan['is_archived']): ?>
                        <span class="badge bg-secondary">Archived</span>
                    <?php elseif (!$plan['is_active']): ?>
                        <span class="badge bg-warning text-dark">Inactive</span>
                    <?php else: ?>
                        <span class="badge bg-success">Active</span>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <h3 class="card-title text-center">
                        $<?php echo number_format($plan['current_price'], 0); ?> 
                        <small class="text-muted">/ <?php echo $plan['period_months']; ?> mo</small>
                    </h3>
                    <p class="text-center text-muted">Code: <?php echo htmlspecialchars($plan['code']); ?></p>
                    
                    <ul class="list-group list-group-flush mb-3">
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Active Gyms:</span>
                            <strong><?php echo $plan['gym_count']; ?></strong>
                        </li>
                        <?php if ($plan['merged_into_plan_id']): ?>
                            <li class="list-group-item text-danger">
                                Merged Into: <strong><?php echo htmlspecialchars($plan['merged_into_name']); ?></strong>
                            </li>
                        <?php endif; ?>
                    </ul>

                    <?php if ($canManage && !$plan['is_archived']): ?>
                        <div class="d-grid gap-2">
                            <button class="btn btn-outline-primary btn-sm" 
                                    onclick="editPlan(<?php echo htmlspecialchars(json_encode($plan)); ?>)">
                                Edit Name/Status
                            </button>
                            <button class="btn btn-outline-info btn-sm" 
                                    onclick="schedulePrice(<?php echo $plan['id']; ?>, '<?php echo htmlspecialchars($plan['name']); ?>', <?php echo $plan['current_price']; ?>)">
                                Schedule Price Change
                            </button>
                            <button class="btn btn-outline-danger btn-sm" 
                                    onclick="mergePlan(<?php echo $plan['id']; ?>, '<?php echo htmlspecialchars($plan['name']); ?>')">
                                Merge / Archive
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Scheduled Changes Section -->
<div class="card mt-4">
    <div class="card-header">
        <h5 class="mb-0">Scheduled Price Changes</h5>
    </div>
    <div class="card-body">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Plan</th>
                    <th>Old Price</th>
                    <th>New Price</th>
                    <th>Effective Date</th>
                    <th>Notify Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($priceChanges as $change): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($change['plan_name']); ?></td>
                        <td><?php echo number_format($change['old_price'], 2); ?></td>
                        <td><?php echo number_format($change['new_price'], 2); ?></td>
                        <td><?php echo $change['effective_date']; ?></td>
                        <td><?php echo $change['notify_date']; ?></td>
                        <td>
                            <span class="badge bg-<?php echo $change['status'] === 'APPLIED' ? 'success' : 'info'; ?>">
                                <?php echo $change['status']; ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($priceChanges)): ?>
                    <tr><td colspan="6" class="text-center">No scheduled changes.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Create Modal -->
<div class="modal fade" id="createPlanModal" tabindex="-1">
    <div class="modal-dialog">
        <form class="modal-content" action="/admin/plans/store" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <div class="modal-header">
                <h5 class="modal-title">Create New Plan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label>Plan Name</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Unique Code</label>
                    <input type="text" name="code" class="form-control" required placeholder="e.g. BASIC_YEARLY">
                </div>
                <div class="row">
                    <div class="col-6 mb-3">
                        <label>Price (COP)</label>
                        <input type="number" name="current_price" class="form-control" required min="0">
                    </div>
                    <div class="col-6 mb-3">
                        <label>Period (Months)</label>
                        <input type="number" name="period_months" class="form-control" required min="1" value="12">
                    </div>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="is_active" id="isActive" checked>
                    <label class="form-check-label" for="isActive">Active for new sales</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Create Plan</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editPlanModal" tabindex="-1">
    <div class="modal-dialog">
        <form class="modal-content" action="/admin/plans/update" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <input type="hidden" name="id" id="edit_id">
            <div class="modal-header">
                <h5 class="modal-title">Edit Plan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label>Plan Name</label>
                    <input type="text" name="name" id="edit_name" class="form-control" required>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="is_active" id="edit_is_active">
                    <label class="form-check-label" for="edit_is_active">Active</label>
                </div>
                <div class="alert alert-info mt-3">
                    Price and Code cannot be edited directly here to preserve data integrity. Use "Schedule Price Change" for pricing updates.
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Update Plan</button>
            </div>
        </form>
    </div>
</div>

<!-- Schedule Price Modal -->
<div class="modal fade" id="schedulePriceModal" tabindex="-1">
    <div class="modal-dialog">
        <form class="modal-content" action="/admin/plans/schedulePrice" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <input type="hidden" name="plan_id" id="schedule_plan_id">
            <div class="modal-header">
                <h5 class="modal-title">Schedule Price Change: <span id="schedule_plan_name"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label>Current Price</label>
                    <input type="text" class="form-control" id="schedule_current_price" readonly>
                </div>
                <div class="mb-3">
                    <label>New Price</label>
                    <input type="number" name="new_price" class="form-control" required min="0">
                </div>
                <div class="mb-3">
                    <label>Effective Date</label>
                    <input type="date" name="effective_date" class="form-control" required>
                    <small class="text-muted">When the price actually updates.</small>
                </div>
                <div class="mb-3">
                    <label>Notification Date</label>
                    <input type="date" name="notify_date" class="form-control" required>
                    <small class="text-muted">When to notify customers (if applicable).</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Schedule Change</button>
            </div>
        </form>
    </div>
</div>

<!-- Merge Modal -->
<div class="modal fade" id="mergeModal" tabindex="-1">
    <div class="modal-dialog">
        <form class="modal-content" action="/admin/plans/merge" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <input type="hidden" name="source_plan_id" id="merge_source_id">
            <div class="modal-header">
                <h5 class="modal-title">Merge Plan: <span id="merge_source_name"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <strong>Warning:</strong> This will archive the source plan and migrate all existing Gyms to the target plan. This action cannot be easily undone.
                </div>
                <div class="mb-3">
                    <label>Target Plan (Move Gyms To)</label>
                    <select name="target_plan_id" class="form-select" required>
                        <option value="">Select Plan...</option>
                        <?php foreach ($activePlans as $p): ?>
                            <option value="<?php echo $p['id']; ?>"><?php echo htmlspecialchars($p['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="update_snapshots" id="updateSnapshots">
                    <label class="form-check-label" for="updateSnapshots">
                        Update Gym Subscription Price/Period Snapshots?
                    </label>
                    <div class="form-text text-muted">
                        Unchecked: Gyms keep their old price (Grandfathered).<br>
                        Checked: Gyms will pay the new Target Plan price on next renewal.
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-danger">Confirm Merge</button>
            </div>
        </form>
    </div>
</div>

<script>
function editPlan(plan) {
    document.getElementById('edit_id').value = plan.id;
    document.getElementById('edit_name').value = plan.name;
    document.getElementById('edit_is_active').checked = (plan.is_active == 1);
    new bootstrap.Modal(document.getElementById('editPlanModal')).show();
}

function schedulePrice(id, name, currentPrice) {
    document.getElementById('schedule_plan_id').value = id;
    document.getElementById('schedule_plan_name').innerText = name;
    document.getElementById('schedule_current_price').value = currentPrice;
    new bootstrap.Modal(document.getElementById('schedulePriceModal')).show();
}

function mergePlan(id, name) {
    document.getElementById('merge_source_id').value = id;
    document.getElementById('merge_source_name').innerText = name;
    new bootstrap.Modal(document.getElementById('mergeModal')).show();
}
</script>
