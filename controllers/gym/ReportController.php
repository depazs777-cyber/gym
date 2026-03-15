<?php

class ReportController extends Controller {
    public function __construct() {
        Auth::requireLogin();
        $this->tenant = Tenant::current();
        if (!$this->tenant || Auth::user()->tenant_id != $this->tenant->id) {
            Helpers::redirect('auth/login');
        }
        $this->accountModel = $this->model('AccountingAccountModel');
        $this->entryModel = $this->model('AccountingEntryModel');
    }

    public function balance() {
        // En una implementación real se sumaría el saldo de cada cuenta
        $this->view('gym/reports/balance', []);
    }

    public function pnl() {
        // Estado de resultados (PyG)
        $this->view('gym/reports/pnl', []);
    }
}
