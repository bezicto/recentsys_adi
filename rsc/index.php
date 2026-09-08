<?php
    session_start();
    define('includeExist', true);
    include_once 'config.php';
    include_once 'includes/ip_guard.php';
    
    if (isset($_SESSION['username'])) {
        $tempusername = $_SESSION['username'];
        $stmt = mysqli_prepare($GLOBALS["conn"], "UPDATE eg_auth SET online='OFF' WHERE username=?");
        mysqli_stmt_bind_param($stmt, "s", $tempusername);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        unset($_SESSION['username']);
        unset($_SESSION['fullname']);
        unset($_SESSION['editmode']);
        unset($_SESSION['lastlogin']);
        unset($_SESSION['ref']);
    } elseif (isset($_SESSION['username_myacc'])) {
        $tempusername = $_SESSION['username_myacc'];
        $stmt = mysqli_prepare($GLOBALS["conn"], "UPDATE eg_auth SET online='OFF' WHERE username=?");
        mysqli_stmt_bind_param($stmt, "s", $tempusername);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        unset($_SESSION['username_myacc']);
        unset($_SESSION['lastlogin']);
    }
    
    if (isset($_REQUEST['submitted']) && $_REQUEST['submitted'] == 'Enter OPAC') {
        header("Location: opac.php");
        exit;
    } elseif (isset($_REQUEST['submitted']) && $_REQUEST['submitted'] == 'My Account Portal') {
        header("Location: index.php?modal=patron");
        exit;
    }

    $datelog = date("D d/m/Y h:i a");
    $ip_status = ip_guard_check_status();

    //preventing CSRF
    include_once 'includes/token_validate.php';

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submitted']) && $_POST['submitted'] == 'TRUE' && $proceedAfterToken) {
        $login_type = $_POST['login_type'] ?? 'staff';
        $client_ip = ip_guard_get_client_ip();

        if ($login_type === 'patron') {
            // Check if IP is currently blocked
            if ($ip_status['is_blocked']) {
                $_SESSION['flash_patron_login_error'] = "Access blocked: Your IP address ($client_ip) is locked for 1 week due to excessive failed attempts. " . ip_guard_format_time_left($ip_status['remaining_seconds']) . " left.";
                header("Location: index.php?modal=patron");
                exit;
            }

            $input_user = $_POST['username'] ?? '';
            $input_pass = $_POST['password'] ?? '';

            $stmt = mysqli_prepare($GLOBALS["conn"], "SELECT id, username, aes_decrypt(passphrase,'$ppaeskeysys') as password, allowed, lastlogin, online, devmode FROM eg_auth WHERE username=?");
            mysqli_stmt_bind_param($stmt, "s", $input_user);
            mysqli_stmt_execute($stmt);
            $result_login = mysqli_stmt_get_result($stmt);
            $myrow = mysqli_fetch_assoc($result_login);
            mysqli_stmt_close($stmt);

            if ($myrow) {
                $id2 = $myrow["id"];
                $username2 = $myrow["username"];
                $password2 = $myrow["password"];
                $date2 = $myrow["lastlogin"];

                if ($input_user === $username2 && $input_pass === $password2) {
                    if (($myrow['allowed'] ?? '') === 'FALSE') {
                        $_SESSION['flash_patron_login_error'] = "Your account has been deactivated. Please consult the library administrator.";
                        header("Location: index.php?modal=patron");
                        exit;
                    }

                    // Reset failed attempts on success
                    ip_guard_record_success($client_ip);

                    session_regenerate_id(true);
                    $_SESSION['username_myacc'] = $input_user;
                    $_SESSION['lastlogin'] = $date2;
                    $stmt = mysqli_prepare($GLOBALS["conn"], "UPDATE eg_auth SET lastlogin=?, online='ON' WHERE id=?");
                    mysqli_stmt_bind_param($stmt, "si", $datelog, $id2);
                    mysqli_stmt_execute($stmt);
                    mysqli_stmt_close($stmt);
                    header("Location: logged.php");
                    exit;
                } else {
                    $guard_result = ip_guard_record_failure($client_ip, 'patron', $input_user);
                    if ($guard_result['is_blocked']) {
                        $_SESSION['flash_patron_login_error'] = "Your IP address ($client_ip) has been blocked for 1 week due to 3 consecutive failed login attempts. Please contact the administrator.";
                    } else {
                        $rem = $guard_result['remaining_attempts'];
                        $_SESSION['flash_patron_login_error'] = "Incorrect authentication information detected! ($rem attempt" . ($rem == 1 ? '' : 's') . " remaining before 1-week IP lockout)";
                    }
                }
            } else {
                $guard_result = ip_guard_record_failure($client_ip, 'patron', $input_user);
                if ($guard_result['is_blocked']) {
                    $_SESSION['flash_patron_login_error'] = "Your IP address ($client_ip) has been blocked for 1 week due to 3 consecutive failed login attempts. Please contact the administrator.";
                } else {
                    $rem = $guard_result['remaining_attempts'];
                    $_SESSION['flash_patron_login_error'] = "Cannot find username! ($rem attempt" . ($rem == 1 ? '' : 's') . " remaining before 1-week IP lockout)";
                }
            }
            header("Location: index.php?modal=patron");
            exit;
        } else {
            // Staff Login Processing
            if ($ip_status['is_blocked']) {
                $_SESSION['flash_login_error'] = "Access blocked: Your IP address ($client_ip) is locked for 1 week due to excessive failed attempts. " . ip_guard_format_time_left($ip_status['remaining_seconds']) . " left.";
                header("Location: index.php?modal=staff");
                exit;
            }

            $input_user = $_POST['username'] ?? '';
            $input_pass = $_POST['password'] ?? '';

            $stmt = mysqli_prepare($GLOBALS["conn"], "SELECT id, username, name, aes_decrypt(passphrase,'$ppaeskeysys') as password, allowed, lastlogin, online, devmode FROM eg_auth WHERE username=?");
            mysqli_stmt_bind_param($stmt, "s", $input_user);
            mysqli_stmt_execute($stmt);
            $result_login = mysqli_stmt_get_result($stmt);
            $myrow = mysqli_fetch_assoc($result_login);
            mysqli_stmt_close($stmt);
            
            if ($myrow) {
                $id2 = $myrow["id"];
                $username2 = $myrow["username"];
                $fullname2 = $myrow["name"];
                $password2 = $myrow["password"];
                $allowed2 = $myrow["allowed"];
                $date2 = $myrow["lastlogin"];

                $allowed3 = ($allowed2 == 'TRUE' || $allowed2 == 'SUPER') ? 'TRUE' : 'FALSE';

                if ($input_user === $username2 && $input_pass === $password2 && $allowed3 === 'TRUE') {
                    // Clear failed attempts upon success
                    ip_guard_record_success($client_ip);

                    session_regenerate_id(true);
                    $_SESSION['username'] = $input_user;
                    $_SESSION['fullname'] = $fullname2;
                    $_SESSION['editmode'] = $allowed2;
                    $_SESSION['lastlogin'] = $date2;
                    $stmt = mysqli_prepare($GLOBALS["conn"], "UPDATE eg_auth SET lastlogin=?, online='ON' WHERE id=?");
                    mysqli_stmt_bind_param($stmt, "si", $datelog, $id2);
                    mysqli_stmt_execute($stmt);
                    mysqli_stmt_close($stmt);
                    header("Location: index2.php");
                    exit;
                } elseif ($input_user === $username2 && $input_pass === $password2 && $allowed3 === 'FALSE') {
                    $guard_result = ip_guard_record_failure($client_ip, 'staff', $input_user);
                    if ($guard_result['is_blocked']) {
                        $_SESSION['flash_login_error'] = "Your IP address ($client_ip) has been blocked for 1 week due to 3 consecutive failed login attempts.";
                    } else {
                        $rem = $guard_result['remaining_attempts'];
                        $_SESSION['flash_login_error'] = "Inactive or patron account detected! You're not permitted to enter the staff workstation. ($rem attempt" . ($rem == 1 ? '' : 's') . " remaining before lockout)";
                    }
                } else {
                    $guard_result = ip_guard_record_failure($client_ip, 'staff', $input_user);
                    if ($guard_result['is_blocked']) {
                        $_SESSION['flash_login_error'] = "Your IP address ($client_ip) has been blocked for 1 week due to 3 consecutive failed login attempts. Please contact the administrator.";
                    } else {
                        $rem = $guard_result['remaining_attempts'];
                        $_SESSION['flash_login_error'] = "Incorrect authentication information detected! ($rem attempt" . ($rem == 1 ? '' : 's') . " remaining before 1-week IP lockout)";
                    }
                }
            } else {
                $guard_result = ip_guard_record_failure($client_ip, 'staff', $input_user);
                if ($guard_result['is_blocked']) {
                    $_SESSION['flash_login_error'] = "Your IP address ($client_ip) has been blocked for 1 week due to 3 consecutive failed login attempts. Please contact the administrator.";
                } else {
                    $rem = $guard_result['remaining_attempts'];
                    $_SESSION['flash_login_error'] = "Cannot find username! ($rem attempt" . ($rem == 1 ? '' : 's') . " remaining before 1-week IP lockout)";
                }
            }
            header("Location: index.php?modal=staff");
            exit;
        }
    }
?>
<!DOCTYPE HTML>

<html lang='en'>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $product_name;?> | Library Portal</title>
    <link href="<?php echo $mini_icon_path;?>" rel="icon" type="image/png" />
    <link href="./assets/styles/style.css" rel="stylesheet" type="text/css">
    <script type="text/javascript">
    function openPatronModal() {
        closeStaffModal();
        var modal = document.getElementById("patronModal");
        if (modal) {
            modal.classList.add("show");
            document.body.style.overflow = "hidden";
            var uInput = modal.querySelector('input[name="username"]');
            if (uInput && !uInput.disabled) {
                setTimeout(function() { uInput.focus(); }, 150);
            }
        }
    }

    function closePatronModal() {
        var modal = document.getElementById("patronModal");
        if (modal) {
            modal.classList.remove("show");
            document.body.style.overflow = "";
        }
    }

    function openStaffModal() {
        closePatronModal();
        var modal = document.getElementById("staffModal");
        if (modal) {
            modal.classList.add("show");
            document.body.style.overflow = "hidden";
            var uInput = modal.querySelector('input[name="username"]');
            if (uInput && !uInput.disabled) {
                setTimeout(function() { uInput.focus(); }, 150);
            }
        }
    }

    function closeStaffModal() {
        var modal = document.getElementById("staffModal");
        if (modal) {
            modal.classList.remove("show");
            document.body.style.overflow = "";
        }
    }

    document.addEventListener("keydown", function(e) {
        if (e.key === "Escape") {
            closePatronModal();
            closeStaffModal();
        }
    });

    <?php 
        $open_patron = (isset($_GET['modal']) && $_GET['modal'] === 'patron') || isset($_GET['patron_login']) || !empty($_SESSION['flash_patron_login_error']);
        $open_staff = (isset($_GET['modal']) && $_GET['modal'] === 'staff') || isset($_GET['login_failed']) || !empty($_SESSION['flash_login_error']);
    ?>
    document.addEventListener("DOMContentLoaded", function() {
        <?php if ($open_patron): ?>
            openPatronModal();
        <?php elseif ($open_staff): ?>
            openStaffModal();
        <?php else: ?>
            if (window.location.hash === '#patronModal') {
                openPatronModal();
            } else if (window.location.hash === '#staffModal') {
                openStaffModal();
            }
        <?php endif; ?>
    });
    </script>
</head>

<body>
    <div class="container-narrow">
        <div class="portal-hero">
            <img class="app-logo mb-2" width="340px" src="<?php echo $logo_image_path;?>" alt="<?php echo $product_name;?>"/>
            <p class="lead-text text-muted mb-4"><?php echo $product_slogan ?? 'Library Resource &amp; Management System';?></p>
            
            <div class="portal-content-wrap">
                <div class="portal-tiles">
                    <a href="opac.php" class="portal-tile portal-tile-opac">
                        <div class="portal-tile-icon">
                            <i class="fa-solid fa-book-open-reader"></i>
                        </div>
                        <div class="portal-tile-title">Guest OPAC</div>
                        <div class="portal-tile-desc">Search public catalog, browse titles, view copy availability &amp; call numbers</div>
                        <div class="portal-tile-badge">Public Catalog &rarr;</div>
                    </a>

                    <button type="button" onclick="openPatronModal()" class="portal-tile portal-tile-patron">
                        <div class="portal-tile-icon">
                            <i class="fa-solid fa-user-check"></i>
                        </div>
                        <div class="portal-tile-title">My Account</div>
                        <div class="portal-tile-desc">Patron self-service portal to view on-loan materials, loan history &amp; overdue fines</div>
                        <div class="portal-tile-badge">Patron Access &rarr;</div>
                    </button>
                </div>

                <div class="portal-secondary">
                    <button type="button" onclick="openStaffModal()" class="portal-staff-row">
                        <div class="portal-staff-icon" style="<?php echo $ip_status['is_blocked'] ? 'background:#fef2f2; color:#dc2626;' : '';?>">
                            <i class="fa-solid <?php echo $ip_status['is_blocked'] ? 'fa-ban' : 'fa-shield-halved';?>"></i>
                        </div>
                        <div class="portal-staff-info">
                            <div class="portal-staff-title">Staff Portal</div>
                            <div class="portal-staff-desc">Administrative access for circulation desk, MARC cataloging &amp; reports</div>
                        </div>
                        <div class="portal-staff-action">
                            <span class="portal-staff-badge"><?php echo $ip_status['is_blocked'] ? 'IP Locked' : 'Staff Login &rarr;';?></span>
                        </div>
                    </button>
                </div>
            </div>

            <?php include_once './includes/footerbar.php';?>
        </div>
    </div>

    <!-- Patron Portal Login Modal Dialog -->
    <div id="patronModal" class="modal-backdrop" onclick="if(event.target===this) closePatronModal();">
        <div class="modal-dialog modal-sm">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fa-solid <?php echo $ip_status['is_blocked'] ? 'fa-ban text-danger' : 'fa-user-check text-primary';?> me-2"></i>
                    My Account Login
                </h5>
                <button type="button" class="modal-close-btn" onclick="closePatronModal();" title="Close dialog">&times;</button>
            </div>
            <div class="modal-body">
                <?php if ($ip_status['is_blocked']): ?>
                    <div class="lockout-banner">
                        <div class="lockout-banner-header">
                            <i class="fa-solid fa-shield-halved fa-lg"></i>
                            <span>Access Blocked (1 Week)</span>
                        </div>
                        <div class="lockout-banner-body">
                            Your IP address (<code><?php echo htmlspecialchars(ip_guard_get_client_ip(), ENT_QUOTES, 'UTF-8');?></code>) has been temporarily locked out due to 3 consecutive failed login attempts.
                        </div>
                        <div class="lockout-banner-footer">
                            <i class="fa-regular fa-clock me-1"></i>
                            <span>Expires in <strong><?php echo ip_guard_format_time_left($ip_status['remaining_seconds']);?></strong> &bull; <?php echo date('d M Y, h:i A', $ip_status['expires_at']);?></span>
                        </div>
                    </div>
                <?php else: ?>
                    <p class="text-muted small mb-3">Sign in with your member credentials to view loans, borrowing history, and fines.</p>
                <?php endif; ?>

                <?php
                    if (!empty($_SESSION['flash_patron_login_error'])) {
                        echo "<div class='alert alert-danger mb-3'><i class='fa-solid fa-circle-exclamation me-1'></i> " . htmlspecialchars($_SESSION['flash_patron_login_error'], ENT_QUOTES, 'UTF-8') . "</div>";
                        unset($_SESSION['flash_patron_login_error']);
                    }
                ?>
                <form action="index.php" method="post" autocomplete="off">
                    <div class="form-group mb-3">
                        <label class="form-label"><i class="fa-solid fa-id-card me-1 text-muted"></i> IC Number / Member ID</label>
                        <input type="text" name="username" maxlength="25" placeholder="Enter member ID" required <?php echo $ip_status['is_blocked'] ? 'disabled' : '';?> />
                    </div>
                    <div class="form-group mb-3">
                        <label class="form-label"><i class="fa-solid fa-key me-1 text-muted"></i> Password / PIN</label>
                        <input type="password" name="password" maxlength="25" placeholder="Enter password" required <?php echo $ip_status['is_blocked'] ? 'disabled' : '';?> />
                    </div>

                    <input type="hidden" name="token" value="<?php echo htmlspecialchars($_SESSION['token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    <input type="hidden" name="login_type" value="patron" />
                    <input type="hidden" name="submitted" value="TRUE" />
                    <button type="submit" class="btn btn-primary w-100 mt-2" name="Submit1" <?php echo $ip_status['is_blocked'] ? 'disabled' : '';?>>
                        <i class="fa-solid fa-right-to-bracket me-1"></i> Sign In to My Account
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Staff Portal Login Modal Dialog -->
    <div id="staffModal" class="modal-backdrop" onclick="if(event.target===this) closeStaffModal();">
        <div class="modal-dialog modal-sm">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fa-solid <?php echo $ip_status['is_blocked'] ? 'fa-ban text-danger' : 'fa-shield-halved text-primary';?> me-2"></i>
                    Staff Portal Login
                </h5>
                <button type="button" class="modal-close-btn" onclick="closeStaffModal();" title="Close dialog">&times;</button>
            </div>
            <div class="modal-body">
                <?php if ($ip_status['is_blocked']): ?>
                    <div class="lockout-banner">
                        <div class="lockout-banner-header">
                            <i class="fa-solid fa-shield-halved fa-lg"></i>
                            <span>Access Blocked (1 Week)</span>
                        </div>
                        <div class="lockout-banner-body">
                            Your IP address (<code><?php echo htmlspecialchars(ip_guard_get_client_ip(), ENT_QUOTES, 'UTF-8');?></code>) has been temporarily locked out due to 3 consecutive failed login attempts.
                        </div>
                        <div class="lockout-banner-footer">
                            <i class="fa-regular fa-clock me-1"></i>
                            <span>Expires in <strong><?php echo ip_guard_format_time_left($ip_status['remaining_seconds']);?></strong> &bull; <?php echo date('d M Y, h:i A', $ip_status['expires_at']);?></span>
                        </div>
                    </div>
                <?php else: ?>
                    <p class="text-muted small mb-3">Sign in with your staff credentials to access circulation, cataloging, and reports.</p>
                <?php endif; ?>

                <?php
                    if (!empty($_SESSION['flash_login_error'])) {
                        echo "<div class='alert alert-danger mb-3'><i class='fa-solid fa-circle-exclamation me-1'></i> " . htmlspecialchars($_SESSION['flash_login_error'], ENT_QUOTES, 'UTF-8') . "</div>";
                        unset($_SESSION['flash_login_error']);
                    }
                ?>
                <form action="index.php" method="post" autocomplete="off">
                    <div class="form-group mb-3">
                        <label class="form-label"><i class="fa-solid fa-user me-1 text-muted"></i> Admin / Staff Username</label>
                        <input type="text" name="username" maxlength="25" placeholder="Enter username" required <?php echo $ip_status['is_blocked'] ? 'disabled' : '';?> />
                    </div>
                    <div class="form-group mb-3">
                        <label class="form-label"><i class="fa-solid fa-key me-1 text-muted"></i> Password</label>
                        <input type="password" name="password" maxlength="25" placeholder="Enter password" required <?php echo $ip_status['is_blocked'] ? 'disabled' : '';?> />
                    </div>

                    <input type="hidden" name="token" value="<?php echo htmlspecialchars($_SESSION['token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    <input type="hidden" name="login_type" value="staff" />
                    <input type="hidden" name="submitted" value="TRUE" />
                    <button type="submit" class="btn btn-primary w-100 mt-2" name="Submit1" <?php echo $ip_status['is_blocked'] ? 'disabled' : '';?>>
                        <i class="fa-solid fa-right-to-bracket me-1"></i> Sign In to Workstation
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
<?php exit;?>
