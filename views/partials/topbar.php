<header class="topbar">
    <div class="topbar-left">
        <!-- Mobile Toggle could go here -->
        <h2 style="margin:0; font-size: 1.1rem; color: var(--text-muted);">
            <?= isset($_SESSION['gym_name']) ? $_SESSION['gym_name'] : 'Global Admin Panel' ?>
        </h2>
    </div>
    <div class="topbar-right">
        
        <!-- Notifications -->
        <?php if (isset($_SESSION['gym_id'])): ?>
        <div style="position: relative; cursor: pointer;" onclick="toggleNotifs()">
            <span style="font-size: 1.2rem;">🔔</span>
            <span id="notifBadge" class="notif-badge" style="display:none;">0</span>
            
            <div id="notifDropdown" class="notif-dropdown" style="display:none; position: absolute; background:white;">
                <div class="notif-header">
                    <span>Notifications</span>
                    <small onclick="markRead('all'); event.stopPropagation();" style="cursor: pointer; color: var(--primary);">Mark all read</small>
                </div>
                <div id="notifList" style="max-height: 300px; overflow-y: auto;"></div>
            </div>
        </div>
        <?php endif; ?>

        <div style="text-align: right; line-height: 1.2;">
            <div style="font-weight: 600;"><?= $_SESSION['user_name'] ?? 'User' ?></div>
            <small style="color: var(--text-muted);"><?= $_SESSION['user_role'] ?? '' ?></small>
        </div>
    </div>
</header>
