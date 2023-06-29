#!/bin/bash

# Isistem Tools Backup

	V='\033[01;31m'
	D='\033[01;32m'
	R='\033[0m'
	

	first_arg_use(){

	echo "Use os seguintes argumentos:"
	echo -e "${D}-d${R} to restore daily backup"
	echo -e "${D}-s${R} to restore weekly backup"
	echo -e "${D}-m${R} to restore monthly backup"

	}

	second_arg_use(){

	echo "Use os seguintes argumentos:"
	echo -e "${D}-c${R} to download and restore backups"
	echo -e "${D}-b${R} to just download backups and not restore them"
	echo -e "${D}-r${R} to just restore backups already downloaded"

	}


	if [ -z $1 ]; then
	echo -e "${V}Voce nao especificou repositorio do qual quer restaurar os backups!${R}"
        first_arg_use
	exit
	fi

	if [ "$1" != "-d" ] && [ "$1" != "-s" ] && [ "$1" != "-m" ]; then
	echo -e "${V}Argumento digitado e invalido!${R}"
        first_arg_use
	exit
	fi

	if [ -z $2 ]; then
	echo -e "${V}Especificar tipo de restauracao: completa ou parcial!${R}"
        second_arg_use
	exit
	fi

	if [ "$2" != "-c" ] && [ "$2" != "-b" ] && [ "$2" != "-r" ]; then
	echo -e "${V}Argumento digitado e invalido!${R}"
        second_arg_use
	exit
	fi

	export hoje=`date +"%d-%m-%Y"`

	CLI="/opt/bin/php"
	INI="/usr/local/cpanel/whostmgr/docroot/cgi/isistem-tools/migrador/isistemtoolsbackup/php.ini"
	SCR="/usr/local/cpanel/scripts/isistemtoolsbackup/restauracao.php"
	LOG="/var/log/isistemtoolsbackup/restauracao/${hoje}.txt"

	touch $LOG

	if [[ -t 1 ]]; then
	nohup $CLI -c $INI $SCR $1 $2 | tee $LOG &

	else
	$CLI -c $INI $SCR $1 $2 | tee $LOG

	fi