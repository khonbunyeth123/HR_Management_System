<div class="w-full h-full">
    <div class="bg-white shadow-lg p-4">
        <div class="bg-white rounded-lg shadow-sm p-4 mb-4">
        <div class="flex justify-between items-center">
            <div class="flex items-center gap-2">
                <iconify-icon icon="mdi:clock-check" style="font-size: 24px; color: #4f46e5;"></iconify-icon>
                <h1 class="text-lg font-bold text-gray-900">Attendance Records</h1>
            </div>
            <span class="text-xs font-semibold bg-indigo-100 text-indigo-700 px-3 py-1 rounded-full" id="totalCount">0 Records</span>
        </div>

        <!-- Filters -->
        <div class="flex flex-col sm:flex-row gap-2 mt-4">
            <div class="flex-1 relative">
                <iconify-icon icon="mdi:magnify"
                    style="position:absolute; left:10px; top:50%; transform:translateY(-50%); color:#9ca3af; font-size:18px;"></iconify-icon>
                <input type="text" id="searchInput" placeholder="Search by employee ID or date..."
                    class="w-full pl-9 pr-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
            </div>
            <select id="checkTypeFilter"
                class="px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white cursor-pointer">
                <option value="">All Check Types</option>
                <option value="1">Check In</option>
                <option value="2">Check Out</option>
            </select>
            <input type="date" id="dateFilter"
                class="px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-900 text-white sticky top-0">
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
                                <iconify-icon icon="mdi:loading" class="animate-spin" style="font-size:20px;"></iconify-icon>
                                Loading...
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div id="paginationContainer" class="px-4 py-3 border-t border-gray-100 bg-gray-50 flex justify-center gap-2"></div>
    </div>
    </div>
    <!-- Header -->
    
</div>

<script>
    let currentPage = 1;
    let totalPages = 1;
    let allRecords = [];
    const perPage = 18;

    // helpers
    function getCheckTypeLabel(typeId) { return typeId == 1 ? 'Check In' : 'Check Out'; }
    function getCheckTypeColor(typeId) { return typeId == 1 ? 'bg-green-100 text-green-700' : 'bg-orange-100 text-orange-700'; }
    function getStatusBadge(statusId) { 
        return statusId == 1 
            ? '<span class="bg-blue-100 text-blue-700 px-2 py-1 rounded text-xs font-semibold">Active</span>' 
            : '<span class="bg-gray-100 text-gray-700 px-2 py-1 rounded text-xs font-semibold">Inactive</span>'; 
    }

    // fetch attendance
    function loadAttendance(page = 1) {
        const searchInput = document.getElementById("searchInput").value;
        const checkType = document.getElementById("checkTypeFilter").value;
        const date = document.getElementById("dateFilter").value;

        const params = new URLSearchParams({
            "paging_options[page]": page,
            "paging_options[per_page]": perPage,
            "filters[status_id]": 1
        });

        fetch("/api/attendance/show?" + params.toString())
            .then(res => res.json())
            .then(result => {
                const tbody = document.getElementById("attendanceTableBody");

                if (result.success && result.data) {
                    allRecords = result.data.attendance_records;

                    // apply frontend filters
                    let filtered = allRecords.filter(rec => 
                        (rec.employee_id.toString().includes(searchInput) || rec.date.includes(searchInput)) &&
                        (!checkType || rec.check_type_id == checkType) &&
                        (!date || rec.date === date)
                    );

                    renderTable(filtered);

                    const total = result.pagination.total;
                    totalPages = result.pagination.total_pages;
                    currentPage = page;
                    document.getElementById("totalCount").textContent = total + " Records";

                    renderPagination(totalPages, currentPage);
                } else {
                    tbody.innerHTML = '<tr><td colspan="6" class="px-4 py-6 text-center text-gray-400">No attendance records found</td></tr>';
                }
            })
            .catch(err => {
                document.getElementById("attendanceTableBody").innerHTML = `<tr><td colspan="6" class="px-4 py-6 text-center text-red-500">
                    <iconify-icon icon="mdi:alert-circle"></iconify-icon> Error loading data
                </td></tr>`;
                console.error(err);
            });
    }

    // render table
    function renderTable(records) {
        const tbody = document.getElementById("attendanceTableBody");
        if (!records.length) {
            tbody.innerHTML = '<tr><td colspan="6" class="px-4 py-6 text-center text-gray-400">No attendance records found</td></tr>';
            return;
        }

        tbody.innerHTML = records.map(rec => `
            <tr class="hover:bg-indigo-50 transition-colors">
                <td class="px-4 py-3 font-medium text-gray-900">#${rec.employee_id}</td>
                <td class="px-4 py-3 text-gray-600 text-sm">${new Date(rec.date).toLocaleDateString()}</td>
                <td class="px-4 py-3 font-mono text-sm font-semibold text-indigo-600">${rec.check_time}</td>
                <td class="px-4 py-3"><span class="${getCheckTypeColor(rec.check_type_id)} px-2 py-1 rounded text-xs font-semibold">${getCheckTypeLabel(rec.check_type_id)}</span></td>
                <td class="px-4 py-3">${getStatusBadge(rec.status_id)}</td>
                <td class="px-4 py-3 text-gray-500 text-xs">${new Date(rec.created_at).toLocaleDateString()}</td>
            </tr>
        `).join('');
    }

    // simple pagination
    function renderPagination(totalPages, currentPage) {
        const container = document.getElementById("paginationContainer");
        container.innerHTML = '';

        if (totalPages <= 1) return;

        for (let i = 1; i <= totalPages; i++) {
            const btn = document.createElement('button');
            btn.textContent = i;
            btn.className = `px-3 py-1 border rounded ${i === currentPage ? 'bg-indigo-500 text-white' : 'bg-white text-gray-700'}`;
            btn.onclick = () => loadAttendance(i);
            container.appendChild(btn);
        }
    }

    // filter events
    document.getElementById("searchInput").addEventListener("input", () => loadAttendance(1));
    document.getElementById("checkTypeFilter").addEventListener("change", () => loadAttendance(1));
    document.getElementById("dateFilter").addEventListener("change", () => loadAttendance(1));

    // initial load
    loadAttendance();
</script>
