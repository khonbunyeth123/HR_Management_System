<div class="w-full max-w-7xl mx-auto space-y-6">
    <!-- Stats Grid -->
    <div id="statsGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Loading placeholders -->
        <?php for($i=0; $i<4; $i++): ?>
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 animate-pulse">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-slate-100 rounded-xl"></div>
                <div class="space-y-2">
                    <div class="h-4 bg-slate-100 rounded w-20"></div>
                    <div class="h-6 bg-slate-100 rounded w-12"></div>
                </div>
            </div>
        </div>
        <?php endfor; ?>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Recent Leave Requests -->
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center">
                <h2 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                    <span class="iconify text-indigo-500" data-icon="mdi:calendar-clock"></span>
                    Recent Leave Requests
                </h2>
                <a href="?page=leave" class="text-xs font-semibold text-indigo-600 hover:underline">View All</a>
            </div>
            <div id="leaveRequests" class="p-6">
                <div class="flex flex-col items-center justify-center py-12 text-slate-400">
                    <span class="iconify text-4xl mb-2" data-icon="mdi:loading" data-inline="false"></span>
                    <p class="text-sm">Loading requests...</p>
                </div>
            </div>
        </div>

        <!-- Departments -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100">
                <h2 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                    <span class="iconify text-emerald-500" data-icon="mdi:office-building"></span>
                    Departments
                </h2>
            </div>
            <div id="departments" class="p-6 space-y-4">
                <div class="flex flex-col items-center justify-center py-12 text-slate-400">
                    <span class="iconify text-4xl mb-2" data-icon="mdi:loading" data-inline="false"></span>
                    <p class="text-sm">Loading departments...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", () => {

        /* ---------- Helpers ---------- */

        function createStat(icon, value, label, colorClass, bgColor) {
            return `
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex items-center gap-4 hover:shadow-md transition-all duration-300">
                <div class="w-12 h-12 ${bgColor} rounded-xl flex items-center justify-center">
                    <span class="iconify text-2xl ${colorClass}" data-icon="${icon}"></span>
                </div>
                <div>
                    <div class="text-sm font-medium text-slate-500">${label}</div>
                    <div class="text-2xl font-bold text-slate-900">${value}</div>
                </div>
            </div>`;
        }

        function createLeaveRequest(req) {
            const statusColors = {
                'pending': 'bg-amber-50 text-amber-600',
                'approved': 'bg-emerald-50 text-emerald-600',
                'rejected': 'bg-rose-50 text-rose-600'
            };
            const statusClass = statusColors[req.status?.toLowerCase()] || 'bg-slate-50 text-slate-500';
            
            return `
            <div class="flex items-center justify-between p-4 rounded-xl border border-slate-50 hover:bg-slate-50 transition-all group">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-full bg-indigo-50 flex items-center justify-center text-indigo-600 font-bold">
                        ${(req.name || 'U').charAt(0)}
                    </div>
                    <div>
                        <div class="font-bold text-slate-900 group-hover:text-indigo-600 transition-colors">${req.name || 'Unknown'}</div>
                        <div class="text-xs text-slate-500">${req.type || 'N/A'} • ${req.period || ''}</div>
                    </div>
                </div>
                <span class="px-3 py-1 text-[10px] font-bold uppercase tracking-wider rounded-full ${statusClass}">${req.status || 'N/A'}</span>
            </div>`;
        }

        function createDepartment(dept) {
            return `
            <div class="space-y-2">
                <div class="flex justify-between text-sm">
                    <span class="font-semibold text-slate-700">${dept.name || 'Unknown'}</span>
                    <span class="text-slate-500">${dept.count || 0}</span>
                </div>
                <div class="w-full bg-slate-100 rounded-full h-1.5">
                    <div class="bg-indigo-500 h-1.5 rounded-full" style="width: ${dept.percentage || 0}%"></div>
                </div>
            </div>`;
        }

        /* ---------- Dashboard Summary ---------- */
        fetch("/api/dashboard/summary")
            .then(res => res.json())
            .then(result => {
                const data = result.success ? result.data : result;
                if (!data || result.success === false) throw new Error("API error");
                
                const statsGrid = document.getElementById("statsGrid");
                statsGrid.innerHTML = `
                    ${createStat("mdi:account-multiple", data.total_employees || 0, "Employees", "text-blue-500", "bg-blue-50")}
                    ${createStat("mdi:account-check", data.active_employees || 0, "Active", "text-emerald-500", "bg-emerald-50")}
                    ${createStat("mdi:clock-alert", data.pending_leaves || 0, "Pending", "text-amber-500", "bg-amber-50")}
                    ${createStat("mdi:calendar-remove", data.on_leave_today || 0, "On Leave", "text-rose-500", "bg-rose-50")}
                `;
            })
            .catch(error => {
                console.error(error);
                window.Toast?.error("Dashboard Error", "Failed to load summary statistics.");
            });

        /* ---------- Recent Leaves ---------- */
        fetch("/api/dashboard/recent-leaves")
            .then(res => res.json())
            .then(result => {
                const div = document.getElementById("leaveRequests");
                const data = result.success ? result.data : (Array.isArray(result) ? result : []);
                
                if (data.length === 0) {
                    div.innerHTML = '<div class="text-center text-slate-400 py-12">No recent leave requests</div>';
                    return;
                }
                
                div.innerHTML = `<div class="space-y-2">${data.map(r => createLeaveRequest(r)).join('')}</div>`;
            })
            .catch(() => window.Toast?.error("Error", "Failed to load leave requests."));

        /* ---------- Departments ---------- */
        fetch("/api/dashboard/department")
            .then(res => res.json())
            .then(result => {
                const div = document.getElementById("departments");
                const data = result.success ? result.data : (Array.isArray(result) ? result : []);
                
                if (data.length === 0) {
                    div.innerHTML = '<div class="text-center text-slate-400 py-12">No department data found</div>';
                    return;
                }
                
                div.innerHTML = data.map(d => createDepartment(d)).join('');
            })
            .catch(() => window.Toast?.error("Error", "Failed to load department data."));
    });
</script>
