# Vibus, версия 1

## Установка и настройка SSH в CentOS

По-умолчанию ssh server уже установлен, поэтому для удобства и сходства с установкой на VPS, заходим в систему через SSH (например через терминал, PAC или Putty)

### Создаем ключи авторизации ssh для root пользователя

На своем ПК, заходим в папку **.ssh** которая находится в домашней папке
```bash
cd ~/.ssh
```
если папка отсутствует, создаем её и заходим
```bash
mkdir ~/.ssh && cd ~/.ssh
```
создаем новую пару ключей
```bash
ssh-keygen -f SERVERNAME
```
** SERVERNAME заменить на произвольное имя вашего сервера

вводим и запоминаем пароль от ключа

создастся два файла **SERVERNAME** и **SERVERNAME.pub**

устанавливаем им права **0600**
```bash
chmod 0600 ~/.ssh/SERVERNAME ~/.ssh/SERVERNAME.pub
```
Выводим содержимое файла **SERVERNAME.pub**
```bash
cat ~/.ssh/SERVERNAME.pub
```
копируем содержимое **НА СЕРВЕР** в файл **/root/.ssh/authorized_keys** (1 запись = 1 строка; если там уже что-то есть, просто копируете в новую строку; если файл не существует создаете его)
```bash
mcedit /root/.ssh/authorized_keys
```

При необходимости настраиваем свой SSH агент (например Putty или PAC), выбрав в нем авторизацию по ключу, сам ключ **~/.ssh/SERVERNAME** (второй файл, который без расширения .pub) и указав пароль от ключа. В зависимости от клиента возможно так же потребуется указать логин пользователя (т.е. root).

Если Вы работаете с SSH прямо из Linux консоли или Midnight Commander-а, то прописываем ключ для хостов в файле **~/.ssh/config** на **ВАШЕМ ПК**:
```bash
mcedit ~/.ssh/config
```
и добавляем туда записи в таком формате (в качестве хоста надо указывать адрес который вы будете передавать в ssh клиент:доменное имя или ip-адрес)
```bash
TCPKeepAlive yes
ServerAliveInterval 90

Host site.com
  IdentityFile ~/.ssh/SERVERNAME

Host 0.0.0.0
  IdentityFile ~/.ssh/SERVERNAME
```

### Настраиваем ssh авторизацию на сервере только для root, по ключу

Делаем бэкап конфигурационого файла
```bash
mv /etc/ssh/sshd_config /etc/ssh/sshd_config.orig
```
Создаем новый файл конфигурации
```bash
mcedit /etc/ssh/sshd_config
```
с таким содержимым
```plain
#Port 2222

AllowUsers root
DenyGroups no_sshgroup

SyslogFacility AUTHPRIV
PermitRootLogin without-password
PubkeyAuthentication yes
AuthorizedKeysFile	.ssh/authorized_keys
PasswordAuthentication no
ChallengeResponseAuthentication no
UsePAM yes
UseDNS no
LoginGraceTime 1m
ClientAliveInterval 600
ClientAliveCountMax 0

# Accept locale-related environment variables
AcceptEnv LANG LC_CTYPE LC_NUMERIC LC_TIME LC_COLLATE LC_MONETARY LC_MESSAGES
AcceptEnv LC_PAPER LC_NAME LC_ADDRESS LC_TELEPHONE LC_MEASUREMENT
AcceptEnv LC_IDENTIFICATION LC_ALL LANGUAGE
AcceptEnv XMODIFIERS

# override default of no subsystems
Subsystem	sftp	/usr/libexec/openssh/sftp-server
```
если планируем коннектится по sftp как www-data, надо добавить следующее
```
AllowUsers root www-data

Match User www-data
      ChrootDirectory /var/www/
      ForceCommand internal-sftp
      X11Forwarding no
      AllowTcpForwarding no
      PasswordAuthentication no
```
так же нужно добавить pub key в /var/www/.ssh/authorized_keys 
```
mkdir -p /var/www/.ssh
chmod 0700 /var/www/.ssh
chown www-data:www-data /var/www/.ssh
touch /var/www/.ssh/authorized_keys 
chmod 0600 /var/www/.ssh/authorized_keys 
chown www-data:www-data /var/www/.ssh/authorized_keys 
mcedit /var/www/.ssh/authorized_keys
```
добавлять именно в /var/www надо потмоу, что у пользователя www-data, эта папка является домашней, а в нашем конфиге поиск этого файла определен иммено там
```
$ cat /etc/passwd | grep www-data
www-data:x:33:33:www-data:/var/www:/usr/sbin/nologin
```

сохраняем конфиг, перезапускаем sshd
```bash
service sshd restart
```
Теперь, не разрывая сессию (если это удаленная машина), пробуем авторизоваться без логина и пароля, по ключу. Если подключение не удалось, разбираемся где накосячили, в крайнем случае, возвращаем оригинальный конфиг и рестартуем, чтобы применился он
```bash
cp /etc/ssh/sshd_config.orig /etc/sshd_config
service sshd restart
```
Если же авторизация по ключу прошла успешно, то разрываем ssh соединение по логину/паролю, и пробуем залогиниться. Должны получать ошибку.

Для упрощения подключения к sftp на нашей машине, в папке ~/.ssh/config можем прописать alias-ы и дефолтные настройки
```
TCPKeepAlive yes
ServerAliveInterval 90

Host ftp.site.com
  Hostname site.com
  Port 2222
  User www-data
  IdentityFile ~/.ssh/server-private-key
```
после этого можно будет коннектится такой командой sftp ftp.site.com

В случае проблем, надо попробовать законнектится со своей машины с включением детального лога
```
ssh -p 22 -i /path/to/private-key -vvv user@site.com
sftp -p 22 -i /path/to/private-key -vvv user@site.com
```
на сервере логи соединений нужно искать в папке /var/log:
```
/var/log/auth.log
```
