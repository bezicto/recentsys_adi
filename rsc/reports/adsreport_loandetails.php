<!DOCTYPE HTML>
<?php
    session_start();define('includeExist', true);
    include_once '../includes/access_logged.php';
    include_once '../config.php';
    include_once '../includes/functions.php';
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
    $cache_key = "loan_details_v4_" . $safe_hits_date;

    $fetch_func = function() use ($range) {
        // High-performance single multi-table JOIN query
        $sql = "SELECT 
                    c.id, 
                    c.`39accessnum`, 
                    c.`39patron`, 
                    c.`39charged_on`, 
                    c.`39charged_by`, 
                    c.`39duedate`, 
                    c.`40dc`, 
                    c.`40dc_on`, 
                    c.`40dc_by`, 
                    COALESCE(b.`38title`, 'Unknown Item') as title, 
                    COALESCE(a.`name`, c.`39patron`) as patron_name, 
                    COALESCE(a.`username`, c.`39patron`) as patron_username,
                    COALESCE(al.`max_day`, 0) as max_day,
                    COALESCE(sc.`name`, c.`39charged_by`) as charged_by_name,
                    COALESCE(sd.`name`, c.`40dc_by`) as discharged_by_name
                FROM eg_item_charge c
                LEFT JOIN eg_item_copies cp ON c.`39accessnum` = cp.`39accessnum`
                LEFT JOIN eg_item b ON cp.eg_item_id = b.id
                LEFT JOIN eg_auth a ON c.`39patron` = a.username
                LEFT JOIN eg_auth_allowedloan al ON a.allowed = al.usertype
                LEFT JOIN eg_auth sc ON c.`39charged_by` = sc.username
                LEFT JOIN eg_auth sd ON c.`40dc_by` = sd.username
                WHERE c.`39charged_on` >= ? AND c.`39charged_on` <= ?
                ORDER BY c.id DESC";

        $stmt = mysqli_prepare($GLOBALS["conn"], $sql);
        mysqli_stmt_bind_param($stmt, "ii", $range['start'], $range['end']);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        $loans = [];
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $patron_user = $row['39patron'] ?? '';
                $charged_on = (int)($row['39charged_on'] ?? 0);
                $duedate_mp = (int)($row['39duedate'] ?? 0);
                $max_day = (int)($row['max_day'] ?? 0);
                if ($max_day <= 0 && !empty($patron_user)) {
                    $max_day = (int)maxday($patron_user);
                }

                $due_date = 0;
                if ($charged_on > 0 && $max_day > 0) {
                    $maxSecond = ($max_day * ($duedate_mp + 1)) * 86400;
                    $beginOfDay_for_charged_on = strtotime("midnight", $charged_on);
                    $calculated_due = $beginOfDay_for_charged_on + $maxSecond + 86399;
                    $due_date = (int)shiftDueDate($calculated_due);
                }

                $loans[] = [
                    'id' => (int)$row['id'],
                    'accession' => $row['39accessnum'] ?? '',
                    'patron' => $patron_user,
                    'charged_on' => $charged_on,
                    'charged_by' => $row['39charged_by'] ?? '',
                    'charged_by_name' => $row['charged_by_name'] ?? ($row['39charged_by'] ?? ''),
                    'due_date' => $due_date,
                    'duedate_mp' => $duedate_mp,
                    'dc' => $row['40dc'] ?? '',
                    'discharged_on' => !empty($row['40dc_on']) ? (int)$row['40dc_on'] : null,
                    'discharged_by' => $row['40dc_by'] ?? '',
                    'discharged_by_name' => $row['discharged_by_name'] ?? ($row['40dc_by'] ?? ''),
                    'title' => $row['title'] ?? 'Unknown Item',
                    'patron_name' => $row['patron_name'] ?? $patron_user,
                    'patron_username' => $row['patron_username'] ?? $patron_user
                ];
            }
        }
        mysqli_stmt_close($stmt);
        return $loans;
    };

    if ($is_all_year) {
        if (StatCache::is_past_year($year_val)) {
            $res = StatCache::remember_month($cache_key, 12, $year_val, $fetch_func);
            $loans_data = $res['data'] ?? [];
            $is_cached = $res['is_cached'];
        } else {
            $loans_data = $fetch_func();
            $is_cached = false;
        }
    } else {
        $res = StatCache::remember_month($cache_key, $month_val, $year_val, $fetch_func);
        $loans_data = $res['data'] ?? [];
        $is_cached = $res['is_cached'];
    }

    $num_results_affected = count($loans_data);
?>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title><?php echo htmlspecialchars($product_name, ENT_QUOTES, 'UTF-8');?> : Loan Transactions (<?php echo $display_date_label;?>)</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="../<?php echo htmlspecialchars($mini_icon_path, ENT_QUOTES, 'UTF-8');?>" rel="icon" type="image/png" />
    <link href="../assets/styles/style.css" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="../assets/fontawesome/css/all.min.css">
</head>
<body class="bg-white p-3">
    <div class="app-container">
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <span><strong>Circulation / Loan Records for:</strong> <?php echo $display_date_label;?></span>
                <div class="d-flex align-items-center gap-2">
                    <?php echo StatCache::render_badge($is_cached); ?>
                    <span class="badge badge-info"><?php echo $num_results_affected;?> loan transaction(s)</span>
                </div>
            </div>
            <div class="card-body p-0">
                <table class="table-modern m-0">
                <thead>
                    <tr>
                        <th class="text-center w-auto">#</th>
                        <th>Material Title &amp; Accession</th>
                        <th>Patron Name &amp; ID</th>
                        <th class="text-center" style="width:190px;">Charged On</th>
                        <th class="text-center" style="width:180px;">Due Date</th>
                        <th class="text-center" style="width:190px;">Discharged On</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                    $n = 1;
                    if (!empty($loans_data)) {
                        foreach ($loans_data as $loan) {
                            $accession2 = htmlspecialchars($loan["accession"] ?? '', ENT_QUOTES, 'UTF-8');
                            $bahanTitle = htmlspecialchars($loan["title"] ?? 'Unknown Item', ENT_QUOTES, 'UTF-8');
                            $patronName = htmlspecialchars($loan["patron_name"] ?? '', ENT_QUOTES, 'UTF-8');
                            $username2 = htmlspecialchars($loan["patron_username"] ?? '', ENT_QUOTES, 'UTF-8');
                            $chargedon2 = !empty($loan["charged_on"]) ? date('d/m/Y H:i:s', (int)$loan["charged_on"]) : '-';

                            $charged_by = $loan["charged_by"] ?? '';
                            $charged_by_name = $loan["charged_by_name"] ?? $charged_by;
                            $charged_by_str = '';
                            if (!empty($charged_by)) {
                                if (!empty($charged_by_name) && $charged_by_name !== $charged_by) {
                                    $charged_by_str = "<div class='text-muted small mt-1'><i class='fa-solid fa-user-check me-1'></i>" . htmlspecialchars($charged_by_name, ENT_QUOTES, 'UTF-8') . " <span class='text-muted'>(" . htmlspecialchars($charged_by, ENT_QUOTES, 'UTF-8') . ")</span></div>";
                                } else {
                                    $charged_by_str = "<div class='text-muted small mt-1'><i class='fa-solid fa-user-check me-1'></i>" . htmlspecialchars($charged_by, ENT_QUOTES, 'UTF-8') . "</div>";
                                }
                            }

                            $is_discharged = (($loan["dc"] ?? '') === 'DC' || !empty($loan["discharged_on"]));
                            $due_date_val = !empty($loan["due_date"]) ? (int)$loan["due_date"] : 0;
                            $duedate_mp = (int)($loan["duedate_mp"] ?? 0);

                            $renewed_str = '';
                            if ($duedate_mp > 0) {
                                $renewed_str = "<div class='text-muted small mt-1'><i class='fa-solid fa-arrows-rotate me-1'></i>Renewed {$duedate_mp}x</div>";
                            }

                            if ($due_date_val > 0) {
                                $duedate_formatted = date('d/m/Y', $due_date_val);
                                $due_cell = "<span class='text-muted small font-weight-bold'>$duedate_formatted</span>$renewed_str";
                                if (!$is_discharged && time() > $due_date_val) {
                                    $due_cell .= "<div class='mt-1'><span class='badge badge-danger'>Overdue</span></div>";
                                }
                            } else {
                                $due_cell = "<span class='text-muted small'>-</span>";
                            }

                            $dc_by = $loan["discharged_by"] ?? '';
                            $dc_by_name = $loan["discharged_by_name"] ?? $dc_by;
                            $dc_by_str = '';
                            if (!empty($dc_by)) {
                                if (!empty($dc_by_name) && $dc_by_name !== $dc_by) {
                                    $dc_by_str = "<div class='text-muted small mt-1'><i class='fa-solid fa-user-check me-1'></i>" . htmlspecialchars($dc_by_name, ENT_QUOTES, 'UTF-8') . " <span class='text-muted'>(" . htmlspecialchars($dc_by, ENT_QUOTES, 'UTF-8') . ")</span></div>";
                                } else {
                                    $dc_by_str = "<div class='text-muted small mt-1'><i class='fa-solid fa-user-check me-1'></i>" . htmlspecialchars($dc_by, ENT_QUOTES, 'UTF-8') . "</div>";
                                }
                            }

                            if ($is_discharged && !empty($loan["discharged_on"])) {
                                $dc_time = date('d/m/Y H:i:s', (int)$loan["discharged_on"]);
                                $late_badge = '';
                                if ($due_date_val > 0 && (int)$loan["discharged_on"] > $due_date_val) {
                                    $late_badge = "<div class='mt-1'><span class='badge badge-warning'>Returned Late</span></div>";
                                }
                                $discharged_cell = "<span class='text-muted small'>$dc_time</span>$dc_by_str$late_badge";
                            } elseif ($is_discharged) {
                                $discharged_cell = "<span class='badge badge-success'>Discharged</span>$dc_by_str";
                            } else {
                                $discharged_cell = "<span class='badge badge-warning'>Not Discharged</span>";
                            }
                                    
                            echo "<tr class='table-row'>";
                            echo "<td class='text-center'>$n</td>";
                            echo "<td><strong>$bahanTitle</strong> <span class='badge badge-secondary ms-1'>#$accession2</span></td>";
                            echo "<td><strong>$patronName</strong> <span class='text-muted small'>($username2)</span></td>";
                            echo "<td class='text-center'><span class='text-muted small'>$chargedon2</span>$charged_by_str</td>";
                            echo "<td class='text-center'>$due_cell</td>";
                            echo "<td class='text-center'>$discharged_cell</td>";
                            echo "</tr>";
                                                                                                            
                            $n++;
                        }
                    } else {
                        echo "<tr><td colspan='6' class='text-center text-muted p-3'>No loan transactions recorded for this timeframe.</td></tr>";
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
