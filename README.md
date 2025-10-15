# 🖥️ Complete Server Control Panel

A comprehensive, modular web-based control panel for managing your Linux VPS server.

**For:** Linux VPS / Cloud Servers

## 📁 File Structure

```
Linux-web-server-Interface/
├── index.php              # Main panel entry point
├── filemanager.php        # Standalone file manager
├── config.php             # Configuration (CHANGE PASSWORD!)
├── auth.php               # Authentication system
├── .htaccess              # Apache security settings
│
├── includes/
│   ├── header.php         # Sidebar navigation
│   ├── footer.php         # Footer
│   └── functions.php      # Shared utilities
│
├── modules/
│   ├── dashboard.php      # System overview
│   ├── terminal.php       # Web terminal
│   ├── monitor.php        # System monitoring
│   ├── files.php          # File manager (embedded)
│   ├── websites.php       # Virtual hosts
│   ├── services.php       # Service control (auto-detect)
│   ├── database.php       # Database management
│   └── logs.php           # Log viewer
│
└── assets/
    ├── css/style.css      # All styles
    └── js/main.js         # JavaScript
```

## ✨ Features

### 📊 Dashboard
- **System Overview** - CPU, RAM, Disk usage at a glance
- **Service Status** - See which services are running
- **Quick Actions** - Jump to any module quickly
- **Real-time Stats** - Auto-refreshes every 5 seconds

### 💻 Terminal
- **Web-based Command Line** - Execute shell commands from browser
- **Command History** - Keeps track of your commands
- **Safety Features** - Blocks dangerous commands
- **Quick Commands** - Pre-built buttons for common tasks
- **Syntax Highlighting** - Color-coded terminal output

### 📈 System Monitor
- **CPU Monitoring** - Real-time CPU usage and load average
- **Memory Stats** - RAM usage with visual progress bars
- **Disk Space** - Storage usage across partitions
- **Process List** - Top processes by memory usage
- **Network Info** - Network interface statistics
- **Auto-refresh** - Updates every 3 seconds

### 📁 File Manager
- Integration point for your full-featured file manager
- Quick file operations via terminal
- Disk usage statistics

### 🌐 Website Management
- **Virtual Host Listing** - See all configured sites
- **Site Status** - Check if sites are enabled/disabled
- **Quick Setup Guide** - Instructions for hosting new sites
- **Apache Commands** - Common website management commands

### ⚙️ Service Control
- **Start/Stop Services** - Control Apache, Nginx, MySQL, PHP-FPM
- **Enable/Disable Auto-start** - Configure boot behavior
- **Service Status** - Real-time status indicators
- **One-click Actions** - Simple buttons for service management

### 🗄️ Database Management
- **MySQL/MariaDB Status** - Check if database is running
- **Common Commands** - SQL command reference
- **phpMyAdmin Setup** - Installation guide
- **Backup/Restore** - Database backup commands

### 📝 Log Viewer
- **Multiple Logs** - Apache, Nginx, MySQL, System logs
- **Real-time Viewing** - Auto-refreshes every 5 seconds
- **Configurable Lines** - View 10-500 lines
- **Search & Filter** - Terminal commands for log analysis

## 🚀 Quick Deployment

### Upload to Server
```powershell
# From Windows PowerShell
scp -r Linux-web-server-Interface/* root@YOUR-SERVER-IP:/var/www/html/panel/
```

### Set Permissions
```bash
ssh root@YOUR-SERVER-IP
chown -R www-data:www-data /var/www/html/panel
find /var/www/html/panel -type d -exec chmod 755 {} \;
find /var/www/html/panel -type f -exec chmod 644 {} \;
```

### Configure
```bash
nano /var/www/html/panel/config.php
# Change ADMIN_PASSWORD (line 16)
# Server IP is auto-detected - no need to change!
```

### Access
```
http://YOUR-SERVER-IP/panel/
```

## 🔧 Installation

### Step 1: Upload Files

Upload all files to your server maintaining the directory structure:

```bash
scp -r Linux-web-server-Interface/* root@YOUR-SERVER-IP:/var/www/html/panel/
```

Or create the structure manually:

```bash
ssh root@YOUR-SERVER-IP
cd /var/www/html
mkdir -p panel/{includes,modules,assets/{css,js}}
```

### Step 2: Set Permissions

```bash
# Set ownership
chown -R www-data:www-data /var/www/html/panel

# Set directory permissions
find /var/www/html/panel -type d -exec chmod 755 {} \;

# Set file permissions
find /var/www/html/panel -type f -exec chmod 644 {} \;
```

### Step 3: Configure

Edit `config.php`:

```php
// Change the password!
define('ADMIN_PASSWORD', 'your-strong-password-here');

// Server IP is auto-detected - no need to change!
// Server root (change if your web root is different)
define('SERVER_ROOT', '/var/www/html');
```

### Step 4: Access

Navigate to:
```
http://YOUR-SERVER-IP/panel/index_new.php
```

Login with the password you set in `config.php`.

## 🔧 Configuration Options

### config.php Settings

```php
// Authentication
ADMIN_PASSWORD          // Login password

// Paths
SERVER_ROOT            // Root directory for file operations
SERVER_IP              // Your server's IP address

// File Manager
MAX_UPLOAD_SIZE        // Maximum upload size (bytes)
MAX_PREVIEW_SIZE       // Maximum preview size (bytes)
ALLOWED_EXTENSIONS     // Array of allowed file types

// Services
SERVICES               // Services to manage

// Logs
LOG_FILES              // Log files to display

// Database
DB_HOST, DB_USER, DB_PASS  // MySQL credentials
```

## 📖 Usage Guide

### Dashboard
- View system stats and service status
- Click any stat card to see more details
- Use quick action buttons to navigate

### Terminal
- Type commands and press Enter
- Use quick command buttons for common tasks
- Type `clear` to clear the screen
- Command history is saved in your session

### System Monitor
- View real-time system resources
- Progress bars show usage percentages
- Auto-refreshes every 3 seconds
- Check top processes and disk partitions

### Service Control
- Click Start/Stop to control services
- Click Restart after configuration changes
- Enable/Disable auto-start on boot
- Confirm dialogs prevent accidents

### Log Viewer
- Select log file from dropdown
- Choose number of lines to display
- Auto-refreshes every 5 seconds
- Use terminal for advanced log analysis

## 🔐 Security Best Practices

### 1. Change Default Password
```php
// In config.php
define('ADMIN_PASSWORD', 'Use-A-Very-Strong-P@ssw0rd!');
```

### 2. Rename Entry Point
```bash
mv index_new.php my-secret-panel.php
```

### 3. Restrict by IP
Add to `.htaccess`:
```apache
<RequireAll>
    Require ip YOUR.IP.ADDRESS
</RequireAll>
```

### 4. Use HTTPS
```bash
# Install SSL certificate
apt install certbot python3-certbot-apache
certbot --apache -d yourdomain.com
```

### 5. Regular Updates
```bash
# Keep system updated
apt update && apt upgrade
```

## 🎨 Customization

### Change Colors
Edit `assets/css/style.css`:
```css
:root {
    --primary: #667eea;      /* Change to your color */
    --secondary: #764ba2;    /* Change to your color */
}
```

### Add New Module
1. Create `modules/mymodule.php`
2. Add to `index_new.php` valid modules array
3. Add navigation link in `includes/header.php`

### Modify Services
Edit `config.php`:
```php
define('SERVICES', [
    'apache2' => 'Apache Web Server',
    'your-service' => 'Your Service Name',
]);
```

## 🐛 Troubleshooting

### Can't Login
```bash
# Check file permissions
ls -la /var/www/html/panel/config.php

# Verify password in config.php
cat /var/www/html/panel/config.php | grep ADMIN_PASSWORD
```

### Terminal Not Working
```bash
# Check PHP exec() is enabled
php -r "echo (function_exists('exec') ? 'Enabled' : 'Disabled');"

# Check disable_functions in php.ini
grep disable_functions /etc/php/*/apache2/php.ini
```

### Services Won't Start/Stop
```bash
# Check if running as www-data has sudo access
# You may need to add www-data to sudoers for systemctl

# Or run panel as root (not recommended for production)
```

### Logs Not Showing
```bash
# Check log file permissions
ls -la /var/log/apache2/

# Make logs readable
chmod 644 /var/log/apache2/*.log
```

## 🔄 Integration with Original File Manager

To integrate your full-featured file manager into the Files module:

1. Open `modules/files.php`
2. Copy the file management code from your original `index.php`
3. Paste it into the Files module
4. Adjust paths and includes as needed

## 📊 Module Overview

| Module | Purpose | Auto-Refresh |
|--------|---------|--------------|
| Dashboard | System overview | 5 seconds |
| Terminal | Command execution | No |
| Monitor | Resource monitoring | 3 seconds |
| Files | File management | No |
| Websites | Virtual host management | No |
| Services | Service control | No |
| Database | MySQL management | No |
| Logs | Log viewing | 5 seconds |

## 🚀 Advanced Features

### Add Cron Job Management
Create `modules/cron.php` to manage scheduled tasks

### Add User Management
Create `modules/users.php` to manage Linux users

### Add Backup System
Create `modules/backup.php` for automated backups

### Add Firewall Management
Create `modules/firewall.php` for UFW/iptables control

## 📝 Development Notes

### Adding a New Module

1. **Create module file**: `modules/newmodule.php`
```php
<?php
if (!defined('PANEL_ACCESS')) {
    die('Direct access not permitted');
}
?>

<div class="card">
    <div class="card-header">Your Module</div>
    <div class="card-body">
        <!-- Your content -->
    </div>
</div>
```

2. **Add to routing**: Edit `index_new.php`
```php
$validModules = ['dashboard', 'terminal', ..., 'newmodule'];
```

3. **Add navigation**: Edit `includes/header.php`
```php
<a href="<?php echo buildUrl('newmodule'); ?>" class="nav-item">
    <span class="nav-icon">🎯</span>
    <span class="nav-text">New Module</span>
</a>
```

## ⚠️ Important Warnings

1. **Terminal Access** - The terminal has full server access. Use carefully!
2. **Service Control** - Stopping critical services can break your server
3. **File Operations** - Always backup before bulk operations
4. **Database Access** - Incorrect SQL can corrupt databases
5. **Log Files** - Some logs may contain sensitive information

## 🆘 Emergency Access

If locked out:

```bash
# SSH into server
ssh root@YOUR-SERVER-IP

# Reset password in config
nano /var/www/html/panel/config.php

# Or remove panel temporarily
mv /var/www/html/panel /root/panel-backup
```

## 📞 Support

For issues:
1. Check the troubleshooting section
2. Review server error logs
3. Test commands in SSH first
4. Verify file permissions

## 📄 License

Personal/Educational use. Modify as needed for your server.

---

**🎉 You now have a complete server control panel!**

Access all features through the sidebar navigation. Start with the Dashboard to get an overview of your system.
