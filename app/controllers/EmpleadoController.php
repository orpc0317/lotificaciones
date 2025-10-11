<?php

namespace App\Controllers;

use App\Models\EmpleadoModel;

class EmpleadoController
{
    public function index()
    {
        include_once __DIR__ . '/../views/empleados.php';
    }

    public function ajaxList()
    {
        $model = new EmpleadoModel();
        $data = $model->getAll();
        echo json_encode(['data' => $data]);
    }

    public function create()
    {
        $model = new EmpleadoModel();
        $result = $model->create($_POST, $_FILES);
        echo json_encode(['success' => $result]);
    }

    public function update()
    {
        $model = new EmpleadoModel();
        $result = $model->update($_POST, $_FILES);
        echo json_encode(['success' => $result]);
    }

    public function delete()
    {
        $model = new EmpleadoModel();
        $result = $model->delete($_POST['id']);
        echo json_encode(['success' => $result]);
    }
}
