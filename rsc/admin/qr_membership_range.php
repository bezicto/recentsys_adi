<?php
    session_start();
    define('includeExist', true);
    include_once '../includes/access_super.php';
    include_once '../config.php';

    $start_id = isset($_GET["start"]) && is_numeric($_GET["start"]) ? (int)$_GET['start'] : 0;
    $end_id = isset($_GET["end"]) && is_numeric($_GET["end"]) ? (int)$_GET['end'] : 0;

    if ($start_id > $end_id) {
        $temp = $start_id;
        $start_id = $end_id;
        $end_id = $temp;
    }

    $patrons = [];
    if ($start_id >= 0 && $end_id > 0) {
        $stmt = mysqli_prepare($GLOBALS["conn"], "SELECT id, username, name FROM eg_auth WHERE id BETWEEN ? AND ? ORDER BY id ASC");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ii", $start_id, $end_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            while ($row = mysqli_fetch_assoc($result)) {
                $patrons[] = [
                    'id'       => (int)($row['id'] ?? 0),
                    'username' => $row['username'] ?? '',
                    'name'     => $row['name'] ?? ''
                ];
            }
            mysqli_stmt_close($stmt);
        }
    }
    $total_count = count($patrons);
?>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title><?php echo $product_name;?> : Print Preview - Membership QR Labels</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="../<?php echo $mini_icon_path;?>" rel="icon" type="image/png" />
    <link href="../assets/styles/style.css" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="../assets/fontawesome/css/all.min.css">
    <script src="../assets/jscripts/jquery_1_11_1.min.js" type="text/javascript"></script>
    <script src="../assets/jscripts/qrcode.js" type="text/javascript"></script>
    <script src="../assets/jscripts/jquery.qrcode.js" type="text/javascript"></script>
</head>

<body class="preview-body">
    <!-- Top Action Toolbar (Hidden during actual print) -->
    <header class="preview-toolbar no-print">
        <div class="preview-toolbar-title">
            <i class="fa fa-qrcode" style="color: #60a5fa; font-size: 1.25rem;"></i>
            <span>Membership QR Labels Preview</span>
            <span class="preview-badge">
                ID #<?php echo htmlspecialchars($start_id);?> &ndash; #<?php echo htmlspecialchars($end_id);?>
            </span>
            <span class="preview-badge" style="background: rgba(37, 99, 235, 0.4);">
                <?php echo $total_count;?> label<?php echo $total_count === 1 ? '' : 's';?>
            </span>
        </div>

        <div class="preview-toolbar-info">
            <i class="fa fa-circle-info"></i>
            <span>A4 Portrait Sheet</span>
        </div>

        <div class="preview-toolbar-actions">
            <button type="button" class="btn btn-primary btn-sm" onclick="window.print()" title="Print now (Ctrl+P / Cmd+P)">
                <i class="fa fa-print"></i> Print Labels
            </button>
            <button type="button" class="btn btn-secondary btn-sm" onclick="window.close()" title="Close this preview">
                <i class="fa fa-xmark"></i> Close
            </button>
        </div>
    </header>

    <!-- Preview Canvas & A4 Sheet -->
    <div class="preview-container">
        <div class="print-preview-sheet">
            <?php if ($total_count > 0): ?>
                <div class="membership-qr-grid">
                    <?php foreach ($patrons as $idx => $patron):
                        $pid = (int)$patron['id'];
                        $username_js = addslashes($patron['username']);
                        $name = htmlspecialchars($patron['name']);
                        $canvas_id = "qrcodeCanvas_" . $pid . "_" . $idx;
                    ?>
                        <div class="membership-qr-card">
                            <div style="width: 100%; display: flex; justify-content: center; margin-bottom: 6px;">
                                <div class="membership-qr-canvas" id="<?php echo $canvas_id;?>"></div>
                            </div>
                            <div>
                                <div class="membership-qr-name"><?php echo $name;?></div>
                                <?php if (!empty($licensed_info)): ?>
                                    <div class="membership-qr-org"><?php echo htmlspecialchars($licensed_info);?></div>
                                <?php endif; ?>
                                <div class="membership-qr-id">#<?php echo $pid;?></div>
                            </div>
                            <script>
                                $(function() {
                                    $("#<?php echo $canvas_id;?>").qrcode({
                                        text: "<?php echo $username_js;?>",
                                        width: 75,
                                        height: 75
                                    });
                                    $("#<?php echo $canvas_id;?> canvas").css({ "margin-left": "auto", "margin-right": "auto", "display": "block" });
                                });
                            </script>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center p-4">
                    <div style="font-size: 2.5rem; color: #94a3b8; margin-bottom: 0.5rem;">
                        <i class="fa fa-triangle-exclamation"></i>
                    </div>
                    <h4>No Patron Accounts Found</h4>
                    <p class="text-muted">
                        No user account records were found for Database ID range 
                        <strong>#<?php echo htmlspecialchars($start_id);?></strong> to 
                        <strong>#<?php echo htmlspecialchars($end_id);?></strong>.
                    </p>
                    <div class="no-print mt-3">
                        <a href="qr_printrange.php" class="btn btn-secondary btn-sm">&larr; Back to range selection</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
