<?php
require_once '../core/View.php';

class EmpleadosController {
    public function index() {
        View::render('empleados/index');
    }
}