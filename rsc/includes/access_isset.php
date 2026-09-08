<?php
if (empty($_SESSION['username'])) {
    $currentdir = substr(getcwd(), -5);
    $cssPath = ($currentdir == 'admin' || $currentdir == 'ports' || $currentdir == 'atics' || $currentdir == 'wsers' || $currentdir == 'helps') ? '../assets/styles/style.css' : './assets/styles/style.css';
    echo "<!DOCTYPE HTML><html lang='en'><head><link href='$cssPath' rel='stylesheet' type='text/css'></head><body><div class='auth-card'><span class='badge badge-danger mb-2'>ACCESS RESTRICTED</span><p class='text-muted'>You do not have permission to access this page. Please contact the administrator.</p></div></body></html>";
    exit;
}
