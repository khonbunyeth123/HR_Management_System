<div class="bg-gradient-to-br from-slate-50 to-slate-100 min-h-screen ">
    <div class="p-2">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-sm p-4 mb-4">
            <div class="flex flex-col gap-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <iconify-icon icon="mdi:account-group" style="font-size: 24px; color: #4f46e5;"></iconify-icon>
                        <h1 class="text-lg font-bold text-gray-900">User Directory</h1>
                    </div>
                    <span class="text-xs font-semibold bg-indigo-100 text-indigo-700 px-3 py-1 rounded-full"
                        id="totalCount">0 Users</span>
                </div>

                <!-- Search Bar -->
                <div class="flex flex-col sm:flex-row gap-2">
                    <div class="flex-1 relative">
                        <iconify-icon icon="mdi:magnify"
                            style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: #9ca3af; font-size: 18px;"></iconify-icon>
                        <input type="text" id="searchInput" placeholder="Search by name or username..."
                            class="w-full pl-9 pr-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    </div>
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

<script>
    let currentPage = 1;
    let totalPages = 1;
    let allUsers = [];
    const perPage = 18;

    function loadUsers(page) {
        const params = new URLSearchParams({
            'paging_options[page]': page,
            'paging_options[per_page]': perPage,
            'filters[0][property]': 'status_id',
            'filters[0][value]': 1,
            'sorts[0][property]': 'created_at',
            'sorts[0][direction]': 'DESC'
        });

        fetch(`api/users/show.php?${params.toString()}`)
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
          <span class="bg-blue-100 text-blue-700 px-2 py-1 rounded text-xs font-semibold">${user.role}</span>
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

    // Load users on page load
    loadUsers(1);
</script>