<?php include '_header.php'; ?>

<h3>Static services</h3>
<?php
    $output='';
    $status = 0;
    exec('sudo systemctl list-unit-files | grep static', $output, $status);
    echo_output($output);
