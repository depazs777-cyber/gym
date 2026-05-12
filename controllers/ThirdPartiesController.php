<?php defined('APP_NAME') or exit('No direct script access allowed');

class ThirdPartiesController extends BaseController {

    public function __construct() {
        // Gym Admin, Reception, Finance can manage third parties
        $this->checkRole(['ADMIN_GYM', 'RECEPCION', 'FINANZAS', 'CONTADOR']);
    }

    public function index() {
        $gymId = $_SESSION['gym_id'];
        $db = (new Database())->getConnection();

        $search = $_GET['search'] ?? '';
        $filter = $_GET['filter'] ?? 'ALL'; // ALL, CLIENT, PROVIDER

        $sql = "SELECT * FROM third_parties WHERE gym_id = ?";
        $params = [$gymId];

        if (!empty($search)) {
            $sql .= " AND (name LIKE ? OR doc_number LIKE ? OR email LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        if ($filter === 'CLIENT') {
            $sql .= " AND is_client = 1";
        } elseif ($filter === 'PROVIDER') {
            $sql .= " AND is_provider = 1";
        }

        $sql .= " ORDER BY name ASC LIMIT 100";
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $thirdParties = $stmt->fetchAll();

        $this->view('layouts/main', [
            'childView' => 'gym/third_parties/index',
            'thirdParties' => $thirdParties,
            'search' => $search,
            'filter' => $filter
        ]);
    }

    public function create() {
        // Show create form
        $this->view('layouts/main', [
            'childView' => 'gym/third_parties/create',
            'isEdit' => false
        ]);
    }

    public function store() {
        $this->verifyCsrf();
        $gymId = $_SESSION['gym_id'];
        
        $docNumber = $_POST['doc_number'] ?? '';
        $name = $_POST['name'] ?? ''; // Razon Social or Full Name
        
        if (empty($docNumber) || empty($name)) {
            $_SESSION['error'] = 'Document Number and Name are required.';
            $this->redirect('/gym/third_parties/create');
        }

        $db = (new Database())->getConnection();

        // Check duplicate
        $stmt = $db->prepare("SELECT id FROM third_parties WHERE gym_id = ? AND doc_number = ?");
        $stmt->execute([$gymId, $docNumber]);
        if ($stmt->fetch()) {
            $_SESSION['error'] = 'A third party with this Document Number already exists.';
            $this->redirect('/gym/third_parties/create');
        }

        $sql = "INSERT INTO third_parties (
            gym_id, type_persona, doc_type, doc_number, dv, 
            name, trade_name, email, phone, address, city, 
            is_client, is_provider, reteiva_percent, reteica_percent, 
            has_economic_activity, rut_required
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $db->prepare($sql);
        $stmt->execute([
            $gymId,
            $_POST['type_persona'] ?? 'NATURAL',
            $_POST['doc_type'] ?? 'CC',
            $docNumber,
            $_POST['dv'] ?? null,
            $name,
            $_POST['trade_name'] ?? null,
            $_POST['email'] ?? null,
            $_POST['phone'] ?? null,
            $_POST['address'] ?? null,
            $_POST['city'] ?? null,
            isset($_POST['is_client']) ? 1 : 0,
            isset($_POST['is_provider']) ? 1 : 0,
            $_POST['reteiva_percent'] ?? 0,
            $_POST['reteica_percent'] ?? 0,
            isset($_POST['has_economic_activity']) ? 1 : 0,
            isset($_POST['rut_required']) ? 1 : 0
        ]);

        $_SESSION['success'] = 'Third Party created successfully.';
        $this->redirect('/gym/third_parties');
    }

    public function edit() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
             $this->redirect('/gym/third_parties');
        }

        $gymId = $_SESSION['gym_id'];
        $db = (new Database())->getConnection();

        $stmt = $db->prepare("SELECT * FROM third_parties WHERE id = ? AND gym_id = ?");
        $stmt->execute([$id, $gymId]);
        $tp = $stmt->fetch();

        if (!$tp) {
            $_SESSION['error'] = 'Third Party not found.';
            $this->redirect('/gym/third_parties');
        }

        $this->view('layouts/main', [
            'childView' => 'gym/third_parties/create', // Reuse create view
            'isEdit' => true,
            'tp' => $tp
        ]);
    }

    public function update() {
        $this->verifyCsrf();
        $gymId = $_SESSION['gym_id'];
        $id = $_POST['id'] ?? null;
        $docNumber = $_POST['doc_number'] ?? '';
        $name = $_POST['name'] ?? '';

        if (!$id || empty($docNumber) || empty($name)) {
            $_SESSION['error'] = 'Invalid Data.';
            $this->redirect('/gym/third_parties');
        }

        $db = (new Database())->getConnection();

        // Check duplicate (exclude self)
        $stmt = $db->prepare("SELECT id FROM third_parties WHERE gym_id = ? AND doc_number = ? AND id != ?");
        $stmt->execute([$gymId, $docNumber, $id]);
        if ($stmt->fetch()) {
            $_SESSION['error'] = 'Duplicate Document Number.';
            $this->redirect('/gym/third_parties/edit/' . $id);
        }

        $sql = "UPDATE third_parties SET 
            type_persona = ?, doc_type = ?, doc_number = ?, dv = ?, 
            name = ?, trade_name = ?, email = ?, phone = ?, address = ?, city = ?, 
            is_client = ?, is_provider = ?, reteiva_percent = ?, reteica_percent = ?, 
            has_economic_activity = ?, rut_required = ?
            WHERE id = ? AND gym_id = ?";

        $stmt = $db->prepare($sql);
        $stmt->execute([
            $_POST['type_persona'] ?? 'NATURAL',
            $_POST['doc_type'] ?? 'CC',
            $docNumber,
            $_POST['dv'] ?? null,
            $name,
            $_POST['trade_name'] ?? null,
            $_POST['email'] ?? null,
            $_POST['phone'] ?? null,
            $_POST['address'] ?? null,
            $_POST['city'] ?? null,
            isset($_POST['is_client']) ? 1 : 0,
            isset($_POST['is_provider']) ? 1 : 0,
            $_POST['reteiva_percent'] ?? 0,
            $_POST['reteica_percent'] ?? 0,
            isset($_POST['has_economic_activity']) ? 1 : 0,
            isset($_POST['rut_required']) ? 1 : 0,
            $id,
            $gymId
        ]);

        $_SESSION['success'] = 'Third Party updated.';
        $this->redirect('/gym/third_parties');
    }
}
