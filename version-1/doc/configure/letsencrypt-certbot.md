# Vibus, версия 1
## Установка и настройка сертификата от LetsEncrypt с помощью CertBot

### Настройка httpd
Устанавливаем **mod_ssl**
```bash
yum install mod_ssl
```
Создаем файл настроек ssl для httpd (либо можно прямо написать это в httpd.conf)
```bash
mcedit /opt/vibus/services/httpd/conf/ssl.conf
```
с таким содержимым (взято из **/etc/httpd/conf.d/ssl.conf**)
```text
Listen 443 https
<IfModule mod_ssl.c>
    SSLPassPhraseDialog exec:/usr/libexec/httpd-ssl-pass-dialog
    SSLSessionCache         shmcb:/run/httpd/sslcache(512000)
    SSLSessionCacheTimeout  300
    SSLRandomSeed startup file:/dev/urandom  256
    SSLRandomSeed connect builtin
    SSLCryptoDevice builtin

    SSLProtocol all -SSLv2
    SSLCipherSuite HIGH:MEDIUM:!aNULL:!MD5:!SEED:!IDEA
</IfModule>

```
подключаем его в **httpd.conf**
```bash
mcedit /opt/vibus/services/httpd/conf/httpd.conf
```
```text
Listen 80 http
...
Include /opt/vibus/services/httpd/conf/ssl.conf
...
Include conf.modules.d/*.conf
```

### Настройка nginx
```bash
mkdir -p /var/www/example.com/.well-known
```
```text
server {
    listen  80;
    listen  [::]:80;
    server_name  www.example.com example.com;
    port_in_redirect off;
    return       301 https://www.example.com$request_uri;
}

server {
    listen 443 ssl;
    listen [::]:443 ssl;
    server_name  example.com;
    ssl                  on;
    ssl_certificate      /etc/letsencrypt/live/example.com/fullchain.pem;
    ssl_certificate_key  /etc/letsencrypt/live/example.com/privkey.pem;
    port_in_redirect off;
    return       301 https://www.example.com$request_uri;
}

server {
    listen 443;
    listen [::]:443 ssl;
    server_name www.example.com;
    port_in_redirect off;

    ssl                  on;
    ssl_certificate      /etc/letsencrypt/live/example.com/fullchain.pem;
    ssl_certificate_key  /etc/letsencrypt/live/example.com/privkey.pem;

    # Improve HTTPS performance with session resumption
    ssl_session_cache shared:SSL:20m;
    ssl_session_timeout 60m;

    # Enable server-side protection against BEAST attacks
    ssl_protocols TLSv1.2;
    ssl_prefer_server_ciphers   on;
    ssl_ciphers "ECDHE-ECDSA-AES256-GCM-SHA384:ECDHE-RSA-AES256-GCM-SHA384:ECDHE-ECDSA-CHACHA20-POLY1305:ECDHE-RSA-CHACHA20-POLY1305:ECDHE-ECDSA-AES256-SHA384:ECDHE-RSA-AES256-SHA384";

    # RFC-7919 recommended: https://wiki.mozilla.org/Security/Server_Side_TLS#ffdhe4096
    ssl_dhparam /etc/ssl/ffdhe4096.pem;
    ssl_ecdh_curve secp521r1:secp384r1;

    # Aditional Security Headers
    # ref: https://developer.mozilla.org/en-US/docs/Security/HTTP_Strict_Transport_Security
    #add_header Strict-Transport-Security "max-age=31536000; includeSubDomains";

    # ref: https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/X-Frame-Options
    add_header X-Frame-Options DENY always;

    # ref: https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/X-Content-Type-Options
    add_header X-Content-Type-Options nosniff always;

    # ref: https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/X-XSS-Protection
    add_header X-Xss-Protection "1; mode=block" always;

    # Enable OCSP stapling 
    # ref. http://blog.mozilla.org/security/2013/07/29/ocsp-stapling-in-firefox
    ssl_stapling on;
    ssl_stapling_verify on;
    ssl_trusted_certificate /etc/letsencrypt/live/example.com/fullchain.pem;
    resolver 1.1.1.1 1.0.0.1 [2606:4700:4700::1111] [2606:4700:4700::1001] valid=300s; # Cloudflare
    resolver_timeout 5s;

    index index.php;
    root /var/www/example.com/public_html;

    charset utf-8;

    # Locations

    location / {
        try_files $uri $uri/ /index.php$is_args$args;
    }

    location ^~ /.well-known {
        default_type "text/plain";
        root /var/www/example.com;
        try_files $uri =404;
    }

    location ~* /\. {
        deny all;
    }

    location ~ \.php$ {
    fastcgi_split_path_info ^(.+\.php)(/.+)$;
	fastcgi_index index.php;
	include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_param QUERY_STRING    $query_string;
        fastcgi_pass unix:/run/php/example.com-php7.3-fpm.sock;
    }

    access_log /var/www/example.com/logs/nginx-access.log;
    error_log  /var/www/example.com/logs/nginx-error.log notice;
}

```
создание сертификата
```bash
certbot certonly --webroot \
-w /var/www/example.com \
-d example.com \
-d www.example.com
```

### Установка CertBot

Устанавливаем **certbot**
```bash
yum install certbot
```
для того, чтобы обновлять ключи в автоматическом режиме, добавляем в крон
```bash
crontab -e
```
задачу по обновлению на запуск каждый день в 00:00
```text
0 0 * * * /usr/bin/certbot renew >/dev/null 2>&1
```
### Перезагрузка веб-сервера
При обновлении сертификата, веб-сервер не перезагружается автоматически и продолжает использовать старый сертификат.
Чтобы перезагружать вебсервер, нужно добавить bash скрипт в папку renewal-hooks.
Например, вот так
```bash
mcedit /etc/letsencrypt/renewal-hooks/deploy/01-reload-nginx
chmod +x /etc/letsencrypt/renewal-hooks/deploy/01-reload-nginx
```
с таким содержимым
```bash
#! /bin/sh
set -e
/etc/init.d/nginx configtest
/etc/init.d/nginx reload
```

### Получение сертификата для домена

выполняем получение сертификатов командой
```bash
certbot certonly --webroot \
-w /opt/vibus/sites/user/site.com/public_html \
-d www.site.com \
-d site.com
```
можно указать сразу несколько доменов и путей к ним, пример
```bash
certbot certonly --webroot \
-w /var/www/example/ \
-d www.example.com \
-d example.com \
-w /var/www/other \
-d other.example.net \
-d another.other.example.net
```

сертификаты будут сгенерированы тут **/etc/letsencrypt/**

### Настройка домена
Для удобства создаем символическую ссылку на папку с сертификатами в **/opt/vibus/services/httpd/cert**

```bash
ln -s /etc/letsencrypt/live/site.com /opt/vibus/cert/site.com
```

Для нужного домена создаем еще один конфиг домена
```bash
cp /opt/vibus/services/httpd/vhosts/user-site.com.conf /opt/vibus/services/httpd/vhosts/user-site.com-ssl.conf
```
в нем меняем порт и добавляем инструкции по подключению сертификатов
```text
<IfModule mod_ssl.c>
    <VirtualHost *:443>
    ...
        SSLEngine on

        SSLCertificateFile      /opt/vibus/services/httpd/cert/site.com/cert.pem
        SSLCertificateKeyFile   /opt/vibus/services/httpd/cert/site.com/privkey.pem
        SSLCertificateChainFile /opt/vibus/services/httpd/cert/site.com/fullchain.pem

        <FilesMatch "\.php$">
            SSLOptions +StdEnvVars
        </FilesMatch>

        BrowserMatch "MSIE [2-6]" nokeepalive ssl-unclean-shutdown downgrade-1.0 force-response-1.0
        BrowserMatch "MSIE [17-9]" ssl-unclean-shutdown
    ...
    </VirtualHost>
</IfModule>
```

При необходимости добавляем редирект в конфигурацию или в .htaccess сайта
```bash
RewriteEngine On
...
#First rewrite any request to the wrong domain to use the correct one (here www.)
RewriteCond %{HTTP_HOST} !^www\.
RewriteRule ^(.*)$ https://www.%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

#Now, rewrite to HTTPS:
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

Перезапускаем httpd
```bash
systemctl restart httpd
```

## Удаление сертификата и его зависимостей

```bash
certbot delete --cert-name www.site.com
```
Название сертификатов можно посмотреть так
```bash
ll /etc/letsencrypt/live
```

## Полезные ссылки
- [CertBot official site](https://certbot.eff.org/)

