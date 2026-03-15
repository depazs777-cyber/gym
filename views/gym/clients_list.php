<?php defined('APP_NAME') or exit('No direct script access allowed'); ?>
<div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
    <h2 style="margin: 0;">Clients</h2>
    <a href="<?= url("/gym/clients/create") ?>" class="btn btn-primary">Register New Client</a>
</div>

<div class="card" style="margin-bottom: 2rem;">
    <form method="GET" action="<?= url("/gym/clients") ?>" style="display: flex; gap: 1rem;">
        <input type="text" name="q" placeholder="Search by name or ID..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" style="flex-grow: 1;">
        <button type="submit" class="btn btn-primary">Search</button>
        <?php if (!empty($_GET['q'])): ?>
            <a href="<?= url("/gym/clients") ?>" class="btn btn-secondary">Clear</a>
        <?php endif; ?>
    </form>
</div>

<div class="card" style="padding: 0; overflow: hidden;">
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Identification</th>
                <th>Paid Until</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($clients as $client): ?>
            <tr>
                <td>
                    <div style="font-weight: 600;"><?= htmlspecialchars($client['name']) ?></div>
                    <small style="color: var(--text-muted);">ID: <?= $client['id'] ?></small>
                </td>
                <td><?= htmlspecialchars($client['identification']) ?></td>
                <td><?= htmlspecialchars($client['paid_until'] ?? '—') ?></td>
                <td>
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <span style="width: 8px; height: 8px; border-radius: 50%; background-color: <?= $client['status_color'] ?>;"></span>
                        <span style="font-size: 0.85rem; color: var(--text-muted);" title="<?= htmlspecialchars($client['status_text']) ?>">
                            <?= htmlspecialchars(ucfirst(strtolower($client['membership_status']))) ?>
                        </span>
                    </div>
                </td>
                <td>
                    <div style="display: flex; gap: 0.5rem;">
                        <?php if ($client['membership_status'] !== 'ACTIVE'): ?>
                            <a href="<?= url("/gym/memberships/create?client_id=" . $client['id']) ?>" class="btn btn-sm btn-success">Renew</a>
                        <?php else: ?>
                            <a href="<?= url("/gym/memberships/create?client_id=" . $client['id']) ?>" class="btn btn-sm btn-secondary">Sell Plan</a>
                        <?php endif; ?>
                        <button class="btn btn-sm btn-primary" onclick="showCard(<?= $client['id'] ?>)">Carnet</button>
                        <button class="btn btn-sm btn-outline" onclick="showQR(<?= $client['id'] ?>, '<?= htmlspecialchars($client['name']) ?>')">QR</button>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- ID Card Modal -->
<div id="cardModal" class="modal">
    <div class="modal-content" style="width: auto;">
        <div class="modal-header">
            <h3>Client ID Card</h3>
            <button class="btn btn-sm btn-secondary" onclick="closeCard()">&times;</button>
        </div>
        <div class="modal-body" style="display: flex; justify-content: center;">
            <!-- The Card -->
            <div id="idCard" style="border: 1px solid var(--border-color); border-radius: var(--radius-lg); overflow: hidden; background: white; width: 320px; box-shadow: var(--shadow-md);">
                <!-- Header Branding -->
                <div style="background-color: var(--bg-sidebar); color: white; padding: 1.5rem; text-align: center;">
                    <img id="cardLogo" src="" alt="Logo" style="max-height: 50px; margin-bottom: 0.5rem; display: none;">
                    <h3 id="cardGymName" style="margin: 0; font-size: 1.2rem; color: white;">Gym Name</h3>
                </div>

                <!-- Client Info -->
                <div style="padding: 1.5rem; text-align: center;">
                    <div style="width: 80px; height: 80px; background: var(--neutral-bg); border-radius: 50%; margin: 0 auto 1rem; display: flex; align-items: center; justify-content: center; font-size: 2rem; color: var(--text-muted);">
                        👤
                    </div>
                    <h2 id="cardClientName" style="margin: 0 0 0.25rem; font-size: 1.25rem;">Client Name</h2>
                    <p id="cardClientID" style="color: var(--text-muted); margin: 0 0 1rem; font-size: 0.9rem;">ID: 12345</p>

                    <div id="cardStatus" style="margin-bottom: 1rem;"></div>

                    <!-- QR Code -->
                    <div id="cardQR" style="display: flex; justify-content: center; margin: 1rem 0;"></div>

                    <p style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.5rem;">Valid Until: <span id="cardValidUntil">-</span></p>
                </div>

                <!-- Footer -->
                <div style="background: var(--neutral-bg); padding: 0.75rem; text-align: center; font-size: 0.7rem; color: var(--text-muted); border-top: 1px solid var(--border-color);">
                    Personal and non-transferable card.
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="printCard()">Print</button>
            <button class="btn btn-primary" onclick="downloadCard()">Download PNG</button>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script src="<?= url('/assets/js/vendor/html2canvas.min.js') ?>"></script>
<script>
function showCard(clientId) {
    // Reset
    document.getElementById('cardQR').innerHTML = '';
    document.getElementById('cardLogo').style.display = 'none';
    document.getElementById('cardStatus').innerHTML = '';

    // Fetch Data
    fetch('<?= url("/gym/clients/card-data?client_id=") ?>' + clientId)
        .then(res => res.json())
        .then(data => {
            if (data.error) {
                alert(data.error);
                return;
            }

            // Populate Data
            const client = data.client;
            const gym = data.gym;

            document.getElementById('cardClientName').innerText = client.name;
            document.getElementById('cardClientID').innerText = 'ID: ' + client.identification;
            document.getElementById('cardGymName').innerText = gym.name;
            document.getElementById('cardValidUntil').innerText = data.paid_until || '—';

            // Logo
            if (gym.branding_logo) {
                const logo = document.getElementById('cardLogo');
                logo.src = '<?= url("/") ?>' + gym.branding_logo;
                logo.style.display = 'block';
            }

            // Status Badge
            const badge = document.createElement('span');
            badge.className = 'badge';
            badge.style.backgroundColor = data.status_color;
            badge.style.color = 'white';
            badge.innerText = data.status_text.toUpperCase();
            document.getElementById('cardStatus').appendChild(badge);

            // QR
            new QRCode(document.getElementById("cardQR"), {
                text: data.qr_content,
                width: 120,
                height: 120
            });

            document.getElementById('cardModal').style.display = 'flex';
        });
}

function closeCard() {
    document.getElementById('cardModal').style.display = 'none';
}

function printCard() {
    window.print();
}

function downloadCard() {
    const card = document.getElementById('idCard');
    html2canvas(card).then(canvas => {
        const link = document.createElement('a');
        link.download = `carnet_${document.getElementById('cardClientName').innerText}.png`;
        link.href = canvas.toDataURL();
        link.click();
    });
}

// Simple QR Logic for the other button (kept for compatibility)
function showQR(id, name) {
    // Reuse Card logic or keep separate?
    // The prompt asked for redesign, assume this is covered by showCard,
    // but button exists in list. Let's redirect to showCard for consistency
    // or implementing a simpler modal if needed.
    // For now, let's just trigger showCard as it's better.
    showCard(id);
}
</script>
