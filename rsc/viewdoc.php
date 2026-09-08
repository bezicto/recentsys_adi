<?php
    session_start();
    define('includeExist', true);
    include_once 'config.php';
    include_once 'includes/functions.php';

    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        http_response_code(400);
        die("<div class='auth-card'><span class='badge badge-danger mb-2'>ERROR</span><h2>Invalid Document Request</h2><em class='text-muted'>Bad Request</em></div>");
    }

    $doc_id = (int)$_GET['id'];
    if ($doc_id <= 0) {
        http_response_code(400);
        die("<div class='auth-card'><span class='badge badge-danger mb-2'>ERROR</span><h2>Invalid Document ID</h2><em class='text-muted'>Bad Request</em></div>");
    }

    // Query record information
    $stmt = mysqli_prepare($GLOBALS["conn"], "SELECT id, `38title`, `39pdfattach`, `40inputdate`, `40instimestamp` FROM eg_item WHERE id = ?");
    if (!$stmt) {
        http_response_code(500);
        die("<div class='auth-card'><span class='badge badge-danger mb-2'>ERROR</span><h2>Database Query Error</h2></div>");
    }

    mysqli_stmt_bind_param($stmt, "i", $doc_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if (!$row || ($row['39pdfattach'] ?? '') !== 'TRUE') {
        http_response_code(404);
        die("<div class='auth-card'><span class='badge badge-danger mb-2'>ERROR</span><h2>Document Not Found</h2><p class='text-muted'>The requested record has no attached PDF file.</p></div>");
    }

    $tarikh_masuk = $row['40inputdate'] ?? '';
    $dir_year = substr((string)$tarikh_masuk, -4);
    if (!preg_match('/^[0-9]{4}$/', $dir_year)) {
        $dir_year = date("Y");
    }

    $instimestamp = $row['40instimestamp'] ?? '';
    // Sanitize timestamp & ID to strictly alphanumeric
    $safe_id = preg_replace('/[^0-9]/', '', (string)$row['id']);
    $safe_ts = preg_replace('/[^0-9]/', '', (string)$instimestamp);

    $filename = $safe_id . "_" . $safe_ts . ".pdf";
    $filepath = $pdf_upload_directory . DIRECTORY_SEPARATOR . $dir_year . DIRECTORY_SEPARATOR . $filename;

    $real_filepath = realpath($filepath);
    $real_base_dir = realpath($pdf_upload_directory);

    // Verify file exists and is strictly within the allowed docs directory (prevent path traversal)
    if (!$real_filepath || !$real_base_dir || strpos($real_filepath, $real_base_dir) !== 0 || !is_file($real_filepath)) {
        http_response_code(404);
        die("<div class='auth-card'><span class='badge badge-danger mb-2'>ERROR</span><h2>File Not Found on Server</h2><p class='text-muted'>The physical document file could not be located.</p></div>");
    }

    // Prepare safe download filename from title
    $raw_title = $row['38title'] ?? 'document';
    $safe_title = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $raw_title);
    $safe_title = trim(substr($safe_title, 0, 50), '_');
    if (empty($safe_title)) {
        $safe_title = "document_" . $safe_id;
    }
    $download_name = $safe_title . ".pdf";

    // Clean output buffer before sending binary stream
    if (ob_get_level()) {
        ob_end_clean();
    }

    // Set headers for inline streaming
    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="' . $download_name . '"');
    header('Content-Length: ' . filesize($real_filepath));
    header('Content-Transfer-Encoding: binary');
    header('Accept-Ranges: bytes');
    header('Cache-Control: private, max-age=3600, must-revalidate');
    header('Pragma: public');
    header('X-Content-Type-Options: nosniff');

    readfile($real_filepath);
    exit;
