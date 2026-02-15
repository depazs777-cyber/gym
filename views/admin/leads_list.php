<?php defined('APP_NAME') or exit('No direct script access allowed'); ?>
<div class="page-header" style="display: flex; justify-content: space-between; align-items: center;">
    <h2>Leads Management</h2>
    <?php if ($_SESSION['user_role'] !== 'CALL_CENTER'): ?>
    <a href="<?= url('/admin/leads/create') ?>" class="btn btn-primary">Create Lead</a>
    <?php endif; ?>
</div>

<!-- Warning for Call Center if no leads -->
<?php if ($_SESSION['user_role'] === 'CALL_CENTER' && empty($leads)): ?>
<div class="alert alert-warning">
    No tienes leads asignados. Solicita asignación a tu supervisor.
</div>
<?php endif; ?>

<!-- Filters -->
<div class="card" style="margin-bottom: 1rem; padding: 1rem;">
    <form method="GET" action="<?= url('/admin/leads') ?>" style="display: flex; gap: 1rem; flex-wrap: wrap;">
        <input type="text" name="search" placeholder="Name or Phone" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" style="padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px;">
        
        <select name="status" style="padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px;">
            <option value="">All Statuses</option>
            <option value="NEW" <?= ($_GET['status'] ?? '') === 'NEW' ? 'selected' : '' ?>>New</option>
            <option value="CONTACTED" <?= ($_GET['status'] ?? '') === 'CONTACTED' ? 'selected' : '' ?>>Contacted</option>
            <option value="INTERESTED" <?= ($_GET['status'] ?? '') === 'INTERESTED' ? 'selected' : '' ?>>Interested</option>
            <option value="WON" <?= ($_GET['status'] ?? '') === 'WON' ? 'selected' : '' ?>>Won</option>
            <option value="LOST" <?= ($_GET['status'] ?? '') === 'LOST' ? 'selected' : '' ?>>Lost</option>
            <option value="DNC" <?= ($_GET['status'] ?? '') === 'DNC' ? 'selected' : '' ?>>DNC</option>
        </select>

        <select name="city" style="padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px;">
            <option value="">All Cities</option>
            <?php foreach ($cities as $city): ?>
                <option value="<?= htmlspecialchars($city) ?>" <?= ($_GET['city'] ?? '') === $city ? 'selected' : '' ?>><?= htmlspecialchars($city) ?></option>
            <?php endforeach; ?>
        </select>

        <label style="display: flex; align-items: center; gap: 0.5rem;">
            <?php if ($_SESSION['user_role'] === 'CALL_CENTER'): ?>
                <input type="hidden" name="assigned_me" value="1">
                <input type="checkbox" checked disabled> Assigned to Me
            <?php else: ?>
                <input type="checkbox" name="assigned_me" value="1" <?= ($_GET['assigned_me'] ?? '') == 1 ? 'checked' : '' ?>>
                Assigned to Me
            <?php endif; ?>
        </label>

        <button type="submit" class="btn btn-primary">Filter</button>
    </form>
</div>

<div class="card" style="padding: 0; overflow-x: auto;">
    <table class="table" style="width: 100%;">
        <thead>
            <tr>
                <th>Status</th>
                <th>Name / Gym</th>
                <th>Phone</th>
                <th>City</th>
                <th>Owner</th>
                <th>Last Call</th>
                <th>Next Call</th>
                <th>Assigned To</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($leads as $lead): ?>
            <tr>
                <td>
                    <?php
                        $colors = [
                            'NEW' => 'bg-info', 'CONTACTED' => 'bg-warning', 'INTERESTED' => 'bg-primary',
                            'WON' => 'bg-success', 'LOST' => 'bg-danger', 'DNC' => 'bg-secondary'
                        ];
                        $bg = $colors[$lead['status']] ?? 'bg-secondary';
                    ?>
                    <span class="badge <?= $bg ?>"><?= $lead['status'] ?></span>
                </td>
                <td>
                    <strong><?= htmlspecialchars($lead['name']) ?></strong><br>
                    <small style="color: var(--text-muted);"><?= htmlspecialchars($lead['gym_name'] ?? '') ?></small>
                </td>
                <td><?= htmlspecialchars($lead['phone']) ?></td>
                <td><?= htmlspecialchars($lead['city'] ?? '-') ?></td>
                <td><?= htmlspecialchars($lead['owner_name'] ?? '-') ?></td>
                <td><?= $lead['last_call_at'] ? date('M d, H:i', strtotime($lead['last_call_at'])) : '-' ?></td>
                <td>
                    <?php if ($lead['next_followup']): ?>
                        <?php 
                            $isOverdue = strtotime($lead['next_followup']) < time();
                            $style = $isOverdue ? 'color: var(--danger); font-weight: bold;' : '';
                        ?>
                        <span style="<?= $style ?>"><?= date('M d, H:i', strtotime($lead['next_followup'])) ?></span>
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($lead['assigned_user_name'] ?? 'Unassigned') ?></td>
                <td>
                    <button class="btn btn-sm btn-success" data-lead-id="<?= $lead['id'] ?>" onclick="openCallModal(<?= $lead['id'] ?>)">📞 Call</button>
                    <?php if ($_SESSION['user_role'] !== 'CALL_CENTER'): ?>
                        <button class="btn btn-sm btn-secondary" onclick='openEditModal(<?= json_encode($lead) ?>)'>✏️ Edit</button>
                        <button class="btn btn-sm btn-info" onclick='openAssignModal(<?= json_encode($lead) ?>)'>👤 Assign</button>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Edit Modal -->
<div id="editModal" class="modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; overflow-y: auto;">
    <div class="modal-content card" style="margin: 5% auto; width: 90%; max-width: 500px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
            <h2>Edit Lead</h2>
            <span onclick="document.getElementById('editModal').style.display='none'" style="cursor: pointer; font-size: 1.5rem;">&times;</span>
        </div>
        <form method="POST" action="<?= url('/admin/leads/update') ?>">
            <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= $_SESSION[CSRF_TOKEN_NAME] ?>">
            <input type="hidden" name="id" id="edit_id">
            
            <div class="form-group">
                <label>Name</label>
                <input type="text" name="name" id="edit_name" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Phone</label>
                <input type="text" name="phone" id="edit_phone" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Gym Name</label>
                <input type="text" name="gym_name" id="edit_gym_name" class="form-control">
            </div>
            <div class="form-group">
                <label>Owner Name</label>
                <input type="text" name="owner_name" id="edit_owner_name" class="form-control">
            </div>
            <div class="form-group">
                <label>City</label>
                <input type="text" name="city" id="edit_city" class="form-control">
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%;">Update Lead</button>
        </form>
    </div>
</div>

<!-- Assign Modal -->
<div id="assignModal" class="modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; overflow-y: auto;">
    <div class="modal-content card" style="margin: 5% auto; width: 90%; max-width: 500px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
            <h2>Assign Lead</h2>
            <span onclick="document.getElementById('assignModal').style.display='none'" style="cursor: pointer; font-size: 1.5rem;">&times;</span>
        </div>
        <form method="POST" action="<?= url('/admin/leads/assign') ?>">
            <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= $_SESSION[CSRF_TOKEN_NAME] ?>">
            <input type="hidden" name="lead_id" id="assign_lead_id">
            
            <div class="form-group">
                <label>Select User (Call Center)</label>
                <select name="user_id" id="assign_user_id" class="form-control" required>
                    <option value="">Loading users...</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%;">Assign Lead</button>
        </form>
    </div>
</div>

<!-- Call Modal -->
<div id="callModal" class="modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; overflow-y: auto;">
    <div class="modal-content card" style="margin: 2% auto; width: 90%; max-width: 800px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
            <h2>Call Lead</h2>
            <span onclick="document.getElementById('callModal').style.display='none'" style="cursor: pointer; font-size: 1.5rem;">&times;</span>
        </div>

        <!-- Motivation Banner inside Modal -->
        <div id="modalMotivation" style="display: none; background: #fff3cd; padding: 10px; margin-bottom: 1rem; border-radius: 4px; border-left: 5px solid #ffc107;">
            <strong id="modalQuote"></strong>
        </div>

        <div class="grid-2">
            <!-- Left: Call Control -->
            <div>
                <div id="leadInfo" style="margin-bottom: 1rem;">Loading...</div>
                
                <!-- Time Traffic Light -->
                <div id="modalTraffic" style="margin-bottom: 1rem; padding: 10px; border-radius: 4px; text-align: center;">
                     Checking Hours...
                </div>

                <div style="text-align: center; margin-bottom: 2rem;">
                    <div id="timerDisplay" style="font-size: 3rem; font-weight: bold; font-family: monospace;">00:00</div>
                    <div style="margin-top: 1rem;">
                        <button id="btnStartCall" class="btn btn-success btn-lg" onclick="startCall()">Start Call</button>
                        <button id="btnEndCall" class="btn btn-danger btn-lg" onclick="endCall()" style="display: none;">End Call</button>
                    </div>
                </div>

                <form id="callForm" method="POST" action="<?= url('/admin/calls/log') ?>" style="display: none;">
                    <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= $_SESSION[CSRF_TOKEN_NAME] ?>">
                    <input type="hidden" name="lead_id" id="inputLeadId">
                    <input type="hidden" name="duration" id="inputDuration">
                    
                    <div class="form-group">
                        <label>Outcome</label>
                        <select name="outcome" class="form-control" required>
                            <option value="">Select Outcome...</option>
                            <option value="ANSWERED">Answered / Conversation</option>
                            <option value="NO_ANSWER">No Answer</option>
                            <option value="BUSY">Busy</option>
                            <option value="WRONG_NUMBER">Wrong Number</option>
                            <option value="INTERESTED">Interested</option>
                            <option value="WON">Sale Closed (WON)</option>
                            <option value="LOST">Lost (Not Interested)</option>
                            <option value="DNC">Do Not Call (DNC)</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Next Follow-up (Optional)</label>
                        <input type="datetime-local" name="next_followup" class="form-control">
                    </div>

                    <div class="form-group">
                        <label>Notes</label>
                        <textarea name="notes" class="form-control" rows="3"></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%;">Save Call Log</button>
                </form>
            </div>

            <!-- Right: Script -->
            <div style="border-left: 1px solid #eee; padding-left: 1rem;">
                <h3>Script</h3>
                <select id="scriptSelect" class="form-control" onchange="showScript()" style="margin-bottom: 1rem;">
                    <option value="">Select Script...</option>
                </select>
                <div id="scriptBody" style="background: #f8f9fa; padding: 1rem; border-radius: 4px; min-height: 200px; white-space: pre-wrap;">
                    Select a script to view.
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let callStartTime;
let timerInterval;
let callDuration = 0;
let callAllowed = false;

function openEditModal(lead) {
    document.getElementById('editModal').style.display = 'block';
    document.getElementById('edit_id').value = lead.id;
    document.getElementById('edit_name').value = lead.name;
    document.getElementById('edit_phone').value = lead.phone;
    document.getElementById('edit_gym_name').value = lead.gym_name || '';
    document.getElementById('edit_owner_name').value = lead.owner_name || '';
    document.getElementById('edit_city').value = lead.city || '';
}

function openCallModal(leadId) {
    if (!leadId) {
        alert("Error: Missing Lead ID");
        return;
    }
    
    document.getElementById('callModal').style.display = 'block';
    document.getElementById('leadInfo').innerHTML = 'Loading...';
    document.getElementById('callForm').style.display = 'none';
    document.getElementById('btnStartCall').style.display = 'inline-block';
    document.getElementById('btnEndCall').style.display = 'none';
    document.getElementById('timerDisplay').innerText = '00:00';
    document.getElementById('btnStartCall').disabled = true;

    // Fetch Data
    fetch('<?= url('/admin/leads/call-data') ?>?id=' + leadId)
        .then(res => res.json())
        .then(data => {
            if (data.error) {
                alert(data.error);
                return;
            }

            // Lead Info
            document.getElementById('inputLeadId').value = data.lead.id;
            document.getElementById('leadInfo').innerHTML = `
                <h3>${data.lead.name}</h3>
                <p>Phone: <strong>${data.lead.phone}</strong></p>
                <p>Gym: ${data.lead.gym_name || '-'}</p>
                <p>City: ${data.lead.city || '-'}</p>
            `;

            // Motivation
            if (data.motivation) {
                document.getElementById('modalMotivation').style.display = 'block';
                document.getElementById('modalQuote').innerText = '"' + data.motivation.quote_text + '"';
            } else {
                document.getElementById('modalMotivation').style.display = 'none';
            }

            // Scripts
            const sel = document.getElementById('scriptSelect');
            sel.innerHTML = '<option value="">Select Script...</option>';
            if (data.scripts && data.scripts.length > 0) {
                 data.scripts.forEach(s => {
                    const opt = document.createElement('option');
                    opt.value = s.id;
                    opt.text = s.title;
                    opt.dataset.body = s.script_body;
                    sel.appendChild(opt);
                });
            } else {
                const opt = document.createElement('option');
                opt.text = "No active scripts available";
                sel.appendChild(opt);
            }

            // Time Control
            checkCallTime(data.settings);
        })
        .catch(err => {
            console.error(err);
            document.getElementById('leadInfo').innerHTML = '<div class="alert alert-danger">Error loading data.</div>';
             document.getElementById('modalTraffic').innerHTML = 'System Error';
        });
}

function checkCallTime(settings) {
    if (!settings) {
         document.getElementById('modalTraffic').innerHTML = '⚠️ Settings Unavailable';
         return;
    }
    
    const start = settings.call_center_start_time || '08:00';
    const end = settings.call_center_end_time || '18:00';
    
    const now = new Date();
    const currentHHMM = now.getHours().toString().padStart(2, '0') + ':' + now.getMinutes().toString().padStart(2, '0');
    
    const div = document.getElementById('modalTraffic');
    const btn = document.getElementById('btnStartCall');

    if (currentHHMM >= start && currentHHMM <= end) {
        callAllowed = true;
        div.innerHTML = '🟢 Calls Allowed (' + start + ' - ' + end + ')';
        div.style.background = '#d1e7dd';
        div.style.color = '#0f5132';
        btn.disabled = false;
    } else {
        callAllowed = false;
        div.innerHTML = '🔴 Outside Business Hours (' + start + ' - ' + end + ')';
        div.style.background = '#f8d7da';
        div.style.color = '#842029';
        btn.disabled = true;
    }
}

function openAssignModal(lead) {
    document.getElementById('assignModal').style.display = 'block';
    document.getElementById('assign_lead_id').value = lead.id;
    
    // Fetch users via AJAX or just populate if we had them. 
    // Since we don't have a specific endpoint for fetching users in this view, 
    // we can assume a simplified approach: Create a small endpoint or just load all Call Center users if possible.
    // For now, let's fetch from the global data endpoint or similar.
    
    const userSelect = document.getElementById('assign_user_id');
    userSelect.innerHTML = '<option>Loading...</option>';
    
    fetch('<?= url('/admin/leads/users') ?>')
        .then(r => r.json())
        .then(data => {
            if (data.users) {
                userSelect.innerHTML = '<option value="">Select User...</option>';
                data.users.forEach(u => {
                    const opt = document.createElement('option');
                    opt.value = u.id;
                    opt.text = u.name;
                    userSelect.appendChild(opt);
                });
            } else {
                 userSelect.innerHTML = '<option>Error loading users</option>';
            }
        })
        .catch(err => {
            console.error(err);
            userSelect.innerHTML = '<option>Error loading users</option>';
        });
}

function startCall() {
    if (!callAllowed) return;
    
    callStartTime = new Date();
    document.getElementById('btnStartCall').style.display = 'none';
    document.getElementById('btnEndCall').style.display = 'inline-block';
    
    timerInterval = setInterval(() => {
        const now = new Date();
        const diff = Math.floor((now - callStartTime) / 1000);
        callDuration = diff;
        
        const m = Math.floor(diff / 60).toString().padStart(2, '0');
        const s = (diff % 60).toString().padStart(2, '0');
        document.getElementById('timerDisplay').innerText = `${m}:${s}`;
    }, 1000);
}

function endCall() {
    clearInterval(timerInterval);
    document.getElementById('btnEndCall').style.display = 'none';
    document.getElementById('callForm').style.display = 'block';
    document.getElementById('inputDuration').value = callDuration;
}

function showScript() {
    const sel = document.getElementById('scriptSelect');
    const body = sel.options[sel.selectedIndex].dataset.body || 'Select a script...';
    document.getElementById('scriptBody').innerText = body;
}
</script>
