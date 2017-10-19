# Vibus, версия 1
## Установка и настройка Httpd (Apache)

Устанавливаем apache
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

делаем резервную копию **/etc/httpd/conf/httpd.conf**
```bash
cp в /etc/httpd/conf/httpd.conf /etc/httpd/conf/httpd.conf.orig
```
создаем наш конфиг /opt/vibus/httpd/httpd.conf
```bash
mcedit /opt/vibus/httpd/httpd.conf
```
с таким содержимым
```plain
ServerName localhost

NameVirtualHost xx.xx.xx.xx:80
NameVirtualHost xx.xx.xx.xx:443

IncludeOptional /opt/vibus/httpd/conf/*.conf
```
!! не забудьте поменять xx.xx.xx.xx на ваш ip адрес

добавляем в конец **/etc/httpd/conf/httpd.conf** подключение нашего конфига
```bash
mcedit /etc/httpd/conf/httpd.conf
```
и в конец
```plain
...
IncludeOptional /opt/vibus/httpd/httpd.conf
```
перезапускаем 
```bash
systemctl restart httpd
```
