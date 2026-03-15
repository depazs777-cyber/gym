<?php defined('APP_NAME') or exit('No direct script access allowed');

class GymSettingsController extends BaseController {

    public function __construct() {
        $this->checkRole(['ADMIN_GYM']);
        if (!isset($_SESSION['gym_id'])) {
            die("No Gym ID associated with this session.");
        }
    }

    public function settings() {
        $gymId = $_SESSION['gym_id'];
        $db = Database::getInstance()->getConnection();

        $stmt = $db->prepare("SELECT * FROM gyms WHERE id = ?");
        $stmt->execute([$gymId]);
        $gym = $stmt->fetch();

        // Decode contact info if it exists
        $contactInfo = json_decode($gym['contact_info'] ?? '', true) ?? [];

        $this->view('layouts/main', [
            'childView' => 'gym/settings',
            'gym' => $gym,
            'contactInfo' => $contactInfo
        ]);
    }

    public function update() {
        $this->verifyCsrf();
        $gymId = $_SESSION['gym_id'];

        $name = $_POST['name'] ?? '';
        $nit = $_POST['nit'] ?? '';
        $address = $_POST['address'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $city = $_POST['city'] ?? '';
        $email = $_POST['email'] ?? '';
        $message = $_POST['message'] ?? '';

        $config_annual_days = $_POST['config_annual_days'] ?? 360;
        $config_renewal_mode = $_POST['config_renewal_mode'] ?? 'CONTINUE';
        $config_deduct_session = $_POST['config_deduct_session'] ?? 1;
        $config_warning_days = $_POST['config_warning_days'] ?? 3;

        if (empty($name)) {
            $_SESSION['error'] = 'Gym Name is required.';
            $this->redirect('/gym/settings');
        }

        // Handle File Upload
        $logoPath = null;
        if (isset($_FILES['branding_logo']) && $_FILES['branding_logo']['error'] === UPLOAD_ERR_OK) {
            $allowedTypes = ['image/png', 'image/jpeg', 'image/webp'];
            $fileType = mime_content_type($_FILES['branding_logo']['tmp_name']);
            $maxSize = 2 * 1024 * 1024; // 2MB

            if (!in_array($fileType, $allowedTypes)) {
                $_SESSION['error'] = 'Invalid file type. Only PNG, JPG, and WEBP allowed.';
                $this->redirect('/gym/settings');
            }

            if ($_FILES['branding_logo']['size'] > $maxSize) {
                $_SESSION['error'] = 'File size exceeds 2MB limit.';
                $this->redirect('/gym/settings');
            }

            $ext = pathinfo($_FILES['branding_logo']['name'], PATHINFO_EXTENSION);
            $filename = 'gym_' . $gymId . '_' . time() . '.' . $ext;
            $uploadDir = ROOT_PATH . '/storage/uploads/logos/';

            if (move_uploaded_file($_FILES['branding_logo']['tmp_name'], $uploadDir . $filename)) {
                $logoPath = '/storage/uploads/logos/' . $filename;
            } else {
                $_SESSION['error'] = 'Failed to upload logo.';
                $this->redirect('/gym/settings');
            }
        }

        // Prepare JSON contact info
        $contactInfo = json_encode([
            'nit' => $nit,
            'address' => $address,
            'phone' => $phone,
            'city' => $city,
            'email' => $email,
            'message' => $message
        ]);

        $db = Database::getInstance()->getConnection();

        try {
            $sql = "UPDATE gyms SET
                    name = ?,
                    contact_info = ?,
                    config_annual_days = ?,
                    config_renewal_mode = ?,
                    config_deduct_session = ?,
                    config_warning_days = ?";

            $params = [$name, $contactInfo, $config_annual_days, $config_renewal_mode, $config_deduct_session, $config_warning_days];

            if ($logoPath) {
                $sql .= ", branding_logo = ?";
                $params[] = $logoPath;
            }

            $sql .= " WHERE id = ?";
            $params[] = $gymId;

            $stmt = $db->prepare($sql);
            $stmt->execute($params);

            // Log action?

            $_SESSION['success'] = 'Settings updated successfully.';
        } catch (PDOException $e) {
            $_SESSION['error'] = 'Database Error: ' . $e->getMessage();
        }

        $this->redirect('/gym/settings');
    }
}
