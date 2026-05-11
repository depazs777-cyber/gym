<?php require_once APP_ROOT . '/views/layouts/gym-header.php'; ?>
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Balance General</h1>
        <button class="btn btn-sm btn-outline-secondary" onclick="window.print()">Imprimir PDF</button>
    </div>
    <div class="card shadow p-4">
        <h4 class="text-center">ESTADO DE SITUACIÓN FINANCIERA (BALANCE GENERAL)</h4>
        <p class="text-center">Al <?php echo date('d/m/Y'); ?></p>
        <!-- Simulación de un reporte contable -->
        <table class="table table-sm mt-4">
            <thead>
                <tr class="table-dark"><th>ACTIVOS</th><th class="text-end">Saldo</th></tr>
            </thead>
            <tbody>
                <tr><td>1105 - Caja</td><td class="text-end">$ 1,500.00</td></tr>
                <tr><td>1110 - Bancos</td><td class="text-end">$ 5,000.00</td></tr>
                <tr><td>1305 - Clientes</td><td class="text-end">$ 300.00</td></tr>
                <tr class="fw-bold bg-light"><td>TOTAL ACTIVOS</td><td class="text-end">$ 6,800.00</td></tr>
            </tbody>

            <thead>
                <tr class="table-dark"><th>PASIVOS</th><th class="text-end">Saldo</th></tr>
            </thead>
            <tbody>
                <tr><td>2205 - Proveedores</td><td class="text-end">$ 800.00</td></tr>
                <tr class="fw-bold bg-light"><td>TOTAL PASIVOS</td><td class="text-end">$ 800.00</td></tr>
            </tbody>

            <thead>
                <tr class="table-dark"><th>PATRIMONIO</th><th class="text-end">Saldo</th></tr>
            </thead>
            <tbody>
                <tr><td>3105 - Capital</td><td class="text-end">$ 4,000.00</td></tr>
                <tr><td>3605 - Utilidad del Ejercicio</td><td class="text-end">$ 2,000.00</td></tr>
                <tr class="fw-bold bg-light"><td>TOTAL PATRIMONIO</td><td class="text-end">$ 6,000.00</td></tr>
            </tbody>
        </table>

        <div class="alert alert-success mt-3 fw-bold text-center">
            TOTAL PASIVO + PATRIMONIO = $ 6,800.00 (Ecuación Patrimonial Cumplida)
        </div>
    </div>
<?php require_once APP_ROOT . '/views/layouts/footer.php'; ?>
