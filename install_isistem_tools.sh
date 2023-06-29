#!/bin/bash

#Instalacao do Isistem Tools

V='\033[01;31m'

D='\033[01;32m'

R='\033[0m'

cd /root
git clone https://github.com/isistem/isistem-tools/

cd isistem-tools/isistem_tools81
cp -R isistem-tools /usr/local/cpanel/whostmgr/docroot/cgi

#Movendo o icone para seu local de armazenamento
cd /root/isistem_tools81/isistem-tools/public/icon/
cp isistem_tools.png /usr/local/cpanel/whostmgr/docroot/addon_plugins/

#Movendo arquivo.conf
cd /root/isistem_tools81
cp isistem_tools.conf /root

#Registra o plugin com AppConfig
/usr/local/cpanel/bin/register_appconfig ~/isistem_tools.conf
ls -al /var/cpanel/apps

#Movendo arquivo de pluguin
mkdir /usr/local/cpanel/whostmgr/docroot/cgi/isistem_tools81
chmod 755 /usr/local/cpanel/whostmgr/docroot/cgi/isistem_tools81
cd /root/isistem_tools81
cp -R isistem-tools /usr/local/cpanel/whostmgr/docroot/cgi/isistem_tools81

#Movendo o arquivo .cgi e outros arquivos .sh para seu local de armazenamento
cd /root/isistem_tools81
cp configuracao.sh /usr/local/cpanel/whostmgr/docroot/cgi/isistem_tools81
cp compilacao.sh /usr/local/cpanel/whostmgr/docroot/cgi/isistem_tools81
cp -f addon_isistem_tools.cgi /usr/local/cpanel/whostmgr/docroot/cgi/isistem_tools81

#Dando permissao aos arquivos
cd /usr/local/cpanel/whostmgr/docroot/cgi/isistem_tools81/
chmod 755 configuracao.sh
chmod 755 compilacao.sh
chmod 755 addon_isistem_tools.cgi
sed -i 's|/usr/bin/perl|/usr/local/cpanel/3rdparty/bin/perl|' addon_isistem_tools.cgi

#Configuração
install=/usr/local/cpanel/whostmgr/docroot/cgi/isistem_tools81/

echo -e "${D}Fazendo instalacao do Isistem Tools Backup!${R}"

echo "Podera demorar alguns minutos. Por favor, aguarde"

cd $install

if [ ! -e "$install/configuracao.sh" ]; then

    echo -e "....   [${V} ERRO ${R}]"

    echo -e "${V}Nao foi possivel obter o script de configuracao!${R}"

    exit

fi

echo -e "   [${D} OK ${R}]"

chmod +x configuracao.sh

. ./configuracao.sh -m -l -c -j

echo "AppConfig sendo ativado em Tweak Settings   ...   "

sed -i '/permit_unregistered_apps_as_root/d' /var/cpanel/cpanel.config

echo "permit_unregistered_apps_as_root=1" >>/var/cpanel/cpanel.config

/usr/local/cpanel/etc/init/startcpsrvd

echo -e "${D}Instalacao concluida com sucesso!${R}"
