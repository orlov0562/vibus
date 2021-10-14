# Конфигурация VPS на CentOS 8

## Обновление и добавление репозиториев
```
dnf update -y
dnf install https://dl.fedoraproject.org/pub/epel/epel-release-latest-8.noarch.rpm
dnf install https://rpms.remirepo.net/enterprise/remi-release-8.rpm
```

## Установка и настройка базовых пакетов
```
dnf install mc htop pv curl wget
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
dnf install php php-cli php-common php-fpm php-pdo php-mysqlnd php-xml php-imap php-intl php-json php-bcmath php-mbstring php-pecl-geoip php-pecl-imagick
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
и добавляем пароль
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
