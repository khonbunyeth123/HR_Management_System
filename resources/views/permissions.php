<?php
$pageTitle = "Permission Management";
$activeMenu = "permissions";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?> - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'DM Sans', sans-serif; }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(16px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to   { opacity: 1; }
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        @keyframes shimmer {
            0%   { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }

        .modal-backdrop { animation: fadeIn 0.2s ease-out; }
        .modal-box      { animation: slideUp 0.25s ease-out; }

        .spinner {
            display: inline-block; width: 14px; height: 14px;
            border: 2px solid rgba(255,255,255,0.3);
            border-radius: 50%; border-top-color: white;
            animation: spin 0.7s linear infinite;
        }

        .skeleton {
            background: linear-gradient(90deg,#f0f0f0 25%,#e0e0e0 50%,#f0f0f0 75%);
            background-size: 200% 100%;
            animation: shimmer 1.2s infinite;
            border-radius: 6px;
        }

        tbody tr { transition: background 0.12s; }
        tbody tr:hover { background: #f0f7ff; }

        .toast-container {
            position: fixed; top: 1rem; right: 1rem;
            z-index: 9999; display: flex; flex-direction: column; gap: 8px;
        }
        .toast {
            animation: slideUp 0.3s ease-out;
            min-width: 280px; display: flex; align-items: center; gap: 10px;
            padding: 12px 16px; border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.12);
            font-size: 14px; font-weight: 500;
        }
        .toast.success { background:#ecfdf5; border-left:4px solid #10b981; color:#065f46; }
        .toast.error   { background:#fef2f2; border-left:4px solid #ef4444; color:#7f1d1d; }
        .toast.info    { background:#eff6ff; border-left:4px solid #3b82f6; color:#1e3a8a; }

        .error-msg { font-size:12px; color:#dc2626; margin-top:3px; display:none; }
        .error-msg.show { display:block; }
        .field-error { border-color:#ef4444 !important; }

        .modal-scroll { max-height: 70vh; overflow-y: auto; }

        .badge { display:inline-block; padding:2px 10px; border-radius:999px; font-size:12px; font-weight:600; }
        .badge-active   { background:#dcfce7; color:#15803d; }
        .badge-inactive { background:#fee2e2; color:#b91c1c; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-6 border-b border-gray-200 mb-6">
        <div class="flex items-center gap-3 mb-4 sm:mb-0">
            <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center text-white">
                <i class="fas fa-shield-alt"></i>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Permission Management</h1>
                <p class="text-gray-500 text-sm">Define system permissions (module + action)</p>
            </div>
        </div>
        <button onclick="openAddModal()"
            class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition-colors shadow-sm text-sm">
            <i class="fas fa-plus"></i> Add Permission
        </button>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
        <div class="bg-white rounded-xl p-5 border border-gray-100 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-xs font-semibold uppercase tracking-wide">Total Permissions</p>
                <p class="text-3xl font-bold text-gray-900 mt-1" id="statTotal">—</p>
            </div>
            <i class="fas fa-key text-blue-300 text-3xl"></i>
        </div>
        <div class="bg-white rounded-xl p-5 border border-gray-100 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-xs font-semibold uppercase tracking-wide">Modules</p>
                <p class="text-3xl font-bold text-gray-900 mt-1" id="statModules">—</p>
            </div>
            <i class="fas fa-layer-group text-green-300 text-3xl"></i>
        </div>
    </div>

    <!-- Search + Module Filter -->
    <div class="flex flex-col sm:flex-row gap-3 mb-5">
        <div class="relative flex-1">
            <i class="fas fa-search absolute left-3 top-3 text-gray-400 text-sm"></i>
            <input type="text" id="searchInput" placeholder="Search permissions..."
                class="w-full pl-9 pr-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                oninput="debounceSearch(loadPermissions, 300)">
        </div>
        <select id="moduleFilter" onchange="loadPermissions()"
            class="px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white min-w-max">
            <option value="">All Modules</option>
        </select>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200 text-gray-600 text-xs uppercase tracking-wide">
                        <th class="px-6 py-3 text-left font-semibold">#</th>
                        <th class="px-6 py-3 text-left font-semibold">Slug</th>
                        <th class="px-6 py-3 text-left font-semibold">Module</th>
                        <th class="px-6 py-3 text-left font-semibold">Action</th>
                        <th class="px-6 py-3 text-left font-semibold">Description</th>
                        <th class="px-6 py-3 text-center font-semibold">Status</th>
                        <th class="px-6 py-3 text-center font-semibold">Actions</th>
                    </tr>
                </thead>
                <tbody id="permissionsTableBody">
                    <?php for ($i = 0; $i < 5; $i++): ?>
                    <tr class="border-b border-gray-100">
                        <?php for ($j = 0; $j < 7; $j++): ?>
                        <td class="px-6 py-4"><div class="skeleton h-4 w-full"></div></td>
                        <?php endfor; ?>
                    </tr>
                    <?php endfor; ?>
                </tbody>
            </table>
        </div>

        <div id="noResults" class="hidden text-center py-16 text-gray-400">
            <i class="fas fa-search text-4xl mb-3 opacity-30 block"></i>
            <p class="font-medium">No permissions found</p>
            <p class="text-sm mt-1">Try adjusting your search or filter</p>
        </div>

        <div class="px-6 py-3 border-t border-gray-100 text-sm text-gray-500">
            <span id="paginationInfo">Showing 0 permissions</span>
        </div>
    </div>
</div>

<!-- ===================== ADD PERMISSION MODAL ===================== -->
<div id="addModal"
    class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4 modal-backdrop"
    onclick="if(event.target===this) closeModal('addModal')">
    <div class="bg-white rounded-xl shadow-xl max-w-md w-full modal-box" role="dialog">
        <div class="flex justify-between items-center px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-bold text-gray-900">Add New Permission</h2>
            <button onclick="closeModal('addModal')" class="text-gray-400 hover:text-gray-700 text-xl leading-none">×</button>
        </div>
        <div class="px-6 py-5 space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Module <span class="text-red-500">*</span></label>
                    <input type="text" id="addModule" placeholder="e.g. users"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        oninput="clearFieldError(this)">
                    <p class="error-msg" id="addModuleErr">Module is required</p>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Action <span class="text-red-500">*</span></label>
                    <input type="text" id="addAction" placeholder="e.g. view"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        oninput="clearFieldError(this)">
                    <p class="error-msg" id="addActionErr">Action is required</p>
                </div>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Description</label>
                <textarea id="addDesc" rows="2" placeholder="What does this permission allow?"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"></textarea>
            </div>
        </div>
        <div class="px-6 py-4 border-t border-gray-100 flex justify-end gap-3">
            <button type="button" onclick="closeModal('addModal')"
                class="px-5 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 font-semibold text-sm transition-colors">Cancel</button>
            <button type="button" onclick="submitAdd()" id="addBtn"
                class="px-5 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-semibold text-sm transition-colors flex items-center gap-2">
                <i class="fas fa-plus"></i> Create
            </button>
        </div>
    </div>
</div>

<!-- ===================== EDIT PERMISSION MODAL ===================== -->
<div id="editModal"
    class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4 modal-backdrop"
    onclick="if(event.target===this) closeModal('editModal')">
    <div class="bg-white rounded-xl shadow-xl max-w-md w-full modal-box" role="dialog">
        <div class="flex justify-between items-center px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-bold text-gray-900">Edit Permission</h2>
            <button onclick="closeModal('editModal')" class="text-gray-400 hover:text-gray-700 text-xl leading-none">×</button>
        </div>
        <div class="px-6 py-5 space-y-4">
            <input type="hidden" id="editId">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Module <span class="text-red-500">*</span></label>
                    <input type="text" id="editModule"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        oninput="clearFieldError(this)">
                    <p class="error-msg" id="editModuleErr">Module is required</p>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Action <span class="text-red-500">*</span></label>
                    <input type="text" id="editAction"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        oninput="clearFieldError(this)">
                    <p class="error-msg" id="editActionErr">Action is required</p>
                </div>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Description</label>
                <textarea id="editDesc" rows="2"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"></textarea>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Status</label>
                <select id="editStatus"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                    <option value="1">Active</option>
                    <option value="2">Inactive</option>
                </select>
            </div>
        </div>
        <div class="px-6 py-4 border-t border-gray-100 flex justify-end gap-3">
            <button type="button" onclick="closeModal('editModal')"
                class="px-5 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 font-semibold text-sm transition-colors">Cancel</button>
            <button type="button" onclick="submitEdit()" id="editBtn"
                class="px-5 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-semibold text-sm transition-colors flex items-center gap-2">
                <i class="fas fa-save"></i> Save Changes
            </button>
        </div>
    </div>
</div>

<!-- ===================== DELETE CONFIRM MODAL ===================== -->
<div id="deleteModal"
    class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4 modal-backdrop"
    onclick="if(event.target===this) closeModal('deleteModal')">
    <div class="bg-white rounded-xl shadow-xl max-w-sm w-full modal-box p-6 text-center" role="dialog">
        <div class="w-14 h-14 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-trash text-red-500 text-xl"></i>
        </div>
        <h2 class="text-lg font-bold text-gray-900 mb-1">Delete Permission</h2>
        <p class="text-gray-500 text-sm mb-6">
            Delete <strong id="deletePermName" class="text-gray-800"></strong>?
            This cannot be undone and may affect roles using it.
        </p>
        <input type="hidden" id="deletePermId">
        <div class="flex gap-3 justify-center">
            <button onclick="closeModal('deleteModal')"
                class="px-5 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 font-semibold text-sm transition-colors">Cancel</button>
            <button onclick="confirmDelete()" id="deleteBtn"
                class="px-5 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 font-semibold text-sm transition-colors flex items-center gap-2">
                <i class="fas fa-trash"></i> Delete
            </button>
        </div>
    </div>
</div>

<!-- Toast -->
<div id="toastContainer" class="toast-container"></div>

<!-- ===================== JAVASCRIPT ===================== -->
<script>
// ─── CONFIG ───────────────────────────────────────────────
const API_BASE = (function() {
    const match = window.location.pathname.match(/^(.*\/admin)/);
    return match ? match[1] + '/api' : '/api';
})();

// ─── STATE ────────────────────────────────────────────────
let allPermissions = [];
let deleteTargetId = null;

// ─── INIT ─────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    loadModules();
    loadPermissions();
});

// ─── LOAD MODULES (for filter dropdown) ───────────────────
async function loadModules() {
    try {
        const res  = await fetch(`${API_BASE}/permissions/categories`);
        const json = await res.json();
        const mods = json.data ?? [];
        const sel  = document.getElementById('moduleFilter');
        mods.forEach(m => {
            const opt = document.createElement('option');
            opt.value = m;
            opt.textContent = m.charAt(0).toUpperCase() + m.slice(1);
            sel.appendChild(opt);
        });
    } catch (err) {
        console.error('loadModules error:', err);
    }
}

// ─── LOAD & RENDER PERMISSIONS ────────────────────────────
// GET /api/permissions  (or /api/permissions/list)
async function loadPermissions() {
    const search = document.getElementById('searchInput').value.trim();
    const module = document.getElementById('moduleFilter').value;

    let url = `${API_BASE}/permissions?`;
    if (search) url += `search=${encodeURIComponent(search)}&`;
    if (module) url += `module=${encodeURIComponent(module)}&`;

    try {
        const res  = await fetch(url);
        const json = await res.json();

        allPermissions = Array.isArray(json.data?.data) ? json.data.data
                       : Array.isArray(json.data)       ? json.data
                       : Array.isArray(json)            ? json
                       : [];

        renderTable();
        updateStats();
    } catch (err) {
        showToast('Failed to load permissions: ' + err.message, 'error');
    }
}

function updateStats() {
    const modules = [...new Set(allPermissions.map(p => p.module))];
    document.getElementById('statTotal').textContent   = allPermissions.length;
    document.getElementById('statModules').textContent = modules.length;
}

function renderTable() {
    const tbody  = document.getElementById('permissionsTableBody');
    const noRes  = document.getElementById('noResults');
    const info   = document.getElementById('paginationInfo');

    if (!allPermissions.length) {
        tbody.innerHTML = '';
        noRes.classList.remove('hidden');
        info.textContent = 'No permissions found';
        return;
    }

    noRes.classList.add('hidden');
    info.textContent = `Showing ${allPermissions.length} permission${allPermissions.length > 1 ? 's' : ''}`;

    const moduleColors = {
        users:'blue', employees:'purple', attendance:'green',
        leave:'yellow', report:'red', roles:'orange', permissions:'indigo'
    };

    tbody.innerHTML = allPermissions.map((p, idx) => {
        const slug   = `${p.module}.${p.action}`;
        const color  = moduleColors[p.module] ?? 'gray';
        const active = parseInt(p.status_id) === 1;
        return `
        <tr class="border-b border-gray-100">
            <td class="px-6 py-4 text-gray-500 font-medium">${idx + 1}</td>
            <td class="px-6 py-4 font-mono text-xs font-semibold text-gray-800 bg-gray-50">${escHtml(slug)}</td>
            <td class="px-6 py-4">
                <span class="inline-block px-2.5 py-1 bg-${color}-100 text-${color}-800 text-xs font-semibold rounded capitalize">${escHtml(p.module)}</span>
            </td>
            <td class="px-6 py-4 text-gray-600 text-sm">${escHtml(p.action)}</td>
            <td class="px-6 py-4 text-gray-500 text-sm max-w-xs truncate">${escHtml(p.description || '—')}</td>
            <td class="px-6 py-4 text-center">
                <span class="badge ${active ? 'badge-active' : 'badge-inactive'}">${active ? 'Active' : 'Inactive'}</span>
            </td>
            <td class="px-6 py-4 text-center">
                <div class="flex items-center justify-center gap-2">
                    <button onclick="openEditModal(${p.id})"
                        class="inline-flex items-center gap-1 px-3 py-1.5 bg-gray-100 text-gray-700 rounded-lg hover:bg-blue-100 hover:text-blue-700 text-xs font-semibold transition-colors">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                    <button onclick="openDeleteModal(${p.id}, '${escHtml(slug)}')"
                        class="inline-flex items-center gap-1 px-3 py-1.5 bg-gray-100 text-gray-700 rounded-lg hover:bg-red-100 hover:text-red-700 text-xs font-semibold transition-colors">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </div>
            </td>
        </tr>`;
    }).join('');
}

// ─── ADD ──────────────────────────────────────────────────
function openAddModal() {
    document.getElementById('addModule').value = '';
    document.getElementById('addAction').value = '';
    document.getElementById('addDesc').value   = '';
    openModal('addModal');
}

async function submitAdd() {
    const module = document.getElementById('addModule').value.trim();
    const action = document.getElementById('addAction').value.trim();
    const desc   = document.getElementById('addDesc').value.trim();

    let valid = true;
    if (!module) { showFieldError('addModule', 'addModuleErr', 'Module is required'); valid = false; }
    if (!action) { showFieldError('addAction', 'addActionErr', 'Action is required'); valid = false; }
    if (!valid) return;

    setLoading('addBtn', true, 'Creating...');
    try {
        const res  = await fetch(`${API_BASE}/permissions`, {
            method:  'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body:    JSON.stringify({ module, action, description: desc }),
        });
        const json = await res.json();
        if (!res.ok) throw new Error(json.message || 'Failed to create');

        showToast('Permission created successfully!', 'success');
        closeModal('addModal');
        loadPermissions();
        loadModules();
    } catch (err) {
        showToast(err.message, 'error');
    } finally {
        setLoading('addBtn', false, '<i class="fas fa-plus"></i> Create');
    }
}

// ─── EDIT ─────────────────────────────────────────────────
function openEditModal(id) {
    const p = allPermissions.find(p => p.id == id);
    if (!p) return;
    document.getElementById('editId').value     = p.id;
    document.getElementById('editModule').value = p.module;
    document.getElementById('editAction').value = p.action;
    document.getElementById('editDesc').value   = p.description ?? '';
    document.getElementById('editStatus').value = p.status_id ?? 1;
    openModal('editModal');
}

async function submitEdit() {
    const id     = document.getElementById('editId').value;
    const module = document.getElementById('editModule').value.trim();
    const action = document.getElementById('editAction').value.trim();
    const desc   = document.getElementById('editDesc').value.trim();
    const status = document.getElementById('editStatus').value;

    let valid = true;
    if (!module) { showFieldError('editModule', 'editModuleErr', 'Module is required'); valid = false; }
    if (!action) { showFieldError('editAction', 'editActionErr', 'Action is required'); valid = false; }
    if (!valid) return;

    setLoading('editBtn', true, 'Saving...');
    try {
        const res  = await fetch(`${API_BASE}/permissions/${id}`, {
            method:  'PUT',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body:    JSON.stringify({ module, action, description: desc, status_id: parseInt(status) }),
        });
        const json = await res.json();
        if (!res.ok) throw new Error(json.message || 'Failed to update');

        showToast('Permission updated!', 'success');
        closeModal('editModal');
        loadPermissions();
    } catch (err) {
        showToast(err.message, 'error');
    } finally {
        setLoading('editBtn', false, '<i class="fas fa-save"></i> Save Changes');
    }
}

// ─── DELETE ───────────────────────────────────────────────
function openDeleteModal(id, slug) {
    deleteTargetId = id;
    document.getElementById('deletePermId').value      = id;
    document.getElementById('deletePermName').textContent = slug;
    openModal('deleteModal');
}

async function confirmDelete() {
    if (!deleteTargetId) return;
    setLoading('deleteBtn', true, 'Deleting...');
    try {
        const res  = await fetch(`${API_BASE}/permissions/${deleteTargetId}`, {
            method:  'DELETE',
            headers: { 'Accept': 'application/json' },
        });
        const json = await res.json();
        if (!res.ok) throw new Error(json.message || 'Failed to delete');

        showToast('Permission deleted!', 'success');
        closeModal('deleteModal');
        loadPermissions();
    } catch (err) {
        showToast(err.message, 'error');
    } finally {
        setLoading('deleteBtn', false, '<i class="fas fa-trash"></i> Delete');
        deleteTargetId = null;
    }
}

// ─── MODAL HELPERS ────────────────────────────────────────
function openModal(id)  { document.getElementById(id).classList.remove('hidden'); }
function closeModal(id) {
    document.getElementById(id).classList.add('hidden');
    document.getElementById(id).querySelectorAll('.field-error').forEach(el => el.classList.remove('field-error'));
    document.getElementById(id).querySelectorAll('.error-msg').forEach(el => el.classList.remove('show'));
}

document.addEventListener('keydown', e => {
    if (e.key === 'Escape')
        document.querySelectorAll('.fixed[id$="Modal"]:not(.hidden)').forEach(m => closeModal(m.id));
});

// ─── UTILITIES ────────────────────────────────────────────
function showFieldError(inputId, errId, msg) {
    const input = document.getElementById(inputId);
    const err   = document.getElementById(errId);
    input.classList.add('field-error');
    err.textContent = msg;
    err.classList.add('show');
}
function clearFieldError(input) {
    input.classList.remove('field-error');
    const err = input.parentElement.querySelector('.error-msg');
    if (err) err.classList.remove('show');
}
function setLoading(btnId, loading, html) {
    const btn = document.getElementById(btnId);
    btn.disabled  = loading;
    btn.innerHTML = loading ? `<span class="spinner"></span> ${html.replace(/(<([^>]+)>)/gi,'')}` : html;
}
function escHtml(str) {
    return String(str)
        .replace(/&/g,'&amp;').replace(/</g,'&lt;')
        .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function showToast(message, type = 'success') {
    const icons = { success:'check-circle', error:'exclamation-circle', info:'info-circle' };
    const c = document.getElementById('toastContainer');
    const t = document.createElement('div');
    t.className = `toast ${type}`;
    t.innerHTML = `
        <i class="fas fa-${icons[type] || 'info-circle'}"></i>
        <span>${message}</span>
        <button onclick="this.parentElement.remove()" class="ml-auto text-lg leading-none hover:opacity-70">×</button>`;
    c.appendChild(t);
    setTimeout(() => t.remove(), 5000);
}
let debounceTimer;
function debounceSearch(fn, delay) {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(fn, delay);
}
</script>
</body>
</html>