<?php
    session_start();define('includeExist', true);
    include_once '../includes/access_super.php';
    include_once '../config.php';
    include_once '../includes/functions.php';
    include_once '../includes/token_validate.php';

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $proceedAfterToken) {
        $typeid = (int)($_POST['typeid'] ?? 0);
        if (isset($_POST['submitted']) && $_POST['submitted'] == 'Enforce New' && is_numeric($_POST['fines_init']) && (int)$_POST['fines_init'] >= 0 && is_numeric($_POST['fines_initamount']) && (float)$_POST['fines_initamount'] >= 0 && is_numeric($_POST['fines_subsequenceamount']) && (float)$_POST['fines_subsequenceamount'] >= 0) {
            $fines_init = (int)$_POST['fines_init'];
            $fines_initamount = number_format((float)$_POST['fines_initamount'], 2, '.', '');
            $fines_subsequenceamount = number_format((float)$_POST['fines_subsequenceamount'], 2, '.', '');
                        
            $current_time = time();
            $stmt = mysqli_prepare($GLOBALS["conn"], "INSERT INTO eg_type_fines VALUES (NULL, ?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "iisss", $typeid, $fines_init, $fines_initamount, $fines_subsequenceamount, $current_time);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            $_SESSION['flash_fines_msg'] = ['type' => 'success', 'text' => 'New fines policy has been successfully enforced.'];
        } elseif (isset($_POST['submitted']) && $_POST['submitted'] == 'Enforce New') {
            $_SESSION['flash_fines_msg'] = ['type' => 'danger', 'text' => 'Please check your input values. Fines must be valid positive numbers.'];
        }
        header("Location: fines_days.php?typeid=" . $typeid);
        exit;
    }

    $typeid_param = isset($_REQUEST["typeid"]) && is_numeric($_REQUEST["typeid"]) ? (int)$_REQUEST["typeid"] : 0;
?>
<!DOCTYPE HTML>
<html lang='en'>

<head>
    <meta charset="utf-8">
    <title><?php echo htmlspecialchars($product_name, ENT_QUOTES, 'UTF-8');?> : Fines Policy Management</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="../<?php echo htmlspecialchars($mini_icon_path, ENT_QUOTES, 'UTF-8');?>" rel="icon" type="image/png" />
    <link href="../assets/styles/style.css" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="../assets/fontawesome/css/all.min.css">
</head>

<body>
    <?php include_once '../includes/loggedinfo.php';?>
    
    <div class="app-container">
        <?php
        if (!empty($_SESSION['flash_fines_msg'])) {
            $fmsg = $_SESSION['flash_fines_msg'];
            $ftype = htmlspecialchars($fmsg['type'] ?? 'info', ENT_QUOTES, 'UTF-8');
            $ftext = $fmsg['text'] ?? '';
            echo "<div class='alert alert-{$ftype}'>{$ftext}</div>";
            unset($_SESSION['flash_fines_msg']);
        }
        $csrfToken = htmlspecialchars($_SESSION['token'] ?? '', ENT_QUOTES, 'UTF-8');
        ?>
        
        <div class="card mb-3">
            <div class="card-header">
                <strong>Fines Policy Configuration: <?php echo htmlspecialchars(idToType($typeid_param), ENT_QUOTES, 'UTF-8');?></strong>
            </div>
            <div class="card-body">
                <div class="alert alert-info d-block mb-4">
                    <div class="d-flex align-items-center gap-2 mb-2 font-bold">
                        <i class="fa-solid fa-circle-info fa-lg"></i>
                        <span>Fines Policy Guide &amp; Terminology</span>
                    </div>
                    <ul class="mb-3 ps-3" style="color: inherit; line-height: 1.6; padding-left: 1.25rem;">
                        <li><strong>Number of days for initial fine:</strong> The initial period (in days) during which overdue items are charged at the base initial rate.</li>
                        <li><strong>Initial Fine Amount:</strong> The daily fine rate (<?php echo $currency_SHORT;?>/day) applied for each day within the initial period (Day 1 up to the configured number of days).</li>
                        <li><strong>Subsequent Fine Amount:</strong> The escalated daily fine rate (<?php echo $currency_SHORT;?>/day) applied for every subsequent day overdue once the initial period has passed.</li>
                    </ul>
                    <div class="p-2 rounded small" style="background: rgba(2, 132, 199, 0.08); border-left: 3px solid var(--info); line-height: 1.5;">
                        <strong>Calculation Example:</strong> If configured with <strong>7 days</strong> @ <strong><?php echo $currency_SHORT;?> 0.20/day</strong> initial and <strong><?php echo $currency_SHORT;?> 0.50/day</strong> subsequent:<br/>
                        &bull; <strong>5 days overdue:</strong> 5 days &times; <?php echo $currency_SHORT;?> 0.20 = <strong><?php echo $currency_SHORT;?> 1.00</strong><br/>
                        &bull; <strong>10 days overdue:</strong> (7 days &times; <?php echo $currency_SHORT;?> 0.20) + (3 days &times; <?php echo $currency_SHORT;?> 0.50) = <?php echo $currency_SHORT;?> 1.40 + <?php echo $currency_SHORT;?> 1.50 = <strong><?php echo $currency_SHORT;?> 2.90</strong>
                    </div>
                </div>

                <form action="fines_days.php" method="post" enctype="multipart/form-data">
                    <input type='hidden' name='token' value='<?php echo $csrfToken;?>'>
                    <input type='hidden' name='typeid' value='<?php echo $typeid_param;?>'>
                
                    <div class="form-group">
                        <label class="form-label">Number of days for initial fine:</label>
                        <select name="fines_init">
                            <option value="0">0</option>
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                            <option value="4">4</option>
                            <option value="5">5</option>
                            <option value="6">6</option>
                            <option value="7">7 (1 week)</option>
                            <option value="8">8</option>
                            <option value="9">9</option>
                            <option value="10">10</option>
                            <option value="11">11</option>
                            <option value="12">12</option>
                            <option value="13">13</option>
                            <option value="14">14 (2 weeks)</option>
                            <option value="15">15</option>
                            <option value="16">16</option>
                            <option value="17">17</option>
                            <option value="18">18</option>
                            <option value="19">19</option>
                            <option value="20">20</option>
                            <option value="21">21 (3 weeks)</option>
                            <option value="22">22</option>
                            <option value="23">23</option>
                            <option value="24">24</option>
                            <option value="25">25</option>
                            <option value="26">26</option>
                            <option value="27">27</option>
                            <option value="28">28 (4 weeks)</option>
                            <option value="29">29</option>
                            <option value="30">30 (1 month)</option>
                            <option value="60">60 (2 month)</option>
                        </select>
                    </div>
                
                    <div class="form-group">
                        <label class="form-label">Initial Fine Amount (<?php echo $currency_SHORT;?>/day):</label>
                        <input type="text" name="fines_initamount" maxlength="70" required />
                    </div>
                
                    <div class="form-group">
                        <label class="form-label">Subsequent Fine Amount (<?php echo $currency_SHORT;?>/day):</label>
                        <input type="text" name="fines_subsequenceamount" maxlength="70" required />
                    </div>

                    <input type="submit" class="btn btn-primary w-100" name="submitted" value="Enforce New" />
                </form>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">
                <strong>Fines History &amp; Policy Versions</strong>
            </div>
            <div class="card-body p-0">
                <table class="table-modern m-0">
                <thead>
                    <tr>
                        <th class="text-center w-auto">#</th>
                        <th class="text-center">Initial Fines (Days)</th>
                        <th class="text-center">Initial Amount (<?php echo $currency_SHORT;?>)</th>
                        <th class="text-center">Subsequent Amount (<?php echo $currency_SHORT;?>)</th>
                        <th class="text-center">Status</th>
                        <th>Enforced On</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                    $stmt = mysqli_prepare($GLOBALS["conn"], "SELECT * FROM eg_type_fines WHERE `38typeid` = ? ORDER BY id DESC");
                    mysqli_stmt_bind_param($stmt, "i", $typeid_param);
                    mysqli_stmt_execute($stmt);
                    $resultT = mysqli_stmt_get_result($stmt);
                    $n = 1;
                    while ($myrow=mysqli_fetch_array($resultT)) {
                        $typeid=$myrow["38typeid"];
                        $fines_initdays=$myrow["39fines_initdays"];
                        $fines_initamount=$myrow["39fines_initamount"];
                        $fines_subsequenceamount=$myrow["39fines_subsequenceamount"];
                        $enforced_on=$myrow["40enforcedon"];
                        
                        echo "<tr class='table-row'>";
                        echo "<td class='text-center'>$n</td>";
                        echo "<td class='text-center'>$fines_initdays days</td>";
                        echo "<td class='text-center font-bold'>$currency_SHORT $fines_initamount</td>";
                        echo "<td class='text-center font-bold'>$currency_SHORT $fines_subsequenceamount</td>";
                        echo "<td class='text-center'>";
                            if ($n == 1) {
                                echo "<span class='badge badge-success'>Enforced</span>";
                            } else {
                                echo "<span class='badge badge-secondary'>Archived</span>";
                            }
                        echo "</td>";
                        echo "<td>".date('D, Y-m-d h:i:s a', $enforced_on)."</td>";
                        echo "</tr>";
                        $n = $n + 1;
                    }
                ?>
                </tbody>
                </table>
            </div>
        </div>
        
        <div class="text-center my-3">
            <a class="btn btn-secondary btn-sm" href="../admin/addtype.php">&larr; Back to material types</a>
        </div>
        
        <?php include_once '../includes/footerbar.php';?>
    </div>
</body>
</html>
