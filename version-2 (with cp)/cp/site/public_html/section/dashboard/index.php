<h2>Dashboard</h2>

<h3>Services</h3>
<ul>
    <li><strong>Httpd:</strong> <?=getStatusForService('httpd')?></li>
    <li><strong>PHP-FPM:</strong> <?=getStatusForService('php-fpm')?></li>
    <li><strong>MariaDB:</strong> <?=getStatusForService('mariadb')?></li>
    <li><strong>Postfix:</strong> <?=getStatusForService('postfix')?></li>
    <li><strong>vsFTPD:</strong> <?=getStatusForService('vsftpd')?></li>
    <li><strong>Crond:</strong> <?=getStatusForService('crond')?></li>
</ul>

<h3>Server</h3>
<ul>
    <li><strong>Time:</strong> <?=`date`?></li>
    <li><strong>Uptime:</strong> <?=`uptime`?></li>
</ul>

<h3>Control panel</h3>
<ul>
    <li><strong>Web server user:</strong> <?=`whoami`?></li>
<ul>