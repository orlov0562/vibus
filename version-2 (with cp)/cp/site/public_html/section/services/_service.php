<?php

if (!empty($service)) {

    echo '
	<div style="margin-bottom:15px;">
    	    <a href="?action=start" class="btn btn-sm btn-primary">Start</a>
	    <a href="?action=stop" class="btn btn-sm btn-danger">Stop</a>
	    <a href="?action=restart" class="btn btn-sm btn-success">Restart</a>
	    <a href="?" class="btn btn-sm btn-secondary">Refresh</a>
	</div>
    ';

    if (!empty($_GET['action']) && in_array($_GET['action'],['start','stop','restart'])) {
        $output=[];
        $status = 0;
        exec('sudo systemctl '.$_GET['action'].' '.$service.' 2>&1', $output, $status);
        echo_output($output);
    }

    echo '<h3>Status</h3>';
    echo '<div>'.getStatusForService($service).'</div>';

    $output=[];
    $status = 0;
    exec('sudo systemctl status '.$service.' 2>&1', $output, $status);
    echo_output($output);

    echo '<h3>Journal</h3>';
    $output=[];
    $status = 0;
    exec('sudo journalctl -u '.$service.' -e --no-pager 2>&1', $output, $status);
    echo_output(array_reverse($output));
}