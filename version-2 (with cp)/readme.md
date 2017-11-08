# Тестовая версия админки Vibus

Экспирементальная версия админки веб-сервера

** !!! НЕ ИСПОЛЬЗУЙТЕ КОД И НАСТРОЙКИ ИЗ ЭТОЙ ПАПКИ НА ПУБЛИЧНЫХ СЕРВЕРАХ ТОЛЬКО НА ЛОКАЛКЕ !!! **

###  Создание пользователя

Создать пользователя
```bash
useradd vibus
```

Дать права на выполнения команд от рута
```bash
visudo
```

Добавить текст
```plain
vibus   ALL=(ALL)       NOPASSWD: ALL
```

###  Установка сервиса

```bash
bash /opt/vibus/cp/service/install.sh
```

```bash
systemctl enabled httpd-vibus
```

```bash
systemctl start httpd-vibus
```

###  Пароль на админку
```
htpasswd -c /opt/vibus/cp/site/secret/vibus.htpasswd vibus

```