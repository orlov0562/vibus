# Vibus, версия 1

## Набор устанавливаемого ПО
- CentOS Minimal
- NGINX
- HTTPD
- PHP-FPM (PHP 7.2+)
- MariaDB

## Установка и настройка
- [CentOS](doc/configure/centos.md)
- [Структура папок](doc/configure/dir.md)
- [MariaDB (MySQL)](doc/configure/mariadb.md)
- [Nginx](doc/configure/nginx.md)
- [Httpd (Apache)](doc/configure/httpd.md)
- [PHP, PHP-FPM](doc/configure/php-fpm.md)


## Структура организации папок

Все помещаем в папку /opt/vibus/* для того, чтобы все было в одном месте, а не разбросано по всей системе. Это не соответствует идеологии организации папок в Linux, но позволяет делать быстрые миграции сайтоы между серверами, обновления пакетов и по минимуму вносить изменения в конфиги устанавливаемых пакетов.

- /opt/vibus/httpd/
    - /opt/vibus/httpd.conf
    - /opt/vibus/httpd/conf/
        - /opt/vibus/httpd/conf/site.com.conf

- /opt/vibus/nginx/
    - /opt/vibus/nginx.conf
    - /opt/vibus/nginx/conf/user-site.com.cnf

- /opt/vibus/mariadb/
    - /opt/vibus/mariadb/mariadb.cnf

- /opt/vibus/php-fpm/
    - /opt/vibus/php-fpm.conf
    - /opt/vibus/php-fpm/conf/
        - /opt/vibus/php-fpm/conf/user-site.com.conf
    - /opt/vibus/php-fpm/sock/
        - /opt/vibus/php-fpm/sock/user-site.com.sock

- /opt/vibus/site/user/site.com/public_html/
- /opt/vibus/site/user/site.com/log/
    - /opt/vibus/site/user/site.com/log/httpd/
        - /opt/vibus/site/user/site.com/log/httpd/access.log
        - /opt/vibus/site/user/site.com/log/httpd/error.log
    - /opt/vibus/site/user/site.com/log/nginx/
        - /opt/vibus/site/user/site.com/log/nginx/access.log
        - /opt/vibus/site/user/site.com/log/nginx/error.log
    - /opt/vibus/site/user/site.com/log/php-fpm/
        - /opt/vibus/site/user/site.com/log/php-fpm/access.log
        - /opt/vibus/site/user/site.com/log/php-fpm/error.log
- /opt/vibus/site/user/site.com/tmp/
- /opt/vibus/site/user/site.com/secret/
    - /opt/vibus/site/user/site.com/secret/.passwd
- /opt/vibus/site/user/site.com/backup/
    - /opt/vibus/site/user/site.com/backup/store/
    - /opt/vibus/site/user/site.com/backup/script/

- /opt/vibus/site/user/ -> /home/user/site/

- /opt/vibus/template
    - /opt/vibus/template/httpd/site.conf
    - /opt/vibus/template/nginx/site.conf
    - /opt/vibus/template/site/..dirs..

- /opt/vibus/install/
    - /opt/vibus/cmd/inc/
    - /opt/vibus/cmd/install.sh

- /opt/vibus/cmd
    - /opt/vibus/cmd/inc/
    - /opt/vibus/cmd/account-create.sh
    - /opt/vibus/cmd/account-delete.sh
    - /opt/vibus/cmd/site-create.sh
    - /opt/vibus/cmd/site-delete.sh

### Описание организации папок
- /opt/vibus/httpd/
    - /opt/vibus/httpd.conf *= конфигурация httpd*
    - /opt/vibus/httpd/conf/
        - /opt/vibus/httpd/conf/site.com.conf *= конфигурация виртуального хоста*

- /opt/vibus/nginx/
    - /opt/vibus/nginx.conf *= конфигурация httpd*
    - /opt/vibus/nginx/conf/user-site.com.cnf *= конфигурация виртуального хоста*

- /opt/vibus/mariadb/
    - /opt/vibus/mariadb/mariadb.cnf *= конфигурация mariadb*

- /opt/vibus/php-fpm/
    - /opt/vibus/php-fpm.conf *= конфигурация php-fpm*
    - /opt/vibus/php-fpm/conf/
        - /opt/vibus/php-fpm/conf/user-site.com.conf *= конфигурация пула хоста*
    - /opt/vibus/php-fpm/sock/
        - /opt/vibus/php-fpm/sock/user-site.com.sock *= сокет пула хоста*
        
- /opt/vibus/site/user/site.com/ *= папки сайта*
    - /opt/vibus/site/user/site.com/public_html/ *= файлы сайта*
    - /opt/vibus/site/user/site.com/log/ *= логи*
        - /opt/vibus/site/user/site.com/log/httpd/
            - /opt/vibus/site/user/site.com/log/httpd/access.log
            - /opt/vibus/site/user/site.com/log/httpd/error.log
        - /opt/vibus/site/user/site.com/log/nginx/
            - /opt/vibus/site/user/site.com/log/nginx/access.log
            - /opt/vibus/site/user/site.com/log/nginx/error.log
        - /opt/vibus/site/user/site.com/log/php-fpm/
            - /opt/vibus/site/user/site.com/log/php-fpm/access.log
            - /opt/vibus/site/user/site.com/log/php-fpm/error.log
    - /opt/vibus/site/user/site.com/tmp/ *= временная папка*
    - /opt/vibus/site/user/site.com/secret/ *= папка для доп. конфигураций*
        - /opt/vibus/site/user/site.com/secret/.passwd
    - /opt/vibus/site/user/site.com/backup/ *= папка для бэкапов*
        - /opt/vibus/site/user/site.com/backup/store/
        - /opt/vibus/site/user/site.com/backup/script/

    - /opt/vibus/site/user/ -> /home/user/site/ *= симлинк в домашнюю папку пользователя*

- /opt/vibus/install/ *= скрипты для инсталяции vibus*
    - /opt/vibus/cmd/inc/
    - /opt/vibus/cmd/install.sh

- /opt/vibus/cmd *= скрипты для создания пользователя / сайта*
    - /opt/vibus/cmd/inc/ *= дополнительные скрипты*
        - /opt/vibus/cmd/template *= шаблоны для создания пользователя / сайта*
            - /opt/vibus/cmd/template/httpd/site.conf
            - /opt/vibus/cmd/template/nginx/site.conf
            - /opt/vibus/cmd/template/site/..dirs..
    - /opt/vibus/cmd/account-create.sh
    - /opt/vibus/cmd/account-delete.sh
    - /opt/vibus/cmd/site-create.sh
    - /opt/vibus/cmd/site-delete.sh

## Конфигурация по-умолчанию
Для минимальной установки необходима хотя бы одна конфигурация для сайта. Для этого будут использоваться такие данные:
- Пользователь **root**
- Сайт **localhost**
- Пример: /opt/vibus/site/**root**/**localhost**/..dirs..
