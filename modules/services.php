<?php
// Security: Prevent direct access
if (!defined('PANEL_ACCESS')) {
    die('Direct access not permitted');
}

$message = '';
$messageType = '';

/**
 * Auto-detect installed services
 */
function getInstalledServices() {
    // Check if running on Windows
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        // Return predefined services for Windows
        return SERVICES;
    }
    
    // Get list of all services
    $output = [];
    exec("systemctl list-unit-files --type=service --state=enabled,disabled --no-pager 2>&1", $output);
    
    $detectedServices = [];
    
    // Common server services to look for
    $commonServices = [
        'apache2' => 'Apache Web Server',
        'nginx' => 'Nginx Web Server',
        'mysql' => 'MySQL Database',
        'mariadb' => 'MariaDB Database',
        'postgresql' => 'PostgreSQL Database',
        'redis' => 'Redis Cache',
        'redis-server' => 'Redis Server',
        'memcached' => 'Memcached',
        'mongodb' => 'MongoDB',
        'mongod' => 'MongoDB Daemon',
        'php8.1-fpm' => 'PHP-FPM 8.1',
        'php8.0-fpm' => 'PHP-FPM 8.0',
        'php7.4-fpm' => 'PHP-FPM 7.4',
        'php-fpm' => 'PHP-FPM',
        'docker' => 'Docker',
        'containerd' => 'Containerd',
        'ssh' => 'SSH Server',
        'sshd' => 'SSH Daemon',
        'fail2ban' => 'Fail2Ban',
        'ufw' => 'UFW Firewall',
        'firewalld' => 'Firewalld',
        'cron' => 'Cron Scheduler',
        'postfix' => 'Postfix Mail',
        'dovecot' => 'Dovecot IMAP/POP3',
        'vsftpd' => 'FTP Server',
        'proftpd' => 'ProFTPD Server',
        'bind9' => 'BIND DNS Server',
        'named' => 'Named DNS Server',
        'elasticsearch' => 'Elasticsearch',
        'rabbitmq-server' => 'RabbitMQ',
    ];
    
    // Parse output and find matching services
    foreach ($output as $line) {
        foreach ($commonServices as $serviceName => $displayName) {
            if (strpos($line, $serviceName . '.service') !== false) {
                $detectedServices[$serviceName] = $displayName;
            }
        }
    }
    
    // If no services detected, fall back to config
    if (empty($detectedServices)) {
        return SERVICES;
    }
    
    // Sort by display name
    asort($detectedServices);
    
    return $detectedServices;
}

// Handle service actions
if (isset($_POST['action']) && isset($_POST['service'])) {
    $service = $_POST['service'];
    $action = $_POST['action'];
    
    $validActions = ['start', 'stop', 'restart', 'enable', 'disable'];
    
    if (in_array($action, $validActions)) {
        $command = "systemctl $action $service 2>&1";
        $output = execCommand($command);
        
        $message = "Service action '$action' executed on $service";
        $messageType = 'success';
    }
}

// Get all installed services
$installedServices = getInstalledServices();

// Get all service statuses
$services = [];
foreach ($installedServices as $service => $name) {
    $services[$service] = [
        'name' => $name,
        'status' => getServiceStatus($service)
    ];
}
?>

<?php if ($message): ?>
    <div class="alert alert-<?php echo $messageType; ?>">
        <?php echo htmlspecialchars($message); ?>
    </div>
<?php endif; ?>

<div class="alert alert-info">
    <strong>🔍 Auto-Detection Enabled:</strong> This page automatically detects installed services on your system. 
    <?php if (count($services) > 0): ?>
        Found <strong><?php echo count($services); ?> service(s)</strong>.
    <?php endif; ?>
    Install new services via Terminal and refresh this page to see them!
</div>

<div class="card">
    <div class="card-header">⚙️ Service Management</div>
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
                <?php foreach ($services as $service => $info): ?>
                <tr>
                    <td>
                        <strong><?php echo htmlspecialchars($info['name']); ?></strong>
                        <br>
                        <small style="color: #666;"><?php echo htmlspecialchars($service); ?></small>
                    </td>
                    <td>
                        <?php if ($info['status']['active']): ?>
                            <span class="badge badge-success">● Running</span>
                        <?php else: ?>
                            <span class="badge badge-danger">○ Stopped</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($info['status']['enabled']): ?>
                            <span class="badge badge-info">Enabled</span>
                        <?php else: ?>
                            <span class="badge badge-warning">Disabled</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div style="display: flex; gap: 5px; flex-wrap: wrap;">
                            <?php if (!$info['status']['active']): ?>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="service" value="<?php echo htmlspecialchars($service); ?>">
                                    <input type="hidden" name="action" value="start">
                                    <button type="submit" class="btn btn-sm btn-success" data-confirm="Start <?php echo htmlspecialchars($info['name']); ?>?">
                                        ▶ Start
                                    </button>
                                </form>
                            <?php else: ?>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="service" value="<?php echo htmlspecialchars($service); ?>">
                                    <input type="hidden" name="action" value="stop">
                                    <button type="submit" class="btn btn-sm btn-danger" data-confirm="Stop <?php echo htmlspecialchars($info['name']); ?>?">
                                        ■ Stop
                                    </button>
                                </form>
                            <?php endif; ?>
                            
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="service" value="<?php echo htmlspecialchars($service); ?>">
                                <input type="hidden" name="action" value="restart">
                                <button type="submit" class="btn btn-sm btn-warning" data-confirm="Restart <?php echo htmlspecialchars($info['name']); ?>?">
                                    ↻ Restart
                                </button>
                            </form>
                            
                            <?php if (!$info['status']['enabled']): ?>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="service" value="<?php echo htmlspecialchars($service); ?>">
                                    <input type="hidden" name="action" value="enable">
                                    <button type="submit" class="btn btn-sm btn-info" data-confirm="Enable auto-start for <?php echo htmlspecialchars($info['name']); ?>?">
                                        Enable
                                    </button>
                                </form>
                            <?php else: ?>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="service" value="<?php echo htmlspecialchars($service); ?>">
                                    <input type="hidden" name="action" value="disable">
                                    <button type="submit" class="btn btn-sm btn-secondary" data-confirm="Disable auto-start for <?php echo htmlspecialchars($info['name']); ?>?">
                                        Disable
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="alert alert-info">
    <strong>ℹ️ Service Control Tips:</strong>
    <ul style="margin: 10px 0 0 20px;">
        <li><strong>Start:</strong> Starts a stopped service</li>
        <li><strong>Stop:</strong> Stops a running service</li>
        <li><strong>Restart:</strong> Restarts the service (useful after config changes)</li>
        <li><strong>Enable:</strong> Service will start automatically on boot</li>
        <li><strong>Disable:</strong> Service will not start automatically on boot</li>
    </ul>
</div>

<div class="card">
    <div class="card-header">📝 Quick Service Commands</div>
    <div class="card-body">
        <p>You can also manage services via the <a href="<?php echo buildUrl('terminal'); ?>">Terminal</a>:</p>
        <pre style="background: #2d2d2d; color: #d4d4d4; padding: 15px; border-radius: 5px; font-size: 13px; border: 1px solid #444;">
# Check service status
systemctl status apache2

# View service logs
journalctl -u apache2 -n 50

# Reload service configuration
systemctl reload apache2

# View all services
systemctl list-units --type=service
        </pre>
    </div>
</div>
