<?php
$title = 'Gimnasios Clientes (SaaS)';
ob_start();
?>

<div class="flex justify-between mb-4">
    <h2>Gimnasios Registrados</h2>
    <button onclick="location.href='<?= BASE_URL ?>/admin/gyms/create'">+ Alta Nuevo Gimnasio</button>
</div>

<div class="card">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre del Gimnasio</th>
                <th>Plan Contratado</th>
                <th>Miembros</th>
                <th>Vencimiento Pago</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($gyms as $gym):
                $isExpired = strtotime($gym['valid_until']) < time();
            ?>
            <tr>
                <td><?= $gym['id'] ?></td>
                <td><?= $gym['name'] ?><br><small class="text-muted"><?= $gym['domain'] ?></small></td>
                <td><?= $gym['plan_name'] ?></td>
                <td><?= $gym['total_members'] ?></td>
                <td style="<?= $isExpired ? 'color: #ff6b6b; font-weight: bold;' : '' ?>">
                    <?= $gym['valid_until'] ?>
                    <?= $isExpired ? ' (Vencido)' : '' ?>
                </td>
                <td><span class="badge badge-<?= $gym['status'] ?>"><?= strtoupper($gym['status']) ?></span></td>
                <td>
                    <form action="<?= BASE_URL ?>/admin/gyms/toggle" method="POST" style="display:inline;">
                        <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= $_SESSION[CSRF_TOKEN_NAME] ?? '' ?>">
                        <input type="hidden" name="id" value="<?= $gym['id'] ?>">
                        <?php if ($gym['status'] === 'active'): ?>
                            <input type="hidden" name="status" value="suspended">
                            <button type="submit" class="badge" style="background:#F44336;">Suspender</button>
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
