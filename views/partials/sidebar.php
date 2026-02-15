<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <span><?= APP_NAME ?></span>
        <small><?= $_SESSION['user_role'] ?? 'User' ?></small>
    </div>
    
    <ul class="sidebar-menu">
        <?php 
            $role = $_SESSION['user_role'] ?? '';
            $isGymUser = isset($_SESSION['gym_id']) && $_SESSION['gym_id'] > 0;
            $currentUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            
            // Helper to check active state
            $isActive = function($path) use ($currentUri) {
                return strpos($currentUri, $path) !== false ? 'active' : '';
            };
        ?>

        <?php if (!$isGymUser): // SaaS Global Users ?>
            <li><a href="<?= url("/admin/dashboard") ?>" class="<?= $isActive('/admin/dashboard') ?>">Dashboard</a></li>
            
            <?php if (in_array($role, ['SUPER_ADMIN', 'VENDEDOR'])): ?>
                <li><a href="<?= url("/admin/gyms") ?>" class="<?= $isActive('/admin/gyms') ?>">Manage Gyms</a></li>
            <?php endif; ?>

            <?php if (in_array($role, ['SUPER_ADMIN', 'FINANZAS'])): ?>
                <li><a href="<?= url("/admin/plans") ?>" class="<?= $isActive('/admin/plans') ?>">Plans & Pricing</a></li>
            <?php endif; ?>

            <?php if (in_array($role, ['SUPER_ADMIN', 'CALL_CENTER', 'MARKETING'])): ?>
                <li class="sidebar-heading">Call Center</li>
                <li><a href="<?= url("/admin/leads") ?>" class="<?= $isActive('/admin/leads') ?>">Leads</a></li>
                <?php if ($role !== 'CALL_CENTER'): ?>
                <li><a href="<?= url("/admin/call-logs") ?>" class="<?= $isActive('/admin/call-logs') ?>">Call Logs</a></li>
                <?php endif; ?>
                <li><a href="<?= url("/admin/agenda") ?>" class="<?= $isActive('/admin/agenda') ?>">My Agenda</a></li>
            <?php endif; ?>

            <?php if (in_array($role, ['SUPER_ADMIN', 'MARKETING'])): ?>
                <li><a href="<?= url("/admin/scripts") ?>" class="<?= $isActive('/admin/scripts') ?>">Scripts</a></li>
                <li><a href="<?= url("/admin/motivation") ?>" class="<?= $isActive('/admin/motivation') ?>">Motivation</a></li>
            <?php endif; ?>

            <?php if ($role === 'SUPER_ADMIN'): ?>
                <li class="sidebar-heading">System</li>
                <li><a href="<?= url("/admin/users") ?>" class="<?= $isActive('/admin/users') ?>">Internal Users</a></li>
            <?php endif; ?>

            <?php if (in_array($role, ['SUPER_ADMIN', 'FINANZAS'])): ?>
                <li class="sidebar-heading">Finance</li>
                <li><a href="<?= url("/admin/billing") ?>" class="<?= $isActive('/admin/billing') ?>">Billing</a></li>
                <li><a href="<?= url("/admin/reports-finance") ?>" class="<?= $isActive('/admin/reports-finance') ?>">Finance Reports</a></li>
                
                <li class="sidebar-heading">Accounting</li>
                <li><a href="<?= url("/admin/accounting/third-parties") ?>" class="<?= $isActive('/admin/accounting/third-parties') ?>">Third Parties</a></li>
                <li><a href="<?= url("/admin/accounting/orders") ?>" class="<?= $isActive('/admin/accounting/orders') ?>">Sales Orders</a></li>
                <li><a href="<?= url("/admin/accounting/purchases") ?>" class="<?= $isActive('/admin/accounting/purchases') ?>">Purchases</a></li>
            <?php endif; ?>

        <?php else: // Gym Users ?>
            <li><a href="<?= url("/gym/dashboard") ?>" class="<?= $isActive('/gym/dashboard') ?>">Dashboard</a></li>
            <li><a href="<?= url("/gym/clients") ?>" class="<?= $isActive('/gym/clients') ?>">Clients</a></li>
            <li><a href="<?= url("/gym/memberships") ?>" class="<?= $isActive('/gym/memberships') ?>">Memberships</a></li>
            <li><a href="<?= url("/gym/plans") ?>" class="<?= $isActive('/gym/plans') ?>">Plans</a></li>
            <li><a href="<?= url("/gym/payments") ?>" class="<?= $isActive('/gym/payments') ?>">Payments</a></li>
            <li><a href="<?= url("/gym/attendance") ?>" class="<?= $isActive('/gym/attendance') ?>">Attendance</a></li>
            
            <li class="sidebar-heading">Accounting</li>
            <li><a href="<?= url("/gym/accounting/third-parties") ?>" class="<?= $isActive('/gym/accounting/third-parties') ?>">Third Parties</a></li>
            <li><a href="<?= url("/gym/accounting/purchases") ?>" class="<?= $isActive('/gym/accounting/purchases') ?>">Purchases</a></li>
            <li><a href="<?= url("/gym/accounting/reports") ?>" class="<?= $isActive('/gym/accounting/reports') ?>">Tax Reports</a></li>

            <li class="sidebar-heading">System</li>
            <li><a href="<?= url("/gym/reports") ?>" class="<?= $isActive('/gym/reports') ?>">Reports</a></li>
            <li><a href="<?= url("/gym/staff") ?>" class="<?= $isActive('/gym/staff') ?>">Staff</a></li>
            <li><a href="<?= url("/gym/settings") ?>" class="<?= $isActive('/gym/settings') ?>">Settings</a></li>
        <?php endif; ?>
        <li><a href="<?= url("/logout") ?>">Logout</a></li>
    </ul>
</aside>
