<?php
session_start();
require_once("inc/funcoes.php");
$verify = verificaToken();
if ($verify === false) {
  header('Location: key.php');
  exit;
}

$usuario = get_usuario($_POST["dominio"]);

if($_POST["erro"] == "403") {

// Corrigi as permissões
shell_exec("chown -Rf ".$usuario.".".$usuario." /home/".$usuario."/public_html");
shell_exec("chmod 755 /home/".$usuario."/public_html");

} else {

// Corrigi as permissões
shell_exec("find /home/".$usuario."/public_html -type d -exec chown -Rf ".$usuario.".".$usuario." {} \;");
shell_exec("find /home/".$usuario."/public_html -type f -exec chown -Rf ".$usuario.".".$usuario." {} \;");
shell_exec("find /home/".$usuario."/public_html -type d -exec chmod 0755 {} \;");
shell_exec("find /home/".$usuario."/public_html -type f -exec chmod 0644 {} \;");

// Verifica variáveis inválidas em arquivos .htaccess
shell_exec("find /home/".$usuario." -name .htaccess | xargs sed -i '/php_flag/d'");
shell_exec("find /home/".$usuario." -name .htaccess | xargs sed -i '/php_value/d'");

}

$resultado_acao .= Criar_Celula_Log("Permissões corrigidas com sucesso.","ok");

// Cria o cookie do status das ações executadas e redireciona.
$_SESSION['status_acao'] = $resultado_acao;
header("Location: ".$_SERVER['HTTP_REFERER']."");

?>