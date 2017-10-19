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
```plain
yum search "php72-php-"
```
и ставим нужные
```plain
yum install php-mcrypt php-mbstring php-intl php-gd php-curl php-mysql php-pdo php-zip php-fileinfo php-xml
```

## Настрйока PHP=FPM
