/**
 * Validation Module
 * 
 * Handles client-side form validation, tab validation badges,
 * and real-time validation feedback.
 * 
 * @module validation
 */

var ValidationModule = (function() {
    'use strict';
    
    /**
     * Update validation badges for form tabs
     * @param {string} formId - Form ID to validate
     */
    function updateTabBadges(formId) {
        try {
            var form = document.getElementById(formId);
            if (!form) return;
            
            var tabPaneIds = [];
            if (formId === 'formNuevo' || formId === 'formNuevoEmpleado') {
                tabPaneIds = ['new-generals', 'new-personal', 'new-puesto', 'new-contact', 'new-address', 'new-others'];
            } else if (formId === 'formEditar') {
                tabPaneIds = ['edit-generals', 'edit-personal', 'edit-puesto', 'edit-contact', 'edit-address', 'edit-others'];
            }
            
            tabPaneIds.forEach(function(tabId) {
                try {
                    var pane = document.getElementById(tabId);
                    if (!pane) return;
                    
                    // Find all required fields in this tab
                    var required = pane.querySelectorAll('input[required], textarea[required], select[required]');
                    var invalidCount = 0;
                    
                    required.forEach(function(field) {
                        var val = (field.value || '').trim();
                        if (!val || val === '') invalidCount++;
                    });
                    
                    // Update badge
                    var badge = document.querySelector('.badge-tab[data-tab="' + tabId + '"]');
                    if (badge) {
                        if (invalidCount > 0) {
                            badge.textContent = invalidCount;
                            badge.style.display = 'inline-block';
                            badge.style.background = '#dc3545';
                            badge.style.color = '#fff';
                        } else {
                            badge.style.display = 'none';
                        }
                    }
                    
                    // Update tab title underline
                    var tabButton = document.getElementById(tabId + '-tab');
                    if (tabButton) {
                        if (invalidCount > 0) {
                            tabButton.classList.add('tab-invalid');
                        } else {
                            tabButton.classList.remove('tab-invalid');
                        }
                    }
                } catch(e) {
                    ErrorHandler.log('ValidationModule.UpdateTabBadge', e, { tabId: tabId });
                }
            });
        } catch(e) {
            ErrorHandler.log('ValidationModule.UpdateTabBadges', e, { formId: formId });
        }
    }
    
    /**
     * Attach input event listeners to all required fields
     */
    function attachValidationListeners() {
        try {
            var forms = ['formNuevo', 'formNuevoEmpleado', 'formEditar'];
            forms.forEach(function(formId) {
                var form = document.getElementById(formId);
                if (!form) return;
                
                var required = form.querySelectorAll('input[required], textarea[required], select[required]');
                required.forEach(function(field) {
                    ['input', 'change', 'blur'].forEach(function(evt) {
                        field.addEventListener(evt, function() {
                            updateTabBadges(formId);
                        });
                    });
                });
            });
        } catch(e) {
            ErrorHandler.log('ValidationModule.AttachListeners', e);
        }
    }
    
    /**
     * Reset photo preview when modal is shown
     */
    function setupModalResetHandlers() {
        // Reset photo preview when New modal is about to be shown
        $(document).on('show.bs.modal', '#modalNuevoEmpleado', function() {
            try {
                // Reset the entire form
                var form = document.getElementById('formNuevoEmpleado');
                if (form) form.reset();
                
                // Reset photo preview to placeholder
                var preview = document.getElementById('nuevo_foto_preview');
                if (preview) preview.src = 'uploads/placeholder.png';
                
                // Clear file input
                var fileInput = document.getElementById('nuevo_foto');
                if (fileInput) fileInput.value = '';
            } catch(e) {
                ErrorHandler.log('ValidationModule.ModalReset', e);
            }
        });
        
        // Update badges when New modal is shown
        $(document).on('shown.bs.modal', '#modalNuevoEmpleado', function() {
            updateTabBadges('formNuevoEmpleado');
        });
        
        // Update badges when Edit modal is shown
        $(document).on('shown.bs.modal', '#modalEditar', function() {
            updateTabBadges('formEditar');
        });
    }
    
    /**
     * Initialize validation module
     */
    function init() {
        attachValidationListeners();
        setupModalResetHandlers();
    }
    
    // Public API
    return {
        init: init,
        updateTabBadges: updateTabBadges
    };
})();
