$(document).ready(function () {
    // Inicializar Select2
    $('.select2').select2({
        placeholder: 'Buscar puesto...',
        allowClear: true,
        width: '100%'
    });

    // Inicializar Flatpickr
    flatpickr("#fechaNacimiento", {
        dateFormat: "Y-m-d",
        onChange: function (selectedDates) {
            const birthDate = selectedDates[0];
            const age = new Date().getFullYear() - birthDate.getFullYear();
            $('#edadCalculada').text(`Edad: ${age} años`);
        }
    });

    // Inicializar DataTable
    const tabla = $('#tablaEmpleados').DataTable({
        ajax: {
            url: `${_API}/empleados`,
            dataSrc: ''
        },
        columns: [
            {
                data: 'foto',
                render: function (data) {
                    return `<img src="${data}" class="rounded-circle" width="40">`;
                }
            },
            { data: 'codigo' },
            { data: 'nombreCompleto' },
            {
                data: null,
                render: function (data) {
                    return `
            <button class="btn btn-sm btn-info verEmpleado" data-id="${data.id}">
              <i class="fas fa-eye"></i>
            </button>
          `;
                }
            }
        ],
        dom: 'Bfrtip',
        buttons: ['csv', 'excel', 'pdf', 'colvis'],
        responsive: true,
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
        }
    });

    // Cargar departamentos
    $.get(`${_API}/departamentos`, function (data) {
        data.forEach(dep => {
            $('#departamento').append(`<option value="${dep.id}">${dep.nombre}</option>`);
        });
    });

    // Cargar puestos
    $.get(`${_API}/puestos`, function (data) {
        data.forEach(p => {
            $('#puesto').append(`<option value="${p.id}">${p.nombre}</option>`);
        });
    });

    // Enviar formulario
    $('#formEmpleado').submit(function (e) {
        e.preventDefault();
        const formData = new FormData(this);

        $.ajax({
            url: `${_API}/empleados`,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function () {
                showToast('Empleado creado exitosamente', 'success');
                tabla.ajax.reload();
                $('#formEmpleado')[0].reset();
                $('.select2').val(null).trigger('change');
                $('#edadCalculada').text('');
            },
            error: function () {
                showToast('Error al crear empleado', 'error');
            }
        });
    });

    // Ver ficha del empleado
    $('#tablaEmpleados').on('click', '.verEmpleado', function () {
        const id = $(this).data('id');
        $.get(`${_API}/empleados/${id}`, function (data) {
            $('#contenidoEmpleado').html(`
        <div class="row">
          <div class="col-md-4 text-center">
            <img src="${data.foto}" class="img-fluid rounded mb-3">
          </div>
          <div class="col-md-8">
            <p><strong>Código:</strong> ${data.codigo}</p>
            <p><strong>Nombre:</strong> ${data.nombres} ${data.apellidos}</p>
            <p><strong>Edad:</strong> ${data.edad}</p>
            <p><strong>Puesto:</strong> ${data.puesto}</p>
            <p><strong>Departamento:</strong> ${data.departamento}</p>
            <p><strong>Género:</strong> ${data.genero}</p>
            <p><strong>Comentarios:</strong> ${data.comentarios}</p>
          </div>
        </div>
      `);
            $('#modalEmpleado').modal('show');
        });
    });

    // Eliminar empleado
    $('#btnEliminar').click(function () {
        const id = $('.verEmpleado').data('id');
        Swal.fire({
            title: '¿Eliminar empleado?',
            text: 'Esta acción no se puede deshacer.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then(result => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `${_API}/empleados/${id}`,
                    method: 'DELETE',
                    success: function () {
                        showToast('Empleado eliminado', 'success');
                        $('#modalEmpleado').modal('hide');
                        tabla.ajax.reload();
                    },
                    error: function () {
                        showToast('Error al eliminar', 'error');
                    }
                });
            }
        });
    });

    // Generar PDF
    $('#btnPDF').click(function () {
        const id = $('.verEmpleado').data('id');
        window.open(`${_API}/empleados/${id}/pdf`, '_blank');
    });

    // Modo oscuro
    $('#toggleDarkMode').click(function () {
        $('body').toggleClass('bg-dark text-white');
        $(this).toggleClass('btn-outline-dark btn-light');
    });

    // Toast flotante
    function showToast(message, type) {
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });
        Toast.fire({
            icon: type,
            title: message
        });
    }

    // Variable global para API
    const _API = _ENV.API_BASE_URL;
});