<?php
include_once("inc/funcoes.php");

if (file_exists("/usr/sbin/csf")) shell_exec("csf -a " . $ipssh);
$access_hash = get_token();
$xmlapi = new xmlapi('127.0.0.1');
$xmlapi->hash_auth('root', $access_hash);
$xmlapi->set_output('json');
$acctlistJson = $xmlapi->listaccts();
$resellerlistJson = $xmlapi->listresellers();
$acctlist = json_decode($acctlistJson);
$resellerlist = json_decode($resellerlistJson);

$reseller_user = "";
if (is_array($resellerlist->reseller)) {
    sort($resellerlist->reseller);
    if (count($resellerlist->reseller) == 1) {
        $resellerlist->reseller = [$resellerlist->reseller];
    }
    foreach ($resellerlist->reseller as $reseller) {
        $reseller_user .= "<option value='{$reseller}' class='texto_padrao' ><span class='texto_padrao'>{$reseller}</span>";
    }
}

$acct_user = "";
foreach ($acctlist->acct as $field => $acct) {
    if (isset($acct->user)) {
        $acct_user .= "<option value='{$acct->user}' ><span class='texto_padrao'>{$acct->user}</span>\n";
    }
}

if (!isset($acct)) {
    $acct_user = list_local_users();
}

$s_acct_user = explode("\n", $acct_user);
sort($s_acct_user);
$acct_user = implode("\n", $s_acct_user);


$script = "/usr/local/cpanel/whostmgr/docroot/cgi/isistem-tools/migrador/isistemtoolsbackup";
$bash = "/usr/local/cpanel/scripts/isistemtoolsbackup";
$rsync = "/usr/bin/rsync";

if (count($_POST) < 1) {
    $tmp = "/tmp/isistemtoolsbackup/" . strtotime(date("F d Y"));
    system("mkdir -p /tmp/isistemtoolsbackup");

    $periodos = array();
    $periodos_permitidos = "";
    $datas_permitidas = "";
    if ($diario) {
        $periodos[] = "diario";
        $periodos_permitidos .= "<option value='diario'>Diário</option>";
        $datas_permitidas .= "<option value='diario'>Diário</option>";
    }
    if ($semanal) {
        $periodos[] = "semanal";
        $periodos_permitidos .= "<option value='semanal'>Semanal</option>";
        $datas_permitidas .= "<option value='semanal'>Semanal</option>";
    }
    if ($mensal) {
        $periodos[] = "mensal";
        $periodos_permitidos .= "<option value='mensal'>Mensal</option>";
        $datas_permitidas .= "<option value='mensal'>Mensal</option>";
    }

    foreach ($periodos as $periodo) {
        if ($ativado && !file_exists($tmp)) @system($rsync . " -az --include='/*' --exclude='*' -e '/usr/bin/ssh -F " . $script . "/modulos/ssh_auth -p " . $portassh . "' " . $usuariossh . "@" . $ipssh . ":" . $diretoriossh . "/" . $periodo . "/ $tmp");
    }

    $lista_backups = "";

    if ($handle = @opendir($tmp)) {

        while (false !== ($file = readdir($handle))) {

            if ($file != "." && $file != "..") {

                if (is_dir($tmp . "/" . $file)) {

                    $lista_backups .= "<option value='{$file}' ><span class='texto_padrao'>{$file}</span>\n";
                }
            }
        }

        @closedir($handle);
    }

    $s_lista_backups = explode("\n", $lista_backups);
    sort($s_lista_backups);
    $lista_backups = implode("\n", $s_lista_backups);

    $particoes = shell_exec("df -h | grep \"/dev\"");
    preg_match_all("/% (.*)\n/U", $particoes, $matches);

    $escolher_particao_cache = "";
    foreach ($matches[1] as $match) {

        $match = str_replace(array(" ", "\n", "\r"), "", $match);
        $marcado = false;
        if ($cache == $match) $marcado = true;
        if ($marcado) $selecionado = "selected='selected'";
        else $selecionado = "";
        $escolher_particao_cache .= "<option value='{$match}' {$selecionado}><span class='texto_padrao'>{$match}</span>\n";
    }

    $rsakey = "/usr/local/cpanel/whostmgr/docroot/cgi/isistem-tools/migrador/isistemtoolsbackup/modulos/id_rsa";
    if (!file_exists($rsakey . ".pub")) {
        system("ssh-keygen -t rsa -f {$rsakey} -N \"\" > /dev/null 2>&1");
    }

    $chavessh = @file_get_contents($rsakey . ".pub");
    $tiposrv = @file_get_contents("/var/cpanel/envtype");
    if (preg_match("/standard/i", $tiposrv)) $n = "6";
    else $n = "3";

    $escolher_carga_cpu = "";
    for ($i = 1; $i <= $n; $i++) {
        $marcado = false;
        if ($carga == $i) $marcado = true;
        if ($marcado) $selecionado = "selected='selected'";
        else $selecionado = "";
        $escolher_carga_cpu .= "<option value='{$i}' {$selecionado}><span class='texto_padrao'>Máx. de {$i}.0</span>\n";
    }

    $escolher_depois_limpeza = "<option value='1'><span class='texto_padrao'>Após 1 dia</span>\n";
    for ($i = 2; $i <= "30"; $i++) {
        $marcado = false;
        if ($limpeza == $i) $marcado = true;
        if ($marcado) $selecionado = "selected='selected'";
        else $selecionado = "";
        $escolher_depois_limpeza .= "<option value='{$i}' {$selecionado}><span class='texto_padrao'>Após {$i} dias</span>\n";
    }

    $escolher_hora = "";
    for ($i = 0; $i <= "9"; $i++) {
        $marcado = false;
        $horario_array = explode(":", $horario);
        if (array_shift($horario_array) == $i) $marcado = true;
        if ($marcado) $selecionado = "selected='selected'";
        else $selecionado = "";
        $escolher_hora .= "<option value='0{$i}' {$selecionado}><span class='texto_padrao'>{$i} h</span>\n";
    }
    for ($i = 10; $i <= "23"; $i++) {
        $marcado = false;
        $horario_array = explode(":", $horario);
        if (array_shift($horario_array) == $i) $marcado = true;
        if ($marcado) $selecionado = "selected='selected'";
        else $selecionado = "";
        $escolher_hora .= "<option value='{$i}' {$selecionado}><span class='texto_padrao'>{$i} h</span>\n";
    }

    $escolher_min = "";
    for ($i = 0; $i <= "9"; $i++) {
        $marcado = false;
        $horario_array = explode(":", $horario);
        if (array_pop($horario_array) == $i) $marcado = true;
        if ($marcado) $selecionado = "selected='selected'";
        else $selecionado = "";
        $escolher_min .= "<option value='0{$i}' {$selecionado}><span class='texto_padrao'>{$i} min</span>\n";
    }
    for ($i = 10; $i <= "59"; $i++) {
        $marcado = false;
        $horario_array = explode(":", $horario);
        if (array_pop($horario_array) == $i) $marcado = true;
        if ($marcado) $selecionado = "selected='selected'";
        else $selecionado = "";
        $escolher_min .= "<option value='{$i}' {$selecionado}><span class='texto_padrao'>{$i} min</span>\n";
    }

    $escolher_dia_semana = "";
    for ($i = 1; $i <= "7"; $i++) {
        $search = array("1", "2", "3", "4", "5", "6", "7");
        $replace = array("Segunda", "Terça", "Quarta", "Quinta", "Sexta", "Sábado", "Domingo");
        $dia_semana = str_replace($search, $replace, $i);
        $marcado = false;
        if ($semanal == $i) $marcado = true;
        if ($marcado) $selecionado = "selected='selected'";
        else $selecionado = "";
        $escolher_dia_semana .= "<option value='{$i}' {$selecionado}><span class='texto_padrao'>{$dia_semana}</span>\n";
    }

    $escolher_dia_mes = "";
    for ($i = 1; $i <= "31"; $i++) {
        $marcado = false;
        if ($mensal == $i) $marcado = true;
        if ($marcado) $selecionado = "selected='selected'";
        else $selecionado = "";
        $escolher_dia_mes .= "<option value='{$i}' {$selecionado}><span class='texto_padrao'>Dia {$i}</span>\n";
    }

    $escolher_vez_dia = "<option value='1' ><span class='texto_padrao'>1 vez</span>\n";
    for ($i = 2; $i <= 24; $i++) {
        if (24 % $i == 0) {
            $marcado = false;
            if ($diario == $i) $marcado = true;
            if ($marcado) $selecionado = "selected='selected'";
            else $selecionado = "";
            $escolher_vez_dia .= "<option value='{$i}' {$selecionado}><span class='texto_padrao'>{$i} vezes</span>\n";
        }
    }

    $escolher_cada = "";
    for ($i = 1; $i <= "59"; $i++) {
        $escolher_cada .= "<option value='{$i}' ><span class='texto_padrao'>A cada {$i}</span>\n";
    }


    function backup_to_array()
    {
        $backup_to_array = array();
        $directory = "/tmp/isistemtoolsbackup";
        if ($handle = @opendir($directory)) {
            while (false !== ($file = readdir($handle))) {
                if ($file != "." && $file != "..") {
                    if (is_dir($directory . "/" . $file) && is_numeric($file)) {
                        $backup_to_array[] = $file;
                    }
                }
            }
            @closedir($handle);
        }
        return $backup_to_array;
    }


    $lista_backup_dia = "";
    for ($i = 1; $i <= 31; $i++) {
        $lista_backup_dia .= "<option value='{$i}' ><span class='texto_padrao'>Dia {$i}</span></option>\n";
    }


    $lista_backup_hora = "";
    if ($diario && $diario != "1") {

        for ($i = 0; $i <= 1; $i++) {
            $lista_backup_hora .= "<option value='{$i}' ><span class='texto_padrao'>à {$i}h</span></option>\n";
        }
        for ($i = 2; $i <= 23; $i++) {
            $lista_backup_hora .= "<option value='{$i}' ><span class='texto_padrao'>às {$i}h</span></option>\n";
        }
    }

    $lista_backup_semana = "";
    for ($i = 1; $i <= "5"; $i++) {
        $lista_backup_semana .= "<option value='{$i}' ><span class='texto_padrao'>{$i}ª semana</span></option>\n";
    }

    $lista_backup_mes = "";
    for ($i = 1; $i <= "12"; $i++) {
        $procura = array("10", "11", "12", "1", "2", "3", "4", "5", "6", "7", "8", "9");
        $substitui = array("Outubro", "Novembro", "Dezembro", "Janeiro", "Fevereiro", "Março", "Abril", "Maio", "Junho", "Julho", "Agosto", "Setembro");
        $n = str_replace($procura, $substitui, $i);
        $lista_backup_mes .= "<option value='{$i}' ><span class='texto_padrao'>{$n}</span>\n";
    }

    $sellected_time = "<option value='60' class='texto_padrao' ><span class='texto_padrao'>1 hora</span></option>";
    for ($i = 2; $i <= "12"; $i++) {
        $time = $i * 60;
        $sellected_time .= "<option value='{$time}' class='texto_padrao' ><span class='texto_padrao'>{$i} horas</span></option>";
    }

    $marc = "selected='selected'";

    $sim = $ativado ? $marc : '';
    $nao = $ativado ? '' : $marc;
    $dia_sim = $diario ? $marc : '';
    $dia_nao = $diario ? '' : $marc;
    $sem_sim = $semanal ? $marc : '';
    $sem_nao = $semanal ? '' : $marc;
    $mes_sim = $mensal ? $marc : '';
    $mes_nao = $mensal ? '' : $marc;
    $cache_sim = $cache ? $marc : '';
    $cache_nao = $cache ? '' : $marc;
    $carga_sim = $carga ? $marc : '';
    $carga_nao = $carga ? '' : $marc;
    $limpa_sim = $limpeza ? $marc : '';
    $limpa_nao = $limpeza ? '' : $marc;
    $r = $rodando ? "checked='yes'" : "";
    $e = $existente ? "checked='yes'" : "";
    $d = $remocao ? "checked='yes'" : "";
    $c = $copia ? "checked='yes'" : "";

    $inc = $exc = "";
    switch ($filtro) {
        case "0":
            $inc = $marc;
            break;
        case "1":
            $exc = $marc;
            break;
    }

    $srv = $rev = $usr = "";
}


function list_local_users()
{

    $accounts = "";

    if ($handle = @opendir('/var/cpanel/users')) {

        while (false !== ($file = readdir($handle))) {

            if ($file != "." && $file != "..") {

                if (is_file("/var/cpanel/users/" . $file)) {

                    $accounts .= "<option value='{$file}' ><span class='texto_padrao'>{$file}</span>\n";
                }
            }
        }

        @closedir($handle);
    }

    return $accounts;
}

function list_users()
{

    $accounts = array();
    $i = 0;

    if ($handle = @opendir('/var/cpanel/users')) {

        while (false !== ($file = readdir($handle))) {

            if ($file != "." && $file != "..") {

                if (is_file("/var/cpanel/users/" . $file)) {

                    $accounts['acct'][$i]['user'] = $file;
                    $i++;
                }
            }
        }

        @closedir($handle);
    }

    return $accounts;
}
