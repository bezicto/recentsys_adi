<?php
    session_start();define('includeExist', true);
    include_once '../includes/access_super.php';
    include_once '../config.php';
    include_once '../includes/functions.php';
    include_once '../includes/token_validate.php';

    // System default material types (ID 1-5: Open Shelf, Red Spot, Audio Visual, Serial, Digital File)
    $systemDefaultTypeIds = [1, 2, 3, 4, 5];
    $systemDefaultTypeInfo = [
        1 => 'Standard circulating collection available for general loan to registered patrons.',
        2 => 'High-demand course reserves and reference books with restricted short-term loan rules.',
        3 => 'Non-print and multimedia resources including CDs, DVDs, kits, and educational media.',
        4 => 'Continuing resources such as journals, periodicals, magazines, and newspapers.',
        5 => 'Digital and electronic resources such as e-books, e-journals, digital documents, and media files.'
    ];

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $proceedAfterToken) {
        if (isset($_POST["action"]) && $_POST["action"] === 'del_type' && isset($_POST["del_id"]) && is_numeric($_POST["del_id"])) {
            $get_id_del = (int)$_POST["del_id"];
            if (in_array($get_id_del, $systemDefaultTypeIds, true)) {
                $_SESSION['flash_type_msg'] = ['type' => 'danger', 'text' => 'System default material types (Open Shelf, Red Spot, Audio Visual, Serial, Digital File) are protected and cannot be deleted.'];
            } else {
                $stmt = mysqli_prepare($GLOBALS["conn"], "DELETE FROM eg_type WHERE `38typeid` = ?");
                mysqli_stmt_bind_param($stmt, "i", $get_id_del);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
                $_SESSION['flash_type_msg'] = ['type' => 'success', 'text' => 'Material type has been deleted.'];
            }
        }

        if (isset($_POST["submitted"]) && $_POST["submitted"] === 'update' && isset($_POST["edt_id"]) && is_numeric($_POST["edt_id"])) {
            $type_upd = trim($_POST["type1"] ?? '');
            $id_upd = (int)$_POST["edt_id"];

            if (in_array($id_upd, $systemDefaultTypeIds, true)) {
                $_SESSION['flash_type_msg'] = ['type' => 'danger', 'text' => 'System default material types are read-only and cannot be renamed.'];
            } elseif (!empty($type_upd) && $id_upd > 0) {
                // Check duplicate among other records
                $stmt = mysqli_prepare($GLOBALS["conn"], "SELECT `38typeid` FROM eg_type WHERE `38type` = ? AND `38typeid` != ?");
                mysqli_stmt_bind_param($stmt, "si", $type_upd, $id_upd);
                mysqli_stmt_execute($stmt);
                $res_dup = mysqli_stmt_get_result($stmt);
                $has_dup = mysqli_num_rows($res_dup) > 0;
                mysqli_stmt_close($stmt);

                if (!$has_dup) {
                    $stmt = mysqli_prepare($GLOBALS["conn"], "UPDATE eg_type SET `38type`=? WHERE `38typeid`=?");
                    mysqli_stmt_bind_param($stmt, "si", $type_upd, $id_upd);
                    mysqli_stmt_execute($stmt);
                    mysqli_stmt_close($stmt);
                    $_SESSION['flash_type_msg'] = ['type' => 'success', 'text' => "Material type <strong>" . htmlspecialchars($type_upd, ENT_QUOTES, 'UTF-8') . "</strong> updated successfully."];
                } else {
                    $_SESSION['flash_type_msg'] = ['type' => 'danger', 'text' => 'Update cancelled: Duplicate material type name already exists.'];
                }
            } else {
                $_SESSION['flash_type_msg'] = ['type' => 'danger', 'text' => 'Error: Material type name cannot be empty.'];
            }
        } elseif (isset($_POST["submitted"]) && ($_POST["submitted"] === 'add' || $_POST["submitted"] == 'TRUE')) {
            $type1 = trim($_POST["type1"] ?? '');
            $stmt = mysqli_prepare($GLOBALS["conn"], "SELECT `38type` FROM eg_type WHERE `38type` = ?");
            mysqli_stmt_bind_param($stmt, "s", $type1);
            mysqli_stmt_execute($stmt);
            $result_type = mysqli_stmt_get_result($stmt);
            $num_results_affected_type = mysqli_num_rows($result_type);
            mysqli_stmt_close($stmt);
            
            if ($num_results_affected_type == 0) {
                if (!empty($type1)) {
                    $stmt = mysqli_prepare($GLOBALS["conn"], "INSERT INTO eg_type (`38type`) VALUES (?)");
                    mysqli_stmt_bind_param($stmt, "s", $type1);
                    mysqli_stmt_execute($stmt);
                    mysqli_stmt_close($stmt);
                    $_SESSION['flash_type_msg'] = ['type' => 'success', 'text' => "The material type <strong>" . htmlspecialchars($type1, ENT_QUOTES, 'UTF-8') . "</strong> has been successfully registered."];
                } else {
                    $_SESSION['flash_type_msg'] = ['type' => 'danger', 'text' => 'Input cancelled. Field cannot be left empty.'];
                }
            } elseif ($num_results_affected_type >= 1) {
                $_SESSION['flash_type_msg'] = ['type' => 'danger', 'text' => 'Input cancelled: Duplicate material type detected.'];
            }
        }
        header("Location: addtype.php");
        exit;
    }

    $is_edit = false;
    $edit_id = 0;
    $edit_type = '';

    if (isset($_GET['edt']) && is_numeric($_GET['edt'])) {
        $edit_id = (int)$_GET['edt'];
        if (in_array($edit_id, $systemDefaultTypeIds, true)) {
            $_SESSION['flash_type_msg'] = ['type' => 'warning', 'text' => 'System default material types (ID 1-5) are read-only and cannot be edited.'];
            header("Location: addtype.php");
            exit;
        }
        $stmt = mysqli_prepare($GLOBALS["conn"], "SELECT `38type` FROM eg_type WHERE `38typeid` = ?");
        mysqli_stmt_bind_param($stmt, "i", $edit_id);
        mysqli_stmt_execute($stmt);
        $result_edit = mysqli_stmt_get_result($stmt);
        if ($result_edit && $row_edit = mysqli_fetch_array($result_edit)) {
            $is_edit = true;
            $edit_type = $row_edit["38type"] ?? '';
        }
        mysqli_stmt_close($stmt);
    }
?>
<!DOCTYPE HTML>
<html lang='en'>

<head>
    <meta charset="utf-8">
    <title><?php echo htmlspecialchars($product_name, ENT_QUOTES, 'UTF-8');?> : <?php echo $is_edit ? 'Update Material Type' : 'Material Types'; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="../<?php echo htmlspecialchars($mini_icon_path, ENT_QUOTES, 'UTF-8');?>" rel="icon" type="image/png" />
    <link href="../assets/styles/style.css" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="../assets/fontawesome/css/all.min.css">
</head>

<body>
    <?php include_once '../includes/loggedinfo.php';?>
    
    <div class="app-container">
        <?php
            if (!empty($_SESSION['flash_type_msg'])) {
                $tmsg = $_SESSION['flash_type_msg'];
                $ttype = htmlspecialchars($tmsg['type'] ?? 'info', ENT_QUOTES, 'UTF-8');
                $ttext = $tmsg['text'] ?? '';
                echo "<div class='alert alert-{$ttype}'>{$ttext}</div>";
                unset($_SESSION['flash_type_msg']);
            }
            $csrfToken = htmlspecialchars($_SESSION['token'] ?? '', ENT_QUOTES, 'UTF-8');
        ?>

        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong><?php echo $is_edit ? "Update Material Type (#$edit_id)" : "Add New Material Type"; ?></strong>
                <?php if ($is_edit): ?>
                    <span class="badge badge-info">Editing Mode</span>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <form action="addtype.php" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="token" value="<?php echo $csrfToken; ?>" />
                    <div class="form-group">
                        <label class="form-label">Material Type Name:</label>
                        <input type="text" name="type1" maxlength="70" value="<?php echo htmlspecialchars($is_edit ? $edit_type : '', ENT_QUOTES, 'UTF-8'); ?>" required />
                    </div>

                    <?php if ($is_edit): ?>
                        <input type="hidden" name="edt_id" value="<?php echo $edit_id; ?>" />
                        <input type="hidden" name="submitted" value="update" />
                        <div class="d-flex gap-2">
                            <input type="submit" class="btn btn-primary w-100" name="Submit1" value="Update Material Type" />
                            <a href="addtype.php" class="btn btn-secondary w-100 text-center">Cancel</a>
                        </div>
                    <?php else: ?>
                        <input type="hidden" name="submitted" value="add" />
                        <input type="submit" class="btn btn-primary w-100" name="Submit1" value="Add Material Type" />
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">
                <strong>Material Types &amp; Fines Policy</strong>
            </div>
            <div class="p-3 bg-light border-bottom text-muted" style="font-size: 0.875rem; line-height: 1.5;">
                <div class="d-flex align-items-start gap-2">
                    <i class="fa-solid fa-circle-info mt-1" style="color: var(--info); font-size: 1.05rem;"></i>
                    <div>
                        <strong>System Notice:</strong> All 5 default material types (<strong>Open Shelf</strong>, <strong>Red Spot</strong>, <strong>Audio Visual</strong>, <strong>Serial</strong>, and <strong>Digital File</strong>) are built-in into <strong><?= $core_product;?></strong> as protected system defaults. You may add additional material types and define loan and fines policies for them as needed.
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <table class="table-modern m-0">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 50px;">#</th>
                        <th>Type Description</th>
                        <th class="text-center" style="width:240px;">Current Enforced Fines<br/><small class="text-muted">(Initial / Subsequent)</small></th>
                        <th class="text-center" style="width:140px;">Options</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                    $queryT = "select `38typeid`, `38type` from eg_type";
                    $resultT = mysqli_query($GLOBALS["conn"], $queryT);
                    $n = 1;
                    $has_types = false;
                    while ($resultT && $myrow=mysqli_fetch_array($resultT)) {
                        $has_types = true;
                        $typeT=$myrow["38type"] ?? '';
                        $typeidT=(int)$myrow["38typeid"];
                        $isDefaultType = in_array($typeidT, $systemDefaultTypeIds, true);
                        $rowHighlight = ($is_edit && $typeidT === $edit_id) ? " style='background-color: rgba(59, 130, 246, 0.08);'" : "";
                        
                        echo "<tr class='table-row'{$rowHighlight}>";
                        echo "<td class='text-center'>$n</td>";
                        echo "<td>";
                        echo "<div class='d-flex align-items-center flex-wrap gap-1'>";
                        echo "<strong>" . htmlspecialchars($typeT, ENT_QUOTES, 'UTF-8') . "</strong>";
                        if ($isDefaultType) {
                            echo " <span class='badge badge-secondary' title='Protected System Default'>System Default</span>";
                        }
                        if ($is_edit && $typeidT === $edit_id) {
                            echo " <span class='badge badge-info'>Editing</span>";
                        }
                        echo "</div>";
                        if ($isDefaultType && !empty($systemDefaultTypeInfo[$typeidT])) {
                            echo "<div class='text-muted small mt-1'><i class='fa fa-circle-info me-1'></i>" . htmlspecialchars($systemDefaultTypeInfo[$typeidT], ENT_QUOTES, 'UTF-8') . "</div>";
                        }
                        echo "</td>";
                        echo "<td class='text-center'><a class='badge badge-info' href='fines_days.php?typeid=$typeidT' title='Configure Fines & Loan Rules'>" . displayFines($typeidT) . "</a></td>";
                        echo "<td class='text-center'>";
                        if ($isDefaultType) {
                            if ($typeidT === 4) {
                                echo "<div class='d-flex justify-content-center gap-1'>";
                                echo "<a class='btn btn-primary btn-sm' href='serials.php' title='Manage Serials &amp; Periodicals'><i class='fa-solid fa-newspaper me-1'></i> Manage Serials</a>";
                                echo "</div>";
                            } else {
                                echo "<span class='badge badge-secondary' title='System default material type cannot be modified or deleted'><i class='fa-lock me-1'></i> Read-Only</span>";
                            }
                        } else {
                            echo "<div class='d-flex justify-content-center gap-1'>";
                            echo "<a class='btn btn-secondary btn-sm' title='Update this record' href='addtype.php?edt=$typeidT'>Edit</a>";
                            echo "<form method='post' action='addtype.php' style='display:inline;' onsubmit=\"return confirm('Are you sure? You are advised to change all items of this type before deleting.');\">";
                            echo "<input type='hidden' name='token' value='$csrfToken'>";
                            echo "<input type='hidden' name='action' value='del_type'>";
                            echo "<input type='hidden' name='del_id' value='$typeidT'>";
                            echo "<button type='submit' class='btn btn-danger btn-sm'>Delete</button>";
                            echo "</form>";
                            echo "</div>";
                        }
                        echo "</td></tr>";
                        $n = $n + 1;
                    }
                    if (!$has_types) {
                        echo "<tr><td colspan='4' class='text-center text-muted p-3'>No material types found.</td></tr>";
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
