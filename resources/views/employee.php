   <body class="bg-gradient-to-br from-slate-50 to-slate-100 min-h-screen">
    <div class="p-2">
        <!-- Header with Filters and Add Button -->
        <div class="bg-white rounded-lg shadow-sm p-4 mb-4">
            <div class="flex flex-col gap-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <iconify-icon icon="mdi:users-group" style="font-size: 24px; color: #4f46e5;"></iconify-icon>
                        <h1 class="text-lg font-bold text-gray-900">Employee Directory</h1>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-semibold bg-indigo-100 text-indigo-700 px-3 py-1 rounded-full"
                            id="totalCount">0 Staff</span>
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
                            style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: #9ca3af; font-size: 18px;">
                        </iconify-icon>
                        <input type="text" id="searchInput" placeholder="Search by name or username..."
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

        <!-- Table -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gradient-to-b from-slate-900 via-slate-800 to-slate-900 shadow-lg text-white">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold">Name</th>
                            <th class="px-4 py-3 text-left font-semibold">Username</th>
                            <th class="px-4 py-3 text-left font-semibold">Position</th>
                            <th class="px-4 py-3 text-left font-semibold">Department</th>
                            <th class="px-4 py-3 text-left font-semibold">Hired</th>
                            <th class="px-4 py-3 text-center font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="employeeTableBody" class="divide-y divide-gray-100">
                        <tr>
                            <td colspan="6" class="px-4 py-6 text-center text-gray-400">
                                <div class="flex items-center justify-center gap-2">
                                    <iconify-icon icon="mdi:loading" style="font-size: 20px;" class="animate-spin">
                                    </iconify-icon>
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

    <!-- Create/Edit Modal -->
    <div id="employeeModal"
        class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-3xl max-h-[90vh] overflow-y-auto">
            <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between">
                <h2 class="text-xl font-bold text-gray-900" id="modalTitle">Add New Employee</h2>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <iconify-icon icon="mdi:close" style="font-size: 24px;"></iconify-icon>
                </button>
            </div>

            <form id="employeeForm" class="p-6 space-y-5">
                <input type="hidden" id="employeeId">

                <!-- Personal Information Section -->
                <div>
                    <h3 class="text-sm font-bold text-gray-700 mb-3 flex items-center gap-2">
                        <iconify-icon icon="mdi:account" style="font-size: 18px; color: #4f46e5;"></iconify-icon>
                        Personal Information
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Username *</label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">@</span>
                                <input type="text" id="username" required
                                    class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                    placeholder="johndoe">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">User ID (Optional)</label>
                            <input type="number" id="user_id"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                placeholder="Leave empty if none">
                        </div>
                    </div>
                </div>

                <!-- Job Information Section -->
                <div>
                    <h3 class="text-sm font-bold text-gray-700 mb-3 flex items-center gap-2">
                        <iconify-icon icon="mdi:briefcase" style="font-size: 18px; color: #4f46e5;"></iconify-icon>
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
                        class="px-5 py-2.5 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors font-medium">Cancel</button>
                    <button type="submit"
                        class="px-5 py-2.5 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors flex items-center gap-2 font-medium">
                        <iconify-icon icon="mdi:content-save"></iconify-icon>
                        <span id="submitButtonText">Save Employee</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md">
            <div class="p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
                        <iconify-icon icon="mdi:alert" style="font-size: 24px; color: #dc2626;"></iconify-icon>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Delete Employee</h3>
                        <p class="text-sm text-gray-600">This action cannot be undone</p>
                    </div>
                </div>

                <p class="text-gray-700 mb-6">Are you sure you want to delete <strong id="deleteEmployeeName"></strong>?
                </p>

                <div class="flex justify-end gap-2">
                    <button onclick="closeDeleteModal()"
                        class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors font-medium">Cancel</button>
                    <button onclick="confirmDelete()"
                        class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors flex items-center gap-2 font-medium">
                        <iconify-icon icon="mdi:delete"></iconify-icon>
                        Delete
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <div id="toast" class="hidden fixed top-4 right-4 z-50 max-w-sm">
        <div class="bg-white rounded-lg shadow-lg border-l-4 p-4 flex items-center gap-3">
            <iconify-icon id="toastIcon" style="font-size: 24px;"></iconify-icon>
            <div>
                <p class="font-semibold text-gray-900" id="toastTitle"></p>
                <p class="text-sm text-gray-600" id="toastMessage"></p>
            </div>
        </div>
    </div>

    <script>
        /* =======================
        DOM ELEMENTS
        ======================= */
        const employeeForm = document.getElementById('employeeForm');
        const employeeId = document.getElementById('employeeId');
        const username = document.getElementById('username');
        const first_name = document.getElementById('first_name');
        const last_name = document.getElementById('last_name');
        const position = document.getElementById('position');
        const department = document.getElementById('department');
        const date_hired = document.getElementById('date_hired');
        const status_id = document.getElementById('status_id');
        const user_id = document.getElementById('user_id');

        const searchInput = document.getElementById('searchInput');
        const employeeTableBody = document.getElementById('employeeTableBody');
        const totalCount = document.getElementById('totalCount');
        const paginationContainer = document.getElementById('paginationContainer');

        const employeeModal = document.getElementById('employeeModal');
        const deleteModal = document.getElementById('deleteModal');
        const deleteEmployeeName = document.getElementById('deleteEmployeeName');
        let deleteEmployeeId = null;

        /* =======================
        STATE
        ======================= */
        let allEmployees = [];
        let currentPage = 1;
        const perPage = 18;
        let totalPages = 1;

        /* =======================
        TOAST
        ======================= */
        function showToast(title, message, type = 'success') {
            const toast = document.getElementById('toast');
            const icon = document.getElementById('toastIcon');
            const toastTitle = document.getElementById('toastTitle');
            const toastMessage = document.getElementById('toastMessage');
            const box = toast.querySelector('div');

            icon.setAttribute('icon', type === 'success' ? 'mdi:check-circle' : 'mdi:alert-circle');
            icon.style.color = type === 'success' ? '#10b981' : '#ef4444';

            box.className = `bg-white rounded-lg shadow-lg border-l-4 p-4 flex items-center gap-3 ${
                type === 'success' ? 'border-green-500' : 'border-red-500'
            }`;

            toastTitle.textContent = title;
            toastMessage.textContent = message;
            toast.classList.remove('hidden');

            setTimeout(() => toast.classList.add('hidden'), 3000);
        }

        /* =======================
        LOAD EMPLOYEES
        ======================= */
        async function loadEmployees() {
            try {
                const res = await fetch('/api/employees');
                const json = await res.json();

                if (!json.success) {
                    showToast('Error', 'Failed to load employees', 'error');
                    return;
                }

                allEmployees = json.data;
                totalCount.textContent = `${allEmployees.length} Staff`;
                applyFilters();
            } catch (err) {
                showToast('Error', 'Failed to load employees', 'error');
                console.error(err);
            }
        }

        /* =======================
        FILTER & PAGINATION
        ======================= */
        function applyFilters() {
            const search = searchInput.value.toLowerCase();

            const filtered = allEmployees.filter(e =>
                (e.full_name || '').toLowerCase().includes(search) ||
                (e.username || '').toLowerCase().includes(search)
            );

            totalPages = Math.max(1, Math.ceil(filtered.length / perPage));
            renderTable(filtered);
            renderPagination(filtered.length);
        }

        function renderTable(data) {
            const start = (currentPage - 1) * perPage;
            const rows = data.slice(start, start + perPage);

            if (!rows.length) {
                employeeTableBody.innerHTML = `<tr><td colspan="6" class="p-6 text-center text-gray-400">No data</td></tr>`;
                return;
            }

            employeeTableBody.innerHTML = rows.map(e => `
                <tr class="hover:bg-indigo-50">
                    <td class="px-4 py-3 font-medium">${e.full_name}</td>
                    <td class="px-4 py-3 text-xs font-mono">@${e.username}</td>
                    <td class="px-4 py-3">${e.position}</td>
                    <td class="px-4 py-3">${e.department}</td>
                    <td class="px-4 py-3 text-xs">${e.date_hired ?? '-'}</td>
                    <td class="px-4 py-3 flex gap-2 justify-center">
                        <button onclick="openEditModal(${e.id})" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1.5 rounded text-xs font-semibold flex items-center gap-1 transition-colors">
                            <iconify-icon icon="mdi:pencil" style="font-size: 14px;"></iconify-icon>
                            edit
                        </button>
                        <button onclick="openDeleteModal(${e.id}, '${e.full_name}')" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1.5 rounded text-xs font-semibold flex items-center gap-1 transition-colors">
                            <iconify-icon icon="mdi:delete" style="font-size: 14px;"></iconify-icon>
                            delete
                        </button>
                    </td>
                </tr>
            `).join('');
        }

        function renderPagination(total) {
            paginationContainer.innerHTML = `
                <div class="flex justify-between items-center">
                    <span class="text-sm">Page ${currentPage} of ${totalPages}</span>
                    <div class="flex gap-2">
                        <button ${currentPage === 1 ? 'disabled' : ''} onclick="goToPage(${currentPage - 1})">Prev</button>
                        <button ${currentPage === totalPages ? 'disabled' : ''} onclick="goToPage(${currentPage + 1})">Next</button>
                    </div>
                </div>
            `;
        }

        function goToPage(p) {
            currentPage = Math.min(Math.max(p, 1), totalPages);
            applyFilters();
        }

        /* =======================
        MODALS
        ======================= */
        function openCreateModal() {
            employeeForm.reset();
            employeeId.value = '';
            employeeModal.classList.remove('hidden');
        }

        function openEditModal(id) {
            const e = allEmployees.find(emp => emp.id == id);
            if (!e) return;

            employeeId.value = e.id;
            username.value = e.username;
            first_name.value = e.first_name;
            last_name.value = e.last_name;
            position.value = e.position;
            department.value = e.department;
            date_hired.value = e.date_hired;
            status_id.value = e.status_id;
            user_id.value = e.user_id ?? '';

            employeeModal.classList.remove('hidden');
        }

        function closeModal() {
            employeeModal.classList.add('hidden');
        }

        function openDeleteModal(id, name) {
            deleteEmployeeId = id;
            deleteEmployeeName.textContent = name;
            deleteModal.classList.remove('hidden');
        }

        function closeDeleteModal() {
            deleteEmployeeId = null;
            deleteModal.classList.add('hidden');
        }

        /* =======================
        CREATE / UPDATE
        ======================= */
        employeeForm.addEventListener('submit', async e => {
            e.preventDefault();

            const id = employeeId.value;
            const payload = {
                user_id: user_id.value ? Number(user_id.value) : null,
                username: username.value.trim(),
                first_name: first_name.value.trim(),
                last_name: last_name.value.trim(),
                full_name: (first_name.value + ' ' + last_name.value).trim(),
                position: position.value.trim(),
                department: department.value.trim(),
                date_hired: date_hired.value,
                status_id: Number(status_id.value)
            };

            const url = id ? `/api/employees/${id}` : '/api/employees';
            const method = id ? 'PUT' : 'POST';

            try {
                const res = await fetch(url, {
                    method,
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });

                const json = await res.json();

                if (json.success) {
                    showToast('Success', json.message);
                    closeModal();
                    loadEmployees(1);
                } else {
                    showToast('Error', json.message, 'error');
                }
            } catch (err) {
                showToast('Error', 'Network or server error', 'error');
                console.error(err);
            }
        });

        /* =======================
        DELETE
        ======================= */
        async function confirmDelete() {
            if (!deleteEmployeeId) return;

            try {
                const res = await fetch(`/api/employees/${deleteEmployeeId}`, { method: 'DELETE' });
                const json = await res.json();

                if (json.success) {
                    showToast('Deleted', 'Employee removed');
                    closeDeleteModal();
                    loadEmployees(1);
                } else {
                    showToast('Error', json.message || 'Delete failed', 'error');
                }
            } catch (err) {
                showToast('Error', 'Network or server error', 'error');
                console.error(err);
            }
        }

        /* =======================
        EVENTS
        ======================= */
        searchInput.addEventListener('input', applyFilters);

        /* =======================
        INITIAL LOAD
        ======================= */
        loadEmployees();
    </script>
</body>