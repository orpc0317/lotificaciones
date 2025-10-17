<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Empleados - Lotificaciones</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Dynamic base so assets resolve correctly whether app is served from a subdirectory or as site root -->
    <?php use App\Helpers\PathHelper; ?>
    <?= PathHelper::baseTag() ?>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- DataTables -->
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <!-- DataTables Buttons -->
    <link href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css" rel="stylesheet">
    <!-- DataTables ColReorder CSS -->
    <link href="https://cdn.datatables.net/colreorder/1.6.2/css/colReorder.dataTables.min.css" rel="stylesheet">
    <!-- Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Modern font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="assets/css/style.css" rel="stylesheet">
    <link href="assets/css/theme.css" rel="stylesheet">
    <link href="assets/css/layout.css" rel="stylesheet">
    <style>
        /* Keep small layout helpers inline while main tokens live in theme.css */
        html,body{height:100%; margin:0; padding:0;}
        body{background:var(--bg); color:var(--text); font-family: 'Inter', 'Segoe UI', Roboto, Arial, Helvetica, sans-serif; overflow-x: hidden;}
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
        /* Action icons use theme color */
        .action-icon { 
            color: var(--primary-600); 
            text-decoration: none;
            transition: color 220ms ease, transform 150ms ease;
        }
        .action-icon:hover { 
            color: var(--primary-700); 
            transform: scale(1.1);
        }
    /* Keep tab-card headers visually minimal but do not override .section-accent headers */
    .tab-card .card-header { padding: 0; background: transparent; border-bottom: 0; }
    /* Make sure general card headers that are marked as section-accent keep their themed background */
    .card-header.section-accent { background: var(--primary-600) !important; color: #fff !important; }
        .tab-card .card-body { padding-top: 0.5rem; }
        .nav-tabs .nav-link i { margin-right:6px; }
        /* Make labels in the Nuevo Empleado left-column card bold for emphasis */
        .col-md-4 .card .form-label { font-weight: 600; }
        /* Modal split layout moved to public/assets/css/style.css (pixel-based minmax grid)
           to keep styles centralized. */
    </style>
    <body>
    <div class="app-container">
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="d-flex align-items-center">
                    <i class="bi bi-building-fill text-white fs-4 me-2" id="appIcon"></i>
                    <span class="sidebar-title text-white fw-bold" id="sidebarTitle">Lotificaciones</span>
                </div>
                <button class="btn btn-sm btn-link text-white p-0" id="toggleSidebar" title="Contraer menú">
                    <i class="bi bi-chevron-left fs-5"></i>
                </button>
            </div>

            <nav class="sidebar-nav">
                <ul class="nav flex-column" id="mainMenu">
                    <!-- Menu items will be generated here -->
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <div class="main-content" id="mainContent">
            <!-- Top Bar -->
            <header class="top-bar">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <button class="btn btn-sm btn-link d-lg-none" id="toggleSidebarMobile">
                            <i class="bi bi-list fs-4"></i>
                        </button>
                    </div>
                    <div class="d-flex align-items-center">
                        <!-- Theme palette swatches -->
                        <button class="btn btn-sm btn-outline-secondary me-2 d-flex align-items-center" id="btnPaletteHint" title="Palette">
                            <i class="bi bi-palette"></i>
                        </button>
                        <div class="palette-swatch" data-palette="blue" title="Blue" role="button" aria-pressed="false"></div>
                        <div class="palette-swatch" data-palette="teal" title="Teal" role="button" aria-pressed="false"></div>
                        <div class="palette-swatch" data-palette="violet" title="Violet" role="button" aria-pressed="false"></div>
                        <select id="langSelect" class="form-select form-select-sm ms-3" style="width:auto;">
                            <option value="es">Español</option>
                            <option value="en">English</option>
                        </select>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="page-content">
                <div class="card">
                    <div class="card-header section-accent d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <h5 class="mb-0 section-title-on-accent me-3"><span class="label-text" data-i18n="list_employees">Empleados</span></h5>
                            <a href="./" class="btn btn-outline-light btn-sm me-2" title="Ir al inicio">
                                <i class="bi bi-house-fill me-1"></i><span class="label-text" data-i18n="home">Inicio</span>
                            </a>
                            <button class="btn btn-light btn-sm me-2" id="btnNuevoEmpleado" data-bs-toggle="modal" data-bs-target="#modalNuevoEmpleado"><i class="bi bi-person-plus-fill me-1"></i><span class="label-text" data-i18n="new_employee">Nuevo Empleado</span></button>
                        </div>
                        <div id="exportButtons" class="btn-group"></div>
                    </div>
                    <div class="card-body">
                        <!-- Filter Panel -->
                        <div class="filter-panel mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="mb-0">
                                    <i class="bi bi-funnel"></i> <span data-i18n="filters">Filtros</span>
                                </h6>
                                <button class="btn btn-sm btn-link text-decoration-none" type="button" data-bs-toggle="collapse" data-bs-target="#filterCollapse" aria-expanded="true" aria-controls="filterCollapse">
                                    <i class="bi bi-chevron-down" id="filterToggleIcon"></i>
                                </button>
                            </div>
                            <div class="collapse show" id="filterCollapse">
                                <div class="card card-body bg-light">
                                    <form id="filterForm">
                                        <div class="row g-3">
                                            <!-- ID Filter -->
                                            <div class="col-md-2">
                                                <label for="filter_id" class="form-label small"><span data-i18n="id">ID</span></label>
                                                <input type="number" class="form-control form-control-sm" id="filter_id" placeholder="123">
                                            </div>
                                            
                                            <!-- Name Filter -->
                                            <div class="col-md-3">
                                                <label for="filter_nombres" class="form-label small"><span data-i18n="nombres">Nombres</span></label>
                                                <input type="text" class="form-control form-control-sm" id="filter_nombres" placeholder="">
                                            </div>
                                            
                                            <!-- Last Name Filter -->
                                            <div class="col-md-3">
                                                <label for="filter_apellidos" class="form-label small"><span data-i18n="apellidos">Apellidos</span></label>
                                                <input type="text" class="form-control form-control-sm" id="filter_apellidos" placeholder="">
                                            </div>
                                            
                                            <!-- Gender Filter -->
                                            <div class="col-md-2">
                                                <label for="filter_genero" class="form-label small"><span data-i18n="genero">Género</span></label>
                                                <select class="form-select form-select-sm" id="filter_genero">
                                                    <option value="" data-i18n="all">Todos</option>
                                                    <option value="Masculino" data-i18n="gender_male">Masculino</option>
                                                    <option value="Femenino" data-i18n="gender_female">Femenino</option>
                                                </select>
                                            </div>
                                            
                                            <!-- Department Filter -->
                                            <div class="col-md-2">
                                                <label for="filter_departamento" class="form-label small"><span data-i18n="departamento">Departamento</span></label>
                                                <select class="form-select form-select-sm" id="filter_departamento">
                                                    <option value="" data-i18n="all">Todos</option>
                                                    <?php if(isset($departamentos) && is_array($departamentos)): foreach($departamentos as $d): ?>
                                                    <option value="<?= htmlspecialchars($d['id']) ?>"><?= htmlspecialchars($d['nombre']) ?></option>
                                                    <?php endforeach; endif; ?>
                                                </select>
                                            </div>
                                        </div>
                                        
                                        <div class="row g-3 mt-1">
                                            <!-- Position Filter -->
                                            <div class="col-md-3">
                                                <label for="filter_puesto" class="form-label small"><span data-i18n="puesto">Puesto</span></label>
                                                <select class="form-select form-select-sm" id="filter_puesto">
                                                    <option value="" data-i18n="all">Todos</option>
                                                    <?php if(isset($puestos) && is_array($puestos)): foreach($puestos as $p): ?>
                                                    <option value="<?= htmlspecialchars($p['id']) ?>"><?= htmlspecialchars($p['nombre']) ?></option>
                                                    <?php endforeach; endif; ?>
                                                </select>
                                            </div>
                                            
                                            <!-- Age Range Filter -->
                                            <div class="col-md-2">
                                                <label for="filter_edad_min" class="form-label small"><span data-i18n="age_from">Edad desde</span></label>
                                                <input type="number" class="form-control form-control-sm" id="filter_edad_min" placeholder="18" min="0" max="120">
                                            </div>
                                            <div class="col-md-2">
                                                <label for="filter_edad_max" class="form-label small"><span data-i18n="age_to">Edad hasta</span></label>
                                                <input type="number" class="form-control form-control-sm" id="filter_edad_max" placeholder="65" min="0" max="120">
                                            </div>
                                            
                                            <!-- Birth Date Range Filter -->
                                            <div class="col-md-3">
                                                <label for="filter_fecha_nacimiento_desde" class="form-label small"><span data-i18n="birth_date_from">Fecha Nac. desde</span></label>
                                                <input type="date" class="form-control form-control-sm" id="filter_fecha_nacimiento_desde">
                                            </div>
                                            <div class="col-md-3">
                                                <label for="filter_fecha_nacimiento_hasta" class="form-label small"><span data-i18n="birth_date_to">Fecha Nac. hasta</span></label>
                                                <input type="date" class="form-control form-control-sm" id="filter_fecha_nacimiento_hasta">
                                            </div>
                                            
                                            <!-- Filter Buttons -->
                                            <div class="col-md-4 d-flex align-items-end">
                                                <button type="submit" class="btn btn-primary btn-sm me-2">
                                                    <i class="bi bi-search"></i> <span data-i18n="apply_filters">Aplicar Filtros</span>
                                                </button>
                                                <button type="button" class="btn btn-secondary btn-sm" id="btnClearFilters">
                                                    <i class="bi bi-x-circle"></i> <span data-i18n="clear_filters">Limpiar</span>
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <!-- End Filter Panel -->
                        
                        <table id="tablaEmpleados" class="table table-striped table-bordered" style="width:100%">
                            <thead>
                                <tr>
                                    <th data-i18n="id">ID</th>
                                    <th data-i18n="codigo_label">Código</th>
                                    <th data-i18n="nombres">Nombres</th>
                                    <th data-i18n="apellidos">Apellidos</th>
                                    <th data-i18n="departamento">Departamento</th>
                                    <th data-i18n="actions">Acciones</th>
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

<!-- Modal: Nuevo Empleado (top-level) -->
<div class="modal fade" id="modalNuevoEmpleado" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header section-accent">
        <h5 class="modal-title"><span class="label-text" data-i18n="new_employee">Nuevo Empleado</span></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="formNuevoEmpleado" action="empleados/create" method="post" enctype="multipart/form-data">
            <input type="hidden" name="id" id="nuevo_id">
            <input type="hidden" name="foto_actual" id="nuevo_foto_actual">

            <div class="row modal-split-row">
                <div class="col-12 col-md-4">
                    <div class="tab-card card edit-photo-card position-relative">
                        <div class="card-body text-center text-md-start position-relative">
                            <img id="nuevo_foto_preview" src="uploads/placeholder.png" alt="Foto" class="img-fluid rounded mb-2 edit-photo-img mx-auto mx-md-0">
                            <input type="file" name="foto" id="nuevo_foto" accept="image/*" class="d-none" />
                            <button type="button" class="btn btn-sm btn-outline-light change-photo-btn" data-i18n="change_photo"><i class="bi bi-camera-fill"></i> <span class="label-text d-none d-sm-inline" data-i18n="change_photo">Change photo</span></button>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-8">
                    <div class="tab-card card">
                        <div class="card-header section-accent" style="padding:0;">
                            <ul class="nav nav-tabs" id="newFormTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active rounded-0" id="new-generals-tab" data-bs-toggle="tab" data-bs-target="#new-generals" type="button" role="tab" aria-controls="new-generals" aria-selected="true"><i class="bi bi-person-fill"></i> <span class="label-text" data-i18n="tab_generals">Generals</span> <span class="badge-tab ms-2" data-tab="new-generals" style="display:none;"></span></button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link rounded-0" id="new-personal-tab" data-bs-toggle="tab" data-bs-target="#new-personal" type="button" role="tab" aria-controls="new-personal" aria-selected="false"><i class="bi bi-person-badge"></i> <span class="label-text">Personal</span> <span class="badge-tab ms-2" data-tab="new-personal" style="display:none;"></span></button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link rounded-0" id="new-puesto-tab" data-bs-toggle="tab" data-bs-target="#new-puesto" type="button" role="tab" aria-controls="new-puesto" aria-selected="false"><i class="bi bi-briefcase-fill"></i> <span class="label-text" data-i18n="tab_position">Puesto</span> <span class="badge-tab ms-2" data-tab="new-puesto" style="display:none;"></span></button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link rounded-0" id="new-contact-tab" data-bs-toggle="tab" data-bs-target="#new-contact" type="button" role="tab" aria-controls="new-contact" aria-selected="false"><i class="bi bi-telephone-fill"></i> <span class="label-text">Contacto</span> <span class="badge-tab ms-2" data-tab="new-contact" style="display:none;"></span></button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link rounded-0" id="new-address-tab" data-bs-toggle="tab" data-bs-target="#new-address" type="button" role="tab" aria-controls="new-address" aria-selected="false"><i class="bi bi-house-fill"></i> <span class="label-text">Dirección</span> <span class="badge-tab ms-2" data-tab="new-address" style="display:none;"></span></button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link rounded-0" id="new-others-tab" data-bs-toggle="tab" data-bs-target="#new-others" type="button" role="tab" aria-controls="new-others" aria-selected="false"><i class="bi bi-three-dots"></i> <span class="label-text" data-i18n="tab_others">Others</span> <span class="badge-tab ms-2" data-tab="new-others" style="display:none;"></span></button>
                                </li>
                            </ul>
                        </div>
                        <div class="card-body">
                            <div class="tab-content pt-2" id="newFormTabsContent">
                                <div class="tab-pane fade show active" id="new-generals" role="tabpanel" aria-labelledby="new-generals-tab">
                                    <div class="mb-3">
                                        <label for="nuevo_nombres" class="form-label"><span class="label-text" data-i18n="nombres">Nombres</span></label>
                                        <input type="text" name="nombres" id="nuevo_nombres" class="form-control" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="nuevo_apellidos" class="form-label"><span class="label-text" data-i18n="apellidos">Apellidos</span></label>
                                        <input type="text" name="apellidos" id="nuevo_apellidos" class="form-control" required>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="new-personal" role="tabpanel" aria-labelledby="new-personal-tab">
                                    <div class="mb-3">
                                        <label for="nuevo_fecha_nacimiento" class="form-label"><span class="label-text" data-i18n="fecha_nacimiento">Fecha de Nacimiento</span></label>
                                        <input type="date" name="fecha_nacimiento" id="nuevo_fecha_nacimiento" class="form-control">
                                    </div>
                                    <div class="mb-3">
                                        <label for="nuevo_genero" class="form-label"><span class="label-text" data-i18n="genero">Género</span></label>
                                        <select name="genero" id="nuevo_genero" class="form-select">
                                            <option value="" data-i18n="select_placeholder">Seleccione</option>
                                            <option value="Masculino" data-i18n="gender_male">Masculino</option>
                                            <option value="Femenino" data-i18n="gender_female">Femenino</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="new-puesto" role="tabpanel" aria-labelledby="new-puesto-tab">
                                    <div class="mb-3">
                                        <label for="nuevo_puesto_id" class="form-label"><span class="label-text" data-i18n="puesto">Puesto</span></label>
                                        <select name="puesto_id" id="nuevo_puesto_id" class="form-select">
                                            <option value="" data-i18n="select_placeholder">Seleccione</option>
                                            <?php if(isset($puestos) && is_array($puestos)): foreach($puestos as $p): ?>
                                            <option value="<?= htmlspecialchars($p['id']) ?>"><?= htmlspecialchars($p['nombre']) ?></option>
                                            <?php endforeach; endif; ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="nuevo_departamento_id" class="form-label"><span class="label-text" data-i18n="departamento">Departamento</span></label>
                                        <select name="departamento_id" id="nuevo_departamento_id" class="form-select">
                                            <option value="" data-i18n="select_placeholder">Seleccione</option>
                                            <?php if(isset($departamentos) && is_array($departamentos)): foreach($departamentos as $d): ?>
                                            <option value="<?= htmlspecialchars($d['id']) ?>"><?= htmlspecialchars($d['nombre']) ?></option>
                                            <?php endforeach; endif; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="new-contact" role="tabpanel" aria-labelledby="new-contact-tab">
                                    <div class="mb-3">
                                        <label for="nuevo_email" class="form-label"><span class="label-text" data-i18n="email">Email</span></label>
                                        <input type="email" name="email" id="nuevo_email" class="form-control">
                                    </div>
                                    <div class="mb-3">
                                        <label for="nuevo_telefono" class="form-label"><span class="label-text" data-i18n="telefono">Teléfono</span></label>
                                        <input type="tel" name="telefono" id="nuevo_telefono" class="form-control">
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="new-address" role="tabpanel" aria-labelledby="new-address-tab">
                                    <div class="mb-3">
                                        <label for="nuevo_direccion" class="form-label"><span class="label-text" data-i18n="direccion">Dirección</span></label>
                                        <textarea name="direccion" id="nuevo_direccion" class="form-control" rows="3"></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label for="nuevo_ciudad" class="form-label"><span class="label-text" data-i18n="ciudad">Ciudad</span></label>
                                        <input type="text" name="ciudad" id="nuevo_ciudad" class="form-control">
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="new-others" role="tabpanel" aria-labelledby="new-others-tab">
                                    <div class="mb-3">
                                        <label for="nuevo_comentarios" class="form-label"><span class="label-text" data-i18n="comments">Comentarios</span></label>
                                        <textarea name="comentarios" id="nuevo_comentarios" class="form-control"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="submit" form="formNuevoEmpleado" class="btn btn-primary"><i class="bi bi-person-plus-fill me-1"></i><span class="label-text" data-i18n="create">Crear</span></button>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Editar Empleado (top-level) -->
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
            <div class="row modal-split-row">
                <div class="col-12 col-md-4">
                    <div class="tab-card card edit-photo-card position-relative">
                        <div class="card-body text-center text-md-start position-relative">
                            <img id="edit_foto_preview" src="uploads/placeholder.png" alt="Foto" class="img-fluid rounded mb-2 edit-photo-img mx-auto mx-md-0">
                            <input type="file" name="foto" id="edit_foto" accept="image/*" class="d-none" />
                            <button type="button" class="btn btn-sm btn-outline-light change-photo-btn" data-i18n="change_photo"><i class="bi bi-camera-fill"></i> <span class="label-text d-none d-sm-inline" data-i18n="change_photo">Change photo</span></button>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-8 mt-md-0 mt-3">
                    <!-- Tabs for edit form -->
                    <div class="tab-card card">
                        <div class="card-header section-accent" style="padding:0;">
                            <ul class="nav nav-tabs" id="editFormTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active rounded-0" id="edit-generals-tab" data-bs-toggle="tab" data-bs-target="#edit-generals" type="button" role="tab" aria-controls="edit-generals" aria-selected="true"><i class="bi bi-person-fill"></i> <span class="label-text" data-i18n="tab_generals">Generals</span> <span class="badge-tab ms-2" data-tab="edit-generals" style="display:none;"></span></button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link rounded-0" id="edit-personal-tab" data-bs-toggle="tab" data-bs-target="#edit-personal" type="button" role="tab" aria-controls="edit-personal" aria-selected="false"><i class="bi bi-person-badge"></i> <span class="label-text">Personal</span> <span class="badge-tab ms-2" data-tab="edit-personal" style="display:none;"></span></button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link rounded-0" id="edit-puesto-tab" data-bs-toggle="tab" data-bs-target="#edit-puesto" type="button" role="tab" aria-controls="edit-puesto" aria-selected="false"><i class="bi bi-briefcase-fill"></i> <span class="label-text" data-i18n="tab_position">Puesto</span> <span class="badge-tab ms-2" data-tab="edit-puesto" style="display:none;"></span></button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link rounded-0" id="edit-contact-tab" data-bs-toggle="tab" data-bs-target="#edit-contact" type="button" role="tab" aria-controls="edit-contact" aria-selected="false"><i class="bi bi-telephone-fill"></i> <span class="label-text">Contacto</span> <span class="badge-tab ms-2" data-tab="edit-contact" style="display:none;"></span></button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link rounded-0" id="edit-address-tab" data-bs-toggle="tab" data-bs-target="#edit-address" type="button" role="tab" aria-controls="edit-address" aria-selected="false"><i class="bi bi-house-fill"></i> <span class="label-text">Dirección</span> <span class="badge-tab ms-2" data-tab="edit-address" style="display:none;"></span></button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link rounded-0" id="edit-others-tab" data-bs-toggle="tab" data-bs-target="#edit-others" type="button" role="tab" aria-controls="edit-others" aria-selected="false"><i class="bi bi-three-dots"></i> <span class="label-text" data-i18n="tab_others">Others</span> <span class="badge-tab ms-2" data-tab="edit-others" style="display:none;"></span></button>
                                </li>
                            </ul>
                        </div>
                        <div class="card-body">
                            <div class="tab-content pt-2" id="editFormTabsContent">
                                <div class="tab-pane fade show active" id="edit-generals" role="tabpanel" aria-labelledby="edit-generals-tab">
                                    <div class="mb-3">
                                        <label for="edit_codigo" class="form-label"><span class="label-text" data-i18n="codigo_label">Código</span></label>
                                        <input type="text" name="codigo" id="edit_codigo" class="form-control" readonly>
                                    </div>
                                    <div class="mb-3">
                                        <label for="edit_nombres" class="form-label"><span class="label-text" data-i18n="nombres">Nombres</span></label>
                                        <input type="text" name="nombres" id="edit_nombres" class="form-control" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="edit_apellidos" class="form-label"><span class="label-text" data-i18n="apellidos">Apellidos</span></label>
                                        <input type="text" name="apellidos" id="edit_apellidos" class="form-control" required>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="edit-personal" role="tabpanel" aria-labelledby="edit-personal-tab">
                                    <div class="mb-3">
                                        <label for="edit_fecha_nacimiento" class="form-label"><span class="label-text" data-i18n="fecha_nacimiento">Fecha de Nacimiento</span></label>
                                        <input type="date" name="fecha_nacimiento" id="edit_fecha_nacimiento" class="form-control">
                                    </div>
                                    <div class="mb-3">
                                        <label for="edit_genero" class="form-label"><span class="label-text" data-i18n="genero">Género</span></label>
                                        <select name="genero" id="edit_genero" class="form-select">
                                            <option value="" data-i18n="select_placeholder">Seleccione</option>
                                            <option value="Masculino" data-i18n="gender_male">Masculino</option>
                                            <option value="Femenino" data-i18n="gender_female">Femenino</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="edit-puesto" role="tabpanel" aria-labelledby="edit-puesto-tab">
                                    <div class="mb-3">
                                        <label for="edit_puesto_id" class="form-label"><span class="label-text" data-i18n="puesto">Puesto</span></label>
                                        <select name="puesto_id" id="edit_puesto_id" class="form-select">
                                            <option value="" data-i18n="select_placeholder">Seleccione</option>
                                            <?php if(isset($puestos) && is_array($puestos)): foreach($puestos as $p): ?>
                                            <option value="<?= htmlspecialchars($p['id']) ?>"><?= htmlspecialchars($p['nombre']) ?></option>
                                            <?php endforeach; endif; ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="edit_departamento_id" class="form-label"><span class="label-text" data-i18n="departamento">Departamento</span></label>
                                        <select name="departamento_id" id="edit_departamento_id" class="form-select">
                                            <option value="" data-i18n="select_placeholder">Seleccione</option>
                                            <?php if(isset($departamentos) && is_array($departamentos)): foreach($departamentos as $d): ?>
                                            <option value="<?= htmlspecialchars($d['id']) ?>"><?= htmlspecialchars($d['nombre']) ?></option>
                                            <?php endforeach; endif; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="edit-contact" role="tabpanel" aria-labelledby="edit-contact-tab">
                                    <div class="mb-3">
                                        <label for="edit_email" class="form-label"><span class="label-text" data-i18n="email">Email</span></label>
                                        <input type="email" name="email" id="edit_email" class="form-control">
                                    </div>
                                    <div class="mb-3">
                                        <label for="edit_telefono" class="form-label"><span class="label-text" data-i18n="telefono">Teléfono</span></label>
                                        <input type="tel" name="telefono" id="edit_telefono" class="form-control">
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="edit-address" role="tabpanel" aria-labelledby="edit-address-tab">
                                    <div class="mb-3">
                                        <label for="edit_direccion" class="form-label"><span class="label-text" data-i18n="direccion">Dirección</span></label>
                                        <textarea name="direccion" id="edit_direccion" class="form-control" rows="3"></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label for="edit_ciudad" class="form-label"><span class="label-text" data-i18n="ciudad">Ciudad</span></label>
                                        <input type="text" name="ciudad" id="edit_ciudad" class="form-control">
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="edit-others" role="tabpanel" aria-labelledby="edit-others-tab">
                                    <div class="mb-3">
                                        <label for="edit_comentarios" class="form-label"><span class="label-text" data-i18n="comments">Comentarios</span></label>
                                        <textarea name="comentarios" id="edit_comentarios" class="form-control"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="submit" form="formEditar" class="btn btn-primary"><i class="bi bi-save me-1" aria-hidden="true"></i> <span class="label-text" data-i18n="save">Guardar</span></button>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Ver Ficha del Empleado -->
<div class="modal fade" id="modalFicha" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header section-accent">
        <h5 class="modal-title"><span class="label-text" data-i18n="employee_details">Ficha del Empleado</span></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row modal-split-row">
          <div class="col-12 col-md-4">
            <div class="tab-card card edit-photo-card position-relative">
              <div class="card-body text-center text-md-start position-relative">
                <img id="ficha_foto" src="uploads/placeholder.png" alt="Foto" class="img-fluid rounded mb-2 edit-photo-img mx-auto mx-md-0">
              </div>
            </div>
          </div>
          <div class="col-12 col-md-8 mt-md-0 mt-3">
            <!-- Tabs for view -->
            <div class="tab-card card">
              <div class="card-header section-accent" style="padding:0;">
                <ul class="nav nav-tabs" id="fichaFormTabs" role="tablist">
                  <li class="nav-item" role="presentation">
                    <button class="nav-link active rounded-0" id="ficha-generals-tab" data-bs-toggle="tab" data-bs-target="#ficha-generals" type="button" role="tab" aria-controls="ficha-generals" aria-selected="true"><i class="bi bi-person-fill"></i> <span class="label-text" data-i18n="tab_generals">Generals</span></button>
                  </li>
                  <li class="nav-item" role="presentation">
                    <button class="nav-link rounded-0" id="ficha-personal-tab" data-bs-toggle="tab" data-bs-target="#ficha-personal" type="button" role="tab" aria-controls="ficha-personal" aria-selected="false"><i class="bi bi-person-badge"></i> <span class="label-text">Personal</span></button>
                  </li>
                  <li class="nav-item" role="presentation">
                    <button class="nav-link rounded-0" id="ficha-puesto-tab" data-bs-toggle="tab" data-bs-target="#ficha-puesto" type="button" role="tab" aria-controls="ficha-puesto" aria-selected="false"><i class="bi bi-briefcase-fill"></i> <span class="label-text" data-i18n="tab_position">Puesto</span></button>
                  </li>
                  <li class="nav-item" role="presentation">
                    <button class="nav-link rounded-0" id="ficha-contact-tab" data-bs-toggle="tab" data-bs-target="#ficha-contact" type="button" role="tab" aria-controls="ficha-contact" aria-selected="false"><i class="bi bi-telephone-fill"></i> <span class="label-text">Contacto</span></button>
                  </li>
                  <li class="nav-item" role="presentation">
                    <button class="nav-link rounded-0" id="ficha-address-tab" data-bs-toggle="tab" data-bs-target="#ficha-address" type="button" role="tab" aria-controls="ficha-address" aria-selected="false"><i class="bi bi-house-fill"></i> <span class="label-text">Dirección</span></button>
                  </li>
                  <li class="nav-item" role="presentation">
                    <button class="nav-link rounded-0" id="ficha-others-tab" data-bs-toggle="tab" data-bs-target="#ficha-others" type="button" role="tab" aria-controls="ficha-others" aria-selected="false"><i class="bi bi-three-dots"></i> <span class="label-text" data-i18n="tab_others">Others</span></button>
                  </li>
                </ul>
              </div>
              <div class="card-body">
                <div class="tab-content pt-2" id="fichaFormTabsContent">
                  <!-- Tab 1: Generals -->
                  <div class="tab-pane fade show active" id="ficha-generals" role="tabpanel" aria-labelledby="ficha-generals-tab">
                    <div class="mb-3">
                      <label class="form-label fw-bold"><span class="label-text" data-i18n="codigo_label">Código</span></label>
                      <p class="form-control-plaintext" id="ficha_codigo">-</p>
                    </div>
                    <div class="mb-3">
                      <label class="form-label fw-bold"><span class="label-text" data-i18n="nombres">Nombres</span></label>
                      <p class="form-control-plaintext" id="ficha_nombres">-</p>
                    </div>
                    <div class="mb-3">
                      <label class="form-label fw-bold"><span class="label-text" data-i18n="apellidos">Apellidos</span></label>
                      <p class="form-control-plaintext" id="ficha_apellidos">-</p>
                    </div>
                  </div>
                  <!-- Tab 2: Personal -->
                  <div class="tab-pane fade" id="ficha-personal" role="tabpanel" aria-labelledby="ficha-personal-tab">
                    <div class="mb-3">
                      <label class="form-label fw-bold"><span class="label-text" data-i18n="fecha_nacimiento">Fecha de Nacimiento</span></label>
                      <p class="form-control-plaintext" id="ficha_fecha_nacimiento">-</p>
                    </div>
                    <div class="mb-3">
                      <label class="form-label fw-bold"><span class="label-text" data-i18n="edad">Edad</span></label>
                      <p class="form-control-plaintext" id="ficha_edad">-</p>
                    </div>
                    <div class="mb-3">
                      <label class="form-label fw-bold"><span class="label-text" data-i18n="genero">Género</span></label>
                      <p class="form-control-plaintext" id="ficha_genero">-</p>
                    </div>
                  </div>
                  <!-- Tab 3: Puesto -->
                  <div class="tab-pane fade" id="ficha-puesto" role="tabpanel" aria-labelledby="ficha-puesto-tab">
                    <div class="mb-3">
                      <label class="form-label fw-bold"><span class="label-text" data-i18n="tab_position">Puesto</span></label>
                      <p class="form-control-plaintext" id="ficha_puesto">-</p>
                    </div>
                    <div class="mb-3">
                      <label class="form-label fw-bold"><span class="label-text" data-i18n="departamento">Departamento</span></label>
                      <p class="form-control-plaintext" id="ficha_departamento">-</p>
                    </div>
                  </div>
                  <!-- Tab 4: Contact -->
                  <div class="tab-pane fade" id="ficha-contact" role="tabpanel" aria-labelledby="ficha-contact-tab">
                    <div class="mb-3">
                      <label class="form-label fw-bold"><span class="label-text" data-i18n="email">Email</span></label>
                      <p class="form-control-plaintext" id="ficha_email">-</p>
                    </div>
                    <div class="mb-3">
                      <label class="form-label fw-bold"><span class="label-text" data-i18n="telefono">Teléfono</span></label>
                      <p class="form-control-plaintext" id="ficha_telefono">-</p>
                    </div>
                  </div>
                  <!-- Tab 5: Address -->
                  <div class="tab-pane fade" id="ficha-address" role="tabpanel" aria-labelledby="ficha-address-tab">
                    <div class="mb-3">
                      <label class="form-label fw-bold"><span class="label-text" data-i18n="direccion">Dirección</span></label>
                      <p class="form-control-plaintext" id="ficha_direccion">-</p>
                    </div>
                    <div class="mb-3">
                      <label class="form-label fw-bold"><span class="label-text" data-i18n="ciudad">Ciudad</span></label>
                      <p class="form-control-plaintext" id="ficha_ciudad">-</p>
                    </div>
                  </div>
                  <!-- Tab 6: Others -->
                  <div class="tab-pane fade" id="ficha-others" role="tabpanel" aria-labelledby="ficha-others-tab">
                    <div class="mb-3">
                      <label class="form-label fw-bold"><span class="label-text" data-i18n="comentarios">Comentarios</span></label>
                      <p class="form-control-plaintext" id="ficha_comentarios">-</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" data-bs-dismiss="modal"><i class="bi bi-x-circle me-1"></i> <span class="label-text" data-i18n="close">Cerrar</span></button>
      </div>
    </div>
  </div>
</div>

<!-- Modal de confirmación de borrado -->
<!-- confirmDeleteModal removed: handled by SweetAlert2 -->
                    </div>
                </div>
            </main>
        </div>
    </div>

<!-- JS dependencies -->
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
<!-- DataTables Buttons HTML5 export and ColVis (ensure proper order) -->
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.colVis.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
<!-- DataTables ColReorder -->
<script src="https://cdn.datatables.net/colreorder/1.6.2/js/dataTables.colReorder.min.js"></script>

<!-- Application Constants -->
<script src="assets/js/constants.js"></script>

<!-- Theme Manager (must load before layout and empleados) -->
<script src="assets/js/theme.js"></script>
<!-- Layout script -->
<script src="assets/js/layout.js"></script>

<!-- Empleados Modules (load in dependency order) -->
<script src="assets/js/modules/errorHandler.js"></script>
<script src="assets/js/modules/i18n.js"></script>
<script src="assets/js/modules/dataTable.js"></script>
<script src="assets/js/modules/fileUpload.js"></script>
<script src="assets/js/modules/formHandlers.js"></script>
<script src="assets/js/modules/validation.js"></script>

<!-- Main Application Coordinator -->
<script src="assets/js/empleados.js"></script>

</body>

</html>