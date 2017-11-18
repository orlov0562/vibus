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
Для удобства создаем символические ссылки в /opt/vibus

```bash
ln -s /etc/letsencrypt/live/site.com /opt/vibus/cert/site.com
```

Для нужного домена добавляем 443 порт в VirtualHost и инструкции по подключению сертификатов
```text
<VirtualHost *:80 *:443>
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
```

Перезапускаем httpd
```bash
systemctl restart httpd
```
## Полезные ссылки
- [CertBot official site](https://certbot.eff.org/)
