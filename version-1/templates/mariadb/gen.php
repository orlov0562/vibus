<?php

    if (count($argv)<3) {
	die('Usage: gen.php DBNAME USERNAME PASSWORD'.PHP_EOL);
    }

    $dbname = trim($argv[1]);
    $username = trim($argv[2]);
    $password = trim($argv[3]);

    $confFileName = $dbname.'-'.$username.'.sql';

    $data = file_get_contents('template.sql');

    $data = str_replace(['{DBNAME}','{USERNAME}','{PASSWORD}'], [$dbname, $username, $password], $data);

    file_put_contents($confFileName, $data);
    
    echo 'Sql file successfully created!'.PHP_EOL;