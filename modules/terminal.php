<?php
// Security: Prevent direct access
if (!defined('PANEL_ACCESS')) {
    die('Direct access not permitted');
}

// Initialize session for command history
if (!isset($_SESSION['terminal_history'])) {
    $_SESSION['terminal_history'] = [];
}

if (!isset($_SESSION['terminal_output'])) {
    $_SESSION['terminal_output'] = [];
}

// Handle command execution
if (isset($_POST['command'])) {
    $command = trim($_POST['command']);
    
    if (!empty($command)) {
        // Add to history
        $_SESSION['terminal_history'][] = $command;
        
        // Special commands
        if ($command === 'clear') {
            $_SESSION['terminal_output'] = [];
        } elseif ($command === 'exit') {
            $_SESSION['terminal_output'][] = [
                'command' => $command,
                'output' => 'Use the logout button to exit the panel.'
            ];
        } else {
            // Execute command
            $output = [];
            $return_var = 0;
            
            // Security: Prevent some dangerous commands
            $dangerous = ['rm -rf /', 'mkfs', 'dd if=', ':(){:|:&};:'];
            $isDangerous = false;
            
            foreach ($dangerous as $danger) {
                if (stripos($command, $danger) !== false) {
                    $isDangerous = true;
                    break;
                }
            }
            
            if ($isDangerous) {
                $output[] = "⚠️ DANGEROUS COMMAND BLOCKED FOR SAFETY";
            } else {
                exec($command . ' 2>&1', $output, $return_var);
            }
            
            $_SESSION['terminal_output'][] = [
                'command' => $command,
                'output' => implode("\n", $output),
                'return' => $return_var
            ];
        }
        
        // Keep only last 50 commands
        if (count($_SESSION['terminal_output']) > 50) {
            $_SESSION['terminal_output'] = array_slice($_SESSION['terminal_output'], -50);
        }
    }
    
    // Redirect to prevent form resubmission
    header('Location: ' . buildUrl('terminal'));
    exit;
}

// Handle clear
if (isset($_GET['clear'])) {
    $_SESSION['terminal_output'] = [];
    header('Location: ' . buildUrl('terminal'));
    exit;
}

$currentDir = getcwd();
$user = get_current_user();
$hostname = php_uname('n');
?>

<div class="alert alert-warning">
    <strong>⚠️ Warning:</strong> Be careful with commands! You have full server access. Dangerous commands are blocked for safety.
</div>

<div class="card">
    <div class="card-header">
        💻 Web Terminal
        <div style="float: right;">
            <a href="<?php echo buildUrl('terminal', ['clear' => '1']); ?>" class="btn btn-sm btn-danger">Clear</a>
        </div>
    </div>
    <div class="card-body">
        <div class="terminal">
            <div class="terminal-output">
                <?php if (empty($_SESSION['terminal_output'])): ?>
                <div style="color: #00d787; text-shadow: 0 0 10px rgba(0, 215, 135, 0.5);">
┌─────────────────────────────────────────────────────────────┐
│  🖥️  SERVER CONTROL PANEL - WEB TERMINAL                   │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  <span style="color: #00ff00;">●</span> Type commands and press Enter to execute              │
│  <span style="color: #00ff00;">●</span> Type 'clear' to clear the screen                      │
│  <span style="color: #00ff00;">●</span> Use quick command buttons below                        │
│                                                             │
│  <span style="color: #ffd700;">Directory:</span> <?php echo str_pad($currentDir, 43); ?> │
│  <span style="color: #ffd700;">User:</span> <?php echo str_pad($user . '@' . $hostname, 48); ?> │
│                                                             │
└─────────────────────────────────────────────────────────────┘
                </div>
                <?php else: ?>
                    <?php foreach ($_SESSION['terminal_output'] as $entry): ?>
                        <div style="margin-bottom: 20px; padding: 10px; background: rgba(0, 255, 0, 0.02); border-left: 3px solid #00d787; border-radius: 4px;">
                            <div class="terminal-prompt" style="margin-bottom: 8px;">
                                <span style="color: #00d787; font-weight: bold;"><?php echo $user; ?>@<?php echo $hostname; ?></span><span style="color: #666;">:</span><span style="color: #5c9fd8;"><?php echo $currentDir; ?></span><span style="color: #00ff00; font-weight: bold;">$</span> 
                                <span class="terminal-command" style="color: #00ff00; text-shadow: 0 0 5px rgba(0, 255, 0, 0.3);"><?php echo htmlspecialchars($entry['command']); ?></span>
                            </div>
                            <?php if (!empty($entry['output'])): ?>
                                <div style="color: #cccccc; margin-top: 8px; padding-left: 10px; white-space: pre-wrap; font-size: 13px; line-height: 1.5;">
<?php echo htmlspecialchars($entry['output']); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        
        <form method="POST" class="terminal-input">
            <span style="color: #00d787; font-family: 'Consolas', 'Monaco', monospace; font-weight: bold; font-size: 14px;">
                <?php echo $user; ?>@<?php echo $hostname; ?><span style="color: #666;">:</span><span style="color: #5c9fd8;"><?php echo $currentDir; ?></span><span style="color: #00ff00;">$</span>
            </span>
            <input type="text" name="command" autofocus autocomplete="off" placeholder="Type command here...">
            <button type="submit" class="btn btn-success" style="padding: 10px 25px; font-weight: bold;">▶ Execute</button>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">📝 Common Commands</div>
    <div class="card-body">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 10px;">
            <button onclick="document.querySelector('input[name=command]').value='ls -la'; document.querySelector('input[name=command]').focus();" class="btn btn-sm btn-secondary">
                ls -la (List files)
            </button>
            <button onclick="document.querySelector('input[name=command]').value='pwd'; document.querySelector('input[name=command]').focus();" class="btn btn-sm btn-secondary">
                pwd (Current directory)
            </button>
            <button onclick="document.querySelector('input[name=command]').value='df -h'; document.querySelector('input[name=command]').focus();" class="btn btn-sm btn-secondary">
                df -h (Disk usage)
            </button>
            <button onclick="document.querySelector('input[name=command]').value='free -h'; document.querySelector('input[name=command]').focus();" class="btn btn-sm btn-secondary">
                free -h (Memory usage)
            </button>
            <button onclick="document.querySelector('input[name=command]').value='top -bn1 | head -20'; document.querySelector('input[name=command]').focus();" class="btn btn-sm btn-secondary">
                top (Processes)
            </button>
            <button onclick="document.querySelector('input[name=command]').value='netstat -tulpn'; document.querySelector('input[name=command]').focus();" class="btn btn-sm btn-secondary">
                netstat (Network)
            </button>
            <button onclick="document.querySelector('input[name=command]').value='systemctl status apache2'; document.querySelector('input[name=command]').focus();" class="btn btn-sm btn-secondary">
                Apache status
            </button>
            <button onclick="document.querySelector('input[name=command]').value='ps aux | grep php'; document.querySelector('input[name=command]').focus();" class="btn btn-sm btn-secondary">
                PHP processes
            </button>
        </div>
    </div>
</div>

<script>
// Auto-scroll terminal to bottom
const terminalOutput = document.querySelector('.terminal-output');
if (terminalOutput) {
    terminalOutput.scrollTop = terminalOutput.scrollHeight;
}

// Focus input on page load
document.querySelector('input[name=command]').focus();
</script>
