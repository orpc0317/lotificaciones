/**
 * Error Handler Module
 * 
 * Centralized error handling system for consistent logging,
 * reporting, and user notifications across the application.
 * 
 * @module errorHandler
 */

var ErrorHandler = (function() {
    'use strict';
    
    /**
     * Log error to console with context
     * @param {string} context - Where the error occurred
     * @param {Error|string} error - The error object or message
     * @param {Object} [metadata] - Additional context
     */
    function log(context, error, metadata) {
        var errorInfo = {
            context: context,
            message: error && error.message ? error.message : String(error),
            timestamp: new Date().toISOString()
        };
        
        if (error && error.stack) {
            errorInfo.stack = error.stack;
        }
        
        if (metadata) {
            errorInfo.metadata = metadata;
        }
        
        console.error('[' + context + ']', errorInfo);
        
        // Store last error for debugging
        try {
            window.__lastError = errorInfo;
        } catch(e) {}
    }
    
    /**
     * Show user-friendly error toast
     * @param {string} title - Error title
     * @param {string} [detail] - Optional error detail
     */
    function showToast(title, detail) {
        try {
            if (window.Swal) {
                Swal.fire({
                    toast: true,
                    position: TOAST_POSITION,
                    icon: 'error',
                    title: title || 'Ha ocurrido un error',
                    text: detail || '',
                    showConfirmButton: false,
                    timer: TOAST_ERROR_DURATION
                });
            } else {
                console.error('Toast notification:', title, detail);
            }
        } catch(e) {
            console.error('Error showing toast:', e);
        }
    }
    
    /**
     * Handle AJAX/fetch errors
     * @param {string} context - Operation context
     * @param {Error} error - The error object
     * @param {boolean} [showToast=true] - Show user notification
     */
    function handleAjaxError(context, error, showToast) {
        showToast = showToast !== false; // Default true
        
        var errorMessage = 'Error de conexión';
        if (error && error.message) {
            errorMessage = error.message;
        }
        
        log(context, error, { type: 'AJAX' });
        
        if (showToast) {
            showToast(errorMessage);
        }
    }
    
    /**
     * Handle validation errors
     * @param {string} context - Operation context
     * @param {Object} validationErrors - Validation error object
     */
    function handleValidationError(context, validationErrors) {
        log(context, 'Validation failed', { errors: validationErrors });
        
        var message = 'Por favor, corrija los errores en el formulario';
        if (validationErrors && typeof validationErrors === 'object') {
            var firstError = Object.values(validationErrors)[0];
            if (firstError) {
                message = Array.isArray(firstError) ? firstError[0] : firstError;
            }
        }
        
        showToast('Error de validación', message);
    }
    
    /**
     * Handle server errors from response
     * @param {string} context - Operation context
     * @param {Object} response - Server response object
     */
    function handleServerError(context, response) {
        var errorMessage = 'Error en el servidor';
        
        if (response && response.error) {
            errorMessage = response.error;
        } else if (response && response.message) {
            errorMessage = response.message;
        }
        
        log(context, errorMessage, { response: response });
        showToast(errorMessage);
    }
    
    // Public API
    return {
        log: log,
        showToast: showToast,
        handleAjaxError: handleAjaxError,
        handleValidationError: handleValidationError,
        handleServerError: handleServerError
    };
})();
