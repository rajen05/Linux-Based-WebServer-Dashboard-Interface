<?php
// Security: Prevent direct access
if (!defined('PANEL_ACCESS')) {
    die('Direct access not permitted');
}

// Get list of sites
$apacheSites = [];
if (is_dir(VHOST_DIR)) {
    $sites = scandir(VHOST_DIR);
    foreach ($sites as $site) {
        if ($site !== '.' && $site !== '..' && pathinfo($site, PATHINFO_EXTENSION) === 'conf') {
            $enabled = file_exists('/etc/apache2/sites-enabled/' . $site);
            $apacheSites[] = [
                'name' => $site,
                'enabled' => $enabled
            ];
        }
    }
}
?>

<div class="card">
    <div class="card-header">🌐 Website Management</div>
    <div class="card-body">
        <p>Manage Apache virtual hosts and websites hosted on your server.</p>
        
        <?php if (!empty($apacheSites)): ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Site Configuration</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($apacheSites as $site): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($site['name']); ?></strong></td>
                        <td>
                            <?php if ($site['enabled']): ?>
                                <span class="badge badge-success">Enabled</span>
                            <?php else: ?>
                                <span class="badge badge-secondary">Disabled</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="<?php echo buildUrl('terminal'); ?>" class="btn btn-sm btn-info">
                                Manage via Terminal
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="alert alert-info">
                No virtual host configurations found in <?php echo VHOST_DIR; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="card">
    <div class="card-header">📝 Common Website Management Commands</div>
    <div class="card-body">
        <pre style="background: #2d2d2d; color: #d4d4d4; padding: 15px; border-radius: 5px; font-size: 13px; border: 1px solid #444;">
# List available sites
ls -la /etc/apache2/sites-available/

# Enable a site
a2ensite example.com.conf

# Disable a site
a2dissite example.com.conf

# Test Apache configuration
apache2ctl configtest

# Reload Apache (after changes)
systemctl reload apache2

# Create new virtual host
nano /etc/apache2/sites-available/newsite.conf

# View site configuration
cat /etc/apache2/sites-available/000-default.conf
        </pre>
        
        <a href="<?php echo buildUrl('terminal'); ?>" class="btn btn-primary">
            💻 Open Terminal
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">🚀 Quick Setup Guide</div>
    <div class="card-body">
        <h4>To host a new website:</h4>
        <ol>
            <li>Create a directory in <code>/var/www/html/</code> for your site</li>
            <li>Upload your website files to that directory</li>
            <li>Create a virtual host configuration (optional for subdomains)</li>
            <li>Enable the site and reload Apache</li>
        </ol>
        
        <h4 style="margin-top: 20px;">Example Virtual Host:</h4>
        <pre style="background: #2d2d2d; color: #d4d4d4; padding: 20px; border-radius: 5px; font-size: 13px; overflow-x: auto; border: 1px solid #444;"><code style="color: #ce9178;">&lt;VirtualHost *:80&gt;</code>
    <code style="color: #9cdcfe;">ServerName</code> <code style="color: #ce9178;">example.com</code>
    <code style="color: #9cdcfe;">ServerAlias</code> <code style="color: #ce9178;">www.example.com</code>
    <code style="color: #9cdcfe;">DocumentRoot</code> <code style="color: #ce9178;">/var/www/html/example</code>
    
    <code style="color: #ce9178;">&lt;Directory /var/www/html/example&gt;</code>
        <code style="color: #9cdcfe;">AllowOverride</code> <code style="color: #4ec9b0;">All</code>
        <code style="color: #9cdcfe;">Require</code> <code style="color: #4ec9b0;">all granted</code>
    <code style="color: #ce9178;">&lt;/Directory&gt;</code>
    
    <code style="color: #9cdcfe;">ErrorLog</code> <code style="color: #ce9178;">${APACHE_LOG_DIR}/example-error.log</code>
    <code style="color: #9cdcfe;">CustomLog</code> <code style="color: #ce9178;">${APACHE_LOG_DIR}/example-access.log combined</code>
<code style="color: #ce9178;">&lt;/VirtualHost&gt;</code></pre>
    </div>
</div>
