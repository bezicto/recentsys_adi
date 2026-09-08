<!DOCTYPE HTML>
<?php
    session_start();define('includeExist', true);
    include_once '../includes/access_logged.php';
    include_once '../config.php';
    include_once '../includes/stat_cache.php';

    $get_m = $_GET['m'] ?? '';
    if (preg_match('/^(ALL|All|[0-9]{2})\/[0-9]{4}$/i', $get_m)) {
        $m = $get_m;
    } else {
        $m = '01/1922';
    }

    if (stripos($m, 'ALL/') === 0) {
        $year_val = substr($m, 4);
        $month_val = '12';
        $is_all_year = true;
        $range = StatCache::get_year_timestamp_range($year_val);
        $display_m_label = "All Months of " . htmlspecialchars($year_val, ENT_QUOTES, 'UTF-8');
    } else {
        $parts = explode('/', $m);
        $month_val = $parts[0] ?? '01';
        $year_val = $parts[1] ?? '1970';
        $is_all_year = false;
        $range = StatCache::get_month_timestamp_range($month_val, $year_val);
        $display_m_label = htmlspecialchars($m, ENT_QUOTES, 'UTF-8');
    }

    $safe_m = StatCache::sanitize_key($m);
    $cache_key = "fine_details_" . $safe_m;

    $fetch_func = function() use ($range) {
        $stmt = mysqli_prepare($GLOBALS["conn"], "SELECT `39patron`, `41f_paidon`, SUM(`41f_received_amount`) as `41f_received_amount`, `41f_receipt_id` FROM eg_item_charge WHERE `41f_paidon` >= ? AND `41f_paidon` <= ? AND `41f_pay` = 'YES' GROUP BY `41f_receipt_id`, `39patron`, `41f_paidon` ORDER BY `41f_paidon` ASC");
        mysqli_stmt_bind_param($stmt, "ii", $range['start'], $range['end']);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        $fines = [];
        $total = 0.0;
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $amt = (float)($row['41f_received_amount'] ?? 0);
                $total += $amt;
                $fines[] = [
                    'patron' => $row['39patron'] ?? '',
                    'paidon' => (int)($row['41f_paidon'] ?? 0),
                    'amount' => $amt,
                    'receipt_id' => $row['41f_receipt_id'] ?? ''
                ];
            }
        }
        mysqli_stmt_close($stmt);

        return [
            'fines' => $fines,
            'total' => $total
        ];
    };

    if ($is_all_year) {
        if (StatCache::is_past_year($year_val)) {
            $res = StatCache::remember_month($cache_key, 12, $year_val, $fetch_func);
            $fines_data = $res['data']['fines'] ?? [];
            $total = $res['data']['total'] ?? 0.0;
            $is_cached = $res['is_cached'];
        } else {
            $data = $fetch_func();
            $fines_data = $data['fines'];
            $total = $data['total'];
            $is_cached = false;
        }
    } else {
        $res = StatCache::remember_month($cache_key, $month_val, $year_val, $fetch_func);
        $fines_data = $res['data']['fines'] ?? [];
        $total = $res['data']['total'] ?? 0.0;
        $is_cached = $res['is_cached'];
    }

    $n = 1;
?>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title><?php echo htmlspecialchars($product_name, ENT_QUOTES, 'UTF-8');?> : Fine Collection for <?php echo $display_m_label;?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="../<?php echo htmlspecialchars($mini_icon_path, ENT_QUOTES, 'UTF-8');?>" rel="icon" type="image/png" />
    <link href="../assets/styles/style.css" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="../assets/fontawesome/css/all.min.css">
</head>
<body class="bg-white p-3">
    <div class="app-container">
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><strong>Fine Collection Report for:</strong> <?php echo $display_m_label;?></span>
                <?php echo StatCache::render_badge($is_cached); ?>
            </div>
            <div class="card-body p-0">
                <table class="table-modern m-0">
                <thead>
                    <tr>
                        <th class="text-center w-auto">#</th>
                        <th>Patron Name / ID</th>
                        <th class="text-center" style="width:220px;">Payment Date &amp; Time</th>
                        <th class="text-end" style="width:140px;">Amount (<?php echo htmlspecialchars($currency_SHORT, ENT_QUOTES, 'UTF-8');?>)</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                    if (!empty($fines_data)) {
                        foreach ($fines_data as $fine_item) {
                            $patron = htmlspecialchars($fine_item["patron"], ENT_QUOTES, 'UTF-8');
                            $amount = (float)$fine_item["amount"];
                            $paid_date = date('D, Y-m-d h:i:s a', (int)$fine_item["paidon"]);
                            
                            echo "<tr class='table-row'>";
                            echo "<td class='text-center'>$n</td>";
                            echo "<td><strong>$patron</strong></td>";
                            echo "<td class='text-center text-muted small'>$paid_date</td>";
                            echo "<td class='text-end font-bold text-success'>" . number_format($amount, 2) . "</td>";
                            echo "</tr>";
                            $n++;
                        }
                    } else {
                        echo "<tr><td colspan='4' class='text-center text-muted p-3'>No fine collections recorded for this month.</td></tr>";
                    }
                ?>
                </tbody>
                <tfoot>
                    <tr class="bg-card font-bold">
                        <td colspan="3" class="text-end">Total Fine Collections:</td>
                        <td class="text-end text-success"><?php echo htmlspecialchars($currency_SHORT, ENT_QUOTES, 'UTF-8') . ' ' . number_format($total, 2);?></td>
                    </tr>
                </tfoot>
                </table>
            </div>
        </div>
        <div class="text-center my-3">
            <button type="button" class="btn btn-secondary btn-sm" onclick="window.close();">Close Window</button>
        </div>
    </div>
</body>
</html>
