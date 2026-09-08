<?php
    session_start();define('includeExist', true);
    include_once '../includes/access_logged.php';
    include_once '../config.php';
    include_once '../includes/stat_cache.php';

    if (!isset($_GET["type"]) || !is_numeric($_GET["type"]) || (isset($_GET["acstat"]) && strlen($_GET["acstat"]) <> 7)) {
        echo "<div class='alert alert-danger m-3 text-center'><strong>Forbidden:</strong> Invalid access parameters.</div>";
        exit;
    }

    $type_param = (int)$_GET["type"];
    $typetext = $_GET["typetext"] ?? 'Material';
    $acstat = $_GET["acstat"] ?? '';
    $is_ajax = isset($_GET["ajax"]) && $_GET["ajax"] == '1';

    // Parse month & year from acstat (MM/YYYY)
    $parts = explode('/', $acstat);
    $m_val = $parts[0] ?? '01';
    $y_val = $parts[1] ?? '1970';

    function get_type_access_details_data($conn, $type_param, $acstat, $m_val, $y_val): array {
        $cache_key = "type_access_det_" . (int)$type_param . "_" . sprintf('%02d', (int)$m_val) . "_" . (int)$y_val;
        
        $res = StatCache::remember_month($cache_key, $m_val, $y_val, function() use ($conn, $type_param, $m_val, $y_val) {
            $range = StatCache::get_month_timestamp_range($m_val, $y_val);
            $type_str = (string)$type_param;
            // High-performance single JOIN query
            $stmt = mysqli_prepare($conn, "SELECT eg_item_det.eg_item_id, eg_item_det.`39ipaddr`, eg_item.`38title` FROM eg_item_det INNER JOIN eg_item ON eg_item_det.eg_item_id = eg_item.id WHERE eg_item.`39type`=? AND eg_item_det.`39logdate` >= ? AND eg_item_det.`39logdate` <= ? ORDER BY eg_item_det.id DESC");
            mysqli_stmt_bind_param($stmt, "sii", $type_str, $range['start'], $range['end']);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            $rows = [];
            if ($result) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $rows[] = [
                        'id' => (int)$row['eg_item_id'],
                        'ipaddr' => $row['39ipaddr'] ?? '',
                        'title' => $row['38title'] ?? 'Unknown Title'
                    ];
                }
            }
            mysqli_stmt_close($stmt);
            return $rows;
        }, []);

        return [
            'rows' => $res['data'] ?? [],
            'is_cached' => $res['is_cached'] ?? false
        ];
    }

    function render_access_details_table($conn, $type_param, $acstat, $m_val, $y_val) {
        $result = get_type_access_details_data($conn, $type_param, $acstat, $m_val, $y_val);
        $rows = $result['rows'];
        $is_cached = $result['is_cached'];
        $n = 1;
        
        ob_start();
        ?>
        <table class="table-modern m-0">
        <thead>
            <tr>
                <th class="text-center col-num">#</th>
                <th class="text-center" style="width:80px;">Item ID</th>
                <th class="text-center" style="width:160px;">Client IP</th>
                <th>Material Title</th>
            </tr>
        </thead>
        <tbody>
        <?php
        if (empty($rows)) {
            echo "<tr><td colspan='4' class='text-center text-muted p-3'>No accesses recorded for this period.</td></tr>";
        } else {
            foreach ($rows as $row) {
                $id = (int)$row['id'];
                $ipaddr = $row['ipaddr'];
                $title = $row['title'];
                
                echo "<tr class='table-row'>";
                echo "<td class='text-center col-num'>$n</td>";
                echo "<td class='text-center'><a href='../details.php?det=$id' class='badge badge-info'>$id</a></td>";
                echo "<td class='text-center'><code>" . htmlspecialchars($ipaddr, ENT_QUOTES, 'UTF-8') . "</code></td>";
                echo "<td><a href='../details.php?det=$id' class='font-bold'>" . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . "</a></td>";
                echo "</tr>";
                
                $n++;
            }
        }
        ?>
        </tbody>
        <tfoot>
            <tr class="bg-card">
                <td colspan="4" class="text-end py-2">
                    <?php echo StatCache::render_badge($is_cached); ?>
                </td>
            </tr>
        </tfoot>
        </table>
        <?php
        return ob_get_clean();
    }

    if ($is_ajax) {
        echo render_access_details_table($GLOBALS["conn"], $type_param, $acstat, $m_val, $y_val);
        exit;
    }
?>
<!DOCTYPE HTML>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title><?php echo htmlspecialchars($product_name, ENT_QUOTES, 'UTF-8');?> : Type Access Details</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="../assets/styles/style.css" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="../assets/fontawesome/css/all.min.css">
</head>
<body class="bg-white p-3">
    <div class="app-container">
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>Access Details for <?php echo htmlspecialchars($typetext, ENT_QUOTES, 'UTF-8');?></strong> (Month: <?php echo htmlspecialchars($acstat, ENT_QUOTES, 'UTF-8');?>)
            </div>
            <div class="card-body p-0">
                <?php echo render_access_details_table($GLOBALS["conn"], $type_param, $acstat, $m_val, $y_val); ?>
            </div>
        </div>

        <div class="text-center my-3">
            <button type="button" class="btn btn-secondary btn-sm" onclick="window.close();">Close Window</button>
        </div>
    </div>
</body>
</html>
