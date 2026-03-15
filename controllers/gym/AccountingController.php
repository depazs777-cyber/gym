<?php

class AccountingController extends Controller {
    public function __construct() {
        Auth::requireLogin();
        $this->tenant = Tenant::current();
        if (!$this->tenant || Auth::user()->tenant_id != $this->tenant->id) {
            Helpers::redirect('auth/login');
        }
        $this->entryModel = $this->model('AccountingEntryModel');
        $this->accountModel = $this->model('AccountingAccountModel');
    }

    public function index() {
        $this->view('gym/accounting/index');
    }

    public function form() {
        $accounts = $this->accountModel->getAccounts($this->tenant->id);
        $this->view('gym/accounting/form', ['accounts' => $accounts]);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Lógica manual para guardar asientos múltiples
            $descripcion = Helpers::sanitize($_POST['descripcion']);
            $tipo = Helpers::sanitize($_POST['tipo_comprobante']);
            $fecha = Helpers::sanitize($_POST['fecha']);

            $accounts = $_POST['accounts']; // arrays
            $debitos = $_POST['debitos'];
            $creditos = $_POST['creditos'];

            // Validar partida doble
            $sumDebitos = array_sum($debitos);
            $sumCreditos = array_sum($creditos);

            if (abs($sumDebitos - $sumCreditos) > 0.01) {
                Helpers::flash('accounting_msg', 'El asiento no está balanceado.', 'alert alert-danger');
                Helpers::redirect('accounting/form');
                return;
            }

            $entry_id = $this->entryModel->createEntry($this->tenant->id, $tipo, $descripcion, $fecha);

            if ($entry_id) {
                for ($i = 0; $i < count($accounts); $i++) {
                    if ($debitos[$i] > 0 || $creditos[$i] > 0) {
                        $this->entryModel->addDetail($entry_id, $accounts[$i], '', $debitos[$i], $creditos[$i]);
                    }
                }
                $this->entryModel->updateTotals($entry_id);
                Helpers::flash('accounting_msg', 'Asiento contable registrado exitosamente.');
            }
            Helpers::redirect('accounting/index');
        }
    }
}
