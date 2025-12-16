<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HR Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.iconify.design/iconify-icon/1.0.7/iconify-icon.min.js"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-4 bg-white p-4 rounded-lg shadow">HR Management Dashboard</h1>

        <!-- Stats Grid -->
        <div id="statsGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
            <!-- Stats will be loaded here -->
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Recent Leave Requests -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                    <iconify-icon icon="mdi:calendar-clock" width="24"></iconify-icon>
                    Recent Leave Requests
                </h2>
                <div id="leaveRequests" class="space-y-3">
                    <!-- Leave requests will be loaded here -->
                </div>
            </div>

            <!-- Departments -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                    <iconify-icon icon="mdi:office-building" width="24"></iconify-icon>
                    Departments
                </h2>
                <div id="departments" class="space-y-4">
                    <!-- Departments will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <script>
        // =======================
        // 1️⃣ FETCH SUMMARY API
        // =======================
        fetch("api/dashboard/summary.php")
            .then(res => res.json())
            .then(result => {
                if (!result.success) return;

                const data = result.data;
                const statsGrid = document.getElementById("statsGrid");

                statsGrid.innerHTML = `
                    ${createStat("mdi:account-multiple", data.total_employees, "Total Employees", "blue")}
                    ${createStat("mdi:check-circle", data.active_employees, "Active Users", "green")}
                    ${createStat("mdi:clock-outline", data.pending_leaves, "Pending Leaves", "orange")}
                    ${createStat("mdi:calendar-blank", data.on_leave_today, "On Leave Today", "purple")}
                `;
            })
            .catch(err => console.error("Summary Error:", err));


        // ============================
        // 2️⃣ FETCH RECENT LEAVES API
        // ============================
        fetch("api/dashboard/recent_leaves.php")
            .then(res => res.json())
            .then(result => {
                if (!result.success) return;

                const leaves = result.data;
                const leaveDiv = document.getElementById("leaveRequests");
                leaveDiv.innerHTML = "";

                leaves.forEach(req => {
                    leaveDiv.innerHTML += createLeaveRequest(req);
                });
            })
            .catch(err => console.error("Recent Leaves Error:", err));


        // ===============================
        // 3️⃣ FETCH DEPARTMENTS API
        // ===============================
        fetch("api/dashboard/departments.php")
            .then(res => res.json())
            .then(result => {
                if (!result.success) return;

                const deptList = result.data;
                const deptDiv = document.getElementById("departments");
                deptDiv.innerHTML = "";

                deptList.forEach(dept => {
                    deptDiv.innerHTML += createDepartment(dept);
                });
            })
            .catch(err => console.error("Departments Error:", err));



        // -----------------------------
        // Helper Functions
        // -----------------------------

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
                            <p class="text-xs text-gray-500">${req.type} • ${req.period}</p>
                        </div>
                    </div>
                    <span class="text-xs font-semibold px-2 py-1 bg-${statusColor}-100 text-${statusColor}-800 rounded-full">
                        ${req.status}
                    </span>
                </div>
            `;
        }

        function getStatusColor(status) {
            switch (status.toLowerCase()) {
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
</body>
</html>