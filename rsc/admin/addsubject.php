<?php
    session_start();define('includeExist', true);
    include_once '../includes/access_super.php';
    include_once '../config.php';
    include_once '../includes/token_validate.php';

    $fr_param = isset($_POST['fr']) && $_POST['fr'] == '1' ? '?fr=1' : (isset($_GET['fr']) && $_GET['fr'] == '1' ? '?fr=1' : '');

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $proceedAfterToken) {
        if (isset($_POST["action"]) && $_POST["action"] === 'del_subject' && isset($_POST["del_id"]) && is_numeric($_POST["del_id"])) {
            $get_id_del = (int)$_POST["del_id"];
            $stmt = mysqli_prepare($GLOBALS["conn"], "DELETE FROM eg_subjectheading WHERE `43subjectid` = ?");
            mysqli_stmt_bind_param($stmt, "i", $get_id_del);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            $_SESSION['flash_subject_msg'] = ['type' => 'success', 'text' => 'Subject heading deleted successfully.'];
        }

        if (isset($_POST["submitted"]) && $_POST["submitted"] === 'update' && isset($_POST["edt_id"]) && is_numeric($_POST["edt_id"])) {
            $subject_upd = trim($_POST["subject1"] ?? '');
            $acronym_upd = trim($_POST["acronym1"] ?? '');
            $id_upd = (int)$_POST["edt_id"];

            if (!empty($subject_upd) && !empty($acronym_upd) && $id_upd > 0) {
                $stmt = mysqli_prepare($GLOBALS["conn"], "UPDATE eg_subjectheading SET `43subject`=?, `43acronym`=? WHERE `43subjectid`=?");
                mysqli_stmt_bind_param($stmt, "ssi", $subject_upd, $acronym_upd, $id_upd);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
                $_SESSION['flash_subject_msg'] = ['type' => 'success', 'text' => "Subject heading <strong>" . htmlspecialchars("$subject_upd ($acronym_upd)", ENT_QUOTES, 'UTF-8') . "</strong> updated successfully."];
            } else {
                $_SESSION['flash_subject_msg'] = ['type' => 'danger', 'text' => 'Error: Please fill all required fields.'];
            }
        } elseif (isset($_POST["submitted"]) && ($_POST["submitted"] === 'add' || $_POST["submitted"] == 'TRUE')) {
            $subject1 = trim($_POST["subject1"] ?? '');
            $acronym1 = trim($_POST["acronym1"] ?? '');
                        
            if (!empty($acronym1) && !empty($subject1)) {
                $stmt = mysqli_prepare($GLOBALS["conn"], "INSERT INTO eg_subjectheading (`43acronym`, `43subject`) VALUES (?, ?)");
                mysqli_stmt_bind_param($stmt, "ss", $acronym1, $subject1);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
                $_SESSION['flash_subject_msg'] = ['type' => 'success', 'text' => "Subject <strong>" . htmlspecialchars("$subject1 ($acronym1)", ENT_QUOTES, 'UTF-8') . "</strong> added successfully."];
            } else {
                $_SESSION['flash_subject_msg'] = ['type' => 'danger', 'text' => 'Input cancelled: Please fill all required fields.'];
            }
        }

        header("Location: addsubject.php" . $fr_param);
        exit;
    }

    $is_edit = false;
    $edit_id = 0;
    $edit_subject = '';
    $edit_acronym = '';

    if (isset($_GET['edt']) && is_numeric($_GET['edt'])) {
        $edit_id = (int)$_GET['edt'];
        $stmt = mysqli_prepare($GLOBALS["conn"], "SELECT `43subject`, `43acronym` FROM eg_subjectheading WHERE `43subjectid` = ?");
        mysqli_stmt_bind_param($stmt, "i", $edit_id);
        mysqli_stmt_execute($stmt);
        $result_edit = mysqli_stmt_get_result($stmt);
        if ($result_edit && $row_edit = mysqli_fetch_array($result_edit)) {
            $is_edit = true;
            $edit_subject = $row_edit["43subject"] ?? '';
            $edit_acronym = $row_edit["43acronym"] ?? '';
        }
        mysqli_stmt_close($stmt);
    }
?>
<!DOCTYPE HTML>
<html lang='en'>

<head>
    <meta charset="utf-8">
    <title><?php echo htmlspecialchars($product_name, ENT_QUOTES, 'UTF-8');?> : <?php echo $is_edit ? 'Update Subject Heading' : 'Subject Headings'; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="../<?php echo htmlspecialchars($mini_icon_path, ENT_QUOTES, 'UTF-8');?>" rel="icon" type="image/png" />
    <link href="../assets/styles/style.css" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="../assets/fontawesome/css/all.min.css">
</head>

<body>
    <?php
        if (!isset($_REQUEST['fr']) || $_REQUEST['fr'] != '1') {
            include_once '../includes/loggedinfo.php';
        }
    ?>
    
    <div class="app-container">
        <?php
            if (!empty($_SESSION['flash_subject_msg'])) {
                $smsg = $_SESSION['flash_subject_msg'];
                $stype = htmlspecialchars($smsg['type'] ?? 'info', ENT_QUOTES, 'UTF-8');
                $stext = $smsg['text'] ?? '';
                echo "<div class='alert alert-{$stype}'>{$stext}</div>";
                unset($_SESSION['flash_subject_msg']);
            }
            $csrfToken = htmlspecialchars($_SESSION['token'] ?? '', ENT_QUOTES, 'UTF-8');
            $frParam = htmlspecialchars($_REQUEST['fr'] ?? '', ENT_QUOTES, 'UTF-8');
        ?>

        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong><?php echo $is_edit ? "Update Subject Heading (#$edit_id)" : "Add New Subject Heading"; ?></strong>
                <?php if ($is_edit): ?>
                    <span class="badge badge-info">Editing Mode</span>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <form action="addsubject.php" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="token" value="<?php echo $csrfToken; ?>" />
                    <input type="hidden" name="fr" value="<?php echo $frParam; ?>" />
                    <div class="form-group">
                        <label class="form-label">Subject Heading Title:</label>
                        <input type="text" name="subject1" maxlength="70" value="<?php echo htmlspecialchars($is_edit ? $edit_subject : '', ENT_QUOTES, 'UTF-8'); ?>" required />
                    </div>

                    <div class="form-group">
                        <label class="form-label">Subject Code / Classification:</label>
                        <input type="text" name="acronym1" maxlength="50" value="<?php echo htmlspecialchars($is_edit ? $edit_acronym : '', ENT_QUOTES, 'UTF-8'); ?>" required />
                    </div>

                    <?php if ($is_edit): ?>
                        <input type="hidden" name="edt_id" value="<?php echo $edit_id; ?>" />
                        <input type="hidden" name="submitted" value="update" />
                        <div class="d-flex gap-2">
                            <input type="submit" class="btn btn-primary w-100" name="Submit1" value="Update Subject Heading" />
                            <a href="addsubject.php<?php echo $fr_param; ?>" class="btn btn-secondary w-100 text-center">Cancel</a>
                        </div>
                    <?php else: ?>
                        <input type="hidden" name="submitted" value="add" />
                        <input type="submit" class="btn btn-primary w-100" name="Submit1" value="Add Subject Heading" />
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">
                <strong>Subject Headings List</strong>
            </div>
            <div class="card-body p-0">
                <table class="table-modern m-0">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 50px;">#</th>
                        <th>Subject Heading</th>
                        <th style="width:200px;">Subject Code</th>
                        <th class="text-center" style="width:140px;">Options</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                    $queryT = "select `43subjectid`, `43acronym`, `43subject` from eg_subjectheading order by `43acronym`";
                    $resultT = mysqli_query($GLOBALS["conn"], $queryT);
                    $n = 1;
                    $has_subjects = false;
                    while ($resultT && $myrow=mysqli_fetch_array($resultT)) {
                        $has_subjects = true;
                        $subjectidT=(int)$myrow["43subjectid"];
                        $acronymT=$myrow["43acronym"] ?? '';
                        $subjectT=$myrow["43subject"] ?? '';
                        $rowHighlight = ($is_edit && $subjectidT === $edit_id) ? " style='background-color: rgba(59, 130, 246, 0.08);'" : "";
                        
                        echo "<tr class='table-row'{$rowHighlight}>";
                        echo "<td class='text-center'>$n</td>";
                        echo "<td><strong>" . htmlspecialchars($subjectT, ENT_QUOTES, 'UTF-8') . "</strong>" . ($is_edit && $subjectidT === $edit_id ? " <span class='badge badge-info ms-1'>Editing</span>" : "") . "</td>";
                        echo "<td><code>" . htmlspecialchars($acronymT, ENT_QUOTES, 'UTF-8') . "</code></td>";
                        echo "<td class='text-center'>";
                        echo "<div class='d-flex justify-content-center gap-1'>";
                        $editUrl = "addsubject.php?edt=$subjectidT" . ($frParam != '' ? "&fr=$frParam" : "");
                        echo "<a class='btn btn-secondary btn-sm' title='Update this record' href='{$editUrl}'>Edit</a>";
                        echo "<form method='post' action='addsubject.php' style='display:inline;' onsubmit=\"return confirm('Are you sure? You are advised to change all items of this subject heading before proceeding.');\">";
                        echo "<input type='hidden' name='token' value='$csrfToken'>";
                        echo "<input type='hidden' name='fr' value='$frParam'>";
                        echo "<input type='hidden' name='action' value='del_subject'>";
                        echo "<input type='hidden' name='del_id' value='$subjectidT'>";
                        echo "<button type='submit' class='btn btn-danger btn-sm'>Delete</button>";
                        echo "</form>";
                        echo "</div>";
                        echo "</td>";
                        echo "</tr>";
                        
                        $n = $n + 1;
                    }
                    if (!$has_subjects) {
                        echo "<tr><td colspan='4' class='text-center text-muted p-3'>No subject headings registered.</td></tr>";
                    }
                ?>
                </tbody>
                </table>
            </div>
        </div>
            
        <?php if (!isset($_GET['fr']) || $_GET['fr'] != '1') { ?>
            <div class="text-center my-3">
                <a class="btn btn-secondary btn-sm" href="../index2.php">&larr; Back to start page</a>
            </div>
            <?php include_once '../includes/footerbar.php';?>
        <?php } else { ?>
            <div class="text-center my-3">
                <input type="button" class="btn btn-secondary btn-sm" value="Close" onClick="if(window.parent && window.parent.closeAppModal) { window.parent.closeAppModal(false); } else { window.close(); }" />
            </div>
        <?php } ?>
    </div>
</body>
</html>
