$('#selecionarTodos').click(function(e) {
  $('input:checkbox').not(this).prop('checked', this.checked);
});