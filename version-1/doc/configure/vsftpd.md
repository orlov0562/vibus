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
mkdir -p /opt/vibus/vsftpd/{conf, log}
```

Создаем файл конфига
```bash
mcedit /opt/vibus/vsftpd/conf/vsftpd.conf
```
с таким содержимым
```plain

```

Делаем символическую сылку
```bash
ln -s /opt/vibus/vsftpd/conf/vsftpd.conf /etc/vsftpd/
```
