<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/iconify/3.1.0/iconify.min.js">
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://code.iconify.design/iconify-icon/1.0.8/iconify-icon.min.js"></script>

<div class="bg-gradient-to-br from-slate-50 to-slate-100 min-h-full ">
    <div class="p-2">
        <!-- Header with Filters -->
        <div class="bg-white rounded-lg shadow-sm p-4 mb-4">
            <div class="flex flex-col gap-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <iconify-icon icon="mdi:calendar-check" style="font-size: 24px; color: #4f46e5;"></iconify-icon>
                        <h1 class="text-lg font-bold text-gray-900">Leave Applications</h1>
                    </div>
                    <span class="text-xs font-semibold bg-indigo-100 text-indigo-700 px-3 py-1 rounded-full"
                        id="totalCount">0 Applications</span>
                </div>

                <!-- Search & Filter Bar -->
                <div class="flex flex-col sm:flex-row gap-2">
                    <div class="flex-1 relative">
                        <iconify-icon icon="mdi:magnify"
                            style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: #9ca3af; font-size: 18px;"></iconify-icon>
                        <input type="text" id="searchInput" placeholder="Search by employee name..."
                            class="w-full pl-9 pr-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    </div>
                    <select id="leaveTypeFilter"
                        class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white cursor-pointer">
                        <option value="">All Leave Types</option>
                    </select>
                    <select id="statusFilter"
                        class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white cursor-pointer">
                        <option value="">All Status</option>
                        <option value="0">Pending</option>
                        <option value="1">Approved</option>
                        <option value="2">Rejected</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <div class="overflow-x-auto  flex flex-col">
                <table class="w-full text-sm flex-1">
                    <thead
                        class="bg-gradient-to-b from-slate-900 via-slate-800 to-slate-900 shadow-lg text-white sticky top-0">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold">Employee</th>
                            <th class="px-4 py-3 text-left font-semibold">Leave Type</th>
                            <th class="px-4 py-3 text-left font-semibold">Start Date</th>
                            <th class="px-4 py-3 text-left font-semibold">End Date</th>
                            <th class="px-4 py-3 text-left font-semibold">Reason</th>
                            <th class="px-4 py-3 text-left font-semibold">Status</th>
                            <th class="px-4 py-3 text-left font-semibold">Created</th>
                        </tr>
                    </thead>
                    <tbody id="leaveTableBody" class="divide-y divide-gray-100">
                        <tr>
                            <td colspan="7" class="px-4 py-6 text-center text-gray-400">
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
            <div id="paginationContainer" class="px-4 py-3 border-t border-gray-100 bg-gray-50"></div>
        </div>
    </div>
</div>

<script src="./utils/component/pagination.js"></script>
<script>
    let currentPage = 1;
    let totalPages = 1;
    let allRecords = [];
    const perPage = 10;
    const leaveTypeSet = new Set();

    function getStatusBadge(statusId) {
        if (statusId == 0) return '<span class="bg-yellow-100 text-yellow-700 px-2 py-1 rounded text-xs font-semibold">Pending</span>';
        if (statusId == 1) return '<span class="bg-green-100 text-green-700 px-2 py-1 rounded text-xs font-semibold">Approved</span>';
        if (statusId == 2) return '<span class="bg-red-100 text-red-700 px-2 py-1 rounded text-xs font-semibold">Rejected</span>';
        return '<span class="bg-gray-100 text-gray-700 px-2 py-1 rounded text-xs font-semibold">Unknown</span>';
    }

    function loadLeaveApplications(page) {
        const params = new URLSearchParams({
            "paging_options[page]": page,
            "paging_options[per_page]": 5
        });

        fetch("api/leave_application/show.php?" + params.toString())
            .then(res => res.json())
            .then(result => {
                console.log("API result:", result);
                if (result.success && result.data) {
                    allRecords = result.data.leave_applications;
                    populateFilterOptions();
                    renderTable(allRecords);

                    const total = result.pagination.total;
                    totalPages = result.pagination.total_pages;
                    currentPage = page;

                    document.getElementById("totalCount").textContent = total + " Applications";

                    renderPagination({
                        currentPage: currentPage,
                        totalPages: totalPages,
                        showingFrom: (page - 1) * perPage + 1,
                        showingTo: Math.min(page * perPage, total),
                        totalRecords: total,
                        showPageNumbers: true,
                        onPrevious: () => currentPage > 1 && loadLeaveApplications(currentPage - 1),
                        onNext: () => currentPage < totalPages && loadLeaveApplications(currentPage + 1),
                        onPageClick: (p) => loadLeaveApplications(p)
                    });
                }
            })
            .catch(err => {
                document.getElementById("leaveTableBody").innerHTML = '<tr><td colspan="7" class="px-4 py-6 text-center text-red-500"><iconify-icon icon="mdi:alert-circle"></iconify-icon> Error loading data</td></tr>';
                console.error("Error:", err);
            });
    }


    function renderTable(records) {
        const tbody = document.getElementById("leaveTableBody");
        if (!records.length) {
            tbody.innerHTML = '<tr><td colspan="7" class="px-4 py-6 text-center text-gray-400">No leave applications found</td></tr>';
            return;
        }

        tbody.innerHTML = records.map(rec => `
            <tr class="hover:bg-indigo-50 transition-colors">
                <td class="px-4 py-3 font-medium text-gray-900">${rec.employee_name}</td>
                <td class="px-4 py-3"><span class="bg-indigo-100 text-indigo-700 px-2 py-1 rounded text-xs font-semibold">${rec.leave_type}</span></td>
                <td class="px-4 py-3 text-gray-600 text-sm">${new Date(rec.start_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}</td>
                <td class="px-4 py-3 text-gray-600 text-sm">${new Date(rec.end_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}</td>
                <td class="px-4 py-3 text-gray-600 text-xs max-w-xs truncate">${rec.reason || '-'}</td>
                <td class="px-4 py-3">${getStatusBadge(rec.status_id)}</td>
                <td class="px-4 py-3 text-gray-500 text-xs">${new Date(rec.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric' })}</td>
            </tr>
        `).join('');
    }

    function populateFilterOptions() {
        allRecords.forEach(rec => {
            leaveTypeSet.add(rec.leave_type);
        });

        const typeSelect = document.getElementById("leaveTypeFilter");

        Array.from(leaveTypeSet).sort().forEach(type => {
            if (!Array.from(typeSelect.options).some(opt => opt.value === type)) {
                const opt = document.createElement("option");
                opt.value = type;
                opt.textContent = type;
                typeSelect.appendChild(opt);
            }
        });
    }

    function applyFilters() {
        const search = document.getElementById("searchInput").value.toLowerCase();
        const leaveType = document.getElementById("leaveTypeFilter").value;
        const status = document.getElementById("statusFilter").value;

        const filtered = allRecords.filter(rec =>
            (rec.employee_name.toLowerCase().includes(search)) &&
            (!leaveType || rec.leave_type === leaveType) &&
            (!status || rec.status_id == status)
        );

        renderTable(filtered);
    }

    document.getElementById("searchInput").addEventListener("input", applyFilters);
    document.getElementById("leaveTypeFilter").addEventListener("change", applyFilters);
    document.getElementById("statusFilter").addEventListener("change", applyFilters);

    loadLeaveApplications(1);
</script>