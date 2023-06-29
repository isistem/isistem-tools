<?php

                    error_reporting(0);
                    set_time_limit(0);
                    ignore_user_abort(true);
                    chdir("/usr/local/cpanel/whostmgr/docroot/cgi/isistem-tools/migrador/isistemtoolsbackup/");
                    require "preferencias.php";
                    require "funcoes.php";


                if(!extension_loaded('curl')){

                    echo "Nao foi possivel carregar a extensao PHP cURL. Nao e possivel continuar!\n";
                    echo "Execute o seguinte comando via SSH para compilar cPanel PHP com suporte a cURL:\n";
                    echo "/usr/local/cpanel/scripts/isistemtoolsbackup/manutencao.sh -c";
                    exit();
                }


                    $p = $argv[1];
                    $t = $argv[2];

                    switch($p)
                    {
                    case "-d": $periodo = "diario"; break;
                    case "-s": $periodo = "semanal"; break;
                    case "-m": $periodo = "mensal"; break;
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

                    $TipoSRV = file_get_contents("/var/cpanel/envtype");
                    $CargaCPU = preg_match("/standard/i", $TipoSRV) ? "6.0" : "3.0";

                    $script = "/usr/local/cpanel/whostmgr/docroot/cgi/isistem-tools/migrador/isistemtoolsbackup";
                    $rsync = "/usr/local/cpanel/bin/cpuwatch ".$CargaCPU." /usr/bin/rsync";

                    if(file_exists("/usr/sbin/csf")) shell_exec( "csf -a ".$ipssh );

                    if($t == "-c" && $remocao) system("rm -rf /home/cptmp");
                    system("mkdir -p /home/cptmp");

                    if($t == "-b" || $t == "-c")
                    {

                    system($rsync." -avz --include='/*' --exclude='*' -e '/usr/bin/ssh -F ".$script."/modulos/ssh_auth -p ".$portassh."' ".$usuariossh."@".$ipssh.":".$diretoriossh."/".$periodo."/ /home/cptmp");
                    
                    }


                    if ($handle = opendir("/home/cptmp")) {

                    while (false !== ($file = readdir($handle))) 
                    {

                    if ($file != "." && $file != "..") 
                    {

                    if(is_dir("/home/cptmp/".$file)) 
                    {

                    if(preg_match("/cpmove-/i", $file)) continue;

                    $usuario = $file;
                    $existecpanel = "/var/cpanel/users/".$usuario;
	
                    if ( file_exists( $existecpanel ) )
                    {
                    echo "\n\nUser account {$usuario} ja existe no servidor.\nVoce deve excluir a conta para que voce possa restaurar o backup!";
                    continue;
                    }


                    echo "\n[ RESTORE OF USER $usuario ]\n\n";

                    if($t == "-c" && $remocao) system("rm -rf /home/cptmp/cpmove-".$usuario);
                    system("mkdir -p /home/cptmp/cpmove-".$usuario);

                    if($t == "-b" || $t == "-c")
                    {
                    echo "fazendo download dos arquivos...\n\n";
                    system($rsync." -avz -e '/usr/bin/ssh -F ".$script."/modulos/ssh_auth -p ".$portassh."' ".$usuariossh."@".$ipssh.":".$diretoriossh."/".$periodo."/".$usuario."/ /home/cptmp/cpmove-".$usuario);
                    }

                    if($t == "-r" || $t == "-c")
                    {

                    echo "Preparando arquivos para restaurar...\n\n";

                    system("cd /home/cptmp;tar --exclude=/home/cptmp/cpmove-".$usuario."/homedir -cvf /home/cpmove-".$usuario.".tar cpmove-".$usuario."/");

                    echo "Restaurando a conta...\n\n";

                    system("/scripts/restorepkg /home/cpmove-".$usuario.".tar");

                    $getinfo = posix_getpwnam($usuario); $falha = false;
                    $userdir = $getinfo['dir']; if($userdir == "") $falha = true;
                    $homedir = str_replace("/".$usuario, "", $userdir); if($homedir == "") $falha = true;
                
                    if($falha){
                    echo "Nao e possivel restaurar a conta de usuario {$usuario}\n";
                    continue;
                    }

                    system("mkdir -p /home/cpmove/cptmp-".$usuario);
                    system("mv -f ".$homedir."/".$usuario." /home/cpmove/cptmp-".$usuario);
                    system("mv -f /home/cptmp/cpmove-".$usuario."/homedir ".$homedir."/".$usuario);
                    system("chown -R ".$usuario.".".$usuario." ".$homedir."/".$usuario.";chown ".$usuario.".nobody ".$homedir."/".$usuario."/public_html");
                    system("find ".$homedir."/".$usuario."/public_html -type d -exec chmod 755 {} \;");
                    system("find ".$homedir."/".$usuario."/public_html -type f -exec chmod 644 {} \;");

                    system("rm -rf /home/cpmove/cptmp-".$usuario);
                    if ($remocao) system("rm -rf /home/cptmp/cpmove-".$usuario);
                    system("rm -f /home/cpmove-".$usuario.".tar");
                

                    }

                    }

                    }

                    }

                    closedir($handle);

                    }

                    if($t == "-c" && $remocao) system("/bin/rm -rf /home/cptmp");

                    echo "\n\nRESTORE COMPLETED!\n\n";
                    notifica_restauracao( $serverconfig['CONTACTEMAIL'], $serverconfig['HOSTNAME'] );

?>