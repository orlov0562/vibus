<?php include '_header.php'; ?>

<h3>Disabled services</h3>
<?php
    $output='';
    $status = 0;
    exec('sudo systemctl list-unit-files | grep disabled', $output, $status);
    echo_output($output);
