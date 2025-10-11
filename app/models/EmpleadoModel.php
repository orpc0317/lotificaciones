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
            die("Error de conexión: " . $e->getMessage());
        }
    }

    public function getAll()
    {
        $stmt = $this->db->query("SELECT id, codigo, nombres, apellidos, fecha_nacimiento, edad, foto, puesto_id, departamento_id, genero, comentarios FROM empleados ORDER BY id DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($data, $files)
    {
        $foto = null;
        if (!empty($files['foto']['name'])) {
            $foto = uniqid() . '_' . basename($files['foto']['name']);
            move_uploaded_file($files['foto']['tmp_name'], __DIR__ . '/../../public/uploads/' . $foto);
        }

        $stmt = $this->db->prepare("INSERT INTO empleados (codigo, nombres, apellidos, fecha_nacimiento, edad, foto, puesto_id, departamento_id, genero, comentarios)
            VALUES (:codigo, :nombres, :apellidos, :fecha_nacimiento, :edad, :foto, :puesto_id, :departamento_id, :genero, :comentarios)");

        return $stmt->execute([
            ':codigo' => uniqid('EMP'),
            ':nombres' => $data['nombres'],
            ':apellidos' => $data['apellidos'],
            ':fecha_nacimiento' => $data['fecha_nacimiento'],
            ':edad' => $this->calcularEdad($data['fecha_nacimiento']),
            ':foto' => $foto,
            ':puesto_id' => $data['puesto_id'],
            ':departamento_id' => $data['departamento_id'],
            ':genero' => $data['genero'],
            ':comentarios' => $data['comentarios']
        ]);
    }

    public function update($data, $files)
    {
        $foto = $data['foto_actual'] ?? null;
        if (!empty($files['foto']['name'])) {
            $foto = uniqid() . '_' . basename($files['foto']['name']);
            move_uploaded_file($files['foto']['tmp_name'], __DIR__ . '/../../public/uploads/' . $foto);
        }

        $stmt = $this->db->prepare("UPDATE empleados SET nombres = :nombres, apellidos = :apellidos, fecha_nacimiento = :fecha_nacimiento, edad = :edad, foto = :foto, puesto_id = :puesto_id, departamento_id = :departamento_id, genero = :genero, comentarios = :comentarios WHERE id = :id");

        return $stmt->execute([
            ':id' => $data['id'],
            ':nombres' => $data['nombres'],
            ':apellidos' => $data['apellidos'],
            ':fecha_nacimiento' => $data['fecha_nacimiento'],
            ':edad' => $this->calcularEdad($data['fecha_nacimiento']),
            ':foto' => $foto,
            ':puesto_id' => $data['puesto_id'],
            ':departamento_id' => $data['departamento_id'],
            ':genero' => $data['genero'],
            ':comentarios' => $data['comentarios']
        ]);
    }

    public function delete($id)
    {
        $stmt = $this->db->prepare("DELETE FROM empleados WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    private function calcularEdad($fecha)
    {
        $nacimiento = new \DateTime($fecha);
        $hoy = new \DateTime();
        return $hoy->diff($nacimiento)->y;
    }
}
