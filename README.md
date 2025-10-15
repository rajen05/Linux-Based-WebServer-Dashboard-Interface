# 🖥️ Complete Server Control Panel

A comprehensive, modular web-based control panel for managing your Linux VPS server.

**For:** Linux VPS / Cloud Servers

## 🚀 Quick Start

```bash
# 1. Upload to server
scp -r * root@YOUR-SERVER-IP:/var/www/html/panel/

# 2. Set permissions
ssh root@YOUR-SERVER-IP
chown -R www-data:www-data /var/www/html/panel
chmod -R 755 /var/www/html/panel

# 3. Change password in config.php
nano /var/www/html/panel/config.php

# 4. Access
# http://YOUR-SERVER-IP/panel/
```

## ⭐ What's New in File Manager

- ✅ **Drag & Drop to Move Files** - Organize by dragging files to folders
- ✅ **Grid/List View Toggle** - Switch between icon and detailed views
- ✅ **Enhanced UI** - Modern Windows Explorer-style interface
- ✅ **Light/Dark Mode** - Theme toggle with saved preference
- ✅ **Improved Navigation** - Breadcrumbs, sidebar tree, keyboard shortcuts

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

### 📁 File Manager (Enhanced!)
- **Modern Windows Explorer Interface** - Familiar layout with sidebar and file grid
- **Drag & Drop Upload** - Simply drag files to upload
- **Drag & Drop to Move** - Drag files to folders to organize
- **Grid/List View Toggle** - Switch between icon view and detailed table
- **In-Browser Editor** - Edit HTML, PHP, CSS, JS, TXT files directly
- **File Operations** - Upload, download, rename, delete, create folders
- **Light/Dark Mode** - Toggle theme to suit your preference
- **Breadcrumb Navigation** - Easy path navigation
- **Directory Tree Sidebar** - Quick folder access
- **Keyboard Shortcuts** - F2 (rename), Delete, F5 (refresh)
- **Responsive Design** - Works on desktop and mobile
- **Security Features** - Path traversal protection, file type validation

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

## 📁 File Manager Guide

### Accessing the File Manager

**Standalone Access:**
```
http://YOUR-SERVER-IP/panel/filemanager.php
```

**Integrated Access:**
- Click "Files" in the sidebar navigation

### Features Overview

#### 1. **Drag & Drop Upload**
- Click "Upload" button or drag files directly
- Supports multiple file uploads
- Max file size: 50MB
- Allowed types: html, php, js, css, txt, jpg, png, gif, svg, zip, pdf, json, xml, md

#### 2. **Drag & Drop to Move Files** ⭐ NEW!
- Click and hold any file/folder
- Drag over a folder (it highlights blue)
- Release to drop
- Confirm the move
- Works with sidebar folders too!

#### 3. **View Modes** ⭐ NEW!
- **Grid View (⊞)** - Large icons, perfect for browsing images
- **List View (☰)** - Detailed table with size, date, permissions
- Your preference is saved automatically

#### 4. **File Operations**
- **Upload** - Drag & drop or click to browse
- **Download** - Select file and click download
- **Rename** - Select file and press F2 or click Rename
- **Delete** - Select file and press Delete or click Delete button
- **Edit** - Double-click text files to edit in browser
- **Create Folder** - Click "New Folder" button

#### 5. **Navigation**
- **Breadcrumbs** - Click any path segment to navigate
- **Sidebar Tree** - Click folders in left sidebar
- **Back/Up Buttons** - Navigate through history
- **Double-click** - Open folders or edit files

#### 6. **Keyboard Shortcuts**
- `F2` - Rename selected file
- `Delete` - Delete selected file
- `F5` - Refresh file list
- `Double-click` - Open folder or edit file

#### 7. **Light/Dark Mode**
- Click theme toggle in header
- Preference saved in browser
- Smooth transitions between themes

### File Manager Configuration

Edit `filemanager.php` (lines 3-6):

```php
define('ROOT_DIR', '/var/www/html');           // Root directory
define('MAX_UPLOAD_SIZE', 52428800);           // 50MB limit
define('ALLOWED_EXTENSIONS', array(...));      // Allowed file types
define('EDITABLE_EXTENSIONS', array(...));     // Editable in browser
```

### Security Features

✅ **Path Traversal Protection** - Cannot access files outside ROOT_DIR
✅ **File Type Validation** - Only allowed extensions can be uploaded
✅ **Input Sanitization** - All user inputs are sanitized
✅ **Confirmation Dialogs** - Confirms before delete/move operations
✅ **Size Limits** - 50MB upload limit prevents abuse

### Tips & Tricks

**Quick Organization:**
1. Switch to List View (☰) to see file details
2. Sort by date/size visually
3. Drag files to folders to organize
4. Switch back to Grid View (⊞) for visual browsing

**Sidebar Shortcuts:**
- Drag files directly to sidebar folders
- No need to navigate into folder first
- Quick access to deep directory structures

**Batch Operations:**
- Upload multiple files at once
- Drag & drop supports multiple files
- Progress shown for each file

### Troubleshooting

**Upload fails:**
- Check folder permissions (755 for folders, 644 for files)
- Verify file size < 50MB
- Ensure file type is in ALLOWED_EXTENSIONS

**Can't move files:**
- Refresh page (F5)
- Check destination folder permissions
- Ensure not moving folder into itself

**Editor not working:**
- File must be in EDITABLE_EXTENSIONS
- File size must be reasonable
- Check file permissions (must be readable)

**Dark mode not saving:**
- Enable browser localStorage
- Clear browser cache
- Try different browser

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

## 🎯 File Manager Highlights

The enhanced file manager (`filemanager.php`) is a standalone, production-ready tool that can be used independently or integrated into the control panel:

### Standalone Use:
```
http://YOUR-SERVER-IP/panel/filemanager.php
```

### Key Features:
- 🎨 **Modern UI** - Windows Explorer-style interface
- 🖱️ **Drag & Drop** - Upload files and move them between folders
- 👁️ **View Modes** - Grid (icons) and List (details) views
- ✏️ **In-Browser Editor** - Edit code files directly
- 🌓 **Dark Mode** - Easy on the eyes
- ⌨️ **Keyboard Shortcuts** - Power user friendly
- 🔒 **Secure** - Path traversal protection, file validation
- 📱 **Responsive** - Works on mobile devices

### Perfect For:
- Managing website files without FTP
- Quick edits to HTML/CSS/JS/PHP files
- Organizing server files visually
- Uploading multiple files at once
- Moving files between directories

---

**🎉 You now have a complete server control panel!**

Access all features through the sidebar navigation. Start with the Dashboard to get an overview of your system.

**File Manager:** The standalone file manager is your go-to tool for visual file management - no more command line or FTP needed!
