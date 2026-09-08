<!DOCTYPE HTML>
<?php
    session_start();define('includeExist', true);
    include_once '../includes/access_logged.php';
    include_once '../config.php';
    include_once '../includes/stat_cache.php';
    
    $get_hitsDate = $_GET['hitsDate'] ?? '';
    if (preg_match('/^(ALL|All|[0-9]{2})\/[0-9]{4}$/i', $get_hitsDate) || (strlen($get_hitsDate) == 10 && preg_match('/^[0-9\/]+$/', $get_hitsDate))) {
        $hitsDate = $get_hitsDate;
    } else {
        $hitsDate = '01/1922';
    }

    if (stripos($hitsDate, 'ALL/') === 0) {
        $year_val = substr($hitsDate, 4);
        $month_val = '12';
        $is_all_year = true;
        $range = StatCache::get_year_timestamp_range($year_val);
        $display_date_label = "All Months of " . htmlspecialchars($year_val, ENT_QUOTES, 'UTF-8');
    } else {
        $parts = explode('/', $hitsDate);
        $month_val = $parts[0] ?? '01';
        $year_val = $parts[1] ?? '1970';
        $is_all_year = false;
        $range = StatCache::get_month_timestamp_range($month_val, $year_val);
        $display_date_label = htmlspecialchars($hitsDate, ENT_QUOTES, 'UTF-8');
    }

    $safe_hits_date = StatCache::sanitize_key($hitsDate);
    $cache_key = "search_hits_details_" . $safe_hits_date;

    $fetch_func = function() use ($range) {
        // 1. IP subnet summary
        $stmtIP = mysqli_prepare($GLOBALS["conn"], "SELECT count(distinct id, substring_index(`38ipaddr`,'.',3)) as total1c, substring_index(`38ipaddr`,'.',3) as `38ipaddr` FROM eg_userlog_det WHERE `38logdate` >= ? AND `38logdate` <= ? AND `38keyword` IS NOT NULL AND TRIM(`38keyword`) != '' GROUP BY substring_index(`38ipaddr`,'.',3) ORDER BY total1c DESC");
        mysqli_stmt_bind_param($stmtIP, "ii", $range['start'], $range['end']);
        mysqli_stmt_execute($stmtIP);
        $resultIP = mysqli_stmt_get_result($stmtIP);
        
        $ip_rows = [];
        if ($resultIP) {
            while ($myrowIP = mysqli_fetch_assoc($resultIP)) {
                $ip_rows[] = [
                    'ipaddr' => $myrowIP["38ipaddr"],
                    'hits' => (int)$myrowIP["total1c"]
                ];
            }
        }
        mysqli_stmt_close($stmtIP);

        // 2. Hits details list
        $stmt1 = mysqli_prepare($GLOBALS["conn"], "SELECT `38keyword`, `38logdate`, `38ipaddr` FROM eg_userlog_det WHERE `38logdate` >= ? AND `38logdate` <= ? AND `38keyword` IS NOT NULL AND TRIM(`38keyword`) != '' ORDER BY id DESC");
        mysqli_stmt_bind_param($stmt1, "ii", $range['start'], $range['end']);
        mysqli_stmt_execute($stmt1);
        $result1 = mysqli_stmt_get_result($stmt1);
        
        $log_rows = [];
        if ($result1) {
            while ($myrow = mysqli_fetch_assoc($result1)) {
                $log_rows[] = [
                    'keyword' => $myrow["38keyword"] ?? '',
                    'logdate' => $myrow["38logdate"] ?? '',
                    'ipaddr' => $myrow["38ipaddr"] ?? ''
                ];
            }
        }
        mysqli_stmt_close($stmt1);

        return [
            'ip_rows' => $ip_rows,
            'log_rows' => $log_rows,
            'total_count' => count($log_rows)
        ];
    };

    if ($is_all_year) {
        if (StatCache::is_past_year($year_val)) {
            $res = StatCache::remember_month($cache_key, 12, $year_val, $fetch_func);
            $ip_rows = $res['data']['ip_rows'] ?? [];
            $log_rows = $res['data']['log_rows'] ?? [];
            $num_results_affected = $res['data']['total_count'] ?? 0;
            $is_cached = $res['is_cached'];
        } else {
            $data = $fetch_func();
            $ip_rows = $data['ip_rows'];
            $log_rows = $data['log_rows'];
            $num_results_affected = $data['total_count'];
            $is_cached = false;
        }
    } else {
        $res = StatCache::remember_month($cache_key, $month_val, $year_val, $fetch_func);
        $ip_rows = $res['data']['ip_rows'] ?? [];
        $log_rows = $res['data']['log_rows'] ?? [];
        $num_results_affected = $res['data']['total_count'] ?? 0;
        $is_cached = $res['is_cached'];
    }
?>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title><?php echo htmlspecialchars($product_name, ENT_QUOTES, 'UTF-8');?> : Search Hits Details (<?php echo $display_date_label;?>)</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="../<?php echo htmlspecialchars($mini_icon_path, ENT_QUOTES, 'UTF-8');?>" rel="icon" type="image/png" />
    <link href="../assets/styles/style.css" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="../assets/fontawesome/css/all.min.css">
</head>
<body class="bg-white p-3">
    <div class="app-container">
        <!-- IP summary -->
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><strong>IP Subnet Hits Breakdown for:</strong> <?php echo $display_date_label;?></span>
                <?php echo StatCache::render_badge($is_cached); ?>
            </div>
            <div class="card-body">
                <div class="d-flex flex-wrap gap-2">
                <?php
                    if (!empty($ip_rows)) {
                        foreach ($ip_rows as $ip_item) {
                            $ipaddrIP = htmlspecialchars($ip_item["ipaddr"], ENT_QUOTES, 'UTF-8');
                            $total1cIP = (int)$ip_item["hits"];
                            echo "<span class='badge badge-secondary p-2'><code>$ipaddrIP.*</code> &rarr; <strong>$total1cIP hits</strong></span>";
                        }
                    } else {
                        echo "<span class='text-muted'>No IP records found for this timeframe.</span>";
                    }
                ?>
                </div>
            </div>
        </div>
        
        <!-- Hits details -->
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><strong>Search Query Log:</strong> <?php echo $display_date_label;?></span>
                <span class="badge badge-info"><?php echo $num_results_affected;?> recorded hit(s)</span>
            </div>
            <div class="card-body p-0">
                <table class="table-modern m-0">
                <thead>
                    <tr>
                        <th class="text-center w-auto">#</th>
                        <th>Search Keyword / Phrase</th>
                        <th class="text-center" style="width:200px;">Logged Timestamp</th>
                        <th class="text-center" style="width:160px;">Client IP Address</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                    $n = 1;
                    if (!empty($log_rows)) {
                        foreach ($log_rows as $log_item) {
                            $keyword2 = htmlspecialchars($log_item["keyword"], ENT_QUOTES, 'UTF-8');
                            $datelog2 = is_numeric($log_item["logdate"]) ? date("D d/m/Y h:i a", (int)$log_item["logdate"]) : htmlspecialchars($log_item["logdate"], ENT_QUOTES, 'UTF-8');
                            $ipaddr2 = htmlspecialchars($log_item["ipaddr"], ENT_QUOTES, 'UTF-8');
                            
                            echo "<tr class='table-row'>";
                            echo "<td class='text-center'>$n</td>";
                            echo "<td><strong>$keyword2</strong></td>";
                            echo "<td class='text-center text-muted small'>$datelog2</td>";
                            echo "<td class='text-center'><code>$ipaddr2</code></td>";
                            echo "</tr>";
                            $n++;
                        }
                    } else {
                        echo "<tr><td colspan='4' class='text-center text-muted p-3'>No search logs found for this date.</td></tr>";
                    }
                ?>
                </tbody>
                </table>
            </div>
        </div>

        <div class="text-center my-3">
            <button type="button" class="btn btn-secondary btn-sm" onclick="window.close();">Close Window</button>
        </div>
    </div>
</body>
</html>
