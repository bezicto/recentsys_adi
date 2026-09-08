<?php
/**
 * Real-time Self-Registration Token Status Checker API
 *
 * Used by qr_selfreg.php to poll token status updates.
 *
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-cache, no-store, must-revalidate');

define('includeExist', true);

// Access check: Super Admin / Library Staff only
if (empty($_SESSION['editmode']) || !in_array($_SESSION['editmode'], ['SUPER', 'TRUE'], true)) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized access'], JSON_UNESCAPED_UNICODE);
    exit;
}

// Config loading
$configCandidates = [
    dirname(__DIR__, 2) . '/config.php',
    dirname(__DIR__, 3) . '/site/config.php',
    __DIR__ . '/../../config.php'
];
foreach ($configCandidates as $cfg) {
    if (is_file($cfg)) {
        @include_once $cfg;
        break;
    }
}

// Load helper
require_once dirname(__DIR__, 2) . '/includes/selfreg_helper.php';

$token = trim((string)($_GET['token'] ?? ''));

if (empty($token)) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing token parameter'], JSON_UNESCAPED_UNICODE);
    exit;
}

$statusInfo = selfreg_get_status($GLOBALS['conn'], $token);

echo json_encode($statusInfo, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
exit;
