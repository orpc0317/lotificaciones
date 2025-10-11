$(document).ready(function () {
    $('#toggleDarkMode').click(function () {
        $('body').toggleClass('bg-light bg-dark text-dark text-white');
    });

    const departamentos = [
        { id: 1, nombre: 'Ventas' },
        { id: 2, nombre: 'Finanzas' },
        { id: 3, nombre: 'Recursos Humanos' },
        { id: 4, nombre: 'TI' }
    ];
    departamentos.forEach(dep => {
        $('#departamento_id').append(`<option value="${dep.id}">${dep.nombre}</option>`);
    });

    $('#fecha_nacimiento').on('change', function () {
        const fecha = new Date($(this).val());
        const hoy = new Date();
        let edad = hoy.getFullYear() - fecha.getFullYear();
        const m = hoy.getMonth() - fecha.getMonth();
        if (m < 0 || (m === 0 && hoy.getDate() < fecha.getDate())) {
            edad--;
        }
        $('#edad').val(edad);
    });

    const tabla = $('#tablaEmpleados').DataTable({
        ajax: {
            url: '/empleados/ajax',
            dataSrc: 'data'
        },
        columns: [
            {
                data: 'foto',
                render: function (data) {
                    return `<img src="/uploads/${data}" class="rounded-circle" width="40" height="40">`;
                }
            },
            { data: 'codigo' },
            {
                data: null,
                render: function (data) {
                    return `${data.nombres} ${data.apellidos}`;
                }
            },
            {
                data: null,
                render: function (data) {
                    return `
                        <button class="btn btn-sm btn-info me-1 ver" data-id="${data.id}"><i class="bi bi-eye-fill"></i></button>
                        <button class="btn btn-sm btn-warning me-1 editar" data-id="${data.id}"><i class="bi bi-pencil-fill"></i></button>
                        <button class="btn btn-sm btn-danger eliminar" data-id="${data.id}"><i class="bi bi-trash-fill"></i></button>
                    `;
                }
            }
        ],
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'csvHtml5',
                text: '<i class="bi bi-filetype-csv me-1"></i>CSV',
                className: 'btn btn-outline-secondary btn-sm'
            },
            {
                extend: 'excelHtml5',
                text: '<i class="bi bi-file-earmark-excel me-1"></i>XLS',
                className: 'btn btn-outline-success btn-sm'
            },
            {
                extend: 'copyHtml5',
                text: '<i class="bi bi-clipboard me-1"></i>Copiar',
                className: 'btn btn-outline-primary btn-sm'
            },
            {
                extend: 'print',
                text: '<i class="bi bi-printer me-1"></i>Imprimir',
                className: 'btn btn-outline-dark btn-sm'
            }
        ]
    });

    $('#formEmpleado').submit(function (e) {
        e.preventDefault();
        const formData = new FormData(this);
        const url = $('#id').val() ? '/empleados/update' : '/empleados/create';

        $.ajax({
            url: url,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function () {
                tabla.ajax.reload();
                $('#formEmpleado')[0].reset();
                $('#id').val('');
                $('#foto_actual').val('');
            }
        });
    });

    $('#tablaEmpleados').on('click', '.eliminar', function () {
        const id = $(this).data('id');
        if (confirm('¿Eliminar este empleado?')) {
            $.post('/empleados/delete', { id }, function () {
                tabla.ajax.reload();
            });
        }
    });

    $('#tablaEmpleados').on('click', '.ver', function () {
        const id = $(this).data('id');

        $.getJSON('/empleados/ajax', function (res) {
            const empleado = res.data.find(e => e.id == id);
            if (!empleado) return;

            $('#fichaFoto').attr('src', '/uploads/' + empleado.foto);
            $('#fichaNombre').text(`${empleado.nombres} ${empleado.apellidos}`);
            $('#fichaCodigo').text(empleado.codigo);
            $('#fichaEdad').text(empleado.edad);
            $('#fichaNacimiento').text(empleado.fecha_nacimiento);
            $('#fichaPuesto').text(empleado.puesto_id);
            $('#fichaDepartamento').text(empleado.departamento_id);
            $('#fichaGenero').text(empleado.genero);
            $('#fichaComentarios').text(empleado.comentarios);

            const modal = new bootstrap.Modal(document.getElementById('modalFicha'));
            modal.show();
        });
    });

    $('#tablaEmpleados').on('click', '.editar', function () {
        const id = $(this).data('id');
        alert('Función editar aún no implementada. ID: ' + id);
    });
});