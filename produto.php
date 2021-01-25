<?php
require_once("funcoes.php");
$breadcumb=array(array("Incluir" => "produto.php?acao=novo", "Listar" => "produto.php"),"Listar");
import_request_variables("f_");

if (!isset($f_acao)){
   require_once('head.php');
   $produtos=slq_assoc("SELECT id, descricao, codbarras, minimo FROM produtos");
   if (count($produtos)>0) {
      echo '<h4 class="blank1">Listagem do cadastro de produtos</h4>';
      echo '<table name="listar_produtos" id="listar_produtos" width="100%" border="1">';
      echo '<thead><th>C&oacute;digo</th><th>Descri&ccedil;&atilde;o</th><th>C&oacute;digo de barras</th><th>Estoque m&iacute;nimo</th><th>Opera&ccedil;&otilde;es</thead>';
      for($i=0;$i<count($produtos);$i++){
         $opcoes='<a href="produto.php?acao=editar&id=' . $produtos[$i]["id"] . '"><i class="fas fa-edit"></i></a>&nbsp;<a href="produto.php?acao=remover&id=' . $produtos[$i]["id"] . '"><i class="fas fa-trash-alt"></i></a>';
         echo '<tr><td align="center">' . $produtos[$i]["id"] . '</td><td>' . $produtos[$i]["descricao"] . '</td><td align="center">' . $produtos[$i]["codbarras"] . '</td><td align="center">' . $produtos[$i]["minimo"] . '</td><td align="center">' . $opcoes . '</td></tr>';
      }
      echo '</tbody></table>';
      $dt_json=load_json("datatables-default_options");
      play_datatable("listar_produtos",$dt_json);
   }
   else {
      msg_alerta("Nenhum produto encontrado");
   }
}
elseif ($f_acao == "novo"){
   $breadcumb[1]="Incluir";
   require_once('head.php');
   form_open("Produto","produto.php");
   form_line(array([5,3,2,2],
   array("nam" => "descricao","lab" => "Nome do produto","typ" => "text","pho" => "Nome do produto","max" => 100,"ico" => "fas fa-font","req" => true,"mas" => null),
   array("nam" => "codbarras","lab" => "Código de berras ","typ" => "text","pho" => "Cod.barras","max" => 45,"ico" => "fas fa-barcode","req" => false,"mas" => null),
   array("nam" => "minimo","lab" => "Estoque mínimo","typ" => "number", "ico" => "fas fa-battery-quarter","req" => true, "max" => 11,"mas" => "soNumeros"),
   array("nam" => "inicial","lab" => "Estoque inicial","typ" => "number", "ico" => "fas fa-chart-line","req" => true, "max" => 11,"mas" => "soNumeros")
   ));
   form_close("Cadastrar");
}
elseif ($f_acao == "cadastrar"){
   $produto["name"]=array("descricao","codbarras","minimo");
	$produto["value"]=array(a($f_descricao),a($f_codbarras),a($f_minimo));
	if ($prod_id=sql_insert(monta_insert("produtos",$produto))) {
		$breadcumb[1]="Incluir";
		require_once('head.php');
      msg_alerta("Produto <b>$f_descricao</b> cadastrado com sucesso!","success");
      if ($f_inicial > 0) {
         $movto["name"]=array("quando","produto","quantidade","descricao");
         $movto["value"]=array(a(NOW),$prod_id,$f_inicial,a("Cadastramento do produto."));
         sql_insert(monta_insert("movimentacao",$movto));
         msg_alerta("Estoque inicial de <b>$f_descricao</b> ($prod_id) definida para: $f_inicial unidades.","success");
      }
   }
}
elseif ($f_acao == "editar") {
   $breadcumb=array(array("Incluir" => "produto.php?acao=novo", "Listar" => "produto.php","Editar" => "#"),"Editar");
   require_once('head.php');
   if (valida_numero($f_id)) {
      $detalhes=slq_assoc("SELECT id, descricao, codbarras, minimo FROM produtos WHERE id=$f_id",true);
      $f_descricao=$detalhes["descricao"];
      $f_codbarras=$detalhes["codbarras"];
      $f_minimo=$detalhes["minimo"];
      form_open("Produto: $f_descricao ($f_id)","produto.php");
      makehidden("id",$f_id);
      form_line(array([6,3,3],
      array("nam" => "descricao","lab" => "Nome do produto","typ" => "text","pho" => "Nome do produto","max" => 100,"ico" => "fas fa-font","req" => true,"mas" => null),
      array("nam" => "codbarras","lab" => "Código de berras ","typ" => "text","pho" => "Cod.barras","max" => 45,"ico" => "fas fa-barcode","req" => false,"mas" => null),
      array("nam" => "minimo","lab" => "Estoque mínimo","typ" => "number", "ico" => "fas fa-battery-quarter","req" => true, "max" => 11,"mas" => "soNumeros")
      ));
      form_close("Alterar");
   }
   else {
      msg_alerta("Erro na requisi&ccedil;&atilde;o: identificador inv&aacute;lido: $f_id");
   }
}
elseif ($f_acao == "alterar"){
   $breadcumb=array(array("Incluir" => "produto.php?acao=novo", "Listar" => "produto.php","Editar" => "#"),"Editar");
   require_once('head.php');
   $atu_produto=array(
		"descricao" => a($f_descricao),
		"codbarras" => a($f_codbarras),
		"minimo" => a($f_minimo)
   );
   if (atualizar_banco("produtos",$atu_produto,"id=" . a($f_id))) {
      msg_alerta("Produto $f_descricao atualizado com sucesso!","success");
   }
   else {
      msg_alerta("Erro ao atualizar o cadastro do produto");
   }
}
elseif ($f_acao == "remover"){
   $breadcumb=array(array("Incluir" => "produto.php?acao=novo", "Listar" => "produto.php","Deletar" => "#"),"Deletar");
   require_once('head.php');
   $detalhes=slq_assoc("SELECT id, descricao, codbarras, minimo FROM produtos WHERE id=$f_id",true);
   $f_descricao=$detalhes["descricao"];
   $f_codbarras=$detalhes["codbarras"];
   $f_minimo=$detalhes["minimo"];
   form_open("Exclus&atilde;o de: $f_descricao ($f_id)","produto.php");
   echo "<p>C&oacute;digo: <b>$f_id</b></p><p>Descri&ccedil;&atildeo: <b>$f_descricao</b></p><p>C&oacute;digo de barras: <b>$f_codbarras</b></p><p>Toda a movimenta&ccedil;&atilde;o relacionada a esse produto ser&aacute; exclu&iacute;da. Deseja prosseguir?</p>";
   makehidden("id",$f_id);
   echo '<button class="btn btn-danger" type="submit" name="acao" value="confirmar_exclusao">Confirmar exclus&atilde;o</button>&nbsp;';
   echo '<a href="produto.php"><button class="btn btn-info">Voltar</button></a>';
}
elseif ($f_acao =="confirmar_exclusao") {
   $breadcumb=array(array("Incluir" => "produto.php?acao=novo", "Listar" => "produto.php","Deletar" => "#"),"Deletar");
   require_once('head.php');
   if (valida_numero($f_id)){
      $qtd=excluir_banco("produtos","id='$f_id'");
      if ($qtd>0){
         msg_alerta("Registro exclu&iacute;do!","success");
      }
      else {
         msg_alerta("Nenhum registro exclu&iacute;do para $f_id");

      }
   }
   else {
      msg_alerta("Erro ao localizar o produto para a exclus&atilde;o, identificador do produto $f_id inv&aacute;lido!");
   }
}
else {
   msg_alerta("Falha na requisi&ccedit;&atilde;o:");
}

require_once("footer-bar.php");
?>