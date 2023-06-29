<?php
session_start();
require_once("inc/funcoes.php");
$verify = verificaToken();
if ($verify === false) {
  header('Location: key.php');
  exit;
}

$usuario = get_usuario($_POST["dominio"]);

if($_POST["acao"] == 'ON') {

system("rm -rf /home/".$usuario."/.htaccess");

system("rm -rf /home/".$usuario."/php.ini");

$resultado_acao = Criar_Celula_Log("Register Globals habilitado(ON) com sucesso no dom�nio ".$_POST["dominio"]."","ok");

} else {

system("echo 'suPHP_ConfigPath /home/".$usuario."' >> /home/".$usuario."/.htaccess");

system("cp /usr/local/lib/php.ini /home/".$usuario."/php.ini");

system("replace 'register_globals = On' 'register_globals = Off' -- /home/".$usuario."/php.ini > /dev/null");

$resultado_acao = Criar_Celula_Log("Register Globals desabilitado(OFF) com sucesso no dom�nio ".$_POST["dominio"]."","ok");

}

// Cria o cookie do status das a��es executadas e redireciona.
$_SESSION['status_acao'] = $resultado_acao;
header("Location: ".$_SERVER['HTTP_REFERER']."");

?>