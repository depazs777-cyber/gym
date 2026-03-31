<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Services\AccessControlService;

class AccessController extends Controller {
    protected $accessService;

    public function __construct() {
        parent::__construct();
        $this->accessService = new AccessControlService();
    }

    public function index() {
        $this->view('access/index');
    }

    public function scan() {
        $tokenUuid = $_POST['token'] ?? '';
        $gymId = $_SESSION['gym_id'];

        $result = $this->accessService->validateAccess($tokenUuid, $gymId);

        $this->json($result);
    }
}
