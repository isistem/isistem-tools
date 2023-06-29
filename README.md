# Ambiente de Desenvolvimento

## Servidor e pré-configuração
Instalar VM com AlmaLinux

Executar as seguintes configurações
```sh
iptables-save > ~/firewall.rules
systemctl stop firewalld.service
systemctl disable firewalld.service
```
```sh
systemctl stop NetworkManager
systemctl disable NetworkManager
```
```sh
yum -y install nano && yum -y install perl && yum -y install dnf && yum -y install git 
```
```sh
nano /etc/selinux/config
```
altere a variavel SELINUX - *SELINUX=disable*

```sh
nano /etc/resolv.conf
```
inclua - *nameserver 8.8.8.8 nameserver 8.8.4.4*

#### Instalação do CPanel
https://docs.cpanel.net/installation-guide/install/

```sh
dnf -y install epel-release && dnf -y install screen && screen -r
```
```sh
cd /home && curl -o latest -L https://securedownloads.cpanel.net/latest && sh latest
```

## Instalação do Isistem Tools
```sh
cd /usr/local/cpanel/whostmgr/docroot/cgi && wget github.com/isistem/isistem-tools-online/raw/main/install_isistem_tools.sh && chmod +x install_isistem_tools.sh && sh install_isistem_tools.sh
```
##### Gerar e configurar accesshash (token)
Manager API Token -> Gerar Token
```sh
nano /root/token.txt
<TOKEN>
```
```sh
nano /usr/local/cpanel/whostmgr/docroot/cgi/isistem-tools/keyfile.itk
<TOKEN>
```
## Desinstalação do Isistem Tools
```sh
/bin/rm -rf /root/isistem-tools-online
/bin/rm -rf /root/isistem_tools.conf
/bin/rm -f /usr/local/cpanel/whostmgr/docroot/addon_plugins/isistem_tools.png
/bin/rm -rf /usr/local/cpanel/whostmgr/docroot/cgi/isistem-tools
/bin/rm -rf /usr/local/cpanel/whostmgr/docroot/cgi/isistem_tools81
/bin/rm -rf /usr/local/cpanel/whostmgr/docroot/cgi/install_isistem_tools.sh
/usr/local/cpanel/bin/unregister_appconfig /var/cpanel/apps/isistem_tools.conf
```
## Notas
##### Documentação 
https://docs.cpanel.net/
https://api.docs.cpanel.net/whm/introduction/
https://api.docs.cpanel.net/openapi/whm/tag/Account-Management/
##### Verificador de Licença Cpanel
https://verify.cpanel.net/app/verify

