<?php
/**
 * Patron Self-Registration QR Token Helper
 *
 * Manages single-use, 30-minute tokens and LAN IP resolution for walk-in patron registration.
 *
 */

defined('includeExist') || die("Direct access forbidden");

/**
 * Generate a new single-use self-registration token (valid for 30 minutes).
 *
 * @param mysqli $conn
 * @param int $ttl_seconds (default 1800 = 30 minutes)
 * @return array ['success' => bool, 'token' => string, 'expires_at' => int]
 */
function selfreg_generate_token($conn, $ttl_seconds = 1800) {
    if (!$conn) {
        return ['success' => false, 'error' => 'Database connection unavailable'];
    }

    try {
        $token = bin2hex(random_bytes(24)); // 48 chars of secure entropy
    } catch (Exception $e) {
        $token = md5(uniqid((string)mt_rand(), true)) . sha1(microtime(true) . "selfreg_salt");
    }

    $created_at = time();
    $expires_at = $created_at + $ttl_seconds;
    $status = 'active';

    $stmt = mysqli_prepare($conn, "INSERT INTO eg_selfreg_tokens (token, created_at, expires_at, status) VALUES (?, ?, ?, ?)");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "siis", $token, $created_at, $expires_at, $status);
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        if ($ok) {
            return [
                'success' => true,
                'token' => $token,
                'created_at' => $created_at,
                'expires_at' => $expires_at,
                'ttl' => $ttl_seconds
            ];
        }
    }

    return ['success' => false, 'error' => mysqli_error($conn) ?: 'Failed to insert token record'];
}

/**
 * Validate a token's existence, expiration, and usage status.
 *
 * @param mysqli $conn
 * @param string $token
 * @return array ['valid' => bool, 'reason' => string, 'message' => string, 'data' => ?array]
 */
function selfreg_validate_token($conn, $token) {
    if (!$conn || empty($token) || !is_string($token)) {
        return [
            'valid' => false,
            'reason' => 'invalid_input',
            'message' => 'No registration token provided.',
            'data' => null
        ];
    }

    $stmt = mysqli_prepare($conn, "SELECT id, token, created_at, expires_at, status, first_scanned_at, first_scanned_ip, client_nonce, used_at, registered_username, registered_name FROM eg_selfreg_tokens WHERE token = ?");
    if (!$stmt) {
        return [
            'valid' => false,
            'reason' => 'db_error',
            'message' => 'Database query preparation error.',
            'data' => null
        ];
    }

    mysqli_stmt_bind_param($stmt, "s", $token);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = $result ? mysqli_fetch_assoc($result) : null;
    mysqli_stmt_close($stmt);

    if (!$row) {
        return [
            'valid' => false,
            'reason' => 'not_found',
            'message' => 'The registration QR code or link is invalid.',
            'data' => null
        ];
    }

    $now = time();
    $expires_at = (int)$row['expires_at'];
    $status = $row['status'];

    if ($status === 'used') {
        return [
            'valid' => false,
            'reason' => 'already_used',
            'message' => 'This registration QR code has already been used for registration.',
            'data' => $row
        ];
    }

    if ($now > $expires_at || $status === 'expired') {
        if ($status !== 'expired' && $status !== 'used') {
            $upd = mysqli_prepare($conn, "UPDATE eg_selfreg_tokens SET status = 'expired' WHERE token = ? AND status != 'used'");
            if ($upd) {
                mysqli_stmt_bind_param($upd, "s", $token);
                mysqli_stmt_execute($upd);
                mysqli_stmt_close($upd);
            }
            $row['status'] = 'expired';
        }
        return [
            'valid' => false,
            'reason' => 'expired',
            'message' => 'This registration QR code has expired (valid for 30 minutes from generation). Please request a new QR code from the library counter.',
            'data' => $row
        ];
    }

    return [
        'valid' => true,
        'reason' => 'ok',
        'message' => 'Token is valid and active.',
        'data' => $row
    ];
}

/**
 * Bind token upon first scan to prevent multi-device race conditions.
 *
 * @param mysqli $conn
 * @param string $token
 * @param string $client_nonce
 * @param string $client_ip
 * @return bool
 */
function selfreg_bind_first_scan($conn, $token, $client_nonce, $client_ip) {
    if (!$conn || empty($token)) {
        return false;
    }

    $now = time();
    $stmt = mysqli_prepare($conn, "UPDATE eg_selfreg_tokens SET status = 'scanned', first_scanned_at = COALESCE(first_scanned_at, ?), first_scanned_ip = COALESCE(first_scanned_ip, ?), client_nonce = COALESCE(client_nonce, ?) WHERE token = ? AND status = 'active'");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "isss", $now, $client_ip, $client_nonce, $token);
        mysqli_stmt_execute($stmt);
        $affected = mysqli_stmt_affected_rows($stmt);
        mysqli_stmt_close($stmt);
        return $affected > 0;
    }
    return false;
}

/**
 * Atomically consume token upon successful user registration.
 *
 * @param mysqli $conn
 * @param string $token
 * @param string $username
 * @param string $fullname
 * @return bool
 */
function selfreg_consume_token($conn, $token, $username, $fullname) {
    if (!$conn || empty($token)) {
        return false;
    }

    $now = time();
    $stmt = mysqli_prepare($conn, "UPDATE eg_selfreg_tokens SET status = 'used', used_at = ?, registered_username = ?, registered_name = ? WHERE token = ? AND status IN ('active', 'scanned')");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "isss", $now, $username, $fullname, $token);
        mysqli_stmt_execute($stmt);
        $affected = mysqli_stmt_affected_rows($stmt);
        mysqli_stmt_close($stmt);
        return $affected > 0;
    }
    return false;
}

/**
 * Retrieve status info for real-time counter polling.
 *
 * @param mysqli $conn
 * @param string $token
 * @return array
 */
function selfreg_get_status($conn, $token) {
    $validation = selfreg_validate_token($conn, $token);
    $data = $validation['data'];

    if (!$data) {
        return [
            'status' => 'not_found',
            'is_valid' => false,
            'message' => $validation['message'],
            'expires_in' => 0
        ];
    }

    $now = time();
    $expires_in = max(0, (int)$data['expires_at'] - $now);

    return [
        'status' => $data['status'],
        'is_valid' => $validation['valid'],
        'message' => $validation['message'],
        'created_at' => (int)$data['created_at'],
        'expires_at' => (int)$data['expires_at'],
        'expires_in' => $expires_in,
        'first_scanned_at' => $data['first_scanned_at'] ? (int)$data['first_scanned_at'] : null,
        'used_at' => $data['used_at'] ? (int)$data['used_at'] : null,
        'registered_username' => $data['registered_username'] ?? '',
        'registered_name' => $data['registered_name'] ?? ''
    ];
}

/**
 * Periodic cleanup of stale expired tokens older than 24 hours.
 *
 * @param mysqli $conn
 * @return int Number of deleted rows
 */
function selfreg_cleanup($conn) {
    if (!$conn) return 0;
    $threshold = time() - 86400; // 24 hours ago
    $stmt = mysqli_prepare($conn, "DELETE FROM eg_selfreg_tokens WHERE expires_at < ?");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $threshold);
        mysqli_stmt_execute($stmt);
        $deleted = mysqli_stmt_affected_rows($stmt);
        mysqli_stmt_close($stmt);
        return max(0, $deleted);
    }
    return 0;
}

/**
 * Discover server LAN / Wi-Fi IPv4 addresses across Windows, Linux, and macOS.
 *
 * @return array List of valid non-loopback IPv4 strings (e.g. ['192.168.1.100', '10.110.1.62'])
 */
function selfreg_get_server_lan_ips() {
    $ips = [];

    // 1. Check SERVER_ADDR if present and non-loopback
    if (!empty($_SERVER['SERVER_ADDR'])) {
        $srv = trim($_SERVER['SERVER_ADDR']);
        if (filter_var($srv, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) && !str_starts_with($srv, '127.')) {
            $ips[] = $srv;
        }
    }

    // 2. OS-specific network discovery
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        @exec('ipconfig', $output);
        if (!empty($output) && is_array($output)) {
            foreach ($output as $line) {
                if (preg_match('/IPv4 Address[ .]*:[ ]*([0-9]+\.[0-9]+\.[0-9]+\.[0-9]+)/i', $line, $m)) {
                    if ($m[1] !== '127.0.0.1' && !str_starts_with($m[1], '127.')) {
                        $ips[] = $m[1];
                    }
                }
            }
        }
    } else {
        @exec('ifconfig 2>/dev/null || ip addr 2>/dev/null || hostname -I 2>/dev/null', $output);
        if (!empty($output) && is_array($output)) {
            foreach ($output as $line) {
                if (preg_match_all('/(?:inet\s+|addr:|\s|^)([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3})/i', $line, $matches)) {
                    foreach ($matches[1] as $candidate) {
                        if (filter_var($candidate, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) 
                            && !str_starts_with($candidate, '127.') 
                            && !str_starts_with($candidate, '169.254.') 
                            && !str_ends_with($candidate, '.255')
                            && !str_ends_with($candidate, '.0')
                            && $candidate !== '0.0.0.0' 
                            && $candidate !== '255.255.255.255') {
                            $ips[] = $candidate;
                        }
                    }
                }
            }
        }
    }

    // 3. UDP socket routing probe (works without sending actual network packets)
    $sock = @fsockopen('udp://8.8.8.8', 53, $errno, $errstr, 1);
    if ($sock) {
        $name = @stream_socket_get_name($sock, false);
        if ($name) {
            $udp_ip = explode(':', $name)[0];
            if (filter_var($udp_ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) && !str_starts_with($udp_ip, '127.')) {
                $ips[] = $udp_ip;
            }
        }
        @fclose($sock);
    }

    // 4. Hostname resolution fallback
    $hostname = @gethostname();
    if ($hostname) {
        $host_ip = @gethostbyname($hostname);
        if (filter_var($host_ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) && !str_starts_with($host_ip, '127.')) {
            $ips[] = $host_ip;
        }
    }

    // Clean, unique, and filter
    $unique_ips = [];
    foreach ($ips as $ip) {
        $ip = trim($ip);
        if (!empty($ip) && !in_array($ip, $unique_ips, true)) {
            $unique_ips[] = $ip;
        }
    }

    return $unique_ips;
}

/**
 * Construct the best external registration URL for walk-in QR scanning.
 * Replaces localhost / 127.0.0.1 with the detected LAN IP while preserving port and path.
 *
 * @param string $token
 * @param string|null $custom_host
 * @return array ['url' => string, 'host' => string, 'is_lan' => bool, 'detected_ips' => array, ...]
 */
function selfreg_get_best_registration_url($token, $custom_host = null) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || ($_SERVER['SERVER_PORT'] ?? 0) == 443) ? "https://" : "http://";
    $current_http_host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $port = $_SERVER['SERVER_PORT'] ?? 80;
    
    // Extract port if specified in HTTP_HOST
    $host_parts = explode(':', $current_http_host);
    $host_name = $host_parts[0];
    $host_port = isset($host_parts[1]) ? ':' . $host_parts[1] : (($port != 80 && $port != 443) ? ':' . $port : '');

    $detected_ips = selfreg_get_server_lan_ips();
    $chosen_host = '';
    $is_lan = false;

    if (!empty($custom_host)) {
        $chosen_host = trim($custom_host);
        $is_lan = true;
    } elseif ($host_name === 'localhost' || $host_name === '127.0.0.1' || $host_name === '::1' || str_starts_with($host_name, '127.')) {
        // If accessed via localhost, use first detected LAN IP if available
        if (!empty($detected_ips)) {
            $chosen_host = $detected_ips[0] . $host_port;
            $is_lan = true;
        } else {
            $chosen_host = $current_http_host;
            $is_lan = false;
        }
    } else {
        $chosen_host = $current_http_host;
        $is_lan = true;
    }

    $script_dir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
    $url = $protocol . $chosen_host . $script_dir . "/adduser.php?token=" . urlencode($token);

    return [
        'url' => $url,
        'host' => $chosen_host,
        'is_lan' => $is_lan,
        'detected_ips' => $detected_ips,
        'script_dir' => $script_dir,
        'protocol' => $protocol,
        'port_suffix' => $host_port,
        'current_http_host' => $current_http_host
    ];
}
