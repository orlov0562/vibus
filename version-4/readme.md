# Конфигурация VPS на CentOS 9

## Обновление и добавление репозиториев
```
dnf update -y
dnf install https://dl.fedoraproject.org/pub/epel/epel-release-latest-9.noarch.rpm
dnf install https://rpms.remirepo.net/enterprise/remi-release-9.rpm
```

## Установка и настройка базовых пакетов
```
dnf install mc htop pv curl wget nano tar zip telnet net-tools
```

Настройка редактора по-умолчанию
```
mcedit ~/.bash_profile

# добавляем в конец
export VISUAL="mcedit"
export EDITOR="mcedit"
```

## Устанавливаем паки локалей
```
dnf install langpacks-en glibc-all-langpacks -y
localectl set-locale LANG=en_US.UTF-8
```
это уберет ошибку
```
Failed to set locale, defaulting to C.UTF-8
```


## Отключение SELinux
```
mcedit /etc/sysconfig/selinux

# меняем переменную SELINUX на disabled

SELINUX=disabled
```

## Настройка SSH
На локальном пк генерируем ключи
```
ssh-keygen -f <server-name>
```
смотрим файл <server-name>.pub 
```
cat <server-name>.pub
```
и записываем его содержимое на сервер в файл ~/.ssh/authorized_keys

делаем бекап конфига ssh 
```
mv /etc/ssh/sshd_config /etc/ssh/sshd_config_orig
```
и создаем новый
```
mcedit /etc/ssh/sshd_config
```
с такими содержимым
```
Port 2222

AllowUsers root
DenyGroups no_sshgroup

SyslogFacility AUTHPRIV
PermitRootLogin without-password
PubkeyAuthentication yes
AuthorizedKeysFile      .ssh/authorized_keys
PasswordAuthentication no
ChallengeResponseAuthentication no
UsePAM yes
UseDNS no
LoginGraceTime 1m
ClientAliveInterval 600
ClientAliveCountMax 0

# Accept locale-related environment variables
AcceptEnv LANG LC_CTYPE LC_NUMERIC LC_TIME LC_COLLATE LC_MONETARY LC_MESSAGES
AcceptEnv LC_PAPER LC_NAME LC_ADDRESS LC_TELEPHONE LC_MEASUREMENT
AcceptEnv LC_IDENTIFICATION LC_ALL LANGUAGE
AcceptEnv XMODIFIERS

# override default of no subsystems
Subsystem       sftp    /usr/libexec/openssh/sftp-server
```
Далее, перезапускаем sshd и не закрывая текущую терминальную сессию пробуем залогиниться (во втором окне).
Это нужно чтобы в случае проблем не потерять коннект к серверу.
```
systemctl restart sshd
```

Если планируем логиниться по sftp, то в конец конфига добавляем
```
AllowUsers root www-data

Match User www-data
      ChrootDirectory /var/www/
      ForceCommand internal-sftp
      X11Forwarding no
      AllowTcpForwarding no
      PasswordAuthentication no
```
Для логина нам понадобится паблик ключ, в папке пользователя www-data (см. ниже)
	
Логи по ошибкам подключения SSH можно посмотреть тут: /var/log/secure

## Создание пользователя www-data
Создаем пользователя www-data, группы www-data. Домашний каталог пользователя /var/www. Shell отключен.
```
useradd -d/var/www -s/sbin/nologin www-data
```
другой вариант создания/модификации:
```
useradd www-data
usermod -d /var/www www-data
usermod www-data --shell=/sbin/nologin
```
можно создать отдельный ключ, а можно скопировать ключ root-пользователя который используется для логина по ssh
```
cp -R /root/.ssh /var/www/
chown -R www-data:www-data /var/www/.ssh
```

Если включен ChrootDirectory, то нужно поменять пользователя и права на папку /var/www, т.к.
> ChrootDirectory
> Specifies the pathname of a directory to chroot(2) to after authentication. All components of the pathname must be root-owned directories that are not writable by any other user or group. After the chroot, sshd(8) changes the working directory to the user's home directory.

для этого выполняем
```
chown root:root /var/www
chmod 0755 /var/www
```
	
## Установка Nginx
```
dnf install nginx
systemctl enable nginx
```
если планируем, что nginx будет работать от www-data пользователя, меняем user в nginx.conf
```
nano /etc/nginx/nginx.conf

user www-data;
worker_processes auto;
error_log /var/log/nginx/error.log;
pid /run/nginx.pid;
...
http {
    ...
}
```
далее меняем владельца временной папки (по-умолчанию, там владелец apache)
```
chown www-data:root -R /var/lib/nginx
```
и папки логов (по-умолчанию, там владелец apache)
```
chown www-data:root -R /var/log/nginx
```

## Установка PHP
```
dnf module enable php:remi-7.4
dnf install php php-cli php-common php-fpm php-pdo php-mysqlnd php-xml php-imap php-intl php-json php-bcmath php-mbstring php-pecl-geoip php-pecl-imagick php-redis
systemctl disable httpd
chown www-data:root /var/log/php-fpm
systemctl enable php-fpm
```
если планируем, что php-fpm будет работать от www-data пользователя, меняем user в www.conf
```
nano /etc/php-fpm.d/www.conf
...
user = www-data
group = www-data

listen.owner = www-data
listen.group = www-data
;listen.mode = 0660

;listen.acl_users = apache,nginx
```
listen.acl_users = надо закомментировать иначе владелец сокета не будет изменен


далее меняем владельца папки логов (по-умолчанию, там владелец apache)
```
chown www-data:root -R /var/log/php-fpm
```

## Logrotate

/etc/logrotate.d/php-fpm
```
/var/log/php-fpm/*log 
/var/www/*/logs/php-fpm-*.log
{
    daily
    missingok
    rotate 14
    compress
    delaycompress
    notifempty
    create 0640 www-data adm
    su root www-data
    sharedscripts
    postrotate
	/bin/kill -SIGUSR1 `cat /run/php-fpm/php-fpm.pid 2>/dev/null` 2>/dev/null || true
    endscript
}
```

/etc/logrotate.d/nginx
```
/var/log/nginx/*log 
/var/www/*/logs/nginx-*.log
{
    daily
    missingok
    rotate 14
    compress
    delaycompress
    notifempty
    create 0640 www-data adm
    su root www-data
    sharedscripts
    postrotate
        /bin/kill -USR1 `cat /run/nginx.pid 2>/dev/null` 2>/dev/null || true
    endscript
```

## Установка Letsencrypt
```
cd /tmp
wget https://dl.eff.org/certbot-auto
mv ./certbot-auto /usr/bin/certbot
chmod +x /usr/bin/certbot
```
и добавляем в крон
```
crontab -e

0 0 * * * /usr/bin/certbot renew >/dev/null 2>&1
```
в случае новой конфигурации, можем добавить домен и конфиги в nginx в авто режиме
```
/usr/bin/certbot --nginx
```
или сгенерировать сертификат вручную
```
certbot certonly --webroot \
-w /opt/vibus/sites/user/site.com/public_html \
-d www.site.com \
-d site.com

# если .well-known прописан в nginx как
# location ^~ /.well-known {
#     default_type "text/plain";
#     root /var/www/site.com;
#     try_files $uri =404;
# }
#
# то в web-root надо указывать папку домена  
#
# certbot certonly --webroot \
# -w /var/www/site.com \
# -d site.com \
# -d www.site.com
# 
# challenge будет сгенерирован в
# /var/www/site.com/.well-known/acme-challenge/Gx1iMjsN5E7qiZAMR6E8TBmyITx
```

Чтобы certbot перезапускал nginx, надо добавить хуки
```
mcedit /etc/letsencrypt/renewal-hooks/deploy/01-reload-nginx


#!/bin/sh
set -e
nginx -t
nginx -s reload


chmod +x /etc/letsencrypt/renewal-hooks/deploy/01-reload-nginx
```
** "set -e" = [Exit immediately if a command exits with a non-zero status](http://linuxcommand.org/lc3_man_pages/seth.html)


и конфигурирование nginx-а
```
    server {
        server_name my-site.com www.my-site.com;
        root        /var/www/my-site.com/public_html/web;

        # Load configuration files for the default server block.
        include /etc/nginx/default.d/*.conf;

        listen [::]:443 ssl ipv6only=on;
        listen 443 ssl;
        ssl_certificate /etc/letsencrypt/live/my-site.com/fullchain.pem;
        ssl_certificate_key /etc/letsencrypt/live/my-site.com/privkey.pem;
        include /etc/letsencrypt/options-ssl-nginx.conf; # managed by Certbot
        ssl_dhparam /etc/letsencrypt/ssl-dhparams.pem; # managed by Certbot


        location / {
            index index.php;
            try_files $uri $uri/ index.php;
        }
    }
```
при необходимости добавляем редиректы с http на https
```
    server {
        if ($host = www.my-site.com) {
            return 301 https://$host$request_uri;
        }

        if ($host = my-site.com) {
            return 301 https://$host$request_uri;
        }

        listen       80 ;
        listen       [::]:80 ;
        server_name my-site.com www.my-site.com;
        return 404;
    }
```
	
## Установка Redis
```
dnf install redis
```

Редактируем конфигурацию
```
nano /etc/redis.conf
```
меняем тип запуска
```
# If you run Redis from upstart or systemd, Redis can interact with your
# supervision tree. Options:
#   supervised no      - no supervision interaction
#   supervised upstart - signal upstart by putting Redis into SIGSTOP mode
#   supervised systemd - signal systemd by writing READY=1 to $NOTIFY_SOCKET
#   supervised auto    - detect upstart or systemd method based on
#                        UPSTART_JOB or NOTIFY_SOCKET environment variables
# Note: these supervision methods only signal "process is ready."
#       They do not enable continuous liveness pings back to your supervisor.
supervised systemd
```
и интерфейсы на которых будет работать
```
bind 127.0.0.1 10.0.0.4
```
при необходимости меняем дефолтный порт 6379 
```
port 16379
```
указываем режим очистки и сколько памяти можно использовать (в байтах)
```
maxmemory 1500000000
maxmemory-policy allkeys-lru
```
добавляем пароль
```
requirepass SOME-STRONG-PASSWORD
```
запускаем и добавляем в автозагрузку
````
systemctl start redis
systemctl enable redis
````
проверяем
```
redis-cli ping
PONG
```
если необходимо, то защищаем подключение с помощью файрвола
```
firewall-cmd --permanent --new-zone=redis
firewall-cmd --permanent --zone=redis --add-port=6379/tcp
firewall-cmd --permanent --zone=redis --add-source=client_server_private_IP
firewall-cmd --reload
```
если необходимо подключить сессии php в редис, то в конфиге php прописываем ip адрес, порт и номер базы редиса
```
nano /etc/php-fpm.d/www.conf
...
;php_value[session.save_handler] = files
;php_value[session.save_path]    = /var/www/$pool/session
php_value[session.save_handler]  = redis
php_value[session.save_path]     = "tcp://192.168.0.10:6379?database=10"
```

## Установка Zabbix Agent
- https://www.zabbix.com/ru/download?zabbix=6.0&os_distribution=centos&os_version=8&db=mysql&ws=nginx

```
rpm -Uvh https://repo.zabbix.com/zabbix/5.5/rhel/8/x86_64/zabbix-release-5.5-1.el8.noarch.rpm
dnf clean all
dnf install zabbix-agent
systemctl enable zabbix-agent
```
Редактируем конфигурацию
```
nano /etc/zabbix/zabbix_agentd.conf

# Passive mode  - не меняем если выбрали Active Mode
Server=zabbix.site.com                            - адрес Zabbix сервера откуда будут приходить запросы
ListenPort=10050                                  - порт на который будут приходить запросы
StartAgents=3                                     - кол-во дочерних процессов агента, которые будут слушать порт
	                                                если указано 0, то Passive Mode отключен
	
# Active mode - не меняем если выбрали Passive Mode
ServerActive=zabbix.site.com:10051                - адрес и порт Zabbix сервера (если пустой то, Active Mode отключен)
Hostname = node-01.site.com                       - имя ноды на которой находится агент, должно соответствовать существующей конфигурации
```

Zabbix Modes Schema
```
	            Zabbix Server requests                     Agent connects to Zabbix
	            data via TCP 10050                         server via TCP 10051
[===============] <----------------------- [===============] <----------------------- [==============]
[ Passive agent ]                          [ Zabbix Server ]                          [ Active agent ]
[===============] -----------------------> [===============] <----------------------- [==============]
	            Agent responds with                        Agent pushes data via
	            the value                                  TCP 10051
```
										       
После того как конфигурация настроена, добавляем соответствующий хост в Zabbix сервер и запускаем агента

```
systemctl start zabbix-agent
```
проверяем что в логах все в порядке
```
tail /var/log/zabbix/zabbix_agentd.log
```

## Отключение IPv6

Проверка IPv6
```
$ ping site.com
PING site.com(2a01:3f8:b05:1000::2 (2a01:3f8:b05:1000::2)) 56 data bytes
^C

$ nslookup site.com
Server:		213.233.100.100
Address:	213.233.100.100#53

Non-authoritative answer:
Name:	site.com
Address: 71.47.2.81
Name:	site.com
Address: 2a01:3f8:b05:1000::2

$ ping6 2a01:3f8:b05:1000::2
PING 2a01:3f8:b05:1000::2(2a01:3f8:b05:1000::2) 56 data bytes
^C

$ traceroute6  2a01:3f8:b05:1000::2
traceroute to 2a01:3f8:b05:1000::2 (2a01:3f8:b05:1000::2), 30 hops max, 80 byte packets
 1  * * *
 2  * * *
 3  * * *
 4  * * *
 5  * * *

$ ip a
1: lo: <LOOPBACK,UP,LOWER_UP> mtu 65536 qdisc noqueue state UNKNOWN group default qlen 1000
    link/loopback 00:00:00:00:00:00 brd 00:00:00:00:00:00
    inet 127.0.0.1/8 scope host lo
       valid_lft forever preferred_lft forever
    inet6 ::1/128 scope host 
       valid_lft forever preferred_lft forever
2: enp4s0: <BROADCAST,MULTICAST,UP,LOWER_UP> mtu 1500 qdisc fq_codel state UP group default qlen 1000
    link/ether c8:60:00:7d:cc:6f brd ff:ff:ff:ff:ff:ff
    inet 251.9.60.18/32 scope global noprefixroute enp4s0
       valid_lft forever preferred_lft forever
    inet6 2a01:3f8:061:6352::2/64 scope global noprefixroute 
       valid_lft forever preferred_lft forever
    inet6 fe80::cb60:ff:fa8d:bb6f/64 scope link noprefixroute 
       valid_lft forever preferred_lft forever
```

Отключаем IPv6, так после ребута он работает, но спустя какое-то время отваливается.
```
nano /etc/sysctl.conf
```
добавляем
```
net.ipv6.conf.all.disable_ipv6 = 1
net.ipv6.conf.default.disable_ipv6 = 1
net.ipv6.conf.lo.disable_ipv6 = 1
```
сохраняем и применяем
```
sysctl -p
```
проверяем, и видим что IPv6 больше не используются
```
$ ip a
1: lo: <LOOPBACK,UP,LOWER_UP> mtu 65536 qdisc noqueue state UNKNOWN group default qlen 1000
    link/loopback 00:00:00:00:00:00 brd 00:00:00:00:00:00
    inet 127.0.0.1/8 scope host lo
       valid_lft forever preferred_lft forever
    inet6 ::1/128 scope host 
       valid_lft forever preferred_lft forever
2: enp4s0: <BROADCAST,MULTICAST,UP,LOWER_UP> mtu 1500 qdisc fq_codel state UP group default qlen 1000
    link/ether c8:60:00:7d:cc:6f brd ff:ff:ff:ff:ff:ff
    inet 251.9.60.18/32 scope global noprefixroute enp4s0
       valid_lft forever preferred_lft forever

$ ping site.com
PING site.com (71.47.2.81) 56(84) bytes of data.
64 bytes from site.com (71.47.2.81): icmp_seq=1 ttl=61 time=0.258 ms
64 bytes from site.com (71.47.2.81): icmp_seq=2 ttl=61 time=0.302 ms
```

## Установка Docker
- https://docs.docker.com/engine/install/centos/
- https://github.com/docker/compose/releases
```
 yum install -y yum-utils

 yum-config-manager \
    --add-repo \
    https://download.docker.com/linux/centos/docker-ce.repo
    
yum install docker-ce docker-ce-cli containerd.io docker-compose-plugin

systemctl enable docker
systemctl start docker

curl -L "https://github.com/docker/compose/releases/download/v2.11.1/docker-compose-linux-x86_64" -o /usr/local/bin/docker-compose
chmod +x /usr/local/bin/docker-compose

docker version
docker-compose version
```
## Установка Portainer
- https://docs.portainer.io/v/ce-2.9/start/install/server/docker/linux
```
docker volume create portainer_data

docker run -d -p 8000:8000 -p 9443:9443 --name portainer \
    --restart=always \
    -v /var/run/docker.sock:/var/run/docker.sock \
    -v portainer_data:/data \
    portainer/portainer-ce:2.9.3

```

## Load Balancer - Draft

Basic balancing of HTTP with nginx
```
/etc/nginx/nginx.conf

http {
    upstream site_com {
        server 10.0.0.2:8080;
        server 10.0.0.3:8080;
    }

    server {
        listen 80;

        location / {
            proxy_pass http://site_com;
            proxy_set_header Host $host;
            proxy_set_header X-Real-IP $remote_addr;
            proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
            proxy_set_header X-Forwarded-Proto $scheme;
        }
    }
}
```
