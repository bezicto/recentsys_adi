<?php
//clearing all config_locality session
session_start();
unset($_SESSION['config_locality']);
unset($_SESSION['parent_dir']);

/*
uncomment to enable multi site
listing all directories except 'rsc'
*/
/*
$dirs = array_filter(glob('*'), 'is_dir');
foreach ($dirs as $key => $item) {
    if ($item != 'rsc') {
        echo "<a href='$item'>$item</a><br/>";
    }
}
*/

/*
uncomment to enable single /site
make automatic redirection to a single /site
*/
header("Location: site/");

exit();
