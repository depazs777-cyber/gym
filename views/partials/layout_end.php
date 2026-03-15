    </div>

    <!-- Scripts (Motivation, Notifs, etc.) -->
    <?php if (in_array($_SESSION['user_role'] ?? '', ['CALL_CENTER', 'SUPER_ADMIN', 'MARKETING', 'VENDEDOR'])): ?>
    <button id="scriptsToggleBtn" onclick="toggleScriptsDrawer()" style="display:none; position:fixed; right:20px; bottom:20px; z-index:9999; border-radius:50%; width:60px; height:60px; font-size:24px; background:var(--primary); color:white; border:none; box-shadow:0 4px 6px rgba(0,0,0,0.1); cursor:pointer;">📜</button>
    <div id="scriptsDrawer" style="position:fixed; top:0; right:-400px; width:350px; height:100vh; background:white; box-shadow:-4px 0 10px rgba(0,0,0,0.1); z-index:10000; transition:right 0.3s; display:flex; flex-direction:column;">
        <div style="padding:1rem; border-bottom:1px solid #eee; display:flex; justify-content:space-between; align-items:center;">
            <h3 style="margin:0;">Call Scripts</h3>
            <span onclick="toggleScriptsDrawer()" style="cursor:pointer; font-size:1.5rem;">&times;</span>
        </div>
        <div style="padding:1rem;">
            <select id="globalScriptSelect" onchange="showGlobalScript()" style="width:100%; padding:0.5rem;">
                <option value="">Select Script...</option>
            </select>
        </div>
        <div id="globalScriptBody" style="flex:1; padding:1rem; overflow-y:auto; background:#f8f9fa; white-space:pre-wrap;"></div>
    </div>
    <script>
    fetch('<?= url('/admin/api/global-data') ?>').then(res => res.json()).then(data => {
        if(data.motivation && data.motivation.length > 0) initMotivationSlider(data.motivation);
        if(data.scripts && data.scripts.length > 0) initScriptsDrawer(data.scripts);
    });
    function initMotivationSlider(posts) {
        const container = document.getElementById('globalMotivationSlider');
        if (container) {
            container.style.display = 'block'; container.style.background = 'linear-gradient(90deg, #1e293b, #334155)'; container.style.color = 'white'; container.style.padding = '1rem'; container.style.marginBottom = '1rem'; container.style.borderRadius = '8px'; container.style.textAlign = 'center';
            let idx = 0;
            function showSlide() {
                const post = posts[idx];
                container.innerHTML = `<div style="display:flex; align-items:center; justify-content:center; gap:15px; animation: fadeIn 0.5s;">${post.image_url ? `<img src="${post.image_url}" style="width:50px; height:50px; border-radius:50%; object-fit:cover;">` : '<span style="font-size:2rem;">💪</span>'}<div><div style="font-size:1.1rem; font-style:italic;">"${post.quote_text}"</div>${post.title ? `<small style="opacity:0.8;">${post.title}</small>` : ''}</div></div>`;
                idx = (idx + 1) % posts.length;
            }
            showSlide(); setInterval(showSlide, 10000);
        }
    }
    function initScriptsDrawer(scripts) {
        document.getElementById('scriptsToggleBtn').style.display = 'block';
        const sel = document.getElementById('globalScriptSelect');
        scripts.forEach(s => { const opt = document.createElement('option'); opt.value = s.id; opt.text = s.title; opt.dataset.body = s.script_body; sel.appendChild(opt); });
    }
    function showGlobalScript() { const sel = document.getElementById('globalScriptSelect'); const body = sel.options[sel.selectedIndex].dataset.body || ''; document.getElementById('globalScriptBody').innerText = body; }
    function toggleScriptsDrawer() { const drawer = document.getElementById('scriptsDrawer'); drawer.style.right = drawer.style.right === '0px' ? '-400px' : '0px'; }
    </script>
    <?php endif; ?>

    <script>
    function toggleNotifs() { const dd = document.getElementById('notifDropdown'); if(dd) { const isVisible = dd.style.display === 'block'; dd.style.display = isVisible ? 'none' : 'block'; if (!isVisible) fetchNotifs(); } }
    window.addEventListener('click', function(e) { const dd = document.getElementById('notifDropdown'); const trigger = document.querySelector('[onclick="toggleNotifs()"]'); if (dd && dd.style.display === 'block' && !dd.contains(e.target) && !trigger.contains(e.target)) { dd.style.display = 'none'; } });
    function fetchNotifs() {
        fetch('<?= url("/gym/notifications/fetch") ?>').then(res => res.json()).then(data => {
            const list = document.getElementById('notifList'); const badge = document.getElementById('notifBadge'); list.innerHTML = '';
            if (data.length > 0) { badge.innerText = data.length; badge.style.display = 'block'; data.forEach(n => { const div = document.createElement('div'); div.className = 'notif-item'; div.innerHTML = `<strong style="color:var(--text-main)">${n.type}</strong> <span style="color:var(--text-muted)">${n.message}</span> <br><small style="color:var(--text-muted); font-size:0.75rem">${n.created_at}</small>`; div.onclick = () => markRead(n.id); list.appendChild(div); }); } else { badge.style.display = 'none'; list.innerHTML = '<div class="notif-item" style="text-align:center; color:var(--text-muted)">No new notifications</div>'; }
        });
    }
    function markRead(id) { const formData = new FormData(); formData.append('<?= CSRF_TOKEN_NAME ?>', '<?= $_SESSION[CSRF_TOKEN_NAME] ?? '' ?>'); formData.append('id', id); fetch('<?= url("/gym/notifications/mark-read") ?>', { method: 'POST', body: formData }).then(() => fetchNotifs()); }
    <?php if (isset($_SESSION['gym_id'])): ?> setInterval(fetchNotifs, 60000); fetchNotifs(); <?php endif; ?>
    </script>
</body>
</html>
