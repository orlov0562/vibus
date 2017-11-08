#!/usr/bin/env php
<?php

if (empty($argv[1]) || empty($argv[2])) {
    die('Usage: cpwd [path-to-bin] [dir-to]'.PHP_EOL);
}

if (!is_file($argv[1])) die('Err: First argument not a valid file'.PHP_EOL);
if (!is_dir($argv[2])) die('Err: Second argument not a valid dir'.PHP_EOL);

$argv[2] = rtrim($argv[2], '/');

$output = '';
$status = 0;
exec('/usr/bin/ldd '.$argv[1], $output, $status);

if (!$status) {
    $depFiles = [];

    foreach($output as $line) {
        if (preg_match('~=>(.+)\([^)]+\)$~', $line, $regs) || preg_match('~^\s*(/.+)\([^)]+\)$~', $line, $regs)) {
            $filepath = trim($regs[1]);
            if (!$filepath) continue;
            if (!file_exists($filepath)) {
                die('Can\'t find dependency file '.$filepath);
            }
            $depFiles[] = $filepath;
        }
    }
}

$depFiles[] = $argv[1];

foreach($depFiles as $filepath) {
    $dstFilePath = $argv[2].'/'.ltrim($filepath,'/');
    $dstDirPath = dirname($dstFilePath);

    if (!is_dir($dstDirPath)) {
        echo 'Create dir '.$dstDirPath.' .. ';
        if (mkdir($dstDirPath, 0755, true)) {
            echo 'OK'.PHP_EOL;
        } else {
            echo 'FATAL ERR'.PHP_EOL;
            die(-1);
        }
    }

    echo 'Copy '.$filepath.' to '.$dstFilePath.' .. ';

    if (file_exists($dstFilePath)) {
        echo 'EXISTS'.PHP_EOL;
    } else {
        $status = 0;
        system('cp '.$filepath.' '.$dstFilePath, $status);
        if ($status) {
            echo 'ERR'.PHP_EOL;
        } else {
            echo 'OK'.PHP_EOL;
        }
    }
}

echo 'Successfully copied!'.PHP_EOL;
