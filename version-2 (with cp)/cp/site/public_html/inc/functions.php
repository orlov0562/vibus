<?php

function getAccountsFilePath(){return '/opt/vibus/cp/data/accounts.list';}
function getVhostsFilePath(){return '/opt/vibus/cp/data/vhosts.list';}
function getSiteDirPath(){return '/opt/vibus/site';}
function getPhpFpmTplPath(){return '/opt/vibus/cp/templates/php-fpm/template.conf';}
function getPhpFpmConfDirPath(){return '/opt/vibus/php-fpm/conf';}
function getHttpdTplPath(){return '/opt/vibus/cp/templates/httpd/template.conf';}
function getHttpdConfDirPath(){return '/opt/vibus/httpd/conf';}
function getPublicHtmlTplDirPath(){return '/opt/vibus/cp/templates/site/public_html';}

function esc($str) {
    return htmlspecialchars($str);
}

function getPasswdInfo($user) {
    $ret = null;
    $items = file('/etc/passwd');
    foreach($items as $item) {
	list($login, $pass, $uid, $gid, $name, $home, $shell) = explode(':', trim($item));
	if ($login == $user) {
	    $ret = [
		'login'	=> $login,
		'pass'	=> $pass,
		'uid'	=> $uid,
		'gid'	=> $gid,
		'name'	=> $name,
		'home'	=> $home,
		'shell'	=> $shell,
	    ];
	}
    }
    return $ret;
}

function echo_output($output) {
    if (is_array($output)) $output = implode(PHP_EOL, $output);
    echo '<div><pre style="font-size:0.8em; background-color:#000; color:#00ff00; padding:15px;">'.esc($output).'</pre></div>';
}

function echo_err($err) {
    if (is_array($err)) $err = implode('</li><li>',$output);
    echo '<div style="color:red">ERROR(S):<ul><li>'.$err.'</li></ul></div>';
}

function getAccounts(){
    $ret = [];

    $accounts = file(getAccountsFilePath());

    foreach($accounts as $account) {
        $account = trim($account);
        $passwdInfo = getPasswdInfo($account);
        if (!$account || preg_match('~^[#]~',$account)) continue;
	$ret[] = $account;
    }

    return $ret;
}

function getAccountNameByHash($hash) {
    $ret = null;
    $accounts = getAccounts();
    foreach($accounts as $account) {
	if (md5($account)==$hash) {
	    $ret = $account;
	}
    }
    return $ret;
}

function getStatusForService($service) {
    $status = '<span style="color:gray">n/a</span>';
    if (preg_match('~^\s*Active:(.+)$~m', `sudo systemctl status $service`, $regs)) {
        $status = trim($regs[1]);
        if (strpos($status, 'active (running)')!==false) {
            $status = '<span style="color:#009900; display:inline-block; padding:0 3px; font-size:0.9em;">'.$status.'</span>';
        } else {
            $status = '<span style="background-color:#FF0000; color:#fff; display:inline-block; padding:0 3px; font-size:0.9em;">'.$status.'</span>';
        }
    }
    return $status;
}

function rrmdir($src) {
    $dir = opendir($src);
    while(false !== ( $file = readdir($dir)) ) {
        if (( $file != '.' ) && ( $file != '..' )) {
            $full = $src . '/' . $file;
            if ( is_dir($full) ) {
                rrmdir($full);
            }
            else {
                unlink($full);
            }
        }
    }
    closedir($dir);
    rmdir($src);
}

function getSortedDomains(){
    $items = file(getVhostsFilePath());

    $sorted = [];

    foreach($items as $item) {

	$item = trim($item);
	if (!$item || preg_match('~^[#]~',$item)) continue;

	list($account, $domain) = explode('=', $item);

	$account = trim($account);
	$domain = trim($domain);

	$sorted[$account][$domain] = $domain;
    }
    return $sorted;
}

function saveSortedDomains($sorted){

    $data = [];
    foreach($sorted as $account=>$items) {
        foreach($items as $domain) {
            $data[] = $account.' = '.$domain;
        }
    }

    file_put_contents(getVhostsFilePath(), implode(PHP_EOL, $data).PHP_EOL);
}

function createDirStruct($dirStruct){
    foreach($dirStruct as $item) {
        $output=[];
        $status = 0;
        exec('sudo mkdir -p '.$item[0].' 2>&1', $output, $status);
        if ($status) {
            echo_err('Error code '.$status);
            echo_output($output);
        }

        if (!$status) {
            $output=[];
            $status = 0;
            exec('sudo chown '.$item[2].':'.$item[3].' '.$item[0].' 2>&1', $output, $status);
            if ($status) {
                echo_err('Error code '.$status);
                echo_output($output);
            }
        }

        if (!$status) {
            $output=[];
            $status = 0;
            exec('sudo chmod '.$item[1].' '.$item[0].' 2>&1', $output, $status);
            if ($status) {
                echo_err('Error code '.$status);
                echo_output($output);
            }
        }

        if ($status) break;
    }

    return $status;
}

function deleteDomain($account, $domain) {
    $output=[];
    $status = 0;
    exec('sudo rm -rf '.getSiteDirPath().'/'.$account.'/'.$domain.' 2>&1', $output, $status);
    if ($status) {
        echo_err('Error code '.$status);
        echo_output($output);
    }

    if (!$status) {

        $phpFpmConfFile = getPhpFpmConfDirPath().'/'.$account.'-'.$domain.'.conf';
        if (file_exists($phpFpmConfFile)) {
            unlink($phpFpmConfFile);
        }

        $httpdConfFile = getHttpdConfDirPath().'/'.$account.'-'.$domain.'.conf';
        if (file_exists($httpdConfFile)) {
            unlink($httpdConfFile);
        }

        $sortedDomains = getSortedDomains();
        unset($sortedDomains[$account][$domain]);
        saveSortedDomains($sortedDomains);
    }

    return $status;
}

