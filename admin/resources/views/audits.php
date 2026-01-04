<div class="bg-gradient-to-br from-slate-50 to-slate-100 h-full ">
    <div class="p-2">
        <!-- Header with Filters -->
        <div class="bg-white rounded-lg shadow-sm p-4 mb-4">
            <div class="flex flex-col gap-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <iconify-icon icon="mdi:file-document-multiple"
                            style="font-size: 24px; color: #4f46e5;"></iconify-icon>
                        <h1 class="text-lg font-bold text-gray-900">User Audit Logs</h1>
                    </div>
                    <span class="text-xs font-semibold bg-indigo-100 text-indigo-700 px-3 py-1 rounded-full"
                        id="totalCount">0 Records</span>
                </div>

                <!-- Search & Filter Bar -->
                <div class="flex flex-col sm:flex-row gap-2">
                    <div class="flex-1 relative">
                        <iconify-icon icon="mdi:magnify"
                            style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: #9ca3af; font-size: 18px;"></iconify-icon>
                        <input type="text" id="searchInput" placeholder="Search by context, operator, or IP..."
                            class="w-full pl-9 pr-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent" />
                    </div>

                    <select id="statusFilter"
                        class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white cursor-pointer">
                        <option value="">All Status</option>
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
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
                            <th class="px-4 py-3 text-left font-semibold">User ID</th>
                            <th class="px-4 py-3 text-left font-semibold">Context</th>
                            <th class="px-4 py-3 text-left font-semibold">Operator</th>
                            <th class="px-4 py-3 text-left font-semibold">IP Address</th>
                            <th class="px-4 py-3 text-left font-semibold">Created At</th>
                        </tr>
                    </thead>
                    <tbody id="auditTableBody" class="divide-y divide-gray-100">
                        <tr>
                            <td colspan="5" class="px-4 py-6 text-center text-gray-400">
                                <div class="flex items-center justify-center gap-2">
                                    <iconify-icon icon="mdi:loading" class="animate-spin"
                                        style="font-size: 20px;"></iconify-icon>
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

<script src="./utils/component/pagination.js"></script>
<script>
    let currentPage = 1;
    let totalPages = 1;
    const perPage = 18;
    let allAudits = [];

    async function loadAudits(page = 1) {
        const params = new URLSearchParams({
            "paging_options[page]": page,
            "paging_options[per_page]": perPage,
            "sorts[0][property]": "created_at",
            "sorts[0][direction]": "DESC"
        });

        const response = await fetch("api/audits/show_user_audits.php?" + params.toString());
        const result = await response.json();

        if (result.success && result.data && result.data.user_audits) {
            allAudits = result.data.user_audits;
            renderTable(allAudits);

            const total = result.pagination.total || allAudits.length;
            totalPages = result.pagination.total_pages || 1;
            currentPage = page;

            document.getElementById("totalCount").textContent = total + " Records";

            renderPagination({
                currentPage,
                totalPages,
                showingFrom: (page - 1) * perPage + 1,
                showingTo: Math.min(page * perPage, total),
                totalRecords: total,
                onPrevious: () => currentPage > 1 && loadAudits(currentPage - 1),
                onNext: () => currentPage < totalPages && loadAudits(currentPage + 1),
                onPageClick: (p) => loadAudits(p)
            });
        } else {
            document.getElementById("auditTableBody").innerHTML =
                '<tr><td colspan="5" class="px-4 py-6 text-center text-gray-400">No audit logs found</td></tr>';
        }
    }

    function renderTable(audits) {
        const tbody = document.getElementById("auditTableBody");
        if (!audits.length) {
            tbody.innerHTML =
                '<tr><td colspan="5" class="px-4 py-6 text-center text-gray-400">No data found</td></tr>';
            return;
        }

        tbody.innerHTML = audits.map(a => `
      <tr class="hover:bg-indigo-50 transition-colors">
        <td class="px-4 py-3 text-gray-900 font-medium">${a.user_id}</td>
        <td class="px-4 py-3 text-gray-600">${a.context}</td>
        <td class="px-4 py-3 text-gray-700">${a.operator}</td>
        <td class="px-4 py-3 font-mono text-xs text-gray-500">${a.ip}</td>
        <td class="px-4 py-3 text-gray-600 text-xs">${new Date(a.created_at).toLocaleString()}</td>
      </tr>
    `).join('');
    }

    // Filters
    document.getElementById("searchInput").addEventListener("input", applyFilters);
    document.getElementById("statusFilter").addEventListener("change", applyFilters);

    function applyFilters() {
        const search = document.getElementById("searchInput").value.toLowerCase();
        const status = document.getElementById("statusFilter").value;

        const filtered = allAudits.filter(a =>
            (a.context.toLowerCase().includes(search) ||
                a.operator.toLowerCase().includes(search) ||
                a.ip.toLowerCase().includes(search)) &&
            (!status || a.status_id == status)
        );

        renderTable(filtered);
    }

    loadAudits(1);
</script>