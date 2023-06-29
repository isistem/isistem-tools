<?php

                error_reporting(0);
                set_time_limit(0);
                ignore_user_abort(true);
                chdir("/usr/local/cpanel/whostmgr/docroot/cgi/isistem-tools/migrador/isistemtoolsbackup/");
                require "preferencias.php";
                require("xmlapi.php");
                require "funcoes.php";

                if(!extension_loaded('curl')){

                    echo "Nao foi possivel carregar a extensao PHP cURL. Nao e possivel continuar!\n";
                    echo "Execute o seguinte comando via SSH para compilar cPanel PHP com suporte a cURL:\n";
                    echo "/usr/local/cpanel/scripts/isistemtoolsbackup/manutencao.sh -c";
                    exit();
                }

                if(empty($licenca)){
                die("Voce não tem uma licenca Isistem Tools Backup!");
                }

                verifica_licenca($licenca);


                $p = $argv[1];
                $t = $argv[2];
                $data = $argv[3];
                $hora = isset($argv[4]) ? $argv[4] : false;

                if($diario != "1" && !$hora) $hora = "0";


                switch($p)
                {
                case "-d": $periodo = "diario";
                break; 
                case "-s": $periodo = "semanal";;
                break;
                case "-m": $periodo = "mensal";
                break;
                }

                if($periodo == "" || $data == "")
                {
                echo "Voce nao escolheu a hora e o dia!\n";
                exit();
                }

                switch($t)
                {
                case "-t": $tipo = "total";
                break;
                case "-w": $tipo = "www";
                break;
                case "-m": $tipo = "mail";
                break;
                case "-q": $tipo = "mysql";
                break;
                case "-d": $tipo = "dns";
                break;
                case "-a": $tipo = "file";
                break;
                }

                $i=0; $week=0;
                if (date("N", mktime(0, 0, 0, date("n"), 1, date("Y"))) <= "6") $week++;
                while ($i <= date("j")) {
                if (date("N", mktime(0, 0, 0, date("n"), $i, date("Y"))) == "7") $week++;
                $i++;
                }


                if($periodo == "diario"){
                if($diario != "1" && $hora) $data = $data."/".$hora;
                }
                if($periodo == "semanal") $data = $week;

                $TipoSRV = file_get_contents("/var/cpanel/envtype");
                $CargaCPU = preg_match("/standard/i", $TipoSRV) ? "6.0" : "3.0";

                $SQLinfo = explode("\n", file_get_contents("sql.ini"));
                $senhasql = $SQLinfo['0'];
                $SENHASQL = $SQLinfo['1'];

                $script = "/usr/local/cpanel/whostmgr/docroot/cgi/isistem-tools/migrador/isistemtoolsbackup";
                $rsync = "/usr/local/cpanel/bin/cpuwatch ".$CargaCPU." /usr/bin/rsync";

                if(file_exists("/usr/sbin/csf")) shell_exec( "csf -a ".$ipssh );

                $access_hash = str_replace('\n', '', @file_get_contents('/root/token.txt'));
                $xmlapi = new xmlapi('127.0.0.1'); 
                $xmlapi->hash_auth('root', $access_hash);
                $xmlapi->set_port('2087'); 
                $xmlapi->set_output('array'); 
                $acctlist = $xmlapi->listaccts();

                if (!isset($acctlist['acct'])) $acctlist = list_users();

                foreach($acctlist['acct'] as $field => $user)
                {

                $usuario = $user['user']; 
                
                echo "Recuperando arquivos da conta do usuario {$usuario}...\n\n";

                $getinfo = posix_getpwnam($usuario); $falha = false;
                $userdir = $getinfo['dir']; if($userdir == "") $falha = true;
                $homedir = str_replace("/".$usuario, "", $userdir); if($homedir == "") $falha = true;
                
                if($falha){
                echo "Incapaz de reverter arquivos da conta do usuario {$usuario}\n\n";
                continue;
                }

                if($tipo == "total"){

                system($rsync." -avz -e '/usr/bin/ssh -F ".$script."/modulos/ssh_auth -p ".$portassh."' ".$usuariossh."@".$ipssh.":".$diretoriossh."/".$periodo."/".$usuario."/homedir/ ".$homedir."/".$usuario);

        if ($diario != "1" && $hora) {
                for ($tempo=0;$tempo<=$hora;$tempo++) {
                system($rsync." -avz -e '/usr/bin/ssh -F ".$script."/modulos/ssh_auth -p ".$portassh."' ".$usuariossh."@".$ipssh.":".$diretoriossh."/~".$periodo."/".$_POST['data1']."/".$tempo."/".$usuario."/homedir/ ".$homedir."/".$usuario);
                }
        }
        else {
                for ($dia=1;$dia<=$data;$dia++) {
                system($rsync." -avz -e '/usr/bin/ssh -F ".$script."/modulos/ssh_auth -p ".$portassh."' ".$usuariossh."@".$ipssh.":".$diretoriossh."/~".$periodo."/".$dia."/".$usuario."/homedir/ ".$homedir."/".$usuario);
                }
        }

                system("chown -R ".$usuario.".".$usuario." ".$homedir."/".$usuario.";chown ".$usuario.".nobody ".$homedir."/".$usuario."/public_html");

                system("find ".$homedir."/".$usuario."/public_html -type d -exec chmod 755 {} \;");
                system("find ".$homedir."/".$usuario."/public_html -type f -exec chmod 644 {} \;");

                system("rm -rf /tmp/isistemtoolsbackup/valiases");
                system("mkdir -p /tmp/isistemtoolsbackup/valiases");

                system($rsync." -avz -e '/usr/bin/ssh -F ".$script."/modulos/ssh_auth -p ".$portassh."' ".$usuariossh."@".$ipssh.":".$diretoriossh."/".$periodo."/".$usuario."/va/ /tmp/isistemtoolsbackup/valiases");
                system($rsync." -avz -e '/usr/bin/ssh -F ".$script."/modulos/ssh_auth -p ".$portassh."' ".$usuariossh."@".$ipssh.":".$diretoriossh."/".$periodo."/".$data."/".$usuario."/va/ /tmp/isistemtoolsbackup/valiases");

                system("chown ".$usuario.".mail /tmp/isistemtoolsbackup/valiases/*");
                system("chmod 640 /tmp/isistemtoolsbackup/valiases/*");
                system("mv -f /tmp/isistemtoolsbackup/valiases/* /etc/valiases");

                system("chown -R ".$usuario.".".$usuario." ".$homedir."/".$usuario."/mail");
                system("chmod 751 ".$homedir."/".$usuario."/mail");

                system("/scripts/mailperm {$usuario}");

                }


                if($tipo == "file"){

                system($rsync." -avz -e '/usr/bin/ssh -F ".$script."/modulos/ssh_auth -p ".$portassh."' ".$usuariossh."@".$ipssh.":".$diretoriossh."/".$periodo."/".$usuario."/homedir/ ".$homedir."/".$usuario);

        if ($diario != "1" && $hora) {
                for ($tempo=0;$tempo<=$hora;$tempo++) {
                system($rsync." -avz -e '/usr/bin/ssh -F ".$script."/modulos/ssh_auth -p ".$portassh."' ".$usuariossh."@".$ipssh.":".$diretoriossh."/~".$periodo."/".$_POST['data1']."/".$tempo."/".$usuario."/homedir/ ".$homedir."/".$usuario);
                }
        }
        else {
                for ($dia=1;$dia<=$data;$dia++) {
                system($rsync." -avz -e '/usr/bin/ssh -F ".$script."/modulos/ssh_auth -p ".$portassh."' ".$usuariossh."@".$ipssh.":".$diretoriossh."/~".$periodo."/".$dia."/".$usuario."/homedir/ ".$homedir."/".$usuario);
                }
        }

                system("chown -R ".$usuario.".".$usuario." ".$homedir."/".$usuario.";chown ".$usuario.".nobody ".$homedir."/".$usuario."/public_html");

                system("find ".$homedir."/".$usuario."/public_html -type d -exec chmod 755 {} \;");
                system("find ".$homedir."/".$usuario."/public_html -type f -exec chmod 644 {} \;");

                system("rm -rf /tmp/isistemtoolsbackup/valiases");
                system("mkdir -p /tmp/isistemtoolsbackup/valiases");

                system("chown -R ".$usuario.".".$usuario." ".$homedir."/".$usuario."/mail");
                system("chmod 751 ".$homedir."/".$usuario."/mail");

                system("/scripts/mailperm {$usuario}");

                }


                if($tipo == "mysql" || $tipo == "total"){

                system("rm -rf /tmp/isistemtoolsbackup/mysql");
                system("mkdir -p /tmp/isistemtoolsbackup/mysql");

                system($rsync." -avz -e '/usr/bin/ssh -F ".$script."/modulos/ssh_auth -p ".$portassh."' ".$usuariossh."@".$ipssh.":".$diretoriossh."/".$periodo."/".$usuario."/mysql/ /tmp/isistemtoolsbackup/mysql");
                system($rsync." -avz -e '/usr/bin/ssh -F ".$script."/modulos/ssh_auth -p ".$portassh."' ".$usuariossh."@".$ipssh.":".$diretoriossh."/".$periodo."/".$data."/".$usuario."/mysql/ /tmp/isistemtoolsbackup/mysql");

                foreach ( glob("/tmp/isistemtoolsbackup/mysql/".$usuario."_*.sql") as $sqldb ) {
                $sql = str_replace( array("/tmp/isistemtoolsbackup/mysql/",".sql"), "", $sqldb );
                system("mysql -uisistemtoolsbackup -p\"{$senhasql}\" -e \"DROP DATABASE IF EXISTS {$sql};\"");
                system("mysql -uisistemtoolsbackup -p\"{$senhasql}\" -e \"CREATE DATABASE IF NOT EXISTS {$sql};\"");
                system("mysql -uisistemtoolsbackup -p\"{$senhasql}\" {$sql} < {$sqldb}");
                } 
               
                }


                if($tipo == "dns" || $tipo == "total"){

                system("rm -rf /tmp/isistemtoolsbackup/dnszones");
                system("mkdir -p /tmp/isistemtoolsbackup/dnszones");

                system($rsync." -avz -e '/usr/bin/ssh -F ".$script."/modulos/ssh_auth -p ".$portassh."' ".$usuariossh."@".$ipssh.":".$diretoriossh."/".$periodo."/".$usuario."/dnszones/ /tmp/isistemtoolsbackup/dnszones");
                system($rsync." -avz -e '/usr/bin/ssh -F ".$script."/modulos/ssh_auth -p ".$portassh."' ".$usuariossh."@".$ipssh.":".$diretoriossh."/".$periodo."/".$data."/".$usuario."/dnszones/ /tmp/isistemtoolsbackup/dnszones");

                system("chown named.named /tmp/isistemtoolsbackup/dnszones/*");
                system("chmod 600 /tmp/isistemtoolsbackup/dnszones/*");
                system("mv -f /tmp/isistemtoolsbackup/dnszones/* /var/named");
               
                }

                if($tipo == "mail"){

                system("rm -rf /tmp/isistemtoolsbackup/valiases");
                system("mkdir -p /tmp/isistemtoolsbackup/valiases");

                system($rsync." -avz -e '/usr/bin/ssh -F ".$script."/modulos/ssh_auth -p ".$portassh."' ".$usuariossh."@".$ipssh.":".$diretoriossh."/".$periodo."/".$usuario."/va/ /tmp/isistemtoolsbackup/valiases");
                system($rsync." -avz -e '/usr/bin/ssh -F ".$script."/modulos/ssh_auth -p ".$portassh."' ".$usuariossh."@".$ipssh.":".$diretoriossh."/".$periodo."/".$data."/".$usuario."/va/ /tmp/isistemtoolsbackup/valiases");

                system("chown ".$usuario.".mail /tmp/isistemtoolsbackup/valiases/*");
                system("chmod 640 /tmp/isistemtoolsbackup/valiases/*");
                system("mv -f /tmp/isistemtoolsbackup/valiases/* /etc/valiases");

                system($rsync." -avz -e '/usr/bin/ssh -F ".$script."/modulos/ssh_auth -p ".$portassh."' ".$usuariossh."@".$ipssh.":".$diretoriossh."/".$periodo."/".$usuario."/homedir/mail/ ".$homedir."/".$usuario."/mail");
                system($rsync." -avz -e '/usr/bin/ssh -F ".$script."/modulos/ssh_auth -p ".$portassh."' ".$usuariossh."@".$ipssh.":".$diretoriossh."/~".$periodo."/".$data."/".$usuario."/homedir/mail/ ".$homedir."/".$usuario."/mail");

                system("chown -R ".$usuario.".".$usuario." ".$homedir."/".$usuario."/mail");
                system("chmod 751 ".$homedir."/".$usuario."/mail");

                system("/scripts/mailperm {$usuario}");
               
                }


                if($tipo == "www"){

                system($rsync." -avz -e '/usr/bin/ssh -F ".$script."/modulos/ssh_auth -p ".$portassh."' ".$usuariossh."@".$ipssh.":".$diretoriossh."/".$periodo."/".$usuario."/homedir/public_html/ ".$homedir."/".$usuario."/public_html");

        if ($diario != "1" && $hora) {
                for ($tempo=0;$tempo<=$hora;$tempo++) {
                system($rsync." -avz -e '/usr/bin/ssh -F ".$script."/modulos/ssh_auth -p ".$portassh."' ".$usuariossh."@".$ipssh.":".$diretoriossh."/~".$periodo."/".$_POST['data1']."/".$tempo."/".$usuario."/homedir/public_html/ ".$homedir."/".$usuario."/public_html");
                }
        }
        else {
                for ($dia=1;$dia<=$data;$dia++) {
                system($rsync." -avz -e '/usr/bin/ssh -F ".$script."/modulos/ssh_auth -p ".$portassh."' ".$usuariossh."@".$ipssh.":".$diretoriossh."/~".$periodo."/".$dia."/".$usuario."/homedir/public_html/ ".$homedir."/".$usuario."/public_html");
                }
        }

                system("chown -R ".$usuario.".".$usuario." ".$homedir."/".$usuario."/public_html");
                system("chown ".$usuario.".nobody ".$homedir."/".$usuario."/public_html");
                system("chmod 750 ".$homedir."/".$usuario."/public_html");

                system("find ".$homedir."/".$usuario."/public_html -type d -exec chmod 755 {} \;");
                system("find ".$homedir."/".$usuario."/public_html -type f -exec chmod 644 {} \;"); 

                }

                echo "\n\n";

                }
                

                echo "Reversao concluida.\n";

?>