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

TG_SEND="yes"
TG_NOTIFY="php /root/scripts/startifdown/tg-notify.php"

CHECK_NGINX="yes"
CHECK_PHP_FPM="yes"
CHECK_MARIADB="yes"
CHECK_REDIS="yes"

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
	if [ "$TG_SEND" == "yes" ]; then
           $TG_NOTIFY "Nginx"
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
        if [ "$TG_SEND" == "yes" ]; then
            $TG_NOTIFY "PHP-FPM"
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
	if [ "$TG_SEND" == "yes" ]; then
            $TG_NOTIFY "MariaDB"
        fi
    else
	echo "- OK"
    fi
fi

if [ "$CHECK_REDIS" == "yes" ]; then
    echo "Check Redis.."
    ps -ef | grep redis | grep -v grep > /dev/null
    if [ $? != 0 ]; then
        echo "- Service down, started.."
        systemctl start redis > /dev/null
        echo $DATE "- Redis down, started" >> $LOG
        if [ "$EMAIL_SEND" == "yes" ]; then
            EMAIL_TEXT="$DATE - Redis down, started"
            echo -e "Subject: $EMAIL_SUBJECT\nFrom: $EMAIL_FROM\nTo: $EMAIL_TO\n\n$EMAIL_TEXT" | $SENDMAIL -t
        fi
	if [ "$TG_SEND" == "yes" ]; then
           $TG_NOTIFY "Redis"
        fi
    else
        echo "- OK"
    fi
fi
```

### Если вклоючена отправка в телеграмм нужно создать и настроить tg-notify.php
```
<?php

define('TELEGRAM_TOKEN', '<ваш-телеграм-токен>');
define('TELEGRAM_CHATID', '<ваш-telegram-chat-id>');

function message_to_telegram($text)
{
    $ch = curl_init();
    curl_setopt_array(
        $ch,
        array(
            CURLOPT_URL => 'https://api.telegram.org/bot' . TELEGRAM_TOKEN . '/sendMessage',
            CURLOPT_POST => TRUE,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_POSTFIELDS => array(
                'chat_id' => TELEGRAM_CHATID,
                'text' => $text,
            ),
        )
    );
    curl_exec($ch);
}

$serviceName = trim($argv[1]) ? trim($argv[1]) : 'Service';

$msg = 'Server server.name'.PHP_EOL
       .'ERROR: '.$serviceName.' was restarted by StartIfDown'.PHP_EOL
;
echo $msg;

message_to_telegram($msg);

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
