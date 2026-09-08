<!DOCTYPE HTML>
<?php
    session_start();define('includeExist', true);
    include_once '../includes/access_super.php';
    include_once '../config.php';
    include_once '../includes/functions.php';
?>

<html lang='en'>

<head>
    <meta charset="utf-8">
    <title><?php echo $product_name;?> : Patron Charge/Discharge History</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="../<?php echo $mini_icon_path;?>" rel="icon" type="image/png" />
    <link href="../assets/styles/style.css" rel="stylesheet" type="text/css">
</head>

<body>
    <?php include_once '../includes/loggedinfo.php';?>
    
    <div class="app-container">
        <div class="card mb-3">
            <div class="card-header">
                <strong>Loan &amp; Discharge History: <?php echo namePatron($_REQUEST["pid"]);?></strong>
            </div>
            <div class="card-body p-0">
                <table class="table-modern m-0">
                <thead>
                    <tr>
                        <th class="text-center w-auto">#</th>
                        <th>Title / Accession</th>
                        <th>Charged On</th>
                        <th>Due Date</th>
                        <th class="text-center">Renewals</th>
                        <th>Discharged On</th>
                        <th class="text-center">Overdue?</th>
                        <th class="text-center">Fines</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                    $patron_user = patronIdToUsername($_REQUEST["pid"] ?? 0);
                    $stmtT = mysqli_prepare($GLOBALS["conn"], "SELECT * FROM eg_item_charge WHERE `39patron`=?");
                    mysqli_stmt_bind_param($stmtT, "s", $patron_user);
                    mysqli_stmt_execute($stmtT);
                    $resultT = mysqli_stmt_get_result($stmtT);
                    $n = 1;
                    $beforepaymenton = "0000";
                    $beforepaymentrcv = "0000";
                    while ($myrow=mysqli_fetch_array($resultT)) {
                        $patron=$myrow["39patron"];
                        $accessnum=$myrow["39accessnum"];
                        $charged_on=$myrow["39charged_on"];
                        $dc_on=$myrow["40dc_on"];
                        $dc_enforcedfine=$myrow["40dc_enforcedfine"];
                        $duedate_mp=$myrow["39duedate"];
                        $received_amount=$myrow["41f_received_amount"];
                        $discount_given=$myrow["41f_discount_amount"];
                        $paidon=$myrow["41f_paidon"];
                        $paidrb=$myrow["41f_received"];
                        $paid_rid=$myrow["41f_receipt_id"];
                        
                        if ($myrow["41f_pay"] == 'YES') {
                            $f_pay="<span class='badge badge-success'>PAID</span>";
                        } else {
                            $f_pay="<a class='btn btn-primary btn-sm' href='payhistory.php?pid=".$_REQUEST["pid"]."'>Pay Fine</a>";
                        }
                        
                        $maxSecond = (maxday($patron)*($duedate_mp+1))*86400;//for calculating due date
                        $beginOfDay_for_charged_on = strtotime("midnight", $charged_on);// start of day for the charged day
                        $duedate = $beginOfDay_for_charged_on + $maxSecond + 86399;// + 86399 to reach midnight
                        $duedate = shiftDueDate($duedate);//shifting duedate if holiday

                        if ($dc_on <> null && $dc_on != 0) {
                            $overdue_days = calculateOverdueDays($dc_on, $duedate);
                        } else {
                            $overdue_days = calculateOverdueDays(time(), $duedate);
                        }
                        
                        echo "<tr class='table-row'>";
                        echo "<td class='text-center'>$n</td>";
                        echo "<td><strong>".getTitle($accessnum)."</strong><br/><small class='text-muted'>$accessnum</small></td>";
                        echo "<td><small>".date('D, Y-m-d h:i:s a', $charged_on)."</small></td>";
                        echo "<td><small>".date('D, Y-m-d h:i:s a', $duedate)."</small></td>";
                        echo "<td class='text-center'>$duedate_mp</td>";
                        if ($dc_on != '') {
                            echo "<td><small>".date('D, Y-m-d h:i:s a', $dc_on)."</small></td>";
                        } else {
                            echo "<td class='text-center text-muted'>-</td>";
                        }

                        echo "<td class='text-center'>";
                        if ($dc_on != '' && $dc_on != null && $dc_on != 0) {
                            if ($overdue_days <= 0) {
                                echo "<span class='badge badge-success'>No</span>";
                            } else {
                                echo "<span class='badge badge-danger'>" . (int)$overdue_days . " days</span>";
                            }
                        } else {
                            if ($overdue_days > 0) {
                                echo "<span class='badge badge-danger'>" . (int)$overdue_days . " days</span>";
                            } else {
                                echo "<span class='badge badge-secondary'>N/A</span>";
                            }
                        }
                        echo "</td>";
                        
                        echo "<td class='text-center'>";
                            if ($myrow["41f_pay"] == 'YES') {
                                if ($beforepaymenton == $paidon && $beforepaymentrcv == $paidrb) {
                                    echo "<small class='text-muted'>Paid as above<br/>Receipt: $paid_rid</small>";
                                } else {
                                    echo "<strong>$currency_SHORT $received_amount</strong> $f_pay";
                                    if ($discount_given > 0.00) {
                                        echo "<br/><small class='text-muted'>Discount: $currency_SHORT $discount_given | Receipt: $paid_rid</small>";
                                    }
                                }
                            } else {
                                if ($overdue_days > 0 && $dc_on != null && $dc_on != 0) {
                                    $fine_val = calculatedFines($overdue_days, $accessnum, $dc_enforcedfine);
                                    echo "<strong>$currency_SHORT " . htmlspecialchars(number_format((float)$fine_val, 2, '.', ''), ENT_QUOTES, 'UTF-8') . "</strong><br/>$f_pay";
                                } else {
                                    echo "-";
                                }
                            }
                        echo "</td>";
                        echo "</tr>";
                        $beforepaymenton = $paidon;
                        $beforepaymentrcv = $paidrb;
                        $n = $n + 1;
                    }
                    mysqli_stmt_close($stmtT);
                ?>
                </tbody>
                </table>
            </div>
        </div>
            
        <div class="text-center my-3">
            <a class="btn btn-secondary btn-sm" href="chanuser.php">&larr; Back to user account page</a>
        </div>
        
        <?php include_once '../includes/footerbar.php';?>
    </div>
</body>
</html>
