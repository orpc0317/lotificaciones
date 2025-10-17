<?php
/**
 * Employee View Page (Read-Only)
 * Displays a single employee record in read-only mode
 * Part of multi-tab navigation system
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
    <title>Employee #<?= htmlspecialchars($empleado['codigo']) ?> - <?= htmlspecialchars($empleado['nombres'] . ' ' . $empleado['apellidos']) ?> | Lotificaciones</title>
    
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
        
        /* Photo preview styling */
        .photo-preview-container {
            max-width: 200px;
            margin: 0 auto;
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
                    <i class="bi bi-person-badge"></i>
                    <span id="pageTitle">Employee #<?= htmlspecialchars($empleado['codigo']) ?></span>
                </h1>
            </div>

            <!-- Page Content -->
            <div class="content-wrapper">
                <div class="container-fluid">
                    <!-- Action Buttons -->
                    <div class="row mb-3">
                        <div class="col-12 d-flex gap-2">
                            <button class="btn btn-secondary" onclick="window.close()">
                                <i class="bi bi-x-circle"></i> <span id="btnClose">Cerrar</span>
                            </button>
                            <a href="#" id="editButton" class="btn btn-primary" onclick="checkEditLockAndNavigate(event)">
                                <i class="bi bi-pencil"></i> <span id="btnEdit">Editar</span>
                            </a>
                            <button class="btn btn-danger ms-auto" onclick="deleteEmployee(<?= htmlspecialchars($empleado['id']) ?>)">
                                <i class="bi bi-trash"></i> <span id="btnDelete">Eliminar</span>
                            </button>
                        </div>
                    </div>

                    <!-- Employee Information - Modal-style Layout -->
                    <div class="row">
                        <div class="col-12">
                            <!-- Split Layout: Photo left, Tabs right -->
                            <div class="row">
                                <!-- Left Column: Photo -->
                                <div class="col-12 col-md-4">
                                    <div class="card">
                                        <div class="card-body text-center">
                                            <div class="photo-preview-container">
                                                <img src="uploads/<?= htmlspecialchars($empleado['foto'] ?? 'placeholder.png') ?>" 
                                                     alt="<?= htmlspecialchars($empleado['nombres'] . ' ' . $empleado['apellidos']) ?>" 
                                                     class="img-fluid rounded">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Right Column: Tabbed Information -->
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
                                                    <button class="nav-link rounded-0" id="contact-tab" data-bs-toggle="tab" data-bs-target="#contact" type="button" role="tab">
                                                        <i class="bi bi-telephone-fill"></i> <span id="tabContact">Contacto</span>
                                                    </button>
                                                </li>
                                                <li class="nav-item" role="presentation">
                                                    <button class="nav-link rounded-0" id="employment-tab" data-bs-toggle="tab" data-bs-target="#employment" type="button" role="tab">
                                                        <i class="bi bi-briefcase-fill"></i> <span id="tabEmployment">Laboral</span>
                                                    </button>
                                                </li>
                                                <li class="nav-item" role="presentation">
                                                    <button class="nav-link rounded-0" id="emergency-tab" data-bs-toggle="tab" data-bs-target="#emergency" type="button" role="tab">
                                                        <i class="bi bi-heart-pulse-fill"></i> <span id="tabEmergency">Emergencia</span>
                                                    </button>
                                                </li>
                                                <li class="nav-item" role="presentation">
                                                    <button class="nav-link rounded-0" id="medical-tab" data-bs-toggle="tab" data-bs-target="#medical" type="button" role="tab">
                                                        <i class="bi bi-clipboard2-pulse-fill"></i> <span id="tabMedical">Médico</span>
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
    
    <!-- SweetAlert2 for confirmation dialogs -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Custom Scripts -->
    <script src="assets/js/theme.js"></script>
    <script src="assets/js/layout.js"></script>
    
    <script>
        // Employee data for JavaScript
        const empleadoData = <?= json_encode($empleado, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?>;
        const empleadoId = <?= (int)$empleado['id'] ?>; // Numeric ID for routes
        const empleadoCodigo = <?= json_encode($empleado['codigo'], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>; // Code for display
        
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
        
        // Delete employee function
        function deleteEmployee(id) {
            Swal.fire({
                title: <?= json_encode('¿Está seguro?', JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
                text: <?= json_encode('Esta acción no se puede deshacer', JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: <?= json_encode('Sí, eliminar', JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
                cancelButtonText: <?= json_encode('Cancelar', JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>
            }).then((result) => {
                if (result.isConfirmed) {
                    // Get CSRF token from body data attribute
                    const csrfToken = document.body.getAttribute('data-csrf-token');
                    
                    fetch(api('empleados/delete'), {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: 'id=' + encodeURIComponent(id) + '&csrf_token=' + encodeURIComponent(csrfToken)
                    })
                    .then(r => r.json().catch(() => null))
                    .then(resp => {
                        if (resp && resp.success) {
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'success',
                                title: <?= json_encode('Empleado eliminado', JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
                                showConfirmButton: false,
                                timer: 1800
                            }).then(() => {
                                // Close current tab and go back to list
                                window.location.href = api('empleados');
                            });
                        } else {
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'error',
                                title: (resp && resp.error) || <?= json_encode('Error al eliminar', JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
                                showConfirmButton: false,
                                timer: 2500
                            });
                        }
                    })
                    .catch(err => {
                        console.error('delete failed', err);
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'error',
                            title: <?= json_encode('Error de conexión', JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
                            showConfirmButton: false,
                            timer: 2500
                        });
                    });
                }
            });
        }
        
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

            try {
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
                    if (event.data.action === 'lock-check' && event.data.empleadoId === empleadoCodigo) {
                        // Another tab is asking if we're editing - we're not (this is view mode)
                        // Do nothing
                    } else if (event.data.action === 'lock-exists' && event.data.empleadoId === empleadoCodigo) {
                        // Another tab responded - someone is already editing!
                        lockExists = true;
                        clearTimeout(timeout);
                        editChannel.close();
                        showEditBlockedMessage();
                    } else if (event.data.action === 'lock-acquired' && event.data.empleadoId === empleadoCodigo) {
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
                    empleadoId: empleadoCodigo 
                });
            } catch(error) {
                console.error('Error in checkEditLockAndNavigate:', error);
                // If there's an error, proceed anyway
                navigateToEditMode();
            }
        }

        function navigateToEditMode() {
            const url = api(`empleados/edit/${empleadoId}`);
            window.location.href = url;
        }

        function showEditBlockedMessage() {
            alert(<?= json_encode('Este empleado está siendo editado en otra pestaña.\n\nNo puede entrar en modo de edición hasta que la otra pestaña cierre o guarde los cambios.', JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>);
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
                    btnDelete: translations.delete || 'Eliminar',
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
