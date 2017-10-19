# Vibus, версия 1
## Проверка настройки PHP 
Создайте файл **/opt/vibus/site/root/localhost/public_html/test.php**
```bash
mcedit /opt/vibus/site/root/localhost/public_html/test.php
```
со следующим содержимым
```php
<?php
    $ses = session_start();
?>
<!doctype html>
<html>
<head>
    <title>Test</title>
</head>
<body>
<?php

echo 'SESSION: '.($ses?'OK':'ERR').'<hr>';

echo 'WHOAMI: '.exec('whoami').'<hr>';

echo 'TMPDIR: '.sys_get_temp_dir().'<hr>';

echo 'WRITE TO TMPDIR: ('.sys_get_temp_dir().'/test.txt): ';
echo file_put_contents(sys_get_temp_dir().'/test.txt', time()) ? 'OK' : 'ERR';
if (file_exists(sys_get_temp_dir().'/test.txt')) unlink(sys_get_temp_dir().'/test.txt');
echo '<hr>';


echo 'OPEN_BASEDIR (WRITE TO /tmp/test.txt): ';
echo file_put_contents('/tmp/test.txt', time()) ? 'OK' : 'ERR';
echo '<hr>';

?>
</body>
</html>
```
Перейдите по адресу: http://xx.xx.xx.xx/test.php

Должны получить такой результат:
```plain
SESSION: OK
-----------------------
WHOAMI: apache
-----------------------
TMPDIR: /opt/vibus/site/root/localhost/tmp
-----------------------
WRITE TO TMPDIR: (/opt/vibus/site/root/localhost/tmp/test.txt): OK
-----------------------
OPEN_BASEDIR (WRITE TO /tmp/test.txt):
  Warning: file_put_contents(): open_basedir restriction in effect. File(/tmp/test.txt) is not within the allowed path(s): (/opt/vibus/site/root/localhost/public_html/) in /opt/vibus/site/root/localhost/public_html/test.php on line 19
  Warning: file_put_contents(/tmp/test.txt): failed to open stream: Operation not permitted in /opt/vibus/site/root/localhost/public_html/test.php on line 19
ERR
```
Тут проверяется что сессии имеют доступ в папку сессий; Пользователь от которого выполняется скрипт; Временная папка; Доступ во временную папку; Ограничение скрипта в папках /public_html и /tmp (open_basedir)
