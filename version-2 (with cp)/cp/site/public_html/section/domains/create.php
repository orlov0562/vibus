<?php
    include '_header.php';

    $accounts = getAccounts();
?>

<h3>Add domain</h3>

<form action="/domains/create?action=create" method="post">

    <label>Domain</label>
    <input type="text" name="domain_name" value="<?=!empty($_POST['domain_name'])?esc($_POST['domain_name']):''?>">
    <br>

    <label>Account</label>
    <select name="account_name" value="">
	<?php foreach($accounts as $account):?>
	<option value="<?=md5($account)?>"
	<?=(!empty($_POST['account_name']) && $_POST['account_name'] == md5($account)?' selected="selected"':'')?>
	><?=esc($account)?></option>
	<?php endforeach?>
    </select>
    <br>


    <input type="submit" value="Create">
</form>

<?php

    if (!empty($_GET['action']) && $_GET['action']=='create') {
	echo '<hr>';
	if (!empty($_POST['account_name'])) {
	    $account = getAccountNameByHash($_POST['account_name']);
	    if ($account) {

		if (!empty($_POST['domain_name']) && trim($_POST['domain_name'])) {
		    $domain = trim($_POST['domain_name']);

		    if (preg_match('~^[-a-z0-9.]+$~', $account)) {
                        $regexp = '~^\s*'.preg_quote($account).'\s*=\s*'.preg_quote($domain).'\s*$~m';
                        $vhosts = file_get_contents(getVhostsFilePath());
                        if (!preg_match($regexp, $vhosts)) {

                            // create directory structure
                            $dirStruct = [
                                [getSiteDirPath().'/'.$account.'/'.$domain,                     '0750', 'vibus', $account],
                                [getSiteDirPath().'/'.$account.'/'.$domain.'/backup',           '0750', 'vibus', $account],
                                [getSiteDirPath().'/'.$account.'/'.$domain.'/backup',           '0750', 'vibus', $account],
                                [getSiteDirPath().'/'.$account.'/'.$domain.'/backup/store',     '0770', 'vibus', $account],
                                [getSiteDirPath().'/'.$account.'/'.$domain.'/backup/script',    '0770', 'vibus', $account],
                                [getSiteDirPath().'/'.$account.'/'.$domain.'/log',              '0750', 'vibus', $account],
                                [getSiteDirPath().'/'.$account.'/'.$domain.'/log/httpd',        '0750', 'vibus', $account],
                                [getSiteDirPath().'/'.$account.'/'.$domain.'/log/nginx',        '0750', 'vibus', $account],
                                [getSiteDirPath().'/'.$account.'/'.$domain.'/log/php-fpm',      '0750', 'vibus', $account],
                                [getSiteDirPath().'/'.$account.'/'.$domain.'/public_html',      '0770', 'vibus', $account],
                                [getSiteDirPath().'/'.$account.'/'.$domain.'/secret',           '0770', 'vibus', $account],
                                [getSiteDirPath().'/'.$account.'/'.$domain.'/session',          '0770', 'vibus', $account],
                                [getSiteDirPath().'/'.$account.'/'.$domain.'/tmp',              '0770', 'vibus', $account],
                            ];

                            $status = createDirStruct($dirStruct);

                            if (!$status) {
                                $phpfpmConf = file_get_contents(getPhpFpmTplPath());
                                $phpfpmConf = str_replace(['{USER}', '{GROUP}'], $account, $phpfpmConf);
                                $phpfpmConf = str_replace('{DOMAIN}', $domain, $phpfpmConf);
                                file_put_contents(getPhpFpmConfDirPath().'/'.$account.'-'.$domain.'.conf', $phpfpmConf);

                                $httpdConf = file_get_contents(getHttpdTplPath());
                                $httpdConf = str_replace('{USER}', $account, $httpdConf);
                                $httpdConf = str_replace('{DOMAIN}', $domain, $httpdConf);
                                file_put_contents(getHttpdConfDirPath().'/'.$account.'-'.$domain.'.conf', $httpdConf);

                                $publicHtmlTplDir = getPublicHtmlTplDirPath();
                                if (!$status && is_dir($publicHtmlTplDir)) {
                                    $output=[];
                                    $status = 0;
                                    exec('cp -r '.$publicHtmlTplDir.'/* '.getSiteDirPath().'/'.$account.'/'.$domain.'/public_html/ 2>&1', $output, $status);
                                    if ($status) {
                                        echo_err('Error code '.$status);
                                        echo_output($output);
                                    }

                                    if (!$status) {
                                        $output=[];
                                        $status = 0;
                                        exec('sudo chown -R '.$account.':'.$account.' '.getSiteDirPath().'/'.$account.'/'.$domain.'/public_html/* 2>&1', $output, $status);
                                        if ($status) {
                                            echo_err('Error code '.$status);
                                            echo_output($output);
                                        }
                                    }
                                }

                                $output=[];
                                $status = 0;
                                exec('sudo systemctl reload httpd php-fpm 2>&1', $output, $status);
                                if ($status) {
                                    echo_err('Error code '.$status);
                                    echo_output($output);
                                }
                            }

                            if (!$status) {
                                $sortedDomains = getSortedDomains();
                                $sortedDomains[$account][$domain] = $domain;
                                saveSortedDomains($sortedDomains);

                                echo 'Domain successfully created';
                            }


                        } else {
                            echo_err('Domain already exists');
                        }
		    }  else {
                        echo_err('Domain contains not allowed characters');
                    }

		} else {
		    echo_err('Domain name not specified');
		}

	    } else {
		echo_err('Account not found');
	    }
	} else {
	    echo_err('Empty account name');
	}
    }
