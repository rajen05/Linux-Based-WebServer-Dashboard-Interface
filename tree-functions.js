// Tree Management
let expandedFolders = new Set();
let folderCache = {};

async function initTree() {
    await loadRootFolders();
    renderTree();
}

async function loadRootFolders() {
    try {
        const f = new FormData();
        f.append('action', 'get_folders');
        f.append('path', '/');
        const r = await fetch('', { method: 'POST', body: f });
        const d = await r.json();
        if (d.success) {
            folderCache['/'] = d.folders;
        }
    } catch (e) {
        console.error('Failed to load root folders:', e);
    }
}

async function loadFolders(path) {
    if (folderCache[path]) return folderCache[path];
    try {
        const f = new FormData();
        f.append('action', 'get_folders');
        f.append('path', path);
        const r = await fetch('', { method: 'POST', body: f });
        const d = await r.json();
        if (d.success) {
            folderCache[path] = d.folders;
            return d.folders;
        }
    } catch (e) {
        console.error('Failed to load folders:', e);
    }
    return [];
}

function renderTree() {
    const sidebar = document.getElementById('sidebar');
    const hasExpanded = expandedFolders.size > 0;
    let html = '<div class="sidebar-section"><div class="sidebar-title"><span>📁 Full Directory</span><button class="expand-all-btn" onclick="toggleExpandAll()">';
    html += hasExpanded ? '<i class="fas fa-compress-alt"></i> Collapse All' : '<i class="fas fa-expand-alt"></i> Expand All';
    html += '</button></div>';
    html += renderFolder('/', '/', true);
    html += '</div>';
    sidebar.innerHTML = html;
}

function renderFolder(path, name, isRoot = false) {
    const folders = folderCache[path] || [];
    const isExpanded = expandedFolders.has(path);
    let html = '';
    
    if (!isRoot) {
        const isPinned = pinnedFolders.some(p => p.path === path);
        const hasChildren = folders.length > 0 || !folderCache[path];
        const toggleIcon = hasChildren 
            ? `<span class="tree-toggle ${!isExpanded ? 'collapsed' : ''}" onclick="event.stopPropagation();toggleFolder('${path}')">▼</span>` 
            : '<span style="width:18px"></span>';
        const pinIcon = isPinned ? '<i class="fas fa-star" style="color:gold"></i>' : '<i class="fas fa-thumbtack"></i>';
        
        html += `<div class="tree-item has-children" onclick="navigateTo('${path}')" ondragover="handleTreeDragOver(event,'${path}')" ondragleave="handleTreeDragLeave(event)" ondrop="handleTreeDrop(event,'${path}')">`;
        html += toggleIcon;
        html += '<i class="fas fa-folder tree-folder-icon"></i>';
        html += `<span class="tree-name">${escapeHtml(name)}</span>`;
        html += `<button class="pin-btn" onclick="event.stopPropagation();${isPinned ? 'unpinFolder' : 'pinFolder'}('${path}','${escapeHtml(name)}')" title="${isPinned ? 'Unpin' : 'Pin'} folder">${pinIcon}</button>`;
        html += '</div>';
    }
    
    if (isExpanded || isRoot) {
        html += '<div class="tree-children">';
        folders.forEach(f => {
            html += renderFolder(f.path, f.name);
        });
        html += '</div>';
    }
    
    return html;
}

async function toggleFolder(path) {
    if (expandedFolders.has(path)) {
        expandedFolders.delete(path);
    } else {
        if (!folderCache[path]) {
            await loadFolders(path);
        }
        expandedFolders.add(path);
    }
    renderTree();
}

async function toggleExpandAll() {
    if (expandedFolders.size > 0) {
        expandedFolders.clear();
        renderTree();
    } else {
        await expandAll();
    }
}

async function expandAll() {
    const toExpand = [];
    
    function collectFolders(path) {
        const folders = folderCache[path] || [];
        folders.forEach(f => {
            toExpand.push(f.path);
        });
    }
    
    collectFolders('/');
    
    for (const path of toExpand) {
        if (!folderCache[path]) {
            await loadFolders(path);
        }
        expandedFolders.add(path);
        collectFolders(path);
    }
    
    renderTree();
}
