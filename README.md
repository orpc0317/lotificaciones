# 🧑‍💼 Módulo de Empleados - Lotificaciones

Este módulo permite gestionar empleados de forma moderna y eficiente, con una interfaz visual tipo DevExpress, exportaciones avanzadas, y arquitectura MVC limpia.

---

## 🚀 Características

- Formulario de creación y edición de empleados
- Tabla dinámica con AJAX y DataTables
- Exportación a CSV, Excel, Copiar e Imprimir
- Modo oscuro elegante
- Ficha detallada del empleado en modal
- Backend modular con PHP MVC
- Subida de fotos con almacenamiento local
- Cálculo automático de edad
- Preparado para generación de PDF

---

## 📁 Estructura del proyecto

---

## ⚙️ Requisitos

- PHP 8+
- MySQL
- Composer
- Servidor Apache con mod_rewrite

---

## 🛠️ Instalación

1. Clonar el repositorio
2. Ejecutar `composer install`
3. Crear la base de datos `lotificaciones`
4. Importar la tabla `empleados`:

```sql
CREATE TABLE empleados (
  id INT AUTO_INCREMENT PRIMARY KEY,
  codigo VARCHAR(20),
  nombres VARCHAR(100),
  apellidos VARCHAR(100),
  fecha_nacimiento DATE,
  edad INT,
  foto VARCHAR(255),
  puesto_id VARCHAR(100),
  departamento_id INT,
  genero VARCHAR(10),
  comentarios TEXT
);