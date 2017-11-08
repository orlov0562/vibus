<?php include '_header.php'; ?>

<h3>Status</h3>

<ul>
    <li><strong>Httpd:</strong> <?=getStatusForService('httpd')?></li>
    <li><strong>PHP-FPM:</strong> <?=getStatusForService('php-fpm')?></li>
    <li><strong>MariaDB:</strong> <?=getStatusForService('mariadb')?></li>
    <li><strong>Postfix:</strong> <?=getStatusForService('postfix')?></li>
    <li><strong>vsFTPD:</strong> <?=getStatusForService('vsftpd')?></li>
    <li><strong>Crond:</strong> <?=getStatusForService('crond')?></li>
</ul>