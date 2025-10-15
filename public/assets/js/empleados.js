$(document).ready(function () {
    // Clean, single-block empleados.js

    function exportHeader(data) {
        var key = (data || '').toString().trim().toLowerCase().replace(/\s+/g, '_');
        // Resolve common headers using runtime translations when available
        var map = {
            id: (typeof getTranslation === 'function' ? getTranslation('id', 'ID') : 'ID'),
            codigo: (typeof getTranslation === 'function' ? getTranslation('codigo_label', 'Código') : 'Código'),
            nombres: (typeof getTranslation === 'function' ? getTranslation('nombres', 'Nombres') : 'Nombres'),
            apellidos: (typeof getTranslation === 'function' ? getTranslation('apellidos', 'Apellidos') : 'Apellidos'),
            fecha_de_nacimiento: (typeof getTranslation === 'function' ? getTranslation('fecha_nacimiento', 'Fecha de Nacimiento') : 'Fecha de Nacimiento'),
            edad: (typeof getTranslation === 'function' ? getTranslation('edad', 'Edad') : 'Edad'),
            foto: (typeof getTranslation === 'function' ? getTranslation('foto', 'Foto') : 'Foto'),
            puesto_id: (typeof getTranslation === 'function' ? getTranslation('puesto', 'Puesto ID') : 'Puesto ID'),
            puesto_nombre: (typeof getTranslation === 'function' ? getTranslation('puesto', 'Puesto') : 'Puesto'),
            departamento_id: (typeof getTranslation === 'function' ? getTranslation('departamento', 'Departamento ID') : 'Departamento ID'),
            departamento_nombre: (typeof getTranslation === 'function' ? getTranslation('departamento', 'Departamento') : 'Departamento'),
            genero: (typeof getTranslation === 'function' ? getTranslation('genero', 'Género') : 'Género'),
            comentarios: (typeof getTranslation === 'function' ? getTranslation('comments', 'Comentarios') : 'Comentarios')
        };
        return map[key] || data;
    }

    var tabla = null;

    function loadScript(url, cb) { var s = document.createElement('script'); s.type='text/javascript'; s.src = url; s.onload = function(){ cb(null); }; s.onerror = function(){ cb(new Error('Failed to load ' + url)); }; document.head.appendChild(s); }

    // Loading overlay helpers (spinner)
    function ensureLoadingContainer() {
        var table = document.getElementById('tablaEmpleados');
        if (!table) return null;
        var container = table.closest ? table.closest('.card-body') : null;
        if (!container) container = table.parentNode || document.body;
        try {
            var st = window.getComputedStyle(container);
            if (st && st.position === 'static') container.style.position = 'relative';
        } catch (e) { /* ignore */ }
        return container;
    }

    function showLoading(msg) {
        try {
            if (document.getElementById('tabla-loading')) return;
            var container = ensureLoadingContainer();
            if (!container) return;
            var overlay = document.createElement('div');
            overlay.id = 'tabla-loading';
            // base styles
            overlay.style.position = 'absolute';
            overlay.style.top = '0';
            overlay.style.left = '0';
            overlay.style.right = '0';
            overlay.style.bottom = '0';
            overlay.style.background = 'rgba(255,255,255,0.0)';
            overlay.style.display = 'flex';
            overlay.style.alignItems = 'center';
            overlay.style.justifyContent = 'center';
            overlay.style.zIndex = '9999';
            overlay.style.transition = 'background 220ms ease, opacity 220ms ease';
            overlay.style.opacity = '0';
            overlay.innerHTML = '<div class="text-center"><div class="spinner-border text-primary" role="status" aria-hidden="true"></div><div class="mt-2 small">' + (msg || 'Cargando...') + '</div></div>';
            container.appendChild(overlay);
            // trigger fade-in on next frame
            requestAnimationFrame(function(){
                try { overlay.style.background = 'rgba(255,255,255,0.75)'; overlay.style.opacity = '1'; } catch(e){}
            });
        } catch (e) { /* ignore */ }
    }

    function hideLoading() {
        try {
            var el = document.getElementById('tabla-loading');
            if (!el) return;
            // fade out then remove
            try { el.style.background = 'rgba(255,255,255,0.0)'; el.style.opacity = '0'; } catch(e){}
            var removeAfter = function(){ try { if (el && el.parentNode) el.parentNode.removeChild(el); } catch(e){} };
            // wait for transition end (safety 300ms)
            var fired = false;
            var onEnd = function(){ if (fired) return; fired = true; removeAfter(); el.removeEventListener('transitionend', onEnd); };
            el.addEventListener('transitionend', onEnd);
            setTimeout(onEnd, 320);
        } catch (e) { }
    }

    function attachTableHandlers(dtInstance) {
        try {
            dtInstance.on('error.dt', function (e, settings, techNote, message) {
                try {
                    if (message && message.indexOf && message.indexOf('Requested unknown parameter') !== -1) {
                        try { dtInstance.state.clear(); } catch (err) {}
                        try { localStorage.removeItem('DataTables_' + settings.sInstance + '_' + location.pathname); } catch (err) {}
                        location.reload();
                    }
                } catch (e) {}
            });

            dtInstance.on('init.dt', function(){
                try {
                    if (!$.fn.colResizable) {
                        loadScript('https://cdn.jsdelivr.net/npm/colresizable@1.6.0/colResizable-1.6.min.js', function(err){ if (!err) try { $('#tablaEmpleados').colResizable({ liveDrag: true, minWidth: 30, resizeMode: 'fit' }); } catch(e){} });
                    } else {
                        try { $('#tablaEmpleados').colResizable({ liveDrag: true, minWidth: 30, resizeMode: 'fit' }); } catch(e){}
                    }
                } catch(e){}
            });

            dtInstance.on('column-visibility.dt', function(e, settings, column, state){
                try {
                    $('#tablaEmpleados thead th').each(function(i, th){ if (th && th.style) th.style.width = ''; });
                    $('#tablaEmpleados tbody tr').each(function(){ Array.from(this.children).forEach(function(td){ if (td && td.style) td.style.width = ''; }); });
                    try { if ($.fn.colResizable) { try { $('#tablaEmpleados').colResizable('destroy'); } catch(e){} try { $('#tablaEmpleados').colResizable('remove'); } catch(e){} try { $('#tablaEmpleados').colResizable({ liveDrag: true, minWidth: 30, resizeMode: 'fit' }); } catch(e){} } } catch(e){}
                    setTimeout(function(){ try { dtInstance.columns.adjust(); dtInstance.draw(false); } catch(e){} }, 80);
                } catch(e){}
            });
        } catch(e){}
    }

    function buildTable(lang) {
        try { if (tabla && $.fn.DataTable.isDataTable('#tablaEmpleados')) { tabla.clear(); tabla.destroy(); $('#tablaEmpleados').empty(); } } catch(e) {}
        // show spinner while fetching column metadata
        showLoading();
        fetch('empleados/ajax?lang=' + encodeURIComponent(lang || 'es')).then(function(r){ return r.json(); }).then(function(resp){
            if (!resp || !resp.columns) { console.error('Invalid server response', resp); return; }
            // Apply server-provided titles to form labels where applicable
            try { applyFieldLabelsFromColumns(resp.columns); } catch(e) {}
            var cols = resp.columns.map(function(c){
                var obj = { data: c.data, title: c.title };
                if (c.className) obj.className = c.className;
                if (typeof c.visible !== 'undefined') obj.visible = c.visible;
                if (c.data === 'thumbnail') { obj.render = function(d){ return d ? '<img src="'+d+'" class="thumb-sm" alt="thumb">' : '<img src="uploads/placeholder.png" class="thumb-sm" alt="thumb">'; }; obj.className = (obj.className||'') + ' no-export'; }
                if (c.data === 'puesto_id') { obj.render = function(data, type, row){ if (row && row.puesto_nombre) return row.puesto_nombre; try { if (!data) return ''; var opt = document.querySelector('#puesto_id option[value="'+data+'"]'); if (opt) return opt.textContent.trim(); } catch(e){} return data; }; }
                if (c.data === 'departamento_id') { obj.render = function(data, type, row){ if (row && row.departamento_nombre) return row.departamento_nombre; try { if (!data) return ''; var opt = document.querySelector('#departamento_id option[value="'+data+'"]'); if (opt) return opt.textContent.trim(); } catch(e){} return data; }; }
                if (!c.data) { obj.render = function(data, type, row){ return '<button class="btn btn-sm btn-info ver-ficha" data-id="'+row.id+'" title="Ver detalles"><i class="bi bi-eye"></i></button> <button class="btn btn-sm btn-warning editar" data-id="'+row.id+'"><i class="bi bi-pencil-square"></i></button> <button class="btn btn-sm btn-danger eliminar" data-id="'+row.id+'"><i class="bi bi-trash"></i></button>'; }; obj.className = (obj.className||'') + ' no-export'; }
                return obj;
            });
            // Initialize DataTable using ajax option so we can call tabla.ajax.reload()
            tabla = $('#tablaEmpleados').DataTable({
                ajax: function(data, callback, settings){
                    fetch('empleados/ajax?lang=' + encodeURIComponent(lang || 'es')).then(function(r){ return r.json(); }).then(function(res){ callback({ data: res.data || [] }); }).catch(function(err){ console.error('DataTables ajax fetch error', err); callback({ data: [] }); });
                },
                columns: cols,
                dom: 'Bfrtip',
                colReorder: true,
                stateSave: true,
                stateLoadParams: function(settings, data){ try { if (data && data.columns && Array.isArray(data.columns)) { data.columns.forEach(function(col){ if (col && col.data === 'puesto_nombre') col.data = 'puesto_id'; if (col && col.data === 'departamento_nombre') col.data = 'departamento_id'; }); } } catch(e){} },
                buttons: [
                    { extend: 'colvis', text: 'Columnas' },
                    {
                        extend: 'collection',
                        text: 'Exportar',
                        className: 'btn-export-collection',
                        buttons: [
                                // SheetJS-based exporter (primary .xlsx exporter)
                                    { text: '<i class="bi bi-file-earmark-spreadsheet me-2"></i> ' + (typeof getTranslation === 'function' ? getTranslation('export_xls_sheetjs','XLSX') : 'XLSX'), action: function(e, dt, node, config){
                                            try {
                                                var visibleCols = dt.columns(':visible:not(.no-export)').indexes().toArray();
                                                var headers = visibleCols.map(function(i){ var h = dt.column(i).header(); return exportHeader(h ? (h.textContent || h.innerText || '') : ''); });
                                                var dataRows = [];
                                                // Helper to guess types: number, date (with locale DD/MM/YYYY), or string
                                                function parseLocaleDate(s) {
                                                    // Accepts DD/MM/YYYY or DD/MM/YYYY HH:mm:ss (24h), optionally with dashes or slashes
                                                    // normalize separators
                                                    var orig = String(s).trim();
                                                    var s2 = orig.replace(/-/g, '/');
                                                    var m = s2.match(/^\s*(\d{1,2})\/(\d{1,2})\/(\d{4})(?:\s+(\d{1,2}):(\d{2})(?::(\d{2}))?)?\s*$/);
                                                    if (m) {
                                                        var day = parseInt(m[1],10), month = parseInt(m[2],10)-1, year = parseInt(m[3],10);
                                                        var hour = m[4] ? parseInt(m[4],10) : 0;
                                                        var min = m[5] ? parseInt(m[5],10) : 0;
                                                        var sec = m[6] ? parseInt(m[6],10) : 0;
                                                        var dt = new Date(year, month, day, hour, min, sec);
                                                        if (!isNaN(dt.getTime())) return { date: dt, hasTime: !!m[4] };
                                                    }
                                                    return null;
                                                }

                                                function guessType(val){
                                                    if (val === null || typeof val === 'undefined') return { v: '' };
                                                    var s = String(val).trim();
                                                    if (s === '') return { v: '' };
                                                    // number?
                                                    var num = s.replace(/,/g,'');
                                                    if (!isNaN(num) && isFinite(num)) return { v: Number(num), t: 'n' };
                                                    // locale date DD/MM/YYYY or with time
                                                    var parsed = parseLocaleDate(s);
                                                    if (parsed) return { v: parsed.date, t: 'd', hasTime: parsed.hasTime };
                                                    // ISO date fallback
                                                    var d = Date.parse(s);
                                                    if (!isNaN(d)) return { v: new Date(d), t: 'd', hasTime: (String(s).indexOf('T') !== -1 || String(s).indexOf(':') !== -1) };
                                                    return { v: s, t: 's' };
                                                }

                                                dt.rows({ search: 'applied', order: 'applied' }).every(function(rowIdx){
                                                    var rowData = this.data();
                                                    var row = visibleCols.map(function(ci){
                                                        var col = dt.settings()[0].aoColumns[ci];
                                                        var key = col && col.data;
                                                        var val = '';
                                                        try { val = (typeof rowData === 'object' && key) ? rowData[key] : (rowData && rowData[ci]); } catch(e) { val = ''; }
                                                        try { if (val && typeof val === 'string') { var tmp = document.createElement('div'); tmp.innerHTML = val; val = tmp.textContent || tmp.innerText || val; } } catch(e){}
                                                        return val;
                                                    });
                                                    dataRows.push(row);
                                                });

                                                // Build worksheet from AOA then convert cells to guessed types
                                                var ws_data = [headers].concat(dataRows);
                                                var wb = XLSX.utils.book_new();
                                                var ws = XLSX.utils.aoa_to_sheet(ws_data);

                                                // Convert types for data cells (skip header row) and set formats for dates
                                                for (var R = 1; R < ws_data.length; ++R) {
                                                    for (var C = 0; C < visibleCols.length; ++C) {
                                                        var cell_address = XLSX.utils.encode_cell({ r: R, c: C });
                                                        var raw = ws_data[R][C];
                                                        var guessed = guessType(raw);
                                                        if (!ws[cell_address]) ws[cell_address] = { t: guessed.t || 's', v: guessed.v };
                                                        else { ws[cell_address].v = guessed.v; if (guessed.t) ws[cell_address].t = guessed.t; }
                                                        // If it's a date, apply the forced format: YYYY-MM-DD or YYYY-MM-DD HH:mm:ss
                                                        if (guessed && guessed.t === 'd' && ws[cell_address]) {
                                                            // Excel serial date requires JS Date object as v and cell.t = 'd' (SheetJS will handle conversion)
                                                            // Apply number format for display
                                                            if (guessed.hasTime) ws[cell_address].z = 'yyyy-mm-dd hh:mm:ss';
                                                            else ws[cell_address].z = 'yyyy-mm-dd';
                                                        }
                                                    }
                                                }

                                                // Set reasonable column widths based on header lengths
                                                ws['!cols'] = headers.map(function(h, idx){
                                                    var maxLen = String(h).length;
                                                    for (var r = 0; r < dataRows.length; ++r) {
                                                        try { var l = String(dataRows[r][idx] || '').length; if (l > maxLen) maxLen = l; } catch(e){}
                                                    }
                                                    // approximate width (characters + padding)
                                                    return { wch: Math.min(Math.max(maxLen + 2, 10), 60) };
                                                });

                                                XLSX.utils.book_append_sheet(wb, ws, 'Empleados');
                                                var wbout = XLSX.write(wb, { bookType: 'xlsx', type: 'array' });
                                                var blob = new Blob([wbout], { type: 'application/octet-stream' });
                                                var fname = (config && config.filename) ? config.filename : 'empleados';
                                                if (!fname.toLowerCase().endsWith('.xlsx')) fname = fname + '.xlsx';
                                                var url = window.URL.createObjectURL(blob);
                                                var a = document.createElement('a'); a.href = url; a.download = fname; document.body.appendChild(a); a.click(); a.remove(); window.URL.revokeObjectURL(url);
                                            } catch (err) { console.error('SheetJS export failed', err); alert('Export failed: ' + (err && err.message ? err.message : err)); }
                                        }, filename: 'empleados' },
                            { extend: 'csvHtml5', text: '<i class="bi bi-filetype-csv me-2"></i> ' + (typeof getTranslation === 'function' ? getTranslation('export_csv','CSV') : 'CSV'), filename: 'empleados', extension: '.csv', fieldSeparator: ',', bom: true, exportOptions: { columns: ':visible:not(.no-export)', format: { header: exportHeader } } },
                            { extend: 'csvHtml5', text: '<i class="bi bi-file-earmark-text me-2"></i> ' + (typeof getTranslation === 'function' ? getTranslation('export_txt','TXT') : 'TXT'), filename: 'empleados', extension: '.txt', fieldSeparator: '\t', bom: true, exportOptions: { columns: ':visible:not(.no-export)', format: { header: exportHeader } } }
                        ]
                    }
                ],
                language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' }
            });

            attachTableHandlers(tabla);
            // debug logs removed
            // hide spinner after successful init
            try { hideLoading(); } catch(e){}
        }).catch(function(err){ console.error('Error building table', err); });
        // hide spinner on fetch error as well
        try { setTimeout(function(){ /* safety hide in case chain fails */ hideLoading(); }, 5000); } catch(e){}
    }

    // Helper to get current language from selector or localStorage
    function getCurrentLang() {
        try { return (document.getElementById('langSelect') && document.getElementById('langSelect').value) || localStorage.getItem('lotificaciones_lang') || 'es'; } catch(e){ return 'es'; }
    }

    // Prefer DataTables ajax reload when available, otherwise rebuild table (fetch columns)
    function reloadOrBuild() {
        try {
            if (tabla && tabla.ajax && typeof tabla.ajax.reload === 'function') {
                try { tabla.ajax.reload(null, false); return; } catch(e){}
            }
            try { buildTable(getCurrentLang()); } catch(e){}
        } catch(e) { try { buildTable(getCurrentLang()); } catch(err){} }
    }

    // Theme and palette helpers
    function applyPalette(name) {
        try {
            var palettes = {
                blue: { '--primary-600': '#1e6fb3', '--primary-400': '#4d9ae0', '--accent': '#7c5aa8' },
                // teal was slightly light for white headers; darken to meet WCAG AA for white-on-primary
                teal: { '--primary-600': '#0c857a', '--primary-400': '#34d399', '--accent': '#0ea5a4' },
                violet: { '--primary-600': '#7c5aa8', '--primary-400': '#a78bfa', '--accent': '#7c5aa8' }
            };
            var p = palettes[name] || palettes.blue;
            Object.keys(p).forEach(function(k){ try { document.documentElement.style.setProperty(k, p[k]); } catch(e){} });
            // also set modal divider gradient stops derived from primary color for a pleasing accent
            try {
                var primary = p['--primary-600'] || '#1e6fb3';
                // simple hex -> rgba fallback function
                function hexToRgba(hex, alpha){ try { hex = hex.replace('#',''); var bigint = parseInt(hex,16); var r = (bigint >> 16) & 255; var g = (bigint >> 8) & 255; var b = bigint & 255; return 'rgba('+r+','+g+','+b+','+ (alpha||1) +')'; } catch(e){ return 'rgba(0,0,0,'+ (alpha||1) +')'; } }
                var start = hexToRgba(primary, 0.06);
                var end = hexToRgba(primary, 0.12);
                document.documentElement.style.setProperty('--modal-divider-color-start', start);
                document.documentElement.style.setProperty('--modal-divider-color-end', end);
            } catch(e){}
            // mark active swatch
            try { document.querySelectorAll('.palette-swatch').forEach(function(el){ try { el.classList.toggle('active', el.getAttribute('data-palette') === name); } catch(e){} }); } catch(e){}
            try { localStorage.setItem('lotificaciones_palette', name); } catch(e){}
        } catch(e) { }
    }

    // Wire up palette swatches and initialize saved palette
    try {
        var swatches = document.querySelectorAll('.palette-swatch');
        swatches.forEach(function(s){ s.addEventListener('click', function(){ try { var name = s.getAttribute('data-palette'); applyPalette(name); } catch(e){} }); });

        // initialize from localStorage
        try {
            var savedPal = localStorage.getItem('lotificaciones_palette') || 'blue';
            applyPalette(savedPal);
        } catch(e) { applyPalette('blue'); }
    } catch(e) {}

    // Map server column data keys to label selectors in the DOM and apply titles
    function applyFieldLabelsFromColumns(columns) {
        if (!Array.isArray(columns)) return;
        // mapping of column.data -> CSS selector(s) for labels
        var map = {
            codigo: 'label[for="codigo"], label[for="edit_codigo"]',
            nombres: 'label[for="nombres"], label[for="edit_nombres"]',
            apellidos: 'label[for="apellidos"], label[for="edit_apellidos"]',
            fecha_nacimiento: 'label[for="fecha_nacimiento"], label[for="edit_fecha_nacimiento"]',
            genero: 'label[for="genero"], label[for="edit_genero"]',
            puesto_id: 'label[for="puesto_id"], label[for="edit_puesto_id"]',
            departamento_id: 'label[for="departamento_id"], label[for="edit_departamento_id"]',
            comentarios: 'label[for="comentarios"], label[for="edit_comentarios"]',
            foto: 'label[for="foto"], label[for="edit_foto"]'
        };

        columns.forEach(function(col){
            try {
                var key = col && col.data;
                if (!key || !map[key]) return;
                var title = col.title || '';
                var nodes = document.querySelectorAll(map[key]);
                nodes.forEach(function(n){
                    try {
                        // Prefer updating a nested span.label-text if present, otherwise update the label's own textContent
                        var lbl = n.querySelector && n.querySelector('.label-text');
                        if (lbl) {
                            lbl.textContent = title;
                        } else {
                            // fallback: set only the text node to avoid clobbering children
                            if (n.childNodes && n.childNodes.length && n.childNodes[0].nodeType === Node.TEXT_NODE) {
                                n.childNodes[0].nodeValue = title;
                            } else {
                                n.textContent = title;
                            }
                        }
                    } catch (e) { try { n.textContent = title; } catch (err) {} }
                });
            } catch (e) { /* ignore individual column errors */ }
        });
        // Update modal display labels (e.g., <strong>Código:</strong> in ficha modal)
        try {
            var displayMap = {
                codigo: 'ficha_codigo',
                nombres: 'ficha_nombres',
                apellidos: 'ficha_apellidos',
                fecha_nacimiento: 'ficha_fecha_nacimiento',
                edad: 'ficha_edad',
                genero: 'ficha_genero',
                puesto_id: 'ficha_puesto',
                departamento_id: 'ficha_departamento',
                comentarios: 'ficha_comentarios'
            };
            columns.forEach(function(col){
                try {
                    var key = col && col.data;
                    if (!key || !displayMap[key]) return;
                    var title = col.title || '';
                    var span = document.getElementById(displayMap[key]);
                    if (!span) return;
                    // Prefer a strong .label-text inside the list item
                    var li = span.closest && span.closest('li');
                    var strong = null;
                    if (li) strong = li.querySelector('strong .label-text');
                    // If not found, search for any strong and set its innerText safely
                    if (!strong) {
                        var s = span.previousElementSibling && span.previousElementSibling.tagName === 'STRONG' ? span.previousElementSibling : (span.parentNode ? span.parentNode.querySelector('strong') : null);
                        if (s) {
                            // if s contains a .label-text, use it; otherwise, create one
                            var st = s.querySelector('.label-text');
                            if (st) strong = st; else {
                                try {
                                    // wrap the current strong text in a span.label-text
                                    var txt = s.textContent || '';
                                    s.textContent = ''; var wrap = document.createElement('span'); wrap.className = 'label-text'; wrap.textContent = txt.replace(/:$/,''); s.appendChild(wrap); strong = wrap;
                                } catch(e) { strong = s; }
                            }
                        }
                    }
                    if (strong) {
                        try { strong.textContent = title; } catch(e) { try { strong.textContent = title; } catch(err){} }
                    }
                } catch(e) { }
            });
        } catch(e) { /* ignore modal label updates */ }
    }

    // language selector wiring (no focus/blur on modal hide)
    try {
        var langSel = document.getElementById('langSelect');
        if (langSel) {
            // Translation loader + cache
            var _i18nCache = {};

            function loadTranslations(lang) {
                return new Promise(function(resolve){
                    try {
                        if (_i18nCache[lang]) return resolve(_i18nCache[lang]);
                        var url = 'assets/i18n/' + encodeURIComponent(lang) + '.json';
                        fetch(url).then(function(r){ if (!r.ok) throw new Error('Failed to load ' + url); return r.json(); }).then(function(json){ _i18nCache[lang] = json || {}; resolve(_i18nCache[lang]); }).catch(function(){ _i18nCache[lang] = {}; resolve(_i18nCache[lang]); });
                    } catch(e) { _i18nCache[lang] = {}; resolve(_i18nCache[lang]); }
                });
            }

            function applyUiLabels(lang) {
                try {
                    var dict = _i18nCache[lang] || {};
                    // fallback to english or spanish keys if missing
                    function t(key) { return dict[key] || (_i18nCache['en'] && _i18nCache['en'][key]) || (_i18nCache['es'] && _i18nCache['es'][key]) || key; }
                    document.querySelectorAll('[data-i18n]').forEach(function(el){
                        try {
                            var key = el.getAttribute('data-i18n');
                            if (!key) return;
                            var txt = t(key);
                            // set visible text appropriately
                            var nested = el.querySelector && el.querySelector('.label-text');
                            if (nested) nested.textContent = txt;
                            else if (el.classList && el.classList.contains('label-text')) el.textContent = txt;
                            else {
                                // for buttons or inline spans we may want to replace innerText but preserve icons
                                if (el.tagName === 'BUTTON' || el.tagName === 'A') {
                                    // if the button contains an icon (i) keep it and update the text node after it
                                    var icon = el.querySelector('i');
                                    if (icon) {
                                        // find a text node sibling or span
                                        var txtSpan = el.querySelector('.label-text') || el.querySelector('span');
                                        if (txtSpan) txtSpan.textContent = txt; else {
                                            // append a small span
                                            var s = document.createElement('span'); s.className = 'label-text'; s.textContent = txt; el.appendChild(s);
                                        }
                                    } else {
                                        el.textContent = txt;
                                    }
                                } else {
                                    el.textContent = txt;
                                }
                            }

                            // apply aria-label override if provided
                            try {
                                var ariaKey = el.getAttribute('data-i18n-aria');
                                if (ariaKey) {
                                    var ariaTxt = t(ariaKey);
                                    if (ariaTxt) el.setAttribute('aria-label', ariaTxt);
                                }
                            } catch(e){}
                        } catch(e){}
                    });
                    // (Tooltip UI removed) — no-op
                } catch(e){}
            }

            // Lightweight helper to get a translation anywhere in this file
            function getTranslation(key, fallback) {
                try {
                    var lang = (document.getElementById('langSelect') && document.getElementById('langSelect').value) || localStorage.getItem('lotificaciones_lang') || 'es';
                    var dict = _i18nCache[lang] || _i18nCache['es'] || _i18nCache['en'] || {};
                    if (dict && dict[key]) return dict[key];
                    // fallbacks
                    if (_i18nCache['en'] && _i18nCache['en'][key]) return _i18nCache['en'][key];
                    if (_i18nCache['es'] && _i18nCache['es'][key]) return _i18nCache['es'][key];
                } catch(e){}
                return fallback || key;
            }

            langSel.addEventListener('change', function(){ var v = langSel.value || 'es'; try { localStorage.setItem('lotificaciones_lang', v); } catch(e){}; loadTranslations(v).then(function(){ applyUiLabels(v); buildTable(v); }); });
            try { var savedLang = localStorage.getItem('lotificaciones_lang') || 'es'; langSel.value = savedLang; loadTranslations(savedLang).then(function(){ applyUiLabels(savedLang); }); } catch(e){}
        }
    } catch(e){}

    try { var initialLang = (function(){ try { return document.getElementById('langSelect') ? document.getElementById('langSelect').value : (localStorage.getItem('lotificaciones_lang') || 'es'); } catch(e){ return 'es'; } })(); buildTable(initialLang); } catch(e){}

    // minimal css for resize handles
    (function(){ var css = '\n#tablaEmpleados th { position:relative; }\n'; var s = document.createElement('style'); s.type='text/css'; s.appendChild(document.createTextNode(css)); document.head.appendChild(s); })();

    // tooltips init
    try { var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]')); tooltipTriggerList.forEach(function (el) { new bootstrap.Tooltip(el); }); } catch(e){}

    // handlers for ficha (view) - show modal but do not alter focus
    $(document).on('click', '.ver-ficha', function(){
        try {
            var id = $(this).data('id');
            fetch('empleados/get?id='+encodeURIComponent(id)).then(function(r){ return r.json(); }).then(function(json){
                if (json.data) {
                    var e = json.data;
                    try { console.debug('empleados/get response', e); } catch(e2){}
                    // populate codigo using multiple possible property names
                    try {
                        var codeVal = e.codigo || e.code || e.Code || e.Codigo || e.id_code || e.emp_codigo || '';
                        // fallback: if no explicit code, but id exists, try prefixing
                        if (!codeVal && e.id) codeVal = 'EMP' + String(e.id).padStart(3,'0');
                        $('#edit_codigo').val(codeVal || '');
                    } catch(e3){}
                    $('#ficha_codigo').text(e.codigo||'');
                    $('#ficha_nombres').text(e.nombres||'');
                    $('#ficha_apellidos').text(e.apellidos||'');
                    $('#ficha_fecha_nacimiento').text(e.fecha_nacimiento||'');
                    $('#ficha_edad').text(e.edad||'');
                    $('#ficha_genero').text(e.genero||'');
                    $('#ficha_puesto').text(e.puesto_nombre||'');
                    $('#ficha_departamento').text(e.departamento_nombre||'');
                    $('#ficha_comentarios').text(e.comentarios||'');
                    if (e.thumbnail) $('#ficha_foto').attr('src', e.thumbnail).show(); else if (e.foto) $('#ficha_foto').attr('src','uploads/'+e.foto).show(); else $('#ficha_foto').attr('src','uploads/placeholder.png').show();
                    var modalEl = document.getElementById('modalFicha');
                    if (modalEl) { var modal = new bootstrap.Modal(modalEl); modal.show(); }
                } else if (json.error) {
                    try { Swal.fire({ toast:true, position:'top-end', icon:'error', title:json.error || getTranslation('error_fetching_ficha','Error al obtener ficha'), showConfirmButton:false, timer:3500 }); } catch(e){}
                }
            }).catch(function(err){ try { Swal.fire({ toast:true, position:'top-end', icon:'error', title:getTranslation('error_fetching_ficha','Error al obtener ficha'), showConfirmButton:false, timer:3500 }); } catch(e){} });
        } catch(e){}
    });

    // delete handler
    $(document).on('click', '.eliminar', function(){
        try {
            var id = $(this).data('id');
            Swal.fire({ title: getTranslation('confirm_delete_title','¿Eliminar empleado?'), text: getTranslation('confirm_delete_text','Esta acción no se puede deshacer.'), icon: 'warning', showCancelButton: true, confirmButtonText: getTranslation('confirm_delete_confirm','Sí, eliminar'), cancelButtonText: getTranslation('confirm_delete_cancel','Cancelar') }).then(function(result){
                if (result.isConfirmed) {
                    fetch('empleados/delete', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: 'id=' + encodeURIComponent(id) }).then(function(r){ return r.json(); }).then(function(res){
                        if (res.success) {
                            try{ Swal.fire({ toast:true, position:'top-end', icon:'success', title:getTranslation('employee_deleted','Empleado eliminado'), showConfirmButton:false, timer:2000 }); }catch(e){}
                            try { reloadOrBuild(); } catch(e){}
                        } else {
                            try{ Swal.fire({ toast:true, position:'top-end', icon:'error', title: res.error || getTranslation('error_deleting','Error al eliminar'), showConfirmButton:false, timer:3500 }); }catch(e){}
                        }
                    }).catch(function(err){ try{ Swal.fire({ toast:true, position:'top-end', icon:'error', title:getTranslation('error_deleting','Error al eliminar'), showConfirmButton:false, timer:3500 }); }catch(e){} });
                }
            });
        } catch(e){}
    });

    // edit handler: fetch data, populate edit form, and show modal
    $(document).on('click', '.editar', function(){
        try {
            var id = $(this).data('id');
            fetch('empleados/get?id='+encodeURIComponent(id)).then(function(r){ return r.json(); }).then(function(json){
                if (json.data) {
                    var e = json.data;
                    // Ensure edit selects have options (clone from main form if needed)
                    try {
                        if ($('#edit_puesto_id').length && $('#edit_puesto_id option').length <= 1 && $('#puesto_id').length) {
                            $('#puesto_id option').clone().appendTo('#edit_puesto_id');
                        }
                        if ($('#edit_departamento_id').length && $('#edit_departamento_id option').length <= 1 && $('#departamento_id').length) {
                            $('#departamento_id option').clone().appendTo('#edit_departamento_id');
                        }
                    } catch(e) { /* ignore */ }

                    try { $('#edit_id').val(e.id); } catch(e){}
                    try { $('#edit_codigo').val(e.codigo || ''); } catch(e){}
                    try { $('#edit_nombres').val(e.nombres || ''); } catch(e){}
                    try { $('#edit_apellidos').val(e.apellidos || ''); } catch(e){}
                    try { $('#edit_fecha_nacimiento').val(e.fecha_nacimiento || ''); } catch(e){}
                    try { $('#edit_genero').val(e.genero || ''); } catch(e){}
                    try { $('#edit_comentarios').val(e.comentarios || ''); } catch(e){}
                    try { $('#edit_puesto_id').val(e.puesto_id || '').trigger('change'); } catch(e){}
                    try { $('#edit_departamento_id').val(e.departamento_id || '').trigger('change'); } catch(e){}

                    // set current foto value and preview
                    try { $('#edit_foto_actual').val(e.foto || ''); } catch(e){}
                    if (e.thumbnail) { try { $('#edit_foto_preview').attr('src', e.thumbnail); } catch(e){} }
                    else if (e.foto) { try { $('#edit_foto_preview').attr('src', 'uploads/' + e.foto); } catch(e){} }
                    else { try { $('#edit_foto_preview').attr('src', 'uploads/placeholder.png'); } catch(e){} }

                    // show modal
                    // re-validate edit panes so per-tab badges reflect the populated values
                    try { validateAndUpdateForPane('edit-generals'); validateAndUpdateForPane('edit-puesto'); validateAndUpdateForPane('edit-others'); } catch(e){}
                    var modalEl = document.getElementById('modalEditar');
                    if (modalEl) {
                        var modal = new bootstrap.Modal(modalEl);
                        // Before showing, compute tallest tab pane height and set min-height on the tab-content
                        try {
                            // small helper to compute and apply
                            var applyStableEditModalHeight = function() {
                                try {
                                    var panes = modalEl.querySelectorAll('#editFormTabsContent .tab-pane');
                                    var maxH = 0;
                                    panes.forEach(function(p){ try { p.style.minHeight = ''; p.style.minHeight = p.scrollHeight + 'px'; if (p.scrollHeight > maxH) maxH = p.scrollHeight; } catch(e){} });
                                    // set min-height on the tab-content wrapper so switching tabs doesn't change height
                                    var content = modalEl.querySelector('#editFormTabsContent');
                                    if (content) {
                                        content.style.minHeight = maxH + 'px';
                                    }
                                } catch(e){}
                            };

                            // apply once now (will measure current DOM)
                            applyStableEditModalHeight();

                            // Also re-apply after the modal is fully shown to account for fonts/scrollbars
                            modalEl.addEventListener('shown.bs.modal', function onShown(){ try { applyStableEditModalHeight(); modalEl.removeEventListener('shown.bs.modal', onShown); } catch(e){} });

                            // Recompute on window resize while modal is open
                            var onResize = function(){ try { if (document.body.classList && document.body.classList.contains('modal-open')) applyStableEditModalHeight(); } catch(e){} };
                            window.addEventListener('resize', onResize);

                            // Clean up when modal hides
                            modalEl.addEventListener('hidden.bs.modal', function(){ try {
                                // remove inline minHeight settings
                                try { var content = modalEl.querySelector('#editFormTabsContent'); if (content) content.style.minHeight = ''; } catch(e){}
                                try { modalEl.querySelectorAll('#editFormTabsContent .tab-pane').forEach(function(p){ try { p.style.minHeight = ''; } catch(e){} }); } catch(e){}
                                window.removeEventListener('resize', onResize);
                            } catch(e){} });
                        } catch(e){}

                        modal.show();
                    }
                } else if (json.error) {
                    try { Swal.fire({ toast:true, position:'top-end', icon:'error', title:json.error || getTranslation('error_fetching_data','Error al obtener datos'), showConfirmButton:false, timer:3500 }); } catch(e){}
                }
            }).catch(function(err){ try { Swal.fire({ toast:true, position:'top-end', icon:'error', title:getTranslation('error_fetching_data','Error al obtener datos'), showConfirmButton:false, timer:3500 }); } catch(e){} });
        } catch(e){}
    });

    // --- Tab validation and badge updates ---
    // helper: count required empty fields inside a container
    function countMissingRequiredIn(container) {
        try {
            var $c = $(container);
            if (!$c.length) return 0;
            var miss = 0;
            $c.find('input,textarea,select').each(function(){
                try {
                    var $el = $(this);
                    if ($el.prop('required')) {
                        var val = $el.val();
                        if (val === null || (typeof val === 'string' && $.trim(val) === '')) miss++;
                    }
                } catch(e){}
            });
            return miss;
        } catch(e){ return 0; }
    }

    function updateTabBadge(tabName, missingCount) {
        try {
            var sel = '.badge-tab[data-tab="' + tabName + '"]';
            var el = document.querySelector(sel);
            if (!el) return;
            if (missingCount && missingCount > 0) {
                el.textContent = missingCount;
                el.style.display = 'inline-block';
                el.classList.add('bg-danger','text-white');
                el.style.padding = '0 .4rem';
                el.style.borderRadius = '999px';
                el.style.fontSize = '0.75rem';
                el.style.lineHeight = '1.6';
                // also add underline class to the corresponding tab button
                try {
                    var tabBtn = document.querySelector('[data-bs-target="#' + tabName + '"]');
                    if (tabBtn) tabBtn.classList.add('tab-invalid');
                } catch(e){}
            } else {
                el.style.display = 'none';
                el.textContent = '';
                el.classList.remove('bg-danger','text-white');
                try {
                    var tabBtn2 = document.querySelector('[data-bs-target="#' + tabName + '"]');
                    if (tabBtn2) tabBtn2.classList.remove('tab-invalid');
                } catch(e){}
            }
        } catch(e){}
    }

    function validateAndUpdateForPane(paneId) {
        try {
            var missing = countMissingRequiredIn('#' + paneId);
            updateTabBadge(paneId, missing);
        } catch(e){}
    }

    // set codigo field readonly and placeholder (auto-generated on save)
    try { if (document.getElementById('codigo')) { var c = document.getElementById('codigo'); c.setAttribute('readonly','readonly'); c.setAttribute('placeholder','Generado al guardar'); } } catch(e){}

    // wire input/change events for new and edit forms
    $(document).on('input change', '#formEmpleado input, #formEmpleado select, #formEmpleado textarea', function(){
        try {
            var pane = $(this).closest('.tab-pane').attr('id');
            if (pane) validateAndUpdateForPane(pane);
            // clear invalid state when user modifies field
            try { $(this).removeClass('is-invalid'); var fb = $(this).next('.invalid-feedback'); if (fb && fb.length) fb.remove(); } catch(e){}
        } catch(e){}
    });
    $(document).on('input change', '#formEditar input, #formEditar select, #formEditar textarea', function(){
        try {
            var pane = $(this).closest('.tab-pane').attr('id');
            if (pane) validateAndUpdateForPane(pane);
            try { $(this).removeClass('is-invalid'); var fb = $(this).next('.invalid-feedback'); if (fb && fb.length) fb.remove(); } catch(e){}
        } catch(e){}
    });

    // re-validate when tabs are shown (handles programmatic changes)
    $(document).on('shown.bs.tab', 'button[data-bs-toggle="tab"]', function(e){
        try { var target = e.target && e.target.getAttribute('data-bs-target'); if (target) { var id = target.replace('#',''); validateAndUpdateForPane(id); } } catch(e){}
    });

    // Edit modal: Change photo overlay handler
    $(document).on('click', '.change-photo-btn', function(){
        try {
            var $card = $(this).closest('.edit-photo-card');
            var $input = $card.find('input[type="file"][name="foto"]');
            if ($input && $input.length) { $input.trigger('click'); }
        } catch(e){}
    });

    // Preview selected image in edit/new forms with client-side validation
    // Validates: MIME type image/*, file size <= 2MB, dimensions <= 2000x2000
    $(document).on('change', 'input[type="file"][name="foto"]', function(){
        try {
            var input = this;
            var file = input.files && input.files[0];
            if (!file) return;

            var MAX_SIZE = 2 * 1024 * 1024; // 2MB
            var MAX_DIM = 2000; // px

            // Basic MIME check
            if (!file.type || file.type.indexOf('image') === -1) {
                try { Swal.fire({ toast:true, position:'top-end', icon:'error', title: getTranslation('image_invalid','Archivo de imagen inválido'), showConfirmButton:false, timer:3500 }); } catch(e){}
                try { input.value = ''; } catch(e){}
                return;
            }

            // File size check
            if (file.size > MAX_SIZE) {
                try { Swal.fire({ toast:true, position:'top-end', icon:'error', title: getTranslation('image_too_large','El archivo excede el tamaño máximo de 2MB'), showConfirmButton:false, timer:4500 }); } catch(e){}
                try { input.value = ''; } catch(e){}
                return;
            }

            // Read dataURL to validate image dimensions before previewing
            var reader = new FileReader();
            reader.onload = function(ev){
                try {
                    var dataUrl = ev.target.result;
                    var img = new Image();
                    img.onload = function(){
                        try {
                            if (img.width > MAX_DIM || img.height > MAX_DIM) {
                                try { Swal.fire({ toast:true, position:'top-end', icon:'error', title: getTranslation('image_too_large_dimensions','La imagen supera las dimensiones máximas de 2000×2000 px'), showConfirmButton:false, timer:4500 }); } catch(e){}
                                try { input.value = ''; } catch(e){}
                                return;
                            }

                            // Determine preview target: prefer explicit ids, otherwise look inside parent card
                            var $preview = null;
                            try {
                                if (input.id === 'edit_foto') $preview = $('#edit_foto_preview');
                                else if (input.id === 'foto') $preview = $('#foto_preview');
                                if ((!$preview || !$preview.length) && input.closest) {
                                    var $card = $(input).closest('.edit-photo-card, .photo-card, .tab-card');
                                    if ($card && $card.length) $preview = $card.find('img').first();
                                }
                            } catch(ignore){}

                            // Apply preview if we found an img element
                            try { if ($preview && $preview.length) $preview.attr('src', dataUrl); } catch(e){}
                        } catch(e){}
                    };
                    img.onerror = function(){ try { Swal.fire({ toast:true, position:'top-end', icon:'error', title: getTranslation('image_cannot_process','No se puede procesar la imagen'), showConfirmButton:false, timer:3500 }); } catch(e){} try { input.value = ''; } catch(e){} };
                    img.src = dataUrl;
                } catch(e){}
            };
            reader.readAsDataURL(file);
        } catch(e){}
    });

    // initial pass: validate common pane ids
    try {
        ['new-generals','new-puesto','new-others','edit-generals','edit-puesto','edit-others'].forEach(function(id){ validateAndUpdateForPane(id); });
    } catch(e){}

    // Helper to show invalid feedback (Bootstrap style)
    function showInvalidMessage($el, msg) {
        try {
            if (!$el || !$el.length) return;
            $el.addClass('is-invalid');
            // remove any existing feedback immediately following element
            var next = $el.next('.invalid-feedback');
            if (next && next.length) {
                next.text(msg);
            } else {
                // create feedback element after the control
                var fb = $('<div class="invalid-feedback"></div>').text(msg);
                try { $el.after(fb); } catch(e){}
            }
        } catch(e){}
    }

    // Helper to find first missing required field within a form (exclude readonly/disabled)
    function findFirstMissingRequired($form) {
        try {
            var $fields = $form.find('input,textarea,select').filter(function(){
                try {
                    var el = $(this);
                    if (el.prop('required')) {
                        if (el.prop('readonly') || el.prop('disabled')) return false;
                        var val = el.val();
                        return (val === null || (typeof val === 'string' && $.trim(val) === ''));
                    }
                } catch(e){}
                return false;
            });
            return $fields.length ? $($fields.get(0)) : null;
        } catch(e) { return null; }
    }

    // Determine a simple localized required-field message
    function requiredMessage() {
        try {
            var lang = 'es';
            try { lang = (document.getElementById('langSelect') && document.getElementById('langSelect').value) || localStorage.getItem('lotificaciones_lang') || 'es'; } catch(e){}
            if (lang === 'en') return 'This field is required';
            return 'Este campo es obligatorio';
        } catch(e) { return 'Este campo es obligatorio'; }
    }

    // AJAX submit for create form: send FormData, show toast, reset form and refresh table
    $(document).on('submit', '#formEmpleado', function(e){
        e.preventDefault();
        try {
            var $form = $(this);
            var $first = findFirstMissingRequired($form);
            if ($first) {
                var msg = requiredMessage();
                showInvalidMessage($first, msg);
                try { $first.focus(); $first[0].scrollIntoView({behavior: 'smooth', block: 'center'}); } catch(e){}
                return false;
            }

            var formEl = $form.get(0);
            var fd = new FormData(formEl);
            var submitBtn = $form.find('button[type="submit"]');
            try { submitBtn.prop('disabled', true); } catch(e){}

            fetch('empleados/create', { method: 'POST', body: fd }).then(function(r){
                var status = r.status;
                return r.json().then(function(json){ return { status: status, body: json }; }).catch(function(){ return { status: status, body: null }; });
            }).then(function(resObj){
                var status = resObj.status; var resp = resObj.body;
                if (status === 200 && resp && resp.success) {
                    try { Swal.fire({ toast:true, position:'top-end', icon:'success', title: resp.message || getTranslation('employee_created','Empleado creado'), showConfirmButton:false, timer:2000 }); } catch(e){}
                    try { $form[0].reset(); } catch(e){}
                    try { reloadOrBuild(); } catch(e){}
                } else if (status === 422 && resp && resp.errors) {
                    // show field-level errors
                    try { Object.keys(resp.errors).forEach(function(k){ var $el = $('#'+k); if ($el && $el.length) showInvalidMessage($el, resp.errors[k]); }); } catch(e){}
                    try { Swal.fire({ toast:true, position:'top-end', icon:'error', title: resp.error || getTranslation('form_errors','Errores en el formulario'), showConfirmButton:false, timer:3500 }); } catch(e){}
                } else {
                    try { Swal.fire({ toast:true, position:'top-end', icon:'error', title: resp && resp.error ? resp.error : getTranslation('error_creating','Error al crear'), showConfirmButton:false, timer:3500 }); } catch(e){}
                }
            }).catch(function(err){
                console.error('Create request failed', err);
                try { Swal.fire({ toast:true, position:'top-end', icon:'error', title:getTranslation('network_error_creating','Error de red al crear'), showConfirmButton:false, timer:3500 }); } catch(e){}
            }).finally(function(){ try { submitBtn.prop('disabled', false); } catch(e){} });

            return false;
        } catch(e) { console.error(e); return false; }
    });

    // AJAX submit for edit form: send FormData, show toast, close modal and refresh table
    $(document).on('submit', '#formEditar', function(e){
        e.preventDefault();
        try {
            var $form = $(this);
            var $first = findFirstMissingRequired($form);
            if ($first) {
                var msg = requiredMessage();
                showInvalidMessage($first, msg);
                try { $first.focus(); $first[0].scrollIntoView({behavior: 'smooth', block: 'center'}); } catch(e){}
                return false;
            }

            // Prepare FormData (supports file upload)
            var formEl = $form.get(0);
            var fd = new FormData(formEl);

            // Show loading indicator on modal (simple overlay)
            var submitBtn = $form.find('button[type="submit"]');
            try { submitBtn.prop('disabled', true); } catch(e){}

            fetch('empleados/update', { method: 'POST', body: fd }).then(function(r){
                var status = r.status;
                return r.json().then(function(json){ return { status: status, body: json }; }).catch(function(){ return { status: status, body: null }; });
            }).then(function(resObj){
                var status = resObj.status; var resp = resObj.body;
                try {
                    if (status === 200 && resp && resp.success) {
                        try { Swal.fire({ toast:true, position:'top-end', icon:'success', title: resp.message || getTranslation('employee_updated','Empleado actualizado'), showConfirmButton:false, timer:2000 }); } catch(e){}
                        // close modal
                        try { var modalEl = document.getElementById('modalEditar'); if (modalEl) { var modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl); modal.hide(); } } catch(e){}
                        // refresh DataTable (prefer ajax reload)
                        try { reloadOrBuild(); } catch(e){}
                    } else if (status === 422 && resp && resp.errors) {
                        try { Object.keys(resp.errors).forEach(function(k){ var sel = '#edit_' + k; var $el = $(sel); if ($el && $el.length) showInvalidMessage($el, resp.errors[k]); else { var $f = $('#'+k); if ($f && $f.length) showInvalidMessage($f, resp.errors[k]); } }); } catch(e){}
                        try { Swal.fire({ toast:true, position:'top-end', icon:'error', title: resp.error || getTranslation('form_errors','Errores en el formulario'), showConfirmButton:false, timer:3500 }); } catch(e){}
                    } else {
                        try { Swal.fire({ toast:true, position:'top-end', icon:'error', title: resp && resp.error ? resp.error : getTranslation('error_updating','Error al actualizar'), showConfirmButton:false, timer:3500 }); } catch(e){}
                    }
                } catch(e) { console.error('Unexpected response', e); }
            }).catch(function(err){
                console.error('Update request failed', err);
                try { Swal.fire({ toast:true, position:'top-end', icon:'error', title:getTranslation('network_error_updating','Error de red al actualizar'), showConfirmButton:false, timer:3500 }); } catch(e){}
            }).finally(function(){ try { submitBtn.prop('disabled', false); } catch(e){} });

            return false;
        } catch(e) { console.error(e); return false; }
    });

});