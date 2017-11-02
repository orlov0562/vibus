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
bash tuning-primer.sh
```
## Создание базы данных и пользователя
Входим в интерактивный режим
```bash
mysql -uroot -p
```
Создаем базу данных
```sql
CREATE DATABASE dbname CHARACTER SET utf8 COLLATE utf8_general_ci;
```
** тут **dbname**  = имя новой базы, нужно заменить на ваше

Создаем нового пользователя (если надо)
```sql
CREATE USER 'username'@'localhost' IDENTIFIED BY 'userpassword';
```
** тут **username/userpassword**  = имя пользователя / пароль, нужно заменить на ваше

Устанавливаем привилегии пользователю для работы с ранее созданной базой
```sql
GRANT ALL PRIVILEGES ON 'dbname'.* TO 'username'@'localhost';
```
** тут **username**  = имя пользователя, а **dbname** = имя базы

Применяем новые привелегии
```sql
FLUSH PRIVILEGES;
```
выходим из интерактивного режима
```sql
quit;
```
теперь можно протестировать созданную учетку, попытавшись авторизоваться от имени пользователя
```bash
mysql -uusername -p
```
перейдя в интерактивный режим, выбираем базу и смотрим список таблиц
```sql
USE dbname;
SHOW TABLES;
```
