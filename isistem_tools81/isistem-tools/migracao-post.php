<!DOCTYPE html>
<html>
<head>
	    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
  <title>Isistem Tools</title>
  <link rel="stylesheet" type="text/css" href="public/semantic/semantic.min.css">

  <script
    src="https://code.jquery.com/jquery-3.1.1.min.js"
    integrity="sha256-hVVnYaiADRTO2PzUGmuLJr8BLUSjGIZsDYGmIJLv2b8="
    crossorigin="anonymous"></script>
  <script src="public/semantic/semantic.min.js"></script>
<script type="text/javascript" src="inc/sortable.js"></script>
<script type="text/javascript">
  $(document).ready(function(e){
    $(".ui.stackable.pointing.secondary.teal.menu .item").tab();
    $(".ui.stackable.tabular.blue.menu .item").tab();
    $(".ui.radio.checkbox").checkbox();
    $('.ui.table').tablesort();
    $(".ui.form").form();
    $(".ui.dropdown").dropdown();
    $(".ui.checkbox").checkbox();
  });
</script>

</head>
<body style="background-color: #1E88E5">
<?php 

error_reporting(E_ALL);
ini_set("display_errors", "On");
    

$_SERVER['REMOTE_USER'] = 'root';
        $MigraUsr = $_SERVER['REMOTE_USER'];
if (array_key_exists("migracao",$_POST)){

?>
<div id='conteudo' class="ui container">
	<div class='ui segment stackable grid' id='tabPane1'>
   	  	<div class="sixteen wide column">
   	  		<div class="ui breadcrumb">
		        <a class="section">Isistem Tools</a>
		        <i class="right chevron icon divider"></i>
		        <a class="section">Backup</a>
		        <i class="right arrow icon divider"></i>
		        <a class="section">Migração</a>
		        <i class="right arrow icon divider"></i>
		        <div class="active section">Migrando...</div>
		      </div>
   	  	</div>
       	<div class="sixteen wide column">
       		<h2 class='ui header'>Migrando...</h2>
       	</div>

       	<div class="sixteen wide column">
       		<?php

					if($_POST['tipo'] == 'revenda') require("migrador/isistemtoolsbackup/migracao.php");
					else  require("migrador/isistemtoolsbackup/migracao2.php");

				}
				 ?>
       	</div>

</body>
</html>
