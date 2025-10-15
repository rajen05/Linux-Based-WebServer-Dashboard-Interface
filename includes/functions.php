<?php
/**
 * Shared Functions
 */

// Security: Prevent direct access
if (!defined('PANEL_ACCESS')) {
    die('Direct access not permitted');
}

/**
 * Execute shell command safely
 */
function execCommand($command, &$output = null, &$return_var = null) {
    $output = [];
    exec($command . ' 2>&1', $output, $return_var);
    return implode("\n", $output);
}

/**
 * Format file size
 */
function formatSize($bytes) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    return round($bytes, 2) . ' ' . $units[$pow];
}

/**
 * Get system uptime
 */
function getUptime() {
    // Check if running on Windows
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        return 'Windows (N/A)';
    }
    
    $uptime = @file_get_contents('/proc/uptime');
    if ($uptime) {
        $uptime = explode(' ', $uptime)[0];
        $days = floor($uptime / 86400);
        $hours = floor(($uptime % 86400) / 3600);
        $minutes = floor(($uptime % 3600) / 60);
        return "{$days}d {$hours}h {$minutes}m";
    }
    return 'Unknown';
}

/**
 * Get CPU usage
 */
function getCPUUsage() {
    // Check if running on Windows
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        return 0; // Return 0 on Windows (not supported)
    }
    
    $load = @sys_getloadavg();
    return $load ? round($load[0] * 100 / 4, 2) : 0; // Assuming 4 cores
}

/**
 * Get memory usage
 */
function getMemoryUsage() {
    // Check if running on Windows
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        return ['total' => 0, 'used' => 0, 'free' => 0, 'percent' => 0];
    }
    
    $meminfo = @file_get_contents('/proc/meminfo');
    if ($meminfo) {
        preg_match('/MemTotal:\s+(\d+)/', $meminfo, $total);
        preg_match('/MemAvailable:\s+(\d+)/', $meminfo, $available);
        
        if ($total && $available) {
            $total_mb = $total[1] / 1024;
            $available_mb = $available[1] / 1024;
            $used_mb = $total_mb - $available_mb;
            $percent = round(($used_mb / $total_mb) * 100, 2);
            
            return [
                'total' => round($total_mb, 2),
                'used' => round($used_mb, 2),
                'free' => round($available_mb, 2),
                'percent' => $percent
            ];
        }
    }
    return ['total' => 0, 'used' => 0, 'free' => 0, 'percent' => 0];
}

/**
 * Get disk usage
 */
function getDiskUsage($path = '/') {
    // On Windows, use C: drive if Linux path doesn't exist
    if (!file_exists($path)) {
        $path = 'C:';
    }
    
    $total = @disk_total_space($path);
    $free = @disk_free_space($path);
    
    if ($total === false || $free === false || $total == 0) {
        return [
            'total' => '0 B',
            'used' => '0 B',
            'free' => '0 B',
            'percent' => 0
        ];
    }
    
    $used = $total - $free;
    $percent = round(($used / $total) * 100, 2);
    
    return [
        'total' => formatSize($total),
        'used' => formatSize($used),
        'free' => formatSize($free),
        'percent' => $percent
    ];
}

/**
 * Check if service is running
 */
function isServiceRunning($service) {
    $output = execCommand("systemctl is-active $service");
    return trim($output) === 'active';
}

/**
 * Get service status
 */
function getServiceStatus($service) {
    $active = isServiceRunning($service);
    $enabled = trim(execCommand("systemctl is-enabled $service")) === 'enabled';
    
    return [
        'active' => $active,
        'enabled' => $enabled,
        'status' => $active ? 'running' : 'stopped'
    ];
}

/**
 * Sanitize path
 */
function sanitizePath($path) {
    $path = str_replace("\0", '', $path);
    $realPath = realpath($path);
    
    if ($realPath === false) {
        $realPath = realpath(dirname($path));
    }
    
    $rootReal = realpath(SERVER_ROOT);
    if ($realPath === false || strpos($realPath, $rootReal) !== 0) {
        return $rootReal;
    }
    
    return $realPath;
}

/**
 * Get file extension
 */
function getExtension($filename) {
    return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
}

/**
 * Format permissions
 */
function formatPermissions($filepath) {
    $perms = fileperms($filepath);
    
    $info = '';
    $info .= (($perms & 0x0100) ? 'r' : '-');
    $info .= (($perms & 0x0080) ? 'w' : '-');
    $info .= (($perms & 0x0040) ? 'x' : '-');
    $info .= (($perms & 0x0020) ? 'r' : '-');
    $info .= (($perms & 0x0010) ? 'w' : '-');
    $info .= (($perms & 0x0008) ? 'x' : '-');
    $info .= (($perms & 0x0004) ? 'r' : '-');
    $info .= (($perms & 0x0002) ? 'w' : '-');
    $info .= (($perms & 0x0001) ? 'x' : '-');
    
    return $info . ' (' . substr(sprintf('%o', $perms), -4) . ')';
}

/**
 * Get current module
 */
function getCurrentModule() {
    return isset($_GET['module']) ? $_GET['module'] : 'dashboard';
}

/**
 * Build URL
 */
function buildUrl($module, $params = []) {
    $url = 'index.php?module=' . urlencode($module);
    foreach ($params as $key => $value) {
        $url .= '&' . urlencode($key) . '=' . urlencode($value);
    }
    return $url;
}

/**
 * Show alert
 */
function showAlert($message, $type = 'info') {
    return '<div class="alert alert-' . $type . '">' . htmlspecialchars($message) . '</div>';
}
