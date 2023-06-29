<?php


        if(isset($_POST['processos'])){

        if($_POST['processos'] == "todos"){       
        $tarefas= shell_exec("ps aux| grep \"/usr/local/cpanel/whostmgr/docroot/cgi/isistem-tools/migrador/isistemtoolsbackup\"");
        preg_match_all("/root (.*) 0/U", $tarefas, $matches);
        $kill_command = "";
        foreach($matches[1] as $match){
        $kill_command .= "kill -9 ".$match.";";
        }
        print "Todas as migrações foram finalizadas.<br>";
        }

        elseif($_POST['processos'] == "apenas"){       
        $tarefas= shell_exec("ps aux| grep \"/usr/local/cpanel/whostmgr/docroot/cgi/isistem-tools/migrador/isistemtoolsbackup/solicitar.so\"");
        preg_match_all("/root (.*) 0/U", $tarefas, $matches);

        $kill_command = "";
        foreach($matches[1] as $match){
        $kill_command .= "kill -9 ".$match.";";
        }

        # if(isset($matches[1][0])) shell_exec("kill -9 ".$matches[1][0]);
        # if(isset($matches[1][1])) shell_exec("kill -9 ".$matches[1][1]);
        
        unset($matches);

        $tarefas= shell_exec("ps aux| grep \"/usr/local/cpanel/whostmgr/docroot/cgi/isistem-tools/migrador/isistemtoolsbackup/exigir.so\"");
        preg_match_all("/root (.*) 0/U", $tarefas, $matches);

        $kill_command = "";
        foreach($matches[1] as $match){
        $kill_command .= "kill -9 ".$match.";";
        }

        # if(isset($matches[1][0])) shell_exec("kill -9 ".$matches[1][0]);
        # if(isset($matches[1][1])) shell_exec("kill -9 ".$matches[1][1]);
        
        print "As migrações já iniciadas foram finalizadas.<br>";
                
        
        }

        }


        $LiberaFW = FALSE;
        require("rodape.php");

# if( isset($_POST['processos']) ) { if($_POST['processos'] == "todos") shell_exec($kill_command); }

?>