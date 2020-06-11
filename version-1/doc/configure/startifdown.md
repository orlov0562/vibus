# Startifdown

Скрипт который проверяет запущены ли основные сервисы веб-сервера: nginx, PHP-FPM, Mariadb и запускает их если это не так.
Ведет лог, а так же имеет возможность уведомления по email в случае проблем.

### Основной скрипт

Создаем скрипт
```bash
mkdir -p /root/scripts/startifdown
touch /root/scripts/startifdown/startifdown.sh
chmod +x /root/scripts/startifdown/startifdown.sh
nano /root/scripts/startifdown/startifdown.sh
```
С таким содержимым
```
#!/bin/bash

LOG="/var/log/startifdown/startifdown.log"
DATE=`date "+%Y-%m-%d %T"`
SERVER_NAME="server.name"

SENDMAIL="/usr/sbin/sendmail"
EMAIL_SEND="no"
EMAIL_TO="your@email.here"
EMAIL_FROM="no-reply@server.name"
EMAIL_SUBJECT="Startifdown: Service restored on $SERVER_NAME"
EMAIL_TEXT="$DATE Service restored"

CHECK_NGINX="yes"
CHECK_PHP_FPM="yes"
CHECK_MARIADB="yes"

mkdir -p `dirname $LOG` && touch $LOG

#Scripts to start services if not running

if [ "$CHECK_NGINX" == "yes" ]; then
    echo "Check Nginx.."
    ps -ef | grep nginx | grep -v grep > /dev/null
    if [ $? != 0 ]
    then
	echo "- Service down, started.."
	systemctl start nginx > /dev/null
        echo $DATE "- Nginx down, started" >> $LOG
	if [ "$EMAIL_SEND" == "yes" ]; then
	    EMAIL_TEXT="$DATE - Nginx down, started"
	    echo -e "Subject: $EMAIL_SUBJECT\nFrom: $EMAIL_FROM\nTo: $EMAIL_TO\n\n$EMAIL_TEXT" | $SENDMAIL -t
        fi
    else
	echo "- OK"
    fi
fi

if [ "$CHECK_PHP_FPM" == "yes" ]; then
    echo "Check PHP_FPM.."
    ps -ef | grep php-fpm | grep -v grep > /dev/null
    if [ $? != 0 ]
    then
	echo "- Service down, started.."
	systemctl start php-fpm > /dev/null
        echo $DATE "- PHP-FPM down, started" >> $LOG
	if [ "$EMAIL_SEND" == "yes" ]; then
	    EMAIL_TEXT="$DATE - PHP-FPM down, started"
	    echo -e "Subject: $EMAIL_SUBJECT\nFrom: $EMAIL_FROM\nTo: $EMAIL_TO\n\n$EMAIL_TEXT" | $SENDMAIL -t
	fi
    else
	echo "- OK"
    fi
fi

if [ "$CHECK_MARIADB" == "yes" ]; then
    echo "Check Mariadb.."
    ps -ef | grep mysql | grep -v grep > /dev/null
    if [ $? != 0 ]
    then
	echo "- Service down, started.."
	systemctl start mariadb > /dev/null
        echo $DATE "- Mariadb down, started" >> $LOG
	if [ "$EMAIL_SEND" == "yes" ]; then
	    EMAIL_TEXT="$DATE - Mariadb down, started"
	    echo -e "Subject: $EMAIL_SUBJECT\nFrom: $EMAIL_FROM\nTo: $EMAIL_TO\n\n$EMAIL_TEXT" | $SENDMAIL -t
	fi
    else
	echo "- OK"
    fi
fi
```
### Cron
Добавляем скрипт в крон для запуска каждую минуту
```
crontab -e
```
добавляем
```
# Startifdown
*          *        *        *        *        /root/scripts/startifdown/startifdown.sh >/dev/null 2>&1
```

### Logrotate
Создаем конфиг
```
nano /etc/logrotate.d/startifdown
```
с таким содержимым
```
/var/log/startifdown/startifdown.log
{
    daily
    missingok
    rotate 14
    compress
    delaycompress
    notifempty
}
```
проверить все ли ок, можно так
```
logrotate -d /etc/logrotate.d/startifdown
```
