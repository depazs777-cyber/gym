<?php require_once APP_ROOT . '/views/layouts/gym-header.php'; ?>
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Nuevo Asiento Contable (Partida Doble)</h1>
        <a href="<?php echo URL_ROOT; ?>/accounting" class="btn btn-sm btn-outline-secondary">Regresar</a>
    </div>

    <div class="card shadow p-4 mx-auto">
        <form action="<?php echo URL_ROOT; ?>/accounting/store" method="POST" id="form_accounting">
            <div class="row mb-3">
                <div class="col-md-4">
                    <label>Fecha</label>
                    <input type="date" name="fecha" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                </div>
                <div class="col-md-4">
                    <label>Tipo de Comprobante</label>
                    <select name="tipo_comprobante" class="form-select" required>
                        <option value="ingreso">Ingreso (Recibo de Caja)</option>
                        <option value="egreso">Egreso (Comprobante de Pago)</option>
                        <option value="nota">Nota de Contabilidad</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label>Descripción / Concepto</label>
                    <input type="text" name="descripcion" class="form-control" required>
                </div>
            </div>

            <h5 class="mt-4 border-bottom pb-2">Líneas del Asiento</h5>
            <table class="table table-bordered" id="accounting_table">
                <thead>
                    <tr>
                        <th width="40%">Cuenta Contable (PUC)</th>
                        <th width="25%">Débito</th>
                        <th width="25%">Crédito</th>
                        <th width="10%">Acción</th>
                    </tr>
                </thead>
                <tbody id="accounting_body">
                    <!-- Fila 1 -->
                    <tr>
                        <td>
                            <select name="accounts[]" class="form-select select-account" required>
                                <option value="">Seleccione cuenta...</option>
                                <?php foreach ($accounts as $acc): ?>
                                    <option value="<?php echo $acc->id; ?>"><?php echo $acc->codigo . ' - ' . htmlspecialchars($acc->nombre); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td><input type="number" step="0.01" name="debitos[]" class="form-control input-debito" value="0.00" min="0"></td>
                        <td><input type="number" step="0.01" name="creditos[]" class="form-control input-credito" value="0.00" min="0"></td>
                        <td><button type="button" class="btn btn-danger btn-sm remove-row">X</button></td>
                    </tr>
                    <!-- Fila 2 -->
                    <tr>
                        <td>
                            <select name="accounts[]" class="form-select select-account" required>
                                <option value="">Seleccione cuenta...</option>
                                <?php foreach ($accounts as $acc): ?>
                                    <option value="<?php echo $acc->id; ?>"><?php echo $acc->codigo . ' - ' . htmlspecialchars($acc->nombre); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td><input type="number" step="0.01" name="debitos[]" class="form-control input-debito" value="0.00" min="0"></td>
                        <td><input type="number" step="0.01" name="creditos[]" class="form-control input-credito" value="0.00" min="0"></td>
                        <td><button type="button" class="btn btn-danger btn-sm remove-row">X</button></td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4">
                            <button type="button" class="btn btn-success btn-sm" id="add_row">+ Agregar Línea</button>
                        </td>
                    </tr>
                    <tr class="fw-bold bg-light">
                        <td class="text-end">TOTALES:</td>
                        <td id="total_debito">0.00</td>
                        <td id="total_credito">0.00</td>
                        <td id="diferencia">0.00</td>
                    </tr>
                </tfoot>
            </table>

            <button type="submit" class="btn btn-primary" id="btn_save" disabled>Guardar Asiento (Balanceado)</button>
        </form>
    </div>

    <!-- Vanilla JS para manejar formulario contable -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const tableBody = document.getElementById('accounting_body');
            const btnAdd = document.getElementById('add_row');

            // Reutilizar template del primer row
            const firstRowHTML = tableBody.rows[0].innerHTML;

            btnAdd.addEventListener('click', function() {
                const newRow = document.createElement('tr');
                newRow.innerHTML = firstRowHTML;
                // Reset valores
                newRow.querySelector('.input-debito').value = '0.00';
                newRow.querySelector('.input-credito').value = '0.00';
                newRow.querySelector('.select-account').value = '';
                tableBody.appendChild(newRow);
            });

            tableBody.addEventListener('click', function(e) {
                if (e.target.classList.contains('remove-row') && tableBody.rows.length > 2) {
                    e.target.closest('tr').remove();
                    calculateTotals();
                }
            });

            tableBody.addEventListener('input', function(e) {
                if(e.target.classList.contains('input-debito')) {
                    // Si escribe en débito, limpiar crédito
                    if(parseFloat(e.target.value) > 0) {
                        e.target.closest('tr').querySelector('.input-credito').value = '0.00';
                    }
                }
                if(e.target.classList.contains('input-credito')) {
                    // Si escribe en crédito, limpiar débito
                    if(parseFloat(e.target.value) > 0) {
                        e.target.closest('tr').querySelector('.input-debito').value = '0.00';
                    }
                }
                calculateTotals();
            });

            function calculateTotals() {
                let sumDeb = 0, sumCred = 0;
                document.querySelectorAll('.input-debito').forEach(i => sumDeb += parseFloat(i.value || 0));
                document.querySelectorAll('.input-credito').forEach(i => sumCred += parseFloat(i.value || 0));

                document.getElementById('total_debito').innerText = sumDeb.toFixed(2);
                document.getElementById('total_credito').innerText = sumCred.toFixed(2);

                let diff = Math.abs(sumDeb - sumCred);
                document.getElementById('diferencia').innerText = diff.toFixed(2);

                const btnSave = document.getElementById('btn_save');
                if (diff < 0.01 && sumDeb > 0) {
                    document.getElementById('diferencia').style.color = 'green';
                    btnSave.disabled = false;
                    btnSave.innerText = 'Guardar Asiento Contable';
                } else {
                    document.getElementById('diferencia').style.color = 'red';
                    btnSave.disabled = true;
                    btnSave.innerText = 'Desbalanceado (' + diff.toFixed(2) + ')';
                }
            }
        });
    </script>
<?php require_once APP_ROOT . '/views/layouts/footer.php'; ?>
