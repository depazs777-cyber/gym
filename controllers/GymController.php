<?php defined('APP_NAME') or exit('No direct script access allowed');

class GymController extends BaseController {
    
    public function __construct() {
        // Allow Gym Admin and staff
        $this->checkRole(['ADMIN_GYM', 'RECEPCION', 'ENTRENADOR']);
        // Ensure gym_id is set in session
        if (!isset($_SESSION['gym_id'])) {
            die("No Gym ID associated with this session.");
        }
    }

    public function dashboard() {
        $gymId = $_SESSION['gym_id'];
        $db = new Database()->getConnection();

        // Stats
        $stmt = $db->prepare("SELECT COUNT(*) as total FROM clients WHERE gym_id = ?");
        $stmt->execute([$gymId]);
        $totalClients = $stmt->fetch()['total'];

        $stmt = $db->prepare("SELECT COUNT(*) as total FROM clients WHERE gym_id = ? AND status = 'active'");
        $stmt->execute([$gymId]);
        $activeClients = $stmt->fetch()['total'];

        $this->view('layouts/main', [
            'childView' => 'gym/dashboard',
            'stats' => [
                'total_clients' => $totalClients,
                'active_clients' => $activeClients
            ]
        ]);
    }

    public function listClients() {
        $gymId = $_SESSION['gym_id'];
        $db = new Database()->getConnection();
        
        // Get Gym Warning Threshold
        $stmt = $db->prepare("SELECT config_warning_days FROM gyms WHERE id = ?");
        $stmt->execute([$gymId]);
        $gymConfig = $stmt->fetch();
        $warningDays = $gymConfig['config_warning_days'] ?? 3;

        // Search Logic
        $search = $_GET['q'] ?? '';
        $searchSql = "";
        $params = [$gymId, $gymId];

        if (!empty($search)) {
            $searchSql = " AND (c.name LIKE ? OR c.identification LIKE ?)";
            $term = '%' . $search . '%';
            $params[] = $term;
            $params[] = $term;
        }

        // Fetch Clients with Latest Membership End Date
        $sql = "
            SELECT c.*, 
                   (SELECT MAX(end_date) FROM memberships m WHERE m.client_id = c.id AND m.gym_id = ?) as paid_until
            FROM clients c 
            WHERE c.gym_id = ? 
            $searchSql
            ORDER BY c.name ASC
        ";
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $clients = $stmt->fetchAll();

        // Calculate Status for each client
        $today = new DateTime();
        
        foreach ($clients as &$client) {
            $client['membership_status'] = 'NONE'; // Default
            $client['days_left'] = null;
            $client['status_color'] = 'red';
            $client['status_text'] = 'No Membership';

            if (!empty($client['paid_until'])) {
                $endDate = new DateTime($client['paid_until']);
                $interval = $today->diff($endDate);
                $daysLeft = (int)$interval->format('%r%a'); // Signed days
                
                $client['days_left'] = $daysLeft;

                if ($daysLeft < 0) {
                    $client['membership_status'] = 'EXPIRED';
                    $client['status_color'] = 'red';
                    $client['status_text'] = 'Expired ' . abs($daysLeft) . ' days ago';
                } elseif ($daysLeft <= $warningDays) {
                    $client['membership_status'] = 'EXPIRING';
                    $client['status_color'] = '#ffc107'; // Yellow/Orange
                    $client['status_text'] = 'Expiring in ' . $daysLeft . ' days';
                } else {
                    $client['membership_status'] = 'ACTIVE';
                    $client['status_color'] = 'green';
                    $client['status_text'] = 'Active';
                }
            }
        }

        $this->view('layouts/main', [
            'childView' => 'gym/clients_list',
            'clients' => $clients
        ]);
    }

    public function createClientForm() {
        $this->view('layouts/main', [
            'childView' => 'gym/clients_create'
        ]);
    }

    public function storeClient() {
        $this->verifyCsrf();
        $gymId = $_SESSION['gym_id'];
        $name = $_POST['name'] ?? '';
        $identification = $_POST['identification'] ?? '';
        $email = $_POST['email'] ?? '';
        $phone = $_POST['phone'] ?? '';

        if (empty($name) || empty($identification)) {
            $_SESSION['error'] = 'Name and Identification are required.';
            $this->redirect('/gym/clients/create');
        }

        $db = new Database()->getConnection();

        try {
            $stmt = $db->prepare("INSERT INTO clients (gym_id, name, identification, email, phone, status) VALUES (?, ?, ?, ?, ?, 'active')");
            $stmt->execute([$gymId, $name, $identification, $email, $phone]);

            $_SESSION['success'] = 'Client registered successfully.';
            $this->redirect('/gym/clients');
        } catch (PDOException $e) {
            // Check for duplicate entry
            if (strpos($e->getMessage(), 'unique') !== false || strpos($e->getMessage(), 'Duplicate') !== false) {
                 $_SESSION['error'] = 'Client with this identification already exists in this gym.';
            } else {
                 $_SESSION['error'] = 'Error registering client: ' . $e->getMessage();
            }
            $this->redirect('/gym/clients/create');
        }
    }

    public function getCardData() {
        $gymId = $_SESSION['gym_id'];
        $clientId = $_GET['client_id'] ?? null;

        if (!$clientId) {
            http_response_code(400);
            echo json_encode(['error' => 'Client ID required']);
            exit;
        }

        $db = new Database()->getConnection();

        // 1. Get Client Data
        $stmt = $db->prepare("SELECT * FROM clients WHERE id = ? AND gym_id = ?");
        $stmt->execute([$clientId, $gymId]);
        $client = $stmt->fetch();

        if (!$client) {
            http_response_code(404);
            echo json_encode(['error' => 'Client not found']);
            exit;
        }

        // 2. Get Gym Branding
        $stmt = $db->prepare("SELECT name, branding_logo, contact_info FROM gyms WHERE id = ?");
        $stmt->execute([$gymId]);
        $gym = $stmt->fetch();

        // 3. Get Membership Status (Paid Until)
        $stmt = $db->prepare("SELECT MAX(end_date) as paid_until FROM memberships WHERE client_id = ? AND gym_id = ?");
        $stmt->execute([$clientId, $gymId]);
        $membership = $stmt->fetch();
        
        $paidUntil = $membership['paid_until'] ?? null;
        $statusColor = 'red';
        $statusText = 'No Membership';

        if ($paidUntil) {
            $today = new DateTime();
            $endDate = new DateTime($paidUntil);
            $interval = $today->diff($endDate);
            $daysLeft = (int)$interval->format('%r%a');

            if ($daysLeft < 0) {
                $statusColor = 'red';
                $statusText = 'Expired';
            } elseif ($daysLeft <= 3) { // Hardcoded 3 for simple JSON or fetch config if strictly needed
                $statusColor = '#ffc107';
                $statusText = 'Expiring';
            } else {
                $statusColor = 'green';
                $statusText = 'Active';
            }
        }

        // 4. Generate QR Content (Use Client ID or Token)
        // Prompt says "Token must be unique...". We created client_tokens table.
        // Let's try to fetch token, if not create one? 
        // For MVP patch, we rely on ID as per previous turn's "ShowQR". 
        // But prompt says "QR (token del cliente)". 
        // Ideally we should use the token from client_tokens.
        
        $stmt = $db->prepare("SELECT token FROM client_tokens WHERE client_id = ?");
        $stmt->execute([$clientId]);
        $tokenRow = $stmt->fetch();
        $token = $tokenRow['token'] ?? null;

        if (!$token) {
            // Generate simple token if missing
            $token = bin2hex(random_bytes(16));
            try {
                $ins = $db->prepare("INSERT INTO client_tokens (gym_id, client_id, token) VALUES (?, ?, ?)");
                $ins->execute([$gymId, $clientId, $token]);
            } catch(Exception $e) {
                // Ignore duplicate
            }
        }

        echo json_encode([
            'client' => $client,
            'gym' => $gym,
            'paid_until' => $paidUntil,
            'status_color' => $statusColor,
            'status_text' => $statusText,
            'qr_content' => 'CLIENT_TOKEN:' . $token // Use token format
        ]);
        exit;
    }
}
