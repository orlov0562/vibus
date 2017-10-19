# Vibus, версия 1
## Создание структуры папок для сервисов
```bash
mkdir -p /opt/vibus/httpd/conf
mkdir -p /opt/vibus/nginx/conf
mkdir -p /opt/vibus/mariadb
mkdir -p /opt/vibus/php-fpm/{conf,sock,log}
```
## Создание структуры папок конфигурации по-умолчанию
mkdir -p /opt/vibus/site/root/localhost/{public_html,session,tmp,secret}
mkdir -p /opt/vibus/site/root/localhost/log/{httpd,nginx,php-fpm}
mkdir -p /opt/vibus/site/root/localhost/backup/{store,script}
```

## Настройка прав на папки конфигурации по-умолчанию
```bash
chown -R root:apache /opt/vibus/site/root/
chmod -R 0770 /opt/vibus/site/root/
```

## Структура организации папок и файлов

Все помещаем в папку /opt/vibus/* для того, чтобы все было в одном месте, а не разбросано по всей системе. Это не соответствует идеологии организации папок в Linux, но позволяет делать быстрые миграции сайтов и их конфигураций между серверами, обновления пакетов и по минимуму вносить изменения в конфиги устанавливаемых пакетов.

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
    - /opt/vibus/php-fpm/log/
        - /opt/vibus/php-fpm/log/error.log

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
        - /opt/vibus/site/user/site.com/log/php-fpm/slow.log
- /opt/vibus/site/user/site.com/tmp/
- /opt/vibus/site/user/site.com/session/
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

### Описание организации папок и файлов
- /opt/vibus/httpd/
    - /opt/vibus/httpd.conf *= конфигурация httpd*
    - /opt/vibus/httpd/conf/
        - /opt/vibus/httpd/conf/site.com.conf *= конфигурация виртуального хоста*

- /opt/vibus/nginx/
    - /opt/vibus/nginx.conf *= конфигурация nginx*
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
    - /opt/vibus/site/user/site.com/session/ *= папка сессий*
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
