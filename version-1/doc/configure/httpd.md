# Vibus, версия 1
## Установка и настройка Httpd (Apache)

Устанавливаем httpd
```bash
yum install httpd
```
добавляем в автозагрузку
```bash
systemctl enable httpd
```
запускаем
```bash
systemctl start httpd
```
проверяем что сервис запущен
```bash
systemctl status httpd
```
заходим по ip адресу **http://xx.xx.xx.xx**, и убеждаемся что веб-сервер заработал

останавливаем веб-серве
```bash
systemctl stop httpd
```
делаем бэкап основного конфига
```bash
mv /etc/httpd/conf/httpd.conf /etc/httpd/conf/httpd.conf.orig
```
создаем наш конфиг /opt/vibus/httpd/httpd.conf
```bash
mcedit /opt/vibus/httpd/httpd.conf
```
с таким содержимым
```plain
ServerRoot "/etc/httpd"
ServerName localhost
ServerAdmin root@localhost

Listen 80
Listen 443

# if should work only on specified ip address
# Listen xx.xx.xx.xx:80
# Listen xx.xx.xx.xx:443
# NameVirtualHost xx.xx.xx.xx:80
# NameVirtualHost xx.xx.xx.xx:443

Include conf.modules.d/*.conf

User apache
Group apache

<Directory />
    AllowOverride none
    Require all denied
</Directory>

<IfModule dir_module>
    DirectoryIndex index.html
</IfModule>

<Files ".ht*">
    Require all denied
</Files>

ErrorLog "/dev/null"

LogLevel warn

<IfModule log_config_module>
    LogFormat "%h %l %u %t \"%r\" %>s %b \"%{Referer}i\" \"%{User-Agent}i\"" combined
    LogFormat "%h %l %u %t \"%r\" %>s %b" common

    <IfModule logio_module>
      LogFormat "%h %l %u %t \"%r\" %>s %b \"%{Referer}i\" \"%{User-Agent}i\" %I %O" combinedio
    </IfModule>
    
    CustomLog "/dev/null" combined
</IfModule>

<IfModule mime_module>
    TypesConfig /etc/mime.types
    
    AddType application/x-compress .Z
    AddType application/x-gzip .gz .tgz

    AddType text/html .shtml
    AddOutputFilter INCLUDES .shtml
</IfModule>

AddDefaultCharset UTF-8

<IfModule mime_magic_module>
    MIMEMagicFile /etc/httpd/conf/magic
</IfModule>

EnableSendfile on

IncludeOptional /opt/vibus/httpd/conf/*.conf
```
создаем симлинк на наш конфиг в папку **/etc/httpd/conf**
```bash
ln -s /opt/vibus/httpd/httpd.conf /etc/httpd/conf/
```
запускаем httpd
```bash
systemctl start httpd
```
если теперь попытаться зайти по ip адресу **http://xx.xx.xx.xx** вы должны получить ошибку 
```text
Forbidden
You don't have permission to access / on this server.
```
это правильно т.к. еще нет настроенных виртуальных хостов

## Настройка виртуального хоста по-умолчанию

создаем файл конфигурации виртаульного хоста по-умолчанию
```bash
mcedit /opt/vibus/httpd/conf/root-localhost.conf
```
с таким содержимым
```plain
<VirtualHost *:80>

    ServerName localhost
    ServerAlias www.localhost

    DocumentRoot /opt/vibus/site/root/localhost/public_html

    DirectoryIndex index.php index.html

    <Directory /opt/vibus/site/root/localhost/public_html>
        Options -Indexes +FollowSymLinks +MultiViews
        AllowOverride All
        Require all granted
    </Directory>

    <FilesMatch \.php$>
	    SetHandler "proxy:unix:/opt/vibus/php-fpm/sock/root-localhost.sock|fcgi://localhost"
    </FilesMatch>

    ErrorLog  /opt/vibus/site/root/localhost/log/httpd/error.log
    LogLevel warn

    CustomLog /opt/vibus/site/root/localhost/log/httpd/access.log combined

</VirtualHost>

```
