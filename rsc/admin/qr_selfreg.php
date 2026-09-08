<?php
    session_start();
    define('includeExist', true);
    include_once '../includes/access_super.php';
    include_once '../config.php';
    include_once '../includes/selfreg_helper.php';

    // Handle generating a new QR token explicitly or retrieving the current active one
    $force_new = isset($_GET['action']) && $_GET['action'] === 'new';
    $active_token = '';
    $token_expires_at = 0;
    $token_ttl = 1800; // 30 minutes

    if (!$force_new && !empty($_SESSION['active_selfreg_token'])) {
        $check = selfreg_validate_token($GLOBALS["conn"], $_SESSION['active_selfreg_token']);
        if ($check['valid'] && !empty($check['data'])) {
            $active_token = $check['data']['token'];
            $token_expires_at = (int)$check['data']['expires_at'];
        }
    }

    if (empty($active_token) || $force_new) {
        $gen = selfreg_generate_token($GLOBALS["conn"], $token_ttl);
        if ($gen['success']) {
            $active_token = $gen['token'];
            $token_expires_at = $gen['expires_at'];
            $_SESSION['active_selfreg_token'] = $active_token;
        } else {
            $error_msg = $gen['error'] ?? 'Unable to generate registration token';
        }
    }

    $time_remaining = max(0, $token_expires_at - time());

    // Resolve best external registration URL (auto-substitutes localhost with detected LAN/Wi-Fi IP)
    $url_info = selfreg_get_best_registration_url($active_token);
    $registration_url = $url_info['url'];
    $detected_ips = $url_info['detected_ips'];
    $is_lan = $url_info['is_lan'];
    $chosen_host = $url_info['host'];
    $protocol = $url_info['protocol'];
    $port_suffix = $url_info['port_suffix'];
    $script_dir = $url_info['script_dir'];
    $current_http_host = $url_info['current_http_host'];
?>
<!DOCTYPE HTML>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title><?php echo htmlspecialchars($product_name, ENT_QUOTES, 'UTF-8');?> : Patron Self-Registration QR</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="../<?php echo htmlspecialchars($mini_icon_path, ENT_QUOTES, 'UTF-8');?>" rel="icon" type="image/png" />
    <link href="../assets/styles/style.css" rel="stylesheet" type="text/css">
    <script src="../assets/jscripts/jquery_1_11_1.min.js" type="text/javascript"></script>
    <script src="../assets/jscripts/qrcode.js" type="text/javascript"></script>
    <script src="../assets/jscripts/jquery.qrcode.js" type="text/javascript"></script>
    <style>
        .qr-kiosk-wrapper {
            max-width: 700px;
            margin: 1.5rem auto 3rem auto;
            text-align: center;
        }
        .qr-card {
            background: #ffffff;
            border-radius: var(--radius-lg, 12px);
            border: 1px solid var(--border-color, #e2e8f0);
            box-shadow: var(--shadow-lg, 0 10px 15px -3px rgba(0,0,0,0.08));
            padding: 2.25rem 2rem;
            position: relative;
            overflow: hidden;
        }
        .qr-card-header {
            margin-bottom: 1.25rem;
        }
        .qr-display-box {
            display: inline-flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: #ffffff;
            padding: 1.25rem;
            border-radius: var(--radius-md, 8px);
            border: 2px dashed #cbd5e1;
            margin: 0.75rem auto 1.25rem auto;
            min-height: 250px;
            position: relative;
        }
        .qr-display-box canvas {
            display: block;
            margin: 0 auto;
            max-width: 100%;
            height: auto;
        }
        .timer-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: #eff6ff;
            color: #1d4ed8;
            font-weight: 700;
            font-size: 1.15rem;
            padding: 0.4rem 1.15rem;
            border-radius: var(--radius-full, 9999px);
            border: 1px solid #bfdbfe;
            margin-bottom: 0.75rem;
            font-variant-numeric: tabular-nums;
        }
        .timer-badge.warning {
            background: #fffbeb;
            color: #b45309;
            border-color: #fde68a;
        }
        .timer-badge.danger {
            background: #fef2f2;
            color: #dc2626;
            border-color: #fecaca;
        }
        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            font-size: 0.85rem;
            font-weight: 600;
            padding: 0.35rem 0.85rem;
            border-radius: 9999px;
            margin-bottom: 0.75rem;
        }
        .status-pill.active {
            background: #ecfdf5;
            color: #047857;
            border: 1px solid #a7f3d0;
        }
        .status-pill.scanned {
            background: #fffbeb;
            color: #b45309;
            border: 1px solid #fde68a;
        }
        .status-pill.used {
            background: #eff6ff;
            color: #1d4ed8;
            border: 1px solid #bfdbfe;
        }
        .status-pill.expired {
            background: #fef2f2;
            color: #b91c1c;
            border: 1px solid #fecaca;
        }
        .qr-expired-overlay {
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(255, 255, 255, 0.93);
            backdrop-filter: blur(4px);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            z-index: 10;
        }
        .net-config-box {
            background: #ffffff;
            border: 1px solid var(--border-color, #e2e8f0);
            border-radius: var(--radius-lg, 12px);
            box-shadow: var(--shadow-sm, 0 1px 3px rgba(0,0,0,0.05));
            padding: 1rem 1.25rem;
            margin-bottom: 1.25rem;
            text-align: left;
            font-size: 0.85rem;
        }
        .url-copy-box {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: var(--radius-md, 8px);
            padding: 0.5rem 0.75rem;
            margin-top: 1rem;
            font-size: 0.85rem;
        }
        .url-copy-box input {
            flex: 1;
            border: none;
            background: transparent;
            font-family: monospace;
            font-size: 0.85rem;
            color: #334155;
            outline: none;
        }
        .toast-notify {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            background: #1e293b;
            color: #ffffff;
            padding: 0.75rem 1.25rem;
            border-radius: 8px;
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.2);
            font-size: 0.9rem;
            display: none;
            z-index: 1000;
        }
        @media print {
            .no-print { display: none !important; }
            body { background: #ffffff !important; }
            .qr-kiosk-wrapper { margin: 0 auto; max-width: 100%; }
            .qr-card { border: none; box-shadow: none; padding: 1rem; }
            .qr-display-box { border: 2px solid #000000; padding: 1.5rem; }
        }
    </style>
</head>
<body>
    <?php include_once '../includes/loggedinfo.php';?>

    <div class="app-container">
        <div class="qr-kiosk-wrapper">
        <?php if (empty($error_msg)): ?>
            <!-- Network IP / Wi-Fi Host Selector (Separate card above the main QR card) -->
            <div class="net-config-box no-print">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <strong class="text-dark">
                        <i class="fa-solid fa-wifi text-primary me-1"></i> Network Host / Wi-Fi IP
                    </strong>
                    <span id="netModeBadge" class="badge <?php echo $is_lan ? 'badge-success' : 'badge-warning'; ?>">
                        <?php echo $is_lan ? 'LAN / Wi-Fi Active' : 'Localhost Only'; ?>
                    </span>
                </div>
                <div class="d-flex gap-2 align-items-center flex-wrap">
                    <select id="hostSelect" class="form-select form-select-sm" style="flex: 1; min-width: 220px; padding: 4px 8px; font-size: 0.85rem; border-radius: 4px; border: 1px solid #cbd5e1;" onchange="onHostSelectChange()">
                        <?php if (!empty($detected_ips)): ?>
                            <?php foreach ($detected_ips as $idx => $dip): 
                                $opt_val = $dip . $port_suffix;
                            ?>
                                <option value="<?php echo htmlspecialchars($opt_val, ENT_QUOTES, 'UTF-8'); ?>" <?php if ($idx === 0) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($opt_val, ENT_QUOTES, 'UTF-8'); ?> (LAN / Wi-Fi IPv4 - Auto Detected)
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <?php 
                            $is_loopback = (strpos($current_http_host, 'localhost') !== false || strpos($current_http_host, '127.0.0.1') !== false || strpos($current_http_host, '::1') !== false);
                            if (!$is_loopback && !in_array($current_http_host, array_map(fn($x) => $x . $port_suffix, $detected_ips), true)): 
                        ?>
                            <option value="<?php echo htmlspecialchars($current_http_host, ENT_QUOTES, 'UTF-8'); ?>">
                                <?php echo htmlspecialchars($current_http_host, ENT_QUOTES, 'UTF-8'); ?> (Domain / Public Host)
                            </option>
                        <?php endif; ?>
                        <option value="__custom__" <?php if (empty($detected_ips)) echo 'selected'; ?>>Custom IP / Hostname / Domain...</option>
                    </select>
                    <div id="customHostWrap" style="display: none; flex: 1; min-width: 180px;">
                        <input type="text" id="customHostInput" placeholder="e.g. 192.168.1.50 or library.local" style="width: 100%; padding: 4px 8px; font-size: 0.85rem; border-radius: 4px; border: 1px solid #cbd5e1;" oninput="onCustomHostInput()" />
                    </div>
                </div>
                <div class="text-muted small mt-1" style="font-size: 0.78rem;">
                    <i class="fa-solid fa-circle-info me-1"></i> Make sure the patron's smartphone is connected to the same Wi-Fi network or local switch.
                </div>
            </div>
        <?php endif; ?>

        <div class="qr-card">
            <div class="qr-card-header">
                <div style="font-size: 2.25rem; color: var(--primary, #2563eb); margin-bottom: 0.5rem;">
                    <i class="fa-solid fa-qrcode"></i>
                </div>
                <h2>Patron Self-Registration QR</h2>
                <p class="text-muted" style="max-width: 520px; margin: 0 auto; font-size: 0.9rem;">
                    Show this QR code to the walk-in patron. They can scan it with their smartphone camera to register their patron account.
                </p>
            </div>

            <?php if (!empty($error_msg)): ?>
                <div class="alert alert-danger mb-3">
                    <i class="fa-solid fa-triangle-exclamation me-1"></i> <?php echo htmlspecialchars($error_msg, ENT_QUOTES, 'UTF-8');?>
                </div>
            <?php else: ?>
                <!-- Expiry Timer & Real-time Status Pill -->
                <div class="no-print mb-1 d-flex flex-column align-items-center">
                    <div id="timerBox" class="timer-badge">
                        <i class="fa-regular fa-clock"></i>
                        <span>Expires in <span id="timerDisplay">--:--</span></span>
                    </div>
                    <div id="statusBadge" class="status-pill active">
                        <span id="statusText"><i class="fa-solid fa-qrcode me-1"></i> Ready for patron scan</span>
                    </div>
                </div>

                <!-- QR Code Display Box -->
                <div class="qr-display-box" id="qrBox">
                    <div id="qrcodeCanvas"></div>
                    <div style="margin-top: 10px; font-weight: 600; font-size: 0.85rem; color: #475569;">
                        Scan with your Mobile Camera
                    </div>

                    <!-- Overlay for expired/used token -->
                    <div id="expiredOverlay" class="qr-expired-overlay" style="display: none;">
                        <div id="overlayIcon" style="font-size: 2.5rem; margin-bottom: 0.5rem; color: #dc2626;">
                            <i class="fa-solid fa-circle-xmark"></i>
                        </div>
                        <h4 id="overlayTitle">QR Code Expired</h4>
                        <p class="text-muted small" id="overlayDesc">This single-use registration QR code has expired.</p>
                        <a href="qr_selfreg.php?action=new" class="btn btn-primary btn-sm mt-2">
                            <i class="fa-solid fa-arrows-rotate me-1"></i> Generate Next QR Code
                        </a>
                    </div>
                </div>

                <!-- Registration Details Message (Hidden initially) -->
                <div id="regSuccessAlert" class="alert alert-success mt-3" style="display: none; text-align: left;">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="fa-solid fa-circle-check fa-lg"></i>
                        <strong>Registration Completed Successfully!</strong>
                    </div>
                    <div id="regSuccessDetails" class="small"></div>
                    <div class="mt-3 text-center">
                        <a href="qr_selfreg.php?action=new" class="btn btn-success btn-sm">
                            <i class="fa-solid fa-user-plus me-1"></i> Generate Next Patron QR Code
                        </a>
                        <a href="chanuser.php" class="btn btn-secondary btn-sm ms-2">
                            <i class="fa-solid fa-users me-1"></i> View User Accounts
                        </a>
                    </div>
                </div>

                <!-- Direct URL with 1-click Copy -->
                <div class="url-copy-box no-print">
                    <i class="fa-solid fa-link text-muted"></i>
                    <input type="text" id="regUrlInput" value="<?php echo htmlspecialchars($registration_url, ENT_QUOTES, 'UTF-8');?>" readonly />
                    <button type="button" class="btn btn-secondary btn-sm" onclick="copyRegUrl()" title="Copy direct registration link">
                        <i class="fa-regular fa-copy"></i> Copy
                    </button>
                </div>

                <!-- Counter Staff Action Buttons -->
                <div class="d-flex gap-2 justify-content-center mt-4 no-print flex-wrap">
                    <a href="qr_selfreg.php?action=new" class="btn btn-primary">
                        <i class="fa-solid fa-arrows-rotate me-1"></i> Generate New QR
                    </a>
                    <a href="chanuser.php" class="btn btn-secondary">
                        <i class="fa-solid fa-arrow-left me-1"></i> Back to Users
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php include_once '../includes/footerbar.php';?>
</div>

    <!-- Toast Notification -->
    <div id="toastNotify" class="toast-notify">
        <i class="fa-solid fa-check me-2"></i> Registration link copied to clipboard!
    </div>

    <?php if (!empty($active_token)): ?>
    <script type="text/javascript">
        var activeToken = "<?php echo addslashes($active_token);?>";
        var protocol = "<?php echo addslashes($protocol);?>";
        var scriptDir = "<?php echo addslashes($script_dir);?>";
        var currentHost = "<?php echo addslashes($chosen_host);?>";
        var timeRemaining = <?php echo (int)$time_remaining;?>;
        var pollInterval = null;
        var timerInterval = null;
        var isUsed = false;

        function buildRegistrationUrl(host) {
            return protocol + host + scriptDir + "/adduser.php?token=" + encodeURIComponent(activeToken);
        }

        function renderQrCode(url) {
            $("#qrcodeCanvas").empty().qrcode({
                text: url,
                width: 220,
                height: 220
            });
            $("#qrcodeCanvas canvas").css({
                "margin": "0 auto",
                "display": "block",
                "border-radius": "6px"
            });
            $("#regUrlInput").val(url);
        }

        function onHostSelectChange() {
            var sel = document.getElementById("hostSelect");
            var val = sel.value;
            var customWrap = document.getElementById("customHostWrap");

            if (val === "__custom__") {
                customWrap.style.display = "block";
                var cInput = document.getElementById("customHostInput");
                if (cInput.value.trim() !== "") {
                    currentHost = cInput.value.trim();
                }
            } else {
                customWrap.style.display = "none";
                currentHost = val;
            }

            try {
                localStorage.setItem("counter_qr_host", currentHost);
                localStorage.setItem("counter_qr_select_val", val);
            } catch (e) {}

            updateNetworkBadge();
            renderQrCode(buildRegistrationUrl(currentHost));
        }

        function onCustomHostInput() {
            var cInput = document.getElementById("customHostInput");
            var val = cInput.value.trim();
            if (val !== "") {
                currentHost = val;
                try {
                    localStorage.setItem("counter_qr_host", currentHost);
                } catch (e) {}
                updateNetworkBadge();
                renderQrCode(buildRegistrationUrl(currentHost));
            }
        }

        function updateNetworkBadge() {
            var badge = $("#netModeBadge");
            if (currentHost.indexOf("localhost") !== -1 || currentHost.indexOf("127.0.0.1") !== -1) {
                badge.attr("class", "badge badge-warning").text("Localhost Only (Not Reachable on Wi-Fi)");
            } else {
                badge.attr("class", "badge badge-success").text("LAN / Wi-Fi Reachable");
            }
        }

        // Initialize on page load
        $(function() {
            // Check stored preference
            try {
                var savedHost = localStorage.getItem("counter_qr_host");
                var savedSel = localStorage.getItem("counter_qr_select_val");
                if (savedHost && savedHost.indexOf("localhost") === -1 && savedHost.indexOf("127.0.0.1") === -1) {
                    var sel = document.getElementById("hostSelect");
                    if (savedSel === "__custom__") {
                        sel.value = "__custom__";
                        document.getElementById("customHostWrap").style.display = "block";
                        document.getElementById("customHostInput").value = savedHost;
                        currentHost = savedHost;
                    } else if (savedSel && $("#hostSelect option[value='" + savedSel + "']").length > 0) {
                        sel.value = savedSel;
                        currentHost = savedSel;
                    }
                }
            } catch (e) {}

            updateNetworkBadge();
            renderQrCode(buildRegistrationUrl(currentHost));

            // Start countdown timer
            updateTimerDisplay();
            timerInterval = setInterval(function() {
                if (timeRemaining > 0) {
                    timeRemaining--;
                    updateTimerDisplay();
                } else {
                    clearInterval(timerInterval);
                    handleTokenExpired();
                }
            }, 1000);

            // Start real-time polling
            pollInterval = setInterval(pollTokenStatus, 3000);
        });

        function updateTimerDisplay() {
            var mins = Math.floor(timeRemaining / 60);
            var secs = timeRemaining % 60;
            var formatted = (mins < 10 ? "0" : "") + mins + ":" + (secs < 10 ? "0" : "") + secs;
            $("#timerDisplay").text(formatted);

            var timerBox = $("#timerBox");
            if (timeRemaining <= 180) { // 3 min
                timerBox.removeClass("warning").addClass("danger");
            } else if (timeRemaining <= 600) { // 10 min
                timerBox.addClass("warning").removeClass("danger");
            }
        }

        function pollTokenStatus() {
            if (isUsed || timeRemaining <= 0) return;

            $.ajax({
                url: "api/check-token-status.php",
                type: "GET",
                data: { token: activeToken },
                dataType: "json",
                success: function(res) {
                    if (!res) return;

                    if (res.status === 'scanned') {
                        $("#statusBadge").attr("class", "status-pill scanned");
                        $("#statusText").html("<i class='fa-solid fa-spinner fa-spin me-1'></i> Patron is currently filling the registration form...");
                    } else if (res.status === 'used') {
                        isUsed = true;
                        clearInterval(timerInterval);
                        clearInterval(pollInterval);

                        $("#statusBadge").attr("class", "status-pill used");
                        $("#statusText").html("<i class='fa-solid fa-circle-check me-1'></i> Registration Completed!");

                        var patronInfo = "<strong>Patron Name:</strong> " + escapeHtml(res.registered_name || 'N/A') + "<br/>" +
                                         "<strong>IC / Username:</strong> <code>" + escapeHtml(res.registered_username || 'N/A') + "</code><br/>" +
                                         "<strong>Role:</strong> <span class='badge badge-primary'>PATRON</span>";
                        $("#regSuccessDetails").html(patronInfo);
                        $("#regSuccessAlert").slideDown();

                        $("#overlayIcon").html("<i class='fa-solid fa-circle-check' style='color:#10b981;'></i>");
                        $("#overlayTitle").text("Registration Complete");
                        $("#overlayDesc").text("Patron " + (res.registered_name || '') + " registered successfully.");
                        $("#expiredOverlay").show();
                    } else if (res.status === 'expired' || res.is_valid === false) {
                        handleTokenExpired();
                    }
                }
            });
        }

        function handleTokenExpired() {
            clearInterval(pollInterval);
            $("#statusBadge").attr("class", "status-pill expired");
            $("#statusText").html("<i class='fa-solid fa-ban me-1'></i> QR Code Expired");
            $("#timerDisplay").text("00:00");
            $("#expiredOverlay").show();
        }

        function copyRegUrl() {
            var input = document.getElementById("regUrlInput");
            input.select();
            input.setSelectionRange(0, 99999);
            navigator.clipboard.writeText(input.value).then(function() {
                var toast = $("#toastNotify");
                toast.fadeIn(200).delay(2500).fadeOut(300);
            }).catch(function() {
                document.execCommand("copy");
                var toast = $("#toastNotify");
                toast.fadeIn(200).delay(2500).fadeOut(300);
            });
        }

        function escapeHtml(str) {
            return $("<div>").text(str).html();
        }
    </script>
    <?php endif; ?>
</body>
</html>
