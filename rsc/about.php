<!DOCTYPE HTML>
<?php
    session_start();define('includeExist', true);
    include_once 'config.php';
?>

<html lang='en'>

<head>
    <meta charset="utf-8">
    <title><?php echo $product_name;?> : About</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="<?php echo $mini_icon_path;?>" rel="icon" type="image/png" />
    <link href="./assets/styles/style.css" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="./assets/fontawesome/css/all.min.css">
</head>

<body>
    <?php include_once 'includes/loggedinfo.php';?>
    
    <div class="app-container">
        <div class="card mw-800 text-center mb-3">
            <div class="card-header">
                <strong>About <?php echo $product_name;?></strong>
            </div>
            <div class="card-body">
                <img src="<?php echo $logo_image_path;?>" alt="Powered by <?= $core_product;?>" class="app-logo mb-3" style="margin-top:20px;width:340px;"/>
                <div class="lead-text mb-3">
                    <?php echo $about_text;?>
                </div>
            </div>
        </div>

        <div class="text-center my-3">
            <a class="btn btn-secondary btn-sm" href="<?php echo $_SESSION['ref'] ?? 'opac.php';?>">&larr; Back</a>
        </div>

        <?php include_once 'includes/footerbar.php';?>
    </div>
</body>
</html>
