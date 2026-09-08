<?php
    session_start();
    define('includeExist', true);
    include_once '../includes/access_logged.php';
    include_once '../config.php';

    $start_num = isset($_GET["start"]) && is_numeric($_GET["start"]) ? (int)$_GET['start'] : 0;
    $end_num = isset($_GET["end"]) && is_numeric($_GET["end"]) ? (int)$_GET['end'] : 0;

    if ($start_num > $end_num) {
        $temp = $start_num;
        $start_num = $end_num;
        $end_num = $temp;
    }

    $labels = [];
    if ($start_num > 0 && $end_num > 0) {
        $stmt = mysqli_prepare($GLOBALS["conn"], "
            SELECT c.`39accessnum`, c.eg_item_id, c.`39volume`, c.`39issue`, c.`39year`, c.`39is_reference`,
                   b.`38title`, b.`38localcallnum`, b.`38localcallnum_b`
            FROM eg_item_copies c
            LEFT JOIN eg_item b ON c.eg_item_id = b.id
            WHERE CAST(c.`39accessnum` AS UNSIGNED) BETWEEN ? AND ?
            ORDER BY CAST(c.`39accessnum` AS UNSIGNED) ASC
        ");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ii", $start_num, $end_num);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            while ($row = mysqli_fetch_assoc($result)) {
                $volume = trim($row['39volume'] ?? '');
                $issue = trim($row['39issue'] ?? '');
                $year = trim($row['39year'] ?? '');
                $is_ref = ($row['39is_reference'] ?? 'NO') === 'YES';

                $enum_parts = [];
                if (!empty($volume)) $enum_parts[] = $volume;
                if (!empty($issue)) $enum_parts[] = $issue;
                if (!empty($year)) $enum_parts[] = '(' . $year . ')';
                $enum_text = implode(' ', $enum_parts);

                $full_callnum = trim($row['38localcallnum'] ?? '');
                $callnum_b = trim($row['38localcallnum_b'] ?? '');
                if (!empty($callnum_b)) {
                    if (empty($full_callnum)) {
                        $full_callnum = $callnum_b;
                    } elseif (!str_contains($full_callnum, $callnum_b)) {
                        $full_callnum .= ' ' . $callnum_b;
                    }
                }
                if (empty($full_callnum)) $full_callnum = '-';

                $labels[] = [
                    'accessnum' => $row['39accessnum'] ?? '',
                    'title'     => $row['38title'] ?? 'Untitled',
                    'callnum'   => $full_callnum,
                    'enum_text' => $enum_text,
                    'is_ref'    => $is_ref
                ];
            }
            mysqli_stmt_close($stmt);
        }
    }
    $total_count = count($labels);
?>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title><?php echo $product_name;?> : Print Preview - Book Barcode Labels</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="../<?php echo $mini_icon_path;?>" rel="icon" type="image/png" />
    <link href="../assets/styles/style.css" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="../assets/fontawesome/css/all.min.css">
    <script src="../assets/jscripts/jquery_1_11_1.min.js" type="text/javascript"></script>
    <script src="../assets/jscripts/jquery-barcode.min.js" type="text/javascript"></script>
</head>

<body class="preview-body">
    <!-- Top Action Toolbar (Hidden during actual print) -->
    <header class="preview-toolbar no-print">
        <div class="preview-toolbar-title">
            <i class="fa fa-barcode" style="color: #60a5fa; font-size: 1.25rem;"></i>
            <span>Book Barcode Labels Preview</span>
            <span class="preview-badge">
                Accession #<?php echo htmlspecialchars(sprintf("%010d", $start_num));?> &ndash; #<?php echo htmlspecialchars(sprintf("%010d", $end_num));?>
            </span>
            <span class="preview-badge" style="background: rgba(37, 99, 235, 0.4);">
                <?php echo $total_count;?> label<?php echo $total_count === 1 ? '' : 's';?>
            </span>
        </div>

        <div class="preview-toolbar-info">
            <i class="fa fa-circle-info"></i>
            <span>A4 Portrait Sheet</span>
        </div>

        <div class="preview-toolbar-actions">
            <button type="button" class="btn btn-primary btn-sm" onclick="window.print()" title="Print now (Ctrl+P / Cmd+P)">
                <i class="fa fa-print"></i> Print Labels
            </button>
            <button type="button" class="btn btn-secondary btn-sm" onclick="window.close()" title="Close this preview">
                <i class="fa fa-xmark"></i> Close
            </button>
        </div>
    </header>

    <!-- Preview Canvas & A4 Sheet -->
    <div class="preview-container">
        <div class="print-preview-sheet">
            <?php if ($total_count > 0): ?>
                <div class="book-barcode-grid">
                    <?php foreach ($labels as $idx => $label): 
                        $acc_js = addslashes($label['accessnum']);
                        $title = htmlspecialchars($label['title']);
                        $callnum = trim($label['callnum']);
                        $enum_label = $label['enum_text'];
                        $is_ref_copy = $label['is_ref'];
                        $canvas_id = "codeCanvas_" . preg_replace('/[^a-zA-Z0-9_-]/', '_', $label['accessnum']) . "_" . $idx;
                    ?>
                        <div class="book-barcode-card">
                            <div class="book-barcode-spine">
                                <div>
                                    <?php echo (!empty($callnum) && $callnum !== '-') ? nl2br(htmlspecialchars($callnum)) : '&nbsp;';?>
                                    <?php if (!empty($enum_label)): ?>
                                        <div style="font-size: 8px; font-weight: bold; margin-top: 2px; line-height: 1.1;"><?php echo htmlspecialchars($enum_label, ENT_QUOTES, 'UTF-8'); ?></div>
                                    <?php endif; ?>
                                    <?php if ($is_ref_copy): ?>
                                        <div style="font-size: 7px; color: #dc2626; font-weight: bold; margin-top: 1px;">[REF ONLY]</div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="book-barcode-info">
                                <div class="book-barcode-title" title="<?php echo $title;?>">
                                    <?php echo $title;?>
                                    <?php if (!empty($enum_label)): ?>
                                        <span style="font-weight: 600; color: #2563eb;"> &bull; <?php echo htmlspecialchars($enum_label, ENT_QUOTES, 'UTF-8'); ?></span>
                                    <?php endif; ?>
                                </div>
                                <div style="width: 100%; display: flex; justify-content: center;">
                                    <div class="book-barcode-canvas" id="<?php echo $canvas_id;?>"></div>
                                </div>
                                <script>
                                    $(function() {
                                        $("#<?php echo $canvas_id;?>").barcode("<?php echo $acc_js;?>", "code128", {
                                            barWidth: 1,
                                            barHeight: 32,
                                            fontSize: 10,
                                            showHRI: true,
                                            output: "svg"
                                        });
                                        $("#<?php echo $canvas_id;?>").css({ "margin-left": "auto", "margin-right": "auto" });
                                        $("#<?php echo $canvas_id;?> img").css({ "margin-left": "auto", "margin-right": "auto", "display": "block" });
                                    });
                                </script>
                            </div>
                        </div>
                    <?php endforeach;?>
                </div>
            <?php else: ?>
                <div class="text-center p-4">
                    <div style="font-size: 2.5rem; color: #94a3b8; margin-bottom: 0.5rem;">
                        <i class="fa fa-triangle-exclamation"></i>
                    </div>
                    <h4>No Book Copies Found</h4>
                    <p class="text-muted">
                        No physical copy records were found for Accession Number range 
                        <strong>#<?php echo htmlspecialchars($start_num);?></strong> to 
                        <strong>#<?php echo htmlspecialchars($end_num);?></strong>.
                    </p>
                    <div class="no-print mt-3">
                        <a href="qr_bprintrange.php" class="btn btn-secondary btn-sm">&larr; Back to range selection</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
