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
    <!-- Modern font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="assets/css/style.css" rel="stylesheet">
    <link href="assets/css/theme.css" rel="stylesheet">
    <style>
        /* Keep small layout helpers inline while main tokens live in theme.css */
        html,body{height:100%;}
        body{background:var(--bg); color:var(--text); font-family: 'Inter', 'Segoe UI', Roboto, Arial, Helvetica, sans-serif;}
        /* smooth transitions for theme/palette changes */
        body, .card, .modal-content, .btn, .nav-tabs .nav-link { transition: background-color 220ms ease, color 220ms ease, border-color 220ms ease; }
        .card{background:var(--card-bg); border:1px solid var(--border);} 
        .card-header{background:transparent; border-bottom:1px solid var(--border);} 
        .btn-primary{background:var(--primary-600); border-color:var(--primary-600);} 
        .btn-outline-primary{color:var(--primary-600); border-color:var(--primary-600);} 
        .tab-fixed-height { overflow-y: auto; }
        .modal .modal-content { font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, Arial, Helvetica, sans-serif; }
        .modal .modal-body { padding: 1rem 1.25rem; }
        .card .card-body { padding: 0.75rem; }
        .nav-tabs .nav-link.rounded-0 { border-radius: 0 !important; }
        .list-group-item { padding: 0.5rem 0.75rem; background: transparent; border: none; }
    /* Keep tab-card headers visually minimal but do not override .section-accent headers */
    .tab-card .card-header { padding: 0; background: transparent; border-bottom: 0; }
    /* Make sure general card headers that are marked as section-accent keep their themed background */
    .card-header.section-accent { background: var(--primary-600) !important; color: #fff !important; }
        .tab-card .card-body { padding-top: 0.5rem; }
    /* dark mode toggle removed */
        .palette-swatch { width:20px; height:20px; border-radius:4px; border:2px solid transparent; cursor:pointer; display:inline-block; margin-left:8px }
        .palette-swatch.active { outline:2px solid var(--primary-600); transform:scale(1.05); }
        .nav-tabs .nav-link i { margin-right:6px; }
    </style>
    <body>
    <div class="container-fluid py-4">
        <div class="row">
            <!-- Nuevo Empleado (left column) -->
            <div class="col-md-4">
                <div class="card shadow-sm">
                        <div class="card-header section-accent d-flex align-items-center">
                        <h5 class="mb-0 section-title-on-accent"><span class="label-text" data-i18n="new_employee">Nuevo Empleado</span></h5>
                    </div>
                    <div class="card-body">
                        <form id="formEmpleado" action="empleados/create" method="post" enctype="multipart/form-data">
                            <div class="tab-card card">
                                <div class="card-header section-accent" style="padding:0;">
                                    <ul class="nav nav-tabs" id="newFormTabs" role="tablist">
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link active rounded-0" id="new-generals-tab" data-bs-toggle="tab" data-bs-target="#new-generals" type="button" role="tab" aria-controls="new-generals" aria-selected="true"><i class="bi bi-person-fill"></i> <span class="label-text" data-i18n="tab_generals">Generals</span> <span class="badge-tab ms-2" data-tab="new-generals" style="display:none;"></span></button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link rounded-0" id="new-puesto-tab" data-bs-toggle="tab" data-bs-target="#new-puesto" type="button" role="tab" aria-controls="new-puesto" aria-selected="false"><i class="bi bi-briefcase-fill"></i> <span class="label-text" data-i18n="tab_position">Puesto</span> <span class="badge-tab ms-2" data-tab="new-puesto" style="display:none;"></span></button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link rounded-0" id="new-others-tab" data-bs-toggle="tab" data-bs-target="#new-others" type="button" role="tab" aria-controls="new-others" aria-selected="false"><i class="bi bi-three-dots"></i> <span class="label-text" data-i18n="tab_others">Others</span> <span class="badge-tab ms-2" data-tab="new-others" style="display:none;"></span></button>
                                        </li>
                                    </ul>
                                </div>
                                <div class="card-body">
                                    <div class="tab-content pt-2" id="newFormTabsContent">
                                        <div class="tab-pane fade show active" id="new-generals" role="tabpanel" aria-labelledby="new-generals-tab">
                                            <div class="card">
                                                <div class="card-body">
                                                    <div class="mb-3">
                                                        <label for="codigo" class="form-label"><span class="label-text">Código</span></label>
                                                        <input type="text" name="codigo" id="codigo" class="form-control">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="nombres" class="form-label"><span class="label-text">Nombres</span></label>
                                                        <input type="text" name="nombres" id="nombres" class="form-control" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="apellidos" class="form-label"><span class="label-text">Apellidos</span></label>
                                                        <input type="text" name="apellidos" id="apellidos" class="form-control" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="fecha_nacimiento" class="form-label"><span class="label-text">Fecha de Nacimiento</span></label>
                                                        <input type="date" name="fecha_nacimiento" id="fecha_nacimiento" class="form-control">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="genero" class="form-label"><span class="label-text">Género</span></label>
                                                        <select name="genero" id="genero" class="form-select">
                                                            <option value="">Seleccione</option>
                                                            <option value="Masculino">Masculino</option>
                                                            <option value="Femenino">Femenino</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="new-puesto" role="tabpanel" aria-labelledby="new-puesto-tab">
                                            <div class="card">
                                                <div class="card-body">
                                                    <div class="mb-3">
                                                        <label for="puesto_id" class="form-label"><span class="label-text">Puesto</span></label>
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
                                                        <label for="departamento_id" class="form-label"><span class="label-text">Departamento</span></label>
                                                        <select name="departamento_id" id="departamento_id" class="form-select">
                                                            <option value="">Seleccione</option>
                                                            <?php if (!empty($departamentos)): ?>
                                                                <?php foreach ($departamentos as $d): ?>
                                                                    <option value="<?= htmlspecialchars($d['id']) ?>"><?= htmlspecialchars($d['nombre']) ?></option>
                                                                <?php endforeach; ?>
                                                            <?php endif; ?>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="new-others" role="tabpanel" aria-labelledby="new-others-tab">
                                            <div class="card">
                                                <div class="card-body">
                                                    <div class="mb-3">
                                                        <label for="comentarios" class="form-label"><span class="label-text">Comentarios</span></label>
                                                        <textarea name="comentarios" id="comentarios" class="form-control"></textarea>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="foto" class="form-label"><span class="label-text">Foto</span></label>
                                                        <input type="file" name="foto" id="foto" accept="image/*" class="form-control">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="d-grid mt-2">
                                <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1" aria-hidden="true"></i> <span class="label-text" data-i18n="create">Crear</span></button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Tabla -->
            <div class="col-md-8">
                    <div class="card shadow-sm">
                        <div class="card-header section-accent d-flex align-items-center">
                            <h5 class="mb-0 section-title-on-accent"><span class="label-text" data-i18n="list_employees">Lista de Empleados</span></h5>
                        <div class="ms-auto d-flex align-items-center">
                            <!-- Dark mode removed: only palette swatches remain -->
                            <div id="palettePicker" class="d-flex align-items-center">
                                <!-- Use explicit softer swatch colors so the swatch preview doesn't depend on CSS vars at load time -->
                                <div class="palette-swatch" data-palette="blue" title="Blue" role="button" aria-pressed="false"></div>
                                <div class="palette-swatch" data-palette="teal" title="Teal" role="button" aria-pressed="false"></div>
                                <div class="palette-swatch" data-palette="violet" title="Violet" role="button" aria-pressed="false"></div>
                            </div>
                            <div class="ms-3 d-flex align-items-center">
                                <label for="langSelect" class="form-label mb-0 me-2" style="font-size:0.85rem;"><span class="label-text" data-i18n="language">Idioma</span></label>
                                <select id="langSelect" class="form-select form-select-sm" style="width:auto;">
                                    <option value="es">Español</option>
                                    <option value="en">English</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <table id="tablaEmpleados" class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th></th>
                                    <th>Código</th>
                                    <th>Nombres</th>
                                    <th>Apellidos</th>
                                    <th>Edad</th>
                                    <th>Fecha de Nacimiento</th>
                                    <th>Género</th>
                                    <th>Puesto</th>
                                    <th>Departamento</th>
                                    <th>Comentarios</th>
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
    <script>
    // Ensure tab panes use the same height as the Generals pane
    (function(){
        function syncTabHeights() {
            const generals = document.querySelector('#new-generals, #edit-generals, #ficha-generals');
            if (!generals) return;
            // pick the first existing generals pane
            const el = (generals.length ? generals[0] : generals);
            const rect = el.getBoundingClientRect();
            const height = rect.height || 300;
            document.querySelectorAll('#newFormTabsContent .tab-pane, #editFormTabsContent .tab-pane, #fichaTabsContent .tab-pane').forEach(function(p){
                p.style.minHeight = height + 'px';
                p.classList.add('tab-fixed-height');
            });
        }
        window.addEventListener('load', syncTabHeights);
        window.addEventListener('resize', function(){ setTimeout(syncTabHeights, 120); });
        // Also run after DOM changes such as AJAX fill
        document.addEventListener('DOMContentLoaded', function(){ setTimeout(syncTabHeights, 200); });
    })();
    </script>

<!-- Modal para ver ficha -->
<div class="modal fade" id="modalFicha" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header section-accent d-flex align-items-center">
                <h5 class="modal-title section-title-on-accent"><span class="label-text" data-i18n="ficha_title">Ficha de Empleado</span></h5>
                <div class="ms-auto me-2">
                    <button id="exportPdfBtn" type="button" class="btn btn-sm btn-outline-primary"><span class="label-text" data-i18n="export_pdf">Exportar PDF</span></button>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-4 text-center">
                        <img id="ficha_foto" src="uploads/placeholder.png" alt="Foto" class="img-fluid rounded" style="max-height:250px;">
                    </div>
                    <div class="col-md-8">
                        <!-- Tabs: Generals | Puesto | Others (wrapped in card) -->
                        <div class="tab-card card">
                            <div class="card-header section-accent" style="padding:0;">
                                <ul class="nav nav-tabs" id="fichaTabs" role="tablist">
                                    <li class="nav-item" role="presentation">
                                            <button class="nav-link active rounded-0" id="ficha-generals-tab" data-bs-toggle="tab" data-bs-target="#ficha-generals" type="button" role="tab" aria-controls="ficha-generals" aria-selected="true"><i class="bi bi-person-fill"></i> Generals <span class="badge-tab ms-2" data-tab="ficha-generals" style="display:none;"></span></button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                            <button class="nav-link rounded-0" id="ficha-puesto-tab" data-bs-toggle="tab" data-bs-target="#ficha-puesto" type="button" role="tab" aria-controls="ficha-puesto" aria-selected="false"><i class="bi bi-briefcase-fill"></i> Puesto <span class="badge-tab ms-2" data-tab="ficha-puesto" style="display:none;"></span></button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                            <button class="nav-link rounded-0" id="ficha-others-tab" data-bs-toggle="tab" data-bs-target="#ficha-others" type="button" role="tab" aria-controls="ficha-others" aria-selected="false"><i class="bi bi-three-dots"></i> Others <span class="badge-tab ms-2" data-tab="ficha-others" style="display:none;"></span></button>
                                    </li>
                                </ul>
                            </div>
                            <div class="card-body">
                                <div class="tab-content pt-2" id="fichaTabsContent">
                            <div class="tab-pane fade show active" id="ficha-generals" role="tabpanel" aria-labelledby="ficha-generals-tab">
                                <div class="card">
                                    <div class="card-body">
                                        <ul class="list-group">
                                            <li class="list-group-item"><strong><span class="label-text">Código</span>:</strong> <span id="ficha_codigo"></span></li>
                                            <li class="list-group-item"><strong><span class="label-text">Nombres</span>:</strong> <span id="ficha_nombres"></span></li>
                                            <li class="list-group-item"><strong><span class="label-text">Apellidos</span>:</strong> <span id="ficha_apellidos"></span></li>
                                            <li class="list-group-item"><strong><span class="label-text">Fecha de Nacimiento</span>:</strong> <span id="ficha_fecha_nacimiento"></span></li>
                                            <li class="list-group-item"><strong><span class="label-text">Edad</span>:</strong> <span id="ficha_edad"></span></li>
                                            <li class="list-group-item"><strong><span class="label-text">Género</span>:</strong> <span id="ficha_genero"></span></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="ficha-puesto" role="tabpanel" aria-labelledby="ficha-puesto-tab">
                                <div class="card">
                                    <div class="card-body">
                                        <ul class="list-group">
                                            <li class="list-group-item"><strong><span class="label-text">Puesto</span>:</strong> <span id="ficha_puesto"></span></li>
                                            <li class="list-group-item"><strong><span class="label-text">Departamento</span>:</strong> <span id="ficha_departamento"></span></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="ficha-others" role="tabpanel" aria-labelledby="ficha-others-tab">
                                <div class="card">
                                    <div class="card-body">
                                        <h6><span class="label-text" data-i18n="comments">Comentarios</span></h6>
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
    </div>
    </div>

<!-- Modal para editar/crear empleado -->
<div class="modal fade" id="modalEditar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header section-accent">
                <h5 class="modal-title section-title-on-accent"><span class="label-text" data-i18n="edit_employee">Editar Empleado</span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formEditar" action="empleados/update" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="id" id="edit_id">
                    <input type="hidden" name="foto_actual" id="edit_foto_actual">
                    <div class="row">
                        <div class="col-md-4 text-center">
                            <img id="edit_foto_preview" src="uploads/placeholder.png" alt="Foto" class="img-fluid rounded mb-2" style="max-height:220px;">
                            <!-- File input moved to Others tab to group media + comments -->
                        </div>
                        <div class="col-md-8">
                            <!-- Tabs for edit form -->
                            <div class="tab-card card">
                                <div class="card-header section-accent" style="padding:0;">
                                    <ul class="nav nav-tabs" id="editFormTabs" role="tablist">
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link active rounded-0" id="edit-generals-tab" data-bs-toggle="tab" data-bs-target="#edit-generals" type="button" role="tab" aria-controls="edit-generals" aria-selected="true"><i class="bi bi-person-fill"></i> Generals <span class="badge-tab ms-2" data-tab="edit-generals" style="display:none;"></span></button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link rounded-0" id="edit-puesto-tab" data-bs-toggle="tab" data-bs-target="#edit-puesto" type="button" role="tab" aria-controls="edit-puesto" aria-selected="false"><i class="bi bi-briefcase-fill"></i> Puesto <span class="badge-tab ms-2" data-tab="edit-puesto" style="display:none;"></span></button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link rounded-0" id="edit-others-tab" data-bs-toggle="tab" data-bs-target="#edit-others" type="button" role="tab" aria-controls="edit-others" aria-selected="false"><i class="bi bi-three-dots"></i> Others <span class="badge-tab ms-2" data-tab="edit-others" style="display:none;"></span></button>
                                        </li>
                                    </ul>
                                </div>
                                <div class="card-body">
                                    <div class="tab-content pt-2" id="editFormTabsContent">
                                <div class="tab-pane fade show active" id="edit-generals" role="tabpanel" aria-labelledby="edit-generals-tab">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <label for="edit_nombres" class="form-label"><span class="label-text">Nombres</span></label>
                                                <input type="text" name="nombres" id="edit_nombres" class="form-control" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="edit_apellidos" class="form-label"><span class="label-text">Apellidos</span></label>
                                                <input type="text" name="apellidos" id="edit_apellidos" class="form-control" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="edit_fecha_nacimiento" class="form-label"><span class="label-text">Fecha de Nacimiento</span></label>
                                                <input type="date" name="fecha_nacimiento" id="edit_fecha_nacimiento" class="form-control">
                                            </div>
                                            <div class="mb-3">
                                                <label for="edit_genero" class="form-label"><span class="label-text">Género</span></label>
                                                <select name="genero" id="edit_genero" class="form-select">
                                                    <option value="">Seleccione</option>
                                                    <option value="Masculino">Masculino</option>
                                                    <option value="Femenino">Femenino</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="edit-puesto" role="tabpanel" aria-labelledby="edit-puesto-tab">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <label for="edit_puesto_id" class="form-label"><span class="label-text">Puesto</span></label>
                                                <select name="puesto_id" id="edit_puesto_id" class="form-select"></select>
                                            </div>
                                            <div class="mb-3">
                                                <label for="edit_departamento_id" class="form-label"><span class="label-text">Departamento</span></label>
                                                <select name="departamento_id" id="edit_departamento_id" class="form-select"></select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="edit-others" role="tabpanel" aria-labelledby="edit-others-tab">
                                        <div class="mb-3">
                                            <label for="edit_comentarios" class="form-label"><span class="label-text">Comentarios</span></label>
                                            <textarea name="comentarios" id="edit_comentarios" class="form-control"></textarea>
                                        </div>
                                        <div class="mb-3 mt-2">
                                            <label for="edit_foto" class="form-label"><span class="label-text">Actualizar foto</span></label>
                                            <input type="file" name="foto" id="edit_foto" accept="image/*" class="form-control">
                                        </div>
                                </div>
                                    </div>
                                </div>
                            </div>
                            <div class="d-grid mt-2">
                                <button type="submit" class="btn btn-primary"><span class="label-text" data-i18n="save_changes">Guardar cambios</span></button>
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