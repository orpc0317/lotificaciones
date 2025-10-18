<?php
/**
 * Employee Edit Page
 * Displays a single employee record in edit mode with save functionality
 * Part of multi-tab navigation system with conflict detection
 */

use App\Security\CsrfProtection;
use App\Helpers\PathHelper;

// Ensure $empleado is available from controller
if (!isset($empleado)) {
    http_response_code(404);
    echo "Empleado no encontrado";
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?= PathHelper::baseTag() ?>
    <title>Editing Employee #<?= htmlspecialchars($empleado['codigo']) ?> - <?= htmlspecialchars($empleado['nombres'] . ' ' . $empleado['apellidos']) ?> | Lotificaciones</title>
    
    <!-- Bootstrap 5.3.2 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Custom Styles -->
    <link rel="stylesheet" href="assets/css/theme.css">
    <link rel="stylesheet" href="assets/css/layout.css">
    <link rel="stylesheet" href="assets/css/style.css">
    
    <style>
        /* Modal-style theming for tabs */
        .section-accent {
            background: var(--primary-600) !important;
            color: #fff !important;
        }
        
        .nav-tabs .nav-link {
            color: #fff;
            border: none;
        }
        
        .nav-tabs .nav-link.active {
            background: rgba(0,0,0,0.14);
            color: #fff;
            box-shadow: inset 0 -2px 0 rgba(0,0,0,0.08);
        }
        
        .nav-tabs .nav-link i {
            margin-right: 6px;
        }
        
        .nav-tabs .nav-link.rounded-0 {
            border-radius: 0 !important;
        }
        
        /* Tab content styling */
        .tab-content {
            min-height: 300px;
        }
        
        /* Photo card styling */
        .edit-photo-card {
            position: relative;
        }
        
        .section-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 15px;
            color: var(--primary-600);
        }
        .photo-preview-container {
            max-width: 200px;
            margin: 0 auto;
        }
        .photo-preview-img {
            width: 100%;
            height: auto;
            border-radius: 8px;
            border: 2px solid var(--border);
        }
    </style>
</head>
<body data-csrf-token="<?= htmlspecialchars(CsrfProtection::getToken()) ?>">
    <div class="app-container">
        <!-- Sidebar placeholder (will be rendered by layout.js) -->
        <div id="sidebar-container"></div>
        
        <!-- Main Content -->
        <div class="main-content">
            <!-- Top Bar -->
            <div class="top-bar">
                <button class="btn btn-link menu-toggle" id="menuToggle">
                    <i class="bi bi-list"></i>
                </button>
                <h1 class="page-title">
                    <i class="bi bi-pencil-square"></i>
                    <span id="pageTitle">Editing Employee #<?= htmlspecialchars($empleado['codigo']) ?></span>
                </h1>
            </div>

            <!-- Page Content -->
            <div class="content-wrapper">
                <div class="container-fluid">
                    <!-- Form -->
                    <form id="formEditEmpleado" method="post" enctype="multipart/form-data">
                        <?= CsrfProtection::getTokenInput() ?>
                        <input type="hidden" name="id" value="<?= htmlspecialchars($empleado['codigo']) ?>">
                        <input type="hidden" name="foto_actual" value="<?= htmlspecialchars($empleado['foto'] ?? '') ?>">
                        
                        <!-- Action Buttons -->
                        <div class="row mb-3">
                            <div class="col-12 d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save"></i> <span id="btnSave">Guardar</span>
                                </button>
                                <button type="button" class="btn btn-outline-secondary" onclick="cancelEdit()">
                                    <i class="bi bi-x-circle"></i> <span id="btnCancel">Cancelar</span>
                                </button>
                            </div>
                        </div>

                        <!-- Modal-style Split Layout -->
                        <div class="row">
                            <!-- Left Column: Photo -->
                            <div class="col-12 col-md-4">
                                <div class="card edit-photo-card">
                                    <div class="card-body text-center">
                                        <div class="photo-preview-container mb-3">
                                            <img id="photoPreview" 
                                                 src="uploads/<?= htmlspecialchars($empleado['foto'] ?? 'placeholder.png') ?>" 
                                                 alt="Employee Photo" 
                                                 class="img-fluid rounded">
                                        </div>
                                        <input type="file" name="foto" id="photoInput" accept="image/*" class="d-none">
                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="document.getElementById('photoInput').click()">
                                            <i class="bi bi-camera-fill"></i> <span id="btnChangePhoto">Cambiar Foto</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Right Column: Tabbed Form -->
                            <div class="col-12 col-md-8 mt-3 mt-md-0">
                                <div class="card">
                                    <!-- Tab Header with Theme Color -->
                                    <div class="card-header section-accent" style="padding:0; position: relative;">
                                        <button type="button" class="tab-scroll-btn tab-scroll-left" id="editTabScrollLeft">
                                            <i class="bi bi-chevron-left"></i>
                                        </button>
                                        <div class="tab-container-wrapper">
                                            <ul class="nav nav-tabs" id="employeeTabs" role="tablist">
                                                <li class="nav-item" role="presentation">
                                                    <button class="nav-link active rounded-0" id="general-tab" data-bs-toggle="tab" data-bs-target="#general" type="button" role="tab">
                                                        <i class="bi bi-person-fill"></i> <span id="tabGeneral">General</span>
                                                        <span class="badge-tab ms-2" data-tab="general" style="display:none;"></span>
                                                    </button>
                                                </li>
                                            <li class="nav-item" role="presentation">
                                                <button class="nav-link rounded-0" id="personal-tab" data-bs-toggle="tab" data-bs-target="#personal" type="button" role="tab">
                                                    <i class="bi bi-person-badge"></i> <span id="tabPersonal">Personal</span>
                                                    <span class="badge-tab ms-2" data-tab="personal" style="display:none;"></span>
                                                </button>
                                            </li>
                                            <li class="nav-item" role="presentation">
                                                <button class="nav-link rounded-0" id="employment-tab" data-bs-toggle="tab" data-bs-target="#employment" type="button" role="tab">
                                                    <i class="bi bi-briefcase-fill"></i> <span id="tabEmployment">Laboral</span>
                                                    <span class="badge-tab ms-2" data-tab="employment" style="display:none;"></span>
                                                </button>
                                            </li>
                                            <li class="nav-item" role="presentation">
                                                <button class="nav-link rounded-0" id="contact-tab" data-bs-toggle="tab" data-bs-target="#contact" type="button" role="tab">
                                                    <i class="bi bi-telephone-fill"></i> <span id="tabContact">Contacto</span>
                                                    <span class="badge-tab ms-2" data-tab="contact" style="display:none;"></span>
                                                </button>
                                            </li>
                                            <li class="nav-item" role="presentation">
                                                <button class="nav-link rounded-0" id="address-tab" data-bs-toggle="tab" data-bs-target="#address" type="button" role="tab">
                                                    <i class="bi bi-house-fill"></i> <span id="tabAddress">Dirección</span>
                                                    <span class="badge-tab ms-2" data-tab="address" style="display:none;"></span>
                                                </button>
                                            </li>
                                            <li class="nav-item" role="presentation">
                                                <button class="nav-link rounded-0" id="other-tab" data-bs-toggle="tab" data-bs-target="#other" type="button" role="tab">
                                                    <i class="bi bi-three-dots"></i> <span id="tabOther">Otros</span>
                                                    <span class="badge-tab ms-2" data-tab="other" style="display:none;"></span>
                                                </button>
                                            </li>
                                            <li class="nav-item" role="presentation">
                                                <button class="nav-link rounded-0" id="training-tab" data-bs-toggle="tab" data-bs-target="#training" type="button" role="tab">
                                                    <i class="bi bi-book-fill"></i> <span id="tabTraining">Capacitación</span>
                                                    <span class="badge-tab ms-2" data-tab="training" style="display:none;"></span>
                                                </button>
                                            </li>
                                            </ul>
                                        </div>
                                        <button type="button" class="tab-scroll-btn tab-scroll-right" id="editTabScrollRight">
                                            <i class="bi bi-chevron-right"></i>
                                        </button>
                                    </div>

                                    <!-- Tab Content -->
                                    <div class="card-body">
                                        <div class="tab-content" id="employeeTabContent"
                                        <!-- General Tab -->
                                        <div class="tab-pane fade show active" id="general" role="tabpanel">
                                            <div class="row g-3">
                                                <div class="col-md-4">
                                                    <label for="codigo" class="form-label"><span id="lblCode">Código</span></label>
                                                    <input type="text" class="form-control" id="codigo" value="<?= htmlspecialchars($empleado['codigo']) ?>" readonly>
                                                </div>
                                                <div class="col-md-4">
                                                    <label for="nombres" class="form-label"><span id="lblFirstName">Nombres</span> <span class="text-danger">*</span></label>
                                                    <input type="text" name="nombres" class="form-control" id="nombres" value="<?= htmlspecialchars($empleado['nombres']) ?>" required>
                                                </div>
                                                <div class="col-md-4">
                                                    <label for="apellidos" class="form-label"><span id="lblLastName">Apellidos</span> <span class="text-danger">*</span></label>
                                                    <input type="text" name="apellidos" class="form-control" id="apellidos" value="<?= htmlspecialchars($empleado['apellidos']) ?>" required>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Personal Tab -->
                                        <div class="tab-pane fade" id="personal" role="tabpanel">
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label for="genero" class="form-label"><span id="lblGender">Género</span></label>
                                                    <select name="genero" id="genero" class="form-select">
                                                        <option value="">Seleccione</option>
                                                        <option value="Masculino" <?= ($empleado['genero'] ?? '') === 'Masculino' ? 'selected' : '' ?>>Masculino</option>
                                                        <option value="Femenino" <?= ($empleado['genero'] ?? '') === 'Femenino' ? 'selected' : '' ?>>Femenino</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="fecha_nacimiento" class="form-label"><span id="lblBirthDate">Fecha de Nacimiento</span></label>
                                                    <input type="date" name="fecha_nacimiento" class="form-control" id="fecha_nacimiento" value="<?= htmlspecialchars($empleado['fecha_nacimiento'] ?? '') ?>">
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Employment Tab -->
                                        <div class="tab-pane fade" id="employment" role="tabpanel">
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label for="departamento_id" class="form-label"><span id="lblDepartment">Departamento</span></label>
                                                    <select name="departamento_id" id="departamento_id" class="form-select">
                                                        <option value="">Seleccione</option>
                                                        <?php if(isset($departamentos) && is_array($departamentos)): foreach($departamentos as $d): ?>
                                                        <option value="<?= htmlspecialchars($d['id']) ?>" <?= ($empleado['departamento_id'] ?? '') == $d['id'] ? 'selected' : '' ?>>
                                                            <?= htmlspecialchars($d['nombre']) ?>
                                                        </option>
                                                        <?php endforeach; endif; ?>
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="puesto_id" class="form-label"><span id="lblPosition">Puesto</span></label>
                                                    <select name="puesto_id" id="puesto_id" class="form-select">
                                                        <option value="">Seleccione</option>
                                                        <?php if(isset($puestos) && is_array($puestos)): foreach($puestos as $p): ?>
                                                        <option value="<?= htmlspecialchars($p['id']) ?>" <?= ($empleado['puesto_id'] ?? '') == $p['id'] ? 'selected' : '' ?>>
                                                            <?= htmlspecialchars($p['nombre']) ?>
                                                        </option>
                                                        <?php endforeach; endif; ?>
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="fecha_contratacion" class="form-label"><span id="lblHireDate">Fecha de Contratación</span></label>
                                                    <input type="date" name="fecha_contratacion" class="form-control" id="fecha_contratacion" value="<?= htmlspecialchars($empleado['fecha_contratacion'] ?? '') ?>">
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="salario" class="form-label"><span id="lblSalary">Salario</span></label>
                                                    <input type="number" name="salario" class="form-control" id="salario" step="0.01" value="<?= htmlspecialchars($empleado['salario'] ?? '') ?>">
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Contact Tab -->
                                        <div class="tab-pane fade" id="contact" role="tabpanel">
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label for="telefono" class="form-label"><span id="lblPhone">Teléfono</span></label>
                                                    <input type="tel" name="telefono" class="form-control" id="telefono" value="<?= htmlspecialchars($empleado['telefono'] ?? '') ?>">
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="email" class="form-label"><span id="lblEmail">Email</span></label>
                                                    <input type="email" name="email" class="form-control" id="email" value="<?= htmlspecialchars($empleado['email'] ?? '') ?>">
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="contacto_emergencia" class="form-label"><span id="lblEmergencyContact">Contacto de Emergencia</span></label>
                                                    <input type="text" name="contacto_emergencia" class="form-control" id="contacto_emergencia" value="<?= htmlspecialchars($empleado['contacto_emergencia'] ?? '') ?>">
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="telefono_emergencia" class="form-label"><span id="lblEmergencyPhone">Teléfono de Emergencia</span></label>
                                                    <input type="tel" name="telefono_emergencia" class="form-control" id="telefono_emergencia" value="<?= htmlspecialchars($empleado['telefono_emergencia'] ?? '') ?>">
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Address Tab -->
                                        <div class="tab-pane fade" id="address" role="tabpanel">
                                            <div class="row g-3">
                                                <div class="col-12">
                                                    <label for="direccion" class="form-label"><span id="lblAddress">Dirección</span></label>
                                                    <textarea name="direccion" class="form-control" id="direccion" rows="3"><?= htmlspecialchars($empleado['direccion'] ?? '') ?></textarea>
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="ciudad" class="form-label"><span id="lblCity">Ciudad</span></label>
                                                    <input type="text" name="ciudad" class="form-control" id="ciudad" value="<?= htmlspecialchars($empleado['ciudad'] ?? '') ?>">
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Other Tab -->
                                        <div class="tab-pane fade" id="other" role="tabpanel">
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label for="tipo_sangre" class="form-label"><span id="lblBloodType">Tipo de Sangre</span></label>
                                                    <input type="text" name="tipo_sangre" class="form-control" id="tipo_sangre" value="<?= htmlspecialchars($empleado['tipo_sangre'] ?? '') ?>">
                                                </div>
                                                <div class="col-12">
                                                    <label for="alergias" class="form-label"><span id="lblAllergies">Alergias</span></label>
                                                    <textarea name="alergias" class="form-control" id="alergias" rows="2"><?= htmlspecialchars($empleado['alergias'] ?? '') ?></textarea>
                                                </div>
                                                <div class="col-12">
                                                    <label for="comentarios" class="form-label"><span id="lblComments">Comentarios</span></label>
                                                    <textarea name="comentarios" class="form-control" id="comentarios" rows="3"><?= htmlspecialchars($empleado['comentarios'] ?? '') ?></textarea>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Training Tab -->
                                        <div class="tab-pane fade" id="training" role="tabpanel">
                                            <div class="row g-3">
                                                <div class="col-12">
                                                    <div class="card">
                                                        <div class="card-header bg-light">
                                                            <h6 class="mb-0"><i class="bi bi-book-fill me-2"></i><span id="lblTrainingTitle">Cursos y Capacitaciones</span></h6>
                                                        </div>
                                                        <div class="card-body">
                                                            <!-- Add Training Form -->
                                                            <div class="row g-3 mb-3 p-3 bg-light rounded">
                                                                <div class="col-md-4">
                                                                    <label for="curso_nombre" class="form-label"><span id="lblCourseName">Nombre del Curso</span> <span class="text-danger">*</span></label>
                                                                    <input type="text" class="form-control" id="curso_nombre" placeholder="Ej: Seguridad Industrial">
                                                                </div>
                                                                <div class="col-md-3">
                                                                    <label for="curso_fecha" class="form-label"><span id="lblCourseDate">Fecha Aprobado</span> <span class="text-danger">*</span></label>
                                                                    <input type="date" class="form-control" id="curso_fecha">
                                                                </div>
                                                                <div class="col-md-3">
                                                                    <label for="curso_recursos" class="form-label"><span id="lblCourseResources">Recursos Aprobados</span></label>
                                                                    <input type="number" class="form-control" id="curso_recursos" placeholder="0.00" step="0.01">
                                                                </div>
                                                                <div class="col-md-2 d-flex align-items-end">
                                                                    <button type="button" class="btn btn-primary w-100" id="btnAddCourse">
                                                                        <i class="bi bi-plus-circle"></i> <span id="lblAddButton">Agregar</span>
                                                                    </button>
                                                                </div>
                                                                <div class="col-12">
                                                                    <label for="curso_comentarios" class="form-label"><span id="lblCourseComments">Comentarios</span></label>
                                                                    <textarea class="form-control" id="curso_comentarios" rows="2" placeholder="Observaciones sobre el curso..."></textarea>
                                                                </div>
                                                            </div>

                                                            <!-- Training Table -->
                                                            <div class="table-responsive">
                                                                <table class="table table-striped table-hover" id="trainingTable">
                                                                    <thead>
                                                                        <tr>
                                                                            <th><span id="thCourseName">Curso</span></th>
                                                                            <th><span id="thCourseDate">Fecha</span></th>
                                                                            <th><span id="thCourseResources">Recursos</span></th>
                                                                            <th><span id="thCourseComments">Comentarios</span></th>
                                                                            <th width="100"><span id="thActions">Acciones</span></th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody id="trainingTableBody">
                                                                        <!-- Rows will be added dynamically -->
                                                                        <tr id="emptyTrainingRow">
                                                                            <td colspan="5" class="text-center text-muted">
                                                                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                                                                <span id="lblNoTraining">No hay cursos registrados. Agregue el primero usando el formulario arriba.</span>
                                                                            </td>
                                                                        </tr>
                                                                    </tbody>
                                                                </table>
                                                            </div>

                                                            <!-- Hidden input to store training data -->
                                                            <input type="hidden" name="training_data" id="training_data" value="[]">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom Scripts -->
    <script src="assets/js/theme.js"></script>
    <script src="assets/js/layout.js"></script>
    
    <script>
        // Employee data for JavaScript
        const empleadoData = <?= json_encode($empleado, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
        const empleadoNumericId = <?= (int)$empleado['id'] ?>; // Numeric ID for routes
        const empleadoId = <?= json_encode($empleado['codigo'], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>; // Code for BroadcastChannel (backward compatibility)
        
        // Get base URL from base tag
        function api(path) {
            try {
                const baseEl = document.querySelector('base');
                const base = baseEl ? baseEl.getAttribute('href') : '/';
                return base.replace(/\/+$/, '') + '/' + path.replace(/^\/+/, '');
            } catch(e) {
                return '/' + path.replace(/^\/+/, '');
            }
        }
        
        // Cross-tab communication channel
        let editChannel = null;
        let formModified = false;
        let editLockAcquired = false;
        let checkingLock = false;

        document.addEventListener('DOMContentLoaded', function() {
            checkEditLockAndProceed();
            // Initialize tab validation badges
            if (typeof attachValidationListeners === 'function') {
                attachValidationListeners();
            }
        });

        // Update translations when language changes
        window.addEventListener('languageChanged', function(e) {
            loadTranslations();
        });

        // Check if another tab is editing before allowing edit mode
        function checkEditLockAndProceed() {
            if (!('BroadcastChannel' in window)) {
                // Browser doesn't support Broadcast Channel, proceed anyway
                proceedWithEdit();
                return;
            }

            checkingLock = true;
            editChannel = new BroadcastChannel('lotificaciones-employee-edit');
            
            let responseReceived = false;
            const timeout = setTimeout(() => {
                if (!responseReceived && !editLockAcquired) {
                    // No response = no one else is editing, safe to proceed
                    acquireEditLock();
                }
            }, 200); // Wait 200ms for responses

            // Listen for lock status responses
            editChannel.onmessage = (event) => {
                if (event.data.action === 'lock-check' && event.data.empleadoId === empleadoId) {
                    // Another tab is asking if we're editing
                    if (editLockAcquired) {
                        editChannel.postMessage({ 
                            action: 'lock-exists', 
                            empleadoId: empleadoId 
                        });
                    }
                } else if (event.data.action === 'lock-exists' && event.data.empleadoId === empleadoId) {
                    // Another tab responded - they're already editing!
                    responseReceived = true;
                    clearTimeout(timeout);
                    blockEditMode();
                } else if (event.data.action === 'lock-released' && event.data.empleadoId === empleadoId) {
                    // Edit lock was released in another tab
                    console.log('Edit lock released by another tab');
                } else if (event.data.action === 'data-updated' && event.data.empleadoId === empleadoId) {
                    showDataUpdatedNotification();
                }
            };

            // Ask if anyone else is editing this employee
            editChannel.postMessage({ 
                action: 'lock-check', 
                empleadoId: empleadoId 
            });
        }

        function acquireEditLock() {
            editLockAcquired = true;
            checkingLock = false;
            console.log('Edit lock acquired for employee', empleadoId);
            proceedWithEdit();

            // Announce that we have the lock
            editChannel.postMessage({ 
                action: 'lock-acquired', 
                empleadoId: empleadoId 
            });

            // Release lock on page unload
            window.addEventListener('beforeunload', function() {
                editChannel.postMessage({ 
                    action: 'lock-released', 
                    empleadoId: empleadoId 
                });
                editChannel.close();
            });
        }

        function blockEditMode() {
            checkingLock = false;
            console.log('Edit mode blocked - already being edited in another tab');
            
            // Show error message
            alert('Este empleado está siendo editado en otra pestaña.\n\nNo puede entrar en modo de edición hasta que la otra pestaña cierre o guarde.\n\nSerá redirigido a la vista de solo lectura.');
            
            // Redirect to view mode
            window.location.href = api(`empleados/view/${empleadoNumericId}`);
        }

        function proceedWithEdit() {
            initializeEditPage();
            setupFormChangeTracking();
            setupPhotoPreview();
            loadTranslations();
        }

        function initializeEditPage() {
            const nombre = empleadoData.nombres + ' ' + empleadoData.apellidos;
            document.title = `Editing Employee #${empleadoData.codigo} - ${nombre} | Lotificaciones`;
        }

        function showDataUpdatedNotification() {
            if (confirm('Este empleado ha sido modificado en otra pestaña. ¿Desea recargar para ver los cambios?')) {
                location.reload();
            }
        }

        // Track form modifications
        function setupFormChangeTracking() {
            const form = document.getElementById('formEditEmpleado');
            const inputs = form.querySelectorAll('input, select, textarea');
            
            inputs.forEach(input => {
                input.addEventListener('change', () => {
                    formModified = true;
                });
            });

            // Warn before leaving with unsaved changes
            window.addEventListener('beforeunload', (e) => {
                if (formModified) {
                    e.preventDefault();
                    e.returnValue = ''; // Required for Chrome
                }
            });

            // Form submission
            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                
                const formData = new FormData(form);
                
                try {
                    const response = await fetch('empleados/update', {
                        method: 'POST',
                        body: formData
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        formModified = false; // Reset modification flag
                        
                        // Notify other tabs
                        if (editChannel) {
                            editChannel.postMessage({ 
                                action: 'data-updated', 
                                empleadoId: empleadoId 
                            });
                        }
                        
                        alert('Empleado actualizado correctamente');
                        
                        // Redirect to view mode
                        window.location.href = api(`empleados/view/${empleadoNumericId}`);
                    } else {
                        alert('Error al actualizar: ' + (result.message || 'Error desconocido'));
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('Error al guardar los cambios');
                }
            });
        }

        function cancelEdit() {
            if (formModified) {
                if (confirm('Tienes cambios sin guardar. ¿Deseas cancelar?')) {
                    formModified = false;
                    window.location.href = api(`empleados/view/${empleadoNumericId}`);
                }
            } else {
                window.location.href = api(`empleados/view/${empleadoNumericId}`);
            }
        }

        // Photo preview
        function setupPhotoPreview() {
            const photoInput = document.getElementById('photoInput');
            const photoPreview = document.getElementById('photoPreview');
            
            photoInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        photoPreview.src = e.target.result;
                        formModified = true;
                    };
                    reader.readAsDataURL(file);
                }
            });
        }

        async function loadTranslations() {
            const lang = window.themeManager ? window.themeManager.getLanguage() : 'es';
            
            try {
                const response = await fetch(`assets/i18n/${lang}.json`);
                const t = await response.json();
                
                // Update UI text
                const elements = {
                    pageTitle: `${t.editing || 'Editing'} ${t.employee || 'Employee'} #${empleadoData.codigo}`,
                    btnSave: t.save || 'Guardar',
                    btnCancel: t.cancel || 'Cancelar',
                    btnViewMode: t.view_mode || 'Ver sin editar',
                    btnChangePhoto: t.change_photo || 'Cambiar Foto',
                    lblPhoto: t.photo || 'Fotografía',
                    
                    // Tab titles
                    tabGeneral: t.general || 'General',
                    tabPersonal: t.personal || 'Personal',
                    tabEmployment: t.employment || 'Laboral',
                    tabContact: t.contact || 'Contacto',
                    tabAddress: t.address || 'Dirección',
                    tabOther: t.other || 'Otros',
                    
                    // Labels
                    lblCode: t.codigo || 'Código',
                    lblFirstName: t.nombres || 'Nombres',
                    lblLastName: t.apellidos || 'Apellidos',
                    lblGender: t.genero || 'Género',
                    lblBirthDate: t.fecha_nacimiento || 'Fecha de Nacimiento',
                    lblDepartment: t.departamento || 'Departamento',
                    lblPosition: t.puesto || 'Puesto',
                    lblHireDate: t.fecha_contratacion || 'Fecha de Contratación',
                    lblSalary: t.salario || 'Salario',
                    lblPhone: t.telefono || 'Teléfono',
                    lblEmail: t.email || 'Email',
                    lblEmergencyContact: t.contacto_emergencia || 'Contacto de Emergencia',
                    lblEmergencyPhone: t.telefono_emergencia || 'Teléfono de Emergencia',
                    lblAddress: t.direccion || 'Dirección',
                    lblCity: t.ciudad || 'Ciudad',
                    lblBloodType: t.tipo_sangre || 'Tipo de Sangre',
                    lblAllergies: t.alergias || 'Alergias',
                    lblComments: t.comentarios || 'Comentarios',
                    
                    // Warning
                    warningTitle: t.warning || '¡Advertencia!',
                    warningMessage: t.edit_conflict_warning || 'Este empleado está siendo editado en otra pestaña.'
                };
                
                Object.keys(elements).forEach(id => {
                    const el = document.getElementById(id);
                    if (el) el.textContent = elements[id];
                });
                
                // Update document title
                document.title = `${t.editing || 'Editing'} ${t.employee || 'Employee'} #${empleadoData.codigo} - ${empleadoData.nombres} ${empleadoData.apellidos} | Lotificaciones`;
            } catch (error) {
                console.error('Error loading translations:', error);
            }
        }

        /**
         * Tab Validation Badge System
         * Updates badges and underlines to show missing required fields per tab
         */
        function updateTabBadges() {
            const form = document.getElementById('formEditEmpleado');
            if (!form) {
                console.warn('Tab Badges: Form #formEditEmpleado not found');
                return;
            }

            const tabIds = ['general', 'personal', 'employment', 'contact', 'address', 'other'];

            tabIds.forEach(function(tabId) {
                const pane = document.getElementById(tabId);
                if (!pane) return;

                // Find all required fields in this tab
                const requiredFields = pane.querySelectorAll('input[required], textarea[required], select[required]');
                let invalidCount = 0;

                requiredFields.forEach(function(field) {
                    const val = (field.value || '').trim();
                    if (!val || val === '') {
                        invalidCount++;
                    }
                });

                // Update badge
                const badge = document.querySelector('.badge-tab[data-tab="' + tabId + '"]');
                if (badge) {
                    if (invalidCount > 0) {
                        badge.textContent = invalidCount;
                        badge.style.display = 'inline-block';
                        badge.style.background = '#dc3545';
                        badge.style.color = '#fff';
                        badge.style.fontSize = '11px';
                        badge.style.minWidth = '18px';
                        badge.style.height = '18px';
                        badge.style.lineHeight = '16px';
                        badge.style.padding = '0 6px';
                        badge.style.borderRadius = '999px';
                        badge.style.textAlign = 'center';
                        badge.style.verticalAlign = 'middle';
                    } else {
                        badge.style.display = 'none';
                    }
                }

                // Update tab title underline
                const tabButton = document.getElementById(tabId + '-tab');
                if (tabButton) {
                    if (invalidCount > 0) {
                        tabButton.classList.add('tab-invalid');
                    } else {
                        tabButton.classList.remove('tab-invalid');
                    }
                }
            });
        }

        /**
         * Attach validation listeners to all required fields
         */
        function attachValidationListeners() {
            const form = document.getElementById('formEditEmpleado');
            if (!form) {
                console.warn('Tab Badges: Form #formEditEmpleado not found');
                return;
            }

            const requiredFields = form.querySelectorAll('input[required], textarea[required], select[required]');
            console.log('Tab Badges: Found ' + requiredFields.length + ' required fields');
            
            requiredFields.forEach(function(field) {
                ['input', 'change', 'blur'].forEach(function(eventType) {
                    field.addEventListener(eventType, function() {
                        updateTabBadges();
                    });
                });
            });

            // Initial update
            updateTabBadges();
        }

        // ==================== TRAINING TAB FUNCTIONALITY ====================
        
        let trainingData = [];
        let editingTrainingIndex = -1;

        // Load existing training data from employee
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize training data from employee if exists
            if (empleadoData.training_data) {
                try {
                    trainingData = typeof empleadoData.training_data === 'string' 
                        ? JSON.parse(empleadoData.training_data) 
                        : empleadoData.training_data;
                    renderTrainingTable();
                } catch(e) {
                    console.error('Error loading training data:', e);
                    trainingData = [];
                }
            }

            // Add Course Button
            document.getElementById('btnAddCourse').addEventListener('click', addTrainingRow);

            // Allow Enter key to add row
            ['curso_nombre', 'curso_fecha', 'curso_recursos', 'curso_comentarios'].forEach(id => {
                document.getElementById(id).addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        addTrainingRow();
                    }
                });
            });
        });

        function addTrainingRow() {
            const nombre = document.getElementById('curso_nombre').value.trim();
            const fecha = document.getElementById('curso_fecha').value;
            const recursos = document.getElementById('curso_recursos').value;
            const comentarios = document.getElementById('curso_comentarios').value.trim();

            // Validation - using i18n
            if (!nombre) {
                alert(window.i18n ? window.i18n.t('alertCourseName') : 'Por favor ingrese el nombre del curso');
                document.getElementById('curso_nombre').focus();
                return;
            }

            if (!fecha) {
                alert(window.i18n ? window.i18n.t('alertCourseDate') : 'Por favor seleccione la fecha');
                document.getElementById('curso_fecha').focus();
                return;
            }

            const training = {
                nombre: nombre,
                fecha: fecha,
                recursos: parseFloat(recursos) || 0,
                comentarios: comentarios
            };

            if (editingTrainingIndex >= 0) {
                // Update existing row
                trainingData[editingTrainingIndex] = training;
                editingTrainingIndex = -1;
                // Update button text using i18n
                const btnText = window.i18n ? window.i18n.t('lblAddButton') : 'Agregar';
                document.getElementById('btnAddCourse').innerHTML = '<i class="bi bi-plus-circle"></i> <span id="lblAddButton">' + btnText + '</span>';
            } else {
                // Add new row
                trainingData.push(training);
            }

            // Clear form
            document.getElementById('curso_nombre').value = '';
            document.getElementById('curso_fecha').value = '';
            document.getElementById('curso_recursos').value = '';
            document.getElementById('curso_comentarios').value = '';

            // Update table and hidden input
            renderTrainingTable();
            updateTrainingHiddenInput();

            // Focus back to first field
            document.getElementById('curso_nombre').focus();
        }

        function renderTrainingTable() {
            const tbody = document.getElementById('trainingTableBody');
            const emptyRow = document.getElementById('emptyTrainingRow');

            if (trainingData.length === 0) {
                emptyRow.style.display = '';
                return;
            }

            emptyRow.style.display = 'none';

            // Clear existing rows (except empty row)
            while (tbody.children.length > 1) {
                tbody.removeChild(tbody.lastChild);
            }

            trainingData.forEach((training, index) => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${escapeHtml(training.nombre)}</td>
                    <td>${formatDate(training.fecha)}</td>
                    <td class="text-end">$${parseFloat(training.recursos).toFixed(2)}</td>
                    <td>${escapeHtml(training.comentarios || '-')}</td>
                    <td>
                        <button type="button" class="btn btn-sm btn-outline-primary me-1" onclick="editTrainingRow(${index})" title="Editar">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteTrainingRow(${index})" title="Eliminar">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                `;
                tbody.appendChild(row);
            });
        }

        function editTrainingRow(index) {
            const training = trainingData[index];
            
            document.getElementById('curso_nombre').value = training.nombre;
            document.getElementById('curso_fecha').value = training.fecha;
            document.getElementById('curso_recursos').value = training.recursos;
            document.getElementById('curso_comentarios').value = training.comentarios || '';

            editingTrainingIndex = index;
            // Update button text using i18n
            const btnText = window.i18n ? window.i18n.t('lblUpdateButton') : 'Actualizar';
            document.getElementById('btnAddCourse').innerHTML = '<i class="bi bi-check-circle"></i> <span id="lblUpdateButton">' + btnText + '</span>';
            document.getElementById('curso_nombre').focus();
        }

        function deleteTrainingRow(index) {
            const confirmMsg = window.i18n ? window.i18n.t('confirmDeleteCourse') : '¿Está seguro de eliminar este curso?';
            if (confirm(confirmMsg)) {
                trainingData.splice(index, 1);
                renderTrainingTable();
                updateTrainingHiddenInput();
            }
        }

        function updateTrainingHiddenInput() {
            document.getElementById('training_data').value = JSON.stringify(trainingData);
        }

        function formatDate(dateStr) {
            if (!dateStr) return '-';
            const date = new Date(dateStr + 'T00:00:00');
            return date.toLocaleDateString('es-ES', { year: 'numeric', month: 'short', day: 'numeric' });
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // ==================== TAB SCROLL ARROWS FOR EDIT PAGE ====================
        document.addEventListener('DOMContentLoaded', function() {
            const tabContainer = document.querySelector('#employeeTabs')?.closest('.tab-container-wrapper');
            const leftBtn = document.getElementById('editTabScrollLeft');
            const rightBtn = document.getElementById('editTabScrollRight');
            
            if (!tabContainer || !leftBtn || !rightBtn) return;
            
            function updateScrollArrows() {
                const scrollLeft = tabContainer.scrollLeft;
                const scrollWidth = tabContainer.scrollWidth;
                const clientWidth = tabContainer.clientWidth;
                const maxScroll = scrollWidth - clientWidth;
                
                if (maxScroll <= 1) {
                    leftBtn.style.display = 'none';
                    rightBtn.style.display = 'none';
                } else {
                    leftBtn.style.display = scrollLeft > 5 ? 'flex' : 'none';
                    rightBtn.style.display = scrollLeft < maxScroll - 5 ? 'flex' : 'none';
                }
            }
            
            function scrollTabs(direction) {
                const scrollAmount = 200;
                const targetScroll = tabContainer.scrollLeft + (direction === 'right' ? scrollAmount : -scrollAmount);
                tabContainer.scrollTo({ left: targetScroll, behavior: 'smooth' });
            }
            
            leftBtn.addEventListener('click', () => scrollTabs('left'));
            rightBtn.addEventListener('click', () => scrollTabs('right'));
            tabContainer.addEventListener('scroll', updateScrollArrows);
            window.addEventListener('resize', updateScrollArrows);
            
            setTimeout(updateScrollArrows, 100);
        });
    </script>
</body>
</html>
