<?php
    session_start();define('includeExist', true);
    include_once '../includes/access_super.php';
    include_once '../config.php';
    include_once '../includes/functions.php';
    include_once '../includes/ip_guard.php';
    include_once '../includes/token_validate.php';

    // Handle Actions
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $proceedAfterToken) {
        $action = $_POST['action'];
        $target_ip = trim($_POST['target_ip'] ?? '');

        if ($action === 'unblock' && !empty($target_ip)) {
            if (ip_guard_unblock($target_ip)) {
                $_SESSION['flash_block_msg'] = [
                    'type' => 'success',
                    'text' => "Successfully unblocked IP address: " . htmlspecialchars($target_ip, ENT_QUOTES, 'UTF-8') . ". Access has been immediately restored."
                ];
            } else {
                $_SESSION['flash_block_msg'] = [
                    'type' => 'danger',
                    'text' => "Failed to unblock IP address: " . htmlspecialchars($target_ip, ENT_QUOTES, 'UTF-8')
                ];
            }
        } elseif ($action === 'clean_expired') {
            $cleaned = ip_guard_clean_expired();
            $_SESSION['flash_block_msg'] = [
                'type' => 'info',
                'text' => "Purged $cleaned expired block record(s) from the system."
            ];
        } elseif ($action === 'manual_block' && !empty($target_ip)) {
            $days = isset($_POST['duration_days']) ? (int)$_POST['duration_days'] : 7;
            $reason = trim($_POST['reason'] ?? 'Manual Administrative Lock');
            $adminName = $_SESSION['username'] ?? 'Staff';

            if (filter_var($target_ip, FILTER_VALIDATE_IP)) {
                if (ip_guard_manual_block($target_ip, $days, $reason, $adminName)) {
                    $_SESSION['flash_block_msg'] = [
                        'type' => 'warning',
                        'text' => "IP address " . htmlspecialchars($target_ip, ENT_QUOTES, 'UTF-8') . " has been blocked for $days day(s)."
                    ];
                } else {
                    $_SESSION['flash_block_msg'] = [
                        'type' => 'danger',
                        'text' => "Failed to apply block to IP address: " . htmlspecialchars($target_ip, ENT_QUOTES, 'UTF-8')
                    ];
                }
            } else {
                $_SESSION['flash_block_msg'] = [
                    'type' => 'danger',
                    'text' => "Invalid IP address format. Please enter a valid IPv4 or IPv6 address."
                ];
            }
        }

        header("Location: blocked_ips.php");
        exit;
    }

    $all_records = ip_guard_get_all_records();
    $active_count = ip_guard_active_count();
    $total_count = count($all_records);
    $my_ip = ip_guard_get_client_ip();
?>
<!DOCTYPE HTML>
<html lang='en'>

<head>
    <meta charset="utf-8">
    <title><?php echo htmlspecialchars($product_name, ENT_QUOTES, 'UTF-8');?> : Blocked IP Management</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="../assets/styles/style.css" rel="stylesheet" type="text/css">
    <link href="../<?php echo htmlspecialchars($mini_icon_path, ENT_QUOTES, 'UTF-8');?>" rel="icon" type="image/png" />
    <link rel="stylesheet" href="../assets/fontawesome/css/all.min.css">
    <style>
        .ip-badge {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: 0.85rem;
            padding: 3px 8px;
            background: #f1f5f9;
            border-radius: 4px;
            color: #0f172a;
            border: 1px solid #e2e8f0;
            display: inline-block;
        }
        .stat-cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .stat-card {
            background: #ffffff;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            padding: 1.25rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            box-shadow: var(--shadow-sm);
        }
        .stat-card-icon {
            width: 48px;
            height: 48px;
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
        }
        .stat-card-danger .stat-card-icon { background: #fef2f2; color: #dc2626; }
        .stat-card-info .stat-card-icon { background: #eff6ff; color: #2563eb; }
        .stat-card-neutral .stat-card-icon { background: #f8fafc; color: #475569; border: 1px solid #e2e8f0; }
        .stat-val { font-size: 1.5rem; font-weight: 700; line-height: 1.2; }
        .stat-lbl { font-size: 0.8rem; color: var(--text-muted); }
    </style>
</head>

<body>
    <?php include_once '../includes/loggedinfo.php';?>

    <div class="app-container">
        <div class="d-flex justify-between align-center mb-3">
            <div>
                <h2 class="mb-0" style="font-size:1.4rem;"><i class="fa-solid fa-shield-virus text-danger me-2"></i> Blocked IP Management</h2>
                <span class="text-muted small">File-based brute force protection &amp; login rate limiting</span>
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-primary btn-sm" onclick="openManualBlockModal();">
                    <i class="fa-solid fa-ban me-1"></i> Block IP Manually
                </button>
                <form action="blocked_ips.php" method="post" class="d-inline" onsubmit="return confirm('Purge all expired block files from the system?');">
                    <input type="hidden" name="action" value="clean_expired">
                    <input type="hidden" name="token" value="<?php echo htmlspecialchars($_SESSION['token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    <button type="submit" class="btn btn-secondary btn-sm" title="Remove expired blocks">
                        <i class="fa-solid fa-broom me-1"></i> Clean Expired
                    </button>
                </form>
                <a class="btn btn-secondary btn-sm" href="../index2.php">
                    <i class="fa-solid fa-arrow-left me-1"></i> Back to Dashboard
                </a>
            </div>
        </div>

        <?php
            if (!empty($_SESSION['flash_block_msg'])) {
                $fmsg = $_SESSION['flash_block_msg'];
                $ftype = htmlspecialchars($fmsg['type'] ?? 'info', ENT_QUOTES, 'UTF-8');
                $ftext = $fmsg['text'] ?? '';
                echo "<div class='alert alert-{$ftype} mb-3'><i class='fa-solid fa-circle-info me-2'></i>" . htmlspecialchars($ftext, ENT_QUOTES, 'UTF-8') . "</div>";
                unset($_SESSION['flash_block_msg']);
            }
        ?>

        <!-- Stat Cards -->
        <div class="stat-cards-grid">
            <div class="stat-card <?php echo $active_count > 0 ? 'stat-card-danger' : 'stat-card-neutral';?>">
                <div class="stat-card-icon">
                    <i class="fa-solid fa-ban"></i>
                </div>
                <div>
                    <div class="stat-val <?php echo $active_count > 0 ? 'text-danger' : '';?>"><?php echo $active_count;?></div>
                    <div class="stat-lbl">Active Blocked IPs (1 Week)</div>
                </div>
            </div>

            <div class="stat-card stat-card-info">
                <div class="stat-card-icon">
                    <i class="fa-solid fa-list-check"></i>
                </div>
                <div>
                    <div class="stat-val"><?php echo $total_count;?></div>
                    <div class="stat-lbl">Total Tracked IP Records</div>
                </div>
            </div>

            <div class="stat-card stat-card-neutral">
                <div class="stat-card-icon">
                    <i class="fa-solid fa-network-wired"></i>
                </div>
                <div>
                    <div class="stat-val" style="font-size:1rem; font-family:monospace;"><?php echo htmlspecialchars($my_ip, ENT_QUOTES, 'UTF-8');?></div>
                    <div class="stat-lbl">Your Current IP Address</div>
                </div>
            </div>
        </div>

        <!-- IP List Card -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-between align-center">
                <strong><i class="fa-solid fa-shield-halved text-primary me-2"></i> Tracked &amp; Blocked IP Addresses</strong>
                <span class="badge badge-secondary"><?php echo $total_count;?> Record<?php echo $total_count === 1 ? '' : 's';?></span>
            </div>
            <div class="card-body p-0">
                <?php if (empty($all_records)): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="fa-solid fa-shield-check fa-3x text-success mb-3"></i>
                        <h5>No Blocked IPs</h5>
                        <p class="small mb-0">There are currently no active blocks or failed attempt records in the blocks folder.</p>
                    </div>
                <?php else: ?>
                    <table class="table-modern m-0">
                        <thead>
                            <tr>
                                <th class="text-center" style="width:50px;">#</th>
                                <th>IP Address</th>
                                <th>Status</th>
                                <th class="text-center">Failed Attempts</th>
                                <th>Target Portal</th>
                                <th>Blocked Date</th>
                                <th>Expires / Remaining</th>
                                <th class="text-center" style="width:160px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                                $idx = 1;
                                foreach ($all_records as $rec):
                                    $rec_ip = $rec['ip'] ?? '';
                                    $is_active = !empty($rec['is_active_block']);
                                    $is_expired = !empty($rec['is_expired']);
                                    $attempts = $rec['failed_attempts'] ?? 0;
                                    $blocked_at = !empty($rec['blocked_at']) ? date('d M Y, h:i A', $rec['blocked_at']) : '-';
                                    $expires_at = !empty($rec['block_expires_at']) ? date('d M Y, h:i A', $rec['block_expires_at']) : '-';
                                    $portal = ucfirst($rec['portal'] ?? 'unknown');
                                    $reason = $rec['block_reason'] ?? '3 failed login attempts';
                                    $rem_sec = $rec['remaining_seconds'] ?? 0;
                            ?>
                            <tr class="table-row">
                                <td class="text-center text-muted"><?php echo $idx++;?></td>
                                <td>
                                    <span class="ip-badge"><?php echo htmlspecialchars($rec_ip, ENT_QUOTES, 'UTF-8');?></span>
                                    <?php if ($rec_ip === $my_ip): ?>
                                        <span class="badge badge-info ms-1">Your IP</span>
                                    <?php endif; ?>
                                    <div class="small text-muted mt-1" title="<?php echo htmlspecialchars($reason, ENT_QUOTES, 'UTF-8');?>">
                                        <i class="fa-solid fa-info-circle me-1"></i><?php echo htmlspecialchars($reason, ENT_QUOTES, 'UTF-8');?>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($is_active): ?>
                                        <span class="badge badge-danger">
                                            <i class="fa-solid fa-ban me-1"></i> Blocked (1 Week)
                                        </span>
                                    <?php elseif ($is_expired): ?>
                                        <span class="badge badge-secondary">
                                            <i class="fa-solid fa-clock-rotate-left me-1"></i> Block Expired
                                        </span>
                                    <?php else: ?>
                                        <span class="badge badge-warning">
                                            <i class="fa-solid fa-triangle-exclamation me-1"></i> <?php echo $attempts;?>/3 Attempts
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center font-bold">
                                    <span class="<?php echo $attempts >= 3 ? 'text-danger' : 'text-warning';?>">
                                        <?php echo $attempts;?> / 3
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-primary"><?php echo htmlspecialchars($portal, ENT_QUOTES, 'UTF-8');?></span>
                                </td>
                                <td class="small text-muted">
                                    <?php echo $blocked_at;?>
                                </td>
                                <td class="small">
                                    <?php if ($is_active): ?>
                                        <strong class="text-danger"><?php echo ip_guard_format_time_left($rem_sec);?></strong><br/>
                                        <span class="text-muted"><?php echo $expires_at;?></span>
                                    <?php elseif ($is_expired): ?>
                                        <span class="text-muted">Expired on <?php echo $expires_at;?></span>
                                    <?php else: ?>
                                        <span class="text-muted">Not blocked</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <form action="blocked_ips.php" method="post" class="d-inline" onsubmit="return confirm('Are you sure you want to unblock <?php echo htmlspecialchars($rec_ip, ENT_QUOTES, 'UTF-8');?> and restore access immediately?');">
                                        <input type="hidden" name="action" value="unblock">
                                        <input type="hidden" name="target_ip" value="<?php echo htmlspecialchars($rec_ip, ENT_QUOTES, 'UTF-8');?>">
                                        <input type="hidden" name="token" value="<?php echo htmlspecialchars($_SESSION['token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                        <button type="submit" class="btn btn-success btn-sm" title="Unblock this IP">
                                            <i class="fa-solid fa-lock-open me-1"></i> Unblock
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>

        <?php include_once '../includes/footerbar.php';?>
    </div>

    <!-- Manual Block Modal Dialog -->
    <div id="manualBlockModal" class="modal-backdrop" onclick="if(event.target===this) closeManualBlockModal();">
        <div class="modal-dialog modal-sm">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fa-solid fa-ban text-danger me-2"></i> Manually Block IP</h5>
                <button type="button" class="modal-close-btn" onclick="closeManualBlockModal();">&times;</button>
            </div>
            <div class="modal-body">
                <form action="blocked_ips.php" method="post">
                    <div class="form-group mb-3">
                        <label class="form-label">Target IP Address (IPv4 / IPv6)</label>
                        <input type="text" name="target_ip" placeholder="e.g. 192.168.1.50" required class="form-control" />
                    </div>
                    <div class="form-group mb-3">
                        <label class="form-label">Block Duration</label>
                        <select name="duration_days" class="form-control">
                            <option value="7" selected>7 Days (1 Week - Default)</option>
                            <option value="1">1 Day</option>
                            <option value="3">3 Days</option>
                            <option value="14">14 Days (2 Weeks)</option>
                            <option value="30">30 Days (1 Month)</option>
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label class="form-label">Reason / Notes</label>
                        <input type="text" name="reason" placeholder="e.g. Malicious brute force attempts" class="form-control" value="Manual administrator block" />
                    </div>

                    <input type="hidden" name="action" value="manual_block">
                    <input type="hidden" name="token" value="<?php echo htmlspecialchars($_SESSION['token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-danger w-100">
                            <i class="fa-solid fa-ban me-1"></i> Apply Block
                        </button>
                        <button type="button" class="btn btn-secondary w-100" onclick="closeManualBlockModal();">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openManualBlockModal() {
            var m = document.getElementById('manualBlockModal');
            if (m) m.classList.add('show');
        }
        function closeManualBlockModal() {
            var m = document.getElementById('manualBlockModal');
            if (m) m.classList.remove('show');
        }
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeManualBlockModal();
        });
    </script>
</body>
</html>
