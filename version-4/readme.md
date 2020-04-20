# Конфигурация VPS на CentOS 8

## Обновление и добавление репозиториев
```
dnf update -y
dnf install https://dl.fedoraproject.org/pub/epel/epel-release-latest-8.noarch.rpm
dnf install https://rpms.remirepo.net/enterprise/remi-release-8.rpm
```

## Установка и насройка базовых пакетов
```
dnf install mc htop pv
```

Настройка редактора по-умолчанию
```
mcedit ~/.bash_profile

# добавляем в конец
export VISUAL="mcedit"
export EDITOR="mcedit"
```

## отключение SELinux
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
```

## Установка PHP
```
dnf module enable php:remi-7.4
dnf install php php-cli php-common php-fpm php-pdo php-mysqlnd php-xml php-imap php-intl php-json php-bcmath php-mbstring
systemctl disable httpd
```
