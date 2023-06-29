#!/bin/bash

# Isistem Tools Backup

	V='\033[01;31m'
	D='\033[01;32m'
	R='\033[0m'
	

	first_arg_use(){

	echo "Use the following arguments:"
	echo -e "${D}-d${R} to remove daily backups"
	echo -e "${D}-s${R} to remove weekly backups"
	echo -e "${D}-m${R} to remove monthly backups"

	}

	if [ -z $1 ]; then
	echo -e "${V}Você nao especificou repositorio do qual quer excluir backups!${R}"
        first_arg_use
	exit
	fi

	if [ "$1" != "-d" ] && [ "$1" != "-s" ] && [ "$1" != "-m" ]; then
	echo -e "${V}Argumento digitado e invalido!${R}"
        first_arg_use
	exit
	fi

	export hoje=`date +"%d-%m-%Y"`

	CLI="/opt/bin/php"
	INI="/usr/local/cpanel/whostmgr/docroot/easycpbackup/php.ini"
	SCR="/usr/local/cpanel/scripts/easycpbackup/remocao.php"
	LOG="/var/log/easycpbackup/remocao/${hoje}.txt"

	touch $LOG

	if [[ -t 1 ]]; then

	while true
	do

	echo -e "Voce ainda deseja remover todos os backups?"
	echo -ne "Digite ${D}Y${R} ou ${V}N${R}  "

	read resposta
  	case $resposta in

	  [yY]* ) 
	nohup $CLI -c $INI $SCR $1 | tee $LOG &

	break;;

	  [nN]* ) 
          exit;;

	* )  echo -e "${V}Por favor, digite Y ou N!${R}";;

	esac
	done

	else
	$CLI -c $INI $SCR $1 | tee $LOG

	fi

