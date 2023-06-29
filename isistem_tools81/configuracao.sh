#!/bin/sh

	V='\033[01;31m'
	D='\033[01;32m'
	R='\033[0m'

    fatal_error_exit_now(){
		rm -rf /scripts/isistemtoolsbackup
		exit
    }

    phpshield_cpanel(){

        echo -n "Instalando o SourceGuardian Loader   "

		PHPINI="/usr/local/cpanel/whostmgr/docroot/cgi/isistem_tools81/isistem-tools/migrador/isistemtoolsbackup/php.ini"
		curl -o loaders.linux-x86_64.zip https://www.sourceguardian.com/loaders/download/loaders.linux-x86_64.zip &> /dev/null &
		while [ -e "/proc/$!" ]
		do
		sleep 1
		echo -n "..."
		done

		mkdir -p /opt/lib
        mkdir -p /opt/etc

		unzip -od /opt/lib loaders.linux-x86_64.zip &> /dev/null
		sed -ri 's/^extension_dir = (.+)/extension_dir = "\/opt\/lib"/g' $PHPINI 	

        if grep -i "ixed.5.2.lin" $PHPINI > /dev/null; then
	    sed -i 's/"ixed.5.2.lin"/"ixed.5.3.lin"/g' $PHPINI
		else
			echo 'extension="ixed.5.3.lin"' >> $PHPINI
		fi
		cp -f $PHPINI /opt/etc/

        if [ ! -e "/opt/lib/ixed.5.3.lin" ]; then
	    echo -e "   [${V} ERRO ${R}]"
        echo "Nao foi possivel baixar o SourceGuardian Loader"
	    echo -e "${V}Infelizmente nao e possivel continuar!${R}"
        fatal_error_exit_now

        fi
		echo -e "   [${D} OK ${R}]"
    }

    mysql_admin() {
		SQLPASS=$(openssl rand -hex 8)

		if ! grep -i open-files-limit /etc/my.cnf > /dev/null; then
			echo 'open-files-limit=32000' >> /etc/my.cnf
		fi

		echo -n "Configurando suporte a bancos de dados   "
		sleep 1

		mysql -uroot -e "DROP USER IF EXISTS 'isistemtoolsbackup'@'localhost';" > /dev/null 2>&1
		mysql -uroot -e "CREATE USER 'isistemtoolsbackup'@'localhost';" > /dev/null 2>&1
		mysql -uroot -e "ALTER USER 'isistemtoolsbackup'@'localhost' IDENTIFIED BY '${SQLPASS}';" > /dev/null 2>&1

		HEXPASS=$(mysql -uroot -N -B -e "SELECT HEX(authentication_string) FROM mysql.user WHERE User = 'isistemtoolsbackup';")

		mysql -uroot -e "GRANT ALL PRIVILEGES ON *.* TO 'isistemtoolsbackup'@'localhost' IDENTIFIED BY '${HEXPASS}' WITH GRANT OPTION;" > /dev/null 2>&1
		mysql -uroot -e "FLUSH PRIVILEGES;" > /dev/null 2>&1

		echo -e "${SQLPASS}\n${HEXPASS}" > /usr/local/cpanel/whostmgr/docroot/cgi/isistem_tools81/isistem-tools/migrador/isistemtoolsbackup/sql.ini

		echo -e "........   [${D} OK ${R}]"

		echo -n "Configurando base de dados 'isistemtools'   "
		mysql -B -N -e "
		CREATE DATABASE IF NOT EXISTS isistemtools;
		DROP USER IF EXISTS 'isistemtools_admin'@'localhost';
		CREATE USER 'isistemtools_admin'@'localhost';
		ALTER USER 'isistemtools_admin'@'localhost' IDENTIFIED BY 'OpEe5Sh3GSI523d';
		GRANT ALL PRIVILEGES ON isistemtools.* TO 'isistemtools_admin'@'localhost';
		FLUSH PRIVILEGES;
		USE isistemtools;
		CREATE TABLE IF NOT EXISTS token (
			id INT AUTO_INCREMENT PRIMARY KEY,
			code VARCHAR(255) NOT NULL,
			created DATETIME NOT NULL
		);
		"
		echo -e "........   [${D} OK ${R}]"
	}


	script_config() {

		echo "Verificando e configurando dependencias... "
		sleep 1   

		if [ $? != 0 ] || [ ! -e "/usr/local/cpanel/3rdparty/php/81/bin/php" ] || [ ! -e "/usr/local/cpanel/3rdparty/php/81/bin/php-cgi" ]; then
			echo "O PHP não está instalado ou o caminho do php e php-cgi foi auterado!"
			echo -e "${V}Instale o PHP com suporte a OPENSSL, CURL e SQLITE ativo, antes de continuar!${R}"
			fatal_error_exit_now
		fi

		cp -f isistem-tools /usr/local/cpanel/whostmgr/docroot/cgi/isistem_tools81/isistem-tools/migrador/isistemtoolsbackup/php.ini /opt/etc

		echo "Instalando CSF Firewall"
		cd /usr/src
		rm -fv csf.tgz
		wget https://download.configserver.com/csf.tgz
		tar -xzf csf.tgz
		cd csf
		sh install.sh

		echo -n "Criando diretórios e gerando chave SSH... Pressione 'ENTER':"
		mkdir -p /var/log/isistemtoolsbackup/backup
		mkdir -p /var/log/isistemtoolsbackup/restauracao
		mkdir -p /var/log/isistemtoolsbackup/reversao
		mkdir -p /var/log/isistemtoolsbackup/remocao

		mkdir -p /usr/isistemtoolsbackup/restauracao
		mkdir -p /usr/isistemtoolsbackup/remocao

		if [ ! -e "/usr/local/cpanel/whostmgr/docroot/cgi/isistem_tools81/isistem-tools/migrador/isistemtoolsbackup/modulos/id_rsa" ]; then
			ssh-keygen -t rsa -f /usr/local/cpanel/whostmgr/docroot/cgi/isistem_tools81/isistem-tools/migrador/isistemtoolsbackup/modulos/id_rsa -N "" > /dev/null 2>&1
		fi

		chmod 600 /usr/local/cpanel/whostmgr/docroot/cgi/isistem_tools81/isistem-tools/migrador/isistemtoolsbackup/modulos/id_rsa.pub
		chmod 777 /usr/local/cpanel/whostmgr/docroot/cgi/isistem_tools81/isistem-tools/migrador/isistemtoolsbackup/modulos/config.pm

		echo -e "[${D} OK ${R}]"

		echo -n "Aplicando permissões e criando links simbólicos..."

		declare -a SCRIPTS=('backup' 'restauracao' 'manutencao' 'remocao' 'reversao')
		len=${#SCRIPTS[*]}

		for (( i=0; i<${len}; i++ )); do
			chmod 0755 /usr/local/cpanel/scripts/isistemtoolsbackup/${SCRIPTS[$i]}.sh > /dev/null 2>&1
			unlink /scripts/${SCRIPTS[$i]}.sh > /dev/null 2>&1
			ln -s /usr/local/cpanel/scripts/isistemtoolsbackup/${SCRIPTS[$i]}.sh /scripts/${SCRIPTS[$i]}.sh > /dev/null 2>&1
		done

		nohup cpan -i IO::Socket::SSL > /dev/null 2>&1 &

		echo -e "[${D} OK ${R}]"
	}

    backup_cronjob() {
		echo -ne "Configurando o backup via tarefa agendada... "
		sleep 1

		rm -f /root/cron
		cp -f /var/spool/cron/root /root/cron
		sed -e "/0 0 \* \* \* sh \/usr\/local\/cpanel\/scripts\/isistemtoolsbackup\/backup.sh > \/dev\/null 2>&1/d" </root/cron >/var/spool/cron/root

		if grep -i /usr/local/cpanel/scripts/isistemtoolsbackup/backup.sh /var/spool/cron/root > /dev/null; then
			echo -e "[${D} OK ${R}]"
		else
			crontab -l | sed '/isistemtoolsbackup\/backup.sh/d; $a* * * * * sh /usr/local/cpanel/scripts/isistemtoolsbackup/backup.sh > /tmp/__BACKUP__ 2>&1' | crontab -
			echo -e "[${D} OK ${R}]"
		fi
	}

    system_cronjob() {

		echo -n "Configurando a manutenção automática... "
		sleep 1

		rm -f /root/cron
		cp -f /var/spool/cron/root /root/cron
		sed -e "/0 8 \* \* \* sh \/usr\/local\/cpanel\/scripts\/isistemtoolsbackup\/manutencao.sh > \/dev\/null 2>&1/d" </root/cron >/var/spool/cron/root

		if grep -i /usr/local/cpanel/scripts/isistemtoolsbackup/manutencao.sh /var/spool/cron/root > /dev/null; then
			echo -e "[${D} OK ${R}]"
		else
			crontab -l | sed '/isistemtoolsbackup\/manutencao.sh/d; $a0 0 * * * sh /usr/local/cpanel/scripts/isistemtoolsbackup/manutencao.sh > /tmp/__MANUTENCAO__ 2>&1' | crontab -
			echo -e "[${D} OK ${R}]"
		fi
	}


    mostra_uso(){

	  echo -e "${V}O argumento passado e invalido!${R}"
	  echo "Apenas os seguintes argumentos sao validos:"
	  echo -e "${D}-m${R} criar usuario do MySQL"
	  echo -e "${D}-l${R} instalar o SourceGuardian Loader"
	  echo -e "${D}-c${R} configurar o aplicativo"
	  echo -e "${D}-b${R} setar o backup automatico"
	  echo -e "${D}-j${R} setar a manutencao automatica"
    }

	args=("$@") 
	params=${#args[@]} 

	if [ "$params" -ge "1" ];then

        for (( i=0;i<$params;i++)); do

	      case "${args[${i}]}" in

	        -m) mysql_admin;;
	        -l) phpshield_cpanel;;
	        -c) script_config;;

	        -b) backup_cronjob;;
	        -j) system_cronjob;;

	          *) mostra_uso
	         ;;

	      esac

	    done

	fi
