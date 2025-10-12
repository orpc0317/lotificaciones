<?php

namespace App\Controllers;

use App\Models\EmpleadoModel;

class EmpleadoController
{
    public function index()
    {
        // Cargar datos necesarios para el formulario (puestos, departamentos)
        try {
            $model = new EmpleadoModel();
            $puestos = $model->getPuestos();
            $departamentos = $model->getDepartamentos();
        } catch (\Exception $e) {
            $puestos = [];
            $departamentos = [];
        }

        include_once __DIR__ . '/../views/empleados.php';
    }

    public function ajaxList()
    {
        try {
            $model = new EmpleadoModel();
            $data = $model->getAll();

            header('Content-Type: application/json', true, 200);
            echo json_encode(['data' => $data]);
        } catch (\Exception $e) {
            $this->logError($e);
            header('Content-Type: application/json', true, 500);
            echo json_encode(['error' => 'Ocurrió un error al obtener los empleados.']);
        }
    }

    public function create()
    {
        try {
            $model = new EmpleadoModel();
            $result = $model->create($_POST, $_FILES);
            header('Content-Type: application/json', true, 200);
            echo json_encode(['success' => $result]);
        } catch (\Exception $e) {
            $this->logError($e);
            header('Content-Type: application/json', true, 500);
            echo json_encode(['error' => 'Ocurrió un error al crear el empleado.']);
        }
    }

    public function update()
    {
        try {
            $model = new EmpleadoModel();
            $result = $model->update($_POST, $_FILES);
            header('Content-Type: application/json; charset=utf-8', true, 200);
            echo json_encode(['success' => $result]);
        } catch (\Exception $e) {
            $this->logError($e);
            header('Content-Type: application/json; charset=utf-8', true, 500);
            echo json_encode(['error' => 'Ocurrió un error al actualizar el empleado.']);
        }
    }

    public function delete()
    {
        try {
            $model = new EmpleadoModel();
            $result = $model->delete($_POST['id']);
            header('Content-Type: application/json', true, 200);
            echo json_encode(['success' => $result]);
        } catch (\Exception $e) {
            $this->logError($e);
            header('Content-Type: application/json', true, 500);
            echo json_encode(['error' => 'Ocurrió un error al eliminar el empleado.']);
        }
    }

    private function logError($e)
    {
        $logDir = __DIR__ . '/../../storage/logs';
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }
        $logFile = $logDir . '/app.log';
        $message = sprintf("[%s] %s in %s:%d\nStack trace:\n%s\n\n", date('Y-m-d H:i:s'), $e->getMessage(), $e->getFile(), $e->getLine(), $e->getTraceAsString());
        @file_put_contents($logFile, $message, FILE_APPEND | LOCK_EX);
    }

    // Ruta de depuración accesible sólo desde localhost
    public function debug()
    {
        $remote = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        if ($remote !== '127.0.0.1' && $remote !== '::1' && $remote !== 'localhost') {
            http_response_code(403);
            echo 'Forbidden';
            return;
        }

        try {
            $model = new EmpleadoModel();
            $count = $model->countAll();
            $db = $model->getDatabaseName();
            header('Content-Type: application/json', true, 200);
            echo json_encode(['db' => $db, 'empleados_count' => $count]);
        } catch (\Exception $e) {
            $this->logError($e);
            header('Content-Type: application/json', true, 500);
            echo json_encode(['error' => 'Debug error']);
        }
    }

    // Obtener un empleado por id (para modal)
    public function get()
    {
        try {
            $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
            if ($id <= 0) {
                header('Content-Type: application/json', true, 400);
                echo json_encode(['error' => 'ID inválido']);
                return;
            }

            $model = new EmpleadoModel();
            $empleado = $model->getById($id);
            if (!$empleado) {
                header('Content-Type: application/json', true, 404);
                echo json_encode(['error' => 'Empleado no encontrado']);
                return;
            }

            header('Content-Type: application/json', true, 200);
            echo json_encode(['data' => $empleado]);
        } catch (\Exception $e) {
            $this->logError($e);
            header('Content-Type: application/json', true, 500);
            echo json_encode(['error' => 'Error al obtener el empleado']);
        }
    }
}
