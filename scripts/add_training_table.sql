-- Add Training/Courses table for employee training records
-- This creates a detail table for master-detail functionality

CREATE TABLE IF NOT EXISTS empleado_capacitacion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    empleado_id INT NOT NULL,
    nombre_curso VARCHAR(200) NOT NULL,
    fecha_aprobado DATE NOT NULL,
    recursos_aprobados DECIMAL(10,2) DEFAULT 0.00,
    comentarios TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (empleado_id) REFERENCES empleados(id) ON DELETE CASCADE,
    INDEX idx_empleado (empleado_id),
    INDEX idx_fecha (fecha_aprobado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add some sample data (optional)
INSERT INTO empleado_capacitacion (empleado_id, nombre_curso, fecha_aprobado, recursos_aprobados, comentarios) VALUES
(1, 'Seguridad Industrial Básica', '2024-01-15', 250.00, 'Curso obligatorio completado satisfactoriamente'),
(1, 'Primeros Auxilios', '2024-03-10', 180.00, 'Certificación vigente por 2 años'),
(2, 'Manejo de Equipos Pesados', '2024-02-20', 500.00, 'Incluye certificación oficial'),
(2, 'Prevención de Riesgos Laborales', '2024-04-05', 150.00, NULL);
