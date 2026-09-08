<!DOCTYPE HTML>
<?php
    session_start();define('includeExist', true);
    include_once 'config.php';
    include_once 'includes/functions.php';
    unset($_SESSION['username']);
    unset($_SESSION['fullname']);
    unset($_SESSION['editmode']);
    $_SESSION['ref'] = 'opac.php';

    if (isset($_GET["scstr"]) && $_GET["scstr"] <> '') {
        $scstr_value = stripslashes(str_replace('"', '&#34;', (string)$_GET["scstr"]));
    }
?>
<html lang='en'>

<head>
    <meta charset="utf-8">
    <title><?php echo $product_name;?> : Guest Mode</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="<?php echo $mini_icon_path;?>" rel="icon" type="image/png" />
    <link href="./assets/styles/style.css?v=2" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="./assets/fontawesome/css/all.min.css">
</head>

<body>
    <div id="navbar">
        <form action="opac.php" method="get" enctype="multipart/form-data">
            <input type="text" name="scstr" maxlength="255" placeholder="Enter terms to search..." value="<?php echo $scstr_value ?? "";?>"/>
            <input type="hidden" name="sctype" value="All Type" />
            <input type="submit" class="btn btn-primary btn-sm" name="scflag" value="Search" />
        </form>
    </div>
    
    <?php include_once './includes/loggedinfo.php'; ?>
    
    <div class="app-container">
        <div class="card text-center mb-3">
            <div class="card-body">
                <p class="text-muted mb-2"><em>Enter your search terms and press Search to continue.</em></p>
                <form action="opac.php" method="get" enctype="multipart/form-data">
                    <div class="search-box-wrap">
                        <input type="text" name="scstr" maxlength="255" placeholder="Search catalog..." value="<?php echo $scstr_value ?? "";?>"/>
                        <input type="hidden" name="sctype" value="All Type" />
                        <input type="submit" class="btn btn-primary" name="scflag" value="Search" />
                    </div>
                </form>
            </div>
        </div>

        <?php include_once './includes/browser_bar.php'; ?>
        
        <div class="mt-3">
            <?php
                //start paging 1
                include_once './includes/paging-p1.php';
                        
                $scstr2 = '';
                if ((isset($_GET["scflag"]) && $_GET["scflag"] == 'Search') || (isset($_GET['sctype']) && $_GET['sctype'] <> null)) {
                    $latest1 = "FALSE";
                    $sctype2 = $_GET["sctype"] ?? 'All Type';
                    $scstr2 = $_GET["scstr"] ?? '';
                    include_once './includes/index2_s_boolean.php';
                } else {
                    $latest1 = "TRUE";
                    $query1 = "select SQL_CALC_FOUND_ROWS * from eg_item order by id desc LIMIT $offset, $rowsPerPage";
                }
                
                $time_start = getmicrotime();
                $result1 = mysqli_query($GLOBALS["conn"], $query1);
                
                //start paging 2
                include_once './includes/paging-p2.php';
        
                $time_end = getmicrotime();
                $time = round($time_end - $time_start, 5);
                
                $sortby = '';
                if ($latest1 == "FALSE") {
                    echo "<div class='alert alert-info justify-between'>";
                    echo "<div><strong>Total records found:</strong> $num_results_affected <em>$sortby</em> <strong>in</strong> {$time}s</div>";
                    echo "</div>";
                    include_once './includes/index2_sgmtdsrch.php';
                } else {
                    echo "<div class='alert alert-info'><strong>Latest additions to the catalog:</strong></div>";
                }
                                
                echo "<table class='table-modern'>";

                $n = $offset + 1;
                $stmtCopies = mysqli_prepare($GLOBALS["conn"], "SELECT `39status`, `39is_reference` FROM eg_item_copies WHERE eg_item_id=?");
                while ($myrow=mysqli_fetch_array($result1)) {
                    echo "<tr class='table-row'>";
                        $id2=$myrow["id"];
                        $id2_int = (int)$id2;
                        $tajuk2=$myrow["38title"];
                        $jenis2=$myrow["39type"];
                        $pengarang2=$myrow["38author"];
                        $sumber2=$myrow["38source"];
                        $hits2=$myrow["41hits"];
                        $localcallnum2 =$myrow["38localcallnum"];
                        $publication2 =$myrow["38publication"];
                        
                        $tajuk2_b = $myrow["38title_b"] ?? '';
                        $tajuk2_c = $myrow["38title_c"] ?? '';
                        $callnum2_b = $myrow["38localcallnum_b"] ?? '';

                        $disp_call2 = trim((string)$localcallnum2);
                        if (!empty($callnum2_b)) {
                            $cb = trim((string)$callnum2_b);
                            if (empty($disp_call2)) $disp_call2 = $cb;
                            elseif (!str_contains($disp_call2, $cb)) $disp_call2 .= ' ' . $cb;
                        }

                        // Query copies availability
                        mysqli_stmt_bind_param($stmtCopies, "i", $id2_int);
                        mysqli_stmt_execute($stmtCopies);
                        $resultCopies = mysqli_stmt_get_result($stmtCopies);
                        $totalCopies2 = 0;
                        $availCopies2 = 0;
                        $refCopies2 = 0;
                        while ($rowCop = mysqli_fetch_array($resultCopies)) {
                            $totalCopies2++;
                            $cStatus = $rowCop["39status"] ?? '';
                            $cIsRef = $rowCop["39is_reference"] ?? 'NO';
                            if ($cStatus == 'AVAILABLE' && $cIsRef !== 'YES') {
                                $availCopies2++;
                            } elseif ($cIsRef === 'YES' || $cStatus == 'REFERENCE') {
                                $refCopies2++;
                            }
                        }
                        
                        echo "<td class='valign-top text-center col-num'>";
                            echo "<a href='javascript:window.scrollTo(0,0)' title='Go to top of this page' class='text-muted'><i class='fa-solid fa-circle-chevron-up fa-lg'></i></a><br/><strong>$n</strong>";
                        echo "</td>";
                        
                        echo "<td class='valign-top text-center col-type'>";
                            echo "<span class='badge badge-primary mb-1'>".idToType($jenis2)."</span><br/><i class='fa-solid fa-book-open text-muted fa-2x mt-1'></i>";
                        echo "</td>";
                        
                        echo "<td class='valign-top col-content'>";
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
                            echo " <span class='badge badge-secondary'>$hits2 hits</span>";
                            if (!empty($disp_call2)) {
                                echo " <span class='badge badge-secondary font-monospace ms-1'><i class='fa-solid fa-bookmark me-1'></i>" . htmlspecialchars($disp_call2, ENT_QUOTES, 'UTF-8') . "</span>";
                            }
                            if ($totalCopies2 > 0) {
                                if ($availCopies2 > 0) {
                                    echo " <span class='badge badge-success ms-1' title='$availCopies2 of $totalCopies2 copies available for loan'><i class='fa-solid fa-circle-check me-1'></i>Available: $availCopies2 / $totalCopies2</span>";
                                    if ($refCopies2 > 0) {
                                        echo " <span class='badge badge-info ms-1' title='$refCopies2 reference copies'><i class='fa-solid fa-shield-halved me-1'></i>Ref: $refCopies2</span>";
                                    }
                                } elseif ($refCopies2 > 0) {
                                    echo " <span class='badge badge-info ms-1' title='$refCopies2 copies designated for Reference / In-Library Use'><i class='fa-solid fa-shield-halved me-1'></i>Reference Only ($refCopies2)</span>";
                                } else {
                                    echo " <span class='badge badge-warning text-dark ms-1' title='All copies currently checked out or unavailable'><i class='fa-solid fa-clock me-1'></i>Checked Out (0 / $totalCopies2)</span>";
                                }
                            } else {
                                echo " <span class='badge badge-secondary ms-1'><i class='fa-solid fa-layer-group me-1'></i>0 Copies</span>";
                            }
                                            
                            if ($pengarang2 != '') {
                                echo "<br/><a title='Click here to search more titles from this author' class='myclass2' href='opac.php?scstr=".urlencode($pengarang2)."&sctype=Author&scflag=Search'><i class='fa-solid fa-user-pen me-1 small'></i>$pengarang2</a>";
                            } else {
                                echo "<br/><span class='text-muted'>$publication2</span>";
                            }
        
                            if ($sumber2 != '') {
                                echo "<br/><span class='text-muted'>$sumber2</span>";
                            }
                        echo "</td>";
                    echo "</tr>";
                    $n = $n + 1;
                }
                mysqli_stmt_close($stmtCopies);
                echo "</table>";
                
                //start paging 3
                include_once './includes/paging-p3.php';
            ?>
        </div>
        
        <?php include_once './includes/footerbar.php';?>
    </div>

    <script>
    window.onscroll = function() {scrollFunction()};
    function scrollFunction() {
        if (document.body.scrollTop > 50 || document.documentElement.scrollTop > 50) {
            document.getElementById("navbar").style.top = "0";
        } else {
            document.getElementById("navbar").style.top = "-100px";
        }
    }
    </script>
</body>
</html>
