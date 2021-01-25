<?php
/* Arquivo de funcoes gerais de uso no sistema */
@setlocale(LC_TIME, 'pt_PT.ISO_8859-1');

/* Arquivo de credenciais para conexão ao banco de dados */
require_once("banco.php");

/* Importação automática dos posts em variáveis php com prefixo f_ */
import_request_variables("f_");

if (file_exists("banco.php")) {
	/* Detecção de versão */
	$pathGitFile='../.git/refs/heads/master';
	if (file_exists($pathGitFile)){
		define('VERSAO',substr(file_get_contents($pathGitFile),0,7));
	}
	else {
		define('VERSAO','Desconhecida');
	}
	unset($pathGitFile);
}

function import_request_variables($prfix){
	/*
	Fumção implementa uma rotina que fazia parte do php anterior. Que faz com que os dados de post e get
	sejam automaticamente atribuídos em variáveis globais.
	*/
    foreach($_GET as $k => $v){
		$v_name = $prfix.$k;
		global $$v_name;
		${$prfix.$k} = filter_input(INPUT_GET,$k);
    }
    foreach($_POST as $k => $v){
		$v_name = $prfix.$k;
		global $$v_name;
		if (is_array($v)) {
			${$prfix.$k} = $v;
		}
		else {
			${$prfix.$k} = filter_input(INPUT_POST,$k);
		}
    }
}

function importar_contexto($ctx){

	for ($i=0;$i<count($ctx);$i++) {
		$item=$ctx[$i];
		foreach($item as $k => $v){
			global $$k;
			${$k} = $v;
	  }
	}
}

function import_data_fields($assoc_array = null){

	if (!is_null($assoc_array)) {
		$prfix="f_";
		foreach($assoc_array as $k => $v){
			$v_name = $prfix.$k;
			global $$v_name;
			${$prfix.$k} = $v;
		}
	}
}

function slq_assoc($query,$unico=false,$basefixa=false){

	if ($basefixa) {
		$cn_sqlasso=new mysqli(SERVIDOR, USUARIO, SENHA, BFIXA);
	}
	else {
		$cn_sqlasso=new mysqli(SERVIDOR, USUARIO, SENHA, BANCO);
	}
	$cn_sqlasso->set_charset("utf8");
	$qf_sqlasso=mysqli_query($cn_sqlasso,$query);
	if (mysqli_errno($cn_sqlasso) > 0) {
		require_once('head.php');
		msg_alerta("<b>Ocorreu um erro de acesso ao banco de dados.</b><br>" . $query .  "<b>" .  mysqli_errno($cn_sqlasso) . '</b>:' . mysqli_error($cn_sqlasso),'danger');
		die;
	}
	else {
		$r=0;
		while ($resultado=mysqli_fetch_assoc($qf_sqlasso)){
			$retorno[$r]=$resultado;
			$r++;
		}
		mysqli_close($cn_sqlasso);
	}
	if ($unico) {
		$retorno=$retorno[0];
	}
	return $retorno;
}

function checkboxes_relacao($input_nome,$tb_base,$tb_selecao=NULL) {

   /* 
   Monta um conjunto de checkboxes com itens selecionados conforme a relacao.
   $input_nome = Nome do input no form
   $tb_base = array base, podendo ser o reultado de um slq_assoc, formato $tb_base[id] e $tb_base[nome]
   $tb_selecao = array dos itens selecionados, contendo $tb_selecao[id] e $tb_selecao[sele]
   */
	$tb_base=array_column($tb_base,'nome','id');
	if (!is_null($tb_selecao)) { $tb_selecao=array_column($tb_selecao,'sele','id'); }
   foreach ($tb_base as $k => $v) {
      echo '<input type="checkbox" name="' . $input_nome . '" value="' . $k . '"';
      if ($tb_selecao[$k] == 1) { echo ' checked'; }
      echo "> $v<br>\n";
   }
}

function colunas_tabela($tipo,$colunas){

	/*
	Monta as linhas do datatable <thead> e <tfoot>
	Função auxiliar para uso com o make_table
	*/
	echo "<" . $tipo . "><tr>";
	for ($n=1;$n<count($colunas);$n++){ //For iniciando de 1 pressupondo que o primeiro campo sempre sera o id e nao sera exibido.
		echo "<th>" . $colunas[$n] . "</th>";
	}
	echo "<th>Operações</th></tr>";
	echo "</" . $tipo . ">\n";
}

function make_table($pmt){

	/*
	Monta o datatable
	Parâmetros:
	$pmt['consulta']="SELECT * FROM t_ponto_mergulho"; //Consulta SQL desejada
	$pmt['colunas'][0]='identificador'; //Nome da primeira coluna (não será exibido)
	$pmt['colunas'][1]='nomenclatura'; //Nome da coluna seguinte...
	$pmt['colunas'][2]='habilitado'; //Nome da coluna seguinte...
	*/
	echo '<table id="example" class="table table-striped table-bordered stripe hover row-border order-column" style="width:100%">' . "\n";
	colunas_tabela('thead',$pmt['colunas']);
	$cnx_consulta=new mysqli(SERVIDOR, USUARIO, SENHA, BANCO);
	mysqli_set_charset($cnx_consulta,'utf8');
	$dados_consulta=mysqli_query($cnx_consulta, $pmt['consulta']);
	while ($resultado=mysqli_fetch_row($dados_consulta)){
		echo "<tr>";
		$num_colunas=count($resultado);
		for ($i=1;$i<=$num_colunas;$i++){ //For iniciando de 1 pressupondo que o primeiro campo sempre sera o id e nao sera exibido.
			echo "<td>";
			if ($i < $num_colunas){
				echo $resultado[$i];
			}
			else {
				/*?>
				<form method="POST"><button alt="Excluir"><i class="fas fa-trash" ></i></button></form>
				<?php*/
				//echo '<a href="?id=' . $resultado[0] . '&action=remover" title="Excluir registro"><i class="fas fa-trash"></i></a>';

				if($resultado[2]==1){ $a ='checked';}else{$a='';}
				echo "<label class='switch'>  <input type='checkbox' ".$a." onchange=\"mudaStatus('".$resultado[0]."',' t_ponto_mergulho')\">  <span class=\"slider round\"></span></label>";
			}
			echo "</td>";
		}
		echo "</tr>\n";
	}
	echo "</tbody>";
	mysqli_close($cnx_consulta);
	colunas_tabela('tfoot',$pmt['colunas']);
	include "js_table.php";
}

function a($str) {
	/*
	Esta função trata um dado antes de enviar para o banco de dados mysql.
	Caso o conteúdo esteja vazio ele atribui automaticamente NULL, caso seja
	texto o conteúdo é colocado entre aspas.
	*/
   if ($str == "") { $str="NULL"; }
   else { $str = "'" . preg_replace("/'/","",$str) . "'"; }
   return $str;
}

function valida_data($data,$br=0){

	/*
	Valida uma data fornecida verificando se é uma data válida.
	Suporta datas no formato SQL ou brasileiro
	Retorna datas em formato SQL ou brasileiro (parâmetro $br=1)
	*/
	$data=str_replace("/", "-", $data); //Substitui barras por hifem nos separadores das datas
	if (preg_match("([12]\d{3}-(0[1-9]|1[0-2])-(0[1-9]|[12]\d|3[01]))", $data)) {
		$partes=explode("-",$data); //Separa datas em formato sql
		$ano=$partes[0];$mes=$partes[1];$dia=$partes[2];
	}
	elseif (preg_match("((0[1-9]|[12]\d|3[01])-(0[1-9]|1[0-2])-[12]\d{3})", $data)) {
		$partes=explode("-",$data); //Separa datas em formato brasileiro
		$dia=$partes[0];$mes=$partes[1];$ano=$partes[2];
	}
	else { return false; }
	if (checkdate($mes, $dia, $ano)) {
		if ($br) { return "$dia/$mes/$ano"; }
		else { return "$ano-$mes-$dia"; } //Retorna a data em formato sql
	}
	else { return false; }
}

function sql_data($campo) {

	/*
	Altera o campo de uma query SQL para retornar uma data em formato brasileiro
	*/
	return "DATE_FORMAT(" . $campo . ",'%d/%m/%Y')";
}

function formata_data($data) {

	/*
	Altera uma data SQL para formato brasileiro
	*/
	if ($data == "0000-00-00") { return "A DEFINIR"; }
	else { return date("d/m/Y",strtotime($data)); }
}

function formata_datahora($data) {

	/*
	Altera uma data SQL para formato brasileiro
	*/
	if ($data == "0000-00-00") { return "A DEFINIR"; }
	else { return date("d/m/Y H:i:s",strtotime($data)); }
}

function sql_simples($query){

	/*
	Executa uma consulta SQL que possua apenas um valor de retorno.
	*/
	$cn_sqlsim=new mysqli(SERVIDOR, USUARIO, SENHA, BANCO);
	$cn_sqlsim->set_charset("utf8");
	$ex_sqlsim=mysqli_query($cn_sqlsim,$query);
	$retorno=mysqli_fetch_row($ex_sqlsim);
	if (mysqli_errno($cn_sqlsim) > 0) {
		msg_alerta("Ocorreu um erro no acesso ao banco de dados.<br>$query<br>".mysqli_error($cn_sqlsim));
	}
	$cn_sqlsim->close();
	return $retorno[0];
}

function formata_numero($valor){
	return number_format($valor,2,'.','');
}

function listar_querysql($query_sql,$selecao = NULL) {

	/*
	Listagem genérica de dados de uma query SQL em opções de um form select.
	O primeiro campo da query deverá ser obrigatoriamente o value do campo;
	O segundo campo da query é o valor que será exibido na tela do form.
	Parâmetros:
	$query_sql = Consulta a ser realizada no banco;
	$selecao = Ítem a ser selecionado (selected)
	*/
	$cn_listagem=new mysqli(SERVIDOR, USUARIO, SENHA, BANCO);
	$cn_listagem->set_charset("utf8");
	$qf_listagem=mysqli_query($cn_listagem,$query_sql);
	if (mysqli_errno($cn_listagem) > 0) {
		require_once('head.php');
		msg_alerta("<b>Ocorreu um erro de acesso ao banco de dados.</b><br>" . $query_sql .  "<b>" .  mysqli_errno($cn_listagem) . '</b>:' . mysqli_error($cn_listagem),'danger');
		die;
	}
	else {
		while ($resultado=mysqli_fetch_row($qf_listagem)) {
			$valor=$resultado[0];
			if ($valor == $selecao) { $sel = ' selected'; }
			else { $sel=''; }
			echo '<option value="' . $valor . '"' . $sel . '>' . $resultado[1] . '</option>' . "\n";
		}
	}
	echo "</select>";
	mysqli_close($cn_listagem);
}

function listar_generico($tabela,$selecao = NULL,$ordenacao = NULL) {

	/*
	Listagem genérica de dados de uma tabela qualquer como opções de um form select.
	Parâmetros:
	$tabela - Nome da tabela no banco
	$selecao - Ítem a ser selecionado
	$ordenacao - Campo do banco a ser usado para ordenar os resultados.
	*/
	if (!is_null($ordenacao)) { $order=" ORDER BY $ordenacao"; }
	else { $order=''; }
	// Tratamento das tabelas b_ e v_ onde nao ha coluna ativo
	$prefixo_tabela=substr($tabela,0,2);
	if (($prefixo_tabela == "b_") || ($prefixo_tabela == "v_")) { $filtro_where=''; }
	// Fim do tratamento
	else { $filtro_where=' WHERE ativo=1 '; }
	//Listagem genericas de tabela contendo id e nome
	listar_querysql("SELECT * FROM $tabela $filtro_where $order",$selecao);
}


function listar_sql_assoc($query_sql,$selecao = NULL) {

	/*
	Listagem genérica de dados de uma query SQL em opções de um form select.
	Os nomes das colunas de resultado serão os atributos das options
	Necessário um atributo chamado "label" para a opção que será exibida ao usuário.
	Parâmetros:
	$query_sql = Consulta a ser realizada no banco;
	$selecao = Ítem a ser selecionado (selected)
	*/
	$cn_listagem=new mysqli(SERVIDOR, USUARIO, SENHA, BANCO);
	$cn_listagem->set_charset("utf8");
	$qf_listagem=mysqli_query($cn_listagem,$query_sql);
	if (mysqli_errno($cn_listagem) > 0) {
		require_once('head.php');
		msg_alerta("<b>Ocorreu um erro de acesso ao banco de dados.</b><br>" . $query_sql .  "<b>" .  mysqli_errno($cn_listagem) . '</b>:' . mysqli_error($cn_listagem),'danger');
		die;
	}
	else {
		while ($resultado=mysqli_fetch_assoc($qf_listagem)) {
      if (!is_array($selecao)) {
        if (($resultado["label"] == $selecao) || (($resultado["value"] == $selecao))) { $sel = ' selected'; }
        else { $sel=''; }
      }
			else {
        if (array_search($resultado["value"], $selecao) === FALSE) { $sel=''; }
        else { $sel=' selected'; }
      }
			echo '<option';
			foreach ($resultado as $rkey => $rval) {
				echo ' ' . $rkey . '="' . $rval . '"';
			}
			echo $sel . '>' . $resultado["label"] . '</option>' . "\n";
		}
	}
	echo "</select>";
	mysqli_close($cn_listagem);
}


function listar_uf($uf_sel) {

	/*
	Listagem genérica das UFs brasileiras como opções de um form select.
	*/
	$uf=array("AC","AL","AM","AP","BA","CE","DF","ES","GO","MA","MG","MS","MT","PA","PB","PE","PI","PR","RJ","RN","RO","RR","RS","SC","SE","SP","TO","EX");
	for ($i=0; $i<28; $i++) {
		echo '<option value="' . $uf[$i] . '" ';
		if ($uf[$i] == $uf_sel) { echo "selected"; }
		echo '>' . $uf[$i] . '</option>' . "\n";
	}
	echo '</select>';
}

function makehidden($nome,$valor) {

	/*
	Gera um campo input hidden no formulário
	*/
	echo '<input type="hidden" id="' . $nome . '" name="' . $nome . '" value="' . $valor . '">';
}

function extenso($valor=0, $maiusculas=false) {

	/*
	Função retorna um valor numérico por extenso, para emissão de recibos.
	Parâmetros: $valor - Valor numérico; maiusculas: 0 - minusculas; 1 - iniciais maiúsculas; 2 - palavras maiúsculas
	*/
    // verifica se tem virgula decimal
    if (strpos($valor, ",") > 0) {
        // retira o ponto de milhar, se tiver
        $valor = str_replace(".", "", $valor);

        // troca a virgula decimal por ponto decimal
        $valor = str_replace(",", ".", $valor);
    }
    $singular = array("centavo", "real", "mil", "milh&atilde;o", "bilh&atilde;o", "trilh&atilde;o", "quatrilh&atilde;o");
    $plural = array("centavos", "reais", "mil", "milh&otilde;es", "bilh&otilde;es", "trilh&otilde;es",
        "quatrilh&otilde;es");

    $c = array("", "cem", "duzentos", "trezentos", "quatrocentos",
        "quinhentos", "seiscentos", "setecentos", "oitocentos", "novecentos");
    $d = array("", "dez", "vinte", "trinta", "quarenta", "cinquenta",
        "sessenta", "setenta", "oitenta", "noventa");
    $d10 = array("dez", "onze", "doze", "treze", "quatorze", "quinze",
        "dezesseis", "dezesete", "dezoito", "dezenove");
    $u = array("", "um", "dois", "tr&ecirc;s", "quatro", "cinco", "seis",
        "sete", "oito", "nove");

    $z = 0;

    $valor = number_format($valor, 2, ".", ".");
    $inteiro = explode(".", $valor);
    $cont = count($inteiro);
    for ($i = 0; $i < $cont; $i++)
        for ($ii = strlen($inteiro[$i]); $ii < 3; $ii++)
            $inteiro[$i] = "0" . $inteiro[$i];

    $fim = $cont - ($inteiro[$cont - 1] > 0 ? 1 : 2);
    for ($i = 0; $i < $cont; $i++) {
        $valor = $inteiro[$i];
        $rc = (($valor > 100) && ($valor < 200)) ? "cento" : $c[$valor[0]];
        $rd = ($valor[1] < 2) ? "" : $d[$valor[1]];
        $ru = ($valor > 0) ? (($valor[1] == 1) ? $d10[$valor[2]] : $u[$valor[2]]) : "";

        $r = $rc . (($rc && ($rd || $ru)) ? " e " : "") . $rd . (($rd &&
                $ru) ? " e " : "") . $ru;
        $t = $cont - 1 - $i;
        $r .= $r ? " " . ($valor > 1 ? $plural[$t] : $singular[$t]) : "";
        if ($valor == "000")
            $z++; elseif ($z > 0)
            $z--;
        if (($t == 1) && ($z > 0) && ($inteiro[0] > 0))
            $r .= ( ($z > 1) ? " de " : "") . $plural[$t];
        if ($r)
            $rt = $rt . ((($i > 0) && ($i <= $fim) &&
                    ($inteiro[0] > 0) && ($z < 1)) ? ( ($i < $fim) ? ", " : " e ") : "") . $r;
    }

    if (!$maiusculas) { return($rt ? $rt : "zero"); }
    elseif ($maiusculas == "2") { return (strtoupper($rt) ? strtoupper($rt) : "Zero"); }
    else { return (ucwords($rt) ? ucwords($rt) : "Zero"); }
}
function mes($mes) {

	/*
	Converte um mês de numérico para extenso.
	*/
    $meses=array("Janeiro","Fevereiro","Mar&ccedil;o","Abril","Maio","Junho","Julho","Agosto","Setembro","Outubro","Novembro","Dezembro");
    return $meses[$mes-1];
}

function ip_atual() {

	/*
	Retorna o IP atual do usuário acessando a página.
	*/
	return $_SERVER["REMOTE_ADDR"];
}

function msg_alerta($mensagem,$tipo = 'danger'){

	/*
	Exibe uma mensagem de alerta na pagina
	Parâmetros:
	$mensagem = mensagem a ser exibida ao usuário
	$tipo = success info warning danger
	*/
//	echo '<div class="grid_3 grid_5">
   echo'<div class="but_list"><div class="alert alert-' . $tipo . '" role="alert">';
	switch($tipo){
		case "success":
			echo '<i class="fas fa-check-circle"></i>';
			break;
		case "warning":
			echo '<i class="fas fa-exclamation-triangle"></i>';
			break;
		case "danger":
			echo '<i class="fas fa-exclamation-circle"></i>';
			break;
		case "info":
			echo '<i class="fas fa-info-circle"></i>';
			break;
	}
	echo ' ' . $mensagem . '</div></div>';
//   echo '</div>';
}


function form_line($dd){

	/*
	Esta função permite montar um grupo de inputs em um formulário, agrupados em uma linha lado a lado.
	O primeiro parâmetro é um array contendo o espaçamento de cada campo, no grid. O somatório desses
	espaçamentos deve ser 12. E o número de itens do array deve ser igual ao número de arrays do segundo
	parâmetro.
	No segundo parâmetro deve ser estabelecido um array para cada campo de formulário, com os seguintes parâmetros:
	nam - name do field
	lab - label superior
	typ - type do field (text, number, lista, lista_preco, yn)
	pho - placeholder
	max - tamanho máximo
	ico - icone do fontawesome, caso não inicie com fa será exibido o texto.
	req - true/falso indicativo se o campo é obrigatório
	mas - mascara javascript do js/mascara
	tbl - (usar com lista) tabela onde devem ser listadas as opções do <select>
  id - id do campo
  */
	import_request_variables("f_");
	for($i=0;$i<count($dd);$i++){
		if($i==0){ //Verificação se o somatório dos mds é igual a 12
			$soma=0;
			for($n=0;$n<count($dd[0]);$n++) { $soma+=$dd[0][$n]; }
			if ($soma == 12) {
				//echo '<div class="form-group"><div class="row">'; // Retirado para reducao de espaço entre as linhas
				echo "\n"; }
			else { msg_alerta("Somatório de itens do form field deve ser 12, calculado:" . $soma);die;}
			if ((count($dd)-1) != count($dd[0])) { msg_alerta("Numero de inputs (" . (count($dd)-1) . ") é diferente da quantidade de posições " . count($dd[0])); die; }
		}
		else {
			$nome_campo=$dd[$i]["nam"];
			$nome_var="f_" . $nome_campo;
			$required_color='';
			if ($dd[$i]["req"]) { $required_color=' has-success'; }
			if ($dd[$i]["rol"]) { $required_color=' alert-info'; }
			echo '<div class="col-md-' . $dd[0][$i-1] . ' grid_box1">
	<label class="control-label" for="input_' . $nome_campo . '">' . $dd[$i]["lab"] . '</label>
	<div class="input-group input-group1' . $required_color . '">';
			if (isset($dd[$i]["ico"])) {
				echo '<span class="input-group-addon' . $required_color . '">';
				if (substr($dd[$i]["ico"],0,2) == "fa") { echo '<i class="' . $dd[$i]["ico"] . '"></i>'; }
				else { echo $dd[$i]["ico"]; }
				echo '</span>';
			}
      if (!isset($dd[$i]["id"])){
        $id_campo='input_' . $nome_campo;
      }
      else {
        $id_campo=$dd[$i]["id"];
      }
			switch($dd[$i]["typ"]){
				case "check":
					if (isset($GLOBALS[$nome_var]) && ($GLOBALS[$nome_var] == 1)) { $checkbox=' checked=""'; }
					echo '<div class="checkbox-inline1"><label><input type="checkbox" id="' . $id_campo . '" name="' . $nome_campo . '"' . $checkbox . '>' . $dd[$i]["pho"] . '</label></div>';
					break;
				case "yn":
					if (isset($GLOBALS[$nome_var]) && ($GLOBALS[$nome_var] == 1)) { $yescheck=' checked=""'; $nocheck=''; }
					else { $nocheck=' checked=""'; $yescheck=''; }
					echo '<div class="radio-inline"><label><input type="radio" id="' . $id_campo . '" name="' . $nome_campo . '"' . $yescheck . ' value="1"> Sim</label></div>';
					echo '<div class="radio-inline"><label><input type="radio" id="' . $id_campo . '" name="' . $nome_campo . '"' . $nocheck . ' value="0"> Não</label></div>';
					break;
				case "lista":
					echo '<select class="form-control1" id="' . $id_campo . '" name="' . $nome_campo . '" ';
					if (isset($dd[$i]["max"])) { echo 'size="' . $dd[$i]["max"] . '"'; }
					if ($dd[$i]["req"]) { echo 'required '; }
					echo '><option value="">-Selecione-</option>';
					if ($nome_campo == 'uf') { listar_uf($GLOBALS[$nome_var]); } // Listagem das UF
					elseif ($nome_campo == 'folga') { lista_folgas($GLOBALS[$nome_var]); } // Listagem das UF	
					elseif ($nome_campo == 'extra') { listar_tpserv($GLOBALS[$nome_var]);}
					else { listar_generico($dd[$i]["tbl"],$GLOBALS[$nome_var]); } // Listagem de alguma tabela
					break;
				case "lista_preco":
					echo '<select class="form-control1" id="' . $id_campo . '" name="' . $nome_campo . '" onchange="' . $dd[$i]["jsf"] . '(this.options[this.selectedIndex].getAttribute(\'preco\'),' . $dd[$i]["uid"] .  ' );" ';
					if (isset($dd[$i]["max"])) { echo 'size="' . $dd[$i]["max"] . '"'; }
					if ($dd[$i]["req"]) { echo 'required '; }
					echo '><option value="">-Selecione-</option>';
					listar_id_nome_preco($dd[$i]["tbl"],$GLOBALS[$nome_var]);  // Listagem de alguma tabela com preço
					break;
/*				case "number":
					echo '<input type="number" class="form-control1" id="' . $id_campo . '" name="' . $nome_campo . '" ';
					if (isset($dd[$i]["pho"])) { echo 'placeholder="' . $dd[$i]["pho"] . '" '; }
					if ($dd[$i]["max"] > 0) { echo 'max="' . $dd[$i]["max"] . '" '; }
					if ((isset($dd[$i]["mas"])) && ($dd[$i]["mas"] != null) && ($dd[$i]["mas"] != "")) { echo 'onkeypress="mascara(this,' . $dd[$i]["mas"] . ')" '; }
					if (isset($GLOBALS[$nome_var])) { echo 'value="' . $GLOBALS[$nome_var] . '" '; }
					if ($dd[$i]["req"]) { echo 'required '; }
					echo '></div></div>';
					break;*/
				default:
					if ($dd[$i]["rol"]) { $addclass=" alert-info"; }
					else { $addclass=""; }
					echo '<input type="' . $dd[$i]["typ"] . '" class="form-control1' . $addclass . '" id="' . $id_campo . '" name="' . $nome_campo . '" ';
					if (isset($dd[$i]["pho"])) { echo 'placeholder="' . $dd[$i]["pho"] . '" '; }
					if ($dd[$i]["max"] > 0) { echo 'maxlength="' . $dd[$i]["max"] . '" '; }
					if ((isset($dd[$i]["mas"])) && ($dd[$i]["mas"] != null) && ($dd[$i]["mas"] != "")) { echo 'onkeypress="mascara(this,' . $dd[$i]["mas"] . ')" '; }
					if (isset($GLOBALS[$nome_var])) { echo 'value="' . $GLOBALS[$nome_var] . '" '; }
					if ($dd[$i]["req"]) { echo 'required '; }
					if ($dd[$i]["rol"]) { echo 'readonly="" '; }
					echo '>';
					break;
			}
			echo '</div></div>';
		}
	}
	echo '<div class="clearfix"></div>'; // </div></div> retirado para redução de espaço entre as linhas
}

function form_open($nome=NULL,$redirecionar = NULL){

	/*
	Abre um form com action para o próprio script.
	Coloca o cabeçalho $nome no topo da página
	*/
	$scpt_nom=basename($_SERVER["SCRIPT_FILENAME"], '.php');
	echo '<form name="' . $scpt_nom . '" id="form_' . $scpt_nom . '" method="POST" action="' . $redirecionar . '" autocomplete="off" class="form-horizontal">';
	if (!is_null($nome)){
		echo '<h3 class="blank1">' . $nome . '</h3>';
//    echo '<div class="panel-body panel-body-inputin">';
	}
}

function form_close($botao_nome,$print_link=null){

	$id=preg_replace("/ /","",ucwords($botao_nome));
	echo '<div class="panel-footer">
	<div class="row">
		<div class="col-sm-8 col-sm-offset-2">
			<button id="sbm_' . $id . '" class="btn-success btn" name="acao" type="submit" value="' . strtolower($botao_nome) . '">' . ucfirst($botao_nome) . '</button>
			<button class="btn-default btn" type="reset" value="Reset" onclick="window.location.reload()">Limpar</button>';
	if (!is_null($print_link)) {
		if (!is_array($print_link)) {
			$print_link=a($print_link) . ",'impressao'";
			echo '<button type="button" class="btn-default btn" onClick="window.open(' . $print_link . ')"><i class="fas fa-print"></i></button>';
		}
		else {
			foreach ($print_link as $nomeImpressao => $linkImpressao) {
				$linkImpressao=a($linkImpressao) . ",'impressao'";
				echo '<button type="button" class="btn-default btn" onClick="window.open(' . $linkImpressao . ')"><i class="fas fa-print"></i> ' . ucwords($nomeImpressao) . '</button>';
			}
		}
	}
	echo '</div>
	</div>
</div> <!-- /panel-footer --> </form>';
}

function load_post_fields(){

	/*
	Carrega os campos postados no formulário em um array de retorno.
	$array["name"] - contendo os nomes dos campos
	$array["value"] - contendo os valores dos campos
	Ignora automaticamente o post acao referente aos botoes.
	*/
	$i=0;
	foreach ($_POST as $k => $v) {
		if ($k != "acao") {
			$retorno["name"][$i]=$k;
			$retorno["value"][$i]=a($v);
			$i++;
		}
	}
	return $retorno;
}

function monta_insert($tabela,$it = NULL){

	/*
	Gera a query SQL de inserção no banco de dados, parâmetros:
	$tabela = Tabela a inserir os dados;
	$it = Array contendo names e values dos campos. Caso seja omitido por padrão será obtido
	o valor com a função load_post_fields.
	*/
	if (is_null($it)) { $it=load_post_fields(); }
	$names=implode(",", $it["name"]);
	$values=implode(",", $it["value"]);
	$sql_retorno="INSERT INTO $tabela ($names) VALUES ($values)";
	return $sql_retorno;
}

function insert_update($tabela,$it){

	/*
	Gera a query SQL de inserção no banco de dados e update em duplicate key, parâmetros:
	$tabela = Tabela a inserir os dados;
	$it = Array contendo names e values dos campos.
	*/
	$names=implode(",", $it["name"]);
	$values=implode(",", $it["value"]);
	$sql_retorno="INSERT INTO $tabela ($names) VALUES ($values) ON DUPLICATE KEY UPDATE ";
	for($i=0;$i<count($it["name"]);$i++){
		$pos_sql[$i]=$it["name"][$i] . "=" . $it["value"][$i];
	}
	$sql_retorno.=implode(",",$pos_sql);
	return $sql_retorno;
}

function sql_add_funcao($busca_campo,$funcao_add,$fa = NULL){

	/*
	Função para tratar os casos onde seja necessário adicionar alguma função do mysql ao montar um insert
	$busca_campo = Nome do input do form que deve ser procurado.
	$funcao_add = Nome da função mysql para adicionar ao value, ex.: md5, unhex.
	$fa = array contendo names e values de todos os campos. Caso seja omitido por padrão será obtido
	o valor com a função load_post_fields.
	*/
	if (is_null($fa)) { $fa=load_post_fields(); }
	$mudar_pos=array_search($busca_campo, $fa['name']);
	$fa['value'][$mudar_pos]=$funcao_add . '(' . $fa['value'][$mudar_pos] . ')';
	return $fa;
}

function sql_insert($query_sql,$mostrarErros=true){

	/*
	Realiza a insercao no banco de dados da $query_sql passada por parâmetro.
	Trata os erros:
	- 1062 Duplicidades
	- Demais erros exibe o erro do banco.
	*/
	$cnx_inserir=new mysqli(SERVIDOR, USUARIO, SENHA, BANCO);
	mysqli_set_charset($cnx_inserir,'utf8');
	$dados_consulta=mysqli_query($cnx_inserir, $query_sql);
	$cod_erro=mysqli_errno($cnx_inserir);
	$msg_erro=mysqli_error($cnx_inserir);
	$insert_id=mysqli_insert_id($cnx_inserir);
	mysqli_close($cnx_inserir);
	if ($cod_erro > 0) {
		if ($mostrarErros) {
			exibeErroInsert($cod_erro,$msg_erro,$query_sql);
			msg_alerta($query_sql);
		}
		return false;
	}
	else{
		if ($insert_id) { return $insert_id; }
		else { return true; }
	}
}

function exibeErroInsert($cod_erro,$msg_erro,$query_sql){

	require_once('head.php');
	switch($cod_erro){
		case 1048:
			$key=$msg_erro;
			$key=substr($key,8,strpos($key,"'",8)-8);
			msg_alerta("Não é possível inserir os dados pois <b>$key</b> é obrigatório.");
			break;
		case 1062:
			$key=$msg_erro;
			$key=substr($key,17,strpos($key,"'",18)-17);
			msg_alerta("Não é possível inserir dados <b>duplicados</b>. O banco de dados recusou a inclusão pois <b>$key</b> já existe. $query_sql");
			break;
		case 1364:
			$key=$msg_erro;
			$key=substr($key,7,strpos($key,"'",8)-7);
			msg_alerta("Não é possível inserir os dados pois <b>$key</b> é obrigatório.");
			break;
		case 1452:
			$key=$msg_erro;
			$ps_ini=strpos($key,"FOREIGN KEY")+14;
			$key=substr($key,$ps_ini,strpos($key,"`",$ps_ini+1)-$ps_ini);
			msg_alerta("Não é possível inserir dados, chave estrangeira <b>$key</b> inválida.");
			break;
		default:
			msg_alerta("<b>Ocorreu um erro de acesso ao banco de dados.</b><br>" . $query_sql .  "<b>" .  $cod_erro . '</b>:' . $msg_erro,'danger');
			break;
	}
}


function page_breadcumb($arr_itm,$active){

	/*
	Monta o breadcumb superior de navegação.
	*/
	//echo '<div class="grid_3 grid_5">';
	echo '<div class="but_list"><ol class="breadcrumb">';
	foreach ($arr_itm as $nome => $link) {
		if ($nome == $active) {
			echo '<li class="active">' . $nome . '</li>';
		}
		else {
			echo '<li><a href="' . $link . '"';
			if ($nome == '<i class="fas fa-print"></i>') {
				echo ' target="impressao"';
			}
			echo '>' . $nome . '</a></li>';
		}
	}
	echo '</ol></div>';
}

function load_json($tabela){

	/*
	Carrega um arquivo json em uma variável.
	*/
	$json_path='json/' . $tabela . '.json';
	if (!file_exists($json_path)) {
		require_once('head.php');
		msg_alerta("Parametrização json para <b>$tabela</b> inexistente.");
	}
	else {
		$contents = file_get_contents($json_path);
		//$contents = utf8_encode($contents);
		$results = json_decode($contents,true);
		return $results;
	}
}

function play_datatable($nome_tabela,$json_array){

	/*
	Monta uma datatable, parâmetros
	$nome_tabela - Identificador do datatable.
	$json_array - array de opções do datatable.
	*/
	echo '<script type="text/javascript">' . "\n";
	echo "$(document).ready(function() {
    $('#$nome_tabela').DataTable( " . json_encode($json_array) . ");
} );
</script>" . "\n";
	echo '<script type="text/javascript" src="dataTable/datatables.min.js"></script>' . "\n";
}


function valida_numero($numero) {
	return preg_match("/^[0-9]+$/",$numero);
}


function monta_update($variable,$b=null){ 

	// VERSAO INICIAIL , Recebe array, espera id ou cpf como WHERE e o nome da tebela;
	$sql = 'UPDATE t_'.$variable['tabela'].' SET ';
	unset($variable['tabela']);
	foreach($variable as $key => $value){
		if($key!='id' && $key!='cpf' && $key!='acao' )
			if (!isset($b[$key])){
		$sql .= " ".$key."='".$value."',";
		}else{
		$sql .=" ".$key."= ".$b[$key]."('".$value."'),";
		}
	}
	$sql = substr($sql, 0, -1);
	if(isset($variable['id']))
	$sql.= ' WHERE id='.$variable['id'];
	else
	$sql.= ' WHERE cpf='.$variable['cpf'];
	return $sql;
}

function play_sql($sql){

	$cnx_sql=new mysqli(SERVIDOR, USUARIO, SENHA, BANCO);
	mysqli_set_charset($cnx_sql,'utf8');
	$dados=mysqli_query($cnx_sql, $sql);
   mysqli_close($cnx_sql);
	return $dados;
}

function delete_sql($sql) {

	$cnx_sql=new mysqli(SERVIDOR, USUARIO, SENHA, BANCO);
	mysqli_set_charset($cnx_sql,'utf8');
	$dados=mysqli_query($cnx_sql, $sql);
	$excluidos=mysqli_affected_rows($cnx_sql);
   mysqli_close($cnx_sql);
	return $excluidos;
}

function excluir_banco($tabela,$filtro,$limitador=false){

	$cn_del=new mysqli(SERVIDOR, USUARIO, SENHA, BANCO);
	mysqli_set_charset($cn_del,'utf8');
	$sql="DELETE FROM $tabela WHERE $filtro";
	if ($limitador) {
		$sql.=" LIMIT $limitador";
	}
	$dados=mysqli_query($cn_del, $sql);
	$excluidos=mysqli_affected_rows($cn_del);
   mysqli_close($cn_del);
	return $excluidos;
}

function atualizar_banco($tabela,$assoc,$filtro,$dbg=false){

	$updates='';
	foreach ($assoc as $chave => $valor){
		if (is_null($valor)) { $valor='NULL'; }
		if ($updates != '') {
			$updates.=', '; // Adiciona a vírgula quando não for a primeira passagem.
		}
		$updates.="$chave=$valor";
	}
	$query="UPDATE $tabela SET $updates WHERE $filtro";
	if ($dbg) { msg_alerta($query,"info"); }
	$cn_atualiza=new mysqli(SERVIDOR, USUARIO, SENHA, BANCO);
	$cn_atualiza->set_charset("utf8");
	$qf_atualiza=mysqli_query($cn_atualiza,$query);
	if (mysqli_errno($cn_atualiza) > 0) {
		require_once('head.php');
		if (mysqli_errno($cn_atualiza) == 1644) {
			msg_alerta("<b>O banco de dados recusou a atualização dos dados!</b><br><b>" .  mysqli_errno($cn_atualiza) . '</b>:' . mysqli_error($cn_atualiza),'danger');
		}
		else {
			msg_alerta("<b>Ocorreu um erro de acesso ao banco de dados.</b><br>" . $query .  "<b>" .  mysqli_errno($cn_atualiza) . '</b>:' . mysqli_error($cn_atualiza),'danger');
		}
		mysqli_close($cn_atualiza);
		die;
	}
	$quantidade=$cn_atualiza->affected_rows;
	mysqli_close($cn_atualiza);
	return $quantidade;
}


function criarModal($nome){

	echo "<!-- modal$nome -->";
	echo '<div id="modal' . $nome . '" class="modal fade" role="dialog">
		<div class="modal-dialog" id="modal' . $nome . 'Dialog">
		</div>
	</div>';
	echo "<!-- /modal$nome -->";
}

function nsz($valor){

	if ($valor == 0) {
      $valor='';
	}
	return $valor;
}


function printPontLn($repetir,$texto=NULL,$valor=NULL,$qtdMax=35) {

	if(is_numeric($valor)) {
		$qtdTxt=$qtdMax-13;
		$saida="R$ " . str_pad(formata_reais($valor), 10, " ", STR_PAD_LEFT);
	}
	else {
		$qtdTxt=$qtdMax;
		$saida='';
	}
	if (strlen($texto) > $qtdTxt) { $texto=substr($texto,0,$qtdTxt); }
	echo str_pad($texto,$qtdTxt,$repetir) . $saida . "\n"; 
}
function dbgPst() {
	echo "<PRE>";
	print_r($_POST);
	echo "</PRE>";
}

?>
