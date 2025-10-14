// JS template for {{Name}} module
document.addEventListener('DOMContentLoaded', function(){
  console.log('Module {{Name}} loaded');
  var cols = {{datatable_columns_js}};
  try {
    if (window.jQuery && $.fn.DataTable) {
      $('#tabla{{Name}}').DataTable({
        data: [],
        columns: cols,
        dom: 'Bfrtip',
        buttons: ['csv', 'print']
      });
    }
  } catch(e){}

  var form = document.getElementById('{{name}}-form');
  if (form) {
    form.addEventListener('submit', function(ev){ ev.preventDefault();
      if ({{include_validation}}) {
        // Basic validation placeholder - replace with real rules
        var invalid = false;
        form.querySelectorAll('input[required]').forEach(function(i){ if (!i.value) invalid = true; });
        if (invalid) { alert('Please fill required fields'); return; }
      }
      // Collect form data
      var fd = new FormData(form);
      if ({{include_upload}}) {
        // file input handling included - ensure server accepts multipart/form-data
      }
      alert('Implement save logic for {{Name}} (send fd via fetch/XHR)');
    });
  }
});
