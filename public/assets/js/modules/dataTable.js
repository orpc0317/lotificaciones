/**
 * DataTable Module
 * 
 * Manages the employee DataTable including initialization,
 * configuration, loading states, and data reloading.
 * 
 * @module dataTable
 */

var DataTableModule = (function() {
    'use strict';
    
    // Private variables
    var tabla = null;
    var buildingTable = false;
    
    /**
     * Get API URL with proper base path
     * @param {string} path - API path
     * @returns {string} Full API URL
     */
    function api(path) {
        try {
            var baseEl = document.querySelector('base');
            var base = baseEl ? baseEl.getAttribute('href') : '/';
            if (!base) base = '/';
            return base.replace(/\/+$/, '') + '/' + path.replace(/^\/+/, '');
        } catch(e) {
            return '/' + path.replace(/^\/+/, '');
        }
    }
    
    /**
     * Fetch JSON with fallback to index.php prefix
     * @param {string} path - API path
     * @returns {Promise} Promise resolving to JSON data
     */
    function fetchJsonWithIndexPhpFallback(path) {
        var triedIndex = false;
        function tryFetch(p) {
            return fetch(api(p)).then(function(r) {
                var status = r.status;
                var contentType = r.headers && r.headers.get ? r.headers.get('content-type') : null;
                var url = r.url || api(p);
                return r.text().then(function(t) {
                    try {
                        var parsed = JSON.parse(t);
                        try { window.__lastEmpResponse = { url: url, status: status, contentType: contentType, parsed: parsed }; } catch(_) {}
                        return parsed;
                    } catch(e) {
                        try { window.__lastEmpResponse = { url: url, status: status, contentType: contentType, raw: t }; } catch(_) {}
                        ErrorHandler.log('FetchJSON', e, { url: url, status: status, contentType: contentType });
                        if (!triedIndex) {
                            triedIndex = true;
                            return tryFetch('index.php/' + p);
                        }
                        var err = new Error('Invalid JSON response');
                        err.raw = t; err.status = status; err.contentType = contentType; err.url = url;
                        throw err;
                    }
                });
            });
        }
        return tryFetch(path);
    }
    
    /**
     * Update employee count badge
     * @param {number} n - Number of employees
     */
    function updateEmployeeCount(n) {
        try {
            var el = document.getElementById('empleadosCount');
            if (!el) return;
            el.textContent = Number(n || 0);
            el.style.display = (n && n > 0) ? 'inline-block' : 'none';
        } catch(e) {}
    }
    
    /**
     * Show loading overlay on table
     */
    function showLoading() {
        try {
            if (document.getElementById('tabla-loading')) return;
            var t = document.getElementById('tablaEmpleados');
            if (!t) return;
            var container = t.closest ? t.closest('.card-body') : t.parentNode || document.body;
            try {
                var st = window.getComputedStyle(container);
                if (st && st.position === 'static') container.style.position = 'relative';
            } catch(e) {}
            var ov = document.createElement('div');
            ov.id = 'tabla-loading';
            ov.style.position = 'absolute';
            ov.style.top = '0';
            ov.style.left = '0';
            ov.style.right = '0';
            ov.style.bottom = '0';
            ov.style.display = 'flex';
            ov.style.alignItems = 'center';
            ov.style.justifyContent = 'center';
            ov.style.zIndex = Z_INDEX_LOADING_OVERLAY;
            ov.innerHTML = '<div class="text-center"><div class="spinner-border text-primary" role="status" aria-hidden="true"></div><div class="mt-2 small">Cargando...</div></div>';
            container.appendChild(ov);
            requestAnimationFrame(function() {
                try { ov.style.background = 'rgba(255,255,255,0.7)'; } catch(e) {}
            });
        } catch(e) {}
    }
    
    /**
     * Hide loading overlay
     */
    function hideLoading() {
        try {
            var el = document.getElementById('tabla-loading');
            if (!el) return;
            try { el.parentNode.removeChild(el); } catch(e) {}
        } catch(e) {}
    }
    
    /**
     * Attach error handlers to DataTable
     * @param {Object} dt - DataTable instance
     */
    function attachTableHandlers(dt) {
        try {
            dt.on('error.dt', function(e, settings, techNote, message) {
                try {
                    ErrorHandler.log('DataTable.Error', message, { techNote: techNote, table: (settings && settings.nTable && settings.nTable.id) || null });
                    if (message && message.indexOf && message.indexOf('Requested unknown parameter') !== -1) {
                        try { dt.state.clear(); } catch(_) {}
                        try { localStorage.removeItem('DataTables_' + settings.sInstance + '_' + location.pathname); } catch(_) {}
                        location.reload();
                    }
                } catch(_) {}
            });
        } catch(e) {}
    }
    
    /**
     * Export header formatter for DataTables
     * @param {string} data - Column header text
     * @returns {string} Formatted header
     */
    function exportHeader(data) {
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
    
    /**
     * Get current language from UI or localStorage
     * @returns {string} Language code (es, en)
     */
    function getCurrentLang() {
        try {
            return (document.getElementById('langSelect') && document.getElementById('langSelect').value) || 
                   localStorage.getItem('lotificaciones_lang') || 
                   DEFAULT_LANGUAGE;
        } catch(e) {
            return DEFAULT_LANGUAGE;
        }
    }
    
    /**
     * Build and initialize DataTable
     * @param {string} lang - Language code
     * @param {Object} translations - Translation object
     */
    function buildTable(lang, translations) {
        if (buildingTable) return;
        buildingTable = true;
        showLoading();
        
        // Destroy existing table if it exists
        try {
            if (tabla && $.fn.DataTable.isDataTable('#tablaEmpleados')) {
                tabla.destroy();
                tabla = null;
            }
        } catch(e) {
            ErrorHandler.log('BuildTable.Destroy', e);
        }
        
        // Clear and rebuild table structure
        var $table = $('#tablaEmpleados');
        $table.empty();
        $table.append('<thead></thead><tbody></tbody>');

        fetchJsonWithIndexPhpFallback('empleados/ajax?lang=' + encodeURIComponent(lang || DEFAULT_LANGUAGE))
            .then(function(resp) {
                if (!resp || !resp.columns) {
                    ErrorHandler.log('BuildTable', 'Invalid response - no columns found');
                    buildingTable = false;
                    hideLoading();
                    return;
                }

                var cols = resp.columns.map(function(c) {
                    var obj = { data: c.data, title: c.title };
                    if (c.className) obj.className = c.className;
                    if (typeof c.visible !== 'undefined') obj.visible = c.visible;
                    if (c.data === 'thumbnail') {
                        obj.render = function(d) {
                            return d ? ('<img src="' + d + '" class="thumb-sm" alt="thumb">') : ('<img src="uploads/placeholder.png" class="thumb-sm" alt="thumb">');
                        };
                        obj.className = (obj.className || '') + ' no-export';
                    }
                    if (!c.data) {
                        // Only View icon - uses theme color
                        obj.render = function(data, type, row) {
                            return '<a href="#" class="ver-ficha action-icon fs-5" data-id="' + row.id + '" title="Ver detalles"><i class="bi bi-eye-fill"></i></a>';
                        };
                        obj.className = (obj.className || '') + ' no-export text-center';
                    }
                    return obj;
                });

                try {
                    tabla = $('#tablaEmpleados').DataTable({
                        processing: true,
                        serverSide: true,
                        ajax: {
                            url: api('empleados/ajax?lang=' + encodeURIComponent(lang || DEFAULT_LANGUAGE)),
                            type: 'POST',
                            data: function(d) {
                                // Add filter parameters to the request
                                d.filter_id = $('#filter_id').val();
                                d.filter_nombres = $('#filter_nombres').val();
                                d.filter_apellidos = $('#filter_apellidos').val();
                                d.filter_genero = $('#filter_genero').val();
                                d.filter_departamento = $('#filter_departamento').val();
                                d.filter_puesto = $('#filter_puesto').val();
                                d.filter_edad_min = $('#filter_edad_min').val();
                                d.filter_edad_max = $('#filter_edad_max').val();
                                d.filter_fecha_nacimiento_desde = $('#filter_fecha_nacimiento_desde').val();
                                d.filter_fecha_nacimiento_hasta = $('#filter_fecha_nacimiento_hasta').val();
                                return d;
                            },
                            error: function(xhr, error, thrown) {
                                ErrorHandler.log('DataTable.Ajax', error, { thrown: thrown });
                            }
                        },
                        columns: cols,
                        dom: 'Blfrtip',
                        colReorder: true,
                        stateSave: false,
                        deferRender: true,
                        pageLength: DATATABLE_PAGE_LENGTH,
                        lengthMenu: [DATATABLE_PAGE_LENGTH_OPTIONS, DATATABLE_PAGE_LENGTH_OPTIONS],
                        buttons: [
                            {
                                extend: 'colvis',
                                text: 'Columnas',
                                columns: ':not(.dt-no-colvis)'
                            },
                            {
                                extend: 'collection',
                                text: translations['export'] || 'Exportar',
                                buttons: [
                                    {
                                        extend: 'excelHtml5',
                                        text: 'XLSX',
                                        exportOptions: { columns: ':visible:not(.no-export)', format: { header: exportHeader } }
                                    },
                                    {
                                        extend: 'csvHtml5',
                                        text: 'CSV',
                                        exportOptions: { columns: ':visible:not(.no-export)', format: { header: exportHeader } }
                                    },
                                    {
                                        text: 'TXT',
                                        action: function(e, dt, button, config) {
                                            var data = dt.buttons.exportData({
                                                columns: ':visible:not(.no-export)',
                                                format: { header: exportHeader }
                                            });
                                            var txt = '';
                                            // Add headers
                                            txt += data.header.join('\t') + '\n';
                                            // Add rows
                                            for (var i = 0; i < data.body.length; i++) {
                                                txt += data.body[i].join('\t') + '\n';
                                            }
                                            // Create download
                                            var blob = new Blob([txt], { type: 'text/plain;charset=utf-8' });
                                            var link = document.createElement('a');
                                            link.href = URL.createObjectURL(blob);
                                            link.download = 'empleados.txt';
                                            link.click();
                                        }
                                    }
                                ]
                            }
                        ],
                        language: {
                            search: 'Buscar:',
                            paginate: { previous: 'Anterior', next: 'Siguiente' },
                            emptyTable: 'No hay datos disponibles'
                        }
                    });

                    // Reload data method for server-side processing
                    tabla.reloadData = function(cb) {
                        try {
                            tabla.ajax.reload(function() {
                                if (cb) cb(null);
                            }, false); // false = don't reset paging
                        } catch(e) {
                            ErrorHandler.log('DataTable.Reload', e);
                            if (cb) cb(e);
                        }
                    };

                    // Add loading indicators for DataTable processing
                    tabla.on('processing.dt', function(e, settings, processing) {
                        try {
                            if (processing) {
                                showLoading();
                            } else {
                                hideLoading();
                            }
                        } catch(err) {
                            ErrorHandler.log('DataTable.Processing', err);
                        }
                    });

                    // Add visual feedback for DataTable draw
                    tabla.on('draw.dt', function() {
                        try {
                            // Update employee count after table draws
                            var info = tabla.page.info();
                            if (info) {
                                updateEmployeeCount(info.recordsTotal);
                            }
                        } catch(err) {
                            ErrorHandler.log('DataTable.Draw', err);
                        }
                    });

                    attachTableHandlers(tabla);
                    try { window.__tabla = tabla; } catch(e) {}
                } catch(e) {
                    ErrorHandler.log('BuildTable.Initialize', e);
                }
                hideLoading();
                buildingTable = false;
            })
            .catch(function(err) {
                ErrorHandler.log('BuildTable.Fetch', err, { raw: err.raw });
                hideLoading();
                buildingTable = false;
            });
    }
    
    /**
     * Reload table data or rebuild if table doesn't exist
     */
    function reloadOrBuild() {
        try {
            if (tabla && tabla.reloadData) {
                tabla.reloadData();
            } else {
                buildTable(getCurrentLang(), I18nModule.getAll());
            }
        } catch(e) {
            ErrorHandler.log('ReloadOrBuild', e);
        }
    }
    
    /**
     * Get current DataTable instance
     * @returns {Object} DataTable instance
     */
    function getTable() {
        return tabla;
    }
    
    // Configure DataTables global error mode
    try {
        if (window.jQuery && window.jQuery.fn && window.jQuery.fn.dataTable) {
            window.jQuery.fn.dataTable.ext.errMode = function(settings, helpPage, message) {
                try {
                    ErrorHandler.log('DataTable.GlobalError', message, { helpPage: helpPage, tableId: (settings && settings.nTable && settings.nTable.id) || null });
                } catch(e) {}
            };
        }
    } catch(e) {}
    
    // Public API
    return {
        api: api,
        buildTable: buildTable,
        reloadOrBuild: reloadOrBuild,
        getTable: getTable,
        getCurrentLang: getCurrentLang
    };
})();
