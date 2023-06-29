#!/bin/sh

# Isistem Tools Backup

V='\033[01;31m'
D='\033[01;32m'
R='\033[0m'

rm -f /tmp/__COMPILACAO__
setup="/root/tmp/setup"
rm -rf $setup >/dev/null 2>&1
mkdir -p $setup

run_cmd() {

	if [ ! -z $2 ]; then
		cd $name
	fi
	$1 >>/tmp/__COMPILACAO__ 2>&1 &
	while [ -e "/proc/$!" ]; do
		sleep 3
		echo -ne "..."
	done

}

exit_code() {

	$1 >>/tmp/__COMPILACAO__ 2>&1 &
	pid=$!
	while echo -n "..."; do sleep 1; done &
	echoer=$!
	trap "kill -9 $echoer" 0
	if wait $pid; then
		echo -e "   [${D} OK ${R}]"
	else
		echo -e "   [${V} ERRO ${R}]"
		echo -e "${V}Houve um erro durante a compilacao do aplicativo!${R}"
		exit 1
	fi

}

if [ ! -e "/opt/curlssl" ]; then

	cd $setup
	echo -ne "\t Instalando o OpenSSL   "
	name="openssl-1.1.1u"
	run_cmd "wget https://www.openssl.org/source/${name}.tar.gz"
	run_cmd "tar zxf ${name}.tar.gz"
	echo -ne "\t Executando o Configure   "
	run_cmd "./config --prefix=/opt/openssl" $name
	echo -ne "\t Executando o Make   "
	run_cmd "make"
	exit_code "make install"
	echo -e "${D}OpenSSL instalado com sucesso${R}"

	cd $setup
	name="curl-8.1.0"
	echo -ne "\t Instalando o Curl   "
	run_cmd "wget https://curl.se/download/${name}.tar.gz"
	run_cmd "tar zxf ${name}.tar.gz"
	echo -ne "\t Executando o Configure   "
	run_cmd "./configure --prefix=/opt/curlssl --with-openssl=/opt/openssl" $name
	echo -ne "\t Executando o Make   "
	run_cmd "make"
	exit_code "make install"
	echo -e "${D}CURL instalado com sucesso${R}"

	cd $setup
	name="sqlite-autoconf-3420000"
	echo -ne "\t Instalando o Sqlite   "
	run_cmd "wget https://www.sqlite.org/2023/${name}.tar.gz"
	run_cmd "tar zxf ${name}.tar.gz"
	echo -ne "\t Executando o Configure   "
	run_cmd "./configure --prefix=/opt/sqlite3" $name
	echo -ne "\t Executando o Make   "
	run_cmd "make"
	exit_code "make install"
	echo -e "${D}SQLITE instalado com sucesso${R}"

fi

export SQLITE_CFLAGS="-I/opt/sqlite3/include"
export SQLITE_LIBS="-L/opt/sqlite3/lib -lsqlite3"
export OPENSSL_CFLAGS="-I/opt/openssl/include"
export OPENSSL_LIBS="-L/opt/openssl/lib -lssl -lcrypto"
export CURL_CFLAGS="-I/opt/curlssl/include"
export CURL_LIBS="-L/opt/curlssl/lib -lcurl"

OS=$(uname -p)
FLAGS=""
if [ $OS = 'x86_64' ]; then
	FLAGS="--with-libdir=lib64"
fi
cd $setup
name="php-8.1.19"
wget https://php.net/distributions/$name.tar.gz
tar zxf ${name}.tar.gz
cd $name
./buildconf --force
nano ./ext/curl/config.m4 #remover função 'PHP_CHECK_LIBRARY'
echo -ne "\t Configurando o PHP  "
./configure --prefix=/opt/php --with-config-file-path=/opt/php/etc --with-curl=/opt/curlssl --with-openssl=/opt/openssl --without-iconv --disable-opcache $FLAGS
make
exit_code "make install"
echo -e "${D}PHP instalado com sucesso${R}"

exit 0
