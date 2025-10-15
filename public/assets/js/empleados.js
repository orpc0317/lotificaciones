// Final clean empleados.js - single IIFE

(function(){
    'use strict';
    
    // i18n translations cache
    var translations = {};
    
    // Load and apply translations
    function loadTranslations(lang){
        return fetch('assets/i18n/' + lang + '.json')
            .then(function(r){ return r.json(); })
            .then(function(data){
                translations = data;
                applyTranslations();
                return data;
            })
            .catch(function(err){
                console.error('Error loading translations:', err);
                return {};
            });
    }
    
    // Apply translations to elements with data-i18n attribute
    function applyTranslations(){
        try{
            document.querySelectorAll('[data-i18n]').forEach(function(el){
                var key = el.getAttribute('data-i18n');
                if(translations[key]){
                    // For input placeholders and select options
                    if(el.tagName === 'OPTION' || el.tagName === 'INPUT'){
                        if(el.hasAttribute('placeholder')){
                            el.placeholder = translations[key];
                        } else {
                            el.textContent = translations[key];
                        }
                    } else {
                        // For spans, buttons, labels, etc.
                        el.textContent = translations[key];
                    }
                }
            });
        }catch(e){
            console.error('Error applying translations:', e);
        }
    }
    
    // Configure passive events to suppress Chrome warnings
    try {
        var opts = Object.defineProperty({}, "passive", {
            get: function() {
                jQuery.event.special.touchstart = {
                    setup: function(_, ns, handle) {
                        if (!ns.includes("noPreventDefault")) {
                            this.addEventListener("touchstart", handle, { passive: true });
                        } else {
                            this.addEventListener("touchstart", handle);
                        }
                    }
                };
                jQuery.event.special.touchmove = {
                    setup: function(_, ns, handle) {
                        if (!ns.includes("noPreventDefault")) {
                            this.addEventListener("touchmove", handle, { passive: true });
                        } else {
                            this.addEventListener("touchmove", handle);
                        }
                    }
                };
                return true;
            }
        });
        window.addEventListener("test", null, opts);
    } catch (e) { /* Silent catch - passive events not supported */ }

    function exportHeader(data){
        var key = (data||'').toString().trim().toLowerCase().replace(/\s+/g,'_');
        var map = {
            id:'ID', codigo:'Código', nombres:'Nombres', apellidos:'Apellidos',
            fecha_de_nacimiento:'Fecha de Nacimiento', edad:'Edad', foto:'Foto',
            puesto_id:'Puesto ID', puesto_nombre:'Puesto', departamento_id:'Departamento ID',
            departamento_nombre:'Departamento', genero:'Género', comentarios:'Comentarios'
        };
        return map[key] || data;
    }

    var tabla = null;
    var buildingTable = false;

    function api(path){
        try{
            var baseEl = document.querySelector('base');
            var base = baseEl ? baseEl.getAttribute('href') : '/';
            if(!base) base = '/';
            return base.replace(/\/+$/,'') + '/' + path.replace(/^\/+/, '');
        }catch(e){ return '/' + path.replace(/^\/+/, ''); }
    }

    function fetchJsonWithIndexPhpFallback(path){
        var triedIndex = false;
        function tryFetch(p){
            return fetch(api(p)).then(function(r){
                var status = r.status;
                var contentType = r.headers && r.headers.get ? r.headers.get('content-type') : null;
                var url = r.url || api(p);
                return r.text().then(function(t){
                    try{
                        var parsed = JSON.parse(t);
                        try{ window.__lastEmpResponse = { url: url, status: status, contentType: contentType, parsed: parsed }; }catch(_){ }
                        return parsed;
                    }catch(e){
                        try{ window.__lastEmpResponse = { url: url, status: status, contentType: contentType, raw: t }; }catch(_){ }
                        console.error('Invalid JSON response for', url, { status: status, contentType: contentType, body: (t && t.slice) ? t.slice(0,2000) : t });
                        if(!triedIndex){ triedIndex = true; return tryFetch('index.php/' + p); }
                        var err = new Error('Invalid JSON response');
                        err.raw = t; err.status = status; err.contentType = contentType; err.url = url;
                        throw err;
                    }
                });
            });
        }
        return tryFetch(path);
    }

    function updateEmployeeCount(n){ try{ var el = document.getElementById('empleadosCount'); if(!el) return; el.textContent = Number(n||0); el.style.display = (n&&n>0)?'inline-block':'none'; }catch(e){}
    }

    function showLoading(){ try{ if(document.getElementById('tabla-loading')) return; var t = document.getElementById('tablaEmpleados'); if(!t) return; var container = t.closest ? t.closest('.card-body') : t.parentNode || document.body; try{ var st = window.getComputedStyle(container); if(st && st.position==='static') container.style.position='relative'; }catch(e){} var ov = document.createElement('div'); ov.id='tabla-loading'; ov.style.position='absolute'; ov.style.top='0'; ov.style.left='0'; ov.style.right='0'; ov.style.bottom='0'; ov.style.display='flex'; ov.style.alignItems='center'; ov.style.justifyContent='center'; ov.style.zIndex='9999'; ov.innerHTML='<div class="text-center"><div class="spinner-border text-primary" role="status" aria-hidden="true"></div><div class="mt-2 small">Cargando...</div></div>'; container.appendChild(ov); requestAnimationFrame(function(){ try{ ov.style.background='rgba(255,255,255,0.7)'; }catch(e){} }); }catch(e){} }
    function hideLoading(){ try{ var el = document.getElementById('tabla-loading'); if(!el) return; try{ el.parentNode.removeChild(el); }catch(e){} }catch(e){} }

    function attachTableHandlers(dt){ try{ dt.on('error.dt', function(e, settings, techNote, message){ try{ console.error('DataTable error.dt', { techNote: techNote, message: message, table: (settings && settings.nTable && settings.nTable.id) || null }); if(message && message.indexOf && message.indexOf('Requested unknown parameter')!==-1){ try{ dt.state.clear(); }catch(_){} try{ localStorage.removeItem('DataTables_' + settings.sInstance + '_' + location.pathname); }catch(_){} location.reload(); } }catch(_){} }); }catch(e){} }

    try{ if(window.jQuery && window.jQuery.fn && window.jQuery.fn.dataTable){ window.jQuery.fn.dataTable.ext.errMode = function(settings, helpPage, message){ try{ console.error('DataTables global error', { helpPage: helpPage, message: message, tableId: (settings && settings.nTable && settings.nTable.id) || null }); }catch(e){} }; } }catch(e){}

    function getCurrentLang(){ try{ return (document.getElementById('langSelect') && document.getElementById('langSelect').value) || localStorage.getItem('lotificaciones_lang') || 'es'; }catch(e){ return 'es'; } }

    function buildTable(lang){
        if(buildingTable) return; buildingTable = true; showLoading();
        
        // Destroy existing table if it exists
        try{ 
            if(tabla && $.fn.DataTable.isDataTable('#tablaEmpleados')){ 
                tabla.destroy(); 
                tabla = null; 
            } 
        }catch(e){ console.error('[buildTable] Error destroying table:', e); }
        
        // Clear and rebuild table structure
        var $table = $('#tablaEmpleados');
        $table.empty();
        $table.append('<thead></thead><tbody></tbody>');

        fetchJsonWithIndexPhpFallback('empleados/ajax?lang=' + encodeURIComponent(lang||'es'))
            .then(function(resp){
                if(!resp || !resp.columns){ 
                    console.error('[buildTable] Invalid response - no columns found');
                    buildingTable = false; hideLoading(); return; 
                }

                var cols = resp.columns.map(function(c){
                    var obj = { data: c.data, title: c.title };
                    if(c.className) obj.className = c.className;
                    if(typeof c.visible !== 'undefined') obj.visible = c.visible;
                    if(c.data==='thumbnail'){
                        obj.render = function(d){ return d?('<img src="'+d+'" class="thumb-sm" alt="thumb">') : ('<img src="uploads/placeholder.png" class="thumb-sm" alt="thumb">'); };
                        obj.className=(obj.className||'')+' no-export';
                    }
                    if(!c.data){
                        obj.render = function(data,type,row){ return '<button class="btn btn-sm btn-info ver-ficha" data-id="'+row.id+'"><i class="bi bi-eye"></i></button> <button class="btn btn-sm btn-warning editar" data-id="'+row.id+'"><i class="bi bi-pencil-square"></i></button> <button class="btn btn-sm btn-danger eliminar" data-id="'+row.id+'"><i class="bi bi-trash"></i></button>'; };
                        obj.className=(obj.className||'')+' no-export';
                    }
                    return obj;
                });

                var initial = (resp && Array.isArray(resp.data)) ? resp.data : [];
                try{
                    tabla = $('#tablaEmpleados').DataTable({
                        data: initial,
                        columns: cols,
                        dom: 'Bfrtip',
                        colReorder: true,
                        stateSave: false,
                        deferRender: true,
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
                                        action: function(e, dt, button, config){
                                            var data = dt.buttons.exportData({ 
                                                columns: ':visible:not(.no-export)',
                                                format: { header: exportHeader }
                                            });
                                            var txt = '';
                                            // Add headers
                                            txt += data.header.join('\t') + '\n';
                                            // Add rows
                                            for(var i = 0; i < data.body.length; i++){
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
                        language: { search: 'Buscar:', paginate: { previous: 'Anterior', next: 'Siguiente' }, emptyTable: 'No hay datos disponibles' }
                    });

                    tabla.reloadData = function(cb){
                        fetchJsonWithIndexPhpFallback('empleados/ajax?lang=' + encodeURIComponent(getCurrentLang()||'es')).then(function(rr){
                            var rows = (rr && Array.isArray(rr.data))? rr.data : [];
                            tabla.clear(); tabla.rows.add(rows);
                            try{ tabla.draw(false); }catch(e){}
                            updateEmployeeCount(rows.length);
                            if(cb) cb(null, rows);
                        }).catch(function(err){ if(err && err.raw) console.error('reloadData raw response:', err.raw); if(cb) cb(err); updateEmployeeCount(0); });
                    };

                    attachTableHandlers(tabla);
                    try{ window.__tabla = tabla; }catch(e){}
                    try{ updateEmployeeCount(initial.length); }catch(e){}
                }catch(e){ console.error('Error initializing DataTable', e); }
                hideLoading(); buildingTable = false;
            })
            .catch(function(err){ console.error('[buildTable] Failed to fetch columns/rows', err); if(err && err.raw) console.error('[buildTable] Raw response:', err.raw); hideLoading(); buildingTable = false; });
    }

    function reloadOrBuild(){ 
        try{ 
            // We're not using DataTables' ajax feature, so always rebuild the table
            buildTable(getCurrentLang()); 
        }catch(e){ 
            console.error('[reloadOrBuild] Error:', e);
        } 
    }

    // palette
    function applyPalette(name){ try{ var palettes = { blue:{'--primary-600':'#1e6fb3'}, teal:{'--primary-600':'#0c857a'}, violet:{'--primary-600':'#7c5aa8'} }; var p = palettes[name]||palettes.blue; Object.keys(p).forEach(function(k){ try{ document.documentElement.style.setProperty(k,p[k]); }catch(e){} }); try{ localStorage.setItem('lotificaciones_palette', name); }catch(e){} }catch(e){} }
    try{ document.querySelectorAll('.palette-swatch').forEach(function(s){ s.addEventListener('click', function(){ try{ applyPalette(s.getAttribute('data-palette')); }catch(e){} }); }); var sp = localStorage.getItem('lotificaciones_palette')||'blue'; applyPalette(sp); }catch(e){}

    // Change photo button click handler
    $(document).on('click', '.change-photo-btn', function(){ 
        try{ 
            var btn = $(this);
            var card = btn.closest('.edit-photo-card');
            var fileInput = card.find('input[type="file"]');
            if(fileInput.length > 0){
                fileInput.click();
            }
        }catch(e){ console.error('Error triggering file input', e); } 
    });

    // file preview with validation
    $(document).on('change','input[type="file"][name="foto"]', function(){ 
        try{ 
            var input=this; 
            var file = input.files && input.files[0]; 
            if(!file) return; 
            
            // Validate file type (MIME type check)
            var validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
            if(!file.type || validTypes.indexOf(file.type) === -1){ 
                try{ 
                    Swal.fire({ 
                        toast:true, 
                        position:'top-end', 
                        icon:'error', 
                        title:'Solo se permiten imágenes (JPG, PNG, GIF, WebP)', 
                        showConfirmButton:false, 
                        timer:3000 
                    }); 
                }catch(e){} 
                input.value=''; 
                return; 
            }
            
            // Validate file size (max 2MB)
            var maxSize = 2 * 1024 * 1024; // 2MB in bytes
            if(file.size > maxSize){
                try{ 
                    Swal.fire({ 
                        toast:true, 
                        position:'top-end', 
                        icon:'error', 
                        title:'La imagen no debe superar 2MB', 
                        showConfirmButton:false, 
                        timer:2500 
                    }); 
                }catch(e){} 
                input.value=''; 
                return; 
            }
            
            // Additional validation: Check file signature (magic numbers)
            var reader = new FileReader();
            reader.onloadend = function(e){
                try{
                    var arr = new Uint8Array(e.target.result).subarray(0, 4);
                    var header = '';
                    for(var i = 0; i < arr.length; i++){
                        header += arr[i].toString(16);
                    }
                    
                    // Check magic numbers for common image formats
                    var isValidImage = false;
                    // JPEG: FF D8 FF
                    if(header.indexOf('ffd8ff') === 0) isValidImage = true;
                    // PNG: 89 50 4E 47
                    else if(header.indexOf('89504e47') === 0) isValidImage = true;
                    // GIF: 47 49 46 38
                    else if(header.indexOf('47494638') === 0) isValidImage = true;
                    // WebP: 52 49 46 46 (RIFF)
                    else if(header.indexOf('52494646') === 0) isValidImage = true;
                    
                    if(!isValidImage){
                        try{
                            Swal.fire({ 
                                toast:true, 
                                position:'top-end', 
                                icon:'error', 
                                title:'El archivo no es una imagen válida', 
                                showConfirmButton:false, 
                                timer:3000 
                            });
                        }catch(err){}
                        input.value='';
                        return;
                    }
                    
                    // If valid, show preview
                    var readerPreview = new FileReader();
                    readerPreview.onload = function(ev){ 
                        try{ 
                            var dataUrl = ev.target.result; 
                            var img = new Image(); 
                            img.onload = function(){ 
                                try{ 
                                    var target = null; 
                                    if(input.id==='edit_foto') target = document.getElementById('edit_foto_preview'); 
                                    else if(input.id==='foto') target = document.getElementById('foto_preview'); 
                                    else if(input.id==='nuevo_foto') target = document.getElementById('nuevo_foto_preview'); 
                                    if(target) target.src = dataUrl; 
                                }catch(e){} 
                            };
                            img.onerror = function(){
                                try{
                                    Swal.fire({ 
                                        toast:true, 
                                        position:'top-end', 
                                        icon:'error', 
                                        title:'Error al cargar la imagen', 
                                        showConfirmButton:false, 
                                        timer:2500 
                                    });
                                }catch(err){}
                                input.value='';
                            };
                            img.src = dataUrl; 
                        }catch(e){} 
                    }; 
                    readerPreview.readAsDataURL(file);
                    
                }catch(err){
                    console.error('Error validating file signature:', err);
                    input.value='';
                }
            };
            reader.readAsArrayBuffer(file.slice(0, 4));
            
        }catch(e){ 
            console.error('Error in file validation:', e);
        } 
    });

    // forms
    $(document).on('submit', '#formNuevoEmpleado, #formEmpleado', function(ev){ ev.preventDefault(); try{ var $form = $(this); var fd = new FormData($form.get(0)); var btn = $form.find('button[type=submit]'); try{ btn.prop('disabled', true); }catch(e){} fetch(api('empleados/create'), { method:'POST', body: fd }).then(function(r){ return r.json().catch(function(){ return null; }); }).then(function(resp){ try{ if(resp && resp.success){ try{ Swal.fire({ toast:true, position:'top-end', icon:'success', title: resp.message||'Empleado creado', showConfirmButton:false, timer:1800 }); }catch(e){} try{ $form.get(0).reset(); }catch(e){} try{ if(tabla && tabla.reloadData){ tabla.reloadData(); } else { reloadOrBuild(); } }catch(e){} } else { try{ Swal.fire({ toast:true, position:'top-end', icon:'error', title: (resp && resp.error) || 'Error', showConfirmButton:false, timer:2500 }); }catch(e){} } }catch(e){} }).finally(function(){ try{ btn.prop('disabled', false); }catch(e){} }); }catch(e){} return false; });

    $(document).on('submit', '#formEditar', function(ev){ ev.preventDefault(); try{ var $form = $(this); var fd = new FormData($form.get(0)); var btn = $form.find('button[type=submit]'); try{ btn.prop('disabled', true); }catch(e){} fetch(api('empleados/update'), { method:'POST', body: fd }).then(function(r){ return r.json().catch(function(){ return null; }); }).then(function(resp){ try{ if(resp && resp.success){ try{ Swal.fire({ toast:true, position:'top-end', icon:'success', title: resp.message||'Empleado actualizado', showConfirmButton:false, timer:1800 }); }catch(e){} try{ var modalEl = document.getElementById('modalEditar'); if(modalEl){ try{ var m = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl); m.hide(); }catch(e){} } }catch(e){} try{ if(tabla && tabla.reloadData){ tabla.reloadData(); } else { reloadOrBuild(); } }catch(e){} } else { try{ Swal.fire({ toast:true, position:'top-end', icon:'error', title: (resp && resp.error) || 'Error', showConfirmButton:false, timer:2500 }); }catch(e){} } }catch(e){} }).finally(function(){ try{ btn.prop('disabled', false); }catch(e){} }); }catch(e){} return false; });

    // ficha
    $(document).on('click', '.ver-ficha', function(){ try{ var id = $(this).data('id'); fetch(api('empleados/get?id='+encodeURIComponent(id))).then(function(r){ return r.json(); }).then(function(json){ if(json && json.data){ var e = json.data; try{ document.getElementById('ficha_codigo').textContent = e.codigo||''; document.getElementById('ficha_nombres').textContent = e.nombres||''; document.getElementById('ficha_apellidos').textContent = e.apellidos||''; document.getElementById('ficha_fecha_nacimiento').textContent = e.fecha_nacimiento||''; document.getElementById('ficha_edad').textContent = e.edad||''; document.getElementById('ficha_genero').textContent = e.genero||''; document.getElementById('ficha_puesto').textContent = e.puesto_nombre||''; document.getElementById('ficha_departamento').textContent = e.departamento_nombre||''; document.getElementById('ficha_email').textContent = e.email||'-'; document.getElementById('ficha_telefono').textContent = e.telefono||'-'; document.getElementById('ficha_direccion').textContent = e.direccion||'-'; document.getElementById('ficha_ciudad').textContent = e.ciudad||'-'; document.getElementById('ficha_comentarios').textContent = e.comentarios||''; var img = document.getElementById('ficha_foto'); if(img){ if(e.thumbnail) img.src = e.thumbnail; else if(e.foto) img.src = 'uploads/'+e.foto; else img.src = 'uploads/placeholder.png'; } }catch(e){} var modalEl = document.getElementById('modalFicha'); if(modalEl){ try{ var m = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl); m.show(); }catch(e){} } } }).catch(function(err){ console.error('empleados/get failed', err); }); }catch(e){} });

    // editar
    $(document).on('click', '.editar', function(){ try{ var id = $(this).data('id'); fetch(api('empleados/get?id='+encodeURIComponent(id))).then(function(r){ return r.json(); }).then(function(json){ if(json && json.data){ var e = json.data; try{ document.getElementById('edit_id').value = e.id||''; document.getElementById('edit_codigo').value = e.codigo||''; document.getElementById('edit_nombres').value = e.nombres||''; document.getElementById('edit_apellidos').value = e.apellidos||''; document.getElementById('edit_fecha_nacimiento').value = e.fecha_nacimiento||''; document.getElementById('edit_genero').value = e.genero||''; document.getElementById('edit_puesto_id').value = e.puesto_id||''; document.getElementById('edit_departamento_id').value = e.departamento_id||''; document.getElementById('edit_email').value = e.email||''; document.getElementById('edit_telefono').value = e.telefono||''; document.getElementById('edit_direccion').value = e.direccion||''; document.getElementById('edit_ciudad').value = e.ciudad||''; document.getElementById('edit_comentarios').value = e.comentarios||''; document.getElementById('edit_foto_actual').value = e.foto||''; var img = document.getElementById('edit_foto_preview'); if(img){ if(e.thumbnail) img.src = e.thumbnail; else if(e.foto) img.src = 'uploads/'+e.foto; else img.src = 'uploads/placeholder.png'; } }catch(err){ console.error('Error populating edit form', err); } var modalEl = document.getElementById('modalEditar'); if(modalEl){ try{ var m = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl); m.show(); }catch(err){ console.error('Error showing modal', err); } } } }).catch(function(err){ console.error('empleados/get failed', err); }); }catch(e){ console.error('editar click handler error', e); } });

    // eliminar
    $(document).on('click', '.eliminar', function(){ try{ var id = $(this).data('id'); Swal.fire({ title: '¿Está seguro?', text: 'Esta acción no se puede deshacer', icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', cancelButtonColor: '#3085d6', confirmButtonText: 'Sí, eliminar', cancelButtonText: 'Cancelar' }).then(function(result){ if(result.isConfirmed){ fetch(api('empleados/delete'), { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: 'id=' + encodeURIComponent(id) }).then(function(r){ return r.json().catch(function(){ return null; }); }).then(function(resp){ if(resp && resp.success){ Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: resp.message || 'Empleado eliminado', showConfirmButton: false, timer: 1800 }); if(tabla && tabla.reloadData){ tabla.reloadData(); } else { reloadOrBuild(); } } else { Swal.fire({ toast: true, position: 'top-end', icon: 'error', title: (resp && resp.error) || 'Error al eliminar', showConfirmButton: false, timer: 2500 }); } }).catch(function(err){ console.error('delete failed', err); Swal.fire({ toast: true, position: 'top-end', icon: 'error', title: 'Error de conexión', showConfirmButton: false, timer: 2500 }); }); } }); }catch(e){ console.error('eliminar click handler error', e); } });

    // lang
    try{ 
        var langSel = document.getElementById('langSelect'); 
        if(langSel){ 
            langSel.addEventListener('change', function(){ 
                console.log('[langSelect] Language changed to:', langSel.value); 
                try{ localStorage.setItem('lotificaciones_lang', langSel.value); }catch(e){} 
                try{ loadTranslations(langSel.value); }catch(e){ console.error('[loadTranslations] failed:', e); }
                try{ reloadOrBuild(); }catch(e){ console.error('[reloadOrBuild] failed:', e); } 
            }); 
        } 
    }catch(e){ console.error('[langSelect] Setup failed:', e); }

    // Tab validation badges logic
    function updateTabBadges(formId){
        try{
            var form = document.getElementById(formId);
            if(!form) return;
            var tabPaneIds = [];
            if(formId === 'formNuevo' || formId === 'formNuevoEmpleado'){
                tabPaneIds = ['new-generals', 'new-personal', 'new-puesto', 'new-contact', 'new-address', 'new-others'];
            } else if(formId === 'formEditar'){
                tabPaneIds = ['edit-generals', 'edit-personal', 'edit-puesto', 'edit-contact', 'edit-address', 'edit-others'];
            }
            
            tabPaneIds.forEach(function(tabId){
                try{
                    var pane = document.getElementById(tabId);
                    if(!pane) return;
                    
                    // Find all required fields in this tab
                    var required = pane.querySelectorAll('input[required], textarea[required], select[required]');
                    var invalidCount = 0;
                    
                    required.forEach(function(field){
                        var val = (field.value || '').trim();
                        if(!val || val === '') invalidCount++;
                    });
                    
                    // Update badge
                    var badge = document.querySelector('.badge-tab[data-tab="' + tabId + '"]');
                    if(badge){
                        if(invalidCount > 0){
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
                    if(tabButton){
                        if(invalidCount > 0){
                            tabButton.classList.add('tab-invalid');
                        } else {
                            tabButton.classList.remove('tab-invalid');
                        }
                    }
                }catch(e){ console.error('Error updating tab badge for', tabId, e); }
            });
        }catch(e){ console.error('Error in updateTabBadges', e); }
    }

    // Attach input event listeners to all required fields in New and Edit modals
    function attachValidationListeners(){
        try{
            var forms = ['formNuevo', 'formNuevoEmpleado', 'formEditar'];
            forms.forEach(function(formId){
                var form = document.getElementById(formId);
                if(!form) return;
                
                var required = form.querySelectorAll('input[required], textarea[required], select[required]');
                required.forEach(function(field){
                    ['input', 'change', 'blur'].forEach(function(evt){
                        field.addEventListener(evt, function(){
                            updateTabBadges(formId);
                        });
                    });
                });
            });
        }catch(e){ console.error('Error attaching validation listeners', e); }
    }

    // Reset photo preview when New modal is about to be shown (before it opens)
    $(document).on('show.bs.modal', '#modalNuevoEmpleado', function(){
        try{
            // Reset the entire form
            var form = document.getElementById('formNuevoEmpleado');
            if(form) form.reset();
            
            // Reset photo preview to placeholder immediately
            var preview = document.getElementById('nuevo_foto_preview');
            if(preview) preview.src = 'uploads/placeholder.png';
            
            // Clear file input
            var fileInput = document.getElementById('nuevo_foto');
            if(fileInput) fileInput.value = '';
        }catch(e){}
    });

    // Call updateTabBadges when modals are shown
    $(document).on('shown.bs.modal', '#modalNuevoEmpleado', function(){
        updateTabBadges('formNuevoEmpleado');
    });
    
    $(document).on('shown.bs.modal', '#modalEditar', function(){
        updateTabBadges('formEditar');
    });

    // init
    try{ 
        var saved = localStorage.getItem('lotificaciones_lang') || (document.getElementById('langSelect') && document.getElementById('langSelect').value) || 'es'; 
        if(document.getElementById('langSelect')) document.getElementById('langSelect').value = saved; 
        loadTranslations(saved); // Load translations on page load
        buildTable(saved); 
    }catch(e){}

    // Attach validation listeners on page load
    attachValidationListeners();

    try{ window.__lastEmpResponse = window.__lastEmpResponse || null; }catch(e){}

})();