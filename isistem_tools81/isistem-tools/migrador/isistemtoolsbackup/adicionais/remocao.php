<?php

                error_reporting(0);
                set_time_limit(0);
                ignore_user_abort(true);
                chdir("/usr/local/cpanel/whostmgr/docroot/cgi/isistem-tools/migrador/isistemtoolsbackup/");
                require "funcoes.php";
                require "preferencias.php";

                if(!extension_loaded('curl')){

                    echo "Nao foi possivel carregar a extensao PHP cURL. Nao e possivel continuar!\n";
                    echo "Execute o seguinte comando via SSH para compilar cPanel PHP com suporte a cURL:\n";
                    echo "/usr/local/cpanel/scripts/isistemtoolsbackup/manutencao.sh -c";
                    exit();
                }

                if(empty($licenca)){
                die("Voce nao tem uma licenca Isistem Tools Backup!");
                }

                verifica_licenca($licenca);

                $p = $argv[1];

                switch($p)
                {
                case "-d": $periodo = "diario"; break;
                case "-s": $periodo = "semanal"; break;
                case "-m": $periodo = "mensal"; break;
                }

                system("/usr/bin/ssh -F /usr/local/cpanel/whostmgr/docroot/cgi/isistem-tools/migrador/isistemtoolsbackup/modulos/ssh_auth -p ".$portassh." ".$usuariossh."@".$ipssh." /bin/rm -rf ".$diretoriossh."/".$periodo."/*");

                system("/usr/bin/ssh -F /usr/local/cpanel/whostmgr/docroot/cgi/isistem-tools/migrador/isistemtoolsbackup/modulos/ssh_auth -p ".$portassh." ".$usuariossh."@".$ipssh." /bin/rm -rf ".$diretoriossh."/~".$periodo."/*");

                echo "A remocao foi completada.\n";

?>