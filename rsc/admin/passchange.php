<?php
    session_start();define('includeExist', true);
    if (isset($_SESSION["username"])) {
        include_once '../includes/access_isset.php';
    } elseif (isset($_SESSION["username_myacc"])) {
        include_once '../includes/access_isset_myacc.php';
    } else {
        echo "<!DOCTYPE HTML><html lang='en'><head><link href='../assets/styles/style.css' rel='stylesheet' type='text/css'></head><body><div class='auth-card'><span class='badge badge-danger mb-2'>FORBIDDEN</span><h2>Forbidden: Direct access prohibited</h2><em class='text-muted'>System Response Code</em></div></body></html>";
        exit;
    }
    include_once '../config.php';
    include_once '../includes/token_validate.php';

    $username3 = $_SESSION['username'] ?? $_SESSION["username_myacc"];

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST["submitted"]) && $proceedAfterToken) {
        $curpassword = $_POST["curpassword"] ?? '';
        $password4 = $_POST["password3"] ?? '';
        $password4a = $_POST["password3a"] ?? '';
        
        // Verify current password first
        $stmtCheck = mysqli_prepare($GLOBALS["conn"], "SELECT aes_decrypt(passphrase,'$ppaeskeysys') as password FROM eg_auth WHERE username = ?");
        mysqli_stmt_bind_param($stmtCheck, "s", $username3);
        mysqli_stmt_execute($stmtCheck);
        $resCheck = mysqli_stmt_get_result($stmtCheck);
        $rowCheck = mysqli_fetch_assoc($resCheck);
        mysqli_stmt_close($stmtCheck);

        $dbPassword = $rowCheck['password'] ?? '';

        if (empty($curpassword) || $curpassword !== $dbPassword) {
            $_SESSION['flash_pass_msg'] = ['type' => 'danger', 'text' => 'Current password verification failed. Please try again.'];
        } elseif (empty($password4)) {
            $_SESSION['flash_pass_msg'] = ['type' => 'danger', 'text' => 'New password cannot be empty. Please try again.'];
        } elseif ($password4 !== $password4a) {
            $_SESSION['flash_pass_msg'] = ['type' => 'danger', 'text' => 'Password confirmation does not match. Please try again.'];
        } else {
            $stmt = mysqli_prepare($GLOBALS["conn"], "UPDATE eg_auth SET passphrase=AES_ENCRYPT(?,'$ppaeskeysys') WHERE username=?");
            mysqli_stmt_bind_param($stmt, "ss", $password4, $username3);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            $_SESSION['flash_pass_msg'] = ['type' => 'success', 'text' => "Your password has been successfully updated. Click <a href='../index.php'>here</a> to log in with your new password."];
        }
        header("Location: passchange.php");
        exit;
    }
?>
<!DOCTYPE HTML>
<html lang='en'>

<head>
    <meta charset="utf-8">
    <title><?php echo htmlspecialchars($product_name, ENT_QUOTES, 'UTF-8');?> : Password Control</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="../<?php echo htmlspecialchars($mini_icon_path, ENT_QUOTES, 'UTF-8');?>" rel="icon" type="image/png" />
    <link href="../assets/styles/style.css" rel="stylesheet" type="text/css">
</head>

<body>
    <?php include_once '../includes/loggedinfo.php'; ?>
    
    <div class="app-container">
        <?php
            if (!empty($_SESSION['flash_pass_msg'])) {
                $pmsg = $_SESSION['flash_pass_msg'];
                $ptype = htmlspecialchars($pmsg['type'] ?? 'info', ENT_QUOTES, 'UTF-8');
                $ptext = $pmsg['text'] ?? '';
                echo "<div class='alert alert-{$ptype}'>{$ptext}</div>";
                unset($_SESSION['flash_pass_msg']);
            }
        ?>
        
            <div class="card mw-800 mb-3">
                <div class="card-header">
                    <strong>Change Password: <?php echo htmlspecialchars($username3, ENT_QUOTES, 'UTF-8');?></strong>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">Please input your current password and your new password confirmation:</p>
                    <form action="passchange.php" method="post">
                        <div class="form-group">
                            <label class="form-label">Current Password:</label>
                            <input type="password" name="curpassword" maxlength="40" placeholder="Enter current password" required />
                        </div>

                        <div class="form-group">
                            <label class="form-label">New Password:</label>
                            <input type="password" name="password3" maxlength="40" placeholder="Enter new password" required />
                            <small class="text-muted">Enter a new secure password</small>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Confirm New Password:</label>
                            <input type="password" name="password3a" maxlength="40" placeholder="Re-enter new password" required />
                            <small class="text-muted">Re-enter the exact same password</small>
                        </div>

                        <input type="hidden" name="token" value="<?php echo htmlspecialchars($_SESSION['token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" />
                        <input type="hidden" name="submitted" value="TRUE" />
                        <input type="submit" class="btn btn-primary w-100" name="Submit1" value="Update Password"/>
                    </form>
                </div>
            </div>
                                        
        <div class="text-center my-3">
            <?php
                if (isset($_SESSION["username_myacc"])) {
                    $backUrl = "../logged.php";
                    $backLabel = "Back to My Account";
                } else {
                    $backUrl = "../index2.php";
                    $backLabel = "Back to Start Page";
                }
            ?>
            <a class="btn btn-secondary btn-sm" href="<?php echo htmlspecialchars($backUrl, ENT_QUOTES, 'UTF-8');?>">&larr; <?php echo htmlspecialchars($backLabel, ENT_QUOTES, 'UTF-8');?></a>
        </div>
            
        <?php include_once '../includes/footerbar.php';?>
    </div>
</body>
</html>
