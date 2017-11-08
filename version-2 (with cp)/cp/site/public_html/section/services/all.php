<?php include '_header.php'; ?>

<h3>All services</h3>
<?php
    $output='';
    $status = 0;
    exec('sudo systemctl list-unit-files', $output, $status);
    echo_output($output);
