$(document).ready(function () {
    // Inicializar DataTable con exportaciones
        // Map column header or data key to friendly export header
        function exportHeader(data, columnIdx) {
            var key = (data || '').toString().trim().toLowerCase().replace(/\s+/g, '_');
            var map = {
                id: 'ID',
                codigo: 'Código',
                nombres: 'Nombres',
                apellidos: 'Apellidos',
                fecha_de_nacimiento: 'Fecha de Nacimiento',
                edad: 'Edad',
                foto: 'Foto',
                puesto_id: 'Puesto ID',
                puesto_nombre: 'Puesto',
                departamento_id: 'Departamento ID',
                departamento_nombre: 'Departamento',
                genero: 'Género',
                comentarios: 'Comentarios'
            };
            return map[key] || data;
        }

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
            { extend: 'copy', text: 'Copiar', exportOptions: { columns: ':visible:not(.no-export)', format: { header: exportHeader } } },
            { extend: 'colvis', text: 'Columnas' },
            // Excel: use excelHtml5 and set extension to xls for compatibility
            { extend: 'excelHtml5', text: 'XLS', filename: 'empleados', extension: '.xls', exportOptions: { columns: ':visible:not(.no-export)', format: { header: exportHeader } } },
            // CSV (comma separated)
            { extend: 'csvHtml5', text: 'CSV', filename: 'empleados', extension: '.csv', fieldSeparator: ',', bom: true, exportOptions: { columns: ':visible:not(.no-export)', format: { header: exportHeader } } },
            // TXT (tab separated values)
            { extend: 'csvHtml5', text: 'TXT', filename: 'empleados', extension: '.txt', fieldSeparator: '\t', bom: true, exportOptions: { columns: ':visible:not(.no-export)', format: { header: exportHeader } } },
            { extend: 'print', text: 'Imprimir', exportOptions: { columns: ':visible:not(.no-export)', format: { header: exportHeader } } },
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

    // Dark mode toggle: persist in localStorage and apply data-theme on <html>
    function applyTheme(theme) {
        if (theme === 'dark') document.documentElement.setAttribute('data-theme', 'dark');
        else document.documentElement.removeAttribute('data-theme');
        try { localStorage.setItem('lotificaciones_theme', theme); } catch (e) { }
        // update toggle button icon
        const btn = document.getElementById('darkModeToggle');
        if (btn) btn.textContent = theme === 'dark' ? '☀️' : '🌙';
    }

    // Initialize from storage
    (function(){
        let t = 'light';
        try { t = localStorage.getItem('lotificaciones_theme') || 'light'; } catch (e) { }
        applyTheme(t);
        const btn = document.getElementById('darkModeToggle');
        if (btn) btn.addEventListener('click', function(){
            const current = document.documentElement.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
            const next = current === 'dark' ? 'light' : 'dark';
            applyTheme(next);
        });
        // Palette picker
        const paletteSwatches = document.querySelectorAll('.palette-swatch');
        function applyPalette(name){
            let p600 = '--primary-600';
            let p400 = '--primary-400';
            if (name === 'teal') { document.documentElement.style.setProperty('--primary-600','#0d9488'); document.documentElement.style.setProperty('--primary-400','#34d399'); }
            else if (name === 'indigo') { document.documentElement.style.setProperty('--primary-600','#6366f1'); document.documentElement.style.setProperty('--primary-400','#8b5cf6'); }
            else { document.documentElement.style.setProperty('--primary-600','#0b63d3'); document.documentElement.style.setProperty('--primary-400','#3b82f6'); }
            try { localStorage.setItem('lotificaciones_palette', name); } catch (e) {}
            paletteSwatches.forEach(s => s.classList.toggle('active', s.getAttribute('data-palette') === name));
        }
        if (paletteSwatches.length) {
            const saved = (function(){ try { return localStorage.getItem('lotificaciones_palette') || 'blue' } catch(e){ return 'blue' } })();
            applyPalette(saved);
            paletteSwatches.forEach(function(s){ s.addEventListener('click', function(){ applyPalette(s.getAttribute('data-palette')); }); });
        }
    })();

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
                    $('#ficha_fecha_nacimiento').text(e.fecha_nacimiento || '');
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
                    // Ensure the Generals tab is active when opening the ficha
                    try { var fichaTabEl = document.querySelector('#ficha-generals-tab'); if (fichaTabEl) new bootstrap.Tab(fichaTabEl).show(); } catch (e) { }
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
                    // Ensure the Generals tab is active when opening the edit modal
                    try { var editTabEl = document.querySelector('#edit-generals-tab'); if (editTabEl) new bootstrap.Tab(editTabEl).show(); } catch (e) { }
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
        // Export ficha modal to PDF (uses html2canvas + jsPDF)
        $(document).on('click', '#exportPdfBtn', function () {
            const codigo = $('#ficha_codigo').text().trim() || 'empleado';
            const imgSrc = $('#ficha_foto').attr('src') || '';
            const hasImage = imgSrc && !imgSrc.includes('placeholder.png');

            // Build a clean container for PDF rendering
            const $container = $('<div id="pdfContainer">').css({
                position: 'absolute',
                left: '-9999px',
                top: '0',
                width: '800px',
                background: '#ffffff',
                color: '#000',
                padding: '20px',
                'font-family': 'Arial, Helvetica, sans-serif',
                'font-size': '12px',
                'line-height': '1.4'
            });

            // Header
            $container.append($('<h2>').text('Ficha de Empleado').css({ 'margin-top': 0 }));

            // Row: image left, fields right
            const $row = $('<div>').css({ display: 'flex', 'align-items': 'flex-start' });

            if (hasImage) {
                const $imgWrap = $('<div>').css({ 'flex': '0 0 160px', 'margin-right': '20px' });
                const $img = $('<img>').attr('src', imgSrc).css({ width: '150px', height: 'auto', display: 'block' });
                $imgWrap.append($img);
                $row.append($imgWrap);
            }

            const $fields = $('<div>').css({ 'flex': '1 1 auto' });
            const addField = (label, value) => {
                const $p = $('<p>').css({ margin: '4px 0' });
                $p.append($('<strong>').text(label + ': ')).append(document.createTextNode(value || ''));
                $fields.append($p);
            };

            addField('Código', $('#ficha_codigo').text().trim());
            addField('Nombres', $('#ficha_nombres').text().trim());
            addField('Apellidos', $('#ficha_apellidos').text().trim());
            addField('Edad', $('#ficha_edad').text().trim());
            addField('Género', $('#ficha_genero').text().trim());
            addField('Puesto', $('#ficha_puesto').text().trim());
            addField('Departamento', $('#ficha_departamento').text().trim());

            $row.append($fields);
            $container.append($row);

            // Comentarios section below
            const comentarios = $('#ficha_comentarios').text().trim();
            if (comentarios) {
                $container.append($('<h3>').text('Comentarios').css({ 'margin-top': '16px' }));
                $container.append($('<p>').text(comentarios).css({ 'white-space': 'pre-wrap' }));
            }

            $('body').append($container);

            // Open a new tab immediately so the popup is allowed by the browser
            const newWin = window.open('', '_blank');
            if (!newWin) {
                showToast('Error', 'El navegador bloqueó la apertura de la nueva pestaña. Permite popups para continuar.', 'danger');
                $container.remove();
                return;
            }
            // temporary content while we render
            try {
                newWin.document.write('<!doctype html><html><head><meta charset="utf-8"><title>Generando PDF...</title></head><body><p style="font-family:Arial,Helvetica,sans-serif;padding:20px;">Generando PDF, por favor espere...</p></body></html>');
                newWin.document.close();
            } catch (e) {
                // some browsers may throw if cross-origin; ignore and proceed
            }

            Swal.fire({ title: 'Generando PDF', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });

            const renderAndSave = () => {
                html2canvas($container[0], { scale: 2 }).then(canvas => {
                    try {
                        const imgData = canvas.toDataURL('image/jpeg', 0.95);
                        const pdfConstructor = (window.jspdf && window.jspdf.jsPDF) ? window.jspdf.jsPDF : (typeof jsPDF !== 'undefined' ? jsPDF : null);
                        if (!pdfConstructor) throw new Error('jsPDF not available');
                        const pdf = new pdfConstructor('p', 'mm', 'a4');
                        const pageWidth = pdf.internal.pageSize.getWidth();
                        const pageHeight = pdf.internal.pageSize.getHeight();
                        const margin = 10; // mm
                        const pdfInnerWidth = pageWidth - margin * 2;
                        const pdfInnerHeight = pageHeight - margin * 2;

                        // Full image size in mm when scaled to pdfInnerWidth
                        const fullImgHeightMm = (canvas.height * pdfInnerWidth) / canvas.width;

                        if (fullImgHeightMm <= pdfInnerHeight) {
                            // Single page
                            pdf.addImage(imgData, 'JPEG', margin, margin, pdfInnerWidth, fullImgHeightMm);
                        } else {
                            // Multi-page: slice canvas vertically into page-sized chunks
                            // Calculate how many px correspond to one PDF inner page height
                            const pxPerPage = Math.floor((canvas.width * pdfInnerHeight) / pdfInnerWidth);
                            let y = 0;
                            while (y < canvas.height) {
                                const sliceHeight = Math.min(pxPerPage, canvas.height - y);
                                // Create a temporary canvas to hold the slice
                                const sliceCanvas = document.createElement('canvas');
                                sliceCanvas.width = canvas.width;
                                sliceCanvas.height = sliceHeight;
                                const ctx = sliceCanvas.getContext('2d');
                                // draw the slice from the original canvas
                                ctx.drawImage(canvas, 0, y, canvas.width, sliceHeight, 0, 0, canvas.width, sliceHeight);
                                const sliceData = sliceCanvas.toDataURL('image/jpeg', 0.95);
                                const sliceHeightMm = (sliceHeight * pdfInnerWidth) / canvas.width;
                                if (y > 0) pdf.addPage();
                                pdf.addImage(sliceData, 'JPEG', margin, margin, pdfInnerWidth, sliceHeightMm);
                                y += sliceHeight;
                            }
                        }

                        // Prepare blob URL for the PDF
                        const blobUrl = pdf.output('bloburl');
                        const filename = 'ficha_' + (codigo || 'empleado').replace(/[^a-z0-9_-]/gi, '_') + '.pdf';

                        // Build a simple viewer page with Download / Print buttons and embed the PDF in an iframe
                        try {
                            const html = `<!doctype html><html><head><meta charset="utf-8"><title>${filename}</title></head><body style="margin:0;font-family:Arial,Helvetica,sans-serif;">
    <div style="padding:8px;background:#f2f2f2;display:flex;gap:8px;align-items:center;">
      <button id="downloadBtn" style="padding:6px 10px">Descargar</button>
      <button id="printBtn" style="padding:6px 10px">Imprimir</button>
      <button id="closeBtn" style="padding:6px 10px;margin-left:auto">Cerrar</button>
    </div>
    <iframe id="pdfFrame" src="${blobUrl}" style="width:100%;height:calc(100vh - 48px);border:0"></iframe>
    <script>
      (function(){
        const blob = '${blobUrl}';
        document.getElementById('downloadBtn').addEventListener('click', function(){
          const a = document.createElement('a'); a.href = blob; a.download = '${filename}'; document.body.appendChild(a); a.click(); a.remove();
        });
        document.getElementById('printBtn').addEventListener('click', function(){
          const f = document.getElementById('pdfFrame'); try { f.contentWindow.focus(); f.contentWindow.print(); } catch(e) { window.open(blob); }
        });
        document.getElementById('closeBtn').addEventListener('click', function(){ window.close(); });
      })();
    </script>
    </body></html>`;
                            newWin.document.open();
                            newWin.document.write(html);
                            newWin.document.close();
                        } catch (e) {
                            // Fallback: navigate directly to blob URL
                            try { newWin.location.href = blobUrl; } catch (e2) { window.location.href = blobUrl; }
                        }

                        Swal.close();
                        $container.remove();
                    } catch (err) {
                        console.error('Error generating PDF', err);
                        Swal.close();
                        $container.remove();
                        showToast('Error', 'Error al generar el PDF', 'danger');
                    }
                }).catch(err => {
                    console.error('html2canvas error', err);
                    Swal.close();
                    $container.remove();
                    showToast('Error', 'Error al renderizar PDF', 'danger');
                });
            };

            // If there's an image, wait for it to load before rendering
            if (hasImage) {
                const $img = $container.find('img');
                if ($img.length) {
                    if ($img[0].complete) {
                        renderAndSave();
                    } else {
                        $img.on('load error', function () { renderAndSave(); });
                    }
                } else {
                    renderAndSave();
                }
            } else {
                renderAndSave();
            }
        });

        // showToast using SweetAlert2 toast
        function showToast(title, message, type) {
            const icon = type === 'success' ? 'success' : (type === 'danger' ? 'error' : 'warning');
            Swal.fire({ toast: true, position: 'top-end', icon: icon, title: message, showConfirmButton: false, timer: 3500, timerProgressBar: true });
        }
});