<?php
/**
 * Server Control Panel - Configuration
 */

// Security: Prevent direct access
if (!defined('PANEL_ACCESS')) {
    die('Direct access not permitted');
}

// ============================================
// MAIN CONFIGURATION
// ============================================

// Admin password - CHANGE THIS IMMEDIATELY!
define('ADMIN_PASSWORD', 'changeme123');

// Server root directory (change if your web root is different)
define('SERVER_ROOT', '/var/www/html');

// Panel title
define('PANEL_TITLE', 'Server Control Panel');

// Server IP - Automatically detected from server environment
// No need to change this - it will detect your server's IP automatically
define('SERVER_IP', $_SERVER['SERVER_ADDR'] ?? $_SERVER['HTTP_HOST'] ?? 'localhost');

// ============================================
// FILE MANAGER SETTINGS
// ============================================

define('MAX_UPLOAD_SIZE', 50 * 1024 * 1024); // 50MB
define('MAX_PREVIEW_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_EXTENSIONS', [
    'txt', 'html', 'htm', 'php', 'css', 'js', 'json', 'xml', 
    'jpg', 'jpeg', 'png', 'gif', 'svg', 'pdf', 'zip', 'tar', 'gz',
    'md', 'log', 'conf', 'ini', 'yml', 'yaml', 'sh'
]);

// ============================================
// TERMINAL SETTINGS
// ============================================

define('TERMINAL_ENABLED', true);
define('TERMINAL_TIMEOUT', 30); // seconds

// ============================================
// SERVICE SETTINGS
// ============================================

// Services to manage
define('SERVICES', [
    'apache2' => 'Apache Web Server',
    'nginx' => 'Nginx Web Server',
    'mysql' => 'MySQL Database',
    'mariadb' => 'MariaDB Database',
    'php8.1-fpm' => 'PHP-FPM 8.1',
    'php7.4-fpm' => 'PHP-FPM 7.4',
]);

// ============================================
// LOG FILES
// ============================================

define('LOG_FILES', [
    'Apache Access' => '/var/log/apache2/access.log',
    'Apache Error' => '/var/log/apache2/error.log',
    'Nginx Access' => '/var/log/nginx/access.log',
    'Nginx Error' => '/var/log/nginx/error.log',
    'MySQL Error' => '/var/log/mysql/error.log',
    'System Log' => '/var/log/syslog',
    'Auth Log' => '/var/log/auth.log',
]);

// ============================================
// WEBSITE DIRECTORIES
// ============================================

define('VHOST_DIR', '/etc/apache2/sites-available');
define('NGINX_VHOST_DIR', '/etc/nginx/sites-available');

// ============================================
// DATABASE SETTINGS
// ============================================

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', ''); // Set your MySQL root password

// ============================================
// THEME COLORS
// ============================================

define('PRIMARY_COLOR', '#667eea');
define('SECONDARY_COLOR', '#764ba2');
