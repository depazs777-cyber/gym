<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Third Parties (Suppliers/Clients)</h1>
    <a href="/gym/third_parties/create" class="btn btn-primary">
        <i class="fas fa-plus"></i> New Third Party
    </a>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-6">
                <input type="text" name="search" class="form-control" placeholder="Search by Name, NIT, Email..." value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <div class="col-md-3">
                <select name="filter" class="form-select">
                    <option value="ALL" <?php echo $filter === 'ALL' ? 'selected' : ''; ?>>All Types</option>
                    <option value="CLIENT" <?php echo $filter === 'CLIENT' ? 'selected' : ''; ?>>Clients</option>
                    <option value="PROVIDER" <?php echo $filter === 'PROVIDER' ? 'selected' : ''; ?>>Providers</option>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-outline-secondary w-100">Filter</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Name / Trade Name</th>
                        <th>Document</th>
                        <th>Type</th>
                        <th>Location</th>
                        <th>Contact</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($thirdParties)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-4">No records found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($thirdParties as $tp): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($tp['name']); ?></strong><br>
                                    <small class="text-muted"><?php echo htmlspecialchars($tp['trade_name'] ?? ''); ?></small>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($tp['doc_type']); ?> <?php echo htmlspecialchars($tp['doc_number']); ?>
                                    <?php if ($tp['dv']): ?>-<?php echo htmlspecialchars($tp['dv']); ?><?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($tp['is_client']): ?><span class="badge bg-info">Client</span><?php endif; ?>
                                    <?php if ($tp['is_provider']): ?><span class="badge bg-warning text-dark">Provider</span><?php endif; ?>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($tp['city'] ?? '-'); ?><br>
                                    <small><?php echo htmlspecialchars($tp['address'] ?? ''); ?></small>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($tp['email'] ?? ''); ?><br>
                                    <?php echo htmlspecialchars($tp['phone'] ?? ''); ?>
                                </td>
                                <td>
                                    <a href="/gym/third_parties/edit?id=<?php echo $tp['id']; ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
