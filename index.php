<?php
require 'head.php';

?>
<div id="page-wrapper">
	<div class="graphs">
		<div class="switches">
				<div class="col-md-8">
					<div class="switch-right-grid">
						<div class="switch-right-grid1">
							<h3><a href="agenda.php">COMPOSI&Ccedil;&Atilde;O DO ESTOQUE EM <?php echo formata_data(HOJE); ?></a></h3>
							<canvas id="estoque" height="315" width="470" style="width: 470px; height: 315px;"></canvas>
							<script>
<?php

$estoque["type"]="pie";
$estoque["data"]["labels"]=array();
$estoque["options"]["responsive"]=true;
//$barco["options"]["scales"]["xAxes"][0]["stacked"]=true;
$estoque["options"]["scales"]["xAxes"][0]["ticks"]=array("stepSize" => 1);
//$barco["options"]["scales"]["yAxes"][0]["stacked"]=true;

$quantidade=slq_assoc("SELECT descricao, quantidade FROM estoque_atual ORDER BY descricao ASC");
$cores_fundo=array();
for($j=0;$j<count($quantidade);$j++){
	if (count($cores_fundo) == 0) { $cores_fundo=load_json("cores_fundo"); }
    $cor='#' . array_shift($cores_fundo);
	array_push($estoque["data"]["labels"], $quantidade[$j]["descricao"]);
	$estoque["data"]["datasets"][0]["data"][$j]=$quantidade[$j]["quantidade"];
	$estoque["data"]["datasets"][0]["backgroundColor"][$j]="$cor";
}

$json_estoq=json_encode($estoque);
echo 'var confEstoque =' . $json_estoq . ';';

?>

				var bandpie = document.getElementById('estoque').getContext('2d');
				window.myPie = new Chart(bandpie, confEstoque);


							</script>

						</div>
					</div>
				</div>
			<div class="clearfix"></div>
		</div>
	</div>
</div>
</div>

<?php
require 'footer-bar.php';
?>
