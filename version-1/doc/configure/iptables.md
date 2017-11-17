# Vibus, версия 1
## Установка и настройка Iptables в CentOS

По-умолчанию в CentOS используется **firewalld**, но некоторые предпочитают использовать **iptables** напрямую. 

Останавливаем, выключаем из автозагрузки и удаляем **firewalld**
```bash
systemctl stop firewalld
systemctl disable firewalld
yum remove firewalld
```
Устанавливаем **iptables-service** и добавляем в автозагрузку
```bash
yum install iptables-services
systemctl enable iptables
```
Очищаем все правила **iptables**
```bash
iptables -P INPUT ACCEPT
iptables -P FORWARD ACCEPT
iptables -P OUTPUT ACCEPT
iptables -t nat -F
iptables -t mangle -F
iptables -F
iptables -X
```
Проверяем, что все правила удалены
```bash
iptables --line -vnL
```
Разрешаем входящие соединения только на порты 21,22,80,443 + ping и traceroute
```bash
# Exceptions to default policy

iptables -A INPUT -p icmp --icmp-type echo-request -j ACCEPT   # PING
iptables -A INPUT -p udp --dport 33435:33525 -j ACCEPT         # TRACEROUTE

iptables -A INPUT -p tcp --dport 21 -j ACCEPT                  # FTP
iptables -A INPUT -p tcp --dport 22 -j ACCEPT                  # SSH
iptables -A INPUT -p tcp --dport 80 -j ACCEPT                  # HTTP
iptables -A INPUT -p tcp --dport 443 -j ACCEPT                 # HTTPS

iptables -A INPUT -m conntrack --ctstate RELATED,ESTABLISHED -j ACCEPT
iptables -A INPUT -m state --state ESTABLISHED,RELATED -j ACCEPT

iptables -A FORWARD -m state --state ESTABLISHED,RELATED -j ACCEPT

# Setting default policies:
iptables -P INPUT DROP
iptables -P FORWARD DROP
iptables -P OUTPUT ACCEPT
```

Проверяем, что все правила верны
```bash
iptables --line -vnL
```

Если есть какое-то правило, которое надо удалить, это можно сделать так
```bash
iptables --line -vnL    # вывод всех правил по номерам
iptables -D INPUT 3     # удаление правила в цепочке INPUT под номером 3
```

Сохраняем правила, для автозагрузки
```bash
service iptables save
```
