<?php
    session_start();define('includeExist', true);
    include_once '../includes/access_logged.php';
    include_once '../config.php';
    include_once '../includes/stat_cache.php';

    if (isset($_SESSION['editmode']) && $_SESSION['editmode'] == 'SUPER') {
        $get_user = $_GET["user"] ?? ($_SESSION["username"] ?? '');
    } else {
        $get_user = $_SESSION["username"] ?? '';
    }

    $get_month = $_GET["month"] ?? '';
    $get_year = $_GET["year"] ?? '';

    if (!is_numeric($get_month) || !is_numeric($get_year)) {
        echo "<div class='alert alert-danger m-3 text-center'><strong>Error:</strong> Invalid month or year specified.</div>";
        exit;
    }

    $is_ajax = isset($_GET["ajax"]) && $_GET["ajax"] == '1';

    function get_breakdown_data($conn, $get_user, $get_month, $get_year): array {
        $m_formatted = sprintf('%02d', (int)$get_month);
        $y_formatted = (string)(int)$get_year;
        $safe_user = StatCache::sanitize_key($get_user);
        $cache_key = "cataloger_pop_{$safe_user}_{$m_formatted}_{$y_formatted}";

        $res = StatCache::remember_month($cache_key, $get_month, $get_year, function() use ($conn, $get_user, $m_formatted, $y_formatted) {
            // 1. Fetch material type list
            $types_list = [];
            $res_types = mysqli_query($conn, "SELECT `38typeid`, `38type` FROM eg_type ORDER BY `38typeid`");
            if ($res_types) {
                while ($rt = mysqli_fetch_assoc($res_types)) {
                    $types_list[] = [
                        'id' => (string)$rt['38typeid'],
                        'name' => $rt['38type']
                    ];
                }
            }

            // 2. Fetch counts in single grouped query
            $date_pattern = '%' . $m_formatted . '/' . $y_formatted;
            $type_counts = [];
            $stmt = mysqli_prepare($conn, "SELECT `39type`, count(id) as totalUf FROM eg_item WHERE `40inputby`=? AND `40inputdate` LIKE ? GROUP BY `39type`");
            mysqli_stmt_bind_param($stmt, "ss", $get_user, $date_pattern);
            mysqli_stmt_execute($stmt);
            $res_counts = mysqli_stmt_get_result($stmt);
            if ($res_counts) {
                while ($rc = mysqli_fetch_assoc($res_counts)) {
                    $type_counts[(string)$rc['39type']] = (int)$rc['totalUf'];
                }
            }
            mysqli_stmt_close($stmt);

            $rows = [];
            $total = 0;
            foreach ($types_list as $t) {
                $cnt = $type_counts[$t['id']] ?? 0;
                $total += $cnt;
                $rows[] = [
                    'type_id' => $t['id'],
                    'type_name' => $t['name'],
                    'count' => $cnt
                ];
            }

            return [
                'rows' => $rows,
                'total' => $total
            ];
        }, ['rows' => [], 'total' => 0]);

        return [
            'data' => $res['data'],
            'is_cached' => $res['is_cached']
        ];
    }

    function render_breakdown_table($conn, $get_user, $get_month, $get_year) {
        $result = get_breakdown_data($conn, $get_user, $get_month, $get_year);
        $payload = $result['data'];
        $is_cached = $result['is_cached'];
        $rows = $payload['rows'] ?? [];
        $total_items = $payload['total'] ?? 0;

        ob_start();
        ?>
        <table class="table-modern m-0">
        <thead>
            <tr>
                <th>Material Type</th>
                <th class="text-center" style="width:120px;">Count</th>
            </tr>
        </thead>
        <tbody>
        <?php
        if (empty($rows)) {
            echo "<tr><td colspan='2' class='text-center text-muted p-3'>No items recorded for this month.</td></tr>";
        } else {
            foreach ($rows as $row) {
                $cnt = (int)$row['count'];
                echo "<tr class='table-row'>";
                echo "<td><strong>" . htmlspecialchars($row['type_name'], ENT_QUOTES, 'UTF-8') . "</strong></td>";
                echo "<td class='text-center'><span class='badge " . ($cnt > 0 ? "badge-primary font-bold" : "badge-secondary") . "'>$cnt</span></td>";
                echo "</tr>";
            }
        }
        ?>
        </tbody>
        <tfoot>
            <tr class="bg-card font-bold">
                <td class="d-flex justify-content-between align-items-center">
                    <span>Total Items Added</span>
                    <?php echo StatCache::render_badge($is_cached); ?>
                </td>
                <td class="text-center"><span class="badge badge-success"><?php echo $total_items;?></span></td>
            </tr>
        </tfoot>
        </table>
        <?php
        return ob_get_clean();
    }

    if ($is_ajax) {
        echo render_breakdown_table($GLOBALS["conn"], $get_user, $get_month, $get_year);
        exit;
    }
?>
<!DOCTYPE HTML>
<html lang='en'>
<head>
    <meta charset="utf-8">
    <title><?php echo htmlspecialchars($product_name, ENT_QUOTES, 'UTF-8');?> : Monthly Input Summary</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="../<?php echo htmlspecialchars($mini_icon_path, ENT_QUOTES, 'UTF-8');?>" rel="icon" type="image/png" />
    <link href="../assets/styles/style.css" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="../assets/fontawesome/css/all.min.css">
</head>
<body class="bg-white p-3">
    <div class="container-narrow">
        <div class="card mb-3">
            <div class="card-header">
                <strong>Input Summary:</strong> <?php echo htmlspecialchars($get_user, ENT_QUOTES, 'UTF-8');?> (<?php echo htmlspecialchars("$get_month/$get_year", ENT_QUOTES, 'UTF-8');?>)
            </div>
            <div class="card-body p-0">
                <?php echo render_breakdown_table($GLOBALS["conn"], $get_user, $get_month, $get_year); ?>
            </div>
        </div>
        <div class="text-center my-3">
            <button type="button" class="btn btn-secondary btn-sm" onclick="window.close();">Close Window</button>
        </div>
    </div>
</body>
</html>
