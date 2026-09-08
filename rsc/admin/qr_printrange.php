<?php
    session_start();define('includeExist', true);
    include_once '../includes/access_super.php';
    include_once '../config.php';
?>

<html lang='en'>
<head>
    <meta charset="utf-8">
    <title><?php echo $product_name;?> : Print Patron QR / Barcode Range</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="../<?php echo $mini_icon_path;?>" rel="icon" type="image/png" />
    <link href="../assets/styles/style.css" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="../assets/fontawesome/css/all.min.css">
</head>

<body>
    <?php include_once '../includes/loggedinfo.php';?>
        
    <div class="app-container">
        <div class="card mw-800 mb-3">
            <div class="card-header">
                <strong>Print Patron Barcode / QR Label Range</strong>
            </div>
            <div class="card-body">
                <form target='_blank' action="qr_membership_range.php" method="get" enctype="multipart/form-data">
                    <div class="form-group">
                        <label class="form-label">Database ID Range Start:</label>
                        <input type="text" name="start" maxlength="20" placeholder="e.g. 1" required />
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Database ID Range End:</label>
                        <input type="text" name="end" maxlength="20" placeholder="e.g. 50" required />
                    </div>

                    <div class="mt-3">
                        <input type="submit" class="btn btn-primary w-100" name="submitted" value="Display &amp; Print Labels" />
                    </div>
                </form>
            </div>
        </div>
        
        <div class="text-center my-3">
            <a class="btn btn-secondary btn-sm" href="../admin/chanuser.php">&larr; Back to user accounts</a>
        </div>
        
        <?php include_once '../includes/footerbar.php';?>
    </div>
</body>
</html>
