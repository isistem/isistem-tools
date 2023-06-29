#!/bin/bash

# Isistem Tools Backup
# http://tools.isistem.com.br

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
	echo -e "${D}-t${R} to restore all: dbs, dns, emails, files"
	echo -e "${D}-a${R} to restore just files"
	echo -e "${D}-w${R} to restore folder public html"
	echo -e "${D}-m${R} to restore emails only"
	echo -e "${D}-q${R} to restore mysql databases"
	echo -e "${D}-d${R} to restore dns zones only"

	}

	third_arg_use(){

	echo -e "Use only numbers from ${D}1${R} to ${D}31${R} to restore daily backup"
	echo -e "Use only numbers from ${D}1${R} to ${D}5${R} to restore weekly backup"
	echo -e "Use only numbers from ${D}1${R} to ${D}12${R} to restore monthly backup"

	}


	if [ -z $1 ]; then
	echo -e "${V}Voce nao especificou repositorio do qual quer restaurar backups!${R}"
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


	if [ "$2" != "-t" ] && [ "$2" != "-a" ] && [ "$2" != "-w" ] &&
        [ "$2" != "-m" ] && [ "$2" != "-q" ] && [ "$2" != "-d" ]; then
	echo -e "${V}Argumento digitado e invalido!${R}"
        second_arg_use
	exit
	fi

	if [ -z $3 ]; then
	echo -e "${V}Especificar o periodo que voce deseja restaurar backups!${R}"
        third_arg_use
	exit
	fi

        if [[ -n ${3//[0-9]} ]]; then
        echo -e "${V}Argumento digitado e invalido!${R}"
        third_arg_use
	exit
	fi

	if [ -n "$4" ]; then
        if [[ -n ${4//[0-9]} ]]; then
        echo -e "${V}Argumento digitado e invalido!${R}"
	echo "Para restaurar o backup de tempo específico:"
	echo -e "Use apenas numeros de ${D}0${R} a ${D}23${R}"
	exit
	fi
	fi

	export hoje=`date +"%d-%m-%Y"`

	CLI="/opt/bin/php"
	INI="/usr/local/cpanel/whostmgr/docroot/cgi/isistem-tools/migrador/isistemtoolsbackup//php.ini"
	SCR="/usr/local/cpanel/scripts/isistemtoolsbackup/reversao.php"
	LOG="/var/log/easycpbackup/reversao/${hoje}.txt"

	touch $LOG

	if [[ -t 1 ]]; then
	nohup $CLI -c $INI $SCR $1 $2 $3 $4 | tee $LOG &

	else
	$CLI -c $INI $SCR $1 $2 $3 $4 | tee $LOG

	fi