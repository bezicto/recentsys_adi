<?php
    session_start();
    define('includeExist', true);
    include_once '../config.php';
    include_once '../includes/token_validate.php';
    include_once '../includes/selfreg_helper.php';

    // Check if accessing via QR self-registration token
    $raw_token = trim((string)($_REQUEST['token'] ?? ($_REQUEST['reg_token'] ?? ($_POST['token_reg'] ?? ($_SESSION['selfreg_current_token'] ?? '')))));
    $is_selfreg = false;
    $selfreg_error = '';
    $registration_success = false;
    $registered_patron = [];

    if (!empty($raw_token)) {
        $token_check = selfreg_validate_token($GLOBALS["conn"], $raw_token);
        if ($token_check['valid']) {
            $is_selfreg = true;
            $_SESSION['selfreg_current_token'] = $raw_token;

            // Bind first scan to patron session
            if (empty($_SESSION['selfreg_nonce'])) {
                try {
                    $_SESSION['selfreg_nonce'] = bin2hex(random_bytes(16));
                } catch (Exception $e) {
                    $_SESSION['selfreg_nonce'] = md5(uniqid((string)mt_rand(), true));
                }
            }
            $client_ip = $_SERVER['REMOTE_ADDR'] ?? '';
            selfreg_bind_first_scan($GLOBALS["conn"], $raw_token, $_SESSION['selfreg_nonce'], $client_ip);
        } else {
            // Token is invalid/expired/used
            $is_selfreg = true;
            $selfreg_error = $token_check['message'];
        }
    }

    // If not self-registration mode, require Super Admin authentication
    if (!$is_selfreg) {
        include_once '../includes/access_super.php';
    }

    // Handle Form Submissions
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $proceedAfterToken) {
        // --- Self-Registration Form Submission ---
        if ($is_selfreg && isset($_POST["submitted"]) && $_POST["submitted"] === 'selfreg') {
            $recheck = selfreg_validate_token($GLOBALS["conn"], $raw_token);
            if (!$recheck['valid']) {
                $selfreg_error = $recheck['message'];
            } else {
                $staffid1 = trim($_POST["staffid1"] ?? '');
                $fullname1 = trim($_POST["fullname1"] ?? '');
                $division1 = trim($_POST["division1"] ?? '');
                $allowed1 = 'PATRON'; // Enforce PATRON role server-side

                if (empty($staffid1) || empty($fullname1) || empty($division1)) {
                    $_SESSION['flash_selfreg_err'] = 'Please fill in all required fields to complete registration.';
                } else {
                    // Check duplicate IC / username
                    $stmt = mysqli_prepare($GLOBALS["conn"], "SELECT id FROM eg_auth WHERE username = ?");
                    mysqli_stmt_bind_param($stmt, "s", $staffid1);
                    mysqli_stmt_execute($stmt);
                    $result_username = mysqli_stmt_get_result($stmt);
                    $num_duplicates = mysqli_num_rows($result_username);
                    mysqli_stmt_close($stmt);

                    if ($num_duplicates > 0) {
                        $_SESSION['flash_selfreg_err'] = "An account with IC / Username <strong>" . htmlspecialchars($staffid1, ENT_QUOTES, 'UTF-8') . "</strong> already exists in the system. Please consult the counter librarian.";
                    } else {
                        // Insert new patron
                        $stmt = mysqli_prepare($GLOBALS["conn"], "INSERT INTO eg_auth VALUES (NULL, ?, AES_ENCRYPT('1','$ppaeskeysys'), ?, ?, ?, '', 'OFF', 'NO')");
                        mysqli_stmt_bind_param($stmt, "ssss", $staffid1, $allowed1, $fullname1, $division1);
                        $inserted = mysqli_stmt_execute($stmt);
                        $new_id = mysqli_insert_id($GLOBALS["conn"]);
                        mysqli_stmt_close($stmt);

                        if ($inserted) {
                            // Atomically consume token
                            selfreg_consume_token($GLOBALS["conn"], $raw_token, $staffid1, $fullname1);
                            unset($_SESSION['selfreg_current_token']);

                            $registration_success = true;
                            $registered_patron = [
                                'id' => $new_id,
                                'username' => $staffid1,
                                'name' => $fullname1,
                                'division' => $division1,
                                'role' => 'PATRON'
                            ];
                        } else {
                            $_SESSION['flash_selfreg_err'] = 'Registration failed due to a database error. Please try again or approach the counter.';
                        }
                    }
                }
            }
        }
        // --- Admin: Update Existing User ---
        elseif (isset($_POST["submitted"]) && $_POST["submitted"] === 'update' && isset($_POST["edt_id"]) && is_numeric($_POST["edt_id"])) {
            $name_upd = trim($_POST["fullname1"] ?? '');
            $username_upd = trim($_POST["staffid1"] ?? '');
            $division_upd = trim($_POST["division1"] ?? '');
            $allowed_upd = trim($_POST["allowed1"] ?? 'STANDARD');
            $id_upd = (int)$_POST["edt_id"];

            if (!empty($name_upd) && !empty($username_upd) && !empty($division_upd) && $id_upd > 0) {
                $stmt = mysqli_prepare($GLOBALS["conn"], "SELECT id FROM eg_auth WHERE username = ? AND id != ?");
                mysqli_stmt_bind_param($stmt, "si", $username_upd, $id_upd);
                mysqli_stmt_execute($stmt);
                $res_dup = mysqli_stmt_get_result($stmt);
                $has_dup = mysqli_num_rows($res_dup) > 0;
                mysqli_stmt_close($stmt);

                if (!$has_dup) {
                    $stmt = mysqli_prepare($GLOBALS["conn"], "UPDATE eg_auth SET name=?, username=?, division=?, devmode='NO', allowed=? WHERE id=?");
                    mysqli_stmt_bind_param($stmt, "ssssi", $name_upd, $username_upd, $division_upd, $allowed_upd, $id_upd);
                    mysqli_stmt_execute($stmt);
                    mysqli_stmt_close($stmt);
                    $_SESSION['flash_adduser_msg'] = ['type' => 'success', 'text' => "User <strong>" . htmlspecialchars($name_upd, ENT_QUOTES, 'UTF-8') . "</strong> record has been updated successfully!"];
                    header("Location: adduser.php?edt=" . $id_upd);
                    exit;
                } else {
                    $_SESSION['flash_adduser_msg'] = ['type' => 'danger', 'text' => 'Update failed: Another account with this IC/Username already exists.'];
                    header("Location: adduser.php?edt=" . $id_upd);
                    exit;
                }
            } else {
                $_SESSION['flash_adduser_msg'] = ['type' => 'danger', 'text' => 'Update error. Please make sure there are no empty fields.'];
                header("Location: adduser.php?edt=" . $id_upd);
                exit;
            }
        }
        // --- Admin: Add New User ---
        elseif (isset($_POST["submitted"]) && ($_POST["submitted"] === 'add' || $_POST["submitted"] == 'TRUE')) {
            $staffid1 = trim($_POST["staffid1"] ?? '');
            $fullname1 = trim($_POST["fullname1"] ?? '');
            $division1 = trim($_POST["division1"] ?? '');
            $allowed1 = trim($_POST["allowed1"] ?? 'STANDARD');
            
            $stmt = mysqli_prepare($GLOBALS["conn"], "SELECT username FROM eg_auth WHERE username = ?");
            mysqli_stmt_bind_param($stmt, "s", $staffid1);
            mysqli_stmt_execute($stmt);
            $result_username = mysqli_stmt_get_result($stmt);
            $num_results_affected_username = mysqli_num_rows($result_username);
            mysqli_stmt_close($stmt);
            
            if ($num_results_affected_username == 0) {
                if (!empty($staffid1) && !empty($fullname1) && !empty($division1) && !empty($allowed1)) {
                    $stmt = mysqli_prepare($GLOBALS["conn"], "INSERT INTO eg_auth VALUES (NULL, ?, AES_ENCRYPT('1','$ppaeskeysys'), ?, ?, ?, '', 'OFF', 'NO')");
                    mysqli_stmt_bind_param($stmt, "ssss", $staffid1, $allowed1, $fullname1, $division1);
                    mysqli_stmt_execute($stmt);
                    mysqli_stmt_close($stmt);
                    $_SESSION['flash_adduser_msg'] = ['type' => 'success', 'text' => "User <strong>" . htmlspecialchars($fullname1, ENT_QUOTES, 'UTF-8') . "</strong> has been successfully added to the system!"];
                } else {
                    $_SESSION['flash_adduser_msg'] = ['type' => 'danger', 'text' => 'Input failed. Please check if any field(s) were left empty.'];
                }
            } elseif ($num_results_affected_username >= 1) {
                $_SESSION['flash_adduser_msg'] = ['type' => 'danger', 'text' => 'Input failed: Duplicate IC/ID already exists in the system.'];
            }
            header("Location: adduser.php");
            exit;
        }
    }

    // Admin edit state
    $is_edit = false;
    $edit_id = 0;
    $edit_username = '';
    $edit_name = '';
    $edit_division = '';
    $edit_allowed = 'STANDARD';

    if (!$is_selfreg && isset($_GET['edt']) && is_numeric($_GET['edt'])) {
        $edit_id = (int)$_GET['edt'];
        $stmt = mysqli_prepare($GLOBALS["conn"], "SELECT username, allowed, division, name FROM eg_auth WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $edit_id);
        mysqli_stmt_execute($stmt);
        $result_edit = mysqli_stmt_get_result($stmt);
        if ($result_edit && $row_edit = mysqli_fetch_array($result_edit)) {
            $is_edit = true;
            $edit_username = $row_edit["username"] ?? '';
            $edit_allowed = $row_edit["allowed"] ?? 'STANDARD';
            $edit_division = $row_edit["division"] ?? '';
            $edit_name = $row_edit["name"] ?? '';
        }
        mysqli_stmt_close($stmt);
    }

    $csrfToken = htmlspecialchars($_SESSION['token'] ?? '', ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE HTML>
<html lang='en'>
<head>
    <meta charset="utf-8">
    <title><?php 
        if ($is_selfreg) {
            echo htmlspecialchars($product_name, ENT_QUOTES, 'UTF-8') . " : Patron Self-Registration";
        } else {
            echo htmlspecialchars($product_name, ENT_QUOTES, 'UTF-8') . " : " . ($is_edit ? 'Update User Account' : 'Add User');
        }
    ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="../<?php echo htmlspecialchars($mini_icon_path, ENT_QUOTES, 'UTF-8');?>" rel="icon" type="image/png" />
    <link href="../assets/styles/style.css" rel="stylesheet" type="text/css" />
    <style>
        .selfreg-container {
            max-width: 560px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        .selfreg-card {
            background: #ffffff;
            border-radius: var(--radius-lg, 12px);
            border: 1px solid var(--border-color, #e2e8f0);
            box-shadow: var(--shadow-lg, 0 10px 15px -3px rgba(0,0,0,0.08));
            overflow: hidden;
        }
        .selfreg-header {
            background: var(--header-bg-gradient, linear-gradient(135deg, #1e40af 0%, #3b82f6 100%));
            color: #ffffff;
            padding: 1.75rem 1.5rem;
            text-align: center;
        }
        .selfreg-header h2 {
            color: #ffffff;
            margin: 0.25rem 0 0 0;
            font-size: 1.35rem;
        }
        .selfreg-body {
            padding: 1.75rem 1.5rem;
        }
        .role-locked-badge {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.75rem 1rem;
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: var(--radius-md, 8px);
        }
    </style>
</head>

<body>
    <?php if ($is_selfreg): ?>
        <!-- ================= PATRON SELF-REGISTRATION VIEW ================= -->
        <div class="selfreg-container">
            <div class="selfreg-card">
                <div class="selfreg-header">
                    <div style="font-size: 2rem; margin-bottom: 0.25rem;">
                        <i class="fa-solid fa-id-card-clip"></i>
                    </div>
                    <h2>Patron Self-Registration</h2>
                    <p style="margin: 0.25rem 0 0 0; opacity: 0.9; font-size: 0.9rem;">
                        <?php echo htmlspecialchars($product_name, ENT_QUOTES, 'UTF-8');?>
                    </p>
                </div>

                <div class="selfreg-body">
                    <?php if ($registration_success): ?>
                        <!-- Success View -->
                        <div class="text-center py-2">
                            <div style="font-size: 3rem; color: var(--success, #10b981); margin-bottom: 0.75rem;">
                                <i class="fa-solid fa-circle-check"></i>
                            </div>
                            <h3>Welcome, <?php echo htmlspecialchars($registered_patron['name'], ENT_QUOTES, 'UTF-8');?>!</h3>
                            <p class="text-muted">
                                Your library patron membership has been registered successfully.
                            </p>

                            <div class="card p-3 my-3 text-start bg-subtle" style="font-size: 0.9rem;">
                                <div class="mb-1"><strong>Full Name:</strong> <?php echo htmlspecialchars($registered_patron['name'], ENT_QUOTES, 'UTF-8');?></div>
                                <div class="mb-1"><strong>IC / Member ID:</strong> <code><?php echo htmlspecialchars($registered_patron['username'], ENT_QUOTES, 'UTF-8');?></code></div>
                                <div class="mb-1"><strong>Address / Dept:</strong> <?php echo nl2br(htmlspecialchars($registered_patron['division'], ENT_QUOTES, 'UTF-8'));?></div>
                                <div><strong>Membership Role:</strong> <span class="badge badge-primary">PATRON</span></div>
                            </div>

                            <div class="alert alert-info text-start small mb-4">
                                <i class="fa-solid fa-circle-info me-1"></i>
                                <strong>Default PIN / Password:</strong> Your default password is <code>1</code>. You can log into the <strong>My Account Portal</strong> to check your loans and change your PIN.
                            </div>

                            <div class="d-flex gap-2 flex-column">
                                <a href="../index.php?modal=patron" class="btn btn-primary w-100">
                                    <i class="fa-solid fa-right-to-bracket me-1"></i> Log In to My Account
                                </a>
                                <a href="../opac.php" class="btn btn-secondary w-100">
                                    <i class="fa-solid fa-book-open me-1"></i> Browse Library Catalog (OPAC)
                                </a>
                            </div>
                        </div>

                    <?php elseif (!empty($selfreg_error)): ?>
                        <!-- Error View for Expired / Invalid Token -->
                        <div class="text-center py-3">
                            <div style="font-size: 3rem; color: #dc2626; margin-bottom: 0.75rem;">
                                <i class="fa-solid fa-circle-exclamation"></i>
                            </div>
                            <h4>QR Code Expired or Invalid</h4>
                            <p class="text-muted">
                                <?php echo htmlspecialchars($selfreg_error, ENT_QUOTES, 'UTF-8');?>
                            </p>
                            <div class="alert alert-warning text-start small mt-3">
                                <i class="fa-solid fa-triangle-exclamation me-1"></i>
                                Each self-registration QR code is valid for <strong>single use</strong> and expires within <strong>30 minutes</strong> of generation. Please ask the counter librarian to generate a fresh QR code for you.
                            </div>
                            <div class="mt-4">
                                <a href="../index.php" class="btn btn-secondary w-100">
                                    &larr; Back to Library Homepage
                                </a>
                            </div>
                        </div>

                    <?php else: ?>
                        <!-- Active Self-Registration Form -->
                        <?php if (!empty($_SESSION['flash_selfreg_err'])): ?>
                            <div class="alert alert-danger mb-3">
                                <i class="fa-solid fa-circle-exclamation me-1"></i>
                                <?php echo $_SESSION['flash_selfreg_err']; unset($_SESSION['flash_selfreg_err']); ?>
                            </div>
                        <?php endif; ?>

                        <form action="adduser.php?token=<?php echo urlencode($raw_token); ?>" method="post" autocomplete="off">
                            <input type="hidden" name="token" value="<?php echo $csrfToken; ?>" />
                            <input type="hidden" name="token_reg" value="<?php echo htmlspecialchars($raw_token, ENT_QUOTES, 'UTF-8'); ?>" />
                            <input type="hidden" name="submitted" value="selfreg" />
                            <input type="hidden" name="allowed1" value="PATRON" />

                            <div class="form-group mb-3">
                                <label class="form-label font-bold">
                                    <i class="fa-solid fa-user me-1 text-muted"></i> Full Name <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="fullname1" maxlength="255" placeholder="e.g. Siti Aminah binti Ahmad" required autofocus />
                            </div>

                            <div class="form-group mb-3">
                                <label class="form-label font-bold">
                                    <i class="fa-solid fa-id-card me-1 text-muted"></i> IC Number / Passport / Staff ID <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="staffid1" maxlength="255" placeholder="e.g. 950101085544" required />
                                <small class="text-muted">This will be your username and member identification number.</small>
                            </div>

                            <div class="form-group mb-3">
                                <label class="form-label font-bold">
                                    <i class="fa-solid fa-location-dot me-1 text-muted"></i> Address / Department / Contact <span class="text-danger">*</span>
                                </label>
                                <textarea name="division1" rows="3" placeholder="Enter your residential address, department/faculty, and phone number" required></textarea>
                            </div>

                            <div class="form-group mb-4">
                                <label class="form-label font-bold">
                                    <i class="fa-solid fa-shield-halved me-1 text-muted"></i> Membership Role
                                </label>
                                <div class="role-locked-badge">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge badge-primary">PATRON</span>
                                        <span class="text-muted small">Standard Library User &bull; Borrowing Access</span>
                                    </div>
                                    <i class="fa-solid fa-lock text-muted" title="Role is preselected and locked to PATRON"></i>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 py-2 font-bold" name="Submit1">
                                <i class="fa-solid fa-check me-1"></i> Submit
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>

            <div class="text-center mt-3 text-muted small">
                <?php echo htmlspecialchars($product_name, ENT_QUOTES, 'UTF-8');?> &bull; <?php echo htmlspecialchars($licensed_info ?? '', ENT_QUOTES, 'UTF-8');?>
            </div>
        </div>

    <?php else: ?>
        <!-- ================= SUPER ADMIN MANAGEMENT VIEW ================= -->
        <?php include_once '../includes/loggedinfo.php';?>
            
        <div class="app-container">
            <?php
                if (!empty($_SESSION['flash_adduser_msg'])) {
                    $umsg = $_SESSION['flash_adduser_msg'];
                    $utype = htmlspecialchars($umsg['type'] ?? 'info', ENT_QUOTES, 'UTF-8');
                    $utext = $umsg['text'] ?? '';
                    echo "<div class='alert alert-{$utype}'>{$utext}</div>";
                    unset($_SESSION['flash_adduser_msg']);
                }
            ?>
            
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <strong><?php echo $is_edit ? "Update User Profile (#$edit_id)" : "Add New System User"; ?></strong>
                    <?php if ($is_edit): ?>
                        <span class="badge badge-info">Editing Mode</span>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <form action="adduser.php<?php echo $is_edit ? "?edt=$edit_id" : ""; ?>" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="token" value="<?php echo $csrfToken; ?>" />
                        
                        <div class="form-group">
                            <label class="form-label">Full Name:</label>
                            <input type="text" name="fullname1" maxlength="255" value="<?php echo htmlspecialchars($is_edit ? $edit_name : '', ENT_QUOTES, 'UTF-8'); ?>" required />
                        </div>

                        <div class="form-group">
                            <label class="form-label">IC / Username / Member ID:</label>
                            <input type="text" name="staffid1" maxlength="255" value="<?php echo htmlspecialchars($is_edit ? $edit_username : '', ENT_QUOTES, 'UTF-8'); ?>" required />
                        </div>
                                            
                        <div class="form-group">
                            <label class="form-label">Address / Department:</label>
                            <textarea name="division1" rows="4" required><?php echo htmlspecialchars($is_edit ? $edit_division : '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                        </div>
               
                        <div class="form-group">
                            <label class="form-label">User Role / Eligibility Level:</label>
                            <select name="allowed1" required>
                                <option value="SUPER" <?php if ($edit_allowed == 'SUPER') {echo "selected";}?>>SUPER (System Administrator)</option>
                                <option value="TRUE" <?php if ($edit_allowed == 'TRUE') {echo "selected";}?>>TRUE (Library Staff Access)</option>
                                <option value="FALSE" <?php if ($edit_allowed == 'FALSE') {echo "selected";}?>>FALSE (Deactivated)</option>
                                <option value='PATRON' <?php if ($edit_allowed == 'PATRON') {echo "selected";}?>>PATRON (Library User)</option>
                            </select>
                        </div>
                        
                        <?php if ($is_edit): ?>
                            <input type="hidden" name="edt_id" value="<?php echo $edit_id; ?>" />
                            <input type="hidden" name="submitted" value="update" />
                            <div class="d-flex gap-2">
                                <input type="submit" class="btn btn-primary w-100" name="Submit1" value="Update User Record" />
                                <a href="../admin/chanuser.php" class="btn btn-secondary w-100 text-center">Cancel</a>
                            </div>
                        <?php else: ?>
                            <input type="hidden" name="submitted" value="add" />
                            <input type="submit" class="btn btn-primary w-100" name="Submit1" value="Add User Record" />
                        <?php endif; ?>
                    </form>
                </div>
            </div>
            
            <div class="text-center my-3">
                <a class="btn btn-secondary btn-sm" href="../admin/chanuser.php">&larr; Back to users account page</a>
            </div>
            
            <?php include_once '../includes/footerbar.php';?>
        </div>
    <?php endif; ?>
</body>
</html>
