<script src="https://cdn.tailwindcss.com"></script>
<script src="https://code.iconify.design/iconify-icon/1.0.8/iconify-icon.min.js"></script>
<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

    * {
        font-family: 'Inter', sans-serif;
    }
</style>

<div class="bg-gray-100 min-h-full">
    <div class=" p-2">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-sm p-4 mb-4 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
                <p class="text-sm text-gray-500">Welcome back, Admin</p>
            </div>
            <div class="flex items-center gap-3">
                <button class="p-2 hover:bg-gray-100 rounded-lg">
                    <iconify-icon icon="mdi:bell" width="20"></iconify-icon>
                </button>
                <div class="w-9 h-9 bg-blue-600 rounded-full flex items-center justify-center text-white font-semibold">
                    A
                </div>
            </div>
        </div>

        <!-- Stats Grid -->
        <div id="statsGrid" class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4"></div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            <!-- Recent Leaves -->
            <div class="lg:col-span-2 bg-white rounded-lg shadow-sm p-4">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Recent Leave Requests</h3>
                <div id="leaveRequests" class="space-y-3"></div>
            </div>

            <!-- Department Breakdown -->
            <div class="bg-white rounded-lg shadow-sm p-4">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Departments</h3>
                <div id="departments" class="space-y-4"></div>
            </div>
        </div>
    </div>
</div>

<script>
    fetch("api/dashboard/show.php?action=dashboard_stats")
        .then(response => response.json())
        .then(result => {
            if (!result.success) {
                console.error("API Error:", result.message);
                return;
            }

            const data = result.data;

        // Render Stats
        const statsGrid = document.getElementById('statsGrid');
        statsGrid.innerHTML = `
            ${createStat("mdi:account-multiple", data.total_employees, "Total Employees", "blue")}
            ${createStat("mdi:check-circle", data.active_employees, "Active Users", "green")}
            ${createStat("mdi:clock-outline", data.pending_leaves, "Pending Leaves", "orange")}
            ${createStat("mdi:calendar-blank", data.on_leave_today, "On Leave Today", "purple")}
        `;

        // Render Leave Requests
        const leaveRequestsDiv = document.getElementById('leaveRequests');
        leaveRequestsDiv.innerHTML = "";
        data.recent_leave_requests.forEach(req => {
            leaveRequestsDiv.innerHTML += createLeaveRequest(req);
        });

        // Render Departments
        const departmentsDiv = document.getElementById('departments');
        departmentsDiv.innerHTML = "";
        data.departments.forEach(dept => {
            departmentsDiv.innerHTML += createDepartment(dept);
        });

    })
    .catch(error => console.error("Fetch Error:", error));


    // --- Helper Functions ---

    function createStat(icon, value, label, color) {
        return `
            <div class="bg-white rounded-lg shadow-sm p-4">
                <div class="flex items-center justify-between mb-2">
                    <iconify-icon icon="${icon}" width="24" class="text-${color}-600"></iconify-icon>
                </div>
                <p class="text-2xl font-bold text-gray-900">${value}</p>
                <p class="text-xs text-gray-600">${label}</p>
            </div>
        `;
    }

    function createLeaveRequest(req) {
        const statusColor = getStatusColor(req.status);
        return `
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 bg-blue-100 rounded-full flex items-center justify-center">
                        <iconify-icon icon="mdi:account" width="18" class="text-blue-600"></iconify-icon>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-900">${req.name}</p>
                        <p class="text-xs text-gray-500">${req.type} • ${req.days}</p>
                    </div>
                </div>
                <span class="text-xs font-semibold px-2 py-1 bg-${statusColor}-100 text-${statusColor}-800 rounded-full">${req.status}</span>
            </div>
        `;
    }

    function getStatusColor(status) {
        switch(status.toLowerCase()) {
            case "approved": return "green";
            case "pending": return "yellow";
            case "rejected": return "red";
            default: return "gray";
        }
    }

    function createDepartment(dept) {
        return `
            <div>
                <div class="flex items-center justify-between mb-1">
                    <span class="text-sm text-gray-700 font-medium">${dept.name}</span>
                    <span class="text-sm font-bold text-gray-900">${dept.count}</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-blue-600 h-2 rounded-full" style="width: ${dept.percentage}%"></div>
                </div>
            </div>
        `;
    }
</script>
