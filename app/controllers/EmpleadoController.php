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
            
            // Check if DataTables is requesting server-side processing
            $isServerSide = isset($_GET['draw']) || isset($_POST['draw']);
            
            if ($isServerSide) {
                // Server-side processing mode
                $params = array_merge($_GET, $_POST);
                $response = $model->getServerSide($params);
                
                header('Content-Type: application/json', true, 200);
                echo json_encode($response);
                return;
            }
            
            // Legacy client-side mode (for initial column setup)
            $data = $model->getAll();

            // Support language for column titles (es / en)
            $lang = isset($_GET['lang']) && $_GET['lang'] === 'en' ? 'en' : 'es';

            // Load translations from i18n JSON file
            $i18nPath = __DIR__ . '/../../public/assets/i18n/' . $lang . '.json';
            $translations = [];
            if (file_exists($i18nPath)) {
                $jsonContent = file_get_contents($i18nPath);
                $translations = json_decode($jsonContent, true);
            }

            // Helper function to get translation with fallback
            $t = function($key, $fallback = '') use ($translations) {
                return isset($translations[$key]) ? $translations[$key] : $fallback;
            };

            $columns = [
                ['data' => 'id', 'title' => $t('id', 'ID')],
                ['data' => 'thumbnail', 'title' => $t('foto', 'Foto'), 'className' => 'no-export'],
                ['data' => 'codigo', 'title' => $t('codigo', 'Código'), 'visible' => false],
                ['data' => 'nombres', 'title' => $t('nombres', 'Nombres')],
                ['data' => 'apellidos', 'title' => $t('apellidos', 'Apellidos')],
                ['data' => 'edad', 'title' => $t('edad', 'Edad')],
                ['data' => 'fecha_nacimiento', 'title' => $t('fecha_nacimiento', 'Fecha de Nacimiento'), 'visible' => false],
                ['data' => 'genero', 'title' => $t('genero', 'Género'), 'visible' => false],
                ['data' => 'puesto_id', 'title' => $t('puesto', 'Puesto'), 'visible' => false],
                ['data' => 'departamento_id', 'title' => $t('departamento', 'Departamento'), 'visible' => false],
                ['data' => 'email', 'title' => $t('email', 'Email'), 'visible' => false],
                ['data' => 'telefono', 'title' => $t('telefono', 'Teléfono'), 'visible' => false],
                ['data' => 'direccion', 'title' => $t('direccion', 'Dirección'), 'visible' => false],
                ['data' => 'ciudad', 'title' => $t('ciudad', 'Ciudad'), 'visible' => false],
                ['data' => 'comentarios', 'title' => $t('comentarios', 'Comentarios'), 'visible' => false],
                ['data' => null, 'title' => $t('actions', 'Acciones'), 'className' => 'no-export dt-no-colvis']
            ];

            header('Content-Type: application/json', true, 200);
            echo json_encode(['columns' => $columns, 'data' => $data]);
        } catch (\Exception $e) {
            $this->logError($e);
            header('Content-Type: application/json', true, 500);
            echo json_encode(['error' => 'Ocurrió un error al obtener los empleados.']);
        }
    }

    public function create()
    {
        try {
            // Basic server-side validation
            $errors = [];
            $nombres = isset($_POST['nombres']) ? trim($_POST['nombres']) : '';
            $apellidos = isset($_POST['apellidos']) ? trim($_POST['apellidos']) : '';
            if ($nombres === '') $errors['nombres'] = 'Nombres es obligatorio';
            if ($apellidos === '') $errors['apellidos'] = 'Apellidos es obligatorio';

            if (!empty($errors)) {
                header('Content-Type: application/json', true, 422);
                echo json_encode(['success' => false, 'error' => 'Validation failed', 'errors' => $errors]);
                return;
            }

            $model = new EmpleadoModel();
            $result = $model->create($_POST, $_FILES);
            header('Content-Type: application/json', true, 200);
            echo json_encode(['success' => (bool)$result, 'message' => $result ? 'Empleado creado' : 'No se pudo crear el empleado']);
        } catch (\Exception $e) {
            $this->logError($e);
            header('Content-Type: application/json', true, 500);
            echo json_encode(['success' => false, 'error' => 'Ocurrió un error al crear el empleado.']);
        }
    }

    public function update()
    {
        try {
            $errors = [];
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            if ($id <= 0) $errors['id'] = 'ID inválido';
            $nombres = isset($_POST['nombres']) ? trim($_POST['nombres']) : '';
            $apellidos = isset($_POST['apellidos']) ? trim($_POST['apellidos']) : '';
            if ($nombres === '') $errors['nombres'] = 'Nombres es obligatorio';
            if ($apellidos === '') $errors['apellidos'] = 'Apellidos es obligatorio';

            if (!empty($errors)) {
                header('Content-Type: application/json; charset=utf-8', true, 422);
                echo json_encode(['success' => false, 'error' => 'Validation failed', 'errors' => $errors]);
                return;
            }

            $model = new EmpleadoModel();
            $result = $model->update($_POST, $_FILES);
            header('Content-Type: application/json; charset=utf-8', true, 200);
            echo json_encode(['success' => (bool)$result, 'message' => $result ? 'Empleado actualizado' : 'No se realizaron cambios']);
        } catch (\Exception $e) {
            $this->logError($e);
            header('Content-Type: application/json; charset=utf-8', true, 500);
            echo json_encode(['success' => false, 'error' => 'Ocurrió un error al actualizar el empleado.']);
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
