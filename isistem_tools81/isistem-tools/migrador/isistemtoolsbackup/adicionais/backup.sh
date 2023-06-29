#!/bin/bash

# Isistem Tools Backup

	V='\033[01;31m'
	D='\033[01;32m'
	R='\033[0m'


	export hoje=`date +"%d-%m-%Y"`
        export hora=`date +"%H:%M"`
	agora="${hoje}_${hora}"

	CLI="/opt/bin/php"
	INI="/usr/local/cpanel/whostmgr/docroot/cgi/isistem-tools/migrador/isistemtoolsbackup/php.ini"
	SCR="/usr/local/cpanel/scripts/isistemtoolsbackup/backup.php"
	LOG="/var/log/isistemtoolsbackup/backup/${hoje}.txt"


	if [ -n "$1" ]; then
	if [ "$1" != "-d" ] && [ "$1" != "-s" ] && [ "$1" != "-m" ]; then

	echo -e "${V}Argumento digitado é inválido!${R}"
	echo "Use os seguintes argumentos:"
	echo -e "${D}-d${R} to daily backup"
	echo -e "${D}-s${R} to weekly backup"
	echo -e "${D}-m${R} to monthly backup"

	exit

	fi
	fi


	if [ -n "$2" ]; then

        if [ "$2" != "-i" ] && [ "$2" != "-e" ]; then

        echo -e "${V}Argumento digitado e invalido!${R}"
	echo "Para configurar contas que sera feito o backup:"
	echo -e "Basta digitar ${D}-e${R} (exclusion) ou ${D}-i${R} (inclusion)"
	exit

	fi

        if [ ! -n "$3" ]; then

	echo "Voce escolheu para definir manualmente que as contas"
        echo -e "${V}Voce deve digitar um ou mais nomes de usuario!${R}"
	echo "Nomes de usuário separados usando uma virgula"
	exit

	fi

	fi


	if [ -e "/var/log/isistemtoolsbackup/INICIADO" ]; then

        if [ ! -n "$1" ]; then
        echo -e "${V}Outro backup ja esta em andamento!${R}"
        echo -e "${V}Voce deve esperar para o backup ficar completo!${R}"
        exit
        fi

        fi


        touch $LOG

        CMD="tee -a ${LOG}"

	if [ -e "/var/log/isistemtoolsbackup/MULTIPLO" ]; then
	REL="/var/log/isistemtoolsbackup/backup/${agora}.txt"
        CMD="tee -a ${LOG} | tee -a ${REL}"           
	fi


	if [[ -t 1 ]]; then

	nohup $CLI -c $INI $SCR $1 $2 $3 | $CMD &

	else

	$CLI -c $INI $SCR $1 $2 $3 | $CMD

	fi

        rm -f /var/log/isistemtoolsbackup/INICIADO

	if [ -e "/var/log/isistemtoolsbackup/MULTIPLO" ]; then

        SIZE=$(stat -c%s "$REL")
        if [ "$SIZE" -lt "24" ];then
        rm -f $REL
        fi
       
	fi