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

---

## 🧩 Scaffolder (generador de módulos)

Se ha incluido un scaffolder ligero para generar módulos basados en plantillas. Está pensado para acelerar la creación de formularios y vistas que sigan el patrón del módulo `Empleado`.

Ubicación: `scripts/scaffold_module.php` — plantillas en `scripts/templates/module/`

Uso (interactivo):

```powershell
php scripts/scaffold_module.php ModuleName
```

Uso (no interactivo):

```powershell
php scripts/scaffold_module.php ModuleName --fields-file=scripts/samples/example_fields.json --yes
```

Flags:
- `--storage=api|db` — estrategia de almacenamiento (por defecto: `api`).
- `--fields-file=path` — JSON con un array de objetos {"name","type"} (ej.: `scripts/samples/example_fields.json`).
- `--yes` / `-y` — ejecutar sin prompts (usa valores por defecto).

La documentación específica del scaffolder se encuentra en `scripts/SCaffolder_README.md`.

Ejemplo rápido:

```powershell
php scripts/scaffold_module.php Product --fields-file=scripts/samples/example_fields.json --yes
```

Esto generará archivos en:
- `app/Controllers/ProductController.php`
- `app/Models/ProductModel.php`
- `app/views/product.php`
- `public/assets/js/product.js`

Notas:
- Las plantillas actuales incluyen una modal con foto a la izquierda y campos a la derecha, además de un exportador XLSX (SheetJS) como alternativa.
- El scaffolder no sobrescribe archivos existentes; los saltará si ya existen.