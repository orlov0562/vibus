<?php include '_header.php';?>

<h3>Add account</h3>

<form action="/accounts/create?action=create" method="post">

    <label>Account name</label>
    <input type="text" name="account_name" value="<?=!empty($_POST['account_name'])?esc($_POST['account_name']):''?>"><br>

    <label>Password</label>
    <input type="text" name="account_pass" value="<?=!empty($_POST['account_pass'])?esc($_POST['account_pass']):''?>"><br>

    <input type="submit" value="Create">
</form>

<?php

    if (!empty($_GET['action']) && $_GET['action']=='create') {
        echo '<hr>';
        $errors = false;

        if (empty($_POST['account_name'])) {
            echo_err('Empty account name');
            $errors = true;
        }

        if (empty($_POST['account_pass']) || trim($_POST['account_pass'])) {
            $_POST['account_pass'] = md5(rand(0,1000).time().rand(0,1000).'%$%$%$');
        } elseif (!preg_match('~^[a-z0-9]+$~i', $_POST['account_pass'])) {
            echo_err('Account password should contains only a-zA-Z0-9');
            $errors = true;
        }

        if (!$errors) {
            $account = trim($_POST['account_name']);

            if (preg_match('~^[a-z0-9]+$~', $account)) {
                    if (!getPasswdInfo($account)) {

                    $output=[];
                    $status = 0;
                    exec('sudo useradd '.$account, $output, $status);
                    if ($status) {
                        echo_err('Error code '.$status);
                        echo_output($output);
                    }

                    if (!$status) {
                        $output=[];
                        $status = 0;
                        exec('sudo usermod --password '.$_POST['account_pass'].' '.$account.' 2>&1', $output, $status);
                        if ($status) {
                            echo_err('Error code '.$status);
                            echo_output($output);
                        }
                    }

                    if (!$status) {
                        $output=[];
                        $status = 0;
                        exec('sudo usermod -aG '.$account.' apache 2>&1', $output, $status);
                        if ($status) {
                            echo_err('Error code '.$status);
                            echo_output($output);
                        }
                    }

                    if (!$status) {
                        $output=[];
                        $status = 0;
                        exec('sudo mkdir -p '.getSiteDirPath().'/'.$account.' 2>&1', $output, $status);
                        if ($status) {
                            echo_err('Error code '.$status);
                            echo_output($output);
                        }
                    }

                    if (!$status) {
                        $output=[];
                        $status = 0;
                        exec('sudo chown vibus:'.$account.' '.getSiteDirPath().'/'.$account.' 2>&1', $output, $status);
                        if ($status) {
                            echo_err('Error code '.$status);
                            echo_output($output);
                        }
                    }

                    if (!$status) {
                        $output=[];
                        $status = 0;
                        exec('sudo chmod 0750 '.getSiteDirPath().'/'.$account.' 2>&1', $output, $status);
                        if ($status) {
                            echo_err('Error code '.$status);
                            echo_output($output);
                        }
                    }

                    if (!$status) {
                        file_put_contents(getAccountsFilePath(), $account.PHP_EOL,FILE_APPEND);
                        echo 'Account successfully created';
                    }

                } else {
                        echo_err('Account already exists');
                }
            } else {
                echo_err('Account name contains forbidden chars (allowed only a-z0-9)');
            }
        }
    }

?>