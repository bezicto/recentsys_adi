<!DOCTYPE HTML>
<?php
    session_start();define('includeExist', true);
    include_once '../includes/access_logged.php';
    include_once '../config.php';
    include_once '../includes/functions.php';
?>

<html lang='en'>

<head>
    <meta charset="utf-8">
    <title><?php echo htmlspecialchars($product_name, ENT_QUOTES, 'UTF-8');?> : Manage Copies</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="../<?php echo htmlspecialchars($mini_icon_path, ENT_QUOTES, 'UTF-8');?>" rel="icon" type="image/png" />
    <link href="../assets/styles/style.css?v=2" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="../assets/fontawesome/css/all.min.css">
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

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeAppModal(false);
            }
        });
    </script>
</head>

<body>
    <?php include_once '../includes/loggedinfo.php';?>
            
    <div class="app-container">
        <?php
            if (isset($_GET['id']) && is_numeric($_GET['id'])) {
                $get_id = $_GET['id'];
            } else {
                $get_id = 0;
            }

            $get_id_int = (int)$get_id;
            $stmt1 = mysqli_prepare($GLOBALS["conn"], "SELECT * FROM eg_item WHERE id=?");
            mysqli_stmt_bind_param($stmt1, "i", $get_id_int);
            mysqli_stmt_execute($stmt1);
            $result1 = mysqli_stmt_get_result($stmt1);
            $myrow1=mysqli_fetch_array($result1);
            mysqli_stmt_close($stmt1);
            $tajuk5=$myrow1["38title"] ?? '';
        ?>

        <div class="card mb-3">
            <div class="card-header">
                <strong>Record Copies Management: <?php echo $tajuk5;?></strong>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-primary btn-sm" onclick="return openAppModal('copies_add.php?bahan_id=<?php echo $get_id;?>', 'Add New Copy', 'modal-md')">+ Add New Copies</button>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="document.location.reload(true)">Refresh</button>
                </div>
            </div>
            <div class="card-body p-0">
                <table class="table-modern m-0">
                <thead>
                    <tr>
                        <th style="width:20%;">Accession Number</th>
                        <th class="text-center" style="width:15%;">Status</th>
                        <th>Details, Invoicing &amp; History</th>
                        <th class="text-center" style="width:15%;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                    $stmtC = mysqli_prepare($GLOBALS["conn"], "SELECT * FROM eg_item_copies WHERE eg_item_id=? ORDER BY id ASC");
                    mysqli_stmt_bind_param($stmtC, "i", $get_id_int);
                    mysqli_stmt_execute($stmtC);
                    $resultC = mysqli_stmt_get_result($stmtC);
                    $has_copies = false;
                    while ($myrowC=mysqli_fetch_array($resultC)) {
                        $has_copies = true;
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

                        $has_enumeration = !empty($copies_volume) || !empty($copies_issue) || !empty($copies_year);
                        $enum_parts = [];
                        if (!empty($copies_volume)) $enum_parts[] = $copies_volume;
                        if (!empty($copies_issue)) $enum_parts[] = $copies_issue;
                        if (!empty($copies_year)) $enum_parts[] = '(' . $copies_year . ')';
                        $enum_text = implode(' ', $enum_parts);

                        echo "<tr class='table-row'>";
                        echo "<td>";
                            echo "<strong class='text-primary'>$copies_accession_number</strong>";
                            if ($has_enumeration) {
                                echo "<div class='mt-1'><span class='badge badge-secondary font-monospace'><i class='fa-solid fa-layer-group me-1'></i>" . htmlspecialchars($enum_text, ENT_QUOTES, 'UTF-8') . "</span></div>";
                            }
                        echo "</td>";
                        echo "<td class='text-center'>";
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
                            if ($copies_is_reference === 'YES') {
                                echo "<br/><small class='text-danger'>[Non-circulating]</small>";
                            }
                        }
                        echo "</td>";
                        echo "<td>";
                            echo "<div class='text-muted'><small>Price: <strong>$currency_SHORT " . htmlspecialchars($copies_invoice_a, ENT_QUOTES, 'UTF-8') . "</strong> | Supplier: " . htmlspecialchars($copies_invoice_b, ENT_QUOTES, 'UTF-8') . " | Invoice: " . htmlspecialchars($copies_invoice_c, ENT_QUOTES, 'UTF-8') . "</small></div>";
                            echo "<div class='text-muted'><small>Added: ".date('D, Y-m-d h:i:s a', $copies_addedon)." | Modified: ".date('D, Y-m-d h:i:s a', $copies_lastchange)."</small></div>";
                        echo "</td>";
                        echo "<td class='text-center'>";
                            echo "<div class='d-flex justify-content-center gap-1'>";
                            echo "<button type='button' class='btn btn-secondary btn-sm' onclick=\"return openAppModal('change_status.php?cid=$copies_id', 'Edit Copy', 'modal-md');\"><i class='fa-solid fa-pen me-1'></i> Edit Copy</button>";
                            if ($copies_status != 'CIRCULATED' && (($_SESSION['editmode'] ?? '') == 'SUPER')) {
                                echo "<button type='button' class='btn btn-danger btn-sm' onclick=\"return openAppModal('copies_delete.php?id=$copies_id', 'Delete Copy Item', 'modal-sm');\"><i class='fa-solid fa-trash me-1'></i> Delete</button>";
                            }
                            echo "</div>";
                        echo "</td>";
                        echo "</tr>";
                    }
                    mysqli_stmt_close($stmtC);
                    if (!$has_copies) {
                        echo "<tr><td colspan='4' class='text-center text-muted p-3'>No physical copies registered yet for this record. Click '+ Add New Copies' above.</td></tr>";
                    }
                ?>
                </tbody>
                </table>
            </div>
        </div>
        
        <div class="text-center my-3 d-flex justify-content-center gap-2 flex-wrap">
            <?php
                $item_type_rc = (string)($myrow1["39type"] ?? '');
                $is_rc_serial = ($item_type_rc === '4' || strcasecmp($item_type_rc, 'Serial') === 0 || strcasecmp(idToType($item_type_rc), 'Serial') === 0 || ($_GET['delr'] ?? '') === 'serials');
                if ($is_rc_serial):
            ?>
                <a class="btn btn-primary btn-sm" href="serials.php"><i class="fa-solid fa-newspaper me-1"></i> Back to Serial Management</a>
                <a class="btn btn-secondary btn-sm" href="reg.php?upd=<?php echo (int)$get_id_int; ?>&delr=serials">&larr; Edit Serial Details</a>
            <?php else: ?>
                <a class="btn btn-secondary btn-sm" href="reg.php">&larr; Back to Insert New Record</a>
            <?php endif; ?>
        </div>
        
        <?php include_once '../includes/footerbar.php';?>
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
</body>
</html>
