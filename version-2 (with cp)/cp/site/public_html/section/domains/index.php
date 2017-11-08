<?php include '_header.php';?>

<h3>List</h3>

<table class="table">
<tr>
    <th>Account</th>
    <th>Domain</th>
    <th>Manage</th>
</tr>
<?php

    $sorted = getSortedDomains();


    foreach($sorted as $account=>$domains) {
	foreach($domains as $domain) {
	echo '<tr>';
	echo '<td>'.esc($account).'</td>';
	echo '<td>'.esc($domain).'</td>';
	echo '<td><a href="/domains/delete?id='.md5($account.$domain).'" class="btn btn-sm btn-danger" onclick="if (!confirm(\'Are you sure to delete domain and all data?\')) return false;">Delete</a></td>';
	echo '</tr>';
	}
    }

?>
</table>