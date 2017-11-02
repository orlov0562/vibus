<?php

    function cpy($source, $dest){
        if(is_dir($source)) {
            $dir_handle=opendir($source);
            while($file=readdir($dir_handle)){
                if($file!="." && $file!=".."){
                    if(is_dir($source."/".$file)){
                        if(!is_dir($dest."/".$file)){
                            mkdir($dest."/".$file);
                        }
                        cpy($source."/".$file, $dest."/".$file);
                    } else {
                        copy($source."/".$file, $dest."/".$file);
                    }
                }
            }
            closedir($dir_handle);
        } else {
            copy($source, $dest);
        }
    }

    if (count($argv)<3) {
	die('Usage: gen.php USER GROUP DOMAIN'.PHP_EOL);
    }

    $user = trim($argv[1]);
    $group = trim($argv[2]);
    $domain = trim($argv[3]);

    mkdir(dirname(__FILE__).'/'.$user.'/'.$domain, 0755, true);

    system('cp -r template/* "'.$user.'/'.$domain.'"');
    system('chown -R '.$user.':'.$group.' '.$user);
    
    echo 'Directory structure successfully created!'.PHP_EOL;