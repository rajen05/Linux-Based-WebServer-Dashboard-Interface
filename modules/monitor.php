<?php
// Security: Prevent direct access
if (!defined('PANEL_ACCESS')) {
    die('Direct access not permitted');
}

// Get system stats
$memory = getMemoryUsage();
$disk = getDiskUsage('/');
$cpu = getCPUUsage();
$loadAvg = function_exists('sys_getloadavg') ? @sys_getloadavg() : [0, 0, 0];

// Get network interfaces
$network = [];
exec("ip -s link 2>&1", $netOutput);
$networkInfo = implode("\n", $netOutput);

// Get top processes
$processes = [];
exec("ps aux --sort=-%mem | head -11 2>&1", $procOutput);
array_shift($procOutput); // Remove header
foreach ($procOutput as $line) {
    if (!empty(trim($line))) {
        $processes[] = $line;
    }
}

// Get disk partitions
$partitions = [];
exec("df -h 2>&1", $partOutput);
array_shift($partOutput); // Remove header
foreach ($partOutput as $line) {
    if (!empty(trim($line)) && !str_starts_with($line, 'tmpfs') && !str_starts_with($line, 'udev')) {
        $partitions[] = $line;
    }
}
?>

<div class="card-grid">
    <div class="stat-card <?php echo $cpu > 80 ? 'danger' : ($cpu > 60 ? 'warning' : 'success'); ?>">
        <div class="stat-label">🔥 CPU Usage</div>
        <div class="stat-value"><?php echo $cpu; ?>%</div>
        <div class="progress">
            <div class="progress-bar <?php echo $cpu > 80 ? 'danger' : ($cpu > 60 ? 'warning' : 'success'); ?>" style="width: <?php echo $cpu; ?>%">
                <?php echo $cpu; ?>%
            </div>
        </div>
        <div class="stat-subtitle">Load Average: <?php echo implode(', ', array_map(fn($v) => round($v, 2), $loadAvg)); ?></div>
    </div>
    
    <div class="stat-card <?php echo $memory['percent'] > 80 ? 'danger' : ($memory['percent'] > 60 ? 'warning' : 'success'); ?>">
        <div class="stat-label">💾 Memory</div>
        <div class="stat-value"><?php echo $memory['used']; ?> MB</div>
        <div class="progress">
            <div class="progress-bar <?php echo $memory['percent'] > 80 ? 'danger' : ($memory['percent'] > 60 ? 'warning' : 'success'); ?>" style="width: <?php echo $memory['percent']; ?>%">
                <?php echo $memory['percent']; ?>%
            </div>
        </div>
        <div class="stat-subtitle">Total: <?php echo $memory['total']; ?> MB | Free: <?php echo $memory['free']; ?> MB</div>
    </div>
    
    <div class="stat-card <?php echo $disk['percent'] > 80 ? 'danger' : ($disk['percent'] > 60 ? 'warning' : 'success'); ?>">
        <div class="stat-label">💿 Disk Space</div>
        <div class="stat-value"><?php echo $disk['used']; ?></div>
        <div class="progress">
            <div class="progress-bar <?php echo $disk['percent'] > 80 ? 'danger' : ($disk['percent'] > 60 ? 'warning' : 'success'); ?>" style="width: <?php echo $disk['percent']; ?>%">
                <?php echo $disk['percent']; ?>%
            </div>
        </div>
        <div class="stat-subtitle">Total: <?php echo $disk['total']; ?> | Free: <?php echo $disk['free']; ?></div>
    </div>
    
    <div class="stat-card info">
        <div class="stat-label">⏱️ Uptime</div>
        <div class="stat-value"><?php echo getUptime(); ?></div>
        <div class="stat-subtitle">System running time</div>
    </div>
</div>

<div class="card">
    <div class="card-header">📊 Top Processes (by Memory)</div>
    <div class="card-body">
        <div style="overflow-x: auto;">
            <pre style="font-size: 12px; background: #2d2d2d; color: #d4d4d4; padding: 15px; border-radius: 5px; overflow-x: auto; border: 1px solid #444;"><?php
foreach ($processes as $proc) {
    echo htmlspecialchars($proc) . "\n";
}
            ?></pre>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">💿 Disk Partitions</div>
    <div class="card-body">
        <div style="overflow-x: auto;">
            <pre style="font-size: 13px; background: #2d2d2d; color: #d4d4d4; padding: 15px; border-radius: 5px; border: 1px solid #444;"><?php
foreach ($partitions as $part) {
    echo htmlspecialchars($part) . "\n";
}
            ?></pre>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">🌐 Network Interfaces</div>
    <div class="card-body">
        <div style="overflow-x: auto;">
            <pre style="font-size: 12px; background: #2d2d2d; color: #d4d4d4; padding: 15px; border-radius: 5px; max-height: 400px; overflow-y: auto; border: 1px solid #444;"><?php echo htmlspecialchars($networkInfo); ?></pre>
        </div>
    </div>
</div>

<script>
// Auto-refresh every 3 seconds
setTimeout(() => {
    location.reload();
}, 3000);
</script>
