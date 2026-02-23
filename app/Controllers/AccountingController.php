<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Services\AccountingService;
use App\Models\JournalEntry;
use App\Models\ThirdParty;

class AccountingController extends Controller {
    protected $accountingService;

    public function __construct() {
        parent::__construct();
        $this->accountingService = new AccountingService();
    }

    public function index() {
        // Listar asientos recientes
        $stmt = $this->db->prepare("SELECT * FROM journal_entries WHERE gym_id = :gym_id ORDER BY entry_date DESC LIMIT 20");
        $stmt->execute([':gym_id' => $_SESSION['gym_id']]);
        $entries = $stmt->fetchAll();

        $this->view('accounting/index', ['entries' => $entries]);
    }

    public function viewJournal() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            http_response_code(404);
            die("Asiento no especificado.");
        }

        // Obtener Cabecera
        $stmt = $this->db->prepare("SELECT * FROM journal_entries WHERE id = :id AND gym_id = :gym_id");
        $stmt->execute([':id' => $id, ':gym_id' => $_SESSION['gym_id']]);
        $entry = $stmt->fetch();

        if (!$entry) {
            http_response_code(404);
            die("Asiento no encontrado.");
        }

        // Obtener Líneas con nombre de cuenta
        $stmtLines = $this->db->prepare("
            SELECT l.*, a.code, a.name as account_name
            FROM journal_lines l
            JOIN gl_accounts a ON l.account_id = a.id
            WHERE l.entry_id = :id
        ");
        $stmtLines->execute([':id' => $id]);
        $lines = $stmtLines->fetchAll();

        $this->view('accounting/view_journal', ['entry' => $entry, 'lines' => $lines]);
    }

    public function storeReceipt() {
        // Validar CSRF (Middleware ya lo hizo)
        $data = $_POST;
        $amount = floatval($data['amount']);
        $concept = $data['concept'] ?? 'Venta';

        // Buscar IDs de cuentas por Código (PUC)
        $accountCash = $this->getAccountIdByCode('1105'); // Caja General
        $accountRevenue = $this->getAccountIdByCode('4135'); // Ingresos por Servicios

        if (!$accountCash || !$accountRevenue) {
            die("Error de configuración: Cuentas 1105 o 4135 no encontradas en el PUC.");
        }

        $lines = [
            [
                'account_id' => $accountCash,
                'debit' => $amount,
                'credit' => 0
            ],
            [
                'account_id' => $accountRevenue,
                'debit' => 0,
                'credit' => $amount
            ]
        ];

        try {
            $entryId = $this->accountingService->createEntry(
                $_SESSION['gym_id'],
                date('Y-m-d'),
                "RC - " . $concept,
                $lines,
                'receipt',
                null // ID de recibo si existiera tabla específica
            );
            $this->redirect(BASE_URL . '/accounting/view?id=' . $entryId);
        } catch (\Exception $e) {
            echo "Error al crear asiento: " . $e->getMessage();
        }
    }

    private function getAccountIdByCode($code) {
        $stmt = $this->db->prepare("SELECT id FROM gl_accounts WHERE code LIKE :code AND gym_id = :gym_id LIMIT 1");
        // Usamos LIKE por si el código tiene subniveles, pero aquí asumimos match exacto o prefijo
        $stmt->execute([':code' => $code . '%', ':gym_id' => $_SESSION['gym_id']]);
        $result = $stmt->fetch();
        return $result ? $result['id'] : null;
    }
}
