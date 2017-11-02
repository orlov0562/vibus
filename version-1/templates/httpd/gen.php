<?php

    if (count($argv)<3) {
	die('Usage: gen.php USER GROUP DOMAIN'.PHP_EOL);
    }

    $user = trim($argv[1]);
    $group = trim($argv[2]);
    $domain = trim($argv[3]);

    $confFileName = $user.'-'.$domain.'.conf';

    $data = file_get_contents('template.conf');

    $data = str_replace(['{USER}','{GROUP}','{DOMAIN}'], [$user, $group, $domain], $data);

    file_put_contents($confFileName, $data);
    
    echo 'Config file successfully created!'.PHP_EOL;