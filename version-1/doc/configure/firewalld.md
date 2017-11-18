# Vibus, версия 1
## Установка и настройка FirewallD в CentOS
Устанавливаем firewalld
```bash
yum install firewalld
```
Проверяем что он работает
```bash
systemctl status firewalld
```
если не работает, запускаем
```bash
systemctl start firewalld
```
создаем свою зону, я назову её webserver
```bash
firewall-cmd --permanent --new-zone=webserver
firewall-cmd --reload
```
проверяем что она добавилась
```bash
firewall-cmd --get-zones
```
смотрим название интерфейса
```bash
ifconfig -a
```
добавляем интерфейс в зону
```bash
firewall-cmd --zone=webserver --permanent --add-interface=venet0:0
```

чтобы перманентно привязать интерфейс к зоне открываем файл настройки интерфейса
```bash
mcedit /etc/sysconfig/network-scripts/ifcfg-venet0:0
```
и добавляем/меняем там 
```bash
ZONE=webserver
```
перезапускаем сеть и файрвол
```bash
systemctl restart network firewalld
```
проверяем зоны
```bash
firewall-cmd --get-active-zones
```
смотрим доступные сервисы
```bash
firewall-cmd --get-services
```
добавляем нужные
```bash
firewall-cmd --zone=webserver --permanent --add-service=ftp
firewall-cmd --zone=webserver --permanent --add-service=ssh
firewall-cmd --zone=webserver --permanent --add-service=http
firewall-cmd --zone=webserver --permanent --add-service=https
```
применяем правила и смотрим какие сервисы активны
```bash
firewall-cmd --reload
firewall-cmd --zone=webserver --list-all
```
при необходимости удаляем лишние сервисы и применяем правила
```bash
firewall-cmd --zone=webserver --permanent --remove-service=ssh
firewall-cmd --reload
firewall-cmd --zone=webserver --list-all
```
открываем нужные порты
```bash
# ftp
firewall-cmd --zone=webserver --permanent --add-port=21/tcp
# traceroute
firewall-cmd --zone=webserver --permanent --add-port=33435:33525/udp
```
просмотр открытых портов
```bash
firewall-cmd --zone=webserver --list-ports
```
по-умолчанию блокировки icmp (например пинга) нет, если нужно, тогда 
смотрим доступный список icmp
```bash
firewall-cmd --get-icmptypes
```
и блокируем, например пинг
```bash
firewall-cmd --zone=webserver --permanent --add-icmp-block=echo-request
```
другой способ, это поменять поведение с "разрешить все icmp кроме", на "заблокировать все icmp кроме" и разрешить только нужные
```bash
firewall-cmd --zone=webserver --permanent --add-icmp-block-inversion
```
не забываем применять правила
```bash
firewall-cmd --reload
```
теперь если выполнить
```bash
firewall-cmd --zone=webserver --list-all
```
увидим
```text
webserver (active)
  target: default
  icmp-block-inversion: yes <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
  interfaces: venet0
  sources: 
  services: ftp ssh http https
  ports: 8000/tcp
  protocols: 
  masquerade: no
  forward-ports: 
  source-ports: 
  icmp-blocks: 
  rich rules: 
```
и разрешаем только пинг
```bash
firewall-cmd --zone=webserver --permanent --add-icmp-block=echo-request
```

если все работает, назначаем зону нашу зону "по-умолчанию" и проверяем
```bash
firewall-cmd --set-default-zone=webserver
firewall-cmd --get-default-zone
```
если еще firewalld не добавлен, добавляем в автозагрузку
```bash
systemctl enable firewalld
```

Справка по командам тут: [http://www.firewalld.org/](http://www.firewalld.org/documentation/man-pages/firewall-cmd.html)
