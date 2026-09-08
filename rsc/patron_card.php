<?php
    session_start();
    define('includeExist', true);

    // Authentication check
    if (!isset($_SESSION['username_myacc']) && !isset($_SESSION['username'])) {
        header('Location: index.php?modal=patron');
        exit;
    }

    include_once 'config.php';
    include_once 'includes/functions.php';

    // Determine target patron username
    $target_user = '';
    if (isset($_SESSION['username']) && (isset($_GET['u']) || isset($_GET['pid']))) {
        if (!empty($_GET['u'])) {
            $target_user = trim($_GET['u']);
        } elseif (isset($_GET['pid']) && is_numeric($_GET['pid'])) {
            $pid = (int)$_GET['pid'];
            $stmt = mysqli_prepare($GLOBALS["conn"], "SELECT username FROM eg_auth WHERE id=?");
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "i", $pid);
                mysqli_stmt_execute($stmt);
                $res = mysqli_stmt_get_result($stmt);
                if ($res && $r = mysqli_fetch_assoc($res)) {
                    $target_user = $r['username'];
                }
                mysqli_stmt_close($stmt);
            }
        }
    }

    if (empty($target_user)) {
        $target_user = $_SESSION['username_myacc'] ?? ($_SESSION['username'] ?? '');
    }

    // Fetch user details from eg_auth
    $user_row = null;
    $stmt = mysqli_prepare($GLOBALS["conn"], "SELECT id, username, name, division, allowed, lastlogin FROM eg_auth WHERE username=?");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $target_user);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        if ($res && $r = mysqli_fetch_assoc($res)) {
            $user_row = $r;
        }
        mysqli_stmt_close($stmt);
    }

    if (!$user_row) {
        die("<!DOCTYPE HTML><html lang='en'><head><link href='./assets/styles/style.css' rel='stylesheet'></head><body><div class='auth-card'><span class='badge badge-danger mb-2'>ERROR</span><h2>Error: Patron account not found.</h2><a class='btn btn-secondary mt-3' href='logged.php'>&larr; Back to My Account</a></div></body></html>");
    }

    $patron_id = $user_row['username'] ?? $target_user;
    $patron_name = $user_row['name'] ?? $target_user;
    $patron_dept = $user_row['division'] ?? '-';
    $patron_role = $user_row['allowed'] ?? 'STANDARD';
    $patron_num_id = $user_row['id'] ?? 0;
    $patron_avatar = getPatronAvatarPath($patron_id);

    $is_myacc = isset($_SESSION['username_myacc']) && ($_SESSION['username_myacc'] === $target_user);
    $back_url = $is_myacc ? 'logged.php' : 'admin/chanuser.php';
    $back_label = $is_myacc ? 'Back to My Account' : 'Back to Users List';
    $username_js = addslashes($patron_id);
?>
<!DOCTYPE HTML>
<html lang='en'>

<head>
    <meta charset="utf-8">
    <title><?php echo htmlspecialchars($product_name, ENT_QUOTES, 'UTF-8');?> : Digital Patron Card</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="<?php echo htmlspecialchars($mini_icon_path, ENT_QUOTES, 'UTF-8');?>" rel="icon" type="image/png" />
    <link href="./assets/styles/style.css?v=4" rel="stylesheet" type="text/css">
    <link href="./assets/styles/card_style.css" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="./assets/fontawesome/css/all.min.css">
    <script src="./assets/jscripts/jquery_1_11_1.min.js" type="text/javascript"></script>
    <script src="./assets/jscripts/qrcode.js" type="text/javascript"></script>
    <script src="./assets/jscripts/jquery.qrcode.js" type="text/javascript"></script>
    <script src="./assets/jscripts/jquery-barcode.min.js" type="text/javascript"></script>
</head>

<body>
    <?php include_once './includes/loggedinfo.php'; ?>

    <div class="app-container">
        <!-- Action Toolbar -->
        <div class="card mb-3 no-print">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2">
                    <i class="fa-solid fa-id-card-clip text-primary" style="font-size: 1.25rem;"></i>
                    <strong>Digital Patron Library Card</strong>
                    <span class="badge badge-info">ID: <?php echo htmlspecialchars($patron_id, ENT_QUOTES, 'UTF-8'); ?></span>
                </div>
                <div class="d-flex gap-2 flex-wrap card-actions-bar">
                    <button type="button" class="btn btn-secondary btn-sm" onclick="toggleCardFlip();" title="Flip card front and back">
                        <i class="fa-solid fa-rotate me-1"></i> Flip Card (Front / Back)
                    </button>
                    <button type="button" class="btn btn-secondary btn-sm" id="btnToggleView" onclick="toggleDualView();" title="Toggle side-by-side view">
                        <i class="fa-solid fa-table-columns me-1"></i> <span id="viewModeText">Show Both Sides</span>
                    </button>
                    <button type="button" class="btn btn-primary btn-sm" onclick="window.print();" title="Print this library card (Ctrl+P / Cmd+P)">
                        <i class="fa-solid fa-print me-1"></i> Print Card
                    </button>
                </div>
            </div>
            <div class="card-body bg-light text-center py-2">
                <small class="text-muted">
                    <i class="fa-solid fa-hand-pointer me-1"></i> Click anywhere on the card below or press the <strong>Flip Card</strong> button to switch between Front &amp; Back.
                </small>
            </div>
        </div>

        <!-- 1. Interactive 3D Flip Card Scene -->
        <div class="card-stage no-print" id="flipSceneWrapper">
            <div class="flip-scene" onclick="toggleCardFlip();" title="Click to flip card">
                <div class="digital-card" id="digitalCard">
                    <!-- FRONT FACE -->
                    <div class="card-face card-face-front">
                        <div class="card-header-brand">
                            <div>
                                <div class="brand-title"><?php echo htmlspecialchars($licensed_info ?: $product_name, ENT_QUOTES, 'UTF-8'); ?></div>
                                <div class="brand-sub">Official Library Membership Pass</div>
                            </div>
                            <div>
                                <span class="card-badge-type"><?php echo htmlspecialchars($patron_role, ENT_QUOTES, 'UTF-8'); ?></span>
                            </div>
                        </div>

                        <div class="card-front-body">
                            <div class="card-avatar-box">
                                <?php if ($patron_avatar): ?>
                                    <img class="card-avatar-img" src="<?php echo htmlspecialchars($patron_avatar, ENT_QUOTES, 'UTF-8'); ?>" alt="Patron Photo">
                                <?php else: ?>
                                    <i class="fa-solid fa-user text-muted" style="font-size: 2.25rem;"></i>
                                <?php endif; ?>
                            </div>
                            <div class="card-info-box">
                                <div class="card-patron-name" title="<?php echo htmlspecialchars($patron_name, ENT_QUOTES, 'UTF-8'); ?>">
                                    <?php echo htmlspecialchars($patron_name, ENT_QUOTES, 'UTF-8'); ?>
                                </div>
                                <div class="card-patron-id-large" title="Member ID: <?php echo htmlspecialchars($patron_id, ENT_QUOTES, 'UTF-8'); ?>">
                                    <?php echo htmlspecialchars($patron_id, ENT_QUOTES, 'UTF-8'); ?>
                                </div>
                                <div class="card-patron-dept">
                                    <i class="fa-solid fa-building me-1 text-muted"></i><?php echo htmlspecialchars($patron_dept, ENT_QUOTES, 'UTF-8'); ?>
                                </div>
                            </div>
                        </div>

                        <div class="card-barcode-wrap" title="Barcode ID: <?php echo htmlspecialchars($patron_id, ENT_QUOTES, 'UTF-8'); ?>">
                            <div id="frontBarcodeContainer"></div>
                        </div>
                    </div>

                    <!-- BACK FACE -->
                    <div class="card-face card-face-back">
                        <div class="card-magstripe"></div>
                        <div class="card-back-content">
                            <div class="card-qr-box" title="Scan QR Code for ID: <?php echo htmlspecialchars($patron_id, ENT_QUOTES, 'UTF-8'); ?>">
                                <div id="backQrcodeContainer"></div>
                            </div>
                            <div class="card-back-text">
                                <div class="card-back-id-display"><?php echo htmlspecialchars($patron_id, ENT_QUOTES, 'UTF-8'); ?></div>
                                <p class="mb-1"><strong>Library Digital Identity:</strong></p>
                                <p class="mb-1" style="font-size: 0.68rem; color: #94a3b8;">
                                    This card is personal &amp; non-transferable. Present QR code or barcode at checkout terminals and circulation counters for borrowing.
                                </p>
                                <p class="mb-0" style="font-size: 0.65rem; color: #64748b;">
                                    Powered by <?php echo htmlspecialchars($product_name, ENT_QUOTES, 'UTF-8'); ?>
                                </p>
                            </div>
                        </div>
                        <div class="card-back-footer">
                            <span><i class="fa-solid fa-shield-halved me-1"></i> Authorized Library Pass</span>
                            <span>System Record #<?php echo (int)$patron_num_id; ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Dual Side-by-Side View (Active on Print and Dual View toggle) -->
        <div class="dual-view-container" id="dualViewContainer">
            <!-- Front Clone for Dual/Print -->
            <div class="digital-card">
                <div class="card-face card-face-front" style="position: relative;">
                    <div class="card-header-brand">
                        <div>
                            <div class="brand-title"><?php echo htmlspecialchars($licensed_info ?: $product_name, ENT_QUOTES, 'UTF-8'); ?></div>
                            <div class="brand-sub">Official Library Membership Pass</div>
                        </div>
                        <div>
                            <span class="card-badge-type"><?php echo htmlspecialchars($patron_role, ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                    </div>

                    <div class="card-front-body">
                        <div class="card-avatar-box">
                            <?php if ($patron_avatar): ?>
                                <img class="card-avatar-img" src="<?php echo htmlspecialchars($patron_avatar, ENT_QUOTES, 'UTF-8'); ?>" alt="Patron Photo">
                            <?php else: ?>
                                <i class="fa-solid fa-user text-muted" style="font-size: 2.25rem;"></i>
                            <?php endif; ?>
                        </div>
                        <div class="card-info-box">
                            <div class="card-patron-name">
                                <?php echo htmlspecialchars($patron_name, ENT_QUOTES, 'UTF-8'); ?>
                            </div>
                            <div class="card-patron-id-large">
                                <?php echo htmlspecialchars($patron_id, ENT_QUOTES, 'UTF-8'); ?>
                            </div>
                            <div class="card-patron-dept">
                                <i class="fa-solid fa-building me-1 text-muted"></i><?php echo htmlspecialchars($patron_dept, ENT_QUOTES, 'UTF-8'); ?>
                            </div>
                        </div>
                    </div>

                    <div class="card-barcode-wrap">
                        <div id="dualFrontBarcodeContainer"></div>
                    </div>
                </div>
            </div>

            <!-- Back Clone for Dual/Print -->
            <div class="digital-card">
                <div class="card-face card-face-back" style="position: relative;">
                    <div class="card-magstripe"></div>
                    <div class="card-back-content">
                        <div class="card-qr-box">
                            <div id="dualBackQrcodeContainer"></div>
                        </div>
                        <div class="card-back-text">
                            <div class="card-back-id-display"><?php echo htmlspecialchars($patron_id, ENT_QUOTES, 'UTF-8'); ?></div>
                            <p class="mb-1"><strong>Library Digital Identity:</strong></p>
                            <p class="mb-1" style="font-size: 0.68rem; color: #94a3b8;">
                                This card is personal &amp; non-transferable. Present QR code or barcode at checkout terminals and circulation counters for borrowing.
                            </p>
                            <p class="mb-0" style="font-size: 0.65rem; color: #64748b;">
                                Powered by <?php echo htmlspecialchars($product_name, ENT_QUOTES, 'UTF-8'); ?>
                            </p>
                        </div>
                    </div>
                    <div class="card-back-footer">
                        <span><i class="fa-solid fa-shield-halved me-1"></i> Authorized Library Pass</span>
                        <span>System Record #<?php echo (int)$patron_num_id; ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="print-cut-guide" style="display: none;">
            --- Cut along dotted lines &bull; Standard CR80 ID Card dimensions ---
        </div>

        <div class="text-center my-3 no-print">
            <a class="btn btn-secondary btn-sm" href="<?php echo htmlspecialchars($back_url, ENT_QUOTES, 'UTF-8'); ?>">&larr; <?php echo htmlspecialchars($back_label, ENT_QUOTES, 'UTF-8'); ?></a>
        </div>

        <?php include_once './includes/footerbar.php'; ?>
    </div>

    <script>
        // Render Barcode & QR Code
        $(function() {
            var idText = "<?php echo $username_js; ?>";

            // 1. Front Barcode (Code128 SVG)
            $("#frontBarcodeContainer, #dualFrontBarcodeContainer").barcode(idText, "code128", {
                barWidth: 1.5,
                barHeight: 34,
                fontSize: 10,
                showHRI: false,
                output: "svg"
            });

            // 2. Back QR Code
            $("#backQrcodeContainer, #dualBackQrcodeContainer").qrcode({
                text: idText,
                width: 92,
                height: 92
            });
        });

        // 3D Card Flip Animation
        function toggleCardFlip() {
            var card = document.getElementById('digitalCard');
            if (card) {
                card.classList.toggle('is-flipped');
            }
        }

        // Toggle Dual Side-by-Side vs 3D Flip
        function toggleDualView() {
            var flipScene = document.getElementById('flipSceneWrapper');
            var dualView = document.getElementById('dualViewContainer');
            var modeText = document.getElementById('viewModeText');

            if (dualView.style.display === 'flex') {
                dualView.style.display = 'none';
                flipScene.style.display = 'flex';
                modeText.textContent = 'Show Both Sides';
            } else {
                dualView.style.display = 'flex';
                flipScene.style.display = 'none';
                modeText.textContent = 'Show 3D Flip Card';
            }
        }

        // Keyboard navigation (Space or F to flip, P to print, Escape to return)
        document.addEventListener('keydown', function(e) {
            if (e.target && (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA')) {
                return;
            }
            if (e.key === ' ' || e.key === 'f' || e.key === 'F') {
                e.preventDefault();
                toggleCardFlip();
            }
        });
    </script>
</body>
</html>
