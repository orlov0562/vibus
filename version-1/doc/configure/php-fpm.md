# Vibus, версия 1
## Установка и настройка PHP и PHP-FPM
Тут рассматривается установка наиболее свежей версии PHP 7.2, если Вам нужна другая, тогда укажите нужную, с помощью **yum-config-manager**, либо используйте ту, которая идет по-умолчанию (на сегодня это 5.4).

## PHP 7.2
По умолчанию, в репозитории лежит версия php 5.4.16, проверить можно так
```bash
yum info php
```
Я же хочу последнюю версию ветки PHP 7. Для этого, прежде всего нужно подключить репозиторий remi. Мы это уже сделали в разделе установке CentOS), но если Вы этого еще не делали, тогда самое время сделать
```bash
yum install http://rpms.remirepo.net/enterprise/remi-release-7.rpm
yum upgrade
```
теперь можно посмотреть доступные версии
```bash
yum search php | grep "Package that installs PHP"
```
получим примерно такой вывод
```plain
php54.x86_64 : Package that installs PHP 5.4
php55.x86_64 : Package that installs PHP 5.5
php56.x86_64 : Package that installs PHP 5.6
php70.x86_64 : Package that installs PHP 7.0
php71.x86_64 : Package that installs PHP 7.1
php72.x86_64 : Package that installs PHP 7.2
```
нас интересует **php72**, выбираем его как пакет по-умолчанию с помощью **yum-config-manager**
```bash
yum install yum-utils
yum-config-manager --enable remi-php72
```
проверяем
```bash
yum info php
```
и видим что теперь версия по-умолчанию 7.2.0
```plain
Available Packages
Name        : php
Arch        : x86_64
Version     : 7.2.0~RC4 <<<<<<<<<===================
Release     : 1.el7.remi
Size        : 3.2 M
Repo        : remi-php72 
...
```
## Установка PHP и модулей
устанавливаем php и php-fpm
```bash
yum install php php-fpm
```
проверяем версию
```bash
php -v
```
видим, что установлена версия 7.2
```plain
PHP 7.2.0RC4 (cli) (built: Oct 10 2017 14:59:39) ( NTS )
```
теперь смотрим доступные модули 
```bash
yum search "php72-php-"
```
и ставим нужные
```bash
yum install php-mcrypt php-mbstring php-intl php-gd php-curl php-mysql php-pdo php-zip php-fileinfo php-xml
```
так же добавляем другие необходимые зависимости
```bash
yum install php-pecl-imagick
```

## Настройка PHP-FPM

Описание настроек можно посмотреть тут: [http://php.net/manual/ru/install.fpm.configuration.php](http://php.net/manual/ru/install.fpm.configuration.php)

Делаем бэкап основного конфига
```bash
mv /etc/php-fpm.conf /etc/php-fpm.conf.orig
```

Создаем наш конфиг **/opt/vibus/php-fpm/php-fpm.conf**
```bash
mcedit /opt/vibus/php-fpm/php-fpm.conf
```
с таким содержимым
```plain
include=/opt/vibus/php-fpm/conf/*.conf

[global]
error_log = /opt/vibus/php-fpm/log/error.log
daemonize = yes
```
создаем симлинк на наш конфиг в папку /etc/httpd/conf
```bash
ln -s /opt/vibus/php-fpm/php-fpm.conf /etc/
```

## Настройка PHP-FPM пула по-умолчанию

Описание настроек можно посмотреть тут: [http://php.net/manual/ru/install.fpm.configuration.php](http://php.net/manual/ru/install.fpm.configuration.php)

создаем файл конфигурации пула по-умолчанию
```bash
mcedit /opt/vibus/php-fpm/conf/root-localhost.conf
```
с таким содержимым
```plain
[root-localhost]

;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;
; Pool configuration
;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;

user = apache
group = apache

listen = /opt/vibus/php-fpm/sock/root-localhost.sock

listen.owner = apache
listen.group = apache
listen.mode = 0600

; The value can vary from -19 (highest priority) to 20 (lower priority)
; process.priority = -19

pm = dynamic
pm.max_children = 50
pm.start_servers = 5
pm.min_spare_servers = 5
pm.max_spare_servers = 35

access.log = /opt/vibus/site/root/localhost/log/php-fpm/access.log
access.format = "%R - %u %t \"%m %r%Q%q\" %s %f %{mili}d %{kilo}M %C%%"

slowlog = /opt/vibus/site/root/localhost/log/php-fpm/slow.log
request_slowlog_timeout = 30s
request_terminate_timeout = 180s

catch_workers_output = yes

security.limit_extensions = .php

;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;
; PHP & ENV configuration
;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;

env[HOME] = /opt/vibus/site/root/localhost
env[PATH] = /usr/local/bin:/usr/bin:/bin
env[TMP] = /opt/vibus/site/root/localhost/tmp
env[TMPDIR] = /opt/vibus/site/root/localhost/tmp
env[TEMP] = /opt/vibus/site/root/localhost/tmp

php_admin_value[open_basedir] = /opt/vibus/site/root/localhost/public_html/:/opt/vibus/site/root/localhost/tmp/

php_admin_value[sendmail_path] = /usr/sbin/sendmail -t -i -f no-reply@localhost

php_admin_value[error_reporting] = E_ALL
php_flag[display_errors] = on
php_admin_flag[log_errors] = on

php_admin_value[memory_limit] = 128M

php_value[post_max_size] = 128M

php_value[upload_max_filesize] = 128M
php_admin_value[max_file_uploads] = 20
php_admin_value[upload_tmp_dir] = /opt/vibus/site/root/localhost/tmp

php_value[session.save_handler] = files
php_value[session.save_path]    = /opt/vibus/site/root/localhost/session
```
Проверяем конфигурацию на наличие ошибок
```bash
php-fpm -t
```
добавляем в автозагрузку и запускаем
```bash
systemctl enable php-fpm
systemctl start php-fpm
```
создаем тестовый файл /opt/vibus/site/root/localhost/public_html/index.php
```bash
mcedit /opt/vibus/site/root/localhost/public_html/index.php
```
с таким содержимым
```php
<?php
phpinfo();
```
если теперь зайти по ip адресу http://xx.xx.xx.xx вы должны увидеть "PHP Info" страницу
