# Vibus, версия 1
## Установка и настройка MariaDB (MySQL)

Устанавливаем **mariadb-server**
```bash
yum install mariadb-server
# или
apt-get install mariadb-server
```
создаем папку в vibus
```bash
mkdir -p /opt/vibus/services/mariadb/conf
```
копируем оригинальные файлы настроек
```bash
cp /etc/my.cnf.d/* /opt/vibus/services/mariadb/conf/
```
делаем бэкапы оригинальных файлов
```bash
mv /etc/my.cnf.d/client.cnf /etc/my.cnf.d/client.cnf.orig
mv /etc/my.cnf.d/mysql-clients.cnf /etc/my.cnf.d/mysql-clients.cnf.orig
mv /etc/my.cnf.d/server.cnf /etc/my.cnf.d/server.cnf.orig
```
создаем символические ссылки
```bash
ln -s /opt/vibus/services/mariadb/conf/client.cnf /etc/my.cnf.d/client.cnf
ln -s /opt/vibus/services/mariadb/conf/mysql-clients.cnf /etc/my.cnf.d/mysql-clients.cnf
ln -s /opt/vibus/services/mariadb/conf/server.cnf /etc/my.cnf.d/server.cnf
```
настраиваем параметры работы, например для слабых VPS (до 1 Гб ram)
```bash
mcedit ln -s /opt/vibus/services/mariadb/conf/server.cnf
```
и добавляем
```text
[server]
[mysqld]
port                            = 3306
socket                          = /var/lib/mysql/mysql.sock
skip-external-locking
key_buffer_size                 = 64M
max_allowed_packet              = 1M
table_open_cache                = 64
sort_buffer_size                = 8M
net_buffer_length               = 8K
read_buffer_size                = 256K
read_rnd_buffer_size            = 512K
myisam_sort_buffer_size         = 128M
default-storage-engine          = myisam
innodb_file_per_table           = 1

[embedded]
[mysqld-5.5]
[mariadb]
[mariadb-5.5]

```
заранее подготовленные конфигурации можно увидеть в файлах: 
```bash
ls -l /usr/share/mysql | grep "\.cnf"
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

при необходимости добавляем права для логина пользователя root
```bash
GRANT ALL PRIVILEGES ON *.* TO 'root'@'localhost' IDENTIFIED BY '<ВАШ-ПАРОЛЬ>';
GRANT ALL PRIVILEGES ON *.* TO 'root'@'127.0.0.1' IDENTIFIED BY '<ВАШ-ПАРОЛЬ>';
FLUSH PRIVILEGES;
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
GRANT ALL PRIVILEGES ON dbname.* TO 'username'@'localhost';
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
## Документация
- [Описание опций конфигурации MySQL](https://dev.mysql.com/doc/refman/5.7/en/server-system-variables.html)
