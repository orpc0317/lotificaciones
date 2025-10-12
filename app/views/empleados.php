<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Empleados - Lotificaciones</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Ruta base para que los assets se resuelvan correctamente -->
    <base href="/lotificaciones/public/">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- DataTables -->
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <!-- DataTables Buttons -->
    <link href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css" rel="stylesheet">
    <!-- Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="assets/css/style.css" rel="stylesheet">
</head>

<body class="bg-light">

    <div class="container-fluid py-4">
        <div class="row">
            <!-- Formulario -->
            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Nuevo Empleado</h5>
                    </div>
                    <div class="card-body">
                        <form id="formEmpleado" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="nombres" class="form-label">Nombres</label>
                                <input type="text" name="nombres" id="nombres" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="apellidos" class="form-label">Apellidos</label>
                                <input type="text" name="apellidos" id="apellidos" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="fecha_nacimiento" class="form-label">Fecha de Nacimiento</label>
                                <input type="date" name="fecha_nacimiento" id="fecha_nacimiento" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="edad" class="form-label">Edad</label>
                                <input type="text" id="edad" class="form-control" readonly>
                            </div>
                            <div class="mb-3">
                                <label for="genero" class="form-label">Género</label>
                                <select name="genero" id="genero" class="form-select" required>
                                    <option value="">Seleccione</option>
                                    <option value="Masculino">Masculino</option>
                                    <option value="Femenino">Femenino</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="puesto_id" class="form-label">Puesto</label>
                                <select name="puesto_id" id="puesto_id" class="form-select">
                                    <option value="">Seleccione</option>
                                    <?php if (!empty($puestos)): ?>
                                        <?php foreach ($puestos as $p): ?>
                                            <option value="<?= htmlspecialchars($p['id']) ?>"><?= htmlspecialchars($p['nombre']) ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="departamento_id" class="form-label">Departamento</label>
                                <select name="departamento_id" id="departamento_id" class="form-select">
                                    <option value="">Seleccione</option>
                                    <?php if (!empty($departamentos)): ?>
                                        <?php foreach ($departamentos as $d): ?>
                                            <option value="<?= htmlspecialchars($d['id']) ?>"><?= htmlspecialchars($d['nombre']) ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="foto" class="form-label">Foto</label>
                                <input type="file" name="foto" id="foto" accept="image/*" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label for="comentarios" class="form-label">Comentarios</label>
                                <textarea name="comentarios" id="comentarios" class="form-control"></textarea>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-success">Guardar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Tabla -->
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-secondary text-white d-flex align-items-center">
                        <h5 class="mb-0">Lista de Empleados</h5>
                        <i class="bi bi-info-circle ms-3" data-bs-toggle="tooltip" title="Arrastra las columnas para reordenarlas. Usa 'Columnas' para ocultar/mostrar columnas. Las exportaciones usan solo las columnas visibles."></i>
                    </div>
                    <div class="card-body">
                        <table id="tablaEmpleados" class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th></th>
                                    <th>Nombres</th>
                                    <th>Apellidos</th>
                                    <th>Edad</th>
                                    
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Se llena por AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.colVis.min.js"></script>
    <script src="https://cdn.datatables.net/colreorder/1.6.2/js/dataTables.colReorder.min.js"></script>
    <!-- SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <!-- pdf export libs -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="assets/js/empleados.js"></script>

<!-- Modal para ver ficha -->
<div class="modal fade" id="modalFicha" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header d-flex align-items-center">
                <h5 class="modal-title">Ficha de Empleado</h5>
                <div class="ms-auto me-2">
                    <button id="exportPdfBtn" type="button" class="btn btn-sm btn-outline-primary">Exportar PDF</button>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-4 text-center">
                        <img id="ficha_foto" src="uploads/placeholder.png" alt="Foto" class="img-fluid rounded" style="max-height:250px;">
                    </div>
                    <div class="col-md-8">
                        <!-- Tabs: Generals | Puesto | Others -->
                        <ul class="nav nav-tabs" id="fichaTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="ficha-generals-tab" data-bs-toggle="tab" data-bs-target="#ficha-generals" type="button" role="tab" aria-controls="ficha-generals" aria-selected="true">Generals</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="ficha-puesto-tab" data-bs-toggle="tab" data-bs-target="#ficha-puesto" type="button" role="tab" aria-controls="ficha-puesto" aria-selected="false">Puesto</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="ficha-others-tab" data-bs-toggle="tab" data-bs-target="#ficha-others" type="button" role="tab" aria-controls="ficha-others" aria-selected="false">Others</button>
                            </li>
                        </ul>
                        <div class="tab-content pt-2" id="fichaTabsContent">
                            <div class="tab-pane fade show active" id="ficha-generals" role="tabpanel" aria-labelledby="ficha-generals-tab">
                                <ul class="list-group">
                                    <li class="list-group-item"><strong>Código:</strong> <span id="ficha_codigo"></span></li>
                                    <li class="list-group-item"><strong>Nombres:</strong> <span id="ficha_nombres"></span></li>
                                    <li class="list-group-item"><strong>Apellidos:</strong> <span id="ficha_apellidos"></span></li>
                                    <li class="list-group-item"><strong>Fecha de Nacimiento:</strong> <span id="ficha_fecha_nacimiento"></span></li>
                                    <li class="list-group-item"><strong>Edad:</strong> <span id="ficha_edad"></span></li>
                                    <li class="list-group-item"><strong>Género:</strong> <span id="ficha_genero"></span></li>
                                </ul>
                            </div>
                            <div class="tab-pane fade" id="ficha-puesto" role="tabpanel" aria-labelledby="ficha-puesto-tab">
                                <ul class="list-group">
                                    <li class="list-group-item"><strong>Puesto:</strong> <span id="ficha_puesto"></span></li>
                                    <li class="list-group-item"><strong>Departamento:</strong> <span id="ficha_departamento"></span></li>
                                </ul>
                            </div>
                            <div class="tab-pane fade" id="ficha-others" role="tabpanel" aria-labelledby="ficha-others-tab">
                                <div class="card">
                                    <div class="card-body">
                                        <h6>Comentarios</h6>
                                        <div id="ficha_comentarios" style="white-space:pre-wrap;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>

<!-- Modal para editar/crear empleado -->
<div class="modal fade" id="modalEditar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Empleado</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formEditar" enctype="multipart/form-data">
                    <input type="hidden" name="id" id="edit_id">
                    <input type="hidden" name="foto_actual" id="edit_foto_actual">
                    <div class="row">
                        <div class="col-md-4 text-center">
                            <img id="edit_foto_preview" src="uploads/placeholder.png" alt="Foto" class="img-fluid rounded mb-2" style="max-height:220px;">
                            <div class="mb-3">
                                <label for="edit_foto" class="form-label">Actualizar foto</label>
                                <input type="file" name="foto" id="edit_foto" accept="image/*" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-8">
                            <!-- Tabs for edit form -->
                            <ul class="nav nav-tabs" id="editFormTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="edit-generals-tab" data-bs-toggle="tab" data-bs-target="#edit-generals" type="button" role="tab" aria-controls="edit-generals" aria-selected="true">Generals</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="edit-puesto-tab" data-bs-toggle="tab" data-bs-target="#edit-puesto" type="button" role="tab" aria-controls="edit-puesto" aria-selected="false">Puesto</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="edit-others-tab" data-bs-toggle="tab" data-bs-target="#edit-others" type="button" role="tab" aria-controls="edit-others" aria-selected="false">Others</button>
                                </li>
                            </ul>
                            <div class="tab-content pt-2" id="editFormTabsContent">
                                <div class="tab-pane fade show active" id="edit-generals" role="tabpanel" aria-labelledby="edit-generals-tab">
                                    <div class="mb-3">
                                        <label for="edit_nombres" class="form-label">Nombres</label>
                                        <input type="text" name="nombres" id="edit_nombres" class="form-control" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="edit_apellidos" class="form-label">Apellidos</label>
                                        <input type="text" name="apellidos" id="edit_apellidos" class="form-control" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="edit_fecha_nacimiento" class="form-label">Fecha de Nacimiento</label>
                                        <input type="date" name="fecha_nacimiento" id="edit_fecha_nacimiento" class="form-control">
                                    </div>
                                    <div class="mb-3">
                                        <label for="edit_genero" class="form-label">Género</label>
                                        <select name="genero" id="edit_genero" class="form-select">
                                            <option value="">Seleccione</option>
                                            <option value="Masculino">Masculino</option>
                                            <option value="Femenino">Femenino</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="edit-puesto" role="tabpanel" aria-labelledby="edit-puesto-tab">
                                    <div class="mb-3">
                                        <label for="edit_puesto_id" class="form-label">Puesto</label>
                                        <select name="puesto_id" id="edit_puesto_id" class="form-select"></select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="edit_departamento_id" class="form-label">Departamento</label>
                                        <select name="departamento_id" id="edit_departamento_id" class="form-select"></select>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="edit-others" role="tabpanel" aria-labelledby="edit-others-tab">
                                    <div class="mb-3">
                                        <label for="edit_comentarios" class="form-label">Comentarios</label>
                                        <textarea name="comentarios" id="edit_comentarios" class="form-control"></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="d-grid mt-2">
                                <button type="submit" class="btn btn-primary">Guardar cambios</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmación de borrado -->
<!-- confirmDeleteModal removed: handled by SweetAlert2 -->
    
</body>

</html>