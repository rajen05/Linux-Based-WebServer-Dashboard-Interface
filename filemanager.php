<?php
/**
 * Advanced File Browser Interface
 * A web-based file manager for Linux VPS
 * 
 * FEATURES:
 * - File browsing with breadcrumb navigation
 * - File preview (text, code, images, PDFs)
 * - File editing with syntax highlighting
 * - Upload, download, delete, rename, copy
 * - Directory creation and management
 * - File search functionality
 * - Permissions display and modification
 * - Responsive design
 * 
 * SECURITY FEATURES:
 * - Password authentication
 * - Session management
 * - Path traversal protection
 * - File type validation
 * - Size limits on uploads
 */

session_start();

// ============================================
// CONFIGURATION - CHANGE THESE VALUES
// ============================================

// Change this password immediately!
define('ADMIN_PASSWORD', 'changeme123');

// Root directory to browse (default: /var/www/html)
// You can change this to browse other directories
define('ROOT_DIR', '/var/www/html');

// Maximum upload file size (in bytes) - 50MB default
define('MAX_UPLOAD_SIZE', 50 * 1024 * 1024);

// Maximum file size for preview/edit (in bytes) - 5MB default
define('MAX_PREVIEW_SIZE', 5 * 1024 * 1024);

// Allowed file extensions for upload (security measure)
define('ALLOWED_EXTENSIONS', ['txt', 'html', 'htm', 'php', 'css', 'js', 'json', 'xml', 'jpg', 'jpeg', 'png', 'gif', 'svg', 'pdf', 'zip', 'tar', 'gz', 'md', 'log', 'conf', 'ini', 'yml', 'yaml', 'sh']);

// ============================================
// AUTHENTICATION
// ============================================

function isLoggedIn() {
    return isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true;
}

function login($password) {
    if ($password === ADMIN_PASSWORD) {
        $_SESSION['authenticated'] = true;
        return true;
    }
    return false;
}

function logout() {
    session_destroy();
    header('Location: index.php');
    exit;
}

// Handle login
if (isset($_POST['login'])) {
    if (login($_POST['password'])) {
        header('Location: index.php');
        exit;
    } else {
        $loginError = 'Invalid password';
    }
}

// Handle logout
if (isset($_GET['logout'])) {
    logout();
}

// Show login form if not authenticated
if (!isLoggedIn()) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Server File Manager - Login</title>
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body {
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .login-container {
                background: white;
                padding: 40px;
                border-radius: 10px;
                box-shadow: 0 10px 40px rgba(0,0,0,0.2);
                width: 100%;
                max-width: 400px;
            }
            h1 {
                color: #333;
                margin-bottom: 10px;
                font-size: 24px;
            }
            .subtitle {
                color: #666;
                margin-bottom: 30px;
                font-size: 14px;
            }
            .form-group {
                margin-bottom: 20px;
            }
            label {
                display: block;
                margin-bottom: 5px;
                color: #333;
                font-weight: 500;
            }
            input[type="password"] {
                width: 100%;
                padding: 12px;
                border: 2px solid #e0e0e0;
                border-radius: 5px;
                font-size: 14px;
                transition: border-color 0.3s;
            }
            input[type="password"]:focus {
                outline: none;
                border-color: #667eea;
            }
            button {
                width: 100%;
                padding: 12px;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                border: none;
                border-radius: 5px;
                font-size: 16px;
                font-weight: 600;
                cursor: pointer;
                transition: transform 0.2s;
            }
            button:hover {
                transform: translateY(-2px);
            }
            .error {
                background: #fee;
                color: #c33;
                padding: 10px;
                border-radius: 5px;
                margin-bottom: 20px;
                font-size: 14px;
            }
            .warning {
                background: #fff3cd;
                color: #856404;
                padding: 15px;
                border-radius: 5px;
                margin-top: 20px;
                font-size: 13px;
                border-left: 4px solid #ffc107;
            }
        </style>
    </head>
    <body>
        <div class="login-container">
            <h1>🖥️ Server File Manager</h1>
            <p class="subtitle">Login to access your server files</p>
            
            <?php if (isset($loginError)): ?>
                <div class="error"><?php echo htmlspecialchars($loginError); ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required autofocus>
                </div>
                <button type="submit" name="login">Login</button>
            </form>
            
            <div class="warning">
                <strong>⚠️ Security Notice:</strong><br>
                Change the default password in index.php (line 21) before deploying to production!
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// ============================================
// SECURITY FUNCTIONS
// ============================================

/**
 * Sanitize and validate path to prevent directory traversal attacks
 */
function sanitizePath($path) {
    // Remove any null bytes
    $path = str_replace("\0", '', $path);
    
    // Get real path
    $realPath = realpath($path);
    
    // If path doesn't exist, try parent directory
    if ($realPath === false) {
        $realPath = realpath(dirname($path));
    }
    
    // Ensure path is within ROOT_DIR
    $rootReal = realpath(ROOT_DIR);
    if ($realPath === false || strpos($realPath, $rootReal) !== 0) {
        return $rootReal;
    }
    
    return $realPath;
}

/**
 * Get file extension
 */
function getExtension($filename) {
    return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
}

/**
 * Check if file extension is allowed
 */
function isAllowedExtension($filename) {
    $ext = getExtension($filename);
    return in_array($ext, ALLOWED_EXTENSIONS);
}

/**
 * Format file size
 */
function formatSize($bytes) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    return round($bytes, 2) . ' ' . $units[$pow];
}

/**
 * Get file icon based on extension
 */
function getFileIcon($filename, $isDir) {
    if ($isDir) return '📁';
    
    $ext = getExtension($filename);
    $icons = [
        'php' => '🐘',
        'html' => '🌐',
        'htm' => '🌐',
        'css' => '🎨',
        'js' => '⚡',
        'json' => '📋',
        'xml' => '📋',
        'txt' => '📄',
        'md' => '📝',
        'pdf' => '📕',
        'jpg' => '🖼️',
        'jpeg' => '🖼️',
        'png' => '🖼️',
        'gif' => '🖼️',
        'svg' => '🖼️',
        'zip' => '📦',
        'tar' => '📦',
        'gz' => '📦',
        'log' => '📊',
        'conf' => '⚙️',
        'ini' => '⚙️',
        'yml' => '⚙️',
        'yaml' => '⚙️',
        'sh' => '🔧',
    ];
    
    return $icons[$ext] ?? '📄';
}

/**
 * Check if file can be previewed
 */
function canPreview($filename, $size) {
    if ($size > MAX_PREVIEW_SIZE) return false;
    
    $ext = getExtension($filename);
    $previewable = ['txt', 'html', 'htm', 'php', 'css', 'js', 'json', 'xml', 'md', 'log', 'conf', 'ini', 'yml', 'yaml', 'sh', 'jpg', 'jpeg', 'png', 'gif', 'svg', 'pdf'];
    return in_array($ext, $previewable);
}

/**
 * Check if file can be edited
 */
function canEdit($filename, $size) {
    if ($size > MAX_PREVIEW_SIZE) return false;
    
    $ext = getExtension($filename);
    $editable = ['txt', 'html', 'htm', 'php', 'css', 'js', 'json', 'xml', 'md', 'log', 'conf', 'ini', 'yml', 'yaml', 'sh'];
    return in_array($ext, $editable);
}

/**
 * Get language for syntax highlighting
 */
function getLanguage($filename) {
    $ext = getExtension($filename);
    $languages = [
        'php' => 'php',
        'html' => 'html',
        'htm' => 'html',
        'css' => 'css',
        'js' => 'javascript',
        'json' => 'json',
        'xml' => 'xml',
        'md' => 'markdown',
        'sh' => 'bash',
        'yml' => 'yaml',
        'yaml' => 'yaml',
    ];
    return $languages[$ext] ?? 'plaintext';
}

/**
 * Format file permissions
 */
function formatPermissions($filepath) {
    $perms = fileperms($filepath);
    
    // Owner
    $info = '';
    $info .= (($perms & 0x0100) ? 'r' : '-');
    $info .= (($perms & 0x0080) ? 'w' : '-');
    $info .= (($perms & 0x0040) ? 'x' : '-');
    
    // Group
    $info .= (($perms & 0x0020) ? 'r' : '-');
    $info .= (($perms & 0x0010) ? 'w' : '-');
    $info .= (($perms & 0x0008) ? 'x' : '-');
    
    // Others
    $info .= (($perms & 0x0004) ? 'r' : '-');
    $info .= (($perms & 0x0002) ? 'w' : '-');
    $info .= (($perms & 0x0001) ? 'x' : '-');
    
    return $info . ' (' . substr(sprintf('%o', $perms), -4) . ')';
}

// ============================================
// FILE OPERATIONS
// ============================================

$message = '';
$messageType = '';

// Get current directory
$currentDir = isset($_GET['dir']) ? sanitizePath($_GET['dir']) : realpath(ROOT_DIR);

// Handle file upload
if (isset($_POST['upload']) && isset($_FILES['file'])) {
    $targetDir = sanitizePath($_POST['upload_dir']);
    $fileName = basename($_FILES['file']['name']);
    $targetFile = $targetDir . '/' . $fileName;
    
    // Validate
    if ($_FILES['file']['size'] > MAX_UPLOAD_SIZE) {
        $message = 'File too large. Maximum size: ' . formatSize(MAX_UPLOAD_SIZE);
        $messageType = 'error';
    } elseif (!isAllowedExtension($fileName)) {
        $message = 'File type not allowed. Allowed: ' . implode(', ', ALLOWED_EXTENSIONS);
        $messageType = 'error';
    } elseif (move_uploaded_file($_FILES['file']['tmp_name'], $targetFile)) {
        chmod($targetFile, 0644);
        $message = 'File uploaded successfully: ' . htmlspecialchars($fileName);
        $messageType = 'success';
    } else {
        $message = 'Upload failed. Check directory permissions.';
        $messageType = 'error';
    }
}

// Handle file deletion
if (isset($_POST['delete'])) {
    $fileToDelete = sanitizePath($_POST['file_path']);
    
    if (is_file($fileToDelete)) {
        if (unlink($fileToDelete)) {
            $message = 'File deleted successfully';
            $messageType = 'success';
        } else {
            $message = 'Failed to delete file. Check permissions.';
            $messageType = 'error';
        }
    } elseif (is_dir($fileToDelete)) {
        if (rmdir($fileToDelete)) {
            $message = 'Directory deleted successfully';
            $messageType = 'success';
        } else {
            $message = 'Failed to delete directory. Directory must be empty.';
            $messageType = 'error';
        }
    }
}

// Handle directory creation
if (isset($_POST['create_dir'])) {
    $newDirName = basename($_POST['dir_name']);
    $newDirPath = $currentDir . '/' . $newDirName;
    
    if (mkdir($newDirPath, 0755)) {
        $message = 'Directory created successfully: ' . htmlspecialchars($newDirName);
        $messageType = 'success';
    } else {
        $message = 'Failed to create directory. Check permissions.';
        $messageType = 'error';
    }
}

// Handle file editing
if (isset($_POST['save_file'])) {
    $fileToEdit = sanitizePath($_POST['file_path']);
    $newContent = $_POST['file_content'];
    
    if (is_file($fileToEdit) && is_writable($fileToEdit)) {
        if (file_put_contents($fileToEdit, $newContent) !== false) {
            $message = 'File saved successfully';
            $messageType = 'success';
        } else {
            $message = 'Failed to save file. Check permissions.';
            $messageType = 'error';
        }
    } else {
        $message = 'File is not writable.';
        $messageType = 'error';
    }
}

// Handle file rename
if (isset($_POST['rename'])) {
    $oldPath = sanitizePath($_POST['old_path']);
    $newName = basename($_POST['new_name']);
    $newPath = dirname($oldPath) . '/' . $newName;
    
    if (file_exists($oldPath)) {
        if (rename($oldPath, $newPath)) {
            $message = 'Renamed successfully';
            $messageType = 'success';
        } else {
            $message = 'Failed to rename. Check permissions.';
            $messageType = 'error';
        }
    }
}

// Handle file copy
if (isset($_POST['copy'])) {
    $sourcePath = sanitizePath($_POST['source_path']);
    $destName = basename($_POST['dest_name']);
    $destPath = dirname($sourcePath) . '/' . $destName;
    
    if (is_file($sourcePath)) {
        if (copy($sourcePath, $destPath)) {
            chmod($destPath, 0644);
            $message = 'File copied successfully';
            $messageType = 'success';
        } else {
            $message = 'Failed to copy file. Check permissions.';
            $messageType = 'error';
        }
    }
}

// Handle permissions change
if (isset($_POST['chmod'])) {
    $filePath = sanitizePath($_POST['file_path']);
    $newPerms = $_POST['permissions'];
    
    // Validate octal permissions (e.g., 0644, 0755)
    if (preg_match('/^[0-7]{3,4}$/', $newPerms)) {
        $octalPerms = octdec($newPerms);
        if (chmod($filePath, $octalPerms)) {
            $message = 'Permissions changed successfully';
            $messageType = 'success';
        } else {
            $message = 'Failed to change permissions.';
            $messageType = 'error';
        }
    } else {
        $message = 'Invalid permission format. Use octal (e.g., 644, 755)';
        $messageType = 'error';
    }
}

// Handle file download
if (isset($_GET['download'])) {
    $fileToDownload = sanitizePath($_GET['download']);
    
    if (is_file($fileToDownload)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($fileToDownload) . '"');
        header('Content-Length: ' . filesize($fileToDownload));
        header('Pragma: public');
        readfile($fileToDownload);
        exit;
    }
}

// Handle file preview
$previewFile = null;
$previewContent = null;
$previewType = null;

if (isset($_GET['preview'])) {
    $previewFile = sanitizePath($_GET['preview']);
    
    if (is_file($previewFile) && is_readable($previewFile)) {
        $ext = getExtension($previewFile);
        
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'svg'])) {
            $previewType = 'image';
        } elseif ($ext === 'pdf') {
            $previewType = 'pdf';
        } else {
            $previewType = 'text';
            if (filesize($previewFile) <= MAX_PREVIEW_SIZE) {
                $previewContent = file_get_contents($previewFile);
            } else {
                $previewContent = "File too large to preview (max: " . formatSize(MAX_PREVIEW_SIZE) . ")";
            }
        }
    }
}

// Handle file edit view
$editFile = null;
$editContent = null;

if (isset($_GET['edit'])) {
    $editFile = sanitizePath($_GET['edit']);
    
    if (is_file($editFile) && is_readable($editFile)) {
        if (filesize($editFile) <= MAX_PREVIEW_SIZE) {
            $editContent = file_get_contents($editFile);
        }
    }
}

// Handle search
$searchResults = [];
$searchQuery = '';

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $searchQuery = $_GET['search'];
    $searchDir = $currentDir;
    
    function searchFiles($dir, $query, $rootDir) {
        $results = [];
        $items = @scandir($dir);
        
        if ($items === false) return $results;
        
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;
            
            $fullPath = $dir . '/' . $item;
            
            // Check if filename matches
            if (stripos($item, $query) !== false) {
                $results[] = [
                    'name' => $item,
                    'path' => $fullPath,
                    'is_dir' => is_dir($fullPath),
                    'size' => is_file($fullPath) ? filesize($fullPath) : 0,
                ];
            }
            
            // Recursively search subdirectories
            if (is_dir($fullPath) && is_readable($fullPath)) {
                $results = array_merge($results, searchFiles($fullPath, $query, $rootDir));
            }
        }
        
        return $results;
    }
    
    $searchResults = searchFiles($searchDir, $searchQuery, realpath(ROOT_DIR));
}

// Get directory contents
$files = [];
$dirs = [];

if (is_dir($currentDir)) {
    $items = scandir($currentDir);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        
        $fullPath = $currentDir . '/' . $item;
        $itemInfo = [
            'name' => $item,
            'path' => $fullPath,
            'size' => is_file($fullPath) ? filesize($fullPath) : 0,
            'modified' => filemtime($fullPath),
            'is_dir' => is_dir($fullPath),
            'is_readable' => is_readable($fullPath),
            'is_writable' => is_writable($fullPath),
        ];
        
        if ($itemInfo['is_dir']) {
            $dirs[] = $itemInfo;
        } else {
            $files[] = $itemInfo;
        }
    }
}

// Sort: directories first, then files, both alphabetically
usort($dirs, fn($a, $b) => strcasecmp($a['name'], $b['name']));
usort($files, fn($a, $b) => strcasecmp($a['name'], $b['name']));
$allItems = array_merge($dirs, $files);

// Get breadcrumb path
$breadcrumbs = [];
$rootReal = realpath(ROOT_DIR);
$currentReal = realpath($currentDir);
$relativePath = str_replace($rootReal, '', $currentReal);
$pathParts = array_filter(explode('/', $relativePath));

$breadcrumbPath = $rootReal;
$breadcrumbs[] = ['name' => 'Root', 'path' => $rootReal];

foreach ($pathParts as $part) {
    $breadcrumbPath .= '/' . $part;
    $breadcrumbs[] = ['name' => $part, 'path' => $breadcrumbPath];
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Server File Manager</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: #f5f5f5;
            color: #333;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .header-content {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .header h1 {
            font-size: 24px;
            font-weight: 600;
        }
        
        .logout-btn {
            background: rgba(255,255,255,0.2);
            color: white;
            padding: 8px 16px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 14px;
            transition: background 0.3s;
        }
        
        .logout-btn:hover {
            background: rgba(255,255,255,0.3);
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .breadcrumb {
            background: white;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .breadcrumb-item {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
        }
        
        .breadcrumb-item:hover {
            text-decoration: underline;
        }
        
        .breadcrumb-separator {
            margin: 0 10px;
            color: #999;
        }
        
        .actions {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }
        
        .action-card {
            border: 2px dashed #ddd;
            padding: 20px;
            border-radius: 8px;
            transition: border-color 0.3s;
        }
        
        .action-card:hover {
            border-color: #667eea;
        }
        
        .action-card h3 {
            font-size: 16px;
            margin-bottom: 15px;
            color: #333;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group:last-child {
            margin-bottom: 0;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            font-size: 14px;
            font-weight: 500;
            color: #555;
        }
        
        input[type="text"],
        input[type="file"] {
            width: 100%;
            padding: 10px;
            border: 2px solid #e0e0e0;
            border-radius: 5px;
            font-size: 14px;
        }
        
        input[type="file"] {
            padding: 8px;
        }
        
        button[type="submit"] {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }
        
        button[type="submit"]:hover {
            transform: translateY(-2px);
        }
        
        .message {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .message.success {
            background: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }
        
        .message.error {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }
        
        .file-list {
            background: white;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .file-list-header {
            background: #f8f9fa;
            padding: 15px 20px;
            border-bottom: 1px solid #e0e0e0;
            font-weight: 600;
            display: grid;
            grid-template-columns: 40px 1fr 120px 150px 150px 280px;
            gap: 15px;
            font-size: 14px;
            color: #666;
        }
        
        .file-item {
            padding: 15px 20px;
            border-bottom: 1px solid #f0f0f0;
            display: grid;
            grid-template-columns: 40px 1fr 120px 150px 150px 280px;
            gap: 15px;
            align-items: center;
            transition: background 0.2s;
        }
        
        .file-item:hover {
            background: #f8f9fa;
        }
        
        .file-item:last-child {
            border-bottom: none;
        }
        
        .file-icon {
            font-size: 24px;
        }
        
        .file-name {
            font-weight: 500;
            color: #333;
            word-break: break-all;
        }
        
        .file-name a {
            color: #667eea;
            text-decoration: none;
        }
        
        .file-name a:hover {
            text-decoration: underline;
        }
        
        .file-size,
        .file-modified {
            font-size: 13px;
            color: #666;
        }
        
        .file-actions {
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 6px 10px;
            border-radius: 4px;
            font-size: 11px;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: opacity 0.2s;
            white-space: nowrap;
        }
        
        .btn:hover {
            opacity: 0.8;
        }
        
        .btn-preview {
            background: #17a2b8;
            color: white;
        }
        
        .btn-edit {
            background: #ffc107;
            color: #333;
        }
        
        .btn-download {
            background: #28a745;
            color: white;
        }
        
        .btn-rename {
            background: #6c757d;
            color: white;
        }
        
        .btn-delete {
            background: #dc3545;
            color: white;
        }
        
        .btn-small {
            padding: 4px 8px;
            font-size: 10px;
        }
        
        .empty-state {
            padding: 60px 20px;
            text-align: center;
            color: #999;
        }
        
        .empty-state-icon {
            font-size: 48px;
            margin-bottom: 15px;
        }
        
        .info-box {
            background: #e7f3ff;
            border-left: 4px solid #2196F3;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            color: #0c5460;
        }
        
        .search-box {
            background: white;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .search-form {
            display: flex;
            gap: 10px;
        }
        
        .search-input {
            flex: 1;
            padding: 10px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 5px;
            font-size: 14px;
        }
        
        .search-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 10px 25px;
            border: none;
            border-radius: 5px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
        }
        
        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.7);
            overflow: auto;
        }
        
        .modal.active {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .modal-content {
            background: white;
            border-radius: 10px;
            max-width: 90%;
            max-height: 90vh;
            overflow: auto;
            position: relative;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
        }
        
        .modal-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 10px 10px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .modal-header h2 {
            margin: 0;
            font-size: 20px;
        }
        
        .modal-close {
            background: rgba(255,255,255,0.2);
            border: none;
            color: white;
            font-size: 24px;
            cursor: pointer;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.3s;
        }
        
        .modal-close:hover {
            background: rgba(255,255,255,0.3);
        }
        
        .modal-body {
            padding: 20px;
        }
        
        .code-preview {
            background: #282c34;
            color: #abb2bf;
            padding: 20px;
            border-radius: 5px;
            overflow-x: auto;
            font-family: 'Courier New', monospace;
            font-size: 13px;
            line-height: 1.6;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        
        .image-preview {
            text-align: center;
        }
        
        .image-preview img {
            max-width: 100%;
            height: auto;
            border-radius: 5px;
        }
        
        .pdf-preview {
            width: 100%;
            height: 80vh;
        }
        
        .pdf-preview iframe {
            width: 100%;
            height: 100%;
            border: none;
            border-radius: 5px;
        }
        
        .editor-container {
            width: 900px;
            max-width: 100%;
        }
        
        .editor-textarea {
            width: 100%;
            min-height: 500px;
            padding: 15px;
            border: 2px solid #e0e0e0;
            border-radius: 5px;
            font-family: 'Courier New', monospace;
            font-size: 13px;
            line-height: 1.6;
            resize: vertical;
        }
        
        .editor-actions {
            margin-top: 15px;
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }
        
        .file-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 15px;
        }
        
        .file-info-item {
            margin-bottom: 8px;
            font-size: 14px;
        }
        
        .file-info-item:last-child {
            margin-bottom: 0;
        }
        
        .file-info-label {
            font-weight: 600;
            color: #555;
        }
        
        .permissions-display {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            color: #666;
        }
        
        @media (max-width: 768px) {
            .file-list-header,
            .file-item {
                grid-template-columns: 30px 1fr;
                gap: 10px;
            }
            
            .file-size,
            .file-modified,
            .file-permissions,
            .file-list-header span:nth-child(3),
            .file-list-header span:nth-child(4),
            .file-list-header span:nth-child(5) {
                display: none;
            }
            
            .file-actions {
                grid-column: 1 / -1;
                margin-top: 10px;
            }
            
            .modal-content {
                max-width: 95%;
            }
            
            .editor-container {
                width: 100%;
            }
        }
    </style>
    <script>
        // Modal functions
        function openModal(modalId) {
            document.getElementById(modalId).classList.add('active');
        }
        
        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('active');
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.classList.remove('active');
            }
        }
        
        // Rename file
        function renameFile(path, currentName) {
            const newName = prompt('Enter new name:', currentName);
            if (newName && newName !== currentName) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="rename" value="1">
                    <input type="hidden" name="old_path" value="${path}">
                    <input type="hidden" name="new_name" value="${newName}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        // Copy file
        function copyFile(path, currentName) {
            const newName = prompt('Enter name for copy:', 'copy_of_' + currentName);
            if (newName) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="copy" value="1">
                    <input type="hidden" name="source_path" value="${path}">
                    <input type="hidden" name="dest_name" value="${newName}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        // Change permissions
        function changePermissions(path, currentPerms) {
            const newPerms = prompt('Enter new permissions (e.g., 644, 755):', currentPerms);
            if (newPerms) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="chmod" value="1">
                    <input type="hidden" name="file_path" value="${path}">
                    <input type="hidden" name="permissions" value="${newPerms}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <h1>🖥️ Server File Manager</h1>
            <a href="?logout" class="logout-btn">Logout</a>
        </div>
    </div>
    
    <div class="container">
        <div class="info-box">
            <strong>ℹ️ Current Server Path:</strong> <?php echo htmlspecialchars($currentDir); ?>
        </div>
        
        <?php if ($message): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <div class="breadcrumb">
            <?php foreach ($breadcrumbs as $i => $crumb): ?>
                <?php if ($i > 0): ?>
                    <span class="breadcrumb-separator">/</span>
                <?php endif; ?>
                <a href="?dir=<?php echo urlencode($crumb['path']); ?>" class="breadcrumb-item">
                    <?php echo htmlspecialchars($crumb['name']); ?>
                </a>
            <?php endforeach; ?>
        </div>
        
        <div class="search-box">
            <form method="GET" class="search-form">
                <input type="hidden" name="dir" value="<?php echo htmlspecialchars($currentDir); ?>">
                <input type="text" name="search" class="search-input" placeholder="🔍 Search files and directories..." value="<?php echo htmlspecialchars($searchQuery); ?>">
                <button type="submit" class="search-btn">Search</button>
                <?php if ($searchQuery): ?>
                    <a href="?dir=<?php echo urlencode($currentDir); ?>" class="btn btn-download">Clear</a>
                <?php endif; ?>
            </form>
        </div>
        
        <?php if ($searchQuery && !empty($searchResults)): ?>
            <div class="info-box">
                <strong>🔍 Search Results:</strong> Found <?php echo count($searchResults); ?> item(s) matching "<?php echo htmlspecialchars($searchQuery); ?>"
            </div>
            
            <div class="file-list">
                <div class="file-list-header">
                    <span></span>
                    <span>Name</span>
                    <span>Size</span>
                    <span>Path</span>
                    <span>Permissions</span>
                    <span>Actions</span>
                </div>
                
                <?php foreach ($searchResults as $item): ?>
                    <div class="file-item">
                        <div class="file-icon">
                            <?php echo getFileIcon($item['name'], $item['is_dir']); ?>
                        </div>
                        <div class="file-name">
                            <?php if ($item['is_dir']): ?>
                                <a href="?dir=<?php echo urlencode($item['path']); ?>">
                                    <?php echo htmlspecialchars($item['name']); ?>
                                </a>
                            <?php else: ?>
                                <?php echo htmlspecialchars($item['name']); ?>
                            <?php endif; ?>
                        </div>
                        <div class="file-size">
                            <?php echo $item['is_dir'] ? '-' : formatSize($item['size']); ?>
                        </div>
                        <div class="file-modified">
                            <?php echo htmlspecialchars(dirname(str_replace(realpath(ROOT_DIR), '', $item['path']))); ?>
                        </div>
                        <div class="permissions-display">
                            <?php echo formatPermissions($item['path']); ?>
                        </div>
                        <div class="file-actions">
                            <?php if (!$item['is_dir']): ?>
                                <?php if (canPreview($item['name'], $item['size'])): ?>
                                    <a href="?preview=<?php echo urlencode($item['path']); ?>&dir=<?php echo urlencode($currentDir); ?>" class="btn btn-preview">View</a>
                                <?php endif; ?>
                                <?php if (canEdit($item['name'], $item['size'])): ?>
                                    <a href="?edit=<?php echo urlencode($item['path']); ?>&dir=<?php echo urlencode($currentDir); ?>" class="btn btn-edit">Edit</a>
                                <?php endif; ?>
                                <a href="?download=<?php echo urlencode($item['path']); ?>" class="btn btn-download">Download</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php elseif ($searchQuery): ?>
            <div class="empty-state">
                <div class="empty-state-icon">🔍</div>
                <p>No files found matching "<?php echo htmlspecialchars($searchQuery); ?>"</p>
            </div>
        <?php endif; ?>
        
        <?php if (!$searchQuery): ?>
        <div class="actions">
            <div class="actions-grid">
                <div class="action-card">
                    <h3>📤 Upload File</h3>
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="upload_dir" value="<?php echo htmlspecialchars($currentDir); ?>">
                        <div class="form-group">
                            <label>Select File (Max: <?php echo formatSize(MAX_UPLOAD_SIZE); ?>)</label>
                            <input type="file" name="file" required>
                        </div>
                        <button type="submit" name="upload">Upload</button>
                    </form>
                </div>
                
                <div class="action-card">
                    <h3>📁 Create Directory</h3>
                    <form method="POST">
                        <div class="form-group">
                            <label>Directory Name</label>
                            <input type="text" name="dir_name" required placeholder="new-folder">
                        </div>
                        <button type="submit" name="create_dir">Create</button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="file-list">
            <div class="file-list-header">
                <span></span>
                <span>Name</span>
                <span>Size</span>
                <span>Modified</span>
                <span>Permissions</span>
                <span>Actions</span>
            </div>
            
            <?php if ($currentDir !== $rootReal): ?>
                <div class="file-item">
                    <div class="file-icon">⬆️</div>
                    <div class="file-name">
                        <a href="?dir=<?php echo urlencode(dirname($currentDir)); ?>">..</a>
                    </div>
                    <div class="file-size">-</div>
                    <div class="file-modified">-</div>
                    <div class="permissions-display">-</div>
                    <div class="file-actions"></div>
                </div>
            <?php endif; ?>
            
            <?php if (empty($allItems)): ?>
                <div class="empty-state">
                    <div class="empty-state-icon">📭</div>
                    <p>This directory is empty</p>
                </div>
            <?php else: ?>
                <?php foreach ($allItems as $item): ?>
                    <div class="file-item">
                        <div class="file-icon">
                            <?php echo getFileIcon($item['name'], $item['is_dir']); ?>
                        </div>
                        <div class="file-name">
                            <?php if ($item['is_dir']): ?>
                                <a href="?dir=<?php echo urlencode($item['path']); ?>">
                                    <?php echo htmlspecialchars($item['name']); ?>
                                </a>
                            <?php else: ?>
                                <?php echo htmlspecialchars($item['name']); ?>
                            <?php endif; ?>
                        </div>
                        <div class="file-size">
                            <?php echo $item['is_dir'] ? '-' : formatSize($item['size']); ?>
                        </div>
                        <div class="file-modified">
                            <?php echo date('Y-m-d H:i', $item['modified']); ?>
                        </div>
                        <div class="permissions-display">
                            <a href="javascript:void(0)" onclick="changePermissions('<?php echo htmlspecialchars($item['path'], ENT_QUOTES); ?>', '<?php echo substr(sprintf('%o', fileperms($item['path'])), -3); ?>')" title="Click to change permissions">
                                <?php echo formatPermissions($item['path']); ?>
                            </a>
                        </div>
                        <div class="file-actions">
                            <?php if (!$item['is_dir']): ?>
                                <?php if (canPreview($item['name'], $item['size'])): ?>
                                    <a href="?preview=<?php echo urlencode($item['path']); ?>&dir=<?php echo urlencode($currentDir); ?>" class="btn btn-preview">View</a>
                                <?php endif; ?>
                                <?php if (canEdit($item['name'], $item['size'])): ?>
                                    <a href="?edit=<?php echo urlencode($item['path']); ?>&dir=<?php echo urlencode($currentDir); ?>" class="btn btn-edit">Edit</a>
                                <?php endif; ?>
                                <a href="?download=<?php echo urlencode($item['path']); ?>" class="btn btn-download">Download</a>
                                <button type="button" onclick="copyFile('<?php echo htmlspecialchars($item['path'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($item['name'], ENT_QUOTES); ?>')" class="btn btn-small btn-rename">Copy</button>
                            <?php endif; ?>
                            <button type="button" onclick="renameFile('<?php echo htmlspecialchars($item['path'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($item['name'], ENT_QUOTES); ?>')" class="btn btn-small btn-rename">Rename</button>
                            <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this <?php echo $item['is_dir'] ? 'directory' : 'file'; ?>?');">
                                <input type="hidden" name="file_path" value="<?php echo htmlspecialchars($item['path']); ?>">
                                <button type="submit" name="delete" class="btn btn-small btn-delete">Delete</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Preview Modal -->
    <?php if ($previewFile): ?>
    <div id="previewModal" class="modal active">
        <div class="modal-content">
            <div class="modal-header">
                <h2>📄 <?php echo htmlspecialchars(basename($previewFile)); ?></h2>
                <button class="modal-close" onclick="window.location.href='?dir=<?php echo urlencode($currentDir); ?>'">&times;</button>
            </div>
            <div class="modal-body">
                <div class="file-info">
                    <div class="file-info-item">
                        <span class="file-info-label">File:</span> <?php echo htmlspecialchars(basename($previewFile)); ?>
                    </div>
                    <div class="file-info-item">
                        <span class="file-info-label">Size:</span> <?php echo formatSize(filesize($previewFile)); ?>
                    </div>
                    <div class="file-info-item">
                        <span class="file-info-label">Modified:</span> <?php echo date('Y-m-d H:i:s', filemtime($previewFile)); ?>
                    </div>
                    <div class="file-info-item">
                        <span class="file-info-label">Permissions:</span> <?php echo formatPermissions($previewFile); ?>
                    </div>
                </div>
                
                <?php if ($previewType === 'image'): ?>
                    <div class="image-preview">
                        <img src="data:image/<?php echo getExtension($previewFile); ?>;base64,<?php echo base64_encode(file_get_contents($previewFile)); ?>" alt="Preview">
                    </div>
                <?php elseif ($previewType === 'pdf'): ?>
                    <div class="pdf-preview">
                        <iframe src="data:application/pdf;base64,<?php echo base64_encode(file_get_contents($previewFile)); ?>"></iframe>
                    </div>
                <?php else: ?>
                    <div class="code-preview"><?php echo htmlspecialchars($previewContent); ?></div>
                <?php endif; ?>
                
                <div class="editor-actions">
                    <?php if (canEdit(basename($previewFile), filesize($previewFile))): ?>
                        <a href="?edit=<?php echo urlencode($previewFile); ?>&dir=<?php echo urlencode($currentDir); ?>" class="btn btn-edit">Edit File</a>
                    <?php endif; ?>
                    <a href="?download=<?php echo urlencode($previewFile); ?>" class="btn btn-download">Download</a>
                    <a href="?dir=<?php echo urlencode($currentDir); ?>" class="btn btn-rename">Close</a>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Edit Modal -->
    <?php if ($editFile): ?>
    <div id="editModal" class="modal active">
        <div class="modal-content editor-container">
            <div class="modal-header">
                <h2>✏️ Edit: <?php echo htmlspecialchars(basename($editFile)); ?></h2>
                <button class="modal-close" onclick="if(confirm('Discard changes?')) window.location.href='?dir=<?php echo urlencode($currentDir); ?>'">&times;</button>
            </div>
            <div class="modal-body">
                <div class="file-info">
                    <div class="file-info-item">
                        <span class="file-info-label">File:</span> <?php echo htmlspecialchars($editFile); ?>
                    </div>
                    <div class="file-info-item">
                        <span class="file-info-label">Size:</span> <?php echo formatSize(filesize($editFile)); ?>
                    </div>
                    <div class="file-info-item">
                        <span class="file-info-label">Language:</span> <?php echo getLanguage(basename($editFile)); ?>
                    </div>
                </div>
                
                <form method="POST">
                    <input type="hidden" name="file_path" value="<?php echo htmlspecialchars($editFile); ?>">
                    <textarea name="file_content" class="editor-textarea" required><?php echo htmlspecialchars($editContent); ?></textarea>
                    <div class="editor-actions">
                        <button type="submit" name="save_file" class="btn btn-download">💾 Save Changes</button>
                        <a href="?dir=<?php echo urlencode($currentDir); ?>" class="btn btn-rename">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>
</body>
</html>
