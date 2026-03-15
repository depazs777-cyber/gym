<?php

class RoutineController extends Controller {
    public function __construct() {
        Auth::requireLogin();
        $this->tenant = Tenant::current();
        if (!$this->tenant || Auth::user()->tenant_id != $this->tenant->id) {
            Helpers::redirect('auth/login');
        }
    }

    public function index() {
        // Dummy implementation
        $this->view('gym/dashboard', ['title' => 'Rutinas']);
    }
}
