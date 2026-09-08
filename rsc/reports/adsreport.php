<!DOCTYPE HTML>
<?php
    session_start();define('includeExist', true);
    include_once '../includes/access_logged.php';
    include_once '../config.php';
    include_once '../includes/stat_cache.php';

    if (isset($_GET['toggle']) && !is_numeric($_GET['toggle'])) {
        echo "<div class='container-narrow my-5 text-center alert alert-danger'><h2>Forbidden: Illegal Access</h2></div>";
        exit;
    }
    $current_tab = isset($_GET['toggle']) && is_numeric($_GET['toggle']) ? (string)(int)$_GET['toggle'] : '1';

    $flash_msg = '';
    // Handle Cache Purge (SUPER admin only)
    if (isset($_POST['purge_cache']) && ($_SESSION['editmode'] ?? '') === 'SUPER') {
        $deleted_count = StatCache::clear_all();
        $flash_msg = "<div class='alert alert-success mb-3'><i class='fa-solid fa-circle-check me-2'></i>Successfully purged <strong>$deleted_count</strong> cached statistics files. Cache will rebuild automatically on demand.</div>";
    }
?>

<html lang='en'>
<head>
    <meta charset="utf-8">
    <title><?php echo htmlspecialchars($product_name, ENT_QUOTES, 'UTF-8');?> : Statistical Reports</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="../<?php echo htmlspecialchars($mini_icon_path, ENT_QUOTES, 'UTF-8');?>" rel="icon" type="image/png" />
    <link href="../assets/styles/style.css" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="../assets/fontawesome/css/all.min.css">
</head>

<body>
    <?php include_once '../includes/loggedinfo.php';?>
                    
    <div class="app-container">
        <?php if (!empty($flash_msg)) echo $flash_msg; ?>

        <!-- Sub-Navigation Toolbar -->
        <div class="card mb-3">
            <div class="card-body p-2">
                <div class="admin-toolbar d-flex flex-wrap gap-2 justify-content-between align-items-center">
                    <div class="d-flex flex-wrap gap-1">
                        <a href="adsreport.php?toggle=1" class="btn btn-sm <?php echo $current_tab == '1' ? 'btn-primary' : 'btn-secondary';?>">Data Managers Report</a>
                        <a href="adsreport.php?toggle=2" class="btn btn-sm <?php echo $current_tab == '2' ? 'btn-primary' : 'btn-secondary';?>">Type Statistics</a>
                        <a href="adsreport.php?toggle=3" class="btn btn-sm <?php echo $current_tab == '3' ? 'btn-primary' : 'btn-secondary';?>">Search Analytics</a>
                        <a href="adsreport.php?toggle=4" class="btn btn-sm <?php echo $current_tab == '4' ? 'btn-primary' : 'btn-secondary';?>">Loan Analytics</a>
                        <a href="adsreport.php?toggle=5" class="btn btn-sm <?php echo $current_tab == '5' ? 'btn-primary' : 'btn-secondary';?>">Fine Collections</a>
                    </div>
                    <?php if (($_SESSION['editmode'] ?? '') === 'SUPER') { ?>
                    <div>
                        <a href="adsreport.php?toggle=6" class="btn btn-sm <?php echo $current_tab == '6' ? 'btn-primary' : 'btn-secondary';?>" title="Manage Performance &amp; JSON Caching"><i class="fa-solid fa-bolt me-1 text-warning"></i>Cache Manager</a>
                    </div>
                    <?php } ?>
                </div>
            </div>
        </div>

        <?php if ($current_tab == '1') {
            // High-performance batch aggregation: eliminate N+1 queries
            $query1 = "SELECT username, name, lastlogin, online FROM eg_auth WHERE allowed='SUPER' OR allowed='TRUE'";
            $result1 = mysqli_query($GLOBALS["conn"], $query1);

            // Fetch input totals per user in a single grouped query
            $input_counts = [];
            $res_in = mysqli_query($GLOBALS["conn"], "SELECT `40inputby`, count(id) as totalid FROM eg_item GROUP BY `40inputby`");
            if ($res_in) {
                while ($rin = mysqli_fetch_assoc($res_in)) {
                    if (!empty($rin['40inputby'])) {
                        $input_counts[$rin['40inputby']] = (int)$rin['totalid'];
                    }
                }
            }

            // Fetch update totals per user in a single grouped query
            $update_counts = [];
            $res_up = mysqli_query($GLOBALS["conn"], "SELECT `40lastupdateby`, count(id) as totalida FROM eg_item GROUP BY `40lastupdateby`");
            if ($res_up) {
                while ($rup = mysqli_fetch_assoc($res_up)) {
                    if (!empty($rup['40lastupdateby'])) {
                        $update_counts[$rup['40lastupdateby']] = (int)$rup['totalida'];
                    }
                }
            }
        ?>
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <strong>Data Managers &amp; Cataloging Statistics</strong>
                    <span class="badge badge-info"><i class="fa-solid fa-bolt me-1"></i>Optimized Batch Query</span>
                </div>
                <div class="card-body p-0">
                    <table class="table-modern m-0">
                    <thead>
                        <tr>
                            <th class="text-center w-auto">#</th>
                            <th>Staff Name &amp; Username</th>
                            <th class="text-center" style="width:160px;">Total Records (Input)</th>
                            <th class="text-center" style="width:160px;">Total Records (Edits)</th>
                            <th class="text-center" style="width:200px;">Last Logged-In</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                        $n = 1;
                        while ($myrow = mysqli_fetch_array($result1)) {
                            $username2 = $myrow["username"];
                            $name2 = $myrow["name"];
                            $lastlogin2 = $myrow["lastlogin"];
                            $online2 = $myrow["online"];
                            
                            $num_results_affected = $input_counts[$username2] ?? 0;
                            $num_results_affecteda = $update_counts[$username2] ?? 0;
                                                            
                            echo "<tr class='table-row'>";
                            echo "<td class='text-center'>$n</td>";
                            echo "<td><strong>" . htmlspecialchars($name2, ENT_QUOTES, 'UTF-8') . "</strong>";
                            if (($_SESSION['editmode'] ?? '') == 'SUPER') {
                                echo " <span class='text-muted small'>[" . htmlspecialchars($username2, ENT_QUOTES, 'UTF-8') . "]</span>";
                            }
                            echo "</td>";
                            echo "<td class='text-center'><a href='adsreport_details.php?inf=" . urlencode($username2) . "&infname=" . urlencode($name2) . "' class='badge badge-info'>$num_results_affected</a></td>";
                            echo "<td class='text-center'><span class='badge badge-secondary'>$num_results_affecteda</span></td>";
                            echo "<td class='text-center'>";
                            if ($online2 == 'ON') {
                                echo htmlspecialchars($lastlogin2, ENT_QUOTES, 'UTF-8') . " <span class='badge badge-success ms-1'>ONLINE</span>";
                            } else {
                                echo "<span class='text-muted'>" . htmlspecialchars($lastlogin2, ENT_QUOTES, 'UTF-8') . "</span>";
                            }
                            echo "</td></tr>";
                                                                                                            
                            $n = $n + 1;
                        }
                    ?>
                    </tbody>
                    </table>
                </div>
            </div>
        <?php } ?>
    
        <?php if ($current_tab == '2') {
            // High-performance batch aggregation for material types
            // 1. Material type names lookup
            $types_map = [];
            $res_ty = mysqli_query($GLOBALS["conn"], "SELECT `38typeid`, `38type` FROM eg_type ORDER BY `38typeid`");
            if ($res_ty) {
                while ($rty = mysqli_fetch_assoc($res_ty)) {
                    $types_map[(string)$rty['38typeid']] = $rty['38type'];
                }
            }

            // 2. Count & cumulative hits grouped by type
            $stats_map = [];
            $res_st = mysqli_query($GLOBALS["conn"], "SELECT `39type`, count(id) as totalid, sum(`41hits`) as totalhits FROM eg_item GROUP BY `39type`");
            if ($res_st) {
                while ($rst = mysqli_fetch_assoc($res_st)) {
                    $stats_map[(string)$rst['39type']] = [
                        'totalid' => (int)$rst['totalid'],
                        'totalhits' => (int)$rst['totalhits']
                    ];
                }
            }

            // 3. Unique hits grouped by type
            $unique_map = [];
            $res_uq = mysqli_query($GLOBALS["conn"], "SELECT `39type`, count(id) as uniquehits FROM eg_item WHERE `41hits` > 0 GROUP BY `39type`");
            if ($res_uq) {
                while ($ruq = mysqli_fetch_assoc($res_uq)) {
                    $unique_map[(string)$ruq['39type']] = (int)$ruq['uniquehits'];
                }
            }

            // 4. Total loaned per type
            $loan_map = [];
            $res_ln = mysqli_query($GLOBALS["conn"], "SELECT eg_item.`39type`, count(eg_item_charge.id) as num_type FROM eg_item_charge INNER JOIN eg_item_copies ON eg_item_charge.`39accessnum` = eg_item_copies.`39accessnum` INNER JOIN eg_item ON eg_item_copies.eg_item_id = eg_item.id GROUP BY eg_item.`39type`");
            if ($res_ln) {
                while ($rln = mysqli_fetch_assoc($res_ln)) {
                    $loan_map[(string)$rln['39type']] = (int)$rln['num_type'];
                }
            }

            // Get distinct types present in library
            $query2 = "SELECT DISTINCT `39type` FROM eg_item";
            $result2 = mysqli_query($GLOBALS["conn"], $query2);
        ?>
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <strong>Material Type Statistics &amp; Circulation Load</strong>
                    <span class="badge badge-info"><i class="fa-solid fa-bolt me-1"></i>Optimized Aggregation</span>
                </div>
                <div class="card-body p-0">
                    <table class="table-modern m-0">
                    <thead>
                        <tr>
                            <th class="text-center w-auto">#</th>
                            <th>Material Type</th>
                            <th class="text-center" style="width:140px;">Total Indexed</th>
                            <th class="text-center" style="width:180px;">Accessed (Cumulative)</th>
                            <th class="text-center" style="width:160px;">Accessed (Unique)</th>
                            <th class="text-center" style="width:140px;">Total Loaned</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                        $m = 1;
                        $jumlah = 0; $jumlahhits = 0; $jumlahunique = 0; $jumlahType = 0;
                        while ($myrow = mysqli_fetch_array($result2)) {
                            $jenisbahan2 = (string)$myrow["39type"];
                            $jenisTy = $types_map[$jenisbahan2] ?? "Type #$jenisbahan2";
                            
                            $num_results_affected = $stats_map[$jenisbahan2]['totalid'] ?? 0;
                            $num_hits_affected2 = $stats_map[$jenisbahan2]['totalhits'] ?? 0;
                            $num_unique_hits2 = $unique_map[$jenisbahan2] ?? 0;
                            $num_results_type2 = $loan_map[$jenisbahan2] ?? 0;
                            
                            echo "<tr class='table-row'>";
                            echo "<td class='text-center'>$m</td>";
                            echo "<td><strong>" . htmlspecialchars($jenisTy, ENT_QUOTES, 'UTF-8') . "</strong></td>";
                            echo "<td class='text-center'><a class='badge badge-info' href='adsreport_typedetails.php?type=" . urlencode($jenisbahan2) . "&typetext=" . urlencode($jenisTy) . "'>$num_results_affected</a></td>";
                            echo "<td class='text-center'><a class='badge badge-secondary' href='adsreport_typeaccess.php?type=" . urlencode($jenisbahan2) . "&typetext=" . urlencode($jenisTy) . "'>$num_hits_affected2</a></td>";
                            echo "<td class='text-center'>$num_unique_hits2</td>";
                            echo "<td class='text-center'><span class='badge badge-warning'>$num_results_type2</span></td>";
                            echo "</tr>";
                                                                
                            $jumlah += $num_results_affected;
                            $jumlahhits += $num_hits_affected2;
                            $jumlahunique += $num_unique_hits2;
                            $jumlahType += $num_results_type2;
                            $m = $m + 1;
                        }
                    ?>
                    </tbody>
                    <tfoot>
                        <tr class="bg-card font-bold">
                            <td colspan="2" class="text-end">Total Overall:</td>
                            <td class="text-center"><?php echo $jumlah;?></td>
                            <td class="text-center"><?php echo $jumlahhits;?></td>
                            <td class="text-center"><?php echo $jumlahunique;?></td>
                            <td class="text-center"><?php echo $jumlahType;?></td>
                        </tr>
                    </tfoot>
                    </table>
                </div>
            </div>
        <?php } ?>
    
        <?php if ($current_tab == '3') {
            $querySUM = "SELECT count(id) as totalhitSUM FROM eg_userlog_det WHERE `38keyword` IS NOT NULL AND TRIM(`38keyword`) != ''";
            $resultSUM = mysqli_query($GLOBALS["conn"], $querySUM);
            $myrowSUM = mysqli_fetch_array($resultSUM);
            $numSUM = (int)($myrowSUM["totalhitSUM"] ?? 0);
        ?>
            <div class="card mb-3">
                <div class="card-header">
                    <strong>Search Statistical Tools &amp; Analytics</strong>
                </div>
                <div class="card-body">
                    <div class="p-3 bg-card border rounded mb-3">
                        <span class="text-muted d-block small">Total Search Queries to Date:</span>
                        <h3 class="m-0 text-primary"><?php echo "$numSUM";?> Hits</h3>
                    </div>

                    <div class="border rounded p-3">
                        <label class="form-label"><strong>Filter Search Hits by Month:</strong></label>
                        <form name="getMonthStat" action="adsreport.php?toggle=3" method="post" class="d-flex flex-wrap gap-2 align-items-center">
                            <select name='monthly'>
                                <option value='ALL' <?php if ((isset($_POST['monthly'])) && ($_POST['monthly'] == 'ALL')) {echo 'selected';}?>>All Months</option>
                                <option value='01' <?php if ((isset($_POST['monthly'])) && ($_POST['monthly'] == '01')) {echo 'selected';}?>>Jan</option>
                                <option value='02' <?php if ((isset($_POST['monthly'])) && ($_POST['monthly'] == '02')) {echo 'selected';}?>>Feb</option>
                                <option value='03' <?php if ((isset($_POST['monthly'])) && ($_POST['monthly'] == '03')) {echo 'selected';}?>>Mar</option>
                                <option value='04' <?php if ((isset($_POST['monthly'])) && ($_POST['monthly'] == '04')) {echo 'selected';}?>>Apr</option>
                                <option value='05' <?php if ((isset($_POST['monthly'])) && ($_POST['monthly'] == '05')) {echo 'selected';}?>>May</option>
                                <option value='06' <?php if ((isset($_POST['monthly'])) && ($_POST['monthly'] == '06')) {echo 'selected';}?>>June</option>
                                <option value='07' <?php if ((isset($_POST['monthly'])) && ($_POST['monthly'] == '07')) {echo 'selected';}?>>Jul</option>
                                <option value='08' <?php if ((isset($_POST['monthly'])) && ($_POST['monthly'] == '08')) {echo 'selected';}?>>Aug</option>
                                <option value='09' <?php if ((isset($_POST['monthly'])) && ($_POST['monthly'] == '09')) {echo 'selected';}?>>Sep</option>
                                <option value='10' <?php if ((isset($_POST['monthly'])) && ($_POST['monthly'] == '10')) {echo 'selected';}?>>Oct</option>
                                <option value='11' <?php if ((isset($_POST['monthly'])) && ($_POST['monthly'] == '11')) {echo 'selected';}?>>Nov</option>
                                <option value='12' <?php if ((isset($_POST['monthly'])) && ($_POST['monthly'] == '12')) {echo 'selected';}?>>Dec</option>
                            </select>
                            <input type="text" name="yearly" size="5" <?php if (isset($_POST['yearly'])) {echo 'value="'.htmlspecialchars($_POST['yearly'], ENT_QUOTES, 'UTF-8').'"';} else {echo 'value="'.date('Y').'"';}?> maxlength="4"/>
                            <input type="submit" class="btn btn-primary btn-sm" value="Query Month" />
                            
                            <?php
                                if ((isset($_POST['monthly'])) && (isset($_POST['yearly']))) {
                                    $monthly_val = $_POST['monthly'];
                                    $yearly_val = $_POST['yearly'];
                                    
                                    if ($monthly_val === 'ALL') {
                                        $hitsSTRING = 'ALL/' . $yearly_val;
                                        $display_label = "in all months of " . htmlspecialchars($yearly_val, ENT_QUOTES, 'UTF-8');
                                        $range = StatCache::get_year_timestamp_range($yearly_val);
                                        
                                        $cache_key = "search_hits_year_" . $yearly_val;
                                        if (StatCache::is_past_year($yearly_val)) {
                                            $res = StatCache::remember_month($cache_key, 12, $yearly_val, function() use ($range) {
                                                $stmtSTAT = mysqli_prepare($GLOBALS["conn"], "SELECT count(id) as totalhitSTAT FROM eg_userlog_det WHERE `38logdate` >= ? AND `38logdate` <= ? AND `38keyword` IS NOT NULL AND TRIM(`38keyword`) != ''");
                                                mysqli_stmt_bind_param($stmtSTAT, "ii", $range['start'], $range['end']);
                                                mysqli_stmt_execute($stmtSTAT);
                                                $resultSTAT = mysqli_stmt_get_result($stmtSTAT);
                                                $myrowSTAT = mysqli_fetch_array($resultSTAT);
                                                mysqli_stmt_close($stmtSTAT);
                                                return (int)($myrowSTAT["totalhitSTAT"] ?? 0);
                                            });
                                            $numSTAT = $res['data'];
                                            $is_cached = $res['is_cached'];
                                        } else {
                                            $stmtSTAT = mysqli_prepare($GLOBALS["conn"], "SELECT count(id) as totalhitSTAT FROM eg_userlog_det WHERE `38logdate` >= ? AND `38logdate` <= ? AND `38keyword` IS NOT NULL AND TRIM(`38keyword`) != ''");
                                            mysqli_stmt_bind_param($stmtSTAT, "ii", $range['start'], $range['end']);
                                            mysqli_stmt_execute($stmtSTAT);
                                            $resultSTAT = mysqli_stmt_get_result($stmtSTAT);
                                            $myrowSTAT = mysqli_fetch_array($resultSTAT);
                                            $numSTAT = (int)($myrowSTAT["totalhitSTAT"] ?? 0);
                                            mysqli_stmt_close($stmtSTAT);
                                            $is_cached = false;
                                        }
                                    } else {
                                        $hitsSTRING = $monthly_val . '/' . $yearly_val;
                                        $display_label = "in " . htmlspecialchars($hitsSTRING, ENT_QUOTES, 'UTF-8');
                                        $range = StatCache::get_month_timestamp_range($monthly_val, $yearly_val);
                                        
                                        $cache_key = "search_hits_m" . $monthly_val . "_" . $yearly_val;
                                        $res = StatCache::remember_month($cache_key, $monthly_val, $yearly_val, function() use ($range) {
                                            $stmtSTAT = mysqli_prepare($GLOBALS["conn"], "SELECT count(id) as totalhitSTAT FROM eg_userlog_det WHERE `38logdate` >= ? AND `38logdate` <= ? AND `38keyword` IS NOT NULL AND TRIM(`38keyword`) != ''");
                                            mysqli_stmt_bind_param($stmtSTAT, "ii", $range['start'], $range['end']);
                                            mysqli_stmt_execute($stmtSTAT);
                                            $resultSTAT = mysqli_stmt_get_result($stmtSTAT);
                                            $myrowSTAT = mysqli_fetch_array($resultSTAT);
                                            mysqli_stmt_close($stmtSTAT);
                                            return (int)($myrowSTAT["totalhitSTAT"] ?? 0);
                                        });
                                        $numSTAT = $res['data'];
                                        $is_cached = $res['is_cached'];
                                    }

                                    $badge_html = StatCache::render_badge($is_cached, 'me-1');
                                    echo "<div class='ms-auto d-flex align-items-center gap-2'>$badge_html<a target='_blank' class='btn btn-warning btn-sm' href='adsreport_hitsdetails.php?hitsDate=" . urlencode($hitsSTRING) . "'>$numSTAT hits $display_label &rarr;</a></div>";
                                }
                            ?>
                        </form>
                    </div>
                </div>
            </div>
        <?php } ?>
    
        <?php if ($current_tab == '4') {
            $querySUMl = "SELECT count(id) as totalLoan FROM eg_item_charge";
            $resultSUMl = mysqli_query($GLOBALS["conn"], $querySUMl);
            $myrowSUMl = mysqli_fetch_array($resultSUMl);
            $numSUMl = $myrowSUMl["totalLoan"] ?? 0;
        ?>
            <div class="card mb-3">
                <div class="card-header">
                    <strong>Loans &amp; Circulation Statistical Tools</strong>
                </div>
                <div class="card-body">
                    <div class="p-3 bg-card border rounded mb-3">
                        <span class="text-muted d-block small">Total Charge / Loan Transactions to Date:</span>
                        <h3 class="m-0 text-primary"><?php echo "$numSUMl";?> Loans</h3>
                    </div>

                    <div class="border rounded p-3">
                        <label class="form-label"><strong>Filter Loans by Month:</strong></label>
                        <form name="getMonthStat" action="adsreport.php?toggle=4" method="post" class="d-flex flex-wrap gap-2 align-items-center">
                            <select name='lmonthly'>
                                <option value='ALL' <?php if ((isset($_POST['lmonthly'])) && ($_POST['lmonthly'] == 'ALL')) {echo 'selected';}?>>All Months</option>
                                <option value='01' <?php if ((isset($_POST['lmonthly'])) && ($_POST['lmonthly'] == '01')) {echo 'selected';}?>>Jan</option>
                                <option value='02' <?php if ((isset($_POST['lmonthly'])) && ($_POST['lmonthly'] == '02')) {echo 'selected';}?>>Feb</option>
                                <option value='03' <?php if ((isset($_POST['lmonthly'])) && ($_POST['lmonthly'] == '03')) {echo 'selected';}?>>Mar</option>
                                <option value='04' <?php if ((isset($_POST['lmonthly'])) && ($_POST['lmonthly'] == '04')) {echo 'selected';}?>>Apr</option>
                                <option value='05' <?php if ((isset($_POST['lmonthly'])) && ($_POST['lmonthly'] == '05')) {echo 'selected';}?>>May</option>
                                <option value='06' <?php if ((isset($_POST['lmonthly'])) && ($_POST['lmonthly'] == '06')) {echo 'selected';}?>>June</option>
                                <option value='07' <?php if ((isset($_POST['lmonthly'])) && ($_POST['lmonthly'] == '07')) {echo 'selected';}?>>Jul</option>
                                <option value='08' <?php if ((isset($_POST['lmonthly'])) && ($_POST['lmonthly'] == '08')) {echo 'selected';}?>>Aug</option>
                                <option value='09' <?php if ((isset($_POST['lmonthly'])) && ($_POST['lmonthly'] == '09')) {echo 'selected';}?>>Sep</option>
                                <option value='10' <?php if ((isset($_POST['lmonthly'])) && ($_POST['lmonthly'] == '10')) {echo 'selected';}?>>Oct</option>
                                <option value='11' <?php if ((isset($_POST['lmonthly'])) && ($_POST['lmonthly'] == '11')) {echo 'selected';}?>>Nov</option>
                                <option value='12' <?php if ((isset($_POST['lmonthly'])) && ($_POST['lmonthly'] == '12')) {echo 'selected';}?>>Dec</option>
                            </select>
                            <input type="text" name="lyearly" size="5" <?php if (isset($_POST['lyearly'])) {echo 'value="'.htmlspecialchars($_POST['lyearly'], ENT_QUOTES, 'UTF-8').'"';} else {echo 'value="'.date('Y').'"';}?> maxlength="4"/>
                            <input type="submit" class="btn btn-primary btn-sm" value="Query Month" />
                            
                            <?php
                                if ((isset($_POST['lmonthly'])) && (isset($_POST['lyearly']))) {
                                    $lmonthly_val = $_POST['lmonthly'];
                                    $lyearly_val = $_POST['lyearly'];
                                    
                                    if ($lmonthly_val === 'ALL') {
                                        $hitsSTRINGl = 'ALL/' . $lyearly_val;
                                        $display_labell = "in all months of " . htmlspecialchars($lyearly_val, ENT_QUOTES, 'UTF-8');
                                        $range = StatCache::get_year_timestamp_range($lyearly_val);
                                        
                                        $cache_key = "loan_stat_year_" . $lyearly_val;
                                        if (StatCache::is_past_year($lyearly_val)) {
                                            $res = StatCache::remember_month($cache_key, 12, $lyearly_val, function() use ($range) {
                                                $stmt = mysqli_prepare($GLOBALS["conn"], "SELECT count(id) as totalhitSTAT FROM eg_item_charge WHERE `39charged_on` >= ? AND `39charged_on` <= ?");
                                                mysqli_stmt_bind_param($stmt, "ii", $range['start'], $range['end']);
                                                mysqli_stmt_execute($stmt);
                                                $result = mysqli_stmt_get_result($stmt);
                                                $row = mysqli_fetch_assoc($result);
                                                mysqli_stmt_close($stmt);
                                                return (int)($row['totalhitSTAT'] ?? 0);
                                            });
                                            $numSTATl = $res['data'];
                                            $is_cached = $res['is_cached'];
                                        } else {
                                            $stmt = mysqli_prepare($GLOBALS["conn"], "SELECT count(id) as totalhitSTAT FROM eg_item_charge WHERE `39charged_on` >= ? AND `39charged_on` <= ?");
                                            mysqli_stmt_bind_param($stmt, "ii", $range['start'], $range['end']);
                                            mysqli_stmt_execute($stmt);
                                            $result = mysqli_stmt_get_result($stmt);
                                            $row = mysqli_fetch_assoc($result);
                                            $numSTATl = (int)($row['totalhitSTAT'] ?? 0);
                                            mysqli_stmt_close($stmt);
                                            $is_cached = false;
                                        }
                                    } else {
                                        $hitsSTRINGl = $lmonthly_val . '/' . $lyearly_val;
                                        $display_labell = "in " . htmlspecialchars($hitsSTRINGl, ENT_QUOTES, 'UTF-8');
                                        $range = StatCache::get_month_timestamp_range($lmonthly_val, $lyearly_val);
                                        
                                        $cache_key = "loan_stat_m" . $lmonthly_val . "_" . $lyearly_val;
                                        $res = StatCache::remember_month($cache_key, $lmonthly_val, $lyearly_val, function() use ($range) {
                                            $stmt = mysqli_prepare($GLOBALS["conn"], "SELECT count(id) as totalhitSTAT FROM eg_item_charge WHERE `39charged_on` >= ? AND `39charged_on` <= ?");
                                            mysqli_stmt_bind_param($stmt, "ii", $range['start'], $range['end']);
                                            mysqli_stmt_execute($stmt);
                                            $result = mysqli_stmt_get_result($stmt);
                                            $row = mysqli_fetch_assoc($result);
                                            mysqli_stmt_close($stmt);
                                            return (int)($row['totalhitSTAT'] ?? 0);
                                        });
                                        $numSTATl = $res['data'];
                                        $is_cached = $res['is_cached'];
                                    }

                                    $badge_html = StatCache::render_badge($is_cached, 'me-1');
                                    echo "<div class='ms-auto d-flex align-items-center gap-2'>$badge_html<a target='_blank' class='btn btn-warning btn-sm' href='adsreport_loandetails.php?hitsDate=" . urlencode($hitsSTRINGl) . "'>$numSTATl transactions $display_labell &rarr;</a></div>";
                                }
                            ?>
                        </form>
                    </div>
                </div>
            </div>
        <?php } ?>

        <?php if ($current_tab == '5') {
            $querySUMlf = "SELECT sum(`41f_received_amount`) as totalFineCollection FROM eg_item_charge";
            $resultSUMlf = mysqli_query($GLOBALS["conn"], $querySUMlf);
            $myrowSUMlf = mysqli_fetch_array($resultSUMlf);
            $numSUMlf = number_format((float)($myrowSUMlf["totalFineCollection"] ?? 0), 2);
        ?>
            <div class="card mb-3">
                <div class="card-header">
                    <strong>Fine Collections &amp; Financial Summary</strong>
                </div>
                <div class="card-body">
                    <div class="p-3 bg-card border rounded mb-3">
                        <span class="text-muted d-block small">Total Fine Collections to Date:</span>
                        <h3 class="m-0 text-success"><?php echo "$currency_SHORT $numSUMlf";?></h3>
                    </div>

                    <div class="border rounded p-3">
                        <label class="form-label"><strong>Filter Fine Collections by Month:</strong></label>
                        <form name="getMonthFine" action="adsreport.php?toggle=5" method="post" class="d-flex flex-wrap gap-2 align-items-center">
                            <select name='fmonthly'>
                                <option value='ALL' <?php if ((isset($_POST['fmonthly'])) && ($_POST['fmonthly'] == 'ALL')) {echo 'selected';}?>>All Months</option>
                                <option value='01' <?php if ((isset($_POST['fmonthly'])) && ($_POST['fmonthly'] == '01')) {echo 'selected';}?>>Jan</option>
                                <option value='02' <?php if ((isset($_POST['fmonthly'])) && ($_POST['fmonthly'] == '02')) {echo 'selected';}?>>Feb</option>
                                <option value='03' <?php if ((isset($_POST['fmonthly'])) && ($_POST['fmonthly'] == '03')) {echo 'selected';}?>>Mar</option>
                                <option value='04' <?php if ((isset($_POST['fmonthly'])) && ($_POST['fmonthly'] == '04')) {echo 'selected';}?>>Apr</option>
                                <option value='05' <?php if ((isset($_POST['fmonthly'])) && ($_POST['fmonthly'] == '05')) {echo 'selected';}?>>May</option>
                                <option value='06' <?php if ((isset($_POST['fmonthly'])) && ($_POST['fmonthly'] == '06')) {echo 'selected';}?>>June</option>
                                <option value='07' <?php if ((isset($_POST['fmonthly'])) && ($_POST['fmonthly'] == '07')) {echo 'selected';}?>>Jul</option>
                                <option value='08' <?php if ((isset($_POST['fmonthly'])) && ($_POST['fmonthly'] == '08')) {echo 'selected';}?>>Aug</option>
                                <option value='09' <?php if ((isset($_POST['fmonthly'])) && ($_POST['fmonthly'] == '09')) {echo 'selected';}?>>Sep</option>
                                <option value='10' <?php if ((isset($_POST['fmonthly'])) && ($_POST['fmonthly'] == '10')) {echo 'selected';}?>>Oct</option>
                                <option value='11' <?php if ((isset($_POST['fmonthly'])) && ($_POST['fmonthly'] == '11')) {echo 'selected';}?>>Nov</option>
                                <option value='12' <?php if ((isset($_POST['fmonthly'])) && ($_POST['fmonthly'] == '12')) {echo 'selected';}?>>Dec</option>
                            </select>
                            <input type="text" name="fyearly" size="5" <?php if (isset($_POST['fyearly'])) {echo 'value="'.htmlspecialchars($_POST['fyearly'], ENT_QUOTES, 'UTF-8').'"';} else {echo 'value="'.date('Y').'"';}?> maxlength="4"/>
                            <input type="submit" class="btn btn-primary btn-sm" value="Query Month" />
                            
                            <?php
                                if ((isset($_POST['fmonthly'])) && (isset($_POST['fyearly']))) {
                                    $fmonthly_val = $_POST['fmonthly'];
                                    $fyearly_val = $_POST['fyearly'];
                                    
                                    if ($fmonthly_val === 'ALL') {
                                        $hitsSTRINGlf = 'ALL/' . $fyearly_val;
                                        $display_labellf = "in all months of " . htmlspecialchars($fyearly_val, ENT_QUOTES, 'UTF-8');
                                        $range = StatCache::get_year_timestamp_range($fyearly_val);
                                        
                                        $cache_key = "fine_sum_year_" . $fyearly_val;
                                        if (StatCache::is_past_year($fyearly_val)) {
                                            $res = StatCache::remember_month($cache_key, 12, $fyearly_val, function() use ($range) {
                                                $stmt = mysqli_prepare($GLOBALS["conn"], "SELECT sum(`41f_received_amount`) as totalFine FROM eg_item_charge WHERE `41f_paidon` >= ? AND `41f_paidon` <= ? AND `41f_pay` = 'YES'");
                                                mysqli_stmt_bind_param($stmt, "ii", $range['start'], $range['end']);
                                                mysqli_stmt_execute($stmt);
                                                $result = mysqli_stmt_get_result($stmt);
                                                $row = mysqli_fetch_assoc($result);
                                                mysqli_stmt_close($stmt);
                                                return (float)($row['totalFine'] ?? 0.0);
                                            });
                                            $fine_raw = $res['data'];
                                            $is_cached = $res['is_cached'];
                                        } else {
                                            $stmt = mysqli_prepare($GLOBALS["conn"], "SELECT sum(`41f_received_amount`) as totalFine FROM eg_item_charge WHERE `41f_paidon` >= ? AND `41f_paidon` <= ? AND `41f_pay` = 'YES'");
                                            mysqli_stmt_bind_param($stmt, "ii", $range['start'], $range['end']);
                                            mysqli_stmt_execute($stmt);
                                            $result = mysqli_stmt_get_result($stmt);
                                            $row = mysqli_fetch_assoc($result);
                                            $fine_raw = (float)($row['totalFine'] ?? 0.0);
                                            mysqli_stmt_close($stmt);
                                            $is_cached = false;
                                        }
                                    } else {
                                        $hitsSTRINGlf = $fmonthly_val . '/' . $fyearly_val;
                                        $display_labellf = "in " . htmlspecialchars($hitsSTRINGlf, ENT_QUOTES, 'UTF-8');
                                        $range = StatCache::get_month_timestamp_range($fmonthly_val, $fyearly_val);
                                        
                                        $cache_key = "fine_sum_m" . $fmonthly_val . "_" . $fyearly_val;
                                        $res = StatCache::remember_month($cache_key, $fmonthly_val, $fyearly_val, function() use ($range) {
                                            $stmt = mysqli_prepare($GLOBALS["conn"], "SELECT sum(`41f_received_amount`) as totalFine FROM eg_item_charge WHERE `41f_paidon` >= ? AND `41f_paidon` <= ? AND `41f_pay` = 'YES'");
                                            mysqli_stmt_bind_param($stmt, "ii", $range['start'], $range['end']);
                                            mysqli_stmt_execute($stmt);
                                            $result = mysqli_stmt_get_result($stmt);
                                            $row = mysqli_fetch_assoc($result);
                                            mysqli_stmt_close($stmt);
                                            return (float)($row['totalFine'] ?? 0.0);
                                        });
                                        $fine_raw = $res['data'];
                                        $is_cached = $res['is_cached'];
                                    }

                                    $numSTATlf = number_format((float)$fine_raw, 2);
                                    $badge_html = StatCache::render_badge($is_cached, 'me-1');
                                    echo "<div class='ms-auto d-flex align-items-center gap-2'>$badge_html<a href='adsreport_fine.php?m=" . urlencode($hitsSTRINGlf) . "' target='_blank' class='btn btn-success btn-sm'>$currency_SHORT $numSTATlf $display_labellf &rarr;</a></div>";
                                }
                            ?>
                        </form>
                    </div>
                </div>
            </div>
        <?php } ?>

        <?php if ($current_tab == '6' && ($_SESSION['editmode'] ?? '') === 'SUPER') {
            $cache_info = StatCache::get_stats();
        ?>
            <!-- Stats Cache Management (Super Admin Only) -->
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <strong><i class="fa-solid fa-bolt text-warning me-2"></i>JSON Statistics Cache Manager</strong>
                    <span class="badge badge-success"><i class="fa-solid fa-check-circle me-1"></i>Active</span>
                </div>
                <div class="card-body">
                    <div class="alert alert-info mb-3">
                        <i class="fa-solid fa-circle-info me-2"></i>
                        Statistical queries for past months and years are automatically compiled and stored in atomic JSON format inside <code>$stat_cache_directory</code>. Current month and upcoming months are always fetched live to ensure real-time accuracy while eliminating database strain during peak hours.
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <div class="p-3 bg-card border rounded">
                                <span class="text-muted d-block small">Cache Directory:</span>
                                <code class="small text-break"><?php echo htmlspecialchars($cache_info['dir'], ENT_QUOTES, 'UTF-8'); ?></code>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-card border rounded">
                                <span class="text-muted d-block small">Cached JSON Files:</span>
                                <h3 class="m-0 text-primary"><?php echo $cache_info['count']; ?> files</h3>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-card border rounded">
                                <span class="text-muted d-block small">Total Cache Disk Size:</span>
                                <h3 class="m-0 text-success"><?php echo $cache_info['size_formatted']; ?></h3>
                            </div>
                        </div>
                    </div>

                    <div class="border-top pt-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <strong>Purge Cache:</strong>
                            <p class="text-muted small m-0">Clearing the cache will remove all pre-computed historical JSON records. They will seamlessly recompile on their next query.</p>
                        </div>
                        <form method="post" action="adsreport.php?toggle=6" onsubmit="return confirm('Are you sure you want to purge all cached statistics files?');">
                            <button type="submit" name="purge_cache" value="1" class="btn btn-danger btn-sm">
                                <i class="fa-solid fa-trash me-1"></i> Purge All Stats Cache
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        <?php } ?>
                        
        <div class="text-center my-3">
            <a class="btn btn-secondary btn-sm" href="../index2.php">&larr; Back to start page</a>
        </div>
        
        <?php include_once '../includes/footerbar.php';?>
    </div>
</body>
</html>
