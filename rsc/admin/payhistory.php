<?php
    session_start();define('includeExist', true);
    include_once '../includes/access_logged.php';
    include_once '../config.php';
    include_once '../includes/functions.php';
    include_once '../includes/token_validate.php';

    $is_admin = (($_SESSION['editmode'] ?? '') === 'SUPER');

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST["f_button"]) && ($_POST["f_button"] === "Pay" || $_POST["f_button"] === "Submit Payment") && $proceedAfterToken) {
        $pid = (int)($_POST["pid"] ?? 0);
        $f_discount = ($is_admin && is_numeric($_POST["f_discount"] ?? '')) ? max(0, (float)$_POST["f_discount"]) : 0.00;
        $f_givenamount = is_numeric($_POST["f_givenamount"] ?? '') ? max(0, (float)$_POST["f_givenamount"]) : 0.00;
        $patron_user = patronIdToUsername($pid);
        
        if (!empty($_POST['chargeid_list']) && is_array($_POST['chargeid_list']) && !empty($patron_user)) {
            $valid_charge_ids = [];
            $total_calculated_fine = 0.00;
            
            foreach ($_POST['chargeid_list'] as $check) {
                $checkA = explode(' ', $check);
                $charge_id = (int)$checkA[0];
                if ($charge_id <= 0) continue;
                
                // Validate loan record belongs to patron and is returned
                $stmtV = mysqli_prepare($GLOBALS["conn"], "SELECT * FROM eg_item_charge WHERE id=? AND `39patron`=? AND `41f_pay`!='YES' AND `40dc`='DC'");
                mysqli_stmt_bind_param($stmtV, "is", $charge_id, $patron_user);
                mysqli_stmt_execute($stmtV);
                $resV = mysqli_stmt_get_result($stmtV);
                if ($rowV = mysqli_fetch_assoc($resV)) {
                    $valid_charge_ids[] = $charge_id;
                    $charged_on = $rowV["39charged_on"];
                    $dc_on = $rowV["40dc_on"];
                    $dc_enforcedfine = $rowV["40dc_enforcedfine"];
                    $duedate_mp = $rowV["39duedate"];
                    $accessnum = $rowV["39accessnum"];
                    
                    $maxSecond = (maxday($patron_user) * ($duedate_mp + 1)) * 86400;
                    $beginOfDay_for_charged_on = strtotime("midnight", $charged_on);
                    $duedate = $beginOfDay_for_charged_on + $maxSecond + 86399;
                    $duedate = shiftDueDate($duedate);
                    $overdue_days = calculateOverdueDays($dc_on, $duedate);
                    $item_fine = (float)calculatedFines($overdue_days, $accessnum, $dc_enforcedfine);
                    $total_calculated_fine += $item_fine;
                }
                mysqli_stmt_close($stmtV);
            }
            
            if (!empty($valid_charge_ids)) {
                $f_discount = min($f_discount, $total_calculated_fine);
                $f_finalamount = max(0, $total_calculated_fine - $f_discount);
                $current_time = time();
                $receipt_id = $pid . $current_time;
                $count_items = count($valid_charge_ids);
                $item_received_share = number_format($f_finalamount / $count_items, 2, '.', '');
                $item_discount_share = number_format($f_discount / $count_items, 2, '.', '');
                $item_given_share = number_format($f_givenamount / $count_items, 2, '.', '');
                
                foreach ($valid_charge_ids as $cid) {
                    $stmt = mysqli_prepare(
                        $GLOBALS["conn"],
                        "UPDATE eg_item_charge
                        SET `41f_pay`='YES',
                        `41f_paidon`=?,
                        `41f_received`=?,
                        `41f_received_amount`=?,
                        `41f_discount_amount`=?,
                        `41f_given_amount`=?,
                        `41f_receipt_id`=?
                        WHERE id=?"
                    );
                    mysqli_stmt_bind_param($stmt, "isssssi", $current_time, $_SESSION['username'], $item_received_share, $item_discount_share, $item_given_share, $receipt_id, $cid);
                    mysqli_stmt_execute($stmt);
                    mysqli_stmt_close($stmt);
                }
                
                $final_display = number_format($f_finalamount, 2);
                $_SESSION['flash_pay_msg'] = ['type' => 'success', 'text' => "Payment completed successfully! Received: <strong>" . htmlspecialchars($currency_SHORT, ENT_QUOTES, 'UTF-8') . " $final_display</strong> (Receipt: " . htmlspecialchars($receipt_id, ENT_QUOTES, 'UTF-8') . ")."];
            }
        }
        header("Location: payhistory.php?pid=" . $pid);
        exit;
    }

    $pid_param = isset($_REQUEST["pid"]) && is_numeric($_REQUEST["pid"]) ? (int)$_REQUEST["pid"] : 0;
?>
<!DOCTYPE HTML>
<html lang='en'>

<head>
    <meta charset="utf-8">
    <title><?php echo htmlspecialchars($product_name, ENT_QUOTES, 'UTF-8');?> : Payment History</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="../<?php echo htmlspecialchars($mini_icon_path, ENT_QUOTES, 'UTF-8');?>" rel="icon" type="image/png" />
    <link href="../assets/styles/style.css" rel="stylesheet" type="text/css">
    
    <script type="text/javascript">
        function checkTotal()
        {
            var sum = 0.00;
            var checkboxes = document.querySelectorAll('input[name="chargeid_list[]"]:checked');
            checkboxes.forEach(function(cb) {
                var parts = cb.value.split(' ');
                if (parts.length > 1) {
                    sum += parseFloat(parts[1]) || 0;
                }
            });
            if (document.listForm && document.listForm.f_amount) {
                document.listForm.f_amount.value = sum.toFixed(2);
            }
            changeFinalAmount();
        }
        function changeFinalAmount()
        {
            if (!document.listForm) return;
            var totalAmt = parseFloat(document.listForm.f_amount.value) || 0;
            var disc = parseFloat(document.listForm.f_discount.value) || 0;
            if (disc > totalAmt) { disc = totalAmt; document.listForm.f_discount.value = disc.toFixed(2); }
            if (disc < 0) { disc = 0; document.listForm.f_discount.value = '0.00'; }
            document.listForm.f_finalamount.value = (totalAmt - disc).toFixed(2);
            changeBalance();
        }
        function changeBalance()
        {
            if (!document.listForm) return;
            var finalAmt = parseFloat(document.listForm.f_finalamount.value) || 0;
            var givenAmt = parseFloat(document.listForm.f_givenamount.value) || 0;
            document.listForm.f_balance.value = (givenAmt - finalAmt).toFixed(2);
        }
    </script>
</head>

<body>
    <?php include_once '../includes/loggedinfo.php';?>
    
    <div class="app-container">
        <?php
            if (!empty($_SESSION['flash_pay_msg'])) {
                $pmsg = $_SESSION['flash_pay_msg'];
                $ptype = htmlspecialchars($pmsg['type'] ?? 'info', ENT_QUOTES, 'UTF-8');
                $ptext = $pmsg['text'] ?? '';
                echo "<div class='alert alert-{$ptype}'>{$ptext}</div>";
                unset($_SESSION['flash_pay_msg']);
            }
            $csrfToken = htmlspecialchars($_SESSION['token'] ?? '', ENT_QUOTES, 'UTF-8');
        ?>
            
        <form action="payhistory.php" name="listForm" method="post" enctype="multipart/form-data">
            <input type="hidden" name="token" value="<?php echo $csrfToken; ?>" />
            <div class="card mb-3">
                <div class="card-header">
                    <div>Fines for Patron: <strong><?php echo htmlspecialchars(namePatron($pid_param), ENT_QUOTES, 'UTF-8');?></strong></div>
                </div>
                <div class="card-body p-0">
                    <table class="table-modern m-0">
                    <thead>
                        <tr>
                            <th class="text-center w-auto">#</th>
                            <th>Material / Accession</th>
                            <th>Loan Statement</th>
                            <th class="text-center">Fines</th>
                            <th class="text-center">Pay?</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                        $patron_user = patronIdToUsername($pid_param);
                        $stmtT = mysqli_prepare($GLOBALS["conn"], "SELECT * FROM eg_item_charge WHERE `39patron`=? AND `41f_pay`!='YES' AND `40dc`='DC'");
                        mysqli_stmt_bind_param($stmtT, "s", $patron_user);
                        mysqli_stmt_execute($stmtT);
                        $resultT = mysqli_stmt_get_result($stmtT);
                        $n = 1;
                        $amountdue = 0;
                        while ($resultT && $myrow=mysqli_fetch_array($resultT)) {
                            $id=(int)$myrow["id"];
                            $patron=$myrow["39patron"] ?? '';
                            $accessnum=$myrow["39accessnum"] ?? '';
                            $charged_on=$myrow["39charged_on"] ?? 0;
                            $dc_on=$myrow["40dc_on"] ?? 0;
                            $dc_enforcedfine=$myrow["40dc_enforcedfine"] ?? 0;
                            $duedate_mp=$myrow["39duedate"] ?? 0;
                            
                            $maxSecond = (maxday($patron)*($duedate_mp+1))*86400;//for calculating due date
                            $beginOfDay_for_charged_on = strtotime("midnight", $charged_on);//early morning for the day charged
                            $duedate = $beginOfDay_for_charged_on + $maxSecond + 86399;// + 86399 to reach midnight
                            $duedate = shiftDueDate($duedate); //shifting duedate if holiday
                            
                            if ($dc_on <> null && $dc_on != 0) {
                                $overdue_days = calculateOverdueDays($dc_on, $duedate);
                            } else {
                                $overdue_days = 0;
                            }
                            
                            $fines2pay = calculatedFines($overdue_days, $accessnum, $dc_enforcedfine);
                            
                            if ($overdue_days > 0) {
                                echo "<tr class='table-row'>";
                                echo "<td class='text-center'>$n</td>";
                                echo "<td><strong>" . htmlspecialchars(getTitle($accessnum), ENT_QUOTES, 'UTF-8') . "</strong><br/><small class='text-muted'>" . htmlspecialchars($accessnum, ENT_QUOTES, 'UTF-8') . "</small></td>";
                                echo "<td><small>Charged: " . htmlspecialchars(date('D, Y-m-d h:i:s a', $charged_on), ENT_QUOTES, 'UTF-8') . "<br/>Due: " . htmlspecialchars(date('D, Y-m-d', $duedate), ENT_QUOTES, 'UTF-8') . "<br/>Returned: " . htmlspecialchars(date('D, Y-m-d h:i:s a', $dc_on), ENT_QUOTES, 'UTF-8') . "<br/><span class='badge badge-danger mt-1'>" . (int)$overdue_days . " days overdue</span></small></td>";
                                echo "<td class='text-center text-danger font-bold'>" . htmlspecialchars($currency_SHORT, ENT_QUOTES, 'UTF-8') . " " . number_format((float)$fines2pay, 2, '.', '') . "</td>";
                                echo "<td class='text-center'><input type='checkbox' onchange='checkTotal()' id='chargeid_list_$id' name='chargeid_list[]' value='$id $fines2pay'></td>";
                                echo "</tr>";
                                $amountdue = $amountdue + (float)$fines2pay;
                                $n = $n + 1;
                            }
                        }
                        mysqli_stmt_close($stmtT);
                    ?>
                    </tbody>
                    </table>
                </div>
            </div>
            
            <div class="card mw-800 mb-3">
                <div class="card-header">
                    <strong>Payment Settlement Calculation</strong>
                    <span class="badge badge-danger">Total Due: <?php echo htmlspecialchars($currency_SHORT, ENT_QUOTES, 'UTF-8') . " " . number_format($amountdue, 2);?></span>
                </div>
                <div class="card-body">
                    <input type="hidden" name="pid" value="<?php echo $pid_param;?>">
                    
                    <div class="form-group">
                        <label class="form-label">Selected Amount to Pay (check items above):</label>
                        <input readonly type="text" name="f_amount" value="0.00"/>
                    </div>
                    
                    <?php if ($is_admin): ?>
                        <div class="form-group">
                            <label class="form-label">Waiver / Discount Amount:</label>
                            <div class="d-flex gap-2">
                                <input type="text" name="f_discount" value="0.00" onchange='changeFinalAmount()'/>
                                <input type="button" class="btn btn-secondary" name="Calc" value="Apply Discount" onclick='changeFinalAmount()'>
                            </div>
                        </div>
                    <?php else: ?>
                        <input type="hidden" name="f_discount" value="0.00" />
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label class="form-label">Final Amount to Pay:</label>
                        <input type="text" readonly name="f_finalamount" value="0.00"/>
                    </div>

                    <hr>

                    <div class="form-group">
                        <label class="form-label">Tendered / Received Amount:</label>
                        <div class="d-flex gap-2">
                            <input type="text" name="f_givenamount" value="0.00" onchange='changeBalance()'/>
                            <input type="button" class="btn btn-secondary" name="Calc" value="Calculate Balance" onclick='changeBalance()'>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Balance to Return:</label>
                        <input type="text" name="f_balance" readonly value="0.00"/>
                    </div>
                    
                    <input type="submit" class="btn btn-success w-100" name="f_button" value="Submit Payment" onclick="return confirm('Are you sure to charge the patron for: <?php echo htmlspecialchars($currency_SHORT, ENT_QUOTES, 'UTF-8');?> '+document.listForm.f_finalamount.value+<?php echo $is_admin ? "' (Discount: " . htmlspecialchars($currency_SHORT, ENT_QUOTES, 'UTF-8') . " '+document.listForm.f_discount.value+')?'" : "'?'"; ?>)">
                </div>
            </div>
        </form>
            
        <div class="text-center my-3">
            <?php
                if (isset($_SESSION['paysearch'])) {
                    echo "<a class='btn btn-secondary btn-sm' href=\"paysearch.php\">&larr; Search for other patron fines</a>";
                } else {
                    echo "<a class='btn btn-secondary btn-sm' href=\"userhistory.php?pid=" . $pid_param . "\">&larr; Back to user history page</a>";
                }
            ?>
        </div>
        
        <?php include_once '../includes/footerbar.php';?>
    </div>
</body>
</html>
