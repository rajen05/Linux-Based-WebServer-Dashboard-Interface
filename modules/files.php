<?php
// Security: Prevent direct access
if (!defined('PANEL_ACCESS')) {
    die('Direct access not permitted');
}
?>

<style>
.file-manager-frame {
    width: 100%;
    height: calc(100vh - 200px);
    min-height: 700px;
    border: none;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}
</style>

<div class="alert alert-info">
    <strong>📁 File Manager</strong><br>
    Full-featured file manager with preview, editing, upload, download, and search capabilities.
    <a href="filemanager.php" target="_blank" style="float: right; color: #667eea; text-decoration: none; font-weight: bold;">
        🔗 Open in New Tab
    </a>
</div>

<div class="card" style="padding: 0;">
    <iframe src="filemanager.php" class="file-manager-frame"></iframe>
</div>

<div class="card">
    <div class="card-header">🚀 Quick File Operations</div>
    <div class="card-body">
        <p>Common file management commands:</p>
        <pre style="background: #2d2d2d; color: #d4d4d4; padding: 15px; border-radius: 5px; font-size: 13px; border: 1px solid #444;">
# List files
ls -la /var/www/html

# Change directory
cd /var/www/html

# Create directory
mkdir /var/www/html/new-site

# Copy files
cp source.txt destination.txt

# Move/rename files
mv oldname.txt newname.txt

# Delete file
rm file.txt

# Delete directory
rm -r directory/

# Edit file
nano /var/www/html/index.html

# View file
cat /var/www/html/index.html

# Check permissions
ls -l /var/www/html/

# Change permissions
chmod 644 file.txt
chmod 755 directory/

# Change owner
chown www-data:www-data file.txt
        </pre>
        
        <a href="<?php echo buildUrl('terminal'); ?>" class="btn btn-primary">
            💻 Open Terminal for File Operations
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">📊 Disk Usage</div>
    <div class="card-body">
        <?php
        $diskUsage = getDiskUsage('/var/www/html');
        ?>
        <div class="stat-card">
            <div class="stat-label">Web Root Usage (/var/www/html)</div>
            <div class="stat-value"><?php echo $diskUsage['used']; ?></div>
            <div class="progress">
                <div class="progress-bar <?php echo $diskUsage['percent'] > 80 ? 'danger' : 'success'; ?>" style="width: <?php echo $diskUsage['percent']; ?>%">
                    <?php echo $diskUsage['percent']; ?>%
                </div>
            </div>
            <div class="stat-subtitle">Total: <?php echo $diskUsage['total']; ?> | Free: <?php echo $diskUsage['free']; ?></div>
        </div>
    </div>
</div>

<div class="alert alert-warning">
    <strong>🔧 Integration Note:</strong> To integrate your full file manager, copy the file management code from your original <code>index.php</code> into this module file.
</div>
