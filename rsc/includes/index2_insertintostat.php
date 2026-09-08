<?php
    
    defined('includeExist') || die("<div class='auth-card'><span class='badge badge-danger mb-2'>WARNING</span><h2>Forbidden: Direct access prohibited</h2><em class='text-muted'>System Response Code</em></div>");
    
    $trimmed_scstr = trim((string)$scstr2);
    if ($trimmed_scstr !== '' && !isset($_GET["page"])) {
        $sctype21 = ($sctype2 !== 'Author') ? 'All Type' : 'Author';
        $current_ts = time();
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        
        $stmt = mysqli_prepare($GLOBALS["conn"], "INSERT INTO eg_userlog_det (id, `38keyword`, `38logdate`, `38ipaddr`, `38type`) VALUES (NULL, ?, ?, ?, ?)");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "siss", $trimmed_scstr, $current_ts, $ip, $sctype21);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }
