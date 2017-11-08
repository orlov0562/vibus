<?php include '_header.php';?>

<h3>List</h3>

<table class="table">
<tr>
    <th>Account</th>
    <th>Shell</th>
    <th>Manage</th>
</tr>
<?php

    $accounts = file(getAccountsFilePath());

    foreach($accounts as $account) {
	$account = trim($account);
	$passwdInfo = getPasswdInfo($account);
	if (!$account || preg_match('~^[#]~',$account)) continue;
	echo '<tr>';
	echo '<td>'.htmlspecialchars($account).'</td>';
	echo '<td>'.(!empty($passwdInfo['shell'])?htmlspecialchars($passwdInfo['shell']):'NOT FOUND').'</td>';
	echo '<td><a href="/accounts/delete?id='.md5($account).'" class="btn btn-sm btn-danger" onclick="if (!confirm(\'Are you sure to delete account and all data?\')) return false;">Delete</a></td>';
	echo '</tr>';
    }

?>
</table>
