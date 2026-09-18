<?php
/**
 * Bibliographic Metadata Fetch API Endpoint
 *
 * Accepts GET/POST requests:
 *   - type: "isbn" | "issn"
 *   - identifier: string (ISBN or ISSN)
 *
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

// Check authentication for cataloging access
$isLoggedIn = !empty($_SESSION['editmode']) && in_array($_SESSION['editmode'], ['SUPER', 'TRUE'], true);
$hasUsername = !empty($_SESSION['username']);

if (!$isLoggedIn && !$hasUsername) {
    http_response_code(401);
    echo json_encode([
        'status' => 'error',
        'message' => 'Unauthorized access. Please log in to fetch bibliographic metadata.'
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

// Locate and load BibliographicService
$servicePathCandidates = [
    __DIR__ . '/../classes/BibliographicService.php',
    __DIR__ . '/../../classes/BibliographicService.php',
    __DIR__ . '/../../rsc/classes/BibliographicService.php',
    __DIR__ . '/classes/BibliographicService.php',
    dirname(__DIR__, 2) . '/rsc/classes/BibliographicService.php',
    dirname(__DIR__, 1) . '/rsc/classes/BibliographicService.php'
];

$serviceLoaded = false;
foreach ($servicePathCandidates as $path) {
    if (is_file($path)) {
        require_once $path;
        $serviceLoaded = true;
        break;
    }
}

if (!$serviceLoaded) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'BibliographicService engine could not be loaded.'
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

/**
 * Check if ISBN or ISSN already exists in catalog database
 */
function findLocalItemMatch($identifier, $excludeId = 0) {
    global $conn;
    if (!$conn) {
        $conn = $GLOBALS['conn'] ?? null;
    }
    if (!$conn) {
        return ['exists' => false];
    }
    
    $itemIds = function_exists('getItemIdsByIsbnIssn') ? getItemIdsByIsbnIssn($identifier) : [];

    // Fallback if variants didn't match directly
    if (empty($itemIds)) {
        $clean = function_exists('cleanIsbnIssn') ? cleanIsbnIssn($identifier) : preg_replace('/[^0-9X]/i', '', $identifier);
        if (!empty($clean)) {
            $stmtFB = mysqli_prepare($conn, "SELECT id FROM eg_item WHERE REPLACE(REPLACE(REPLACE(`38isbn`, '-', ''), ' ', ''), '–', '') = ? OR REPLACE(REPLACE(REPLACE(`38issn`, '-', ''), ' ', ''), '–', '') = ?");
            if ($stmtFB) {
                mysqli_stmt_bind_param($stmtFB, "ss", $clean, $clean);
                mysqli_stmt_execute($stmtFB);
                $resFB = mysqli_stmt_get_result($stmtFB);
                while ($rowFB = mysqli_fetch_assoc($resFB)) {
                    $itemIds[] = (int)$rowFB['id'];
                }
                mysqli_stmt_close($stmtFB);
            }
        }
    }

    if (!empty($excludeId) && is_numeric($excludeId)) {
        $itemIds = array_values(array_diff($itemIds, [(int)$excludeId]));
    }
    
    if (empty($itemIds)) {
        return ['exists' => false];
    }
    
    $matchedId = (int)$itemIds[0];
    
    $stmt = mysqli_prepare($conn, "SELECT id, `38title`, `38author`, `38isbn`, `38issn`, `38localcallnum`, `38localcallnum_b`, `39type` FROM eg_item WHERE id = ?");
    if (!$stmt) {
        return ['exists' => false];
    }
    mysqli_stmt_bind_param($stmt, "i", $matchedId);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $item = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);
    
    if (!$item) {
        return ['exists' => false];
    }
    
    // Count copies in eg_item_copies
    $copiesCount = 0;
    $stmtCopies = mysqli_prepare($conn, "SELECT COUNT(*) as cnt FROM eg_item_copies WHERE eg_item_id = ?");
    if ($stmtCopies) {
        mysqli_stmt_bind_param($stmtCopies, "i", $matchedId);
        mysqli_stmt_execute($stmtCopies);
        $resCopies = mysqli_stmt_get_result($stmtCopies);
        if ($rowCopies = mysqli_fetch_assoc($resCopies)) {
            $copiesCount = (int)($rowCopies['cnt'] ?? 0);
        }
        mysqli_stmt_close($stmtCopies);
    }
    
    $rawType = trim((string)($item['39type'] ?? ''));
    $typeName = function_exists('idToType') && is_numeric($rawType) ? idToType($rawType) : $rawType;
    $isSerial = (!empty($item['38issn']) || $rawType === '4' || strcasecmp($rawType, 'Serial') === 0 || stripos($typeName, 'serial') !== false || stripos($typeName, 'periodical') !== false || stripos($typeName, 'journal') !== false || stripos($typeName, 'bersiri') !== false || stripos($typeName, 'majalah') !== false);
    
    return [
        'exists' => true,
        'id' => (int)$item['id'],
        'title' => $item['38title'] ?? '',
        'author' => $item['38author'] ?? '',
        'isbn' => $item['38isbn'] ?? '',
        'issn' => $item['38issn'] ?? '',
        'callnum' => trim(($item['38localcallnum'] ?? '') . ' ' . ($item['38localcallnum_b'] ?? '')),
        'type' => $rawType,
        'type_name' => $typeName,
        'is_serial' => $isSerial,
        'copies_count' => $copiesCount
    ];
}

// Extract parameters
$type = strtolower(trim((string)($_REQUEST['type'] ?? '')));
$identifier = trim((string)($_REQUEST['identifier'] ?? ($_REQUEST['id'] ?? ($_REQUEST['query'] ?? ($_REQUEST['isbn'] ?? ($_REQUEST['issn'] ?? ''))))));
$action = strtolower(trim((string)($_REQUEST['action'] ?? '')));
$excludeId = isset($_REQUEST['exclude_id']) && is_numeric($_REQUEST['exclude_id']) ? (int)$_REQUEST['exclude_id'] : 0;

if (empty($type) || empty($identifier)) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Missing required parameters. Please provide "type" (isbn|issn) and "identifier".'
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

// Handle fast check_local action without querying external APIs
if ($action === 'check_local') {
    $localMatch = findLocalItemMatch($identifier, $excludeId);
    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'local_match' => $localMatch
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

// Check local catalog database first
$localMatch = findLocalItemMatch($identifier, $excludeId);

$serviceConfig = [
    'staff_contact_email' => $staff_contact_email ?? '',
    'enable_googlebookapi' => isset($enable_googlebookapi) ? (bool)$enable_googlebookapi : true,
    'googlebook_api_key' => $googlebook_api_key ?? '',
    'enable_openlibraryapi' => isset($enable_openlibraryapi) ? (bool)$enable_openlibraryapi : true,
    'enable_pnm_polaris_integration' => !empty($enable_pnm_polaris_integration),
    'pnm_polaris_access_key' => $pnm_polaris_access_key ?? '',
    'pnm_polaris_access_id' => $pnm_polaris_access_id ?? '',
    'pnm_polaris_base_url' => $pnm_polaris_base_url ?? ''
];

$service = new BibliographicService($serviceConfig);

try {
    if ($type === 'isbn') {
        $data = $service->fetchByIsbn($identifier);
    } elseif ($type === 'issn') {
        $data = $service->fetchByIssn($identifier);
    } else {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => "Unsupported type: '{$type}'. Must be 'isbn' or 'issn'."
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'local_match' => $localMatch,
        'data' => $data
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

} catch (InvalidArgumentException $e) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'local_match' => $localMatch,
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
} catch (RuntimeException $e) {
    if (!empty($localMatch['exists'])) {
        // Record exists in local library DB even if external provider has no record
        http_response_code(200);
        echo json_encode([
            'status' => 'success',
            'local_match' => $localMatch,
            'data' => null,
            'message' => 'Found in local catalog database. Note from external provider: ' . $e->getMessage()
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }
    http_response_code(404);
    echo json_encode([
        'status' => 'error',
        'local_match' => $localMatch,
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
} catch (Throwable $e) {
    if (!empty($localMatch['exists'])) {
        http_response_code(200);
        echo json_encode([
            'status' => 'success',
            'local_match' => $localMatch,
            'data' => null,
            'message' => 'Found in local catalog database. External lookup notice: ' . $e->getMessage()
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'local_match' => $localMatch,
        'message' => 'Failed to retrieve metadata: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}
