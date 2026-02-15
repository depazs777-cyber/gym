<?php defined('APP_NAME') or exit('No direct script access allowed');
require_once __DIR__ . '/BaseModel.php';

class SalesOrder extends BaseModel {
    protected $table = 'sales_orders';

    public function create($gymId, $planId, $amount, $discount, $total, $notes, $createdBy) {
        try {
            // Map inputs to schema columns
            $sql = "INSERT INTO sales_orders (gym_id, plan_id, unit_price, discount_value, total_amount, status, notes, seller_user_id) 
                    VALUES (?, ?, ?, ?, ?, 'PENDING_PAYMENT', ?, ?)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$gymId, $planId, $amount, $discount, $total, $notes, $createdBy]);
            return $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            // Prevent crash on missing table
            return 0;
        }
    }

    public function get($id) {
        try {
            $stmt = $this->pdo->prepare("SELECT *, total_amount as total FROM sales_orders WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            return null;
        }
    }

    public function getAllPending() {
        try {
            $stmt = $this->pdo->prepare("
                SELECT o.*, o.total_amount as total, g.name as gym_name, p.name as plan_name 
                FROM sales_orders o
                JOIN gyms g ON o.gym_id = g.id
                LEFT JOIN saas_plans p ON o.plan_id = p.id
                WHERE o.status != 'PAID'
                ORDER BY o.created_at DESC
            ");
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    public function updateStatus($id, $status) {
        try {
            $stmt = $this->pdo->prepare("UPDATE sales_orders SET status = ? WHERE id = ?");
            $stmt->execute([$status, $id]);
        } catch (PDOException $e) {}
    }

    public function getByGym($gymId) {
        try {
            $stmt = $this->pdo->prepare("SELECT *, total_amount as total FROM sales_orders WHERE gym_id = ? ORDER BY created_at DESC");
            $stmt->execute([$gymId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
}
