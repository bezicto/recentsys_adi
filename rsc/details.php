<?php
    session_start();define('includeExist', true);

    include_once 'config.php';
    include_once 'includes/functions.php';

    if (isset($_GET["det"]) && is_numeric($_GET["det"])) {
        $get_id_det = $_GET["det"] ?? 0;
    } else {
        $get_id_det = 0;
    }
        
    $get_scstr = $_GET["scstr"] ?? '';
    $get_infname = $_GET["infname"] ?? '';
    $get_page = isset($_GET["page"]) && is_numeric($_GET["page"]) ? (int)$_GET["page"] : 0;
    $get_sctype = $_GET["sctype"] ?? '';
    $get_scflag = $_GET["scflag"] ?? '';
    $get_delr = $_GET["delr"] ?? '';
    $get_subid = isset($_GET["subid"]) && is_numeric($_GET["subid"]) ? (int)$_GET["subid"] : 0;
    $get_inf = $_GET["inf"] ?? '';

    $stmt1 = mysqli_prepare($GLOBALS["conn"], "SELECT * FROM eg_item WHERE id=?");
    $det_id = (int)$get_id_det;
    mysqli_stmt_bind_param($stmt1, "i", $det_id);
    mysqli_stmt_execute($stmt1);
    $result1 = mysqli_stmt_get_result($stmt1);
    $num_results_affected = mysqli_num_rows($result1);

    if ($num_results_affected <= 0) {
        mysqli_stmt_close($stmt1);
        echo "<!DOCTYPE HTML><html lang='en'><head><link href='./assets/styles/style.css' rel='stylesheet' type='text/css'></head><body><div class='auth-card'><span class='badge badge-danger mb-2'>ERROR</span><h2>Error: Item does not exist.</h2><em class='text-muted'>System Response Code</em></div></body></html>";
        exit;
    }

    $myrow = mysqli_fetch_array($result1);
    mysqli_stmt_close($stmt1);
        $id5=$myrow["id"] ?? '';
        $tajuk5=$myrow["38title"] ?? '';
        $location5=$myrow["38location"] ?? '';
        $link5=$myrow["38link"] ?? '';
        $isbn5 = str_replace(['-', '–', '—'], '', $myrow["38isbn"] ?? '');
        $issn5 = str_replace(['-', '–', '—'], '', $myrow["38issn"] ?? '');
        $edition5 = $myrow["38edition"] ?? '';
        $publication5 = $myrow["38publication"] ?? '';
        $physicaldesc5 = $myrow["38physicaldesc"] ?? '';
        $series5 = $myrow["38series"] ?? '';
        $notes5 = $myrow["38notes"] ?? '';
        $fcnotes5 = $myrow["38fcnotes"] ?? '';
        $source5 = $myrow["38source"] ?? '';
        $pengarang5=$myrow["38author"] ?? '';
        $callnumber5=$myrow["38localcallnum"] ?? '';
        $jenis5=$myrow["39type"] ?? '';
        $subjectheading5 =$myrow["39subjectheading"] ?? '';
        $imageatt5=$myrow["39imageatt"] ?? '';
        $pdfattach5=$myrow["39pdfattach"] ?? '';
        $tarikh_masuk5=$myrow["40inputdate"] ?? '';
        $input_oleh5=$myrow["40inputby"] ?? '';
        $lastupdateby5=$myrow["40lastupdateby"] ?? '';
        $instimestamp5 =$myrow["40instimestamp"] ?? '';
        $hits5=$myrow["41hits"] ?? '';
        $language5=$myrow["39language"] ?? '';
        
        include_once 'includes/details_insertintostat.php';
        
        $callnum5_b=$myrow["38localcallnum_b"] ?? '';
        $pengarang5_d=$myrow["38author_d"] ?? '';
        $tajuk5_b=$myrow["38title_b"] ?? '';
        $tajuk5_c=$myrow["38title_c"] ?? '';
        $publication5_b=$myrow["38publication_b"] ?? '';
        $publication5_c=$myrow["38publication_c"] ?? '';
        $physicaldesc5_b=$myrow["38physicaldesc_b"] ?? '';
        $physicaldesc5_c=$myrow["38physicaldesc_c"] ?? '';
        $physicaldesc5_e=$myrow["38physicaldesc_e"] ?? '';
        $series5_v=$myrow["38series_v"] ?? '';
        $sumber5_b=$myrow["38source_b"] ?? '';
        $sumber5_e=$myrow["38source_e"] ?? '';
        $lokasi5_b=$myrow["38location_b"] ?? '';
        $lokasi5_c=$myrow["38location_c"] ?? '';

        // Fetch additional ISBNs
        $extra_isbns5 = [];
        $stmt_isbn = mysqli_prepare($GLOBALS["conn"], "SELECT * FROM eg_item_isbn WHERE eg_item_id = ?");
        mysqli_stmt_bind_param($stmt_isbn, "i", $det_id);
        mysqli_stmt_execute($stmt_isbn);
        $result_isbn = mysqli_stmt_get_result($stmt_isbn);
        if ($result_isbn && $myrow_isbn = mysqli_fetch_assoc($result_isbn)) {
            for ($i = 1; $i <= 20; $i++) {
                $val_isbn_i = str_replace(['-', '–', '—'], '', trim($myrow_isbn["isbn$i"] ?? ($myrow_isbn["isbn3_$i"] ?? '')));
                if (!empty($val_isbn_i)) {
                    $extra_isbns5[] = $val_isbn_i;
                }
            }
        }
        mysqli_stmt_close($stmt_isbn);

        // Language display mapping
        $language_display5 = '';
        if (!empty($language5)) {
            $language_display5 = $language5;
            if (!empty($tag_041_selectable) && !empty($tag_041_selectable_def)) {
                $selectable_041 = explode("|", $tag_041_selectable);
                $selectable_041_def = explode("|", $tag_041_selectable_def);
                $lang_key = array_search($language5, $selectable_041);
                if ($lang_key !== false && isset($selectable_041_def[$lang_key])) {
                    $language_display5 = $selectable_041_def[$lang_key] . " (" . $language5 . ")";
                }
            }
        }

        // Call Number display formatting (combines 38localcallnum and 38localcallnum_b cleanly)
        $display_callnum = trim((string)$callnumber5);
        if (!empty($callnum5_b)) {
            $callnum_b_clean = trim((string)$callnum5_b);
            if (empty($display_callnum)) {
                $display_callnum = $callnum_b_clean;
            } elseif (!str_contains($display_callnum, $callnum_b_clean)) {
                $display_callnum .= ' ' . $callnum_b_clean;
            }
        }

        // Copies availability summary
        $stmtCopiesTot = mysqli_prepare($GLOBALS["conn"], "SELECT 
            COUNT(*) as total_copies,
            SUM(CASE WHEN `39status` = 'AVAILABLE' AND (`39is_reference` IS NULL OR `39is_reference` != 'YES') THEN 1 ELSE 0 END) as available_copies,
            SUM(CASE WHEN `39is_reference` = 'YES' OR `39status` = 'REFERENCE' THEN 1 ELSE 0 END) as reference_copies
            FROM eg_item_copies WHERE eg_item_id = ?");
        $id5_int = (int)$id5;
        mysqli_stmt_bind_param($stmtCopiesTot, "i", $id5_int);
        mysqli_stmt_execute($stmtCopiesTot);
        $resCopiesTot = mysqli_stmt_get_result($stmtCopiesTot);
        $rowCopiesTot = mysqli_fetch_assoc($resCopiesTot);
        $total_copies5 = (int)($rowCopiesTot['total_copies'] ?? 0);
        $available_copies5 = (int)($rowCopiesTot['available_copies'] ?? 0);
        $reference_copies5 = (int)($rowCopiesTot['reference_copies'] ?? 0);
        mysqli_stmt_close($stmtCopiesTot);

    $get_ser_scstr = $_GET["ser_scstr"] ?? ($_GET["scstr"] ?? '');
    $get_ser_filter = $_GET["ser_filter"] ?? ($_GET["filter_status"] ?? '');
    $get_ser_sort = $_GET["ser_sort"] ?? ($_GET["sort_by"] ?? '');
    $get_ser_page = isset($_GET["ser_page"]) && is_numeric($_GET["ser_page"]) ? (int)$_GET["ser_page"] : (isset($_GET["page"]) && is_numeric($_GET["page"]) ? (int)$_GET["page"] : 0);

    $contextParams = [];
    if (!empty($get_scstr)) $contextParams['scstr'] = $get_scstr;
    if (!empty($get_sctype)) $contextParams['sctype'] = $get_sctype;
    if (!empty($get_scflag)) $contextParams['scflag'] = $get_scflag;
    if (!empty($get_page)) $contextParams['page'] = $get_page;
    if (!empty($get_delr)) $contextParams['delr'] = $get_delr;
    if (!empty($get_subid)) $contextParams['subid'] = $get_subid;
    if (!empty($get_inf)) $contextParams['inf'] = $get_inf;
    if (!empty($get_infname)) $contextParams['infname'] = $get_infname;
    if (!empty($get_ser_scstr)) $contextParams['ser_scstr'] = $get_ser_scstr;
    if (!empty($get_ser_filter)) $contextParams['ser_filter'] = $get_ser_filter;
    if (!empty($get_ser_sort)) $contextParams['ser_sort'] = $get_ser_sort;
    if (!empty($get_ser_page)) $contextParams['ser_page'] = $get_ser_page;

    $updContextQuery = !empty($contextParams) ? '&' . http_build_query($contextParams) : '';

    // Calculate deterministic return/back destination
    if ($get_delr === 'serials') {
        $serParams = [];
        if (!empty($get_ser_scstr)) $serParams['scstr'] = $get_ser_scstr;
        if (!empty($get_ser_filter) && $get_ser_filter !== 'all') $serParams['filter_status'] = $get_ser_filter;
        if (!empty($get_ser_sort) && $get_ser_sort !== 'title_asc') $serParams['sort_by'] = $get_ser_sort;
        if (!empty($get_ser_page) && $get_ser_page > 1) $serParams['page'] = $get_ser_page;
        $backUrl = "admin/serials.php" . (!empty($serParams) ? '?' . http_build_query($serParams) : '');
        $backLabel = "Back to Serial Management";
    } elseif ($get_delr === 'rep' || $get_delr === 'urep') {
        $reportInf = !empty($get_inf) ? $get_inf : $input_oleh5;
        $backUrl = "reports/adsreport_details.php?inf=" . urlencode($reportInf);
        if (!empty($get_page)) $backUrl .= "&page=" . $get_page;
        if (!empty($get_infname)) $backUrl .= "&infname=" . urlencode($get_infname);
        if (!empty($_GET['inputyear'])) $backUrl .= "&inputyear=" . urlencode($_GET['inputyear']);
        $backLabel = ($get_delr === 'urep') ? "Back to my input list" : "Back to report details";
    } elseif ($get_delr === 'sjdir') {
        $backUrl = "browsers/subject_details.php?subid=" . (int)$get_subid;
        if (!empty($get_page)) $backUrl .= "&page=" . $get_page;
        $backLabel = "Back to subject browser";
    } elseif (!empty($get_scstr) || !empty($get_sctype) || !empty($get_scflag) || !empty($get_page)) {
        $targetScript = (isset($_SESSION['username'])) ? 'index2.php' : 'opac.php';
        $searchParams = [];
        if (!empty($get_scstr)) $searchParams['scstr'] = $get_scstr;
        if (!empty($get_sctype)) $searchParams['sctype'] = $get_sctype;
        if (!empty($get_scflag)) $searchParams['scflag'] = $get_scflag;
        if (!empty($get_page)) $searchParams['page'] = $get_page;
        $backUrl = $targetScript . (!empty($searchParams) ? '?' . http_build_query($searchParams) : '');
        $backLabel = "Back to search results";
    } else {
        $targetScript = (isset($_SESSION['username'])) ? 'index2.php' : 'opac.php';
        $backUrl = $targetScript;
        $backLabel = "Back to catalog";
    }
?>
<!DOCTYPE HTML>
<html lang='en'>

<head>
    <meta charset="utf-8">
    <title><?php echo htmlspecialchars($product_name, ENT_QUOTES, 'UTF-8');?> : Record Details</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="<?php echo htmlspecialchars($mini_icon_path, ENT_QUOTES, 'UTF-8');?>" rel="icon" type="image/png" />
    <link href="./assets/styles/style.css?v=2" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="./assets/fontawesome/css/all.min.css">
    <script>
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

        function openImageModal(elemOrSrc, titleText) {
            var imgSrc = '';
            var imgTitle = '';

            if (typeof elemOrSrc === 'string') {
                imgSrc = elemOrSrc;
                imgTitle = titleText || 'Cover Image';
            } else if (elemOrSrc && elemOrSrc.getAttribute) {
                imgSrc = elemOrSrc.getAttribute('data-src') || elemOrSrc.getAttribute('data-cover-src') || '';
                imgTitle = elemOrSrc.getAttribute('data-title') || elemOrSrc.getAttribute('data-cover-title') || 'Cover Image';
            }

            if (!imgSrc) return false;

            var modal = document.getElementById('imageModal');
            var img = document.getElementById('imageModalImg');
            var title = document.getElementById('imageModalTitle');

            if (modal && img) {
                img.src = imgSrc;
                img.alt = imgTitle;
                if (title) title.textContent = imgTitle;
                modal.classList.add('show');
                document.body.style.overflow = 'hidden';
            }
            return false;
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
    <?php include_once 'includes/loggedinfo.php'; ?>
    
    <div class="app-container">
        <?php
            if (!empty($_SESSION['flash_alerts'])) {
                foreach ($_SESSION['flash_alerts'] as $alert) {
                    $atype = htmlspecialchars($alert['type'] ?? 'info', ENT_QUOTES, 'UTF-8');
                    $amsg = $alert['msg'] ?? '';
                    echo "<div class='alert alert-{$atype}'>{$amsg}</div>";
                }
                unset($_SESSION['flash_alerts']);
            }
        ?>
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <strong>Record Details</strong>
                    <span class="badge badge-primary"><?php echo htmlspecialchars(idToType($jenis5), ENT_QUOTES, 'UTF-8');?></span>
                    <?php if (!empty($display_callnum)) { ?>
                        <span class="badge badge-secondary font-monospace" style="font-size: 0.85rem;"><i class="fa-solid fa-bookmark me-1"></i><?php echo htmlspecialchars($display_callnum, ENT_QUOTES, 'UTF-8');?></span>
                    <?php } ?>
                    <?php if ($total_copies5 > 0) { ?>
                        <?php if ($available_copies5 > 0) { ?>
                            <span class="badge badge-success" style="font-size: 0.85rem;"><i class="fa-solid fa-circle-check me-1"></i>Available: <?php echo $available_copies5;?>/<?php echo $total_copies5;?></span>
                        <?php } else { ?>
                            <span class="badge badge-warning text-dark" style="font-size: 0.85rem;"><i class="fa-solid fa-clock me-1"></i>Checked Out (0/<?php echo $total_copies5;?>)</span>
                        <?php } ?>
                        <?php if ($reference_copies5 > 0) { ?>
                            <span class="badge badge-info" style="font-size: 0.85rem;"><i class="fa-solid fa-shield-halved me-1"></i>Reference: <?php echo $reference_copies5;?></span>
                        <?php } ?>
                    <?php } else { ?>
                        <span class="badge badge-secondary" style="font-size: 0.85rem;"><i class="fa-solid fa-layer-group me-1"></i>0 Copies</span>
                    <?php } ?>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-secondary btn-sm" onclick="openAppModal('marc_view.php?det=<?php echo (int)$id5; ?>', 'MARC21 Bibliographic Record (#<?php echo (int)$id5; ?>)', 'modal-lg');" title="View MARC21 record in modal dialog">
                        <i class="fa-solid fa-file-code me-1"></i> MARC Record
                    </button>
                    <a class="btn btn-secondary btn-sm" href="marc_export.php?det=<?php echo (int)$id5; ?>&format=mrc" title="Download standard MARC21 (.mrc) record">
                        <i class="fa-solid fa-download me-1"></i> Export MARC
                    </a>
                </div>
            </div>
            <div class="card-body p-0">
                <table class="table-modern m-0">
                    <tr><th class="w-25 text-right">Control Number:</th><td><strong><?php echo htmlspecialchars((string)$id5, ENT_QUOTES, 'UTF-8');?></strong></td></tr>
                    <tr><th class="text-right">Hits:</th><td>
                        <?php
                            if (isset($_SESSION['editmode']) && $_SESSION['editmode'] == 'SUPER') {
                                echo "<a title='Hits for " . htmlspecialchars((string)$id5, ENT_QUOTES, 'UTF-8') . "' target='_blank' class='badge badge-secondary' href='reports/adsreport_ipitems.php?det=" . (int)$id5 . "'>" . htmlspecialchars((string)$hits5, ENT_QUOTES, 'UTF-8') . " hits</a>";
                            } else {
                                echo "<span class='badge badge-secondary'>" . htmlspecialchars((string)$hits5, ENT_QUOTES, 'UTF-8') . " hits</span>";
                            }
                        ?>
                    </td></tr>
                    
                    <!-- 1. Material Type (39type) -->
                    <?php if (!empty($jenis5) && $jenis5 !== '0') { ?>
                        <tr>
                            <th class="text-right">Material Type:</th>
                            <td><span class="badge badge-primary"><?php echo htmlspecialchars(idToType($jenis5), ENT_QUOTES, 'UTF-8');?></span></td>
                        </tr>
                    <?php } ?>

                    <!-- 2. Call Number (090) (38localcallnum & 38localcallnum_b) -->
                    <?php if (!empty($display_callnum)) { ?>
                        <tr>
                            <th class="text-right">Call Number:</th>
                            <td>
                                <span class="badge badge-primary font-monospace" style="font-size: 0.95rem; padding: 4px 10px; letter-spacing: 0.5px;">
                                    <i class="fa-solid fa-bookmark me-1"></i><?php echo htmlspecialchars($display_callnum, ENT_QUOTES, 'UTF-8');?>
                                </span>
                            </td>
                        </tr>
                    <?php } ?>

                    <!-- 2. Subject Heading (39subjectheading) -->
                    <?php
                        if ($subjectheading5 <> null && trim($subjectheading5) !== '') {
                            $subjectheadings = explode("|", $subjectheading5);
                            $total = count($subjectheadings) - 1;
                            $i = 1; $returnSH = '';
                            foreach ($subjectheadings as $subjectheading) {
                                $subjectheading = trim($subjectheading);
                                if (!empty($subjectheading)) {
                                    $shName = subjectFromAcronym($subjectheading) ?? $subjectheading;
                                    $returnSH .= htmlspecialchars($shName, ENT_QUOTES, 'UTF-8');
                                    if ($i < $total) { $returnSH .= "<br/>"; }
                                    $i++;
                                }
                            }
                            if (!empty($returnSH)) {
                                echo "<tr><th class='text-right valign-top'>Subject Heading:</th><td>$returnSH</td></tr>";
                            }
                        }
                    ?>

                    <!-- 3. ISBN (020) (38isbn + eg_item_isbn) -->
                    <?php
                        $all_isbns = [];
                        if (!empty($isbn5)) {
                            $all_isbns[] = $isbn5;
                        }
                        foreach ($extra_isbns5 as $ex_isbn) {
                            if (!empty($ex_isbn) && !in_array($ex_isbn, $all_isbns, true)) {
                                $all_isbns[] = $ex_isbn;
                            }
                        }
                        if (!empty($all_isbns)) {
                    ?>
                        <tr>
                            <th class="text-right valign-top">ISBN:</th>
                            <td>
                                <?php
                                    $isbn_lines = array_map(function($val) {
                                        return htmlspecialchars($val, ENT_QUOTES, 'UTF-8');
                                    }, $all_isbns);
                                    echo implode("<br/>", $isbn_lines);
                                ?>
                            </td>
                        </tr>
                    <?php } ?>

                    <!-- 4. ISSN (022) (38issn) -->
                    <?php if ($issn5 != '') { ?>
                        <tr><th class="text-right">ISSN:</th><td><?php echo htmlspecialchars($issn5, ENT_QUOTES, 'UTF-8');?></td>
                        </tr>
                    <?php } ?>

                    <!-- 5. Language (041) (39language) -->
                    <?php if (!empty($language_display5)) { ?>
                        <tr><th class="text-right">Language:</th><td><?php echo htmlspecialchars($language_display5, ENT_QUOTES, 'UTF-8');?></td></tr>
                    <?php } ?>

                    <!-- 6. Author (100) (38author & 38author_d) -->
                    <?php if ($pengarang5 != '' || $pengarang5_d != '') { ?>
                        <tr>
                            <th class="text-right">Author:</th>
                            <td>
                                <?php
                                    $author_display = $pengarang5;
                                    if (!empty($pengarang5_d)) {
                                        $author_display .= (!empty($author_display) ? ', ' : '') . $pengarang5_d;
                                    }
                                    echo htmlspecialchars(trim($author_display), ENT_QUOTES, 'UTF-8');
                                ?>
                            </td>
                        </tr>
                    <?php } ?>

                    <!-- 8. Title (245) (38title, 38title_b, 38title_c) -->
                    <?php if ($tajuk5 != '' || $tajuk5_b != '' || $tajuk5_c != '') { ?>
                        <tr>
                            <th class="text-right">Title:</th>
                            <td>
                                <strong>
                                    <?php
                                        $title_display = $tajuk5;
                                        if (!empty($tajuk5_b)) {
                                            $title_display .= " : " . $tajuk5_b;
                                        }
                                        if (!empty($tajuk5_c)) {
                                            $title_display .= " / " . $tajuk5_c;
                                        }
                                        echo htmlspecialchars(trim($title_display), ENT_QUOTES, 'UTF-8');
                                    ?>
                                </strong>
                            </td>
                        </tr>
                    <?php } ?>

                    <!-- 9. Edition (250) (38edition) -->
                    <?php if ($edition5 != '') { ?>
                        <tr><th class="text-right">Edition:</th><td><?php echo htmlspecialchars($edition5, ENT_QUOTES, 'UTF-8');?></td></tr>
                    <?php } ?>

                    <!-- 10. Publication (264) (38publication, 38publication_b, 38publication_c) -->
                    <?php if ($publication5 != '' || $publication5_b != '' || $publication5_c != '') { ?>
                        <tr>
                            <th class="text-right">Publication:</th>
                            <td>
                                <?php
                                    $pub_parts = [];
                                    if (!empty($publication5)) $pub_parts[] = $publication5;
                                    if (!empty($publication5_b)) $pub_parts[] = (!empty($pub_parts) ? ': ' : '') . $publication5_b;
                                    if (!empty($publication5_c)) $pub_parts[] = (!empty($pub_parts) ? ', ' : '') . $publication5_c;
                                    echo htmlspecialchars(trim(implode(' ', $pub_parts)), ENT_QUOTES, 'UTF-8');
                                ?>
                            </td>
                        </tr>
                    <?php } ?>

                    <!-- 11. Physical Description (300) (38physicaldesc, 38physicaldesc_b, 38physicaldesc_c, 38physicaldesc_e) -->
                    <?php if ($physicaldesc5 != '' || $physicaldesc5_b != '' || $physicaldesc5_c != '' || $physicaldesc5_e != '') { ?>
                        <tr>
                            <th class="text-right">Physical Description:</th>
                            <td>
                                <?php
                                    $phys_parts = [];
                                    if (!empty($physicaldesc5)) $phys_parts[] = $physicaldesc5;
                                    if (!empty($physicaldesc5_b)) $phys_parts[] = (!empty($phys_parts) ? ': ' : '') . $physicaldesc5_b;
                                    if (!empty($physicaldesc5_c)) $phys_parts[] = (!empty($phys_parts) ? '; ' : '') . $physicaldesc5_c;
                                    if (!empty($physicaldesc5_e)) $phys_parts[] = (!empty($phys_parts) ? '+ ' : '') . $physicaldesc5_e;
                                    echo htmlspecialchars(trim(implode(' ', $phys_parts)), ENT_QUOTES, 'UTF-8');
                                ?>
                            </td>
                        </tr>
                    <?php } ?>

                    <!-- 12. Series (490) (38series, 38series_v) -->
                    <?php if ($series5 != '' || $series5_v != '') { ?>
                        <tr>
                            <th class="text-right">Series:</th>
                            <td>
                                <?php
                                    $series_display = $series5;
                                    if (!empty($series5_v)) {
                                        $series_display .= (!empty($series_display) ? ' ; ' : '') . $series5_v;
                                    }
                                    echo htmlspecialchars(trim($series_display), ENT_QUOTES, 'UTF-8');
                                ?>
                            </td>
                        </tr>
                    <?php } ?>

                    <!-- 13. Notes (500) (38notes) -->
                    <?php if ($notes5 != '') { ?>
                        <tr><th class="text-right">Notes:</th><td><?php echo nl2br(htmlspecialchars($notes5, ENT_QUOTES, 'UTF-8'));?></td></tr>
                    <?php } ?>

                    <!-- 14. Formatted Contents Note (505) (38fcnotes) -->
                    <?php if ($fcnotes5 != '') { ?>
                        <tr><th class="text-right valign-top">Formatted Contents Note:</th><td><?php echo nl2br(htmlspecialchars($fcnotes5, ENT_QUOTES, 'UTF-8'));?></td></tr>
                    <?php } ?>
        
                    <!-- 15. Corporate Name (710) (38source, 38source_b, 38source_e) -->
                    <?php if ($source5 != '' || $sumber5_b != '' || $sumber5_e != '') { ?>
                        <tr>
                            <th class="text-right">Corporate Name:</th>
                            <td>
                                <?php
                                    $corp_parts = [];
                                    if (!empty($source5)) $corp_parts[] = $source5;
                                    if (!empty($sumber5_b)) $corp_parts[] = (!empty($corp_parts) ? '. ' : '') . $sumber5_b;
                                    if (!empty($sumber5_e)) $corp_parts[] = "($sumber5_e)";
                                    echo htmlspecialchars(trim(implode(' ', $corp_parts)), ENT_QUOTES, 'UTF-8');
                                ?>
                            </td>
                        </tr>
                    <?php } ?>
                    
                    <!-- 16. Location (852) (38location, 38location_b, 38location_c) -->
                    <?php if ($location5 != '' || $lokasi5_b != '' || $lokasi5_c != '') { ?>
                        <tr>
                            <th class="text-right">Location:</th>
                            <td>
                                <?php
                                    $loc_parts = [];
                                    if (!empty($location5)) $loc_parts[] = $location5;
                                    if (!empty($lokasi5_b)) $loc_parts[] = (!empty($loc_parts) ? ' - ' : '') . $lokasi5_b;
                                    if (!empty($lokasi5_c)) $loc_parts[] = (!empty($loc_parts) ? " ($lokasi5_c)" : $lokasi5_c);
                                    echo htmlspecialchars(trim(implode(' ', $loc_parts)), ENT_QUOTES, 'UTF-8');
                                ?>
                            </td>
                        </tr>
                    <?php } ?>
                                                        
                    <!-- 17. HTTP / Web Link (856) (38link) -->
                    <?php
                        if ($link5 <> null && trim($link5) !== '') {
                            $link_decoded = urldecode($link5);
                            if (substr($link_decoded, 0, 4) !== 'http') { $link_decoded = 'http://' . $link_decoded; }
                            $safe_link = htmlspecialchars($link_decoded, ENT_QUOTES, 'UTF-8');
                            if (filter_var($link_decoded, FILTER_VALIDATE_URL)) {
                                echo "<tr><th class='text-right'>Web Link:</th><td><a href='$safe_link' target='_blank' rel='noopener noreferrer'>$safe_link</a></td></tr>";
                            }
                        }
                    ?>

                    <!-- 18. PDF File (39pdfattach) -->
                    <?php
                        $dir_year5 = substr("$tarikh_masuk5", -4);
                        if ($pdfattach5 == 'TRUE') {
                            echo "<tr><th class='text-right'>PDF File:</th><td><a class='btn btn-sm btn-primary' href='viewdoc.php?id=" . (int)$id5 . "' target='_blank'><i class='fa-solid fa-file-pdf me-1'></i> View / Download PDF</a></td></tr>";
                        }
                    ?>
                    
                    <!-- 19. Cover Image (39imageatt) -->
                    <?php
                        if ($imageatt5 == 'TRUE') {
                            if (is_file("$cover_upload_directory/$dir_year5/$id5"."_"."$instimestamp5.jpg")) {
                                $cover_url = htmlspecialchars("$cover_upload_directory/$dir_year5/$id5" . "_" . "$instimestamp5.jpg", ENT_QUOTES, 'UTF-8');
                                $cover_alt = htmlspecialchars($tajuk5, ENT_QUOTES, 'UTF-8');
                                echo "<tr>";
                                echo "<th class='text-right' style='vertical-align: top; padding-top: 14px;'>Cover Image:</th>";
                                echo "<td style='vertical-align: top;'>";
                                echo "<div class='d-flex align-items-start gap-3 flex-wrap'>";
                                echo "<a href='javascript:void(0)' data-src='$cover_url' data-title='$cover_alt' onclick='openImageModal(this); return false;' title='Click to enlarge cover image' style='display:inline-block; text-decoration:none;'>";
                                echo "<div style='position:relative; display:inline-block; border-radius:8px; overflow:hidden; border:1px solid rgba(0,0,0,0.12); box-shadow:0 2px 6px rgba(0,0,0,0.08); cursor:pointer; transition:transform 0.15s ease, box-shadow 0.15s ease;' onmouseover=\"this.style.transform='scale(1.03)';this.style.boxShadow='0 4px 12px rgba(0,0,0,0.15)';\" onmouseout=\"this.style.transform='scale(1)';this.style.boxShadow='0 2px 6px rgba(0,0,0,0.08)';\">";
                                echo "<img src='$cover_url' alt='$cover_alt' style='width: 100px; height: 140px; object-fit: cover; display: block;'>";
                                echo "<div style='position:absolute; bottom:0; left:0; right:0; background:linear-gradient(to top, rgba(0,0,0,0.7), transparent); padding:16px 4px 4px; text-align:center; color:#fff; font-size:0.7rem; font-weight:600;'><i class='fa-solid fa-magnifying-glass-plus me-1'></i>Enlarge</div>";
                                echo "</div>";
                                echo "</a>";
                                echo "</div>";
                                echo "</td>";
                                echo "</tr>";
                            } else {
                                echo "<tr><th class='text-right' style='vertical-align: top; padding-top: 14px;'>Cover Image:</th><td style='vertical-align: top;'><span class='badge badge-danger'>Missing Image</span></td></tr>";
                            }
                        }
                    ?>
                </table>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <strong>Copies Availability &amp; Status</strong>
                    <?php if (!empty($display_callnum)) { ?>
                        <span class="badge badge-secondary font-monospace"><i class="fa-solid fa-bookmark me-1"></i><?php echo htmlspecialchars($display_callnum, ENT_QUOTES, 'UTF-8');?></span>
                    <?php } ?>
                    <?php if ($total_copies5 > 0) { ?>
                        <?php if ($available_copies5 > 0) { ?>
                            <span class="badge badge-success"><i class="fa-solid fa-circle-check me-1"></i>Available: <?php echo $available_copies5;?> of <?php echo $total_copies5;?></span>
                        <?php } else { ?>
                            <span class="badge badge-warning text-dark"><i class="fa-solid fa-clock me-1"></i>Checked Out (0 of <?php echo $total_copies5;?> available)</span>
                        <?php } ?>
                        <?php if ($reference_copies5 > 0) { ?>
                            <span class="badge badge-info"><i class="fa-solid fa-shield-halved me-1"></i>Reference / Non-circulating: <?php echo $reference_copies5;?></span>
                        <?php } ?>
                    <?php } else { ?>
                        <span class="badge badge-secondary"><i class="fa-solid fa-layer-group me-1"></i>0 Copies Registered</span>
                    <?php } ?>
                </div>
                <?php if (isset($_SESSION['editmode']) && ($_SESSION['editmode'] == 'SUPER' || $_SESSION['editmode'] == 'TRUE')) { ?>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-success btn-sm" onclick="return openAppModal('admin/copies_add.php?bahan_id=<?php echo (int)$id5;?>', 'Add New Copy', 'modal-md')"><i class="fa-solid fa-plus me-1"></i> Add New Copy</button>
                        <button type="button" class="btn btn-secondary btn-sm" onClick="document.location.reload(true)"><i class="fa-solid fa-rotate me-1"></i> Refresh</button>
                    </div>
                <?php } ?>
            </div>
            <div class="card-body p-0">
                <?php if (isset($_SESSION['editmode']) && ($_SESSION['editmode'] == 'SUPER' || $_SESSION['editmode'] == 'TRUE')) { ?>
                    <table class="table-modern m-0">
                    <thead>
                        <tr>
                            <th class="text-center w-25">Accession &amp; Issue</th>
                            <th>Status &amp; Acquisition Info</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            $stmtC = mysqli_prepare($GLOBALS["conn"], "SELECT * FROM eg_item_copies WHERE eg_item_id=? ORDER BY id ASC");
                            $id5_int = (int)$id5;
                            mysqli_stmt_bind_param($stmtC, "i", $id5_int);
                            mysqli_stmt_execute($stmtC);
                            $resultC = mysqli_stmt_get_result($stmtC);
                            while ($myrowC=mysqli_fetch_array($resultC)) {
                                $copies_id=$myrowC["id"];
                                $copies_accession_number=$myrowC["39accessnum"];
                                $copies_status=$myrowC["39status"];
                                $copies_volume=$myrowC["39volume"] ?? '';
                                $copies_issue=$myrowC["39issue"] ?? '';
                                $copies_year=$myrowC["39year"] ?? '';
                                $copies_is_reference=$myrowC["39is_reference"] ?? 'NO';
                                $copies_addedon=$myrowC["39addedon"];
                                $copies_lastchange=$myrowC["39lastchange"];
                                $copies_invoice_a=$myrowC["39invoice_a"];
                                $copies_invoice_b=$myrowC["39invoice_b"];
                                $copies_invoice_c=$myrowC["39invoice_c"];

                                $enum_parts = [];
                                if (!empty($copies_volume)) $enum_parts[] = $copies_volume;
                                if (!empty($copies_issue)) $enum_parts[] = $copies_issue;
                                if (!empty($copies_year)) $enum_parts[] = '(' . $copies_year . ')';
                                $enum_text = implode(' ', $enum_parts);

                                echo "<tr class='table-row'>";
                                     echo "<td class='text-center valign-top'>";
                                         echo "<strong>" . htmlspecialchars($copies_accession_number, ENT_QUOTES, 'UTF-8') . "</strong>";
                                         if (!empty($enum_text)) {
                                             echo "<div class='mt-1'><span class='badge badge-secondary font-monospace'><i class='fa-solid fa-layer-group me-1'></i>" . htmlspecialchars($enum_text, ENT_QUOTES, 'UTF-8') . "</span></div>";
                                         }
                                         if ($copies_status != 'CIRCULATED' && (($_SESSION['editmode'] ?? '') == 'SUPER')) {
                                             echo "<br/><button type='button' class='btn btn-danger btn-sm mt-1' onclick=\"return openAppModal('admin/copies_delete.php?id=$copies_id', 'Delete Copy Item', 'modal-sm');\"><i class='fa-solid fa-trash me-1'></i> Delete</button>";
                                         }
                                     echo "</td>";
                                     echo "<td class='valign-top'>";
                                         if ($copies_status == 'AVAILABLE') {
                                             if ($copies_is_reference === 'YES') {
                                                 echo "<span class='badge badge-warning'><i class='fa-solid fa-lock me-1'></i>Reference Only</span> ";
                                             } else {
                                                 echo "<span class='badge badge-success'>Available</span> ";
                                             }
                                         } elseif ($copies_status == 'REFERENCE') {
                                             echo "<span class='badge badge-warning'><i class='fa-solid fa-lock me-1'></i>Reference</span> ";
                                         } elseif ($copies_status == 'CIRCULATED') {
                                             echo "<span class='badge badge-warning'>Circulated</span> ";
                                         } else {
                                             echo "<span class='badge badge-secondary'>" . htmlspecialchars($copies_status, ENT_QUOTES, 'UTF-8') . "</span> ";
                                         }
                                         if (!empty($display_callnum)) {
                                             echo "<span class='badge badge-secondary font-monospace ms-1'><i class='fa-solid fa-bookmark me-1'></i>" . htmlspecialchars($display_callnum, ENT_QUOTES, 'UTF-8') . "</span> ";
                                         }
                                         echo "<button type='button' class='btn btn-secondary btn-sm' onclick=\"return openAppModal('admin/change_status.php?cid=$copies_id', 'Edit Copy', 'modal-md');\"><i class='fa-solid fa-pen me-1'></i> Edit Copy</button><br/>";
                                         if (($_SESSION['editmode'] ?? '') == 'SUPER') {
                                             echo "<div class='text-muted mt-1'>Price: " . htmlspecialchars($currency_SHORT, ENT_QUOTES, 'UTF-8') . " " . htmlspecialchars($copies_invoice_a, ENT_QUOTES, 'UTF-8') . " | Supplier: " . htmlspecialchars($copies_invoice_b, ENT_QUOTES, 'UTF-8') . " | Invoice: " . htmlspecialchars($copies_invoice_c, ENT_QUOTES, 'UTF-8') . "</div>";
                                         }
                                         echo "<div class='text-muted'><small>Added: " . date('D, Y-m-d h:i:s a', $copies_addedon) . " | Modified: " . date('D, Y-m-d h:i:s a', $copies_lastchange) . "</small></div>";
                                     echo "</td>";
                                 echo "</tr>";
                            }
                            mysqli_stmt_close($stmtC);
                        ?>
                    </tbody>
                    </table>
                <?php } else { ?>
                    <table class="table-modern m-0">
                    <thead>
                        <tr>
                            <th class="text-center w-50">Accession Number / Issue</th>
                            <th>Current Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            $stmtC = mysqli_prepare($GLOBALS["conn"], "SELECT * FROM eg_item_copies WHERE eg_item_id=? ORDER BY id ASC");
                            $id5_int = (int)$id5;
                            mysqli_stmt_bind_param($stmtC, "i", $id5_int);
                            mysqli_stmt_execute($stmtC);
                            $resultC = mysqli_stmt_get_result($stmtC);
                            while ($myrowC=mysqli_fetch_array($resultC)) {
                                $copies_accession_number=$myrowC["39accessnum"] ?? '';
                                $copies_status=$myrowC["39status"] ?? '';
                                $copies_volume=$myrowC["39volume"] ?? '';
                                $copies_issue=$myrowC["39issue"] ?? '';
                                $copies_year=$myrowC["39year"] ?? '';
                                $copies_is_reference=$myrowC["39is_reference"] ?? 'NO';

                                $enum_parts = [];
                                if (!empty($copies_volume)) $enum_parts[] = $copies_volume;
                                if (!empty($copies_issue)) $enum_parts[] = $copies_issue;
                                if (!empty($copies_year)) $enum_parts[] = '(' . $copies_year . ')';
                                $enum_text = implode(' ', $enum_parts);

                                echo "<tr class='table-row'>";
                                    echo "<td class='text-center'>";
                                        echo "<strong>" . htmlspecialchars($copies_accession_number, ENT_QUOTES, 'UTF-8') . "</strong>";
                                        if (!empty($enum_text)) {
                                             echo "<div class='mt-1'><span class='badge badge-secondary font-monospace'><i class='fa-solid fa-layer-group me-1'></i>" . htmlspecialchars($enum_text, ENT_QUOTES, 'UTF-8') . "</span></div>";
                                        }
                                    echo "</td>";
                                    echo "<td>";
                                        if ($copies_status == 'AVAILABLE') {
                                            if ($copies_is_reference === 'YES') {
                                                echo "<span class='badge badge-warning'><i class='fa-solid fa-lock me-1'></i>Reference Only</span>";
                                            } else {
                                                echo "<span class='badge badge-success'>Available</span>";
                                            }
                                        } elseif ($copies_status == 'REFERENCE') {
                                            echo "<span class='badge badge-warning'><i class='fa-solid fa-lock me-1'></i>Reference</span>";
                                        } elseif ($copies_status == 'CIRCULATED') {
                                            echo "<span class='badge badge-warning'>Circulated</span>";
                                        } else {
                                            echo "<span class='badge badge-secondary'>" . htmlspecialchars($copies_status, ENT_QUOTES, 'UTF-8') . "</span>";
                                        }
                                        if (!empty($display_callnum)) {
                                            echo "<span class='badge badge-secondary font-monospace ms-2'><i class='fa-solid fa-bookmark me-1'></i>" . htmlspecialchars($display_callnum, ENT_QUOTES, 'UTF-8') . "</span>";
                                        }
                                    echo "</td>";
                                echo "</tr>";
                            }
                            mysqli_stmt_close($stmtC);
                        ?>
                    </tbody>
                    </table>
                <?php } ?>
            </div>
        </div>

        <?php if (isset($_SESSION['editmode']) && ($_SESSION['editmode'] == 'SUPER' || $_SESSION['editmode'] == 'TRUE')) { ?>
            <div class="card mb-3">
                <div class="card-header">
                    <strong>Administrative Details</strong>
                </div>
                <div class="card-body p-0">
                    <table class="table-modern m-0">
                        <tr><th class="w-25 text-right">Input Date:</th><td><?php echo htmlspecialchars($tarikh_masuk5, ENT_QUOTES, 'UTF-8');?></td></tr>
                        <tr><th class="text-right">Input By:</th><td><?php echo htmlspecialchars(patronUsernametoName($input_oleh5), ENT_QUOTES, 'UTF-8');?></td></tr>
                        
                        <?php if (isset($_SESSION['editmode']) && $_SESSION['editmode'] == 'SUPER') { ?>
                            <tr><th class="text-right">Last Update By:</th><td><?php echo htmlspecialchars(patronUsernametoName($lastupdateby5), ENT_QUOTES, 'UTF-8');?></td></tr>
                        <?php } ?>
                            
                        <tr><th class="text-right">Actions:</th><td>
                            <?php
                                echo "<a class='btn btn-primary btn-sm' href='admin/reg.php?upd=" . (int)$id5 . htmlspecialchars($updContextQuery, ENT_QUOTES, 'UTF-8') . "'><i class='fa-solid fa-pen-to-square me-1'></i> Update Record</a> ";
                                echo "<button type='button' class='btn btn-secondary btn-sm' onclick=\"openAppModal('marc_view.php?det=" . (int)$id5 . "', 'MARC21 Bibliographic Record (#" . (int)$id5 . ")', 'modal-lg');\"><i class='fa-solid fa-file-code me-1'></i> MARC View</button> ";

                                if (($_SESSION['editmode'] ?? '') == 'SUPER') {
                                    $csrfToken = htmlspecialchars($_SESSION['token'] ?? '', ENT_QUOTES, 'UTF-8');
                                    $returnPage = 'index2.php';
                                    if (isset($_GET['delr']) && $_GET['delr'] == 'serials') {
                                        $returnPage = "admin/serials.php";
                                    } elseif (isset($_GET['delr']) && ($_GET['delr'] == 'rep' || $_GET['delr'] == 'urep')) {
                                        $returnPage = "reports/adsreport_details.php?inf=" . urlencode($input_oleh5);
                                    } elseif (isset($_GET['delr']) && $_GET['delr'] == 'sjdir') {
                                        $returnPage = "browsers/subject_details.php?subid=" . (int)($_GET['subid'] ?? 0);
                                    }

                                    $delFilename = ($pdfattach5 == 'TRUE') ? htmlspecialchars("$dir_year5/$id5" . "_" . "$instimestamp5", ENT_QUOTES, 'UTF-8') : '';
                                    echo "<form method='post' action='" . htmlspecialchars($returnPage, ENT_QUOTES, 'UTF-8') . "' style='display:inline;' onsubmit=\"return confirm('Are you sure to permanently delete this catalog record?');\">";
                                    echo "<input type='hidden' name='token' value='$csrfToken'>";
                                    echo "<input type='hidden' name='del' value='" . (int)$id5 . "'>";
                                    if (!empty($delFilename)) {
                                        echo "<input type='hidden' name='delfilename' value='$delFilename'>";
                                    }
                                    echo "<button type='submit' class='btn btn-danger btn-sm'><i class='fa-solid fa-trash me-1'></i> Delete Record</button>";
                                    echo "</form>";
                                }
                            ?>
                        </td></tr>
                    </table>
                </div>
            </div>
        <?php } ?>

        <div class="text-center my-3">
            <a class="btn btn-secondary btn-sm" href="<?php echo htmlspecialchars($backUrl, ENT_QUOTES, 'UTF-8');?>">&larr; <?php echo htmlspecialchars($backLabel, ENT_QUOTES, 'UTF-8');?></a>
        </div>
        
        <?php include_once './includes/footerbar.php';?>
    </div>

    <!-- Application Modal Dialog -->
    <div id="appModal" class="modal-backdrop" onclick="if(event.target===this) closeAppModal(true);">
        <div id="appModalDialog" class="modal-dialog modal-md">
            <div class="modal-header">
                <h5 class="modal-title" id="appModalTitle">Item Action</h5>
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
