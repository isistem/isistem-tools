<?php
session_start();
require_once("inc/funcoes.php");
$verify = verificaToken();
if ($verify === false) {
  header('Location: key.php');
  exit;
}


if($_ENV['REMOTE_USER'] == "root") {

// Adiciona a configura��o na lista de limites
shell_exec("echo '".$_POST["dominio"]."=".$_POST["limite"]."' >> /var/cpanel/maxemails");

// Recarrega a lista de limites
shell_exec("/scripts/build_maxemails_config");

$resultado_acao .= Criar_Celula_Log("Limite de envio de e-mails por hora configurado com sucesso.","ok");

$resultado_acao .= Criar_Celula_Log("Dom�nio ".$_POST["dominio"]." limitado a ".$_POST["limite"]." e-mails por hora.","alerta");

} else {

if($_POST["limite"] > 1000) {

$resultado_acao .= Criar_Celula_Log("O limite informado � maior que o limite m�ximo permitido(1000).","erro");

} else {

// Adiciona a configura��o na lista de limites
shell_exec("echo '".$_POST["dominio"]."=".$_POST["limite"]."' >> /var/cpanel/maxemails");

// Recarrega a lista de limites
shell_exec("/scripts/build_maxemails_config");

$resultado_acao .= Criar_Celula_Log("Limite de envio de e-mails por hora configurado com sucesso.","ok");

$resultado_acao .= Criar_Celula_Log("Dom�nio ".$_POST["dominio"]." limitado a ".$_POST["limite"]." e-mails por hora.","alerta");

}

}



// Cria o cookie do status das a��es executadas e redireciona.
$_SESSION['status_acao'] = $resultado_acao;
header("Location: ".$_SERVER['HTTP_REFERER']."");

?>