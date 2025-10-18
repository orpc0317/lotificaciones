<?php

namespace App\Models;

use PDO;
use Exception;

class EmpleadoModel
{
    private $db;

    public function __construct()
    {
        $env = parse_ini_file(__DIR__ . '/../../config/.env');
        $dsn = "mysql:host={$env['DB_HOST']};dbname={$env['DB_NAME']};charset=utf8mb4";

        try {
            $this->db = new PDO($dsn, $env['DB_USER'], $env['DB_PASS']);
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (Exception $e) {
            // Lanzamos la excepción para que el controlador la maneje y retorne JSON de error
            throw new Exception('Error de conexión a la base de datos: ' . $e->getMessage(), 0, $e);
        }
    }

    public function getAll()
    {
        // Include puesto and departamento names so the front-end can show friendly labels
        $stmt = $this->db->query("SELECT e.*, p.nombre as puesto_nombre, d.nombre as departamento_nombre FROM empleados e LEFT JOIN puestos p ON e.puesto_id = p.id LEFT JOIN departamentos d ON e.departamento_id = d.id ORDER BY e.id DESC");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        // Añadir campo thumbnail si el archivo existe
        foreach ($rows as &$r) {
            $r['thumbnail'] = null;
            if (!empty($r['foto']) && file_exists(__DIR__ . '/../../public/uploads/thumbs/' . $r['foto'])) {
                $r['thumbnail'] = 'uploads/thumbs/' . $r['foto'];
            } elseif (!empty($r['foto']) && file_exists(__DIR__ . '/../../public/uploads/' . $r['foto'])) {
                // si no hay thumb, usar la imagen original
                $r['thumbnail'] = 'uploads/' . $r['foto'];
            }
        }
        return $rows;
    }

    /**
     * Get paginated data for server-side DataTables processing
     * @param array $params - DataTables request parameters (start, length, search, order, etc.)
     * @return array - Formatted response for DataTables
     */
    public function getServerSide($params = [])
    {
        // Extract parameters
        $start = isset($params['start']) ? (int)$params['start'] : 0;
        $length = isset($params['length']) ? (int)$params['length'] : 10;
        $searchValue = isset($params['search']['value']) ? trim($params['search']['value']) : '';
        $orderColumnIndex = isset($params['order'][0]['column']) ? (int)$params['order'][0]['column'] : 0;
        $orderDir = isset($params['order'][0]['dir']) && $params['order'][0]['dir'] === 'asc' ? 'ASC' : 'DESC';

        // Extract filter parameters
        $filterId = isset($params['filter_id']) && $params['filter_id'] !== '' ? (int)$params['filter_id'] : null;
        $filterNombres = isset($params['filter_nombres']) && $params['filter_nombres'] !== '' ? trim($params['filter_nombres']) : '';
        $filterApellidos = isset($params['filter_apellidos']) && $params['filter_apellidos'] !== '' ? trim($params['filter_apellidos']) : '';
        $filterGenero = isset($params['filter_genero']) && $params['filter_genero'] !== '' ? trim($params['filter_genero']) : '';
        $filterDepartamento = isset($params['filter_departamento']) && $params['filter_departamento'] !== '' ? (int)$params['filter_departamento'] : null;
        $filterPuesto = isset($params['filter_puesto']) && $params['filter_puesto'] !== '' ? (int)$params['filter_puesto'] : null;
        $filterEdadMin = isset($params['filter_edad_min']) && $params['filter_edad_min'] !== '' ? (int)$params['filter_edad_min'] : null;
        $filterEdadMax = isset($params['filter_edad_max']) && $params['filter_edad_max'] !== '' ? (int)$params['filter_edad_max'] : null;
        $filterFechaNacimientoDesde = isset($params['filter_fecha_nacimiento_desde']) && $params['filter_fecha_nacimiento_desde'] !== '' ? trim($params['filter_fecha_nacimiento_desde']) : '';
        $filterFechaNacimientoHasta = isset($params['filter_fecha_nacimiento_hasta']) && $params['filter_fecha_nacimiento_hasta'] !== '' ? trim($params['filter_fecha_nacimiento_hasta']) : '';

        // Column mapping (based on DataTables column order)
        $columns = ['e.id', 'e.foto', 'e.codigo', 'e.nombres', 'e.apellidos', 'e.edad', 'e.fecha_nacimiento', 
                    'e.genero', 'p.nombre', 'd.nombre', 'e.email', 'e.telefono', 'e.direccion', 'e.ciudad', 'e.comentarios'];
        
        $orderColumn = isset($columns[$orderColumnIndex]) ? $columns[$orderColumnIndex] : 'e.id';

        // Base query
        $baseQuery = "FROM empleados e 
                      LEFT JOIN puestos p ON e.puesto_id = p.id 
                      LEFT JOIN departamentos d ON e.departamento_id = d.id";

        // Build WHERE clause
        $conditions = [];
        $params_bind = [];

        // Global search
        if ($searchValue !== '') {
            $conditions[] = "(e.codigo LIKE :search OR e.nombres LIKE :search OR e.apellidos LIKE :search 
                            OR e.email LIKE :search OR e.telefono LIKE :search OR e.ciudad LIKE :search 
                            OR p.nombre LIKE :search OR d.nombre LIKE :search)";
            $params_bind[':search'] = '%' . $searchValue . '%';
        }

        // Filter by ID (exact match)
        if ($filterId !== null) {
            $conditions[] = "e.id = :filter_id";
            $params_bind[':filter_id'] = $filterId;
        }

        // Filter by nombres (LIKE)
        if ($filterNombres !== '') {
            $conditions[] = "e.nombres LIKE :filter_nombres";
            $params_bind[':filter_nombres'] = '%' . $filterNombres . '%';
        }

        // Filter by apellidos (LIKE)
        if ($filterApellidos !== '') {
            $conditions[] = "e.apellidos LIKE :filter_apellidos";
            $params_bind[':filter_apellidos'] = '%' . $filterApellidos . '%';
        }

        // Filter by genero (exact match)
        if ($filterGenero !== '') {
            $conditions[] = "e.genero = :filter_genero";
            $params_bind[':filter_genero'] = $filterGenero;
        }

        // Filter by departamento (exact match)
        if ($filterDepartamento !== null) {
            $conditions[] = "e.departamento_id = :filter_departamento";
            $params_bind[':filter_departamento'] = $filterDepartamento;
        }

        // Filter by puesto (exact match)
        if ($filterPuesto !== null) {
            $conditions[] = "e.puesto_id = :filter_puesto";
            $params_bind[':filter_puesto'] = $filterPuesto;
        }

        // Filter by edad (age range logic)
        // If only "from" is provided: exact match
        // If only "to" is provided: ignore filter
        // If both provided: range filter
        if ($filterEdadMin !== null && $filterEdadMax === null) {
            // Only "from" provided - exact match
            $conditions[] = "e.edad = :filter_edad_exact";
            $params_bind[':filter_edad_exact'] = $filterEdadMin;
        } elseif ($filterEdadMin !== null && $filterEdadMax !== null) {
            // Both provided - range filter
            $conditions[] = "e.edad >= :filter_edad_min";
            $conditions[] = "e.edad <= :filter_edad_max";
            $params_bind[':filter_edad_min'] = $filterEdadMin;
            $params_bind[':filter_edad_max'] = $filterEdadMax;
        }
        // If only "to" is provided (filterEdadMax !== null && filterEdadMin === null): ignore

        // Filter by fecha_nacimiento (birth date range logic)
        // If only "from" is provided: exact match
        // If only "to" is provided: ignore filter
        // If both provided: range filter
        if ($filterFechaNacimientoDesde !== '' && $filterFechaNacimientoHasta === '') {
            // Only "from" provided - exact match
            $conditions[] = "e.fecha_nacimiento = :filter_fecha_nacimiento_exact";
            $params_bind[':filter_fecha_nacimiento_exact'] = $filterFechaNacimientoDesde;
        } elseif ($filterFechaNacimientoDesde !== '' && $filterFechaNacimientoHasta !== '') {
            // Both provided - range filter
            $conditions[] = "e.fecha_nacimiento >= :filter_fecha_nacimiento_desde";
            $conditions[] = "e.fecha_nacimiento <= :filter_fecha_nacimiento_hasta";
            $params_bind[':filter_fecha_nacimiento_desde'] = $filterFechaNacimientoDesde;
            $params_bind[':filter_fecha_nacimiento_hasta'] = $filterFechaNacimientoHasta;
        }
        // If only "to" is provided (filterFechaNacimientoHasta !== '' && filterFechaNacimientoDesde === ''): ignore

        // Construct WHERE clause
        $whereClause = !empty($conditions) ? ' WHERE ' . implode(' AND ', $conditions) : '';

        // Count total records (without filtering)
        $stmtTotal = $this->db->query("SELECT COUNT(*) as total FROM empleados");
        $totalRecords = $stmtTotal->fetch(\PDO::FETCH_ASSOC)['total'];

        // Count filtered records
        $stmtFiltered = $this->db->prepare("SELECT COUNT(*) as total $baseQuery $whereClause");
        foreach ($params_bind as $key => $value) {
            $stmtFiltered->bindValue($key, $value);
        }
        $stmtFiltered->execute();
        $totalFiltered = $stmtFiltered->fetch(\PDO::FETCH_ASSOC)['total'];

        // Get paginated data
        $sql = "SELECT e.*, p.nombre as puesto_nombre, d.nombre as departamento_nombre 
                $baseQuery 
                $whereClause 
                ORDER BY $orderColumn $orderDir 
                LIMIT :start, :length";
        
        $stmt = $this->db->prepare($sql);
        foreach ($params_bind as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':start', $start, \PDO::PARAM_INT);
        $stmt->bindValue(':length', $length, \PDO::PARAM_INT);
        $stmt->execute();
        
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Add thumbnail paths
        foreach ($rows as &$r) {
            $r['thumbnail'] = null;
            if (!empty($r['foto']) && file_exists(__DIR__ . '/../../public/uploads/thumbs/' . $r['foto'])) {
                $r['thumbnail'] = 'uploads/thumbs/' . $r['foto'];
            } elseif (!empty($r['foto']) && file_exists(__DIR__ . '/../../public/uploads/' . $r['foto'])) {
                $r['thumbnail'] = 'uploads/' . $r['foto'];
            }
        }

        return [
            'draw' => isset($params['draw']) ? (int)$params['draw'] : 1,
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalFiltered,
            'data' => $rows
        ];
    }

    // Obtener lista de puestos (id, nombre)
    public function getPuestos()
    {
        try {
            $stmt = $this->db->query("SELECT id, nombre FROM puestos ORDER BY nombre");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            // Si no existe la tabla, retornar array vacío
            return [];
        }
    }

    // Obtener lista de departamentos (id, nombre)
    public function getDepartamentos()
    {
        try {
            $stmt = $this->db->query("SELECT id, nombre FROM departamentos ORDER BY nombre");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    // Devuelve el conteo de filas en la tabla empleados
    public function countAll()
    {
        $stmt = $this->db->query("SELECT COUNT(*) as c FROM empleados");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return isset($row['c']) ? (int)$row['c'] : 0;
    }

    // Devuelve el nombre de la base de datos conectada (útil para depuración)
    public function getDatabaseName()
    {
        $stmt = $this->db->query("SELECT DATABASE() as db");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['db'] ?? null;
    }

    // Obtener empleado por id con nombre de puesto y departamento
    public function getById($id)
    {
        $stmt = $this->db->prepare("SELECT e.*, p.nombre as puesto_nombre, d.nombre as departamento_nombre FROM empleados e LEFT JOIN puestos p ON e.puesto_id = p.id LEFT JOIN departamentos d ON e.departamento_id = d.id WHERE e.id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $row['thumbnail'] = null;
            if (!empty($row['foto']) && file_exists(__DIR__ . '/../../public/uploads/thumbs/' . $row['foto'])) {
                $row['thumbnail'] = 'uploads/thumbs/' . $row['foto'];
            } elseif (!empty($row['foto']) && file_exists(__DIR__ . '/../../public/uploads/' . $row['foto'])) {
                $row['thumbnail'] = 'uploads/' . $row['foto'];
            }
        }
        return $row;
    }

    public function create($data, $files)
    {
        $foto = null;
        if (!empty($files['foto']['name'])) {
            // Validar y verificar imagen (no confiar en extensión)
            $validated = $this->validateUploadedImage($files['foto']);
            if ($validated === false) {
                throw new Exception('El archivo no es una imagen válida');
            }

            $foto = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', basename($files['foto']['name']));
            $dest = __DIR__ . '/../../public/uploads/' . $foto;
            if (!move_uploaded_file($files['foto']['tmp_name'], $dest)) {
                throw new Exception('No se pudo guardar la imagen');
            }
            // Generar thumbnail (si GD disponible)
            $this->createThumbnail($dest, __DIR__ . '/../../public/uploads/thumbs/' . $foto, 120, 120);
        }

        $stmt = $this->db->prepare("INSERT INTO empleados (codigo, nombres, apellidos, fecha_nacimiento, edad, foto, puesto_id, departamento_id, genero, email, telefono, direccion, ciudad, comentarios)
            VALUES (:codigo, :nombres, :apellidos, :fecha_nacimiento, :edad, :foto, :puesto_id, :departamento_id, :genero, :email, :telefono, :direccion, :ciudad, :comentarios)");

        return $stmt->execute([
            ':codigo' => uniqid('EMP'),
            ':nombres' => $data['nombres'],
            ':apellidos' => $data['apellidos'],
            ':fecha_nacimiento' => $data['fecha_nacimiento'],
            ':edad' => $this->calcularEdad($data['fecha_nacimiento']),
            ':foto' => $foto,
            ':puesto_id' => (isset($data['puesto_id']) && $data['puesto_id'] !== '') ? $data['puesto_id'] : null,
            ':departamento_id' => (isset($data['departamento_id']) && $data['departamento_id'] !== '') ? $data['departamento_id'] : null,
            ':genero' => $data['genero'],
            ':email' => $data['email'] ?? null,
            ':telefono' => $data['telefono'] ?? null,
            ':direccion' => $data['direccion'] ?? null,
            ':ciudad' => $data['ciudad'] ?? null,
            ':comentarios' => $data['comentarios']
        ]);
    }

    public function update($data, $files)
    {
        $foto = $data['foto_actual'] ?? null;
        if (!empty($files['foto']['name'])) {
            // Validar y verificar imagen (no confiar en extensión)
            $validated = $this->validateUploadedImage($files['foto']);
            if ($validated === false) {
                throw new Exception('El archivo no es una imagen válida');
            }

            // eliminar archivos antiguos (si existen)
            if (!empty($foto)) {
                $old = __DIR__ . '/../../public/uploads/' . $foto;
                $oldThumb = __DIR__ . '/../../public/uploads/thumbs/' . $foto;
                if (file_exists($old)) @unlink($old);
                if (file_exists($oldThumb)) @unlink($oldThumb);
            }

            $foto = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', basename($files['foto']['name']));
            $dest = __DIR__ . '/../../public/uploads/' . $foto;
            if (!move_uploaded_file($files['foto']['tmp_name'], $dest)) {
                throw new Exception('No se pudo guardar la imagen');
            }
            $this->createThumbnail($dest, __DIR__ . '/../../public/uploads/thumbs/' . $foto, 120, 120);
        }

        $stmt = $this->db->prepare("UPDATE empleados SET nombres = :nombres, apellidos = :apellidos, fecha_nacimiento = :fecha_nacimiento, edad = :edad, foto = :foto, puesto_id = :puesto_id, departamento_id = :departamento_id, genero = :genero, email = :email, telefono = :telefono, direccion = :direccion, ciudad = :ciudad, comentarios = :comentarios WHERE id = :id");
        $params = [
            ':id' => $data['id'],
            ':nombres' => $data['nombres'] ?? null,
            ':apellidos' => $data['apellidos'] ?? null,
            ':fecha_nacimiento' => $data['fecha_nacimiento'] ?? null,
            ':edad' => isset($data['fecha_nacimiento']) ? $this->calcularEdad($data['fecha_nacimiento']) : null,
            ':foto' => $foto,
            ':puesto_id' => (isset($data['puesto_id']) && $data['puesto_id'] !== '') ? $data['puesto_id'] : null,
            ':departamento_id' => (isset($data['departamento_id']) && $data['departamento_id'] !== '') ? $data['departamento_id'] : null,
            ':genero' => $data['genero'] ?? null,
            ':email' => $data['email'] ?? null,
            ':telefono' => $data['telefono'] ?? null,
            ':direccion' => $data['direccion'] ?? null,
            ':ciudad' => $data['ciudad'] ?? null,
            ':comentarios' => $data['comentarios'] ?? null
        ];

        return $stmt->execute($params);
    }

    /**
     * Validate an uploaded image file more strictly.
     * Returns an array with 'mime' and 'ext' on success, or false on failure.
     */
    private function validateUploadedImage(array $file)
    {
        if (empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            // allow direct local testing where is_uploaded_file may be false, so also accept existing tmp_name
            if (empty($file['tmp_name']) || !file_exists($file['tmp_name'])) {
                return false;
            }
        }

        $maxBytes = 2 * 1024 * 1024; // 2MB
        if (!empty($file['size']) && $file['size'] > $maxBytes) {
            return false;
        }

        // Use finfo to get MIME type (more reliable than extension)
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
        } else {
            $mime = null;
        }

        $imageInfo = @getimagesize($file['tmp_name']);
        $mimeFromGetImage = $imageInfo['mime'] ?? null;

        // Prefer finfo result but ensure it matches getimagesize if available
        $detectedMime = $mime ?: $mimeFromGetImage;
        $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif'];
        if (empty($detectedMime) || !array_key_exists($detectedMime, $allowed)) {
            return false;
        }

        // Now try to actually create an image resource to be extra sure
        $type = $imageInfo[2] ?? null; // IMAGETYPE_*
        $ok = false;
        switch ($type) {
            case IMAGETYPE_JPEG:
                $res = @imagecreatefromjpeg($file['tmp_name']);
                $ok = $res !== false;
                break;
            case IMAGETYPE_PNG:
                $res = @imagecreatefrompng($file['tmp_name']);
                $ok = $res !== false;
                break;
            case IMAGETYPE_GIF:
                $res = @imagecreatefromgif($file['tmp_name']);
                $ok = $res !== false;
                break;
            default:
                $ok = false;
        }
    if (isset($res) && $res !== false) imagedestroy($res);
        if (!$ok) return false;

        return ['mime' => $detectedMime, 'ext' => $allowed[$detectedMime]];
    }

    public function delete($id)
    {
        // obtener foto actual para eliminar archivos
        $stmtSel = $this->db->prepare("SELECT foto FROM empleados WHERE id = :id LIMIT 1");
        $stmtSel->execute([':id' => $id]);
        $row = $stmtSel->fetch(PDO::FETCH_ASSOC);
        if ($row && !empty($row['foto'])) {
            $f = $row['foto'];
            $p1 = __DIR__ . '/../../public/uploads/' . $f;
            $p2 = __DIR__ . '/../../public/uploads/thumbs/' . $f;
            if (file_exists($p1)) @unlink($p1);
            if (file_exists($p2)) @unlink($p2);
        }

        $stmt = $this->db->prepare("DELETE FROM empleados WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    private function calcularEdad($fecha)
    {
        $nacimiento = new \DateTime($fecha);
        $hoy = new \DateTime();
        return $hoy->diff($nacimiento)->y;
    }

    // Generar thumbnail simple usando GD
    private function createThumbnail($srcPath, $destPath, $maxWidth = 120, $maxHeight = 120)
    {
        if (!extension_loaded('gd')) {
            // Intentar continuar sin thumb
            return false;
        }

        list($width, $height, $type) = @getimagesize($srcPath);
        if (!$width || !$height) return false;

        $ratio = min($maxWidth / $width, $maxHeight / $height);
        $newW = (int)($width * $ratio);
        $newH = (int)($height * $ratio);

        $thumb = imagecreatetruecolor($newW, $newH);
        switch ($type) {
            case IMAGETYPE_JPEG:
                $src = imagecreatefromjpeg($srcPath);
                break;
            case IMAGETYPE_PNG:
                $src = imagecreatefrompng($srcPath);
                // Preserve transparency for PNG
                imagecolortransparent($thumb, imagecolorallocatealpha($thumb, 0, 0, 0, 127));
                imagealphablending($thumb, false);
                imagesavealpha($thumb, true);
                break;
            case IMAGETYPE_GIF:
                $src = imagecreatefromgif($srcPath);
                break;
            default:
                return false;
        }

        imagecopyresampled($thumb, $src, 0, 0, 0, 0, $newW, $newH, $width, $height);
        // Asegurar directorio destino
        $dir = dirname($destPath);
        if (!is_dir($dir)) @mkdir($dir, 0755, true);

        switch ($type) {
            case IMAGETYPE_JPEG:
                imagejpeg($thumb, $destPath, 85);
                break;
            case IMAGETYPE_PNG:
                imagepng($thumb, $destPath);
                break;
            case IMAGETYPE_GIF:
                imagegif($thumb, $destPath);
                break;
        }
        imagedestroy($thumb);
        imagedestroy($src);
        return true;
    }

    // ==================== TRAINING METHODS ====================

    /**
     * Get all training records for an employee
     * 
     * @param int $empleadoId
     * @return array
     */
    public function getTrainingByEmpleado($empleadoId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    id,
                    empleado_id,
                    nombre_curso as nombre,
                    fecha_aprobado as fecha,
                    recursos_aprobados as recursos,
                    comentarios,
                    created_at,
                    updated_at
                FROM empleado_capacitacion 
                WHERE empleado_id = :empleado_id 
                ORDER BY fecha_aprobado DESC
            ");
            
            $stmt->execute([':empleado_id' => $empleadoId]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Format numeric values
            foreach ($results as &$row) {
                $row['recursos'] = (float) $row['recursos'];
            }
            
            return $results;
        } catch (Exception $e) {
            error_log("Error getting training data: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Save training records for an employee
     * Deletes existing records and inserts new ones
     * 
     * @param int $empleadoId
     * @param array $trainingData
     * @return bool
     */
    public function saveTraining($empleadoId, $trainingData)
    {
        try {
            // Start transaction
            $this->db->beginTransaction();
            
            // Delete existing records
            $stmt = $this->db->prepare("DELETE FROM empleado_capacitacion WHERE empleado_id = :empleado_id");
            $stmt->execute([':empleado_id' => $empleadoId]);
            
            // Insert new records
            if (!empty($trainingData) && is_array($trainingData)) {
                $stmt = $this->db->prepare("
                    INSERT INTO empleado_capacitacion 
                    (empleado_id, nombre_curso, fecha_aprobado, recursos_aprobados, comentarios)
                    VALUES (:empleado_id, :nombre_curso, :fecha_aprobado, :recursos_aprobados, :comentarios)
                ");
                
                foreach ($trainingData as $training) {
                    // Skip invalid entries
                    if (empty($training['nombre']) || empty($training['fecha'])) {
                        continue;
                    }
                    
                    $stmt->execute([
                        ':empleado_id' => $empleadoId,
                        ':nombre_curso' => $training['nombre'],
                        ':fecha_aprobado' => $training['fecha'],
                        ':recursos_aprobados' => $training['recursos'] ?? 0,
                        ':comentarios' => !empty($training['comentarios']) ? $training['comentarios'] : null
                    ]);
                }
            }
            
            // Commit transaction
            $this->db->commit();
            
            return true;
        } catch (Exception $e) {
            // Rollback on error
            $this->db->rollBack();
            error_log("Error saving training data: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Count training records for an employee
     * 
     * @param int $empleadoId
     * @return int
     */
    public function countTrainingByEmpleado($empleadoId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as total 
                FROM empleado_capacitacion 
                WHERE empleado_id = :empleado_id
            ");
            
            $stmt->execute([':empleado_id' => $empleadoId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return (int) ($result['total'] ?? 0);
        } catch (Exception $e) {
            error_log("Error counting training data: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Delete all training records for an employee
     * 
     * @param int $empleadoId
     * @return bool
     */
    public function deleteTrainingByEmpleado($empleadoId)
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM empleado_capacitacion WHERE empleado_id = :empleado_id");
            $stmt->execute([':empleado_id' => $empleadoId]);
            
            return true;
        } catch (Exception $e) {
            error_log("Error deleting training data: " . $e->getMessage());
            return false;
        }
    }

}
