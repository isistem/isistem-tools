<?php
session_start();
require_once("inc/funcoes.php");



$verify = verificaToken();
if ($verify === false) {
  header('Location: key.php');
  exit;
}

if($_POST["acao"] == 'b') {

$resultado = shell_exec("csf -d ".$_POST["ip"]."");

if(preg_match('/Adding/',$resultado)) {
	$resultado_acao = Criar_Celula_Log("IP ".$_POST["ip"]." bloqueado com sucesso.","ok");
} else {
	$resultado_acao = Criar_Celula_Log("Não foi possível bloquear o IP ".$_POST["ip"]." no firewall.",$resultado,"erro");
}

} else if($_POST["acao"] == 'd') {

	$resultado_grep = shell_exec("grep ".$_POST["ip"]." /etc/csf/csf.deny");
	$resultado_grep = explode("#",$resultado_grep);

	$resultado = shell_exec("csf -dr ".$_POST["ip"]."");

	if(preg_match('/Removing/',$resultado)) {
		
		$resultado_acao .= Criar_Celula_Log("IP ".$_POST["ip"]." desbloqueado com sucesso.","ok");
		$resultado_acao .= Criar_Celula_Log("Motivo do Bloqueio: ".$resultado_grep[1]."","alerta");
	
	} elseif(preg_match('/not found/',$resultado)) {
		$resultado = shell_exec("csf -tr ".$_POST["ip"]."");
		if(preg_match('/Removing/',$resultado)) {
			
			$resultado_acao .= Criar_Celula_Log("IP ".$_POST["ip"]." desbloqueado com sucesso.","ok");
			$resultado_acao .= Criar_Celula_Log("Motivo do Bloqueio: ".$resultado_grep[1]."","alerta");
		
		}elseif(preg_match('/not found/',$resultado)) {
			$resultado_acao = Criar_Celula_Log("O IP ".$_POST["ip"]." não esta bloqueado no firewall.","erro");
		}else{
			$resultado_acao = Criar_Celula_Log("Não foi possível desbloquear o IP ".$_POST["ip"]." no firewall.","erro");
		}
		
	} else {
		$resultado_acao = Criar_Celula_Log("Não foi possível desbloquear o IP ".$_POST["ip"]." no firewall.","erro");
	}

} else {

		$resultado_acao = Criar_Celula_Log("Selecione a Acão!.","erro");
}

// Cria o cookie do status das ações executadas e redireciona.
$_SESSION['status_acao'] = $resultado_acao;
header("Location: ".$_SERVER['HTTP_REFERER']."");

?>