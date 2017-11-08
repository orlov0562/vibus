<?php include '_header.php'; ?>

<h3>Enabled services</h3>
<?php
    $output='';
    $status = 0;
    exec('sudo systemctl list-unit-files | grep enabled', $output, $status);
    echo_output($output);
