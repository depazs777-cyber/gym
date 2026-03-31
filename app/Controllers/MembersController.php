<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\ThirdParty;
use App\Services\AccessControlService;

class MembersController extends Controller {

    public function index() {
        $stmt = $this->db->prepare("
            SELECT m.*, t.name, t.doc_number, t.email, t.phone
            FROM members m
            JOIN third_parties t ON m.third_party_id = t.id
            WHERE m.gym_id = :gym_id
        ");
        $stmt->execute([':gym_id' => $_SESSION['gym_id']]);
        $members = $stmt->fetchAll();

        $this->view('members/index', ['members' => $members]);
    }

    public function create() {
        $this->view('members/create');
    }

    public function store() {
        $data = $_POST;
        $gymId = $_SESSION['gym_id'];

        try {
            $this->db->beginTransaction();

            // 1. Crear Tercero (Datos Personales)
            $thirdPartyStmt = $this->db->prepare("
                INSERT INTO third_parties (gym_id, doc_type, doc_number, name, email, phone, type)
                VALUES (:gym_id, :doc_type, :doc_number, :name, :email, :phone, 'customer')
            ");
            $thirdPartyStmt->execute([
                ':gym_id' => $gymId,
                ':doc_type' => $data['doc_type'],
                ':doc_number' => $data['doc_number'],
                ':name' => $data['name'],
                ':email' => $data['email'],
                ':phone' => $data['phone']
            ]);
            $thirdPartyId = $this->db->lastInsertId();

            // 2. Crear Miembro (Datos Gym)
            $memberStmt = $this->db->prepare("
                INSERT INTO members (gym_id, third_party_id, code, status, membership_expiry)
                VALUES (:gym_id, :tp_id, :code, 'active', :expiry)
            ");
            $memberStmt->execute([
                ':gym_id' => $gymId,
                ':tp_id' => $thirdPartyId,
                ':code' => $data['doc_number'], // Usamos cédula como código inicial
                ':expiry' => date('Y-m-d', strtotime('+30 days')) // Por defecto 1 mes
            ]);
            $memberId = $this->db->lastInsertId();

            // 3. Generar Token de Acceso Inicial
            $accessService = new AccessControlService();
            $accessService->generateToken($gymId, $memberId);

            $this->db->commit();
            $this->redirect(BASE_URL . '/members');

        } catch (\PDOException $e) {
            $this->db->rollBack();
            // Manejo básico de duplicados
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                die("Error: El documento ya está registrado.");
            }
            die("Error al crear miembro: " . $e->getMessage());
        }
    }
}
