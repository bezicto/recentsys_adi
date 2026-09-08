<?php
    session_start();define('includeExist', true);
    include_once '../includes/access_super.php';
    include_once '../config.php';
    include_once '../includes/token_validate.php';

    function render_loan_day_options($selected = 14) {
        $selected = is_numeric($selected) ? (int)$selected : 14;
        $options = array_merge(range(0, 30), [45, 60, 90, 120, 180, 365]);
        if (!in_array($selected, $options, true)) {
            $options[] = $selected;
            sort($options);
        }
        $html = '';
        foreach ($options as $val) {
            $sel = ($val === $selected) ? 'selected' : '';
            $label = ($val === 365) ? "365 days (1 year)" : "$val days";
            $html .= "<option value='$val' $sel>$label</option>";
        }
        return $html;
    }

    function render_loan_item_options($selected = 4) {
        $selected = is_numeric($selected) ? (int)$selected : 4;
        $options = array_merge(range(0, 30), [40, 50, 60, 75, 100]);
        if (!in_array($selected, $options, true)) {
            $options[] = $selected;
            sort($options);
        }
        $html = '';
        foreach ($options as $val) {
            $sel = ($val === $selected) ? 'selected' : '';
            $label = "$val items";
            $html .= "<option value='$val' $sel>$label</option>";
        }
        return $html;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $proceedAfterToken) {
        if (isset($_POST['submitted']) && $_POST['submitted'] == 'Enforce' && is_numeric($_POST["max_day"]) && is_numeric($_POST["max_loanitem"]) && !empty($_POST["usertype"])) {
            $usertype = $_POST["usertype"] ?? '';
            $usertypedesc = $_POST["usertypedesc"] ?? '';
            $max_day = (int)$_POST["max_day"];
            $max_loanitem = (int)$_POST["max_loanitem"];
            
            $stmt = mysqli_prepare($GLOBALS["conn"], "SELECT id FROM eg_auth_allowedloan WHERE usertype = ?");
            mysqli_stmt_bind_param($stmt, "s", $usertype);
            mysqli_stmt_execute($stmt);
            $resultid = mysqli_stmt_get_result($stmt);
            $num_results_affected = mysqli_num_rows($resultid);
            mysqli_stmt_close($stmt);
            
            if ($num_results_affected == 0) {
                $stmt = mysqli_prepare($GLOBALS["conn"], "INSERT INTO eg_auth_allowedloan VALUES (NULL, ?, ?, ?, ?)");
                mysqli_stmt_bind_param($stmt, "ssii", $usertype, $usertypedesc, $max_day, $max_loanitem);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
                $_SESSION['flash_loandays_msg'] = ['type' => 'success', 'text' => "New user type <strong>" . htmlspecialchars($usertype, ENT_QUOTES, 'UTF-8') . "</strong> has been created."];
            } elseif ($num_results_affected == 1) {
                $_SESSION['flash_loandays_msg'] = ['type' => 'danger', 'text' => 'Duplicate user type detected.'];
            }
        } elseif (isset($_POST['submitted']) && $_POST['submitted'] == 'Enforce' && (!is_numeric($_POST["max_day"] ?? '') || !is_numeric($_POST["max_loanitem"] ?? '') || empty($_POST["usertype"]))) {
            $_SESSION['flash_loandays_msg'] = ['type' => 'danger', 'text' => 'Please check your input values.'];
        }
        
        if (isset($_POST['submitted']) && $_POST['submitted'] == 'Update') {
            $usertype = $_POST["usertype"] ?? '';
            $max_day = (int)($_POST["max_day"] ?? 0);
            $max_loanitem = (int)($_POST["max_loanitem"] ?? 0);
            $usertypedesc = $_POST["usertypedesc"] ?? '';
            $id = (int)($_POST["id"] ?? 0);
            
            $stmt = mysqli_prepare($GLOBALS["conn"], "UPDATE eg_auth_allowedloan SET usertype=?, usertypedesc=?, max_day=?, max_loanitem=? WHERE id=?");
            mysqli_stmt_bind_param($stmt, "ssiii", $usertype, $usertypedesc, $max_day, $max_loanitem, $id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            $_SESSION['flash_loandays_msg'] = ['type' => 'success', 'text' => "User type <strong>" . htmlspecialchars($usertype, ENT_QUOTES, 'UTF-8') . "</strong> updated successfully."];
        }

        header("Location: chan_loandays.php");
        exit;
    }
?>
<!DOCTYPE HTML>

<html lang='en'>

<head>
    <meta charset="utf-8">
    <title><?php echo htmlspecialchars($product_name, ENT_QUOTES, 'UTF-8');?> : Loan Eligibility</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="../<?php echo htmlspecialchars($mini_icon_path, ENT_QUOTES, 'UTF-8');?>" rel="icon" type="image/png" />
    <link href="../assets/styles/style.css" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="../assets/fontawesome/css/all.min.css">
</head>

<body>
    <?php include_once '../includes/loggedinfo.php';?>
    
    <div class="app-container">
        <?php
            if (!empty($_SESSION['flash_loandays_msg'])) {
                $lmsg = $_SESSION['flash_loandays_msg'];
                $ltype = htmlspecialchars($lmsg['type'] ?? 'info', ENT_QUOTES, 'UTF-8');
                $ltext = $lmsg['text'] ?? '';
                echo "<div class='alert alert-{$ltype}'>{$ltext}</div>";
                unset($_SESSION['flash_loandays_msg']);
            }
            $csrfToken = htmlspecialchars($_SESSION['token'] ?? '', ENT_QUOTES, 'UTF-8');
            
            $max_dayA = ""; $max_loanitemA = "";
            if (isset($_GET['aid']) && is_numeric($_GET['aid'])) {
                $stmt = mysqli_prepare($GLOBALS["conn"], "SELECT * FROM eg_auth_allowedloan WHERE id = ?");
                mysqli_stmt_bind_param($stmt, "i", $_GET['aid']);
                mysqli_stmt_execute($stmt);
                $resultA = mysqli_stmt_get_result($stmt);
                while ($myrowA=mysqli_fetch_array($resultA)) {
                    $usertypeA=$myrowA["usertype"];
                    $usertypedescA=$myrowA["usertypedesc"];
                    $max_dayA=$myrowA["max_day"];
                    $max_loanitemA=$myrowA["max_loanitem"];
                }
                mysqli_stmt_close($stmt);
            ?>
        
            <div class="card mb-3">
                <div class="card-header">
                    <strong>Edit Loan Eligibility Status</strong>
                </div>
                <div class="card-body">
                    <form action="chan_loandays.php" method="post" enctype="multipart/form-data">
                        <div class="form-group">
                            <label class="form-label">User Type Code:</label>
                            <input type="text" readonly name="usertype" maxlength="70" value="<?php echo htmlspecialchars($usertypeA ?? '', ENT_QUOTES, 'UTF-8');?>"/>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">User Type Description:</label>
                            <input type="text" readonly name="usertypedesc" maxlength="70" value="<?php echo htmlspecialchars($usertypedescA ?? '', ENT_QUOTES, 'UTF-8');?>"/>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Total Days Allowed for Item Loan:</label>
                            <select name="max_day">
                                <?php echo render_loan_day_options($max_dayA); ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Total Number of Allowed Borrowed Items:</label>
                            <select name="max_loanitem">
                                <?php echo render_loan_item_options($max_loanitemA); ?>
                            </select>
                        </div>
                    
                        <input type="hidden" name="token" value="<?php echo $csrfToken; ?>" />
                        <input type="hidden" name="id" value="<?php echo (int)($_GET['aid'] ?? 0); ?>" />
                        <input type="hidden" name="aid" value="<?php echo (int)($_GET['aid'] ?? 0); ?>" />
                        <div class="d-flex gap-2">
                            <input type="submit" class="btn btn-primary w-100" name="submitted" value="Update" />
                            <input type="button" class="btn btn-secondary w-100" name="reset" value="Cancel" onclick="window.location='chan_loandays.php';"/>
                        </div>
                    </form>
                </div>
            </div>
            
        <?php } else { ?>

            <div class="card mb-3">
                <div class="card-header">
                    <strong>User Type Listing &amp; Loan Eligibility</strong>
                </div>
                <div class="card-body p-0">
                    <table class="table-modern m-0">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 50px;">#</th>
                            <th>User Type</th>
                            <th>Description</th>
                            <th class="text-center">Max Loan Duration</th>
                            <th class="text-center">Max Items</th>
                            <th class="text-center">Options</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                        $queryT = "select * from eg_auth_allowedloan";
                        $resultT = mysqli_query($GLOBALS["conn"], $queryT);
                        $n = 1;
                        while ($myrow=mysqli_fetch_array($resultT)) {
                            $id=$myrow["id"];
                            $usertypeT=$myrow["usertype"];
                            $usertypedescT=$myrow["usertypedesc"];
                            $max_dayT=$myrow["max_day"];
                            $max_loanitemT=$myrow["max_loanitem"];
                            echo "<tr class='table-row'>";
                                echo "<td class='text-center'>$n</td>";
                                echo "<td><span class='badge badge-primary'>$usertypeT</span></td>";
                                echo "<td>$usertypedescT</td>";
                                echo "<td class='text-center'>$max_dayT days</td>";
                                echo "<td class='text-center'>$max_loanitemT items</td>";
                                echo "<td class='text-center'><a class='btn btn-primary btn-sm' href='chan_loandays.php?aid=$id'>Edit</a></td>";
                            echo "</tr>";
                            $n = $n + 1;
                        }
                    ?>
                    </tbody>
                    </table>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header">
                    <strong>+ Add New User Type &amp; Loan Eligibility Tier</strong>
                </div>
                <div class="card-body">
                    <form action="chan_loandays.php" method="post" enctype="multipart/form-data">
                        <div class="row g-2">
                            <div class="col-md-6 form-group">
                                <label class="form-label">User Type Code (e.g. FACULTY, STUDENT, VISITOR):</label>
                                <input type="text" name="usertype" maxlength="50" required placeholder="e.g. POSTGRAD" />
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="form-label">User Type Description:</label>
                                <input type="text" name="usertypedesc" maxlength="100" placeholder="e.g. Postgraduate Students" />
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="form-label">Total Days Allowed for Item Loan:</label>
                                <select name="max_day">
                                    <?php echo render_loan_day_options(14); ?>
                                </select>
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="form-label">Total Number of Allowed Borrowed Items:</label>
                                <select name="max_loanitem">
                                    <?php echo render_loan_item_options(4); ?>
                                </select>
                            </div>
                        </div>
                        <input type="hidden" name="token" value="<?php echo $csrfToken; ?>" />
                        <div class="mt-3">
                            <input type="submit" class="btn btn-success w-100" name="submitted" value="Enforce" />
                        </div>
                    </form>
                </div>
            </div>
        <?php } ?>
        
        <div class="text-center my-3">
            <a class="btn btn-secondary btn-sm" href="../admin/chanuser.php">&larr; Back to users account page</a>
        </div>
        
        <?php include_once '../includes/footerbar.php';?>
    </div>
</body>
</html>
