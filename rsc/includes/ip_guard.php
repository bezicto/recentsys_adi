<?php
/**
 * IP Guard - File-based Login Rate Limiter & IP Blocking Safeguard
 * 
 * Provides brute-force protection for Patron and Admin/Staff login pages.
 * Blocks IP address for 1 week (7 days) after 3 consecutive failed attempts.
 * Uses file-based JSON storage inside $block_upload_directory ('site/blocks/').
 * No database required.
 */

if (!defined('IP_GUARD_MAX_ATTEMPTS')) {
    define('IP_GUARD_MAX_ATTEMPTS', 3);
}

if (!defined('IP_GUARD_BLOCK_DURATION')) {
    // 7 days = 1 week in seconds (7 * 24 * 60 * 60)
    define('IP_GUARD_BLOCK_DURATION', 604800);
}

/**
 * Get client IP address reliably, handling proxies and validating syntax.
 * 
 * @return string Client IP address
 */
function ip_guard_get_client_ip() {
    $ip = '';
    
    if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
        $ip = trim($_SERVER['HTTP_CF_CONNECTING_IP']);
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $forwarded = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $ip = trim($forwarded[0]);
    } elseif (!empty($_SERVER['HTTP_X_REAL_IP'])) {
        $ip = trim($_SERVER['HTTP_X_REAL_IP']);
    } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
        $ip = trim($_SERVER['REMOTE_ADDR']);
    }

    if (filter_var($ip, FILTER_VALIDATE_IP)) {
        return $ip;
    }

    return (!empty($_SERVER['REMOTE_ADDR'])) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1';
}

/**
 * Resolve the absolute filesystem path to the blocks directory.
 * 
 * @return string|false Path to blocks directory, or false on error
 */
function ip_guard_get_blocks_dir() {
    // 1. If $_SESSION['config_locality'] is set, use its parent dir
    if (!empty($_SESSION['config_locality']) && file_exists($_SESSION['config_locality'])) {
        $dir = dirname($_SESSION['config_locality']) . DIRECTORY_SEPARATOR . 'blocks';
        if (is_dir($dir)) {
            return realpath($dir);
        }
    }

    // 2. If $GLOBALS['block_upload_directory'] is available
    if (!empty($GLOBALS['block_upload_directory'])) {
        $globalDir = $GLOBALS['block_upload_directory'];
        if (is_dir($globalDir)) {
            return realpath($globalDir);
        }
        if (is_dir(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . $globalDir)) {
            return realpath(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . $globalDir);
        }
    }

    // 3. Known relative fallbacks from includes/ directory
    $possible_paths = [
        __DIR__ . '/../../site/blocks',
        __DIR__ . '/../site/blocks',
        dirname(dirname(__DIR__)) . '/site/blocks'
    ];

    foreach ($possible_paths as $p) {
        if (is_dir($p)) {
            return realpath($p);
        }
    }

    // Attempt to create if parent site exists
    $site_blocks = dirname(dirname(__DIR__)) . '/site/blocks';
    if (!is_dir($site_blocks) && is_dir(dirname(dirname(__DIR__)) . '/site')) {
        @mkdir($site_blocks, 0755, true);
        return realpath($site_blocks);
    }

    return false;
}

/**
 * Generate a safe filename for an IP address.
 * 
 * @param string $ip
 * @return string Filename
 */
function ip_guard_get_filename($ip) {
    // Sanitize IP for safe filesystem naming (replace colons in IPv6, etc.)
    $safe_ip = preg_replace('/[^a-zA-Z0-9\.\-]/', '_', $ip);
    return 'ip_' . $safe_ip . '.json';
}

/**
 * Read the block/tracking file for an IP.
 * 
 * @param string $ip
 * @return array|null Record data or null if not found
 */
function ip_guard_read_record($ip) {
    $dir = ip_guard_get_blocks_dir();
    if (!$dir) return null;

    $filePath = $dir . DIRECTORY_SEPARATOR . ip_guard_get_filename($ip);
    if (!file_exists($filePath)) {
        return null;
    }

    $raw = @file_get_contents($filePath);
    if ($raw === false) return null;

    $data = @json_decode($raw, true);
    return is_array($data) ? $data : null;
}

/**
 * Write a block/tracking record for an IP.
 * 
 * @param string $ip
 * @param array $data
 * @return bool
 */
function ip_guard_write_record($ip, $data) {
    $dir = ip_guard_get_blocks_dir();
    if (!$dir || !is_writable($dir)) return false;

    $filePath = $dir . DIRECTORY_SEPARATOR . ip_guard_get_filename($ip);
    $json = json_encode($data, JSON_PRETTY_PRINT);
    return @file_put_contents($filePath, $json, LOCK_EX) !== false;
}

/**
 * Check if an IP is currently blocked.
 * Handles automatic unblocking if the 1-week block period has expired.
 * 
 * @param string|null $ip If null, detects current client IP
 * @return array Status array: ['is_blocked' => bool, 'attempts' => int, 'remaining_seconds' => int, 'expires_at' => int|null, 'reason' => string]
 */
function ip_guard_check_status($ip = null) {
    if ($ip === null) {
        $ip = ip_guard_get_client_ip();
    }

    $record = ip_guard_read_record($ip);

    if (!$record) {
        return [
            'is_blocked' => false,
            'attempts' => 0,
            'remaining_seconds' => 0,
            'expires_at' => null,
            'reason' => '',
            'data' => null
        ];
    }

    $currentTime = time();
    $isBlocked = !empty($record['blocked']);
    $expiresAt = $record['block_expires_at'] ?? 0;

    if ($isBlocked) {
        if ($currentTime >= $expiresAt) {
            // Expired after 1 week: Automatically lift the block
            ip_guard_unblock($ip);
            return [
                'is_blocked' => false,
                'attempts' => 0,
                'remaining_seconds' => 0,
                'expires_at' => null,
                'reason' => 'Block expired and automatically lifted.',
                'data' => null
            ];
        }

        // Active block
        return [
            'is_blocked' => true,
            'attempts' => $record['failed_attempts'] ?? IP_GUARD_MAX_ATTEMPTS,
            'remaining_seconds' => ($expiresAt - $currentTime),
            'expires_at' => $expiresAt,
            'blocked_at' => $record['blocked_at'] ?? $currentTime,
            'reason' => $record['block_reason'] ?? 'Exceeded 3 failed login attempts',
            'data' => $record
        ];
    }

    // Not blocked, but has pending failed attempts
    return [
        'is_blocked' => false,
        'attempts' => (int)($record['failed_attempts'] ?? 0),
        'remaining_seconds' => 0,
        'expires_at' => null,
        'reason' => '',
        'data' => $record
    ];
}

/**
 * Record a failed login attempt.
 * If failed attempts reach 3, automatically blocks the IP for 1 week.
 * 
 * @param string|null $ip
 * @param string $portal 'patron' | 'staff'
 * @param string $username Attempted username (for logging)
 * @return array Result array: ['is_blocked' => bool, 'attempts' => int, 'remaining_attempts' => int, 'expires_at' => int|null]
 */
function ip_guard_record_failure($ip = null, $portal = 'patron', $username = '') {
    if ($ip === null) {
        $ip = ip_guard_get_client_ip();
    }

    $currentTime = time();
    $record = ip_guard_read_record($ip);

    if (!$record) {
        $record = [
            'ip' => $ip,
            'failed_attempts' => 0,
            'first_failed_at' => $currentTime,
            'last_failed_at' => $currentTime,
            'blocked' => false,
            'blocked_at' => null,
            'block_expires_at' => null,
            'block_reason' => '',
            'portal' => $portal,
            'last_user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'attempts_history' => []
        ];
    }

    $record['failed_attempts'] = (int)($record['failed_attempts'] ?? 0) + 1;
    $record['last_failed_at'] = $currentTime;
    $record['portal'] = $portal;
    $record['last_user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
    
    if (!isset($record['attempts_history']) || !is_array($record['attempts_history'])) {
        $record['attempts_history'] = [];
    }
    
    $record['attempts_history'][] = [
        'time' => $currentTime,
        'date' => date('Y-m-d H:i:s', $currentTime),
        'portal' => $portal,
        'username' => htmlspecialchars(substr($username, 0, 30), ENT_QUOTES, 'UTF-8')
    ];

    // Keep history trimmed to last 10 attempts
    if (count($record['attempts_history']) > 10) {
        $record['attempts_history'] = array_slice($record['attempts_history'], -10);
    }

    $isNowBlocked = false;
    if ($record['failed_attempts'] >= IP_GUARD_MAX_ATTEMPTS) {
        $record['blocked'] = true;
        $record['blocked_at'] = $currentTime;
        $record['block_expires_at'] = $currentTime + IP_GUARD_BLOCK_DURATION; // 1 week
        $record['block_reason'] = 'Exceeded maximum failed login attempts (' . IP_GUARD_MAX_ATTEMPTS . ' times)';
        $isNowBlocked = true;
    }

    ip_guard_write_record($ip, $record);

    return [
        'is_blocked' => $isNowBlocked,
        'attempts' => $record['failed_attempts'],
        'remaining_attempts' => max(0, IP_GUARD_MAX_ATTEMPTS - $record['failed_attempts']),
        'expires_at' => $record['block_expires_at'] ?? null,
        'blocked_at' => $record['blocked_at'] ?? null
    ];
}

/**
 * Record a successful login. Clears tracking/failed attempt record for this IP.
 * 
 * @param string|null $ip
 * @return bool
 */
function ip_guard_record_success($ip = null) {
    if ($ip === null) {
        $ip = ip_guard_get_client_ip();
    }

    return ip_guard_unblock($ip);
}

/**
 * Unblock an IP and remove its record file from the blocks folder.
 * 
 * @param string $ip
 * @return bool
 */
function ip_guard_unblock($ip) {
    $dir = ip_guard_get_blocks_dir();
    if (!$dir) return false;

    $filePath = $dir . DIRECTORY_SEPARATOR . ip_guard_get_filename($ip);
    if (file_exists($filePath)) {
        return @unlink($filePath);
    }

    return true;
}

/**
 * Manually block an IP for a specified number of days.
 * 
 * @param string $ip
 * @param int $durationDays Default 7 days (1 week)
 * @param string $reason
 * @param string $adminUser
 * @return bool
 */
function ip_guard_manual_block($ip, $durationDays = 7, $reason = 'Manual administrative block', $adminUser = 'Staff') {
    if (!filter_var($ip, FILTER_VALIDATE_IP)) {
        return false;
    }

    $currentTime = time();
    $durationSeconds = max(1, (int)$durationDays) * 86400;

    $record = [
        'ip' => $ip,
        'failed_attempts' => IP_GUARD_MAX_ATTEMPTS,
        'first_failed_at' => $currentTime,
        'last_failed_at' => $currentTime,
        'blocked' => true,
        'blocked_at' => $currentTime,
        'block_expires_at' => $currentTime + $durationSeconds,
        'block_reason' => $reason . ' (by ' . $adminUser . ')',
        'portal' => 'manual',
        'last_user_agent' => 'Admin Action',
        'attempts_history' => [
            [
                'time' => $currentTime,
                'date' => date('Y-m-d H:i:s', $currentTime),
                'portal' => 'manual',
                'username' => 'admin_block'
            ]
        ]
    ];

    return ip_guard_write_record($ip, $record);
}

/**
 * Get all tracked and blocked IP records from the blocks folder.
 * 
 * @return array List of records with calculated fields
 */
function ip_guard_get_all_records() {
    $dir = ip_guard_get_blocks_dir();
    if (!$dir) return [];

    $files = glob($dir . DIRECTORY_SEPARATOR . 'ip_*.json');
    if (!$files) return [];

    $records = [];
    $currentTime = time();

    foreach ($files as $file) {
        $raw = @file_get_contents($file);
        if (!$raw) continue;

        $data = @json_decode($raw, true);
        if (!is_array($data) || empty($data['ip'])) continue;

        $isBlocked = !empty($data['blocked']);
        $expiresAt = $data['block_expires_at'] ?? 0;
        $isExpired = ($isBlocked && $currentTime >= $expiresAt);

        $data['is_active_block'] = ($isBlocked && !$isExpired);
        $data['is_expired'] = $isExpired;
        $data['file_path'] = $file;
        $data['remaining_seconds'] = max(0, $expiresAt - $currentTime);

        $records[] = $data;
    }

    // Sort: Active blocks first (newest blocked_at first), then pending attempts
    usort($records, function($a, $b) {
        if ($a['is_active_block'] !== $b['is_active_block']) {
            return $a['is_active_block'] ? -1 : 1;
        }
        $timeA = $a['blocked_at'] ?? $a['last_failed_at'] ?? 0;
        $timeB = $b['blocked_at'] ?? $b['last_failed_at'] ?? 0;
        return $timeB <=> $timeA;
    });

    return $records;
}

/**
 * Purge all expired block records from disk.
 * 
 * @return int Number of files cleaned
 */
function ip_guard_clean_expired() {
    $records = ip_guard_get_all_records();
    $cleaned = 0;

    foreach ($records as $r) {
        if ($r['is_expired']) {
            if (ip_guard_unblock($r['ip'])) {
                $cleaned++;
            }
        }
    }

    return $cleaned;
}

/**
 * Get count of active blocked IPs.
 * 
 * @return int
 */
function ip_guard_active_count() {
    $records = ip_guard_get_all_records();
    $count = 0;

    foreach ($records as $r) {
        if (!empty($r['is_active_block'])) {
            $count++;
        }
    }

    return $count;
}

/**
 * Format remaining seconds into a human-readable string (e.g., "6 days, 23 hours").
 * 
 * @param int $seconds
 * @return string
 */
function ip_guard_format_time_left($seconds) {
    if ($seconds <= 0) return 'Expired';

    $days = floor($seconds / 86400);
    $hours = floor(($seconds % 86400) / 3600);
    $mins = floor(($seconds % 3600) / 60);

    if ($days > 0) {
        return $days . 'd ' . $hours . 'h remaining';
    } elseif ($hours > 0) {
        return $hours . 'h ' . $mins . 'm remaining';
    } else {
        return max(1, $mins) . 'm remaining';
    }
}
