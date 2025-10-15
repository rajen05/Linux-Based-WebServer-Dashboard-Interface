<?php
// Security: Prevent direct access
if (!defined('PANEL_ACCESS')) {
    die('Direct access not permitted');
}

$currentModule = getCurrentModule();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo PANEL_TITLE; ?> - <?php echo ucfirst($currentModule); ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <h2>🖥️ Server Panel</h2>
            <p class="server-ip"><?php echo SERVER_IP; ?></p>
        </div>
        
        <nav class="sidebar-nav">
            <a href="<?php echo buildUrl('dashboard'); ?>" class="nav-item <?php echo $currentModule === 'dashboard' ? 'active' : ''; ?>">
                <span class="nav-icon">📊</span>
                <span class="nav-text">Dashboard</span>
            </a>
            
            <a href="<?php echo buildUrl('terminal'); ?>" class="nav-item <?php echo $currentModule === 'terminal' ? 'active' : ''; ?>">
                <span class="nav-icon">💻</span>
                <span class="nav-text">Terminal</span>
            </a>
            
            <a href="<?php echo buildUrl('monitor'); ?>" class="nav-item <?php echo $currentModule === 'monitor' ? 'active' : ''; ?>">
                <span class="nav-icon">📈</span>
                <span class="nav-text">System Monitor</span>
            </a>
            
            <a href="<?php echo buildUrl('files'); ?>" class="nav-item <?php echo $currentModule === 'files' ? 'active' : ''; ?>">
                <span class="nav-icon">📁</span>
                <span class="nav-text">File Manager</span>
            </a>
            
            <a href="<?php echo buildUrl('websites'); ?>" class="nav-item <?php echo $currentModule === 'websites' ? 'active' : ''; ?>">
                <span class="nav-icon">🌐</span>
                <span class="nav-text">Websites</span>
            </a>
            
            <a href="<?php echo buildUrl('services'); ?>" class="nav-item <?php echo $currentModule === 'services' ? 'active' : ''; ?>">
                <span class="nav-icon">⚙️</span>
                <span class="nav-text">Services</span>
            </a>
            
            <a href="<?php echo buildUrl('database'); ?>" class="nav-item <?php echo $currentModule === 'database' ? 'active' : ''; ?>">
                <span class="nav-icon">🗄️</span>
                <span class="nav-text">Database</span>
            </a>
            
            <a href="<?php echo buildUrl('logs'); ?>" class="nav-item <?php echo $currentModule === 'logs' ? 'active' : ''; ?>">
                <span class="nav-icon">📝</span>
                <span class="nav-text">Logs</span>
            </a>
        </nav>
        
        <div class="sidebar-footer">
            <a href="?logout" class="btn btn-danger btn-block">
                <span>🚪</span> Logout
            </a>
        </div>
    </div>
    
    <div class="main-content">
        <div class="topbar">
            <h1><?php echo ucfirst($currentModule); ?></h1>
            <div class="topbar-right">
                <span class="user-info">
                    👤 Admin | 
                    ⏱️ Uptime: <?php echo getUptime(); ?>
                </span>
            </div>
        </div>
        
        <div class="content-wrapper">
