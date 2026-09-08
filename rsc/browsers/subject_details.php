<!DOCTYPE HTML>
<?php
    session_start();define('includeExist', true);
    include_once '../config.php';
    include_once '../includes/token_validate.php';
    include_once '../includes/del_code.php';

    if (isset($_GET["subid"]) && is_numeric($_GET["subid"])) {
        $get_subid = $_GET["subid"];
    } else {
        $get_subid = 0;
    }
?>

<html lang='en'>

<head>
    <meta charset="utf-8">
    <title><?php echo $product_name;?> : Subject Heading Details</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="../<?php echo $mini_icon_path;?>" rel="icon" type="image/png" />
    <link href="../assets/styles/style.css" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="../assets/fontawesome/css/all.min.css">
</head>

<body>
    <?php
        include_once '../includes/loggedinfo.php';
            
        $stmtU = mysqli_prepare($GLOBALS["conn"], "SELECT `43acronym`, `43subject` FROM eg_subjectheading WHERE `43subjectid`=?");
        $get_subid_int = (int)$get_subid;
        mysqli_stmt_bind_param($stmtU, "i", $get_subid_int);
        mysqli_stmt_execute($stmtU);
        $resultU = mysqli_stmt_get_result($stmtU);
        $rowU = mysqli_fetch_array($resultU);
        mysqli_stmt_close($stmtU);
        
        $get_subacr = 'N/A';
        $get_subname = '';
        if (isset($rowU['43acronym'])) {
            $get_subacr = $rowU['43acronym'];
            $get_subname = $rowU['43subject'];
        }
        
        include_once '../includes/paging-p1.php';
        
        $like_sub = '%' . $get_subacr . '|%';
        $offset_int = (int)$offset;
        $rowsPerPage_int = (int)$rowsPerPage;
        $stmt1 = mysqli_prepare($GLOBALS["conn"], "SELECT id, `38title`, `38title_b`, `38title_c`, `39type`, `38source` FROM eg_item WHERE `39subjectheading` LIKE ? ORDER BY `38title` LIMIT ?, ?");
        mysqli_stmt_bind_param($stmt1, "sii", $like_sub, $offset_int, $rowsPerPage_int);
        mysqli_stmt_execute($stmt1);
        $result1 = mysqli_stmt_get_result($stmt1);

        //paging 2 start
        $stmt2_count = mysqli_prepare($GLOBALS["conn"], "SELECT count(id) as total2 FROM eg_item WHERE `39subjectheading` LIKE ?");
        mysqli_stmt_bind_param($stmt2_count, "s", $like_sub);
        mysqli_stmt_execute($stmt2_count);
        $result2_count = mysqli_stmt_get_result($stmt2_count);
        $myrow2 = mysqli_fetch_array($result2_count);
        $count2 = $myrow2["total2"] ?? 0;
        mysqli_stmt_close($stmt2_count);
        $maxPage = ceil($count2/$rowsPerPage);
        $self = $_SERVER['PHP_SELF'];
        //paging 2 end
    ?>

    <div class="app-container">
        <?php
            if (!empty($_SESSION['flash_delete_msg'])) {
                $del_msg = $_SESSION['flash_delete_msg'];
                $del_type = htmlspecialchars($del_msg['type'] ?? 'info', ENT_QUOTES, 'UTF-8');
                $del_text = $del_msg['text'] ?? '';
                echo "<div class='alert alert-{$del_type} mb-3'>{$del_text}</div>";
                unset($_SESSION['flash_delete_msg']);
            }
        ?>
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><strong>Subject Classification:</strong> <code><?php echo $get_subacr;?></code> <?php echo !empty($get_subname) ? "- $get_subname" : "";?></span>
                <span class="badge badge-info"><?php echo $count2;?> item(s)</span>
            </div>
            <div class="card-body p-0">
                <table class="table-modern m-0">
                <thead>
                    <tr>
                        <th class="text-center col-num">#</th>
                        <th>Record Title</th>
                        <th style="width:180px;">Material Type</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                    $n = $offset + 1;
                    $has_items = false;
                    $stmtTy = mysqli_prepare($GLOBALS["conn"], "SELECT `38type` FROM eg_type WHERE `38typeid` = ?");
                    while ($myrow=mysqli_fetch_array($result1)) {
                        $has_items = true;
                        $id2=(int)$myrow["id"];
                        $type2=$myrow["39type"];
                        $title2=$myrow["38title"];

                        $title2_b=$myrow["38title_b"] ?? '';
                        $title2_c=$myrow["38title_c"] ?? '';
                        
                        mysqli_stmt_bind_param($stmtTy, "s", $type2);
                        mysqli_stmt_execute($stmtTy);
                        $resultTy = mysqli_stmt_get_result($stmtTy);
                        $myrowTy=mysqli_fetch_array($resultTy);
                        $jenisTy=$myrowTy["38type"] ?? 'General';
                        
                        echo "<tr class='table-row'>";
                        echo "<td class='text-center'>$n</td>";
                        echo "<td>";
                        echo "<a href='../details.php?det=$id2&delr=sjdir&subid=$get_subid&page=$pageNum' class='font-bold'>$title2 $title2_b $title2_c</a>";
                        echo "</td>";
                        echo "<td><span class='badge badge-secondary'>$jenisTy</span></td>";
                        echo "</tr>";
                                                                
                        $n = $n + 1;
                    }
                    mysqli_stmt_close($stmtTy);
                    mysqli_stmt_close($stmt1);
                    if (!$has_items) {
                        echo "<tr><td colspan='3' class='text-center text-muted p-3'>No items registered under this subject heading.</td></tr>";
                    }
                ?>
                </tbody>
                </table>
            </div>
        </div>
        
        <?php
            if ($count2 > $rowsPerPage) {
                echo "<div class='pagination-bar my-3'>";
                if ($pageNum > 1) {
                    $page = $pageNum - 1;
                    echo "<a class='pagination-link' href=\"$self?subid=$get_subid&page=1\">&laquo; First</a>";
                    echo "<a class='pagination-link' href=\"$self?subid=$get_subid&page=$page\">&lsaquo; Prev</a>";
                }
                echo "<span class='pagination-current'>Page $pageNum of $maxPage</span>";
                if ($pageNum < $maxPage) {
                    $page = $pageNum + 1;
                    echo "<a class='pagination-link' href=\"$self?subid=$get_subid&page=$page\">Next &rsaquo;</a>";
                    echo "<a class='pagination-link' href=\"$self?subid=$get_subid&page=$maxPage\">Last &raquo;</a>";
                }
                echo "</div>";
            }
        ?>
        
        <div class="text-center my-3">
            <a class="btn btn-secondary btn-sm" href="subject.php">&larr; Back to subject browser</a>
        </div>
        
        <?php include_once '../includes/footerbar.php';?>
    </div>
</body>
</html>
