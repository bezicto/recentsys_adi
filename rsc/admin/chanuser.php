<?php
    session_start();define('includeExist', true);
    include_once '../includes/access_super.php';
    include_once '../config.php';
    include_once '../includes/functions.php';
    include_once '../includes/token_validate.php';

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST["action"]) && $proceedAfterToken) {
        $target_id = isset($_POST["target_id"]) && is_numeric($_POST["target_id"]) ? (int)$_POST["target_id"] : 0;
        $action = $_POST["action"];

        if ($target_id > 0) {
            if ($action === 'deactivate') {
                $stmt = mysqli_prepare($GLOBALS["conn"], "UPDATE eg_auth SET allowed='FALSE', devmode='NO' WHERE id=? AND username != 'admin'");
                mysqli_stmt_bind_param($stmt, "i", $target_id);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
                $_SESSION['flash_chanuser_msg'] = ['type' => 'warning', 'text' => "User ID #$target_id has been deactivated."];
            } elseif ($action === 'reset_pwd') {
                $stmt = mysqli_prepare($GLOBALS["conn"], "UPDATE eg_auth SET passphrase=AES_ENCRYPT('1','$ppaeskeysys') WHERE id=?");
                mysqli_stmt_bind_param($stmt, "i", $target_id);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
                $_SESSION['flash_chanuser_msg'] = ['type' => 'info', 'text' => "Password for User ID #$target_id was reset to default."];
            } elseif ($action === 'set_offline') {
                $stmt = mysqli_prepare($GLOBALS["conn"], "UPDATE eg_auth SET online='OFF' WHERE id=?");
                mysqli_stmt_bind_param($stmt, "i", $target_id);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
                $_SESSION['flash_chanuser_msg'] = ['type' => 'info', 'text' => "User ID #$target_id was set to offline."];
            }
        }
        header("Location: chanuser.php");
        exit;
    }
?>
<!DOCTYPE HTML>
<html lang='en'>

<head>
    <meta charset="utf-8">
    <title><?php echo htmlspecialchars($product_name, ENT_QUOTES, 'UTF-8');?> : User Accounts Management</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="../assets/styles/style.css" rel="stylesheet" type="text/css">
    <link href="../<?php echo htmlspecialchars($mini_icon_path, ENT_QUOTES, 'UTF-8');?>" rel="icon" type="image/png" />
</head>

<body>
    <?php include_once '../includes/loggedinfo.php';?>
    
    <div class="app-container">
        <?php
            if (!empty($_SESSION['flash_chanuser_msg'])) {
                $cmsg = $_SESSION['flash_chanuser_msg'];
                $ctype = htmlspecialchars($cmsg['type'] ?? 'info', ENT_QUOTES, 'UTF-8');
                $ctext = $cmsg['text'] ?? '';
                echo "<div class='alert alert-{$ctype}'>" . htmlspecialchars($ctext, ENT_QUOTES, 'UTF-8') . "</div>";
                unset($_SESSION['flash_chanuser_msg']);
            }
        ?>

        <div class="card mb-3">
            <div class="card-header">
                <?php
                    $query1 = "select id, username, allowed, name, division, online, devmode, lastlogin from eg_auth order by id";
                    $result1 = mysqli_query($GLOBALS["conn"], $query1);
                    $num_results_affected = $result1 ? mysqli_num_rows($result1) : 0;
                ?>
                <div>
                    <strong>User Accounts Management</strong>
                    <span class="badge badge-secondary"><?php echo (int)$num_results_affected;?> users</span>
                </div>
                <div class="d-flex gap-2">
                    <a class="btn btn-primary btn-sm" href="qr_selfreg.php" title="Display QR code for walk-in patron self-registration">
                        <i class="fa-solid fa-qrcode me-1"></i> Own Registration With QR
                    </a>
                    <a class="btn btn-secondary btn-sm" href="../admin/qr_printrange.php" title="Print barcode range for membership">
                        <i class="fa-solid fa-barcode me-1"></i> Print Barcodes
                    </a>
                    <a class="btn btn-secondary btn-sm" href="../admin/chan_loandays.php" title="Add/Edit Types and Eligibility">
                        <i class="fa-solid fa-calendar-check me-1"></i> Loan Eligibility
                    </a>
                    <a class="btn btn-success btn-sm" href="../admin/adduser.php" title="Add new system user">
                        <i class="fa-solid fa-user-plus me-1"></i> Add User
                    </a>
                </div>
            </div>
            <div class="card-body p-0">
                <table class="table-modern m-0">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 50px;">#</th>
                        <th>IC / Username</th>
                        <th>Full Name</th>
                        <th class="text-center">Account Role</th>
                        <th class="text-center">Actions</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                    $n = 1;
                    $csrfToken = htmlspecialchars($_SESSION['token'] ?? '', ENT_QUOTES, 'UTF-8');
                    while ($result1 && $myrow = mysqli_fetch_array($result1)) {
                        $username2 = $myrow["username"] ?? '';
                        $allowed2 = $myrow["allowed"] ?? '';
                        $name2 = $myrow["name"] ?? '';
                        $online2 = $myrow["online"] ?? 'OFF';
                        $id2 = (int)$myrow["id"];
                        $lastlogin2 = $myrow["lastlogin"] ?? '';
                        
                        $safe_user = htmlspecialchars($username2, ENT_QUOTES, 'UTF-8');
                        $safe_name = htmlspecialchars($name2, ENT_QUOTES, 'UTF-8');
                        $safe_role = htmlspecialchars($allowed2, ENT_QUOTES, 'UTF-8');
                        $js_confirm_reset = htmlspecialchars("Are you sure to reset the password for user '" . addslashes($username2) . "' to default ('1')?", ENT_QUOTES, 'UTF-8');
                        $js_confirm_deact = htmlspecialchars("Are you sure to deactivate user '" . addslashes($username2) . "'?", ENT_QUOTES, 'UTF-8');
                        $js_confirm_offline = htmlspecialchars("Are you sure to set user '" . addslashes($username2) . "' to offline?", ENT_QUOTES, 'UTF-8');

                        echo "<tr class='table-row'>";
                            echo "<td class='text-center'>$n</td>";
                            echo "<td><a href='adduser.php?edt=$id2' class='font-bold'>$safe_user</a> <small class='text-muted'>(#$id2)</small></td>";
                            echo "<td>$safe_name</td>";
                            echo "<td class='text-center'><span class='badge badge-primary'>$safe_role</span></td>";
                            echo "<td class='text-center'>";
                                echo "<div class='d-flex gap-1 justify-center align-items-center flex-wrap'>";
                                if ($username2 != 'admin') {
                                    echo "<form method='post' action='chanuser.php' style='display:inline;' onsubmit=\"return confirm('$js_confirm_deact');\">";
                                    echo "<input type='hidden' name='token' value='$csrfToken'>";
                                    echo "<input type='hidden' name='action' value='deactivate'>";
                                    echo "<input type='hidden' name='target_id' value='$id2'>";
                                    echo "<button type='submit' class='btn btn-danger btn-sm'>Deactivate</button>";
                                    echo "</form> ";
                                }
                                echo "<form method='post' action='chanuser.php' style='display:inline;' onsubmit=\"return confirm('$js_confirm_reset');\">";
                                echo "<input type='hidden' name='token' value='$csrfToken'>";
                                echo "<input type='hidden' name='action' value='reset_pwd'>";
                                echo "<input type='hidden' name='target_id' value='$id2'>";
                                echo "<button type='submit' class='btn btn-warning btn-sm'>Reset Pwd</button>";
                                echo "</form> ";
                                
                                echo "<a class='btn btn-secondary btn-sm' href='userhistory.php?pid=$id2'>History</a> ";
                                echo "<a class='btn btn-primary btn-sm' href='../patron_card.php?pid=$id2' title='View digital library card'><i class='fa-solid fa-id-card'></i> Card</a> ";
                                echo "<a class='btn btn-secondary btn-sm' target='_blank' href='qr_membership.php?pid=$id2'>QR</a>";
                                echo "</div>";
                            echo "</td>";
                            
                            echo "<td class='text-center'>";
                            if ($online2 == 'ON') {
                                echo "<span class='badge badge-success mb-1'>ONLINE</span><br/>";
                                echo "<form method='post' action='chanuser.php' style='display:inline;' onsubmit=\"return confirm('$js_confirm_offline');\">";
                                echo "<input type='hidden' name='token' value='$csrfToken'>";
                                echo "<input type='hidden' name='action' value='set_offline'>";
                                echo "<input type='hidden' name='target_id' value='$id2'>";
                                echo "<button type='submit' class='btn btn-secondary btn-sm mt-1'>Set Offline</button>";
                                echo "</form><br/>";
                                echo "<small class='text-muted'>" . countOnlineDuration(date("D d/m/Y h:i a"), $lastlogin2) . "</small>";
                            } else {
                                echo "<span class='badge badge-secondary'>OFFLINE</span>";
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
</body>
</html>
