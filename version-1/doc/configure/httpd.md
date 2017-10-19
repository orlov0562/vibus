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
