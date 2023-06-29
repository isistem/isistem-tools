<?php

            error_reporting(E_ALL);
            set_time_limit(0);
            ignore_user_abort(true);
            chdir("/usr/local/cpanel/whostmgr/docroot/cgi/isistem-tools/migrador/isistemtoolsbackup/");

            require("funcoes.php");
            require_once("xmlapi.php");
            require ("preferencias.php");

            echo '<head><meta charset="UTF-8" /><title>Isistem Tools Backup</title></head>';

                if(!extension_loaded('curl')){

                    echo "Não foi possível carregar a extensão cURL do PHP. Não é possível continuar!";
                    echo "<br>Execute o seguinte comando via SSH para compilar cPanel PHP:";
                    echo "<br>/usr/local/cpanel/scripts/isistemtoolsbackup/manutencao.sh -c<br>";
                    include("rodape.php");
                    exit();
                }

            if(!function_exists('file_put_contents')) die("<br>The file_put_contents() função é desativada!");
            if(!function_exists('system')) die("<br>The system() função é desativada!");
            if(!function_exists('passthru')) die("<br>The passthru() função é desativada!");

                if(empty($licenca)){
                die("Você não tem uma licença Isistem Tools Backup!");
                }

                verifica_licenca($licenca);
                
            $revendas = glob (dirname(__FILE__) . '/reseller*.php');

            foreach ($revendas as $revenda) {

            require $revenda;

            $BackGround = TRUE;
            $ChooseTime = FALSE;
            $WaitTime = 0;

            $ChangeOwner = FALSE;
            $AccountOwner = 'root';
            $UsrInfo = posix_getpwuid(posix_getuid());
            $MigraUsr = $UsrInfo['name'];

            $Forced = $CONFIG['force'];
            $IfExists = TRUE;
            $BkpDelete = FALSE;
            $SkipRestore = $CONFIG['restore']  ? 0 : 1;
            $ForceRestore = $CONFIG['restore']  ? 1 : 0;

            if($BackGround || $ChooseTime) ignore_user_abort(true);

            $Reseller = array();
            $Reseller['domain'] = $CONFIG['domain']; $search = array("http", "//", "/whm", ":", "2087"); $Reseller['domain'] = str_replace($search, "", $Reseller['domain']);
            $Reseller['ip'] = gethostbyname( $Reseller['domain'] );
            $Reseller['user'] = $CONFIG['user'];
            $Reseller['pass'] = $CONFIG['pass'];
            $ChooseAccs = $CONFIG['sort'];
            $CpanelAccs = $CONFIG['accts'];

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
            echo "Você não preencheu um dos campos do formulário!<br>";

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
            echo "Você preferiu escolher quais contas para migrar, mas não inseriu os nomes de usuários!<br>";
            exit( );
            }

            if ($ChooseTime)
            {

            if (empty($WaitTime))
            {
            echo "Você escolheu agendar a migração mas não definiu o tempo de espera para a migração começar!<br>";
            exit( );
            }

            if (is_numeric($WaitTime)===FALSE)
            {
            echo "O tempo de espera definido para a migração começar é inválido!<br>";
            exit( );
            }

            }

            if ($ChangeOwner)
            {

            if (empty($AccountOwner))
            {
            echo "Você escolheu mover as contas migradas pra outra revenda mas não definiu o nome de usuário!<br>";
            exit( );
            }

            $existecpanel = "/var/cpanel/users/".$AccountOwner."";
            if ( !file_exists( $existecpanel ) && $AccountOwner != "root")
            {
            echo "Você escolheu mover as contas migradas pra uma revenda que não existe!<br>";
            exit( );
            }
            }

            file_put_contents (dirname(__FILE__) . '/' . basename ($revenda, '.php') . '.log', '');
            system("chmod 755 /usr/local/cpanel/whostmgr/docroot/cgi/isistem-tools/migrador/isistemtoolsbackup/solicitar.so");
            system("chmod 755 /usr/local/cpanel/whostmgr/docroot/cgi/isistem-tools/migrador/isistemtoolsbackup/exigir.so");
            system("chmod 755 /usr/local/cpanel/whostmgr/docroot/cgi/isistem-tools/migrador/isistemtoolsbackup/incluir.so");

            echo "Migrando WHM revendedor {$Reseller['user']} @ {$Reseller['ip']}<br>";

            if(file_exists("/usr/sbin/csf"))
            {
            echo "<br>Permitindo IP servidor remoto no firewall local ... ";
            $resultado = shell_exec( "csf -a ".$Reseller['ip'] );
            if ( preg_match( "/Adding/i", $resultado ) || preg_match( "/already in the allow file/i", $resultado ) )
            {
            echo "OK.<br>";
            $LiberaFW = TRUE;
            }
            else
            {
            echo "<br>Nao poderia permitir que o IP no firewall!<br>";
            }
            }


            if ($ChooseTime)
            {
            echo "<br>Conectando-se ao WHM... ";
            $AccountPage = getByCurl( "{$Protocol}{$Reseller['ip']}:{$WhmPort}", $Reseller['user'], $Reseller['pass'] );
    
            if ( !$AccountPage )
            {
            echo "<br>Impossível ligar ao WHM!<br>Verifique se o domínio ou IP está correta ou se há um firewall bloqueando o acesso.<br>"; 
            exit( );
            }
            if ( preg_match( "/Login/i", $AccountPage ) )
            {
            echo "<br>Impossível Conectar ao WHM!<br>Verifique se o domínio ou IP está correto ou se há um firewall bloqueando o acesso.<br>";
            exit( );
            }

            echo "OK.<br>";
            echo "<br>A migração só será iniciado em {$WaitTime} minutos<br>";
            echo "Se desejar, você pode fechar esta janela.<br><br>";

            $WaitTime = 60 * $WaitTime;
            sleep($WaitTime);
            }

            if ($Forced)
            {
            echo "Salvando as configurações... ";
            $arquivo = "/usr/local/cpanel/whostmgr/docroot/cgi/isistem-tools/migrador/isistemtoolsbackup/modulos/config.pm";
            if ( !( $fh = fopen( $arquivo, "w" ) ) )
            {
            exit( "<br>Não é possível abrir arquivo de configuração!<br>" );
            }
            $dados = "#!/usr/bin/perl \nour \$senha_cpanel = '{$Reseller['pass']}';\n1;";
            fwrite( $fh, $dados );
            fclose( $fh );
            echo "OK.<br><br>";
            }

            echo "<br>Conectando-se ao WHM... ";

            $AccountsPage = getByCurl( "{$Protocol}{$Reseller['ip']}:{$WhmPort}", $Reseller['user'], $Reseller['pass'] );

            if ( !$AccountsPage )
            {
            echo "<br>Impossível ligar ao WHM!<br>Verifique se o domínio ou IP está correto ou se há um firewall bloqueando o acesso.<br>";
            exit( );
            }

            if ( preg_match( "/Login/i", $AccountsPage ) )
            {
            echo "<br>Não é possível fazer login para WHM! Tenha certeza que o nome de usuário e senha estão corretos.<br>";
            exit( );
            }

            echo "OK.<br>";
        
            $xmlapi = new xmlapi($Reseller['ip']); 
            $xmlapi->password_auth($Reseller['user'], $Reseller['pass']);
            $xmlapi->set_port('2087'); 
            $xmlapi->set_output('array'); 
            $userlist = $xmlapi->listaccts(); 
            if(isset($userlist['data']['result']) && $userlist['data']['result']=="0") die("Impossível usar cPanel API para obter a lista de contas: Acesso Negado!");

            echo "Listando cPanel, Obtendo contas do revendedor... ";
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
            echo "Você escolheu {$ApenasContas} contas somente.<br><br>";
            }
            else
            {
            echo "Todas as contas serão copiadas.<br><br>";
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
           echo "O usuário cPanel {$ContaLista} não foi encontrado entre as contas de propriedade do revendedor.<br>";


            }

            }

            }

            echo "<br><br>";

            foreach ($userlist['acct'] as $field => $Account) 
            {
                
            $ExistecPanel = "/var/cpanel/users/".$Account['user']."";

            if ( $IfExists || !file_exists($ExistecPanel) )
            {
    
            if(  (!$AcctIN && !$AcctEX)  || ( $AcctIN && in_array($Account['user'], $ListaContas) ) || ( $AcctEX && !in_array($Account['user'], $ListaContas) ) )
            {

            $FalhaSuspensao = FALSE; $ContaSuspensa = FALSE;
            $Account['ip'] = $Reseller['ip']; $Account['pass'] = $Reseller['pass'];
            
            if($Account['suspended'] == "1")
            {

            echo "Removendo a suspensão da conta {$Account['user']}... ";
                                        
            $xmlapi = new xmlapi($Reseller['ip']); 
            $xmlapi->password_auth($Reseller['user'], $Reseller['pass']);
            $xmlapi->set_port('2087'); 
            $xmlapi->set_output('array'); 
            $unsuspend = $xmlapi->unsuspendacct($Account['user']);

            if ( $unsuspend['result']['status'] == "0" )
            {
            echo "ERRO!<br>Não foi possível remover a suspensão da conta de {$Account['user']}!<br>Esta conta não pode ser migrado!<br><br><br>";
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
            echo "Alterar senha do usuário cPanel {$Account['user']}... ";
            $PasswdPage = getByCurl( $MudarSenha, $Reseller['user'], $Reseller['pass'], array( "CURLOPT_POST" => $Campos ) );
                
            if ( !$PasswdPage ){
            echo "ERRO!<br>Não é possível alterar a senha do usuário {$Account['user']}!<br>Esta conta não pode ser transferida!";
            $BackupFalha[] = $Account['user'];
            $RestauraFalha[] = $Account['user'];
            $RestauraErro[$Account['user']] = "Backup não foi gerado ou não foi migrado!";
            $BackupErro[$Account['user']] = "Não é possível alterar a senha do usuário!";
            }
            
            else
            {
            echo "OK.<br>";

            deleta_backup();
            unset($ApagarItens, $BkpPagina, $RemPagina);
            $Reseller['pass'] = base64_encode( $Reseller['pass'] );

            system("echo -n \"\" > /tmp/{$Account['user']}.txt; chmod 777 /tmp/{$Account['user']}.txt");

            passthru( "echo {$ForceRestore} | /usr/local/cpanel/whostmgr/docroot/cgi/isistem-tools/migrador/isistemtoolsbackup/exigir.so {$Reseller['ip']} {$Account['user']} {$Account['domain']} {$Reseller['pass']} {$ContaUsr} {$MigraUsr} {$SkipRestore} | tee /tmp/{$Account['user']}.txt" );

            $Reseller['pass'] = base64_decode( $Reseller['pass'] );

            }

            }     

            else
            {

            deleta_backup();
            unset($ApagarItens, $BkpPagina, $RemPagina);
            $Reseller['pass'] = base64_encode( $Reseller['pass'] );

            system("echo -n \"\" > /tmp/{$Account['user']}.txt; chmod 777 /tmp/{$Account['user']}.txt");
 
            passthru( "echo {$ForceRestore} | /usr/local/cpanel/whostmgr/docroot/cgi/isistem-tools/migrador/isistemtoolsbackup/solicitar.so {$Reseller['ip']} {$Account['user']} {$Account['domain']} {$Reseller['pass']} {$ContaUsr} {$MigraUsr} {$SkipRestore} | tee /tmp/{$Account['user']}.txt");

            $Reseller['pass'] = base64_decode( $Reseller['pass'] );
            echo "<br>";
            deleta_backup();
            unset($ApagarItens, $BkpPagina, $RemPagina);

        
            }
            
            $log = file_get_contents("/tmp/{$Account['user']}.txt");
            $savelog = str_replace (array('<br>', '<br />', "<span class='texto_padrao'>", '</span>'), "\n", $log);
            $start = "\n\n\n********* BACKUP OF ACCOUNT {$Account['user']} *********\n";
            $end = "\n\nEND ********* BACKUP OF ACCOUNT {$Account['user']} ********* END\n\n";
            file_put_contents (dirname(__FILE__) . '/' . basename ($revenda, '.php') . '.log', $start . $savelog, FILE_APPEND);

            if ( preg_match('/MIGRATION/i', $log) && preg_match('/COMPLETE/i', $log) ) {
            $BackupSucesso[] = $Account['user'];
            }

            else{
            $BackupFalha[] = $Account['user'];

            $BackupErro[$Account['user']] = "Unable to migrate account!";

            if ( preg_match('/UNABLE/i', $log) && preg_match('/MIGRATE/i', $log) ) {

            if ( preg_match('/Failed/i', $log) && preg_match('/start/i', $log) ) $BackupErro[$Account['user']] = "Não é possível iniciar o backup!"; 
            if ( preg_match('/Unable/i', $log) && preg_match('/access/i', $log) && preg_match('/domain/i', $log) ) $BackupErro[$Account['user']] = "Não é possível acessar o servidor por domínio!";

 if ( preg_match('/Unable/i', $log) && preg_match('/access/i', $log) && preg_match('/IP/i', $log) ) $BackupErro[$Account['user']] = "Unable to access the server by IP address!";  


            }

            }

            if ( preg_match('/RESTORE COMPLETED/i', $log) ) {
            $RestauraSucesso[] = $Account['user'];

            if($BkpDelete){

            if($MigraUsr == "root") $MigraHome = "/home";
            system("rm -f ".$MigraHome."/cpmove-{$Account['user']}.tar.gz");

            }

            }

            else{

            $RestauraFalha[] = $Account['user'];

            $RestauraErro[$Account['user']] = $log; if($log == "") $RestauraErro[$Account['user']] = "Backup não foi gerado ou não foi migrado!";
            
            }
            unset($log);

    
            echo "<br>";

            if($ContaSuspensa===TRUE){   

            echo "Resuspending account {$Account['user']}... ";
                     
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
            echo "A conta {$Account['user']} já existe no servidor e não serão migrados!<br>";
            }
            else
            {
            foreach($ListaContas  as $ContaIgnorada){
            if($AcctIN){
            if($ContaIgnorada==$Account['user']) echo "A conta {$ContaIgnorada} já existe no servidor e não serão migrados!<br>";
            }
            else{
            if($ContaIgnorada!=$Account['user']) echo "A conta {$ContaIgnorada} já existe no servidor e não serão migrados!<br>";
            }
         
            }
            }
            }
         
            }


            echo "<br>O backup do revendedor {$Reseller['user']} @ {$Reseller['ip']} foi completado.<br><br><br>";

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
            $x = explode( " ", $Line );
            $SrvName = trim( $x[1] );
            $SrvConfig['HOSTNAME'] = $SrvName;
            }
            }


       $para = ($MigraUsr == 'root') ? $SrvConfig['CONTACTEMAIL'] : $CONFIG['email'];
       $Host = $SrvConfig['HOSTNAME'];

       $assunto = "Backup Concluido";
       $mensagem = "<html><body><span style='font-size: small; font-family: georgia,palatino;'>";
       $mensagem .= "O backup do revendedor {$Reseller['user']} ao servidor {$Host} foi completado.<br>";       
      
       $mensagem .= "<br><br>"; 
       $mensagem .= "Att,</span><br><img src='http://tools.isistem.com.br/images/logo.png' alt='' width='174' height='69' /><br></body><br></html>";
       $nome = "Isistem Tools Backup";
       $email = "isistemtools@".$Host;
       $cabecalho  = "From: ".$nome." <".$email.">\r\n";
       $cabecalho .= "Return-Path: <$email>\r\n";
       $cabecalho .= "Reply-to: $nome <$email>\r\n";
       $cabecalho .= "X-Priority: 1"; 

       $arquivo = dirname(__FILE__) . '/' . basename ($revenda, '.php') . '.log';
       $horario = date("d-m-Y");

       $htmlbody = "<span style='font-size: small; font-family: georgia,palatino;'>O backup do revendedor {$Reseller['user']} ao servidor {$Host} foi completado.<br>";

       if(empty($BackupFalha)){
       $htmlbody  .= "<br>Todas as contas foram migradas com exito.<br>";
       }
       else{
       $htmlbody  .= "<br><b>As seguintes contas nao pode ser completado</b>: <br>";
       foreach($BackupFalha as $backupconta){
       $htmlbody  .= strtoupper($backupconta)." - ".strtolower($BackupErro[$backupconta])."<br>";
       }
       
       }

$htmlbody .= "Voce pode ver o relatorio completo em anexo ou no "."{$arquivo}"."<br><br>Att,<br><img src='http://tools.isistem.com.br/images/logo.png' alt='' width='174' height='69' /><br>";

$textmessage = "";
$subject = "Backup Completado!";

$random_hash = md5(date('r', time()));
$cabecalho .= "\r\nContent-Type: multipart/mixed; boundary=\"PHP-mixed-".$random_hash."\"";
$attachment = chunk_split(base64_encode(file_get_contents($arquivo)));

$message = "--PHP-mixed-$random_hash\r\n"
."Content-Type: multipart/alternative; boundary=\"PHP-alt-$random_hash\"\r\n\r\n";
$message .= "--PHP-alt-$random_hash\r\n"
."Content-Type: text/plain; charset=\"iso-8859-1\"\r\n"
."Content-Transfer-Encoding: 7bit\r\n\r\n";

$message .= strip_tags($textmessage);
$message .= "\r\n\r\n--PHP-alt-$random_hash\r\n"
."Content-Type: text/html; charset=\"utf-8\r\n"
."Content-Transfer-Encoding: 7bit\r\n\r\n";
$message .= $htmlbody;
$message .="\r\n\r\n--PHP-alt-$random_hash--\r\n\r\n";

$message .= "--PHP-mixed-$random_hash\r\n"
."Content-Type: text/plain; name=\"backup_{$Reseller['user']}_".$horario.".txt\"\r\n"
."Content-Transfer-Encoding: base64\r\n"
."Content-Disposition: attachment; filename=\"backup_{$Reseller['user']}_".$horario.".txt\"\r\n\r\n";
$message .= $attachment;
$message .= "/r/n--PHP-mixed-$random_hash--";

mail ( $para, $subject , $message, $cabecalho );

}

?>