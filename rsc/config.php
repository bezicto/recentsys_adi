<?php
    if (isset($_SESSION['config_locality']) && is_file($_SESSION['config_locality'])) {
        include_once $_SESSION['config_locality'];

        //database connection
        try {
            $conn = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
            if (!$conn) {
                die("Please check database connection.");
            }
        } catch (Throwable $e) {
            die("Please check database connection.");
        }

        //version info
        $core_product = "ReCentSYS ADI";
        $version_num = "V8 Build 2";

        //icon and logo paths
        $mini_icon_path = "../".$_SESSION["parent_dir"].$mini_icon;
        $logo_image_path  = "../".$_SESSION["parent_dir"].$logo_image;

        //pdf upload directory
        $pdf_upload_directory = "../".$_SESSION["parent_dir"]."/docs";
        //cover upload directory
        $cover_upload_directory = "../".$_SESSION["parent_dir"]."/albums";
        //blocks directory
        $block_upload_directory = "../".$_SESSION["parent_dir"]."/blocks";
        //avatar upload directory
        $avatar_upload_directory = "../".$_SESSION["parent_dir"]."/avatars";
        //stat cache directory
        $stat_cache_directory = "../".$_SESSION["parent_dir"]."/stats";

    }  else {
        //redirect to index if improper access
        header('Location: '.'../index.php');
        exit;
    }
    //projek ini dihasilkan di Perpustakaan Tuanku Bainun, Universiti Pendidikan Sultan Idris
    //disediakan dan diselenggara oleh Khairul Asyrani Sulaiman melalui Github: github.com/bezicto
    //dan Mohd Hizam Samin yang telah mengenalpasti skema rekod MARC21 minima dan juga maklumbalas berkaitan operasi perpustakaan
    //pahala daripada pengguna sistem ini disedekah kepada ahli keluarga pembangun dan semua yang terlibat dengannya insyaAllah
