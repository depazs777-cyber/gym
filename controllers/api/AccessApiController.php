<?php

class AccessApiController extends Controller {
    public function __construct() {
        header("Access-Control-Allow-Origin: *");
        header("Content-Type: application/json; charset=UTF-8");
    }

    public function checkin() {
        // Check-in por geolocalización
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = json_decode(file_get_contents("php://input"));

            if (!empty($data->lat) && !empty($data->lng)) {
                http_response_code(200);
                echo json_encode(array("message" => "Check-in exitoso por geolocalización."));
                return;
            }

            http_response_code(400);
            echo json_encode(array("message" => "Coordenadas requeridas."));
            return;
        }
    }
}
