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
        <!-- Calendar Section -->
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                            <span class="iconify text-indigo-500" data-icon="mdi:calendar-month"></span>
                            Company Calendar
                        </h2>
                        <p class="mt-1 text-xs text-slate-500">A quick look at company events, leave, holidays, and schedules.</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <a id="openFullCalendarLink" href="?page=calendar"
                           class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2 text-xs font-black text-white shadow-sm shadow-indigo-100 transition hover:bg-indigo-700">
                            <span class="iconify" data-icon="mdi:calendar-open"></span>
                            Open Full Calendar
                        </a>
                        <span class="flex items-center gap-1 text-[10px] font-bold text-slate-500 uppercase tracking-wider">
                            <span class="w-2 h-2 rounded-full bg-emerald-400"></span> Approved
                        </span>
                        <span class="flex items-center gap-1 text-[10px] font-bold text-slate-500 uppercase tracking-wider">
                            <span class="w-2 h-2 rounded-full bg-amber-400"></span> Pending
                        </span>
                        <span class="flex items-center gap-1 text-[10px] font-bold text-slate-500 uppercase tracking-wider">
                            <span class="w-2 h-2 rounded-full bg-rose-400"></span> Rejected
                        </span>
                    </div>
                </div>
            </div>
            <div id="calendarContainer" class="min-h-[500px]">
                <div class="flex flex-col items-center justify-center py-24 text-slate-400">
                    <span class="iconify text-4xl mb-2 animate-spin" data-icon="mdi:loading"></span>
                    <p class="text-sm">Initializing calendar...</p>
                </div>
            </div>
        </div>

        <!-- Departments -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden flex flex-col">
            <div class="px-6 py-4 border-b border-slate-100">
                <h2 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                    <span class="iconify text-emerald-500" data-icon="mdi:office-building"></span>
                    Departments
                </h2>
            </div>
            <div id="departments" class="p-6 space-y-4 flex-grow min-h-0 max-h-[420px] overflow-y-auto no-scrollbar">
                <div class="flex flex-col items-center justify-center py-12 text-slate-400">
                    <span class="iconify text-4xl mb-2 animate-spin" data-icon="mdi:loading"></span>
                    <p class="text-sm">Loading departments...</p>
                </div>
            </div>

            <!-- Mini Recent Leaves in Sidebar -->
            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
                <h3 class="text-sm font-bold text-slate-700 mb-4">Recent Leave Requests</h3>
                <div id="miniLeaveRequests" class="space-y-3">
                    <p class="text-xs text-slate-400 text-center py-4">Loading...</p>
                </div>
                <a href="?page=leave" class="block text-center mt-4 text-xs font-bold text-indigo-600 hover:text-indigo-700 transition-colors">View All Applications</a>
            </div>
        </div>
    </div>
</div>

<style>
    .no-scrollbar::-webkit-scrollbar { display: none; }
    .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>

<script>
    /* ---------- SimpleCalendar Class ---------- */
    class SimpleCalendar {
        constructor(containerId) {
            this.container = document.getElementById(containerId);
            this.currentDate = new Date();
            this.events = [];
            this.isLoading = false;
        }

        async fetchEvents() {
            this.isLoading = true;
            const monthStr = this.currentDate.getFullYear() + '-' + String(this.currentDate.getMonth() + 1).padStart(2, '0');
            try {
                const res = await fetch(`/api/dashboard/calendar-events?month=${monthStr}`);
                const result = await res.json();
                this.events = result.success ? result.data : [];
            } catch (e) {
                console.error("Failed to fetch events", e);
                window.Toast?.error("Calendar Error", "Failed to load events.");
            } finally {
                this.isLoading = false;
            }
        }

        getEventClasses(event) {
            const eventType = (event.event_type || event.type || 'event').toLowerCase();
            const status = (event.status || 'pending').toLowerCase();

            const typeStyles = {
                holiday: 'bg-indigo-50 text-indigo-700 border-indigo-200',
                shift: 'bg-sky-50 text-sky-700 border-sky-200',
                leave: 'bg-amber-50 text-amber-700 border-amber-200',
                meeting: 'bg-violet-50 text-violet-700 border-violet-200',
                reminder: 'bg-emerald-50 text-emerald-700 border-emerald-200',
                task: 'bg-slate-50 text-slate-700 border-slate-200',
                event: 'bg-slate-50 text-slate-700 border-slate-200',
            };

            const statusBorder = {
                approved: 'ring-1 ring-emerald-100',
                pending: 'ring-1 ring-amber-100',
                rejected: 'ring-1 ring-rose-100',
                cancelled: 'ring-1 ring-slate-100',
            };

            return `${typeStyles[eventType] || typeStyles.event} ${statusBorder[status] || statusBorder.pending}`;
        }

        async render() {
            await this.fetchEvents();
            
            const year = this.currentDate.getFullYear();
            const month = this.currentDate.getMonth();
            const monthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
            const pad = (value) => String(value).padStart(2, '0');
            const fullCalendarLink = document.getElementById('openFullCalendarLink');
            if (fullCalendarLink) {
                fullCalendarLink.href = `?page=calendar&date=${year}-${pad(month + 1)}-01`;
            }
            
            const firstDay = new Date(year, month, 1).getDay();
            const daysInMonth = new Date(year, month + 1, 0).getDate();
            const today = new Date();
            const isCurrentMonth = today.getFullYear() === year && today.getMonth() === month;

            let html = `
                <div class="p-6">
                    <div class="flex justify-between items-center mb-6">
                        <div class="flex items-center gap-4">
                            <h3 class="text-xl font-black text-slate-800 tracking-tight">${monthNames[month]} ${year}</h3>
                            ${isCurrentMonth ? '<span class="px-2 py-0.5 bg-indigo-100 text-indigo-600 text-[10px] font-bold rounded-full uppercase">Current</span>' : ''}
                        </div>
                        <div class="flex gap-1">
                            <button id="calPrev" class="p-2 hover:bg-slate-100 rounded-xl transition-all text-slate-600">
                                <span class="iconify" data-icon="mdi:chevron-left" data-width="24"></span>
                            </button>
                            <button id="calToday" class="px-4 py-2 hover:bg-slate-100 rounded-xl transition-all text-xs font-bold text-slate-600">Today</button>
                            <button id="calNext" class="p-2 hover:bg-slate-100 rounded-xl transition-all text-slate-600">
                                <span class="iconify" data-icon="mdi:chevron-right" data-width="24"></span>
                            </button>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-7 gap-px bg-slate-100 border border-slate-100 rounded-2xl overflow-hidden shadow-sm">
                        ${['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'].map(d => 
                            `<div class="bg-slate-50/80 py-3 text-center text-[10px] font-black text-slate-400 uppercase tracking-widest">${d}</div>`
                        ).join('')}
                        ${this.generateDays(firstDay, daysInMonth)}
                    </div>
                </div>
            `;

            this.container.innerHTML = html;
            this.attachEvents();
        }

        generateDays(firstDay, daysInMonth) {
            let daysHtml = '';
            const today = new Date();
            const year = this.currentDate.getFullYear();
            const month = this.currentDate.getMonth();
            
            // Empty days from previous month
            for (let i = 0; i < firstDay; i++) {
                daysHtml += `<div class="bg-white/50 h-32 p-2"></div>`;
            }

            // Days of the month
            for (let d = 1; d <= daysInMonth; d++) {
                const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
                const dayEvents = this.events.filter(e => dateStr >= e.start && dateStr <= e.end);
                const isToday = today.getFullYear() === year && today.getMonth() === month && today.getDate() === d;
                
                daysHtml += `
                    <div class="bg-white h-32 p-2 relative group hover:bg-slate-50/80 transition-all border-transparent border-2 hover:border-indigo-100">
                        <div class="flex justify-between items-start mb-1">
                            <span class="text-sm font-bold ${isToday ? 'bg-indigo-600 text-white w-7 h-7 flex items-center justify-center rounded-lg shadow-lg shadow-indigo-200' : 'text-slate-400 group-hover:text-slate-600'} transition-colors">${d}</span>
                            ${dayEvents.length ? `<span class="text-[10px] font-black rounded-full px-2 py-0.5 bg-slate-100 text-slate-500">${dayEvents.length}</span>` : ''}
                        </div>
                        <div class="space-y-1 overflow-y-auto max-h-20 no-scrollbar pb-1">
                            ${dayEvents.map(e => {
                                const classes = this.getEventClasses(e);
                                const label = (e.event_type || e.type || 'event').toUpperCase();
                                const scope = e.scope_label ? ` • ${e.scope_label}` : '';

                                return `
                                    <div class="text-[9px] px-2 py-1 rounded-md border truncate font-bold shadow-sm ${classes}"
                                         title="${e.title} (${label} - ${e.status})${scope}">
                                        <span class="block truncate">${e.title}</span>
                                    </div>
                                `;
                            }).join('')}
                        </div>
                    </div>
                `;
            }

            // Fill remaining slots
            const totalSlots = firstDay + daysInMonth;
            const remaining = totalSlots > 35 ? 42 - totalSlots : 35 - totalSlots;
            for (let i = 0; i < remaining; i++) {
                daysHtml += `<div class="bg-white/50 h-32 p-2"></div>`;
            }

            return daysHtml;
        }

        attachEvents() {
            document.getElementById('calPrev').onclick = () => {
                this.currentDate.setMonth(this.currentDate.getMonth() - 1);
                this.render();
            };
            document.getElementById('calNext').onclick = () => {
                this.currentDate.setMonth(this.currentDate.getMonth() + 1);
                this.render();
            };
            document.getElementById('calToday').onclick = () => {
                this.currentDate = new Date();
                this.render();
            };
        }
    }

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

        function createMiniLeave(req) {
            const statusColors = {
                'pending': 'bg-amber-400',
                'approved': 'bg-emerald-400',
                'rejected': 'bg-rose-400'
            };
            const statusDot = statusColors[req.status?.toLowerCase()] || 'bg-slate-300';
            
            return `
            <div class="flex items-center gap-3 p-2 rounded-xl hover:bg-white hover:shadow-sm transition-all border border-transparent hover:border-slate-100 group">
                <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 font-bold text-xs">
                    ${(req.name || 'U').charAt(0)}
                </div>
                <div class="flex-grow min-w-0">
                    <div class="text-xs font-bold text-slate-800 truncate group-hover:text-indigo-600 transition-colors">${req.name || 'Unknown'}</div>
                    <div class="text-[10px] text-slate-500 truncate">${req.type || 'N/A'} • ${req.start_date_formatted || ''}</div>
                </div>
                <div class="w-2 h-2 rounded-full ${statusDot}"></div>
            </div>`;
        }

        function createDepartment(dept) {
            return `
            <div class="space-y-2">
                <div class="flex justify-between text-sm">
                    <span class="font-semibold text-slate-700">${dept.name || 'Unknown'}</span>
                    <span class="text-slate-500 font-bold">${dept.count || 0}</span>
                </div>
                <div class="w-full bg-slate-100 rounded-full h-1.5">
                    <div class="bg-indigo-500 h-1.5 rounded-full shadow-sm shadow-indigo-200" style="width: ${dept.percentage || 0}%"></div>
                </div>
            </div>`;
        }

        function loadDepartments() {
            const departmentsContainer = document.getElementById("departments");

            if (departmentsContainer) {
                departmentsContainer.innerHTML = `
                    <div class="flex flex-col items-center justify-center py-12 text-slate-400">
                        <span class="iconify text-4xl mb-2 animate-spin" data-icon="mdi:loading"></span>
                        <p class="text-sm">Loading departments...</p>
                    </div>`;
            }

            fetch("/api/dashboard/department")
                .then(res => res.json())
                .then(result => {
                    const data = result.success ? (result.data || []) : (Array.isArray(result) ? result : []);

                    if (!departmentsContainer) {
                        return;
                    }

                    departmentsContainer.scrollTop = 0;

                    if (!data.length) {
                        departmentsContainer.innerHTML = '<div class="text-center text-slate-400 py-12">No departments found</div>';
                        return;
                    }

                    departmentsContainer.innerHTML = data.map(d => createDepartment(d)).join('');
                })
                .catch(() => {
                    if (departmentsContainer) {
                        departmentsContainer.innerHTML = '<div class="text-center text-rose-400 py-12">Failed to load departments</div>';
                    }
                });
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

        /* ---------- Recent Leaves (Mini) ---------- */
        fetch("/api/dashboard/recent-leaves?limit=4")
            .then(res => res.json())
            .then(result => {
                const div = document.getElementById("miniLeaveRequests");
                const data = result.success ? result.data : (Array.isArray(result) ? result : []);
                
                if (data.length === 0) {
                    div.innerHTML = '<p class="text-[10px] text-slate-400 text-center py-4">No recent requests</p>';
                    return;
                }
                
                div.innerHTML = data.map(r => createMiniLeave(r)).join('');
            })
            .catch(() => {
                const div = document.getElementById("miniLeaveRequests");
                div.innerHTML = '<p class="text-[10px] text-rose-400 text-center py-4">Failed to load</p>';
            });

        /* ---------- Departments ---------- */
        loadDepartments();

        /* ---------- Initialize Calendar ---------- */
        const cal = new SimpleCalendar("calendarContainer");
        cal.render();
    });
</script>
