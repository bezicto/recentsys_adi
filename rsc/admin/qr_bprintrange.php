<?php
    session_start();define('includeExist', true);
    include_once '../includes/access_logged.php';
    include_once '../config.php';
?>

<!DOCTYPE HTML>
<html lang='en'>
<head>
    <meta charset="utf-8">
    <title><?php echo $product_name;?> : Print Material Barcode Range</title>
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
                <strong>Print Materials Barcode Range</strong>
            </div>
            <div class="card-body">
                <form target='_blank' action="qr_bprintpreview_range.php" method="get" enctype="multipart/form-data" onsubmit="var s=document.getElementById('start_range'); var e=document.getElementById('end_range'); if(s && s.value.trim()!=='' && !isNaN(s.value.trim())){ s.value=s.value.trim().padStart(10,'0'); } if(e && e.value.trim()!=='' && !isNaN(e.value.trim())){ e.value=e.value.trim().padStart(10,'0'); }">
                    <div class="form-group">
                        <label class="form-label">Accession Number Range Start:</label>
                        <input type="text" id="start_range" name="start" maxlength="20" placeholder="e.g. 1 or 0000000001" onblur="if(this.value.trim()!=='' && !isNaN(this.value.trim())){this.value=this.value.trim().padStart(10,'0');}" required />
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Accession Number Range End:</label>
                        <input type="text" id="end_range" name="end" maxlength="20" placeholder="e.g. 50 or 0000000050" onblur="if(this.value.trim()!=='' && !isNaN(this.value.trim())){this.value=this.value.trim().padStart(10,'0');}" required />
                    </div>

                    <div class="mt-3">
                        <input type="submit" class="btn btn-primary w-100" name="submitted" value="Display &amp; Print Barcodes" />
                    </div>
                </form>
            </div>
        </div>
        
        <div class="text-center my-3">
            <a class="btn btn-secondary btn-sm" href="../index2.php">&larr; Back to start page</a>
        </div>
        
        <?php include_once '../includes/footerbar.php';?>
    </div>
</body>
</html>
