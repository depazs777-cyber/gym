<?php defined('APP_NAME') or exit('No direct script access allowed');

class AccountingController extends BaseController {

    public function __construct() {
        $this->checkRole(['ADMIN_GYM', 'FINANZAS', 'CONTADOR']);
    }

    public function index() {
        $gymId = $_SESSION['gym_id'];
        $db = Database::getInstance()->getConnection();

        $type = $_GET['type'] ?? 'ALL'; // RC, CE, FC, ETC.
        $start = $_GET['start_date'] ?? date('Y-m-01');
        $end = $_GET['end_date'] ?? date('Y-m-t');

        $sql = "SELECT d.*, tp.name as third_party_name
                FROM accounting_documents d
                LEFT JOIN third_parties tp ON d.third_party_id = tp.id
                WHERE d.gym_id = ? AND d.issue_date BETWEEN ? AND ?";

        $params = [$gymId, $start, $end];

        if ($type !== 'ALL') {
            $sql .= " AND d.doc_type = ?";
            $params[] = $type;
        }

        $sql .= " ORDER BY d.issue_date DESC, d.id DESC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $documents = $stmt->fetchAll();

        $this->view('layouts/main', [
            'childView' => 'gym/accounting/documents',
            'documents' => $documents,
            'type' => $type,
            'startDate' => $start,
            'endDate' => $end
        ]);
    }

    public function viewDocument($id) {
        $gymId = $_SESSION['gym_id'];
        $db = Database::getInstance()->getConnection();

        $stmt = $db->prepare("
            SELECT d.*, tp.name as third_party_name, tp.doc_number, tp.doc_type as tp_doc_type, tp.address, tp.phone
            FROM accounting_documents d
            LEFT JOIN third_parties tp ON d.third_party_id = tp.id
            WHERE d.id = ? AND d.gym_id = ?
        ");
        $stmt->execute([$id, $gymId]);
        $doc = $stmt->fetch();

        if (!$doc) {
            $_SESSION['error'] = 'Document not found.';
            $this->redirect('/gym/accounting');
        }

        // Get Lines
        $stmt = $db->prepare("SELECT * FROM accounting_document_lines WHERE document_id = ?");
        $stmt->execute([$id]);
        $lines = $stmt->fetchAll();

        // Since we are using Modals, we can return JSON if requested via AJAX,
        // or render a dedicated view. For now, let's render a printable view or detail view.
        // Assuming a simple detail view.

        $this->view('layouts/main', [
            'childView' => 'gym/accounting/document_detail',
            'doc' => $doc,
            'lines' => $lines
        ]);
    }
}
