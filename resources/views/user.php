<?php
$pageTitle = "User Management";
$activeMenu = "users";
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
    <!-- <style>
        * { font-family: 'DM Sans', sans-serif; }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(16px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes fadeIn  { from { opacity: 0; } to { opacity: 1; } }
        @keyframes spin    { to { transform: rotate(360deg); } }
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
            background-size: 200% 100%; animation: shimmer 1.2s infinite; border-radius: 6px;
        }
        tbody tr { transition: background 0.12s; }
        tbody tr:hover { background: #f5f3ff; }

        .toast-container {
            position: fixed; top: 1rem; right: 1rem;
            z-index: 9999; display: flex; flex-direction: column; gap: 8px;
        }
        .toast {
            animation: slideUp 0.3s ease-out; min-width: 280px;
            display: flex; align-items: center; gap: 10px;
            padding: 12px 16px; border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.12); font-size: 14px; font-weight: 500;
        }
        .toast.success { background:#ecfdf5; border-left:4px solid #10b981; color:#065f46; }
        .toast.error   { background:#fef2f2; border-left:4px solid #ef4444; color:#7f1d1d; }
        .toast.info    { background:#eff6ff; border-left:4px solid #3b82f6; color:#1e3a8a; }

        .error-msg { font-size:12px; color:#dc2626; margin-top:3px; display:none; }
        .error-msg.show { display:block; }
        .field-error { border-color:#ef4444 !important; }

        .badge { display:inline-block; padding:2px 10px; border-radius:999px; font-size:12px; font-weight:600; }
        .badge-active   { background:#dcfce7; color:#15803d; }
        .badge-inactive { background:#fee2e2; color:#b91c1c; }

        .modal-scroll { max-height: 70vh; overflow-y: auto; }
    </style> -->
</head>
<body class="bg-gray-50 min-h-screen">

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-6 border-b border-gray-200 mb-6">
        <div class="flex items-center gap-3 mb-4 sm:mb-0">
            <div class="w-10 h-10 bg-indigo-600 rounded-lg flex items-center justify-center text-white">
                <i class="fas fa-users"></i>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">User Management</h1>
                <p class="text-gray-500 text-sm">Manage users and assign roles</p>
            </div>
        </div>
        <button onclick="openCreateModal()"
            class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 text-white rounded-lg font-semibold hover:bg-indigo-700 transition-colors shadow-sm text-sm">
            <i class="fas fa-plus"></i> Add User
        </button>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl p-5 border border-gray-100 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-xs font-semibold uppercase tracking-wide">Total Users</p>
                <p class="text-3xl font-bold text-gray-900 mt-1" id="statTotal">—</p>
            </div>
            <i class="fas fa-users text-indigo-300 text-3xl"></i>
        </div>
        <div class="bg-white rounded-xl p-5 border border-gray-100 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-xs font-semibold uppercase tracking-wide">Active</p>
                <p class="text-3xl font-bold text-gray-900 mt-1" id="statActive">—</p>
            </div>
            <i class="fas fa-user-check text-green-300 text-3xl"></i>
        </div>
        <div class="bg-white rounded-xl p-5 border border-gray-100 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-xs font-semibold uppercase tracking-wide">Inactive</p>
                <p class="text-3xl font-bold text-gray-900 mt-1" id="statInactive">—</p>
            </div>
            <i class="fas fa-user-slash text-red-300 text-3xl"></i>
        </div>
    </div>

    <!-- Search -->
    <div class="flex flex-col sm:flex-row gap-3 mb-5">
        <div class="relative flex-1">
            <i class="fas fa-search absolute left-3 top-3 text-gray-400 text-sm"></i>
            <input type="text" id="searchInput" placeholder="Search by name, username or email..."
                class="w-full pl-9 pr-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                oninput="applyFilters()"
                autocomplete="off">
        </div>
        <select id="filterStatus" onchange="applyFilters()"
            class="px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white min-w-max">
            <option value="">All Status</option>
            <option value="1">Active</option>
            <option value="0">Inactive</option>
        </select>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200 text-gray-600 text-xs uppercase tracking-wide">
                        <th class="px-6 py-3 text-left font-semibold">#</th>
                        <th class="px-6 py-3 text-left font-semibold">Name</th>
                        <th class="px-6 py-3 text-left font-semibold">Username</th>
                        <th class="px-6 py-3 text-left font-semibold">Email</th>
                        <th class="px-6 py-3 text-left font-semibold">Role</th>
                        <th class="px-6 py-3 text-center font-semibold">Status</th>
                        <th class="px-6 py-3 text-left font-semibold">Created</th>
                        <th class="px-6 py-3 text-center font-semibold">Actions</th>
                    </tr>
                </thead>
                <tbody id="userTableBody">
                    <?php for ($i = 0; $i < 5; $i++): ?>
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
            <p class="font-medium">No users found</p>
            <p class="text-sm mt-1">Try adjusting your search or filter</p>
        </div>

        <div class="px-6 py-3 border-t border-gray-100 text-sm text-gray-500">
            <span id="paginationInfo">Showing 0 users</span>
        </div>
    </div>
</div>

<!-- ===================== CREATE USER MODAL ===================== -->
<div id="createUserModal"
    class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4 modal-backdrop"
    onclick="if(event.target===this) closeModal('createUserModal')">
    <div class="bg-white rounded-xl shadow-xl max-w-lg w-full modal-box" role="dialog">
        <div class="flex justify-between items-center px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-bold text-gray-900">Add New User</h2>
            <button onclick="closeModal('createUserModal')" class="text-gray-400 hover:text-gray-700 text-xl leading-none">×</button>
        </div>
        <div class="px-6 py-5 space-y-4 modal-scroll">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Full Name <span class="text-red-500">*</span></label>
                    <input type="text" id="createFullName" placeholder="John Doe"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        oninput="clearFieldError(this)">
                    <p class="error-msg" id="createFullNameErr">Full name is required</p>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Username <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <span class="absolute left-3 top-2.5 text-gray-400 text-sm">@</span>
                        <input type="text" id="createUsername" placeholder="johndoe"
                            class="w-full pl-7 pr-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            oninput="clearFieldError(this)">
                    </div>
                    <p class="error-msg" id="createUsernameErr">Username is required</p>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
                    <input type="email" id="createEmail" placeholder="john@example.com"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        oninput="clearFieldError(this)">
                    <p class="error-msg" id="createEmailErr">Valid email is required</p>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Password <span class="text-red-500">*</span></label>
                    <input type="password" id="createPassword" placeholder="Enter a strong password"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        oninput="clearFieldError(this)">
                    <p class="error-msg" id="createPasswordErr">Password is required</p>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Role <span class="text-red-500">*</span></label>
                    <select id="createRoleId"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white"
                        onchange="clearFieldError(this)">
                        <option value="">Select role...</option>
                    </select>
                    <p class="error-msg" id="createRoleErr">Role is required</p>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Status</label>
                    <select id="createStatus"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="px-6 py-4 border-t border-gray-100 flex justify-end gap-3">
            <button type="button" onclick="closeModal('createUserModal')"
                class="px-5 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 font-semibold text-sm">Cancel</button>
            <button type="button" onclick="submitCreateUser()" id="createUserBtn"
                class="px-5 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-semibold text-sm flex items-center gap-2">
                <i class="fas fa-plus"></i> Add User
            </button>
        </div>
    </div>
</div>

<!-- ===================== EDIT USER MODAL ===================== -->
<div id="editUserModal"
    class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4 modal-backdrop"
    onclick="if(event.target===this) closeModal('editUserModal')">
    <div class="bg-white rounded-xl shadow-xl max-w-lg w-full modal-box" role="dialog">
        <div class="flex justify-between items-center px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-bold text-gray-900" id="editUserTitle">Edit User</h2>
            <button onclick="closeModal('editUserModal')" class="text-gray-400 hover:text-gray-700 text-xl leading-none">×</button>
        </div>
        <div class="px-6 py-5 space-y-4 modal-scroll">
            <input type="hidden" id="editUserId">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Full Name <span class="text-red-500">*</span></label>
                    <input type="text" id="editFullName"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        oninput="clearFieldError(this)">
                    <p class="error-msg" id="editFullNameErr">Full name is required</p>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Username</label>
                    <div class="relative">
                        <span class="absolute left-3 top-2.5 text-gray-400 text-sm">@</span>
                        <input type="text" id="editUsername" disabled
                            class="w-full pl-7 pr-3 py-2 border border-gray-200 rounded-lg text-sm bg-gray-50 text-gray-500 cursor-not-allowed">
                    </div>
                    <p class="text-xs text-gray-400 mt-1">Username cannot be changed</p>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
                    <input type="email" id="editEmail"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        oninput="clearFieldError(this)">
                    <p class="error-msg" id="editEmailErr">Valid email is required</p>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">
                        Password <span class="text-xs text-gray-400 font-normal">(leave blank to keep)</span>
                    </label>
                    <input type="password" id="editPassword" placeholder="New password (optional)"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Role <span class="text-red-500">*</span></label>
                    <select id="editRoleId"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white"
                        onchange="clearFieldError(this)">
                        <option value="">Select role...</option>
                    </select>
                    <p class="error-msg" id="editRoleErr">Role is required</p>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Status</label>
                    <select id="editStatus"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="px-6 py-4 border-t border-gray-100 flex justify-end gap-3">
            <button type="button" onclick="closeModal('editUserModal')"
                class="px-5 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 font-semibold text-sm">Cancel</button>
            <button type="button" onclick="submitEditUser()" id="editUserBtn"
                class="px-5 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-semibold text-sm flex items-center gap-2">
                <i class="fas fa-save"></i> Update User
            </button>
        </div>
    </div>
</div>

<!-- ===================== DELETE USER MODAL ===================== -->
<div id="deleteUserModal"
    class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4 modal-backdrop"
    onclick="if(event.target===this) closeModal('deleteUserModal')">
    <div class="bg-white rounded-xl shadow-xl max-w-sm w-full modal-box p-6 text-center" role="dialog">
        <div class="w-14 h-14 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-trash text-red-500 text-xl"></i>
        </div>
        <h2 class="text-lg font-bold text-gray-900 mb-1">Delete User?</h2>
        <p class="text-gray-500 text-sm mb-6">
            Delete <strong id="deleteUserName" class="text-gray-800"></strong>? This action cannot be undone.
        </p>
        <input type="hidden" id="deleteUserId">
        <div class="flex gap-3 justify-center">
            <button onclick="closeModal('deleteUserModal')"
                class="px-5 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 font-semibold text-sm">Cancel</button>
            <button onclick="confirmDeleteUser()" id="deleteUserBtn"
                class="px-5 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 font-semibold text-sm flex items-center gap-2">
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

let allUsers = [];
let allRoles = [];

document.addEventListener('DOMContentLoaded', () => {
    loadRoles();
    loadUsers();

    setTimeout(() => {
        const s = document.getElementById('searchInput');
        s.value = '';
        applyFilters();
    }, 200);
});


async function loadRoles() {
    try {
        const res  = await fetch(`${API_BASE}/roles`);
        const json = await res.json();

        allRoles = Array.isArray(json.data?.data) ? json.data.data
                 : Array.isArray(json.data)        ? json.data
                 : Array.isArray(json)             ? json : [];

        // Only show active roles for assignment
        const activeRoles = allRoles.filter(r => r.status === 'active');
        populateRoleSelects(activeRoles);
    } catch (err) {
        showToast('Failed to load roles', 'error');
    }
}

function populateRoleSelects(roles) {
    ['createRoleId', 'editRoleId'].forEach(id => {
        const sel = document.getElementById(id);
        const current = sel.value;
        sel.innerHTML = '<option value="">Select role...</option>';
        roles.forEach(r => {
            const opt = document.createElement('option');
            opt.value = r.id;
            opt.textContent = r.name;
            if (String(r.id) === String(current)) opt.selected = true;
            sel.appendChild(opt);
        });
    });
}

async function loadUsers() {
    try {
        const res  = await fetch(`${API_BASE}/users/show`);
        const json = await res.json();

        if (!res.ok) throw new Error(json.message || 'Failed to load users');

        allUsers = json.data?.users ?? json.data ?? [];
        updateStats();
        applyFilters();
    } catch (err) {
        showToast('Failed to load users: ' + err.message, 'error');
        document.getElementById('userTableBody').innerHTML =
            `<tr><td colspan="8" class="px-6 py-10 text-center text-gray-400">Failed to load. Please refresh.</td></tr>`;
    }
}

function updateStats() {
    const active   = allUsers.filter(u => parseInt(u.status_id) === 1).length;
    const inactive = allUsers.length - active;
    document.getElementById('statTotal').textContent   = allUsers.length;
    document.getElementById('statActive').textContent  = active;
    document.getElementById('statInactive').textContent = inactive;
}

function applyFilters() {
    const q      = document.getElementById('searchInput').value.toLowerCase();
    const status = document.getElementById('filterStatus').value;

    const filtered = allUsers.filter(u => {
        const matchSearch =
            (u.full_name || '').toLowerCase().includes(q) ||
            (u.username  || '').toLowerCase().includes(q) ||
            (u.email     || '').toLowerCase().includes(q);
        const matchStatus = !status || String(u.status_id) === status;
        return matchSearch && matchStatus;
    });

    renderTable(filtered);
}

function renderTable(users) {
    const tbody = document.getElementById('userTableBody');
    const noRes = document.getElementById('noResults');
    const info  = document.getElementById('paginationInfo');

    if (!users.length) {
        tbody.innerHTML = '';
        noRes.classList.remove('hidden');
        info.textContent = 'No users found';
        return;
    }

    noRes.classList.add('hidden');
    info.textContent = `Showing ${users.length} user${users.length > 1 ? 's' : ''}`;

    tbody.innerHTML = users.map((user, idx) => {
        const active = parseInt(user.status_id) === 1;
        return `
        <tr class="border-b border-gray-100">
            <td class="px-6 py-4 text-gray-500 font-medium">${idx + 1}</td>
            <td class="px-6 py-4 font-semibold text-gray-900">${escHtml(user.full_name || '—')}</td>
            <td class="px-6 py-4 font-mono text-xs text-gray-600">@${escHtml(user.username || '—')}</td>
            <td class="px-6 py-4 text-gray-600">${escHtml(user.email || '—')}</td>
            <td class="px-6 py-4">
                <span class="inline-block px-2.5 py-1 bg-indigo-100 text-indigo-700 text-xs font-semibold rounded">
                    ${escHtml(user.role_name || '—')}
                </span>
            </td>
            <td class="px-6 py-4 text-center">
                <span class="badge ${active ? 'badge-active' : 'badge-inactive'}">${active ? 'Active' : 'Inactive'}</span>
            </td>
            <td class="px-6 py-4 text-gray-500 text-xs">${formatDate(user.created_at)}</td>
            <td class="px-6 py-4 text-center">
                <div class="flex items-center justify-center gap-1.5">
                    <button onclick="openEditModal(${user.id})"
                        class="inline-flex items-center gap-1 px-3 py-1.5 bg-gray-100 text-gray-700 rounded-lg hover:bg-indigo-100 hover:text-indigo-700 text-xs font-semibold transition-colors">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                    <button onclick="openDeleteModal(${user.id}, '${escHtml(user.full_name)}')"
                        class="inline-flex items-center gap-1 px-3 py-1.5 bg-gray-100 text-gray-700 rounded-lg hover:bg-red-100 hover:text-red-700 text-xs font-semibold transition-colors">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </div>
            </td>
        </tr>`;
    }).join('');
}

function openCreateModal() {
    document.getElementById('createFullName').value = '';
    document.getElementById('createUsername').value = '';
    document.getElementById('createEmail').value    = '';
    document.getElementById('createPassword').value = '';
    document.getElementById('createRoleId').value   = '';
    document.getElementById('createStatus').value   = '1';
    openModal('createUserModal');
}

async function submitCreateUser() {
    const fullName = document.getElementById('createFullName').value.trim();
    const username = document.getElementById('createUsername').value.trim();
    const email    = document.getElementById('createEmail').value.trim();
    const password = document.getElementById('createPassword').value.trim();
    const roleId   = document.getElementById('createRoleId').value;
    const status   = document.getElementById('createStatus').value;

    let valid = true;
    if (!fullName) { showFieldError('createFullName','createFullNameErr','Full name is required'); valid = false; }
    if (!username) { showFieldError('createUsername','createUsernameErr','Username is required'); valid = false; }
    if (!email || !email.includes('@')) { showFieldError('createEmail','createEmailErr','Valid email is required'); valid = false; }
    if (!password) { showFieldError('createPassword','createPasswordErr','Password is required'); valid = false; }
    if (!roleId)   { showFieldError('createRoleId','createRoleErr','Role is required'); valid = false; }
    if (!valid) return;

    setLoading('createUserBtn', true, 'Creating...');
    try {
        // ✅ Consistent JSON body — same as permissions.php and roles.php
        const res  = await fetch(`${API_BASE}/users/create`, {
            method:  'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body:    JSON.stringify({ full_name: fullName, username, email, password, role_id: parseInt(roleId), status_id: parseInt(status) }),
        });
        const json = await res.json();
        if (!res.ok) throw new Error(json.message || 'Failed to create user');

        showToast('User created successfully!', 'success');
        closeModal('createUserModal');
        loadUsers();
    } catch (err) {
        showToast(err.message, 'error');
    } finally {
        setLoading('createUserBtn', false, '<i class="fas fa-plus"></i> Add User');
    }
}

function openEditModal(userId) {
    const user = allUsers.find(u => u.id === userId);
    if (!user) return;

    document.getElementById('editUserId').value    = user.id;
    document.getElementById('editFullName').value  = user.full_name || '';
    document.getElementById('editUsername').value  = user.username  || '';
    document.getElementById('editEmail').value     = user.email     || '';
    document.getElementById('editPassword').value  = '';
    document.getElementById('editStatus').value    = String(user.status_id ?? 1);
    document.getElementById('editUserTitle').textContent = `Edit User: ${user.full_name}`;

    // Set current role in dropdown
    const roleSelect = document.getElementById('editRoleId');
    roleSelect.value = String(user.role_id ?? '');

    openModal('editUserModal');
}

async function submitEditUser() {
    const id       = parseInt(document.getElementById('editUserId').value);
    const fullName = document.getElementById('editFullName').value.trim();
    const email    = document.getElementById('editEmail').value.trim();
    const password = document.getElementById('editPassword').value.trim();
    const roleId   = document.getElementById('editRoleId').value;
    const status   = document.getElementById('editStatus').value;

    let valid = true;
    if (!fullName)               { showFieldError('editFullName','editFullNameErr','Full name is required'); valid = false; }
    if (!email || !email.includes('@')) { showFieldError('editEmail','editEmailErr','Valid email is required'); valid = false; }
    if (!roleId)                 { showFieldError('editRoleId','editRoleErr','Role is required'); valid = false; }
    if (!valid) return;

    const payload = { id, full_name: fullName, email, role_id: parseInt(roleId), status_id: parseInt(status) };
    if (password) payload.password = password; // only send if changed

    setLoading('editUserBtn', true, 'Updating...');
    try {
        const res  = await fetch(`${API_BASE}/users/update`, {
            method:  'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body:    JSON.stringify(payload),
        });
        const json = await res.json();
        if (!res.ok) throw new Error(json.message || 'Failed to update user');

        showToast('User updated successfully!', 'success');
        closeModal('editUserModal');
        loadUsers();
    } catch (err) {
        showToast(err.message, 'error');
    } finally {
        setLoading('editUserBtn', false, '<i class="fas fa-save"></i> Update User');
    }
}

function openDeleteModal(userId, userName) {
    document.getElementById('deleteUserId').value         = userId;
    document.getElementById('deleteUserName').textContent = userName;
    openModal('deleteUserModal');
}

async function confirmDeleteUser() {
    const id = parseInt(document.getElementById('deleteUserId').value);
    setLoading('deleteUserBtn', true, 'Deleting...');
    try {
        const res  = await fetch(`${API_BASE}/users/delete`, {
            method:  'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body:    JSON.stringify({ id }),
        });
        const json = await res.json();
        if (!res.ok) throw new Error(json.message || 'Failed to delete user');

        allUsers = allUsers.filter(u => u.id !== id);
        updateStats();
        applyFilters();
        closeModal('deleteUserModal');
        showToast('User deleted!', 'success');
    } catch (err) {
        showToast(err.message, 'error');
    } finally {
        setLoading('deleteUserBtn', false, '<i class="fas fa-trash"></i> Delete');
    }
}

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

function formatDate(str) {
    if (!str) return '<span class="text-gray-400">—</span>';
    try {
        const d = new Date(str);
        if (isNaN(d)) return '<span class="text-gray-400">—</span>';
        return d.toLocaleDateString(undefined, { year:'numeric', month:'short', day:'numeric' });
    } catch { return '<span class="text-gray-400">—</span>'; }
}

function showFieldError(inputId, errId, msg) {
    document.getElementById(inputId).classList.add('field-error');
    const err = document.getElementById(errId);
    err.textContent = msg; err.classList.add('show');
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
</script>
</body>
</html>