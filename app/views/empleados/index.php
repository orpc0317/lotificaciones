<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Empleados</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="/assets/css/select2.min.css" rel="stylesheet">
    <link href="/assets/css/flatpickr.min.css" rel="stylesheet">
    <link href="/assets/css/datatables.min.css" rel="stylesheet">
    <link href="/assets/css/empleados.css" rel="stylesheet">
    <link href="/assets/css/fontawesome.min.css" rel="stylesheet">
</head>

<body class="bg-light" id="body">

    <div class="container-fluid mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2>Gestión de Empleados</h2>
            <button id="toggleDarkMode" class="btn btn-outline-dark">
                <i class="fas fa-moon"></i> Modo Oscuro
            </button>
        </div>

        <div class="row">
            <!-- Formulario -->
            <div class="col-md-4">
                <form id="formEmpleado" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label>Código</label>
                        <input type="text" class="form-control" disabled placeholder="Automático">
                    </div>
                    <div class="mb-3">
                        <label>Nombres</label>
                        <input type="text" class="form-control" name="nombres" required>
                    </div>
                    <div class="mb-3">
                        <label>Apellidos</label>
                        <input type="text" class="form-control" name="apellidos" required>
                    </div>
                    <div class="mb-3">
                        <label>Fecha Nacimiento</label>
                        <input type="text" class="form-control" id="fechaNacimiento" name="fechaNacimiento">
                        <small id="edadCalculada" class="text-muted"></small>
                    </div>
                    <div class="mb-3">
                        <label>Foto</label>
                        <input type="file" class="form-control" name="foto">
                    </div>
                    <div class="mb-3">
                        <label>Puesto</label>
                        <select class="form-control select2" name="puesto" id="puesto"></select>
                    </div>
                    <div class="mb-3">
                        <label>Departamento</label>
                        <select class="form-control" name="departamento" id="departamento"></select>
                    </div>
                    <div class="mb-3">
                        <label>Género</label><br>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="genero" value="Hombre" checked>
                            <label class="form-check-label">Hombre</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="genero" value="Mujer">
                            <label class="form-check-label">Mujer</label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label>Comentarios</label>
                        <textarea class="form-control" name="comentarios" rows="3"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-plus-circle"></i> Crear
                    </button>
                </form>
            </div>

            <!-- Grid -->
            <div class="col-md-8">
                <table id="tablaEmpleados" class="table table-striped table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th>Foto</th>
                            <th>Código</th>
                            <th>Nombre</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Se llena dinámicamente vía AJAX -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Ver/Editar -->
    <div class="modal fade" id="modalEmpleado" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Ficha del Empleado</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="contenidoEmpleado">
                    <!-- Se llena dinámicamente -->
                </div>
                <div class="modal-footer">
                    <button class="btn btn-danger" id="btnEliminar"><i class="fas fa-trash"></i> Eliminar</button>
                    <button class="btn btn-secondary" id="btnPDF"><i class="fas fa-file-pdf"></i> PDF</button>
                </div>
            </div>
        </div>
    </div>

    <script src="/assets/js/jquery.min.js"></script>
    <script src="/assets/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/select2.min.js"></script>
    <script src="/assets/js/flatpickr.min.js"></script>
    <script src="/assets/js/datatables.min.js"></script>
    <script src="/assets/js/sweetalert2.all.min.js"></script>
    <script src="/assets/js/empleados.js"></script>
</body>

</html>