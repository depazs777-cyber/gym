<?php require_once APP_ROOT . '/views/layouts/gym-header.php'; ?>
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Punto de Venta (POS)</h1>
    </div>

    <div class="row">
        <!-- Panel de Productos -->
        <div class="col-md-7">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">Productos (Agua, Suplementos, etc.)</div>
                <div class="card-body">
                    <div class="row">
                        <?php if (empty($products)): ?>
                            <div class="col-12"><p>No hay productos registrados en inventario.</p></div>
                        <?php else: ?>
                            <?php foreach ($products as $p): ?>
                                <div class="col-md-4 mb-3">
                                    <div class="card h-100 border-primary text-center btn-add-product"
                                         style="cursor:pointer"
                                         data-id="<?php echo $p->id; ?>"
                                         data-name="<?php echo htmlspecialchars($p->nombre); ?>"
                                         data-price="<?php echo $p->precio; ?>">
                                        <div class="card-body">
                                            <h6 class="card-title"><?php echo htmlspecialchars($p->nombre); ?></h6>
                                            <p class="card-text fw-bold text-success">$<?php echo number_format($p->precio, 2); ?></p>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Panel de Carrito/Facturación -->
        <div class="col-md-5">
            <div class="card shadow">
                <div class="card-header bg-dark text-white">Factura Actual</div>
                <div class="card-body p-0">
                    <table class="table table-sm m-0" id="cart-table">
                        <thead class="bg-light">
                            <tr>
                                <th>Cant</th>
                                <th>Producto</th>
                                <th>Subtotal</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="cart-items">
                            <!-- Items agregados por JS -->
                        </tbody>
                    </table>
                </div>
                <div class="card-footer bg-light">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="m-0">Total:</h4>
                        <h3 class="m-0 text-success fw-bold">$<span id="cart-total">0.00</span></h3>
                    </div>

                    <div class="mb-3">
                        <label>Método de Pago:</label>
                        <select id="payment-method" class="form-select form-select-lg">
                            <option value="efectivo">Efectivo</option>
                            <option value="tarjeta">Tarjeta (Datafono)</option>
                            <option value="transferencia">Transferencia Bancaria</option>
                        </select>
                    </div>

                    <button class="btn btn-success btn-lg w-100" id="btn-checkout" disabled>Cobrar y Contabilizar</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let cart = [];

        document.querySelectorAll('.btn-add-product').forEach(item => {
            item.addEventListener('click', function() {
                let id = this.getAttribute('data-id');
                let name = this.getAttribute('data-name');
                let price = parseFloat(this.getAttribute('data-price'));

                // Buscar si existe
                let existing = cart.find(i => i.id === id);
                if (existing) {
                    existing.qty += 1;
                } else {
                    cart.push({id: id, name: name, price: price, qty: 1});
                }
                updateCartUI();
            });
        });

        function updateCartUI() {
            let tbody = document.getElementById('cart-items');
            tbody.innerHTML = '';
            let total = 0;

            cart.forEach((item, index) => {
                let subtotal = item.qty * item.price;
                total += subtotal;

                let tr = document.createElement('tr');
                tr.innerHTML = `
                    <td class="align-middle">${item.qty}</td>
                    <td class="align-middle">${item.name}</td>
                    <td class="align-middle">$${subtotal.toFixed(2)}</td>
                    <td class="align-middle text-end">
                        <button class="btn btn-sm btn-danger btn-remove" data-index="${index}">X</button>
                    </td>
                `;
                tbody.appendChild(tr);
            });

            document.getElementById('cart-total').innerText = total.toFixed(2);
            document.getElementById('btn-checkout').disabled = cart.length === 0;

            // Re-bind remove buttons
            document.querySelectorAll('.btn-remove').forEach(btn => {
                btn.addEventListener('click', function() {
                    let idx = parseInt(this.getAttribute('data-index'));
                    cart.splice(idx, 1);
                    updateCartUI();
                });
            });
        }

        document.getElementById('btn-checkout').addEventListener('click', function() {
            if(cart.length === 0) return;

            let total = document.getElementById('cart-total').innerText;
            let method = document.getElementById('payment-method').value;

            let formData = new FormData();
            formData.append('cart', JSON.stringify(cart));
            formData.append('total', total);
            formData.append('metodo', method);

            fetch('<?php echo URL_ROOT; ?>/pos/checkout', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    alert('Venta registrada y contabilizada exitosamente.');
                    cart = [];
                    updateCartUI();
                } else {
                    alert('Error al registrar la venta.');
                }
            });
        });
    </script>
<?php require_once APP_ROOT . '/views/layouts/footer.php'; ?>
