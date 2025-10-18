/**
 * Empleados Main Application
 * 
 * Coordinates all modules for the employee management system.
 * This is the main entry point that initializes and connects all modules.
 * 
 * @requires constants.js
 * @requires modules/errorHandler.js
 * @requires modules/i18n.js
 * @requires modules/dataTable.js
 * @requires modules/fileUpload.js
 * @requires modules/formHandlers.js
 * @requires modules/validation.js
 */

(function() {
    'use strict';
    
    // ========================================
    // PASSIVE EVENT LISTENERS
    // ========================================
    
    // Configure passive events to suppress Chrome warnings
    try {
        var supportsPassive = false;
        try {
            var opts = Object.defineProperty({}, 'passive', {
                get: function() { supportsPassive = true; }
            });
            window.addEventListener('test', null, opts);
            window.removeEventListener('test', null, opts);
        } catch (e) {}
        
        if (supportsPassive) {
            var addEvent = EventTarget.prototype.addEventListener;
            EventTarget.prototype.addEventListener = function(type, listener, options) {
                var opts = options;
                if (typeof opts === 'boolean') {
                    opts = { capture: opts };
                } else if (typeof opts === 'object') {
                    opts = Object.assign({}, opts);
                }
                if (opts && typeof opts === 'object') {
                    if (type === 'touchstart' || type === 'touchmove' || type === 'wheel' || type === 'mousewheel') {
                        if (opts.passive === undefined) {
                            opts.passive = true;
                        }
                    }
                }
                addEvent.call(this, type, listener, opts);
            };
        }
    } catch (e) { /* Silent catch - passive events not supported */ }
    
    // ========================================
    // FILTER HANDLERS
    // ========================================
    
    /**
     * Handle filter form submission
     */
    function setupFilterHandlers() {
        // Filter form submit handler
        $(document).on('submit', '#filterForm', function(e) {
            e.preventDefault();
            try {
                var table = DataTableModule.getTable();
                if (table) {
                    table.ajax.reload();
                }
            } catch(err) {
                ErrorHandler.log('FilterForm.Submit', err);
            }
            return false;
        });
        
        // Clear filters button handler
        $(document).on('click', '#clearFilters', function() {
            try {
                $('#filterForm')[0].reset();
                var table = DataTableModule.getTable();
                if (table) {
                    table.ajax.reload();
                }
            } catch(err) {
                ErrorHandler.log('FilterForm.Clear', err);
            }
        });
    }
    
    // ========================================
    // LANGUAGE HANDLER
    // ========================================
    
    /**
     * Setup language selector handler
     */
    function setupLanguageHandler() {
        try {
            var langSel = document.getElementById('langSelect');
            if (langSel) {
                langSel.addEventListener('change', function() {
                    console.log('[langSelect] Language changed to:', langSel.value);
                    try {
                        localStorage.setItem('lotificaciones_lang', langSel.value);
                    } catch(e) {}
                    try {
                        I18nModule.loadTranslations(langSel.value);
                    } catch(e) {
                        ErrorHandler.log('LanguageChange.LoadTranslations', e);
                    }
                    try {
                        DataTableModule.reloadOrBuild();
                    } catch(e) {
                        ErrorHandler.log('LanguageChange.ReloadTable', e);
                    }
                });
            }
        } catch(e) {
            ErrorHandler.log('SetupLanguageHandler', e);
        }
    }
    
    // ========================================
    // INITIALIZATION
    // ========================================
    
    /**
     * Initialize the application
     */
    function init() {
        try {
            // Initialize cross-tab sync
            I18nModule.initCrossTabSync(function() {
                DataTableModule.reloadOrBuild();
            });
            
            // Load translations
            var lang = DataTableModule.getCurrentLang();
            I18nModule.loadTranslations(lang).then(function() {
                // Build DataTable
                DataTableModule.buildTable(lang, I18nModule.getAll());
            });
            
            // Initialize modules
            FileUploadModule.init();
            FormHandlersModule.init();
            ValidationModule.init();
            
            // Setup handlers
            setupFilterHandlers();
            setupLanguageHandler();
            
            console.log('Empleados application initialized successfully');
        } catch(e) {
            ErrorHandler.log('Application.Init', e);
        }
    }
    
    // Initialize when DOM is ready
    $(document).ready(function() {
        init();
    });

// ==================== NEW EMPLOYEE TRAINING FUNCTIONALITY ====================
// Training data for new employee modal
let newTrainingData = [];
let editingNewTrainingIndex = -1;

function addNewTrainingRow() {
    const nombre = document.getElementById('nuevo_curso_nombre').value.trim();
    const fecha = document.getElementById('nuevo_curso_fecha').value;
    const recursos = document.getElementById('nuevo_curso_recursos').value;
    const comentarios = document.getElementById('nuevo_curso_comentarios').value.trim();
    
    if (!nombre) {
        alert(window.i18n ? window.i18n.t('alertCourseName') : 'Por favor ingrese el nombre del curso');
        return;
    }
    
    if (!fecha) {
        alert(window.i18n ? window.i18n.t('alertCourseDate') : 'Por favor seleccione la fecha');
        return;
    }
    
    const training = {
        nombre: nombre,
        fecha_aprobado: fecha,
        recursos_aprobados: parseFloat(recursos) || 0,
        comentarios: comentarios
    };
    
    if (editingNewTrainingIndex >= 0) {
        newTrainingData[editingNewTrainingIndex] = training;
        editingNewTrainingIndex = -1;
        document.getElementById('btnAddNewTraining').innerHTML = '<i class="bi bi-plus-circle"></i> <span id="lblAddButton">Agregar</span>';
        document.getElementById('btnCancelNewTraining').classList.add('d-none');
    } else {
        newTrainingData.push(training);
    }
    
    renderNewTrainingTable();
    updateNewTrainingHiddenInput();
    
    // Clear form
    ['nuevo_curso_nombre', 'nuevo_curso_fecha', 'nuevo_curso_recursos', 'nuevo_curso_comentarios'].forEach(id => {
        document.getElementById(id).value = '';
    });
}

function renderNewTrainingTable() {
    const tbody = document.getElementById('newTrainingTableBody');
    const emptyRow = document.getElementById('emptyNewTrainingRow');
    
    if (newTrainingData.length === 0) {
        emptyRow.style.display = '';
        return;
    }
    
    emptyRow.style.display = 'none';
    
    const rows = newTrainingData.map((training, index) => `
        <tr>
            <td>${escapeHtml(training.nombre)}</td>
            <td>${formatDate(training.fecha_aprobado)}</td>
            <td class="text-end">$${parseFloat(training.recursos_aprobados).toFixed(2)}</td>
            <td>${escapeHtml(training.comentarios)}</td>
            <td>
                <button type="button" class="btn btn-sm btn-primary" onclick="editNewTrainingRow(${index})">
                    <i class="bi bi-pencil"></i>
                </button>
                <button type="button" class="btn btn-sm btn-danger" onclick="deleteNewTrainingRow(${index})">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        </tr>
    `).join('');
    
    tbody.innerHTML = rows + emptyRow.outerHTML;
}

function editNewTrainingRow(index) {
    const training = newTrainingData[index];
    document.getElementById('nuevo_curso_nombre').value = training.nombre;
    document.getElementById('nuevo_curso_fecha').value = training.fecha_aprobado;
    document.getElementById('nuevo_curso_recursos').value = training.recursos_aprobados;
    document.getElementById('nuevo_curso_comentarios').value = training.comentarios;
    
    editingNewTrainingIndex = index;
    document.getElementById('btnAddNewTraining').innerHTML = '<i class="bi bi-check-circle"></i> <span id="lblUpdateButton">Actualizar</span>';
    document.getElementById('btnCancelNewTraining').classList.remove('d-none');
}

function cancelNewTrainingEdit() {
    editingNewTrainingIndex = -1;
    document.getElementById('btnAddNewTraining').innerHTML = '<i class="bi bi-plus-circle"></i> <span id="lblAddButton">Agregar</span>';
    document.getElementById('btnCancelNewTraining').classList.add('d-none');
    
    ['nuevo_curso_nombre', 'nuevo_curso_fecha', 'nuevo_curso_recursos', 'nuevo_curso_comentarios'].forEach(id => {
        document.getElementById(id).value = '';
    });
}

function deleteNewTrainingRow(index) {
    const confirmMsg = window.i18n ? window.i18n.t('confirmDeleteCourse') : '¿Está seguro de eliminar este curso?';
    if (confirm(confirmMsg)) {
        newTrainingData.splice(index, 1);
        renderNewTrainingTable();
        updateNewTrainingHiddenInput();
        
        if (editingNewTrainingIndex === index) {
            cancelNewTrainingEdit();
        }
    }
}

function updateNewTrainingHiddenInput() {
    document.getElementById('nuevo_training_data').value = JSON.stringify(newTrainingData);
}

function formatDate(dateStr) {
    if (!dateStr) return '';
    const date = new Date(dateStr + 'T00:00:00');
    return date.toLocaleDateString();
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// ==================== TAB SCROLL ARROWS FUNCTIONALITY ====================
// Initialize tab scroll arrows for new employee modal
$(document).ready(function() {
    // Target the wrapper div, not the ul parent
    const tabContainer = document.querySelector('.tab-container-wrapper');
    const leftBtn = document.getElementById('newTabScrollLeft');
    const rightBtn = document.getElementById('newTabScrollRight');
    
    if (!tabContainer || !leftBtn || !rightBtn) {
        console.error('Tab scroll elements not found:', { tabContainer, leftBtn, rightBtn });
        return;
    }
    
    console.log('Tab scroll arrows initialized:', { tabContainer, leftBtn, rightBtn });
    
    // Check if scrolling is needed and update arrow visibility
    function updateScrollArrows() {
        const scrollLeft = tabContainer.scrollLeft;
        const scrollWidth = tabContainer.scrollWidth;
        const clientWidth = tabContainer.clientWidth;
        const maxScroll = scrollWidth - clientWidth;
        
        console.log('Scroll check:', { scrollLeft, scrollWidth, clientWidth, maxScroll });
        
        // Show/hide arrows based on scroll position (use 1px threshold for sensitivity)
        if (maxScroll <= 1) {
            // No scrolling needed - hide both arrows
            leftBtn.style.display = 'none';
            rightBtn.style.display = 'none';
            console.log('No scrolling needed');
        } else {
            // Scrolling is possible - show appropriate arrows
            const showLeft = scrollLeft > 5; // More sensitive - show after 5px scroll
            const showRight = scrollLeft < maxScroll - 5; // Show if more than 5px left
            
            leftBtn.style.display = showLeft ? 'flex' : 'none';
            rightBtn.style.display = showRight ? 'flex' : 'none';
            
            console.log('Arrows:', { showLeft, showRight });
        }
    }
    
    // Scroll tab container
    function scrollTabs(direction) {
        const scrollAmount = 200;
        const targetScroll = tabContainer.scrollLeft + (direction === 'right' ? scrollAmount : -scrollAmount);
        tabContainer.scrollTo({
            left: targetScroll,
            behavior: 'smooth'
        });
    }
    
    // Event listeners
    leftBtn.addEventListener('click', () => scrollTabs('left'));
    rightBtn.addEventListener('click', () => scrollTabs('right'));
    tabContainer.addEventListener('scroll', updateScrollArrows);
    window.addEventListener('resize', updateScrollArrows);
    
    // Initial check
    setTimeout(updateScrollArrows, 100);
    
    // Force check on window resize
    let resizeTimeout;
    $(window).on('resize', function() {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(updateScrollArrows, 100);
    });
    
    // Pulse right arrow on modal open to indicate more tabs
    $('#modalNuevoEmpleado').on('shown.bs.modal', function() {
        console.log('Modal opened, checking scroll arrows...');
        setTimeout(() => {
            // Force multiple checks with delays
            updateScrollArrows();
            setTimeout(updateScrollArrows, 100);
            setTimeout(updateScrollArrows, 300);
            setTimeout(updateScrollArrows, 500);
            
            if (rightBtn.style.display !== 'none') {
                console.log('Pulsing right arrow');
                rightBtn.classList.add('pulse');
                setTimeout(() => rightBtn.classList.remove('pulse'), 4500);
            } else {
                console.log('Right arrow is hidden - checking why...');
                console.log('Container:', tabContainer);
                console.log('ScrollWidth:', tabContainer.scrollWidth);
                console.log('ClientWidth:', tabContainer.clientWidth);
            }
        }, 300);
    });
});
})();
