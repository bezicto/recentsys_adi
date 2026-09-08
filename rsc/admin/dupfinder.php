<!DOCTYPE HTML>
<?php
    session_start();define('includeExist', true);
    include_once '../includes/access_logged.php';
    include_once '../config.php';
?>

<html lang='en'>
<head>
    <meta charset="utf-8">
    <title><?php echo htmlspecialchars($product_name, ENT_QUOTES, 'UTF-8');?> : Duplicate Finder</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="../<?php echo htmlspecialchars($mini_icon_path, ENT_QUOTES, 'UTF-8');?>" rel="icon" type="image/png" />
    <link href="../assets/styles/style.css" rel="stylesheet" type="text/css">
</head>

<body>
    <?php include_once '../includes/loggedinfo.php';?>
            
    <div class="app-container">
        <div class="card mb-3">
            <div class="card-header">
                <strong>Detected Duplicate Records</strong>
            </div>
            <div class="card-body p-0">
                <?php
                    $query1 = "SELECT count(*) as num, `38title` FROM eg_item GROUP BY `38title` HAVING count(*) > 1 ORDER BY `38title` ASC";
                    $result1 = mysqli_query($GLOBALS["conn"], $query1);
                ?>
                <table class="table-modern m-0">
                <thead>
                    <tr>
                        <th class="text-center w-auto">#</th>
                        <th>Catalog Record Title</th>
                        <th class="text-center" style="width:200px;">Duplicate Count (Approx.)</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                    $n = 1;
                    $has_dups = false;
                    while ($myrow=mysqli_fetch_array($result1)) {
                        $has_dups = true;
                        $num2=$myrow["num"];
                        $tajuk2=$myrow["38title"];
                        
                        $tajuk2converted = stripslashes(str_replace('"', '&#34;', $tajuk2));
                        $tajuk2converted = str_replace('\'s', '', $tajuk2converted);
                        $tajuk2converted = str_replace('\'', '', $tajuk2converted);
                        $searchurl = '../index2.php?scstr='.urlencode($tajuk2converted).'&scflag=Search&sctype=All+Type';
                        
                        echo "<tr class='table-row'>";
                        echo "<td class='text-center'>$n</td>";
                        echo "<td><a href=\"" . htmlspecialchars($searchurl, ENT_QUOTES, 'UTF-8') . "\" class='font-bold'>" . htmlspecialchars($tajuk2, ENT_QUOTES, 'UTF-8') . "</a></td>";
                        echo "<td class='text-center'><span class='badge badge-warning'>$num2 copies</span></td>";
                        echo "</tr>";
                        $n = $n + 1;
                    }
                    if (!$has_dups) {
                        echo "<tr><td colspan='3' class='text-center text-muted p-3'>No duplicate records detected in the catalog.</td></tr>";
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
