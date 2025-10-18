<?php

namespace App\Controllers;

use App\Models\EmpleadoModel;
use App\Services\ApiService;
use App\Security\CsrfProtection;
use App\Security\InputValidator;

class EmpleadoController
{
    private $api;
    private $useApi;

    public function __construct()
    {
        // Check if API should be used
        $env = parse_ini_file(__DIR__ . '/../../config/.env');
        $this->useApi = isset($env['API_BASE_URL']) && !empty($env['API_BASE_URL']);
        
        if ($this->useApi) {
            $this->api = new ApiService();
        }
    }

    public function index()
    {
        // Cargar datos necesarios para el formulario (puestos, departamentos)
        try {
            if ($this->useApi) {
                // Use API
                $puestosResponse = $this->api->get('/puestos');
                $departamentosResponse = $this->api->get('/departamentos');
                
                $puestos = $puestosResponse['data'] ?? [];
                $departamentos = $departamentosResponse['data'] ?? [];
            } else {
                // Use direct model (fallback)
                $model = new EmpleadoModel();
                $puestos = $model->getPuestos();
                $departamentos = $model->getDepartamentos();
            }
        } catch (\Exception $e) {
            $this->logError($e);
            $puestos = [];
            $departamentos = [];
        }

        include_once __DIR__ . '/../views/empleados.php';
    }

    public function ajaxList()
    {
        try {
            // Check if DataTables is requesting server-side processing
            $isServerSide = isset($_GET['draw']) || isset($_POST['draw']);
            
            if ($isServerSide) {
                // Server-side processing mode
                $params = array_merge($_GET, $_POST);
                
                if ($this->useApi) {
                    // Use API with query parameters
                    $response = $this->api->get('/empleados', $params);
                    $data = $response['data'] ?? $response;
                } else {
                    // Use direct model (fallback)
                    $model = new EmpleadoModel();
                    $data = $model->getServerSide($params);
                }
                
                header('Content-Type: application/json', true, 200);
                echo json_encode($data);
                return;
            }
            
            // Legacy client-side mode (for initial column setup)
            if ($this->useApi) {
                $response = $this->api->get('/empleados');
                $data = $response['data'] ?? [];
            } else {
                $model = new EmpleadoModel();
                $data = $model->getAll();
            }

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
                ['data' => null, 'title' => '', 'className' => 'no-export dt-no-colvis'],
                ['data' => 'codigo', 'title' => $t('codigo', 'Código')],
                ['data' => 'thumbnail', 'title' => $t('foto', 'Foto'), 'className' => 'no-export'],
                ['data' => 'id', 'title' => $t('id', 'ID'), 'visible' => false],
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
                ['data' => 'comentarios', 'title' => $t('comentarios', 'Comentarios'), 'visible' => false]
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
            // Validate CSRF token
            CsrfProtection::validateOrDie($_POST['csrf_token'] ?? '');
            
            // Comprehensive input validation and sanitization
            $validator = new InputValidator($_POST);
            
            // Required fields
            $validator->required('nombres', 'Nombres')->string()->maxLength(255)->minLength(2);
            $validator->required('apellidos', 'Apellidos')->string()->maxLength(255)->minLength(2);
            
            // Optional fields with validation
            $validator->optional('fecha_nacimiento')->date();
            $validator->optional('edad')->integer()->min(18)->max(100);
            $validator->optional('email')->email()->maxLength(255);
            $validator->optional('telefono')->phone()->maxLength(50);
            $validator->optional('direccion')->string()->maxLength(500);
            $validator->optional('ciudad')->string()->maxLength(100);
            $validator->optional('genero')->string()->in(['Masculino', 'Femenino', 'Otro', '']);
            $validator->optional('puesto_id')->integer()->min(1);
            $validator->optional('departamento_id')->integer()->min(1);
            $validator->optional('comentarios')->string()->maxLength(1000);

            if ($validator->hasErrors()) {
                header('Content-Type: application/json', true, 422);
                echo json_encode([
                    'success' => false, 
                    'error' => 'Validation failed', 
                    'errors' => $validator->getErrors()
                ]);
                return;
            }

            // Get sanitized data
            $sanitizedData = $validator->getSanitized();
            
            // Merge with other POST data (like codigo which is generated server-side)
            $data = array_merge($_POST, $sanitizedData);
            
            // Extract training data if present
            $trainingData = null;
            if (isset($data['training_data'])) {
                $trainingData = json_decode($data['training_data'], true);
                unset($data['training_data']); // Remove from employee data
            }
            
            if ($this->useApi) {
                // Use API
                $response = $this->api->post('/empleados', $data, $_FILES);
                $success = isset($response['success']) ? $response['success'] : false;
                $message = $response['message'] ?? 'No se pudo crear el empleado';
                
                // If employee created successfully and has training data, save it
                if ($success && $trainingData !== null && !empty($trainingData)) {
                    $empleadoId = $response['data']['id'] ?? null;
                    if ($empleadoId) {
                        try {
                            $this->api->put("/empleados/$empleadoId/training", ['training_data' => $trainingData]);
                        } catch (\Exception $e) {
                            error_log("Failed to save training data for new employee $empleadoId: " . $e->getMessage());
                            // Don't fail the whole operation, just log it
                        }
                    }
                }
            } else {
                // Use direct model (fallback)
                $model = new EmpleadoModel();
                $result = $model->create($data, $_FILES);
                $success = (bool)$result;
                $message = $result ? 'Empleado creado exitosamente' : 'No se pudo crear el empleado';
                
                // If employee created successfully and has training data, save it
                if ($success && $trainingData !== null && !empty($trainingData)) {
                    try {
                        $model->saveTraining($result, $trainingData);
                    } catch (\Exception $e) {
                        error_log("Failed to save training data for new employee $result: " . $e->getMessage());
                        // Don't fail the whole operation, just log it
                    }
                }
            }
            
            header('Content-Type: application/json', true, 200);
            echo json_encode([
                'success' => $success, 
                'message' => $message
            ]);
        } catch (\Exception $e) {
            $this->logError($e);
            header('Content-Type: application/json', true, 500);
            echo json_encode(['success' => false, 'error' => 'Ocurrió un error al crear el empleado.']);
        }
    }

    public function update()
    {
        try {
            // Validate CSRF token
            CsrfProtection::validateOrDie($_POST['csrf_token'] ?? '');
            
            // Comprehensive input validation and sanitization
            $validator = new InputValidator($_POST);
            
            // Required fields
            $validator->required('id', 'ID')->integer()->min(1);
            $validator->required('nombres', 'Nombres')->string()->maxLength(255)->minLength(2);
            $validator->required('apellidos', 'Apellidos')->string()->maxLength(255)->minLength(2);
            
            // Optional fields with validation
            $validator->optional('fecha_nacimiento')->date();
            $validator->optional('edad')->integer()->min(18)->max(100);
            $validator->optional('email')->email()->maxLength(255);
            $validator->optional('telefono')->phone()->maxLength(50);
            $validator->optional('direccion')->string()->maxLength(500);
            $validator->optional('ciudad')->string()->maxLength(100);
            $validator->optional('genero')->string()->in(['Masculino', 'Femenino', 'Otro', '']);
            $validator->optional('puesto_id')->integer()->min(1);
            $validator->optional('departamento_id')->integer()->min(1);
            $validator->optional('comentarios')->string()->maxLength(1000);
            $validator->optional('foto_actual')->string()->maxLength(255);

            if ($validator->hasErrors()) {
                header('Content-Type: application/json; charset=utf-8', true, 422);
                echo json_encode([
                    'success' => false, 
                    'error' => 'Validation failed', 
                    'errors' => $validator->getErrors()
                ]);
                return;
            }

            // Get sanitized data
            $sanitizedData = $validator->getSanitized();
            
            // Merge with other POST data
            $data = array_merge($_POST, $sanitizedData);
            
            // Extract training data if present
            $trainingData = null;
            if (isset($data['training_data'])) {
                $trainingData = json_decode($data['training_data'], true);
                unset($data['training_data']); // Remove from main data
            }
            
            if ($this->useApi) {
                // Use API
                $id = $data['id'];
                if (!empty($_FILES['foto']['name'])) {
                    // Photo upload - use upload endpoint
                    $response = $this->api->post("/empleados/$id/upload", [], $_FILES);
                    // Then update other fields
                    unset($data['foto']);
                    unset($data['foto_actual']);
                    if (count($data) > 1) { // More than just ID
                        $response = $this->api->put("/empleados/$id", $data);
                    }
                } else {
                    // Regular update
                    $response = $this->api->put("/empleados/$id", $data);
                }
                
                // Save training data via API if present
                if ($trainingData !== null) {
                    try {
                        $this->api->put("/empleados/$id/training", ['training_data' => $trainingData]);
                    } catch (\Exception $e) {
                        error_log("Error saving training data via API: " . $e->getMessage());
                    }
                }
                
                $success = isset($response['success']) ? $response['success'] : false;
                $message = $response['message'] ?? 'No se realizaron cambios';
            } else {
                // Use direct model (fallback)
                $model = new EmpleadoModel();
                $result = $model->update($data, $_FILES);
                
                // Save training data via model if present
                if ($trainingData !== null) {
                    $model->saveTraining((int)$data['id'], $trainingData);
                }
                
                $success = (bool)$result;
                $message = $result ? 'Empleado actualizado exitosamente' : 'No se realizaron cambios';
            }
            
            header('Content-Type: application/json; charset=utf-8', true, 200);
            echo json_encode([
                'success' => $success, 
                'message' => $message
            ]);
        } catch (\Exception $e) {
            $this->logError($e);
            header('Content-Type: application/json; charset=utf-8', true, 500);
            echo json_encode(['success' => false, 'error' => 'Ocurrió un error al actualizar el empleado.']);
        }
    }

    public function delete()
    {
        try {
            // Validate CSRF token
            CsrfProtection::validateOrDie($_POST['csrf_token'] ?? '');
            
            // Validate ID
            $validator = new InputValidator($_POST);
            $validator->required('id', 'ID')->integer()->min(1);
            
            if ($validator->hasErrors()) {
                header('Content-Type: application/json', true, 422);
                echo json_encode([
                    'success' => false,
                    'error' => 'Validation failed',
                    'errors' => $validator->getErrors()
                ]);
                return;
            }
            
            $id = $validator->get('id');
            
            if ($this->useApi) {
                // Use API
                $response = $this->api->delete("/empleados/$id");
                $success = isset($response['success']) ? $response['success'] : false;
                $message = $response['message'] ?? 'No se pudo eliminar el empleado';
            } else {
                // Use direct model (fallback)
                $model = new EmpleadoModel();
                $result = $model->delete($id);
                $success = $result;
                $message = $result ? 'Empleado eliminado exitosamente' : 'No se pudo eliminar el empleado';
            }
            
            header('Content-Type: application/json', true, 200);
            echo json_encode([
                'success' => $success,
                'message' => $message
            ]);
        } catch (\Exception $e) {
            $this->logError($e);
            header('Content-Type: application/json', true, 500);
            echo json_encode(['success' => false, 'error' => 'Ocurrió un error al eliminar el empleado.']);
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
            if ($this->useApi) {
                // Use API
                $response = $this->api->get('/stats');
                $data = $response['data'] ?? [];
                $count = $data['total'] ?? 0;
                $db = $data['database'] ?? 'unknown';
                $source = 'API';
            } else {
                // Use direct model (fallback)
                $model = new EmpleadoModel();
                $count = $model->countAll();
                $db = $model->getDatabaseName();
                $source = 'Direct Model';
            }
            
            header('Content-Type: application/json', true, 200);
            echo json_encode([
                'db' => $db, 
                'empleados_count' => $count,
                'source' => $source,
                'api_enabled' => $this->useApi
            ]);
        } catch (\Exception $e) {
            $this->logError($e);
            header('Content-Type: application/json', true, 500);
            echo json_encode(['error' => 'Debug error: ' . $e->getMessage()]);
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

            if ($this->useApi) {
                // Use API
                $response = $this->api->get("/empleados/$id");
                $empleado = $response['data'] ?? null;
            } else {
                // Use direct model (fallback)
                $model = new EmpleadoModel();
                $empleado = $model->getById($id);
            }

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

    // View single employee record (read-only)
    public function view($id)
    {
        try {
            if ($this->useApi) {
                // Use API
                $response = $this->api->get("/empleados/" . (int)$id);
                $empleado = $response['data'] ?? null;
                
                // Load training data via API
                $trainingResponse = $this->api->get("/empleados/" . (int)$id . "/training");
                $empleado['training_data'] = $trainingResponse['data'] ?? [];
                
                $puestosResponse = $this->api->get('/puestos');
                $departamentosResponse = $this->api->get('/departamentos');
                
                $puestos = $puestosResponse['data'] ?? [];
                $departamentos = $departamentosResponse['data'] ?? [];
            } else {
                // Use direct model (fallback)
                $model = new EmpleadoModel();
                $empleado = $model->getById((int)$id);
                
                // Load training data via model
                $empleado['training_data'] = $model->getTrainingByEmpleado((int)$id);
                
                $puestos = $model->getPuestos();
                $departamentos = $model->getDepartamentos();
            }
            
            if (!$empleado) {
                http_response_code(404);
                echo "Empleado no encontrado (ID: " . (int)$id . ")";
                return;
            }

            // Debug: Log that we found the employee
            error_log("EmpleadoController::view - Found employee ID: " . $id . ", Name: " . $empleado['nombres']);

            // Debug: Verify view file exists
            $viewPath = __DIR__ . '/../views/empleado_view.php';
            if (!file_exists($viewPath)) {
                throw new \Exception("View file not found: " . $viewPath);
            }

            include_once $viewPath;
        } catch (\Exception $e) {
            $this->logError($e);
            http_response_code(500);
            echo "Error al cargar el empleado: " . $e->getMessage();
        }
    }

    // Edit single employee record
    public function edit($id)
    {
        try {
            if ($this->useApi) {
                // Use API
                $response = $this->api->get("/empleados/" . (int)$id);
                $empleado = $response['data'] ?? null;
                
                // Load training data via API
                $trainingResponse = $this->api->get("/empleados/" . (int)$id . "/training");
                $empleado['training_data'] = $trainingResponse['data'] ?? [];
                
                $puestosResponse = $this->api->get('/puestos');
                $departamentosResponse = $this->api->get('/departamentos');
                
                $puestos = $puestosResponse['data'] ?? [];
                $departamentos = $departamentosResponse['data'] ?? [];
            } else {
                // Use direct model (fallback)
                $model = new EmpleadoModel();
                $empleado = $model->getById((int)$id);
                $empleado['training_data'] = $model->getTrainingByEmpleado((int)$id);
                $puestos = $model->getPuestos();
                $departamentos = $model->getDepartamentos();
            }
            
            if (!$empleado) {
                http_response_code(404);
                echo "Empleado no encontrado";
                return;
            }

            include_once __DIR__ . '/../views/empleado_edit.php';
        } catch (\Exception $e) {
            $this->logError($e);
            http_response_code(500);
            echo "Error al cargar el empleado";
        }
    }
}
