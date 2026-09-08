<?php
    
    defined('includeExist') || die("<div class='auth-card'><span class='badge badge-danger mb-2'>WARNING</span><h2>Forbidden: Direct access prohibited</h2><em class='text-muted'>System HTTP Response Code</em></div>");
    
    // Ensure session token exists
    if (empty($_SESSION['token'])) {
        try {
            $_SESSION['token'] = bin2hex(random_bytes(32));
        } catch (Exception $e) {
            $_SESSION['token'] = md5(uniqid(mt_rand(), true)) . sha1(microtime(true) . ($tokenaeskeysys ?? 'default_secret'));
        }
    }

    $proceedAfterToken = false;

    // Preventing CSRF for POST requests
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['token']) && is_string($_POST['token']) && !empty($_SESSION['token']) && hash_equals($_SESSION['token'], $_POST['token'])) {
            $proceedAfterToken = true;
        } else {
            http_response_code(403);
            echo "<div class='auth-card'><span class='badge badge-danger mb-2'>403</span><h2>Forbidden: Invalid CSRF Token</h2><em class='text-muted'>System Response Code: Request validation failed. Please refresh the page and try again.</em></div>";
            exit;
        }
    } else {
        $proceedAfterToken = true;
    }
