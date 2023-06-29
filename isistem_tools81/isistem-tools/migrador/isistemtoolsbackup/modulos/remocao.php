<?php

if( ini_get('safe_mode') ){
print "FINALIZADO\n";
exit();
}

else{
set_time_limit(0);
$url = $_SERVER['SCRIPT_FILENAME'];
$parte = explode('/',$url);
system("rm -rvf /".$parte[1]."/".$parte[2]."/public_html/cgi-bin/forcedbackup/");
system("rm -rvf /".$parte[1]."/".$parte[2]."/public_html/forcedbackup/");
print "FINALIZADO\n";
}

?>