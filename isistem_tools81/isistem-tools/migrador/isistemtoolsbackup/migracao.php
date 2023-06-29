<?php

            require("funcoes.php");
            include_once("xmlapi.php");
            require ("preferencias.php");
            error_reporting(0);
            ini_set("display_errors", "Off");

                if(!extension_loaded('curl')){

                    echo "<h5 class='ui red header'>Could not load the cURL extension of PHP. Unable to continue!</h5>";
                    echo "<h5 class='ui red header'><br>Run the following command via SSH to compile cPanel PHP with support cURL:";
                    echo "<br>/usr/local/cpanel/scripts/isistemtoolsbackup/manutencao.sh -c<br></h5>";
                    include("rodape.php");
                    exit();
                }

            if(!function_exists('file_put_contents')) die("<h5 class='ui red header'><br>The file_put_contents() function is disabled!</h5>");
            if(!function_exists('system')) die("<h5 class='ui red header'><br>The system() function is disabled!</h5>");
            if(!function_exists('passthru')) die("<h5 class='ui red header'><br>The passthru() function is disabled!</h5>");

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
          
            
	    $Forced = isset($_POST['forcado']) ? TRUE : FALSE;
            $IfExists = isset($_POST['existente']) ? $_POST['existente'] : $MigraUsr;
	    $BkpDelete = $remocao;

            if($BackGround || $ChooseTime) ignore_user_abort(true);

            $Reseller = array();
            $Reseller['domain'] = $_POST['dominio']; $search = array("http", "//", "/whm", ":", "2087"); $Reseller['domain'] = str_replace($search, "", $Reseller['domain']);
            $Reseller['ip'] = gethostbyname( $Reseller['domain'] );
            $Reseller['user'] = $_POST['usuario'];
            $Reseller['pass'] = $_POST['senha'];
            $ChooseAccs = $_POST['filtro'];
            $CpanelAccs = $_POST['contas'];

            $LogFile = "log.txt";
            $SSL = TRUE;
            $Protocol = "https://";
            $WhmPort = "2087";
            $CpPort = "2083";

            $IP = $Reseller['ip'];
            $LiberaFW = FALSE;

            $AcctIN = FALSE;
            $AcctEX = FALSE;

            switch($ChooseAccs)
            {

            case "IN":
            $AcctIN = TRUE;
            break;

            case "EX":
            $AcctEX = TRUE;
            break;

            }

            if($Reseller['ip'] == "" || $Reseller['user'] == "" || $Reseller['pass'] == "")
            {
            echo "<h5 class='ui red header'>Você não preencheu um dos campos do formulário!<br></h5>";
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

            if ( ( $ChooseAccs == "IN" || $ChooseAccs == "EX" ) && empty( $CpanelAccs ) )
            {
            echo "<h5 class='ui red header'>Você escolheu quais contas para migrar, mas não inseriu os nomes de usuários!<br></h5>";
            include( "rodape.php" );
            exit( );
            }

            if ($ChooseTime)
            {

            if (empty($WaitTime))
            {
            echo "<h5 class='ui red header'>Você escolheu agendar a migração mas não definiu o tempo de espera para a migração começar!<br></h5>";
            include( "rodape.php" );
            exit( );
            }

            if (is_numeric($WaitTime)===FALSE)
            {
            echo "<h5 class='ui red header'>O tempo de espera definido para a migração começar é inválido!<br></h5>";
            include( "rodape.php" );
            exit( );
            }

            }

            if ($ChangeOwner)
            {

            if (empty($AccountOwner))
            {
            echo "<h5 class='ui red header'>Você escolheu mover as contas migradas pra outra revenda mas não definiu o nome de usuário!<br></h5>";
            include( "rodape.php" );
            exit( );
            }

            $existecpanel = "/var/cpanel/users/".$AccountOwner."";
            if ( !file_exists( $existecpanel ) && $AccountOwner != "root")
            {
            echo "<h5 class='ui red header'>Você escolheu mover as contas migradas pra uma revenda que não existe!<br></h5>";
            include( "rodape.php" );
            exit( );
            }
            }


            system("chmod 755 /usr/local/cpanel/whostmgr/docroot/cgi/isistem-tools/migrador/isistemtoolsbackup/solicitar.so");
            system("chmod 755 /usr/local/cpanel/whostmgr/docroot/cgi/isistem-tools/migrador/isistemtoolsbackup/exigir.so");
            system("chmod 755 /usr/local/cpanel/whostmgr/docroot/cgi/isistem-tools/migrador/isistemtoolsbackup/incluir.so");

            echo "Migrando revenda WHM {$Reseller['user']} @ {$Reseller['ip']}<br>";

            if(file_exists("/usr/sbin/csf"))
            {
            echo "<br>Permitindo IP do servidor remoto no firewall local ... ";
            $resultado = shell_exec( "csf -a ".$Reseller['ip'] );
            if ( preg_match( "/Adding/i", $resultado ) || preg_match( "/already in the allow file/i", $resultado ) )
            {
            echo "OK.<br>";
            $LiberaFW = TRUE;
            }
            else
            {
            echo "<br>Não foi possivel liberar o IP no firewall!<br>";
            }
            }

            if ($BackGround || $ChooseTime) echo "<br>Se fechar a janela do navegador a migração irá continuar.<br>";

            else  echo "<br>Se fechar janela do navegador a migração continua.<br>";


            if ($ChooseTime)
            {
            echo "<br>Conectando-se a Web Host Manager ... ";
            $AccountPage = getByCurl( "{$Protocol}{$Reseller['ip']}:{$WhmPort}", $Reseller['user'], $Reseller['pass'] );
    
            if ( !$AccountPage )
            {
            echo "<h5 class='ui red header'><br>Impossível conectar ao WHM!<br>Verifique se o domínio ou IP está correto ou se há um firewall bloqueando o acesso.<br></h5>"; 
            include( "rodape.php" );
            exit( );
            }
            if ( preg_match( "/Login/i", $AccountPage ) )
            {
            echo "<h5 class='ui red header'><br>Impossível fazer login no WHM!<br>Certifique-se de nome de usuário e senha estão corretos.<br></h5>";
            include( "rodape.php" );
            exit( );
            }

            echo "OK.<br>";
            echo "<br>A migração só será iniciado em {$WaitTime} minutos.<br>";
            echo "Se desejar, você pode fechar esta janela.<br><br>";

            $WaitTime = 60 * $WaitTime;
            sleep($WaitTime);
            }

            if ($Forced)
            {
            echo "Salvando as configurações ... ";
            $arquivo = "/usr/local/cpanel/whostmgr/docroot/cgi/isistem-tools/migrador/isistemtoolsbackup/modulos/config.pm";
            if ( !( $fh = fopen( $arquivo, "w" ) ) )
            {
            exit( "<h5 class='ui red header'><br>Não é possível abrir arquivo de configuração!<br></h5>" );
            }
            $dados = "#!/usr/bin/perl \nour \$senha_cpanel = '{$Reseller['pass']}';\n1;";
            fwrite( $fh, $dados );
            fclose( $fh );
            echo "OK.<br><br>";
            }

            echo "<br>Conectando-se a Web Host Manager ... ";

            $AccountsPage = getByCurl( "{$Protocol}{$Reseller['ip']}:{$WhmPort}", $Reseller['user'], $Reseller['pass'] );

            if ( !$AccountsPage )
            {
            echo "<h5 class='ui red header'><br>Impossível conectar ao WHM!<br>Verifique se o domínio ou IP está correto ou se há um firewall bloqueando o acesso.<br></h5>";
            include( "rodape.php" );
            exit( );
            }

            if ( preg_match( "/Login/i", $AccountsPage ) )
            {
            echo "<h5 class='ui red header'><br>Impossível fazer login no WHM!<br>Certifique-se de nome de usuário e senha estão corretos.<br></h5>";
            include( "rodape.php" );
            exit( );
            }

            echo "OK.<br>";
        
            $xmlapi = new xmlapi($Reseller['ip']); 
            $xmlapi->password_auth($Reseller['user'], $Reseller['pass']);
            $xmlapi->set_port('2087'); 
            $xmlapi->set_output('array'); 
            $userlist = $xmlapi->listaccts(); 
            if(isset($userlist['acct']['disklimit'])) $userlist['acct'] = array($userlist['acct']);
            if(isset($userlist['data']['result']) && $userlist['data']['result']=="0") die("<h5 class='ui red header'>Impossível usar cPanel API para obter a lista de contas: acesso negado!</h5>");

            echo "Lista de cPanel Conseguir contas sob o revendedor ... ";
            $numero_contas = count($userlist['acct']);

            echo "OK.<br>Encontramos {$numero_contas} contas cPanel.<br>";
           
            $BackupSucesso = array(); $BackupFalha = array();
            $RestauraSucesso = array(); $RestauraFalha = array();
            $RestauraErro = array(); $BackupErro = array();
            
            $ListaContas = array();
            if($AcctIN || $AcctEX)
            {
            $CpanelAccs = str_replace(" ", "", $CpanelAccs);
            $ListaContas = explode(",", $CpanelAccs);
            if($AcctIN): $ApenasContas = count($ListaContas); else: $ApenasContas = $numero_contas - count($ListaContas); endif; 
            echo "Apenas contas selecionadas serão copiados.<br>";
            echo "Você escolheu {$ApenasContas} apenas contas.<br><br>";
            }
            else
            {
            echo "Todas as contas serão incluídas no backup.<br><br>";
            }
            
            if($AcctIN || $AcctEX){

            $ListaUsuarios = array();

            foreach ($userlist['acct'] as $field => $Account) 
            {

            $ListaUsuarios[]= $Account['user'];

            }

           $ContasExcluidas = array();  

           foreach($ListaContas as $ContaLista){

           if(!in_array($ContaLista, $ListaUsuarios)){

           $ContasExcluidas[]= $ContaLista;             
           echo "<h5 class='ui red header'>O usuário cPanel {$ContaLista} não foi encontrado entre contas pertencentes ao revendedor.<br></h5>";


            }

            }

            }

            echo "<br><br>";
            
            $account_number = 0;

            foreach ($userlist['acct'] as $field => $Account) 
            {                       
                
            $ExistecPanel = "/var/cpanel/users/".$Account['user']."";

            if ( $IfExists || !file_exists($ExistecPanel) )
            {
               
            if(  (!$AcctIN && !$AcctEX)  || ( $AcctIN && in_array($Account['user'], $ListaContas) ) || ( $AcctEX && !in_array($Account['user'], $ListaContas) ) )
            {

            $account_number ++;
            $FalhaSuspensao = FALSE; $ContaSuspensa = FALSE;
            $Account['ip'] = $Reseller['ip']; $Account['pass'] = $Reseller['pass'];
            
            if($Account['suspended'] == "1")
            {

            echo "Removing account suspension {$Account['user']}... ";
                                        
            $xmlapi = new xmlapi($Reseller['ip']); 
            $xmlapi->password_auth($Reseller['user'], $Reseller['pass']);
            $xmlapi->set_port('2087'); 
            $xmlapi->set_output('array'); 
            $unsuspend = $xmlapi->unsuspendacct($Account['user']);

            if ( $unsuspend['result']['status'] == "0" )
            {
            echo "<h5 class='ui red header'>ERROR!<br>Não foi possível remover a suspensão da conta de {$Account['user']}!<br>Esta conta não pode ser importado!<br><br><br></h5>";
            $FalhaSuspensao = TRUE;
            $BackupFalha[] = $Account['user'];
            $RestauraFalha[] = $Account['user'];
            $RestauraErro[$Account['user']] = "Backup não foi gerado ou não foi transferido!";
            $BackupErro[$Account['user']] = "Não foi possível remover a suspensão da conta!";
            }
            else
            {
            echo "OK.<br>";
            $ContaSuspensa = TRUE;
            }
            }
                
            if($FalhaSuspensao===FALSE){
        
            if ($Forced){

            $Campos = "user={$Account['user']}&pass={$Reseller['pass']}";
            $MudarSenha = $Protocol."{$Reseller['ip']}:{$WhmPort}/xml-api/passwd?";
            echo "Alterando a senha de usuário cPanel['user']}... ";
            $PasswdPage = getByCurl( $MudarSenha, $Reseller['user'], $Reseller['pass'], array( "CURLOPT_POST" => $Campos ) );
                
            if ( !$PasswdPage ){
            echo "<h5 class='ui red header'>ERRO!<br>Não é possível alterar a senha do usuário {$Account['user']}!<br>Esta conta não pode ser transferida!</h5>";
            $BackupFalha[] = $Account['user'];
            $RestauraFalha[] = $Account['user'];
            $RestauraErro[$Account['user']] = "Backup não foi gerado ou não foi transferido!";
            $BackupErro[$Account['user']] = "Não é possível alterar a senha do usuário!";
            }
            
            else
            {
            echo "OK.<br>";

            deleta_backup();
            unset($ApagarItens, $BkpPagina, $RemPagina);
            $Reseller['pass'] = base64_encode( $Reseller['pass'] );

            system("echo -n \"\" > /var/log/isistemtoolsbackup/backup/{$Account['user']}.txt");
            system("echo -n \"\" > /var/log/isistemtoolsbackup/restauracao/{$Account['user']}.txt");

            passthru( "/usr/local/cpanel/whostmgr/docroot/cgi/isistem-tools/migrador/isistemtoolsbackup/exigir.so {$Reseller['ip']} {$Account['user']} {$Account['domain']} {$Reseller['pass']} {$ContaUsr} {$MigraUsr} | tee /var/log/isistemtoolsbackup/backup/{$Account['user']}.txt" );

            $Reseller['pass'] = base64_decode( $Reseller['pass'] );

            }

            }     

            else
            {

            deleta_backup();
            unset($ApagarItens, $BkpPagina, $RemPagina);
            $Reseller['pass'] = base64_encode( $Reseller['pass'] );

            system("echo -n \"\" > /var/log/isistemtoolsbackup/backup/{$Account['user']}.txt");
            system("echo -n \"\" > /var/log/isistemtoolsbackup/restauracao/{$Account['user']}.txt");

            if (!isset($ApenasContas)) $ApenasContas = $numero_contas;
            echo "<br><h5 class='ui blue header'>Migrating account {$account_number} of {$ApenasContas} ({$Account['diskused']})<br><br></h5>";
 
            passthru( "/usr/local/cpanel/whostmgr/docroot/cgi/isistem-tools/migrador/isistemtoolsbackup/solicitar.so {$Reseller['ip']} {$Account['user']} {$Account['domain']} {$Reseller['pass']} {$ContaUsr} {$MigraUsr} | tee /var/log/isistemtoolsbackup/backup/{$Account['user']}.txt");

            $Reseller['pass'] = base64_decode( $Reseller['pass'] );
            echo "<br>";
            deleta_backup();
            unset($ApagarItens, $BkpPagina, $RemPagina);

        
            }
            
            $log = file_get_contents("/var/log/isistemtoolsbackup/backup/{$Account['user']}.txt");

            if (preg_match('/COMPLETE/i', $log) or preg_match('/CONCLUÍDA/i',$log)) {
            $BackupSucesso[] = $Account['user'];
            }

            else{
            $BackupFalha[] = $Account['user'];

            $BackupErro[$Account['user']] = "Não é possível migrar conta!";

            if ( preg_match('/UNABLE/i', $log) && preg_match('/MIGRATE/i', $log) ) {

            if ( preg_match('/Failed/i', $log) && preg_match('/start/i', $log) ) $BackupErro[$Account['user']] = "Não foi possível iniciar o backup!"; 
            if ( preg_match('/Unable/i', $log) && preg_match('/access/i', $log) && preg_match('/domain/i', $log) ) $BackupErro[$Account['user']] = "Não é possível acessar o servidor!";

 if ( preg_match('/Unable/i', $log) && preg_match('/access/i', $log) && preg_match('/IP/i', $log) ) $BackupErro[$Account['user']] = "Unable to access the server by IP address!";  


            }

            }
            unset($log);
    
            $log = file_get_contents("/var/log/isistemtoolsbackup/restauracao/{$Account['user']}.txt");

            if ( preg_match('/RESTORE COMPLETED/i', $log) or preg_match('/RESTAURAÇÃO CONCLUÍDA/i',$log)) {
            $RestauraSucesso[] = $Account['user'];

            if($BkpDelete){

            if($MigraUsr == "root") $MigraHome = "/home";
            system("rm -f ".$MigraHome."/cpmove-{$Account['user']}.tar.gz");

            }

            }

            else{

            $RestauraFalha[] = $Account['user'];

            $RestauraErro[$Account['user']] = $log; if($log == "") $RestauraErro[$Account['user']] = "Backup não foi gerado ou não foi transferido!";
            
            }
            unset($log);

    
            echo "<br>";

            if($ContaSuspensa===TRUE){   

            echo "Retirado suspensão da conta {$Account['user']}... ";
                     
            $xmlapi = new xmlapi($Reseller['ip']); 
            $xmlapi->password_auth($Reseller['user'], $Reseller['pass']);
            $xmlapi->set_port('2087'); 
            $xmlapi->set_output('array'); 
            $suspend = $xmlapi->suspendacct($Account['user']);
                    
            echo "OK.";

            }
        
            echo "<br><br>";
    
            }
    
            }
        
 
            }
         
            else
            {

            if( !$AcctIN && !$AcctEX)
            {
            echo "<h5 class='ui red header'>A conta {$Account['user']} já existe no servidor e não serão migrados!</h5><br>";
            }
            else
            {
            foreach($ListaContas  as $ContaIgnorada){
            if($AcctIN){
            if($ContaIgnorada==$Account['user']) echo "<h5 class='ui red header'>A conta {$ContaIgnorada} já existe no servidor e não serão migrados!</h5><br>";
            }
            else{
            if($ContaIgnorada!=$Account['user']) echo "<h5 class='ui red header'>A conta {$ContaIgnorada} já existe no servidor e não serão migrados!</h5><br>";
            }
         
            }
            }
            }
            
            }


            echo "<br>Migração da revenda {$Reseller['user']} @ {$Reseller['ip']} foi completado.<br>";
            include( "rodape.php" );


            $SrvConfig = array( );
            $ConfigFile = file( "/etc/wwwacct.conf" );
            foreach ( $ConfigFile as $Line )
            {
            if ( preg_match( "/^CONTACTEMAIL/", $Line ) )
            {
            $x = explode( " ", $Line );
            $ConfigMail = trim( $x[1] );
            $SrvConfig['CONTACTEMAIL'] = $ConfigMail;
            }
            if ( preg_match( "/^HOST/", $Line ) )
            {
            $y = explode( " ", $Line );
            $SrvName = trim( $y[1] );
            $SrvConfig['HOSTNAME'] = $SrvName;
            }
            }


            $UserFile = file( "/var/cpanel/users/{$MigraUsr}" );
            foreach ( $UserFile as $Line )
            {
            if ( preg_match( "/^CONTACTEMAIL=/", $Line ) )
            {
            $z = explode( "=", $Line );
            $ConfigMail = trim( $z[1] );
            }
            }

       $To = $SrvConfig['CONTACTEMAIL'];
       $Host = $SrvConfig['HOSTNAME'];

       $para = ($MigraUsr == "root") ? $To : $ConfigMail;

       $assunto = "Migracao completada";
       $mensagem = "<html><body><h5 style='font-size: small; font-family: georgia,palatino;'>";
       $mensagem .= "Migracao da revenda {$Reseller['user']} para o servidor {$Host} foi concluida.<br>";
       
       if(empty($BackupFalha)){
       $mensagem .= "<br>Todas as contas foram migradas com exito.<br>";
       }
       else{
       $mensagem .= "<br><b>As contas a seguir nao pode ser restaurado</b>: <br>";
       foreach($BackupFalha as $backupconta){
       $mensagem .= strtoupper($backupconta)." - ".strtolower($BackupErro[$backupconta])."<br>";
       }
       
       }

       if(empty($RestauraFalha)){
       $mensagem .= "<br>Todas as contas foi restaurado com sucesso.<br>";
       }
       else{
       $mensagem .= "<br><b>As contas a seguir nao pode ser restaurado</b>: <br>";

       foreach($RestauraFalha as $restauraconta){
       $mensagem .= strtoupper($restauraconta)." - ".strtolower($RestauraErro[$restauraconta])."<br>";
       }

       }
      
       $mensagem .= "<br><br>"; 
       $mensagem .= "Att,</h5><br><img src='https://www.easycpanelbackup.com.br/images/logo.png' alt='' width='200' height='48' /><br></body><br></html>";
       $nome = "Easy Cp Backup";
       $email = "easywhmbackup@".$Host;
       $cabecalho = "MIME-Version: 1.0\n";
       $cabecalho .= "Content-Type: text/html; charset='utf-8'\n";
       $cabecalho .= "From: ".$nome." <".$email.">\n";
       $cabecalho .= "Return-Path: <$email>\n";
       $cabecalho .= "Reply-to: $nome <$email>\n";
       $cabecalho .= "X-Priority: 1\n"; 

       mail($para,$assunto,$mensagem,$cabecalho);
