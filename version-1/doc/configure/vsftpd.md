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
local_enable=YES
write_enable=YES
local_umask=022
dirmessage_enable=YES
connect_from_port_20=YES

xferlog_enable=YES
xferlog_file=/opt/vibus/vsftpd/log/xferlog.log
xferlog_std_format=YES

idle_session_timeout=600
data_connection_timeout=120

chroot_local_user=YES
chroot_list_enable=YES
chroot_list_file=/opt/vibus/vsftpd/conf/no_chroot_users.conf

listen=YES
listen_ipv6=NO

pam_service_name=vsftpd
userlist_enable=NO
tcp_wrappers=YES
```

Создаем файл пользователей, которые не будут ограничены своей папкой
```bash
mcedit /opt/vibus/vsftpd/conf/no_chroot_users.conf
```
с таким содержимым
```plain
root
```

Делаем символическую сылку
```bash
ln -s /opt/vibus/vsftpd/conf/vsftpd.conf /etc/vsftpd/
```
