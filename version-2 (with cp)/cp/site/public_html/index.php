<?php
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    include 'inc/functions.php';
?>
<!doctype html>
<html lang="en">
<head>
    <title>Vibus Control Panel</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.2/css/bootstrap.min.css" integrity="sha384-PsH8R72JQ3SOdhVi3uxftmaW6Vc51MKb0q5P2rRUpPvrszuE4W1povHYgTpBfshb" crossorigin="anonymous">
</head>
<body>

    <div class="container">
	<div class="row" style="background-color:#000;">
	    <div class="col-12">
		<h1 style="color:#FAFAFA;margin:15px 0;">Vibus Control Panel</h1>
	    </div>
	</div>
	<div class="row">
	    <div class="col-3" style="background-color:#F0F0F0;">
		<?php include 'section/_menu.php'?>
	    </div>
	    <div class="col-8">
	    <div style="margin:15px 0;">
	    <?php
		$incSection = 'dashboard/index';
		if (!empty($_REQUEST['section'])) {
		    $section = $_REQUEST['section'];
		    $section = preg_replace('~[^-a-z/]~','',$section);
		    if ($section) {
			$filePath = dirname(__FILE__).'/section/'.$section.'.php';
			if (file_exists($filePath)) {
			    $incSection=$section;
			} else {
			    $incSection='error/not-found';
			}
		    }
		}
		include dirname(__FILE__).'/section/'.$incSection.'.php';
	    ?>
	    </div>
	    </div>
	</div>
    </div>

    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.3/umd/popper.min.js" integrity="sha384-vFJXuSJphROIrBnz7yo7oB41mKfc8JzQZiCq4NCceLEaO4IHwicKwpJf9c9IpFgh" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.2/js/bootstrap.min.js" integrity="sha384-alpBpkh1PFOepccYVYDB4do5UnbKysX5WZXm3XxPqe5iKTfUKjNkCk9SaVuEZflJ" crossorigin="anonymous"></script>
</body>
</html>