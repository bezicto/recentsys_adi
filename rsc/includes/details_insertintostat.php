<?php
    
    defined('includeExist') || die("<div class='auth-card'><span class='badge badge-danger mb-2'>WARNING</span><h2>Forbidden: Direct access prohibited</h2><em class='text-muted'>System Response Code</em></div>");
    
    // Only log hits for non-logged-in visitors (OPAC) or patrons logged into My Account module.
    // Exclude admin and staff users.
    $is_staff = !empty($_SESSION['username']) || (!empty($_SESSION['editmode']) && ($_SESSION['editmode'] === 'SUPER' || $_SESSION['editmode'] === 'TRUE'));

    if (!$is_staff && isset($det_id) && $det_id > 0) {
        try {
            $current_ts = time();
            $raw_ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            $ip = substr($raw_ip, 0, 45);
            
            // Calculate start of today (00:00:00 midnight)
            $start_of_today = strtotime('today midnight');
            
            // Check if this IP has already accessed this item today
            $stmt_check = mysqli_prepare($GLOBALS["conn"], "SELECT id FROM eg_item_det WHERE eg_item_id = ? AND `39ipaddr` = ? AND `39logdate` >= ? LIMIT 1");
            $already_viewed_today = false;
            if ($stmt_check) {
                mysqli_stmt_bind_param($stmt_check, "isi", $det_id, $ip, $start_of_today);
                mysqli_stmt_execute($stmt_check);
                $result_check = mysqli_stmt_get_result($stmt_check);
                if ($result_check && mysqli_num_rows($result_check) > 0) {
                    $already_viewed_today = true;
                }
                mysqli_stmt_close($stmt_check);
            }
            
            // Only count hit if not already accessed by this IP today
            if (!$already_viewed_today) {
                // 1. Insert access log into eg_item_det (39logdate is Unix timestamp bigint)
                $stmt_stat = mysqli_prepare($GLOBALS["conn"], "INSERT INTO eg_item_det (id, eg_item_id, `39logdate`, `39ipaddr`) VALUES (NULL, ?, ?, ?)");
                if ($stmt_stat) {
                    mysqli_stmt_bind_param($stmt_stat, "iis", $det_id, $current_ts, $ip);
                    mysqli_stmt_execute($stmt_stat);
                    mysqli_stmt_close($stmt_stat);
                }
                
                // 2. Increment cumulative hits in eg_item
                $stmt_hit = mysqli_prepare($GLOBALS["conn"], "UPDATE eg_item SET `41hits` = `41hits` + 1 WHERE id = ?");
                if ($stmt_hit) {
                    mysqli_stmt_bind_param($stmt_hit, "i", $det_id);
                    mysqli_stmt_execute($stmt_hit);
                    mysqli_stmt_close($stmt_hit);
                }
                
                // Update local hits variable for current view rendering
                $hits5 = (int)$hits5 + 1;
            }
        } catch (Throwable $e) {
            // Log error silently so stats failure never crashes page view
            error_log("Item hit logging error: " . $e->getMessage());
        }
    }
