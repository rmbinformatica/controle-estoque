<?php
require_once("funcoes.php");
$breadcumb=array(array("Movimenta&ccedil;o de produto" => "movimentacao_produto.php", "Relat&oacute;rio de estoque" => "estoque_atual.php", "Estoque abaixo do m&iacute;nimo" => "estoque_minimo.php"),"Movimenta&ccedil;o de produto");
import_request_variables("f_");
require_once('head.php');
$titulo="Movimenta&ccedil;&atilde;o de produtos no estoque";
form_open($titulo);
?>
<div class="col-md-3 grid_box1">
<label class="control-label" for="selproduto">Selecione o produto</label>
<div class="input-group input-group1">
   <span class="input-group-addon"><i class="fas fa-shopping-bag"></i></span>
   <select class="form-control1" id="selproduto" name="id_produto" >
   <option value="">-Selecione-</option>
   <?php listar_querysql("SELECT id, descricao FROM produtos ORDER BY descricao asc",$f_selproduto) ?>
</div></div>
<div class="col-md-3 grid_box1">
<label class="control-label" for="codbarras">C&oacute;digo de barras</label>
<div class="input-group input-group1">
   <span class="input-group-addon"><i class="fas fa-barcode"></i></span><input type="text" class="form-control1" id="codbarras" name="codbarras" value="<?= $f_codbarras; ?>">
</div></div>
<div class="col-md-3 grid_box1">
<label class="control-label" for="inicio">Data Inicial</label>
<div class="input-group input-group1">
   <span class="input-group-addon"><i class="fas fa-calendar"></i></span><input type="date" class="form-control1" id="inicio" name="data_inicial" min="2020-01-01" max="2999-12-31" required>
</div></div>
<div class="col-md-3 grid_box1">
<label class="control-label" for="final">Data Final</label>
<div class="input-group input-group1">
   <span class="input-group-addon"><i class="fas fa-calendar"></i></span><input type="date" class="form-control1" id="final" name="data_final" min="2020-01-01" max="2999-12-31" required>
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
         if (valida_data($f_data_inicial)) {
            if (valida_data($f_data_final)) {
               $query_fim=" AND quando <=" . a($f_data_final);
            }
            else {
               $query_fim='';
            }
            echo "<p>C&oacute;digo: <b>" . $detalhes["id"] . "</b></p>";
            echo "<p>Descri&ccedil;&atilde;o: <b>" . $detalhes["descricao"] . "</p></p>";
            echo "<p>C&oacute;digo de barras: <b>" . $detalhes["codbarras"] . "</p></p>";
            $movto=slq_assoc("SELECT quando, descricao, quantidade FROM movimentacao WHERE produto=" . $detalhes["id"] . " AND quando >= " . a($f_data_inicial) . " $query_fim ORDER BY quando");
            $saldo=sql_simples("SELECT SUM(quantidade) FROm movimentacao WHERE produto=" . $detalhes["id"] . " AND quando < " . a($f_data_inicial));
            if (empty($saldo)) { $saldo=0; }
            if (count($movto)>0){
               echo '<h4 class="blank1">Movimenta&ccedil;&atilde;o de: ' . $detalhes["descricao"] . ' </h4>';
               echo '<table name="movto_produto" id="movto_produto" width="100%" border="1" class="table table-fhr">';
               echo '<thead><th>Data</th><th>Descri&ccedil;&atilde;o</th><th>Quantidade</th><th>Saldo Estoque</th></thead>';
               echo '<tr><td align="center">' . formata_data($f_data_inicial) . "</td><td>Saldo inicial</td><td>&nbsp;</td><td align=\"center\">$saldo</td></tr>";
               for($i=0;$i<count($movto);$i++){
                  $saldo+=$movto[$i]["quantidade"];
                  echo '<tr><td align="center">' . formata_datahora($movto[$i]["quando"])  . '</td><td>' . $movto[$i]["descricao"] . '</td><td align="center">' . $movto[$i]["quantidade"] . '</td><td align="center">' . $saldo . '</td></tr>';
               }
               echo '</tbody></table>';
               $dt_json=load_json("datatables-default_options");
               play_datatable("movto_produto",$dt_json);
            }
            else {
               msg_alerta("Nenhuma movimenta&ccedil;&atilde;o encontrada.");
            }

         }
         else {
            msg_alerta("Data inicial inv&aacute;lida");
         }
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