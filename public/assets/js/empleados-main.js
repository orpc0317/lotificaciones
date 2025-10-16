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
    
})();
