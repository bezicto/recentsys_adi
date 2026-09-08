<?php
    session_start();
    
    //legacy code for direct path setting
    //_SESSION["config_locality"] = $_SERVER['DOCUMENT_ROOT']."/rc7/site/config.php";
    
    //new code for dynamic path setting
    $_SESSION["parent_dir"] = basename(__DIR__);
    $_SESSION["config_locality"] = dirname(__DIR__) . DIRECTORY_SEPARATOR . $_SESSION["parent_dir"] . DIRECTORY_SEPARATOR . "config.php";

    //redirect to rsc folder, all other multisite will be redirect to here
    header('Location: '.'../rsc/');
