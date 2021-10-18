# LinkChecker
- site: http://wummel.github.io/linkchecker/index.html
- main: http://wummel.github.io/linkchecker/man1/linkchecker.1.html

# Installation on CentOS 8
```bash
dnf install python3-pip
pip3 install git+https://github.com/linkchecker/linkchecker.git
```
Если планируется запуск от www-data и домашняя папка (например /var/www) находится в Chroot (принадлежит root пользователю), то нужно создать подпапки и дать им права на запись
```
mkdir -p /var/www/.local/share/linkchecker/plugins
chown -R www-data:www-data /var/www/.local

mkdir -p /var/www/.config/linkchecker/linkcheckerrc
chown -R www-data:www-data /var/www/.config
```

# Instalation on CentOS 7

```bash
yum install python2-pip python-devel gcc
pip install --upgrade pip
pip install linkchecker
```
в случае ошибки
```text
This program requires Python requests 2.2.0 or later.
```
устанавливаем requests
```bash
pip install requests==2.9.2
```

# Instalation on CentOS 8

```bash
dnf install python2-pip python2-devel gcc
pip2.7 install --upgrade pip
pip2.7 install linkchecker
```

# Run with timeout from cli example

```bash
timeout -sHUP 1m linkchecker http://site.com
timeout -sINT 1m linkchecker http://site.com
```

# SIGNALS

**Common kill signal**
```plain
SIGHUP    1 	Hangup
SIGINT    2 	Interrupt from keyboard
SIGKILL   9 	Kill signal (never graceful)
SIGTERM   15 	Termination signal
SIGSTOP   17,19,23 	Stop the process 
```
