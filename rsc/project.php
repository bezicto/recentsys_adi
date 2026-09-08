<!DOCTYPE HTML>
<?php
    session_start();define('includeExist', true);
    include_once 'config.php';
?>

<html lang='en'>

<head>
    <meta charset="utf-8">
    <title><?php echo $product_name;?> : Project Information</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="./assets/styles/style.css" rel="stylesheet" type="text/css">
    <link href="<?php echo $mini_icon_path;?>" rel="icon" type="image/png" />
    <link rel="stylesheet" href="./assets/fontawesome/css/all.min.css">
</head>

<body>
    <?php include_once 'includes/loggedinfo.php';?>
    
    <div class="app-container">
        <div class="card mw-800 text-center mb-3">
            <div class="card-header">
                <strong>Project Information</strong>
            </div>
            <div class="card-body">
                <img src="./assets/images/company.png" style="margin-top:20px;width:340px;" alt="Powered by <?= $core_product;?>" class="app-logo mb-3" />
                <p class="lead-text mb-4">
                    <?= $core_product;?> was developed to cater the need of simple library management in use for schools or other small to mid size organizations to organize and handle their collections.
                    It was developed from ground up at Tuanku Bainun Library and has been used in many schools that took part in CSR and outreach programs in reorganizing their library collections.
                    <br/><br/><?= $core_product;?> also complies with MARC 21 Core Level standards. All bibliographic record registration workflows comply with ISO 2709 exchange formats, ensuring high-fidelity metadata capture, standardized indicator handling, and frictionless interoperability with national union catalogs and global bibliographic utilities.
                    <br/><br/>This product also has integration with Google Books API, Open Library API and Perpustakaan Negara Malaysia Polaris API. However, some of these features require access key and ID from the respective service providers.
                </p>
                
                <hr class="my-4" />
                
                <h4 class="mb-3">Development &amp; Compliance</h4>
                <img src='./assets/images/logo_upsi.png' alt="Organization Logo" class="mb-3" style="max-height:100px;" /><br/>
                <p class="mb-1"><strong>Project Lead and Developer:</strong><br/> Khairul Asyrani bin Sulaiman<br/><br/>
                <strong>Support Database Layout / Cataloguing Compliance:</strong><br/>Mohd. Hizam bin Samin</p>
            </div>
        </div>

        <div class="text-center my-3">
            <a class="btn btn-secondary btn-sm" href="<?php echo $_SESSION['ref'] ?? 'opac.php';?>">&larr; Back</a>
        </div>

        <?php include_once 'includes/footerbar.php';?>
    </div>
</body>
</html>
