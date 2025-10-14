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
    form.addEventListener('submit', function(ev){ ev.preventDefault(); alert('Implement save logic for {{Name}}'); });
  }
});
