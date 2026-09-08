<!DOCTYPE HTML>
<?php
    session_start();define('includeExist', true);
    include_once '../includes/access_logged.php';
    include_once '../config.php';
    include_once '../includes/stat_cache.php';

    if (!isset($_GET["type"]) || !is_numeric($_GET["type"])) {
        echo "<div class='container-narrow my-5 text-center alert alert-danger'><h2>Forbidden: Illegal Access</h2></div>";
        exit;
    }
    
    $typeid = (int)$_GET["type"];
    $typetext = $_GET["typetext"] ?? 'Material Type';
    $inputyear = isset($_GET["inputyear"]) && is_numeric($_GET["inputyear"]) ? $_GET["inputyear"] : date('Y');
?>

<html lang='en'>
<head>
    <meta charset="utf-8">
    <title><?php echo htmlspecialchars($product_name, ENT_QUOTES, 'UTF-8');?> : Type Index Statistics (<?php echo htmlspecialchars($typetext, ENT_QUOTES, 'UTF-8');?>)</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="../<?php echo htmlspecialchars($mini_icon_path, ENT_QUOTES, 'UTF-8');?>" rel="icon" type="image/png" />
    <link href="../assets/styles/style.css" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="../assets/fontawesome/css/all.min.css">
</head>

<body>
    <?php include_once '../includes/loggedinfo.php';?>
                    
    <div class="app-container">
        <div class="card mw-800 mb-3">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <strong>Indexing Statistics:</strong> <?php echo htmlspecialchars($typetext, ENT_QUOTES, 'UTF-8');?>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="small font-bold">Year:</span>
                    <?php
                        $query3 = "select distinct SUBSTRING(`40inputdate`,7) AS inputyear from eg_item order by inputyear";
                        $result3 = mysqli_query($GLOBALS["conn"], $query3);
                        echo "<select name='inputyear' class='form-control form-control-sm' ONCHANGE='location = this.options[this.selectedIndex].value;'>";
                        while ($myrow3=mysqli_fetch_array($result3)) {
                            $yr=$myrow3["inputyear"];
                            echo "<option value=\"adsreport_typedetails.php?inputyear=$yr&type=$typeid&typetext=".urlencode($typetext)."\"";
                            if ($inputyear == $yr) {
                                echo " selected";
                            }
                            echo ">$yr</option>";
                        }
                        echo "</select>";
                    ?>
                </div>
            </div>
            <div class="card-body p-0">
                <table class="table-modern m-0">
                <thead>
                    <tr>
                        <th class="text-center" style="width:140px;">Month / Year</th>
                        <th class="text-center" style="width:180px;">Total Indexed (Monthly)</th>
                        <th class="text-center">Cumulative Total</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                    $months_names = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                    $key_prefix = "type_index_" . (int)$typeid;

                    // Hybrid 12-month caching: past cached in JSON, active month live, future 0
                    $breakdown = StatCache::remember_year_breakdown($key_prefix, $inputyear, function($m, $y) use ($typeid) {
                        $counterc = sprintf('%02d', $m);
                        $date_like = '%' . $counterc . '/' . $y;
                        $stmt2 = mysqli_prepare($GLOBALS["conn"], "SELECT count(id) as totalid FROM eg_item WHERE `39type`=? AND `40inputdate` LIKE ?");
                        $type_str = (string)$typeid;
                        mysqli_stmt_bind_param($stmt2, "ss", $type_str, $date_like);
                        mysqli_stmt_execute($stmt2);
                        $result2 = mysqli_stmt_get_result($stmt2);
                        $myrow = mysqli_fetch_array($result2);
                        mysqli_stmt_close($stmt2);
                        return (int)($myrow["totalid"] ?? 0);
                    });

                    $month_counts = $breakdown['months'];
                    $is_cached_breakdown = $breakdown['is_cached'];

                    $jumlah = 0;
                    for ($counter = 1; $counter <= 12; $counter += 1) {
                        $num_results_affected = $month_counts[$counter - 1] ?? 0;
                        $jumlah += $num_results_affected;

                        echo "<tr class='table-row'>";
                        echo "<td class='text-center'><strong>" . $months_names[$counter-1] . " " . htmlspecialchars($inputyear, ENT_QUOTES, 'UTF-8') . "</strong></td>";
                        echo "<td class='text-center'><span class='badge " . ($num_results_affected > 0 ? "badge-primary font-bold" : "badge-secondary") . "'>$num_results_affected</span></td>";
                        echo "<td class='text-center font-bold'>$jumlah</td>";
                        echo "</tr>";
                    }
                ?>
                </tbody>
                <tfoot>
                    <tr class="bg-card font-bold">
                        <td class="text-center d-flex justify-content-between align-items-center">
                            <span>Total Indexed in <?php echo htmlspecialchars($inputyear, ENT_QUOTES, 'UTF-8');?>:</span>
                            <?php echo StatCache::render_badge($is_cached_breakdown); ?>
                        </td>
                        <td colspan="2" class="text-center text-primary"><?php echo $jumlah;?> records</td>
                    </tr>
                </tfoot>
                </table>
            </div>
            <div class="card-footer text-muted small">
                * Note: The cumulative total reflects records cataloged within the selected calendar year only.
            </div>
        </div>

        <div class="text-center my-3">
            <a class="btn btn-secondary btn-sm" href="adsreport.php?toggle=2">&larr; Back to report page</a>
        </div>
        
        <?php include_once '../includes/footerbar.php';?>
    </div>
</body>
</html>
