<?php
require_once("funcoes.php");
$breadcumb=array(array("Saida de estoque" => "saida.php", "Entrada no estoque" => "entrada.php"),"Saida de estoque");
import_request_variables("f_");
require_once('head.php');
$titulo="Sa&iacute;da de produtos do estoque";
form_open($titulo);
?>
<div class="col-md-4 grid_box1">
<label class="control-label" for="selproduto">Selecione o produto</label>
<div class="input-group input-group1">
   <span class="input-group-addon"><i class="fas fa-shopping-bag"></i></span>
   <select class="form-control1" id="selproduto" name="id_produto" >
   <option value="">-Selecione-</option>
   <?php listar_querysql("SELECT id, descricao FROM produtos ORDER BY descricao asc",$f_selproduto) ?>
</div></div>
<div class="col-md-4 grid_box1">
<label class="control-label" for="codbarras">C&oacute;digo de barras</label>
<div class="input-group input-group1">
   <span class="input-group-addon"><i class="fas fa-barcode"></i></span><input type="text" class="form-control1" id="codbarras" name="codbarras" value="<?= $f_codbarras; ?>">
</div></div>
<?php
form_close("Buscar");
if (isset($f_acao)){
   if ($f_acao == "buscar"){
      if (valida_numero($f_id_produto)){
         $detalhes=slq_assoc("SELECT id, descricao, codbarras FROM produtos WHERE id=$f_id_produto",true);
      }
      elseif (!empty($f_codbarras)){
         $detalhes=slq_assoc("SELECT id, descricao, codbarras FROm produtos WHERE codbarras=" . a($f_codbarras),true);
      }
      if ($detalhes["id"] > 0){
         form_open("Sa&iacute;da do produto: " . $detalhes["descricao"]);
         echo "<p>C&oacute;digo: <b>" . $detalhes["id"] . "</b></p>";
         echo "<p>Descri&ccedil;&atilde;o: <b>" . $detalhes["descricao"] . "</p></p>";
         echo "<p>C&oacute;digo de barras: <b>" . $detalhes["codbarras"] . "</p></p>";
         makehidden("id",$detalhes["id"]);
?>
<div class="col-md-4 grid_box1">
<label class="control-label" for="quantidade">Quantidade da sa&iacute;da</label>
<div class="input-group input-group1">
   <span class="input-group-addon"><i class="fas fa-arrow-down"></i></span><input type="number" class="form-control1" id="quantidade" name="quantidade" min="1" required>
</div></div>
<div class="col-md-4 grid_box1">
<label class="control-label" for="descricao">Observa&ccedil;&atilde;o</label>
<div class="input-group input-group1">
   <span class="input-group-addon"><i class="fas fa-comment-dots"></i></span><input type="text" class="form-control1" id="descricao" name="descricao">
</div></div>
<?php
      form_close("Saida");
      }
      else {
         msg_alerta("Nenhum produto encontrado.");
      }
   }
   elseif ($f_acao == "saida"){
      if (valida_numero($f_id)  && valida_numero($f_quantidade) && ($f_quantidade > 0)) {
         $detalhes=slq_assoc("SELECT id, descricao, codbarras FROM produtos WHERE id=$f_id",true);
         $movto["name"]=array("quando","produto","quantidade","descricao");
         $movto["value"]=array(a(NOW),$f_id,-$f_quantidade,a($f_descricao));
         if (sql_insert(monta_insert("movimentacao",$movto))) {
            msg_alerta("Sa&iacute;da de $f_quantidade unidades de " . $detalhes["descricao"] . " registrada com sucesso!","success");
         }
      }
      else {
         msg_alerta("N&atilde;o foi poss&iacute;vel movimentar o produto $f_id");
      }
   }
   else {
      msg_alerta("N&atilde;o foi poss&iacute;vel executar $f_acao");
   }

}

require_once("footer-bar.php");

?>