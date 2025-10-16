<?php
/**
 * Employee Edit Page
 * Displays a single employee record in edit mode with save functionality
 * Part of multi-tab navigation system with conflict detection
 */

// Ensure $empleado is available from controller
if (!isset($empleado)) {
    http_response_code(404);
    echo "Empleado no encontrado";
    exit;
}

// Calculate APP_ROOT for base href (normalize to forward slashes for URLs)
// SCRIPT_NAME will be something like /lotificaciones/public/index.php
$APP_ROOT = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
$APP_ROOT = rtrim($APP_ROOT, '/');
// Ensure trailing slash for base href
$baseHref = $APP_ROOT . '/';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <base href="<?= $baseHref ?>">
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
    </style>
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
<body>
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
                <div class="top-bar-actions">
                    <div class="theme-palette-selector">
                        <span class="palette-swatch" data-palette="blue" title="Blue"></span>
                        <span class="palette-swatch" data-palette="teal" title="Teal"></span>
                        <span class="palette-swatch" data-palette="violet" title="Violet"></span>
                    </div>
                    <select id="languageSelector" class="form-select form-select-sm">
                        <option value="es">Español</option>
                        <option value="en">English</option>
                    </select>
                </div>
            </div>

            <!-- Page Content -->
            <div class="content-wrapper">
                <div class="container-fluid">
                    <!-- Form -->
                    <form id="formEditEmpleado" method="post" enctype="multipart/form-data">
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
                                <a href="empleados/view/<?= htmlspecialchars($empleado['codigo']) ?>" class="btn btn-outline-secondary">
                                    <i class="bi bi-eye"></i> <span id="btnViewMode">Ver sin editar</span>
                                </a>
                                <a href="empleados" class="btn btn-outline-secondary ms-auto">
                                    <i class="bi bi-list-ul"></i> <span id="btnBackToList">Volver a la lista</span>
                                </a>
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
                                    <div class="card-header section-accent" style="padding:0;">
                                        <ul class="nav nav-tabs" id="employeeTabs" role="tablist">
                                            <li class="nav-item" role="presentation">
                                                <button class="nav-link active rounded-0" id="general-tab" data-bs-toggle="tab" data-bs-target="#general" type="button" role="tab">
                                                    <i class="bi bi-person-fill"></i> <span id="tabGeneral">General</span>
                                                </button>
                                            </li>
                                            <li class="nav-item" role="presentation">
                                                <button class="nav-link rounded-0" id="personal-tab" data-bs-toggle="tab" data-bs-target="#personal" type="button" role="tab">
                                                    <i class="bi bi-person-badge"></i> <span id="tabPersonal">Personal</span>
                                                </button>
                                            </li>
                                            <li class="nav-item" role="presentation">
                                                <button class="nav-link rounded-0" id="employment-tab" data-bs-toggle="tab" data-bs-target="#employment" type="button" role="tab">
                                                    <i class="bi bi-briefcase-fill"></i> <span id="tabEmployment">Laboral</span>
                                                </button>
                                            </li>
                                            <li class="nav-item" role="presentation">
                                                <button class="nav-link rounded-0" id="contact-tab" data-bs-toggle="tab" data-bs-target="#contact" type="button" role="tab">
                                                    <i class="bi bi-telephone-fill"></i> <span id="tabContact">Contacto</span>
                                                </button>
                                        </li>
                                            </li>
                                            <li class="nav-item" role="presentation">
                                                <button class="nav-link rounded-0" id="address-tab" data-bs-toggle="tab" data-bs-target="#address" type="button" role="tab">
                                                    <i class="bi bi-house-fill"></i> <span id="tabAddress">Dirección</span>
                                                </button>
                                            </li>
                                            <li class="nav-item" role="presentation">
                                                <button class="nav-link rounded-0" id="other-tab" data-bs-toggle="tab" data-bs-target="#other" type="button" role="tab">
                                                    <i class="bi bi-three-dots"></i> <span id="tabOther">Otros</span>
                                                </button>
                                            </li>
                                        </ul>
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
        const empleadoData = <?= json_encode($empleado) ?>;
        const empleadoId = <?= json_encode($empleado['codigo']) ?>;
        
        // Cross-tab communication channel
        let editChannel = null;
        let formModified = false;
        let editLockAcquired = false;
        let checkingLock = false;

        document.addEventListener('DOMContentLoaded', function() {
            checkEditLockAndProceed();
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
            window.location.href = `empleados/view/${empleadoId}`;
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
                        window.location.href = `empleados/view/${empleadoId}`;
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
                    window.location.href = `empleados/view/${empleadoId}`;
                }
            } else {
                window.location.href = `empleados/view/${empleadoId}`;
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
                    btnBackToList: t.back_to_list || 'Volver a la lista',
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
    </script>
</body>
</html>
