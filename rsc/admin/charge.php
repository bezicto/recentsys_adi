<?php
    session_start();define('includeExist', true);
    include_once '../includes/access_logged.php';
    include_once '../config.php';
    include_once '../includes/functions.php';
    include_once '../includes/token_validate.php';

    $current_circulation_mode = $circulation_mode ?? 'accessnum';

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST["submitted"]) && $_POST["submitted"] == 'Charge' && $proceedAfterToken) {
        $raw_input = trim($_POST["accessnum"] ?? '');
        $patron_id = trim($_POST["patron_id"] ?? '');
        $lodged_time = time();
        
        if (existPatron($patron_id) != 1) {
            $_SESSION['flash_charge_msg'] = ['type' => 'danger', 'text' => 'Patron is not registered in the system.'];
            header("Location: charge.php");
            exit;
        }

        if (isPatronEligibility($patron_id) != "TRUE") {
            $_SESSION['flash_charge_msg'] = ['type' => 'danger', 'text' => 'The patron has exceeded max number of loan items.'];
            header("Location: charge.php");
            exit;
        }

        if ($current_circulation_mode === 'isbnissn') {
            $availResult = getAvailableCopyByIsbnIssn($raw_input);
            if ($availResult['status'] === 'AVAILABLE') {
                $accessnum = $availResult['accessnum'];
                $item_title = $availResult['title'];

                $stmt = mysqli_prepare($GLOBALS["conn"], "INSERT INTO eg_item_charge VALUES (NULL, ?, ?, ?, ?, 0, '', '', '', NULL, '', '', '', 0.00, 0.00, 0.00, '')");
                mysqli_stmt_bind_param($stmt, "ssis", $accessnum, $patron_id, $lodged_time, $_SESSION['username']);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
                
                $stmt = mysqli_prepare($GLOBALS["conn"], "UPDATE eg_item_copies SET `39status`='CIRCULATED', `39lastchange`=? WHERE `39accessnum`=?");
                mysqli_stmt_bind_param($stmt, "is", $lodged_time, $accessnum);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
                $_SESSION['flash_charge_msg'] = ['type' => 'success', 'text' => "Successfully charged <strong>" . htmlspecialchars($item_title, ENT_QUOTES, 'UTF-8') . "</strong> (Accession: <strong>" . htmlspecialchars($accessnum, ENT_QUOTES, 'UTF-8') . "</strong>) to patron <strong>" . htmlspecialchars($patron_id, ENT_QUOTES, 'UTF-8') . "</strong>."];
            } else {
                $_SESSION['flash_charge_msg'] = ['type' => 'danger', 'text' => $availResult['message']];
            }
        } else {
            // Standard Accession Number Mode
            $accessnum = formatAccessionNumber($raw_input);
            if (existCopies($accessnum) == 1) {
                if (isAvailable($accessnum) == "TRUE") {
                    $stmt = mysqli_prepare($GLOBALS["conn"], "INSERT INTO eg_item_charge VALUES (NULL, ?, ?, ?, ?, 0, '', '', '', NULL, '', '', '', 0.00, 0.00, 0.00, '')");
                    mysqli_stmt_bind_param($stmt, "ssis", $accessnum, $patron_id, $lodged_time, $_SESSION['username']);
                    mysqli_stmt_execute($stmt);
                    mysqli_stmt_close($stmt);
                    
                    $stmt = mysqli_prepare($GLOBALS["conn"], "UPDATE eg_item_copies SET `39status`='CIRCULATED', `39lastchange`=? WHERE `39accessnum`=?");
                    mysqli_stmt_bind_param($stmt, "is", $lodged_time, $accessnum);
                    mysqli_stmt_execute($stmt);
                    mysqli_stmt_close($stmt);
                    $_SESSION['flash_charge_msg'] = ['type' => 'success', 'text' => "Successfully charged item <strong>" . htmlspecialchars($accessnum, ENT_QUOTES, 'UTF-8') . "</strong> to patron <strong>" . htmlspecialchars($patron_id, ENT_QUOTES, 'UTF-8') . "</strong>."];
                } elseif (isReferenceCopy($accessnum) == "TRUE") {
                    $_SESSION['flash_charge_msg'] = ['type' => 'danger', 'text' => 'Loan restricted: Item <strong>' . htmlspecialchars($accessnum, ENT_QUOTES, 'UTF-8') . '</strong> is designated as <strong>Reference Only / Current Issue</strong> (In-Library Use Only).'];
                } else {
                    $_SESSION['flash_charge_msg'] = ['type' => 'danger', 'text' => 'This material is unavailable for borrowing.'];
                }
            } else {
                $_SESSION['flash_charge_msg'] = ['type' => 'danger', 'text' => 'Patron not registered or material does not exist.'];
            }
        }
        header("Location: charge.php");
        exit;
    }
?>
<!DOCTYPE HTML>
<html lang='en'>

<head>
    <meta charset="utf-8">
    <title><?php echo htmlspecialchars($product_name, ENT_QUOTES, 'UTF-8');?> : Item Charging</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="../<?php echo htmlspecialchars($mini_icon_path, ENT_QUOTES, 'UTF-8');?>" rel="icon" type="image/png" />
    <link href="../assets/styles/style.css" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="../assets/fontawesome/css/all.min.css">
</head>

<body onload="document.getElementById('patron_id').focus();">
    <?php include_once '../includes/loggedinfo.php';?>
    
    <div class="app-container">
        <div class="circulation-header circulation-header-charge">
            <div class="circulation-header-content">
                <div class="circulation-header-icon">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i>
                </div>
                <div>
                    <h1 class="circulation-header-title">CHARGING</h1>
                    <div class="circulation-header-subtitle">Check-out and loan materials to patrons</div>
                </div>
            </div>
            <span class="circulation-header-badge">BORROW / CHECK-OUT</span>
        </div>

        <?php
            if (!empty($_SESSION['flash_charge_msg'])) {
                $cmsg = $_SESSION['flash_charge_msg'];
                $ctype = htmlspecialchars($cmsg['type'] ?? 'info', ENT_QUOTES, 'UTF-8');
                $ctext = $cmsg['text'] ?? '';
                echo "<div class='alert alert-{$ctype}'>{$ctext}</div>";
                unset($_SESSION['flash_charge_msg']);
            }
            $csrfToken = htmlspecialchars($_SESSION['token'] ?? '', ENT_QUOTES, 'UTF-8');
        ?>

        <div class="card mb-3">
            <div class="card-header">
                <strong>Charge / Check-Out Material</strong>
            </div>
            <div class="card-body">
                <form action="charge.php" method="post" enctype="multipart/form-data"<?php if ($current_circulation_mode === 'accessnum'): ?> onsubmit="var a=document.getElementById('accessnum'); if(a && a.value.trim()!=='' && !isNaN(a.value.trim())){ a.value=a.value.trim().padStart(10,'0'); }"<?php endif; ?>>
                    <input type="hidden" name="token" value="<?php echo $csrfToken; ?>" />
                    <div class="form-group">
                        <label class="form-label">Patron ID / IC Number:</label>
                        <div class="d-flex gap-2">
                            <input type="text" id="patron_id" name="patron_id" maxlength="70" value="<?php echo htmlspecialchars($_POST["patron_id"] ?? '', ENT_QUOTES, 'UTF-8');?>" required />
                            <button type="button" class="btn btn-scan btn-scan-charge" onclick="circScanner.openScanner('patron_id');" title="Scan Patron Barcode with Camera">
                                <i class="fa-solid fa-camera"></i> Scan
                            </button>
                            <input type="button" class="btn btn-secondary" name="clear" value="Clear" onclick="document.getElementById('patron_id').value='';document.getElementById('patron_id').focus();">
                        </div>
                    </div>
                
                    <div class="form-group">
                        <label class="form-label"><?php echo ($current_circulation_mode === 'isbnissn') ? 'ISBN / ISSN Barcode:' : 'Accession Number (Item Barcode):'; ?></label>
                        <div class="d-flex gap-2">
                            <input type="text" id="accessnum" name="accessnum" maxlength="70" placeholder="<?php echo ($current_circulation_mode === 'isbnissn') ? 'e.g. 978983... or 0123-4567' : 'e.g. 1 or 0000000001'; ?>"<?php if ($current_circulation_mode === 'accessnum'): ?> onblur="if(this.value.trim()!=='' && !isNaN(this.value.trim())){this.value=this.value.trim().padStart(10,'0');}"<?php endif; ?> required />
                            <button type="button" class="btn btn-scan btn-scan-charge" onclick="circScanner.openScanner('accessnum');" title="Scan Item Barcode with Camera">
                                <i class="fa-solid fa-camera"></i> Scan
                            </button>
                        </div>
                    </div>
                        
                    <input type="hidden" name="submitted" value="Charge" />
                    <input type="submit" class="btn btn-primary w-100" name="Submit1" value="Complete Charge"/>
                </form>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">
                <strong>Today's Charge Sessions</strong>
            </div>
            <div class="card-body p-0">
                <table class="table-modern m-0">
                <thead>
                    <tr>
                        <th class="text-center w-auto">#</th>
                        <th>Item / Accession</th>
                        <th>Patron</th>
                        <th>Due Date</th>
                        <th>Charge Info</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                    $stmt = mysqli_prepare($GLOBALS["conn"], "SELECT * FROM eg_item_charge WHERE `39charged_on` >= ? AND `39charged_by` = ? ORDER BY id DESC");
                    $today_timestamp = strtotime(date("Y-m-d"));
                    mysqli_stmt_bind_param($stmt, "is", $today_timestamp, $_SESSION['username']);
                    mysqli_stmt_execute($stmt);
                    $resultT = mysqli_stmt_get_result($stmt);
                    $n = 1;
                    while ($myrowT=mysqli_fetch_array($resultT)) {
                        $accessnum=$myrowT["39accessnum"];
                        $patron=$myrowT["39patron"];
                        $charged_on=$myrowT["39charged_on"];
                        $charged_by=$myrowT["39charged_by"];
                        
                        $duedate_mp = (int)($myrowT["39duedate"] ?? 0);
                        $maxSecond = (maxday($patron) * ($duedate_mp + 1)) * 86400;
                        $beginOfDay_for_charged_on = strtotime("midnight", $charged_on);
                        $duedate = $beginOfDay_for_charged_on + $maxSecond + 86399;
                        $duedate = shiftDueDate($duedate);
                        $item_title = getTitle($accessnum);
                        
                        echo "<tr class='table-row'>";
                            echo "<td class='text-center'>$n</td>";
                            echo "<td><strong>" . htmlspecialchars($accessnum, ENT_QUOTES, 'UTF-8') . "</strong><br/><small class='text-muted'>" . htmlspecialchars($item_title, ENT_QUOTES, 'UTF-8') . "</small></td>";
                            echo "<td>" . htmlspecialchars($patron, ENT_QUOTES, 'UTF-8') . "</td>";
                            echo "<td>" . htmlspecialchars(date('D, Y-m-d', $duedate), ENT_QUOTES, 'UTF-8') . "</td>";
                            echo "<td class='text-muted'><small>" . htmlspecialchars(date('D, Y-m-d h:i:s a', $charged_on) . " by $charged_by", ENT_QUOTES, 'UTF-8') . "</small></td>";
                        echo "</tr>";
                        $n = $n + 1;
                    }
                ?>
                </tbody>
                </table>
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
                <h5 class="scanner-modal-title"><i class="fa-solid fa-camera text-success"></i> Barcode &amp; QR Scanner</h5>
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
                            <option value="patron_id">Patron ID</option>
                            <option value="accessnum">Item Barcode</option>
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
                    <input type="checkbox" id="circulationAutoSubmit" class="form-check-input">
                    <label for="circulationAutoSubmit" class="form-check-label small">Auto-submit</label>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/jscripts/html5-qrcode.min.js"></script>
    <script src="../assets/jscripts/circulation_scanner.js"></script>
    <script>
        const circScanner = new CirculationScanner({
            pageType: 'charge',
            circulationMode: '<?php echo htmlspecialchars($current_circulation_mode, ENT_QUOTES, 'UTF-8'); ?>',
            defaultTarget: 'patron_id'
        });
    </script>
</body>
</html>
