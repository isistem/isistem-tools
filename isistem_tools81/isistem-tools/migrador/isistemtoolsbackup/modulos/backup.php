<?php

if( ini_get('safe_mode') ){
print "O PHP está rodando em safe mode no servidor remoto.<br>Não será possível fazer backup da conta!";
exit();
}

else{
set_time_limit(0);

if (PHP_SAPI == "apache2handler"){
print "O PHP no servidor remoto está rodando como DSO.<br>Não será possível fazer backup da conta!";
exit();
}

$user = $_REQUEST['user'];
$pass = urldecode($_REQUEST['pass']);
$url = $_SERVER['SCRIPT_FILENAME'];
$parte = explode('/',$url);

passthru("export REMOTE_PASSWORD='$pass'; /usr/local/cpanel/scripts/pkgacct ".$parte[2]." /".$parte[1]."/".$parte[2]."/public_html/forcedbackup");

$archive = "/".$parte[1]."/".$parte[2]."/public_html/forcedbackup/cpmove-".$parte[2].".tar.gz";
$arquivo = "cpmove-".$parte[2].".tar.gz";

if(!file_exists($arquivo)){
print "Não foi possível criar o arquivo de backup da conta!";
exit();
}

@exec("/bin/chmod 644 $archive");

$file = "cpmove-".$parte[2].".tar.gz";
print "Arquivo de backup compactado: $arquivo\n";
print "MD5 do arquivo: ".md5_file($file)."\n";
print "\n";

}

?>