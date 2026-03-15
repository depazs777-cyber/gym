<?php

class MemberApiController extends Controller {
    public function __construct() {
        header("Access-Control-Allow-Origin: *");
        header("Content-Type: application/json; charset=UTF-8");
        // Se debe verificar el Bearer token aquí en la vida real
    }

    public function qr() {
        // Devuelve el QR actual del atleta logueado
        // Para la demo devolvemos datos falsos
        http_response_code(200);
        echo json_encode(array(
            "qr_data" => "uuid-1234-5678-90ab",
            "expires_in" => 3600
        ));
    }

    public function history() {
        // Devuelve historial de accesos
        http_response_code(200);
        echo json_encode(array(
            "accesos" => [
                ['fecha' => '2023-10-25 08:00:00', 'tipo' => 'entrada'],
                ['fecha' => '2023-10-25 10:00:00', 'tipo' => 'salida']
            ]
        ));
    }
}
