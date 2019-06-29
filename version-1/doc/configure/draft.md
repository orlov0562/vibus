
### Создание пользователя и группы
```bash
useradd {username}     # создаем пользователя
passwd {username}      # устанавливаем пароль пользователя
groupadd {groupname}   # добавляем группу
usermod -g {groupname} {username}    # добавляем пользователя в существующую группу 
usermod -a -G {groupname} {username} # добавляем пользователя в группу 

# Просмотр пользователей в группе
grep {groupname} /etc/group

# Просмотр шела пользователя
grep {username} /etc/passwd

# Отключить шел пользователю
usermod -s /sbin/nologin {username}
```

### Права 0755 для папок, 0644 для файлов
```bash
find /opt/lampp/htdocs -type d -exec chmod 755 {} \;
find /opt/lampp/htdocs -type f -exec chmod 644 {} \;
```
