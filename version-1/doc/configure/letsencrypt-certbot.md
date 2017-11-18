# Vibus, версия 1
## Установка и настройка сертификата от LetsEncrypt с помощью CertBot

Устанавливаем certbot
```bash
yum install certbot
```

В httpd.conf добавляем 443 порт
```text
...
Listen 80
Listen 443
...
```

Для нужного домена добавляем 443 порт в VirtualHost
```text
<VirtualHost *:80 *:443>
...
```

Перезапускаем httpd
```bash
systemctl restart httpd
```
## Полезные ссылки
- [CertBot official site](https://certbot.eff.org/)
