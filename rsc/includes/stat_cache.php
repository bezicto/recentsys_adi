<?php
/**
 * StatCache - High-Performance JSON Caching & Query Optimization Engine
 *
 * Designed for statistical reporting suite (adsreport).
 * Manages atomic JSON caching for historical statistics while ensuring
 * active/current periods are computed live and fast.
 */

defined('includeExist') || die("<div class='auth-card'><span class='badge badge-danger mb-2'>WARNING</span><h2>Forbidden: Direct access prohibited</h2><em class='text-muted'>System Response Code</em></div>");

class StatCache
{
    private static ?string $cache_dir = null;

    /**
     * Resolves and ensures the stats cache directory exists and is writable.
     */
    public static function get_cache_dir(): string
    {
        if (self::$cache_dir !== null && is_dir(self::$cache_dir)) {
            return self::$cache_dir;
        }

        global $stat_cache_directory;

        $target_dir = '';
        if (!empty($_SESSION['parent_dir'])) {
            $target_dir = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . $_SESSION['parent_dir'] . DIRECTORY_SEPARATOR . 'stats';
        } elseif (!empty($stat_cache_directory)) {
            $target_dir = realpath($stat_cache_directory) ?: $stat_cache_directory;
        } else {
            $target_dir = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'site' . DIRECTORY_SEPARATOR . 'stats';
        }

        if (!is_dir($target_dir)) {
            @mkdir($target_dir, 0775, true);
        }

        // Place an index.php / safety file if missing
        $index_file = $target_dir . DIRECTORY_SEPARATOR . 'index.php';
        if (!file_exists($index_file)) {
            @file_put_contents($index_file, "<?php header('Location: ../'); exit; ?>\n");
        }

        self::$cache_dir = $target_dir;
        return self::$cache_dir;
    }

    /**
     * Sanitizes a cache key into a safe filename.
     */
    public static function sanitize_key(string $key): string
    {
        return preg_replace('/[^a-zA-Z0-9_\-]/', '_', $key);
    }

    /**
     * Checks if a month/year is in the past (completed and immutable).
     */
    public static function is_past_month($month, $year): bool
    {
        $m = (int)$month;
        $y = (int)$year;
        $cur_y = (int)date('Y');
        $cur_m = (int)date('n');

        if ($y < $cur_y) {
            return true;
        }
        if ($y === $cur_y && $m < $cur_m) {
            return true;
        }
        return false;
    }

    /**
     * Checks if a month/year is in the future.
     */
    public static function is_future_month($month, $year): bool
    {
        $m = (int)$month;
        $y = (int)$year;
        $cur_y = (int)date('Y');
        $cur_m = (int)date('n');

        if ($y > $cur_y) {
            return true;
        }
        if ($y === $cur_y && $m > $cur_m) {
            return true;
        }
        return false;
    }

    /**
     * Checks if a year is strictly in the past.
     */
    public static function is_past_year($year): bool
    {
        return ((int)$year < (int)date('Y'));
    }

    /**
     * Returns Unix timestamp boundaries [start, end] for a given month and year.
     * Useful for replacing DATE_FORMAT(FROM_UNIXTIME(...)) with fast indexed range scans.
     */
    public static function get_month_timestamp_range($month, $year): array
    {
        $m = (int)$month;
        $y = (int)$year;
        $start = mktime(0, 0, 0, $m, 1, $y);
        $days_in_month = (int)date('t', $start);
        $end = mktime(23, 59, 59, $m, $days_in_month, $y);

        return [
            'start' => $start,
            'end' => $end
        ];
    }

    /**
     * Returns Unix timestamp boundaries [start, end] for a full year.
     */
    public static function get_year_timestamp_range($year): array
    {
        $y = (int)$year;
        return [
            'start' => mktime(0, 0, 0, 1, 1, $y),
            'end' => mktime(23, 59, 59, 12, 31, $y)
        ];
    }

    /**
     * Retrieves cached data from JSON file.
     */
    public static function get(string $key)
    {
        $safe_key = self::sanitize_key($key);
        $file = self::get_cache_dir() . DIRECTORY_SEPARATOR . $safe_key . '.json';

        if (!file_exists($file) || !is_readable($file)) {
            return null;
        }

        $raw = @file_get_contents($file);
        if ($raw === false || $raw === '') {
            return null;
        }

        $decoded = json_decode($raw, true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
            return null;
        }

        return $decoded['data'] ?? null;
    }

    /**
     * Writes data to a JSON cache file atomically using temp file + rename.
     * Prevents partial/corrupt reads during concurrent peak hours.
     */
    public static function put(string $key, $data): bool
    {
        $dir = self::get_cache_dir();
        $safe_key = self::sanitize_key($key);
        $target_file = $dir . DIRECTORY_SEPARATOR . $safe_key . '.json';

        $payload = [
            'key' => $key,
            'cached_at' => time(),
            'cached_date' => date('Y-m-d H:i:s'),
            'data' => $data
        ];

        $json = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if ($json === false) {
            return false;
        }

        $tmp_file = @tempnam($dir, 'stat_tmp_');
        if ($tmp_file === false) {
            return (@file_put_contents($target_file, $json, LOCK_EX) !== false);
        }

        if (@file_put_contents($tmp_file, $json) === false) {
            @unlink($tmp_file);
            return false;
        }

        @chmod($tmp_file, 0664);
        $renamed = @rename($tmp_file, $target_file);
        if (!$renamed) {
            @unlink($tmp_file);
            return (@file_put_contents($target_file, $json, LOCK_EX) !== false);
        }

        return true;
    }

    /**
     * Clears all JSON stats cache files.
     */
    public static function clear_all(): int
    {
        $dir = self::get_cache_dir();
        $files = glob($dir . DIRECTORY_SEPARATOR . '*.json');
        $deleted = 0;

        if ($files) {
            foreach ($files as $f) {
                if (is_file($f) && @unlink($f)) {
                    $deleted++;
                }
            }
        }

        return $deleted;
    }

    /**
     * Returns statistics about the current cache storage.
     */
    public static function get_stats(): array
    {
        $dir = self::get_cache_dir();
        $files = glob($dir . DIRECTORY_SEPARATOR . '*.json') ?: [];
        $total_size = 0;

        foreach ($files as $f) {
            if (is_file($f)) {
                $total_size += filesize($f);
            }
        }

        return [
            'dir' => $dir,
            'count' => count($files),
            'size_bytes' => $total_size,
            'size_formatted' => self::format_bytes($total_size)
        ];
    }

    private static function format_bytes(int $bytes): string
    {
        if ($bytes < 1024) return $bytes . ' B';
        if ($bytes < 1048576) return round($bytes / 1024, 2) . ' KB';
        return round($bytes / 1048576, 2) . ' MB';
    }

    /**
     * Smart caching for monthly stats:
     * - Past month: serves from cache, or runs callback and caches permanently.
     * - Current month: runs callback LIVE (does not cache).
     * - Future month: returns default $future_default without running heavy queries.
     *
     * @return array [ 'data' => mixed, 'is_cached' => bool, 'is_future' => bool ]
     */
    public static function remember_month(string $key, $month, $year, callable $live_callback, $future_default = 0): array
    {
        $m = (int)$month;
        $y = (int)$year;

        if (self::is_future_month($m, $y)) {
            return [
                'data' => $future_default,
                'is_cached' => false,
                'is_future' => true
            ];
        }

        if (self::is_past_month($m, $y)) {
            $cached = self::get($key);
            if ($cached !== null) {
                return [
                    'data' => $cached,
                    'is_cached' => true,
                    'is_future' => false
                ];
            }

            // Generate live and cache
            $data = $live_callback();
            self::put($key, $data);
            return [
                'data' => $data,
                'is_cached' => false,
                'is_future' => false
            ];
        }

        // Current month: Always fetch live
        $data = $live_callback();
        return [
            'data' => $data,
            'is_cached' => false,
            'is_future' => false
        ];
    }

    /**
     * Smart hybrid 12-month array generator for a given year:
     * - If past year: caches and returns entire 12-month array from JSON.
     * - If current year: loads past months from JSON cache, executes live callback for current month, sets future months to 0.
     * - If future year: returns 12 zeros with 0 queries.
     *
     * Callback signature: function(int $month, int $year): mixed
     *
     * @return array [ 'months' => array (1..12 indexed 0..11), 'total' => numeric, 'is_cached' => bool ]
     */
    public static function remember_year_breakdown(string $key_prefix, $year, callable $query_month_callback): array
    {
        $y = (int)$year;
        $cur_y = (int)date('Y');
        $cur_m = (int)date('n');

        // Case 1: Future year
        if ($y > $cur_y) {
            $months = array_fill(0, 12, 0);
            return [
                'months' => $months,
                'total' => 0,
                'is_cached' => false
            ];
        }

        // Case 2: Past year - can cache entire 12-month set in one file
        if ($y < $cur_y) {
            $full_key = $key_prefix . '_full_' . $y;
            $cached = self::get($full_key);
            if ($cached !== null && isset($cached['months'])) {
                return [
                    'months' => $cached['months'],
                    'total' => $cached['total'] ?? array_sum($cached['months']),
                    'is_cached' => true
                ];
            }

            $months = [];
            $total = 0;
            for ($m = 1; $m <= 12; $m++) {
                $val = $query_month_callback($m, $y);
                $months[] = $val;
                $total += (is_numeric($val) ? $val : 0);
            }

            $result = [
                'months' => $months,
                'total' => $total
            ];
            self::put($full_key, $result);

            return [
                'months' => $months,
                'total' => $total,
                'is_cached' => false
            ];
        }

        // Case 3: Current year - Hybrid aggregation
        $months = [];
        $total = 0;
        $all_cached = true;

        for ($m = 1; $m <= 12; $m++) {
            if ($m < $cur_m) {
                // Past month of current year - use individual monthly cache
                $month_key = $key_prefix . '_m' . sprintf('%02d', $m) . '_' . $y;
                $cached_val = self::get($month_key);
                if ($cached_val !== null) {
                    $months[] = $cached_val;
                    $total += (is_numeric($cached_val) ? $cached_val : 0);
                } else {
                    $val = $query_month_callback($m, $y);
                    self::put($month_key, $val);
                    $months[] = $val;
                    $total += (is_numeric($val) ? $val : 0);
                    $all_cached = false;
                }
            } elseif ($m === $cur_m) {
                // Current active month - live compute
                $val = $query_month_callback($m, $y);
                $months[] = $val;
                $total += (is_numeric($val) ? $val : 0);
                $all_cached = false;
            } else {
                // Future month of current year - 0 with zero queries
                $months[] = 0;
            }
        }

        return [
            'months' => $months,
            'total' => $total,
            'is_cached' => $all_cached
        ];
    }

    /**
     * Helper to render visual badge indicating cache vs live computation status.
     */
    public static function render_badge(bool $is_cached, string $extra_class = ''): string
    {
        if ($is_cached) {
            return "<span class='badge badge-success {$extra_class}' title='Served instantly from JSON cache'><i class='fa-solid fa-bolt me-1'></i>Cached</span>";
        }
        return "<span class='badge badge-info {$extra_class}' title='Computed live from database'><i class='fa-solid fa-circle-dot me-1'></i>Live</span>";
    }
}
