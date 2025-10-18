# Training Tab - Full Integration Checklist

## ✅ Completed Integrations

### 1. **i18n Language Support** ✅
- Added 18 new translation keys to `es.json` and `en.json`
- Updated JavaScript to use `window.i18n.t()` for all user-facing messages
- Translation keys added:
  - `tabTraining`, `lblTrainingTitle`, `lblCourseName`, `lblCourseDate`
  - `lblCourseResources`, `lblCourseComments`, `lblAddButton`, `lblUpdateButton`
  - `thCourseName`, `thCourseDate`, `thCourseResources`, `thCourseComments`
  - `lblNoTraining`, `alertCourseName`, `alertCourseDate`, `confirmDeleteCourse`

### 2. **Theme System Compatibility** ✅
- Uses standard Bootstrap classes compatible with theme.js
- Card headers use `bg-light` (theme-aware)
- Table uses `table-striped table-hover` (theme-aware)
- Buttons use standard Bootstrap variants (theme colors auto-apply)
- No hardcoded colors that would break theme switching

### 3. **Frontend Architecture** ✅
- Follows modular pattern used in other tabs
- Uses same card structure as other sections
- Consistent with existing form patterns
- Compatible with tab badge system

## ⚠️ Pending Integrations

### 1. **API Mode Support** ⚠️ CRITICAL
Currently, training data won't load/save when using API mode.

**Required Changes:**

#### A. **API Endpoints** (lotificaciones-api)
Create new file: `lotificaciones-api/app/Controllers/TrainingController.php`
```php
<?php
namespace App\Controllers;
use App\Models\TrainingModel;
use App\Services\ResponseService;

class TrainingController
{
    private $model;
    
    public function __construct()
    {
        $this->model = new TrainingModel();
    }
    
    // GET /api/empleados/{id}/training
    public function getByEmpleado($empleadoId)
    {
        $training = $this->model->getByEmpleado((int)$empleadoId);
        ResponseService::success($training);
    }
    
    // POST /api/empleados/{id}/training
    public function create($empleadoId)
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $result = $this->model->create((int)$empleadoId, $input);
        ResponseService::success($result, 'Training created', 201);
    }
    
    // PUT /api/empleados/{id}/training (bulk update)
    public function bulkUpdate($empleadoId)
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $trainingData = $input['training_data'] ?? [];
        $result = $this->model->bulkUpdate((int)$empleadoId, $trainingData);
        ResponseService::success($result, 'Training updated');
    }
    
    // DELETE /api/empleados/{id}/training/{trainingId}
    public function delete($empleadoId, $trainingId)
    {
        $this->model->delete((int)$trainingId, (int)$empleadoId);
        ResponseService::success(null, 'Training deleted');
    }
}
```

#### B. **API Model** (lotificaciones-api)
Create new file: `lotificaciones-api/app/Models/TrainingModel.php`
```php
<?php
namespace App\Models;
use App\Database\Database;

class TrainingModel
{
    private $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function getByEmpleado($empleadoId)
    {
        $stmt = $this->db->prepare("
            SELECT * FROM empleado_capacitacion 
            WHERE empleado_id = :empleado_id 
            ORDER BY fecha_aprobado DESC
        ");
        $stmt->execute([':empleado_id' => $empleadoId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    public function create($empleadoId, $data)
    {
        $stmt = $this->db->prepare("
            INSERT INTO empleado_capacitacion 
            (empleado_id, nombre_curso, fecha_aprobado, recursos_aprobados, comentarios)
            VALUES (:empleado_id, :nombre_curso, :fecha_aprobado, :recursos_aprobados, :comentarios)
        ");
        
        $stmt->execute([
            ':empleado_id' => $empleadoId,
            ':nombre_curso' => $data['nombre'],
            ':fecha_aprobado' => $data['fecha'],
            ':recursos_aprobados' => $data['recursos'] ?? 0,
            ':comentarios' => $data['comentarios'] ?? null
        ]);
        
        return ['id' => $this->db->lastInsertId()];
    }
    
    public function bulkUpdate($empleadoId, $trainingData)
    {
        // Delete existing records
        $stmt = $this->db->prepare("DELETE FROM empleado_capacitacion WHERE empleado_id = :empleado_id");
        $stmt->execute([':empleado_id' => $empleadoId]);
        
        // Insert new records
        if (!empty($trainingData)) {
            $stmt = $this->db->prepare("
                INSERT INTO empleado_capacitacion 
                (empleado_id, nombre_curso, fecha_aprobado, recursos_aprobados, comentarios)
                VALUES (:empleado_id, :nombre_curso, :fecha_aprobado, :recursos_aprobados, :comentarios)
            ");
            
            foreach ($trainingData as $training) {
                $stmt->execute([
                    ':empleado_id' => $empleadoId,
                    ':nombre_curso' => $training['nombre'],
                    ':fecha_aprobado' => $training['fecha'],
                    ':recursos_aprobados' => $training['recursos'] ?? 0,
                    ':comentarios' => $training['comentarios'] ?? null
                ]);
            }
        }
        
        return ['updated' => count($trainingData)];
    }
    
    public function delete($id, $empleadoId)
    {
        $stmt = $this->db->prepare("
            DELETE FROM empleado_capacitacion 
            WHERE id = :id AND empleado_id = :empleado_id
        ");
        $stmt->execute([':id' => $id, ':empleado_id' => $empleadoId]);
        return $stmt->rowCount() > 0;
    }
}
```

#### C. **API Routes** (lotificaciones-api/routes/api.php)
Add these routes:
```php
// Training routes
$router->get('/api/empleados/{id}/training', 'TrainingController@getByEmpleado');
$router->post('/api/empleados/{id}/training', 'TrainingController@create');
$router->put('/api/empleados/{id}/training', 'TrainingController@bulkUpdate');
$router->delete('/api/empleados/{id}/training/{trainingId}', 'TrainingController@delete');
```

#### D. **Frontend Model** (lotificaciones/app/Models/EmpleadoModel.php)
Add these methods:
```php
public function getTrainingByEmpleado($empleadoId)
{
    $stmt = $this->db->prepare("
        SELECT * FROM empleado_capacitacion 
        WHERE empleado_id = :empleado_id 
        ORDER BY fecha_aprobado DESC
    ");
    $stmt->execute([':empleado_id' => $empleadoId]);
    return $stmt->fetchAll(\PDO::FETCH_ASSOC);
}

public function saveTraining($empleadoId, $trainingData)
{
    // Delete existing
    $stmt = $this->db->prepare("DELETE FROM empleado_capacitacion WHERE empleado_id = :empleado_id");
    $stmt->execute([':empleado_id' => $empleadoId]);
    
    // Insert new
    if (!empty($trainingData)) {
        $stmt = $this->db->prepare("
            INSERT INTO empleado_capacitacion 
            (empleado_id, nombre_curso, fecha_aprobado, recursos_aprobados, comentarios)
            VALUES (?, ?, ?, ?, ?)
        ");
        
        foreach ($trainingData as $training) {
            $stmt->execute([
                $empleadoId,
                $training['nombre'],
                $training['fecha'],
                $training['recursos'] ?? 0,
                $training['comentarios'] ?? null
            ]);
        }
    }
    
    return true;
}
```

#### E. **Frontend Controller - Load Training** (lotificaciones/app/Controllers/EmpleadoController.php)
Update `edit()` method:
```php
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
```

#### F. **Frontend Controller - Save Training** (lotificaciones/app/Controllers/EmpleadoController.php)
Update `update()` method to process training_data:
```php
public function update($id)
{
    try {
        $data = $_POST;
        
        // Extract training data if present
        $trainingData = null;
        if (isset($data['training_data'])) {
            $trainingData = json_decode($data['training_data'], true);
            unset($data['training_data']); // Remove from main data
        }
        
        if ($this->useApi) {
            // Update employee via API
            $response = $this->api->put("/empleados/" . (int)$id, $data, $_FILES);
            
            // Update training via API
            if ($trainingData !== null) {
                $this->api->put("/empleados/" . (int)$id . "/training", ['training_data' => $trainingData]);
            }
        } else {
            // Update using model
            $model = new EmpleadoModel();
            $result = $model->update((int)$id, $data, $_FILES);
            
            // Save training data
            if ($trainingData !== null) {
                $model->saveTraining((int)$id, $trainingData);
            }
        }
        
        // ... rest of update logic
    } catch (\Exception $e) {
        // ... error handling
    }
}
```

### 2. **View Page Integration** ⚠️
The training tab should also appear in `empleado_view.php` (read-only mode).

**Required:** Copy training tab HTML to view page, remove form, show table only.

---

## Testing After Integration

### Test API Mode:
1. Set `USE_API=true` in `.env`
2. Edit employee
3. Add training courses
4. Save employee
5. Refresh page
6. Verify training courses loaded from API

### Test Direct Mode:
1. Set `USE_API=false` in `.env`
2. Repeat above test
3. Verify training courses saved to database

### Test Language Switching:
1. Add training course in Spanish
2. Switch to English
3. Verify all labels translated
4. Verify alerts in English

### Test Theme Switching:
1. Add training courses
2. Switch between light/dark themes
3. Verify colors adapt correctly

---

## Priority Order

1. **HIGH**: API endpoints and models (critical for API mode)
2. **HIGH**: Controller integration for load/save
3. **MEDIUM**: View page integration
4. **LOW**: Additional enhancements (inline editing, drag-drop reorder, etc.)

---

## Files to Create/Modify

### New Files:
- `lotificaciones-api/app/Controllers/TrainingController.php`
- `lotificaciones-api/app/Models/TrainingModel.php`

### Modified Files:
- `lotificaciones-api/routes/api.php` (add training routes)
- `lotificaciones/app/Models/EmpleadoModel.php` (add training methods)
- `lotificaciones/app/Controllers/EmpleadoController.php` (load/save training)
- `lotificaciones/app/views/empleado_view.php` (add read-only training tab)
- ✅ `lotificaciones/public/assets/i18n/es.json` (DONE)
- ✅ `lotificaciones/public/assets/i18n/en.json` (DONE)
- ✅ `lotificaciones/app/views/empleado_edit.php` (DONE - i18n integration)

---

## Current Status Summary

| Feature | Status | Notes |
|---------|--------|-------|
| UI Implementation | ✅ Complete | Form, table, CRUD working |
| i18n Support | ✅ Complete | All strings translatable |
| Theme Compatibility | ✅ Complete | Uses standard Bootstrap classes |
| Client-side Logic | ✅ Complete | Add/Edit/Delete functional |
| API Endpoints | ❌ Not Started | Critical for API mode |
| Database Models | ❌ Not Started | Required for persistence |
| Controller Integration | ❌ Not Started | Load/save logic missing |
| View Page | ❌ Not Started | Read-only display missing |

---

**Next Step:** Implement API endpoints and models to enable persistence.
