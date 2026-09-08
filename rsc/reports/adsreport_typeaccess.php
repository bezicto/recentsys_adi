<!DOCTYPE HTML>
<?php
    session_start();define('includeExist', true);
    include_once '../includes/access_logged.php';
    include_once '../config.php';
    include_once '../includes/stat_cache.php';

    if (!isset($_GET["type"]) || !is_numeric($_GET["type"])) {
        echo "<div class='container-narrow my-5 text-center alert alert-danger'><h2>Forbidden: Illegal Access</h2></div>";
        exit;
    }
    
    $typeid = (int)$_GET["type"];
    $typetext = $_GET["typetext"] ?? 'Material Type';
    $inputyear = isset($_GET["inputyear"]) && is_numeric($_GET["inputyear"]) ? $_GET["inputyear"] : date('Y');
?>

<html lang='en'>

<head>
    <meta charset="utf-8">
    <title><?php echo htmlspecialchars($product_name, ENT_QUOTES, 'UTF-8');?> : Type Access Analytics (<?php echo htmlspecialchars($typetext, ENT_QUOTES, 'UTF-8');?>)</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="../<?php echo htmlspecialchars($mini_icon_path, ENT_QUOTES, 'UTF-8');?>" rel="icon" type="image/png" />
    <link href="../assets/styles/style.css" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="../assets/fontawesome/css/all.min.css">
</head>

<body>
    <?php include_once '../includes/loggedinfo.php';?>
                    
    <div class="app-container">
        <div class="card mw-800 mb-3">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <span><strong>Access Analytics:</strong> <?php echo htmlspecialchars($typetext, ENT_QUOTES, 'UTF-8');?></span>
                <div class="d-flex align-items-center gap-2">
                    <span class="small font-bold">Year:</span>
                    <?php
                        $query3 = "select distinct SUBSTRING(`40inputdate`,7) AS inputyear from eg_item order by inputyear";
                        $result3 = mysqli_query($GLOBALS["conn"], $query3);
                        echo "<select name='inputyear' class='form-control form-control-sm' ONCHANGE='location = this.options[this.selectedIndex].value;'>";
                        while ($myrow3=mysqli_fetch_array($result3)) {
                            $yr=$myrow3["inputyear"];
                            echo "<option value=\"adsreport_typeaccess.php?inputyear=$yr&type=$typeid&typetext=".urlencode($typetext)."\"";
                            if ($inputyear == $yr) {
                                echo " selected";
                            }
                            echo ">$yr</option>";
                        }
                        echo "</select>";
                    ?>
                </div>
            </div>
            <div class="card-body p-0">
                <table class="table-modern m-0">
                <thead>
                    <tr>
                        <th class="text-center" style="width:140px;">Month / Year</th>
                        <th class="text-center">Total Accesses (Click to inspect)</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                    $months_names = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                    $key_prefix = "type_access_" . (int)$typeid;

                    // Hybrid 12-month caching: past cached in JSON, active month live, future 0
                    $breakdown = StatCache::remember_year_breakdown($key_prefix, $inputyear, function($m, $y) use ($typeid) {
                        $range = StatCache::get_month_timestamp_range($m, $y);
                        $stmt2 = mysqli_prepare($GLOBALS["conn"], "SELECT count(eg_item_det.id) as totalid FROM eg_item_det INNER JOIN eg_item ON eg_item_det.eg_item_id=eg_item.id WHERE eg_item.`39type`=? AND eg_item_det.`39logdate` >= ? AND eg_item_det.`39logdate` <= ?");
                        $type_str = (string)$typeid;
                        mysqli_stmt_bind_param($stmt2, "sii", $type_str, $range['start'], $range['end']);
                        mysqli_stmt_execute($stmt2);
                        $result2 = mysqli_stmt_get_result($stmt2);
                        $myrow = mysqli_fetch_array($result2);
                        mysqli_stmt_close($stmt2);
                        return (int)($myrow["totalid"] ?? 0);
                    });

                    $month_counts = $breakdown['months'];
                    $is_cached_breakdown = $breakdown['is_cached'];

                    $jumlah = 0;
                    for ($counter = 1; $counter <= 12; $counter += 1) {
                        $counterc = sprintf('%02d', $counter);
                        $num_results_affected = $month_counts[$counter - 1] ?? 0;
                        $jumlah += $num_results_affected;

                        echo "<tr class='table-row'>";
                        echo "<td class='text-center'><strong>" . $months_names[$counter-1] . " " . htmlspecialchars($inputyear, ENT_QUOTES, 'UTF-8') . "</strong></td>";
                        echo "<td class='text-center'>";
                        if ($num_results_affected > 0) {
                            $acstat_param = "$counterc/$inputyear";
                            $title_param = htmlspecialchars($typetext . " (" . $months_names[$counter-1] . " $inputyear)", ENT_QUOTES);
                            echo "<a href='javascript:void(0);' onclick=\"openAccessModal('$typeid', '$acstat_param', '$title_param');\" class='badge badge-primary' title='View access details'>$num_results_affected access(es) &rarr;</a>";
                        } else {
                            echo "<span class='text-muted'>0</span>";
                        }
                        echo "</td>";
                        echo "</tr>";
                    }
                ?>
                </tbody>
                <tfoot>
                    <tr class="bg-card font-bold">
                        <td class="text-center d-flex justify-content-between align-items-center">
                            <span>Cumulative for <?php echo htmlspecialchars($inputyear, ENT_QUOTES, 'UTF-8');?>:</span>
                            <?php echo StatCache::render_badge($is_cached_breakdown); ?>
                        </td>
                        <td class="text-center text-primary"><?php echo $jumlah;?> total access(es)</td>
                    </tr>
                </tfoot>
                </table>
            </div>
        </div>

        <div class="text-center my-3">
            <a class="btn btn-secondary btn-sm" href="adsreport.php?toggle=2">&larr; Back to report page</a>
        </div>
        
        <?php include_once '../includes/footerbar.php';?>
    </div>

    <!-- Access Details Modal Dialog -->
    <div id="accessModal" class="modal-backdrop" onclick="if(event.target===this) closeAccessModal();">
        <div class="modal-dialog modal-lg">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle"><i class="fa-solid fa-chart-line me-2 text-primary"></i> Access Details</h5>
                <button type="button" class="modal-close-btn" onclick="closeAccessModal();" title="Close modal">&times;</button>
            </div>
            <div class="modal-body p-0" id="modalBody">
                <div class="text-center p-4 text-muted">
                    <i class="fa-solid fa-spinner fa-spin fa-2x mb-2 text-primary"></i>
                    <div>Loading access details...</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" onclick="closeAccessModal();">Close</button>
            </div>
        </div>
    </div>

    <script>
        function openAccessModal(typeId, acstat, titleText) {
            var modal = document.getElementById('accessModal');
            var title = document.getElementById('modalTitle');
            var body = document.getElementById('modalBody');
            
            title.innerHTML = '<i class="fa-solid fa-chart-line me-2 text-primary"></i> Access Details: ' + titleText;
            body.innerHTML = '<div class="text-center p-4 text-muted"><i class="fa-solid fa-spinner fa-spin fa-2x mb-2 text-primary"></i><div>Loading access details...</div></div>';
            
            modal.classList.add('show');
            document.body.style.overflow = 'hidden';

            fetch('adsreport_typeaccess_more.php?type=' + encodeURIComponent(typeId) + '&acstat=' + encodeURIComponent(acstat) + '&ajax=1')
                .then(function(response) {
                    if (!response.ok) throw new Error('Network error');
                    return response.text();
                })
                .then(function(html) {
                    body.innerHTML = html;
                })
                .catch(function(err) {
                    body.innerHTML = '<div class="alert alert-danger m-3 text-center">Unable to load access details.</div>';
                });
        }

        function closeAccessModal() {
            var modal = document.getElementById('accessModal');
            if (modal) {
                modal.classList.remove('show');
                document.body.style.overflow = '';
            }
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeAccessModal();
            }
        });
    </script>
</body>
</html>
