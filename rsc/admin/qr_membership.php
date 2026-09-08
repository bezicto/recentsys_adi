<?php
    session_start();
    define('includeExist', true);
    include_once '../includes/access_super.php';
    include_once '../config.php';

    $get_id = isset($_GET["pid"]) && is_numeric($_GET["pid"]) ? (int)$_GET["pid"] : 0;
    $username3 = '';
    $name3 = '';

    if ($get_id > 0) {
        $stmt3 = mysqli_prepare($GLOBALS["conn"], "SELECT username, name FROM eg_auth WHERE id = ?");
        if ($stmt3) {
            mysqli_stmt_bind_param($stmt3, "i", $get_id);
            mysqli_stmt_execute($stmt3);
            $result3 = mysqli_stmt_get_result($stmt3);
            if ($myrow2 = mysqli_fetch_array($result3)) {
                $username3 = $myrow2["username"] ?? '';
                $name3 = $myrow2["name"] ?? '';
            }
            mysqli_stmt_close($stmt3);
        }
    }
    $has_patron = !empty($username3);
?>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title><?php echo $product_name;?> : Print Preview - Membership QR Label</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="../<?php echo $mini_icon_path;?>" rel="icon" type="image/png" />
    <link href="../assets/styles/style.css" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="../assets/fontawesome/css/all.min.css">
    <script src="../assets/jscripts/jquery_1_11_1.min.js" type="text/javascript"></script>
    <script src="../assets/jscripts/qrcode.js" type="text/javascript"></script>
    <script src="../assets/jscripts/jquery.qrcode.js" type="text/javascript"></script>
    <script src="../assets/jscripts/jquery-barcode.min.js" type="text/javascript"></script>
</head>

<body class="preview-body">
    <!-- Top Action Toolbar (Hidden during actual print) -->
    <header class="preview-toolbar no-print">
        <div class="preview-toolbar-title">
            <i class="fa fa-qrcode" style="color: #60a5fa; font-size: 1.25rem;"></i>
            <span>Membership QR Label Preview</span>
            <?php if ($has_patron): ?>
                <span class="preview-badge">ID #<?php echo htmlspecialchars($get_id);?> &bull; <?php echo htmlspecialchars($name3);?></span>
            <?php endif; ?>
        </div>

        <div class="preview-toolbar-info">
            <i class="fa fa-circle-info"></i>
            <span>A4 Portrait Sheet</span>
        </div>

        <div class="preview-toolbar-actions">
            <?php if ($has_patron): ?>
                <button type="button" class="btn btn-primary btn-sm" onclick="window.print()" title="Print now (Ctrl+P / Cmd+P)">
                    <i class="fa fa-print"></i> Print Label
                </button>
            <?php endif; ?>
            <button type="button" class="btn btn-secondary btn-sm" onclick="window.close()" title="Close this preview">
                <i class="fa fa-xmark"></i> Close
            </button>
        </div>
    </header>

    <!-- Preview Canvas & A4 Sheet -->
    <div class="preview-container">
        <div class="print-preview-sheet">
            <?php if ($has_patron): 
                $username_js = addslashes($username3);
            ?>
                <div style="display: flex; justify-content: flex-start;">
                    <div class="membership-single-card">
                        <div style="width: 100%; display: flex; justify-content: center; margin-bottom: 8px;">
                            <div id="qrcodeCanvas"></div>
                        </div>
                        <div style="width: 100%; display: flex; justify-content: center; margin-bottom: 8px;">
                            <div id="codeCanvas"></div>
                        </div>
                        <div style="font-weight: 700; font-size: 12px; line-height: 1.25; margin-bottom: 2px;"><?php echo htmlspecialchars($name3);?></div>
                        <?php if (!empty($licensed_info)): ?>
                            <div style="font-size: 9.5px; color: #555555; line-height: 1.15;"><?php echo htmlspecialchars($licensed_info);?></div>
                        <?php endif; ?>
                        <div style="font-size: 9.5px; color: #777777; font-family: monospace; margin-top: 4px;">#<?php echo $get_id;?></div>
                    </div>
                </div>
                <script>
                    $(function() {
                        $("#qrcodeCanvas").qrcode({
                            text: "<?php echo $username_js;?>",
                            width: 80,
                            height: 80
                        });
                        $("#codeCanvas").barcode("<?php echo $username_js;?>", "code128", {
                            barWidth: 1,
                            barHeight: 28,
                            fontSize: 10,
                            showHRI: true,
                            output: "svg"
                        });
                        $("#codeCanvas, #qrcodeCanvas").css({ "margin-left": "auto", "margin-right": "auto" });
                        $("#codeCanvas img, #qrcodeCanvas canvas").css({ "margin-left": "auto", "margin-right": "auto", "display": "block" });
                    });
                </script>
            <?php else: ?>
                <div class="text-center p-4">
                    <div style="font-size: 2.5rem; color: #94a3b8; margin-bottom: 0.5rem;">
                        <i class="fa fa-triangle-exclamation"></i>
                    </div>
                    <h4>Patron Account Not Found</h4>
                    <p class="text-muted">
                        No user account was found with Database ID <strong>#<?php echo htmlspecialchars($get_id);?></strong>.
                    </p>
                    <div class="no-print mt-3">
                        <a href="chanuser.php" class="btn btn-secondary btn-sm">&larr; Back to user accounts</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
