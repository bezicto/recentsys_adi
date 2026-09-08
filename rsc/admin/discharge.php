<?php
    session_start();define('includeExist', true);
    include_once '../includes/access_logged.php';
    include_once '../config.php';
    include_once '../includes/functions.php';
    include_once '../includes/token_validate.php';

    $current_circulation_mode = $circulation_mode ?? 'accessnum';

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST["submitted"]) && $_POST["submitted"] == 'Discharge' && $proceedAfterToken) {
        $raw_input = trim($_POST["accessnum"] ?? '');
        $patron_id = trim($_POST["patron_id"] ?? '');
        $lodged_time = time();

        if ($current_circulation_mode === 'isbnissn') {
            $loanRes = getActiveLoansByIsbnIssn($raw_input, $patron_id);

            if ($loanRes['status'] === 'MATCH') {
                $chargeRecord = $loanRes['loan'];
                $accessnum = $chargeRecord['39accessnum'];
                $patronOfLoan = $chargeRecord['39patron'];
                $charge_id = (int)$chargeRecord['id'];
                $item_title = $chargeRecord['38title'] ?? getTitle($accessnum);

                $enforced_fine = getIDCurrentEnforcedFine(getTypeID($accessnum));
                $stmt = mysqli_prepare($GLOBALS["conn"], "UPDATE eg_item_charge SET `40dc`='DC', `40dc_on`=?, `40dc_by`=?, `40dc_enforcedfine`=? WHERE id=? AND (`40dc` IS NULL OR `40dc` != 'DC')");
                mysqli_stmt_bind_param($stmt, "isis", $lodged_time, $_SESSION["username"], $enforced_fine, $charge_id);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);

                $stmt = mysqli_prepare($GLOBALS["conn"], "UPDATE eg_item_copies SET `39status`='AVAILABLE', `39lastchange`=? WHERE `39accessnum`=?");
                mysqli_stmt_bind_param($stmt, "is", $lodged_time, $accessnum);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);

                $_SESSION['flash_discharge_msg'] = [
                    'type' => 'success',
                    'text' => "Successfully discharged <strong>" . htmlspecialchars($item_title, ENT_QUOTES, 'UTF-8') . "</strong> (Accession: <strong>" . htmlspecialchars($accessnum, ENT_QUOTES, 'UTF-8') . "</strong>) from patron <strong>" . htmlspecialchars($patronOfLoan, ENT_QUOTES, 'UTF-8') . "</strong>."
                ];
            } elseif ($loanRes['status'] === 'MULTIPLE') {
                $_SESSION['flash_discharge_msg'] = [
                    'type' => 'warning',
                    'text' => $loanRes['message'],
                    'focus_patron' => true,
                    'last_code' => $raw_input
                ];
            } else {
                $_SESSION['flash_discharge_msg'] = ['type' => 'danger', 'text' => $loanRes['message']];
            }
        } else {
            // Standard Accession Number Mode
            $accessnum = formatAccessionNumber($raw_input);
            if (existCharge($accessnum) >= 1) {
                $enforced_fine = getIDCurrentEnforcedFine(getTypeID($accessnum));
                $stmt = mysqli_prepare($GLOBALS["conn"], "UPDATE eg_item_charge SET `40dc`='DC', `40dc_on`=?, `40dc_by`=?, `40dc_enforcedfine`=? WHERE `39accessnum`=? AND (`40dc` IS NULL OR `40dc` != 'DC')");
                mysqli_stmt_bind_param($stmt, "isis", $lodged_time, $_SESSION["username"], $enforced_fine, $accessnum);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
                
                $stmt = mysqli_prepare($GLOBALS["conn"], "UPDATE eg_item_copies SET `39status`='AVAILABLE', `39lastchange`=? WHERE `39accessnum`=?");
                mysqli_stmt_bind_param($stmt, "is", $lodged_time, $accessnum);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
                $_SESSION['flash_discharge_msg'] = ['type' => 'success', 'text' => "Successfully discharged item <strong>" . htmlspecialchars($accessnum, ENT_QUOTES, 'UTF-8') . "</strong>."];
            } else {
                $_SESSION['flash_discharge_msg'] = ['type' => 'danger', 'text' => 'Error: Item is not currently charged.'];
            }
        }
        header("Location: discharge.php");
        exit;
    }

    $focus_field = (!empty($_SESSION['flash_discharge_msg']['focus_patron'])) ? 'patron_id' : 'accessnum';
    $preserved_code = $_SESSION['flash_discharge_msg']['last_code'] ?? '';
?>
<!DOCTYPE HTML>
<html lang='en'>

<head>
    <meta charset="utf-8">
    <title><?php echo htmlspecialchars($product_name, ENT_QUOTES, 'UTF-8');?> : Item Discharging</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="../<?php echo htmlspecialchars($mini_icon_path, ENT_QUOTES, 'UTF-8');?>" rel="icon" type="image/png" />
    <link href="../assets/styles/style.css" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="../assets/fontawesome/css/all.min.css">
</head>

<body onload="var el = document.getElementById('<?php echo $focus_field; ?>'); if(el) el.focus();">
    <?php include_once '../includes/loggedinfo.php';?>
    
    <div class="app-container">
        <div class="circulation-header circulation-header-discharge">
            <div class="circulation-header-content">
                <div class="circulation-header-icon">
                    <i class="fa-solid fa-inbox"></i>
                </div>
                <div>
                    <h1 class="circulation-header-title">DISCHARGING</h1>
                    <div class="circulation-header-subtitle">Check-in and return borrowed materials</div>
                </div>
            </div>
            <span class="circulation-header-badge">RETURN / CHECK-IN</span>
        </div>

        <?php
            if (!empty($_SESSION['flash_discharge_msg'])) {
                $dmsg = $_SESSION['flash_discharge_msg'];
                $dtype = htmlspecialchars($dmsg['type'] ?? 'info', ENT_QUOTES, 'UTF-8');
                $dtext = $dmsg['text'] ?? '';
                echo "<div class='alert alert-{$dtype}'>{$dtext}</div>";
                unset($_SESSION['flash_discharge_msg']);
            }
            $csrfToken = htmlspecialchars($_SESSION['token'] ?? '', ENT_QUOTES, 'UTF-8');
        ?>

        <div class="card mb-3">
            <div class="card-header">
                <strong>Discharge / Check-In Material</strong>
            </div>
            <div class="card-body">
                <form action="discharge.php" method="post" enctype="multipart/form-data"<?php if ($current_circulation_mode === 'accessnum'): ?> onsubmit="var a=document.getElementById('accessnum'); if(a && a.value.trim()!=='' && !isNaN(a.value.trim())){ a.value=a.value.trim().padStart(10,'0'); }"<?php endif; ?>>
                    <input type="hidden" name="token" value="<?php echo $csrfToken; ?>" />
                    <?php if ($current_circulation_mode === 'isbnissn'): ?>
                    <div class="form-group">
                        <label class="form-label">Patron ID / IC Number <small class="text-muted">(Optional &mdash; required if multiple copies are on loan)</small>:</label>
                        <div class="d-flex gap-2">
                            <input type="text" id="patron_id" name="patron_id" maxlength="70" value="<?php echo htmlspecialchars($_POST["patron_id"] ?? '', ENT_QUOTES, 'UTF-8');?>" placeholder="Enter Patron ID if needed" />
                            <button type="button" class="btn btn-scan btn-scan-discharge" onclick="circScanner.openScanner('patron_id');" title="Scan Patron Barcode with Camera">
                                <i class="fa-solid fa-camera"></i> Scan
                            </button>
                            <input type="button" class="btn btn-secondary" name="clear" value="Clear" onclick="document.getElementById('patron_id').value='';document.getElementById('patron_id').focus();">
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="form-group">
                        <label class="form-label"><?php echo ($current_circulation_mode === 'isbnissn') ? 'ISBN / ISSN Barcode:' : 'Accession Number (Item Barcode):'; ?></label>
                        <div class="d-flex gap-2">
                            <input type="text" id="accessnum" name="accessnum" maxlength="70" value="<?php echo htmlspecialchars($preserved_code, ENT_QUOTES, 'UTF-8'); ?>" placeholder="<?php echo ($current_circulation_mode === 'isbnissn') ? 'e.g. 978983... or 0123-4567' : 'e.g. 1 or 0000000001'; ?>"<?php if ($current_circulation_mode === 'accessnum'): ?> onblur="if(this.value.trim()!=='' && !isNaN(this.value.trim())){this.value=this.value.trim().padStart(10,'0');}"<?php endif; ?> required />
                            <button type="button" class="btn btn-scan btn-scan-discharge" onclick="circScanner.openScanner('accessnum');" title="Scan Item Barcode with Camera">
                                <i class="fa-solid fa-camera"></i> Scan
                            </button>
                        </div>
                    </div>
                        
                    <input type="hidden" name="submitted" value="Discharge" />
                    <input type="submit" class="btn btn-primary w-100" name="Submit1" value="Complete Discharge"/>
                </form>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">
                <strong>Today's Discharge Sessions</strong>
            </div>
            <div class="card-body p-0">
                <table class="table-modern m-0">
                <thead>
                    <tr>
                        <th class="text-center w-auto">#</th>
                        <th>Item / Accession</th>
                        <th>Patron</th>
                        <th>Due Date</th>
                        <th>Discharge Info</th>
                        <th class="text-center">Overdue?</th>
                        <th class="text-center">Late Fines (<?php echo htmlspecialchars($currency_SHORT, ENT_QUOTES, 'UTF-8');?>)</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                    $stmt = mysqli_prepare($GLOBALS["conn"], "SELECT * FROM eg_item_charge WHERE `40dc`='DC' AND `40dc_on` >= ? AND `40dc_by` = ? ORDER BY id DESC");
                    $today_timestamp = strtotime(date("Y-m-d"));
                    mysqli_stmt_bind_param($stmt, "is", $today_timestamp, $_SESSION['username']);
                    mysqli_stmt_execute($stmt);
                    $resultT = mysqli_stmt_get_result($stmt);
                    $n = 1;
                    while ($myrowT=mysqli_fetch_array($resultT)) {
                        $accessnum=$myrowT["39accessnum"] ?? '';
                        $patron=$myrowT["39patron"] ?? '';
                        $charged_on=$myrowT["39charged_on"] ?? 0;
                        $dc_on=$myrowT["40dc_on"] ?? 0;
                        $dc_by=$myrowT["40dc_by"] ?? '';
                        $dc_enforcedfine=$myrowT["40dc_enforcedfine"] ?? 0;
                        $duedate_mp=$myrowT["39duedate"] ?? 0;
                                            
                        $maxSecond = (maxday($patron)*($duedate_mp+1))*86400;//for calculating due date
                        $beginOfDay_for_charged_on = strtotime("midnight", $charged_on);//early morning for the day charged
                        $duedate = $beginOfDay_for_charged_on + $maxSecond + 86399;// + 86399 to reach midnight
                        $duedate = shiftDueDate($duedate); //shifting duedate if holiday
                        $item_title = getTitle($accessnum);
                        
                        echo "<tr class='table-row'>";
                            echo "<td class='text-center'>$n</td>";
                            echo "<td><strong>$accessnum</strong><br/><small class='text-muted'>" . htmlspecialchars($item_title, ENT_QUOTES, 'UTF-8') . " (" . getTypeName($accessnum) . ")</small></td>";
                            echo "<td>" . htmlspecialchars($patron, ENT_QUOTES, 'UTF-8') . "</td>";
                            echo "<td>" . htmlspecialchars(date('D, Y-m-d', $duedate), ENT_QUOTES, 'UTF-8') . "</td>";
                            echo "<td class='text-muted'><small>" . htmlspecialchars(date('D, Y-m-d h:i:s a', $dc_on) . " by $dc_by", ENT_QUOTES, 'UTF-8') . "</small></td>";
                            
                            $overdue_days = calculateOverdueDays($dc_on, $duedate);

                            echo "<td class='text-center'>";
                                if ($overdue_days <= 0) {
                                    echo "<span class='badge badge-success'>No</span>";
                                } else {
                                    echo "<span class='badge badge-danger'>" . (int)$overdue_days . " day(s)</span>";
                                }
                            echo "</td>";
                            echo "<td class='text-center'>";
                                if ($overdue_days > 0) {
                                    $fine_val = calculatedFines($overdue_days, $accessnum, $dc_enforcedfine);
                                    echo "<strong>" . htmlspecialchars(number_format((float)$fine_val, 2, '.', ''), ENT_QUOTES, 'UTF-8') . "</strong>";
                                } else {
                                    echo "-";
                                }
                            echo "</td>";
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
                <h5 class="scanner-modal-title"><i class="fa-solid fa-camera text-danger"></i> Barcode &amp; QR Scanner</h5>
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
                            <option value="accessnum">Item Barcode</option>
                            <?php if ($current_circulation_mode === 'isbnissn'): ?>
                            <option value="patron_id">Patron ID</option>
                            <?php endif; ?>
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
                    <label for="circulationAutoSubmit" class="form-check-label small">Auto-submit on scan</label>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/jscripts/html5-qrcode.min.js"></script>
    <script src="../assets/jscripts/circulation_scanner.js"></script>
    <script>
        const circScanner = new CirculationScanner({
            pageType: 'discharge',
            circulationMode: '<?php echo htmlspecialchars($current_circulation_mode, ENT_QUOTES, 'UTF-8'); ?>',
            defaultTarget: 'accessnum'
        });
    </script>
</body>
</html>
