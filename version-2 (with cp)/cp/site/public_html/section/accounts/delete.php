<?php include '_header.php';?>

<h3>Delete account</h3>

<?php

    if (!empty($_GET['id'])) {

        $accounts = file(getAccountsFilePath());

        $deleted = false;

        foreach ($accounts as $k=>$account) {
            $account = trim($account);
            if (md5($account)==$_GET['id']) {
                    if (getPasswdInfo($account)) {

                    $output=[];
                    $status = 0;
                    exec('sudo rm -rf '.getSiteDirPath().'/'.$account.' 2>&1', $output, $status);
                    if ($status) {
                        echo_err('Error code '.$status);
                        echo_output($output);
                    }

                    if (!$status) {
                        $output=[];
                        $status = 0;
                        exec('sudo userdel -rf '.$account.' 2>&1', $output, $status);
                        if ($status) {
                            echo_err('Error code '.$status);
                            echo_output($output);
                        }
                    }

                    if (!$status) {
                        $output=[];
                        $status = 0;
                        exec('sudo groupdel '.$account.' 2>&1', $output, $status);
                        if ($status) {
                            echo_err('Error code '.$status);
                            echo_output($output);
                        }
                    }

                    if (!$status) {
                        $sortedDomains = getSortedDomains();
                        if (!empty($sortedDomains[$account])) {

                            foreach($sortedDomains[$account] as $domain) {
                                $status = deleteDomain($account, $domain);
                                if ($status) break;
                            }

                            if (!$status) {
                                $output=[];
                                $status = 0;
                                exec('sudo systemctl reload httpd php-fpm 2>&1', $output, $status);
                                if ($status) {
                                    echo_err('Error code '.$status);
                                    echo_output($output);
                                }
                            }

                            if (!$status) {
                                unset($sortedDomains[$account]);
                                saveSortedDomains($sortedDomains);
                            }
                        }

                        if (!$status) {
                            unset($accounts[$k]);
                            file_put_contents(getAccountsFilePath(), implode($accounts));
                            echo 'Account successfully deleted';
                        }
                    }

                } else {
                    unset($accounts[$k]);
                    file_put_contents(getAccountsFilePath(), implode($accounts));
                    echo 'Account successfully deleted';
                }

                $deleted = true;

                break;
            }
        }

        if (!$deleted) echo_err('Account not found');
    }
