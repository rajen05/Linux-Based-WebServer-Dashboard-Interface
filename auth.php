<?php
/**
 * Authentication Handler
 */

// Security: Prevent direct access
if (!defined('PANEL_ACCESS')) {
    die('Direct access not permitted');
}

session_start();

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true;
}

/**
 * Login user
 */
function login($password) {
    if ($password === ADMIN_PASSWORD) {
        $_SESSION['authenticated'] = true;
        $_SESSION['login_time'] = time();
        $_SESSION['user_ip'] = $_SERVER['REMOTE_ADDR'];
        return true;
    }
    return false;
}

/**
 * Logout user
 */
function logout() {
    session_destroy();
    header('Location: index.php');
    exit;
}

/**
 * Require authentication
 */
function requireAuth() {
    if (!isLoggedIn()) {
        showLoginPage();
        exit;
    }
}

/**
 * Show login page
 */
function showLoginPage() {
    $error = '';
    
    if (isset($_POST['login'])) {
        if (login($_POST['password'])) {
            header('Location: index.php');
            exit;
        } else {
            $error = 'Invalid password';
        }
    }
    
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo PANEL_TITLE; ?> - Login</title>
        <link rel="stylesheet" href="assets/css/style.css">
    </head>
    <body class="login-page">
        <div class="login-container">
            <div class="login-header">
                <h1>🖥️ <?php echo PANEL_TITLE; ?></h1>
                <p>Server: <?php echo SERVER_IP; ?></p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <form method="POST" class="login-form">
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required autofocus>
                </div>
                <button type="submit" name="login" class="btn btn-primary btn-block">Login</button>
            </form>
            
            <div class="login-footer">
                <small>⚠️ Change default password in config.php</small>
            </div>
        </div>
    </body>
    </html>
    <?php
}

// Handle logout
if (isset($_GET['logout'])) {
    logout();
}
