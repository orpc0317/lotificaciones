<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Empleados - Lotificaciones</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- DataTables -->
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <!-- DataTables Buttons -->
    <link href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css" rel="stylesheet">
    <!-- Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="/assets/css/style.css" rel="stylesheet">
</head>

<body class="bg-light text-dark" id="body">

    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-people-fill me-2"></i>Gestión de Empleados</h2>
            <button class="btn btn-outline-dark" id="toggleDarkMode"><i class="bi bi-moon-stars-fill"></i></button>
        </div>

        <div class="row">
            <!-- Formulario -->
            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <strong>Nuevo Empleado</strong>
                    </div>
                    <div class="card-body">
                        <form id="formEmpleado" enctype="multipart/form-data">
                            <input type="hidden" name="id" id="id">
                            <input type="hidden" name="foto_actual" id="foto_actual">

                            <div class="mb-2">
                                <label>Nombres</label>
                                <input type="text" name="nombres" id="nombres" class="form-control" required>
                            </div>
                            <div class="mb-2">
                                <label>Apellidos</label>
                                <input type="text" name="apellidos" id="apellidos" class="form-control" required>
                            </div>
                            <div class="mb-2">
                                <label>Fecha Nacimiento</label>
                                <input type="date" name="fecha_nacimiento" id="fecha_nacimiento" class="form-control" required>
                            </div>
                            <div class="mb-2">
                                <label>Edad</label>
                                <input type="text" id="edad" class="form-control" readonly>
                            </div>
                            <div class="mb-2">
                                <label>Foto</label>
                                <input type="file" name="foto" id="foto" class="form-control">
                            </div>
                            <div class="mb-2">
                                <label>Puesto</label>
                                <input type="text" name="puesto_id" id="puesto_id" class="form-control" placeholder="Buscar puesto..." required>
                            </div>
                            <div class="mb-2">
                                <label>Departamento</label>
                                <select name="departamento_id" id="departamento_id" class="form-select" required>
                                    <option value="">Seleccione</option>
                                </select>
                            </div>
                            <div class="mb-2">
                                <label>Género</label><br>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="genero" id="hombre" value="Hombre" required>
                                    <label class="form-check-label" for="hombre">Hombre</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="genero" id="mujer" value="Mujer">
                                    <label class="form-check-label" for="mujer">Mujer</label>
                                </div>
                            </div>
                            <div class="mb-2">
                                <label>Comentarios</label>
                                <textarea name="comentarios" id="comentarios" class="form-control"></textarea>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-success"><i class="bi bi-person-plus-fill me-1"></i>Crear</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Tabla -->
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                        <strong>Listado de Empleados</strong>
                        <div id="exportButtons" class="btn-group"></div>
                    </div>
                    <div class="card-body">
                        <table id="tablaEmpleados" class="table table-striped table-hover w-100">
                            <thead>
                                <tr>
                                    <th>Foto</th>
                                    <th>Código</th>
                                    <th>Nombre</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <!-- DataTables Buttons -->
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="/assets/js/empleados.js"></script>
</body>

</html>