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
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editing Employee #<?= htmlspecialchars($empleado['codigo']) ?> - <?= htmlspecialchars($empleado['nombres'] . ' ' . $empleado['apellidos']) ?> | Lotificaciones</title>
    
    <!-- Bootstrap 5.3.2 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Custom Styles -->
    <link rel="stylesheet" href="../assets/css/theme.css">
    <link rel="stylesheet" href="../assets/css/layout.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    
    <style>
        .edit-lock-warning {
            position: fixed;
            top: 80px;
            right: 20px;
            z-index: 1050;
            max-width: 400px;
        }
        .form-section {
            background: var(--card-bg);
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
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
<body>
    <?php include __DIR__ . '/layouts/main.php'; ?>
    
    <!-- Edit Lock Warning (will be shown/hidden by JavaScript) -->
    <div id="editLockWarning" class="edit-lock-warning alert alert-warning alert-dismissible fade" role="alert" style="display: none;">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <strong id="warningTitle">¡Advertencia!</strong>
        <p class="mb-0" id="warningMessage">Este empleado está siendo editado en otra pestaña.</p>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    
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
                                <a href="../view/<?= htmlspecialchars($empleado['codigo']) ?>" class="btn btn-outline-secondary">
                                    <i class="bi bi-eye"></i> <span id="btnViewMode">Ver sin editar</span>
                                </a>
                                <a href="../empleados" class="btn btn-outline-secondary ms-auto">
                                    <i class="bi bi-list-ul"></i> <span id="btnBackToList">Volver a la lista</span>
                                </a>
                            </div>
                        </div>

                        <!-- Photo Section -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="form-section text-center">
                                    <div class="section-title"><i class="bi bi-camera-fill"></i> <span id="lblPhoto">Fotografía</span></div>
                                    <div class="photo-preview-container mb-3">
                                        <img id="photoPreview" 
                                             src="../uploads/<?= htmlspecialchars($empleado['foto'] ?? 'placeholder.png') ?>" 
                                             alt="Employee Photo" 
                                             class="photo-preview-img">
                                    </div>
                                    <input type="file" name="foto" id="photoInput" accept="image/*" class="d-none">
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="document.getElementById('photoInput').click()">
                                        <i class="bi bi-camera-fill"></i> <span id="btnChangePhoto">Cambiar Foto</span>
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Main Form Tabs -->
                            <div class="col-md-9">
                                <div class="form-section">
                                    <!-- Tab Navigation -->
                                    <ul class="nav nav-tabs mb-3" id="employeeTabs" role="tablist">
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link active" id="general-tab" data-bs-toggle="tab" data-bs-target="#general" type="button" role="tab">
                                                <i class="bi bi-person-fill"></i> <span id="tabGeneral">General</span>
                                            </button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link" id="personal-tab" data-bs-toggle="tab" data-bs-target="#personal" type="button" role="tab">
                                                <i class="bi bi-person-badge"></i> <span id="tabPersonal">Personal</span>
                                            </button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link" id="employment-tab" data-bs-toggle="tab" data-bs-target="#employment" type="button" role="tab">
                                                <i class="bi bi-briefcase-fill"></i> <span id="tabEmployment">Laboral</span>
                                            </button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link" id="contact-tab" data-bs-toggle="tab" data-bs-target="#contact" type="button" role="tab">
                                                <i class="bi bi-telephone-fill"></i> <span id="tabContact">Contacto</span>
                                            </button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link" id="address-tab" data-bs-toggle="tab" data-bs-target="#address" type="button" role="tab">
                                                <i class="bi bi-house-fill"></i> <span id="tabAddress">Dirección</span>
                                            </button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link" id="other-tab" data-bs-toggle="tab" data-bs-target="#other" type="button" role="tab">
                                                <i class="bi bi-three-dots"></i> <span id="tabOther">Otros</span>
                                            </button>
                                        </li>
                                    </ul>

                                    <!-- Tab Content -->
                                    <div class="tab-content" id="employeeTabContent">
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
    <script src="../assets/js/theme.js"></script>
    <script src="../assets/js/layout.js"></script>
    
    <script>
        // Employee data for JavaScript
        const empleadoData = <?= json_encode($empleado) ?>;
        const empleadoId = <?= json_encode($empleado['codigo']) ?>;
        
        // Cross-tab communication channel
        let editChannel = null;
        let formModified = false;
        let isEditingInOtherTab = false;

        document.addEventListener('DOMContentLoaded', function() {
            initializeEditPage();
            setupEditLockDetection();
            setupFormChangeTracking();
            setupPhotoPreview();
            loadTranslations();
        });

        // Update translations when language changes
        window.addEventListener('languageChanged', function(e) {
            loadTranslations();
        });

        function initializeEditPage() {
            const nombre = empleadoData.nombres + ' ' + empleadoData.apellidos;
            document.title = `Editing Employee #${empleadoData.codigo} - ${nombre} | Lotificaciones`;
        }

        // Edit lock detection using Broadcast Channel API
        function setupEditLockDetection() {
            if ('BroadcastChannel' in window) {
                editChannel = new BroadcastChannel('lotificaciones-employee-edit');
                
                // Listen for edit start messages from other tabs
                editChannel.onmessage = (event) => {
                    if (event.data.action === 'edit-started' && event.data.empleadoId === empleadoId) {
                        showEditLockWarning();
                        isEditingInOtherTab = true;
                    } else if (event.data.action === 'edit-closed' && event.data.empleadoId === empleadoId) {
                        hideEditLockWarning();
                        isEditingInOtherTab = false;
                    } else if (event.data.action === 'data-updated' && event.data.empleadoId === empleadoId) {
                        showDataUpdatedNotification();
                    }
                };
                
                // Announce that we're editing this employee
                editChannel.postMessage({ action: 'edit-started', empleadoId: empleadoId });
                
                // Cleanup on page unload
                window.addEventListener('beforeunload', function() {
                    editChannel.postMessage({ action: 'edit-closed', empleadoId: empleadoId });
                    editChannel.close();
                });
            }
        }

        function showEditLockWarning() {
            const warning = document.getElementById('editLockWarning');
            warning.style.display = 'block';
            setTimeout(() => {
                warning.classList.add('show');
            }, 10);
        }

        function hideEditLockWarning() {
            const warning = document.getElementById('editLockWarning');
            warning.classList.remove('show');
            setTimeout(() => {
                warning.style.display = 'none';
            }, 150);
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
                
                if (isEditingInOtherTab) {
                    if (!confirm('Este empleado está siendo editado en otra pestaña. ¿Desea continuar y sobrescribir los cambios?')) {
                        return;
                    }
                }
                
                const formData = new FormData(form);
                
                try {
                    const response = await fetch('../empleados/update', {
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
                        window.location.href = `../view/${empleadoId}`;
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
                    window.location.href = `../view/${empleadoId}`;
                }
            } else {
                window.location.href = `../view/${empleadoId}`;
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
                const response = await fetch(`../assets/i18n/${lang}.json`);
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
