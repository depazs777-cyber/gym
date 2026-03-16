<?php

class PosController extends Controller {
    protected $tenant;
    protected $productModel;
    protected $saleModel;
    protected $accountModel;
    protected $entryModel;
    public function __construct() {
        Auth::requireLogin();
        $this->tenant = Tenant::current();
        if (!$this->tenant || Auth::user()->tenant_id != $this->tenant->id) {
            Helpers::redirect('auth/login');
        }
        $this->productModel = $this->model('ProductModel');
        $this->saleModel = $this->model('SaleModel');
        $this->accountModel = $this->model('AccountingAccountModel');
        $this->entryModel = $this->model('AccountingEntryModel');
    }

    public function index() {
        $products = $this->productModel->getAllByTenant($this->tenant->id);
        $this->view('gym/pos/index', ['products' => $products]);
    }

    public function checkout() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Recibe JSON de productos y método de pago
            $cart = json_decode($_POST['cart'], true);
            $total = $_POST['total'];
            $metodo = $_POST['metodo'] ?? 'efectivo';

            $sale_id = $this->saleModel->createSale($this->tenant->id, null, $total, $metodo);

            if ($sale_id) {
                foreach ($cart as $item) {
                    $this->saleModel->addDetail($sale_id, $item['id'], $item['qty'], $item['price'], $item['qty'] * $item['price']);
                }

                // Asiento contable de venta POS
                $this->registerPosAccounting($total, "Venta POS #$sale_id", $metodo);

                echo json_encode(['success' => true]);
                return;
            }
            echo json_encode(['success' => false]);
            return;
        }
    }

    private function registerPosAccounting($total, $descripcion, $metodo) {
        $cuenta_origen = $metodo == 'efectivo' ? '1105' : '1110'; // Caja o Bancos
        $ingresos_ventas = $this->accountModel->getByCode('4140', $this->tenant->id); // Ingresos ventas POS
        $caja_banco = $this->accountModel->getByCode($cuenta_origen, $this->tenant->id);

        if ($caja_banco && $ingresos_ventas) {
            $entry_id = $this->entryModel->createEntry($this->tenant->id, 'venta', $descripcion, date('Y-m-d'));

            if ($entry_id) {
                $this->entryModel->addDetail($entry_id, $caja_banco->id, "Ingreso a $metodo", $total, 0);
                $this->entryModel->addDetail($entry_id, $ingresos_ventas->id, 'Venta POS', 0, $total);
                $this->entryModel->updateTotals($entry_id);
            }
        }
    }
}
