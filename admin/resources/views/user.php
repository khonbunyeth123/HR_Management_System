<div class="bg-gradient-to-br from-slate-50 to-slate-100 min-h-screen ">
    <div class="p-2">
        <div class="bg-white rounded-lg shadow-sm p-4 mb-4">
            <div class="flex flex-col gap-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <iconify-icon icon="mdi:users-group" style="font-size: 24px; color: #4f46e5;"></iconify-icon>
                        <h1 class="text-lg font-bold text-gray-900">User Directory</h1>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-semibold bg-indigo-100 text-indigo-700 px-3 py-1 rounded-full"
                            id="totalCount">0 Users</span>
                        <button onclick="openCreateModal()"
                            class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-semibold flex items-center gap-2 transition-colors">
                            <iconify-icon icon="mdi:plus-circle"></iconify-icon>
                            Add User
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
                            <th class="px-4 py-3 text-left font-semibold">Email</th>
                            <th class="px-4 py-3 text-left font-semibold">Role</th>
                            <th class="px-4 py-3 text-left font-semibold">Created At</th>
                        </tr>
                    </thead>
                    <tbody id="userTableBody" class="divide-y divide-gray-100">
                        <tr>
                            <td colspan="5" class="px-4 py-6 text-center text-gray-400">
                                <div class="flex items-center justify-center gap-2">
                                    <iconify-icon icon="mdi:loading" style="font-size: 20px;"
                                        class="animate-spin"></iconify-icon>
                                    Loading...
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div id="paginationContainer"></div>
        </div>
    </div>
</div>

<!-- Add User Modal -->
<div id="createUserModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-2xl w-full max-w-md mx-4 max-h-[90vh] overflow-y-auto">
        <!-- Modal Header -->
        <div class="sticky top-0 bg-gradient-to-r from-slate-900 to-slate-800 text-white px-6 py-4 flex items-center justify-between border-b">
            <div class="flex items-center gap-3">
                <iconify-icon icon="mdi:account-plus" style="font-size: 24px;"></iconify-icon>
                <h2 class="text-lg font-bold">Add New User</h2>
            </div>
            <button onclick="closeCreateModal()" class="text-gray-300 hover:text-white transition-colors p-1">
                <iconify-icon icon="mdi:close" style="font-size: 20px;"></iconify-icon>
            </button>
        </div>

        <!-- Modal Body -->
        <div class="p-6">
            <form id="createUserForm" onsubmit="submitCreateUser(event)">
                <!-- Row 1: Full Name & Username -->
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <!-- Full Name -->
                    <div>
                        <label for="fullName" class="block text-sm font-semibold text-gray-700 mb-2">
                            Full Name
                            <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="fullName" name="full_name" required
                            class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-colors"
                            placeholder="John Doe">
                    </div>

                    <!-- Username -->
                    <div>
                        <label for="username" class="block text-sm font-semibold text-gray-700 mb-2">
                            Username
                            <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-2.5 text-gray-400 text-sm">@</span>
                            <input type="text" id="username" name="username" required
                                class="w-full pl-7 pr-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-colors"
                                placeholder="johndoe">
                        </div>
                    </div>
                </div>

                <!-- Row 2: Email & Password -->
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">
                            Email Address
                            <span class="text-red-500">*</span>
                        </label>
                        <input type="email" id="email" name="email" required
                            class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-colors"
                            placeholder="john@example.com">
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">
                            Password
                            <span class="text-red-500">*</span>
                        </label>
                        <input type="password" id="password" name="password" required
                            class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-colors"
                            placeholder="Enter a strong password">
                    </div>
                </div>

                <p class="text-xs text-gray-500 mb-4">Password: At least 6 characters recommended</p>

                <!-- Row 3: Role & Status -->
                <div class="grid grid-cols-2 gap-4 mb-6">
                    <!-- Role -->
                    <div>
                        <label for="role_id" class="block text-sm font-semibold text-gray-700 mb-2">
                            Role
                            <span class="text-red-500">*</span>
                        </label>
                        <select id="role_id" name="role_id" required
                            class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent bg-white cursor-pointer transition-colors">
                            <option value="">Select a role</option>
                        </select>
                    </div>

                    <!-- Status -->
                    <div>
                        <label for="status" class="block text-sm font-semibold text-gray-700 mb-2">
                            Status
                        </label>
                        <select id="status" name="status_id"
                            class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent bg-white cursor-pointer transition-colors">
                            <option value="1" selected>Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </div>

                <!-- Error Message -->
                <div id="formError" class="hidden mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700 flex items-center gap-2">
                    <iconify-icon icon="mdi:alert-circle"></iconify-icon>
                    <span id="errorText"></span>
                </div>

                <!-- Submit and Cancel Buttons -->
                <div class="flex gap-3">
                    <button type="button" onclick="closeCreateModal()"
                        class="flex-1 px-4 py-2 border border-gray-200 text-gray-700 rounded-lg text-sm font-semibold hover:bg-gray-50 transition-colors">
                        Cancel
                    </button>
                    <button type="submit" id="submitBtn"
                        class="flex-1 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-semibold transition-colors flex items-center justify-center gap-2">
                        <iconify-icon icon="mdi:check-circle" id="submitIcon"></iconify-icon>
                        <span id="submitText">Add User</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    let currentPage = 1;
    let totalPages = 1;
    let allUsers = [];
    const perPage = 18;

    // Modal Functions
    function openCreateModal() {
        document.getElementById("createUserModal").classList.remove("hidden");
        document.getElementById("createUserForm").reset();
        document.getElementById("formError").classList.add("hidden");
    }

    function closeCreateModal() {
        document.getElementById("createUserModal").classList.add("hidden");
        document.getElementById("createUserForm").reset();
        document.getElementById("formError").classList.add("hidden");
    }

    function showFormError(message) {
        document.getElementById("formError").classList.remove("hidden");
        document.getElementById("errorText").textContent = message;
    }

    function hideFormError() {
        document.getElementById("formError").classList.add("hidden");
    }

    function submitCreateUser(event) {
        event.preventDefault();
        hideFormError();

        const fullName = document.getElementById("fullName").value.trim();
        const username = document.getElementById("username").value.trim();
        const email = document.getElementById("email").value.trim();
        const password = document.getElementById("password").value.trim();
        const roleId = document.getElementById("role_id").value;
        const status = document.getElementById("status").value;

        // Validation
        if (!fullName) {
            showFormError("Full name is required");
            return;
        }
        if (!username) {
            showFormError("Username is required");
            return;
        }
        if (!email) {
            showFormError("Email is required");
            return;
        }
        if (!email.includes("@")) {
            showFormError("Valid email is required");
            return;
        }
        if (!password || password.length < 6) {
            showFormError("Password must be at least 6 characters");
            return;
        }
        if (!roleId) {
            showFormError("Role is required");
            return;
        }

        // Show loading state
        const submitBtn = document.getElementById("submitBtn");
        const submitText = document.getElementById("submitText");
        const submitIcon = document.getElementById("submitIcon");
        submitBtn.disabled = true;
        submitText.textContent = "Creating...";
        submitIcon.setAttribute("class", "animate-spin");

        // Send data to API
        const formData = new FormData();
        formData.append("full_name", fullName);
        formData.append("username", username);
        formData.append("email", email);
        formData.append("password", password);
        formData.append("role_id", roleId);
        formData.append("status_id", status);

        fetch("api/users/create", {
            method: "POST",
            body: formData
        })
            .then(res => res.json())
            .then(result => {
                if (result.success) {
                    closeCreateModal();
                    loadUsers(1); // Reload users list
                    console.log("User created successfully");
                } else {
                    showFormError(result.message || "Failed to create user");
                }
            })
            .catch(err => {
                console.error("Error:", err);
                showFormError("Error creating user. Please try again.");
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitText.textContent = "Add User";
                submitIcon.setAttribute("class", "");
            });
    }

    function loadUsers(page) {
        const params = new URLSearchParams({
            'paging_options[page]': page,
            'paging_options[per_page]': perPage,
            'filters[0][property]': 'status_id',
            'filters[0][value]': 1,
            'sorts[0][property]': 'created_at',
            'sorts[0][direction]': 'DESC'
        });

        fetch(`api/users/show?${params.toString()}`)
            .then(res => res.json())
            .then(result => {
                if (result.success && result.data?.users) {
                    allUsers = result.data.users;
                    applyFilters();

                    const total = result.pagination?.total || allUsers.length;
                    totalPages = Math.ceil(total / perPage);
                    currentPage = page;

                    document.getElementById("totalCount").textContent = `${total} Users`;
                    renderPagination(total);
                } else {
                    showError("No users found");
                }
            })
            .catch(err => {
                console.error("Error:", err);
                showError("Error loading data");
            });
    }

    function showError(message) {
        document.getElementById("userTableBody").innerHTML = `
      <tr>
        <td colspan="5" class="px-4 py-6 text-center text-red-500">
          <div class="flex items-center justify-center gap-2">
            <iconify-icon icon="mdi:alert-circle"></iconify-icon> ${message}
          </div>
        </td>
      </tr>`;
    }

    function renderTable(users) {
        const tbody = document.getElementById("userTableBody");
        if (!users.length) {
            tbody.innerHTML = '<tr><td colspan="5" class="px-4 py-6 text-center text-gray-400">No users found</td></tr>';
            return;
        }

        tbody.innerHTML = users.map(user => `
      <tr class="hover:bg-indigo-50 transition-colors">
        <td class="px-4 py-3 font-medium text-gray-900">${user.full_name}</td>
        <td class="px-4 py-3 text-gray-600 text-xs font-mono">@${user.username}</td>
        <td class="px-4 py-3 text-gray-700">${user.email}</td>
        <td class="px-4 py-3">
          <span class="bg-blue-100 text-blue-700 px-2 py-1 rounded text-xs font-semibold">${user.role_name || '—'}</span>
        </td>
        <td class="px-4 py-3 text-gray-600 text-xs">${new Date(user.created_at).toLocaleDateString('en-US', {
            month: 'short', day: 'numeric', year: 'numeric'
        })}</td>
      </tr>
    `).join('');
    }

    function applyFilters() {
        const search = document.getElementById("searchInput").value.toLowerCase();
        const filtered = allUsers.filter(u =>
            u.full_name.toLowerCase().includes(search) ||
            u.username.toLowerCase().includes(search)
        );
        renderTable(filtered);
        renderPagination(filtered.length);
    }

    function renderPagination(total) {
        const container = document.getElementById("paginationContainer");
        const pages = Math.ceil(total / perPage);

        if (pages <= 1) {
            container.innerHTML = '';
            return;
        }

        let paginationHTML = `
      <div class="flex items-center justify-between px-4 py-3 border-t border-gray-100 bg-gray-50">
        <div class="text-xs text-gray-600">
          Showing ${(currentPage - 1) * perPage + 1} to ${Math.min(currentPage * perPage, total)} of ${total} results
        </div>
        <div class="flex items-center gap-2">
          <button onclick="goToPreviousPage()" ${currentPage === 1 ? 'disabled' : ''}
            class="p-1 text-gray-600 hover:bg-gray-200 rounded disabled:opacity-50 disabled:cursor-not-allowed">
            <iconify-icon icon="mdi:chevron-left" style="font-size: 18px;"></iconify-icon>
          </button>
          <div class="flex gap-1">
    `;

        for (let i = 1; i <= pages; i++) {
            paginationHTML += `
        <button onclick="goToPage(${i})"
          class="px-2 py-1 rounded text-xs font-medium ${i === currentPage ? 'bg-indigo-600 text-white' : 'text-gray-700 hover:bg-gray-200'}">       
          ${i}
        </button>
      `;
        }

        paginationHTML += `
          </div>
          <button onclick="goToNextPage()" ${currentPage === pages ? 'disabled' : ''}
            class="p-1 text-gray-600 hover:bg-gray-200 rounded disabled:opacity-50 disabled:cursor-not-allowed">
            <iconify-icon icon="mdi:chevron-right" style="font-size: 18px;"></iconify-icon>
          </button>
        </div>
      </div>
    `;

        container.innerHTML = paginationHTML;
    }

    function goToPreviousPage() {
        if (currentPage > 1) {
            currentPage--;
            applyFilters();
        }
    }

    function goToNextPage() {
        const total = allUsers.length;
        const pages = Math.ceil(total / perPage);
        if (currentPage < pages) {
            currentPage++;
            applyFilters();
        }
    }

    function goToPage(page) {
        currentPage = page;
        applyFilters();
    }

    document.getElementById("searchInput").addEventListener("input", applyFilters);

    // Close modal on escape key
    document.addEventListener("keydown", function(event) {
        if (event.key === "Escape") {
            closeCreateModal();
        }
    });

    // Close modal when clicking outside
    document.getElementById("createUserModal").addEventListener("click", function(event) {
        if (event.target === this) {
            closeCreateModal();
        }
    });

    function loadRoles() {
        fetch("api/roles")
            .then(res => res.json())
            .then(result => {
                if (!result.success || !Array.isArray(result.data)) return;
                const select = document.getElementById("role_id");
                select.innerHTML = '<option value="">Select a role</option>';
                const myRole = (window.__currentRoleName || '').toLowerCase();
                const rank = (name) => name === 'admin' ? 3 : name === 'manager' ? 2 : name === 'employee' ? 1 : 0;
                const myRank = rank(myRole);
                result.data.forEach(r => {
                    const rRank = rank((r.name || '').toLowerCase());
                    if (rRank > myRank) return;
                    const opt = document.createElement("option");
                    opt.value = r.id;
                    opt.textContent = r.name;
                    select.appendChild(opt);
                });
            })
            .catch(err => console.error("Error loading roles:", err));
    }

    // Load users on page load
    loadRoles();
    loadUsers(1);
</script>
