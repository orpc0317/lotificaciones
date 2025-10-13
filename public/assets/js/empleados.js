$(document).ready(function () {
    // Make touch event listeners passive when the browser supports it to avoid
    // "Added non-passive event listener" console violations from libraries
    // that attach touchstart/touchmove handlers (DataTables ColReorder, colResizable, etc).
    // This is a defensive, non-invasive patch: it changes how jQuery binds
    // touch events by setting the `passive` option if supported.
    (function enablePassiveTouchForjQuery(){
        try {
            var supportsPassive = false;
            try {
                var opts = Object.defineProperty({}, 'passive', {
                    get: function() { supportsPassive = true; }
                });
                window.addEventListener('testPassive', null, opts);
                window.removeEventListener('testPassive', null, opts);
            } catch (e) { supportsPassive = false; }

            if (!supportsPassive || !window.jQuery || !jQuery.event || !jQuery.event.special) return;

            ['touchstart','touchmove'].forEach(function(evtName){
                if (!jQuery.event.special[evtName]) jQuery.event.special[evtName] = {};
                // preserve existing setup if present
                var origSetup = jQuery.event.special[evtName].setup;
                jQuery.event.special[evtName].setup = function(data, namespaces, handler) {
                    // when jQuery calls this, `this` is the element
                    try {
                        this.addEventListener(evtName, handler, { passive: true });
                        return true;
                    } catch (e) {
                        // fallback to original setup or to jQuery's internal handler
                        if (typeof origSetup === 'function') return origSetup.call(this, data, namespaces, handler);
                        try { this.addEventListener(evtName, handler, false); return true; } catch (err) { return false; }
                    }
                };
            });
        } catch (e) { /* silently ignore - not critical */ }
    })();
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
        ajax: { url: 'empleados/ajax', dataSrc: 'data' },
        columns: [
                        { data: 'id' },
                        { data: 'thumbnail', className: 'no-export', render: function (d) { return d ? `<img src="${d}" class="thumb-sm" alt="thumb">` : `<img src="uploads/placeholder.png" class="thumb-sm" alt="thumb">`; } },
                        { data: 'codigo', visible: false },
                        { data: 'nombres' },
                        { data: 'apellidos' },
                        { data: 'edad' },
                        { data: 'fecha_nacimiento', visible: false },
                        { data: 'genero', visible: false },
                        { data: 'puesto_id', visible: false, render: function(data, type, row) {
                                // Prefer server-provided friendly name
                                if (row && row.puesto_nombre) return row.puesto_nombre;
                                // Try to find option text from the main form selects
                                try {
                                    if (!data) return '';
                                    var opt = document.querySelector('#puesto_id option[value="' + data + '"]');
                                    if (opt && opt.textContent && opt.textContent.trim()) return opt.textContent.trim();
                                } catch (e) {}
                                // final fallback: show the ID
                                return data;
                            }
                        },
                        { data: 'departamento_id', visible: false, render: function(data, type, row) {
                                if (row && row.departamento_nombre) return row.departamento_nombre;
                                try {
                                    if (!data) return '';
                                    var opt = document.querySelector('#departamento_id option[value="' + data + '"]');
                                    if (opt && opt.textContent && opt.textContent.trim()) return opt.textContent.trim();
                                } catch (e) {}
                                return data;
                            }
                        },
                        { data: 'comentarios', visible: false },
                        {
                            data: null,
                            className: 'no-export',
                            render: function (data, type, row) {
                                return `
                                    <button class="btn btn-sm btn-info ver-ficha" data-id="${row.id}" title="Ver detalles">
                                        <i class="bi bi-eye"></i>
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
    // When loading a saved state, remap old column data keys to current ones
    stateLoadParams: function (settings, data) {
        try {
            if (data && data.columns && Array.isArray(data.columns)) {
                data.columns.forEach(function (col) {
                    if (col && col.data === 'puesto_nombre') col.data = 'puesto_id';
                    if (col && col.data === 'departamento_nombre') col.data = 'departamento_id';
                });
            }
        } catch (e) { /* ignore */ }
    },
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
            // Use explicit HTTPS to avoid redirects that can trigger CORS failures when running a local server
            url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
        }
    });

    // Handle DataTables 'unknown parameter' errors (tn/4) caused by stale saved state
    tabla.on('error.dt', function (e, settings, techNote, message) {
        try {
            if (message && message.indexOf && message.indexOf('Requested unknown parameter') !== -1) {
                console.warn('DataTables unknown-parameter detected; clearing saved state.');
                try { tabla.state.clear(); } catch (err) { }
                try { localStorage.removeItem('DataTables_' + settings.sInstance + '_' + location.pathname); } catch (err) { }
                // reload page to reset UI
                location.reload();
            }
        } catch (e) { /* ignore */ }
    });

    /* ========= Resizing ========= */
    // Inject minimal CSS for resizer handles
    (function(){
        var css = '\n#tablaEmpleados th { position:relative; }\n';
        var s = document.createElement('style'); s.type='text/css'; s.appendChild(document.createTextNode(css)); document.head.appendChild(s);
    })();

    // When columns are toggled visible/hidden, remove any inline width styles that
    // could force a column to become very narrow and then adjust columns.
    tabla.on('column-visibility.dt', function(e, settings, column, state){
        try {
            // Clear inline width styles on all header and body cells so the browser
            // can recompute reasonable widths instead of keeping previous tiny values.
            $('#tablaEmpleados thead th').each(function(i, th){ if (th && th.style) th.style.width = ''; });
            $('#tablaEmpleados tbody tr').each(function(){
                Array.from(this.children).forEach(function(td){ if (td && td.style) td.style.width = ''; });
            });

            // If colResizable is present it may have injected inline widths/handles.
            // Try to destroy/remove it then reinitialize to ensure it doesn't hold stale widths.
            try {
                if ($.fn.colResizable) {
                    // attempt common destroy/remove APIs defensively
                    try { $('#tablaEmpleados').colResizable('destroy'); } catch (e) {}
                    try { $('#tablaEmpleados').colResizable('remove'); } catch (e) {}
                    try { $('#tablaEmpleados').colResizable({ liveDrag: true, minWidth: 30, resizeMode: 'fit' }); } catch (e) { /* ignore colResizable reinit errors */ }
                }
            } catch (e) { /* ignore colResizable cleanup errors */ }

            // small delay then adjust and redraw to recalc layout and remove distortions
            setTimeout(function(){ try { tabla.columns.adjust(); tabla.draw(false); } catch(e){} }, 80);
    } catch (err) { /* ignore column-visibility handler errors */ }
    });

    // Replace custom resizer with a small, well-tested library (colResizable)
    function loadScript(url, cb) {
        var s = document.createElement('script'); s.type = 'text/javascript'; s.src = url;
        s.onload = function(){ cb(null); };
        s.onerror = function(){ cb(new Error('Failed to load ' + url)); };
        document.head.appendChild(s);
    }

    tabla.on('init.dt', function(){
        try {
            // load colResizable from CDN if not present
            if (!$.fn.colResizable) {
                loadScript('https://cdn.jsdelivr.net/npm/colresizable@1.6.0/colResizable-1.6.min.js', function(err){
                    if (err) { /* Could not load colResizable - continue without it */ return; }
                    try { $('#tablaEmpleados').colResizable({ liveDrag: true, minWidth: 30, resizeMode: 'fit' }); } catch(e) { /* ignore */ }
                });
            } else {
                try { $('#tablaEmpleados').colResizable({ liveDrag: true, minWidth: 30, resizeMode: 'fit' }); } catch(e) { /* ignore */ }
            }
        } catch(e){ /* ignore */ }
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
            // Default blue
            var prim600 = '#0b63d3';
            var prim400 = '#3b82f6';
            if (name === 'teal') {
                prim600 = '#0d9488'; prim400 = '#34d399';
            } else if (name === 'violet') {
                prim600 = '#7c3aed'; prim400 = '#a78bfa';
            }
            // Apply custom vars used by our CSS
            document.documentElement.style.setProperty('--primary-600', prim600);
            document.documentElement.style.setProperty('--primary-400', prim400);
            // Also update Bootstrap root variable for broader effect
            try {
                document.documentElement.style.setProperty('--bs-primary', prim600);
            } catch (err) { /* ignore */ }
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
                    var modalElFicha = document.getElementById('modalFicha');
                    var modal = new bootstrap.Modal(modalElFicha);
                    modal.show();
                    // Ensure the Generals tab is active when opening the ficha
                    try { var fichaTabEl = modalElFicha.querySelector('#ficha-generals-tab'); if (fichaTabEl) new bootstrap.Tab(fichaTabEl).show(); } catch (e) { }
                    // refresh badges when the modal is shown; blur focused descendant on hide to avoid aria-hidden focus errors
                    try {
                        if (typeof updateTabBadges === 'function') {
                            modalElFicha.addEventListener('shown.bs.modal', function () { scheduleBadgeRefresh(modalElFicha); }, { once: true });
                            modalElFicha.addEventListener('hide.bs.modal', function () { try { var a = document.activeElement; if (a && modalElFicha.contains(a)) a.blur(); } catch (e) { } }, { once: true });
                        }
                    } catch (e) { }
                } else if (json.error) {
                    showToast('Error', json.error, 'danger');
                }
            }).catch(err => {
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
                    // Assign visible input values immediately so the modal shows the
                    // stored data without delay. We still keep the shown.bs.modal
                    // handler below to reapply values after Bootstrap finishes
                    // rendering (this combination is robust against browser autofill
                    // and timing races).
                    try {
                        $('#edit_nombres').val(e.nombres || '');
                        $('#edit_apellidos').val(e.apellidos || '');
                        $('#edit_fecha_nacimiento').val(e.fecha_nacimiento || '');
                        $('#edit_genero').val(e.genero || '');
                        $('#edit_comentarios').val(e.comentarios || '');
                    } catch (err) { /* ignore assignment errors */ }
                    // Ensure edit selects have option lists. If empty, clone from main form selects.
                    try {
                        if ($('#edit_puesto_id').length && $('#edit_puesto_id option').length <= 1 && $('#puesto_id').length) {
                            $('#puesto_id option').clone().appendTo('#edit_puesto_id');
                        }
                        if ($('#edit_departamento_id').length && $('#edit_departamento_id option').length <= 1 && $('#departamento_id').length) {
                            $('#departamento_id option').clone().appendTo('#edit_departamento_id');
                        }
                        // set values after ensuring options exist
                        try { $('#edit_puesto_id').val(e.puesto_id || ''); } catch(e){}
                        try { $('#edit_departamento_id').val(e.departamento_id || ''); } catch(e){}
                    } catch (err) { /* ignore clone errors */ }
                    // Set genero if provided
                    // store fetched data on the modal element for applying after show
                    $('#edit_foto_actual').val(e.foto || '');
                    // Set comentarios in edit modal will be applied after modal shown
                    // We purposely defer assigning text inputs/selects until after the modal
                    // is fully shown to avoid race conditions with browser autofill/focus
                    // that were causing "first-open" validation false-positives.
                    // set photo preview
                    if (e.thumbnail) {
                        $('#edit_foto_preview').attr('src', e.thumbnail);
                    } else if (e.foto) {
                        $('#edit_foto_preview').attr('src', 'uploads/' + e.foto);
                    } else {
                        $('#edit_foto_preview').attr('src', 'uploads/placeholder.png');
                    }

                    var modalElEdit = document.getElementById('modalEditar');
                    // debug banner removed (issue confirmed fixed)
                    var modal = new bootstrap.Modal(modalElEdit);
                    // Attach shown handler to populate inputs reliably after Bootstrap finishes
                    try { var editTabEl = modalElEdit.querySelector('#edit-generals-tab'); if (editTabEl) new bootstrap.Tab(editTabEl).show(); } catch (e) { }
                    if (typeof updateTabBadges === 'function') {
                        modalElEdit.addEventListener('shown.bs.modal', function () {
                            try {
                                // Unconditionally apply fetched values after the modal is shown.
                                // This avoids races with browser autofill/focus that could clear
                                // or move focus and make inputs appear empty to validity checks.
                                var applyMap = {
                                    edit_nombres: e.nombres || '',
                                    edit_apellidos: e.apellidos || '',
                                    edit_fecha_nacimiento: e.fecha_nacimiento || '',
                                    edit_genero: e.genero || '',
                                    edit_comentarios: e.comentarios || ''
                                };
                                Object.keys(applyMap).forEach(function(id){
                                    try {
                                        var el = document.getElementById(id);
                                        if (!el) return;
                                        el.value = applyMap[id];
                                        try { el.defaultValue = applyMap[id]; } catch(e){}
                                        try { el.setAttribute('value', applyMap[id]); } catch(e){}
                                        el.dispatchEvent(new Event('input', { bubbles: true }));
                                        el.dispatchEvent(new Event('change', { bubbles: true }));
                                    } catch(err){}
                                });
                                // Ensure selects reflect values (in case options were cloned earlier)
                                try { $('#edit_puesto_id').val(e.puesto_id || ''); } catch(e){}
                                try { $('#edit_departamento_id').val(e.departamento_id || ''); } catch(e){}
                                // Final badge update and scheduled refreshes
                                try { updateTabBadges(modalElEdit); } catch(e){}
                                scheduleBadgeRefresh(modalElEdit);
                                // Set focus to the first input to avoid focus remaining on close button
                                try { var first = modalElEdit.querySelector('input, select, textarea'); if (first) first.focus(); } catch(e){}
                            } catch (e) {}
                        }, { once: true });

                        // Blur any focused descendant when the modal is about to hide to avoid
                        // "aria-hidden descendant retains focus" warnings in some browsers.
                        modalElEdit.addEventListener('hide.bs.modal', function () { try { var a = document.activeElement; if (a && modalElEdit.contains(a)) a.blur(); } catch (e) { } }, { once: true });
                    }

                    // finally show the modal
                    modal.show();

                    // Fallback reapply: in some browsers/extensions autofill or
                    // focus behaviors can clear values immediately after show.
                    // Reapply the most important fields a few times shortly after
                    // showing the modal to make the assignment robust.
                    try {
                        [50, 200].forEach(function(delay){
                            setTimeout(function(){
                                try {
                                    var en = document.getElementById('edit_nombres');
                                    var ea = document.getElementById('edit_apellidos');
                                    if (en && (en.value||'').toString().trim() === '') en.value = e.nombres || '';
                                    if (ea && (ea.value||'').toString().trim() === '') ea.value = e.apellidos || '';
                                    try { var ef = document.getElementById('edit_fecha_nacimiento'); if (ef && (ef.value||'')==='' ) ef.value = e.fecha_nacimiento || ''; } catch(e2){}
                                    try { var eg = document.getElementById('edit_genero'); if (eg && (eg.value||'')==='' ) eg.value = e.genero || ''; } catch(e2){}
                                    try { updateTabBadges(modalElEdit); } catch(e){}
                                    try { scheduleBadgeRefresh(modalElEdit); } catch(e){}
                                } catch (err) { }
                            }, delay);
                        });
                    } catch (err) { }
                } else if (json.error) {
                    showToast('Error', json.error, 'danger');
                }
            }).catch(err => {
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

        // Ensure badge-refresh helper exists: dispatch input/change events and re-run badge checks
        function scheduleBadgeRefresh(root) {
            try {
                var delays = [0, 50, 250, 800];
                delays.forEach(function(d){
                    setTimeout(function(){
                        try {
                            root = root || document;
                            // trigger input/change events for elements that might be autofilled
                            var elems = root.querySelectorAll('input, textarea, select');
                            elems.forEach(function(el){ try { el.dispatchEvent(new Event('input', { bubbles: true })); el.dispatchEvent(new Event('change', { bubbles: true })); } catch(e){} });
                            try { updateTabBadges(root); } catch(e){}
                        } catch(e){}
                    }, d);
                });
            } catch(e) { /* ignore */ }
        }

        // -- Persist last-open tab per modal/form and per-tab validation badges --
        (function(){
            const LS_PREFIX = 'lotificaciones_last_tab_';

            // Save shown tab to localStorage; works for buttons with data-bs-toggle="tab"
            document.addEventListener('shown.bs.tab', function (e) {
                try {
                    const target = e.target; // the activated tab button
                    const tabSelector = target.getAttribute('data-bs-target') || target.getAttribute('href');
                    if (!tabSelector) return;
                    // find enclosing modal id or 'page' for the new form
                    let container = target.closest('.modal');
                    let key = LS_PREFIX + (container ? container.id : 'newform');
                    // normalize selector to pane id (remove leading #)
                    const paneId = (tabSelector.charAt(0) === '#') ? tabSelector.substring(1) : tabSelector;
                    localStorage.setItem(key, paneId);
                } catch (err) { /* ignore storage errors */ }
            }, false);

            // Restore tab when modal opens
            ['modalFicha','modalEditar'].forEach(function(modalId){
                const modalEl = document.getElementById(modalId);
                if (!modalEl) return;
                modalEl.addEventListener('show.bs.modal', function () {
                    try {
                        const saved = localStorage.getItem(LS_PREFIX + modalId);
                        if (saved) {
                            const tabBtn = modalEl.querySelector('[data-bs-target="#' + saved + '"]');
                            if (tabBtn) new bootstrap.Tab(tabBtn).show();
                        }
                    } catch (err) { }
                });
            });

            // Restore tab for new employee form on page load
            try {
                const savedNew = localStorage.getItem(LS_PREFIX + 'newform');
                if (savedNew) {
                    const btn = document.querySelector('[data-bs-target="#' + savedNew + '"]');
                    if (btn) new bootstrap.Tab(btn).show();
                }
            } catch (err) { }

            // Per-tab validation: show badge if tab contains invalid required fields
            function updateTabBadges(formEl) {
                const badges = document.querySelectorAll('.badge-tab');
                badges.forEach(b => { b.style.display = 'none'; b.textContent = ''; b.classList.remove('bg-danger','text-white','rounded-pill','px-1'); });

                // Scope search to the provided form/modal to avoid counting fields in other forms
                const root = formEl || document;
                const tabPanes = root.querySelectorAll('.tab-pane');
                tabPanes.forEach(function(pane){
                    const paneId = pane.id;
                    if (!paneId) return;

                    // gather required fields in this pane
                    const requiredElems = pane.querySelectorAll('input[required], textarea[required], select[required]');
                    let invalidCount = 0;
                    const invalidList = [];
                    requiredElems.forEach(function(el) {
                        try {
                            // For text-like inputs, consider whitespace-only as empty
                            if (el.tagName === 'INPUT' && (el.type === 'text' || el.type === 'search' || el.type === 'tel' || el.type === 'email' || el.type === 'url')) {
                                if ((el.value || '').toString().trim() === '') { invalidCount++; invalidList.push(el); return; }
                            }
                            // Use native validity as fallback for other types (select/date/etc.)
                            if (typeof el.checkValidity === 'function') {
                                if (!el.checkValidity()) { invalidCount++; invalidList.push(el); }
                            } else {
                                // last resort: empty value
                                if (!el.value) { invalidCount++; invalidList.push(el); }
                            }
                        } catch (e) {
                            // ignore individual element errors
                        }
                    });

                    if (invalidCount > 0) {
                        // prefer badges inside the same root (modal/form); fallback to document
                        const badge = (root && root.querySelector) ? root.querySelector('.badge-tab[data-tab="' + paneId + '"]') : null;
                        const badgeElem = badge || document.querySelector('.badge-tab[data-tab="' + paneId + '"]');
                        if (badgeElem) {
                            badgeElem.style.display = 'inline-block';
                            badgeElem.classList.add('bg-danger','text-white','rounded-pill','px-1');
                            badgeElem.textContent = '¡' + invalidCount + '!';
                        }
                        // prefer tab link inside same root
                        const tabLinkRoot = (root && root.querySelector) ? root.querySelector('[data-bs-target="#' + paneId + '"]') : null;
                        const tabLink = tabLinkRoot || document.querySelector('[data-bs-target="#' + paneId + '"]');
                        if (tabLink) tabLink.classList.add('text-danger');
                        // debug: list concise descriptors for invalid elements (id/name/tag/type/value)
                        if (invalidList.length) {
                            try {
                                const desc = invalidList.map(function(el){ return { tag: el.tagName, id: el.id||null, name: el.name||null, type: el.type||null, value: (el.value||'').toString().slice(0,40) }; });
                                console.debug('Tab', paneId, 'invalid elements:', desc);
                            } catch(e){ console.debug('Tab', paneId, 'invalid elements count', invalidList.length); }
                        }
                    } else {
                        const tabLinkRoot = (root && root.querySelector) ? root.querySelector('[data-bs-target="#' + paneId + '"]') : null;
                        const tabLink = tabLinkRoot || document.querySelector('[data-bs-target="#' + paneId + '"]');
                        if (tabLink) tabLink.classList.remove('text-danger');
                    }
                });
            }

            // Validate on submit for create and edit forms
            ['#formEmpleado','#formEditar'].forEach(function(selector){
                const form = document.querySelector(selector);
                if (!form) return;
                form.addEventListener('submit', function(e){
                    // run HTML5 validity checks grouped by tab
                    const panes = form.querySelectorAll('.tab-pane');
                    let firstInvalid = null;
                    panes.forEach(function(pane){
                        const invalid = pane.querySelectorAll('input[required]:invalid, textarea[required]:invalid, select[required]:invalid');
                        if (!firstInvalid && invalid.length) firstInvalid = invalid[0];
                    });
                    updateTabBadges(form);
                    if (firstInvalid) {
                        e.preventDefault();
                        e.stopPropagation();
                        // switch to the tab containing the first invalid
                        const pane = firstInvalid.closest('.tab-pane');
                        if (pane) {
                            const btn = document.querySelector('[data-bs-target="#' + pane.id + '"]');
                            if (btn) new bootstrap.Tab(btn).show();
                        }
                        firstInvalid.focus();
                        showToast('Error', 'Por favor corrige los campos marcados en la pestaña seleccionada.', 'danger');
                        return false;
                    }
                    // otherwise allow submission to proceed
                }, false);

                // live update on input/change so selects also trigger immediate badge updates
                form.addEventListener('input', function(){ updateTabBadges(form); }, true);
                form.addEventListener('change', function(){ updateTabBadges(form); }, true);

                // run an initial check to show badges/tab highlights on page load
                try { updateTabBadges(form); } catch (e) { }
            });
        })();
});