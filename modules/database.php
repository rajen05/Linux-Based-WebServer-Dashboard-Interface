<?php
// Security: Prevent direct access
if (!defined('PANEL_ACCESS')) {
    die('Direct access not permitted');
}

// Check if MySQL/MariaDB is running
$mysqlRunning = isServiceRunning('mysql') || isServiceRunning('mariadb');
?>

<div class="card">
    <div class="card-header">🗄️ Database Management</div>
    <div class="card-body">
        <div class="stat-card <?php echo $mysqlRunning ? 'success' : 'danger'; ?>">
            <div class="stat-label">Database Server Status</div>
            <div class="stat-value">
                <?php if ($mysqlRunning): ?>
                    <span class="badge badge-success">● Running</span>
                <?php else: ?>
                    <span class="badge badge-danger">○ Stopped</span>
                <?php endif; ?>
            </div>
            <div class="stat-subtitle">
                <?php if ($mysqlRunning): ?>
                    MySQL/MariaDB is running and accepting connections
                <?php else: ?>
                    MySQL/MariaDB is not running. Start it from the <a href="<?php echo buildUrl('services'); ?>">Services</a> page.
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="alert alert-info">
    <strong>💡 Database Access:</strong><br>
    For full database management, you can:
    <ul style="margin: 10px 0 0 20px;">
        <li>Install <strong>phpMyAdmin</strong> for a web-based interface</li>
        <li>Use the <a href="<?php echo buildUrl('terminal'); ?>">Terminal</a> with MySQL commands</li>
        <li>Connect remotely using MySQL Workbench or similar tools</li>
    </ul>
</div>

<div class="card">
    <div class="card-header">📝 Common MySQL Commands</div>
    <div class="card-body">
        <pre style="background: #2d2d2d; color: #d4d4d4; padding: 15px; border-radius: 5px; font-size: 13px; border: 1px solid #444;">
# Connect to MySQL
mysql -u root -p

# Show databases
SHOW DATABASES;

# Create database
CREATE DATABASE mywebsite;

# Create user
CREATE USER 'username'@'localhost' IDENTIFIED BY 'password';

# Grant privileges
GRANT ALL PRIVILEGES ON mywebsite.* TO 'username'@'localhost';
FLUSH PRIVILEGES;

# Backup database
mysqldump -u root -p database_name > backup.sql

# Restore database
mysql -u root -p database_name < backup.sql

# Show MySQL status
systemctl status mysql
        </pre>
        
        <a href="<?php echo buildUrl('terminal'); ?>" class="btn btn-primary">
            💻 Open Terminal for MySQL
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">🚀 Install phpMyAdmin</div>
    <div class="card-body">
        <p>phpMyAdmin provides a user-friendly web interface for MySQL management.</p>
        
        <h4>Installation Steps:</h4>
        <pre style="background: #2d2d2d; color: #d4d4d4; padding: 15px; border-radius: 5px; font-size: 13px; border: 1px solid #444;">
# Update package list
apt update

# Install phpMyAdmin
apt install phpmyadmin

# During installation:
# - Select Apache2 as web server
# - Configure database with dbconfig-common: Yes
# - Set phpMyAdmin password

# Enable PHP mbstring extension
phpenmod mbstring

# Restart Apache
systemctl restart apache2

# Access phpMyAdmin at:
# http://<?php echo SERVER_IP; ?>/phpmyadmin
        </pre>
        
        <p style="margin-top: 15px;">
            <strong>Security Tip:</strong> Change the default phpMyAdmin URL by editing the Apache configuration.
        </p>
    </div>
</div>
