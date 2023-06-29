|<?php

            require("funcoes.php");
            require_once("xmlapi.php");
            require ("preferencias.php");


                if(!extension_loaded('curl')){

                    echo "<h5 class='ui red header'>Não foi possível carregar a extensão cURL do PHP. Não é possível continuar a operação!</div>";
                    echo "<h5 class='ui red header'><br>Execute o seguinte comando via SSH para compilar o cPanel PHP com suporte ao cURL:";
                    echo "<br>/usr/local/cpanel/scripts/isistemtoolsbackup/manutencao.sh -c<br></div>";
                    include("rodape.php");
                    exit();
                }

            if(!function_exists('file_put_contents')) die("<h5 class='ui red header'><br>A função file_put_contents() está desabilitada!</div>");
            if(!function_exists('system')) die("<h5 class='ui red header'><br>A função system() está desabilitada!</div>");
            if(!function_exists('passthru')) die("<h5 class='ui red header'><br>A função passthru() está desabilitada!</div>");

                /***
                ***
                ***    Verificar licenca isistem tools
                ***
                ***
                ***/


	    $BackGround = $rodando;
	    $ChooseTime = isset($_POST['agendar']) ? TRUE : FALSE;
	    $WaitTime = isset($_POST['tempo']) ? $_POST['tempo'] : "0";

	    $ChangeOwner = isset($_POST['mudar']) ? TRUE : FALSE;
	    $AccountOwner = isset($_POST['revenda']) ? $_POST['revenda'] : $MigraUsr;
	    if ($_ENV['REMOTE_USER'] != 'root') {
	    	$ChangeOwner = TRUE;
	    	$AccountOwner = $_ENV['REMOTE_USER'];
	    }
	    $Forced = isset($_POST['forcado']) ? TRUE : FALSE;
            $IfExists = isset($_POST['existente']) ? $_POST['existente'] : $MigraUsr;
	    $BkpDelete = $remocao;

	    if($BackGround || $ChooseTime) ignore_user_abort(true);

	    $Account = array();
	    $Account['domain'] = $_POST['dominio'];
	    $Account['ip'] = gethostbyname( $Account['domain'] );
	    $Account['user'] = $_POST['usuario'];
	    $Account['pass'] = $_POST['senha'];

	    $LogFile = "log.txt";
	    $SSL = TRUE;
	    $Protocol = "https://";
	    $WhmPort = "2087";
	    $CpPort = "2083";

	    $IP = $Account['ip'];
	    $LiberaFW = FALSE;

	    if ( $Account['ip'] == "" || $Account['user'] == "" || $Account['pass'] == "" )
	    {
    	      echo "<h5 class='ui red header'>Você não preencheu algum campo do formulário!<br></div>";
    	      include( "rodape.php" );
    	      exit( );
	    }

	    if($ChangeOwner){
	    $ContaUsr = $AccountOwner;
	    
	    }
	    else{
	    if($MigraUsr != "root"): $ContaUsr = $MigraUsr;
	    else: $ContaUsr = "MESMO"; endif;

	    }

	    if ($ChooseTime)
	    {

	    if (empty($WaitTime))
	    {
	    echo "<h5 class='ui red header'>Você escolheu agendar a migração mas não definiu o tempo de espera para a migração começar!<br></div>";
	    include( "rodape.php" );
	    exit( );
	    }

	    if (is_numeric($WaitTime)===FALSE)
	    {
	    echo "<h5 class='ui red header'>O tempo de espera definido para a migração começar é inválido!<br></div>";
	    include( "rodape.php" );
	    exit( );
	    }

	    }

	    if ($ChangeOwner)
	    {

	    if (empty($AccountOwner))
	    {
	    echo "<h5 class='ui red header'>Você escolheu mover as contas migradas pra outra revenda mas não definiu o nome de usuário!<br></div>";
	    include( "rodape.php" );
	    exit( );
	    }

	    $existecpanel = "/var/cpanel/users/".$AccountOwner."";
	    if ( !file_exists( $existecpanel ) && $AccountOwner != "root")
	    {
	    echo "<h5 class='ui red header'>Você escolheu mover as contas migradas pra uma revenda que não existe!<br></div>";
	    include( "rodape.php" );
	    exit( );
	    }
	    }

	    $ExistecPanel = "/var/cpanel/users/".$Account['user']."";

            if ( !$IfExists && file_exists($ExistecPanel) )
            {
	    echo "<h5 class='ui red header'>Já existe uma conta neste servidor com o mesmo nome de usuário!<br></div>";
	    include( "rodape.php" );
	    exit( );
	    }


	    system("chmod 755 /usr/local/cpanel/whostmgr/docroot/cgi/isistem-tools/migrador/isistemtoolsbackup/solicitar.so");
	    system("chmod 755 /usr/local/cpanel/whostmgr/docroot/cgi/isistem-tools/migrador/isistemtoolsbackup/exigir.so");
	    system("chmod 755 /usr/local/cpanel/whostmgr/docroot/cgi/isistem-tools/migrador/isistemtoolsbackup/incluir.so");


            echo "Migrando conta {$Account['user']} @ {$Account['domain']}<br>";

	    if(file_exists("/usr/sbin/csf"))
	    {
	    echo "<br>Permitindo o IP do servidor remoto no firewall local... ";
	    $resultado = shell_exec( "csf -a ".$Account['ip'] );
	    if ( preg_match( "/Adding/i", $resultado ) || preg_match( "/already in the allow file/i", $resultado ) )
	    {
	    echo "OK.<br>";
	    $LiberaFW = TRUE;
	    }
	    else
	    {
	    echo "<br>Não não foi possível permitir o IP no firewall!<br>";
	    }
	    }

            if ($BackGround || $ChooseTime) echo "<br>Se a janela do navegador fechar/A migração continuará em segundo plano.<br>";

            else  echo "<br>If close browser window/tab migration stops.<br>";


	    if ($ChooseTime)
	    {
	    echo "Conexão com cPanel... ";
	    $acctPage = getByCurl( "{$Protocol}{$Account['ip']}:{$CpPort}/", $Account['user'], $Account['pass'] );

	    if ( !$acctPage )
	    {
	    echo "<h5 class='ui red header'><br>Unable to connect to cPanel!<br>Verify if domain or IP is correct or if there is a firewall blocking access.<br></div>";
	    include( "rodape.php" );
	    exit( );
	    }

	    if ( preg_match( "/Login/", $acctPage ) )
	    {
	    echo "<h5 class='ui red header'><br>Não é possível fazer login para cPanel!<br>Certifique-se de nome de usuário e senha estão corretos.<br></div>";
	    include( "rodape.php" );
	    exit( );
	    }

	    echo "OK.<br>";
            echo "<br>A migração só irá ser iniciado em {$WaitTime} minutos.<br>";
            echo "Se desejar, você pode fechar esta janela.<br><br>";

	    $WaitTime = 60 * $WaitTime;
	    sleep($WaitTime);
	    }

	    if ($Forced)
	    {
	    echo "Saving settings... ";
	    $arquivo = "/usr/local/cpanel/whostmgr/docroot/cgi/isistem-tools/migrador/isistemtoolsbackup/modulos/config.pm";
	    if ( !( $fh = fopen( $arquivo, "w" ) ) )
	    {
	    exit( "<h5 class='ui red header'><br>Não é possível abrir arquivo de configuração!<br></div>" );
	    }
	    $dados = "#!/usr/bin/perl \nour \$senha_cpanel = '{$Account['pass']}';\n1;";
	    fwrite( $fh, $dados );
	    fclose( $fh );
	    echo "OK.<br>";
	    }

	    echo "<br>Conexão com cPanel... ";
	    $acctsPage = getByCurl( "{$Protocol}{$Account['ip']}:{$CpPort}/", $Account['user'], $Account['pass'] );

	    if ( !$acctsPage )
	    {
	    echo "<h5 class='ui red header'><br>Impossível logar no cPanel!<br>Verifique se o domínio ou IP está correto ou se há um acesso firewall bloqueando.<br></div>";
	    include( "rodape.php" );
	    exit( );
	    }
	    if ( preg_match( "/Login/", $acctsPage ) )
	    {
	    echo "<h5 class='ui red header'><br>Não é possível fazer login para cPanel!<br>Certifique-se de nome de usuário e senha estão corretos.<br></div>";
	    include( "rodape.php" );
	    exit( );
	    }

	    echo "OK.<br>";

	    if ( $Forced )
	    {
	    echo "<br>";
            deleta_backup();
            $Account['pass'] = base64_encode( $Account['pass'] );

	    passthru( "/usr/local/cpanel/whostmgr/docroot/cgi/isistem-tools/migrador/isistemtoolsbackup/exigir.so {$Account['ip']} {$Account['user']} {$Account['domain']} {$Account['pass']} {$ContaUsr} {$MigraUsr}" );
	    }
	    else
	    {
            deleta_backup();
            $Account['pass'] = base64_encode( $Account['pass'] );

            passthru( "/usr/local/cpanel/whostmgr/docroot/cgi/isistem-tools/migrador/isistemtoolsbackup/solicitar.so {$Account['ip']} {$Account['user']} {$Account['domain']} {$Account['pass']} {$ContaUsr} {$MigraUsr}" );
	    }

	    echo "<br>Migração da conta {$Account['user']} @ {$Account['ip']} concluída.<br>";
	    include( "rodape.php" );

?>