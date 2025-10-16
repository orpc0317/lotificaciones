<?php
/**
 * Employee View Page (Read-Only)
 * Displays a single employee record in read-only mode
 * Part of multi-tab navigation system
 */

// Ensure $empleado is available from controller
if (!isset($empleado)) {
    http_response_code(404);
    echo "Empleado no encontrado";
    exit;
}

// Calculate APP_ROOT for base href
$APP_ROOT = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
if ($APP_ROOT === '') $APP_ROOT = '/';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <base href="<?= $APP_ROOT ?>/">
    <title>Employee #<?= htmlspecialchars($empleado['codigo']) ?> - <?= htmlspecialchars($empleado['nombres'] . ' ' . $empleado['apellidos']) ?> | Lotificaciones</title>
    
    <!-- Bootstrap 5.3.2 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Custom Styles -->
    <link rel="stylesheet" href="assets/css/theme.css">
    <link rel="stylesheet" href="assets/css/layout.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include __DIR__ . '/layouts/main.php'; ?>
    
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
                    <i class="bi bi-person-badge"></i>
                    <span id="pageTitle">Employee #<?= htmlspecialchars($empleado['codigo']) ?></span>
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
                    <!-- Action Buttons -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <button class="btn btn-secondary" onclick="window.close()">
                                <i class="bi bi-x-circle"></i> <span id="btnClose">Cerrar</span>
                            </button>
                            <a href="empleados" class="btn btn-outline-secondary">
                                <i class="bi bi-list-ul"></i> <span id="btnBackToList">Volver a la lista</span>
                            </a>
                            <a href="#" id="editButton" class="btn btn-primary" onclick="checkEditLockAndNavigate(event)">
                                <i class="bi bi-pencil"></i> <span id="btnEdit">Editar</span>
                            </a>
                        </div>
                    </div>

                    <!-- Employee Information Card -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="bi bi-info-circle"></i>
                                        <span id="cardTitleInfo">Información del Empleado</span>
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <!-- Tab Navigation -->
                                    <ul class="nav nav-tabs mb-3" id="employeeTabs" role="tablist">
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link active" id="general-tab" data-bs-toggle="tab" data-bs-target="#general" type="button" role="tab">
                                                <span id="tabGeneral">General</span>
                                            </button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link" id="contact-tab" data-bs-toggle="tab" data-bs-target="#contact" type="button" role="tab">
                                                <span id="tabContact">Contacto</span>
                                            </button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link" id="employment-tab" data-bs-toggle="tab" data-bs-target="#employment" type="button" role="tab">
                                                <span id="tabEmployment">Laboral</span>
                                            </button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link" id="emergency-tab" data-bs-toggle="tab" data-bs-target="#emergency" type="button" role="tab">
                                                <span id="tabEmergency">Emergencia</span>
                                            </button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link" id="medical-tab" data-bs-toggle="tab" data-bs-target="#medical" type="button" role="tab">
                                                <span id="tabMedical">Médico</span>
                                            </button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link" id="other-tab" data-bs-toggle="tab" data-bs-target="#other" type="button" role="tab">
                                                <span id="tabOther">Otros</span>
                                            </button>
                                        </li>
                                    </ul>

                                    <!-- Tab Content -->
                                    <div class="tab-content" id="employeeTabContent">
                                        <!-- General Tab -->
                                        <div class="tab-pane fade show active" id="general" role="tabpanel">
                                            <div class="row g-3">
                                                <div class="col-md-4">
                                                    <label class="form-label fw-bold"><span id="lblCode">Código</span>:</label>
                                                    <p class="form-control-plaintext"><?= htmlspecialchars($empleado['codigo']) ?></p>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label fw-bold"><span id="lblFirstName">Nombres</span>:</label>
                                                    <p class="form-control-plaintext"><?= htmlspecialchars($empleado['nombres']) ?></p>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label fw-bold"><span id="lblLastName">Apellidos</span>:</label>
                                                    <p class="form-control-plaintext"><?= htmlspecialchars($empleado['apellidos']) ?></p>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label fw-bold"><span id="lblGender">Género</span>:</label>
                                                    <p class="form-control-plaintext"><?= htmlspecialchars($empleado['genero']) ?></p>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label fw-bold"><span id="lblBirthDate">Fecha de Nacimiento</span>:</label>
                                                    <p class="form-control-plaintext"><?= htmlspecialchars($empleado['fecha_nacimiento'] ?? 'N/A') ?></p>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label fw-bold"><span id="lblAge">Edad</span>:</label>
                                                    <p class="form-control-plaintext"><?= htmlspecialchars($empleado['edad'] ?? 'N/A') ?></p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Contact Tab -->
                                        <div class="tab-pane fade" id="contact" role="tabpanel">
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label class="form-label fw-bold"><span id="lblPhone">Teléfono</span>:</label>
                                                    <p class="form-control-plaintext"><?= htmlspecialchars($empleado['telefono'] ?? 'N/A') ?></p>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label fw-bold"><span id="lblEmail">Email</span>:</label>
                                                    <p class="form-control-plaintext"><?= htmlspecialchars($empleado['email'] ?? 'N/A') ?></p>
                                                </div>
                                                <div class="col-12">
                                                    <label class="form-label fw-bold"><span id="lblAddress">Dirección</span>:</label>
                                                    <p class="form-control-plaintext"><?= htmlspecialchars($empleado['direccion'] ?? 'N/A') ?></p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Employment Tab -->
                                        <div class="tab-pane fade" id="employment" role="tabpanel">
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label class="form-label fw-bold"><span id="lblDepartment">Departamento</span>:</label>
                                                    <p class="form-control-plaintext"><?= htmlspecialchars($empleado['departamento'] ?? 'N/A') ?></p>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label fw-bold"><span id="lblPosition">Puesto</span>:</label>
                                                    <p class="form-control-plaintext"><?= htmlspecialchars($empleado['puesto'] ?? 'N/A') ?></p>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label fw-bold"><span id="lblHireDate">Fecha de Contratación</span>:</label>
                                                    <p class="form-control-plaintext"><?= htmlspecialchars($empleado['fecha_contratacion'] ?? 'N/A') ?></p>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label fw-bold"><span id="lblSalary">Salario</span>:</label>
                                                    <p class="form-control-plaintext"><?= htmlspecialchars($empleado['salario'] ?? 'N/A') ?></p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Emergency Tab -->
                                        <div class="tab-pane fade" id="emergency" role="tabpanel">
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label class="form-label fw-bold"><span id="lblEmergencyContact">Contacto de Emergencia</span>:</label>
                                                    <p class="form-control-plaintext"><?= htmlspecialchars($empleado['contacto_emergencia'] ?? 'N/A') ?></p>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label fw-bold"><span id="lblEmergencyPhone">Teléfono de Emergencia</span>:</label>
                                                    <p class="form-control-plaintext"><?= htmlspecialchars($empleado['telefono_emergencia'] ?? 'N/A') ?></p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Medical Tab -->
                                        <div class="tab-pane fade" id="medical" role="tabpanel">
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label class="form-label fw-bold"><span id="lblBloodType">Tipo de Sangre</span>:</label>
                                                    <p class="form-control-plaintext"><?= htmlspecialchars($empleado['tipo_sangre'] ?? 'N/A') ?></p>
                                                </div>
                                                <div class="col-12">
                                                    <label class="form-label fw-bold"><span id="lblAllergies">Alergias</span>:</label>
                                                    <p class="form-control-plaintext"><?= htmlspecialchars($empleado['alergias'] ?? 'N/A') ?></p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Other Tab -->
                                        <div class="tab-pane fade" id="other" role="tabpanel">
                                            <div class="row g-3">
                                                <div class="col-12">
                                                    <label class="form-label fw-bold"><span id="lblComments">Comentarios</span>:</label>
                                                    <p class="form-control-plaintext"><?= htmlspecialchars($empleado['comentarios'] ?? 'N/A') ?></p>
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

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom Scripts -->
    <script src="assets/js/theme.js"></script>
    <script src="assets/js/layout.js"></script>
    
    <script>
        // Employee data for JavaScript
        const empleadoData = <?= json_encode($empleado) ?>;
        const empleadoId = <?= json_encode($empleado['codigo']) ?>;
        
        // Set dynamic page title with employee name
        document.addEventListener('DOMContentLoaded', function() {
            updatePageTitle();
            loadTranslations();
        });

        // Update page title when language changes
        window.addEventListener('languageChanged', function(e) {
            loadTranslations();
        });

        function updatePageTitle() {
            const nombre = empleadoData.nombres + ' ' + empleadoData.apellidos;
            document.title = `Employee #${empleadoData.codigo} - ${nombre} | Lotificaciones`;
        }

        // Check if record is being edited before navigating to edit mode
        function checkEditLockAndNavigate(event) {
            event.preventDefault();
            
            if (!('BroadcastChannel' in window)) {
                // Browser doesn't support Broadcast Channel, proceed anyway
                navigateToEditMode();
                return;
            }

            const editChannel = new BroadcastChannel('lotificaciones-employee-edit');
            let lockExists = false;
            let checkComplete = false;

            const timeout = setTimeout(() => {
                if (!lockExists) {
                    // No response = no one is editing, safe to proceed
                    checkComplete = true;
                    editChannel.close();
                    navigateToEditMode();
                }
            }, 200); // Wait 200ms for responses

            // Listen for lock status responses
            editChannel.onmessage = (event) => {
                if (event.data.action === 'lock-check' && event.data.empleadoId === empleadoId) {
                    // Another tab is asking if we're editing - we're not (this is view mode)
                    // Do nothing
                } else if (event.data.action === 'lock-exists' && event.data.empleadoId === empleadoId) {
                    // Another tab responded - someone is already editing!
                    lockExists = true;
                    clearTimeout(timeout);
                    editChannel.close();
                    showEditBlockedMessage();
                } else if (event.data.action === 'lock-acquired' && event.data.empleadoId === empleadoId) {
                    // Someone just acquired the lock
                    lockExists = true;
                    clearTimeout(timeout);
                    editChannel.close();
                    showEditBlockedMessage();
                }
            };

            // Ask if anyone else is editing this employee
            editChannel.postMessage({ 
                action: 'lock-check', 
                empleadoId: empleadoId 
            });
        }

        function navigateToEditMode() {
            window.location.href = `empleados/edit/${empleadoId}`;
        }

        function showEditBlockedMessage() {
            alert('Este empleado está siendo editado en otra pestaña.\n\nNo puede entrar en modo de edición hasta que la otra pestaña cierre o guarde los cambios.');
        }

        async function loadTranslations() {
            const lang = window.themeManager ? window.themeManager.getLanguage() : 'es';
            
            try {
                const response = await fetch(`assets/i18n/${lang}.json`);
                const translations = await response.json();
                
                // Update UI text
                const elements = {
                    pageTitle: `${translations.employee || 'Employee'} #${empleadoData.codigo}`,
                    btnClose: translations.close || 'Cerrar',
                    btnBackToList: translations.back_to_list || 'Volver a la lista',
                    btnEdit: translations.edit || 'Editar',
                    cardTitleInfo: translations.employee_information || 'Información del Empleado',
                    
                    // Tab titles
                    tabGeneral: translations.general || 'General',
                    tabContact: translations.contact || 'Contacto',
                    tabEmployment: translations.employment || 'Laboral',
                    tabEmergency: translations.emergency || 'Emergencia',
                    tabMedical: translations.medical || 'Médico',
                    tabOther: translations.other || 'Otros',
                    
                    // Field labels
                    lblCode: translations.codigo || 'Código',
                    lblFirstName: translations.nombres || 'Nombres',
                    lblLastName: translations.apellidos || 'Apellidos',
                    lblGender: translations.genero || 'Género',
                    lblBirthDate: translations.fecha_nacimiento || 'Fecha de Nacimiento',
                    lblAge: translations.edad || 'Edad',
                    lblPhone: translations.telefono || 'Teléfono',
                    lblEmail: translations.email || 'Email',
                    lblAddress: translations.direccion || 'Dirección',
                    lblDepartment: translations.departamento || 'Departamento',
                    lblPosition: translations.puesto || 'Puesto',
                    lblHireDate: translations.fecha_contratacion || 'Fecha de Contratación',
                    lblSalary: translations.salario || 'Salario',
                    lblEmergencyContact: translations.contacto_emergencia || 'Contacto de Emergencia',
                    lblEmergencyPhone: translations.telefono_emergencia || 'Teléfono de Emergencia',
                    lblBloodType: translations.tipo_sangre || 'Tipo de Sangre',
                    lblAllergies: translations.alergias || 'Alergias',
                    lblComments: translations.comentarios || 'Comentarios'
                };
                
                Object.keys(elements).forEach(id => {
                    const el = document.getElementById(id);
                    if (el) el.textContent = elements[id];
                });
            } catch (error) {
                console.error('Error loading translations:', error);
            }
        }
    </script>
</body>
</html>
