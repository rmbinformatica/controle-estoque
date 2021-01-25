<?php
require_once("funcoes.php");
$breadcumb=array(array("Relat&oacute;rio de estoque" => "estoque_atual.php", "Estoque abaixo do m&iacute;nimo" => "estoque_minimo.php", "Movimenta&ccedil;o de produto" => "movimentacao_produto.php"),"Relat&oacute;rio de estoque");
import_request_variables("f_");
$titulo="Estoque atual";
require_once('head.php');
$produtos=slq_assoc("SELECT id, descricao, codbarras, quantidade, minimo FROM estoque_atual");
if (count($produtos)>0) {
   echo '<h4 class="blank1">Estoque atual</h4>';
   echo '<table name="estoque_atual" id="estoque_atual" width="100%" border="1" class="table table-fhr">';
   echo '<thead><th>C&oacute;digo</th><th>Descri&ccedil;&atilde;o</th><th>C&oacute;digo de barras</th><th>Estoque atual</th><th>Estoque m&iacute;nimo</th></thead>';
   for($i=0;$i<count($produtos);$i++){
      echo '<tr><td align="center">' . $produtos[$i]["id"] . '</td><td>' . $produtos[$i]["descricao"] . '</td><td align="center">' . $produtos[$i]["codbarras"] . '</td><td align="center">' . $produtos[$i]["quantidade"] . '</td><td align="center">' . $produtos[$i]["minimo"] . '</td></tr>';
   }
   echo '</tbody></table>';
   $dt_json=load_json("datatables-default_options");
   play_datatable("estoque_atual",$dt_json);
}
else {
   msg_alerta("Nenhum produto encontrado.");
}
