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

## Настройка имени хоста
```bash
# Текущие настройки
hostnamectl status

# Установка полного имени
hostnamectl server-name.domain.tld --static

# Установка сокращеного имени (например, по-умолчанию используется для командной строки)
hostnamectl server-name.domain.tld --pretty
```
При необходимости правим так же файл /etc/hosts
```bash
mcedit /etc/hosts
```
примерно так
```plain
xx.xx.xx.xx server-name.domain.tld www.server-name.domain.tld
```
## SSH
[Настройка SSH в CentOS](ssh.md)

## Настройка репозиториев и обновление ПО

Для использования свежего PHP, добавляем Epel и Remi репозиторий

```bash
dnf install https://dl.fedoraproject.org/pub/epel/epel-release-latest-8.noarch.rpm
dnf install https://rpms.remirepo.net/enterprise/remi-release-8.rpm
```

обновляемся

```bash
yum upgrade
```
## Установка базового ПО
Устанавливаем ПО, которое Вы постоянно используете (у вас может быть другой список)
```bash
dnf install wget curl ftp mc htop screen bind-utils net-tools nmap telnet nano git composer
```

## Кодировка
При попытке установить пакеты выскочит ошибка кодировки по-умолчанию
```bash
Failed to set locale, defaulting to C.UTF-8
```
Чтобы это исправить выполняекм следующие команды
```bash
localectl set-locale LANG=en_US.UTF-8
dnf install langpacks-en glibc-all-langpacks -y
```

## Редактор по-умолчанию (например для крона)
```bash
mcedit ~/.bash_profile
```
и добавляем в конец
```plain
...
export VISUAL="mcedit"
export EDITOR="mcedit"
```
сохраняемся и применяем настройки к текущей сессии
```bash
source ~/.bash_profile
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

## Настраиваем mlocate
Актуально для vps с ограничением iops и кеш серверов с большим кол-вом файлов.
Если на сервере будет много кеш файлов, удаляем [mlocate](https://packages.debian.org/ru/sid/mlocate) (поиск файлов по маске используя заранее кешированую структуру файлов), т.к. он запускает по крону /usr/bin/updatedb, который создает большую нагрузку на диск и процессор во время обновлениыя базы файлов.
```bash
yum remove mlocate
```
Если все же mlocate нужен, тогда тюнингуем его крон задачу, для запуска в определенное время.

## Настраиваем firewalld
[Настройка FirewallD в CentOS](firewalld.md)

## Настраиваем iptables
[Настройка Iptables в CentOS](iptables.md)

## Завершение установки
Перезагружаемся

## Полезные команды
### Установка/Удаление rpm
```bash
# установка
rpm -ivh webmin.rpm
# удаление
rpm -qa | grep -i webmin
rpm -e <package name>
```

### Проверка портов
Какие сервисы на каких портах
```bash
nmap localhost
```
Кто занял определенный порт
```bash
netstat -lntp | grep ":80"
```
Открыт ли порт
```bash
telnet localhost 443
```

### Информация о дисках
Посмотреть какие диски доступны в системе
```bash
ls /dev/sd*
```
Для просмотра информации нужен пакет smartmontools
```bash
yum install smartmontools
```
Смотрим информацию о конкретном диске
```bash
smartctl -a /dev/sda | less
```
Смотрим состояние рейд массива
```bash
cat /proc/mdstat
```
