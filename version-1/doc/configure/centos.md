# Vibus, версия 1

## Установка и настройка CentOS

Я буду писать настройку на основе установки минимального образа в VirtualBox. Если Вы производите настройку на VPS, Dedicated сервере, то пункты по установке, настройке сети можно пропустить и сразу переходить к пункту **SSH**.

## Настройка VirtualBox
- Создаем виртуальную машину.
- Настраиваем второй сетевой интерфейс (первый уже должен быть настроен в режиме NAT), чтобы машина появилась в вашей сети
  - заходим в **Settings** виртуальной машины
  - переходим в раздел **Network**, вкладка **Adapter 2**
    - Включаем **[x] Enable Network Adapter**
    - Attached to: **Bridged Adapter**
  - сохраняемся

## CentOS Minimal

Скачать образ CentOS Minimal можно на этой странице [https://www.centos.org/download/](https://www.centos.org/download/), устанавливаем ОС.

## Настройка сети

Проверяем сетевые интерфейсы
```bash
nmcli d
```
Проверяем сеть
```bash
ip a
```
Если вы не настроили сеть при установке ОС, то делаем следующее:

```bash
nmtui
```
в появившемся окне:
- выбираем **Edit connection**
- выбираем интерфейс
- ставим галку напротив **[x] Automatically connect**
- нажимаем **Done**, **Back**, повторяем для всех интерфейсов
- **Quit**

Перезапускаем сеть
```bash
service network restart
```

Проверяем что интерфейсы получили ip по dhcp
```bash
service network restart
```
Запоминаем ip второго интерфейса, везде дальше, я буду обозначать этот ip как **xx.xx.xx.xx**

Проверяем, что **xx.xx.xx.xx** пингуется с нашего ПК

Если надо настроить статические ip (т.е. нет в сети dhcp), то читаем инструкцию [тут](https://lintut.com/how-to-configure-static-ip-address-on-centos-7/)


## SSH
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

создастся два файла **SERVERNAME** и **SERVERNAME.pub**

устанавливаем им права **0600**
```bash
chmod 0600 ~/.ssh/SERVERNAME ~/.ssh/SERVERNAME.pub
```
Выводим содержимое файла **SERVERNAME.pub**
```bash
cat ~/.ssh/SERVERNAME.pub
```
копируем содержимое **НА СЕРВЕР** в файл /root/.ssh/authorized_keys (1 запись = 1 строка, если там уже что-то есть, просто копируете в новую строку)

При необходимости настраиваем свой SSH агент (например Putty или PAC), выбрав в нем авторизацию по ключу и сам ключ **~/.ssh/SERVERNAME** (второй файл, который без расширения .pub)

### Настраиваем ssh авторизацию на сервере только для root, по ключу

Делаем бэкап конфигурационого файла
```bash
mv /etc/ssh/sshd_config /etc/sshd_config.orig
```
Создаем новый файл конфигурации
```bash
mcedit /etc/ssh/sshd_config
```
с таким содержимым
```plain
SyslogFacility AUTHPRIV
PermitRootLogin without-password
PubkeyAuthentication yes
AuthorizedKeysFile	.ssh/authorized_keys
PasswordAuthentication no
ChallengeResponseAuthentication no
UsePAM yes
UseDNS no
UsePrivilegeSeparation sandbox		# Default for new installations.

# Accept locale-related environment variables
AcceptEnv LANG LC_CTYPE LC_NUMERIC LC_TIME LC_COLLATE LC_MONETARY LC_MESSAGES
AcceptEnv LC_PAPER LC_NAME LC_ADDRESS LC_TELEPHONE LC_MEASUREMENT
AcceptEnv LC_IDENTIFICATION LC_ALL LANGUAGE
AcceptEnv XMODIFIERS

# override default of no subsystems
Subsystem	sftp	/usr/libexec/openssh/sftp-server
DenyGroups no_sshgroup
```
сохраняем конфиг, перезапускаем sshd
```bash
service ssh restart
```
Теперь, не разрывая сессию (если это удаленная машина), пробуем авторизоваться без логина и пароля, по ключу. Если подключение не удалось, разбираемся где накосячили, в крайнем случае, возвращаем оригинальный конфиг и рестартуем, чтобы применился он
```bash
cp /etc/ssh/sshd_config.orig /etc/sshd_config
service ssh restart
```
Если же авторизация по ключу прошла успешно, то разрываем ssh соединение по логину/паролю, и пробуем залогиниться. Должны получать ошибку.

## Настройка репозиториев и обновление ПО

Для использования свежего PHP, добавляем Remi репозиторий

```bash
yum install http://rpms.remirepo.net/enterprise/remi-release-7.rpm
```

обновляемся

```bash
yum upgrade
```
## Установка базового ПО
Устанавливаем ПО, которое Вы постоянно используете (у вас может быть другой список)
```bash
yum install wget mc htop screen net-tools
```

## Настраиваем SELinux
В этом мануале я отключаю его полностью.

Открываем **/etc/sysconfig/selinux**
```bash
# mcedit /etc/sysconfig/selinux
```
ищем
```plain
SELINUX=enforcing
```
меняем на
```plain
SELINUX=disabled
```
сохраняемся, перезагружаемся

## Настраиваем iptables
По-умолчанию используется **firewalld**, я предпочитаю использовать **iptables** напрямую. 

Останавливаем, выключаем из автозагрузки и удаляем **firewalld**
```bash
systemctl stop firewalld
systemctl disable firewalld
yum remove firewalld
```
Устанавливаем **iptables-service** и добавляем в автозагрузку
```bash
yum install iptables-services
systemctl enable iptables
```
Очищаем все правила **iptables**
```bash
iptables -P INPUT ACCEPT
iptables -P FORWARD ACCEPT
iptables -P OUTPUT ACCEPT
iptables -t nat -F
iptables -t mangle -F
iptables -F
iptables -X
```
Проверяем, что все правила удалены
```bash
iptables --line -vnL
```
Разрешаем входящие соединения только на порты 21,22,80,443
```bash
# Exceptions to default policy
iptables -A INPUT -p tcp --dport 21 -j ACCEPT       # FTP
iptables -A INPUT -p tcp --dport 22 -j ACCEPT       # SSH
iptables -A INPUT -p tcp --dport 80 -j ACCEPT       # HTTP
iptables -A INPUT -p tcp --dport 443 -j ACCEPT      # HTTPS

iptables -A INPUT -m conntrack --ctstate RELATED,ESTABLISHED -j ACCEPT
iptables -A INPUT -m state --state ESTABLISHED,RELATED -j ACCEPT

iptables -A FORWARD -m state --state ESTABLISHED,RELATED -j ACCEPT

# Setting default policies:
iptables -P INPUT DROP
iptables -P FORWARD DROP
iptables -P OUTPUT ACCEPT
```

Проверяем, что все правила верны
```bash
iptables --line -vnL
```
Сохраняем правила, для автозагрузки
```bash
service iptables save
```
## Завершение установки
Перезагружаемся

