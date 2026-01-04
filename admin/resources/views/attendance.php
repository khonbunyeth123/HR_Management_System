<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/iconify/3.1.0/iconify.min.js">
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://code.iconify.design/iconify-icon/1.0.8/iconify-icon.min.js"></script>

<div class="bg-gradient-to-br from-slate-50 to-slate-100 min-h-screen ">
    <div class="p-2">
        <!-- Header with Filters -->
        <div class="bg-white rounded-lg shadow-sm p-4 mb-4">
            <div class="flex flex-col gap-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <iconify-icon icon="mdi:clock-check" style="font-size: 24px; color: #4f46e5;"></iconify-icon>
                        <h1 class="text-lg font-bold text-gray-900">Attendance Records</h1>
                    </div>
                    <span class="text-xs font-semibold bg-indigo-100 text-indigo-700 px-3 py-1 rounded-full"
                        id="totalCount">0 Records</span>
                </div>

                <!-- Search & Filter Bar -->
                <div class="flex flex-col sm:flex-row gap-2">
                    <div class="flex-1 relative">
                        <iconify-icon icon="mdi:magnify"
                            style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: #9ca3af; font-size: 18px;"></iconify-icon>
                        <input type="text" id="searchInput" placeholder="Search by employee ID or date..."
                            class="w-full pl-9 pr-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    </div>
                    <select id="checkTypeFilter"
                        class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white cursor-pointer">
                        <option value="">All Check Types</option>
                        <option value="1">Check In</option>
                        <option value="2">Check Out</option>
                    </select>
                    <input type="date" id="dateFilter"
                        class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
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
                            <th class="px-4 py-3 text-left font-semibold">Employee ID</th>
                            <th class="px-4 py-3 text-left font-semibold">Date</th>
                            <th class="px-4 py-3 text-left font-semibold">Check Time</th>
                            <th class="px-4 py-3 text-left font-semibold">Type</th>
                            <th class="px-4 py-3 text-left font-semibold">Status</th>
                            <th class="px-4 py-3 text-left font-semibold">Created</th>
                        </tr>
                    </thead>
                    <tbody id="attendanceTableBody" class="divide-y divide-gray-100">
                        <tr>
                            <td colspan="6" class="px-4 py-6 text-center text-gray-400">
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
    const perPage = 18;

    function getCheckTypeLabel(typeId) {
        return typeId == 1 ? 'Check In' : 'Check Out';
    }

    function getCheckTypeColor(typeId) {
        return typeId == 1 ? 'bg-green-100 text-green-700' : 'bg-orange-100 text-orange-700';
    }

    function getStatusBadge(statusId) {
        return statusId == 1 ? '<span class="bg-blue-100 text-blue-700 px-2 py-1 rounded text-xs font-semibold">Active</span>' : '<span class="bg-gray-100 text-gray-700 px-2 py-1 rounded text-xs font-semibold">Inactive</span>';
    }

    function loadAttendance(page) {
        const params = new URLSearchParams({
            "paging_options[page]": page,
            "paging_options[per_page]": perPage,
            "filters[0][property]": "status_id",
            "filters[0][value]": 1
        });

        fetch("api/attendance/show.php?" + params.toString())
            .then(res => res.json())
            .then(result => {
                if (result.success && result.data) {
                    allRecords = result.data.attendance_records;
                    renderTable(allRecords);

                    const total = result.pagination.total;
                    totalPages = result.pagination.total_pages;
                    currentPage = page;

                    document.getElementById("totalCount").textContent = total + " Records";

                    renderPagination({
                        currentPage: currentPage,
                        totalPages: totalPages,
                        showingFrom: (page - 1) * perPage + 1,
                        showingTo: Math.min(page * perPage, total),
                        totalRecords: total,
                        showPageNumbers: true,
                        onPrevious: () => currentPage > 1 && loadAttendance(currentPage - 1),
                        onNext: () => currentPage < totalPages && loadAttendance(currentPage + 1),
                        onPageClick: (p) => loadAttendance(p)
                    });
                }
            })
            .catch(err => {
                document.getElementById("attendanceTableBody").innerHTML = '<tr><td colspan="6" class="px-4 py-6 text-center text-red-500"><iconify-icon icon="mdi:alert-circle"></iconify-icon> Error loading data</td></tr>';
                console.error("Error:", err);
            });
    }

    function renderTable(records) {
        const tbody = document.getElementById("attendanceTableBody");
        if (!records.length) {
            tbody.innerHTML = '<tr><td colspan="6" class="px-4 py-6 text-center text-gray-400">No attendance records found</td></tr>';
            return;
        }

        tbody.innerHTML = records.map(rec => `
            <tr class="hover:bg-indigo-50 transition-colors">
                <td class="px-4 py-3 font-medium text-gray-900">#${rec.employee_id}</td>
                <td class="px-4 py-3 text-gray-600 text-sm">${new Date(rec.date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}</td>
                <td class="px-4 py-3 font-mono text-sm font-semibold text-indigo-600">${rec.check_time}</td>
                <td class="px-4 py-3"><span class="${getCheckTypeColor(rec.check_type_id)} px-2 py-1 rounded text-xs font-semibold">${getCheckTypeLabel(rec.check_type_id)}</span></td>
                <td class="px-4 py-3">${getStatusBadge(rec.status_id)}</td>
                <td class="px-4 py-3 text-gray-500 text-xs">${new Date(rec.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric' })}</td>
            </tr>
        `).join('');
    }

    function applyFilters() {
        const search = document.getElementById("searchInput").value.toLowerCase();
        const checkType = document.getElementById("checkTypeFilter").value;
        const date = document.getElementById("dateFilter").value;

        const filtered = allRecords.filter(rec =>
            (rec.employee_id.toString().includes(search) || rec.date.includes(search)) &&
            (!checkType || rec.check_type_id == checkType) &&
            (!date || rec.date === date)
        );

        renderTable(filtered);
    }

    document.getElementById("searchInput").addEventListener("input", applyFilters);
    document.getElementById("checkTypeFilter").addEventListener("change", applyFilters);
    document.getElementById("dateFilter").addEventListener("change", applyFilters);

    loadAttendance(1);
</script>