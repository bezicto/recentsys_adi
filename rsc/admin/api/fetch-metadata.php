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

// Locate and load site config if available
$configLoaded = false;
if (!empty($_SESSION['config_locality']) && is_file($_SESSION['config_locality'])) {
    @include_once $_SESSION['config_locality'];
    $configLoaded = true;
}

if (!$configLoaded) {
    $configCandidates = [
        dirname(__DIR__, 3) . '/site/config.php',
        dirname(__DIR__, 2) . '/site/config.php',
        dirname(__DIR__, 1) . '/site/config.php',
        dirname(__DIR__, 2) . '/config.php',
        dirname(__DIR__, 1) . '/config.php',
        __DIR__ . '/../../config.php'
    ];
    foreach ($configCandidates as $cfg) {
        if (is_file($cfg)) {
            @include_once $cfg;
            $configLoaded = true;
            break;
        }
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

// Extract parameters
$type = strtolower(trim((string)($_REQUEST['type'] ?? '')));
$identifier = trim((string)($_REQUEST['identifier'] ?? ($_REQUEST['id'] ?? ($_REQUEST['query'] ?? ($_REQUEST['isbn'] ?? ($_REQUEST['issn'] ?? ''))))));

if (empty($type) || empty($identifier)) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Missing required parameters. Please provide "type" (isbn|issn) and "identifier".'
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

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
        'data' => $data
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

} catch (InvalidArgumentException $e) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
} catch (RuntimeException $e) {
    http_response_code(404);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to retrieve metadata: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}
