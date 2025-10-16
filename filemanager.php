<?php
session_start();
define('ROOT_DIR', '/');
define('MAX_UPLOAD_SIZE', 52428800);
define('ALLOWED_EXTENSIONS', array('html','php','js','css','txt','jpg','jpeg','png','gif','svg','zip','pdf','json','xml','md'));
define('EDITABLE_EXTENSIONS', array('html','php','js','css','txt','json','xml','md'));

function sanitizePath($path) {
    $path = str_replace(chr(0), '', $path);
    $path = str_replace('\\', '/', $path);
    if (empty($path)) $path = '/';
    $realPath = realpath($path);
    if ($realPath === false) {
        $parent = dirname($path);
        $realPath = realpath($parent);
        if ($realPath === false) return '/';
    }
    return $realPath;
}

function getExt($f) { return strtolower(pathinfo($f, PATHINFO_EXTENSION)); }
function isAllowed($f) { return in_array(getExt($f), ALLOWED_EXTENSIONS); }
function isEdit($f) { return in_array(getExt($f), EDITABLE_EXTENSIONS); }
function fmtSize($b) {
    $u = array('B','KB','MB','GB','TB');
    $b = max($b, 0);
    $p = floor(($b ? log($b) : 0) / log(1024));
    $p = min($p, count($u) - 1);
    return round($b / pow(1024, $p), 2) . ' ' . $u[$p];
}

function getIcon($f, $d) {
    if ($d) return 'folder';
    $e = getExt($f);
    $m = array('html'=>'code','php'=>'code','js'=>'code','css'=>'code','txt'=>'text','json'=>'code','xml'=>'code','md'=>'text','jpg'=>'image','jpeg'=>'image','png'=>'image','gif'=>'image','svg'=>'image','pdf'=>'text','zip'=>'archive');
    return isset($m[$e]) ? $m[$e] : 'file';
}

function getPerms($f) {
    $p = fileperms($f);
    return (($p&0x0100)?'r':'-').(($p&0x0080)?'w':'-').(($p&0x0040)?'x':'-').(($p&0x0020)?'r':'-').(($p&0x0010)?'w':'-').(($p&0x0008)?'x':'-').(($p&0x0004)?'r':'-').(($p&0x0002)?'w':'-').(($p&0x0001)?'x':'-');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    $res = array('success' => false, 'message' => '');
    try {
        switch ($_POST['action']) {
            case 'upload':
                if (!isset($_FILES['file'])) throw new Exception('No file');
                $dir = sanitizePath($_POST['dir']);
                $name = basename($_FILES['file']['name']);
                $target = $dir . '/' . $name;
                if ($_FILES['file']['size'] > MAX_UPLOAD_SIZE) throw new Exception('Too large');
                if (!isAllowed($name)) throw new Exception('Not allowed');
                if (move_uploaded_file($_FILES['file']['tmp_name'], $target)) {
                    chmod($target, 0644);
                    $res = array('success' => true, 'message' => 'Uploaded');
                } else throw new Exception('Failed');
                break;
            case 'create_folder':
                $dir = sanitizePath($_POST['dir']);
                $name = basename($_POST['name']);
                if (mkdir($dir . '/' . $name, 0755)) $res = array('success' => true, 'message' => 'Created');
                else throw new Exception('Failed');
                break;
            case 'rename':
                $old = sanitizePath($_POST['old_path']);
                $new = basename($_POST['new_name']);
                if (rename($old, dirname($old) . '/' . $new)) $res = array('success' => true, 'message' => 'Renamed');
                else throw new Exception('Failed');
                break;
            case 'delete':
                $path = sanitizePath($_POST['path']);
                if (is_file($path)) {
                    if (unlink($path)) $res = array('success' => true, 'message' => 'Deleted');
                    else throw new Exception('Failed');
                } elseif (is_dir($path)) {
                    if (rmdir($path)) $res = array('success' => true, 'message' => 'Deleted');
                    else throw new Exception('Must be empty');
                }
                break;
            case 'get_content':
                $path = sanitizePath($_POST['path']);
                if (is_file($path) && isEdit(basename($path))) $res = array('success' => true, 'content' => file_get_contents($path));
                else throw new Exception('Cannot edit');
                break;
            case 'save_content':
                $path = sanitizePath($_POST['path']);
                if (is_file($path) && isEdit(basename($path))) {
                    if (file_put_contents($path, $_POST['content']) !== false) $res = array('success' => true, 'message' => 'Saved');
                    else throw new Exception('Failed');
                } else throw new Exception('Cannot edit');
                break;
            case 'get_files':
                $dir = sanitizePath($_POST['dir']);
                $files = array();
                if (is_dir($dir)) {
                    foreach (scandir($dir) as $item) {
                        if ($item === '.' || $item === '..') continue;
                        $full = $dir . '/' . $item;
                        $isDir = is_dir($full);
                        $files[] = array('name'=>$item,'path'=>$full,'type'=>$isDir?'folder':'file','size'=>$isDir?0:filesize($full),'modified'=>filemtime($full),'permissions'=>getPerms($full),'icon'=>getIcon($item,$isDir),'editable'=>!$isDir&&isEdit($item));
                    }
                    usort($files, function($a,$b){ return $a['type']===$b['type']?strcasecmp($a['name'],$b['name']):($a['type']==='folder'?-1:1); });
                }
                $res = array('success' => true, 'files' => $files);
                break;
            case 'move':
                $source = sanitizePath($_POST['source']);
                $destDir = sanitizePath($_POST['dest_dir']);
                $fileName = basename($source);
                $destination = $destDir . '/' . $fileName;
                if (file_exists($destination)) throw new Exception('File already exists');
                if (rename($source, $destination)) $res = array('success' => true, 'message' => 'Moved');
                else throw new Exception('Failed to move');
                break;
            case 'get_tree':
                function buildTree($d, $depth = 0, $maxDepth = 2) {
                    if ($depth >= $maxDepth) return array();
                    $t = array();
                    $items = @scandir($d);
                    if ($items === false) return $t;
                    foreach ($items as $i) {
                        if ($i==='.'||$i==='..') continue;
                        $f = $d . DIRECTORY_SEPARATOR . $i;
                        if (is_dir($f)) {
                            $t[] = array('name'=>$i,'path'=>$f,'children'=>buildTree($f, $depth + 1, $maxDepth));
                        }
                    }
                    usort($t, function($a,$b){ return strcasecmp($a['name'],$b['name']); });
                    return $t;
                }
                if (DIRECTORY_SEPARATOR === '\\\\') {
                    $drives = array();
                    for ($l = 'A'; $l <= 'Z'; $l++) {
                        $drive = $l . ':\\\\';
                        if (is_dir($drive)) {
                            $drives[] = array('name'=>$l.':','path'=>$drive,'children'=>buildTree($drive, 0, 2));
                        }
                    }
                    $res = array('success'=>true,'tree'=>$drives);
                } else {
                    $res = array('success'=>true,'tree'=>array(array('name'=>'/','path'=>'/','children'=>buildTree('/', 0, 2))));
                }
                break;
        }
    } catch (Exception $e) { $res = array('success'=>false,'message'=>$e->getMessage()); }
    echo json_encode($res);
    exit;
}

if (isset($_GET['download'])) {
    $file = sanitizePath($_GET['download']);
    if (is_file($file)) {
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="'.basename($file).'"');
        header('Content-Length: '.filesize($file));
        readfile($file);
        exit;
    }
}

$currentDir = isset($_GET['dir']) ? sanitizePath($_GET['dir']) : '/';
if (DIRECTORY_SEPARATOR === '\\') {
    $currentDir = str_replace('/', '\\', $currentDir);
}
$breadcrumbs = array();
$pathParts = array_filter(explode(DIRECTORY_SEPARATOR, $currentDir));
$breadcrumbPath = '';
if (DIRECTORY_SEPARATOR === '\\') {
    if (count($pathParts) > 0) {
        $drive = array_shift($pathParts);
        $breadcrumbPath = $drive . '\\';
        $breadcrumbs[] = array('name'=>$drive,'path'=>$breadcrumbPath);
    }
} else {
    $breadcrumbs[] = array('name'=>'/','path'=>'/');
    $breadcrumbPath = '';
}
foreach ($pathParts as $part) {
    $breadcrumbPath .= ($breadcrumbPath && $breadcrumbPath !== '/' ? DIRECTORY_SEPARATOR : '') . $part;
    if (DIRECTORY_SEPARATOR === '\\' && !strpos($breadcrumbPath, ':\\')) {
        $breadcrumbPath .= '\\';
    }
    $breadcrumbs[] = array('name'=>$part,'path'=>$breadcrumbPath);
}
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>File Manager</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
*{margin:0;padding:0;box-sizing:border-box}:root{--bg:#fff;--bg2:#f5f5f5;--text:#000;--text2:#666;--border:#ddd;--accent:#0078d4;--hover:#e5f3ff;--selected:#cce8ff}[data-theme=dark]{--bg:#1e1e1e;--bg2:#252526;--text:#ccc;--text2:#858585;--border:#3e3e42;--accent:#0e639c;--hover:#2a2d2e;--selected:#094771}body{font-family:system-ui,-apple-system,sans-serif;background:var(--bg2);color:var(--text);height:100vh;display:flex;flex-direction:column;overflow:hidden}.header{background:var(--bg);border-bottom:1px solid var(--border);padding:12px 20px;display:flex;justify-content:space-between;align-items:center}.header-title{font-size:18px;font-weight:600}.theme-toggle{background:var(--bg2);border:1px solid var(--border);color:var(--text);padding:8px 16px;border-radius:4px;cursor:pointer;transition:.2s}.theme-toggle:hover{background:var(--accent);color:#fff}.toolbar{background:var(--bg);border-bottom:1px solid var(--border);padding:10px 20px;display:flex;gap:8px;flex-wrap:wrap}.toolbar-btn{background:var(--bg);border:1px solid var(--border);color:var(--text);padding:8px 16px;border-radius:4px;cursor:pointer;font-size:13px;transition:.2s}.toolbar-btn:hover{background:var(--hover)}.toolbar-btn:disabled{opacity:.5;cursor:not-allowed}.toolbar-btn.primary{background:var(--accent);color:#fff}.toolbar-btn.danger{background:#d13438;color:#fff}.view-toggle{display:flex;gap:4px;margin-left:auto;border:1px solid var(--border);border-radius:4px}.view-toggle-btn{background:var(--bg);border:none;padding:8px 12px;cursor:pointer;transition:.2s}.view-toggle-btn.active{background:var(--accent);color:#fff}.address-bar-container{background:var(--bg);border-bottom:1px solid var(--border);padding:10px 20px;display:flex;align-items:center;gap:10px}.address-bar-label{font-size:13px;color:var(--text2);white-space:nowrap}.address-bar{flex:1;display:flex;align-items:center;background:var(--bg2);border:1px solid var(--border);border-radius:4px;padding:6px 10px;font-size:13px;cursor:text;transition:.2s}.address-bar:hover{border-color:var(--accent)}.address-bar:focus-within{border-color:var(--accent);box-shadow:0 0 0 2px rgba(0,120,212,.1)}.address-bar-input{flex:1;background:transparent;border:none;outline:none;color:var(--text);font-family:inherit;font-size:13px}.address-bar-segments{flex:1;display:flex;align-items:center;gap:4px;flex-wrap:wrap}.address-segment{padding:4px 8px;border-radius:3px;cursor:pointer;transition:.2s;white-space:nowrap}.address-segment:hover{background:var(--hover)}.address-separator{color:var(--text2);user-select:none}.address-bar-icon{color:var(--text2);margin-right:6px}.search-container{background:var(--bg);border-bottom:1px solid var(--border);padding:10px 20px;display:flex;align-items:center;gap:10px}.search-box{flex:1;max-width:400px;display:flex;align-items:center;background:var(--bg2);border:1px solid var(--border);border-radius:4px;padding:6px 12px;transition:.2s}.search-box:focus-within{border-color:var(--accent);box-shadow:0 0 0 2px rgba(0,120,212,.1)}.search-box i{color:var(--text2);font-size:14px;margin-right:8px}.search-input{flex:1;background:transparent;border:none;outline:none;color:var(--text);font-family:inherit;font-size:13px}.search-input::placeholder{color:var(--text2)}.search-clear{background:none;border:none;color:var(--text2);cursor:pointer;padding:4px;border-radius:3px;display:none;transition:.2s}.search-clear:hover{background:var(--hover);color:var(--text)}.search-clear.visible{display:block}.search-results-info{font-size:13px;color:var(--text2);margin-left:auto}.breadcrumb{background:var(--bg);border-bottom:1px solid var(--border);padding:10px 20px;display:none;gap:8px;font-size:13px;overflow-x:auto}.breadcrumb-item{color:var(--text);text-decoration:none;padding:4px 8px;border-radius:3px}.breadcrumb-item:hover{background:var(--hover)}.main-container{display:flex;flex:1;overflow:hidden}.sidebar{width:250px;background:var(--bg2);border-right:1px solid var(--border);overflow-y:auto;padding:10px}.sidebar-section{margin-bottom:20px}.sidebar-title{font-size:11px;font-weight:600;color:var(--text2);text-transform:uppercase;letter-spacing:0.5px;padding:8px 10px;margin-bottom:4px}.drive-item{padding:8px 10px;cursor:pointer;border-radius:4px;font-size:13px;display:flex;align-items:center;gap:8px;transition:.2s}.drive-item:hover{background:var(--hover)}.drive-icon{font-size:16px} var(--border);overflow-y:auto;padding:10px}.tree-item{padding:6px 10px;cursor:pointer;border-radius:4px;font-size:13px;display:flex;align-items:center;gap:6px;transition:.2s;position:relative}.tree-item:hover{background:var(--hover)}.tree-item.drag-over{background:var(--accent);color:#fff}.tree-toggle{width:16px;height:16px;display:inline-flex;align-items:center;justify-content:center;font-size:10px;cursor:pointer;user-select:none;transition:transform .2s}.tree-toggle:hover{background:var(--hover);border-radius:2px}.tree-toggle.collapsed{transform:rotate(-90deg)}.tree-children{margin-left:16px;overflow:hidden;transition:max-height .2s ease-out}.tree-children.collapsed{max-height:0!important;display:none}.file-list-container{flex:1;background:var(--bg);overflow-y:auto}.file-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(100px,1fr));gap:10px;padding:20px}.file-list-view{display:table;width:100%}.file-list-view .file-item{display:table-row}.file-list-view .file-item>div{display:table-cell;padding:12px;border-bottom:1px solid var(--border)}.file-list-view .file-icon{font-size:24px;width:40px}.file-item{padding:10px;border:1px solid transparent;border-radius:4px;cursor:pointer;display:flex;flex-direction:column;align-items:center;gap:8px;transition:.2s}.file-item:hover{background:var(--hover)}.file-item.selected{background:var(--selected)}.file-item.dragging{opacity:.5}.file-item.drag-over{background:var(--accent);color:#fff}.file-icon{font-size:48px;width:64px;height:64px;display:flex;align-items:center;justify-content:center}.file-name{font-size:12px;word-break:break-word}.modal{display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:1000;align-items:center;justify-content:center}.modal.active{display:flex}.modal-content{background:var(--bg);border-radius:8px;max-width:90%;max-height:90%;overflow:auto;display:flex;flex-direction:column;min-width:400px}.modal-header{padding:16px 20px;border-bottom:1px solid var(--border);display:flex;justify-content:space-between}.modal-title{font-size:16px;font-weight:600}.modal-close{background:none;border:none;font-size:24px;cursor:pointer;padding:0;width:32px;height:32px;border-radius:4px}.modal-close:hover{background:var(--hover)}.modal-body{padding:20px;flex:1}.modal-footer{padding:16px 20px;border-top:1px solid var(--border);display:flex;justify-content:flex-end;gap:10px}.form-group{margin-bottom:16px}.form-label{display:block;margin-bottom:6px;font-size:13px;font-weight:500}.form-input{width:100%;padding:8px 12px;border:1px solid var(--border);border-radius:4px;background:var(--bg);color:var(--text);font-size:13px}textarea.form-input{min-height:400px;font-family:monospace;resize:vertical}.drop-zone{border:2px dashed var(--border);border-radius:8px;padding:40px;text-align:center;cursor:pointer}.drop-zone.dragover{border-color:var(--accent);background:var(--hover)}.notification{position:fixed;top:20px;right:20px;background:var(--bg);border:1px solid var(--border);border-radius:6px;padding:16px 20px;z-index:2000;min-width:300px;animation:slideIn .3s}@keyframes slideIn{from{transform:translateX(400px);opacity:0}to{transform:translateX(0);opacity:1}}.notification.success{border-left:4px solid #107c10}.notification.error{border-left:4px solid #d13438}.spinner{border:3px solid var(--border);border-top:3px solid var(--accent);border-radius:50%;width:40px;height:40px;animation:spin 1s linear infinite;margin:40px auto}@keyframes spin{to{transform:rotate(360deg)}}.empty-state{text-align:center;padding:40px;color:var(--text2)}
</style>
</head>
<body>
<div class="header"><div class="header-title">📁 File Manager</div><button class="theme-toggle" onclick="toggleTheme()"><span id="theme-icon">🌙</span> Theme</button></div>
<div class="toolbar">
<button class="toolbar-btn" onclick="navigateBack()"><i class="fas fa-arrow-left"></i> Back</button>
<button class="toolbar-btn" onclick="navigateUp()"><i class="fas fa-arrow-up"></i> Up</button>
<button class="toolbar-btn" onclick="refreshFiles()"><i class="fas fa-sync"></i> Refresh</button>
<button class="toolbar-btn primary" onclick="showUploadModal()"><i class="fas fa-upload"></i> Upload</button>
<button class="toolbar-btn primary" onclick="showNewFolderModal()"><i class="fas fa-folder-plus"></i> New Folder</button>
<button class="toolbar-btn" onclick="renameSelected()" id="renameBtn" disabled><i class="fas fa-edit"></i> Rename</button>
<button class="toolbar-btn danger" onclick="deleteSelected()" id="deleteBtn" disabled><i class="fas fa-trash"></i> Delete</button>
<button class="toolbar-btn" onclick="downloadSelected()" id="downloadBtn" disabled><i class="fas fa-download"></i> Download</button>
<div class="view-toggle">
<button class="view-toggle-btn active" onclick="setView('grid')" id="gridViewBtn"><i class="fas fa-th"></i></button>
<button class="view-toggle-btn" onclick="setView('list')" id="listViewBtn"><i class="fas fa-list"></i></button>
</div>
</div>
<div class="address-bar-container">
<div class="address-bar-label">📍 Address:</div>
<div class="address-bar" id="addressBar" onclick="enableAddressEdit()">
<i class="fas fa-folder address-bar-icon"></i>
<div class="address-bar-segments" id="addressSegments"></div>
<input type="text" class="address-bar-input" id="addressInput" style="display:none" onblur="disableAddressEdit()" onkeydown="handleAddressKeydown(event)">
</div>
</div>
<div class="search-container">
<div class="search-box">
<i class="fas fa-search"></i>
<input type="text" class="search-input" id="searchInput" placeholder="Search files and folders..." oninput="handleSearch()">
<button class="search-clear" id="searchClear" onclick="clearSearch()">
<i class="fas fa-times"></i>
</button>
</div>
<div class="search-results-info" id="searchInfo"></div>
</div>
<div class="breadcrumb">
<?php foreach($breadcrumbs as $i=>$crumb):?>
<a href="#" class="breadcrumb-item" onclick="navigateTo('<?=htmlspecialchars($crumb['path'])?>')"><?=htmlspecialchars($crumb['name'])?></a>
<?php if($i<count($breadcrumbs)-1):?><span>›</span><?php endif;?>
<?php endforeach;?>
</div>
<div class="main-container">
<div class="sidebar" id="sidebar"></div>
<div class="file-list-container" id="fileListContainer"><div class="file-grid" id="fileList"></div></div>
</div>
<div class="modal" id="uploadModal"><div class="modal-content"><div class="modal-header"><div class="modal-title">Upload Files</div><button class="modal-close" onclick="closeModal('uploadModal')">×</button></div><div class="modal-body"><div class="drop-zone" id="dropZone"><div style="font-size:48px;margin-bottom:16px">📤</div><div>Drag & drop or click to browse</div><input type="file" id="fileInput" multiple style="display:none"></div><div id="uploadProgress"></div></div></div></div>
<div class="modal" id="newFolderModal"><div class="modal-content"><div class="modal-header"><div class="modal-title">Create Folder</div><button class="modal-close" onclick="closeModal('newFolderModal')">×</button></div><div class="modal-body"><div class="form-group"><label class="form-label">Folder Name</label><input type="text" class="form-input" id="folderNameInput"></div></div><div class="modal-footer"><button class="toolbar-btn" onclick="closeModal('newFolderModal')">Cancel</button><button class="toolbar-btn primary" onclick="createFolder()">Create</button></div></div></div>
<div class="modal" id="renameModal"><div class="modal-content"><div class="modal-header"><div class="modal-title">Rename</div><button class="modal-close" onclick="closeModal('renameModal')">×</button></div><div class="modal-body"><div class="form-group"><label class="form-label">New Name</label><input type="text" class="form-input" id="renameInput"></div></div><div class="modal-footer"><button class="toolbar-btn" onclick="closeModal('renameModal')">Cancel</button><button class="toolbar-btn primary" onclick="performRename()">Rename</button></div></div></div>
<div class="modal" id="editorModal"><div class="modal-content" style="width:90%;height:90%"><div class="modal-header"><div class="modal-title" id="editorTitle">Edit File</div><button class="modal-close" onclick="closeModal('editorModal')">×</button></div><div class="modal-body" style="flex:1;display:flex"><textarea class="form-input" id="fileEditor" style="flex:1"></textarea></div><div class="modal-footer"><button class="toolbar-btn" onclick="closeModal('editorModal')">Cancel</button><button class="toolbar-btn primary" onclick="saveFile()">Save</button></div></div></div>
<script>
let currentPath='<?=addslashes($currentDir)?>';let selectedFile=null;let history=[currentPath];let historyIndex=0;let currentView='grid';let draggedFile=null;let collapsedFolders=new Set();let allFiles=[];let searchQuery='';document.addEventListener('DOMContentLoaded',()=>{loadTheme();loadTree();loadFiles(currentPath);setupDragDrop();loadView();updateAddressBar()});function toggleTheme(){const t=document.documentElement.getAttribute('data-theme');const n=t==='dark'?'light':'dark';document.documentElement.setAttribute('data-theme',n);document.getElementById('theme-icon').textContent=n==='dark'?'☀️':'🌙';localStorage.setItem('theme',n)}function loadTheme(){const t=localStorage.getItem('theme')||'light';document.documentElement.setAttribute('data-theme',t);document.getElementById('theme-icon').textContent=t==='dark'?'☀️':'🌙'}function setView(v){currentView=v;localStorage.setItem('fileView',v);document.getElementById('gridViewBtn').classList.toggle('active',v==='grid');document.getElementById('listViewBtn').classList.toggle('active',v==='list');renderFiles(window.currentFiles||[])}function loadView(){setView(localStorage.getItem('fileView')||'grid')}function navigateTo(p){currentPath=p;if(historyIndex<history.length-1)history=history.slice(0,historyIndex+1);history.push(p);historyIndex=history.length-1;loadFiles(p);updateAddressBar();window.history.pushState({},'','?dir='+encodeURIComponent(p))}function updateAddressBar(){const segments=document.getElementById('addressSegments');const parts=currentPath.split('/').filter(p=>p);let path='';const html=parts.map((part,i)=>{path+=(path?'/':'/')+part;const p=path;return `<span class="address-segment" onclick="navigateTo('${p}')">${escapeHtml(part)}</span>${i<parts.length-1?'<span class="address-separator">›</span>':''}`}).join('');segments.innerHTML=html||'<span class="address-segment" onclick="navigateTo(\'/\')">/</span>'}function enableAddressEdit(){const segments=document.getElementById('addressSegments');const input=document.getElementById('addressInput');input.value=currentPath;segments.style.display='none';input.style.display='block';input.focus();input.select()}function disableAddressEdit(){const segments=document.getElementById('addressSegments');const input=document.getElementById('addressInput');segments.style.display='flex';input.style.display='none'}async function handleAddressKeydown(e){if(e.key==='Enter'){const path=document.getElementById('addressInput').value.trim();if(path){const f=new FormData();f.append('action','get_files');f.append('dir',path);try{const r=await fetch('',{method:'POST',body:f});const d=await r.json();if(d.success){navigateTo(path)}else{showNotification('Error','Directory not found','error');disableAddressEdit()}}catch(err){showNotification('Error','Directory not found','error');disableAddressEdit()}}else{disableAddressEdit()}}else if(e.key==='Escape'){disableAddressEdit()}}function navigateBack(){if(historyIndex>0){historyIndex--;currentPath=history[historyIndex];loadFiles(currentPath)}}function navigateUp(){const p=currentPath.split('/');if(p.length>1){p.pop();navigateTo(p.join('/')||'/')}}function refreshFiles(){loadFiles(currentPath);loadTree()}async function loadFiles(p){const l=document.getElementById('fileList');l.innerHTML='<div class="spinner"></div>';try{const f=new FormData();f.append('action','get_files');f.append('dir',p);const r=await fetch('',{method:'POST',body:f});const d=await r.json();if(d.success){allFiles=d.files;window.currentFiles=d.files;if(searchQuery){handleSearch()}else{renderFiles(d.files)}}else{showNotification('Error',d.message||'Directory not found','error');l.innerHTML='<div style="text-align:center;padding:40px;color:var(--text2)"><i class="fas fa-folder-open" style="font-size:48px;margin-bottom:16px;opacity:0.3"></i><div style="font-size:16px;font-weight:600;margin-bottom:8px">Directory Not Found</div><div style="font-size:14px">The path you entered does not exist or is not accessible.</div></div>'}}catch(e){showNotification('Error','Failed to load','error')}}function renderFiles(files){const l=document.getElementById('fileList');if(!files.length){l.innerHTML='<div class="empty-state">Empty folder</div>';return}if(currentView==='grid'){l.className='file-grid';l.innerHTML=files.map(f=>`<div class="file-item" draggable="true" onclick='selectFile(this,${JSON.stringify(f)})' ondblclick='openFile(${JSON.stringify(f)})' ondragstart='handleDragStart(event,${JSON.stringify(f)})' ondragend='handleDragEnd(event)' ondragover='handleDragOver(event,${JSON.stringify(f)})' ondragleave='handleDragLeave(event)' ondrop='handleDrop(event,${JSON.stringify(f)})'><div class="file-icon">${getIconEmoji(f.icon)}</div><div class="file-name">${escapeHtml(f.name)}</div></div>`).join('')}else{l.className='file-list-view';l.innerHTML=files.map(f=>`<div class="file-item" draggable="true" onclick='selectFile(this,${JSON.stringify(f)})' ondblclick='openFile(${JSON.stringify(f)})' ondragstart='handleDragStart(event,${JSON.stringify(f)})' ondragend='handleDragEnd(event)' ondragover='handleDragOver(event,${JSON.stringify(f)})' ondragleave='handleDragLeave(event)' ondrop='handleDrop(event,${JSON.stringify(f)})'><div class="file-icon">${getIconEmoji(f.icon)}</div><div class="file-name">${escapeHtml(f.name)}</div><div>${f.type==='folder'?'--':formatSize(f.size)}</div><div>${new Date(f.modified*1000).toLocaleString()}</div><div style="font-family:monospace;font-size:11px">${f.permissions}</div></div>`).join('')}setupFileListDrop()}function getIconEmoji(i){const m={'folder':'📁','file':'📄','code':'💻','image':'🖼️','archive':'📦','text':'📝'};return m[i]||'📄'}function formatSize(b){const u=['B','KB','MB','GB'];let s=b,i=0;while(s>=1024&&i<3){s/=1024;i++}return s.toFixed(2)+' '+u[i]}function handleDragStart(e,f){draggedFile=f;e.currentTarget.classList.add('dragging')}function handleDragEnd(e){e.currentTarget.classList.remove('dragging');draggedFile=null}function handleDragOver(e,f){if(draggedFile&&f.type==='folder'&&draggedFile.path!==f.path){e.preventDefault();e.currentTarget.classList.add('drag-over')}}function handleDragLeave(e){e.currentTarget.classList.remove('drag-over')}async function handleDrop(e,f){e.preventDefault();e.currentTarget.classList.remove('drag-over');if(draggedFile&&f.type==='folder'&&confirm(`Move "${draggedFile.name}" to "${f.name}"?`)){await moveFile(draggedFile.path,f.path)}}function setupFileListDrop(){const c=document.getElementById('fileListContainer');c.addEventListener('dragover',e=>{if(draggedFile)e.preventDefault()});c.addEventListener('drop',async e=>{e.preventDefault();if(draggedFile&&confirm(`Move "${draggedFile.name}" here?`)){await moveFile(draggedFile.path,currentPath)}})}async function moveFile(s,d){const f=new FormData();f.append('action','move');f.append('source',s);f.append('dest_dir',d);try{const r=await fetch('',{method:'POST',body:f});const data=await r.json();if(data.success){showNotification('Success',data.message,'success');refreshFiles()}else showNotification('Error',data.message,'error')}catch(e){showNotification('Error','Move failed','error')}}function selectFile(el,f){document.querySelectorAll('.file-item').forEach(e=>e.classList.remove('selected'));el.classList.add('selected');selectedFile=f;document.getElementById('renameBtn').disabled=false;document.getElementById('deleteBtn').disabled=false;document.getElementById('downloadBtn').disabled=f.type==='folder'}function openFile(f){if(f.type==='folder')navigateTo(f.path);else if(f.editable)editFile(f);else window.open('?download='+encodeURIComponent(f.path))}async function loadTree(){try{const f=new FormData();f.append('action','get_tree');const r=await fetch('',{method:'POST',body:f});const d=await r.json();if(d.success)renderTree(d.tree)}catch(e){}}function renderTree(t){const sidebar=document.getElementById('sidebar');let html='<div class="sidebar-section"><div class="sidebar-title">Folders</div>';html+=renderTreeItems(t);html+='</div>';sidebar.innerHTML=html}function renderTreeItems(items){return items.map(i=>{const hasChildren=i.children&&i.children.length>0;const isInCurrentPath=currentPath.startsWith(i.path);const isCollapsed=!collapsedFolders.has(i.path)&&!isInCurrentPath;const toggleIcon=hasChildren?`<span class="tree-toggle ${isCollapsed?'collapsed':''}" onclick="event.stopPropagation();toggleFolder('${i.path}')">▼</span>`:'<span style="width:16px"></span>';return `<div class="tree-item" onclick="expandAndNavigate('${i.path}')" ondragover="handleTreeDragOver(event,'${i.path}')" ondragleave="handleTreeDragLeave(event)" ondrop="handleTreeDrop(event,'${i.path}')">${toggleIcon}📁 ${escapeHtml(i.name)}</div>${hasChildren?'<div class="tree-children '+(isCollapsed?'collapsed':'')+'">'+renderTreeItems(i.children)+'</div>':''}`}).join('')}function toggleFolder(path){if(collapsedFolders.has(path)){collapsedFolders.delete(path)}else{collapsedFolders.add(path)}loadTree()}function expandAndNavigate(path){collapsedFolders.add(path);navigateTo(path)}function handleTreeDragOver(e,p){if(draggedFile){e.preventDefault();e.currentTarget.classList.add('drag-over')}}function handleTreeDragLeave(e){e.currentTarget.classList.remove('drag-over')}async function handleTreeDrop(e,p){e.preventDefault();e.currentTarget.classList.remove('drag-over');if(draggedFile)await moveFile(draggedFile.path,p)}function showUploadModal(){document.getElementById('uploadModal').classList.add('active')}function setupDragDrop(){const z=document.getElementById('dropZone'),i=document.getElementById('fileInput');z.onclick=()=>i.click();z.ondragover=e=>{e.preventDefault();z.classList.add('dragover')};z.ondragleave=()=>z.classList.remove('dragover');z.ondrop=e=>{e.preventDefault();z.classList.remove('dragover');handleFiles(e.dataTransfer.files)};i.onchange=e=>handleFiles(e.target.files)}async function handleFiles(files){const p=document.getElementById('uploadProgress');p.innerHTML='';for(let file of files){const f=new FormData();f.append('action','upload');f.append('dir',currentPath);f.append('file',file);try{const r=await fetch('',{method:'POST',body:f});const d=await r.json();p.innerHTML+=`<div style="color:${d.success?'green':'red'}">${d.success?'✓':'✗'} ${file.name}</div>`}catch(e){p.innerHTML+=`<div style="color:red">✗ ${file.name}</div>`}}setTimeout(()=>{closeModal('uploadModal');refreshFiles()},2000)}function showNewFolderModal(){document.getElementById('newFolderModal').classList.add('active');document.getElementById('folderNameInput').value='';document.getElementById('folderNameInput').focus()}async function createFolder(){const n=document.getElementById('folderNameInput').value.trim();if(!n)return;const f=new FormData();f.append('action','create_folder');f.append('dir',currentPath);f.append('name',n);try{const r=await fetch('',{method:'POST',body:f});const d=await r.json();if(d.success){showNotification('Success',d.message,'success');closeModal('newFolderModal');refreshFiles()}else showNotification('Error',d.message,'error')}catch(e){showNotification('Error','Failed','error')}}function renameSelected(){if(!selectedFile)return;document.getElementById('renameModal').classList.add('active');document.getElementById('renameInput').value=selectedFile.name;document.getElementById('renameInput').focus()}async function performRename(){const n=document.getElementById('renameInput').value.trim();if(!n||!selectedFile)return;const f=new FormData();f.append('action','rename');f.append('old_path',selectedFile.path);f.append('new_name',n);try{const r=await fetch('',{method:'POST',body:f});const d=await r.json();if(d.success){showNotification('Success',d.message,'success');closeModal('renameModal');refreshFiles()}else showNotification('Error',d.message,'error')}catch(e){showNotification('Error','Failed','error')}}async function deleteSelected(){if(!selectedFile||!confirm(`Delete ${selectedFile.name}?`))return;const f=new FormData();f.append('action','delete');f.append('path',selectedFile.path);try{const r=await fetch('',{method:'POST',body:f});const d=await r.json();if(d.success){showNotification('Success',d.message,'success');selectedFile=null;refreshFiles()}else showNotification('Error',d.message,'error')}catch(e){showNotification('Error','Failed','error')}}function downloadSelected(){if(!selectedFile||selectedFile.type==='folder')return;window.open('?download='+encodeURIComponent(selectedFile.path))}async function editFile(f){document.getElementById('editorModal').classList.add('active');document.getElementById('editorTitle').textContent='Edit: '+f.name;document.getElementById('fileEditor').value='Loading...';const fd=new FormData();fd.append('action','get_content');fd.append('path',f.path);try{const r=await fetch('',{method:'POST',body:fd});const d=await r.json();if(d.success){document.getElementById('fileEditor').value=d.content;selectedFile=f}else{showNotification('Error',d.message,'error');closeModal('editorModal')}}catch(e){showNotification('Error','Failed','error');closeModal('editorModal')}}async function saveFile(){if(!selectedFile)return;const c=document.getElementById('fileEditor').value;const f=new FormData();f.append('action','save_content');f.append('path',selectedFile.path);f.append('content',c);try{const r=await fetch('',{method:'POST',body:f});const d=await r.json();if(d.success){showNotification('Success',d.message,'success');closeModal('editorModal')}else showNotification('Error',d.message,'error')}catch(e){showNotification('Error','Failed','error')}}function closeModal(id){document.getElementById(id).classList.remove('active')}function showNotification(t,m,type){const n=document.createElement('div');n.className=`notification ${type}`;n.innerHTML=`<div style="font-weight:600;margin-bottom:4px">${t}</div><div style="font-size:13px">${m}</div>`;document.body.appendChild(n);setTimeout(()=>n.remove(),3000)}function escapeHtml(t){const d=document.createElement('div');d.textContent=t;return d.innerHTML}function handleSearch(){const input=document.getElementById('searchInput');const clearBtn=document.getElementById('searchClear');const info=document.getElementById('searchInfo');searchQuery=input.value.trim().toLowerCase();if(searchQuery){clearBtn.classList.add('visible');const filtered=allFiles.filter(f=>f.name.toLowerCase().includes(searchQuery));renderFiles(filtered);info.textContent=`${filtered.length} of ${allFiles.length} items`;info.style.display='block'}else{clearBtn.classList.remove('visible');renderFiles(allFiles);info.textContent='';info.style.display='none'}}function clearSearch(){const input=document.getElementById('searchInput');const clearBtn=document.getElementById('searchClear');const info=document.getElementById('searchInfo');input.value='';searchQuery='';clearBtn.classList.remove('visible');renderFiles(allFiles);info.textContent='';info.style.display='none';input.focus()}document.addEventListener('keydown',e=>{if(e.key==='F2'&&selectedFile)renameSelected();if(e.key==='Delete'&&selectedFile)deleteSelected();if(e.key==='F5'){e.preventDefault();refreshFiles()}if(e.ctrlKey&&e.key==='f'){e.preventDefault();document.getElementById('searchInput').focus()}});
</script>
</body>
</html>