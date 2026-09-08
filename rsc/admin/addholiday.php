<?php
    session_start();define('includeExist', true);
    include_once '../includes/access_super.php';
    include_once '../config.php';
    include_once '../includes/token_validate.php';

    $fr_param = isset($_POST['fr']) && $_POST['fr'] == '1' ? '?fr=1' : (isset($_GET['fr']) && $_GET['fr'] == '1' ? '?fr=1' : '');

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $proceedAfterToken) {
        if (isset($_POST["action"]) && $_POST["action"] === 'del_holiday' && isset($_POST["del_id"]) && is_numeric($_POST["del_id"])) {
            $get_id_del = (int)$_POST["del_id"];
            $stmt = mysqli_prepare($GLOBALS["conn"], "DELETE FROM eg_holiday WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "i", $get_id_del);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            $_SESSION['flash_holiday_msg'] = ['type' => 'success', 'text' => 'Holiday record has been deleted.'];
        }

        if (isset($_POST["submitted"]) && $_POST["submitted"] === 'update' && isset($_POST["edt_id"]) && is_numeric($_POST["edt_id"])) {
            $title_upd = trim($_POST["title1"] ?? '');
            $date_upd = trim($_POST["date1"] ?? '');
            $id_upd = (int)$_POST["edt_id"];

            if (!empty($title_upd) && !empty($date_upd) && $id_upd > 0) {
                $stmt = mysqli_prepare($GLOBALS["conn"], "UPDATE eg_holiday SET `38hol_title`=?, `38hol_date`=? WHERE id=?");
                mysqli_stmt_bind_param($stmt, "ssi", $title_upd, $date_upd, $id_upd);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
                $_SESSION['flash_holiday_msg'] = ['type' => 'success', 'text' => "Holiday <strong>" . htmlspecialchars($title_upd, ENT_QUOTES, 'UTF-8') . "</strong> record has been updated."];
            } else {
                $_SESSION['flash_holiday_msg'] = ['type' => 'danger', 'text' => 'Error: Please make sure no fields are left empty.'];
            }
        } elseif (isset($_POST["submitted"]) && ($_POST["submitted"] === 'add' || $_POST["submitted"] == 'TRUE')) {
            $title1 = trim($_POST["title1"] ?? '');
            $date1 = trim($_POST["date1"] ?? '');
            
            if (!empty($title1) && !empty($date1)) {
                $stmt = mysqli_prepare($GLOBALS["conn"], "INSERT INTO eg_holiday VALUES (NULL, ?, ?)");
                mysqli_stmt_bind_param($stmt, "ss", $title1, $date1);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
                $_SESSION['flash_holiday_msg'] = ['type' => 'success', 'text' => "Holiday <strong>" . htmlspecialchars($title1, ENT_QUOTES, 'UTF-8') . "</strong> on <strong>" . htmlspecialchars($date1, ENT_QUOTES, 'UTF-8') . "</strong> has been successfully added."];
            } else {
                $_SESSION['flash_holiday_msg'] = ['type' => 'danger', 'text' => 'Input failed. Please check if any field was left empty.'];
            }
        }

        if (isset($_POST["submitting"]) && $_POST["submitting"] == 'TRUE') {
            $mon1 = $_POST["monday"] ?? '0'; if ($mon1 =='') {$mon1 = '0';}
            $tue1 = $_POST["tuesday"] ?? '0'; if ($tue1 =='') {$tue1 = '0';}
            $wed1 = $_POST["wednesday"] ?? '0'; if ($wed1 =='') {$wed1 = '0';}
            $thu1 = $_POST["thursday"] ?? '0'; if ($thu1 =='') {$thu1 = '0';}
            $fri1 = $_POST["friday"] ?? '0'; if ($fri1 =='') {$fri1 = '0';}
            $sat1 = $_POST["saturday"] ?? '0'; if ($sat1 =='') {$sat1 = '0';}
            $sun1 = $_POST["sunday"] ?? '0'; if ($sun1 =='') {$sun1 = '0';}

            $stmt = mysqli_prepare($GLOBALS["conn"], "UPDATE eg_working_days SET mon=?, tue=?, wed=?, thu=?, fri=?, sat=?, sun=? WHERE id=1");
            mysqli_stmt_bind_param($stmt, "sssssss", $mon1, $tue1, $wed1, $thu1, $fri1, $sat1, $sun1);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            $_SESSION['flash_holiday_msg'] = ['type' => 'success', 'text' => 'Library working days schedule has been saved.'];
        }

        header("Location: addholiday.php" . $fr_param);
        exit;
    }

    $is_edit = false;
    $edit_id = 0;
    $edit_title = '';
    $edit_date = '';

    if (isset($_GET['edt']) && is_numeric($_GET['edt'])) {
        $edit_id = (int)$_GET['edt'];
        $stmt = mysqli_prepare($GLOBALS["conn"], "SELECT `38hol_title`, `38hol_date` FROM eg_holiday WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $edit_id);
        mysqli_stmt_execute($stmt);
        $result_edit = mysqli_stmt_get_result($stmt);
        if ($result_edit && $row_edit = mysqli_fetch_array($result_edit)) {
            $is_edit = true;
            $edit_title = $row_edit["38hol_title"] ?? '';
            $edit_date = $row_edit["38hol_date"] ?? '';
        }
        mysqli_stmt_close($stmt);
    }
?>
<!DOCTYPE HTML>
<html lang='en'>

<head>
    <meta charset="utf-8">
    <title><?php echo htmlspecialchars($product_name, ENT_QUOTES, 'UTF-8');?> : <?php echo $is_edit ? 'Update Holiday' : 'Holiday & Working Days'; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="../<?php echo htmlspecialchars($mini_icon_path, ENT_QUOTES, 'UTF-8');?>" rel="icon" type="image/png" />
    <link href="../assets/styles/style.css" rel="stylesheet" type="text/css">
</head>

<body>
    <?php
        if (!isset($_REQUEST['fr']) || $_REQUEST['fr'] != '1') {
            include_once '../includes/loggedinfo.php';
        }
    ?>
    
    <div class="app-container">
        <?php
            if (!empty($_SESSION['flash_holiday_msg'])) {
                $hmsg = $_SESSION['flash_holiday_msg'];
                $htype = htmlspecialchars($hmsg['type'] ?? 'info', ENT_QUOTES, 'UTF-8');
                $htext = $hmsg['text'] ?? '';
                echo "<div class='alert alert-{$htype}'>{$htext}</div>";
                unset($_SESSION['flash_holiday_msg']);
            }
        
            $queryW = "select * from eg_working_days";
            $resultW = mysqli_query($GLOBALS["conn"], $queryW);
            $myrowW = mysqli_fetch_array($resultW);
            $mon=$myrowW["mon"] ?? '0';
            $tue=$myrowW["tue"] ?? '0';
            $wed=$myrowW["wed"] ?? '0';
            $thu=$myrowW["thu"] ?? '0';
            $fri=$myrowW["fri"] ?? '0';
            $sat=$myrowW["sat"] ?? '0';
            $sun=$myrowW["sun"] ?? '0';
            $csrfToken = htmlspecialchars($_SESSION['token'] ?? '', ENT_QUOTES, 'UTF-8');
            $frParam = htmlspecialchars($_REQUEST['fr'] ?? '', ENT_QUOTES, 'UTF-8');
        ?>

        <div class="card mb-3">
            <div class="card-header">
                <strong>Library Official Working Days</strong>
            </div>
            <div class="card-body">
                <form action="addholiday.php" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="token" value="<?php echo $csrfToken; ?>" />
                    <input type="hidden" name="fr" value="<?php echo $frParam; ?>" />
                    <div class="d-flex flex-wrap gap-3 mb-3">
                        <label class="form-check-label"><input type="checkbox" name="monday" value="1" <?php if ($mon == '1') {echo "checked";}?>> Monday</label>
                        <label class="form-check-label"><input type="checkbox" name="tuesday" value="1" <?php if ($tue == '1') {echo "checked";}?>> Tuesday</label>
                        <label class="form-check-label"><input type="checkbox" name="wednesday" value="1" <?php if ($wed == '1') {echo "checked";}?>> Wednesday</label>
                        <label class="form-check-label"><input type="checkbox" name="thursday" value="1" <?php if ($thu == '1') {echo "checked";}?>> Thursday</label>
                        <label class="form-check-label"><input type="checkbox" name="friday" value="1" <?php if ($fri == '1') {echo "checked";}?>> Friday</label>
                        <label class="form-check-label"><input type="checkbox" name="saturday" value="1" <?php if ($sat == '1') {echo "checked";}?>> Saturday</label>
                        <label class="form-check-label"><input type="checkbox" name="sunday" value="1" <?php if ($sun == '1') {echo "checked";}?>> Sunday</label>
                    </div>

                    <input type="hidden" name="submitting" value="TRUE" />
                    <input type="submit" class="btn btn-primary w-100" name="Submit1" value="Save Working Days" />
                </form>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong><?php echo $is_edit ? "Update Holiday (#$edit_id)" : "Add New Holiday"; ?></strong>
                <?php if ($is_edit): ?>
                    <span class="badge badge-info">Editing Mode</span>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <form action="addholiday.php" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="token" value="<?php echo $csrfToken; ?>" />
                    <input type="hidden" name="fr" value="<?php echo $frParam; ?>" />
                    <div class="form-group">
                        <label class="form-label">Holiday Description:</label>
                        <input type="text" name="title1" maxlength="70" value="<?php echo htmlspecialchars($is_edit ? $edit_title : '', ENT_QUOTES, 'UTF-8'); ?>" required />
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Date (Format: dd/mm/yyyy e.g. 31/12/2026):</label>
                        <input type="text" name="date1" maxlength="50" placeholder="dd/mm/yyyy" value="<?php echo htmlspecialchars($is_edit ? $edit_date : '', ENT_QUOTES, 'UTF-8'); ?>" required />
                    </div>

                    <?php if ($is_edit): ?>
                        <input type="hidden" name="edt_id" value="<?php echo $edit_id; ?>" />
                        <input type="hidden" name="submitted" value="update" />
                        <div class="d-flex gap-2">
                            <input type="submit" class="btn btn-primary w-100" name="Submit1" value="Update Holiday" />
                            <a href="addholiday.php<?php echo $fr_param; ?>" class="btn btn-secondary w-100 text-center">Cancel</a>
                        </div>
                    <?php else: ?>
                        <input type="hidden" name="submitted" value="add" />
                        <input type="submit" class="btn btn-primary w-100" name="Submit1" value="Add Holiday" />
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">
                <strong>Library Holiday Schedule</strong>
            </div>
            <div class="card-body p-0">
                <table class="table-modern m-0">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 50px;">#</th>
                        <th>Holiday Description</th>
                        <th style="width:160px;">Date</th>
                        <th class="text-center" style="width:140px;">Options</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                    $queryT = "select id, `38hol_date`, str_to_date(`38hol_date`, '%d/%m/%Y') as `38hol_date2`, `38hol_title` from eg_holiday order by `38hol_date2`";
                    $resultT = mysqli_query($GLOBALS["conn"], $queryT);
                    $n = 1;
                    $has_holidays = false;
                    while ($resultT && $myrow=mysqli_fetch_array($resultT)) {
                        $has_holidays = true;
                        $idT=(int)$myrow["id"];
                        $titleT=$myrow["38hol_title"] ?? '';
                        $dateT=$myrow["38hol_date"] ?? '';
                        $rowHighlight = ($is_edit && $idT === $edit_id) ? " style='background-color: rgba(59, 130, 246, 0.08);'" : "";
                        
                        echo "<tr class='table-row'{$rowHighlight}>";
                            echo "<td class='text-center'>$n</td>";
                            echo "<td><strong>" . htmlspecialchars($titleT, ENT_QUOTES, 'UTF-8') . "</strong>" . ($is_edit && $idT === $edit_id ? " <span class='badge badge-info ms-1'>Editing</span>" : "") . "</td>";
                            echo "<td>" . htmlspecialchars($dateT, ENT_QUOTES, 'UTF-8') . "</td>";
                            echo "<td class='text-center'>";
                                echo "<div class='d-flex justify-content-center gap-1'>";
                                $editUrl = "addholiday.php?edt=$idT" . ($frParam != '' ? "&fr=$frParam" : "");
                                echo "<a class='btn btn-secondary btn-sm' title='Update this record' href='{$editUrl}'>Edit</a>";
                                echo "<form method='post' action='addholiday.php' style='display:inline;' onsubmit=\"return confirm('Are you sure to delete this holiday?');\">";
                                echo "<input type='hidden' name='token' value='$csrfToken'>";
                                echo "<input type='hidden' name='fr' value='$frParam'>";
                                echo "<input type='hidden' name='action' value='del_holiday'>";
                                echo "<input type='hidden' name='del_id' value='$idT'>";
                                echo "<button type='submit' class='btn btn-danger btn-sm'>Delete</button>";
                                echo "</form>";
                                echo "</div>";
                            echo "</td>";
                        echo "</tr>";
                        
                        $n = $n + 1;
                    }
                    if (!$has_holidays) {
                        echo "<tr><td colspan='4' class='text-center text-muted p-3'>No holidays registered yet.</td></tr>";
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
