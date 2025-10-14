# 🧑‍💼 Módulo de Empleados - Lotificaciones

Este módulo permite gestionar empleados de forma moderna y eficiente, con una interfaz visual tipo DevExpress, exportaciones avanzadas, y arquitectura MVC limpia.

---

````markdown
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
```

---

## Tests

Hay tests de humo ligeros disponibles en `scripts/tests/api_tests.php`.

Cómo ejecutar localmente:

1. Inicia el servidor PHP integrado (desde la raíz del proyecto):

```powershell
php -S 127.0.0.1:8000 -t public
```

2. En otra terminal ejecuta:

```powershell
php scripts/tests/api_tests.php
```

O puedes exportar `BASE_URL` si quieres probar contra tu servidor local (Apache/IIS):

```powershell
$env:BASE_URL = 'http://localhost/lotificaciones/public/'
php scripts/tests/api_tests.php
```

También se incluyó un workflow de GitHub Actions en `.github/workflows/php-tests.yml` que corre estos tests en cada push a `main`.

## Nota importante: Casing y PSR-4

Este proyecto sigue la convención PSR-4: los namespaces de PHP deben coincidir exactamente con las rutas de archivos en el repositorio, incluyendo mayúsculas/minúsculas. En entornos Linux (por ejemplo runners de CI) el sistema de ficheros es case-sensitive; en Windows no siempre lo es. Para evitar errores tipo "Class 'App\\Controllers\\EmpleadoController' not found":

- Mantén las carpetas con la misma capitalización que los namespaces (p. ej. `app/Controllers`, `app/Models`).
- Antes de hacer push puedes ejecutar la comprobación rápida incluida:

```powershell
php scripts/check_namespace_case.php
```

La comprobación fallará con código de salida distinto de 0 si detecta inconsistencias en el casing.

Si prefieres usar carpetas en minúscula, hay que mantener esa convención también en los `namespace` de los archivos PHP; en general la opción menos disruptiva es alinear carpetas con namespaces.