<!DOCTYPE HTML>
<?php
    session_start();define('includeExist', true);
    include_once '../includes/access_logged.php';
    include_once '../config.php';
    include_once '../includes/functions.php';
    unset($_SESSION['paysearch']);
?>

<html lang='en'>

<head>
    <meta charset="utf-8">
    <title><?php echo $product_name;?> : Payment Search</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="../<?php echo $mini_icon_path;?>" rel="icon" type="image/png" />
    <link href="../assets/styles/style.css" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="../assets/fontawesome/css/all.min.css">
</head>

<body>
    <?php
        if (isset($_POST['pid']) && $_POST['pid'] != '' && patronUsernameToID($_POST['pid']) >= 1) {
            echo "<script type='text/javascript'>location.href='payhistory.php?pid=".patronUsernameToID($_POST['pid'])."';</script>";
            $_SESSION['paysearch'] = "on";
        } elseif (isset($_POST['pid']) && patronUsernameToID($_POST['pid']) == 0) {
            echo "<script>alert('Not a valid ID.');</script>";
        } elseif (isset($_POST['pid']) && $_POST['pid'] == '') {
            echo "<script>alert('Not a valid ID.');</script>";
        }
    
        include_once '../includes/loggedinfo.php';
    ?>
    
    <div class="app-container">
        <div class="card mb-3">
            <div class="card-header">
                <strong>Search Patron for Fines Settlement</strong>
            </div>
            <div class="card-body">
                <form action="paysearch.php" method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label class="form-label">Patron IC Number / ID:</label>
                        <div class="d-flex gap-2">
                            <input type="text" id="pid" name="pid" maxlength="70" value="<?php echo htmlspecialchars($_POST['pid'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required autofocus />
                            <button type="button" class="btn btn-scan btn-scan-pay" onclick="circScanner.openScanner('pid');" title="Scan Patron Barcode / QR with Camera">
                                <i class="fa-solid fa-camera"></i> Scan
                            </button>
                            <input type="button" class="btn btn-secondary" name="clear" value="Clear" onclick="document.getElementById('pid').value='';document.getElementById('pid').focus();">
                        </div>
                    </div>
                    <input type="submit" class="btn btn-primary w-100" name="search" value="Search Patron Fines"/>
                </form>
            </div>
        </div>
        
        <div class="text-center my-3">
            <a class="btn btn-secondary btn-sm" href="../index2.php">&larr; Back to start page</a>
        </div>
        
        <?php include_once '../includes/footerbar.php';?>
    </div>

    <!-- Circulation Barcode Scanner Modal Dialog -->
    <div id="circulationScanModal" class="scanner-modal-backdrop" onclick="if(event.target===this) circScanner.closeScanner();">
        <div class="scanner-modal-container">
            <div class="scanner-modal-header">
                <h5 class="scanner-modal-title"><i class="fa-solid fa-camera text-info"></i> Barcode &amp; QR Scanner</h5>
                <button type="button" class="modal-close-btn" id="circulationScanClose" title="Close Scanner">&times;</button>
            </div>
            <div class="scanner-modal-body">
                <div class="scanner-viewport-wrap">
                    <div id="circulationScanReader"></div>
                </div>
                <div id="circulationScanStatus" class="scanner-status">Initializing camera...</div>
                <div id="circulationScanFeedback" class="scanner-feedback-badge"></div>

                <div class="scanner-controls">
                    <div class="scanner-btn-group">
                        <select id="circulationCameraSelect" class="form-select form-select-sm" style="display:none; max-width: 170px;" aria-label="Select Camera"></select>
                        <button type="button" id="circulationTorchBtn" class="btn btn-secondary btn-sm" style="display:none;">
                            <i class="fa-regular fa-lightbulb"></i> Torch
                        </button>
                    </div>
                    <div class="d-flex align-items-center gap-1">
                        <label for="circulationScanTarget" class="form-label mb-0 small text-muted">Target:</label>
                        <select id="circulationScanTarget" class="form-select form-select-sm" style="width: auto;">
                            <option value="pid">Patron ID</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="scanner-modal-footer">
                <div>
                    <input type="file" id="circulationFileInput" accept="image/*" capture="environment" style="display:none;">
                    <button type="button" id="circulationFileBtn" class="btn btn-secondary btn-sm" title="Capture photo or upload image to scan">
                        <i class="fa-solid fa-image me-1"></i> Snap Photo / Upload
                    </button>
                </div>
                <div class="form-check m-0">
                    <input type="checkbox" id="circulationAutoSubmit" class="form-check-input" checked>
                    <label for="circulationAutoSubmit" class="form-check-label small">Auto-search on scan</label>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/jscripts/html5-qrcode.min.js"></script>
    <script src="../assets/jscripts/circulation_scanner.js"></script>
    <script>
        const circScanner = new CirculationScanner({
            pageType: 'paysearch',
            defaultTarget: 'pid',
            autoSubmit: true
        });
    </script>
</body>
</html>
