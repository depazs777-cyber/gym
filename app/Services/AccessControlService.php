<?php

namespace App\Services;

use App\Core\Database;

class AccessControlService {
    protected $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function generateToken($gymId, $memberId) {
        // Generar UUIDv4 manual (sin librerías externas)
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10
        $uuid = vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));

        $stmt = $this->db->prepare("
            INSERT INTO access_tokens (gym_id, member_id, token_uuid, expires_at)
            VALUES (:gym_id, :member_id, :uuid, DATE_ADD(NOW(), INTERVAL 24 HOUR))
        ");

        $stmt->execute([
            ':gym_id' => $gymId,
            ':member_id' => $memberId,
            ':uuid' => $uuid
        ]);

        return $uuid;
    }

    public function validateAccess($tokenUuid, $gymId) {
        $stmt = $this->db->prepare("
            SELECT t.*, m.status as member_status, m.membership_expiry
            FROM access_tokens t
            JOIN members m ON t.member_id = m.id
            WHERE t.token_uuid = :uuid AND t.gym_id = :gym_id
        ");

        $stmt->execute([':uuid' => $tokenUuid, ':gym_id' => $gymId]);
        $token = $stmt->fetch();

        if (!$token) {
            return ['allowed' => false, 'reason' => 'Token no válido.'];
        }

        if ($token['status'] !== 'valid') {
            return ['allowed' => false, 'reason' => 'Token ya usado o revocado.'];
        }

        if (strtotime($token['expires_at']) < time()) {
            return ['allowed' => false, 'reason' => 'Token expirado.'];
        }

        if ($token['member_status'] !== 'active') {
            return ['allowed' => false, 'reason' => 'Membresía inactiva.'];
        }

        // Validar Anti-passback: Verificar si el usuario ya ingresó hoy
        $stmtLog = $this->db->prepare("
            SELECT COUNT(*) as accesses
            FROM access_logs
            WHERE gym_id = :gym_id
              AND member_id = :member_id
              AND direction = 'in'
              AND DATE(access_at) = CURDATE()
              AND status = 'granted'
        ");
        $stmtLog->execute([
            ':gym_id' => $gymId,
            ':member_id' => $token['member_id']
        ]);
        $logCount = $stmtLog->fetch();

        if ($logCount['accesses'] > 0) {
            // Registrar intento fallido por anti-passback
            $log = $this->db->prepare("INSERT INTO access_logs (gym_id, member_id, direction, status, denial_reason) VALUES (:gym_id, :member_id, 'in', 'denied', 'Anti-passback: Ya ingresó hoy')");
            $log->execute([':gym_id' => $gymId, ':member_id' => $token['member_id']]);

            return ['allowed' => false, 'reason' => 'Anti-passback: Ya ingresó hoy.'];
        }

        // Marcar como usado
        $update = $this->db->prepare("UPDATE access_tokens SET status = 'used', used_at = NOW() WHERE id = :id");
        $update->execute([':id' => $token['id']]);

        // Registrar Log Exitoso
        $log = $this->db->prepare("INSERT INTO access_logs (gym_id, member_id, direction, status) VALUES (:gym_id, :member_id, 'in', 'granted')");
        $log->execute([':gym_id' => $gymId, ':member_id' => $token['member_id']]);

        return ['allowed' => true];
    }
}
