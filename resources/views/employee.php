
<div class="w-full h-full"> 
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
    class="hidden fixed inset-0 bg-black bg-opacity-30 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl shadow-xl shadow-indigo-100 w-full max-w-3xl max-h-[90vh] overflow-y-auto border border-gray-100">
 
        <!-- Header -->
        <div class="sticky top-0 bg-gradient-to-r from-indigo-50 to-sky-50 border-b border-indigo-100 px-6 py-4 flex items-center justify-between rounded-t-2xl z-10">
            <h2 class="text-sm font-semibold text-gray-900" id="modalTitle">Add New Employee</h2>
            <button onclick="closeModal()" class="w-8 h-8 flex items-center justify-center rounded-lg border border-gray-200 bg-red-500 text-white hover:bg-red-600 transition-colors">
                <iconify-icon icon="mdi:close" style="font-size:18px;">x</iconify-icon>
            </button>
        </div>
 
        <form id="employeeForm" class="p-6 space-y-6">
            <input type="hidden" id="employeeId">   
 
            <!-- Personal Information -->
            <div>
                <h3 class="flex items-center gap-2 text-[10.5px] font-semibold uppercase tracking-widest text-indigo-500 mb-3 pb-2 border-b border-indigo-100">
                    <iconify-icon icon="mdi:account" style="font-size:15px;"></iconify-icon>
                    Personal Information
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
 
                    <!-- Photo Upload -->
                    <div class="md:col-span-3">
                        <label class="block text-xs font-medium text-gray-500 mb-2">Profile Photo</label>
                        <div class="flex items-center gap-4 px-4 py-3 bg-gradient-to-r from-indigo-50 to-sky-50 rounded-xl border border-indigo-100">
                            <div class="relative flex-shrink-0">
                                <img id="photoPreview"
                                    src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='96' height='96' viewBox='0 0 96 96'%3E%3Ccircle cx='48' cy='48' r='48' fill='%23e0e7ff'/%3E%3Ccircle cx='48' cy='38' r='17' fill='%23a5b4fc'/%3E%3Cellipse cx='48' cy='84' rx='26' ry='22' fill='%23a5b4fc'/%3E%3C/svg%3E"
                                    class="w-12 h-12 rounded-full object-cover border-2 border-indigo-200">
                                <button type="button" id="removePhotoBtn" onclick="removePhoto()"
                                    class="hidden absolute -top-1 -right-1 w-5 h-5 rounded-full bg-red-500 text-white text-xs items-center justify-center border-2 border-white hover:bg-red-600 transition-colors"
                                    title="Remove photo">&#x2715;</button>
                            </div>
                            <div class="flex-1">
                                <p id="photoFileName" class="hidden text-xs font-medium text-indigo-600 mb-0.5"></p>
                                <p class="text-xs font-medium text-gray-600">Profile photo</p>
                                <p id="photoHint" class="text-xs text-gray-400">JPG, PNG, WEBP — max 2 MB</p>
                                <p id="photoError" class="hidden text-xs text-red-500 mt-1"></p>
                            </div>
                            <label for="photo"
                                class="cursor-pointer inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-indigo-600 bg-white border border-indigo-200 rounded-lg hover:bg-indigo-50 hover:border-indigo-400 transition-colors">
                                <iconify-icon icon="mdi:upload" style="font-size:14px;"></iconify-icon>
                                Upload Photo
                            </label>
                            <input type="file" id="photo" accept="image/jpeg,image/png,image/webp" class="hidden">
                        </div>
                    </div>
 
                    <!-- First Name -->
                    <div class="flex flex-col gap-1">
                        <label class="text-xs font-medium text-gray-500">First Name <span class="text-red-400">*</span></label>
                        <input type="text" id="first_name" required placeholder="John"
                            class="w-full h-9 px-3 text-sm text-gray-900 bg-gray-50 border border-gray-200 rounded-lg hover:border-indigo-200 focus:outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all placeholder:text-gray-300">
                    </div>
 
                    <!-- Last Name -->
                    <div class="flex flex-col gap-1">
                        <label class="text-xs font-medium text-gray-500">Last Name <span class="text-red-400">*</span></label>
                        <input type="text" id="last_name" required placeholder="Doe"
                            class="w-full h-9 px-3 text-sm text-gray-900 bg-gray-50 border border-gray-200 rounded-lg hover:border-indigo-200 focus:outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all placeholder:text-gray-300">
                    </div>
 
                    <!-- Gender -->
                    <div class="flex flex-col gap-1">
                        <label class="text-xs font-medium text-gray-500">Gender <span class="text-red-400">*</span></label>
                        <div class="relative">
                            <iconify-icon icon="mdi:gender-male-female" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#a5b4fc;font-size:15px;pointer-events:none;"></iconify-icon>
                            <select id="gender" required
                                class="w-full h-9 pl-8 pr-3 text-sm text-gray-900 bg-gray-50 border border-gray-200 rounded-lg hover:border-indigo-200 focus:outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all appearance-none cursor-pointer">
                                <option value="">Select Gender</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                            </select>
                        </div>
                    </div>
 
                    <!-- Username -->
                    <div class="flex flex-col gap-1">
                        <label class="text-xs font-medium text-gray-500">Username <span class="text-red-400">*</span></label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-indigo-300 text-sm font-medium">@</span>
                            <input type="text" id="username" required placeholder="johndoe"
                                class="w-full h-9 pl-7 pr-3 text-sm text-gray-900 bg-gray-50 border border-gray-200 rounded-lg hover:border-indigo-200 focus:outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all placeholder:text-gray-300">
                        </div>
                    </div>
 
                    <!-- Employee ID -->
                    <div class="flex flex-col gap-1">
                        <label class="text-xs font-medium text-gray-500">Employee ID</label>
                        <div class="relative">
                            <iconify-icon icon="mdi:badge-account-outline" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#c7d2fe;font-size:15px;pointer-events:none;"></iconify-icon>
                            <input type="text" id="employee_code" readonly placeholder="Auto-generated after save"
                                class="w-full h-9 pl-8 pr-3 text-sm text-indigo-400 bg-indigo-50 border border-indigo-100 rounded-lg cursor-default placeholder:text-indigo-300">
                        </div>
                    </div>
 
                    <!-- Email -->
                    <div class="flex flex-col gap-1">
                        <label class="text-xs font-medium text-gray-500">Email <span class="text-red-400">*</span></label>
                        <div class="relative">
                            <iconify-icon icon="mdi:email-outline" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#a5b4fc;font-size:15px;pointer-events:none;"></iconify-icon>
                            <input type="email" id="email" required placeholder="john.doe@example.com"
                                class="w-full h-9 pl-8 pr-3 text-sm text-gray-900 bg-gray-50 border border-gray-200 rounded-lg hover:border-indigo-200 focus:outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all placeholder:text-gray-300">
                        </div>
                    </div>
 
                    <!-- Address -->
                    <div class="md:col-span-2 flex flex-col gap-1">
                        <label class="text-xs font-medium text-gray-500">Address <span class="text-red-400">*</span></label>
                        <div class="relative">
                            <iconify-icon icon="mdi:map-marker-outline" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#a5b4fc;font-size:15px;pointer-events:none;"></iconify-icon>
                            <input type="text" id="address" required placeholder="123 Main St, City, State"
                                class="w-full h-9 pl-8 pr-3 text-sm text-gray-900 bg-gray-50 border border-gray-200 rounded-lg hover:border-indigo-200 focus:outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all placeholder:text-gray-300">
                        </div>
                    </div>
 
                    <!-- Date of Birth -->
                    <div class="flex flex-col gap-1">
                        <label class="text-xs font-medium text-gray-500">Date of Birth <span class="text-red-400">*</span></label>
                        <div class="relative">
                            <iconify-icon icon="mdi:calendar-outline" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#a5b4fc;font-size:15px;pointer-events:none;"></iconify-icon>
                            <input type="date" id="dob" required
                                class="w-full h-9 pl-8 pr-3 text-sm text-gray-900 bg-gray-50 border border-gray-200 rounded-lg hover:border-indigo-200 focus:outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
                        </div>
                    </div>

                     <!-- Phone -->
                    <div class="flex flex-col gap-1">
                        <label class="text-xs font-medium text-gray-500">Phone Number</label>
                        <div class="relative">
                            <iconify-icon icon="mdi:phone-outline" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#a5b4fc;font-size:15px;pointer-events:none;"></iconify-icon>
                            <input type="text" id="phone" placeholder="012 345 678"
                                class="w-full h-9 pl-8 pr-3 text-sm text-gray-900 bg-gray-50 border border-gray-200 rounded-lg hover:border-indigo-200 focus:outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all placeholder:text-gray-300">
                        </div>
                    </div>
 
                    <!-- Password -->
                    <div class="flex flex-col gap-1">
                        <label class="text-xs font-medium text-gray-500">Password</label>
                        <div class="relative">
                            <iconify-icon icon="mdi:lock-outline" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#a5b4fc;font-size:15px;pointer-events:none;"></iconify-icon>
                            <input type="password" id="password" placeholder="Required for new employee login"
                                class="w-full h-9 pl-8 pr-3 text-sm text-gray-900 bg-gray-50 border border-gray-200 rounded-lg hover:border-indigo-200 focus:outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all placeholder:text-gray-300">
                        </div>
                        <p class="text-xs text-gray-400" id="passwordHelp">Set the mobile login password for this employee.</p>
                    </div>
 
                </div>
            </div>
 
            <!-- Divider -->
            <div class="h-px bg-gray-100"></div>
 
            <!-- Job Information -->
            <div>
                <h3 class="flex items-center gap-2 text-[10.5px] font-semibold uppercase tracking-widest text-indigo-500 mb-3 pb-2 border-b border-indigo-100">
                    <iconify-icon icon="mdi:briefcase-outline" style="font-size:15px;"></iconify-icon>
                    Job Information
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
 
                    <!-- Position -->
                    <div class="flex flex-col gap-1">
                        <label class="text-xs font-medium text-gray-500">Position <span class="text-red-400">*</span></label>
                        <div class="relative">
                            <iconify-icon icon="mdi:tag-outline" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#a5b4fc;font-size:15px;pointer-events:none;"></iconify-icon>
                            <input type="text" id="position" required placeholder="Software Engineer"
                                class="w-full h-9 pl-8 pr-3 text-sm text-gray-900 bg-gray-50 border border-gray-200 rounded-lg hover:border-indigo-200 focus:outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all placeholder:text-gray-300">
                        </div>
                    </div>
 
                    <!-- Department -->
                    <div class="flex flex-col gap-1">
                        <label class="text-xs font-medium text-gray-500">Department <span class="text-red-400">*</span></label>
                        <div class="relative">
                            <iconify-icon icon="mdi:office-building-outline" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#a5b4fc;font-size:15px;pointer-events:none;"></iconify-icon>
                            <input type="text" id="department" required placeholder="Engineering"
                                class="w-full h-9 pl-8 pr-3 text-sm text-gray-900 bg-gray-50 border border-gray-200 rounded-lg hover:border-indigo-200 focus:outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all placeholder:text-gray-300">
                        </div>
                    </div>
 
                    <!-- Date Hired -->
                    <div class="flex flex-col gap-1">
                        <label class="text-xs font-medium text-gray-500">Date Hired <span class="text-red-400">*</span></label>
                        <div class="relative">
                            <iconify-icon icon="mdi:calendar-check-outline" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#a5b4fc;font-size:15px;pointer-events:none;"></iconify-icon>
                            <input type="date" id="date_hired" required
                                class="w-full h-9 pl-8 pr-3 text-sm text-gray-900 bg-gray-50 border border-gray-200 rounded-lg hover:border-indigo-200 focus:outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
                        </div>
                    </div>
 
                    <!-- Status -->
                    <div class="flex flex-col gap-1">
                        <label class="text-xs font-medium text-gray-500">Status <span class="text-red-400">*</span></label>
                        <div class="relative">
                            <span id="statusDot" class="absolute left-3 top-1/2 -translate-y-1/2 w-2 h-2 rounded-full bg-gray-300 pointer-events-none z-10"></span>
                            <select id="status_id" required onchange="updateStatusStyle(this)"
                                class="w-full h-9 pl-7 pr-3 text-sm font-medium bg-gray-50 border border-gray-200 rounded-lg hover:border-indigo-200 focus:outline-none focus:ring-2 focus:ring-indigo-100 transition-all appearance-none cursor-pointer text-gray-500">
                                <option value="">Select Status</option>
                                <option value="1">Active</option>
                                <option value="2">Inactive</option>
                                <option value="3">On Leave</option>
                            </select>
                        </div>
                    </div>
 
                </div>
            </div>
 
            <!-- Footer -->
            <div class="flex justify-end gap-2 pt-4 border-t border-gray-100">
                <button type="button" onclick="closeModal()"
                    class="h-9 px-5 text-sm font-medium text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
                <button type="submit"
                    class="h-9 px-5 text-sm font-semibold text-white bg-gradient-to-r from-indigo-600 to-sky-500 rounded-lg shadow shadow-indigo-200 hover:opacity-90 active:scale-95 transition-all flex items-center gap-2">
                    <iconify-icon icon="mdi:content-save-outline" style="font-size:16px;"></iconify-icon>
                    <span id="submitButtonText">Save Employee</span>
                </button>
            </div>
 
        </form>
    </div>
</div>
 
<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="hidden fixed inset-0 bg-black bg-opacity-30 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md border border-gray-100">
        <div class="p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-11 h-11 rounded-full bg-red-50 border border-red-100 flex items-center justify-center flex-shrink-0">
                    <iconify-icon icon="mdi:alert-outline" style="font-size:22px;color:#ef4444;"></iconify-icon>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-900">Delete Employee</h3>
                    <p class="text-xs text-gray-400">This action cannot be undone</p>
                </div>
            </div>
            <p class="text-sm text-gray-600 mb-6">Are you sure you want to delete <strong id="deleteEmployeeName" class="text-gray-900"></strong>?</p>
            <div class="flex justify-end gap-2">
                <button onclick="closeDeleteModal()"
                    class="h-9 px-4 text-sm font-medium text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
                <button onclick="confirmDelete()"
                    class="h-9 px-4 text-sm font-semibold text-white bg-red-500 rounded-lg hover:bg-red-600 active:scale-95 transition-all flex items-center gap-2">
                    <iconify-icon icon="mdi:delete-outline" style="font-size:16px;"></iconify-icon>
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
const passwordInput    = document.getElementById('password');
const passwordHelp     = document.getElementById('passwordHelp');

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
                    <button type="button" class="js-edit-employee inline-flex items-center gap-1 px-3 py-1.5 bg-gray-100 text-gray-700 rounded-lg hover:bg-indigo-100 hover:text-indigo-700 text-xs font-semibold transition-colors" data-id="${encodeDataAttr(e.id)}">
                        <iconify-icon icon="mdi:pencil" style="font-size:14px;"></iconify-icon> Edit
                    </button>
                    <button type="button" class="js-delete-employee inline-flex items-center gap-1 px-3 py-1.5 bg-gray-100 text-gray-700 rounded-lg hover:bg-red-100 hover:text-red-700 text-xs font-semibold transition-colors" data-id="${encodeDataAttr(e.id)}" data-name="${encodeDataAttr(e.full_name || '')}">
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
    return String(n);
}

function normalizeEmployeeRecord(raw) {
    const e = raw || {};
    const firstName = e.first_name ?? e.firstName ?? e.firstname ?? '';
    const lastName  = e.last_name ?? e.lastName ?? e.lastname ?? '';
    const fullName  = e.full_name ?? e.fullName ?? `${firstName} ${lastName}`.trim();

    return {
        id: e.id ?? '',
        employee_code: formatEmployeeCode(e.id),
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
    passwordInput.value     = '';

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

function setPasswordMode(isCreate) {
    passwordInput.required = isCreate;
    passwordInput.placeholder = isCreate
        ? 'Required for new employee login'
        : 'Leave blank to keep current password';
    passwordHelp.textContent = isCreate
        ? 'Set the mobile login password for this employee.'
        : 'Leave blank if you do not want to change the employee password.';
}

function openCreateModal() {
    // 1. Reset the form via the browser API
    employeeForm.reset();

    // 2. Explicitly clear every specific field to ensure no lingering data
    const fields = [
        'employeeId', 'employee_code', 'username', 'first_name', 
        'last_name', 'gender', 'email', 'phone', 'address', 
        'dob', 'position', 'department', 'date_hired', 'status_id', 'password'
    ];
    
    fields.forEach(id => {
        const el = document.getElementById(id);
        if (el) {
            if (el.tagName === 'SELECT') {
                el.value = '';
            } else {
                el.value = '';
            }
        }
    });

    // 3. Reset UI components
    setPasswordMode(true);
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
    setPasswordMode(false);
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
        username:   usernameInput.value.trim(),
        password:   passwordInput.value,
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
<!-- </body> -->
<!-- </html> -->

