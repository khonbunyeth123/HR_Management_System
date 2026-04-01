<?php
$pageTitle = "Permission Management";
$activeMenu = "permissions";
$totalCount = $totalCount ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?> - Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <!-- <style>
            @keyframes slideUp { from { opacity:0;transform:translateY(20px); } to { opacity:1;transform:translateY(0); } }
            @keyframes fadeIn  { from { opacity:0; } to { opacity:1; } }
            @keyframes spin    { to { transform:rotate(360deg); } }
            .modal-enter     { animation:slideUp .25s ease-out; }
            .modal-backdrop  { animation:fadeIn .2s ease-out; }
            .spinner         { display:inline-block;width:16px;height:16px;border:2px solid rgba(255,255,255,.3);border-radius:50%;border-top-color:white;animation:spin .7s linear infinite; }
            .form-error      { border-color:#ef4444!important; }
            .error-msg       { font-size:12px;color:#dc2626;margin-top:3px;display:none; }
            .error-msg.show  { display:block; }
            .no-results      { text-align:center;padding:2.5rem;color:#9ca3af; }
            .toast-wrap      { position:fixed;top:1rem;right:1rem;z-index:9999;display:flex;flex-direction:column;gap:8px; }
            .toast           { animation:slideUp .25s ease-out;min-width:280px;max-width:380px; }
            .toast.success   { background:#ecfdf5;border-left:4px solid #10b981;color:#065f46; }
            .toast.error     { background:#fef2f2;border-left:4px solid #ef4444;color:#7f1d1d; }
            .toast.info      { background:#eff6ff;border-left:4px solid #3b82f6;color:#1e3a8a; }
            .tab-content.hidden { display:none; }
            .group-chevron.open { transform:rotate(180deg); }
        </style> -->
</head>
<body class="bg-gray-50">\

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-8 border-b-2 border-gray-200 mb-8">
        <div class="flex items-center gap-4 mb-4 sm:mb-0">
            <div class="w-10 h-10 bg-gradient-to-br from-blue-600 to-blue-400 rounded-lg flex items-center justify-center text-white text-xl shadow">
                <i class="fas fa-shield-alt"></i>
            </div>
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Permission Management</h1>
                <p class="text-gray-500 text-sm mt-0.5">Manage system permissions and role assignments</p>
            </div>
        </div>
        <button onclick="openAddModal()" class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition-colors shadow">
            <i class="fas fa-plus"></i> Add Permission
        </button>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-8">
        <div class="bg-white p-6 rounded-xl shadow-sm border-l-4 border-blue-500 flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm font-medium"><i class="fas fa-key mr-1"></i> Total Permissions</p>
                <p class="text-3xl font-bold text-gray-900 mt-1" id="statTotal"><?php echo (int)$totalCount; ?></p>
            </div>
            <i class="fas fa-key text-blue-400 text-3xl opacity-20"></i>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-sm border-l-4 border-green-500 flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm font-medium"><i class="fas fa-layer-group mr-1"></i> Modules</p>
                <p class="text-3xl font-bold text-gray-900 mt-1" id="statModules">—</p>
            </div>
            <i class="fas fa-layer-group text-green-400 text-3xl opacity-20"></i>
        </div>
    </div>

    <!-- Tabs -->
    <div class="border-b-2 border-gray-200 mb-8">
        <div class="flex gap-1 overflow-x-auto">
            <button class="tab-btn px-6 py-3 font-medium text-blue-600 border-b-2 border-blue-600 transition-colors" onclick="switchTab(event,'tab-permissions')">
                <i class="fas fa-key mr-2"></i>Permissions
            </button>
            <button class="tab-btn px-6 py-3 font-medium text-gray-500 border-b-2 border-transparent hover:text-blue-600 transition-colors" onclick="switchTab(event,'tab-assignments')">
                <i class="fas fa-link mr-2"></i>Role Assignments
            </button>
        </div>
    </div>

    <!-- ── PERMISSIONS TAB ── -->
    <div id="tab-permissions" class="tab-content">
        <!-- Filters -->
        <div class="flex flex-col sm:flex-row gap-3 mb-6">
            <div class="flex-1 relative">
                <i class="fas fa-search absolute left-3 top-3.5 text-gray-400"></i>
                <input id="searchInput" type="text" placeholder="Search permissions…" oninput="debounce(loadPermissions,350)()"
                    class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
            </div>
            <select id="moduleFilter" onchange="loadPermissions()"
                class="px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white text-sm min-w-max">
                <option value="">All Modules</option>
            </select>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-4 text-left"><input type="checkbox" id="selectAll" onchange="toggleSelectAll(this)" class="w-4 h-4 text-blue-600 rounded cursor-pointer"></th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Permission</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Module</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Action</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Description</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="permissionsTable" class="divide-y divide-gray-100">
                        <tr><td colspan="7" class="px-6 py-10 text-center text-gray-400"><i class="fas fa-spinner fa-spin mr-2"></i>Loading…</td></tr>
                    </tbody>
                </table>
            </div>
            <div id="permsNoResults" class="no-results hidden">
                <i class="fas fa-search text-4xl mb-3 opacity-20"></i>
                <p>No permissions found</p>
            </div>
        </div>
    </div>

    <!-- ── ROLE ASSIGNMENTS TAB ── -->
    <div id="tab-assignments" class="tab-content hidden">
        <div class="flex flex-col sm:flex-row gap-3 mb-6">
            <div class="flex-1 relative">
                <i class="fas fa-search absolute left-3 top-3.5 text-gray-400"></i>
                <input id="roleSearchInput" type="text" placeholder="Search roles…" oninput="debounce(loadRoles,350)()"
                    class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Role</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Slug</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Permissions</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="rolesTable" class="divide-y divide-gray-100">
                        <tr><td colspan="5" class="px-6 py-10 text-center text-gray-400"><i class="fas fa-spinner fa-spin mr-2"></i>Loading…</td></tr>
                    </tbody>
                </table>
            </div>
            <div id="rolesNoResults" class="no-results hidden">
                <i class="fas fa-search text-4xl mb-3 opacity-20"></i>
                <p>No roles found</p>
            </div>
        </div>
    </div>

</div><!-- /container -->

<!-- ═══════════════════════════════════════════
     ADD PERMISSION MODAL
════════════════════════════════════════════ -->
<div id="addModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4 modal-backdrop"
     onclick="if(event.target===this)closeModal('addModal')">
    <div class="bg-white rounded-xl shadow-lg w-full max-w-lg modal-enter">
        <div class="flex justify-between items-center px-6 py-5 border-b border-gray-200">
            <h2 class="text-xl font-bold text-gray-900">Add New Permission</h2>
            <button onclick="closeModal('addModal')" class="text-gray-400 hover:text-gray-700 text-2xl leading-none">×</button>
        </div>
        <div class="p-6 space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Module *</label>
                    <input id="addModule" type="text" placeholder="e.g. users" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                    <p class="error-msg" id="addModuleErr">Module is required</p>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Action *</label>
                    <input id="addAction" type="text" placeholder="e.g. view" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                    <p class="error-msg" id="addActionErr">Action is required</p>
                </div>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Description</label>
                <textarea id="addDesc" rows="3" placeholder="What does this permission allow?" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm resize-none"></textarea>
            </div>
        </div>
        <div class="flex justify-end gap-3 px-6 py-4 border-t border-gray-100 bg-gray-50 rounded-b-xl">
            <button onclick="closeModal('addModal')" class="px-5 py-2.5 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-medium text-sm transition-colors">Cancel</button>
            <button onclick="submitAdd()" id="addBtn" class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium text-sm transition-colors shadow">
                <i class="fas fa-plus"></i> Create
            </button>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════
     EDIT PERMISSION MODAL
════════════════════════════════════════════ -->
<div id="editModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4 modal-backdrop"
     onclick="if(event.target===this)closeModal('editModal')">
    <div class="bg-white rounded-xl shadow-lg w-full max-w-lg modal-enter">
        <div class="flex justify-between items-center px-6 py-5 border-b border-gray-200">
            <h2 class="text-xl font-bold text-gray-900">Edit Permission</h2>
            <button onclick="closeModal('editModal')" class="text-gray-400 hover:text-gray-700 text-2xl leading-none">×</button>
        </div>
        <div class="p-6 space-y-4">
            <input type="hidden" id="editId">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Module *</label>
                    <input id="editModule" type="text" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                    <p class="error-msg" id="editModuleErr">Module is required</p>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Action *</label>
                    <input id="editAction" type="text" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                    <p class="error-msg" id="editActionErr">Action is required</p>
                </div>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Description</label>
                <textarea id="editDesc" rows="3" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm resize-none"></textarea>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Status</label>
                <select id="editStatus" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white text-sm">
                    <option value="1">Active</option>
                    <option value="2">Inactive</option>
                </select>
            </div>
        </div>
        <div class="flex justify-end gap-3 px-6 py-4 border-t border-gray-100 bg-gray-50 rounded-b-xl">
            <button onclick="closeModal('editModal')" class="px-5 py-2.5 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-medium text-sm transition-colors">Cancel</button>
            <button onclick="submitEdit()" id="editBtn" class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium text-sm transition-colors shadow">
                <i class="fas fa-save"></i> Save Changes
            </button>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════
     ASSIGN PERMISSIONS TO ROLE MODAL
════════════════════════════════════════════ -->
<div id="assignModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4 modal-backdrop"
     onclick="if(event.target===this)closeModal('assignModal')">
    <div class="bg-white rounded-xl shadow-lg w-full max-w-2xl max-h-[90vh] flex flex-col modal-enter">
        <div class="flex justify-between items-center px-6 py-5 border-b border-gray-200 shrink-0">
            <div>
                <h2 class="text-xl font-bold text-gray-900">Assign Permissions</h2>
                <p id="assignSubtitle" class="text-sm text-gray-500 mt-0.5"></p>
            </div>
            <button onclick="closeModal('assignModal')" class="text-gray-400 hover:text-gray-700 text-2xl leading-none">×</button>
        </div>
        <div class="p-6 overflow-y-auto flex-1 space-y-3" id="assignGroups">
            <!-- Injected by JS -->
        </div>
        <div class="flex items-center justify-between px-6 py-4 border-t border-gray-100 bg-gray-50 shrink-0 rounded-b-xl">
            <p class="text-sm text-gray-500"><span id="assignCount">0</span> permission(s) selected</p>
            <div class="flex gap-3">
                <button onclick="closeModal('assignModal')" class="px-5 py-2.5 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-medium text-sm transition-colors">Cancel</button>
                <button onclick="submitAssign()" id="assignBtn" class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium text-sm transition-colors shadow">
                    <i class="fas fa-save"></i> Save
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════
     DELETE CONFIRM MODAL
════════════════════════════════════════════ -->
<div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4 modal-backdrop"
     onclick="if(event.target===this)closeModal('deleteModal')">
    <div class="bg-white rounded-xl shadow-lg w-full max-w-sm modal-enter text-center p-8">
        <div class="w-14 h-14 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-trash text-red-500 text-xl"></i>
        </div>
        <h3 class="text-lg font-bold text-gray-900 mb-2">Delete Permission</h3>
        <p class="text-gray-500 text-sm mb-6" id="deleteMsg">Are you sure? This cannot be undone.</p>
        <div class="flex gap-3 justify-center">
            <button onclick="closeModal('deleteModal')" class="px-5 py-2.5 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-medium text-sm">Cancel</button>
            <button onclick="confirmDelete()" id="deleteBtn" class="px-5 py-2.5 bg-red-600 text-white rounded-lg hover:bg-red-700 font-medium text-sm shadow">Delete</button>
        </div>
    </div>
</div>

<!-- Toast -->
<div id="toastWrap" class="toast-wrap"></div>

<script>
// ════════════════════════════════════
//  STATE
// ════════════════════════════════════
let allPermissions   = [];   // flat list
let allRoles         = [];
let permsByModule    = {};   // { module: [perm, …] }
let deleteTargetId   = null;
let assignRoleId     = null;
let assignRolePerms  = [];   // currently assigned perm IDs

// ════════════════════════════════════
//  INIT
// ════════════════════════════════════
document.addEventListener('DOMContentLoaded', () => {
    loadPermissions();
    loadModules();
});

// ════════════════════════════════════
//  TABS
// ════════════════════════════════════
function switchTab(e, id) {
    document.querySelectorAll('.tab-content').forEach(t => t.classList.add('hidden'));
    document.querySelectorAll('.tab-btn').forEach(b => {
        b.classList.remove('text-blue-600','border-blue-600');
        b.classList.add('text-gray-500','border-transparent');
    });
    document.getElementById(id).classList.remove('hidden');
    const btn = e.target.closest('.tab-btn');
    btn.classList.add('text-blue-600','border-blue-600');
    btn.classList.remove('text-gray-500','border-transparent');
    if (id === 'tab-assignments' && allRoles.length === 0) loadRoles();
}

// ════════════════════════════════════
//  LOAD PERMISSIONS
// ════════════════════════════════════
async function loadPermissions() {
    const search = document.getElementById('searchInput').value.trim();
    const module = document.getElementById('moduleFilter').value;

    let url = '/api/permissions/list?';
    if (search) url += `search=${encodeURIComponent(search)}&`;
    if (module) url += `module=${encodeURIComponent(module)}&`;

    const tbody = document.getElementById('permissionsTable');
    tbody.innerHTML = `<tr><td colspan="7" class="px-6 py-10 text-center text-gray-400"><i class="fas fa-spinner fa-spin mr-2"></i>Loading…</td></tr>`;

    try {
        const res  = await fetch(url);
        const json = await res.json();
        allPermissions = json.data?.data ?? json.data ?? [];

        // rebuild module map
        permsByModule = {};
        allPermissions.forEach(p => {
            if (!permsByModule[p.module]) permsByModule[p.module] = [];
            permsByModule[p.module].push(p);
        });

        document.getElementById('statTotal').textContent   = allPermissions.length;
        document.getElementById('statModules').textContent = Object.keys(permsByModule).length;

        renderPermissionsTable(allPermissions);
    } catch(err) {
        tbody.innerHTML = `<tr><td colspan="7" class="px-6 py-10 text-center text-red-400"><i class="fas fa-exclamation-circle mr-2"></i>Failed to load permissions</td></tr>`;
    }
}

function renderPermissionsTable(perms) {
    const tbody   = document.getElementById('permissionsTable');
    const noRes   = document.getElementById('permsNoResults');

    if (!perms.length) {
        tbody.innerHTML = '';
        noRes.classList.remove('hidden');
        return;
    }
    noRes.classList.add('hidden');

    const moduleColors = {
        users:'blue', employees:'purple', attendance:'green',
        leave:'yellow', report:'red', roles:'orange', permissions:'indigo'
    };

    tbody.innerHTML = perms.map(p => {
        const slug   = `${p.module}.${p.action}`;
        const color  = moduleColors[p.module] ?? 'gray';
        const active = parseInt(p.status_id) === 1;
        return `
        <tr class="hover:bg-gray-50 transition-colors">
            <td class="px-6 py-4"><input type="checkbox" class="perm-cb w-4 h-4 text-blue-600 rounded cursor-pointer" data-id="${p.id}"></td>
            <td class="px-6 py-4 font-mono text-sm font-semibold text-gray-800">${slug}</td>
            <td class="px-6 py-4">
                <span class="inline-block px-2.5 py-1 bg-${color}-100 text-${color}-800 text-xs font-semibold rounded capitalize">${p.module}</span>
            </td>
            <td class="px-6 py-4 text-sm text-gray-600">${p.action}</td>
            <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate">${p.description ?? '—'}</td>
            <td class="px-6 py-4">
                <span class="inline-block px-2.5 py-1 text-xs font-semibold rounded-full ${active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-500'}">
                    ${active ? 'Active' : 'Inactive'}
                </span>
            </td>
            <td class="px-6 py-4 flex gap-1">
                <button onclick="openEditModal(${p.id})" class="p-2 text-gray-500 hover:bg-gray-100 rounded-lg transition-colors" title="Edit">
                    <i class="fas fa-edit text-sm"></i>
                </button>
                <button onclick="openDeleteModal(${p.id},'${slug}')" class="p-2 text-gray-500 hover:bg-red-100 hover:text-red-600 rounded-lg transition-colors" title="Delete">
                    <i class="fas fa-trash text-sm"></i>
                </button>
            </td>
        </tr>`;
    }).join('');
}

// ════════════════════════════════════
//  LOAD MODULES (for filter dropdown)
// ════════════════════════════════════
async function loadModules() {
    try {
        const res  = await fetch('/api/permissions/categories');
        const json = await res.json();
        const mods = json.data ?? [];
        const sel  = document.getElementById('moduleFilter');
        mods.forEach(m => {
            const opt = document.createElement('option');
            opt.value = m; opt.textContent = m.charAt(0).toUpperCase() + m.slice(1);
            sel.appendChild(opt);
        });
    } catch {}
}

// ════════════════════════════════════
//  LOAD ROLES
// ════════════════════════════════════
async function loadRoles() {
    const tbody = document.getElementById('rolesTable');
    tbody.innerHTML = `<tr><td colspan="5" class="px-6 py-10 text-center text-gray-400"><i class="fas fa-spinner fa-spin mr-2"></i>Loading…</td></tr>`;

    try {
        const res  = await fetch('/api/roles');
        const json = await res.json();
        allRoles   = json.data?.data ?? json.data ?? [];

        const q = document.getElementById('roleSearchInput').value.toLowerCase();
        const filtered = q ? allRoles.filter(r => r.name.toLowerCase().includes(q) || r.slug.toLowerCase().includes(q)) : allRoles;

        renderRolesTable(filtered);
    } catch {
        tbody.innerHTML = `<tr><td colspan="5" class="px-6 py-10 text-center text-red-400">Failed to load roles</td></tr>`;
    }
}

function renderRolesTable(roles) {
    const tbody = document.getElementById('rolesTable');
    const noRes = document.getElementById('rolesNoResults');

    if (!roles.length) {
        tbody.innerHTML = '';
        noRes.classList.remove('hidden');
        return;
    }
    noRes.classList.add('hidden');

    tbody.innerHTML = roles.map(r => {
        const active = r.status === 'active' || parseInt(r.status_id) === 1;
        const permCount = Array.isArray(r.permissions) ? r.permissions.length : 0;
        return `
        <tr class="hover:bg-gray-50 transition-colors">
            <td class="px-6 py-4 font-semibold text-gray-900">${r.name}</td>
            <td class="px-6 py-4 font-mono text-sm text-gray-500">${r.slug}</td>
            <td class="px-6 py-4">
                <span class="inline-block px-2.5 py-1 bg-blue-50 text-blue-700 text-xs font-semibold rounded">${permCount} assigned</span>
            </td>
            <td class="px-6 py-4">
                <span class="inline-block px-2.5 py-1 text-xs font-semibold rounded-full ${active ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'}">
                    ${active ? 'Active' : 'Pending'}
                </span>
            </td>
            <td class="px-6 py-4">
                <button onclick="openAssignModal(${r.id},'${r.name}')"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium text-sm transition-colors shadow-sm">
                    <i class="fas fa-sliders-h"></i> Manage Permissions
                </button>
            </td>
        </tr>`;
    }).join('');
}

// ════════════════════════════════════
//  ADD PERMISSION
// ════════════════════════════════════
function openAddModal() {
    document.getElementById('addModule').value = '';
    document.getElementById('addAction').value = '';
    document.getElementById('addDesc').value   = '';
    clearErr('addModule','addModuleErr');
    clearErr('addAction','addActionErr');
    openModal('addModal');
}

async function submitAdd() {
    const module = document.getElementById('addModule').value.trim();
    const action = document.getElementById('addAction').value.trim();
    const desc   = document.getElementById('addDesc').value.trim();

    let valid = true;
    if (!module) { showErr('addModule','addModuleErr'); valid = false; } else clearErr('addModule','addModuleErr');
    if (!action) { showErr('addAction','addActionErr'); valid = false; } else clearErr('addAction','addActionErr');
    if (!valid) return;

    setBtnLoading('addBtn', true, 'Creating…');
    try {
        const res  = await fetch('/api/permissions', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ module, action, description: desc })
        });
        const json = await res.json();
        if (json.success || res.ok) {
            toast('Permission created successfully!', 'success');
            closeModal('addModal');
            loadPermissions();
            loadModules();
        } else {
            toast(json.message ?? 'Failed to create permission', 'error');
        }
    } catch { toast('Network error', 'error'); }
    setBtnLoading('addBtn', false, '<i class="fas fa-plus"></i> Create');
}

// ════════════════════════════════════
//  EDIT PERMISSION
// ════════════════════════════════════
function openEditModal(id) {
    const perm = allPermissions.find(p => p.id == id);
    if (!perm) return;

    document.getElementById('editId').value     = perm.id;
    document.getElementById('editModule').value = perm.module;
    document.getElementById('editAction').value = perm.action;
    document.getElementById('editDesc').value   = perm.description ?? '';
    document.getElementById('editStatus').value = perm.status_id ?? 1;
    clearErr('editModule','editModuleErr');
    clearErr('editAction','editActionErr');
    openModal('editModal');
}

async function submitEdit() {
    const id     = document.getElementById('editId').value;
    const module = document.getElementById('editModule').value.trim();
    const action = document.getElementById('editAction').value.trim();
    const desc   = document.getElementById('editDesc').value.trim();
    const status = document.getElementById('editStatus').value;

    let valid = true;
    if (!module) { showErr('editModule','editModuleErr'); valid = false; } else clearErr('editModule','editModuleErr');
    if (!action) { showErr('editAction','editActionErr'); valid = false; } else clearErr('editAction','editActionErr');
    if (!valid) return;

    setBtnLoading('editBtn', true, 'Saving…');
    try {
        const res  = await fetch(`/api/permissions/${id}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ module, action, description: desc, status_id: parseInt(status) })
        });
        const json = await res.json();
        if (json.success || res.ok) {
            toast('Permission updated successfully!', 'success');
            closeModal('editModal');
            loadPermissions();
        } else {
            toast(json.message ?? 'Failed to update', 'error');
        }
    } catch { toast('Network error', 'error'); }
    setBtnLoading('editBtn', false, '<i class="fas fa-save"></i> Save Changes');
}

// ════════════════════════════════════
//  DELETE PERMISSION
// ════════════════════════════════════
function openDeleteModal(id, slug) {
    deleteTargetId = id;
    document.getElementById('deleteMsg').textContent = `Delete "${slug}"? This cannot be undone.`;
    openModal('deleteModal');
}

async function confirmDelete() {
    if (!deleteTargetId) return;
    setBtnLoading('deleteBtn', true, 'Deleting…');
    try {
        const res  = await fetch(`/api/permissions/${deleteTargetId}`, { method: 'DELETE' });
        const json = await res.json();
        if (json.success || res.ok) {
            toast('Permission deleted!', 'success');
            closeModal('deleteModal');
            loadPermissions();
        } else {
            toast(json.message ?? 'Cannot delete — may be assigned to roles', 'error');
        }
    } catch { toast('Network error', 'error'); }
    setBtnLoading('deleteBtn', false, 'Delete');
    deleteTargetId = null;
}

// ════════════════════════════════════
//  ASSIGN PERMISSIONS TO ROLE
// ════════════════════════════════════
async function openAssignModal(roleId, roleName) {
    assignRoleId = roleId;
    document.getElementById('assignSubtitle').textContent = `Role: ${roleName}`;
    document.getElementById('assignGroups').innerHTML = `<p class="text-center text-gray-400"><i class="fas fa-spinner fa-spin mr-2"></i>Loading…</p>`;
    openModal('assignModal');

    try {
        // load all permissions + currently assigned ones in parallel
        const [permRes, assignedRes] = await Promise.all([
            fetch('/api/permissions/list'),
            fetch(`/api/permissions/role/${roleId}`)
        ]);
        const permJson    = await permRes.json();
        const assignedJson= await assignedRes.json();

        const all      = permJson.data?.data    ?? permJson.data    ?? [];
        const assigned = assignedJson.data?.data ?? assignedJson.data ?? [];
        assignRolePerms = assigned.map(p => p.id);

        // Group by module
        const grouped = {};
        all.forEach(p => {
            if (!grouped[p.module]) grouped[p.module] = [];
            grouped[p.module].push(p);
        });

        const moduleColors = { users:'blue',employees:'purple',attendance:'green',leave:'yellow',report:'red',roles:'orange',permissions:'indigo' };

        document.getElementById('assignGroups').innerHTML = Object.entries(grouped).map(([mod, perms]) => {
            const color    = moduleColors[mod] ?? 'gray';
            const allCheck = perms.every(p => assignRolePerms.includes(p.id));
            const items    = perms.map(p => `
                <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer p-1 rounded hover:bg-gray-50">
                    <input type="checkbox" class="assign-cb w-4 h-4 text-blue-600 rounded" value="${p.id}"
                        ${assignRolePerms.includes(p.id) ? 'checked' : ''} onchange="updateAssignCount()">
                    <span class="font-mono">${mod}.${p.action}</span>
                </label>`).join('');
            return `
            <div class="border border-gray-200 rounded-lg overflow-hidden">
                <div class="flex items-center gap-3 bg-${color}-50 px-4 py-2.5 cursor-pointer select-none" onclick="toggleAssignGroup(this)">
                    <input type="checkbox" class="group-toggle w-4 h-4 text-${color}-600 rounded border-${color}-300"
                        ${allCheck ? 'checked' : ''} onchange="toggleAssignGroupPerms(this)" onclick="event.stopPropagation()">
                    <span class="text-xs font-bold text-${color}-800 uppercase tracking-wide flex-1">${mod}</span>
                    <i class="fas fa-chevron-down text-${color}-400 text-xs group-chevron transition-transform duration-200"></i>
                </div>
                <div class="grid grid-cols-2 gap-1 p-3 bg-white">${items}</div>
            </div>`;
        }).join('');

        updateAssignCount();
    } catch {
        document.getElementById('assignGroups').innerHTML = `<p class="text-center text-red-400">Failed to load permissions</p>`;
    }
}

function toggleAssignGroup(header) {
    const body    = header.nextElementSibling;
    const chevron = header.querySelector('.group-chevron');
    const hidden  = body.classList.toggle('hidden');
    if (chevron) chevron.classList.toggle('open', !hidden);
}

function toggleAssignGroupPerms(toggle) {
    toggle.closest('.border').querySelectorAll('.assign-cb').forEach(cb => cb.checked = toggle.checked);
    updateAssignCount();
}

function updateAssignCount() {
    document.getElementById('assignCount').textContent = document.querySelectorAll('#assignGroups .assign-cb:checked').length;
}

async function submitAssign() {
    if (!assignRoleId) return;
    const ids = [...document.querySelectorAll('#assignGroups .assign-cb:checked')].map(cb => parseInt(cb.value));

    setBtnLoading('assignBtn', true, 'Saving…');
    try {
        const res  = await fetch('/api/permissions/assign-multiple-to-role', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ role_id: assignRoleId, permission_ids: ids })
        });
        const json = await res.json();
        if (json.success || res.ok) {
            toast(`${ids.length} permission(s) assigned to role!`, 'success');
            closeModal('assignModal');
            if (allRoles.length) loadRoles();
        } else {
            toast(json.message ?? 'Failed to assign', 'error');
        }
    } catch { toast('Network error', 'error'); }
    setBtnLoading('assignBtn', false, '<i class="fas fa-save"></i> Save');
}

// ════════════════════════════════════
//  SELECT ALL (permissions table)
// ════════════════════════════════════
function toggleSelectAll(master) {
    document.querySelectorAll('.perm-cb').forEach(cb => cb.checked = master.checked);
}

// ════════════════════════════════════
//  MODAL HELPERS
// ════════════════════════════════════
function openModal(id)  { document.getElementById(id).classList.remove('hidden'); }
function closeModal(id) { document.getElementById(id).classList.add('hidden'); }

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') document.querySelectorAll('[id$="Modal"]:not(.hidden)').forEach(m => closeModal(m.id));
});

// ════════════════════════════════════
//  FORM HELPERS
// ════════════════════════════════════
function showErr(inputId, errId) {
    document.getElementById(inputId).classList.add('form-error','border-red-400');
    document.getElementById(errId).classList.add('show');
}
function clearErr(inputId, errId) {
    document.getElementById(inputId).classList.remove('form-error','border-red-400');
    document.getElementById(errId).classList.remove('show');
}
function setBtnLoading(btnId, loading, html) {
    const btn = document.getElementById(btnId);
    btn.disabled = loading;
    btn.innerHTML = loading ? `<span class="spinner"></span> ${html}` : html;
}

// ════════════════════════════════════
//  TOAST
// ════════════════════════════════════
function toast(msg, type = 'success') {
    const icons = { success:'check-circle', error:'exclamation-circle', info:'info-circle' };
    const t = document.createElement('div');
    t.className = `toast ${type} px-4 py-3 rounded-lg shadow-md flex items-center gap-2`;
    t.innerHTML = `<i class="fas fa-${icons[type]}"></i><span class="flex-1 text-sm">${msg}</span><button onclick="this.parentElement.remove()" class="ml-2 text-lg leading-none opacity-60 hover:opacity-100">×</button>`;
    document.getElementById('toastWrap').appendChild(t);
    setTimeout(() => t.remove(), 5000);
}

// ════════════════════════════════════
//  DEBOUNCE
// ════════════════════════════════════
function debounce(fn, ms) {
    let t;
    return (...args) => { clearTimeout(t); t = setTimeout(() => fn(...args), ms); };
}
</script>
</body>
</html>