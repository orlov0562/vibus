# Vibus, версия 1
## Установка и настройка MTA (Mail Transfer Agent)

### Установка и настройка postfix

Устанавливаем postfix
```bash
yum install postfix
```

Делаем бэкап оригинального конфига
```bash
mv /etc/postfix/main.cf /etc/postfix/main.cf.orig
```

Создаем папку для нашего конфига
```bash
mkdir -p /opt/vibus/postfix/conf
```

Смотрим FQDN имя сервера
```bash
uname -n
# или
perl -e 'use Sys::Hostname; print hostname, "\n"'
# или
php -r 'echo gethostname (),"\n";'
```
Получаем что-то типа **server.mydomain.ru**

Создаем файл конфига
```bash
mcedit /opt/vibus/postfix/conf/main.cf
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
ln -s /opt/vibus/postfix/conf/main.cf /etc/postfix/conf/main.cf
```
Для Ubuntu смотрим текущий MTA так
```bash
telnet 127.0.0.1 25
# введет что-то типа
# 220 domain.tld ESMTP Postfix (Ubuntu)
```

Для CentOs cмотрим текущий MTA, **с наибольшим** приоритетом
```bash
alternatives --display mta
```
будет вывод типа
```text
mta - status is manual.
 link currently points to /usr/sbin/sendmail.postfix
/usr/sbin/sendmail.sendmail - priority 90
...
/usr/sbin/sendmail.postfix - priority 30
...
Current `best' version is /usr/sbin/sendmail.sendmail.
```
в данном случае, это **sendmail.sendmail**

меняем приоритет **sendmail** на 30, a **postfix** на 90 (выше цифар = выше приоритет)

делаем бэкап **/var/lib/alternatives/mta**
```bash
cp /var/lib/alternatives/mta /var/lib/alternatives/mta.bak
```
открываем в редакторе
```bash
mcedit /var/lib/alternatives/mta
```
и там меняем
```plain
/usr/sbin/sendmail.postfix
30 postfix
```
на 
```plain
/usr/sbin/sendmail.postfix
90 postfix
```
и
```plain
/usr/sbin/sendmail.sendmail
90 postfix
```
на 
```plain
/usr/sbin/sendmail.sendmail
30 postfix
```
Изменить приоритет так же можно с помощью команды **update-alternatives -- install**, но с ее помощью это делать сложнее т.к. надо прописывать все зависимости, пример:

```bash
update-alternatives --install /usr/sbin/sendmail sendmail /usr/sbin/sendmail.sendmail 30 \
--slave /etc/pam.d/smtp mta-pam /etc/pam.d/dmtp.sendmail \
--slave /usr/bin/mailq mta-mailq /usr/bin/mailq.sendmail \
--slave /usr/bin/newaliases mta-newaliases /usr/bin/newaliases.sendmail \
--slave /usr/bin/rmail mta-rmail /usr/bin/rmail.sendmail \
--slave /usr/lib/sendmail mta-sendmail /usr/lib/sendmail.sendmail \
--slave /usr/sbin/editmap mta-editmap /usr/sbin/editmap.sendmail \
--slave /usr/sbin/makemap mta-makemap /usr/sbin/makemap.sendmail \
--slave /usr/share/man/man1/mailq.1.gz mta-mailqman /usr/share/man/man1/mailq.sendmail.1.gz \
--slave /usr/share/man/man1/newaliases.1.gz mta-newaliasesman /usr/share/man/man1/newaliases.sendmail.1.gz \
--slave /usr/share/man/man5/aliases.5.gz mta-aliasesman /usr/share/man/man5/aliases.sendmail.5.gz \
--slave /usr/share/man/man8/editmap.8.gz mta-editmapman /usr/share/man/man8/editmap.sendmail.8.gz \
--slave /usr/share/man/man8/makemap.8.gz mta-makemapman /usr/share/man/man8/makemap.sendmail.8.gz \
--slave /usr/share/man/man8/rmail.8.gz mta-rmailman /usr/share/man/man8/rmail.sendmail.8.gz \
--slave /usr/share/man/man8/sendmail.8.gz mta-sendmailman /usr/share/man/man8/sendmail.sendmail.8.gz
```

Проверяем опять MTA
```bash
alternatives --display mta
```
Если он отличается от sendmail.postfix, то устанавливаем его так
```bash
alternatives --set mta /usr/sbin/sendmail.postfix
```
либо выбираем так:
```bash
alternatives --config mta
```
Опять проверяем, чтобы бы sendmail.postfix был выбран и имел наивысший "priority"
```bash
alternatives --display mta
```
Ту же самую проверку можно проверить выполнив
```bash
ls -Al /etc/alternatives/mta
```
Должны увидеть ссылку на **/usr/sbin/sendmail.postfix**

Добавялем postfix в автозагрузку
```bash
systemctl enable postfix
```

Стартуем postfix 
```bash
systemctl start postfix
```

---

### Команды полезные для отладки

Какой сервис слушает 25 порт
```bash
netstat -nlp | grep 25
```

Просмотр очереди postfix
```bash
postqueue -p
```

Принудительная отправка писем
```bash
postqueue -f
```
Повторная отправка писем, который были "заморожены" после нескольких неудачных попыток
```bash
postsuper -H ALL
postqueue -f
```

Лог почты
```bash
tail -n50 /var/log/maillog
```


Формат параметров команды alternatives
```bash
alternatives [options] --install link name path priority [--slave link name path]... [--initscript service]

alternatives [options] --remove name path

alternatives [options] --set name path

alternatives [options] --auto name

alternatives [options] --display name

alternatives [options] --config name 
```

Формат файла **/var/lib/alternatives/mta**
```plain
auto/manual
/usr/sbin/sendmail                          ОСНОВНАЯ ССЫЛКА
{ссылка-1}                                  SLAVE ССЫЛКА 1
{имя-1}                                     SLAVE ИМЯ 1
{ссылка-2}                                  SLAVE ССЫЛКА 2
{имя-2}                                     SLAVE ИМЯ 2
{ссылка-3}                                  SLAVE ССЫЛКА 3
{имя-3}                                     SLAVE ИМЯ 3
...
/usr/sbin/sendmail.postfix                  АЛЬТЕРНАТИВА 1
90 postfix                                  ПРИОРИТЕТ ИМЯ 
{файл-куда-ссылается-ссылка-1}
{файл-куда-ссылается-ссылка-2}
{файл-куда-ссылается-ссылка-3}
...
/usr/sbin/sendmail.sendmail                 АЛЬТЕРНАТИВА 2
30 sendmail                                 ПРИОРИТЕТ ИМЯ
{файл-куда-ссылается-ссылка-1}
                                            ЕСЛИ НЕТ АЛЬТЕРНАТИВЫ, ТО ПРОСТО ПУСТАЯ СТРОКА, ПО ПОРЯДКУ
{файл-куда-ссылается-ссылка-3}      
...
```
пример формирования команды **alternatives --install** и **update-alternatives -- install**
```bash
update-alternatives -- install \
/usr/sbin/sendmail sendmail /usr/sbin/sendmail.sendmail 30 \
--slave {ссылка-1} {имя-1} {файл-куда-ссылается-ссылка-1} \
--slave {ссылка-2} {имя-2} \
--slave {ссылка-3} {имя-3} {файл-куда-ссылается-ссылка-3} \
```
### Проверка отправки писем
Отправляем пусто письмо
```bash
mail -s "Local Outbound SMTP Test" ваш-email@gmail.com < /dev/null
```
или вводим сообщение
```bash
mail -s "Local Outbound SMTP Test" ваш-email@gmail.com
Текст сообщения 1
Текст сообщения 2
<нажимаем Ctrl+D чтобы закончить ввод и отправить>
```
Для использования mail возможно потребуется установить mailutils в Ubuntu
```bash
apt install mailutils
```
или mailx в CentOS
```bash
dnf install mailx
```
Так же обратите внимание, что при отправке на gmail письмо без настройки PTR и SPF с вероятностью 99% окажется в папке СПАМ, так что ищите его там

### Настройка DNS записей
**PTR запись** = запись по которой преобразуется ip-адрес в доменное имя. Указывается у провайдера или хостера для вашего ip. Может быть только одно сопоставление 1 домен = 1 адрес. Принято указывать главный доменный адрес на этом ip. Проверить PTR запись можно так
```
nslookup 8.8.62.108
dig -x 8.8.62.108
```

**MX запись** = указывает какой почтовый домен должен обрабатывать почту для указанного домена. Пример для домена
```
TYPE: MX
HOST: example.com
PRIORITY: 10
ANSWER: mail.domain.com

TYPE: MX
HOST: subdomain.example.com
PRIORITY: 10
ANSWER: mail.domain.com

TYPE: A
HOST: mail.domain.com
ANSWER: 8.8.62.108
```
Письма отправленно на адреса: cat@example.com и dog@subdomain.example.com. У обоих через MX запись будет определен MTA сервер mail.domain.com. Далее для mail.domain.com будет получена A запись, т.е. 8.8.62.108 и на этот адрес будет отправлено письмо для дальнейшей обработки.


**SPF запись** = TXT запись в DNS, в которой указываются ip-адреса скоторых можно отправлять почту для данного домена.
```
TYPE: TXT
HOST: @
ANSWER: v=spf1 ip6:2b02:4f8:161:2353::2 ip6:2b02:4f8:160:6352::2 ip4:8.8.96.89 ip4:8.8.62.108 -all 
```
Ограничение длинны записи 255 символов. Можно указывать несколько ipv4, ipv6 адресов. 
ALL указывает на то, что делать в случае, если домен не соответствует указанным ip:
- -all = Означает, что сервер (отправитель) не распознан и письмо должно быть отклонено.
- ~all = Означает, что сервер (отправитель) не распознан и письмо нужно поместить в Спам.
- +all = Означает, что любой сервер (отправитель) может отправлять, даже если его нет в SPF. НЕ РЕКОМЕНДУЕТСЯ.

### Использованные ресурсы
- https://www.smartertools.com/blog/2019/04/09-understanding-spf-dkim-dmarc
- https://www.youtube.com/watch?v=vrE4d8Qozc0&list=PLHHm04DXWzeLZPU8yhlJ4osBlfZVOpbmz
- https://www.youtube.com/watch?v=y1XUATxYuiY
- https://stackoverflow.com/questions/19142369/create-both-ipv4-and-ipv6-spf-record
- https://www.youtube.com/watch?v=xUTmwcSDvSE
- Проверка: https://mxtoolbox.com

- DKIM: https://www.linuxtechi.com/configure-domainkeys-with-postfix-on-centos-7/
