# Vibus, версия 1
## Установка и настройка MariaDB (MySQL)

Устанавливаем **mariadb-server**
```bash
yum install mariadb-server
```

запускаем и добавляем в автозапуск
```bash
systemctl enable mariadb
systemctl start mariadb
```

делаем первичную конфигурацию
```bash
mysql_secure_installation
```

опционально, тюнингуем конфигурацию с помощью Tuning скрипта. Описание [тут](http://www.day32.com/MySQL/)
```bash
yum install bc
wget http://www.day32.com/MySQL/tuning-primer.sh
chmod u+x tuning-primer.sh
bash tuning-primer.sh
```
