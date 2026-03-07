<div class="w-full h-full">
    <div class="bg-white shadow-lg p-4">
        <h1 class="text-3xl font-bold text-gray-800 mb-6 bg-white p-4 rounded-lg shadow">HR Management Dashboard</h1>

    <!-- Stats Grid -->
    <div id="statsGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <!-- Loading placeholders -->
        <div class="bg-white p-4 rounded-lg shadow animate-pulse">
            <div class="h-10 bg-gray-200 rounded w-3/4"></div>
        </div>
        <div class="bg-white p-4 rounded-lg shadow animate-pulse">
            <div class="h-10 bg-gray-200 rounded w-3/4"></div>
        </div>
        <div class="bg-white p-4 rounded-lg shadow animate-pulse">
            <div class="h-10 bg-gray-200 rounded w-3/4"></div>
        </div>
        <div class="bg-white p-4 rounded-lg shadow animate-pulse">
            <div class="h-10 bg-gray-200 rounded w-3/4"></div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Recent Leave Requests -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                <iconify-icon icon="mdi:calendar-clock" width="24"></iconify-icon>
                Recent Leave Requests
            </h2>
            <div id="leaveRequests" class="space-y-3">
                <div class="text-center text-gray-500 py-4">Loading...</div>
            </div>
        </div>

        <!-- Departments -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                <iconify-icon icon="mdi:office-building" width="24"></iconify-icon>
                Departments
            </h2>
            <div id="departments" class="space-y-4">
                <div class="text-center text-gray-500 py-4">Loading...</div>
            </div>
        </div>
    </div>
</div>
    </div>
    

<script>
    document.addEventListener("DOMContentLoaded", () => {

        /* ---------- Helpers ---------- */

        function createStat(icon, value, label, colorClass) {
            return `
            <div class="bg-white p-4 rounded-lg shadow flex items-center gap-4">
                <iconify-icon icon="${icon}" width="36" class="${colorClass}"></iconify-icon>
                <div>
                    <div class="text-2xl font-bold text-gray-800">${value}</div>
                    <div class="text-sm text-gray-600">${label}</div>
                </div>
            </div>`;
        }

        function createLeaveRequest(req) {
            const statusColors = {
                'pending': 'bg-yellow-100 text-yellow-800',
                'approved': 'bg-green-100 text-green-800',
                'rejected': 'bg-red-100 text-red-800'
            };
            const statusClass = statusColors[req.status?.toLowerCase()] || 'bg-gray-100 text-gray-800';
            
            return `
            <div class="border border-gray-200 p-4 rounded-lg hover:shadow-md transition-shadow">
                <div class="flex justify-between items-start mb-2">
                    <div class="font-semibold text-gray-800">${req.name || 'Unknown'}</div>
                    <span class="px-2 py-1 text-xs rounded-full ${statusClass}">${req.status || 'N/A'}</span>
                </div>
                <div class="text-sm text-gray-600">${req.type || 'N/A'}</div>
                ${req.period ? `<div class="text-xs text-gray-500 mt-1">${req.period}</div>` : ''}
            </div>`;
        }

        function createDepartment(dept) {
            return `
            <div class="border border-gray-200 p-4 rounded-lg hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="font-semibold text-gray-800">${dept.name || 'Unknown Department'}</div>
                        <div class="text-sm text-gray-600 mt-1">
                            <iconify-icon icon="mdi:account-group" width="16" class="inline"></iconify-icon>
                            ${dept.count || 0} employees ${dept.percentage ? `(${dept.percentage}%)` : ''}
                        </div>
                    </div>
                    <iconify-icon icon="mdi:chevron-right" width="24" class="text-gray-400"></iconify-icon>
                </div>
            </div>`;
        }

        function showError(elementId, message) {
            const element = document.getElementById(elementId);
            if (element) {
                element.innerHTML = `<div class="text-center text-red-500 py-4">${message}</div>`;
            }
        }

        function showEmpty(elementId, message) {
            const element = document.getElementById(elementId);
            if (element) {
                element.innerHTML = `<div class="text-center text-gray-500 py-4">${message}</div>`;
            }
        }

        /* ---------- Dashboard Summary ---------- */
        fetch("/api/dashboard/summary")
            .then(res => {
                if (!res.ok) {
                    throw new Error(`HTTP ${res.status}: ${res.statusText}`);
                }
                return res.json();
            })
            .then(result => {
                console.log('Summary API Response:', result);
                
                // Handle both {success: true, data: {...}} and direct data formats
                const data = result.success ? result.data : result;
                
                if (!data || (result.success === false)) {
                    showError("statsGrid", "Failed to load statistics");
                    return;
                }
                
                const statsGrid = document.getElementById("statsGrid");
                statsGrid.innerHTML = `
                    ${createStat("mdi:account-multiple", data.total_employees || 0, "Total Employees", "text-blue-500")}
                    ${createStat("mdi:check-circle", data.active_employees || 0, "Active Users", "text-green-500")}
                    ${createStat("mdi:clock-outline", data.pending_leaves || 0, "Pending Leaves", "text-orange-500")}
                    ${createStat("mdi:calendar-blank", data.on_leave_today || 0, "On Leave Today", "text-purple-500")}
                `;
            })
            .catch(error => {
                console.error('Error loading dashboard summary:', error);
                showError("statsGrid", `Error loading statistics: ${error.message}`);
            });

        /* ---------- Recent Leaves ---------- */
        fetch("/api/dashboard/recent-leaves")
            .then(res => {
                if (!res.ok) {
                    throw new Error(`HTTP ${res.status}: ${res.statusText}`);
                }
                return res.json();
            })
            .then(result => {
                console.log('Leave Requests API Response:', result);
                
                const div = document.getElementById("leaveRequests");
                
                // Handle both {success: true, data: [...]} and direct array formats
                const data = result.success ? result.data : (Array.isArray(result) ? result : null);
                
                if (!data || (result.success === false)) {
                    showError("leaveRequests", "Failed to load leave requests");
                    return;
                }
                
                if (data.length === 0) {
                    showEmpty("leaveRequests", "No recent leave requests");
                    return;
                }
                
                div.innerHTML = data.map(r => createLeaveRequest(r)).join('');
            })
            .catch(error => {
                console.error('Error loading leave requests:', error);
                showError("leaveRequests", `Error loading leave requests: ${error.message}`);
            });

        /* ---------- Departments ---------- */
        fetch("/api/dashboard/department")
            .then(res => {
                if (!res.ok) {
                    throw new Error(`HTTP ${res.status}: ${res.statusText}`);
                }
                return res.json();
            })
            .then(result => {
                console.log('Departments API Response:', result);
                
                const div = document.getElementById("departments");
                
                // Handle both {success: true, data: [...]} and direct array formats
                const data = result.success ? result.data : (Array.isArray(result) ? result : null);
                
                if (!data || (result.success === false)) {
                    showError("departments", "Failed to load departments");
                    return;
                }
                
                if (data.length === 0) {
                    showEmpty("departments", "No departments found");
                    return;
                }
                
                div.innerHTML = data.map(d => createDepartment(d)).join('');
            })
            .catch(error => {
                console.error('Error loading departments:', error);
                showError("departments", `Error loading departments: ${error.message}`);
            });
    });
</script>
