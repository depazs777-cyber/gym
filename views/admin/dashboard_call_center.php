<?php defined('APP_NAME') or exit('No direct script access allowed'); ?>
<div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <h2 style="margin: 0;">Call Center Dashboard</h2>
    <div style="font-size: 0.9rem; color: var(--text-muted);"><?= date('l, F j, Y') ?></div>
</div>

<!-- Motivation is now global in layout -->

<!-- Traffic Light & Stats -->
<div class="grid-3">
    <!-- Traffic Light Widget -->
    <div class="card" id="trafficLightCard">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div style="font-size: 0.9rem; color: var(--text-muted);">Status</div>
            <div id="trafficStatus" style="font-weight: 700;">CHECKING</div>
        </div>
        <div style="margin-top: 1rem; text-align: center;">
            <div id="trafficIcon" style="font-size: 3rem;">⚪</div>
            <small id="trafficMsg">Checking allowed hours...</small>
        </div>
    </div>

    <div class="card">
        <div style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 0.5rem;">Calls Today</div>
        <div style="font-size: 2rem; font-weight: 700; color: var(--primary);"><?= $stats['calls_today'] ?? 0 ?></div>
    </div>

    <div class="card">
        <div style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 0.5rem;">Sales (Month)</div>
        <div style="font-size: 2rem; font-weight: 700; color: var(--success);"><?= $stats['sales_month'] ?? 0 ?></div>
    </div>
</div>

<div class="grid-2" style="margin-top: 2rem;">
    <!-- Agenda -->
    <div class="card">
        <h3>Today's Agenda</h3>
        <?php if (empty($agenda)): ?>
            <p>No pending calls for today.</p>
        <?php else: ?>
            <table class="table" style="width: 100%;">
                <thead>
                    <tr>
                        <th>Lead</th>
                        <th>Time</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($agenda as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['name']) ?></td>
                        <td><?= date('H:i', strtotime($item['next_followup'])) ?></td>
                        <td><a href="<?= url('/admin/leads') ?>?search=<?= urlencode($item['phone']) ?>" class="btn btn-sm btn-primary">Go</a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <!-- Other Info or Leaderboard could go here -->
    <div class="card">
        <h3>Assigned Leads</h3>
        <div style="font-size: 3rem; font-weight: 700; color: var(--text-main); text-align: center; margin-top: 1rem;">
            <?= $stats['leads'] ?? 0 ?>
        </div>
    </div>
</div>

<script>
    const startTime = "<?= $settings['call_center_start_time'] ?? '08:00' ?>";
    const endTime = "<?= $settings['call_center_end_time'] ?? '18:00' ?>";

    function checkTime() {
        const now = new Date();
        // Create Date objects for start/end today
        const start = new Date(now);
        const [sh, sm] = startTime.split(':');
        start.setHours(sh, sm, 0);

        const end = new Date(now);
        const [eh, em] = endTime.split(':');
        end.setHours(eh, em, 0);

        const statusDiv = document.getElementById('trafficStatus');
        const iconDiv = document.getElementById('trafficIcon');
        const msgDiv = document.getElementById('trafficMsg');
        const card = document.getElementById('trafficLightCard');

        if (now >= start && now <= end) {
            statusDiv.innerHTML = '<span style="color:var(--success)">ONLINE</span>';
            iconDiv.innerText = '🟢';
            msgDiv.innerText = 'Calls Allowed';
            card.style.borderLeft = '5px solid var(--success)';
        } else {
            statusDiv.innerHTML = '<span style="color:var(--danger)">OFFLINE</span>';
            iconDiv.innerText = '🔴';
            msgDiv.innerText = 'Outside Business Hours';
            card.style.borderLeft = '5px solid var(--danger)';
        }
    }

    setInterval(checkTime, 60000); // Check every minute
    checkTime(); // Run now
</script>
