<?php

ini_set('display_errors', 0);ini_set('display_startup_errors', 0);error_reporting(0);
putenv("TZ=Asia/Kuala_Lumpur");//set time zone
date_default_timezone_set('Asia/Kuala_Lumpur');//set time zone

//database connection settings
//!! to clear when moving into packaging for client use !!
$dbhost = "localhost";
$dbname = "rcdb";
$dbuser = "root";
$dbpass = "upsi123pustaka";

//aes key for the entire token validation
$tokenaeskeysys = "ASD234234SDSD232#$#4SDS";

//aeskey for password encryption
//it is best to change when only admin user exist (during first time install)
//login using 'admin' with default password and then change this key.
//after that, make admin user change password to re-encrypt with new key.
//logout and test if login works after that
$ppaeskeysys = "3ncypt3Dp445c0d3a3sk3y";

//branding
$product_name = "My Library";
$licensed_info = "Perpustakaan Tuanku Bainun";
$about_text = "Welcome to our library management system.";
$staff_contact_email = "admin@testxyz.my";

//how many times a user can renew an item for loaning.
$max_renew_count = 2;

//circulation barcode identifier mode: 'accessnum' (default) or 'isbnissn'
//'accessnum' = standard accession number barcode on item copies
//'isbnissn'  = publisher-printed ISBN / ISSN barcode
$circulation_mode = "accessnum";

//currency settings
$currency_SHORT = "MYR";

//icon and logo path must be in parent 'site' directory
$mini_icon = "/images/company_www-icon.png";
$logo_image = "/images/company.png";

//MARC tag settings
//version 1.0.20260201.0922

    //39isbnissn 020/022
    $tag_020 = "ISBN (020)";
        $tag_020ph = "ISBN codes (for books etc.)";
    $tag_022 = "ISSN (022)";
        $tag_022ph = "ISSN codes (for serials, journals, periodicals)";

    //39languange 041
    $tag_041 = "Languange (041)";
        $tag_041ph = "Language Code";
        $tag_041_inputtype = "select";
        $tag_041_selectable_default = "zsm";
        $tag_041_selectable = "zsm|eng|chi|tam|ara|oth";//values must be separated by |
        $tag_041_selectable_def = "Bahasa Malaysia|English|Chinese|Tamil|Arabic|Others";//values must be separated by |

    //38localcallnum 090
    $tag_090 = "Call Num (090)";
        $tag_090aph = "Classification number";
        $tag_090bph = "Local Cutter number";

    //38author 100
    $tag_100 = "Author (100)";
        $tag_100aph = "Personal name";
        $tag_100dph = "Date associated with name";

    //38title 245
    $tag_245 = "Title (245)";
        $tag_245aph = "Title statement";
        $tag_245bph = "Remainder of title";
        $tag_245cph = "Statement of responsibility";

    //39edition 250
    $tag_250 = "Edition (250)";
        $tag_250aph = "Edition statement";

    //39publication 264
    $tag_264 = "Publication (264)";
        $tag_264aph = "Place of production, publication, distribution, manufacture";
        $tag_264bph = "Name of producer, publisher, distributor, manufacturer";
        $tag_264cph = "Date of production, publication, distribution, manufacture or copyright notice";

    //39physicaldesc 300
    $tag_300 = "Physical Description (300)";
        $tag_300aph = "Extent";
        $tag_300bph = "Other physical details";
        $tag_300cph = "Dimensions";
        $tag_300eph = "Accompanying material";

    //39series 490
    $tag_490 = "Series (490)";
        $tag_490aph = "Series statement";
        $tag_490vph = "Volume number/sequential designation";

    //39notes 500
    $tag_500 = "Notes (500)";
        $tag_500aph = "General note";

    //39fcnotes 505
    $tag_505 = "Formatted Contents Note (505)";
        $tag_505aph = "Formatted Contents Note";

    //38source 710
    $tag_710 = "Corporate Name (710)";
        $tag_710aph = "Corporate name or jurisdiction name as entry element";
        $tag_710bph = "Subordinate unit";
        $tag_710eph = "Relator term";

    //38location 852
    $tag_852 = "Location (852)";
        $tag_852aph = "Location";
        $tag_852bph = "Sublocation or collection";
        $tag_852cph = "Shelving location";

    //38link 856
    $tag_856 = "HTTP Link (856)";
        $tag_856uph = "Uniform Resource Identifier (URI)";
    
    // File size limits for upload (in Megabytes - MB)
    $pdf_upload_maxsize = 10; // 10MB
    $cover_upload_maxsize = 1; // 1MB
    $avatar_upload_maxsize = 2; // 2MB

    //Bibliographic API Settings (for automated record metadata fetching in reg.php)
    $enable_googlebookapi = true;
    $googlebook_api_key = "AIzaSyCOQIUFQuKz803TPY-IfEKrat2CH2O8Who"; // Optional: Google Books API Key (recommended to avoid unauthenticated HTTP 429 quota limits)
    $enable_openlibraryapi = true;

    //Polaris Integration Settings
    $enable_pnm_polaris_integration = true;
    $pnm_polaris_access_key = "5df53103-30b5-4d4b-b58f-f4ffd42b2011";
    $pnm_polaris_access_id = "UPSI";
    $pnm_polaris_base_url = "https://pnmpolarisapps.pnm.gov.my/PAPIService/REST/public/v1/1033/1/1";
