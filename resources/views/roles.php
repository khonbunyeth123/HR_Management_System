<?php
$pageTitle = "Role Management";
$activeMenu = "roles";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?> - Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'DM Sans', sans-serif; }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to   { opacity: 1; }
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .modal-backdrop { animation: fadeIn 0.2s ease-out; }
        .modal-box      { animation: slideUp 0.25s ease-out; }

        .spinner {
            display: inline-block;
            width: 15px; height: 15px;
            border: 2px solid rgba(255,255,255,0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 0.7s linear infinite;
        }

        tbody tr { transition: background 0.15s; }
        tbody tr:hover { background: #f0f7ff; }

        .toast-container {
            position: fixed; top: 1rem; right: 1rem;
            z-index: 9999; display: flex; flex-direction: column; gap: 8px;
        }
        .toast {
            animation: slideUp 0.3s ease-out;
            min-width: 280px;
            display: flex; align-items: center; gap: 10px;
            padding: 12px 16px; border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.12);
            font-size: 14px; font-weight: 500;
        }
        .toast.success { background:#ecfdf5; border-left:4px solid #10b981; color:#065f46; }
        .toast.error   { background:#fef2f2; border-left:4px solid #ef4444; color:#7f1d1d; }
        .toast.info    { background:#eff6ff; border-left:4px solid #3b82f6; color:#1e3a8a; }

        .field-error   { border-color: #ef4444 !important; }
        .field-success { border-color: #10b981 !important; }
        .error-msg { font-size:12px; color:#dc2626; margin-top:3px; display:none; }
        .error-msg.show { display:block; }

        .badge {
            display:inline-block; padding:2px 10px;
            border-radius:999px; font-size:12px; font-weight:600;
        }
        .badge-active   { background:#dcfce7; color:#15803d; }
        .badge-inactive { background:#fee2e2; color:#b91c1c; }
        .badge-pending  { background:#fef3c7; color:#b45309; }

        .skeleton {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: shimmer 1.2s infinite;
            border-radius: 6px;
        }
        @keyframes shimmer {
            0%   { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }

        .btn-danger { background:#ef4444; color:white; }
        .btn-danger:hover { background:#dc2626; }
        .btn-accept { background:#10b981; color:white; }
        .btn-accept:hover { background:#059669; }

        input[type=checkbox] { accent-color: #2563eb; }
        .modal-scroll { max-height: 70vh; overflow-y: auto; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-6 border-b border-gray-200 mb-6">
        <div class="flex items-center gap-3 mb-4 sm:mb-0">
            <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center text-white">
                <i class="fas fa-crown"></i>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Role Management</h1>
                <p class="text-gray-500 text-sm">Manage system roles and permissions</p>
            </div>
        </div>
        <button onclick="openModal('addRoleModal')"
            class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition-colors shadow-sm text-sm">
            <i class="fas fa-plus"></i> Add Role
        </button>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl p-5 border border-gray-100 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-xs font-semibold uppercase tracking-wide">Total Roles</p>
                <p class="text-3xl font-bold text-gray-900 mt-1" id="statTotalRoles">—</p>
            </div>
            <i class="fas fa-crown text-blue-300 text-3xl"></i>
        </div>
        <div class="bg-white rounded-xl p-5 border border-gray-100 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-xs font-semibold uppercase tracking-wide">Pending Approval</p>
                <p class="text-3xl font-bold text-gray-900 mt-1" id="statPendingRoles">—</p>
            </div>
            <i class="fas fa-clock text-yellow-300 text-3xl"></i>
        </div>
        <div class="bg-white rounded-xl p-5 border border-gray-100 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-xs font-semibold uppercase tracking-wide">Active Users</p>
                <p class="text-3xl font-bold text-gray-900 mt-1" id="statActiveUsers">—</p>
            </div>
            <i class="fas fa-users text-purple-300 text-3xl"></i>
        </div>
    </div>

    <!-- Search + Filter -->
    <div class="flex flex-col sm:flex-row gap-3 mb-5">
        <div class="relative flex-1">
            <i class="fas fa-search absolute left-3 top-3 text-gray-400 text-sm"></i>
            <input type="text" id="searchInput" placeholder="Search roles..."
                class="w-full pl-9 pr-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                oninput="debounceSearch(renderTable, 250)">
        </div>
        <select id="filterStatus" onchange="renderTable()"
            class="px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white min-w-max">
            <option value="">All Status</option>
            <option value="active">Active</option>
            <option value="pending">Pending</option>
            <option value="inactive">Inactive</option>
        </select>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200 text-gray-600 text-xs uppercase tracking-wide">
                        <th class="px-6 py-3 text-left font-semibold">#</th>
                        <th class="px-6 py-3 text-left font-semibold">Role Name</th>
                        <th class="px-6 py-3 text-left font-semibold">Slug</th>
                        <th class="px-6 py-3 text-left font-semibold">Description</th>
                        <th class="px-6 py-3 text-center font-semibold">Permissions</th>
                        <th class="px-6 py-3 text-center font-semibold">Users</th>
                        <th class="px-6 py-3 text-center font-semibold">Status</th>
                        <th class="px-6 py-3 text-center font-semibold">Actions</th>
                    </tr>
                </thead>
                <tbody id="rolesTableBody">
                    <?php for ($i = 0; $i < 4; $i++): ?>
                    <tr class="border-b border-gray-100">
                        <?php for ($j = 0; $j < 8; $j++): ?>
                        <td class="px-6 py-4"><div class="skeleton h-4 w-full"></div></td>
                        <?php endfor; ?>
                    </tr>
                    <?php endfor; ?>
                </tbody>
            </table>
        </div>

        <div id="noResults" class="hidden text-center py-16 text-gray-400">
            <i class="fas fa-search text-4xl mb-3 opacity-30 block"></i>
            <p class="font-medium">No roles found</p>
            <p class="text-sm mt-1">Try adjusting your search or filter</p>
        </div>

        <div class="px-6 py-3 border-t border-gray-100 flex items-center justify-between text-sm text-gray-500">
            <span id="paginationInfo">Showing 0 roles</span>
            <div id="paginationControls" class="flex gap-2"></div>
        </div>
    </div>
</div>

<!-- ===================== ADD ROLE MODAL ===================== -->
<div id="addRoleModal"
    class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4 modal-backdrop"
    onclick="if(event.target===this) closeModal('addRoleModal')">
    <div class="bg-white rounded-xl shadow-xl max-w-lg w-full modal-box" role="dialog" aria-labelledby="addRoleTitle">
        <div class="flex justify-between items-center px-6 py-4 border-b border-gray-200">
            <h2 id="addRoleTitle" class="text-lg font-bold text-gray-900">Add New Role</h2>
            <button onclick="closeModal('addRoleModal')" class="text-gray-400 hover:text-gray-700 text-xl leading-none">×</button>
        </div>
        <form onsubmit="submitAddRole(event)" novalidate class="modal-scroll">
            <div class="px-6 py-5 space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Role Name <span class="text-red-500">*</span></label>
                    <input type="text" id="addRoleName" placeholder="e.g. Supervisor" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        oninput="clearFieldError(this)">
                    <p class="error-msg" id="addRoleNameErr">Role name is required</p>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Slug <span class="text-red-500">*</span></label>
                    <input type="text" id="addRoleSlug" placeholder="e.g. supervisor" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        oninput="clearFieldError(this); autoSlug(this)">
                    <p class="error-msg" id="addRoleSlugErr">Only lowercase letters, numbers, hyphens allowed</p>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Description</label>
                    <textarea id="addRoleDesc" rows="2" placeholder="Describe this role..."
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Assign Permissions</label>
                    <div class="grid grid-cols-2 gap-2" id="addPermissionsGrid">
                        <p class="text-xs text-gray-400 col-span-2">Loading permissions...</p>
                    </div>
                </div>
            </div>
            <div class="px-6 py-4 border-t border-gray-100 flex justify-end gap-3">
                <button type="button" onclick="closeModal('addRoleModal')"
                    class="px-5 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 font-semibold text-sm transition-colors">Cancel</button>
                <button type="submit" id="addRoleBtn"
                    class="px-5 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-semibold text-sm transition-colors flex items-center gap-2">
                    Create Role
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ===================== EDIT ROLE MODAL ===================== -->
<div id="editRoleModal"
    class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4 modal-backdrop"
    onclick="if(event.target===this) closeModal('editRoleModal')">
    <div class="bg-white rounded-xl shadow-xl max-w-lg w-full modal-box" role="dialog" aria-labelledby="editRoleTitle">
        <div class="flex justify-between items-center px-6 py-4 border-b border-gray-200">
            <h2 id="editRoleTitle" class="text-lg font-bold text-gray-900">Edit Role</h2>
            <button onclick="closeModal('editRoleModal')" class="text-gray-400 hover:text-gray-700 text-xl leading-none">×</button>
        </div>
        <form onsubmit="submitEditRole(event)" novalidate class="modal-scroll">
            <input type="hidden" id="editRoleId">
            <div class="px-6 py-5 space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Role Name <span class="text-red-500">*</span></label>
                    <input type="text" id="editRoleName" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        oninput="clearFieldError(this)">
                    <p class="error-msg" id="editRoleNameErr">Role name is required</p>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Slug</label>
                    <input type="text" id="editRoleSlug" readonly
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm bg-gray-50 text-gray-500 cursor-not-allowed">
                    <p class="text-xs text-gray-400 mt-1">Slug cannot be changed after creation</p>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Description</label>
                    <textarea id="editRoleDesc" rows="2"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Permissions</label>
                    <div class="grid grid-cols-2 gap-2" id="editPermissionsGrid">
                        <p class="text-xs text-gray-400 col-span-2">Loading permissions...</p>
                    </div>
                </div>
            </div>
            <div class="px-6 py-4 border-t border-gray-100 flex justify-end gap-3">
                <button type="button" onclick="closeModal('editRoleModal')"
                    class="px-5 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 font-semibold text-sm transition-colors">Cancel</button>
                <button type="submit" id="editRoleBtn"
                    class="px-5 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-semibold text-sm transition-colors flex items-center gap-2">
                    Update Role
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ===================== ACCEPT ROLE MODAL ===================== -->
<div id="acceptRoleModal"
    class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4 modal-backdrop"
    onclick="if(event.target===this) closeModal('acceptRoleModal')">
    <div class="bg-white rounded-xl shadow-xl max-w-sm w-full modal-box p-6 text-center" role="dialog">
        <div class="w-14 h-14 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-check text-green-600 text-xl"></i>
        </div>
        <h2 class="text-lg font-bold text-gray-900 mb-1">Accept Role?</h2>
        <p class="text-gray-500 text-sm mb-6">
            Approve <strong id="acceptRoleName" class="text-gray-800"></strong> and make it available for assignment?
        </p>
        <input type="hidden" id="acceptRoleId">
        <div class="flex gap-3 justify-center">
            <button onclick="closeModal('acceptRoleModal')"
                class="px-5 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 font-semibold text-sm transition-colors">Cancel</button>
            <button onclick="confirmAccept()" id="acceptRoleBtn"
                class="px-5 py-2 btn-accept rounded-lg font-semibold text-sm transition-colors flex items-center gap-2">
                <i class="fas fa-check"></i> Accept
            </button>
        </div>
    </div>
</div>

<!-- ===================== DELETE CONFIRM MODAL ===================== -->
<div id="deleteRoleModal"
    class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4 modal-backdrop"
    onclick="if(event.target===this) closeModal('deleteRoleModal')">
    <div class="bg-white rounded-xl shadow-xl max-w-sm w-full modal-box p-6 text-center" role="dialog">
        <div class="w-14 h-14 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-trash text-red-500 text-xl"></i>
        </div>
        <h2 class="text-lg font-bold text-gray-900 mb-1">Delete Role?</h2>
        <p class="text-gray-500 text-sm mb-6">
            Are you sure you want to delete <strong id="deleteRoleName" class="text-gray-800"></strong>?
            This action cannot be undone.
        </p>
        <input type="hidden" id="deleteRoleId">
        <div class="flex gap-3 justify-center">
            <button onclick="closeModal('deleteRoleModal')"
                class="px-5 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 font-semibold text-sm transition-colors">Cancel</button>
            <button onclick="confirmDelete()" id="deleteRoleBtn"
                class="px-5 py-2 btn-danger rounded-lg font-semibold text-sm transition-colors flex items-center gap-2">
                <i class="fas fa-trash"></i> Delete
            </button>
        </div>
    </div>
</div>

<!-- Toast container -->
<div id="toastContainer" class="toast-container"></div>

<!-- ===================== JAVASCRIPT ===================== -->
<script>
// ─── CONFIG ───────────────────────────────────────────────
// Dynamically resolves to: /project_doorstep/my_project_3/admin/api
const API_BASE = (function() {
    const path = window.location.pathname; // e.g. /project_doorstep/my_project_3/admin/roles
    const match = path.match(/^(.*\/admin)/);
    return match ? match[1] + '/api' : '/api';
})();

// ─── STATE ────────────────────────────────────────────────
let allRoles       = [];   // raw data from API
let filtered       = [];   // after search/filter
let allPermissions = [];   // from GET /api/permissions

// ─── INIT ─────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    loadPermissions();   // load permissions first, then roles
});

// ─── LOAD PERMISSIONS ─────────────────────────────────────
// GET /api/permissions
// Response shape: { success, data: [ { id, permission_slug, module, action, description }, ... ] }
async function loadPermissions() {
    try {
        const res  = await fetch(`${API_BASE}/permissions`);
        const json = await res.json();
        
        console.log('RAW JSON:', json);  // <-- add this

        if (!res.ok) throw new Error(json.message || 'Failed to load permissions');

        const raw = Array.isArray(json.data?.data) ? json.data.data
                  : Array.isArray(json.data)        ? json.data
                  : Array.isArray(json)             ? json
                  : [];

        console.log('RAW array:', raw);  // <-- add this

        allPermissions = raw.map(p => ({
            value: p.value,
            label: p.label,
        }));

        console.log('allPermissions:', allPermissions);  // <-- add this

        buildPermissionGrids();
    } catch (err) {
        showToast('Failed to load permissions: ' + err.message, 'error');
        console.error('loadPermissions error:', err);
    } finally {
        loadRoles();
    }
}

// Helper: "attendance.create" → "Create Attendance" (or use description if provided)
function formatPermissionLabel(slug, description) {
    if (description) return description;
    const [module, action] = slug.split('.');
    return `${capitalize(action)} ${capitalize(module)}`;
}
function capitalize(str) {
    return str ? str.charAt(0).toUpperCase() + str.slice(1) : '';
}

// ─── LOAD ROLES ───────────────────────────────────────────
// GET /api/roles
// Response shape: { success, data: { data: [...], count, filters } }
async function loadRoles() {
    try {
        const res  = await fetch(`${API_BASE}/roles`);
        const json = await res.json();

        if (!res.ok) throw new Error(json.message || 'Failed to load roles');

        allRoles = Array.isArray(json.data?.data) ? json.data.data
                 : Array.isArray(json.data)        ? json.data
                 : Array.isArray(json)              ? json
                 : [];

        updateStats();
        renderTable();

        // Load permission counts for each role in background
        loadPermissionCounts();

    } catch (err) {
        showToast('Failed to load roles: ' + err.message, 'error');
        console.error('loadRoles error:', err);
        document.getElementById('rolesTableBody').innerHTML =
            `<tr><td colspan="8" class="px-6 py-10 text-center text-gray-400">Failed to load data. Please refresh.</td></tr>`;
    }
}

async function loadPermissionCounts() {
    for (const role of allRoles) {
        try {
            const res  = await fetch(`${API_BASE}/roles/${role.id}`);
            const json = await res.json();
            const detail = json.data?.data ?? json.data ?? json;
            role.permissions = detail.permissions || [];
        } catch (e) {
            console.error(`Failed to load permissions for role ${role.id}:`, e);
        }
    }
    renderTable(); // re-render with counts
}

// ─── STATS ────────────────────────────────────────────────
function updateStats() {
    document.getElementById('statTotalRoles').textContent   = allRoles.length;
    document.getElementById('statPendingRoles').textContent = allRoles.filter(r => r.status === 'pending').length;
    document.getElementById('statActiveUsers').textContent  =
        allRoles.reduce((sum, r) => sum + (r.user_count || 0), 0);
}

// ─── RENDER TABLE ─────────────────────────────────────────
function renderTable() {
    const query  = document.getElementById('searchInput').value.toLowerCase();
    const status = document.getElementById('filterStatus').value;

    filtered = allRoles.filter(role => {
        const matchSearch =
            role.name.toLowerCase().includes(query) ||
            role.slug.toLowerCase().includes(query) ||
            (role.description || '').toLowerCase().includes(query);
        const matchStatus = !status || role.status === status;
        return matchSearch && matchStatus;
    });

    const tbody    = document.getElementById('rolesTableBody');
    const noResult = document.getElementById('noResults');
    const info     = document.getElementById('paginationInfo');

    if (!filtered.length) {
        tbody.innerHTML = '';
        noResult.classList.remove('hidden');
        info.textContent = 'No roles found';
        return;
    }

    noResult.classList.add('hidden');
    info.textContent = `Showing ${filtered.length} role${filtered.length > 1 ? 's' : ''}`;

    tbody.innerHTML = filtered.map((role, idx) => {
        const statusBadgeClass = role.status === 'active'  ? 'badge-active'
                               : role.status === 'pending' ? 'badge-pending'
                               : 'badge-inactive';
        const statusText = role.status === 'active'  ? 'Active'
                         : role.status === 'pending' ? 'Pending'
                         : 'Inactive';

        // permissions can be an array of slugs or permission objects
        const permCount = Array.isArray(role.permissions) ? role.permissions.length : 0;

        return `
        <tr class="border-b border-gray-100">
            <td class="px-6 py-4 text-gray-500 font-medium">${idx + 1}</td>
            <td class="px-6 py-4">
                <span class="font-semibold text-gray-900">${escHtml(role.name)}</span>
            </td>
            <td class="px-6 py-4">
                <code class="text-xs bg-gray-100 text-gray-700 px-2 py-1 rounded">${escHtml(role.slug)}</code>
            </td>
            <td class="px-6 py-4 text-gray-500 max-w-xs truncate">${escHtml(role.description || '—')}</td>
            <td class="px-6 py-4 text-center">
                <span class="inline-block px-2 py-0.5 bg-blue-100 text-blue-700 rounded-full text-xs font-semibold">
                    ${permCount}
                </span>
            </td>
            <td class="px-6 py-4 text-center text-gray-700 font-medium">${role.user_count ?? 0}</td>
            <td class="px-6 py-4 text-center">
                <span class="badge ${statusBadgeClass}">${statusText}</span>
            </td>
            <td class="px-6 py-4 text-center">
                <div class="flex items-center justify-center gap-2 flex-wrap">
                    ${role.status === 'pending' ? `
                    <button onclick="openAcceptModal(${role.id}, '${escHtml(role.name)}')"
                        class="inline-flex items-center gap-1 px-3 py-1.5 btn-accept rounded-lg hover:bg-green-600 text-white text-xs font-semibold transition-colors">
                        <i class="fas fa-check"></i> Accept
                    </button>
                    ` : ''}
                    <button onclick="openEditModal(${role.id})"
                        class="inline-flex items-center gap-1 px-3 py-1.5 bg-gray-100 text-gray-700 rounded-lg hover:bg-blue-100 hover:text-blue-700 text-xs font-semibold transition-colors">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                    <button onclick="openDeleteModal(${role.id}, '${escHtml(role.name)}')"
                        class="inline-flex items-center gap-1 px-3 py-1.5 bg-gray-100 text-gray-700 rounded-lg hover:bg-red-100 hover:text-red-700 text-xs font-semibold transition-colors">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </div>
            </td>
        </tr>
        `;
    }).join('');
}

// ─── PERMISSION GRIDS ─────────────────────────────────────
function buildPermissionGrids() {
    if (!allPermissions.length) return;

    ['addPermissionsGrid', 'editPermissionsGrid'].forEach(id => {
        document.getElementById(id).innerHTML = allPermissions.map(p => `
            <label class="flex items-center gap-2 cursor-pointer hover:bg-gray-50 p-2 rounded text-sm">
                <input type="checkbox" value="${escHtml(p.value)}" class="w-4 h-4 rounded">
                <span class="text-gray-700">${escHtml(p.label)}</span>
            </label>
        `).join('');
    });
}

function getCheckedPermissions(gridId) {
    return [...document.querySelectorAll(`#${gridId} input[type=checkbox]:checked`)]
        .map(cb => cb.value);
}

function setCheckedPermissions(gridId, permissions = []) {
    document.querySelectorAll(`#${gridId} input[type=checkbox]`).forEach(cb => {
        cb.checked = permissions.includes(cb.value);
    });
}

// ─── MODALS ───────────────────────────────────────────────
function openModal(id) {
    document.getElementById(id).classList.remove('hidden');
}

function closeModal(id) {
    document.getElementById(id).classList.add('hidden');
    const form = document.getElementById(id).querySelector('form');
    if (form) {
        form.reset();
        form.querySelectorAll('.field-error').forEach(el => el.classList.remove('field-error'));
        form.querySelectorAll('.error-msg').forEach(el => el.classList.remove('show'));
    }
    document.querySelectorAll(`#${id} input[type=checkbox]`).forEach(cb => cb.checked = false);
}

// Open edit modal → fetch latest role detail from API to get current permissions
async function openEditModal(roleId) {
    openModal('editRoleModal');

    // Reset permissions first
    document.querySelectorAll('#editPermissionsGrid input[type=checkbox]').forEach(cb => cb.checked = false);

    // Helper to set slug (readonly field)
    function setSlug(val) {
        document.getElementById('editRoleSlug').value = val || '';
    }

    // Optimistically populate from local state first
    const local = allRoles.find(r => String(r.id) === String(roleId));
    if (local) {
        document.getElementById('editRoleId').value   = local.id;
        document.getElementById('editRoleName').value = local.name        || '';
        document.getElementById('editRoleDesc').value = local.description || '';
        document.getElementById('editRoleTitle').textContent = `Edit Role: ${local.name || ''}`;
        setSlug(local.slug);
    }

    try {
        const res  = await fetch(`${API_BASE}/roles/${roleId}`);
        const json = await res.json();

        if (!res.ok) throw new Error(json.message || 'Failed to load role');

        // Handle response shape: { data: { data: {...} } }
        const role = json.data?.data ?? json.data ?? json;

        // slug is not returned by single role API, use local state
        const localRole = allRoles.find(r => String(r.id) === String(roleId));

        document.getElementById('editRoleId').value   = role.id;
        document.getElementById('editRoleName').value = role.name        || '';
        document.getElementById('editRoleDesc').value = role.description || '';
        document.getElementById('editRoleTitle').textContent = `Edit Role: ${role.name || ''}`;
        setSlug(localRole?.slug || '');

        // permissions is already array of slugs ["attendance.create", ...]
        const perms = (role.permissions || []).map(p =>
            typeof p === 'string' ? p : (p.value ?? p.permission_slug ?? '')
        );

        setCheckedPermissions('editPermissionsGrid', perms);

    } catch (err) {
        showToast('Could not load role details: ' + err.message, 'error');
        console.error('openEditModal error:', err);
    }
}
function openAcceptModal(roleId, roleName) {
    document.getElementById('acceptRoleId').value         = roleId;
    document.getElementById('acceptRoleName').textContent = roleName;
    openModal('acceptRoleModal');
}

function openDeleteModal(roleId, roleName) {
    document.getElementById('deleteRoleId').value         = roleId;
    document.getElementById('deleteRoleName').textContent = roleName;
    openModal('deleteRoleModal');
}

// ─── FORM VALIDATION ──────────────────────────────────────
function validateRequired(inputId, errId, message) {
    const input = document.getElementById(inputId);
    const err   = document.getElementById(errId);
    if (!input.value.trim()) {
        input.classList.add('field-error');
        err.textContent = message;
        err.classList.add('show');
        return false;
    }
    return true;
}

function validateSlug(inputId, errId) {
    const input = document.getElementById(inputId);
    const err   = document.getElementById(errId);
    const val   = input.value.trim();
    if (!val) {
        input.classList.add('field-error');
        err.textContent = 'Slug is required';
        err.classList.add('show');
        return false;
    }
    if (!/^[a-z0-9_-]+$/.test(val)) {
        input.classList.add('field-error');
        err.textContent = 'Only lowercase letters, numbers, hyphens, underscores';
        err.classList.add('show');
        return false;
    }
    return true;
}

function clearFieldError(input) {
    input.classList.remove('field-error');
    const err = input.parentElement.querySelector('.error-msg');
    if (err) err.classList.remove('show');
}

function autoSlug(input) {
    const slugId = input.id.replace('Name', 'Slug');
    const slugEl = document.getElementById(slugId);
    if (slugEl && !slugEl.disabled) {
        slugEl.value = input.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
    }
}

// ─── SUBMIT ADD ───────────────────────────────────────────
// POST /api/roles
// Body: { name, slug, description, permissions: ["slug1", ...] }
async function submitAddRole(event) {
    event.preventDefault();

    const validName = validateRequired('addRoleName', 'addRoleNameErr', 'Role name is required');
    const validSlug = validateSlug('addRoleSlug', 'addRoleSlugErr');
    if (!validName || !validSlug) return;

    const payload = {
        name:        document.getElementById('addRoleName').value.trim(),
        slug:        document.getElementById('addRoleSlug').value.trim(),
        description: document.getElementById('addRoleDesc').value.trim(),
        permissions: getCheckedPermissions('addPermissionsGrid'),
    };

    const btn = document.getElementById('addRoleBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner"></span> Creating...';

    try {
        const res  = await fetch(`${API_BASE}/roles`, {
            method:  'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body:    JSON.stringify(payload),
        });
        const json = await res.json();
        if (!res.ok) throw new Error(json.message || 'Failed to create role');

        // Add newly created role to local state
        const newRole = json.data?.data ?? json.data ?? json;
        allRoles.push(newRole);

        updateStats();
        renderTable();
        closeModal('addRoleModal');
        showToast('Role created successfully!', 'success');
    } catch (err) {
        showToast(err.message || 'Failed to create role', 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = 'Create Role';
    }
}

// ─── SUBMIT EDIT ──────────────────────────────────────────
// PUT /api/roles/{id}
// Body: { name, description, permissions: ["slug1", ...] }
async function submitEditRole(event) {
    event.preventDefault();

    const validName = validateRequired('editRoleName', 'editRoleNameErr', 'Role name is required');
    if (!validName) return;

    const id      = parseInt(document.getElementById('editRoleId').value);
    const payload = {
        name:        document.getElementById('editRoleName').value.trim(),
        description: document.getElementById('editRoleDesc').value.trim(),
        permissions: getCheckedPermissions('editPermissionsGrid'),
    };

    const btn = document.getElementById('editRoleBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner"></span> Updating...';

    try {
        const res  = await fetch(`${API_BASE}/roles/${id}`, {
            method:  'PUT',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body:    JSON.stringify(payload),
        });
        const json = await res.json();
        if (!res.ok) throw new Error(json.message || 'Failed to update role');

        // Refresh updated role in local state
        const updated = json.data?.data ?? json.data ?? { ...payload, id };
        const idx = allRoles.findIndex(r => r.id === id);
        if (idx !== -1) allRoles[idx] = { ...allRoles[idx], ...updated };

        renderTable();
        closeModal('editRoleModal');
        showToast('Role updated successfully!', 'success');
    } catch (err) {
        showToast(err.message || 'Failed to update role', 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = 'Update Role';
    }
}

// ─── ACCEPT ───────────────────────────────────────────────
// PATCH /api/roles/{id}/status
// Body: { status: "active" }
async function confirmAccept() {
    const id  = parseInt(document.getElementById('acceptRoleId').value);
    const btn = document.getElementById('acceptRoleBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner"></span> Accepting...';

    try {
        const res  = await fetch(`${API_BASE}/roles/${id}/status`, {
            method:  'PATCH',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body:    JSON.stringify({ status: 'active' }),
        });
        const json = await res.json();
        if (!res.ok) throw new Error(json.message || 'Failed to accept role');

        const idx = allRoles.findIndex(r => r.id === id);
        if (idx !== -1) allRoles[idx].status = 'active';

        updateStats();
        renderTable();
        closeModal('acceptRoleModal');
        showToast('Role accepted and is now active!', 'success');
    } catch (err) {
        showToast(err.message || 'Failed to accept role', 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-check"></i> Accept';
    }
}

// ─── DELETE ───────────────────────────────────────────────
// DELETE /api/roles/{id}
async function confirmDelete() {
    const id  = parseInt(document.getElementById('deleteRoleId').value);
    const btn = document.getElementById('deleteRoleBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner"></span> Deleting...';

    try {
        const res  = await fetch(`${API_BASE}/roles/${id}`, {
            method:  'DELETE',
            headers: { 'Accept': 'application/json' },
        });
        const json = await res.json();
        if (!res.ok) throw new Error(json.message || 'Failed to delete role');

        allRoles = allRoles.filter(r => r.id !== id);

        updateStats();
        renderTable();
        closeModal('deleteRoleModal');
        showToast('Role deleted successfully!', 'success');
    } catch (err) {
        showToast(err.message || 'Failed to delete role', 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-trash"></i> Delete';
    }
}

// ─── UTILITIES ────────────────────────────────────────────
let debounceTimer;
function debounceSearch(fn, delay) {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(fn, delay);
}

function escHtml(str) {
    return String(str)
        .replace(/&/g,'&amp;').replace(/</g,'&lt;')
        .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function showToast(message, type = 'success') {
    const icons = { success: 'check-circle', error: 'exclamation-circle', info: 'info-circle' };
    const c = document.getElementById('toastContainer');
    const t = document.createElement('div');
    t.className = `toast ${type}`;
    t.innerHTML = `
        <i class="fas fa-${icons[type] || 'info-circle'}"></i>
        <span>${message}</span>
        <button onclick="this.parentElement.remove()" class="ml-auto text-lg leading-none hover:opacity-70">×</button>
    `;
    c.appendChild(t);
    setTimeout(() => t.remove(), 5000);
}

// Close on Escape
document.addEventListener('keydown', e => {
    if (e.key !== 'Escape') return;
    document.querySelectorAll('.fixed[id$="Modal"]:not(.hidden)').forEach(m => closeModal(m.id));
});

// Auto-generate slug from name in Add modal
document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('addRoleName').addEventListener('input', function () {
        const slug = document.getElementById('addRoleSlug');
        slug.value = this.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
        clearFieldError(slug);
    });
});
</script>
</body>
</html>