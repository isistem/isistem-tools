<?php

                    error_reporting(0);
                    set_time_limit(0);
                    ignore_user_abort(true);
                    chdir("/usr/local/cpanel/whostmgr/docroot/cgi/isistem-tools/migrador/isistemtoolsbackup/");
                    require "preferencias.php";
                    require "funcoes.php";
                    require("xmlapi.php");

                    if(!$ativado && !isset($argv[1])){
                    echo "O backup está desabilitado!"; exit();
                    }

                    if(isset($argv[2])) {

                    if($argv[2] == "-e") $filtro == "1"; $contas = $argv[3];
                    if($argv[2] == "-i") $filtro == "0"; $contas = $argv[3];

                    }


                if(!extension_loaded('curl')){

                    echo "Não foi possível carregar a extensão PHP cURL. Não é possível continuar!\n";
                    echo "Execute o seguinte comando via SSH para compilar cPanel PHP com suporte a cURL:\n";
                    echo "/usr/local/cpanel/scripts/isistemtoolsbackup/manutencao.sh -c";
                    exit();
                }


                    $access_hash = str_replace('\n', '', @file_get_contents('/root/token.txt'));
                    $xmlapi = new xmlapi('127.0.0.1'); 
                    $xmlapi->hash_auth('root', $access_hash);
                    $xmlapi->set_port('2087'); 
                    $xmlapi->set_output('array'); 
                    $acctlist = $xmlapi->listaccts();

                    foreach($acctlist['acct'] as $field => $acct)
                    {
                    if (isset($acct['owner'])) file_put_contents("/usr/isistemtoolsbackup/restauracao/".$acct['user'], $acct['owner']);
                    }

                    $serverconfig = array( );
                    $flinewww = file( "/etc/wwwacct.conf" );
                    foreach ( $flinewww as $wwkey )
                    {
                    if ( preg_match( "/^CONTACTEMAIL/", $wwkey ) )
                    {
                    $x = explode( " ", $wwkey );
                    $cemail = trim( $x[1] );
                    $serverconfig['CONTACTEMAIL'] = $cemail;
                    }
                    if ( preg_match( "/^HOST/", $wwkey ) )
                    {
                    $x = explode( " ", $wwkey );
                    $serverhn = trim( $x[1] );
                    $serverconfig['HOSTNAME'] = $serverhn;
                    }
                    }

                    $to = $serverconfig['CONTACTEMAIL'];
                    $host = $serverconfig['HOSTNAME'];
                    
                    $lista = "";
                    if($handle = opendir("/var/cpanel/users"))
                    {
                    while ( false !== ( $arquivo = readdir( $handle ) ) )
                    {
                    if ( $arquivo != "." && $arquivo != ".." )
                    {
                    if( strlen($arquivo) <= "8" ) $lista .= $arquivo.",";
                    }
                    }
                    closedir( $handle );
                    }

                    $usuario = explode(",", $lista); sort($usuario);
                    $numerocontas = count($usuario) - 1;

                    $TipoSRV = file_get_contents("/var/cpanel/envtype");
                    $CargaCPU = preg_match("/standard/i", $TipoSRV) ? "6.0" : "3.0"; 

                    $script = "/usr/local/cpanel/whostmgr/docroot/cgi/isistem-tools/migrador/isistemtoolsbackup";
                    $bash = "/usr/local/cpanel/scripts/isistemtoolsbackup";
                    $rsync = $carga ? "/usr/local/cpanel/bin/cpuwatch ".$carga.".0 /usr/bin/rsync" : "/usr/bin/rsync";

                    if(file_exists("/usr/sbin/csf")) shell_exec( "csf -a ".$ipssh );
                    
                    $day = date("G"); $month = date("n"); $hora_atual = date("H:i"); $notificacao = false;
                    
                    $i=0; $week=0;
                    if (date("N", mktime(0, 0, 0, date("n"), 1, date("Y"))) <= "6") $week++;
                    while ($i <= date("j")) {
                    if (date("N", mktime(0, 0, 0, date("n"), $i, date("Y"))) == "7") $week++;
                    $i++;
                    }
             
                    if(!file_exists("/var/log/isistemtoolsbackup/AGENDADO")){
                    $new_week = $week - 1; $new_month = $month - 1;
                    $new_day = $day - 1; if($day == "0") $new_day = "23";
                    file_put_contents("/var/log/isistemtoolsbackup/AGENDADO", "DIARIO:{$new_day}\nSEMANAL:{$new_week}\nMENSAL:{$new_month}\n");
                    }                    

                    if(isset($argv[1]) && $argv[1] != "-d") $diario = false;
                    if(isset($argv[1]) && $argv[1] != "-s") $semanal = false;
                    if(isset($argv[1]) && $argv[1] != "-m") $mensal = false;
                    
                    if(!isset($argv[1])){

                    $diferenca = floor( ( strtotime(date("H:i")) - strtotime($horario) ) / 60 );
                    if($diferenca >= 1 || $diferenca < 0){
                    $semanal = $mensal = false; 
                    if($diario == "1") $diario = false;
                    } 
     
                    }

                    if(!$diario && !$semanal && !$mensal) exit();

                    $userdir = array(); $homedir = array();

                    if($diario)
                    {

                    $lasttime = file_get_contents("/var/log/isistemtoolsbackup/AGENDADO");
                    $lastcopy = explode("\n", $lasttime);
                    
                    $hora = 24 / $diario; $agora = date("G");                   
                    $tempo = str_replace("DIARIO:", "", $lastcopy[0]);

                    $diferenca = ( $agora < $tempo ) ? 24 + $agora - $tempo : $agora - $tempo;

                    if( $diferenca >= $hora || $diario == "1" || (isset($argv[1]) && $argv[1] == "-d") )
                    {

                    $notificacao = true; file_put_contents("/var/log/isistemtoolsbackup/INICIADO","");

                    if(!isset($argv[1])){
                    $lastcron = str_replace($tempo, $agora, $lastcopy[0]);
                    $lasttime = str_replace($lastcopy[0], $lastcron, $lasttime);
                    file_put_contents("/var/log/isistemtoolsbackup/AGENDADO", $lasttime);
                    }
                    
                    $hour = date("G"); $hoje = date("j"); $today = $hoje; if($diario != "1") $today = $hoje."/".$hour;
                    system("/usr/bin/ssh -F ".$script."/modulos/ssh_auth -p ".$portassh." ".$usuariossh."@".$ipssh." /bin/mkdir -p ".$diretoriossh."/diario");

                    $i = 1;
                    while ( $i <= $numerocontas )
                    {
			
                    $contas1 = str_replace(' ','',$contas);
                    $contas2 = explode(",", $contas1);

                    if($filtro == "0" && !in_array($usuario[$i], $contas2))
                    {
                    echo "Backup is disabled for user ".$usuario[$i]."\n";
                    }
                    elseif ($filtro == "1" && in_array($usuario[$i], $contas2))
                    {
                    echo "Backup is disabled for user ".$usuario[$i]."\n";
                    }

                    else

                    {

                    $getinfo = posix_getpwnam($usuario[$i]);
                    $userdir[$i] = $getinfo['dir'];
                    if($userdir[$i] == "") {
                         ++$i;
                         continue;
                    }                   
                    $homedir[$i] = str_replace("/".$usuario[$i], "", $userdir[$i]);
                    if($homedir[$i] == "") {
                         ++$i;
                         continue;
                    }                   
                    if($homedir[$i] != "")
                    {
                    echo "\n[ Backup diario do usuario {$usuario[$i]} ]\n\n";

                    $storage = $cache ? $cache."/cpcache" : $homedir[$i];
                    
                    if(!$cache) system("rm -rf ".$homedir[$i]."/cpmove-".$usuario[$i]);
                    passthru("/scripts/pkgacct --incremental --skiphomedir ".$usuario[$i]." ".$storage);
                    system("/bin/mkdir -p ".$storage."/cpmove-".$usuario[$i]."/homedir");
                    passthru($rsync." -abvz --backup-dir=".$diretoriossh."/~diario/".$today."/".$usuario[$i]."/ -e '/usr/bin/ssh -F ".$script."/modulos/ssh_auth -p ".$portassh."' ".$storage."/cpmove-".$usuario[$i]."/* ".$usuariossh."@".$ipssh.":".$diretoriossh."/diario/".$usuario[$i]."/");
                    passthru($rsync." -abvz --delete --backup-dir=".$diretoriossh."/~diario/".$today."/".$usuario[$i]."/homedir -e '/usr/bin/ssh -F ".$script."/modulos/ssh_auth -p ".$portassh."' ".$userdir[$i]."/ ".$usuariossh."@".$ipssh.":".$diretoriossh."/diario/".$usuario[$i]."/homedir");
                    if(!$cache) system("rm -rf ".$homedir[$i]."/cpmove-".$usuario[$i]);

                    }                
                    }
                    ++$i;
                    }
                    }      
                    }
                   
                    if ($semanal)
                    {
                   
                    $lasttime = file_get_contents("/var/log/isistemtoolsbackup/AGENDADO");
                    $lastcopy = explode("\n", $lasttime);

                    $agora = $week; $today = date("N"); $tempo = str_replace("SEMANAL:", "", $lastcopy[1]);

                    if ( ($semanal == $today && $tempo != $agora) || (isset($argv[1]) && $argv[1] == "-s") )
                    {

                    $notificacao = true; file_put_contents("/var/log/isistemtoolsbackup/INICIADO","");

                    if(!isset($argv[1])){
                    $lastcron = str_replace($tempo, $agora, $lastcopy[1]);
                    $lasttime = str_replace($lastcopy[1], $lastcron, $lasttime);
                    file_put_contents("/var/log/isistemtoolsbackup/AGENDADO", $lasttime);
                    }
                    
                    system("/usr/bin/ssh -F ".$script."/modulos/ssh_auth -p ".$portassh." ".$usuariossh."@".$ipssh." /bin/mkdir -p ".$diretoriossh."/semanal");
				
                    $i = 1;
                    while ( $i <= $numerocontas )
                    {	
                    $contas1 = str_replace(' ','',$contas);
                    $contas2 = explode(",", $contas1);

                    if($filtro == "0" && !in_array($usuario[$i], $contas2))
                    {
                    echo "Backup está desativado para usuário ".$usuario[$i]."\n";
                    }
                    elseif ($filtro == "1" && in_array($usuario[$i], $contas2))
                    {
                    echo "Backup está desativado para usuário ".$usuario[$i]."\n";
                    }
                    else
                    {

                    $getinfo = posix_getpwnam($usuario[$i]);
                    $userdir[$i] = $getinfo['dir']; 
                    if($userdir[$i] == "") {
                         ++$i;
                         continue;
                    }
                    $homedir[$i] = str_replace("/".$usuario[$i], "", $userdir[$i]); 
                    if($homedir[$i] == "") {
                         ++$i;
                         continue;
                    }

                    if($homedir[$i] != "")
                    {
                    echo "\n[ Backup Semanal do usuario {$usuario[$i]} ]\n\n";

                    $storage = $cache ? $cache."/cpcache" : $homedir[$i];

                    if(!$cache) system("rm -rf ".$homedir[$i]."/cpmove-".$usuario[$i]);
                    passthru("/scripts/pkgacct --incremental --skiphomedir ".$usuario[$i]." ".$storage);
                    system("/bin/mkdir -p ".$storage."/cpmove-".$usuario[$i]."/homedir");
                    passthru($rsync." -abvz --backup-dir=".$diretoriossh."/~semanal/".$week."/".$usuario[$i]."/ -e '/usr/bin/ssh -F ".$script."/modulos/ssh_auth -p ".$portassh."' ".$storage."/cpmove-".$usuario[$i]."/* ".$usuariossh."@".$ipssh.":".$diretoriossh."/semanal/".$usuario[$i]."/");
                    passthru($rsync." -abvz --delete --backup-dir=".$diretoriossh."/~semanal/".$week."/".$usuario[$i]."/homedir -e '/usr/bin/ssh -F ".$script."/modulos/ssh_auth -p ".$portassh."' ".$userdir[$i]."/ ".$usuariossh."@".$ipssh.":".$diretoriossh."/semanal/".$usuario[$i]."/homedir");
                    if(!$cache) system("rm -rf ".$homedir[$i]."/cpmove-".$usuario[$i]);

                    }
                    }
                    ++$i;
                    }
                    }
                    }

                    if($mensal)
                    {

                    $lasttime = file_get_contents("/var/log/isistemtoolsbackup/AGENDADO");
                    $lastcopy = explode("\n", $lasttime);

                    $agora = date("n"); $today = date("j"); $tempo = str_replace("MENSAL:", "", $lastcopy[2]);
                    
                    if ( ($mensal == $today && $agora != $tempo) || (isset($argv[1]) && $argv[1] == "-m") )
                    {

                    $notificacao = true; file_put_contents("/var/log/isistemtoolsbackup/INICIADO","");

                    if(!isset($argv[1])){
                    $lastcron = str_replace($tempo, $agora, $lastcopy[2]);
                    $lasttime = str_replace($lastcopy[2], $lastcron, $lasttime);
                    file_put_contents("/var/log/isistemtoolsbackup/AGENDADO", $lasttime);
                    }

                    system("/usr/bin/ssh -F ".$script."/modulos/ssh_auth -p ".$portassh." ".$usuariossh."@".$ipssh." /bin/mkdir -p ".$diretoriossh."/mensal");

                    $i = 1;
                    while ($i <= $numerocontas)
                    {
                    $contas1 = str_replace(' ','',$contas);
                    $contas2 = explode(",", $contas1);

                    if($filtro == "0" && !in_array($usuario[$i], $contas2))
                    {
                    echo "Backup is disabled for user ".$usuario[$i]."\n";
                    }
                    elseif ($filtro == "1" && in_array($usuario[$i], $contas2))
                    {
                    echo "Backup is disabled for user ".$usuario[$i]."\n";
                    }
                    else
                    {

                    $getinfo = posix_getpwnam($usuario[$i]);
                    $userdir[$i] = $getinfo['dir'];
                    if($userdir[$i] == "") {
                         ++$i;
                         continue;
                    }                   

                    $homedir[$i] = str_replace("/".$usuario[$i], "", $userdir[$i]);
                     if($homedir[$i] == "") {
                         ++$i;
                         continue;
                    }                   

                    if($homedir[$i] != "")
                    {
                    echo "\n[ Backup mensal do usuario {$usuario[$i]} ]\n\n";

                    $storage = $cache ? $cache."/cpcache" : $homedir[$i];

                    if(!$cache) system("rm -rf ".$homedir[$i]."/cpmove-".$usuario[$i]);
                    passthru("/scripts/pkgacct --incremental --skiphomedir ".$usuario[$i]." ".$storage);
                    system("/bin/mkdir -p ".$storage."/cpmove-".$usuario[$i]."/homedir");
                    passthru($rsync." -abvz --backup-dir=".$diretoriossh."/~mensal/".$month."/".$usuario[$i]."/ -e '/usr/bin/ssh -F ".$script."/modulos/ssh_auth -p ".$portassh."' ".$storage."/cpmove-".$usuario[$i]."/* ".$usuariossh."@".$ipssh.":".$diretoriossh."/mensal/".$usuario[$i]."/");
                    passthru($rsync." -abvz --delete --backup-dir=".$diretoriossh."/~mensal/".$month."/".$usuario[$i]."/homedir -e '/usr/bin/ssh -F ".$script."/modulos/ssh_auth -p ".$portassh."' ".$userdir[$i]."/ ".$usuariossh."@".$ipssh.":".$diretoriossh."/mensal/".$usuario[$i]."/homedir");
                    if(!$cache) system("rm -rf ".$homedir[$i]."/cpmove-".$usuario[$i]);

                    }
                    }
                    ++$i;
                    }
                    }
                    }

                    if($notificacao) {
                       echo "\n\nBACKUP COMPLETED!\n\n";
                       notifica_backup( $serverconfig['CONTACTEMAIL'], $serverconfig['HOSTNAME'] );
                       unlink("/var/log/isistemtoolsbackup/INICIADO");
                    }

                    /*
                    else {
                       print "AGORA:".date("H:i");
                       print "HORARIO:".$horario;
                       print "DIFERENCA:".floor((strtotime(date("H:i")) - strtotime($horario))/60);
                    }
                    */

?>