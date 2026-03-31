<?php
$title = 'CRM - Prospectos de Venta';
ob_start();
?>

<div class="flex justify-between mb-4">
    <h2>Leads de Venta (Posibles Gimnasios)</h2>
    <button onclick="location.href='<?= BASE_URL ?>/admin/leads/create'">+ Nuevo Prospecto</button>
</div>

<div class="card">
    <table>
        <thead>
            <tr>
                <th>Gimnasio Prospecto</th>
                <th>Contacto Principal</th>
                <th>Email / Tel</th>
                <th>Estado Embudo</th>
                <th>Fecha Ingreso</th>
                <th>Cambiar Estado</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($leads as $lead): ?>
            <tr>
                <td><strong><?= htmlspecialchars($lead['company_name']) ?></strong></td>
                <td><?= htmlspecialchars($lead['contact_name']) ?></td>
                <td>
                    <a href="mailto:<?= htmlspecialchars($lead['email']) ?>"><?= htmlspecialchars($lead['email']) ?></a><br>
                    <small><?= htmlspecialchars($lead['phone']) ?></small>
                </td>
                <td>
                    <?php
                        $color = '#FFC107'; // new
                        if ($lead['status'] === 'won') $color = '#4CAF50';
                        if ($lead['status'] === 'lost') $color = '#F44336';
                        if ($lead['status'] === 'contacted') $color = '#2196F3';
                    ?>
                    <span class="badge" style="background: <?= $color ?>; color: #fff;"><?= strtoupper(str_replace('_', ' ', $lead['status'])) ?></span>
                </td>
                <td><?= date('d/m/Y', strtotime($lead['created_at'])) ?></td>
                <td>
                    <form action="<?= BASE_URL ?>/admin/leads/update-status" method="POST" style="display:inline;">
                        <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= $_SESSION[CSRF_TOKEN_NAME] ?? '' ?>">
                        <input type="hidden" name="id" value="<?= $lead['id'] ?>">
                        <select name="status" onchange="this.form.submit()" style="padding: 4px; border-radius: 4px; width: 120px;">
                            <option value="new" <?= $lead['status'] === 'new' ? 'selected' : '' ?>>Nuevo</option>
                            <option value="contacted" <?= $lead['status'] === 'contacted' ? 'selected' : '' ?>>Contactado</option>
                            <option value="demo_scheduled" <?= $lead['status'] === 'demo_scheduled' ? 'selected' : '' ?>>Demo Agendada</option>
                            <option value="won" <?= $lead['status'] === 'won' ? 'selected' : '' ?>>Ganado (Cliente)</option>
                            <option value="lost" <?= $lead['status'] === 'lost' ? 'selected' : '' ?>>Perdido</option>
                        </select>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php
$content = ob_get_clean();
include VIEW_PATH . '/layouts/main.php';
?>
