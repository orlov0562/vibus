# Vibus, версия 1

## Установка и настройка vsFTPD

Устанавливаем vsftpd

```bash
yum install vsftpd
```
Делаем бэкап конфига
```bash
mv /etc/vsftpd/vsftpd.conf /etc/vsftpd/vsftpd.conf.orig
```
Создаем папку
```bash
mkdir -p /opt/vibus/vsftpd/{conf,log}
```
Создаем файл конфига
```bash
mcedit /opt/vibus/vsftpd/conf/vsftpd.conf
```
с таким содержимым
```plain
anonymous_enable=NO
guest_enable=NO
local_enable=YES

write_enable=YES
local_umask=022
dirmessage_enable=YES

force_dot_files=YES
delete_failed_uploads=YES
connect_from_port_20=NO
delay_failed_login=3

xferlog_enable=YES
xferlog_file=/opt/vibus/vsftpd/log/xferlog.log
xferlog_std_format=YES

idle_session_timeout=600
data_connection_timeout=120

chroot_local_user=YES
chroot_list_enable=NO

local_root=/opt/vibus/site/$USER
user_sub_token=$USER

listen=YES
listen_ipv6=NO

userlist_enable=YES
userlist_file=/opt/vibus/vsftpd/conf/denied_users.list

pam_service_name=vsftpd

tcp_wrappers=NO

```

Создаем файл пользователей, логин которых будет запрещен еще до проверки паролей
```bash
mcedit /opt/vibus/vsftpd/conf/denied_users.list
```
с таким содержимым (у меня это все пользователи которые были сразу в **/etc/passwd** после чистой установки)
```plain
root
bin
daemon
adm
lp
sync
shutdown
halt
mail
operator
games
ftp
nobody
systemd-bus-proxy
systemd-network
dbus
saslauth
mailnull
smmsp
rpc
apache
sshd
nscd
named
tcpdump
mysql
```

Делаем символическую сылку
```bash
ln -s /opt/vibus/vsftpd/conf/vsftpd.conf /etc/vsftpd/
```

Запускаем vsftpd
```bash
systemctl start vsftpd
```

Добавляем в автозагрузку
```bash
systemctl enable vsftpd
```
