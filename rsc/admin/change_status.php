<?php
    session_start();define('includeExist', true);
    include_once '../includes/access_logged.php';
    include_once '../config.php';
    include_once '../includes/token_validate.php';
?>
<!DOCTYPE HTML>
<html lang='en'>

<head>
    <meta charset="utf-8">
    <title><?php echo htmlspecialchars($product_name, ENT_QUOTES, 'UTF-8');?> : Edit Copy</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="../<?php echo htmlspecialchars($mini_icon_path, ENT_QUOTES, 'UTF-8');?>" rel="icon" type="image/png" />
    <link href="../assets/styles/style.css" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="../assets/fontawesome/css/all.min.css">
    <script>
        window.onunload = refreshParent;
        function refreshParent() {
            try {
                if (window.opener && !window.opener.closed) {
                    window.opener.location.reload();
                }
            } catch(e) {}
            try {
                if (window.parent && window.parent !== window && window.parent.closeAppModal) {
                    window.parent.location.reload();
                }
            } catch(e) {}
        }
    </script>
</head>

<body>
    <div class="container-narrow mt-3">
        <?php
            if (isset($_POST["submitted"]) && ($_POST["submitted"] === 'Change' || $_POST["submitted"] === 'Save Changes') && isset($_POST["cid"]) && is_numeric($_POST["cid"]) && $proceedAfterToken) {
                $cid = (int)$_POST["cid"];
                $status = $_POST["status"] ?? 'AVAILABLE';
                $volume = mb_substr(trim($_POST["volume"] ?? ''), 0, 50);
                $issue = mb_substr(trim($_POST["issue"] ?? ''), 0, 50);
                $year = mb_substr(trim($_POST["year"] ?? ''), 0, 10);
                $is_reference = (isset($_POST["is_reference"]) && $_POST["is_reference"] === 'YES') ? 'YES' : 'NO';
                $invoice_a = mb_substr(trim($_POST["invoice_a"] ?? ''), 0, 70);
                $invoice_b = mb_substr(trim($_POST["invoice_b"] ?? ''), 0, 70);
                $invoice_c = mb_substr(trim($_POST["invoice_c"] ?? ''), 0, 70);
                $lastchange = time();

                $stmt = mysqli_prepare($GLOBALS["conn"], "UPDATE eg_item_copies SET `39status`=?, `39volume`=?, `39issue`=?, `39year`=?, `39is_reference`=?, `39invoice_a`=?, `39invoice_b`=?, `39invoice_c`=?, `39lastchange`=? WHERE id=?");
                mysqli_stmt_bind_param($stmt, "ssssssssii", $status, $volume, $issue, $year, $is_reference, $invoice_a, $invoice_b, $invoice_c, $lastchange, $cid);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
                echo "<div class='alert alert-success'><i class='fa-solid fa-check-circle me-1'></i> Copy details updated successfully!</div>";
                echo "<script>setTimeout(function() { if (window.parent && window.parent.closeAppModal) { window.parent.closeAppModal(true); } else if (window.opener) { window.opener.location.reload(); window.close(); } }, 600);</script>";
            }
            
            $cid_param = isset($_REQUEST["cid"]) && is_numeric($_REQUEST["cid"]) ? (int)$_REQUEST["cid"] : 0;
            $statusS = 'AVAILABLE';
            $volumeS = '';
            $issueS = '';
            $yearS = '';
            $is_refS = 'NO';
            $accessnumS = '';
            $invoice_aS = '';
            $invoice_bS = '';
            $invoice_cS = '';

            if ($cid_param > 0) {
                $stmt = mysqli_prepare($GLOBALS["conn"], "SELECT `39accessnum`, `39status`, `39volume`, `39issue`, `39year`, `39is_reference`, `39invoice_a`, `39invoice_b`, `39invoice_c` FROM eg_item_copies WHERE id=?");
                mysqli_stmt_bind_param($stmt, "i", $cid_param);
                mysqli_stmt_execute($stmt);
                $resultS = mysqli_stmt_get_result($stmt);
                if ($resultS && $myrowS = mysqli_fetch_assoc($resultS)) {
                    $accessnumS = $myrowS["39accessnum"] ?? '';
                    $statusS = $myrowS["39status"] ?? 'AVAILABLE';
                    $volumeS = $myrowS["39volume"] ?? '';
                    $issueS = $myrowS["39issue"] ?? '';
                    $yearS = $myrowS["39year"] ?? '';
                    $is_refS = $myrowS["39is_reference"] ?? 'NO';
                    $invoice_aS = $myrowS["39invoice_a"] ?? '';
                    $invoice_bS = $myrowS["39invoice_b"] ?? '';
                    $invoice_cS = $myrowS["39invoice_c"] ?? '';
                }
                mysqli_stmt_close($stmt);
            }
            $csrfToken = htmlspecialchars($_SESSION['token'] ?? '', ENT_QUOTES, 'UTF-8');
        ?>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong><i class="fa-solid fa-pen-to-square me-1"></i> Edit Copy Details</strong>
                <span class="badge badge-primary font-monospace"><i class="fa-solid fa-barcode me-1"></i><?php echo htmlspecialchars($accessnumS, ENT_QUOTES, 'UTF-8'); ?></span>
            </div>
            <div class="card-body">
                <form action="change_status.php" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="token" value="<?php echo $csrfToken; ?>" />
                    <input type="hidden" name="cid" value="<?php echo $cid_param;?>" />
                    
                    <div class="form-group mb-3">
                        <label class="form-label small text-muted">Accession Number / Barcode (Locked):</label>
                        <input type="text" class="form-control font-monospace" value="<?php echo htmlspecialchars($accessnumS, ENT_QUOTES, 'UTF-8'); ?>" disabled readonly />
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label">Material Availability Status:</label>
                        <select name="status">
                            <option value="AVAILABLE" <?php if ($statusS == 'AVAILABLE') {echo "selected";}?>>AVAILABLE (Available for use)</option>
                            <option value="REFERENCE" <?php if ($statusS == 'REFERENCE') {echo "selected";}?>>REFERENCE (In-Library Use Only)</option>
                            <option value="CIRCULATED" <?php if ($statusS == 'CIRCULATED') {echo "selected";}?>>CIRCULATED (On Loan)</option>
                            <option value="RESERVED" <?php if ($statusS == 'RESERVED') {echo "selected";}?>>RESERVED</option>
                            <option value="ONORDER" <?php if ($statusS == 'ONORDER') {echo "selected";}?>>ON ORDER</option>
                            <option value="FINALPROCESSING" <?php if ($statusS == 'FINALPROCESSING') {echo "selected";}?>>FINAL PROCESSING</option>
                            <option value="CLOSED ACCESS" <?php if ($statusS == 'CLOSED ACCESS') {echo "selected";}?>>CLOSED ACCESS</option>
                            <option value="DAMAGED" <?php if ($statusS == 'DAMAGED') {echo "selected";}?>>DAMAGED</option>
                            <option value="BINDING" <?php if ($statusS == 'BINDING') {echo "selected";}?>>BINDING</option>
                            <option value="LOST" <?php if ($statusS == 'LOST') {echo "selected";}?>>LOST</option>
                            <option value="WEEDED" <?php if ($statusS == 'WEEDED') {echo "selected";}?>>WEEDED</option>
                            <option value="ONHOLD" <?php if ($statusS == 'ONHOLD') {echo "selected";}?>>ON HOLD</option>
                        </select>
                    </div>

                    <!-- Serial / Volume / Issue Designation -->
                    <div class="card p-3 mb-3 bg-light border">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <strong><i class="fa-solid fa-layer-group text-primary me-1"></i> Volume &amp; Issue Details (Serials / Sets)</strong>
                        </div>
                        <div class="row g-2">
                            <div class="col-4 form-group mb-2">
                                <label class="form-label small">Volume / Jilid:</label>
                                <input type="text" name="volume" maxlength="50" value="<?php echo htmlspecialchars($volumeS, ENT_QUOTES, 'UTF-8'); ?>" placeholder="e.g. Vol. 45" />
                            </div>
                            <div class="col-4 form-group mb-2">
                                <label class="form-label small">Issue / Month:</label>
                                <input type="text" name="issue" maxlength="50" value="<?php echo htmlspecialchars($issueS, ENT_QUOTES, 'UTF-8'); ?>" placeholder="e.g. No. 3" />
                            </div>
                            <div class="col-4 form-group mb-2">
                                <label class="form-label small">Year:</label>
                                <input type="text" name="year" maxlength="10" value="<?php echo htmlspecialchars($yearS, ENT_QUOTES, 'UTF-8'); ?>" placeholder="e.g. 2026" />
                            </div>
                        </div>
                        <div class="form-group mt-2">
                            <label class="form-label small">Loan Restriction:</label>
                            <select name="is_reference">
                                <option value="NO" <?php if ($is_refS !== 'YES') echo 'selected'; ?>>Circulating (Standard Loan Allowed)</option>
                                <option value="YES" <?php if ($is_refS === 'YES') echo 'selected'; ?>>Reference Only / Current Issue (Non-circulating)</option>
                            </select>
                            <small class="text-muted d-block mt-1">Set to "Circulating" when this issue becomes a back issue and can be borrowed.</small>
                        </div>
                    </div>

                    <!-- Acquisition & Invoicing Info -->
                    <div class="card p-3 mb-3 bg-light border">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <strong><i class="fa-solid fa-receipt text-primary me-1"></i> Acquisition &amp; Invoicing Info</strong>
                        </div>
                        <div class="form-group mb-2">
                            <label class="form-label small">Price per unit (<?php echo htmlspecialchars($currency_SHORT ?? 'MYR', ENT_QUOTES, 'UTF-8');?>):</label>
                            <input type="text" name="invoice_a" maxlength="70" value="<?php echo htmlspecialchars($invoice_aS, ENT_QUOTES, 'UTF-8'); ?>" placeholder="e.g. 50.00" />
                        </div>
                        <div class="form-group mb-2">
                            <label class="form-label small">Supplier / Vendor:</label>
                            <input type="text" name="invoice_b" maxlength="70" value="<?php echo htmlspecialchars($invoice_bS, ENT_QUOTES, 'UTF-8'); ?>" placeholder="e.g. University Bookstore" />
                        </div>
                        <div class="form-group mb-2">
                            <label class="form-label small">Invoice Number:</label>
                            <input type="text" name="invoice_c" maxlength="70" value="<?php echo htmlspecialchars($invoice_cS, ENT_QUOTES, 'UTF-8'); ?>" placeholder="e.g. INV-2026-001" />
                        </div>
                    </div>

                    <input type="hidden" name="lastchange" value="<?php echo time();?>" />
                    <div class="d-flex gap-2">
                        <input type="submit" class="btn btn-primary w-100" name="submitted" value="Save Changes" />
                        <input type="button" class="btn btn-secondary w-100" value="Cancel" onclick="if(window.parent && window.parent.closeAppModal) { window.parent.closeAppModal(false); } else { window.close(); }" />
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
