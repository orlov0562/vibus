<?php include '_header.php'; ?>

<h3>Indirect services</h3>
<?php
    $output='';
    $status = 0;
    exec('sudo systemctl list-unit-files | grep indirect', $output, $status);
    echo_output($output);
