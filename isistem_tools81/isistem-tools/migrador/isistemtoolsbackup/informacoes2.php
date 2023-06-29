<?php

                    $getinfo = posix_getpwnam($_SERVER["REMOTE_USER"]);
                    $userdir = $getinfo['dir'];
                    $homedir = str_replace("/".$_SERVER["REMOTE_USER"], "", $userdir);
                    $HASHURL = str_replace("cgi/isistem-tools/index.php", "scripts/setrhash", $_SERVER["REQUEST_URI"]);

                    if(!file_exists($userdir.'/.accesshash')) {
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

                    if(file_exists("/usr/sbin/csf")) shell_exec( "csf -a ".$ipssh );
                
                    $access_hash = str_replace('\n', '', @file_get_contents($userdir.'/.accesshash'));
                    $xmlapi = new xmlapi('127.0.0.1'); 
                    $xmlapi->hash_auth($_SERVER["REMOTE_USER"], $access_hash);
                    $xmlapi->set_port('2087'); 
                    $xmlapi->set_output('array'); 
                    $acctlist = $xmlapi->listaccts(); 

                    $script = "/usr/local/cpanel/whostmgr/docroot/cgi/isistem-tools/migrador/isistemtoolsbackup";
                    $bash = "/usr/local/cpanel/scripts/isistemtoolsbackup";
                    $rsync = "/usr/bin/rsync";

                    $tmp = "/tmp/isistemtoolsbackup/" . strtotime(date("F d Y"));                    
                    if(!isset($_POST['restauracao']) && !isset($_POST['reversao']) && !isset($_POST['backup'])){
        
                    $acct_user = ""; $account_username = array();
                    foreach($acctlist['acct'] as $field => $acct)
                    {

                    if(isset($acct['user'])){
                    $acct_user .= "<option value='{$acct['user']}' ><span class='texto_padrao'>{$acct['user']}</span>\n"; 
                    $account_username[] = $acct['user'];   
                    }

                    }

                    if (!isset($acct)) $acct_user = list_local_users();

                    $s_acct_user = explode("\n", $acct_user);
                    sort($s_acct_user);
                    $acct_user = implode("\n", $s_acct_user);

                    # system("rm -rf /tmp/isistemtoolsbackup");
                    system("mkdir -p /tmp/isistemtoolsbackup");

                    $periodos = array();
                    $periodos_permitidos = ""; $datas_permitidas = "";
                    if($diario){
                    $periodos[] = "diario";
                    $periodos_permitidos .= "<option value='diario'>Diário</option>";
                    $datas_permitidas .= "<option value='diario'>Diário</option>";
                    }
                    if($semanal){
                    $periodos[] = "semanal";
                    $periodos_permitidos .= "<option value='semanal'>Semanal</option>";
                    $datas_permitidas .= "<option value='semanal'>Semanal</option>";
                    }
                    if($mensal){
                    $periodos[] = "mensal";
                    $periodos_permitidos .= "<option value='mensal'>Mensal</option>";
                    $datas_permitidas .= "<option value='mensal'>Mensal</option>";
                    }

                    # $periodos = array("diario","semanal","mensal");

                    foreach($periodos as $periodo){
                    if ($ativado && !file_exists($tmp)) @system($rsync." -az --include='/*' --exclude='*' -e '/usr/bin/ssh -F ".$script."/modulos/ssh_auth -p ".$portassh."' ".$usuariossh."@".$ipssh.":".$diretoriossh."/".$periodo."/ $tmp");
                    }

                    $lista_backups = "";

                    if ($handle = @opendir($tmp)) {

                    while (false !== ($file = readdir($handle))) 
                    {

                    if ($file != "." && $file != "..") 
                    {

                    if(is_dir($tmp . "/".$file)) 
                    {

                    $user_file = "/usr/isistemtoolsbackup/restauracao/".$file;
                    $account_owner = file_exists($user_file) ? @file_get_contents($user_file) : "";
                    $account_owner = str_replace("\n", "", $account_owner);

                    if($account_owner == $_SERVER["REMOTE_USER"]) {
                    $lista_backups .= "<option value='{$file}' ><span class='texto_padrao'>{$file}</span>\n";

                    }

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

                    foreach($matches[1] as $match){

                    $match = str_replace(array(" ","\n","\r"), "", $match); 
                    $marcado = false; if($cache == $match) $marcado = true;
                    if($marcado) $selecionado = "selected='selected'"; else $selecionado = "";
                    $escolher_particao_cache .= "<option value='{$match}' {$selecionado}><span class='texto_padrao'>{$match}</span>\n";

                    }
                 
                    $rsakey = "/usr/local/cpanel/whostmgr/docroot/cgi/isistem-tools/migrador/isistemtoolsbackup/modulos/id_rsa";
                    if(!file_exists($rsakey.".pub")){
                    system("ssh-keygen -t rsa -f {$rsakey} -N \"\" > /dev/null 2>&1");
                    }

                    $chavessh = @file_get_contents($rsakey.".pub");
                    $tiposrv = @file_get_contents("/var/cpanel/envtype");
                    if(preg_match("/standard/i", $tiposrv)) $n = "3"; else $n = "0";

                    $escolher_carga_cpu = "";
                    for($i=1; $i<="3"; $i++)
                    {
                    $cargaCPU = $i + $n;
                    $marcado = false; if($carga == $i) $marcado = true;
                    if($marcado) $selecionado = "selected='selected'"; else $selecionado = "";
                    $escolher_carga_cpu .= "<option value='{$cargaCPU}' {$selecionado}>Até <span class='texto_padrao'>{$cargaCPU}.0</span>\n";
                    }

                    $escolher_depois_limpeza = "<option value='1'><span class='texto_padrao'>1 dia</span>\n";
                    for($i=2; $i<="30"; $i++)
                    {
                    $marcado = false; if($limpeza == $i) $marcado = true;
                    if($marcado) $selecionado = "selected='selected'"; else $selecionado = "";
                    $escolher_depois_limpeza .= "<option value='{$i}' {$selecionado}><span class='texto_padrao'>{$i} dias</span>\n";
                    }

                    $escolher_hora = "";
                    for($i=0; $i<="9"; $i++)
                    {
                    $marcado = false; if(array_shift(explode(":",$horario)) == $i) $marcado = true;
                    if($marcado) $selecionado = "selected='selected'"; else $selecionado = "";
                    $escolher_hora .= "<option value='0{$i}' {$selecionado}><span class='texto_padrao'>{$i} h</span>\n";
                    }
                    for($i=10; $i<="23"; $i++)
                    {
                    $marcado = false; if(array_shift(explode(":",$horario)) == $i) $marcado = true;
                    if($marcado) $selecionado = "selected='selected'"; else $selecionado = "";
                    $escolher_hora .= "<option value='{$i}' {$selecionado}><span class='texto_padrao'>{$i} h</span>\n";
                    }

                    $escolher_min = "";
                    for($i=0; $i<="9"; $i++)
                    {
                    $marcado = false; if(array_pop(explode(":",$horario)) == $i) $marcado = true;
                    if($marcado) $selecionado = "selected='selected'"; else $selecionado = "";
                    $escolher_min .= "<option value='0{$i}' {$selecionado}><span class='texto_padrao'>{$i} min</span>\n";
                    }
                    for($i=10; $i<="59"; $i++)
                    {
                    $marcado = false; if(array_pop(explode(":",$horario)) == $i) $marcado = true;
                    if($marcado) $selecionado = "selected='selected'"; else $selecionado = "";
                    $escolher_min .= "<option value='{$i}' {$selecionado}><span class='texto_padrao'>{$i} min</span>\n";
                    }

                    $escolher_dia_semana = "";
                    for($i=1; $i<="7"; $i++)
                    {
                    $search = array("1", "2", "3", "4", "5", "6", "7");
                    $replace = array("Segunda", "Terça", "Quarta", "Quinta", "Sexta", "Sábado", "Domingo");
                    $dia_semana = str_replace($search, $replace, $i);
                    $marcado = false; if($semanal == $i) $marcado = true;
                    if($marcado) $selecionado = "selected='selected'"; else $selecionado = "";
                    $escolher_dia_semana .= "<option value='{$i}' {$selecionado}><span class='texto_padrao'>{$dia_semana}</span>\n";
                    }

                    $escolher_dia_mes = ""; 
                    for($i=1; $i<="31"; $i++)
                    {
                    $marcado = false; if($mensal == $i) $marcado = true;
                    if($marcado) $selecionado = "selected='selected'"; else $selecionado = "";
                    $escolher_dia_mes .= "<option value='{$i}' {$selecionado}><span class='texto_padrao'>Dia {$i}</span>\n";
                    }

                    $escolher_vez_dia = "<option value='1' ><span class='texto_padrao'>1 vez</span>\n";
                    for($i=2; $i<=24; $i++)
                    {
                    if ( 24 % $i == 0) {
                    $marcado = false; if($diario == $i) $marcado = true;
                    if($marcado) $selecionado = "selected='selected'"; else $selecionado = "";
                    $escolher_vez_dia .= "<option value='{$i}' {$selecionado}><span class='texto_padrao'>{$i} vezes</span>\n";
                    }
                    }

                    $escolher_cada = "";
                    for($i=1; $i<="59"; $i++)
                    {
                    $escolher_cada .= "<option value='{$i}' ><span class='texto_padrao'>A cada {$i}</span>\n";
                    }


                    $lista_backup_dia = "";
                    for($i=1; $i<=31; $i++)
                    {
                    $lista_backup_dia .= "<option value='{$i}' >Dia {$i}</option>\n";
                    }

                    $lista_backup_hora = "";
                    if($diario && $diario != "1"){

                    for($i=0; $i<=1; $i++)
                    {
                    $lista_backup_hora .= "<option value='{$i}' ><span class='texto_padrao'>à {$i}h</span>\n";
                    }
                    for($i=2; $i<=23; $i++)
                    {
                    $lista_backup_hora .= "<option value='{$i}' ><span class='texto_padrao'>às {$i}h</span>\n";
                    }

                    } 

                    $lista_backup_semana = "";
                    for($i=1; $i<="5"; $i++)
                    {
                    $lista_backup_semana .= "<option value='{$i}' ><span class='texto_padrao'>{$i}ª semana</span>\n";
                    }

                    $lista_backup_mes = "";
                    for($i=1; $i<="12"; $i++)
                    {
                    $procura = array("10","11","12","1","2","3","4","5","6","7","8","9");
                    $substitui = array("Outubro","Novembro","Dezembro","Janeiro","Fevereiro","Março","Abril","Maio","Junho","Julho","Agosto","Setembro");
                    $n = str_replace($procura, $substitui, $i);
                    $lista_backup_mes .= "<option value='{$i}' ><span class='texto_padrao'>{$n}</span>\n";
                    }

                    $sellected_time = "<option value='60' class='texto_padrao' ><span class='texto_padrao'>1 hora</span></option>";
                    for($i=2; $i<="12"; $i++)
                    {
                    $time = $i * 60;
                    $sellected_time .= "<option value='{$time}' class='texto_padrao' ><span class='texto_padrao'>{$i} horas</span></option>";
                    }
                    
                    $r = $rodando ? "checked='yes'" : "";
                    $e = $existente ? "checked='yes'" : "";
                    $d = $remocao ? "checked='yes'" : "";
                    $c = $copia ? "checked='yes'" : "";
                    $marc = "selected='selected'";
                    
                    
                    }

	function list_local_users() {

                    $accounts = "";

                    if ($handle = @opendir('/var/cpanel/users'))
                    {

                    while (false !== ($file = readdir($handle))) 
                    {

                    if ($file != "." && $file != "..") 
                    {

                    if(is_file("/var/cpanel/users/".$file)) 
                    {

                    if ( preg_match("/OWNER={$_SERVER["REMOTE_USER"]}/", file_get_contents("/var/cpanel/users/".$file)) 
                    || preg_match("/{$file}: {$_SERVER["REMOTE_USER"]}/", file_get_contents("/etc/trueuserowners"))
                    ) $accounts .= "<option value='{$file}' ><span class='texto_padrao'>{$file}</span>\n";

                    }

                    }

                    }

                    @closedir($handle);

                    }
                    
                    return $accounts;                                      
	}

	function list_users() {

                    $accounts = array();
                    $i = 0;

                    if ($handle = @opendir('/var/cpanel/users'))
                    {

                    while (false !== ($file = readdir($handle))) 
                    {

                    if ($file != "." && $file != "..") 
                    {

                    if(is_file("/var/cpanel/users/".$file)) 
                    {

                    if ( preg_match("/OWNER={$_SERVER["REMOTE_USER"]}/", file_get_contents("/var/cpanel/users/".$file)) 
                    || preg_match("/{$file}: {$_SERVER["REMOTE_USER"]}/", file_get_contents("/etc/trueuserowners"))
                    ){
                    $accounts['acct'][$i]['user'] = $file;
                    $i++;
                    }

                    }

                    }

                    }

                    @closedir($handle);

                    }
                    
                    return $accounts;                                      
	}
	

	            $usuarios_backup = array();
	            if ($handle = @opendir($tmp)) {

                    while (false !== ($file = readdir($handle))) 
                    {

                    if ($file != "." && $file != "..") 
                    {

                    if(is_dir($tmp . "/".$file)) 
                    {

                    $user_file = "/usr/isistemtoolsbackup/restauracao/".$file;
                    $account_owner = file_exists($user_file) ? @file_get_contents($user_file) : "";
                    $account_owner = str_replace("\n", "", $account_owner);
                    if($account_owner == $_SERVER["REMOTE_USER"]) {
                    $usuarios_backup[] = $file;

                    }

                    }

                    }

                    }

                    @closedir($handle);

                    }
