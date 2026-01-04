<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Employee Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.iconify.design/iconify-icon/1.0.7/iconify-icon.min.js"></script>
</head>

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
    // state
    let currentPage = 1;
    let totalPages = 1;
    let allEmployees = []; // full dataset fetched once
    const perPage = 18;
    const departmentSet = new Set();
    const positionSet = new Set();
    let deleteEmployeeId = null;

    // toast
    function showToast(title, message, type = 'success') {
        const toast = document.getElementById('toast');
        const icon = document.getElementById('toastIcon');
        const toastTitle = document.getElementById('toastTitle');
        const toastMessage = document.getElementById('toastMessage');
        const toastContainer = toast.querySelector('div');

        if (type === 'success') {
            icon.setAttribute('icon', 'mdi:check-circle');
            icon.style.color = '#10b981';
            toastContainer.classList.remove('border-red-500', 'border-yellow-500');
            toastContainer.classList.add('border-green-500');
        } else {
            icon.setAttribute('icon', 'mdi:alert-circle');
            icon.style.color = '#ef4444';
            toastContainer.classList.remove('border-green-500', 'border-yellow-500');
            toastContainer.classList.add('border-red-500');
        }

        toastTitle.textContent = title;
        toastMessage.textContent = message;
        toast.classList.remove('hidden');

        setTimeout(() => {
            toast.classList.add('hidden');
        }, 3000);
    }

    // load all employees (large per_page) so client can filter/paginate locally
    async function loadEmployees(page = 1) {
        currentPage = page;
        const largePerPage = 10000; // fetch all rows — adjust if dataset large
        const url = `api/employees/show.php?paging_options[page]=1&paging_options[per_page]=${largePerPage}`;

        try {
            const res = await fetch(url);
            const result = await res.json();

            if (!result || !result.success || !result.data) {
                throw new Error(result?.message || 'Invalid API response');
            }

            // set dataset
            allEmployees = result.data.employees || [];
            // populate dropdowns
            populateFilterOptions();

            // compute pagination for client side
            const total = result.data.total_count || allEmployees.length;
            totalPages = Math.max(1, Math.ceil(total / perPage));
            document.getElementById("totalCount").textContent = total + " Staff";

            // render current page slice
            applyFilters();
        } catch (err) {
            console.error(err);
            document.getElementById("employeeTableBody").innerHTML =
                '<tr><td colspan="6" class="px-4 py-6 text-center text-red-500"><iconify-icon icon="mdi:alert-circle"></iconify-icon> Error loading data</td></tr>';
            showToast('Error', 'Failed to load employees', 'error');
        }
    }

    // Render table - accepts a full array (unpaginated) and displays the current page slice
    function renderTable(employees) {
        const tbody = document.getElementById("employeeTableBody");

        const totalRecords = employees.length;
        if (totalRecords === 0) {
            tbody.innerHTML =
                '<tr><td colspan="6" class="px-4 py-6 text-center text-gray-400">No employees found</td></tr>';
            return;
        }

        // compute slice for pagination
        const start = (currentPage - 1) * perPage;
        const paged = employees.slice(start, start + perPage);

        tbody.innerHTML = paged.map(emp => {
            const safeFullName = (emp.full_name || '').replace(/'/g, "\\'");
            const hired = emp.date_hired ? new Date(emp.date_hired).toLocaleDateString('en-US', {
                month: 'short',
                day: 'numeric',
                year: 'numeric'
            }) : 'N/A';
            return `
                    <tr class="hover:bg-indigo-50 transition-colors">
                        <td class="px-4 py-3 font-medium text-gray-900">${escapeHtml(emp.full_name || '')}</td>
                        <td class="px-4 py-3 text-gray-500 text-xs font-mono">@${escapeHtml(emp.username || '')}</td>
                        <td class="px-4 py-3"><span class="bg-blue-100 text-blue-700 px-2 py-1 rounded text-xs font-semibold">${escapeHtml(emp.position || '')}</span></td>
                        <td class="px-4 py-3"><span class="bg-purple-100 text-purple-700 px-2 py-1 rounded text-xs font-semibold">${escapeHtml(emp.department || '')}</span></td>
                        <td class="px-4 py-3 text-gray-600 text-xs">${hired}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-center gap-2">
                                <button onclick="openEditModal(${emp.id})" class="text-indigo-600 hover:text-indigo-800 transition-colors" title="Edit">
                                    <iconify-icon icon="mdi:pencil" style="font-size: 20px;"></iconify-icon>
                                </button>
                                <button onclick="openDeleteModal(${emp.id}, '${safeFullName}')" class="text-red-600 hover:text-red-800 transition-colors" title="Delete">
                                    <iconify-icon icon="mdi:delete" style="font-size: 20px;"></iconify-icon>
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
        }).join('');
    }

    // escape helper for minimal HTML safety when injecting strings
    function escapeHtml(unsafe) {
        if (!unsafe && unsafe !== 0) return '';
        return String(unsafe)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    // Populate filter options
    function populateFilterOptions() {
        departmentSet.clear();
        positionSet.clear();

        allEmployees.forEach(emp => {
            if (emp.department) departmentSet.add(emp.department);
            if (emp.position) positionSet.add(emp.position);
        });

        const deptSelect = document.getElementById("departmentFilter");
        const posSelect = document.getElementById("positionFilter");

        // reset
        deptSelect.innerHTML = '<option value="">All Departments</option>';
        posSelect.innerHTML = '<option value="">All Positions</option>';

        Array.from(departmentSet).sort().forEach(dept => {
            const opt = document.createElement("option");
            opt.value = dept;
            opt.textContent = dept;
            deptSelect.appendChild(opt);
        });

        Array.from(positionSet).sort().forEach(pos => {
            const opt = document.createElement("option");
            opt.value = pos;
            opt.textContent = pos;
            posSelect.appendChild(opt);
        });
    }

    // Apply filters & search (client-side)
    function applyFilters() {
        const search = document.getElementById("searchInput").value.trim().toLowerCase();
        const dept = document.getElementById("departmentFilter").value;
        const pos = document.getElementById("positionFilter").value;

        const filtered = allEmployees.filter(emp => {
            const full = (emp.full_name || '').toLowerCase();
            const user = (emp.username || '').toLowerCase();
            const matchSearch = !search || full.includes(search) || user.includes(search);
            const matchDept = !dept || emp.department === dept;
            const matchPos = !pos || emp.position === pos;
            return matchSearch && matchDept && matchPos;
        });

        // update totalPages for filtered result
        const total = filtered.length;
        totalPages = Math.max(1, Math.ceil(total / perPage));

        // update UI
        document.getElementById("totalCount").textContent = total + " Staff";
        renderTable(filtered);
        renderPagination(currentPage, totalPages, total);
    }

    // Pagination UI
    function renderPagination(current, total, records) {
        const container = document.getElementById('paginationContainer');
        const from = (current - 1) * perPage + 1;
        const to = Math.min(current * perPage, records);

        container.innerHTML = `
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Showing ${records === 0 ? 0 : from} to ${records === 0 ? 0 : to} of ${records} entries</span>
                    <div class="flex gap-2">
                        <button ${current === 1 ? 'disabled' : ''} onclick="goToPage(${current - 1})"
                            class="px-3 py-1 border rounded ${current === 1 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-50'}">
                            Previous
                        </button>
                        <div class="flex items-center gap-1">
                            <span class="text-sm text-gray-600">Page</span>
                            <strong class="px-2">${current}</strong>
                            <span class="text-sm text-gray-600">of</span>
                            <strong class="px-2">${total}</strong>
                        </div>
                        <button ${current === total ? 'disabled' : ''} onclick="goToPage(${current + 1})"
                            class="px-3 py-1 border rounded ${current === total ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-50'}">
                            Next
                        </button>
                    </div>
                </div>
            `;
    }

    function goToPage(page) {
        if (page < 1) page = 1;
        if (page > totalPages) page = totalPages;
        currentPage = page;
        // Re-apply filters so it repaginates the filtered dataset
        applyFilters();
    }

    // Modal functions
    function openCreateModal() {
        document.getElementById('modalTitle').textContent = 'Add New Employee';
        document.getElementById('submitButtonText').textContent = 'Save Employee';
        document.getElementById('employeeForm').reset();
        document.getElementById('employeeId').value = '';
        document.getElementById('status_id').value = '1';
        document.getElementById('employeeModal').classList.remove('hidden');
    }

    function openEditModal(id) {
        const employee = allEmployees.find(e => Number(e.id) === Number(id));
        if (!employee) return;

        document.getElementById('modalTitle').textContent = 'Edit Employee';
        document.getElementById('submitButtonText').textContent = 'Update Employee';
        document.getElementById('employeeId').value = employee.id;
        document.getElementById('username').value = employee.username || '';
        document.getElementById('first_name').value = employee.first_name || '';
        document.getElementById('last_name').value = employee.last_name || '';
        document.getElementById('user_id').value = employee.user_id || '';
        document.getElementById('position').value = employee.position || '';
        document.getElementById('department').value = employee.department || '';
        // date input expects YYYY-MM-DD
        document.getElementById('date_hired').value = employee.date_hired ? employee.date_hired.split(' ')[0] : '';
        document.getElementById('status_id').value = employee.status_id || 1;
        document.getElementById('employeeModal').classList.remove('hidden');
    }

    function closeModal() {
        document.getElementById('employeeModal').classList.add('hidden');
    }

    function openDeleteModal(id, name) {
        deleteEmployeeId = id;
        document.getElementById('deleteEmployeeName').textContent = name || '';
        document.getElementById('deleteModal').classList.remove('hidden');
    }

    function closeDeleteModal() {
        deleteEmployeeId = null;
        document.getElementById('deleteModal').classList.add('hidden');
    }

    // Form submit (create/update)
    document.getElementById('employeeForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const id = document.getElementById('employeeId').value;
        const data = {
            username: document.getElementById('username').value.trim(),
            first_name: document.getElementById('first_name').value.trim(),
            last_name: document.getElementById('last_name').value.trim(),
            position: document.getElementById('position').value.trim(),
            department: document.getElementById('department').value.trim(),
            date_hired: document.getElementById('date_hired').value,
            status_id: parseInt(document.getElementById('status_id').value, 10)
        };

        const userId = document.getElementById('user_id').value.trim();
        if (userId) data.user_id = parseInt(userId, 10);

        const url = id ? 'api/employees/update.php' : 'api/employees/create.php';
        if (id) data.id = parseInt(id, 10);

        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            });
            const result = await response.json();
            if (result && result.success) {
                showToast('Success', result.message || (id ? 'Employee updated' : 'Employee created'),
                    'success');
                closeModal();
                // reload dataset to reflect changes
                await loadEmployees(1);
            } else {
                showToast('Error', result?.message || 'Operation failed', 'error');
            }
        } catch (err) {
            console.error(err);
            showToast('Error', 'Network error occurred', 'error');
        }
    });

    // Delete employee - FIXED VERSION
    async function confirmDelete() {
        if (!deleteEmployeeId) return;
        try {
            const response = await fetch('/api/employees/delete.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    id: deleteEmployeeId
                })
            });
            const result = await response.json();
            if (result && result.success) {
                showToast('Success', result.message || 'Employee deleted', 'success');
                closeDeleteModal();

                // FIX: Remove the deleted employee from the local array
                allEmployees = allEmployees.filter(emp => Number(emp.id) !== Number(deleteEmployeeId));

                // Re-apply filters to refresh the display
                applyFilters();
                await loadEmployees(page = 1)
            } else {
                showToast('Error', result?.message || 'Delete failed', 'error');
            }
        } catch (err) {
            console.error(err);
            showToast('Error', 'Network error occurred', 'error');
        }
    }

    // Event listeners for filters/search
    document.getElementById("searchInput").addEventListener("input", () => {
        applyFilters();
    });
    document.getElementById("departmentFilter").addEventListener("change", () => {
        applyFilters();
    });
    document.getElementById("positionFilter").addEventListener("change", () => {
        applyFilters();
    });

    // initial load
    loadEmployees(1);
    </script>
</body>

</html>