$(document).ready(function () {
    // Inicializar DataTable con exportaciones
        const tabla = $('#tablaEmpleados').DataTable({
                ajax: {
                        url: 'empleados/ajax',
                        dataSrc: 'data'
                },
                columns: [
                        { data: 'id' },
                        { data: 'thumbnail', className: 'no-export', render: function (d) { return d ? `<img src="${d}" class="thumb-sm" alt="thumb">` : `<img src="uploads/placeholder.png" class="thumb-sm" alt="thumb">`; } },
                        { data: 'nombres' },
                        { data: 'apellidos' },
                        { data: 'edad' },
            {
                data: null,
                className: 'no-export',
                render: function (data, type, row) {
                                        return `
                        <button class="btn btn-sm btn-info ver-ficha" data-id="${row.id}">
                            <i class="bi bi-person-lines-fill"></i>
                        </button>
                        <button class="btn btn-sm btn-warning editar" data-id="${row.id}">
                            <i class="bi bi-pencil-square"></i>
                        </button>
                        <button class="btn btn-sm btn-danger eliminar" data-id="${row.id}">
                            <i class="bi bi-trash"></i>
                        </button>
                    `;
                                }
                        }
                ],
    dom: 'Bfrtip',
    colReorder: true,
    stateSave: true,
        buttons: [
            { extend: 'copy', text: 'Copiar', exportOptions: { columns: ':visible:not(.no-export)' } },
            { extend: 'colvis', text: 'Columnas' },
            // Excel: use excelHtml5 and set extension to xls for compatibility
            { extend: 'excelHtml5', text: 'XLS', filename: 'empleados', extension: '.xls', exportOptions: { columns: ':visible:not(.no-export)' } },
            // CSV (comma separated)
            { extend: 'csvHtml5', text: 'CSV', filename: 'empleados', extension: '.csv', fieldSeparator: ',', bom: true, exportOptions: { columns: ':visible:not(.no-export)' } },
            // TXT (tab separated values)
            { extend: 'csvHtml5', text: 'TXT', filename: 'empleados', extension: '.txt', fieldSeparator: '\t', bom: true, exportOptions: { columns: ':visible:not(.no-export)' } },
            { extend: 'print', text: 'Imprimir', exportOptions: { columns: ':visible:not(.no-export)' } },
            {
                text: 'Reset columns',
                action: function (e, dt, node, config) {
                    Swal.fire({
                        title: 'Restaurar columnas',
                        text: '¿Deseas restaurar el orden y visibilidad de columnas al estado por defecto?',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Sí, restaurar',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            try { dt.colReorder.reset(); } catch (err) { }
                            dt.columns().visible(true);
                            try { dt.state.clear(); } catch (err) { }
                            location.reload();
                        }
                    });
                }
            }
        ],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
        }
    });

    // Initialize Bootstrap tooltips for help icons
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.forEach(function (el) { new bootstrap.Tooltip(el); });

    // Calcular edad al seleccionar fecha
    $(document).on('change', '#fecha_nacimiento', function () {
        const fecha = new Date($(this).val());
        if (isNaN(fecha)) {
            $('#edad').val('');
            return;
        }

        const hoy = new Date();
        let edad = hoy.getFullYear() - fecha.getFullYear();
        const m = hoy.getMonth() - fecha.getMonth();
        if (m < 0 || (m === 0 && hoy.getDate() < fecha.getDate())) {
            edad--;
        }

        $('#edad').val(edad);
    });

    // Enviar formulario por AJAX
    $('#formEmpleado').on('submit', function (e) {
        e.preventDefault();
        const form = document.getElementById('formEmpleado');
        const formData = new FormData(form);

        fetch('empleados/create', {
            method: 'POST',
            body: formData
        }).then(function (resp) {
            return resp.json();
        }).then(function (respuesta) {
            if (respuesta.success) {
                showToast('Éxito', 'Empleado guardado correctamente', 'success');
                $('#formEmpleado')[0].reset();
                tabla.ajax.reload();
            } else if (respuesta.error) {
                showToast('Error', respuesta.error, 'danger');
            } else {
                showToast('Error', 'Respuesta inesperada del servidor', 'warning');
            }
        }).catch(function (err) {
            console.error(err);
            showToast('Error', 'Error al guardar el empleado', 'danger');
        });
    });

    // Acciones futuras
    $(document).on('click', '.ver-ficha', function () {
        const id = $(this).data('id');
        fetch('empleados/get?id=' + encodeURIComponent(id))
            .then(r => r.json())
            .then(json => {
                    if (json.data) {
                    const e = json.data;
                    $('#ficha_codigo').text(e.codigo || '');
                    $('#ficha_nombres').text(e.nombres || '');
                    $('#ficha_apellidos').text(e.apellidos || '');
                    $('#ficha_edad').text(e.edad || '');
                    $('#ficha_genero').text(e.genero || '');
                    $('#ficha_puesto').text(e.puesto_nombre || '');
                    $('#ficha_departamento').text(e.departamento_nombre || '');
                    $('#ficha_comentarios').text(e.comentarios || '');
                    if (e.thumbnail) {
                        $('#ficha_foto').attr('src', e.thumbnail).show();
                    } else if (e.foto) {
                        $('#ficha_foto').attr('src', 'uploads/' + e.foto).show();
                    } else {
                        $('#ficha_foto').attr('src', 'uploads/placeholder.png').show();
                    }
                    var modal = new bootstrap.Modal(document.getElementById('modalFicha'));
                    modal.show();
                } else if (json.error) {
                    showToast('Error', json.error, 'danger');
                }
            }).catch(err => {
                console.error(err);
                showToast('Error', 'Error al obtener la ficha', 'danger');
            });
    });

    // Abrir modal de editar cuando se hace click en editar
    $(document).on('click', '.editar', function () {
        const id = $(this).data('id');
        fetch('empleados/get?id=' + encodeURIComponent(id))
            .then(r => r.json())
            .then(json => {
                if (json.data) {
                    const e = json.data;
                    $('#edit_id').val(e.id);
                    $('#edit_nombres').val(e.nombres || '');
                    $('#edit_apellidos').val(e.apellidos || '');
                    $('#edit_fecha_nacimiento').val(e.fecha_nacimiento || '');
                    // Poblar selects de puesto y departamento si están vacíos
                    if ($('#edit_puesto_id option').length <= 1) {
                        // copiar opciones del formulario principal
                        $('#puesto_id option').clone().appendTo('#edit_puesto_id');
                    }
                    if ($('#edit_departamento_id option').length <= 1) {
                        $('#departamento_id option').clone().appendTo('#edit_departamento_id');
                    }
                    $('#edit_puesto_id').val(e.puesto_id || '');
                    $('#edit_departamento_id').val(e.departamento_id || '');
                    // Set genero if provided
                    $('#edit_genero').val(e.genero || '');
                    $('#edit_foto_actual').val(e.foto || '');
                    // Set comentarios in edit modal
                    $('#edit_comentarios').val(e.comentarios || '');
                    // set photo preview
                    if (e.thumbnail) {
                        $('#edit_foto_preview').attr('src', e.thumbnail);
                    } else if (e.foto) {
                        $('#edit_foto_preview').attr('src', 'uploads/' + e.foto);
                    } else {
                        $('#edit_foto_preview').attr('src', 'uploads/placeholder.png');
                    }

                    var modal = new bootstrap.Modal(document.getElementById('modalEditar'));
                    modal.show();
                } else if (json.error) {
                    showToast('Error', json.error, 'danger');
                }
            }).catch(err => {
                console.error(err);
                showToast('Error', 'Error al obtener la ficha', 'danger');
            });
    });

    // Enviar formulario de edición
    $('#formEditar').on('submit', function (e) {
        e.preventDefault();
        const form = document.getElementById('formEditar');
        const formData = new FormData(form);

        fetch('empleados/update', {
            method: 'POST',
            body: formData
        }).then(r => r.json()).then(respuesta => {
            if (respuesta.success) {
                showToast('Éxito', 'Empleado actualizado correctamente', 'success');
                tabla.ajax.reload();
                var modalEl = document.getElementById('modalEditar');
                var modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) modal.hide();
            } else if (respuesta.error) {
                showToast('Error', respuesta.error, 'danger');
            }
        }).catch(err => {
            console.error(err);
            showToast('Error', 'Error al actualizar empleado', 'danger');
        });
    });

    // Client-side image validation helper
    function validateFileInput(file) {
        if (!file) return { ok: true };
        const allowed = ['image/jpeg', 'image/png', 'image/gif'];
        const maxBytes = 2 * 1024 * 1024; // 2MB
        if (!allowed.includes(file.type)) return { ok: false, msg: 'Tipo de archivo no permitido (solo jpg, png, gif)' };
        if (file.size > maxBytes) return { ok: false, msg: 'El archivo supera el tamaño máximo (2MB)' };
        return { ok: true };
    }

    // Preview de imagen cuando se selecciona un archivo en el modal de edición (con validación)
    $(document).on('change', '#edit_foto', function () {
        const input = this;
        if (input.files && input.files[0]) {
            const validation = validateFileInput(input.files[0]);
            if (!validation.ok) {
                showToast('Error', validation.msg, 'danger');
                $(this).val('');
                return;
            }
            const reader = new FileReader();
            reader.onload = function (e) {
                $('#edit_foto_preview').attr('src', e.target.result);
            };
            reader.readAsDataURL(input.files[0]);
        }
    });

    // Validate and preview for new employee form
    $(document).on('change', '#foto', function () {
        const input = this;
        if (input.files && input.files[0]) {
            const validation = validateFileInput(input.files[0]);
            if (!validation.ok) {
                showToast('Error', validation.msg, 'danger');
                $(this).val('');
                return;
            }
        }
    });

    // Delete flow using confirmation modal
    let deleteIdToConfirm = null;
    $(document).on('click', '.eliminar', function () {
        const id = $(this).data('id');
        Swal.fire({
            title: '¿Eliminar empleado?',
            text: 'Esta acción no se puede deshacer.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch('empleados/delete', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'id=' + encodeURIComponent(id)
                }).then(r => r.json()).then(res => {
                    if (res.success) {
                        Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Empleado eliminado', showConfirmButton: false, timer: 2000 });
                        tabla.ajax.reload();
                    } else {
                        Swal.fire({ toast: true, position: 'top-end', icon: 'error', title: res.error || 'Error al eliminar', showConfirmButton: false, timer: 3500 });
                    }
                }).catch(err => {
                    console.error(err);
                    Swal.fire({ toast: true, position: 'top-end', icon: 'error', title: 'Error al eliminar', showConfirmButton: false, timer: 3500 });
                });
            }
        });
    });

        // showToast using SweetAlert2 toast
        function showToast(title, message, type) {
                const icon = type === 'success' ? 'success' : (type === 'danger' ? 'error' : 'warning');
                Swal.fire({ toast: true, position: 'top-end', icon: icon, title: message, showConfirmButton: false, timer: 3500, timerProgressBar: true });
        }
});