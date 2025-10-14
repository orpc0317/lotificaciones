// JS template for {{Name}} module
document.addEventListener('DOMContentLoaded', function(){
  console.log('Module {{Name}} loaded');
  var cols = {{datatable_columns_js}};
  function buildExportButtons(){
    // Use SheetJS exporter when available, fallback to csv/txt/print
    var sheetJsExists = (window.XLSX && XLSX.write);
    var exportCollection = {
      extend: 'collection',
      text: '<i class="bi bi-download"></i> Exportar',
      buttons: []
    };
    exportCollection.buttons.push({ extend: 'csv', text: '<i class="bi bi-file-earmark-csv"></i> CSV' });
    // Copy option removed by template default
    exportCollection.buttons.push({ extend: 'print', text: '<i class="bi bi-printer"></i> Imprimir' });
    if (sheetJsExists) {
      exportCollection.buttons.push({
        text: '<i class="bi bi-file-earmark-excel"></i> XLSX',
        action: function(e, dt, node, config){
          // build data and use SheetJS to create xlsx
          var data = [];
          var headers = dt.columns().header().toArray().map(function(h){ return h.innerText || h.textContent; });
          data.push(headers);
          dt.rows({ search: 'applied' }).data().each(function(r){ data.push(r); });
          var ws = XLSX.utils.aoa_to_sheet(data);
          var wb = XLSX.utils.book_new(); XLSX.utils.book_append_sheet(wb, ws, '{{name}}');
          var wbout = XLSX.write(wb, {bookType:'xlsx', type:'array'});
          var blob = new Blob([wbout], {type: 'application/octet-stream'});
          var fname = '{{name}}_' + new Date().toISOString().slice(0,19).replace(/[:T]/g,'-') + '.xlsx';
          var link = document.createElement('a');
          link.href = URL.createObjectURL(blob); link.download = fname; link.click();
        }
      });
    }
    return [exportCollection];
  }

  try {
    if (window.jQuery && $.fn.DataTable) {
      $('#tabla{{Name}}').DataTable({
        data: [],
        columns: cols,
        dom: 'Bfrtip',
        buttons: buildExportButtons()
      });
    }
  } catch(e){ console.error(e); }

  // file preview handling
  if ({{include_upload}}) {
    var fileInput = document.getElementById('{{name}}_file');
    var preview = document.getElementById('{{name}}_photo_preview');
    if (fileInput && preview) {
      fileInput.addEventListener('change', function(ev){
        var f = ev.target.files[0];
        if (!f) return;
        var reader = new FileReader();
        reader.onload = function(e){ preview.src = e.target.result; };
        reader.readAsDataURL(f);
      });
    }
  }

  var form = document.getElementById('{{name}}-form');
  if (form) {
    form.addEventListener('submit', function(ev){ ev.preventDefault();
      if ({{include_validation}}) {
        var invalid = false;
        form.querySelectorAll('input[required]').forEach(function(i){ if (!i.value) invalid = true; });
        if (invalid) { alert('Please fill required fields'); return; }
      }
      var fd = new FormData(form);
      if ({{include_upload}}) {
        // file input handling included - ensure server accepts multipart/form-data
      }
      // Default: show a toast and close modal (implement actual save logic)
      var bsModal = bootstrap.Modal.getInstance(document.getElementById('modal{{Name}}'));
      if (bsModal) bsModal.hide();
      alert('Implement save logic for {{Name}} (send fd via fetch/XHR)');
    });
  }
});
