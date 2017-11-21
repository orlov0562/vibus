# Vibus, версия 1
## Установка и настройка VPN с использованием xl2tp протокола

### Установка
Устанавливаем xl2tpd и ppp
```bash
yum install epel-release ppp xl2tpd bind-utils
```

### Конфигурация
Делаем бэкап **/etc/xl2tpd/xl2tpd.conf**
```bash
cp /etc/xl2tpd/xl2tpd.conf /etc/xl2tpd/xl2tpd.conf.orig
```
Смотрим и запоминаем ip основного интерфейса
```bash
ifconfig -a
```
Например он будет 5.5.5.5

Редактируем **/etc/xl2tpd/xl2tpd.conf**
```bash
mcedit /etc/xl2tpd/xl2tpd.conf
```
чтобы получилось ( !! не забудьте поменять 5.5.5.5 на свой ip !! )
```text
[global]
port = 1701
listen-addr = 5.5.5.5
[lns l2tpd]
lac = 0.0.0.0-255.255.255.255
ip range = 172.22.100.100-172.22.100.250
local ip = 172.22.100.1
 
require chap = yes
refuse pap = yes
 
require authentication = yes
name = l2tpd
pppoptfile = /etc/ppp/options.xl2tpd
length bit = yes
```
описание опций **/etc/xl2tpd/xl2tpd.conf**
```text
[global]
port = 1701 ;порт на котором работает туннель
listen-addr = 5.5.5.5 ;ip адрес интерфейса на сервере
[lns l2tpd] ;l2tpd — имя можно указать любое
lac = 0.0.0.0-255.255.255.255
ip range = 172.22.100.100-172.22.100.250 ;диапазон ip-алресов, выдаваемых сервером клиентам
local ip = 172.22.100.1 ;адрес сервера в туннеле биндится на интерфейс ppp
 
require chap = yes ;требование аутентификации ppp по протоколу chap
refuse pap = yes ;отказ в аутентификации ppp по протоколу pap
 
require authentication = yes ;требование к клиентам проходить обязательную аутентификацию
name = l2tpd ;имя
pppoptfile = /etc/ppp/options.xl2tpd ;файл содержащий описание опций ppp
length bit = yes ;использование бита длины, указывающего полезную нагрузку пакета
```
Делаем бэкап **/etc/xl2tpd/l2tp-secrets**
```bash
cp /etc/xl2tpd/l2tp-secrets /etc/xl2tpd/l2tp-secrets.orig
```
Редактируем **/etc/xl2tpd/l2tp-secrets**
```bash
mcedit /etc/xl2tpd/l2tp-secrets
```
чтобы получилось
```text
* * *
```
описание опций **/etc/xl2tpd/l2tp-secrets**
```text
# разрешить все , т.к. используется авторизация ppp, 
# детальнее тут: https://habrahabr.ru/company/FastVPS/blog/205162/
*       *       *   
```
Делаем бэкап **/etc/ppp/options.xl2tpd**
```bash
cp /etc/ppp/options.xl2tpd /etc/ppp/options.xl2tpd.orig
```
Редактируем **/etc/ppp/options.xl2tpd**
```bash
mcedit /etc/ppp/options.xl2tpd
```
чтобы получилось
```text
ipcp-accept-local
ipcp-accept-remote
ms-dns  8.8.8.8
ms-dns  8.8.4.4
noccp
aut
idle 1800
mtu 1410
mru 1410
nodefaultroute
debug
lcp-echo-failure 10
lcp-echo-interval 60
proxyarp
connect-delay 5000
logfile /var/log/ppp/ppp.log
```
описание опций **/etc/ppp/options.xl2tpd**
```text
ipcp-accept-local
ipcp-accept-remote
ms-dns  8.8.8.8 # днс сервер
ms-dns  8.8.4.4 # днс сервер
noccp
auth #запрос клиенту на аутентификацию до установления обмена сетевыми пакетами
#crtscts
idle 1800
mtu 1410
mru 1410
nodefaultroute
debug
#lock #создание lock файла для сохранения эксклюзивного доступа к устройству
lcp-echo-failure 10 # количество неудачных echo запросов до того как провести отключение клиента
lcp-echo-interval 60 #интервал echo запросов
proxyarp
connect-delay 5000
logfile /var/log/ppp/ppp.log #логировние
```
### Конфигурация пользователей
Делаем бэкап **/etc/ppp/chap-secrets**
```bash
cp /etc/ppp/chap-secrets /etc/ppp/chap-secrets.orig
```
Настраиваем пользователей в файле **/etc/ppp/chap-secrets**
```bash
mcedit /etc/ppp/chap-secrets
```
В таком формате
```text
# Secrets for authentication using CHAP
# clienе      server secret        IP addresses
 
"{логин}" "l2tpd" "{пароль}" *
```
Теперь можно проверить, запускаем сервис командой
```bash
xl2tpd -D
```
должны увидеть следующее
```text
xl2tpd[27536]: Not looking for kernel SAref support.
xl2tpd[27536]: Using l2tp kernel support.
xl2tpd[27536]: xl2tpd version xl2tpd-1.3.8 started on d5hz.astbiz.com PID:27536
xl2tpd[27536]: Written by Mark Spencer, Copyright (C) 1998, Adtran, Inc.
xl2tpd[27536]: Forked by Scott Balmos and David Stipp, (C) 2001
xl2tpd[27536]: Inherited by Jeff McAdams, (C) 2002
xl2tpd[27536]: Forked again by Xelerance (www.xelerance.com) (C) 2006-2016
xl2tpd[27536]: Listening on IP address 5.5.5.5, port 1701
```
### Настройка файрвола
** настравиваем firewalld **
создаем сервис xl2tpd
```bash
mcedit /etc/firewalld/services/xl2tpd.xml
```
с таким содержимым (если меняли порт в конфигурации, не забудьте поменять порт)
```text
<?xml version="1.0" encoding="utf-8"?>
<service>
  <short>L2TP</short>
  <description>Layer 2 Tunneling Protocol (L2TP)</description>
  <port protocol="udp" port="1701"/>
</service>
```
применяем правила
```bash
echo "net.ipv4.ip_forward = 1"  >> /etc/sysctl.conf
sysctl --system
firewall-cmd --zone=webserver --add-masquerade --permanent 
firewall-cmd --zone=webserver --permanent --add-service=xl2tpd
firewall-cmd --zone=webserver --permanent --direct --add-rule ipv4 nat POSTROUTING 0 -o eth0 -s 172.22.100.0/24 -j MASQUERADE
firewall-cmd --zone=webserver --permanent --direct --add-rule ipv4 filter FORWARD 0 -i eth0 -o ppp0 -m state --state RELATED,ESTABLISHED -j ACCEPT
firewall-cmd --zone=webserver --permanent --direct --add-rule ipv4 filter FORWARD 0 -i ppp0 -o eth0 -j ACCEPT
firewall-cmd --reload 
```
**ИЛИ настравиваем iptables**
```bash
echo "net.ipv4.ip_forward = 1"  >> /etc/sysctl.conf
sysctl --system
iptables -t nat -A POSTROUTING -o eth0 -s 172.22.100.0/24 -j MASQUERADE
iptables -A FORWARD -i eth0 -o ppp0 -m state --state RELATED,ESTABLISHED -j ACCEPT
iptables -A FORWARD -i ppp0 -o eth0 -j ACCEPT
service iptables save
```
### завершение установки

добавляем xl2tpd в автозагрузку
```bash
systemctl enable xl2tpd
```
запускаем
запускаем xl2tpd
```bash
systemctl start xl2tpd
```
