<?php
$title = 'Planes SaaS';
ob_start();
?>

<div class="flex justify-between mb-4">
    <h2>Catálogo de Planes (SaaS)</h2>
    <button onclick="location.href='<?= BASE_URL ?>/admin/plans/create'">+ Nuevo Plan SaaS</button>
</div>

<div class="card">
    <p class="text-muted">Estos son los planes que ofreces a los dueños de gimnasios.</p>
    <table>
        <thead>
            <tr>
                <th>Nombre del Plan</th>
                <th>Precio Mensual</th>
                <th>Límite de Miembros</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($plans as $plan): ?>
            <tr>
                <td><strong><?= htmlspecialchars($plan['name']) ?></strong></td>
                <td>$ <?= number_format($plan['price'], 2) ?></td>
                <td><?= htmlspecialchars($plan['max_members']) ?></td>
                <td>
                    <span class="badge" style="background: <?= $plan['status'] === 'active' ? '#4CAF50' : '#F44336' ?>; color: white;">
                        <?= strtoupper($plan['status']) ?>
                    </span>
                </td>
                <td>
                    <form action="<?= BASE_URL ?>/admin/plans/toggle" method="POST" style="display:inline;">
                        <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= $_SESSION[CSRF_TOKEN_NAME] ?? '' ?>">
                        <input type="hidden" name="id" value="<?= $plan['id'] ?>">
                        <?php if ($plan['status'] === 'active'): ?>
                            <input type="hidden" name="status" value="inactive">
                            <button type="submit" class="badge" style="background:#F44336;">Desactivar</button>
                        <?php else: ?>
                            <input type="hidden" name="status" value="active">
                            <button type="submit" class="badge" style="background:#4CAF50;">Activar</button>
                        <?php endif; ?>
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
