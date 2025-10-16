/**
 * Form Handlers Module
 * 
 * Manages employee form submissions including create, update, and delete operations.
 * 
 * @module formHandlers
 */

var FormHandlersModule = (function() {
    'use strict';
    
    /**
     * Set button loading state
     * @param {jQuery} $btn - jQuery button element
     * @param {boolean} isLoading - Loading state
     */
    function setButtonLoading($btn, isLoading) {
        try {
            if (isLoading) {
                // Store original text and add spinner
                var originalText = $btn.html();
                $btn.data('original-text', originalText);
                $btn.prop('disabled', true);
                $btn.html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Procesando...');
            } else {
                // Restore original text
                var originalText = $btn.data('original-text');
                if (originalText) {
                    $btn.html(originalText);
                }
                $btn.prop('disabled', false);
            }
        } catch(e) {
            ErrorHandler.log('setButtonLoading', e, { isLoading: isLoading });
        }
    }
    
    /**
     * Handle create employee form submission
     */
    function handleCreateForm() {
        $(document).on('submit', '#formNuevoEmpleado, #formEmpleado', function(ev) {
            ev.preventDefault();
            try {
                var $form = $(this);
                var fd = new FormData($form.get(0));
                var $btn = $form.find('button[type=submit]');
                
                setButtonLoading($btn, true);
                
                fetch(DataTableModule.api('empleados/create'), { method: 'POST', body: fd })
                    .then(function(r) { return r.json().catch(function() { return null; }); })
                    .then(function(resp) {
                        try {
                            if (resp && resp.success) {
                                try {
                                    Swal.fire({
                                        toast: true,
                                        position: TOAST_POSITION,
                                        icon: 'success',
                                        title: resp.message || 'Empleado creado',
                                        showConfirmButton: false,
                                        timer: TOAST_SUCCESS_DURATION
                                    });
                                } catch(e) {}
                                try { $form.get(0).reset(); } catch(e) {}
                                try {
                                    DataTableModule.reloadOrBuild();
                                } catch(e) {}
                            } else {
                                ErrorHandler.handleServerError('CreateEmployee', resp);
                            }
                        } catch(e) {
                            ErrorHandler.log('CreateEmployee.ResponseParsing', e);
                        }
                    })
                    .catch(function(err) {
                        ErrorHandler.handleAjaxError('CreateEmployee', err);
                    })
                    .finally(function() {
                        setButtonLoading($btn, false);
                    });
            } catch(e) {
                ErrorHandler.log('CreateEmployeeForm.Submit', e);
            }
            return false;
        });
    }
    
    /**
     * Handle update employee form submission
     */
    function handleUpdateForm() {
        $(document).on('submit', '#formEditar', function(ev) {
            ev.preventDefault();
            try {
                var $form = $(this);
                var fd = new FormData($form.get(0));
                var $btn = $form.find('button[type=submit]');
                
                setButtonLoading($btn, true);
                
                fetch(DataTableModule.api('empleados/update'), { method: 'POST', body: fd })
                    .then(function(r) { return r.json().catch(function() { return null; }); })
                    .then(function(resp) {
                        try {
                            if (resp && resp.success) {
                                try {
                                    Swal.fire({
                                        toast: true,
                                        position: TOAST_POSITION,
                                        icon: 'success',
                                        title: resp.message || 'Empleado actualizado',
                                        showConfirmButton: false,
                                        timer: TOAST_SUCCESS_DURATION
                                    });
                                } catch(e) {}
                                try {
                                    var modalEl = document.getElementById('modalEditar');
                                    if (modalEl) {
                                        try {
                                            var m = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                                            m.hide();
                                        } catch(e) {}
                                    }
                                } catch(e) {}
                                try {
                                    DataTableModule.reloadOrBuild();
                                } catch(e) {}
                            } else {
                                ErrorHandler.handleServerError('UpdateEmployee', resp);
                            }
                        } catch(e) {
                            ErrorHandler.log('UpdateEmployee.ResponseParsing', e);
                        }
                    })
                    .catch(function(err) {
                        ErrorHandler.handleAjaxError('UpdateEmployee', err);
                    })
                    .finally(function() {
                        setButtonLoading($btn, false);
                    });
            } catch(e) {
                ErrorHandler.log('UpdateEmployeeForm.Submit', e);
            }
            return false;
        });
    }
    
    /**
     * Handle delete employee operation
     */
    function handleDelete() {
        $(document).on('click', '.eliminar', function() {
            try {
                var id = $(this).data('id');
                Swal.fire({
                    title: '¿Está seguro?',
                    text: 'Esta acción no se puede deshacer',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar',
                    showLoaderOnConfirm: true,
                    preConfirm: function() {
                        return fetch(DataTableModule.api('empleados/delete'), {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                            body: 'id=' + encodeURIComponent(id)
                        })
                        .then(function(r) {
                            return r.json().catch(function() { return null; });
                        })
                        .then(function(resp) {
                            if (resp && resp.success) {
                                return resp;
                            } else {
                                throw new Error((resp && resp.error) || 'Error al eliminar');
                            }
                        })
                        .catch(function(err) {
                            ErrorHandler.log('DeleteEmployee.Request', err, { id: id });
                            Swal.showValidationMessage('Error: ' + err.message);
                        });
                    },
                    allowOutsideClick: function() { return !Swal.isLoading(); }
                }).then(function(result) {
                    if (result.isConfirmed && result.value) {
                        Swal.fire({
                            toast: true,
                            position: TOAST_POSITION,
                            icon: 'success',
                            title: result.value.message || 'Empleado eliminado',
                            showConfirmButton: false,
                            timer: TOAST_SUCCESS_DURATION
                        });
                        DataTableModule.reloadOrBuild();
                    }
                });
            } catch(e) {
                ErrorHandler.log('DeleteEmployee.ClickHandler', e, { id: id });
            }
        });
    }
    
    /**
     * Handle view employee click
     */
    function handleView() {
        $(document).on('click', '.ver-ficha', function(e) {
            try {
                e.preventDefault();
                var id = $(this).data('id');
                // Open employee view page in new tab
                window.open(DataTableModule.api('empleados/view/' + encodeURIComponent(id)), '_blank');
            } catch(err) {
                ErrorHandler.log('ViewEmployee.ClickHandler', err, { id: id });
            }
        });
    }
    
    /**
     * Handle edit employee click
     */
    function handleEdit() {
        $(document).on('click', '.editar', function(e) {
            try {
                e.preventDefault();
                var id = $(this).data('id');
                // Open employee edit page in new tab
                window.open(DataTableModule.api('empleados/edit/' + encodeURIComponent(id)), '_blank');
            } catch(err) {
                ErrorHandler.log('EditEmployee.ClickHandler', err, { id: id });
            }
        });
    }
    
    /**
     * Initialize all form handlers
     */
    function init() {
        handleCreateForm();
        handleUpdateForm();
        handleDelete();
        handleView();
        handleEdit();
    }
    
    // Public API
    return {
        init: init
    };
})();
