<!DOCTYPE HTML>
<?php
    session_start();define('includeExist', true);
    include_once 'config.php';
    include_once 'includes/functions.php';
    include_once 'includes/token_validate.php';
    include_once 'includes/ip_guard.php';
    include_once './includes/del_code.php';
    $_SESSION['ref'] = 'index2.php';
?>

<html lang='en'>

<head>
    <meta charset="utf-8">
    <title><?php echo $product_name;?> : Administration</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="<?php echo $mini_icon_path;?>" rel="icon" type="image/png" />
    <link href="./assets/styles/style.css?v=2" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="./assets/fontawesome/css/all.min.css">
    <script>
        function authorAlarm(itemId, valueId)
        {
            if ((document.getElementById(itemId).value == valueId))
            {
                alert('This search ('+valueId+') will be using its unique search engine. All filtering criteria and options will not be valid (even if you selected any).');
            }
        }
    </script>
</head>

<body>
    <?php
        include_once './includes/loggedinfo.php';

        if (!isset($_SESSION['username'])) {
            echo "<div class='auth-card'><span class='badge badge-danger mb-2'>ACCESS RESTRICTED</span><p class='text-muted'>You do not have permission to access this page. Please contact the administrator.</p></div>";
            echo "</body></html>";
            exit;
        }
    ?>

    <div class="app-container">
        <div class="card mb-3">
            <div class="card-header">
                <div>
                    Hi, <strong><?php echo $_SESSION['fullname'] ?? '';?></strong>
                    <span class="badge badge-primary ms-1"><?php echo ($_SESSION['editmode'] ?? '') == 'SUPER' ? 'Superadmin' : 'Staff';?></span>
                </div>
                <div class="d-flex gap-2">
                    <a class="btn btn-secondary btn-sm" href="reports/adsreport_details.php">
                        <i class="fa-solid fa-file-signature me-1"></i> My Input
                    </a>
                    <a class="btn btn-secondary btn-sm" href="./admin/passchange.php?upd=.g">
                        <i class="fa-solid fa-key me-1"></i> Change Password
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="admin-toolbar">
                    <a class="admin-toolbar-item" href="./admin/reg.php" title="Add new material into the database">
                        <div class="toolbar-icon-wrap icon-add">
                            <i class="fa-solid fa-circle-plus"></i>
                        </div>
                        <span class="toolbar-icon-label">Add Record</span>
                    </a>
                    <a class="admin-toolbar-item" href="./admin/serials.php" title="Serial &amp; Periodicals Management">
                        <div class="toolbar-icon-wrap icon-serials">
                            <i class="fa-solid fa-newspaper"></i>
                        </div>
                        <span class="toolbar-icon-label">Serials</span>
                    </a>
                    <a class="admin-toolbar-item" href="./admin/qr_bprintrange.php" title="Print barcode range for reading materials">
                        <div class="toolbar-icon-wrap icon-print">
                            <i class="fa-solid fa-barcode"></i>
                        </div>
                        <span class="toolbar-icon-label">Print Labels</span>
                    </a>
                    <a class="admin-toolbar-item" href="./admin/charge.php" title="Charge (Borrow) Module">
                        <div class="toolbar-icon-wrap icon-charge">
                            <i class="fa-solid fa-arrow-up-right-from-square"></i>
                        </div>
                        <span class="toolbar-icon-label">Charge / Loan</span>
                    </a>
                    <a class="admin-toolbar-item" href="./admin/discharge.php" title="Discharge (Return) Module">
                        <div class="toolbar-icon-wrap icon-discharge">
                            <i class="fa-solid fa-inbox"></i>
                        </div>
                        <span class="toolbar-icon-label">Discharge</span>
                    </a>
                    <a class="admin-toolbar-item" href="./admin/paysearch.php" title="Late Discharge Fines">
                        <div class="toolbar-icon-wrap icon-fines">
                            <i class="fa-solid fa-money-bill-wave"></i>
                        </div>
                        <span class="toolbar-icon-label">Fines</span>
                    </a>
                    <?php if (($_SESSION['editmode'] ?? '') == 'SUPER') { ?>
                        <a class="admin-toolbar-item" href="reports/adsreport.php" title="System generated report">
                            <div class="toolbar-icon-wrap icon-reports">
                                <i class="fa-solid fa-chart-pie"></i>
                            </div>
                            <span class="toolbar-icon-label">Reports</span>
                        </a>
                        <a class="admin-toolbar-item" href="./admin/dupfinder.php" title="List duplicated items">
                            <div class="toolbar-icon-wrap icon-duplicates">
                                <i class="fa-solid fa-clone"></i>
                            </div>
                            <span class="toolbar-icon-label">Duplicates</span>
                        </a>
                        <a class="admin-toolbar-item" href="./admin/addholiday.php" title="Holiday Listing">
                            <div class="toolbar-icon-wrap icon-holidays">
                                <i class="fa-solid fa-calendar-days"></i>
                            </div>
                            <span class="toolbar-icon-label">Holidays</span>
                        </a>
                        <a class="admin-toolbar-item" href="./admin/addtype.php" title="Add or remove item type">
                            <div class="toolbar-icon-wrap icon-types">
                                <i class="fa-solid fa-tags"></i>
                            </div>
                            <span class="toolbar-icon-label">Types</span>
                        </a>
                        <a class="admin-toolbar-item" href="./admin/addsubject.php" title="Add or remove subject headings">
                            <div class="toolbar-icon-wrap icon-subjects">
                                <i class="fa-solid fa-layer-group"></i>
                            </div>
                            <span class="toolbar-icon-label">Subjects</span>
                        </a>
                        <a class="admin-toolbar-item" href="./admin/chanuser.php" title="User accounts information">
                            <div class="toolbar-icon-wrap icon-users">
                                <i class="fa-solid fa-users-gear"></i>
                            </div>
                            <span class="toolbar-icon-label">Users</span>
                        </a>
                        <a class="admin-toolbar-item" href="./admin/blocked_ips.php" title="Manage blocked IP addresses and security locks">
                            <div class="toolbar-icon-wrap icon-security" style="position:relative;">
                                <i class="fa-solid fa-shield-virus"></i>
                                <?php
                                    $active_blocks_badge = ip_guard_active_count();
                                    if ($active_blocks_badge > 0) {
                                        echo "<span class='badge badge-danger' style='position:absolute; top:-3px; right:-3px; font-size:0.65rem; padding:2px 5px; border-radius:10px; border:2px solid #fff;'>$active_blocks_badge</span>";
                                    }
                                ?>
                            </div>
                            <span class="toolbar-icon-label">Blocked IPs</span>
                        </a>
                    <?php } ?>
                </div>
            </div>
        </div>

        <?php
            if (!empty($_SESSION['flash_delete_msg'])) {
                $del_msg = $_SESSION['flash_delete_msg'];
                $del_type = htmlspecialchars($del_msg['type'] ?? 'info', ENT_QUOTES, 'UTF-8');
                $del_text = $del_msg['text'] ?? '';
                echo "<div class='alert alert-{$del_type} mb-3'>{$del_text}</div>";
                unset($_SESSION['flash_delete_msg']);
            }
        ?>

        <div class="card text-center mb-3">
            <div class="card-body">
                <p class="text-muted mb-2"><em>Enter your search terms and press Search to continue.</em></p>
                <form action="index2.php" method="get" enctype="multipart/form-data">
                    <div class="search-box-wrap">
                        <input type="text" name="scstr" maxlength="255" placeholder="Search catalog..." value="<?php echo htmlspecialchars($_GET['scstr'] ?? '', ENT_QUOTES, 'UTF-8');?>"/>
                        
                        <select id="sctype" name="sctype" onchange="authorAlarm('sctype','Author');authorAlarm('sctype','Control Number');" class="w-auto">
                        <?php
                            $sctype2 = $_GET["sctype"] ?? 0;
                                
                            echo '<option value="All Type" ';if ($sctype2 == 'All Type') {echo 'selected';} echo ' >Title: All Type</option> ';
                            
                            $queryU = "select `38typeid`, `38type` from eg_type";
                            $resultU = mysqli_query($GLOBALS["conn"], $queryU);
                            while ($row=mysqli_fetch_array($resultU)) {
                                echo '<option value="'.$row['38typeid'].'"';if ($row['38typeid']==$sctype2) {echo ' selected';} echo '>Title: '. $row['38type'] . '</option>'."\n";
                            }
                    
                            if (isset($_SESSION['username'])) {
                                echo '<option value="Control Number" ';if ($sctype2 == 'Control Number') {echo 'selected';} echo ' >Control Number</option>';
                            }
                            
                            echo '<option value="Author" ';if ($sctype2 == 'Author') {echo 'selected';} echo ' >Author</option> ';
                        ?>
                        </select>
                        <input type="submit" class="btn btn-primary" name="scflag" value="Search" />
                    </div>
                </form>
            </div>
        </div>

        <?php include_once './includes/browser_bar.php';?>
                            
        <div class="mt-3">
            <?php
                //start paging 1
                include_once './includes/paging-p1.php';
                
                if ((isset($_GET["scflag"]) && $_GET["scflag"] == 'Search') || (isset($_GET["sctype"]) && $_GET["sctype"] <> null)) {
                    $latest1 = "FALSE";
                    $sctype2 = $_GET["sctype"] ?? 'All Type';
                    $scstr2 = $_GET["scstr"] ?? '';
                    include_once './includes/index2_s_boolean.php';
                } else {
                    $latest1 = "TRUE";
                    $query1 = "select SQL_CALC_FOUND_ROWS * from eg_item order by id desc LIMIT $offset, $rowsPerPage";
                    $sctype2 = 0;
                    $scstr2 = "";
                }
                                        
                $time_start = getmicrotime();
                $result1 = mysqli_query($GLOBALS["conn"], $query1);
                                                        
                //start paging 2
                include_once './includes/paging-p2.php';

                $time_end = getmicrotime();
                $time = round($time_end - $time_start, 5);

                if ($latest1 == "FALSE") {
                    echo "<div class='alert alert-info justify-between'>";
                    echo "<div><strong>Total records found:</strong> $num_results_affected <strong>in</strong> {$time}s <strong>for</strong> <em>".stripslashes(str_replace('"', '&#34;', $scstr2))."</em></div>";
                    echo "</div>";
                    include_once './includes/index2_sgmtdsrch.php';
                } else {
                    echo "<div class='alert alert-info'><strong>Latest additions to the database:</strong></div>";
                }
        
                echo "<table class='table-modern'>";
                                                                    
                $n = $offset + 1;
        
                while ($myrow=mysqli_fetch_array($result1)) {
                    echo "<tr class='table-row'>";
                    
                    $id2=$myrow["id"];
                    $id2_int = (int)$id2;
                    $tajuk2=$myrow["38title"];
                    $jenis2=$myrow["39type"];
                    $pengarang2=$myrow["38author"];
                    $tarikh_masuk2=$myrow["40inputdate"];
                    $hits2=$myrow["41hits"];
                    $input_masuk2=$myrow["40inputby"];
                    $pdfattach2=$myrow["39pdfattach"];
                    $localcallnum2 =$myrow["38localcallnum"];
                    $instimestamp2 =$myrow["40instimestamp"];

                    $tajuk2_b = $myrow["38title_b"] ?? '';
                    $tajuk2_c = $myrow["38title_c"] ?? '';
                    $callnum2_b = $myrow["38localcallnum_b"] ?? '';

                    $disp_call2 = trim((string)$localcallnum2);
                    if (!empty($callnum2_b)) {
                        $cb = trim((string)$callnum2_b);
                        if (empty($disp_call2)) $disp_call2 = $cb;
                        elseif (!str_contains($disp_call2, $cb)) $disp_call2 .= ' ' . $cb;
                    }
                                        
                    echo "<td class='valign-top text-center col-num'>";
                        echo "<a href='javascript:window.scrollTo(0,0)' title='Go to top of this page' class='text-muted'><i class='fa-solid fa-circle-chevron-up fa-lg'></i></a><br/><strong>$n</strong>";
                    echo "</td>";
                                        
                    echo "<td class='valign-top text-left'>";
                        $detail_params = ['det' => $id2];
                        if ($scstr2 !== '') {
                            $detail_params['scstr'] = $scstr2;
                        }
                        if (!empty($_GET['scflag'])) {
                            $detail_params['scflag'] = $_GET['scflag'];
                        }
                        if (!empty($_GET['sctype'])) {
                            $detail_params['sctype'] = $_GET['sctype'];
                        }
                        if (isset($pageNum) && (int)$pageNum > 1) {
                            $detail_params['page'] = (int)$pageNum;
                        }
                        $detailUrl = 'details.php?' . http_build_query($detail_params);

                        echo "<a title='Click here to view detail' class='myclassOri' href='" . htmlspecialchars($detailUrl, ENT_QUOTES, 'UTF-8') . "'>";
                        if ($scstr2 <> null && $scstr2 !== '') {
                            echo highlight($tajuk2 . " / " . $tajuk2_b . " " . $tajuk2_c, $scstr2) . "</a>";
                        } else {
                            echo "$tajuk2 / $tajuk2_b $tajuk2_c</a>";
                        }
                                            
                        if ($pengarang2 != '') {
                            echo "<br/><a class='myclass2' href='index2.php?scstr=".urlencode($pengarang2)."&sctype=Author&scflag=Search'><i class='fa-solid fa-user-pen me-1 small'></i>$pengarang2</a>";
                        }

                        $stmtTy = mysqli_prepare($GLOBALS["conn"], "SELECT `38type` FROM eg_type WHERE `38typeid` = ?");
                        mysqli_stmt_bind_param($stmtTy, "s", $jenis2);
                        mysqli_stmt_execute($stmtTy);
                        $resultTy = mysqli_stmt_get_result($stmtTy);
                        $myrowTy=mysqli_fetch_array($resultTy);
                        $jenisTy=$myrowTy["38type"] ?? '';
                        mysqli_stmt_close($stmtTy);
                        echo "<br/><span class='badge badge-primary me-1'>$jenisTy</span>";
                        if (!empty($disp_call2)) {
                            echo "<span class='badge badge-secondary font-monospace me-1'><i class='fa-solid fa-bookmark me-1'></i>" . htmlspecialchars($disp_call2, ENT_QUOTES, 'UTF-8') . "</span>";
                        }
                        
                        echo "<span class='badge badge-secondary me-1'>Hits: ";
                        if (($_SESSION['editmode'] ?? '') == 'SUPER') {
                            echo "<a title='Hits for $id2' target='_blank' href='reports/adsreport_ipitems.php?det=$id2'>$hits2</a>";
                        } else {
                            echo "$hits2";
                        }
                        echo "</span>";
                        
                        $stmtL = mysqli_prepare($GLOBALS["conn"], "SELECT `39accessnum`, `39status`, `39is_reference` FROM eg_item_copies WHERE eg_item_id=?");
                        mysqli_stmt_bind_param($stmtL, "i", $id2_int);
                        mysqli_stmt_execute($stmtL);
                        $resultL = mysqli_stmt_get_result($stmtL);
                        $totalCopies2 = 0;
                        $availCopies2 = 0;
                        $refCopies2 = 0;
                        $totalLnx = 0;
                        $stmtLnx = mysqli_prepare($GLOBALS["conn"], "SELECT count(`39accessnum`) as total FROM eg_item_charge WHERE `39accessnum`=?");
                        while ($myrowL=mysqli_fetch_array($resultL)) {
                            $totalCopies2++;
                            $cStatusL = $myrowL["39status"] ?? '';
                            $cIsRefL = $myrowL["39is_reference"] ?? 'NO';
                            if ($cStatusL == 'AVAILABLE' && $cIsRefL !== 'YES') {
                                $availCopies2++;
                            } elseif ($cIsRefL === 'YES' || $cStatusL == 'REFERENCE') {
                                $refCopies2++;
                            }
                            $accessnum2=$myrowL["39accessnum"];
                            mysqli_stmt_bind_param($stmtLnx, "s", $accessnum2);
                            mysqli_stmt_execute($stmtLnx);
                            $resultLnx = mysqli_stmt_get_result($stmtLnx);
                            $myrowLnx=mysqli_fetch_array($resultLnx);
                            $totalLnx = $totalLnx + ($myrowLnx["total"] ?? 0);
                        }
                        mysqli_stmt_close($stmtLnx);
                        mysqli_stmt_close($stmtL);
                        
                        if ($totalCopies2 > 0) {
                            if ($availCopies2 > 0) {
                                echo "<span class='badge badge-success me-1' title='$availCopies2 of $totalCopies2 copies available for checkout'><i class='fa-solid fa-circle-check me-1'></i>Copies: $availCopies2/$totalCopies2 Available</span>";
                                if ($refCopies2 > 0) {
                                    echo "<span class='badge badge-info me-1' title='$refCopies2 reference copies (in-library use)'><i class='fa-solid fa-shield-halved me-1'></i>Ref: $refCopies2</span>";
                                }
                            } elseif ($refCopies2 > 0) {
                                echo "<span class='badge badge-info me-1' title='$refCopies2 copies designated for in-library reference only'><i class='fa-solid fa-shield-halved me-1'></i>Ref Only ($refCopies2)</span>";
                            } else {
                                echo "<span class='badge badge-warning text-dark me-1' title='All copies currently borrowed or unavailable'><i class='fa-solid fa-clock me-1'></i>Copies: 0/$totalCopies2 Available</span>";
                            }
                        } else {
                            echo "<span class='badge badge-secondary me-1'><i class='fa-solid fa-layer-group me-1'></i>0 Copies</span>";
                        }
                        echo "<span class='badge badge-info'>Loans: $totalLnx</span>";
                                                
                        $dir_year2 = substr("$tarikh_masuk2", -4);
                        if ($pdfattach2 == 'TRUE') {
                            echo " <span class='ms-2'>";
                            if (is_file("$pdf_upload_directory/$dir_year2/$id2"."_"."$instimestamp2.pdf")) {
                                echo "<a href='$pdf_upload_directory/$dir_year2/$id2"."_"."$instimestamp2.pdf' target='_blank' class='btn btn-danger btn-sm p-1' title='Download PDF attachment'><i class='fa-solid fa-file-pdf'></i> PDF</a>";
                            } else {
                                echo "<span class='badge badge-secondary' title='PDF attachment missing'><i class='fa-regular fa-file'></i> Missing</span>";
                            }
                            echo "</span>";
                        }
                    echo "</td>";
                                                    
                    echo "<td class='valign-top text-muted text-left col-meta'>";
                        echo "<small><strong>Added by:</strong><br/>".patronUsernametoName($input_masuk2)."<br/><strong>Date:</strong> $tarikh_masuk2</small>";
                    echo "</td>";
                    
                    echo "</tr>";
                    $n = $n + 1;
                }
                echo "</table>";
                                    
                //start paging 3
                include_once './includes/paging-p3.php';
            ?>
        </div>
        
        <?php include_once './includes/footerbar.php';?>
    </div>
</body>
</html>
