<?php

class PaymentController extends Controller {
    protected $tenant;
    protected $memberModel;
    protected $accountingModel;
    protected $accountModel;
    public function __construct() {
        Auth::requireLogin();
        $this->tenant = Tenant::current();
        if (!$this->tenant || Auth::user()->tenant_id != $this->tenant->id) {
            Helpers::redirect('gym/auth/login');
        }
        $this->memberModel = $this->model('MemberModel');
        $this->accountingModel = $this->model('AccountingEntryModel');
        $this->accountModel = $this->model('AccountingAccountModel');
    }

    public function index() {
        $this->view('gym/payments/index');
    }

    public function create() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = Helpers::sanitize($_POST);

            $member_id = $_POST['member_id'];
            $monto = $_POST['monto'];
            $meses = $_POST['meses'] ?? 1;

            // Renovar miembro
            $member = $this->memberModel->findById($member_id);
            if ($member) {
                // Calcular nueva fecha
                $fechaActual = $member->fecha_vencimiento && strtotime($member->fecha_vencimiento) > time() ? $member->fecha_vencimiento : date('Y-m-d');
                $nuevaFecha = date('Y-m-d', strtotime("+$meses months", strtotime($fechaActual)));

                $this->memberModel->renew($member_id, $this->tenant->id, $nuevaFecha);

                // Registro Contable Doble Partida
                $this->registerPaymentAccounting($monto, "Pago membresía {$meses} meses, Miembro ID: $member_id");

                Helpers::flash('payment_msg', 'Pago registrado y membresía renovada.');
            }
            Helpers::redirect('payment/index');
        } else {
            $members = $this->memberModel->findAllByTenant($this->tenant->id);
            $this->view('gym/payments/form', ['members' => $members]);
        }
    }

    private function registerPaymentAccounting($monto, $descripcion) {
        $caja = $this->accountModel->getByCode('1105', $this->tenant->id); // Caja
        $ingresos = $this->accountModel->getByCode('4135', $this->tenant->id); // Ingresos servicios

        if ($caja && $ingresos) {
            $entry_id = $this->accountingModel->createEntry($this->tenant->id, 'ingreso', $descripcion, date('Y-m-d'));

            if ($entry_id) {
                // Débito a Caja
                $this->accountingModel->addDetail($entry_id, $caja->id, 'Ingreso a caja', $monto, 0);
                // Crédito a Ingresos
                $this->accountingModel->addDetail($entry_id, $ingresos->id, 'Ingreso por membresía', 0, $monto);

                $this->accountingModel->updateTotals($entry_id);
            }
        }
    }
}
