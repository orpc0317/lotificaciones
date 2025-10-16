/**
 * i18n Module
 * 
 * Handles internationalization, translations, and cross-tab synchronization
 * 
 * @module i18n
 */

var I18nModule = (function() {
    'use strict';
    
    // Private variables
    var translations = {};
    var employeeChannel = null;
    
    /**
     * Initialize Broadcast Channel for cross-tab sync
     * @param {Function} onDataUpdated - Callback when data is updated in another tab
     */
    function initCrossTabSync(onDataUpdated) {
        if ('BroadcastChannel' in window) {
            try {
                employeeChannel = new BroadcastChannel(CHANNEL_EMPLOYEE_EDIT);
                
                // Listen for updates from other tabs
                employeeChannel.onmessage = function(event) {
                    if (event.data.action === 'data-updated') {
                        console.log('Employee data updated in another tab:', event.data.empleadoId);
                        showUpdateNotification();
                        if (onDataUpdated) {
                            onDataUpdated();
                        }
                    }
                };
                
                console.log('Cross-tab sync initialized');
            } catch (e) {
                ErrorHandler.log('CrossTabSync', e, { feature: 'BroadcastChannel' });
            }
        } else {
            console.warn('Broadcast Channel API not supported in this browser');
        }
    }
    
    /**
     * Broadcast data update to other tabs
     * @param {number} empleadoId - ID of the updated employee
     */
    function broadcastUpdate(empleadoId) {
        if (employeeChannel) {
            try {
                employeeChannel.postMessage({
                    action: 'data-updated',
                    empleadoId: empleadoId
                });
            } catch(e) {
                ErrorHandler.log('BroadcastUpdate', e, { empleadoId: empleadoId });
            }
        }
    }
    
    /**
     * Show notification when data is updated in another tab
     */
    function showUpdateNotification() {
        try {
            if (window.Swal) {
                Swal.fire({
                    toast: true,
                    position: TOAST_POSITION,
                    icon: 'info',
                    title: 'Datos actualizados en otra pestaña',
                    showConfirmButton: false,
                    timer: TOAST_INFO_DURATION
                });
            } else {
                console.info('Employee data updated in another tab');
            }
        } catch (e) {
            ErrorHandler.log('showUpdateNotification', e);
        }
    }
    
    /**
     * Load and apply translations for given language
     * @param {string} lang - Language code (es, en)
     * @returns {Promise} Promise resolving to translation data
     */
    function loadTranslations(lang) {
        return fetch('assets/i18n/' + lang + '.json')
            .then(function(r) { return r.json(); })
            .then(function(data) {
                translations = data;
                applyTranslations();
                return data;
            })
            .catch(function(err) {
                ErrorHandler.log('LoadTranslations', err, { lang: lang });
                return {};
            });
    }
    
    /**
     * Apply translations to elements with data-i18n attribute
     */
    function applyTranslations() {
        try {
            document.querySelectorAll('[data-i18n]').forEach(function(el) {
                var key = el.getAttribute('data-i18n');
                if (translations[key]) {
                    // For input placeholders and select options
                    if (el.tagName === 'OPTION' || el.tagName === 'INPUT') {
                        if (el.hasAttribute('placeholder')) {
                            el.placeholder = translations[key];
                        } else {
                            el.textContent = translations[key];
                        }
                    } else {
                        // For spans, buttons, labels, etc.
                        el.textContent = translations[key];
                    }
                }
            });
        } catch(e) {
            ErrorHandler.log('ApplyTranslations', e);
        }
    }
    
    /**
     * Get translation for a key
     * @param {string} key - Translation key
     * @returns {string} Translated string or key if not found
     */
    function get(key) {
        return translations[key] || key;
    }
    
    /**
     * Get all translations
     * @returns {Object} All translations
     */
    function getAll() {
        return translations;
    }
    
    // Public API
    return {
        initCrossTabSync: initCrossTabSync,
        broadcastUpdate: broadcastUpdate,
        loadTranslations: loadTranslations,
        applyTranslations: applyTranslations,
        get: get,
        getAll: getAll
    };
})();
