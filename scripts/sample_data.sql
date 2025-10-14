-- Sample data for lotificaciones
-- WARNING: run this once. It creates tables if they don't exist and inserts sample rows.

USE `lotificaciones`;

-- Table: puestos
CREATE TABLE IF NOT EXISTS `puestos` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: departamentos
CREATE TABLE IF NOT EXISTS `departamentos` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: empleados
CREATE TABLE IF NOT EXISTS `empleados` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `codigo` VARCHAR(100) NOT NULL,
  `nombres` VARCHAR(255) NOT NULL,
  `apellidos` VARCHAR(255) NOT NULL,
  `fecha_nacimiento` DATE DEFAULT NULL,
  `edad` INT DEFAULT NULL,
  `foto` VARCHAR(255) DEFAULT NULL,
  `puesto_id` INT DEFAULT NULL,
  `departamento_id` INT DEFAULT NULL,
  `genero` VARCHAR(50) DEFAULT NULL,
  `comentarios` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert sample puestos
INSERT INTO `puestos` (`nombre`) VALUES
('Desarrollador'),
('Analista'),
('Soporte')
;

-- Insert sample departamentos
INSERT INTO `departamentos` (`nombre`) VALUES
('Sistemas'),
('Recursos Humanos'),
('Administración')
;

-- Insert sample empleados
INSERT INTO `empleados` (`codigo`, `nombres`, `apellidos`, `fecha_nacimiento`, `edad`, `foto`, `puesto_id`, `departamento_id`, `genero`, `comentarios`) VALUES
('EMP001', 'Juan', 'Pérez', '1990-05-10', 35, NULL, 1, 1, 'Masculino', 'Empleado de prueba'),
('EMP002', 'María', 'Gómez', '1988-04-20', 37, NULL, 2, 2, 'Femenino', 'Registro de prueba'),
('EMP003', 'Carlos', 'López', '1995-12-01', 29, NULL, 3, 3, 'Masculino', 'Otro registro')
;

-- Notes:
-- If you run this multiple times you may create duplicate sample records.
-- Prefer running once via phpMyAdmin import or MySQL CLI:
-- mysql -u root -p lotificaciones < scripts/sample_data.sql
