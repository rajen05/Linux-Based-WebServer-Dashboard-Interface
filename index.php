<?php
/**
 * Server Control Panel - Main Entry Point
 * 
 * A comprehensive web-based control panel for Linux VPS management
 * 
 * Features:
 * - Dashboard with system overview
 * - Web-based terminal
 * - System monitoring (CPU, RAM, Disk)
 * - File manager
 * - Website/virtual host management
 * - Service control (Apache, Nginx, MySQL, PHP)
 * - Database management
 * - Log viewer
 */

// Define access constant
define('PANEL_ACCESS', true);

// Load configuration
require_once 'config.php';

// Load authentication
require_once 'auth.php';

// Load shared functions
require_once 'includes/functions.php';

// Require authentication
requireAuth();

// Get current module
$module = getCurrentModule();

// Validate module
$validModules = ['dashboard', 'terminal', 'monitor', 'files', 'websites', 'services', 'database', 'logs'];
if (!in_array($module, $validModules)) {
    $module = 'dashboard';
}

// Load header
require_once 'includes/header.php';

// Load module
$modulePath = "modules/{$module}.php";
if (file_exists($modulePath)) {
    require_once $modulePath;
} else {
    echo '<div class="alert alert-error">Module not found</div>';
}

// Load footer
require_once 'includes/footer.php';
