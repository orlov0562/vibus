Устанавливаем postfix
```bash
yum install postfix
```

Делаем бэкап оригинального конфига
```bash
mv /etc/postfix/main.cnf /etc/postfix/main.cnf.orig
```

Создаем папку для нашего конфига
```bash
mkdir -p /opt/vibus/postfix
```

Смотрим FQDN имя сервера
```bash
perl -e 'use Sys::Hostname; print hostname, "\n"'
# или
php -r 'echo gethostname (),"\n";'
```
Получаем что-то типа **server.mydomain.ru**

Создаем файл конфига
```bash
mcedit /opt/vibus/postfix/main.cnf
```
с таким содержимым
```plain
# Как сервер представляется при соединении с другими почтовыми системами. 
# Здесь должно быть полное имя (FQDN) нашего сервера, такое же, как в "A" и\или "MX" - записях в нашем DNS и в PTR
smtpd_banner = server.mydomain.ru
myhostname = server.mydomain.ru
mydestination = server.mydomain.ru
myorigin = server.mydomain.ru

# Откуда брать списки известных адресов
alias_maps = hash:/etc/aliases
alias_database = hash:/etc/aliases

# на каких сетевых адресах ждать подключений (только localhost, внешние соединения не принимаем)
inet_interfaces = 127.0.0.1

# Отправителей с каким адресом считать доверенными. Т. к. мы шлем только с этого сервера, снова укажем localhost
# Это очень важный для безопасности параметр, старайтесь не добавлять сюда никаких лишних адресов
mynetworks = 127.0.0.1

# И самое главное: ограничение неавторизованных отправителей, без него наш сервер будет открытым релеем, и его тут же используют спамеры
smtpd_recipient_restrictions = permit_mynetworks, reject_unauth_destination
```

Создаем символическую ссылку на наш файл в папку postfix-а
```bash
ln -s /opt/vibus/postfix/main.cnf /etc/postfix/main.cnf
```
Смотрим текущий MTA
```bash
alternatives --display mta
```
Если он отличается от sendmail.postfix, то устанавливаем его
```bash
alternatives --set mta /usr/sbin/sendmail.postfix
```
Опять проверяем, чтобы был sendmail.postfix 
```bash
alternatives --display mta
```

Добавялем postfix в автозагрузку
```bash
systemctl start postfix
```

Стартуем postfix 
```bash
systemctl start postfix
```

