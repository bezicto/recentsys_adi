<!DOCTYPE HTML>
<?php
    session_start();define('includeExist', true);
    include_once '../includes/access_logged.php';
    include_once '../config.php';

    if (isset($_GET['det']) && is_numeric($_GET['det'])) {
        $id = (int)$_GET['det'];
    } else {
        $id = 0;
    }
?>

<html lang='en'>

<head>
    <meta charset="utf-8">
    <title><?php echo htmlspecialchars($product_name, ENT_QUOTES, 'UTF-8');?> : Item Access History</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="../<?php echo htmlspecialchars($mini_icon_path, ENT_QUOTES, 'UTF-8');?>" rel="icon" type="image/png" />
    <link href="../assets/styles/style.css" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="../assets/fontawesome/css/all.min.css">
</head>

<body class="bg-white p-3">
    <div class="app-container">
        <?php
            include_once '../includes/paging-p1.php';
                            
            $id_int = (int)$id;
            $offset_int = (int)$offset;
            $rowsPerPage_int = (int)$rowsPerPage;
            $stmt1 = mysqli_prepare($GLOBALS["conn"], "SELECT SQL_CALC_FOUND_ROWS `39ipaddr`, `39logdate` FROM eg_item_det WHERE eg_item_id=? ORDER BY id DESC LIMIT ?, ?");
            mysqli_stmt_bind_param($stmt1, "iii", $id_int, $offset_int, $rowsPerPage_int);
            mysqli_stmt_execute($stmt1);
            $result1 = mysqli_stmt_get_result($stmt1);
            
            include_once '../includes/paging-p2.php';
        ?>

        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><strong>Item Access Log:</strong> Item #<?php echo $id;?></span>
                <span class="badge badge-info"><?php echo $num_results_affected;?> recorded access(es)</span>
            </div>
            <div class="card-body p-0">
                <table class="table-modern m-0">
                <thead>
                    <tr>
                        <th class="text-center w-auto">#</th>
                        <th>Client IP Address</th>
                        <th class="text-center" style="width:200px;">Accessed On</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                    $n = $offset + 1;
                    $has_items = false;
                    while ($myrow=mysqli_fetch_array($result1)) {
                        $has_items = true;
                        $ipaddr2=$myrow["39ipaddr"];
                        $logdate2=is_numeric($myrow["39logdate"]) ? date("D d/m/Y h:i a", (int)$myrow["39logdate"]) : $myrow["39logdate"];
                        
                        echo "<tr class='table-row'>";
                        echo "<td class='text-center'>$n</td>";
                        echo "<td><code>$ipaddr2</code></td>";
                        echo "<td class='text-center text-muted small'>$logdate2</td>";
                        echo "</tr>";
                                                                                        
                        $n = $n + 1;
                    }
                    mysqli_stmt_close($stmt1);
                    if (!$has_items) {
                        echo "<tr><td colspan='3' class='text-center text-muted p-3'>No access history found for this item.</td></tr>";
                    }
                ?>
                </tbody>
                </table>
            </div>
        </div>

        <?php
            if ($maxPage > 1) {
                echo "<div class='pagination-bar my-3'>";
                if ($pageNum > 1) {
                    $page = $pageNum - 1;
                    echo "<a class='pagination-link' href=\"$self?det=$id&page=1\">&laquo; First</a>";
                    echo "<a class='pagination-link' href=\"$self?det=$id&page=$page\">&lsaquo; Prev</a>";
                }
                echo "<span class='pagination-current'>Page $pageNum of $maxPage</span>";
                if ($pageNum < $maxPage) {
                    $page = $pageNum + 1;
                    echo "<a class='pagination-link' href=\"$self?det=$id&page=$page\">Next &rsaquo;</a>";
                    echo "<a class='pagination-link' href=\"$self?det=$id&page=$maxPage\">Last &raquo;</a>";
                }
                echo "</div>";
            }
        ?>

        <div class="text-center my-3">
            <button type="button" class="btn btn-secondary btn-sm" onclick="window.close();">Close Window</button>
        </div>
        
        <?php include_once '../includes/footerbar.php';?>
    </div>
</body>
</html>
