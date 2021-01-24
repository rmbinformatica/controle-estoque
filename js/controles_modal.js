function exibirModal(nome,link){
  $.ajax({
    url: link,
    data: {},
    success: function(data){
      document.getElementById("modal"+nome+"Dialog").innerHTML=data;
    },
    error: function(xhr, statusText, err){
      alert("ERRO " + xhr.status+" - Impossível obter o conteúdo do servidor.\nTrace: js.controlesModal.exibirModal("+nome+","+link+")");
    }
  });
  $('#modal'+nome).modal('show');
}
function destruirModal(nome){
  $('#modal'+nome).modal('hide');
  document.getElementById("modal"+nome+"Dialog").innerHTML='';
}
