<?php
    session_start();define('includeExist', true);
    include_once 'includes/access_isset_myacc.php';
    include_once 'config.php';
    include_once 'includes/functions.php';
    include_once 'includes/token_validate.php';

    $patron_user = $_SESSION["username_myacc"] ?? '';

    // Handle Profile Photo Upload or Removal
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_avatar']) && $proceedAfterToken && !empty($patron_user)) {
        $safe_user = preg_replace('/[^a-zA-Z0-9_-]/', '_', $patron_user);
        $avatar_dir = $avatar_upload_directory ?? ("../" . ($_SESSION["parent_dir"] ?? 'site') . "/avatars");
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        
        if (!is_dir($avatar_dir)) {
            @mkdir($avatar_dir, 0755, true);
        }

        if ($_POST['action_avatar'] === 'upload') {
            if (isset($_FILES['avatar_file']) && $_FILES['avatar_file']['error'] === UPLOAD_ERR_OK) {
                $max_avatar_bytes = ((int)($avatar_upload_maxsize ?? 2)) * 1024 * 1024;
                $file_tmp = $_FILES['avatar_file']['tmp_name'];
                $file_size = $_FILES['avatar_file']['size'];
                $file_name = $_FILES['avatar_file']['name'];
                $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

                if (!in_array($ext, $allowed_extensions, true)) {
                    $_SESSION['flash_avatar_msg'] = ['type' => 'danger', 'text' => 'Upload failed: Invalid file type. Allowed formats: JPG, JPEG, PNG, WEBP, GIF.'];
                } elseif ($file_size > $max_avatar_bytes) {
                    $_SESSION['flash_avatar_msg'] = ['type' => 'danger', 'text' => 'Upload failed: Image size exceeds maximum allowed (' . ($avatar_upload_maxsize ?? 2) . ' MB).'];
                } else {
                    $img_info = @getimagesize($file_tmp);
                    if ($img_info === false) {
                        $_SESSION['flash_avatar_msg'] = ['type' => 'danger', 'text' => 'Upload failed: The file is not a valid image.'];
                    } else {
                        // Remove old avatar files for this user
                        foreach ($allowed_extensions as $old_ext) {
                            $old_file = $avatar_dir . '/' . $safe_user . '.' . $old_ext;
                            if (is_file($old_file)) {
                                @unlink($old_file);
                            }
                        }
                        
                        // Save new avatar
                        $target_path = $avatar_dir . '/' . $safe_user . '.' . $ext;
                        if (move_uploaded_file($file_tmp, $target_path)) {
                            $_SESSION['flash_avatar_msg'] = ['type' => 'success', 'text' => 'Your profile photo has been updated successfully!'];
                        } else {
                            $_SESSION['flash_avatar_msg'] = ['type' => 'danger', 'text' => 'Upload failed: Unable to save the uploaded image to directory.'];
                        }
                    }
                }
            } else {
                $_SESSION['flash_avatar_msg'] = ['type' => 'danger', 'text' => 'Please select an image file to upload.'];
            }
        } elseif ($_POST['action_avatar'] === 'delete') {
            $deleted = false;
            foreach ($allowed_extensions as $old_ext) {
                $old_file = $avatar_dir . '/' . $safe_user . '.' . $old_ext;
                if (is_file($old_file)) {
                    @unlink($old_file);
                    $deleted = true;
                }
            }
            if ($deleted) {
                $_SESSION['flash_avatar_msg'] = ['type' => 'info', 'text' => 'Your profile photo has been removed.'];
            }
        }
        header("Location: logged.php");
        exit;
    }

    // Secure renewal with patron ownership check and CSRF token
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['renew_id']) && is_numeric($_POST['renew_id']) && $proceedAfterToken) {
        $renew_id = (int)$_POST['renew_id'];

        // 1. Get when charged and ensure it belongs to this patron (prevent IDOR)
        $stmtA = mysqli_prepare($GLOBALS["conn"], "SELECT `39charged_on`, `39duedate` FROM eg_item_charge WHERE id=? AND `39patron`=?");
        mysqli_stmt_bind_param($stmtA, "is", $renew_id, $patron_user);
        mysqli_stmt_execute($stmtA);
        $resultA = mysqli_stmt_get_result($stmtA);
        $myrowA = mysqli_fetch_array($resultA);
        mysqli_stmt_close($stmtA);
        
        if ($myrowA) {
            $charge_onA = $myrowA["39charged_on"] ?? 0;
            $multiplyA = ($myrowA["39duedate"] ?? 0) + 2;
        
            if ($multiplyA <= $max_renew_count + 1) {
                // save and increase 39duedate +1
                $duedateInsert = ($myrowA["39duedate"] ?? 0) + 1;
                $stmt = mysqli_prepare($GLOBALS["conn"], "UPDATE eg_item_charge SET `39duedate`=? WHERE id=? AND `39patron`=?");
                mysqli_stmt_bind_param($stmt, "iis", $duedateInsert, $renew_id, $patron_user);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
                $_SESSION['flash_renew_msg'] = ['type' => 'success', 'text' => 'Pinjaman anda telah berjaya diperbaharui.'];
            } else {
                $_SESSION['flash_renew_msg'] = ['type' => 'danger', 'text' => 'Anda tidak dibenarkan untuk memperbaharui pinjaman kerana telah melebihi had dibenarkan.'];
            }
        }
        header("Location: logged.php");
        exit;
    }

    // Fetch patron details
    $patron_data = [];
    if (!empty($patron_user)) {
        $stmtP = mysqli_prepare($GLOBALS["conn"], "SELECT name, username, division, allowed, lastlogin FROM eg_auth WHERE username=?");
        if ($stmtP) {
            mysqli_stmt_bind_param($stmtP, "s", $patron_user);
            mysqli_stmt_execute($stmtP);
            $resP = mysqli_stmt_get_result($stmtP);
            if ($resP && $rowP = mysqli_fetch_assoc($resP)) {
                $patron_data = $rowP;
            }
            mysqli_stmt_close($stmtP);
        }
    }

    $patron_name = $patron_data['name'] ?? $patron_user;
    $patron_id = $patron_data['username'] ?? $patron_user;
    $patron_dept = $patron_data['division'] ?? '-';
    $patron_role = $patron_data['allowed'] ?? 'STANDARD';
    $patron_lastlogin = $patron_data['lastlogin'] ?? '-';
    $patron_max_days = maxday($patron_user);
    $patron_max_items = maxloanitem($patron_user);
    $patron_current_loans = countCurrentLoans($patron_user);
    $patron_avatar = getPatronAvatarPath($patron_user);
    $csrfToken = htmlspecialchars($_SESSION['token'] ?? '', ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE HTML>
<html lang='en'>

<head>
    <meta charset="utf-8">
    <title><?php echo htmlspecialchars($product_name, ENT_QUOTES, 'UTF-8');?> | My Account</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="<?php echo htmlspecialchars($mini_icon_path, ENT_QUOTES, 'UTF-8');?>" rel="icon" type="image/png" />
    <link href="./assets/styles/style.css?v=3" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="./assets/fontawesome/css/all.min.css">
    <script>
        function openAvatarModal() {
            var modal = document.getElementById('avatarModal');
            if (modal) {
                modal.classList.add('show');
                document.body.style.overflow = 'hidden';
            }
        }

        function closeAvatarModal() {
            var modal = document.getElementById('avatarModal');
            if (modal) {
                modal.classList.remove('show');
                document.body.style.overflow = '';
            }
        }

        function previewAvatar(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    var img = document.getElementById('avatarPreviewImg');
                    var placeholder = document.getElementById('avatarPlaceholderIcon');
                    if (img) {
                        img.src = e.target.result;
                        img.style.display = 'block';
                    }
                    if (placeholder) {
                        placeholder.style.display = 'none';
                    }
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        function validateAvatarForm() {
            var fileInput = document.getElementById('avatarFileInput');
            if (!fileInput || !fileInput.files || fileInput.files.length === 0) {
                alert('Please select an image file first.');
                return false;
            }
            var maxBytes = <?php echo ((int)($avatar_upload_maxsize ?? 2)) * 1024 * 1024; ?>;
            if (fileInput.files[0].size > maxBytes) {
                alert('Selected image exceeds the maximum allowed size of <?php echo (int)($avatar_upload_maxsize ?? 2); ?> MB.');
                return false;
            }
            return true;
        }

        function openImageModal(src, title) {
            var modal = document.getElementById('imageModal');
            var img = document.getElementById('imageModalImg');
            var titleEl = document.getElementById('imageModalTitle');
            if (modal && img) {
                img.src = src;
                if (titleEl && title) titleEl.textContent = title;
                modal.classList.add('show');
                document.body.style.overflow = 'hidden';
            }
        }

        function closeImageModal() {
            var modal = document.getElementById('imageModal');
            var img = document.getElementById('imageModalImg');
            if (modal) {
                modal.classList.remove('show');
                document.body.style.overflow = '';
                if (img) img.src = '';
            }
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeAvatarModal();
                closeImageModal();
            }
        });
    </script>
</head>

<body>
    <?php include_once './includes/loggedinfo.php'; ?>
    
    <div class="app-container">
        <?php
            if (!empty($_SESSION['flash_avatar_msg'])) {
                $amsg = $_SESSION['flash_avatar_msg'];
                $atype = htmlspecialchars($amsg['type'] ?? 'info', ENT_QUOTES, 'UTF-8');
                $atext = $amsg['text'] ?? '';
                echo "<div class='alert alert-{$atype}'>{$atext}</div>";
                unset($_SESSION['flash_avatar_msg']);
            }

            if (!empty($_SESSION['flash_renew_msg'])) {
                $rmsg = $_SESSION['flash_renew_msg'];
                $rtype = htmlspecialchars($rmsg['type'] ?? 'info', ENT_QUOTES, 'UTF-8');
                $rtext = $rmsg['text'] ?? '';
                echo "<div class='alert alert-{$rtype}'>{$rtext}</div>";
                unset($_SESSION['flash_renew_msg']);
            }
        ?>
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2">
                    <i class="fa-solid fa-id-card text-primary"></i>
                    <strong>Patron Information</strong>
                    <span class="badge badge-primary"><?php echo htmlspecialchars($patron_role, ENT_QUOTES, 'UTF-8'); ?></span>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <a class="btn btn-primary btn-sm" href="patron_card.php" title="View your digital library membership card">
                        <i class="fa-solid fa-id-card-clip me-1"></i> Digital Library Card
                    </a>
                    <a class="btn btn-secondary btn-sm" href='admin/passchange.php?upd=.g' title='Change your login password'>
                        <i class="fa-solid fa-key me-1"></i> Change Password
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center gap-4 flex-wrap flex-md-nowrap">
                    <!-- Profile Photo Area -->
                    <div class="text-center" style="min-width: 130px;">
                        <div style="position: relative; display: inline-block;">
                            <?php if ($patron_avatar): ?>
                                <div style="position: relative; width: 110px; height: 110px; border-radius: 50%; overflow: hidden; border: 3px solid var(--primary, #2563eb); box-shadow: 0 4px 10px rgba(0,0,0,0.12); cursor: pointer; transition: transform 0.15s ease;"
                                     onclick="openImageModal('<?php echo htmlspecialchars($patron_avatar, ENT_QUOTES, 'UTF-8'); ?>', 'Profile Photo - <?php echo htmlspecialchars($patron_name, ENT_QUOTES, 'UTF-8'); ?>');"
                                     onmouseover="this.style.transform='scale(1.04)';"
                                     onmouseout="this.style.transform='scale(1)';"
                                     title="Click to enlarge profile photo">
                                    <img src="<?php echo htmlspecialchars($patron_avatar, ENT_QUOTES, 'UTF-8'); ?>"
                                         alt="Profile Photo"
                                         style="width: 100%; height: 100%; object-fit: cover; display: block;">
                                    <div style="position: absolute; bottom: 0; left: 0; right: 0; background: rgba(0,0,0,0.55); color: #fff; font-size: 0.65rem; padding: 2px 0; text-align: center;">
                                        <i class="fa-solid fa-magnifying-glass-plus"></i> View
                                    </div>
                                </div>
                            <?php else: ?>
                                <div style="width: 110px; height: 110px; border-radius: 50%; background: linear-gradient(135deg, #e2e8f0 0%, #cbd5e1 100%); display: flex; align-items: center; justify-content: center; border: 3px dashed #94a3b8; color: #64748b; font-size: 2.75rem; cursor: pointer;"
                                     onclick="openAvatarModal();"
                                     title="Click to upload profile photo">
                                    <i class="fa-solid fa-user"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="mt-2 d-flex justify-content-center gap-1">
                            <button type="button" class="btn btn-secondary btn-sm" style="font-size: 0.72rem; padding: 2px 8px;" onclick="openAvatarModal();">
                                <i class="fa-solid fa-camera me-1"></i> <?php echo $patron_avatar ? 'Update' : 'Upload'; ?>
                            </button>
                            <?php if ($patron_avatar): ?>
                                <form method="post" action="logged.php" style="display:inline; margin:0;" onsubmit="return confirm('Are you sure you want to remove your profile photo?');">
                                    <input type="hidden" name="token" value="<?php echo $csrfToken; ?>" />
                                    <input type="hidden" name="action_avatar" value="delete" />
                                    <button type="submit" class="btn btn-danger btn-sm" style="font-size: 0.72rem; padding: 2px 8px;" title="Remove profile photo">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Patron Details Area -->
                    <div class="flex-grow-1" style="border-left: 1px solid var(--border-color, #e2e8f0); padding-left: 1.5rem;">
                        <h3 class="mb-1" style="font-size: 1.25rem; font-weight: 700; color: var(--text-main, #0f172a);">
                            <?php echo htmlspecialchars($patron_name, ENT_QUOTES, 'UTF-8'); ?>
                        </h3>
                        <div class="text-muted mb-2 font-size-sm">
                            <i class="fa-solid fa-id-badge me-1 text-primary"></i>
                            <strong>Identification / Member ID:</strong> <?php echo htmlspecialchars($patron_id, ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                        <div class="row g-2 font-size-sm">
                            <div class="col-12 col-md-6 mb-1">
                                <span class="text-muted"><i class="fa-solid fa-building me-1"></i><strong>Department / Address:</strong></span>
                                <div><?php echo nl2br(htmlspecialchars($patron_dept, ENT_QUOTES, 'UTF-8')); ?></div>
                            </div>
                            <div class="col-12 col-md-6 mb-1">
                                <span class="text-muted"><i class="fa-solid fa-clock-rotate-left me-1"></i><strong>Last Login:</strong></span>
                                <div><?php echo htmlspecialchars($patron_lastlogin, ENT_QUOTES, 'UTF-8'); ?></div>
                                <div class="mt-1 text-muted">
                                    <i class="fa-solid fa-book-bookmark me-1 text-primary"></i><strong>Max Loan Items:</strong> <span class="badge badge-primary"><?php echo (int)$patron_max_items; ?> items</span>
                                    <?php if ($patron_current_loans > 0): ?>
                                        <small class="text-muted ms-1">(<?php echo (int)$patron_current_loans; ?> active)</small>
                                    <?php endif; ?>
                                </div>
                                <div class="mt-1 text-muted">
                                    <i class="fa-solid fa-calendar-check me-1 text-success"></i><strong>Loan Duration:</strong> <span class="badge badge-primary"><?php echo (int)$patron_max_days; ?> days</span> <small class="text-muted ms-1">(per loan)</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <strong>On-Loan Items History and Fines</strong>
            </div>
            <div class="card-body p-0">
                <table class="table-modern m-0">
                <thead>
                    <tr>
                        <th class="text-center col-num">#</th>
                        <th>Material Title</th>
                        <th class="text-center" style="width:140px;">Accession No.</th>
                        <th class="text-center" style="width:120px;">Loan Date</th>
                        <th class="text-center">Due Date</th>
                        <th class="text-center">Renewals</th>
                        <th class="text-center">Discharged On</th>
                        <th class="text-center">Overdue</th>
                        <th class="text-center">Fines</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                    $stmtT = mysqli_prepare($GLOBALS["conn"], "SELECT * FROM eg_item_charge WHERE `39patron`=?");
                    mysqli_stmt_bind_param($stmtT, "s", $_SESSION['username_myacc']);
                    mysqli_stmt_execute($stmtT);
                    $resultT = mysqli_stmt_get_result($stmtT);
                    $n = 1;
                    $totalunpaid = 0.00;
                    $beforepaymenton = "0000";
                    $beforepaymentrcv = "0000";
                    while ($resultT && $myrow=mysqli_fetch_array($resultT)) {
                        $id=(int)$myrow["id"];
                        $patron=$myrow["39patron"] ?? '';
                        $accessnum=$myrow["39accessnum"] ?? '';
                        $charged_on=$myrow["39charged_on"] ?? 0;
                        $duedate_mp=$myrow["39duedate"] ?? 0;
                        $dc_on=$myrow["40dc_on"] ?? '';
                        $dc=$myrow["40dc"] ?? '';
                        $dc_enforcedfine=$myrow["40dc_enforcedfine"] ?? 0;
                        $received_amount=$myrow["41f_received_amount"] ?? '0.00';
                        $paid_on=$myrow["41f_paidon"] ?? '';
                        $paidrb=$myrow["41f_received"] ?? '';
                        $paid_rid=$myrow["41f_receipt_id"] ?? '';
                        
                        if (($myrow["41f_pay"] ?? '') == 'YES') {
                            $f_pay="<span class='badge badge-success'>PAID</span>";
                        } else {
                            $f_pay="<span class='badge badge-danger'>UNPAID</span>";
                        }
                
                        $maxSecond = ((maxday($patron)*($duedate_mp+1))*86400);//to calculate duedate
                        $beginOfDay_for_charged_on = strtotime("midnight", $charged_on);// start of the day for the charged day
                        $duedate = $beginOfDay_for_charged_on + $maxSecond + 86399;// + 86399 to extend to midnight
                        $duedate = shiftDueDate($duedate);//shifting duedate if holiday

                        if ($dc_on <> null && $dc_on != 0) {
                            $overdue_days = calculateOverdueDays($dc_on, $duedate);
                        } else {
                            $overdue_days = calculateOverdueDays(time(), $duedate);
                        }
                                                        
                        echo "<tr class='table-row'>";
                        echo "<td class='text-center'>$n</td>";
                        echo "<td><strong>".htmlspecialchars(getTitle($accessnum), ENT_QUOTES, 'UTF-8')."</strong></td>";
                        echo "<td class='text-center'><strong>".htmlspecialchars($accessnum, ENT_QUOTES, 'UTF-8')."</strong></td>";
                        echo "<td class='text-center'>".date('D, Y-m-d', $charged_on)."</td>";
                        echo "<td class='text-center'>".date('D, Y-m-d', $duedate)."</td>";
                        echo "<td class='text-center'>$duedate_mp";

                        //display the link to renew only if the number of renewals is less than max and due date not passed
                        if ($dc != 'DC' && $duedate_mp < $max_renew_count && time() <= $duedate) {
                            echo " <form method='post' action='logged.php' style='display:inline-block; margin:0;' onsubmit=\"return confirm('Are you sure you want to renew this loaned item?');\">";
                            echo "<input type='hidden' name='token' value='$csrfToken'>";
                            echo "<input type='hidden' name='renew_id' value='$id'>";
                            echo "<button type='submit' class='btn btn-xs btn-primary ms-1' style='padding: 2px 7px; font-size: 0.72rem; line-height: 1.1; vertical-align: middle; min-height: 0; box-shadow: none;'>Renew</button>";
                            echo "</form>";
                        }
                        echo "</td>";
                        if ($dc_on != '') {
                            echo "<td class='text-center'>".date('D, Y-m-d', $dc_on)."</td>";
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
                            if (($myrow["41f_pay"] ?? '') == 'YES') {
                                if ($beforepaymenton == $paid_on && $beforepaymentrcv == $paidrb) {
                                    echo "<em class='text-muted'>Paid as above <br/>Receipt: " . htmlspecialchars($paid_rid, ENT_QUOTES, 'UTF-8') . "</em>";
                                } else {
                                    echo "<strong>" . htmlspecialchars($currency_SHORT, ENT_QUOTES, 'UTF-8') . " " . htmlspecialchars($received_amount, ENT_QUOTES, 'UTF-8') . "</strong><br/>$f_pay " . date('D, Y-m-d', $paid_on) . "<br/><small class='text-muted'>Receipt: " . htmlspecialchars($paid_rid, ENT_QUOTES, 'UTF-8') . "</small>";
                                }
                            } else {
                                if ($dc_on != null && $dc_on != 0) {
                                    if ($overdue_days > 0) {
                                        $fine_val = calculatedFines($overdue_days, $accessnum, $dc_enforcedfine);
                                        echo "<strong>" . htmlspecialchars($currency_SHORT, ENT_QUOTES, 'UTF-8') . " " . htmlspecialchars(number_format((float)$fine_val, 2, '.', ''), ENT_QUOTES, 'UTF-8') . "</strong> $f_pay";
                                        $totalunpaid += (float)$fine_val;
                                    } else {
                                        echo "-";
                                    }
                                } else {
                                    if ($overdue_days > 0) {
                                        $current_fine_id = getIDCurrentEnforcedFine(getTypeID($accessnum));
                                        $fine_val = calculatedFines($overdue_days, $accessnum, $current_fine_id);
                                        echo "<strong>" . htmlspecialchars($currency_SHORT, ENT_QUOTES, 'UTF-8') . " " . htmlspecialchars(number_format((float)$fine_val, 2, '.', ''), ENT_QUOTES, 'UTF-8') . "</strong> $f_pay";
                                        $totalunpaid += (float)$fine_val;
                                        echo " <span class='badge badge-warning'>UNDISCHARGE</span>";
                                    } else {
                                        echo "-";
                                    }
                                }
                            }
                        echo "</td>";
                        echo "</tr>";
                    
                        $beforepaymenton = $paid_on;
                        $beforepaymentrcv = $paidrb;
                        $n = $n + 1;
                    }
                    mysqli_stmt_close($stmtT);
                ?>
                <tr class="withbgtotal">
                    <td colspan="8" class="text-right font-bold">Total Unpaid Fines:</td>
                    <td class="text-center text-danger font-bold"><?php echo htmlspecialchars($currency_SHORT, ENT_QUOTES, 'UTF-8') . ' ' . number_format($totalunpaid, 2);?></td>
                </tr>
                </tbody>
                </table>
            </div>
            <div class="card-footer text-muted text-center">
                Please proceed to the service counter for settlement of any outstanding fines.
            </div>
        </div>

        <?php include_once './includes/footerbar.php';?>
    </div>

    <!-- Upload Photo Modal Dialog -->
    <div id="avatarModal" class="modal-backdrop" onclick="if(event.target===this) closeAvatarModal();">
        <div class="modal-dialog modal-md" style="max-width: 480px;">
            <div class="modal-header">
                <h5 class="modal-title" id="avatarModalTitle"><i class="fa-solid fa-camera me-2 text-primary"></i>Upload Profile Photo</h5>
                <button type="button" class="modal-close-btn" onclick="closeAvatarModal();" title="Close modal">&times;</button>
            </div>
            <form method="post" action="logged.php" enctype="multipart/form-data" onsubmit="return validateAvatarForm();">
                <input type="hidden" name="token" value="<?php echo $csrfToken; ?>" />
                <input type="hidden" name="action_avatar" value="upload" />
                <div class="modal-body p-3">
                    <div class="text-center mb-3">
                        <div id="avatarPreviewContainer" style="width: 120px; height: 120px; border-radius: 50%; overflow: hidden; margin: 0 auto; border: 2px dashed #94a3b8; display: flex; align-items: center; justify-content: center; background: #f8fafc;">
                            <?php if ($patron_avatar): ?>
                                <img id="avatarPreviewImg" src="<?php echo htmlspecialchars($patron_avatar, ENT_QUOTES, 'UTF-8'); ?>" alt="Preview" style="width: 100%; height: 100%; object-fit: cover; display: block;">
                                <i id="avatarPlaceholderIcon" class="fa-solid fa-image text-muted" style="font-size: 2.5rem; display: none;"></i>
                            <?php else: ?>
                                <img id="avatarPreviewImg" src="" alt="Preview" style="width: 100%; height: 100%; object-fit: cover; display: none;">
                                <i id="avatarPlaceholderIcon" class="fa-solid fa-image text-muted" style="font-size: 2.5rem;"></i>
                            <?php endif; ?>
                        </div>
                        <div class="text-muted mt-2 font-size-sm">
                            Select a clear square portrait photo.<br/>
                            <small>Max file size: <?php echo (int)($avatar_upload_maxsize ?? 2); ?> MB (JPG, PNG, WEBP, GIF)</small>
                        </div>
                    </div>

                    <div class="form-group mb-0">
                        <label class="form-label font-bold">Choose Image File:</label>
                        <input type="file" id="avatarFileInput" name="avatar_file" accept="image/jpeg,image/png,image/webp,image/gif" required class="form-control" onchange="previewAvatar(this);" />
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-between p-3 border-top">
                    <button type="button" class="btn btn-secondary btn-sm" onclick="closeAvatarModal();">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-cloud-arrow-up me-1"></i> Save Profile Photo</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Image Preview Modal Dialog -->
    <div id="imageModal" class="modal-backdrop" onclick="if(event.target===this) closeImageModal();">
        <div class="modal-dialog modal-md" style="max-width: 480px;">
            <div class="modal-header">
                <h5 class="modal-title" id="imageModalTitle">Profile Photo</h5>
                <button type="button" class="modal-close-btn" onclick="closeImageModal();" title="Close modal">&times;</button>
            </div>
            <div class="modal-body text-center p-3" style="background:#f8fafc; overflow:auto;">
                <img id="imageModalImg" src="" alt="Profile Photo" class="rounded shadow" style="max-height: 70vh; max-width: 100%; height: auto; width: auto; object-fit: contain; margin: 0 auto; display: block;">
            </div>
        </div>
    </div>
</body>
</html>
<?php exit;?>

