<?php
require_once("xmlapi.php");

$HASHURL = str_replace("cgi/isistem-tools/index.php", "scripts7/apitokens/home", $_SERVER["REQUEST_URI"]);
if (!file_exists('/root/token.txt')) {
    print "<!DOCTYPE HTML>
<html lang='en-US'>
<head>
    <meta charset='UTF-8'>
    <meta http-equiv='refresh' content='1;url=$HASHURL'>
    <script language='javascript'>
        window.location.href = '$HASHURL'
    </script>
</head>
</html>";
}

function get_token()
{
	return file_get_contents("/root/token.txt");
}

function verificaToken(): bool {
    include_once("conexao.php");
    $tokenFile = "./tokenfile.itk";
    $keyAccess = "/root/token.txt";

    if (file_exists($tokenFile) && file_exists($keyAccess)) {
		$systemKey = trim(file_get_contents($tokenFile));
        $searchKey = "SELECT code FROM token WHERE code = '$systemKey'";
        $result = $mysqli->query($searchKey);
        if ($result === false || $result->num_rows === 0) {
            $result->close();
            $mysqli->close();
            return false;
        }
        $resultKey = $result->fetch_assoc();
        $responseKey = $resultKey["code"];
        $accessToken = trim(file_get_contents($keyAccess));
        return $responseKey == $accessToken;
    }
    return false;
}

function Criar_Celula_Log($log, $tipo)
{
	if ($tipo == 'ok') {
		$celula_log = '<div class="ui success message" ><i class="close icon"></i>' . $log . '</div>';
	} elseif ($tipo == 'alerta') {
		$celula_log = '<div class="ui warning message"><i class="close icon"></i>' . $log . '</div>';
	} elseif ($tipo == 'erro') {
		$celula_log = '<div class="ui error message"><i class="close icon"></i>' . $log . '</div>';
	} else {
		$celula_log = '<div class="ui info message" ><i class="close icon"></i>' . $log . '</div>';
	}
	return $celula_log;
}

function ler_xml($string, $tag, $ordem)
{
	$resultado = explode("<" . $tag . ">", $string);
	$resultado = explode("</" . $tag . ">", $resultado[$ordem]);
	return $resultado[0];
}

function get_revendas()
{
	$lista_revendas = file_get_contents("/var/cpanel/resellers");
	$revendas = explode("\n", $lista_revendas);
	foreach ($revendas as $revenda) {
		if ($revenda) {
			$revenda = substr($revenda, 0, strpos($revenda, ":"));
			echo '<option value="' . $revenda . '">' . $revenda . '</option>';
		}
	}
}

function get_mysql_datas()
{
	$backupDir = shell_exec("grep BACKUPDIR /etc/cpbackup.conf | awk {'print $2'}");
	$backupDir = $backupDir !== null ? trim($backupDir) . "/backup_mysql" : "/backup_mysql";
	$listaPastas = '';
	if (is_dir($backupDir)) {
		$diretorio = new DirectoryIterator($backupDir);
		foreach ($diretorio as $conteudo) {
			if (!$conteudo->isDot()) {
				$listaPastas .= $conteudo->getFilename() . '|';
			}
		}
	}
	return $listaPastas;
}

function get_dominios(string $usuario): string
{
	$xmlapi = new xmlapi('localhost');
	$xmlapi->hash_auth('root', get_token());
	$xmlapi->return_xml(1);
	$xml_dominios = $usuario == 'root'
		? $xmlapi->listaccts('', $usuario)
		: $xmlapi->listaccts('owner', $usuario);

	$json_dominios = json_decode($xml_dominios, true);
	$lista_dominios = '';
	if (isset($json_dominios['acct'])) {
		foreach ($json_dominios['acct'] as $dominio) {
			$lista_dominios .= $dominio['domain'] . ":" . $dominio['user'] . "|";
		}
	}
	return $lista_dominios;
}

function get_usuario(string $dominio): string
{
	$xmlapi = new xmlapi('localhost');
	$xmlapi->hash_auth('root', get_token());
	$xmlapi->return_xml(1);
	$xml = $xmlapi->domainuserdata($dominio);
	$json = json_decode($xml, true);
	$usuario = isset($json['userdata'][0]['user']) ? $json['userdata'][0]['user'] : '';
	return (string) $usuario;
}

function get_hostname(): string
{
	$xmlapi = new xmlapi('localhost');
	$xmlapi->hash_auth('root', get_token());
	$xmlapi->return_xml(1);
	$hostname_json = $xmlapi->gethostname();
	$json = json_decode($hostname_json, true);
	if (isset($json['hostname'])) {
		return $json['hostname'];
	}
	return '';
}

function csf($ip, $acao)
{
	if ($acao == 'b') {
		$resultado = shell_exec("csf -d " . $ip . "");
		return $resultado;
	} else if ($acao == 'd') {
		$resultado = shell_exec("csf -dr " . $ip . "");
		return $resultado;
		if (preg_match('/Removing/', $resultado)) {
			return 'ok';
		} else {
			return 'erro';
		}
	} else if ($acao == 'a') {
		$resultado = shell_exec("csf -dr " . $ip . "");
		if (preg_match('/Adding/', $resultado)) {
			return 'ok';
		} else {
			return 'erro';
		}
	} else {
		$resultado = shell_exec("grep " . $ip . " /etc/csf/csf.deny");
		$resultado = explode("#", $resultado);
		return $resultado[1];
	}
}

if (isset($_GET["acao"]) && $_GET["acao"] == "carregar_backups" && !empty($_GET["usuario"]) && !empty($_GET["data"])) {
	$backup_dir = str_replace("\n", "", shell_exec("grep BACKUPDIR /etc/cpbackup.conf | awk {'print $2'}"));
	$lista_backups = shell_exec("ls " . $backup_dir . "/backup_mysql/" . $_GET["data"] . "/" . $_GET["usuario"] . "_* | cut -d / -f 5");
	$array_lista_backups = explode("\n", $lista_backups);
	foreach ($array_lista_backups as $backup) {
		if ($backup) {
			echo "" . $backup . ":" . str_replace(".sql.zip", "", $backup) . "|";
		}
	}
}

function get_usuarios_revenda(string $revenda): void
{
	$xmlapi = new xmlapi('localhost');
	$xmlapi->hash_auth('root', get_token());
	$xmlapi->return_xml(1);
	if ($revenda == 'root') {
		$usuarios_revenda = $xmlapi->listaccts('', '');
	} else {
		$usuarios_revenda = $xmlapi->listaccts('owner', $revenda);
	}
	$json = json_decode($usuarios_revenda, true);
	if (isset($json['acct'])) {
		foreach ($json['acct'] as $usuario) {
			$username = $usuario['user'];
			$domain = $usuario['domain'];
			if ($username) {
				echo '<option value="' . $username . '">' . $username . ' (' . $domain . ')</option>';
			}
		}
	}
}

function get_dominio(string $usuario): ?string
{
	$xmlapi = new xmlapi('localhost');
	$xmlapi->hash_auth('root', get_token());
	$xmlapi->return_xml(1);
	$dados_usuario = $xmlapi->accountsummary($usuario);
	$json = json_decode($dados_usuario, true);
	$dominio = isset($json['acct']['domain']) ? $json['acct']['domain'] : null;
	return $dominio;
}

function encode_decode(string $texto, string $tipo = "E"): string
{
	if ($tipo === "E") {
		$sesencoded = $texto;
		$num = mt_rand(0, 3);
		for ($i = 1; $i <= $num; $i++) {
			$sesencoded = base64_encode($sesencoded);
		}
		$alpha_array = ['Y', 'D', 'U', 'R', 'P', 'S', 'B', 'M', 'A', 'T', 'H'];
		$sesencoded = $sesencoded . "+" . $alpha_array[$num];
		$sesencoded = base64_encode($sesencoded);
		return $sesencoded;
	} else {
		$alpha_array = ['Y', 'D', 'U', 'R', 'P', 'S', 'B', 'M', 'A', 'T', 'H'];
		[$decoded, $letter] = explode("+", base64_decode($texto));
		$i = array_search($letter, $alpha_array);
		for ($j = 1; $j <= $i; $j++) {
			$decoded = base64_decode($decoded);
		}
		return $decoded;
	}
}

function tamanho_espaco(int $size): string
{
	$i = 0;
	$iec = [" MB", " GB", " TB"];
	while ($size > 1000 && $i < count($iec)) {
		$size /= 1000;
		$i++;
	}
	return sprintf("%.2f", $size) . $iec[$i];
}
