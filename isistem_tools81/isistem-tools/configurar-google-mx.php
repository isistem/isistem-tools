<?php
session_start();
require_once("inc/funcoes.php");

$verify = verificaToken();
if ($verify === false) {
  header('Location: key.php');
  exit;
}


// Verifica o MX a ser configurado
if($_POST["mx"] == 'google') {

// Limpa todas as entradas MX e SPF existentes na zona de DNS
shell_exec("sed -i '/MX/d' /var/named/".$_POST["dominio"].".db");
shell_exec("sed -i '/spf1/d' /var/named/".$_POST["dominio"].".db");

// Insere o MX do google na zona de DNS
shell_exec("echo '".$_POST["dominio"].". 3600 IN MX 1 ASPMX.L.GOOGLE.COM.' >> /var/named/".$_POST["dominio"].".db");
shell_exec("echo '".$_POST["dominio"].". 3600 IN MX 5 ALT1.ASPMX.L.GOOGLE.COM.' >> /var/named/".$_POST["dominio"].".db");
shell_exec("echo '".$_POST["dominio"].". 3600 IN MX 5 ALT2.ASPMX.L.GOOGLE.COM.' >> /var/named/".$_POST["dominio"].".db");
shell_exec("echo '".$_POST["dominio"].". 3600 IN MX 10 ALT3.ASPMX.L.GOOGLE.COM.' >> /var/named/".$_POST["dominio"].".db");
shell_exec("echo '".$_POST["dominio"].". 3600 IN MX 10 ALT3.ASPMX.L.GOOGLE.COM.' >> /var/named/".$_POST["dominio"].".db");


// Insere o SPF
shell_exec("echo '".$_POST["dominio"].". 3600 IN TXT \"v=spf1 a mx ?all\"' >> /var/named/".$_POST["dominio"].".db");

shell_exec("sed -i '/".$_POST["dominio"]."/d' /etc/localdomains");
shell_exec("echo ".$_POST["dominio"]." >> /etc/remotedomains");

$resultado_acao = Criar_Celula_Log("MX do Google configurado com sucesso no dom�nio ".$_POST["dominio"]."","ok");

} else {

// Limpa todas as entradas MX e SPF existentes na zona de DNS
shell_exec("sed -i '/MX/d' /var/named/".$_POST["dominio"].".db");
shell_exec("sed -i '/spf1/d' /var/named/".$_POST["dominio"].".db");

// Insere o MX do servidor na zona de DNS
shell_exec("echo '".$_POST["dominio"].". 3600 IN MX 0 ".get_hostname().".' >> /var/named/".$_POST["dominio"].".db");

// Insere o SPF
shell_exec("echo '".$_POST["dominio"].". 3600 IN TXT \"v=spf1 a mx ?all\"' >> /var/named/".$_POST["dominio"].".db");

shell_exec("sed -i '/".$_POST["dominio"]."/d' /etc/remotedomains");
shell_exec("echo ".$_POST["dominio"]." >> /etc/localdomains");

$resultado_acao = Criar_Celula_Log("MX Padr�o configurado com sucesso no dom�nio ".$_POST["dominio"]."","ok");
}

// Faz reload da zona de DNS no servidor de DNS

shell_exec("/etc/init.d/named reload");

// Cria o cookie do status das a��es executadas e redireciona.
$_SESSION['status_acao'] = $resultado_acao;
header("Location: ".$_SERVER['HTTP_REFERER']."");
?>