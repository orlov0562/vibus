# LinkChecker
- site: http://wummel.github.io/linkchecker/index.html
- main: http://wummel.github.io/linkchecker/man1/linkchecker.1.html

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
