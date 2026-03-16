<?php

class PlanController extends Controller {
    protected $planModel;
    protected $tenantModel;

    public function __construct() {
        Auth::requireLogin('superadmin');
        if (Auth::user()->role_id != 1) {
            Helpers::redirect('auth/login');
        }
        $this->planModel = $this->model('PlanModel');
        $this->tenantModel = $this->model('TenantModel');
    }

    public function index() {
        $plans = $this->planModel->findAll();
        $this->view('superadmin/planes/index', ['plans' => $plans]);
    }

    public function create() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (!Session::verifyCsrfToken($_POST['csrf_token'])) {
                Helpers::flash('plan_msg', 'Error de validación (CSRF).', 'alert alert-danger');
                Helpers::redirect('plan/create');
            }
            $_POST = Helpers::sanitize($_POST);
            $this->planModel->create($_POST);
            Helpers::flash('plan_msg', 'Plan creado exitosamente.');
            Helpers::redirect('plan');
        } else {
            $this->view('superadmin/planes/form');
        }
    }

    public function edit($id) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (!Session::verifyCsrfToken($_POST['csrf_token'])) {
                Helpers::flash('plan_msg', 'Error de validación (CSRF).', 'alert alert-danger');
                Helpers::redirect('plan/edit/' . $id);
            }
            $_POST = Helpers::sanitize($_POST);
            $this->planModel->update($id, $_POST);
            Helpers::flash('plan_msg', 'Plan actualizado exitosamente.');
            Helpers::redirect('plan');
        } else {
            $plan = $this->planModel->findById($id);
            if (!$plan) {
                Helpers::redirect('plan');
            }
            $this->view('superadmin/planes/form', ['plan' => $plan]);
        }
    }

    public function bulkIncrease() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (!Session::verifyCsrfToken($_POST['csrf_token'])) {
                Helpers::flash('plan_msg', 'Error de validación (CSRF).', 'alert alert-danger');
                Helpers::redirect('plan/bulkIncrease');
            }
            $porcentaje = floatval($_POST['porcentaje']);
            $excepciones = isset($_POST['excepciones']) ? $_POST['excepciones'] : [];

            if ($porcentaje > 0) {
                $factor = 1 + ($porcentaje / 100);

                // 1. Guardar precio antiguo para excepciones
                $tenants = $this->tenantModel->getAllWithPlans();
                foreach ($tenants as $t) {
                    if (in_array($t->id, $excepciones)) {
                        // Si está exento, le asignamos como precio personalizado el precio actual del plan
                        // si es que no tiene ya uno configurado
                        if (empty($t->precio_personalizado)) {
                            $this->tenantModel->query("UPDATE tenants SET precio_personalizado = :precio WHERE id = :id");
                            $this->tenantModel->bind(':precio', $t->precio); // $t->precio is from joined plans table in getAllWithPlans
                            $this->tenantModel->bind(':id', $t->id);
                            $this->tenantModel->execute();
                        }
                    } else {
                        // Si no está exento y tenía un precio personalizado, lo borramos para que herede el nuevo precio base
                        // o lo podemos aumentar. Aquí asumiremos que si no es excepción, siempre hereda el precio base.
                        $this->tenantModel->query("UPDATE tenants SET precio_personalizado = NULL WHERE id = :id");
                        $this->tenantModel->bind(':id', $t->id);
                        $this->tenantModel->execute();
                    }
                }

                // 2. Aumentar el precio base de todos los planes
                $this->planModel->query("UPDATE plans SET precio = precio * :factor");
                $this->planModel->bind(':factor', $factor);
                $this->planModel->execute();

                Helpers::flash('plan_msg', 'Aumento del ' . $porcentaje . '% aplicado exitosamente a los planes.');
            } else {
                Helpers::flash('plan_msg', 'Porcentaje inválido.', 'alert alert-danger');
            }

            Helpers::redirect('plan');
        } else {
            $tenants = $this->tenantModel->getAllWithPlans();
            $this->view('superadmin/planes/increase', ['tenants' => $tenants]);
        }
    }
}
