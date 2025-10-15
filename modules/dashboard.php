<?php
// Security: Prevent direct access
if (!defined('PANEL_ACCESS')) {
    die('Direct access not permitted');
}

// Get system information
$uptime = getUptime();
$cpu = getCPUUsage();
$memory = getMemoryUsage();
$disk = getDiskUsage('/');
$loadAvg = function_exists('sys_getloadavg') ? @sys_getloadavg() : [0, 0, 0];

// Get service statuses
$services = [];
foreach (SERVICES as $service => $name) {
    $services[$service] = getServiceStatus($service);
}

// Get PHP version
$phpVersion = phpversion();

// Get server software
$serverSoftware = $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown';

// Check if running on Windows
$isWindows = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');
?>

<?php if ($isWindows): ?>
<div class="alert alert-warning">
    <strong>⚠️ Development Mode:</strong> You're running on Windows (XAMPP). Some features like system monitoring are limited. Deploy to Linux VPS for full functionality.
</div>
<?php endif; ?>

<div class="card-grid">
    <div class="stat-card success">
        <div class="stat-label">⏱️ System Uptime</div>
        <div class="stat-value"><?php echo $uptime; ?></div>
        <div class="stat-subtitle">Server running time</div>
    </div>
    
    <div class="stat-card <?php echo $cpu > 80 ? 'danger' : ($cpu > 60 ? 'warning' : 'info'); ?>">
        <div class="stat-label">🔥 CPU Usage</div>
        <div class="stat-value"><?php echo $cpu; ?>%</div>
        <div class="stat-subtitle">Load: <?php echo implode(', ', array_map(fn($v) => round($v, 2), $loadAvg)); ?></div>
    </div>
    
    <div class="stat-card <?php echo $memory['percent'] > 80 ? 'danger' : ($memory['percent'] > 60 ? 'warning' : 'success'); ?>">
        <div class="stat-label">💾 Memory Usage</div>
        <div class="stat-value"><?php echo $memory['percent']; ?>%</div>
        <div class="stat-subtitle"><?php echo $memory['used']; ?> MB / <?php echo $memory['total']; ?> MB</div>
    </div>
    
    <div class="stat-card <?php echo $disk['percent'] > 80 ? 'danger' : ($disk['percent'] > 60 ? 'warning' : 'success'); ?>">
        <div class="stat-label">💿 Disk Usage</div>
        <div class="stat-value"><?php echo $disk['percent']; ?>%</div>
        <div class="stat-subtitle"><?php echo $disk['used']; ?> / <?php echo $disk['total']; ?></div>
    </div>
</div>

<div class="card">
    <div class="card-header">📊 System Information</div>
    <div class="card-body">
        <table class="table">
            <tr>
                <th>Property</th>
                <th>Value</th>
            </tr>
            <tr>
                <td>Server IP</td>
                <td><strong><?php echo SERVER_IP; ?></strong></td>
            </tr>
            <tr>
                <td>Operating System</td>
                <td><?php echo php_uname('s') . ' ' . php_uname('r'); ?></td>
            </tr>
            <tr>
                <td>Hostname</td>
                <td><?php echo php_uname('n'); ?></td>
            </tr>
            <tr>
                <td>PHP Version</td>
                <td><?php echo $phpVersion; ?></td>
            </tr>
            <tr>
                <td>Web Server</td>
                <td><?php echo $serverSoftware; ?></td>
            </tr>
            <tr>
                <td>Server Root</td>
                <td><?php echo SERVER_ROOT; ?></td>
            </tr>
        </table>
    </div>
</div>

<div class="card">
    <div class="card-header">⚙️ Service Status</div>
    <div class="card-body">
        <table class="table">
            <thead>
                <tr>
                    <th>Service</th>
                    <th>Status</th>
                    <th>Auto-Start</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($services as $service => $status): ?>
                <tr>
                    <td><strong><?php echo SERVICES[$service]; ?></strong></td>
                    <td>
                        <?php if ($status['active']): ?>
                            <span class="badge badge-success">Running</span>
                        <?php else: ?>
                            <span class="badge badge-danger">Stopped</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($status['enabled']): ?>
                            <span class="badge badge-info">Enabled</span>
                        <?php else: ?>
                            <span class="badge badge-warning">Disabled</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="<?php echo buildUrl('services', ['service' => $service]); ?>" class="btn btn-sm btn-info">
                            Manage
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <div class="card-header">🚀 Quick Actions</div>
    <div class="card-body">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
            <a href="<?php echo buildUrl('terminal'); ?>" class="btn btn-primary">
                💻 Open Terminal
            </a>
            <a href="<?php echo buildUrl('files'); ?>" class="btn btn-info">
                📁 Browse Files
            </a>
            <a href="<?php echo buildUrl('monitor'); ?>" class="btn btn-success">
                📈 System Monitor
            </a>
            <a href="<?php echo buildUrl('websites'); ?>" class="btn btn-warning">
                🌐 Manage Websites
            </a>
            <a href="<?php echo buildUrl('logs'); ?>" class="btn btn-secondary">
                📝 View Logs
            </a>
            <a href="<?php echo buildUrl('database'); ?>" class="btn btn-danger">
                🗄️ Database
            </a>
        </div>
    </div>
</div>

<script>
// Auto-refresh dashboard every 5 seconds
setTimeout(() => {
    location.reload();
}, 5000);
</script>
