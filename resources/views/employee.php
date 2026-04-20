<?php
// employee.php — Employee Directory Page
// Connects to your backend API at /api/employees
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Directory</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/iconify/2.1.0/iconify.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Segoe UI', sans-serif; }
        .animate-spin { animation: spin 1s linear infinite; }
        @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
    </style>
</head>
<body class="bg-gradient-to-br from-slate-50 to-slate-100 min-h-screen">
<div class="p-2">

    <!-- ── Header ─────────────────────────────────────── -->
    <div class="bg-white rounded-lg shadow-sm p-4 mb-4">
        <div class="flex flex-col gap-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <iconify-icon icon="mdi:users-group" style="font-size:24px;color:#4f46e5;"></iconify-icon>
                    <h1 class="text-lg font-bold text-gray-900">Employee Directory</h1>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs font-semibold bg-indigo-100 text-indigo-700 px-3 py-1 rounded-full" id="totalCount">0 Staff</span>
                    <button onclick="openCreateModal()"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-semibold flex items-center gap-2 transition-colors">
                        <iconify-icon icon="mdi:plus-circle"></iconify-icon>
                        Add Employee
                    </button>
                </div>
            </div>

            <!-- Search & Filter Bar -->
            <div class="flex flex-col sm:flex-row gap-2">
                <div class="flex-1 relative">
                    <iconify-icon icon="mdi:magnify"
                        style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#9ca3af;font-size:18px;">
                    </iconify-icon>
                    <input type="text" id="searchInput" placeholder="Search by name, employee ID, or username..."
                        class="w-full pl-9 pr-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                </div>
                <select id="departmentFilter"
                    class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white cursor-pointer">
                    <option value="">All Departments</option>
                </select>
                <select id="positionFilter"
                    class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white cursor-pointer">
                    <option value="">All Positions</option>
                </select>
            </div>
        </div>
    </div>

    <!-- ── Table ──────────────────────────────────────── -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gradient-to-b from-slate-900 via-slate-800 to-slate-900 shadow-lg text-white">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold">Emp ID</th>
                        <th class="px-4 py-3 text-left font-semibold">Name</th>
                        <th class="px-4 py-3 text-left font-semibold">Username</th>
                        <th class="px-4 py-3 text-left font-semibold">Phone</th>
                        <th class="px-4 py-3 text-left font-semibold">Position</th>
                        <th class="px-4 py-3 text-left font-semibold">Department</th>
                        <th class="px-4 py-3 text-left font-semibold">Hired</th>
                        <th class="px-4 py-3 text-left font-semibold">Status</th>
                        <th class="px-4 py-3 text-center font-semibold">Actions</th>
                    </tr>
                </thead>
                <tbody id="employeeTableBody" class="divide-y divide-gray-100">
                    <tr>
                        <td colspan="10" class="px-4 py-6 text-center text-gray-400">
                            <div class="flex items-center justify-center gap-2">
                                <iconify-icon icon="mdi:loading" style="font-size:20px;" class="animate-spin"></iconify-icon>
                                Loading...
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div id="paginationContainer" class="p-4 border-t border-gray-200"></div>
    </div>
</div>

<!-- ── Create / Edit Modal ────────────────────────────── -->
<div id="employeeModal"
    class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-3xl max-h-[90vh] overflow-y-auto">

        <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between">
            <h2 class="text-xl font-bold text-gray-900" id="modalTitle">Add New Employee</h2>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                <iconify-icon icon="mdi:close" style="font-size:24px;"></iconify-icon>
            </button>
        </div>

        <form id="employeeForm" class="p-6 space-y-5">
            <input type="hidden" id="employeeId">

            <!-- Personal Information -->
            <div>
                <h3 class="text-sm font-bold text-gray-700 mb-3 flex items-center gap-2">
                    <iconify-icon icon="mdi:account" style="font-size:18px;color:#4f46e5;"></iconify-icon>
                    Personal Information
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                    <!-- Photo Upload -->
                    <div class="md:col-span-3">
                        <label class="block text-sm font-semibold text-gray-700 mb-3">Profile Photo</label>
                        <div class="flex items-center gap-5">
                            <!-- Avatar Preview -->
                            <div class="relative flex-shrink-0">
                                <img id="photoPreview"
                                    src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='96' height='96' viewBox='0 0 96 96'%3E%3Ccircle cx='48' cy='48' r='48' fill='%23e5e7eb'/%3E%3Ccircle cx='48' cy='38' r='17' fill='%239ca3af'/%3E%3Cellipse cx='48' cy='84' rx='26' ry='22' fill='%239ca3af'/%3E%3C/svg%3E"
                                    class="w-24 h-24 rounded-full object-cover border-2 border-gray-200 shadow-sm">
                                <!-- Remove button -->
                                <button type="button" id="removePhotoBtn" onclick="removePhoto()"
                                    class="hidden absolute -top-1 -right-1 w-5 h-5 rounded-full bg-red-500 text-white text-xs flex items-center justify-center border-2 border-white hover:bg-red-600 transition-colors"
                                    title="Remove photo">&#x2715;</button>
                            </div>
                            <!-- Upload controls -->
                            <div>
                                <label for="photo"
                                    class="cursor-pointer inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition">
                                    <iconify-icon icon="mdi:upload"></iconify-icon>
                                    Upload Photo
                                </label>
                                <input type="file" id="photo" accept="image/jpeg,image/png,image/webp" class="hidden">
                                <p id="photoHint" class="text-xs text-gray-500 mt-2">JPG, PNG, WEBP (max 2 MB)</p>
                                <p id="photoFileName" class="hidden text-xs text-indigo-600 mt-2 font-medium"></p>
                                <p id="photoError" class="hidden text-xs text-red-500 mt-2"></p>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">First Name *</label>
                        <input type="text" id="first_name" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            placeholder="John">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Last Name *</label>
                        <input type="text" id="last_name" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            placeholder="Doe">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Gender *</label>
                        <select id="gender" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                            <option value="">Select</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Username *</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">@</span>
                            <input type="text" id="username" required
                                class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                placeholder="johndoe">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Employee ID</label>
                        <input type="text" id="employee_code" readonly
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-600"
                            placeholder="Auto-generated after save">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Email *</label>
                        <div class="relative">
                            <iconify-icon icon="mdi:email-outline"
                                style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#9ca3af;font-size:16px;">
                            </iconify-icon>
                            <input type="email" id="email" required
                                class="w-full pl-4 pr-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                placeholder="john.doe@example.com">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Phone Number</label>
                        <div class="relative">
                            <iconify-icon icon="mdi:phone-outline"
                                style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#9ca3af;font-size:16px;">
                            </iconify-icon>
                            <input type="text" id="phone"
                                class="w-full pl-4 pr-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                placeholder="012 345 678">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">User ID <span class="font-normal text-gray-400">(optional)</span></label>
                        <input type="number" id="user_id"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            placeholder="Leave empty if none">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Address *</label>
                        <input type="text" id="address" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            placeholder="123 Main St, City, State">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Date of Birth *</label>
                        <input type="date" id="dob" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>
            </div>

            <!-- Job Information -->
            <div>
                <h3 class="text-sm font-bold text-gray-700 mb-3 flex items-center gap-2">
                    <iconify-icon icon="mdi:briefcase" style="font-size:18px;color:#4f46e5;"></iconify-icon>
                    Job Information
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Position *</label>
                        <input type="text" id="position" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            placeholder="Software Engineer">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Department *</label>
                        <input type="text" id="department" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            placeholder="Engineering">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Date Hired *</label>
                        <input type="date" id="date_hired" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Status *</label>
                        <select id="status_id" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="1">Active</option>
                            <option value="2">Inactive</option>
                            <option value="3">On Leave</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-2 pt-4 border-t border-gray-200">
                <button type="button" onclick="closeModal()"
                    class="px-5 py-2.5 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors font-medium">
                    Cancel
                </button>
                <button type="submit"
                    class="px-5 py-2.5 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors flex items-center gap-2 font-medium">
                    <iconify-icon icon="mdi:content-save"></iconify-icon>
                    <span id="submitButtonText">Save Employee</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ── Delete Confirmation Modal ──────────────────────── -->
<div id="deleteModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md">
        <div class="p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
                    <iconify-icon icon="mdi:alert" style="font-size:24px;color:#dc2626;"></iconify-icon>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-900">Delete Employee</h3>
                    <p class="text-sm text-gray-600">This action cannot be undone</p>
                </div>
            </div>
            <p class="text-gray-700 mb-6">Are you sure you want to delete <strong id="deleteEmployeeName"></strong>?</p>
            <div class="flex justify-end gap-2">
                <button onclick="closeDeleteModal()"
                    class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors font-medium">
                    Cancel
                </button>
                <button onclick="confirmDelete()"
                    class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors flex items-center gap-2 font-medium">
                    <iconify-icon icon="mdi:delete"></iconify-icon>
                    Delete
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ── Toast Notification ─────────────────────────────── -->
<div id="toast" class="hidden fixed top-4 right-4 z-50 max-w-sm">
    <div class="bg-white rounded-lg shadow-lg border-l-4 p-4 flex items-center gap-3">
        <iconify-icon id="toastIcon" style="font-size:24px;"></iconify-icon>
        <div>
            <p class="font-semibold text-gray-900" id="toastTitle"></p>
            <p class="text-sm text-gray-600" id="toastMessage"></p>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════
     JAVASCRIPT
══════════════════════════════════════════════════════ -->
<script>
/* ── DOM References ─────────────────────────── */
const employeeForm     = document.getElementById('employeeForm');
const employeeIdInput  = document.getElementById('employeeId');
const employeeCodeInput = document.getElementById('employee_code');
const usernameInput    = document.getElementById('username');
const firstNameInput   = document.getElementById('first_name');
const lastNameInput    = document.getElementById('last_name');
const genderInput      = document.getElementById('gender');
const emailInput       = document.getElementById('email');
const phoneInput       = document.getElementById('phone');
const addressInput     = document.getElementById('address');
const dobInput         = document.getElementById('dob');
const positionInput    = document.getElementById('position');
const departmentInput  = document.getElementById('department');
const dateHiredInput   = document.getElementById('date_hired');
const statusIdInput    = document.getElementById('status_id');
const userIdInput      = document.getElementById('user_id');

const photoInput       = document.getElementById('photo');
const photoPreview     = document.getElementById('photoPreview');
const removePhotoBtn   = document.getElementById('removePhotoBtn');
const photoHint        = document.getElementById('photoHint');
const photoFileName    = document.getElementById('photoFileName');
const photoError       = document.getElementById('photoError');

const searchInput          = document.getElementById('searchInput');
const departmentFilter     = document.getElementById('departmentFilter');
const positionFilter       = document.getElementById('positionFilter');
const employeeTableBody    = document.getElementById('employeeTableBody');
const totalCount           = document.getElementById('totalCount');
const paginationContainer  = document.getElementById('paginationContainer');

const employeeModal    = document.getElementById('employeeModal');
const deleteModal      = document.getElementById('deleteModal');
const deleteEmpName    = document.getElementById('deleteEmployeeName');

/* ── State ──────────────────────────────────── */
let allEmployees    = [];
let currentPage     = 1;
const perPage       = 18;
let totalPages      = 1;
let deleteEmpId     = null;
let photoBase64     = null;   // holds selected photo as base64 data URL

const DEFAULT_AVATAR = photoPreview.src;   // save original placeholder

/* ── Helpers: Status Badge ───────────────────── */
function statusBadge(statusId) {
    const map = {
        1: { label: 'Active',   cls: 'bg-green-100 text-green-700' },
        2: { label: 'Inactive', cls: 'bg-gray-100 text-gray-600' },
        3: { label: 'On Leave', cls: 'bg-yellow-100 text-yellow-700' },
    };
    const s = map[statusId] || { label: 'Unknown', cls: 'bg-gray-100 text-gray-500' };
    return `<span class="px-2 py-0.5 rounded-full text-xs font-semibold ${s.cls}">${s.label}</span>`;
}

function encodeDataAttr(value) {
    return encodeURIComponent(String(value ?? ''));
}

function decodeDataAttr(value) {
    if (value === undefined || value === null || value === '') return '';
    try {
        return decodeURIComponent(value);
    } catch (err) {
        return String(value);
    }
}

/* ── Toast ──────────────────────────────────── */
function showToast(title, message, type = 'success') {
    const toast     = document.getElementById('toast');
    const icon      = document.getElementById('toastIcon');
    const toastTitle   = document.getElementById('toastTitle');
    const toastMessage = document.getElementById('toastMessage');
    const box = toast.querySelector('div');

    icon.setAttribute('icon', type === 'success' ? 'mdi:check-circle' : 'mdi:alert-circle');
    icon.style.color = type === 'success' ? '#10b981' : '#ef4444';
    box.className = `bg-white rounded-lg shadow-lg border-l-4 p-4 flex items-center gap-3 ${
        type === 'success' ? 'border-green-500' : 'border-red-500'
    }`;
    toastTitle.textContent   = title;
    toastMessage.textContent = message;
    toast.classList.remove('hidden');
    setTimeout(() => toast.classList.add('hidden'), 3000);
}

/* ── Photo Upload Logic ─────────────────────── */
photoInput.addEventListener('change', () => {
    const file = photoInput.files[0];
    photoError.classList.add('hidden');
    photoFileName.classList.add('hidden');

    if (!file) return;

    if (!file.type.startsWith('image/')) {
        photoError.textContent = 'Please select a valid image file (JPG, PNG, WEBP).';
        photoError.classList.remove('hidden');
        photoInput.value = '';
        return;
    }

    if (file.size > 2 * 1024 * 1024) {
        photoError.textContent = 'Image must be under 2 MB. Please choose a smaller file.';
        photoError.classList.remove('hidden');
        photoInput.value = '';
        return;
    }

    const reader = new FileReader();
    reader.onload = (e) => {
        photoBase64 = e.target.result;
        photoPreview.src = photoBase64;
        removePhotoBtn.classList.remove('hidden');
        photoHint.classList.add('hidden');
        photoFileName.textContent = file.name;
        photoFileName.classList.remove('hidden');
    };
    reader.readAsDataURL(file);
});

function removePhoto() {
    photoBase64 = null;
    photoPreview.src = DEFAULT_AVATAR;
    removePhotoBtn.classList.add('hidden');
    photoInput.value = '';
    photoFileName.classList.add('hidden');
    photoError.classList.add('hidden');
    photoHint.classList.remove('hidden');
}

/* ── Load Employees ─────────────────────────── */
async function loadEmployees() {
    try {
        const res  = await fetch('/api/employees');
        const json = await res.json();
        if (!json.success) { showToast('Error', 'Failed to load employees', 'error'); return; }

        const rows = Array.isArray(json.data) ? json.data : [];
        allEmployees = rows.map(normalizeEmployeeRecord);
        totalCount.textContent = `${allEmployees.length} Staff`;
        populateFilters();
        currentPage = 1;
        applyFilters();
    } catch (err) {
        showToast('Error', 'Failed to connect to server', 'error');
        console.error(err);
    }
}

/* ── Populate Filter Dropdowns ──────────────── */
function populateFilters() {
    const depts = [...new Set(allEmployees.map(e => e.department).filter(Boolean))].sort();
    const posts = [...new Set(allEmployees.map(e => e.position).filter(Boolean))].sort();

    departmentFilter.innerHTML = '<option value="">All Departments</option>' +
        depts.map(d => `<option value="${d}">${d}</option>`).join('');
    positionFilter.innerHTML = '<option value="">All Positions</option>' +
        posts.map(p => `<option value="${p}">${p}</option>`).join('');
}

/* ── Filter & Pagination ────────────────────── */
function applyFilters() {
    const search = searchInput.value.toLowerCase();
    const dept   = departmentFilter.value;
    const pos    = positionFilter.value;

    const filtered = allEmployees.filter(e => {
        const matchSearch = (e.full_name || '').toLowerCase().includes(search) ||
                            (e.username  || '').toLowerCase().includes(search) ||
                            (e.employee_code || '').toLowerCase().includes(search) ||
                            (e.phone     || '').toLowerCase().includes(search);
        const matchDept   = !dept || e.department === dept;
        const matchPos    = !pos  || e.position   === pos;
        return matchSearch && matchDept && matchPos;
    });

    totalPages = Math.max(1, Math.ceil(filtered.length / perPage));
    if (currentPage > totalPages) currentPage = totalPages;

    renderTable(filtered);
    renderPagination(filtered.length);
}

function renderTable(data) {
    const start = (currentPage - 1) * perPage;
    const rows  = data.slice(start, start + perPage);

    if (!rows.length) {
        employeeTableBody.innerHTML = `
            <tr><td colspan="10" class="p-8 text-center text-gray-400">
                <iconify-icon icon="mdi:account-search" style="font-size:32px;display:block;margin:0 auto 8px;"></iconify-icon>
                No employees found
            </td></tr>`;
        return;
    }

    employeeTableBody.innerHTML = rows.map(e => {

        return `
        <tr class="hover:bg-indigo-50 transition-colors">
            <td class="px-4 py-3 text-xs font-mono text-gray-600">${e.employee_code || '-'}</td>
            <td class="px-4 py-3 font-medium text-gray-900">${e.full_name}</td>
            <td class="px-4 py-3 text-xs font-mono text-gray-500">@${e.username}</td>
            <td class="px-4 py-3 text-xs text-gray-600">${e.phone || '-'}</td>
            <td class="px-4 py-3 text-gray-700">${e.position}</td>
            <td class="px-4 py-3 text-gray-700">${e.department}</td>
            <td class="px-4 py-3 text-xs text-gray-500">${e.date_hired ?? '-'}</td>
            <td class="px-4 py-3">${statusBadge(e.status_id)}</td>
            <td class="px-4 py-3">
                <div class="flex gap-2 justify-center">
                    <button type="button" class="js-edit-employee bg-blue-500 hover:bg-blue-600 text-white px-3 py-1.5 rounded text-xs font-semibold flex items-center gap-1 transition-colors" data-id="${encodeDataAttr(e.id)}">
                        <iconify-icon icon="mdi:pencil" style="font-size:14px;"></iconify-icon> Edit
                    </button>
                    <button type="button" class="js-delete-employee bg-red-500 hover:bg-red-600 text-white px-3 py-1.5 rounded text-xs font-semibold flex items-center gap-1 transition-colors" data-id="${encodeDataAttr(e.id)}" data-name="${encodeDataAttr(e.full_name || '')}">
                        <iconify-icon icon="mdi:delete" style="font-size:14px;"></iconify-icon> Delete
                    </button>
                </div>
            </td>
        </tr>`;
    }).join('');
}

function renderPagination(total) {
    if (totalPages <= 1) { paginationContainer.innerHTML = ''; return; }

    let pages = '';
    for (let i = 1; i <= totalPages; i++) {
        pages += `<button onclick="goToPage(${i})"
            class="px-3 py-1 rounded text-xs font-medium border transition-colors ${
                i === currentPage
                    ? 'bg-indigo-600 text-white border-indigo-600'
                    : 'border-gray-300 text-gray-600 hover:bg-gray-50'
            }">${i}</button>`;
    }

    paginationContainer.innerHTML = `
        <div class="flex justify-between items-center">
            <span class="text-sm text-gray-500">
                Showing ${(currentPage-1)*perPage+1}–${Math.min(currentPage*perPage, total)} of ${total}
            </span>
            <div class="flex gap-1 flex-wrap">
                <button onclick="goToPage(${currentPage-1})" ${currentPage===1?'disabled':''
                    } class="px-3 py-1 rounded text-xs font-medium border border-gray-300 text-gray-600 hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed transition-colors">
                    &lsaquo; Prev
                </button>
                ${pages}
                <button onclick="goToPage(${currentPage+1})" ${currentPage===totalPages?'disabled':''
                    } class="px-3 py-1 rounded text-xs font-medium border border-gray-300 text-gray-600 hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed transition-colors">
                    Next &rsaquo;
                </button>
            </div>
        </div>`;
}

function goToPage(p) {
    currentPage = Math.min(Math.max(p, 1), totalPages);
    applyFilters();
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

/* ── Modals ─────────────────────────────────── */
function resetPhotoUI() {
    photoBase64 = null;
    photoPreview.src = DEFAULT_AVATAR;
    removePhotoBtn.classList.add('hidden');
    photoInput.value = '';
    photoFileName.classList.add('hidden');
    photoError.classList.add('hidden');
    photoHint.classList.remove('hidden');
}

function toDateInputValue(value) {
    if (!value) return '';
    const normalized = String(value).slice(0, 10);
    return /^\d{4}-\d{2}-\d{2}$/.test(normalized) ? normalized : '';
}

function normalizeGender(value) {
    const v = String(value ?? '').trim().toLowerCase();
    if (v === 'male' || v === 'm') return 'male';
    if (v === 'female' || v === 'f') return 'female';
    return '';
}

function formatEmployeeCode(value) {
    const n = Number(value);
    if (!Number.isInteger(n) || n <= 0) return '';
    return `EMP${String(n).padStart(5, '0')}`;
}

function normalizeEmployeeRecord(raw) {
    const e = raw || {};
    const firstName = e.first_name ?? e.firstName ?? e.firstname ?? '';
    const lastName  = e.last_name ?? e.lastName ?? e.lastname ?? '';
    const fullName  = e.full_name ?? e.fullName ?? `${firstName} ${lastName}`.trim();

    return {
        id: e.id ?? '',
        employee_code: e.employee_id ?? formatEmployeeCode(e.id),
        username: e.username ?? e.user_name ?? '',
        first_name: firstName,
        last_name: lastName,
        full_name: fullName,
        gender: normalizeGender(e.gender),
        email: e.email ?? '',
        phone: e.phone ?? e.phone_number ?? e.phoneNumber ?? '',
        address: e.address ?? '',
        dob: e.dob ?? e.date_of_birth ?? e.birth_date ?? '',
        position: e.position ?? '',
        department: e.department ?? '',
        date_hired: e.date_hired ?? e.hire_date ?? '',
        status_id: e.status_id ?? e.status ?? 1,
        user_id: e.user_id ?? e.userId ?? '',
        photo: e.photo ?? e.avatar ?? null,
        uuid: e.uuid ?? '',
    };
}

function fillEmployeeForm(rawEmployee) {
    const e = normalizeEmployeeRecord(rawEmployee);

    employeeIdInput.value   = e.id ?? '';
    employeeCodeInput.value = e.employee_code ?? '';
    usernameInput.value     = e.username ?? '';
    firstNameInput.value    = e.first_name ?? '';
    lastNameInput.value     = e.last_name ?? '';
    genderInput.value       = e.gender ?? '';
    emailInput.value        = e.email ?? '';
    phoneInput.value        = e.phone ?? '';
    addressInput.value      = e.address ?? '';
    dobInput.value          = toDateInputValue(e.dob);
    positionInput.value     = e.position ?? '';
    departmentInput.value   = e.department ?? '';
    dateHiredInput.value    = toDateInputValue(e.date_hired);
    statusIdInput.value     = String(e.status_id ?? 1);
    userIdInput.value       = e.user_id ?? '';

    resetPhotoUI();
    if (e.photo) {
        photoBase64 = e.photo;
        photoPreview.src = e.photo;
        removePhotoBtn.classList.remove('hidden');
        photoHint.classList.add('hidden');
        photoFileName.textContent = 'Existing photo';
        photoFileName.classList.remove('hidden');
    }
}

function openCreateModal() {
    employeeForm.reset();
    employeeIdInput.value = '';
    employeeCodeInput.value = '';
    document.getElementById('modalTitle').textContent = 'Add New Employee';
    document.getElementById('submitButtonText').textContent = 'Save Employee';
    resetPhotoUI();
    employeeModal.classList.remove('hidden');
}

async function openEditModal(id) {
    const cached = allEmployees.find(emp => String(emp.id) === String(id));
    if (cached) {
        fillEmployeeForm(cached);
    } else {
        employeeForm.reset();
        employeeIdInput.value = String(id);
        employeeCodeInput.value = '';
        resetPhotoUI();
    }

    try {
        const res = await fetch(`/api/employees/${id}`);
        const json = await res.json();
        if (res.ok && json.success) {
            const apiEmployee = json.data ?? json.employee ?? null;
            if (apiEmployee) {
                fillEmployeeForm(apiEmployee);
            }
        } else if (!cached) {
            showToast('Error', json.message || 'Failed to load employee details.', 'error');
        }
    } catch (err) {
        console.error(err);
        if (!cached) {
            showToast('Error', 'Network error while loading employee details.', 'error');
        }
    }

    document.getElementById('modalTitle').textContent = 'Edit Employee';
    document.getElementById('submitButtonText').textContent = 'Update Employee';
    employeeModal.classList.remove('hidden');
}

function closeModal() {
    employeeModal.classList.add('hidden');
    resetPhotoUI();
}

function openDeleteModal(id, name) {
    deleteEmpId = id;
    deleteEmpName.textContent = name;
    deleteModal.classList.remove('hidden');
}

function closeDeleteModal() {
    deleteEmpId = null;
    deleteModal.classList.add('hidden');
}

/* ── Create / Update ────────────────────────── */
employeeForm.addEventListener('submit', async e => {
    e.preventDefault();
    const id = employeeIdInput.value;

    const payload = {
        user_id:    userIdInput.value   ? Number(userIdInput.value) : null,
        username:   usernameInput.value.trim(),
        first_name: firstNameInput.value.trim(),
        last_name:  lastNameInput.value.trim(),
        full_name:  (firstNameInput.value.trim() + ' ' + lastNameInput.value.trim()).trim(),
        gender:     genderInput.value,
        email:      emailInput.value.trim(),
        phone:      phoneInput.value.trim(),
        address:    addressInput.value.trim(),
        dob:        dobInput.value,
        position:   positionInput.value.trim(),
        department: departmentInput.value.trim(),
        date_hired: dateHiredInput.value,
        status_id:  Number(statusIdInput.value),
    };

    const url    = id ? `/api/employees/${id}` : '/api/employees';
    const method = id ? 'PUT' : 'POST';

    const btn = employeeForm.querySelector('[type="submit"]');
    btn.disabled = true;

    try {
        const fd = new FormData();
        Object.entries(payload).forEach(([key, val]) => {
            if (val !== null && val !== undefined) fd.append(key, val);
        });
        if (photoBase64 && !photoBase64.startsWith('http')) {
            const photoRes = await fetch(photoBase64);
            const blob = await photoRes.blob();
            fd.append('photo', blob, 'photo.jpg');
        }

        const res  = await fetch(url, {
            method,
            body: fd,
        });
        const json = await res.json();

        if (json.success) {
            showToast('Success', json.message || (id ? 'Employee updated.' : 'Employee added.'));
            closeModal();
            loadEmployees();
        } else {
            showToast('Error', json.message || 'Operation failed.', 'error');
        }
    } catch (err) {
        showToast('Error', 'Network or server error.', 'error');
        console.error(err);
    } finally {
        btn.disabled = false;
    }
});

/* ── Delete ─────────────────────────────────── */
async function confirmDelete() {
    if (!deleteEmpId) return;
    try {
        const res  = await fetch(`/api/employees/${deleteEmpId}`, { method: 'DELETE' });
        const json = await res.json();

        if (json.success) {
            showToast('Deleted', 'Employee removed successfully.');
            closeDeleteModal();
            loadEmployees();
        } else {
            showToast('Error', json.message || 'Delete failed.', 'error');
        }
    } catch (err) {
        showToast('Error', 'Network or server error.', 'error');
        console.error(err);
    }
}

/* ── Close modals on backdrop click ─────────── */
employeeModal.addEventListener('click', e => { if (e.target === employeeModal) closeModal(); });
deleteModal.addEventListener('click',   e => { if (e.target === deleteModal)   closeDeleteModal(); });

/* ── Events ─────────────────────────────────── */
employeeTableBody.addEventListener('click', e => {
    const editButton = e.target.closest('.js-edit-employee');
    if (editButton) {
        openEditModal(decodeDataAttr(editButton.dataset.id));
        return;
    }

    const deleteButton = e.target.closest('.js-delete-employee');
    if (deleteButton) {
        openDeleteModal(
            decodeDataAttr(deleteButton.dataset.id),
            decodeDataAttr(deleteButton.dataset.name)
        );
    }
});

searchInput.addEventListener('input', () => { currentPage = 1; applyFilters(); });
departmentFilter.addEventListener('change', () => { currentPage = 1; applyFilters(); });
positionFilter.addEventListener('change',   () => { currentPage = 1; applyFilters(); });

/* ── Init ────────────────────────────────────── */
loadEmployees();

const actionFromQuery = new URLSearchParams(window.location.search).get('action');
if ((actionFromQuery || '').toLowerCase() === 'add') {
    openCreateModal();
}
</script>
</body>
</html>

