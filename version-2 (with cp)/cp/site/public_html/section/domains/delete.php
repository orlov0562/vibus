<?php
    include '_header.php';
?>

<h3>Delete domain</h3>

<?php

    if (!empty($_GET['id'])) {

        $sorted = getSortedDomains();

	$deleted = false;

        foreach($sorted as $account=>$domains) {
            foreach($domains as $domain) {
                if (md5($account.$domain)==$_GET['id']) {

                    // delete domain dir

                    $status = deleteDomain($account, $domain);

                    if (!$status) {
                        echo 'Account successfully deleted';
                        $output=[];
                        $status = 0;
                        exec('sudo systemctl reload httpd php-fpm 2>&1', $output, $status);
                        if ($status) {
                            echo_err('Error code '.$status);
                            echo_output($output);
                        }
                    }
                    $deleted = true;

                    break;
                }
            }
        }

	if (!$deleted) echo_err('Domain not found');
    }
