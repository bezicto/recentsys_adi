<?php
    session_start();
    define('includeExist', true);
    include_once '../includes/access_logged.php';
    include_once '../config.php';
    include_once '../includes/functions.php';
    include_once '../includes/token_validate.php';

    // Page parameters
    $scstr = trim($_GET['scstr'] ?? '');
    $filter_status = trim($_GET['filter_status'] ?? 'all');
    $sort_by = trim($_GET['sort_by'] ?? 'title_asc');
    $pageNum = isset($_GET['page']) && is_numeric($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $rowsPerPage = 15;
    $offset = ($pageNum - 1) * $rowsPerPage;

    // Resolve Serial type IDs from eg_type
    $serialTypeIds = [4];
    $resType = mysqli_query($GLOBALS["conn"], "SELECT `38typeid`, `38type` FROM eg_type WHERE `38typeid` = 4 OR LOWER(`38type`) LIKE '%serial%' OR LOWER(`38type`) LIKE '%periodical%' OR LOWER(`38type`) LIKE '%journal%' OR LOWER(`38type`) LIKE '%bersiri%'");
    if ($resType) {
        while ($rType = mysqli_fetch_assoc($resType)) {
            $tid = (int)$rType['38typeid'];
            if (!in_array($tid, $serialTypeIds, true)) {
                $serialTypeIds[] = $tid;
            }
        }
    }
    $serialTypeIdsList = implode(',', $serialTypeIds);

    // Global Statistics for Serials Dashboard
    $totalSerialsCount = 0;
    $totalIssuesCount = 0;
    $totalAvailableIssues = 0;
    $totalRefIssues = 0;
    $totalCirculatedIssues = 0;

    $statQuery = "SELECT 
        COUNT(DISTINCT i.id) as total_titles,
        COUNT(c.id) as total_copies,
        SUM(CASE WHEN c.`39status` = 'AVAILABLE' AND (c.`39is_reference` IS NULL OR c.`39is_reference` != 'YES') THEN 1 ELSE 0 END) as available_copies,
        SUM(CASE WHEN c.`39is_reference` = 'YES' OR c.`39status` = 'REFERENCE' THEN 1 ELSE 0 END) as reference_copies,
        SUM(CASE WHEN c.`39status` = 'CIRCULATED' THEN 1 ELSE 0 END) as circulated_copies
    FROM eg_item i
    LEFT JOIN eg_type t ON (i.`39type` = t.`38typeid` OR i.`39type` = t.`38type`)
    LEFT JOIN eg_item_copies c ON i.id = c.eg_item_id
    WHERE (i.`39type` IN ($serialTypeIdsList) OR i.`39type` = 'Serial' OR i.`39type` LIKE '%Serial%' OR t.`38typeid` IN ($serialTypeIdsList) OR t.`38type` = 'Serial' OR t.`38type` LIKE '%Serial%')";

    $resStat = mysqli_query($GLOBALS["conn"], $statQuery);
    if ($resStat && $rowStat = mysqli_fetch_assoc($resStat)) {
        $totalSerialsCount = (int)($rowStat['total_titles'] ?? 0);
        $totalIssuesCount = (int)($rowStat['total_copies'] ?? 0);
        $totalAvailableIssues = (int)($rowStat['available_copies'] ?? 0);
        $totalRefIssues = (int)($rowStat['reference_copies'] ?? 0);
        $totalCirculatedIssues = (int)($rowStat['circulated_copies'] ?? 0);
    }

    // Build WHERE clause for filtered search
    $whereClauses = ["(i.`39type` IN ($serialTypeIdsList) OR i.`39type` = 'Serial' OR i.`39type` LIKE '%Serial%' OR t.`38typeid` IN ($serialTypeIdsList) OR t.`38type` = 'Serial' OR t.`38type` LIKE '%Serial%')"];
    $bindTypes = "";
    $bindParams = [];

    if (!empty($scstr)) {
        $searchTerms = '%' . $scstr . '%';
        $searchClean = '%' . str_replace(['-', '–', '—', ' '], '', $scstr) . '%';
        $whereClauses[] = "(
            i.`38title` LIKE ? OR i.`38title_b` LIKE ? OR i.`38title_c` LIKE ? 
            OR i.`38issn` LIKE ? OR i.`38issn` LIKE ? OR i.`38isbn` LIKE ? OR i.`38isbn` LIKE ? OR i.`38author` LIKE ? 
            OR i.`38publication` LIKE ? OR i.`38publication_b` LIKE ? 
            OR i.`38localcallnum` LIKE ? OR i.`38localcallnum_b` LIKE ? 
            OR i.`39subjectheading` LIKE ? OR i.`38fcnotes` LIKE ? 
            OR i.`38notes` LIKE ? OR i.`38source` LIKE ? OR i.`id` = ?
        )";
        $bindTypes .= "ssssssssssssssssi";
        $bindId = is_numeric($scstr) ? (int)$scstr : 0;
        $bindParams = array_merge($bindParams, [
            $searchTerms, $searchTerms, $searchTerms,
            $searchTerms, $searchClean, $searchTerms, $searchClean, $searchTerms,
            $searchTerms, $searchTerms,
            $searchTerms, $searchTerms,
            $searchTerms, $searchTerms,
            $searchTerms, $searchTerms,
            $bindId
        ]);
    }

    // Filter by Holdings Status
    $havingClause = "";
    if ($filter_status === 'has_copies') {
        $havingClause = "HAVING total_copies > 0";
    } elseif ($filter_status === 'no_copies') {
        $havingClause = "HAVING total_copies = 0";
    } elseif ($filter_status === 'available') {
        $havingClause = "HAVING available_copies > 0";
    } elseif ($filter_status === 'reference') {
        $havingClause = "HAVING reference_copies > 0";
    } elseif ($filter_status === 'circulated') {
        $havingClause = "HAVING circulated_copies > 0";
    }

    // Build Sorting
    $orderSql = "i.`38title` ASC";
    switch ($sort_by) {
        case 'title_desc':
            $orderSql = "i.`38title` DESC";
            break;
        case 'id_desc':
            $orderSql = "i.id DESC";
            break;
        case 'id_asc':
            $orderSql = "i.id ASC";
            break;
        case 'issn':
            $orderSql = "i.`38issn` ASC, i.`38title` ASC";
            break;
        case 'callnum':
            $orderSql = "i.`38localcallnum` ASC, i.`38title` ASC";
            break;
        case 'copies_desc':
            $orderSql = "total_copies DESC, i.`38title` ASC";
            break;
        default:
            $orderSql = "i.`38title` ASC";
            break;
    }

    $whereSql = implode(' AND ', $whereClauses);

    // Count total matched records for pagination
    $countMainQuery = "SELECT i.id,
        COUNT(c.id) as total_copies,
        SUM(CASE WHEN c.`39status` = 'AVAILABLE' AND (c.`39is_reference` IS NULL OR c.`39is_reference` != 'YES') THEN 1 ELSE 0 END) as available_copies,
        SUM(CASE WHEN c.`39is_reference` = 'YES' OR c.`39status` = 'REFERENCE' THEN 1 ELSE 0 END) as reference_copies,
        SUM(CASE WHEN c.`39status` = 'CIRCULATED' THEN 1 ELSE 0 END) as circulated_copies
    FROM eg_item i
    LEFT JOIN eg_type t ON (i.`39type` = t.`38typeid` OR i.`39type` = t.`38type`)
    LEFT JOIN eg_item_copies c ON i.id = c.eg_item_id
    WHERE $whereSql
    GROUP BY i.id $havingClause";

    if (!empty($bindParams)) {
        $stmtCount = mysqli_prepare($GLOBALS["conn"], $countMainQuery);
        mysqli_stmt_bind_param($stmtCount, $bindTypes, ...$bindParams);
        mysqli_stmt_execute($stmtCount);
        $resCount = mysqli_stmt_get_result($stmtCount);
        $totalMatchedSerials = mysqli_num_rows($resCount);
        mysqli_stmt_close($stmtCount);
    } else {
        $resCount = mysqli_query($GLOBALS["conn"], $countMainQuery);
        $totalMatchedSerials = $resCount ? mysqli_num_rows($resCount) : 0;
    }

    $maxPage = max(1, ceil($totalMatchedSerials / $rowsPerPage));
    if ($pageNum > $maxPage) {
        $pageNum = $maxPage;
        $offset = ($pageNum - 1) * $rowsPerPage;
    }

    // Main Fetch Query with Aggregated Holdings
    $mainQuery = "SELECT 
        i.*,
        MAX(t.`38type`) as type_name,
        COUNT(c.id) as total_copies,
        SUM(CASE WHEN c.`39status` = 'AVAILABLE' AND (c.`39is_reference` IS NULL OR c.`39is_reference` != 'YES') THEN 1 ELSE 0 END) as available_copies,
        SUM(CASE WHEN c.`39is_reference` = 'YES' OR c.`39status` = 'REFERENCE' THEN 1 ELSE 0 END) as reference_copies,
        SUM(CASE WHEN c.`39status` = 'CIRCULATED' THEN 1 ELSE 0 END) as circulated_copies
    FROM eg_item i
    LEFT JOIN eg_type t ON (i.`39type` = t.`38typeid` OR i.`39type` = t.`38type`)
    LEFT JOIN eg_item_copies c ON i.id = c.eg_item_id
    WHERE $whereSql
    GROUP BY i.id
    $havingClause
    ORDER BY $orderSql
    LIMIT $offset, $rowsPerPage";

    $time_start = getmicrotime();
    $serialsList = [];
    if (!empty($bindParams)) {
        $stmtMain = mysqli_prepare($GLOBALS["conn"], $mainQuery);
        mysqli_stmt_bind_param($stmtMain, $bindTypes, ...$bindParams);
        mysqli_stmt_execute($stmtMain);
        $resMain = mysqli_stmt_get_result($stmtMain);
        while ($r = mysqli_fetch_assoc($resMain)) {
            $serialsList[] = $r;
        }
        mysqli_stmt_close($stmtMain);
    } else {
        $resMain = mysqli_query($GLOBALS["conn"], $mainQuery);
        if ($resMain) {
            while ($r = mysqli_fetch_assoc($resMain)) {
                $serialsList[] = $r;
            }
        }
    }
    $time_end = getmicrotime();
    $searchTime = round($time_end - $time_start, 4);

    // Fetch individual copies for each serial displayed on this page
    $serialIdsOnPage = array_column($serialsList, 'id');
    $copiesBySerial = [];
    if (!empty($serialIdsOnPage)) {
        $inIds = implode(',', array_map('intval', $serialIdsOnPage));
        $resCopies = mysqli_query($GLOBALS["conn"], "SELECT * FROM eg_item_copies WHERE eg_item_id IN ($inIds) ORDER BY eg_item_id ASC, `39year` DESC, `39volume` DESC, `39issue` DESC, id DESC");
        if ($resCopies) {
            while ($cRow = mysqli_fetch_assoc($resCopies)) {
                $copiesBySerial[$cRow['eg_item_id']][] = $cRow;
            }
        }
    }

    // Build context query string to pass state when navigating to details.php or reg.php
    $serialsContextArgs = ['delr' => 'serials'];
    if (!empty($scstr)) {
        $serialsContextArgs['scstr'] = $scstr;
        $serialsContextArgs['ser_scstr'] = $scstr;
    }
    if ($filter_status !== 'all') {
        $serialsContextArgs['filter_status'] = $filter_status;
        $serialsContextArgs['ser_filter'] = $filter_status;
    }
    if ($sort_by !== 'title_asc') {
        $serialsContextArgs['sort_by'] = $sort_by;
        $serialsContextArgs['ser_sort'] = $sort_by;
    }
    if ($pageNum > 1) {
        $serialsContextArgs['page'] = $pageNum;
        $serialsContextArgs['ser_page'] = $pageNum;
    }
    $serialsContextQuery = '&' . http_build_query($serialsContextArgs);
?>
<!DOCTYPE HTML>
<html lang='en'>

<head>
    <meta charset="utf-8">
    <title><?php echo htmlspecialchars($product_name, ENT_QUOTES, 'UTF-8');?> : Serial &amp; Periodical Management</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="../<?php echo htmlspecialchars($mini_icon_path, ENT_QUOTES, 'UTF-8');?>" rel="icon" type="image/png" />
    <link href="../assets/styles/style.css?v=3" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="../assets/fontawesome/css/all.min.css">
    <style>
        .serial-stat-card {
            background: var(--bg-card, #ffffff);
            border: 1px solid var(--border-color, #e2e8f0);
            border-radius: var(--radius-md, 8px);
            padding: 1rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            box-shadow: var(--shadow-sm, 0 1px 3px rgba(0,0,0,0.05));
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }
        .serial-stat-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md, 0 4px 6px -1px rgba(0,0,0,0.1));
        }
        .serial-stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            flex-shrink: 0;
        }
        .stat-icon-titles { background: #e0f2fe; color: #0284c7; }
        .stat-icon-issues { background: #f0fdf4; color: #16a34a; }
        .stat-icon-avail { background: #dcfce7; color: #15803d; }
        .stat-icon-ref { background: #fef3c7; color: #b45309; }
        .stat-icon-circ { background: #ffedd5; color: #c2410c; }
        
        .serial-stat-value {
            font-size: 1.45rem;
            font-weight: 700;
            line-height: 1.1;
            color: var(--text-main, #1e293b);
        }
        .serial-stat-label {
            font-size: 0.78rem;
            color: var(--text-muted, #64748b);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .serial-item-card {
            background: var(--bg-card, #ffffff);
            border: 1px solid var(--border-color, #e2e8f0);
            border-radius: var(--radius-md, 8px);
            margin-bottom: 1.25rem;
            overflow: hidden;
            box-shadow: var(--shadow-sm, 0 1px 3px rgba(0,0,0,0.05));
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }
        .serial-item-card:hover {
            border-color: #94a3b8;
            box-shadow: var(--shadow-md, 0 4px 6px -1px rgba(0,0,0,0.1));
        }
        .serial-item-header {
            padding: 1rem 1.25rem;
            background: #f8fafc;
            border-bottom: 1px solid var(--border-color, #e2e8f0);
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            flex-wrap: wrap;
            gap: 0.75rem;
        }
        .serial-item-body {
            padding: 1.25rem;
        }
        .serial-title {
            font-size: 1.15rem;
            font-weight: 700;
            color: var(--primary, #2563eb);
            text-decoration: none;
        }
        .serial-title:hover {
            text-decoration: underline;
        }
        .serial-meta-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 0.75rem;
            margin-top: 0.75rem;
            padding-top: 0.75rem;
            border-top: 1px dashed #e2e8f0;
            font-size: 0.88rem;
        }
        .serial-meta-item {
            display: flex;
            flex-direction: column;
        }
        .serial-meta-item-label {
            font-size: 0.75rem;
            color: #64748b;
            font-weight: 600;
            text-transform: uppercase;
        }
        .serial-meta-item-value {
            color: #1e293b;
            font-weight: 500;
        }
        .holdings-accordion-btn {
            background: none;
            border: none;
            color: var(--primary, #2563eb);
            font-weight: 600;
            font-size: 0.85rem;
            cursor: pointer;
            padding: 0;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .holdings-drawer {
            display: none;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #e2e8f0;
        }
        .holdings-drawer.open {
            display: block;
        }

        .serial-stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
            gap: 0.75rem;
        }

        .serial-search-form {
            margin: 0;
            width: 100%;
        }

        .serial-search-bar {
            display: flex;
            flex-direction: row;
            align-items: center;
            gap: 0.5rem;
            width: 100%;
        }

        .serial-search-input-wrap {
            flex: 1 1 260px;
            min-width: 180px;
        }

        .serial-search-input-wrap input[type="text"] {
            width: 100%;
            height: 38px;
            padding: 0.45rem 0.85rem;
            font-size: 0.9rem;
            border-radius: var(--radius-md, 6px);
            border: 1px solid var(--border-color, #cbd5e1);
            background: #fff;
            box-sizing: border-box;
        }

        .serial-filter-select-wrap {
            flex: 0 0 190px;
            width: 190px;
        }

        .serial-sort-select-wrap {
            flex: 0 0 170px;
            width: 170px;
        }

        .serial-filter-select-wrap select,
        .serial-sort-select-wrap select {
            width: 100%;
            height: 38px;
            padding: 0.45rem 0.75rem;
            font-size: 0.88rem;
            border-radius: var(--radius-md, 6px);
            border: 1px solid var(--border-color, #cbd5e1);
            background: #fff;
            box-sizing: border-box;
        }

        .serial-search-actions {
            flex: 0 0 auto;
            display: flex;
            align-items: center;
            gap: 0.35rem;
            white-space: nowrap;
        }

        .serial-search-actions .btn {
            height: 38px;
            padding: 0 0.9rem;
            font-size: 0.88rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-sizing: border-box;
            white-space: nowrap;
        }

        @media (max-width: 991px) {
            .serial-search-bar {
                flex-wrap: wrap;
            }
            .serial-search-input-wrap {
                flex: 1 1 100%;
                width: 100%;
            }
            .serial-filter-select-wrap,
            .serial-sort-select-wrap {
                flex: 1 1 calc(50% - 0.25rem);
                width: auto;
            }
            .serial-search-actions {
                flex: 1 1 100%;
                width: 100%;
            }
            .serial-search-actions .btn {
                flex: 1 1 auto;
            }
        }

        @media (max-width: 576px) {
            .serial-search-bar {
                flex-direction: column;
                align-items: stretch;
                gap: 0.5rem;
            }
            .serial-search-input-wrap,
            .serial-filter-select-wrap,
            .serial-sort-select-wrap,
            .serial-search-actions {
                flex: 1 1 100%;
                width: 100%;
            }
            .serial-filter-select-wrap select,
            .serial-sort-select-wrap select,
            .serial-search-input-wrap input[type="text"] {
                width: 100%;
            }
            .serial-search-actions {
                display: flex;
                width: 100%;
            }
            .serial-search-actions .btn {
                flex: 1;
            }
        }
    </style>
    <script>
        function toggleHoldings(id) {
            var drawer = document.getElementById('holdings_' + id);
            var icon = document.getElementById('icon_holdings_' + id);
            if (drawer) {
                var isOpen = drawer.classList.toggle('open');
                if (icon) {
                    icon.className = isOpen ? 'fa-solid fa-chevron-up me-1' : 'fa-solid fa-chevron-down me-1';
                }
            }
        }

        function openAppModal(url, titleText, sizeClass) {
            var modal = document.getElementById('appModal');
            var dialog = document.getElementById('appModalDialog');
            var title = document.getElementById('appModalTitle');
            var iframe = document.getElementById('appModalIframe');
            
            title.textContent = titleText || 'Action';
            dialog.className = 'modal-dialog ' + (sizeClass || 'modal-md');
            iframe.src = url;
            
            modal.classList.add('show');
            document.body.style.overflow = 'hidden';
            return false;
        }

        function closeAppModal(shouldReload) {
            var modal = document.getElementById('appModal');
            var iframe = document.getElementById('appModalIframe');
            if (modal) {
                modal.classList.remove('show');
                document.body.style.overflow = '';
                iframe.src = 'about:blank';
            }
            if (shouldReload) {
                window.location.reload();
            }
        }

        function openImageModal(el) {
            var modal = document.getElementById('imageModal');
            var img = document.getElementById('imageModalImg');
            var title = document.getElementById('imageModalTitle');
            if (modal && img && el) {
                var src = el.getAttribute('data-src');
                var altTitle = el.getAttribute('data-title') || 'Cover Image';
                img.src = src;
                if (title) title.textContent = altTitle;
                modal.classList.add('show');
                document.body.style.overflow = 'hidden';
            }
        }

        function closeImageModal() {
            var modal = document.getElementById('imageModal');
            var img = document.getElementById('imageModalImg');
            if (modal) {
                modal.classList.remove('show');
                document.body.style.overflow = '';
                if (img) img.src = '';
            }
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeAppModal(false);
                closeImageModal();
            }
        });
    </script>
</head>

<body>
    <?php include_once '../includes/loggedinfo.php';?>
    
    <div class="app-container">
        
        <!-- Header Banner & Action Bar -->
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2">
                    <i class="fa-solid fa-newspaper text-primary fa-lg"></i>
                    <strong>Serial &amp; Periodical Management</strong>
                    <span class="badge badge-primary">Cataloging Hub</span>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <a class="btn btn-primary btn-sm" href="reg.php?jenis=4&delr=serials<?php echo htmlspecialchars($serialsContextQuery, ENT_QUOTES, 'UTF-8'); ?>" title="Catalog a new Serial / Journal / Magazine">
                        <i class="fa-solid fa-plus-circle me-1"></i> Add New Serial
                    </a>
                </div>
            </div>
            <div class="card-body p-3">
                <!-- Statistics Summary Cards -->
                <div class="serial-stats-grid">
                    <div class="serial-stat-card">
                        <div class="serial-stat-icon stat-icon-titles">
                            <i class="fa-solid fa-book-journal-whills"></i>
                        </div>
                        <div>
                            <div class="serial-stat-value"><?php echo number_format($totalSerialsCount); ?></div>
                            <div class="serial-stat-label">Serial Titles</div>
                        </div>
                    </div>
                    <div class="serial-stat-card">
                        <div class="serial-stat-icon stat-icon-issues">
                            <i class="fa-solid fa-layer-group"></i>
                        </div>
                        <div>
                            <div class="serial-stat-value"><?php echo number_format($totalIssuesCount); ?></div>
                            <div class="serial-stat-label">Total Issues / Copies</div>
                        </div>
                    </div>
                    <div class="serial-stat-card">
                        <div class="serial-stat-icon stat-icon-avail">
                            <i class="fa-solid fa-circle-check"></i>
                        </div>
                        <div>
                            <div class="serial-stat-value"><?php echo number_format($totalAvailableIssues); ?></div>
                            <div class="serial-stat-label">Available Issues</div>
                        </div>
                    </div>
                    <div class="serial-stat-card">
                        <div class="serial-stat-icon stat-icon-ref">
                            <i class="fa-solid fa-shield-halved"></i>
                        </div>
                        <div>
                            <div class="serial-stat-value"><?php echo number_format($totalRefIssues); ?></div>
                            <div class="serial-stat-label">Reference / Current</div>
                        </div>
                    </div>
                    <div class="serial-stat-card">
                        <div class="serial-stat-icon stat-icon-circ">
                            <i class="fa-solid fa-clock-rotate-left"></i>
                        </div>
                        <div>
                            <div class="serial-stat-value"><?php echo number_format($totalCirculatedIssues); ?></div>
                            <div class="serial-stat-label">Circulated (On Loan)</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search & Filter Controls -->
        <div class="card mb-3">
            <div class="card-body p-3">
                <form action="serials.php" method="get" class="serial-search-form">
                    <div class="serial-search-bar">
                        <div class="serial-search-input-wrap">
                            <input type="text" name="scstr" maxlength="255" placeholder="Search serial title, ISSN, publisher, call number, control no..." value="<?php echo htmlspecialchars($scstr, ENT_QUOTES, 'UTF-8'); ?>" />
                        </div>
                        <div class="serial-filter-select-wrap">
                            <select name="filter_status">
                                <option value="all" <?php if ($filter_status === 'all') echo 'selected'; ?>>Filter: All Serials</option>
                                <option value="has_copies" <?php if ($filter_status === 'has_copies') echo 'selected'; ?>>Has Registered Issues</option>
                                <option value="no_copies" <?php if ($filter_status === 'no_copies') echo 'selected'; ?>>No Issues Yet (Pending Check-in)</option>
                                <option value="available" <?php if ($filter_status === 'available') echo 'selected'; ?>>Has Available Issues</option>
                                <option value="reference" <?php if ($filter_status === 'reference') echo 'selected'; ?>>Has Reference Only Issues</option>
                                <option value="circulated" <?php if ($filter_status === 'circulated') echo 'selected'; ?>>Has On-Loan Issues</option>
                            </select>
                        </div>
                        <div class="serial-sort-select-wrap">
                            <select name="sort_by">
                                <option value="title_asc" <?php if ($sort_by === 'title_asc') echo 'selected'; ?>>Sort: Title (A &rarr; Z)</option>
                                <option value="title_desc" <?php if ($sort_by === 'title_desc') echo 'selected'; ?>>Sort: Title (Z &rarr; A)</option>
                                <option value="id_desc" <?php if ($sort_by === 'id_desc') echo 'selected'; ?>>Newest First</option>
                                <option value="id_asc" <?php if ($sort_by === 'id_asc') echo 'selected'; ?>>Oldest First</option>
                                <option value="issn" <?php if ($sort_by === 'issn') echo 'selected'; ?>>ISSN</option>
                                <option value="callnum" <?php if ($sort_by === 'callnum') echo 'selected'; ?>>Call Number</option>
                                <option value="copies_desc" <?php if ($sort_by === 'copies_desc') echo 'selected'; ?>>Most Copies / Issues</option>
                            </select>
                        </div>
                        <div class="serial-search-actions">
                            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-magnifying-glass me-1"></i> Filter</button>
                            <?php if (!empty($scstr) || $filter_status !== 'all' || $sort_by !== 'title_asc'): ?>
                                <a href="serials.php" class="btn btn-secondary" title="Clear Filters"><i class="fa-solid fa-rotate-left"></i></a>
                            <?php endif; ?>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Search Summary Result Bar -->
        <div class="alert alert-info d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <div>
                <strong><?php echo number_format($totalMatchedSerials); ?></strong> serial titles found 
                <?php if (!empty($scstr)): ?>
                    for <em>"<?php echo htmlspecialchars($scstr, ENT_QUOTES, 'UTF-8'); ?>"</em>
                <?php endif; ?>
                <small class="text-muted ms-2">(Query completed in <?php echo $searchTime; ?>s)</small>
            </div>
            <div class="small">
                Showing page <strong><?php echo $pageNum; ?></strong> of <strong><?php echo $maxPage; ?></strong>
            </div>
        </div>

        <!-- Serials List -->
        <?php if (empty($serialsList)): ?>
            <div class="card p-5 text-center mb-3">
                <div class="text-muted mb-3" style="font-size: 3rem;">
                    <i class="fa-solid fa-newspaper"></i>
                </div>
                <h4>No Serial Records Found</h4>
                <p class="text-muted">
                    <?php if (!empty($scstr) || $filter_status !== 'all'): ?>
                        No serial records match your search or filter criteria. Try adjusting your query or reset filters.
                    <?php else: ?>
                        No materials with Material Type "Serial" (Type ID 4) currently exist in the database.
                    <?php endif; ?>
                </p>
                <div class="mt-2">
                    <a href="reg.php?jenis=4" class="btn btn-primary"><i class="fa-solid fa-plus me-1"></i> Register New Serial Record</a>
                </div>
            </div>
        <?php else: ?>
            <?php
                $itemIndex = $offset + 1;
                foreach ($serialsList as $serial):
                    $sid = (int)$serial['id'];
                    $stitle = $serial['38title'] ?? '';
                    $stitle_b = $serial['38title_b'] ?? '';
                    $stitle_c = $serial['38title_c'] ?? '';
                    $sauthor = $serial['38author'] ?? '';
                    $sissn = trim($serial['38issn'] ?? '');
                    $scallnum = trim($serial['38localcallnum'] ?? '');
                    $scallnum_b = trim($serial['38localcallnum_b'] ?? '');
                    $spub = $serial['38publication'] ?? '';
                    $spub_b = $serial['38publication_b'] ?? '';
                    $spub_c = $serial['38publication_c'] ?? '';
                    $sfcnotes = $serial['38fcnotes'] ?? '';
                    $snotes = $serial['38notes'] ?? '';
                    $ssource = $serial['38source'] ?? '';
                    $ssubject = $serial['39subjectheading'] ?? '';
                    $sinputby = $serial['40inputby'] ?? '';
                    $sinputdate = $serial['40inputdate'] ?? '';
                    $stimestamp = $serial['40instimestamp'] ?? '';
                    $simageatt = $serial['39imageatt'] ?? '';

                    $stotalCopies = (int)($serial['total_copies'] ?? 0);
                    $savailCopies = (int)($serial['available_copies'] ?? 0);
                    $srefCopies = (int)($serial['reference_copies'] ?? 0);
                    $scircCopies = (int)($serial['circulated_copies'] ?? 0);

                    // Combine call number cleanly
                    $dispCall = $scallnum;
                    if (!empty($scallnum_b)) {
                        if (empty($dispCall)) $dispCall = $scallnum_b;
                        elseif (!str_contains($dispCall, $scallnum_b)) $dispCall .= ' ' . $scallnum_b;
                    }

                    // Combined publication text
                    $pubParts = [];
                    if (!empty($spub)) $pubParts[] = $spub;
                    if (!empty($spub_b)) $pubParts[] = $spub_b;
                    if (!empty($spub_c)) $pubParts[] = '(' . $spub_c . ')';
                    $pubDisplay = implode(', ', $pubParts);

                    // Full title string
                    $fullTitle = $stitle;
                    if (!empty($stitle_b)) $fullTitle .= ' : ' . $stitle_b;
                    if (!empty($stitle_c)) $fullTitle .= ' / ' . $stitle_c;

                    // Cover image path
                    $dirYear = substr((string)$sinputdate, -4);
                    if (!preg_match('/^[0-9]{4}$/', $dirYear)) $dirYear = date('Y');
                    $coverPath = "../$cover_upload_directory/$dirYear/{$sid}_{$stimestamp}.jpg";
                    $hasCover = ($simageatt === 'TRUE' && is_file($coverPath));

                    // Copies / Holdings for this serial
                    $serialCopies = $copiesBySerial[$sid] ?? [];
                    
                    // Determine latest issue designation
                    $latestIssueLabel = '';
                    if (!empty($serialCopies)) {
                        $latestCopy = $serialCopies[0];
                        $enumP = [];
                        if (!empty($latestCopy['39volume'])) $enumP[] = $latestCopy['39volume'];
                        if (!empty($latestCopy['39issue'])) $enumP[] = $latestCopy['39issue'];
                        if (!empty($latestCopy['39year'])) $enumP[] = '(' . $latestCopy['39year'] . ')';
                        if (!empty($enumP)) {
                            $latestIssueLabel = implode(' ', $enumP);
                        }
                    }
            ?>
                <div class="serial-item-card">
                    <div class="serial-item-header">
                        <div>
                            <div class="d-flex align-items-center flex-wrap gap-2 mb-1">
                                <span class="badge badge-secondary" style="font-weight: 700;">#<?php echo $itemIndex++; ?></span>
                                <span class="badge badge-primary"><i class="fa-solid fa-newspaper me-1"></i>Serial</span>
                                <span class="badge badge-secondary" title="Control Number">Ctrl #<?php echo $sid; ?></span>
                                <?php if (!empty($sissn)): ?>
                                    <span class="badge badge-info font-monospace" title="International Standard Serial Number"><i class="fa-solid fa-barcode me-1"></i>ISSN: <?php echo htmlspecialchars($sissn, ENT_QUOTES, 'UTF-8'); ?></span>
                                <?php endif; ?>
                                <?php if (!empty($dispCall)): ?>
                                    <span class="badge badge-secondary font-monospace" title="Library Call Number"><i class="fa-solid fa-bookmark me-1"></i><?php echo htmlspecialchars($dispCall, ENT_QUOTES, 'UTF-8'); ?></span>
                                <?php endif; ?>
                            </div>
                            <h3 class="m-0" style="font-size: 1.2rem; line-height: 1.35;">
                                <a href="../details.php?det=<?php echo $sid; ?><?php echo htmlspecialchars($serialsContextQuery, ENT_QUOTES, 'UTF-8'); ?>" class="serial-title" title="View catalog details">
                                    <?php 
                                        if (!empty($scstr)) {
                                            echo highlight($fullTitle, $scstr);
                                        } else {
                                            echo htmlspecialchars($fullTitle, ENT_QUOTES, 'UTF-8');
                                        }
                                    ?>
                                </a>
                            </h3>
                            <?php if (!empty($sauthor)): ?>
                                <div class="text-muted small mt-1">
                                    <i class="fa-solid fa-user-pen me-1"></i><strong>Author / Editor:</strong> <?php echo htmlspecialchars($sauthor, ENT_QUOTES, 'UTF-8'); ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Top Quick Actions -->
                        <div class="d-flex gap-1 flex-wrap">
                            <button type="button" class="btn btn-success btn-sm" onclick="return openAppModal('copies_add.php?bahan_id=<?php echo $sid; ?>', 'Check-in Serial Issue / Add Copies (#<?php echo $sid; ?>)', 'modal-md');" title="Add new issue copy for this serial">
                                <i class="fa-solid fa-plus me-1"></i> Add Issue
                            </button>
                            <button type="button" class="btn btn-secondary btn-sm" onclick="return openAppModal('reg_copies.php?id=<?php echo $sid; ?>', 'Manage Copies (#<?php echo $sid; ?>)', 'modal-lg');" title="Manage all physical copies and accession numbers">
                                <i class="fa-solid fa-boxes-stacked me-1"></i> Copies
                            </button>
                            <a class="btn btn-secondary btn-sm" href="reg.php?upd=<?php echo $sid; ?><?php echo htmlspecialchars($serialsContextQuery, ENT_QUOTES, 'UTF-8'); ?>" title="Edit serial catalog metadata">
                                <i class="fa-solid fa-pen-to-square me-1"></i> Edit
                            </a>
                            <button type="button" class="btn btn-secondary btn-sm" onclick="openAppModal('../marc_view.php?det=<?php echo $sid; ?>', 'MARC21 Record (#<?php echo $sid; ?>)', 'modal-lg');" title="View standard MARC21 record">
                                <i class="fa-solid fa-file-code me-1"></i> MARC
                            </button>
                            <a class="btn btn-secondary btn-sm" href="../details.php?det=<?php echo $sid; ?><?php echo htmlspecialchars($serialsContextQuery, ENT_QUOTES, 'UTF-8'); ?>" title="View catalog record details">
                                <i class="fa-solid fa-file-lines me-1"></i> Details
                            </a>
                        </div>
                    </div>

                    <div class="serial-item-body">
                        <div class="d-flex gap-3 flex-wrap flex-md-nowrap">
                            <?php if ($hasCover): ?>
                                <div style="flex-shrink: 0;">
                                    <a href="javascript:void(0)" data-src="<?php echo htmlspecialchars($coverPath, ENT_QUOTES, 'UTF-8'); ?>" data-title="<?php echo htmlspecialchars($stitle, ENT_QUOTES, 'UTF-8'); ?>" onclick="openImageModal(this); return false;" title="Click to view full cover image">
                                        <img src="<?php echo htmlspecialchars($coverPath, ENT_QUOTES, 'UTF-8'); ?>" alt="Cover" class="rounded shadow-sm" style="width: 75px; height: 105px; object-fit: cover; border: 1px solid #e2e8f0; display: block;" />
                                    </a>
                                </div>
                            <?php endif; ?>

                            <div style="flex-grow: 1;">
                                <!-- Holdings Availability Badges -->
                                <div class="d-flex align-items-center flex-wrap gap-2 mb-2">
                                    <strong>Holdings Inventory:</strong>
                                    <?php if ($stotalCopies > 0): ?>
                                        <span class="badge badge-primary"><i class="fa-solid fa-layer-group me-1"></i><?php echo $stotalCopies; ?> Total Issues</span>
                                        <?php if ($savailCopies > 0): ?>
                                            <span class="badge badge-success" title="Available for checkout"><i class="fa-solid fa-circle-check me-1"></i><?php echo $savailCopies; ?> Available</span>
                                        <?php endif; ?>
                                        <?php if ($srefCopies > 0): ?>
                                            <span class="badge badge-warning text-dark" title="Current issues or in-library reference"><i class="fa-solid fa-shield-halved me-1"></i><?php echo $srefCopies; ?> Reference / Current</span>
                                        <?php endif; ?>
                                        <?php if ($scircCopies > 0): ?>
                                            <span class="badge badge-danger" title="Currently on loan"><i class="fa-solid fa-clock-rotate-left me-1"></i><?php echo $scircCopies; ?> On Loan</span>
                                        <?php endif; ?>
                                        <?php if (!empty($latestIssueLabel)): ?>
                                            <span class="badge badge-secondary font-monospace" title="Latest received issue"><i class="fa-solid fa-star text-warning me-1"></i>Latest: <?php echo htmlspecialchars($latestIssueLabel, ENT_QUOTES, 'UTF-8'); ?></span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="badge badge-secondary"><i class="fa-solid fa-triangle-exclamation me-1"></i>0 Issues Registered</span>
                                        <button type="button" class="btn btn-outline-primary btn-sm py-0 px-2 ms-1" style="font-size:0.75rem;" onclick="return openAppModal('copies_add.php?bahan_id=<?php echo $sid; ?>', 'Check-in Serial Issue / Add Copies (#<?php echo $sid; ?>)', 'modal-md');">
                                            <i class="fa-solid fa-plus me-1"></i> Check-in First Issue
                                        </button>
                                    <?php endif; ?>
                                </div>

                                <!-- Bibliographic Metadata Grid -->
                                <div class="serial-meta-grid">
                                    <?php if (!empty($pubDisplay)): ?>
                                        <div class="serial-meta-item">
                                            <span class="serial-meta-item-label">Publisher / Place</span>
                                            <span class="serial-meta-item-value"><?php echo htmlspecialchars($pubDisplay, ENT_QUOTES, 'UTF-8'); ?></span>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (!empty($sfcnotes)): ?>
                                        <div class="serial-meta-item">
                                            <span class="serial-meta-item-label">Publication Frequency</span>
                                            <span class="serial-meta-item-value"><i class="fa-solid fa-calendar-check text-primary me-1"></i><?php echo htmlspecialchars($sfcnotes, ENT_QUOTES, 'UTF-8'); ?></span>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (!empty($ssource)): ?>
                                        <div class="serial-meta-item">
                                            <span class="serial-meta-item-label">Corporate Body / Source</span>
                                            <span class="serial-meta-item-value"><?php echo htmlspecialchars($ssource, ENT_QUOTES, 'UTF-8'); ?></span>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (!empty($ssubject)): ?>
                                        <div class="serial-meta-item">
                                            <span class="serial-meta-item-label">Subject Headings</span>
                                            <span class="serial-meta-item-value">
                                                <?php
                                                    $shParts = explode('|', $ssubject);
                                                    $shNames = [];
                                                    foreach ($shParts as $shp) {
                                                        $shp = trim($shp);
                                                        if (!empty($shp)) {
                                                            $shFull = subjectFromAcronym($shp);
                                                            $shNames[] = htmlspecialchars(!empty($shFull) ? $shFull : $shp, ENT_QUOTES, 'UTF-8');
                                                        }
                                                    }
                                                    echo !empty($shNames) ? implode('; ', $shNames) : '-';
                                                ?>
                                            </span>
                                        </div>
                                    <?php endif; ?>

                                    <div class="serial-meta-item">
                                        <span class="serial-meta-item-label">Cataloged By / Date</span>
                                        <span class="serial-meta-item-value"><?php echo htmlspecialchars(patronUsernametoName($sinputby), ENT_QUOTES, 'UTF-8'); ?> (<?php echo htmlspecialchars($sinputdate, ENT_QUOTES, 'UTF-8'); ?>)</span>
                                    </div>
                                </div>

                                <?php if (!empty($snotes)): ?>
                                    <div class="mt-2 text-muted small">
                                        <strong>Notes:</strong> <?php echo htmlspecialchars($snotes, ENT_QUOTES, 'UTF-8'); ?>
                                    </div>
                                <?php endif; ?>

                                <!-- Expandable Holdings Drawer -->
                                <?php if ($stotalCopies > 0): ?>
                                    <div class="mt-3">
                                        <button type="button" class="holdings-accordion-btn" onclick="toggleHoldings(<?php echo $sid; ?>);">
                                            <i id="icon_holdings_<?php echo $sid; ?>" class="fa-solid fa-chevron-down me-1"></i>
                                            <strong>View Registered Issues &amp; Holdings (<?php echo $stotalCopies; ?> copies)</strong>
                                        </button>

                                        <div id="holdings_<?php echo $sid; ?>" class="holdings-drawer">
                                            <table class="table-modern m-0">
                                                <thead>
                                                    <tr>
                                                        <th style="width: 140px;">Accession Barcode</th>
                                                        <th>Volume / Issue Designation</th>
                                                        <th class="text-center" style="width: 120px;">Publication Year</th>
                                                        <th class="text-center" style="width: 160px;">Circulation Status</th>
                                                        <th class="text-center" style="width: 150px;">Issue Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($serialCopies as $copy): 
                                                        $cid = (int)$copy['id'];
                                                        $cacc = $copy['39accessnum'] ?? '';
                                                        $cvol = $copy['39volume'] ?? '';
                                                        $ciss = $copy['39issue'] ?? '';
                                                        $cyear = $copy['39year'] ?? '';
                                                        $cstat = $copy['39status'] ?? 'AVAILABLE';
                                                        $cisref = $copy['39is_reference'] ?? 'NO';
                                                        
                                                        $volIssueParts = [];
                                                        if (!empty($cvol)) $volIssueParts[] = $cvol;
                                                        if (!empty($ciss)) $volIssueParts[] = $ciss;
                                                        $volIssueStr = implode(', ', $volIssueParts);
                                                        if (empty($volIssueStr)) $volIssueStr = '<span class="text-muted font-italic">Unspecified Issue</span>';
                                                    ?>
                                                        <tr class="table-row">
                                                            <td>
                                                                <strong class="font-monospace text-primary"><?php echo htmlspecialchars($cacc, ENT_QUOTES, 'UTF-8'); ?></strong>
                                                            </td>
                                                            <td>
                                                                <span class="badge badge-secondary font-monospace"><i class="fa-solid fa-layer-group me-1"></i><?php echo $volIssueStr; ?></span>
                                                            </td>
                                                            <td class="text-center">
                                                                <?php echo !empty($cyear) ? htmlspecialchars($cyear, ENT_QUOTES, 'UTF-8') : '<span class="text-muted">-</span>'; ?>
                                                            </td>
                                                            <td class="text-center">
                                                                <?php if ($cstat === 'AVAILABLE'): ?>
                                                                    <?php if ($cisref === 'YES'): ?>
                                                                        <span class="badge badge-warning"><i class="fa-solid fa-lock me-1"></i>Reference Only</span>
                                                                    <?php else: ?>
                                                                        <span class="badge badge-success"><i class="fa-solid fa-circle-check me-1"></i>Available</span>
                                                                    <?php endif; ?>
                                                                <?php elseif ($cstat === 'REFERENCE'): ?>
                                                                    <span class="badge badge-warning"><i class="fa-solid fa-lock me-1"></i>Reference</span>
                                                                <?php elseif ($cstat === 'CIRCULATED'): ?>
                                                                    <span class="badge badge-danger"><i class="fa-solid fa-clock-rotate-left me-1"></i>Circulated</span>
                                                                <?php else: ?>
                                                                    <span class="badge badge-secondary"><?php echo htmlspecialchars($cstat, ENT_QUOTES, 'UTF-8'); ?></span>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td class="text-center">
                                                                <div class="d-flex justify-content-center gap-1">
                                                                    <button type="button" class="btn btn-secondary btn-sm" onclick="return openAppModal('change_status.php?cid=<?php echo $cid; ?>', 'Edit Copy (#<?php echo htmlspecialchars($cacc, ENT_QUOTES, 'UTF-8'); ?>)', 'modal-md');" title="Edit copy details and status">
                                                                        <i class="fa-solid fa-pen"></i>
                                                                    </button>
                                                                    <a class="btn btn-secondary btn-sm" href="qr_bprintpreview_range.php?start=<?php echo urlencode($cacc); ?>&end=<?php echo urlencode($cacc); ?>" target="_blank" title="Print Barcode Label">
                                                                        <i class="fa-solid fa-barcode"></i>
                                                                    </a>
                                                                    <?php if ($cstat !== 'CIRCULATED' && (($_SESSION['editmode'] ?? '') === 'SUPER')): ?>
                                                                        <button type="button" class="btn btn-danger btn-sm" onclick="return openAppModal('copies_delete.php?id=<?php echo $cid; ?>', 'Delete Copy (#<?php echo htmlspecialchars($cacc, ENT_QUOTES, 'UTF-8'); ?>)', 'modal-sm');" title="Delete copy">
                                                                            <i class="fa-solid fa-trash"></i>
                                                                        </button>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

            <!-- Pagination Bar -->
            <?php if ($maxPage > 1): ?>
                <div class="d-flex justify-content-center align-items-center gap-2 my-4 flex-wrap">
                    <?php
                        $pageQueryArgs = [];
                        if (!empty($scstr)) $pageQueryArgs['scstr'] = $scstr;
                        if ($filter_status !== 'all') $pageQueryArgs['filter_status'] = $filter_status;
                        if ($sort_by !== 'title_asc') $pageQueryArgs['sort_by'] = $sort_by;

                        function buildSerialsPageUrl($p, $args) {
                            $args['page'] = $p;
                            return 'serials.php?' . http_build_query($args);
                        }

                        if ($pageNum > 1) {
                            echo '<a class="btn btn-secondary btn-sm" href="' . htmlspecialchars(buildSerialsPageUrl(1, $pageQueryArgs), ENT_QUOTES, 'UTF-8') . '">&laquo; First</a>';
                            echo '<a class="btn btn-secondary btn-sm" href="' . htmlspecialchars(buildSerialsPageUrl($pageNum - 1, $pageQueryArgs), ENT_QUOTES, 'UTF-8') . '">&lsaquo; Prev</a>';
                        }

                        $rangeStart = max(1, $pageNum - 2);
                        $rangeEnd = min($maxPage, $pageNum + 2);

                        for ($p = $rangeStart; $p <= $rangeEnd; $p++) {
                            if ($p == $pageNum) {
                                echo '<span class="btn btn-primary btn-sm disabled"><strong>' . $p . '</strong></span>';
                            } else {
                                echo '<a class="btn btn-secondary btn-sm" href="' . htmlspecialchars(buildSerialsPageUrl($p, $pageQueryArgs), ENT_QUOTES, 'UTF-8') . '">' . $p . '</a>';
                            }
                        }

                        if ($pageNum < $maxPage) {
                            echo '<a class="btn btn-secondary btn-sm" href="' . htmlspecialchars(buildSerialsPageUrl($pageNum + 1, $pageQueryArgs), ENT_QUOTES, 'UTF-8') . '">Next &rsaquo;</a>';
                            echo '<a class="btn btn-secondary btn-sm" href="' . htmlspecialchars(buildSerialsPageUrl($maxPage, $pageQueryArgs), ENT_QUOTES, 'UTF-8') . '">Last &raquo;</a>';
                        }
                    ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <!-- Footer Return Link -->
        <div class="text-center my-3">
            <a class="btn btn-secondary btn-sm" href="../index2.php">&larr; Back to Administration Dashboard</a>
        </div>

        <?php include_once '../includes/footerbar.php';?>
    </div>

    <!-- Application Modal Dialog -->
    <div id="appModal" class="modal-backdrop" onclick="if(event.target===this) closeAppModal(true);">
        <div id="appModalDialog" class="modal-dialog modal-md">
            <div class="modal-header">
                <h5 class="modal-title" id="appModalTitle">Serial Action</h5>
                <button type="button" class="modal-close-btn" onclick="closeAppModal(true);" title="Close modal">&times;</button>
            </div>
            <div class="modal-body p-0">
                <iframe id="appModalIframe" class="modal-iframe" src="about:blank"></iframe>
            </div>
        </div>
    </div>

    <!-- Image Preview Modal Dialog -->
    <div id="imageModal" class="modal-backdrop" onclick="if(event.target===this) closeImageModal();">
        <div class="modal-dialog modal-md" style="max-width: 520px;">
            <div class="modal-header">
                <h5 class="modal-title" id="imageModalTitle">Cover Image</h5>
                <button type="button" class="modal-close-btn" onclick="closeImageModal();" title="Close modal">&times;</button>
            </div>
            <div class="modal-body text-center p-3" style="background:#f8fafc; overflow:auto;">
                <img id="imageModalImg" src="" alt="Cover Image" class="rounded shadow" style="max-height: 75vh; max-width: 100%; height: auto; width: auto; object-fit: contain; margin: 0 auto; display: block;">
            </div>
        </div>
    </div>
</body>
</html>
