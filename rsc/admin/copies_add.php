<?php
    session_start();define('includeExist', true);
    include_once '../includes/access_logged.php';
    include_once '../config.php';
    include_once '../includes/functions.php';
    include_once '../includes/token_validate.php';

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST["submitted"]) && $_POST["submitted"] == 'Insert' && $proceedAfterToken) {
        $bahan_id = (int)($_POST["bahan_id"] ?? 0);
        $status = $_POST["status"] ?? 'AVAILABLE';
        $volume = mb_substr(trim($_POST["volume"] ?? ''), 0, 50);
        $issue = mb_substr(trim($_POST["issue"] ?? ''), 0, 50);
        $year = mb_substr(trim($_POST["year"] ?? ''), 0, 10);
        $is_reference = (isset($_POST["is_reference"]) && $_POST["is_reference"] === 'YES') ? 'YES' : 'NO';
        $addedon = time();
        $copies = max(1, min(100, (int)($_POST["copies"] ?? 1)));
        $lastchange = time();
        $invoice_a = $_POST["invoice_a"] ?? '';
        $invoice_b = $_POST["invoice_b"] ?? '';
        $invoice_c = $_POST["invoice_c"] ?? '';
        
        // Get last used 39accessnum from eg_item_copies
        $last_accession_num = 0;
        $query_last = "SELECT MAX(CAST(`39accessnum` AS UNSIGNED)) as max_acc FROM eg_item_copies WHERE `39accessnum` REGEXP '^[0-9]+$'";
        $result_last = mysqli_query($GLOBALS["conn"], $query_last);
        if ($result_last && $row_last = mysqli_fetch_assoc($result_last)) {
            $last_accession_num = (int)($row_last["max_acc"] ?? 0);
        }
        
        if ($bahan_id > 0) {
            $accession_num = '';
            $stmt = mysqli_prepare($GLOBALS["conn"], "INSERT INTO eg_item_copies (
                `eg_item_id`, `39accessnum`, `39status`, `39volume`, `39issue`, `39year`, `39is_reference`,
                `39invoice_a`, `39invoice_b`, `39invoice_c`, `39addedon`, `39lastchange`
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "isssssssssii",
                $bahan_id, $accession_num, $status, $volume, $issue, $year, $is_reference,
                $invoice_a, $invoice_b, $invoice_c, $addedon, $lastchange
            );
            $added_accessions = [];
            for ($x = 1; $x <= $copies; $x++) {
                $last_accession_num++;
                $accession_num = sprintf("%010d", $last_accession_num);
                $added_accessions[] = $accession_num;
                mysqli_stmt_execute($stmt);
            }
            mysqli_stmt_close($stmt);
            $accStr = implode(', ', $added_accessions);
            $_SESSION['flash_copies_msg'] = [
                'type' => 'success', 
                'text' => "<strong>$copies copy/copies</strong> successfully registered into the catalog! Accession Barcode(s): <strong>$accStr</strong>",
                'last_bahan_id' => $bahan_id
            ];
        } else {
            $_SESSION['flash_copies_msg'] = ['type' => 'danger', 'text' => 'Input cancelled. Please ensure required information is provided.'];
        }
        header("Location: copies_add.php?bahan_id=" . $bahan_id);
        exit;
    }

    $csrfToken = htmlspecialchars($_SESSION['token'] ?? '', ENT_QUOTES, 'UTF-8');
    $bahan_id_param = isset($_REQUEST["bahan_id"]) && is_numeric($_REQUEST["bahan_id"]) ? (int)$_REQUEST["bahan_id"] : 0;
    $parent_title = '';
    $parent_issn = '';
    $parent_type = '';
    $is_serial = false;

    if ($bahan_id_param > 0) {
        $stmtP = mysqli_prepare($GLOBALS["conn"], "SELECT `38title`, `38issn`, `39type` FROM eg_item WHERE id = ?");
        if ($stmtP) {
            mysqli_stmt_bind_param($stmtP, "i", $bahan_id_param);
            mysqli_stmt_execute($stmtP);
            $resP = mysqli_stmt_get_result($stmtP);
            if ($resP && $rowP = mysqli_fetch_assoc($resP)) {
                $parent_title = $rowP['38title'] ?? '';
                $parent_issn = str_replace(['-', '–', '—'], '', trim($rowP['38issn'] ?? ''));
                $parent_type = trim((string)($rowP['39type'] ?? ''));
                $parent_type_name = is_numeric($parent_type) ? idToType($parent_type) : $parent_type;
                if (!empty($parent_issn) || stripos($parent_type, 'serial') !== false || stripos($parent_type_name, 'serial') !== false || stripos($parent_type_name, 'bersiri') !== false || stripos($parent_type_name, 'journal') !== false || stripos($parent_type_name, 'majalah') !== false || stripos($parent_type_name, 'periodical') !== false) {
                    $is_serial = true;
                }
            }
            mysqli_stmt_close($stmtP);
        }
    }
?>
<!DOCTYPE HTML>
<html lang='en'>

<head>
    <meta charset="utf-8">
    <title><?php echo htmlspecialchars($product_name, ENT_QUOTES, 'UTF-8');?> : <?php echo $is_serial ? 'Check-in Serial Issue' : 'Add Copies'; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="../<?php echo htmlspecialchars($mini_icon_path, ENT_QUOTES, 'UTF-8');?>" rel="icon" type="image/png" />
    <link href="../assets/styles/style.css" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="../assets/fontawesome/css/all.min.css">
</head>

<body>
    <div class="container-narrow mt-3">
        <?php
            $hasSuccess = false;
            if (!empty($_SESSION['flash_copies_msg'])) {
                $cmsg = $_SESSION['flash_copies_msg'];
                $ctype = htmlspecialchars($cmsg['type'] ?? 'info', ENT_QUOTES, 'UTF-8');
                $ctext = $cmsg['text'] ?? '';
                $lastBahan = (int)($cmsg['last_bahan_id'] ?? 0);
                if ($ctype === 'success') {
                    $hasSuccess = true;
                }
                echo "<div class='alert alert-{$ctype} mb-3'>";
                echo "<div>{$ctext}</div>";
                echo "<div class='mt-2 pt-2 border-top border-light-subtle d-flex justify-content-end'>";
                echo "<button type='button' class='btn btn-sm btn-secondary' onclick=\"if(window.parent && window.parent.closeAppModal) { window.parent.closeAppModal(true); } else { window.close(); }\">Done</button>";
                echo "</div>";
                echo "</div>";
                unset($_SESSION['flash_copies_msg']);
            }
        ?>
        <?php if (!$hasSuccess): ?>
            <div class="card">
                <div class="card-header">
                    <strong><?php echo $is_serial ? 'Check-in Serial Issue (Cardex)' : 'Batch Add Physical Copies'; ?> <?php if ($is_serial): ?><span class="badge badge-info ms-2">Serial / Periodical</span><?php endif; ?></strong>
                </div>
                <div class="card-body">
                    <?php if (!empty($parent_title)): ?>
                        <div class="mb-3 p-2 bg-light rounded text-dark">
                            <small class="text-muted d-block">Record Title:</small>
                            <strong><?php echo htmlspecialchars($parent_title, ENT_QUOTES, 'UTF-8'); ?></strong>
                        </div>
                    <?php endif; ?>

                    <form action="copies_add.php" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="token" value="<?php echo $csrfToken; ?>" />
                        
                        <div class="form-group">
                            <label class="form-label">Number of Copies to Generate:</label>
                            <select name="copies">
                                <?php
                                for ($x = 1; $x <= 20; $x++) {
                                    echo "<option value='$x'>$x</option>";
                                }
                                ?>
                            </select>
                            <small class="text-muted d-block mt-1">Accession barcodes will be generated sequentially.</small>
                        </div>
                        
                        <!-- Serial / Periodical / Multi-volume Metadata -->
                        <div class="card p-3 mb-3 bg-light border">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <strong><i class="fa fa-layer-group text-primary me-1"></i> Issue &amp; Volume Designation (Serials / Sets)</strong>
                                <?php if ($is_serial): ?>
                                    <span class="badge badge-primary">Recommended for Serials</span>
                                <?php endif; ?>
                            </div>
                            <div class="row g-2">
                                <div class="col-md-4 form-group mb-2">
                                    <label class="form-label small">Volume / Jilid:</label>
                                    <input type="text" name="volume" maxlength="50" placeholder="e.g. Vol. 45 or Jil. 12" />
                                </div>
                                <div class="col-md-4 form-group mb-2">
                                    <label class="form-label small">Issue / No. / Month:</label>
                                    <input type="text" name="issue" maxlength="50" placeholder="e.g. No. 3 or Mac 2026" />
                                </div>
                                <div class="col-md-4 form-group mb-2">
                                    <label class="form-label small">Publication Year:</label>
                                    <input type="text" name="year" maxlength="10" placeholder="e.g. <?php echo date('Y'); ?>" />
                                </div>
                            </div>
                            <div class="form-group mt-2">
                                <label class="form-label small">Circulation Restriction:</label>
                                <select name="is_reference">
                                    <option value="NO">Circulating (Standard Loan)</option>
                                    <option value="YES" <?php if ($is_serial) echo 'selected'; ?>>Reference Only / Current Issue (Non-circulating)</option>
                                </select>
                                <small class="text-muted d-block mt-1">Select "Reference Only" for display/latest magazine issues or non-circulating reference copies.</small>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Default Availability Status:</label>
                            <select name="status">
                                <option value="AVAILABLE">Available</option>
                                <option value="REFERENCE">Reference (In-Library Use)</option>
                                <option value="CIRCULATED">Circulated (On Loan)</option>
                                <option value="RESERVED">Reserved</option>
                                <option value="ONORDER">On Order</option>
                                <option value="FINALPROCESSING">Final Processing</option>
                                <option value="WEEDED">Weeded</option>
                                <option value="ONHOLD">On Hold</option>
                                <option value="LOST">Lost</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Price per unit (<?php echo htmlspecialchars($currency_SHORT, ENT_QUOTES, 'UTF-8');?>):</label>
                            <input type="text" name="invoice_a" maxlength="70" value=""/>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Supplier / Vendor:</label>
                            <input type="text" name="invoice_b" maxlength="70" value=""/>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Invoice Number:</label>
                            <input type="text" name="invoice_c" maxlength="70" value=""/>
                        </div>
                    
                        <input type="hidden" name="bahan_id" value="<?php echo $bahan_id_param;?>" />
                        <input type="hidden" name="addedon" value="<?php echo time();?>" />
                        <input type="hidden" name="lastchange" value="<?php echo time();?>" />
                    
                        <div class="d-flex gap-2">
                            <input type="submit" class="btn btn-primary w-100" name="submitted" value="Insert" />
                            <input type="button" class="btn btn-secondary w-100" value="Close" onclick="if(window.parent && window.parent.closeAppModal) { window.parent.closeAppModal(true); } else { window.close(); }" />
                        </div>
                    </form>
                </div>
            </div>
        
            <div class="text-center my-3">
                <a class="btn btn-secondary btn-sm" href="#" onclick="if(window.parent && window.parent.closeAppModal) { window.parent.closeAppModal(true); } else { window.close(); }">&times; Close</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
