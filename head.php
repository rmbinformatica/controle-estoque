<?php
require_once("funcoes.php");
//session_start();
?>
<!DOCTYPE HTML>
<html>
<head>
<title><?php
echo  (isset($titulo) ? "$titulo" : 'Controle de estoque');
?></title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<!-- CLEAN <script type="application/x-javascript"> addEventListener("load", function() { setTimeout(hideURLbar, 0); }, false); function hideURLbar(){ window.scrollTo(0,1); } </script> -->
<!-- CSS bootstrap, style, icones e fontes -->
<link href="css/bootstrap.min.css" rel='stylesheet' type='text/css' />
<link href="css/style.css" rel='stylesheet' type='text/css' />
<link rel="stylesheet" href="css/icon-font.min.css" type='text/css' />
<link href='css/font-cabin.css' rel='stylesheet' type='text/css'>
<!-- FontsAwesome -->
<link href="css/font-awesome.css" rel="stylesheet">
<link href="fa/css/all.css" rel="stylesheet">
<!-- Gráficos -->
<script src="js/Chart.bundle.js"></script>
<!-- JS Próprios -->
<script src="js/mascara.js"></script>
<script src="js/controles_modal.js"></script>
<!--animate-->
<link href="css/animate.css" rel="stylesheet" type="text/css" media="all">
<script src="js/wow.min.js"></script>
<script>
new WOW().init();
</script>
<!--//end-animate-->
<!-- jQuery -->
<!-- <script src="js/jquery-1.10.2.min.js"></script> -->
<script src="js/jquery-3.3.1.min.js"></script>
<script src="js/jquery-ui.js"></script>
<link href="css/jquery-ui.css" rel="stylesheet">
<!-- Datatables, js no final do documento para as paginas carregarem mais rapidamente -->
<link rel="stylesheet" type="text/css" href="dataTable/datatables.min.css"/>
</head>
<body class="sticky-header left-side-collapsed">
  <section>
    <div class="left-side sticky-left-side"> <!-- leftside -->
      <div class="logo-icon text-center"><a href="index.php"><i class="lnr lnr-home"></i> </a></div>
      <div class="left-side-inner"> <!-- sidenav -->
        <ul class="nav nav-pills nav-stacked custom-nav"> <!-- navpill -->
          <li><a href="produto.php"><i class="fas fa-shopping-bag"></i><span>Produtos</span></a></li>
          <li><a href="entrada.php"><i class="fas fa-cart-plus"></i><span>Entrada no estoque</span></a>
          <li><a href="saida.php"><i class="fas fa-dolly"></i><span>Sa&iacute;da do estoque</span></a></li>  
          <li><a href="estoque_atual.php"><i class="far fa-file-alt"></i> <span>Estoque atual</span></a></li>
          <li><a href="estoque_minimo.php"><i class="fas fa-exclamation-triangle"></i><span>Produtos abaixo do estoque m&iacute;nimo</span></a></li>
          <li><a href="movimentacao_produto.php"><i class="fas fa-arrows-alt-v"></i><span>Movimenta&ccedil;&atilde;o de produto</span></a></li>
        </ul> <!-- /navpill -->
      </div> <!-- /sidenav -->
    </div> <!-- /leftside -->
    <div class="main-content main-content2 main-content2copy"> <!-- mainContent -->
      <div class="header-section"> <!-- header -->
        <div class="menu-right"> <!-- notificacoes -->
          <div class="user-panel-top">
            <div class="profile_details_left">
              <ul class="nofitications-dropdown">
                <li class="dropdown">
                  <a href="estoque_minimo.php"><i class="fas fa-shopping-cart" title="Produtos abaixo do estoque minimo"></i><span class="badge badge-danger" id="bs_minimo"><?= sql_simples("SELECT count(id) FROM estoque_atual WHERE quantidade<minimo"); ?></span></a>
                </li>
                <li class="login_box" id="containerBuscarLocalizador">
                  <div class="search-box">
                    <div id="sb-search" class="sb-search">
                      <form method="GET" action="movimentacao_produto.php">
                        <input class="sb-search-input" placeholder="Digite o código de barras..." type="search" id="search" name="codbarras">
                        <input type="hidden" name="data_inicial" value="2021-01-01">
                        <input class="sb-search-submit" type="submit" name="acao" value="buscar">
                        <span class="sb-icon-search"> </span>
                      </form>
                    </div>
                  </div>
                  <!-- search-scripts -->
                  <script src="js/classie.js"></script>
                  <script src="js/uisearch.js"></script>
                  <script>
                  new UISearch( document.getElementById( 'sb-search' ) );
                  </script>
                  <!-- //search-scripts -->
                </li>
<!--
                <li class="dropdown">
                  <a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false"><i class="fa fa-bell" title="Notificações"></i><span class="badge blue">3</span></a>
                  <ul class="dropdown-menu">
                    <li>
                      <div class="notification_header">
                        <h3>Você possui 3 notificações</h3>
                      </div>
                    </li>
                    <li><a href="#">
                      <div class="user_img"><img src="images/1.jpg" alt=""></div>
                      <div class="notification_desc">
                        <p>Lorem ipsum dolor sit amet</p>
                        <p><span>1 hour ago</span></p>
                      </div>
                      <div class="clearfix"></div>
                    </a></li>
                    <li class="odd"><a href="#">
                      <div class="user_img"><img src="images/1.jpg" alt=""></div>
                      <div class="notification_desc">
                        <p>Lorem ipsum dolor sit amet </p>
                        <p><span>2 hour ago</span></p>
                      </div>
                      <div class="clearfix"></div>
                    </a></li>
                    <li><a href="#">
                      <div class="user_img"><img src="images/1.jpg" alt=""></div>
                      <div class="notification_desc">
                        <p>Lorem ipsum dolor sit amet </p>
                        <p><span>3 hour ago</span></p>
                      </div>
                      <div class="clearfix"></div>
                    </a></li>
                    <li>
                      <div class="notification_bottom">
                        <a href="#">See all notification</a>
                      </div>
                    </li>
                  </ul>
                </li>
/notificacoes -->
                <div class="clearfix"></div>
              </ul>
            </div>
            <div class="profile_details"> <!-- perfil -->
              <ul>
                <li class="dropdown profile_details_drop">
                  <a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                    <div class="profile_img">
                      <span style="background:url(images/1.jpg) no-repeat center"> </span>
                      <div class="user-name">
                        <p>Usuario<span>Admin</span></p>
                      </div>
                      <i class="lnr lnr-chevron-down"></i>
                      <i class="lnr lnr-chevron-up"></i>
                      <div class="clearfix"></div>
                    </div>
                  </a>
                  <ul class="dropdown-menu drp-mnu">
                    <li> <a href="atualizar_sistema.php"><i class="fa fa-cog"></i>Atualizar sistema</a> </li>
                    <li> <a href="configurar_mensagens.php"><i class="fas fa-comment-dots"></i>Configurar mensagem</a></li>
                    <li> <a href="#"><i class="fas fa-key"></i> Mudar senha</a> </li>
                    <li> <a href="logoff.php"><i class="fas fa-sign-out-alt"></i> Sair</a> </li>
                  </ul>
                </li>
                <div class="clearfix"> </div>
              </ul>
            </div> <!-- /perfil -->
<!---            <div class="social_icons">            
              <div class="col-sm-6 social_icons-left ">
                <a href="caixa.php" title="Valor em dinheiro no caixa de hoje"><i class="fa fa-money-bill-wave-alt"></i><span id="bs_dinh">Carregando...</span></a>
              </div>
              <div class="clearfix"> </div>
            </div>  /social_icons -->
            <div class="clearfix"></div>
          </div> <!-- /user-panel-top -->
          <div class="clearfix"></div>
        </div> <!-- /notificacoes -->
      </div> <!-- /header -->
      <div id="page-wrapper">
<?php
  if (isset($breadcumb)) {
  	page_breadcumb($breadcumb[0],$breadcumb[1]);
  }
?>
