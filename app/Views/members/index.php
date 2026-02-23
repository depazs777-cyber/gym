<?php
$title = 'Listado de Afiliados';
ob_start();
?>

<div class="flex justify-between mb-4">
    <h2>Afiliados</h2>
    <button onclick="location.href='<?= BASE_URL ?>/members/create'">+ Nuevo Afiliado</button>
</div>

<div class="card">
    <table>
        <thead>
            <tr>
                <th>Documento</th>
                <th>Nombre</th>
                <th>Email</th>
                <th>Vencimiento</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($members as $member): ?>
            <tr>
                <td><?= $member['doc_number'] ?></td>
                <td><?= $member['name'] ?></td>
                <td><?= $member['email'] ?></td>
                <td><?= $member['membership_expiry'] ?></td>
                <td><span class="badge badge-<?= $member['status'] ?>"><?= strtoupper($member['status']) ?></span></td>
                <td>
                    <!-- Acciones futuras: Editar, Renovar -->
                    <button class="badge" onclick="alert('Funcionalidad de edición pendiente')">Editar</button>
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
