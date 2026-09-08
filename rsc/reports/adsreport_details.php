<?php
    session_start();define('includeExist', true);
    include_once '../includes/access_logged.php';
    include_once '../config.php';
    include_once '../includes/token_validate.php';
    include_once '../includes/del_code.php';
    include_once '../includes/stat_cache.php';

    $get_inf = '';$get_infname = '';$getappend = '';
    if (($_SESSION['editmode'] ?? '') == 'SUPER' && (isset($_GET['inf']) && $_GET['inf'] != '')) {
        $get_inf = $_GET["inf"];
        $get_infname = $_GET["infname"] ?? '';
        $getappend = "&inf=".urlencode($get_inf)."&infname=".urlencode($get_infname);
    } else {
        $get_inf = $_SESSION["username"] ?? '';
    }
        
    if (isset($_GET["inputyear"]) && is_numeric($_GET["inputyear"])) {
        $get_inputyear = $_GET["inputyear"];
    } elseif (isset($_GET["inputyear"]) && $_GET["inputyear"] == 'All') {
        $get_inputyear = 'All';
    } elseif (isset($_GET["inputyear"]) && !is_numeric($_GET["inputyear"])) {
        echo "<div class='container-narrow my-5 text-center alert alert-danger'><h2>Forbidden: Illegal Access</h2></div>";
        exit;
    } else {
        $get_inputyear = 'All';
    }
?>

<html lang='en'>

<head>
    <meta charset="utf-8">
    <title><?php echo htmlspecialchars($product_name, ENT_QUOTES, 'UTF-8');?> : Cataloging Activity Details</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="../assets/styles/style.css?v=2" rel="stylesheet" type="text/css">
    <link href="../<?php echo htmlspecialchars($mini_icon_path, ENT_QUOTES, 'UTF-8');?>" rel="icon" type="image/png" />
    <link rel="stylesheet" href="../assets/fontawesome/css/all.min.css">
</head>

<body>
    <?php include_once '../includes/loggedinfo.php';?>
                    
    <div class="app-container">
        <?php
            if (!empty($_SESSION['flash_delete_msg'])) {
                $del_msg = $_SESSION['flash_delete_msg'];
                $del_type = htmlspecialchars($del_msg['type'] ?? 'info', ENT_QUOTES, 'UTF-8');
                $del_text = $del_msg['text'] ?? '';
                echo "<div class='alert alert-{$del_type} mb-3'>{$del_text}</div>";
                unset($_SESSION['flash_delete_msg']);
            }
            include_once '../includes/paging-p1.php';
                                                    
            $offset_int = (int)$offset;
            $rowsPerPage_int = (int)$rowsPerPage;
            if ($get_inputyear <> 'All') {
                $year_pattern = '%' . $get_inputyear;
                $stmt1 = mysqli_prepare($GLOBALS["conn"], "SELECT SQL_CALC_FOUND_ROWS * FROM eg_item WHERE `40inputby`=? AND `40inputdate` LIKE ? ORDER BY id DESC LIMIT ?, ?");
                mysqli_stmt_bind_param($stmt1, "ssii", $get_inf, $year_pattern, $offset_int, $rowsPerPage_int);
            } else {
                $stmt1 = mysqli_prepare($GLOBALS["conn"], "SELECT SQL_CALC_FOUND_ROWS * FROM eg_item WHERE `40inputby`=? ORDER BY id DESC LIMIT ?, ?");
                mysqli_stmt_bind_param($stmt1, "sii", $get_inf, $offset_int, $rowsPerPage_int);
            }
            mysqli_stmt_execute($stmt1);
            $result1 = mysqli_stmt_get_result($stmt1);

            include_once '../includes/paging-p2.php';
        ?>

        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <strong>Cataloger:</strong> <?php echo htmlspecialchars("$get_infname ($get_inf)", ENT_QUOTES, 'UTF-8'); ?>
                    <span class="badge badge-info ms-2">Total input <?php if ($get_inputyear <> 'All') {echo "for year " . htmlspecialchars($get_inputyear, ENT_QUOTES, 'UTF-8');} ?>: <?php echo $num_results_affected;?></span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="small font-bold">Filter Year:</span>
                    <?php
                        $query3 = "select distinct SUBSTRING(`40inputdate`,7) AS inputyear from eg_item order by inputyear";
                        $result3 = mysqli_query($GLOBALS["conn"], $query3);
                        echo "<select name='inputyear' class='form-control form-control-sm' ONCHANGE='location = this.options[this.selectedIndex].value;'>";
                        while ($myrow3=mysqli_fetch_array($result3)) {
                            $inputyear=$myrow3["inputyear"];
                            echo "<option value=\"adsreport_details.php?inputyear=$inputyear$getappend\"";
                            if ($get_inputyear == $inputyear) {echo " selected";}
                            echo ">$inputyear</option>";
                        }
                        echo "<option value=\"adsreport_details.php?inputyear=All$getappend\"";
                        if ($get_inputyear== 'All') {echo " selected";}
                        echo ">All Years</option>";
                        echo "</select>";
                    ?>
                </div>
            </div>

            <?php
                if ($get_inputyear != 'All') {
                    $safe_inf = StatCache::sanitize_key($get_inf);
                    $key_prefix = "cataloger_{$safe_inf}";
                    
                    // Hybrid 12-month caching: past cached in JSON, current computed live, future 0
                    $breakdown = StatCache::remember_year_breakdown($key_prefix, $get_inputyear, function($m, $y) use ($get_inf) {
                        $counter_text = sprintf('%02d', $m);
                        $get_monthcount = '%' . $counter_text . '/' . $y;
                        $stmt3_m = mysqli_prepare($GLOBALS["conn"], "SELECT count(*) as total3 FROM eg_item WHERE `40inputby`=? AND `40inputdate` LIKE ?");
                        mysqli_stmt_bind_param($stmt3_m, "ss", $get_inf, $get_monthcount);
                        mysqli_stmt_execute($stmt3_m);
                        $result3 = mysqli_stmt_get_result($stmt3_m);
                        $myrow3 = mysqli_fetch_array($result3);
                        mysqli_stmt_close($stmt3_m);
                        return (int)($myrow3["total3"] ?? 0);
                    });

                    $month_array = $breakdown['months'];
                    $is_cached_breakdown = $breakdown['is_cached'];
                    $months_names = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            ?>
                <div class="card-body border-bottom bg-card p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="small font-bold text-muted">Monthly Breakdown for <?php echo htmlspecialchars($get_inputyear, ENT_QUOTES, 'UTF-8');?> (Click to inspect type breakdown):</div>
                        <div><?php echo StatCache::render_badge($is_cached_breakdown); ?></div>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <?php for ($i = 0; $i < 12; $i++) {
                            $m_idx = ($i + 1 <= 9) ? '0'.($i + 1) : ($i + 1);
                            $month_title_escaped = $months_names[$i] . " " . $get_inputyear;
                            $user_escaped = htmlspecialchars($get_inf, ENT_QUOTES);
                            echo "<a href='javascript:void(0);' onclick=\"openMonthModal('$m_idx', '$get_inputyear', '$user_escaped', '$month_title_escaped');\" class='badge badge-secondary p-2 text-decoration-none' title='View breakdown for ".$months_names[$i]."'>";
                            echo "<strong>".$months_names[$i].":</strong> ".$month_array[$i]." items";
                            echo "</a>";
                        } ?>
                    </div>
                </div>
            <?php
                    unset($month_array);
                }
            ?>

            <div class="card-body p-0">
                <table class="table-modern m-0">
                <thead>
                    <tr>
                        <th class="col-num">#</th>
                        <th class="text-center" style="width:80px;">Doc ID</th>
                        <th>Record Title</th>
                        <th style="width:160px;">Material Type</th>
                        <th class="text-center" style="width:120px;">Input Date</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                    // Pre-fetch material types map to avoid N queries in loop
                    $types_map = [];
                    $res_types = mysqli_query($GLOBALS["conn"], "SELECT `38typeid`, `38type` FROM eg_type");
                    if ($res_types) {
                        while ($rt = mysqli_fetch_assoc($res_types)) {
                            $types_map[(string)$rt['38typeid']] = $rt['38type'];
                        }
                    }

                    $n = $offset + 1;
                    $has_records = false;
                    while ($myrow=mysqli_fetch_array($result1)) {
                        $has_records = true;
                        $tajuk_a=$myrow["38title"];
                        $jenis_a=(string)$myrow["39type"];
                        $id_a=$myrow["id"];
                        $dateinput_a=$myrow["40inputdate"];
                        $jenis_a_mod = $types_map[$jenis_a] ?? 'General';
                        
                        $link_delr = ($get_inf !== '' && $get_infname !== '') ? "&delr=rep&inf=" . urlencode($get_inf) . "&infname=" . urlencode($get_infname) . "&inputyear=" . urlencode($get_inputyear) : "&delr=urep&inputyear=" . urlencode($get_inputyear);
                        
                        echo "<tr class='table-row'>";
                        echo "<td class='col-num'>$n</td>";
                        echo "<td class='text-center'><a href='../details.php?det=$id_a$link_delr&page=$pageNum' class='badge badge-info'>$id_a</a></td>";
                        echo "<td><a href='../details.php?det=$id_a$link_delr&page=$pageNum' class='font-bold'>" . htmlspecialchars($tajuk_a, ENT_QUOTES, 'UTF-8') . "</a></td>";
                        echo "<td><span class='badge badge-secondary'>" . htmlspecialchars($jenis_a_mod, ENT_QUOTES, 'UTF-8') . "</span></td>";
                        echo "<td class='text-center text-muted small'>$dateinput_a</td>";
                        echo "</tr>";
                                                                        
                        $n = $n + 1;
                    }
                    mysqli_stmt_close($stmt1);
                    if (!$has_records) {
                        echo "<tr><td colspan='5' class='text-center text-muted p-3'>No records found for this cataloger in the selected year.</td></tr>";
                    }
                ?>
                </tbody>
                </table>
            </div>
        </div>
        
        <?php
            // Modern pagination
            $pagingappend = '';
            if (isset($get_inputyear) && $get_inputyear != '') {
                $pagingappend .= "&inputyear=$get_inputyear";
            }
            if (($_SESSION['editmode'] ?? '') == 'SUPER') {
                $pagingappend .= "&inf=$get_inf&infname=".urlencode($get_infname);
            }
                                
            if ($num_results_affected > $rowsPerPage) {
                echo "<div class='pagination-bar my-3'>";
                if ($pageNum > 1) {
                    $page = $pageNum - 1;
                    echo "<a class='pagination-link' href=\"$self?page=1$pagingappend\">&laquo; First</a>";
                    echo "<a class='pagination-link' href=\"$self?page=$page$pagingappend\">&lsaquo; Prev</a>";
                }
                echo "<span class='pagination-current'>Page $pageNum of $maxPage</span>";
                if ($pageNum < $maxPage) {
                    $page = $pageNum + 1;
                    echo "<a class='pagination-link' href=\"$self?page=$page$pagingappend\">Next &rsaquo;</a>";
                    echo "<a class='pagination-link' href=\"$self?page=$maxPage$pagingappend\">Last &raquo;</a>";
                }
                echo "</div>";
            }
        ?>
        
        <div class="text-center my-4 d-flex justify-center align-center gap-2 flex-wrap">
            <a class="btn btn-secondary btn-sm" href="../index2.php"><i class="fa-solid fa-house me-1"></i> Back to start page</a>
            <?php if (($_SESSION['editmode'] ?? '') == 'SUPER') { ?>
                <a class="btn btn-secondary btn-sm" href="adsreport.php"><i class="fa-solid fa-chart-pie me-1"></i> Back to report page</a>
            <?php } ?>
        </div>
        
        <?php include_once '../includes/footerbar.php';?>
    </div>

    <!-- Monthly Breakdown Modal Dialog -->
    <div id="monthModal" class="modal-backdrop" onclick="if(event.target===this) closeMonthModal();">
        <div class="modal-dialog">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle"><i class="fa-solid fa-chart-pie me-2 text-primary"></i> Monthly Breakdown</h5>
                <button type="button" class="modal-close-btn" onclick="closeMonthModal();" title="Close modal">&times;</button>
            </div>
            <div class="modal-body p-0" id="modalBody">
                <div class="text-center p-4 text-muted">
                    <i class="fa-solid fa-spinner fa-spin fa-2x mb-2 text-primary"></i>
                    <div>Loading breakdown...</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" onclick="closeMonthModal();">Close</button>
            </div>
        </div>
    </div>

    <script>
        function openMonthModal(month, year, user, titleText) {
            var modal = document.getElementById('monthModal');
            var title = document.getElementById('modalTitle');
            var body = document.getElementById('modalBody');
            
            title.innerHTML = '<i class="fa-solid fa-chart-pie me-2 text-primary"></i> ' + titleText;
            body.innerHTML = '<div class="text-center p-4 text-muted"><i class="fa-solid fa-spinner fa-spin fa-2x mb-2 text-primary"></i><div>Loading breakdown...</div></div>';
            
            modal.classList.add('show');
            document.body.style.overflow = 'hidden';

            fetch('adsreport_details_inc_pop.php?month=' + encodeURIComponent(month) + '&year=' + encodeURIComponent(year) + '&user=' + encodeURIComponent(user) + '&ajax=1')
                .then(function(response) {
                    if (!response.ok) throw new Error('Network error');
                    return response.text();
                })
                .then(function(html) {
                    body.innerHTML = html;
                })
                .catch(function(err) {
                    body.innerHTML = '<div class="alert alert-danger m-3 text-center">Unable to load monthly breakdown.</div>';
                });
        }

        function closeMonthModal() {
            var modal = document.getElementById('monthModal');
            if (modal) {
                modal.classList.remove('show');
                document.body.style.overflow = '';
            }
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeMonthModal();
            }
        });
    </script>
</body>
</html>
