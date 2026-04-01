<?php

class AuthApiController extends Controller {
    protected $memberModel;
    public function __construct() {
        header("Access-Control-Allow-Origin: *");
        header("Content-Type: application/json; charset=UTF-8");
        header("Access-Control-Allow-Methods: POST, OPTIONS");
        header("Access-Control-Max-Age: 3600");
        header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

        $this->memberModel = $this->model('MemberModel');
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = json_decode(file_get_contents("php://input"));

            if (!empty($data->email) && !empty($data->identificacion)) {
                // Autenticación simplificada para la demo (email + id de membresía)
                // En la vida real usaría passwords o token PIN

                // Retornamos un token JWT falso o simulado para la demo
                http_response_code(200);
                echo json_encode(array(
                    "message" => "Login exitoso.",
                    "token" => "Bearer " . base64_encode(json_encode(['member_id' => 1, 'tenant_id' => 1]))
                ));
                return;
            }

            http_response_code(400);
            echo json_encode(array("message" => "Datos incompletos."));
            return;
        }
    }
}
