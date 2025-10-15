-- Add email, telefono, direccion, ciudad fields to empleados table
-- Run this script to update the database schema

USE `lotificaciones`;

-- Add new contact and address fields
ALTER TABLE `empleados`
ADD COLUMN `email` VARCHAR(255) DEFAULT NULL AFTER `genero`,
ADD COLUMN `telefono` VARCHAR(50) DEFAULT NULL AFTER `email`,
ADD COLUMN `direccion` TEXT DEFAULT NULL AFTER `telefono`,
ADD COLUMN `ciudad` VARCHAR(100) DEFAULT NULL AFTER `direccion`;

-- Optional: Update sample data with test values
UPDATE `empleados` 
SET 
  `email` = CONCAT(LOWER(SUBSTRING_INDEX(nombres, ' ', 1)), '.', LOWER(SUBSTRING_INDEX(apellidos, ' ', 1)), '@example.com'),
  `telefono` = CONCAT('555-', LPAD(id * 1000, 4, '0')),
  `direccion` = CONCAT('Calle ', id, ', Zona ', FLOOR(id / 2) + 1),
  `ciudad` = CASE 
    WHEN id % 3 = 0 THEN 'Guatemala'
    WHEN id % 3 = 1 THEN 'Antigua'
    ELSE 'Quetzaltenango'
  END
WHERE id IS NOT NULL;
