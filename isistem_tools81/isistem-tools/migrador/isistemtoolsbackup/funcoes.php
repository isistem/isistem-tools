<?php

# Conversão de string pra base hexadecimal

function str_hex($string){

    $hex='';
    for ($i=0; $i < strlen($string); $i++){
        $hex .= dechex(ord($string[$i]));
    }
    return $hex;
}

# Função de Notificação de Backup Concluído

function notifica_backup($to, $host) {

    global $diario;
    global $hora_atual;

    $arquivo = "/var/log/isistemtoolsbackup/backup/".date("d-m-Y").".txt";
    $horario = date("d-m-Y");

    if($diario && $diario != "1") {

        $arquivo = "/var/log/isistemtoolsbackup/backup/".date("d-m-Y")."_".$hora_atual.".txt";
        $horario = date("d-m-Y")."_".$hora_atual;
    }

$htmlbody = "<span style='font-size: small; font-family: georgia,palatino;'>O backup do servidor "."{$host}".", gerado pelo Isistem tools Backup esta completo.<br>Voce pode ver o relatorio completo em anexo ou em "."{$arquivo}"."<br><br>Att,</span><br><br>";

$textmessage = "";
$subject = "O backup foi concluido";
$headers = "From: Isistem tools Backup<cpanel@"."{$host}".">\r\nReply-To: cpanel@"."{$host}"."";

$random_hash = md5(date('r', time()));
$headers .= "\r\nContent-Type: multipart/mixed; boundary=\"PHP-mixed-".$random_hash."\"";
$attachment = chunk_split(base64_encode(file_get_contents($arquivo)));

$message = "--PHP-mixed-$random_hash\r\n"
."Content-Type: multipart/alternative; boundary=\"PHP-alt-$random_hash\"\r\n\r\n";
$message .= "--PHP-alt-$random_hash\r\n"
."Content-Type: text/plain; charset=\"iso-utf-8\"\r\n"
."Content-Transfer-Encoding: 7bit\r\n\r\n";

$message .= strip_tags($textmessage);
$message .= "\r\n\r\n--PHP-alt-$random_hash\r\n"
."Content-Type: text/html; charset=\"utf-8\r\n"
."Content-Transfer-Encoding: 7bit\r\n\r\n";
$message .= $htmlbody;
$message .="\r\n\r\n--PHP-alt-$random_hash--\r\n\r\n";

$message .= "--PHP-mixed-$random_hash\r\n"
."Content-Type: text/plain; name=\"Backup_".$horario.".txt\"\r\n"
."Content-Transfer-Encoding: base64\r\n"
."Content-Disposition: attachment; filename=\"Backup_".$horario.".txt\"\r\n\r\n";
$message .= $attachment;
$message .= "/r/n--PHP-mixed-$random_hash--";

$mail = mail( $to, $subject , $message, $headers );

}

# Funcao de Notificacao de Restauracao Concluida

function notifica_restauracao($to, $host)
{

$arquivo = "/var/log/isistemtoolsbackup/restauracao/".date("d-m-Y").".txt";
$htmlbody = "<span style='font-size: small; font-family: georgia,palatino;'>Restauracao de contas do servidor "."{$host}".", realizada por Isistem tools Backup esta completo.<br>Voce pode ver o relatorio completo em anexo ou em "."{$arquivo}"."<br><br>Att,</span><br><br>";
$textmessage = "";

$subject = "Restauracao Completada";

$headers = "From: Isistem tools Backup<cpanel@"."{$host}".">\r\nReply-To: cpanel@"."{$host}"."";

$random_hash = md5(date('r', time()));

$headers .= "\r\nContent-Type: multipart/mixed; boundary=\"PHP-mixed-".$random_hash."\"";

$attachment = chunk_split(base64_encode(file_get_contents($arquivo)));

$message = "--PHP-mixed-$random_hash\r\n"
."Content-Type: multipart/alternative; boundary=\"PHP-alt-$random_hash\"\r\n\r\n";
$message .= "--PHP-alt-$random_hash\r\n"
."Content-Type: text/plain; charset=\"utf-8\"\r\n"
."Content-Transfer-Encoding: 7bit\r\n\r\n";

$message .= strip_tags($textmessage);
$message .= "\r\n\r\n--PHP-alt-$random_hash\r\n"
."Content-Type: text/html; charset=\"utf-8\r\n"
."Content-Transfer-Encoding: 7bit\r\n\r\n";
$message .= $htmlbody;
$message .="\r\n\r\n--PHP-alt-$random_hash--\r\n\r\n";

$message .= "--PHP-mixed-$random_hash\r\n"
."Content-Type: text/plain; name=\"Restauracao_".date("d-m-Y").".txt\"\r\n"
."Content-Transfer-Encoding: base64\r\n"
."Content-Disposition: attachment; filename=\"Restauracao_".date("d-m-Y").".txt\"\r\n\r\n";
$message .= $attachment;
$message .= "/r/n--PHP-mixed-$random_hash--";

$mail = mail( $to, $subject , $message, $headers );

}

             function avisa_licenca($erro){


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

                    $assunto = "Importante: problema com a licenca!";
                    $mensagem = "<html><body><span style='font-size: small; font-family: georgia,palatino;'>Ha um problema com a sua licenca de Isistem tools Backup, instalado no servidor {$host}.<br>Mensagem apresentada: <i><b>{$erro}</i></b><br>issso esta impedindo seu servidor de fazer backup e migrar contas e revendas. Regularize a situacao da sua licenca o mais rapido possivel.<br><br>Att,</span><br><br></body><br></html>";

                    $headers = "MIME-Version: 1.0\n";
                    $headers .= "Content-Type: text/html; charset='utf-8'\n";
                    $headers .= "From: Isistem tools Backup <suporte@isistem.com.br>\n";
                    $headers .= "Return-Path: <suporte@isistem.com.br>\n";
                    $headers .= "Reply-to: <suporte@isistem.com.br>\n";
                    $headers .= "X-Priority: 1\n";

                    mail($to,$assunto,$mensagem,$headers);

                    die("<span class='texto_normal_vermelho_destaque'>{$erro}</span>");

             }


      function verifica_licenca($licenca){

            $localkey = file_get_contents('key.txt');

	    $tipo_licenca = array_shift( explode("-",$licenca) );

	    if($tipo_licenca == "Plugin" || $tipo_licenca == "Owned" || $tipo_licenca == "Trial" || $tipo_licenca == "Lifetime"){

	    if ($tipo_licenca == "Plugin") $licensing_secret_key = 'ec09643d4317f72e223c8604aad8b200';
            elseif ($tipo_licenca == "Owned") $licensing_secret_key = 'bd34cf5d5d7f7d1164913c85694f9768';
            elseif ($tipo_licenca == "Trial") $licensing_secret_key = '057afac9db4d1f00f77172e03bf53178';
            elseif ($tipo_licenca == "Lifetime") $licensing_secret_key = 'eh09f32905u9234ujrt4209km9kvf093';
            else avisa_licenca("Voce nao tem uma licenca do Isistem tools Backup!");

	    $results = check_license($licenca,$localkey,$licensing_secret_key);

	    if ($results["status"]=="Active") {

	    if (isset($results["localkey"])) {

	    $localkeydata = $results["localkey"];
	    file_put_contents ('key.txt', $localkeydata);

	    }

	    } elseif ($results["status"]=="Invalid") {

	    if($results["description"] == "Remote Check Failed"){
	    avisa_licenca("Falha ao validar a licença: servidor de licenças temporariamente off-line!<br />Por favor, tente novamente e se o problema persistir, contate o suporte.");
            }
	    else{
	    avisa_licenca("Sua licenca e invalida!");
	    }

	    } elseif ($results["status"]=="Expirada") {

	    avisa_licenca("Sua licenca esta expirada!");

	    } elseif ($results["status"]=="Suspensa") {

	    avisa_licenca("Sua licenca esta suspensa!");

	    }

	    else {

	    avisa_licenca("Falha ao validar a licença: servidor de licenças temporariamente indisponível!<br>Por favor, tente novamente e se o problema persistir, contate o suporte.");

	    }

	    }

	    else{

	    if ($tipo_licenca == "Leased") $secretkey='ec09643d4317f72e223c8604aad8b200';
	    elseif ($tipo_licenca == "Owner") $secretkey='bd34cf5d5d7f7d1164913c85694f9768';
	    elseif ($tipo_licenca == "Free") $secretkey='057afac9db4d1f00f77172e03bf53178';
	    elseif ($tipo_licenca == "Unlimited") $secretkey='eh09f32905u9234ujrt4209km9kvf093';
            else avisa_licenca("Você não tem uma licença Isistem tools Backup valida!");

	    $spbas=new spbas;     
	    $spbas->license_key=$licenca;
	    $spbas->api_server='https://tools.isistem.com.br/include/api/index.php';  
	    $spbas->secret_key=$secretkey;  
	    $spbas->validate(); 

	    if ($spbas->errors) { 

	    $erro = $spbas->errors;
   
	    if (preg_match("/Erro: A licença não e valida para este local/i", $spbas->errors)){
            //$erro = "A licenca do Isistem tools Backup não e valida pra este IP, dominio ou diretorio!";
	    }
            if (preg_match("/Erro: A chave de licença não encontrou nenhum no banco de dados/i", $spbas->errors)){
	    //$erro = "A licenca do Isistem tools Backup e invalida ou inexistente!";
            }

	    avisa_licenca($erro);

            }
            
            unset($spbas);

            }
			         			
	    return true;
      }

function deleta_backup(){

global $Account;
global $CpPort;
global $Protocol;

echo "Verificando se ha uma copia de seguranca dos {$Account['user']}... ";

$PostArg = "user={$Account['user']}&cpanel_xmlapi_module=Fileman&cpanel_xmlapi_func=listfiles&cpanel_xmlapi_apiversion=2&dir=/&types=file";
$ListBkp = $Protocol."{$Account['ip']}:{$CpPort}/xml-api/cpanel?";

$BkpPagina = getByCurl($ListBkp,$Account['user'],$Account['pass'],array('CURLOPT_POST'=>$PostArg));

if($BkpPagina===false || preg_match("/Access denied/", $BkpPagina)){
echo "<span class='texto_normal_vermelho_destaque'>ERRO!<br>Não foi possível listar os backups existentes. Você não pode remove-los!</span>";
}
else{
echo "OK.";

preg_match_all('/<fullpath>(.*)<\/fullpath>/U', $BkpPagina, $match);
$ApagarItens = array();

foreach($match[1] as $ArquivoDir){

$ArquivoNome = array_pop(explode("/", $ArquivoDir));
$ContaUsuariocPanel = $Account['user'];
if(preg_match("/$ContaUsuariocPanel/", $ArquivoNome) && preg_match("/backup/", $ArquivoNome) && preg_match("/.tar.gz/", $ArquivoNome)){
$ApagarItens[] = $ArquivoDir;

}

}


if(!empty($ApagarItens)){

echo "<br>Excluindo backups antigos de {$Account['user']}... ";

$ApagarLista = implode(",", $ApagarItens);
$PostArg = "user={$Account['user']}&cpanel_xmlapi_module=Fileman&cpanel_xmlapi_func=fileop&cpanel_xmlapi_apiversion=2&op=unlink&sourcefiles=$ApagarLista";
$ListBkp = $Protocol."{$Account['ip']}:{$CpPort}/xml-api/cpanel?";
$RemPagina = getByCurl($ListBkp,$Account['user'],$Account['pass'],array('CURLOPT_POST'=>$PostArg));

if($RemPagina===false || preg_match("/Access denied/", $RemPagina)){
echo "<span class='texto_normal_vermelho_destaque'>ERRO!<br>Não foi possível remover os backups existentes!</span>";
}
else{
echo "OK.";
}

}

}

}

############ Funções de Migração ############

function getByCurl($url, $user = '', $pass = '',$extra = '') {
  global $SSL;

  $ch = curl_init();
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_USERPWD, "$user:$pass");
  curl_setopt ($ch, CURLOPT_COOKIEJAR, './cookie.txt');
  curl_setopt ($ch, CURLOPT_FOLLOWLOCATION,0);
  if(!empty($extra) && is_array($extra)){
    foreach($extra as $opt=>$val){
      switch($opt){
        case 'CURLOPT_REFERER':
          curl_setopt($ch,CURLOPT_REFERER,$val);
        break;
        case 'CURLOPT_POST':
        case 'CURLOPT_POSTFIELDS':
          curl_setopt($ch,CURLOPT_POST,1);
          curl_setopt($ch,CURLOPT_POSTFIELDS,$val);
        break;
      }
    }
  }
  if($SSL){
    curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
  }
  $result = curl_exec($ch);
  curl_close($ch);

  return $result;
}

function writeLog($entry) {
  global $tipolog,$arquivolog;

  $method = strtolower($tipolog);

  $entry = date('r').' - '.$entry;

  if($method == 'file') {
    $fp = fopen($arquivolog,'ab');
        fwrite($fp, $entry);
        fclose($fp);
  } elseif($method == 'echo'){
      echo nl2br($entry);//browser
      flush();
  }

  return;
}

function getMicroTime(){
  list($usec, $sec) = explode(" ",microtime());
  return ((float)$usec + (float)$sec);
}


# Licenciamento SPBAS

class spbas
	{
	var $errors;
	var $license_key;
	var $api_server;
	var $remote_port;
	var $remote_timeout;
	var $local_key_storage;
	var $read_query;
	var $update_query;
	var $local_key_path;
	var $local_key_name;
	var $local_key_transport_order;
	var $local_key_grace_period;
	var $local_key_last;
	var $validate_download_access;
	var $release_date;
	var $key_data;
	var $status_messages;
	var $valid_for_product_tiers;

	function spbas()
		{
		$this->errors=false;
		$this->remote_port=80;
		$this->remote_timeout=10;
		$this->valid_local_key_types=array('spbas');
		$this->local_key_type='spbas';
		$this->local_key_storage='filesystem';
		$this->local_key_grace_period=10;
		$this->local_key_last=0;
		$this->read_query=false;
		$this->update_query=false;
		$this->local_key_path='./';
		$this->local_key_name='key.txt';
		$this->local_key_transport_order='scf';
		$this->validate_download_access=false;
		$this->release_date=false;
		$this->valid_for_product_tiers=false;

		$this->key_data=array(
						'custom_fields' => array(), 
						'download_access_expires' => 0, 
						'license_expires' => 0, 
						'local_key_expires' => 0, 
						'status' => 'Invalid', 
						);

		$this->status_messages=array(
						'active' => 'Esta licença esta ativa.', 
						'suspended' => 'Erro: Esta licença foi suspensa.', 
						'expired' => 'Erro: Esta licença expirou.', 
						'pending' => 'Erro: Esta licença esta pendente de revisão.', 
						'download_access_expired' => 'Erro: Uma nova versão do software foi lancada '.
									     'após o seu acesso de download expirou. Desculpe '.
									     'entre em contato com suporte para mais informações.', 
						'missing_license_key' => 'Erro: A variável chave de licença esta vazio.',
						'unknown_local_key_type' => 'Erro: Um tipo desconhecido de validação da chave foi solicitado.',
						'could_not_obtain_local_key' => 'Erro: Eu não posso obter uma nova chave de licença.', 
						'maximum_grace_period_expired' => 'Erro: O período de carência máximo da licença expirou.',
						'local_key_tampering' => 'Erro: A chave de licença local foi adulterado ou e invalido.',
						'local_key_invalid_for_location' => 'Erro: A chave de licença é invalido para este plugin.',
						'missing_license_file' => "Erro: Por favor crie o seguinte arquivo (and directories if they don't exist already):<br />\r\n<br />\r\n",
						'license_file_not_writable' => 'Erro: Por favor, faça o seguinte gravável caminho:<br />',
						'invalid_local_key_storage' => 'Erro: Não podemos localizar a chave no local do armazenamento.',
						'could_not_save_local_key' => 'Erro: Não podemos salvar a chave de licença.',
						'license_key_string_mismatch' => 'Erro: A chave é inválida para esta licença.',
						);


		// replace plain text messages with tags, make the tags keys for this localization array on the server side.
		// move all plain text messages to tags & localizations
		$this->localization=array(
						'active' => 'Esta licença esta ativa.', 
						'suspended' => 'Erro: Esta licença foi suspensa.', 
						'expired' => 'Erro: Esta licença expirou.', 
						'pending' => 'Erro: Esta licença está pendente de revisão.', 
						'download_access_expired' => 'Error: Uma nova versão do software foi lançado '.
									     'após o seu acesso de download expirou. Desculpe '.
									     'entre em contato com suporte para mais informações.',
						);
		}

	/**
	* Validate the license
	* 
	* @return string
	*/
	function validate()
		{
		// Make sure we have a license key.
		if (!$this->license_key) 
			{ 
			return $this->errors=$this->status_messages['missing_license_key']; 
			}

		// Make sure we have a valid local key type.
		if (!in_array(strtolower($this->local_key_type), $this->valid_local_key_types)) 
			{ 
			return $this->errors=$this->status_messages['unknown_local_key_type'];
			}

		// Read in the local key.
		$this->trigger_grace_period=$this->status_messages['could_not_obtain_local_key'];
		switch($this->local_key_storage)
			{
			case 'database':
				$local_key=$this->db_read_local_key();
				break;

			case 'filesystem':
				$local_key=$this->read_local_key();
				break;

			default:
				return $this->errors=$this->status_messages['missing_license_key'];
			}

		// The local key has expired, we can't go remote and we have grace periods defined.
		if ($this->errors==$this->trigger_grace_period&&$this->local_key_grace_period)
			{
			// Process the grace period request
			$grace=$this->process_grace_period($this->local_key_last); 
			if ($grace['write'])
				{
				// We've consumed one of the allowed grace periods.
				if ($this->local_key_storage=='database')
					{
					$this->db_write_local_key($grace['local_key']);
					}
				elseif ($this->local_key_storage=='filesystem')
					{
					$this->write_local_key($grace['local_key'], "{$this->local_key_path}{$this->local_key_name}");
					}
				}

			// We've consumed all the allowed grace periods.
			if ($grace['errors']) { return $this->errors=$grace['errors']; }

			// We are in a valid grace period, let it slide!
			$this->errors=false;
			return $this;
			}

		// Did reading in the local key go ok?
		if ($this->errors) 
			{ 
			return $this->errors; 
			}

		// Validate the local key.
		return $this->validate_local_key($local_key);
		}

	/**
	* Calculate the maximum grace period in unix timestamp.
	* 
	* @param integer $local_key_expires 
	* @param integer $grace 
	* @return integer
	*/
	function calc_max_grace($local_key_expires, $grace)
		{
		return ((integer)$local_key_expires+((integer)$grace*86400));
		}

	/**
	* Process the grace period for the local key.
	* 
	* @param string $local_key 
	* @return string
	*/
	function process_grace_period($local_key)
		{
		// Get the local key expire date
		$local_key_src=$this->decode_key($local_key); 
		$parts=$this->split_key($local_key_src);
		$key_data=unserialize($parts[0]);
		$local_key_expires=(integer)$key_data['local_key_expires'];
		unset($parts, $key_data);

		// Build the grace period rules
		$write_new_key=false;
		$parts=explode("\n\n", $local_key); $local_key=$parts[0];
		foreach ($local_key_grace_period=explode(',', $this->local_key_grace_period) as $key => $grace)
			{
			// add the separator
			if (!$key) { $local_key.="\n"; }

			// we only want to log days past
			if ($this->calc_max_grace($local_key_expires, $grace)>time()) { continue; }

			// log the new attempt, we'll try again next time
			$local_key.="\n{$grace}";

			$write_new_key=true;
			}

		// Are we at the maximum limit? 
		if (time()>$this->calc_max_grace($local_key_expires, array_pop($local_key_grace_period)))
			{
			return array('write' => false, 'local_key' => '', 'errors' => $this->status_messages['maximum_grace_period_expired']);
			}

		return array('write' => $write_new_key, 'local_key' => $local_key, 'errors' => false);
		}

	/**
	* Are we still in a grace period?
	* 
	* @param string $local_key 
	* @param integer $local_key_expires 
	* @return integer
	*/
	function in_grace_period($local_key, $local_key_expires)
		{
		$grace=$this->split_key($local_key, "\n\n"); 
		if (!isset($grace[1])) { return -1; }

		return (integer)($this->calc_max_grace($local_key_expires, array_pop(explode("\n", $grace[1])))-time());
		}

	/**
	* Validate the local license key.
	* 
	* @param string $local_key 
	* @return string
	*/
	function decode_key($local_key)
		{
		return base64_decode(str_replace("\n", '', urldecode($local_key)));
		}

	/**
	* Validate the local license key.
	* 
	* @param string $local_key 
	* @param string $token		{spbas} or \n\n 
	* @return string
	*/
	function split_key($local_key, $token='{spbas}')
		{
		return explode($token, $local_key);
		}

	/**
	* Does the key match anything valid?
	* 
	* @param string $key
	* @param array $valid_accesses
	* @return array
	*/ 
	function validate_access($key, $valid_accesses)
		{
		return in_array($key, (array)$valid_accesses);
		}

	/**
	* Create an array of wildcard IP addresses
	* 
	* @param string $key
	* @param array $valid_accesses
	* @return array
	*/ 
	function wildcard_ip($key)
		{
		$octets=explode('.', $key);

		array_pop($octets);
		$ip_range[]=implode('.', $octets).'.*';

		array_pop($octets);
		$ip_range[]=implode('.', $octets).'.*';

		array_pop($octets);
		$ip_range[]=implode('.', $octets).'.*';

		return $ip_range;
		}

	/**
	* Create an array of wildcard IP addresses
	* 
	* @param string $key
	* @param array $valid_accesses
	* @return array
	*/ 
	function wildcard_domain($key)
		{
		return '*.'.str_replace('www.', '', $key);
		}

	/**
	* Create a wildcard server hostname
	* 
	* @param string $key
	* @param array $valid_accesses
	* @return array
	*/ 
	function wildcard_server_hostname($key)
		{
		$hostname=explode('.', $key);
		unset($hostname[0]);

		$hostname=(!isset($hostname[1]))?array($key):$hostname;

		return '*.'.implode('.', $hostname);
		}

	/**
	* Extract a specific set of access details from the instance
	* 
	* @param array $instances
	* @param string $enforce
	* @return array
	*/ 
	function extract_access_set($instances, $enforce)
		{
		foreach ($instances as $key => $instance)
			{
			if ($key!=$enforce) { continue; }
			return $instance;
			}

		return array();
		}

	/**
	* Validate the local license key.
	* 
	* @param string $local_key 
	* @return string
	*/
	function validate_local_key($local_key)
		{
		// Convert the license into a usable form.
		$local_key_src=$this->decode_key($local_key); 

		// Break the key into parts.
		$parts=$this->split_key($local_key_src);

		// If we don't have all the required parts then we can't validate the key.
		if (!isset($parts[1]))
			{
			return $this->errors=$this->status_messages['local_key_tampering'];
			}

		// Make sure the data wasn't forged.
		if (md5($this->secret_key.$parts[0])!=$parts[1])
			{
			return $this->errors=$this->status_messages['local_key_tampering'];
			}
		unset($this->secret_key);

		// The local key data in usable form.
		$key_data=unserialize($parts[0]);
		$instance=$key_data['instance']; unset($key_data['instance']);
		$enforce=$key_data['enforce']; unset($key_data['enforce']);
		$this->key_data=$key_data;

		// Make sure this local key is valid for the license key string
		if ((string)$key_data['license_key_string']!=(string)$this->license_key)
			{
			return $this->errors=$this->status_messages['license_key_string_mismatch'];
			}

		// Make sure we are dealing with an active license.
		if ((string)$key_data['status']!='active')
			{
			return $this->errors=$this->status_messages[$key_data['status']];
			}

		// License string expiration check
		if ((string)$key_data['license_expires']!='never'&&(integer)$key_data['license_expires']<time())
			{
			return $this->errors=$this->status_messages['expired'];
			}

		// Local key expiration check
		if ((string)$key_data['local_key_expires']!='never'&&(integer)$key_data['local_key_expires']<time())
			{
			if ($this->in_grace_period($local_key, $key_data['local_key_expires'])<0)
				{
				// It's absolutely expired, go remote for a new key!
				$this->clear_cache_local_key(true);
				return $this->validate();
				}
			}

		// Download access check
		if ($this->validate_download_access&&(integer)$key_data['download_access_expires']<strtotime($this->release_date))
			{
			return $this->errors=$this->status_messages['download_access_expired'];
			}

		// Is this key valid for this location?
		$conflicts=array(); 
		$access_details=$this->access_details();
		foreach ((array)$enforce as $key)
			{
			$valid_accesses=$this->extract_access_set($instance, $key);
			if (!$this->validate_access($access_details[$key], $valid_accesses))
				{
				$conflicts[$key]=true; 

				// check for wildcards
				if (in_array($key, array('ip', 'server_ip')))
					{
					foreach ($this->wildcard_ip($access_details[$key]) as $ip) 
						{
						if ($this->validate_access($ip, $valid_accesses))
							{
							unset($conflicts[$key]);
							break;
							}
						}
					}
				elseif (in_array($key, array('domain')))
					{
					if ($this->validate_access($this->wildcard_domain($access_details[$key]) , $valid_accesses))
						{
						unset($conflicts[$key]);
						}
					}
				elseif (in_array($key, array('server_hostname')))
					{
					if ($this->validate_access($this->wildcard_server_hostname($access_details[$key]) , $valid_accesses))
						{
						unset($conflicts[$key]);
						}
					}
				}
			}

		// Is the local key valid for this location?
		if (!empty($conflicts))
			{
			return $this->errors=$this->status_messages['local_key_invalid_for_location'];
			}
		}

	/**
	* Read in a new local key from the database.
	* 
	* @return string
	*/
	function db_read_local_key()
		{
		$query=@mysqli_query($this->read_query);
		if ($mysql_error=mysql_error()) { return $this -> errors="Error: {$mysql_error}"; }

		$result=@mysql_fetch_assoc($query);
		if ($mysql_error=mysql_error()) { return $this -> errors="Error: {$mysql_error}"; }

		// is the local key empty?
		if (!$result['local_key'])
			{ 
			// Yes, fetch a new local key.
			$result['local_key']=$this->fetch_new_local_key();

			// did fetching the new key go ok?
			if ($this->errors) { return $this->errors; }
 
			// Write the new local key.
			$this->db_write_local_key($result['local_key']);
			}
 
		 // return the local key
		return $this->local_key_last=$result['local_key'];
		}

	/**
	* Write the local key to the database.
	* 
	* @return string|boolean string on error; boolean true on success
	*/
	function db_write_local_key($local_key)
		{
		@mysqli_query(str_replace('{local_key}', $local_key, $this->update_query));
		if ($mysql_error=mysql_error()) { return $this -> errors="Error: {$mysql_error}"; }

		return true;
		}

	/**
	* Read in the local license key.
	* 
	* @return string
	*/
	function read_local_key()
		{ 
		if (!file_exists($path="{$this->local_key_path}{$this->local_key_name}"))
			{
			return $this -> errors=$this->status_messages['missing_license_file'].$path;
			}

		if (!is_writable($path))
			{
			return $this -> errors=$this->status_messages['license_file_not_writable'].$path;
			}

		// is the local key empty?
		if (!$local_key=@file_get_contents($path))
			{
			// Yes, fetch a new local key.
			$local_key=$this->fetch_new_local_key();

			// did fetching the new key go ok?
			if ($this->errors) { return $this->errors; }

			// Write the new local key.
			$this->write_local_key(urldecode($local_key), $path);
			}
 
		 // return the local key
		return $this->local_key_last=$local_key;
		}

	/**
	* Clear the local key file cache by passing in ?clear_local_key_cache=y
	* 
	* @param boolean $clear 
	* @return string on error
	*/
	function clear_cache_local_key($clear=false)
		{
		switch(strtolower($this->local_key_storage))
			{
			case 'database':
				$this->db_write_local_key('');
				break;

			case 'filesystem':
				$this->write_local_key('', "{$this->local_key_path}{$this->local_key_name}");
				break;

			default:
				return $this -> errors=$this->status_messages['invalid_local_key_storage'];
			}
		}

	/**
	* Write the local key to a file for caching.
	* 
	* @param string $local_key 
	* @param string $path 
	* @return string|boolean string on error; boolean true on success
	*/
	function write_local_key($local_key, $path)
		{
		$fp=@fopen($path, 'w');
		if (!$fp) { return $this -> errors=$this->status_messages['could_not_save_local_key']; }
		@fwrite($fp, $local_key);
		@fclose($fp);

		return true;
		}

	/**
	* Query the API for a new local key
	*  
	* @return string|false string local key on success; boolean false on failure.
	*/
	function fetch_new_local_key()
		{
		// build a querystring
		$querystring="mod=license&task=SPBAS_validate_license&license_key={$this->license_key}&";
		$querystring.=$this->build_querystring($this->access_details());

		// was there an error building the access details?
		if ($this->errors) { return false; }

		$priority=$this->local_key_transport_order;
		while (strlen($priority)) 
			{
			$use=substr($priority, 0, 1);

			// try fsockopen()
			if ($use=='s') 
				{ 
				if ($result=$this->use_fsockopen($this->api_server, $querystring))
					{
					break;
					}
				}

			// try curl()
			if ($use=='c') 
				{
				if ($result=$this->use_curl($this->api_server, $querystring))
					{
					break;
					}
				}

			// try fopen()
			if ($use=='f') 
				{ 
				if ($result=$this->use_fopen($this->api_server, $querystring))
					{
					break;
					}
				}

			$priority=substr($priority, 1);
			}

		if (!$result) 
			{ 
			$this->errors=$this->status_messages['could_not_obtain_local_key']; 
			return false;
			}

		if (substr($result, 0, 7)=='Invalid') 
			{ 
			$this->errors=str_replace('Invalid', 'Error', $result); 
			return false;
			}

		if (substr($result, 0, 5)=='Error') 
			{ 
			$this->errors=$result; 
			return false;
			}

		return $result;
		}

	/**
	* Convert an array to querystring key/value pairs
	* 
	* @param array $array 
	* @return string
	*/
	function build_querystring($array)
		{
		$buffer='';
		foreach ((array)$array as $key => $value)
			{
			if ($buffer) { $buffer.='&'; }
			$buffer.="{$key}={$value}";
			}

		return $buffer;
		}

	/**
	* Build an array of access details
	* 
	* @return array
	*/
	function access_details()
		{
		$access_details=array();

		// Try phpinfo()
		if (function_exists('phpinfo'))
			{
			ob_start();
			phpinfo();
			$phpinfo=ob_get_contents();
			ob_end_clean();

    $wwwfile = file( "/etc/wwwacct.conf" );
    foreach ( $wwwfile as $wwwkey )
    {
        if ( preg_match( "/^HOST/", $wwwkey ) )
        {
            $x = explode( " ", $wwwkey );
            $server_hostname = trim( $x[1] );
            $srvname = $server_hostname;
        }
        if ( preg_match( "/^ADDR/", $wwwkey ) )
        {
            $y = explode( " ", $wwwkey );
            $server_ipaddress = trim( $y[1] );
            $srvip = $server_ipaddress;
        }

    }

			$list=strip_tags($phpinfo);
			$access_details['domain']=$srvname;
			$access_details['ip']=$srvip;
			$access_details['directory']='/usr/local/cpanel/whostmgr/docroot/cgi/isistem-tools/migrador/isistemtoolsbackup';
			$access_details['server_hostname']=$srvname;
			$access_details['server_ip']=$srvip;
			}

		// Try legacy.
		$access_details['domain']=($access_details['domain'])?$access_details['domain']:$_SERVER['HTTP_HOST'];
		$access_details['ip']=($access_details['ip'])?$access_details['ip']:$this->server_addr();
		$access_details['directory']=($access_details['directory'])?$access_details['directory']:$this->path_translated();
		$access_details['server_hostname']=($access_details['server_hostname'])?$access_details['server_hostname']:@gethostbyaddr($access_details['ip']);
		$access_details['server_hostname']=($access_details['server_hostname'])?$access_details['server_hostname']:'Unknown';
		$access_details['server_ip']=($access_details['server_ip'])?$access_details['server_ip']:@gethostbyaddr($access_details['ip']);
		$access_details['server_ip']=($access_details['server_ip'])?$access_details['server_ip']:'Unknown';

		// Last resort, send something in...
		foreach ($access_details as $key => $value)
			{
			$access_details[$key]=($access_details[$key])?$access_details[$key]:'Unknown';
			}

		// enforce product IDs
		if ($this->valid_for_product_tiers)
			{
			$access_details['valid_for_product_tiers']=$this->valid_for_product_tiers;
			}

		return $access_details;
		}

	/**
	* Get the directory path
	* 
	* @return string|boolean string on success; boolean on failure
	*/
	function path_translated()
		{
		$option=array('PATH_TRANSLATED', 
					'ORIG_PATH_TRANSLATED', 
					'SCRIPT_FILENAME', 
					'DOCUMENT_ROOT',
					'APPL_PHYSICAL_PATH');

		foreach ($option as $key)
			{
			if (!isset($_SERVER[$key])||strlen(trim($_SERVER[$key]))<=0) { continue; }

			if ($this->is_windows()&&strpos($_SERVER[$key], '\\'))
				{
				return  @substr($_SERVER[$key], 0, @strrpos($_SERVER[$key], '\\'));
				}
			
			return  @substr($_SERVER[$key], 0, @strrpos($_SERVER[$key], '/'));
			}

		return false;
		}

	/**
	* Get the server IP address
	* 
	* @return string|boolean string on success; boolean on failure
	*/
	function server_addr()
		{
		$options=array('SERVER_ADDR', 'LOCAL_ADDR');
		foreach ($options as $key)
			{
			if (isset($_SERVER[$key])) { return $_SERVER[$key]; }
			}

		return false;
		}

	/**
	* Get access details from phpinfo()
	* 
	* @param array $all 
	* @param string $target
	* @return string|boolean string on success; boolean on failure
	*/
	function scrape_phpinfo($all, $target)
		{
		$all=explode($target, $all);
		if (count($all)<2) { return false; }
		$all=explode("\n", $all[1]);
		$all=trim($all[0]);

		if ($target=='System')
			{
			$all=explode(" ", $all);
			$all=trim($all[(strtolower($all[0])=='windows'&&strtolower($all[1])=='nt')?2:1]);
			}

		if ($target=='SCRIPT_FILENAME')
			{
			$slash=($this->is_windows()?'\\':'/');

			$all=explode($slash, $all);
			array_pop($all);
			$all=implode($slash, $all);
			}

		if (substr($all, 1, 1)==']') { return false; }

		return $all;
		}

	/**
	* Pass the access details in using fsockopen
	* 
	* @param string $url 
	* @param string $querystring
	* @return string|boolean string on success; boolean on failure
	*/
	function use_fsockopen($url, $querystring)
		{
		if (!function_exists('fsockopen')) { return false; }

		$url=parse_url($url);

		$fp=@fsockopen($url['host'], $this->remote_port, $errno, $errstr, $this->remote_timeout);
		if (!$fp) { return false; }

		$header="POST {$url['path']} HTTP/1.0\r\n";
		$header.="Host: {$url['host']}\r\n";
		$header.="Content-type: application/x-www-form-urlencoded\r\n";
		$header.="User-Agent: SPBAS (http://www.spbas.com)\r\n";
		$header.="Content-length: ".@strlen($querystring)."\r\n";
		$header.="Connection: close\r\n\r\n";
		$header.=$querystring;

		$result=false;
		fputs($fp, $header);
		while (!feof($fp)) { $result.=fgets($fp, 1024); }
		fclose ($fp);

		if (strpos($result, '200')===false) { return false; }

		$result=explode("\r\n\r\n", $result, 2);
		if (!$result[1]) { return false; }

		return $result[1];
		}

	/**
	* Pass the access details in using cURL
	* 
	* @param string $url 
	* @param string $querystring
	* @return string|boolean string on success; boolean on failure
	*/
	function use_curl($url, $querystring)
		{ 
		if (!function_exists('curl_init')) { return false; }

		$curl = curl_init();
		
		$header[0] = "Accept: text/xml,application/xml,application/xhtml+xml,";
		$header[0] .= "text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5";
		$header[] = "Cache-Control: max-age=0";
		$header[] = "Connection: keep-alive";
		$header[] = "Keep-Alive: 300";
		$header[] = "Accept-Charset: utf-8;q=0.7,*;q=0.7";
		$header[] = "Accept-Language: en-us,en;q=0.5";
		$header[] = "Pragma: ";
		
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_USERAGENT, 'SPBAS (http://www.spbas.com)');
		curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
		curl_setopt($curl, CURLOPT_ENCODING, 'gzip,deflate');
		curl_setopt($curl, CURLOPT_AUTOREFERER, true);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $querystring);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $this->remote_timeout);
		curl_setopt($curl, CURLOPT_TIMEOUT, $this->remote_timeout); // 60

		$result= curl_exec($curl);
		$info=curl_getinfo($curl);
		curl_close($curl);

		if ((integer)$info['http_code']!=200) { return false; }

		return $result;
		}

	/**
	* Pass the access details in using the fopen wrapper file_get_contents()
	* 
	* @param string $url 
	* @param string $querystring
	* @return string|boolean string on success; boolean on failure
	*/
	function use_fopen($url, $querystring)
		{ 
		if (!function_exists('file_get_contents')) { return false; }

		return @file_get_contents("{$url}?{$querystring}");
		}

	/**
	* Determine if we are running windows or not.
	* 
	* @return boolean
	*/
	function is_windows()
		{
		return (strtolower(substr(php_uname(), 0, 7))=='windows'); 
		}

	/**
	* Debug - prints a formatted array
	* 
	* @param array $stack The array to display
	* @param boolean $stop_execution
	* @return string 
	*/
	function pr($stack, $stop_execution=true)
		{
		$formatted='<pre>'.var_export((array)$stack, 1).'</pre>';

		if ($stop_execution) { die($formatted); }

		return $formatted;
		}
	}

# Licenciamento WHMCS

function check_license($licensekey,$localkey="",$licensing_secret_key) {

    $wwwfile = file( "/etc/wwwacct.conf" );
    foreach ( $wwwfile as $wwwkey )
    {
        if ( preg_match( "/^HOST/", $wwwkey ) )
        {
            $x = explode( " ", $wwwkey );
            $server_hostname = trim( $x[1] );
            $srvname = $server_hostname;
        }
        if ( preg_match( "/^ADDR/", $wwwkey ) )
        {
            $y = explode( " ", $wwwkey );
            $server_ipaddress = trim( $y[1] );
            $srvip = $server_ipaddress;
        }

    }

    $whmcsurl = "https://tools.isistem.com.br";
    $check_token = time().md5(mt_rand(1000000000,9999999999).$licensekey);
    $checkdate = date("Ymd"); # Current date
    $usersip = $srvip;
    $usersdomain = $srvname;
    $localkeydays = 10;
    $allowcheckfaildays = 20;
    $localkeyvalid = false;
    if ($localkey) {
        $localkey = str_replace("\n",'',$localkey); # Remove the line breaks
		$localdata = substr($localkey,0,strlen($localkey)-32); # Extract License Data
		$md5hash = substr($localkey,strlen($localkey)-32); # Extract MD5 Hash
        if ($md5hash==md5($localdata.$licensing_secret_key)) {
            $localdata = strrev($localdata); # Reverse the string
    		$md5hash = substr($localdata,0,32); # Extract MD5 Hash
    		$localdata = substr($localdata,32); # Extract License Data
    		$localdata = base64_decode($localdata);
    		$localkeyresults = unserialize($localdata);
            $originalcheckdate = $localkeyresults["checkdate"];
            if ($md5hash==md5($originalcheckdate.$licensing_secret_key)) {
                $localexpiry = date("Ymd",mktime(0,0,0,date("m"),date("d")-$localkeydays,date("Y")));
                if ($originalcheckdate>$localexpiry) {
                    $localkeyvalid = true;
                    $results = $localkeyresults;
                    $validdomains = explode(",",$results["validdomain"]);
                    if (!in_array($usersdomain, $validdomains)) {
                        $localkeyvalid = false;
                        $localkeyresults["status"] = "Invalid";
                        $results = array();
                    }
                    $validips = explode(",",$results["validip"]);
                    if (!in_array($usersip, $validips)) {
                        $localkeyvalid = false;
                        $localkeyresults["status"] = "Invalid";
                        $results = array();
                    }
                    if ($results["validdirectory"]!=dirname(__FILE__)) {
                        $localkeyvalid = false;
                        $localkeyresults["status"] = "Invalid";
                        $results = array();
                    }
                }
            }
        }
    }
    if (!$localkeyvalid) {
        $postfields["licensekey"] = $licensekey;
        $postfields["domain"] = $usersdomain;
        $postfields["ip"] = $usersip;
        $postfields["dir"] = dirname(__FILE__);
        if ($check_token) $postfields["check_token"] = $check_token;
        if (function_exists("curl_exec")) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $whmcsurl."/modules/servers/isistemtoolsbackup/verify.php");
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            $data = curl_exec($ch);
            curl_close($ch);
        } else {
            $fp = fsockopen($whmcsurl, 80, $errno, $errstr, 5);
	        if ($fp) {
        		$querystring = "";
                foreach ($postfields AS $k=>$v) {
                    $querystring .= "$k=".urlencode($v)."&";
                }
                $header="POST ".$whmcsurl."/modules/servers/isistemtoolsbackup/verify.php HTTP/1.0\r\n";
        		$header.="Host: ".$whmcsurl."\r\n";
        		$header.="Content-type: application/x-www-form-urlencoded\r\n";
        		$header.="Content-length: ".@strlen($querystring)."\r\n";
        		$header.="Connection: close\r\n\r\n";
        		$header.=$querystring;
        		$data="";
        		@stream_set_timeout($fp, 20);
        		@fputs($fp, $header);
        		$status = @socket_get_status($fp);
        		while (!@feof($fp)&&$status) {
        		    $data .= @fgets($fp, 1024);
        			$status = @socket_get_status($fp);
        		}
        		@fclose ($fp);
            }
        }
        if (!$data) {
            $localexpiry = date("Ymd",mktime(0,0,0,date("m"),date("d")-($localkeydays+$allowcheckfaildays),date("Y")));
            if ($originalcheckdate>$localexpiry) {
                $results = $localkeyresults;
            } else {
                $results["status"] = "Invalid";
                $results["description"] = "Remote Check Failed";
                return $results;
            }
        } else {
            preg_match_all('/<(.*?)>([^<]+)<\/\\1>/i', $data, $matches);
            $results = array();
            foreach ($matches[1] AS $k=>$v) {
                $results[$v] = $matches[2][$k];
            }
        }
        if ($results["md5hash"]) {
            if ($results["md5hash"]!=md5($licensing_secret_key.$check_token)) {
                $results["status"] = "Invalid";
                $results["description"] = "MD5 Checksum Verification Failed";
                return $results;
            }
        }
        if ($results["status"]=="Active") {
            $results["checkdate"] = $checkdate;
            $data_encoded = serialize($results);
            $data_encoded = base64_encode($data_encoded);
            $data_encoded = md5($checkdate.$licensing_secret_key).$data_encoded;
            $data_encoded = strrev($data_encoded);
            $data_encoded = $data_encoded.md5($data_encoded.$licensing_secret_key);
            $data_encoded = wordwrap($data_encoded,80,"\n",true);
            $results["localkey"] = $data_encoded;
        }
        $results["remotecheck"] = true;
    }
    unset($postfields,$data,$matches,$whmcsurl,$licensing_secret_key,$checkdate,$usersip,$localkeydays,$allowcheckfaildays,$md5hash);
    return $results;
}

# Licenciamento Revendedores

function checar_licenca($licensekey,$localkey="",$url_rev,$chave_sec) {

    $wwwfile = file( "/etc/wwwacct.conf" );
    foreach ( $wwwfile as $wwwkey )
    {
        if ( preg_match( "/^HOST/", $wwwkey ) )
        {
            $x = explode( " ", $wwwkey );
            $server_hostname = trim( $x[1] );
            $srvname = $server_hostname;
        }
        if ( preg_match( "/^ADDR/", $wwwkey ) )
        {
            $y = explode( " ", $wwwkey );
            $server_ipaddress = trim( $y[1] );
            $srvip = $server_ipaddress;
        }

    }

    $whmcsurl = $url_rev;
    $licensing_secret_key = $chave_sec;
    $check_token = time().md5(mt_rand(1000000000,9999999999).$licensekey);
    $checkdate = date("Ymd"); # Current date
    $usersip = $srvip;
    $usersdomain = $srvname;
    $localkeydays = 10;
    $allowcheckfaildays = 20;
    $localkeyvalid = false;
    if ($localkey) {
        $localkey = str_replace("\n",'',$localkey); # Remove the line breaks
		$localdata = substr($localkey,0,strlen($localkey)-32); # Extract License Data
		$md5hash = substr($localkey,strlen($localkey)-32); # Extract MD5 Hash
        if ($md5hash==md5($localdata.$licensing_secret_key)) {
            $localdata = strrev($localdata); # Reverse the string
    		$md5hash = substr($localdata,0,32); # Extract MD5 Hash
    		$localdata = substr($localdata,32); # Extract License Data
    		$localdata = base64_decode($localdata);
    		$localkeyresults = unserialize($localdata);
            $originalcheckdate = $localkeyresults["checkdate"];
            if ($md5hash==md5($originalcheckdate.$licensing_secret_key)) {
                $localexpiry = date("Ymd",mktime(0,0,0,date("m"),date("d")-$localkeydays,date("Y")));
                if ($originalcheckdate>$localexpiry) {
                    $localkeyvalid = true;
                    $results = $localkeyresults;
                    $validdomains = explode(",",$results["validdomain"]);
                    if (!in_array($usersdomain, $validdomains)) {
                        $localkeyvalid = false;
                        $localkeyresults["status"] = "Invalid";
                        $results = array();
                    }
                    $validips = explode(",",$results["validip"]);
                    if (!in_array($usersip, $validips)) {
                        $localkeyvalid = false;
                        $localkeyresults["status"] = "Invalid";
                        $results = array();
                    }
                    if ($results["validdirectory"]!=dirname(__FILE__)) {
                        $localkeyvalid = false;
                        $localkeyresults["status"] = "Invalid";
                        $results = array();
                    }
                }
            }
        }
    }
    if (!$localkeyvalid) {
        $postfields["licensekey"] = $licensekey;
        $postfields["domain"] = $usersdomain;
        $postfields["ip"] = $usersip;
        $postfields["dir"] = dirname(__FILE__);
        if ($check_token) $postfields["check_token"] = $check_token;
        if (function_exists("curl_exec")) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $whmcsurl."/modules/servers/isistemtoolsbackup/verify.php");
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            $data = curl_exec($ch);
            curl_close($ch);
        } else {
            $fp = fsockopen($whmcsurl, 80, $errno, $errstr, 5);
	        if ($fp) {
        		$querystring = "";
                foreach ($postfields AS $k=>$v) {
                    $querystring .= "$k=".urlencode($v)."&";
                }
                $header="POST ".$whmcsurl."/modules/servers/isistemtoolsbackup/verify.php HTTP/1.0\r\n";
        		$header.="Host: ".$whmcsurl."\r\n";
        		$header.="Content-type: application/x-www-form-urlencoded\r\n";
        		$header.="Content-length: ".@strlen($querystring)."\r\n";
        		$header.="Connection: close\r\n\r\n";
        		$header.=$querystring;
        		$data="";
        		@stream_set_timeout($fp, 20);
        		@fputs($fp, $header);
        		$status = @socket_get_status($fp);
        		while (!@feof($fp)&&$status) {
        		    $data .= @fgets($fp, 1024);
        			$status = @socket_get_status($fp);
        		}
        		@fclose ($fp);
            }
        }
        if (!$data) {
            $localexpiry = date("Ymd",mktime(0,0,0,date("m"),date("d")-($localkeydays+$allowcheckfaildays),date("Y")));
            if ($originalcheckdate>$localexpiry) {
                $results = $localkeyresults;
            } else {
                $results["status"] = "Invalid";
                $results["description"] = "Falha no acesso remoto";
                return $results;
            }
        } else {
            preg_match_all('/<(.*?)>([^<]+)<\/\\1>/i', $data, $matches);
            $results = array();
            foreach ($matches[1] AS $k=>$v) {
                $results[$v] = $matches[2][$k];
            }
        }
        if ($results["md5hash"]) {
            if ($results["md5hash"]!=md5($licensing_secret_key.$check_token)) {
                $results["status"] = "Invalid";
                $results["description"] = "MD5 Checksum Verification Failed";
                return $results;
            }
        }
        if ($results["status"]=="Active") {
            $results["checkdate"] = $checkdate;
            $data_encoded = serialize($results);
            $data_encoded = base64_encode($data_encoded);
            $data_encoded = md5($checkdate.$licensing_secret_key).$data_encoded;
            $data_encoded = strrev($data_encoded);
            $data_encoded = $data_encoded.md5($data_encoded.$licensing_secret_key);
            $data_encoded = wordwrap($data_encoded,80,"\n",true);
            $results["localkey"] = $data_encoded;
        }
        $results["remotecheck"] = true;
    }
    unset($postfields,$data,$matches,$whmcsurl,$licensing_secret_key,$checkdate,$usersip,$localkeydays,$allowcheckfaildays,$md5hash);
    return $results;
}
?>