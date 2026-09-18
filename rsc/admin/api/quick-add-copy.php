<?php
/**
 * Quick Add Copy API Endpoint
 *
 * Adds 1 physical copy to an existing catalog item in eg_item_copies
 * Sequential accession number is generated automatically.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

// Check authentication
$isLoggedIn = !empty($_SESSION['editmode']) && in_array($_SESSION['editmode'], ['SUPER', 'TRUE'], true);
$hasUsername = !empty($_SESSION['username']);

if (!$isLoggedIn && !$hasUsername) {
    http_response_code(401);
    echo json_encode([
        'status' => 'error',
        'message' => 'Unauthorized access. Please log in.'
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

// Locate and load master config (rsc/config.php) which resolves site_dev / site and connects $conn
$rscConfigCandidates = [
    __DIR__ . '/../../config.php',
    dirname(__DIR__, 2) . '/config.php',
    dirname(__DIR__, 1) . '/config.php'
];
foreach ($rscConfigCandidates as $rcfg) {
    if (is_file($rcfg)) {
        require_once $rcfg;
        break;
    }
}

if (!isset($conn) || !$conn) {
    $conn = $GLOBALS['conn'] ?? null;
}
if ((!isset($conn) || !$conn) && !empty($dbhost) && !empty($dbname) && !empty($dbuser)) {
    try {
        $conn = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
        $GLOBALS['conn'] = $conn;
    } catch (Throwable $e) {}
}

// Locate and load functions
$functionsCandidates = [
    __DIR__ . '/../../includes/functions.php',
    dirname(__DIR__, 2) . '/includes/functions.php',
    dirname(__DIR__, 1) . '/includes/functions.php'
];
foreach ($functionsCandidates as $fn) {
    if (is_file($fn)) {
        require_once $fn;
        break;
    }
}

if (!$conn) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Database connection failed.'
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

$bahan_id = isset($_REQUEST['bahan_id']) && is_numeric($_REQUEST['bahan_id']) ? (int)$_REQUEST['bahan_id'] : 0;

if ($bahan_id <= 0) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid or missing parameter "bahan_id".'
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

// Verify item exists in eg_item
$stmtItem = mysqli_prepare($conn, "SELECT id, `38title`, `38issn`, `39type` FROM eg_item WHERE id = ?");
if (!$stmtItem) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database query preparation failed.'], JSON_UNESCAPED_UNICODE);
    exit;
}
mysqli_stmt_bind_param($stmtItem, "i", $bahan_id);
mysqli_stmt_execute($stmtItem);
$resItem = mysqli_stmt_get_result($stmtItem);
$itemData = mysqli_fetch_assoc($resItem);
mysqli_stmt_close($stmtItem);

if (!$itemData) {
    http_response_code(404);
    echo json_encode([
        'status' => 'error',
        'message' => "Item record #{$bahan_id} not found in database."
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

// Check if item is serial
$rawType = trim((string)($itemData['39type'] ?? ''));
$typeName = function_exists('idToType') && is_numeric($rawType) ? idToType($rawType) : $rawType;
$isSerial = (!empty($itemData['38issn']) || $rawType === '4' || strcasecmp($rawType, 'Serial') === 0 || stripos($typeName, 'serial') !== false || stripos($typeName, 'periodical') !== false || stripos($typeName, 'journal') !== false || stripos($typeName, 'bersiri') !== false);

if ($isSerial) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'is_serial' => true,
        'message' => 'This is a serial publication. Serials require Cardex check-in (Volume, Issue, Year) before adding copies.'
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

// Get last used 39accessnum from eg_item_copies
$last_accession_num = 0;
$query_last = "SELECT MAX(CAST(`39accessnum` AS UNSIGNED)) as max_acc FROM eg_item_copies WHERE `39accessnum` REGEXP '^[0-9]+$'";
$result_last = mysqli_query($conn, $query_last);
if ($result_last && $row_last = mysqli_fetch_assoc($result_last)) {
    $last_accession_num = (int)($row_last["max_acc"] ?? 0);
}

$last_accession_num++;
$accession_num = sprintf("%010d", $last_accession_num);
$status = 'AVAILABLE';
$volume = '';
$issue = '';
$year = '';
$is_reference = 'NO';
$invoice_a = '0.00';
$invoice_b = '';
$invoice_c = '';
$addedon = (string)time();
$lastchange = (string)time();

$stmt = mysqli_prepare($conn, "INSERT INTO eg_item_copies (
    `eg_item_id`, `39accessnum`, `39status`, `39volume`, `39issue`, `39year`, `39is_reference`,
    `39invoice_a`, `39invoice_b`, `39invoice_c`, `39addedon`, `39lastchange`
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

if (!$stmt) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Failed to prepare copy insert statement.'], JSON_UNESCAPED_UNICODE);
    exit;
}

mysqli_stmt_bind_param($stmt, "isssssssssss",
    $bahan_id, $accession_num, $status, $volume, $issue, $year, $is_reference,
    $invoice_a, $invoice_b, $invoice_c, $addedon, $lastchange
);

$executed = mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

if (!$executed) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database error while inserting copy record.'], JSON_UNESCAPED_UNICODE);
    exit;
}

// Count total copies now
$totalCopies = 0;
$stmtCount = mysqli_prepare($conn, "SELECT COUNT(*) as cnt FROM eg_item_copies WHERE eg_item_id = ?");
if ($stmtCount) {
    mysqli_stmt_bind_param($stmtCount, "i", $bahan_id);
    mysqli_stmt_execute($stmtCount);
    $resCount = mysqli_stmt_get_result($stmtCount);
    if ($rowCount = mysqli_fetch_assoc($resCount)) {
        $totalCopies = (int)($rowCount['cnt'] ?? 0);
    }
    mysqli_stmt_close($stmtCount);
}

http_response_code(200);
echo json_encode([
    'status' => 'success',
    'bahan_id' => $bahan_id,
    'accession_num' => $accession_num,
    'copies_count' => $totalCopies,
    'title' => $itemData['38title'] ?? '',
    'message' => "Successfully registered 1 copy with Accession Barcode: {$accession_num}"
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
