<!DOCTYPE HTML>
<?php
    session_start();define('includeExist', true);
    include_once '../config.php';
?>

<html lang='en'>

<head>
    <meta charset="utf-8">
    <title><?php echo $product_name;?> : Subject Heading Browser</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="../<?php echo $mini_icon_path;?>" rel="icon" type="image/png" />
    <link href="../assets/styles/style.css" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="../assets/fontawesome/css/all.min.css">
</head>

<body>
    <?php include_once '../includes/loggedinfo.php';?>

    <div class="app-container">
        <div class="card mw-800 mb-3">
            <div class="card-header">
                <strong>Subject Classification Browser</strong>
            </div>
            <div class="card-body">
                <div class="d-flex flex-column gap-2">
                    <?php
                        $queryU = "select `43subjectid`, `43acronym`, `43subject` from eg_subjectheading order by `43subject`";
                        $resultU = mysqli_query($GLOBALS["conn"], $queryU);
                        $has_sub = false;
                        $stmtSubBahan = mysqli_prepare($GLOBALS["conn"], "SELECT count(id) as totalSubBahan FROM eg_item WHERE `39subjectheading` LIKE ?");
                        while ($row=mysqli_fetch_array($resultU)) {
                            $has_sub = true;
                            $id = (int)$row['43subjectid'];
                            $acronym = $row['43acronym'];
                            
                            $countSubBahan = 0;
                            if ($stmtSubBahan) {
                                $like_pattern = '%' . $acronym . '|%';
                                mysqli_stmt_bind_param($stmtSubBahan, "s", $like_pattern);
                                mysqli_stmt_execute($stmtSubBahan);
                                $resultSubBahan = mysqli_stmt_get_result($stmtSubBahan);
                                $myrowSubBahan=mysqli_fetch_array($resultSubBahan);
                                $countSubBahan=$myrowSubBahan["totalSubBahan"] ?? 0;
                            }
                            
                            echo "<div class='d-flex justify-content-between align-items-center p-2 border-bottom'>";
                            echo "<div><a href='subject_details.php?subid=$id' class='font-bold'>".htmlspecialchars($row['43subject'] ?? '', ENT_QUOTES, 'UTF-8')."</a> <span class='text-muted small ms-2'><code>".htmlspecialchars($acronym, ENT_QUOTES, 'UTF-8')."</code></span></div>";
                            echo "<span class='badge badge-info'>$countSubBahan items</span>";
                            echo "</div>";
                        }
                        if ($stmtSubBahan) {
                            mysqli_stmt_close($stmtSubBahan);
                        }
                        if (!$has_sub) {
                            echo "<p class='text-muted text-center p-3'>No subject headings available.</p>";
                        }
                    ?>
                </div>
            </div>
        </div>
    
        <div class="text-center my-3">
            <a class="btn btn-secondary btn-sm" href="../<?php echo $_SESSION['ref'] ?? 'opac.php';?>">&larr; Back to start page</a>
        </div>
        
        <?php include_once '../includes/footerbar.php';?>
    </div>
</body>
</html>
