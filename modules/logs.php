<?php
// Security: Prevent direct access
if (!defined('PANEL_ACCESS')) {
    die('Direct access not permitted');
}

$selectedLog = isset($_GET['log']) ? $_GET['log'] : 'Apache Error';
$lines = isset($_GET['lines']) ? (int)$_GET['lines'] : 50;
$lines = min(max($lines, 10), 500); // Between 10 and 500

$logContent = '';
$logFile = '';

if (isset(LOG_FILES[$selectedLog])) {
    $logFile = LOG_FILES[$selectedLog];
    
    if (file_exists($logFile) && is_readable($logFile)) {
        $output = [];
        exec("tail -n $lines " . escapeshellarg($logFile) . " 2>&1", $output);
        $logContent = implode("\n", $output);
    } else {
        $logContent = "Log file not found or not readable: $logFile";
    }
}
?>

<div class="card">
    <div class="card-header">
        📝 Log Viewer
        <div style="float: right;">
            <a href="<?php echo buildUrl('logs', ['log' => $selectedLog, 'lines' => $lines]); ?>" class="btn btn-sm btn-info">
                🔄 Refresh
            </a>
        </div>
    </div>
    <div class="card-body">
        <form method="GET" style="margin-bottom: 20px;">
            <input type="hidden" name="module" value="logs">
            <div style="display: grid; grid-template-columns: 1fr 150px auto; gap: 10px;">
                <div class="form-group" style="margin: 0;">
                    <label>Select Log File</label>
                    <select name="log" class="form-control">
                        <?php foreach (LOG_FILES as $name => $path): ?>
                            <option value="<?php echo htmlspecialchars($name); ?>" <?php echo $selectedLog === $name ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group" style="margin: 0;">
                    <label>Lines</label>
                    <input type="number" name="lines" value="<?php echo $lines; ?>" min="10" max="500" class="form-control">
                </div>
                <div class="form-group" style="margin: 0;">
                    <label>&nbsp;</label>
                    <button type="submit" class="btn btn-primary">View</button>
                </div>
            </div>
        </form>
        
        <div style="background: #1e1e1e; color: #d4d4d4; padding: 20px; border-radius: 5px; font-family: 'Courier New', monospace; font-size: 12px; max-height: 600px; overflow-y: auto;">
            <div style="color: #4ec9b0; margin-bottom: 10px;">
                📄 File: <?php echo htmlspecialchars($logFile); ?> (Last <?php echo $lines; ?> lines)
            </div>
            <pre style="margin: 0; white-space: pre-wrap; word-wrap: break-word; color: #d4d4d4;"><?php echo htmlspecialchars($logContent); ?></pre>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">🛠️ Log Management Commands</div>
    <div class="card-body">
        <pre style="background: #2d2d2d; color: #d4d4d4; padding: 15px; border-radius: 5px; font-size: 13px; border: 1px solid #444;">
# View last 50 lines of a log
tail -n 50 /var/log/apache2/error.log

# Follow log in real-time
tail -f /var/log/apache2/access.log

# Search in logs
grep "error" /var/log/apache2/error.log

# View logs with journalctl
journalctl -u apache2 -n 50

# Clear log file (be careful!)
> /var/log/apache2/error.log

# Rotate logs
logrotate -f /etc/logrotate.conf
        </pre>
        
        <a href="<?php echo buildUrl('terminal'); ?>" class="btn btn-primary">
            💻 Open Terminal
        </a>
    </div>
</div>

<script>
// Auto-refresh every 5 seconds
setTimeout(() => {
    location.reload();
}, 5000);
</script>
