// Server Control Panel - Main JavaScript

// Auto-refresh system stats every 5 seconds
if (window.location.search.includes('module=dashboard') || window.location.search.includes('module=monitor')) {
    setInterval(() => {
        location.reload();
    }, 5000);
}

// Confirm dangerous actions
document.querySelectorAll('[data-confirm]').forEach(element => {
    element.addEventListener('click', (e) => {
        if (!confirm(element.dataset.confirm)) {
            e.preventDefault();
        }
    });
});

// Terminal auto-scroll
const terminal = document.querySelector('.terminal-output');
if (terminal) {
    terminal.scrollTop = terminal.scrollHeight;
}

// Copy to clipboard
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        alert('Copied to clipboard!');
    });
}

// Format bytes
function formatBytes(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
}

// Show loading
function showLoading(element) {
    element.innerHTML = '<span class="loading">⏳ Loading...</span>';
    element.disabled = true;
}

// Hide loading
function hideLoading(element, originalText) {
    element.innerHTML = originalText;
    element.disabled = false;
}

console.log('Server Control Panel loaded');
