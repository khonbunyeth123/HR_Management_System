<?php
$requestedDate = $_GET['date'] ?? date('Y-m-d');
$today = preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $requestedDate)
    ? (string) $requestedDate
    : date('Y-m-d');
?>
<script>
    window.__calendarInitialDate = <?= json_encode($today) ?>;
</script>
<div class="w-full h-full">
    <div class="p-3 lg:p-4 space-y-4">
        <div class="rounded-3xl bg-gradient-to-r from-slate-950 via-slate-900 to-indigo-950 px-5 py-5 text-white shadow-xl shadow-slate-200/60">
            <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
                <div class="max-w-3xl">
                    <div class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-3 py-1 text-[10px] font-black uppercase tracking-[0.25em] text-indigo-200">
                        <span class="iconify text-sm" data-icon="mdi:calendar-multiselect"></span>
                        HRM Admin Calendar
                    </div>
                    <h1 class="mt-3 text-2xl font-black tracking-tight sm:text-3xl">Plan schedules, leave approvals, and reminders in one place</h1>
                    <p class="mt-2 max-w-2xl text-sm text-slate-300">
                        Manage company-wide events, employee-specific schedules, leave requests, and recurring calendar items with a single admin dashboard.
                    </p>
                </div>

                <div class="flex flex-wrap gap-2">
                    <button data-view-switch="month" class="view-switch rounded-2xl bg-white px-4 py-2 text-sm font-bold text-slate-900 shadow-lg shadow-slate-950/20">Month</button>
                    <button data-view-switch="week" class="view-switch rounded-2xl border border-white/10 bg-white/10 px-4 py-2 text-sm font-bold text-white transition hover:bg-white/15">Week</button>
                    <button data-view-switch="day" class="view-switch rounded-2xl border border-white/10 bg-white/10 px-4 py-2 text-sm font-bold text-white transition hover:bg-white/15">Day</button>
                    <button id="openCreateEvent" class="rounded-2xl bg-indigo-500 px-4 py-2 text-sm font-black text-white shadow-lg shadow-indigo-500/30 transition hover:bg-indigo-400">
                        + New Event
                    </button>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 xl:grid-cols-[280px_minmax(0,1fr)_360px]">
            <aside class="space-y-4">
                <div class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm">
                    <div class="flex items-center justify-between">
                        <h2 class="text-sm font-black uppercase tracking-[0.2em] text-slate-500">Filters</h2>
                        <button id="clearFilters" class="text-xs font-bold text-indigo-600 hover:text-indigo-700">Reset</button>
                    </div>
                    <div class="mt-4 space-y-3">
                        <div>
                            <label class="mb-1 block text-xs font-bold text-slate-500">Employee</label>
                            <select id="employeeFilter" class="w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm">
                                <option value="">All employees</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-bold text-slate-500">Department</label>
                            <select id="departmentFilter" class="w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm">
                                <option value="">All departments</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-bold text-slate-500">Branch</label>
                            <select id="branchFilter" class="w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm">
                                <option value="">All branches</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-bold text-slate-500">Event Type</label>
                            <select id="eventTypeFilter" class="w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm">
                                <option value="">All types</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-bold text-slate-500">Status</label>
                            <select id="statusFilter" class="w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm">
                                <option value="">All status</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="rounded-3xl border border-slate-200 bg-slate-950 p-4 text-white shadow-sm">
                    <h3 class="text-sm font-black uppercase tracking-[0.2em] text-slate-400">Quick Actions</h3>
                    <div class="mt-4 space-y-3 text-sm">
                        <button id="jumpToday" class="flex w-full items-center justify-between rounded-2xl bg-white/10 px-4 py-3 font-bold transition hover:bg-white/15">
                            <span>Jump to today</span>
                            <span class="iconify" data-icon="mdi:calendar-today"></span>
                        </button>
                        <button id="reloadCalendar" class="flex w-full items-center justify-between rounded-2xl bg-indigo-500 px-4 py-3 font-black transition hover:bg-indigo-400">
                            <span>Refresh data</span>
                            <span class="iconify" data-icon="mdi:refresh"></span>
                        </button>
                    </div>
                    <div class="mt-4 rounded-2xl border border-white/10 bg-white/5 p-4">
                        <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Current date</p>
                        <p id="currentFocusDate" class="mt-2 text-2xl font-black"><?= htmlspecialchars($today) ?></p>
                        <p class="mt-1 text-xs text-slate-300">Use the view controls to switch between month, week, and day schedules.</p>
                    </div>
                </div>
            </aside>

            <section class="min-w-0 space-y-4">
                <div class="rounded-3xl border border-slate-200 bg-white shadow-sm">
                    <div class="flex flex-col gap-3 border-b border-slate-100 px-5 py-4 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <p class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Calendar Panel</p>
                            <h2 id="calendarHeading" class="mt-1 text-xl font-black text-slate-900">Month view</h2>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <button id="prevPeriod" class="rounded-2xl border border-slate-200 px-3 py-2 text-sm font-bold text-slate-700 transition hover:bg-slate-50">
                                <span class="iconify" data-icon="mdi:chevron-left"></span>
                            </button>
                            <button id="todayPeriod" class="rounded-2xl border border-slate-200 px-4 py-2 text-sm font-bold text-slate-700 transition hover:bg-slate-50">Today</button>
                            <button id="nextPeriod" class="rounded-2xl border border-slate-200 px-3 py-2 text-sm font-bold text-slate-700 transition hover:bg-slate-50">
                                <span class="iconify" data-icon="mdi:chevron-right"></span>
                            </button>
                            <input id="dateJump" type="date" value="<?= htmlspecialchars($today) ?>" class="rounded-2xl border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700">
                        </div>
                    </div>

                    <div id="calendarPanel" class="min-h-[640px] p-4 lg:p-5">
                        <div class="flex h-full items-center justify-center rounded-3xl border border-dashed border-slate-200 bg-slate-50 py-24 text-slate-400">
                            <div class="text-center">
                                <span class="iconify mx-auto text-4xl" data-icon="mdi:loading"></span>
                                <p class="mt-2 text-sm font-semibold">Loading calendar...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <aside class="space-y-4">
                <div class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm">
                    <div class="flex items-center justify-between">
                        <h2 class="text-sm font-black uppercase tracking-[0.2em] text-slate-500">Live Summary</h2>
                        <span id="eventCountBadge" class="rounded-full bg-indigo-100 px-3 py-1 text-xs font-black text-indigo-700">0 events</span>
                    </div>
                    <div id="summaryGrid" class="mt-4 grid grid-cols-2 gap-3">
                        <div class="rounded-2xl bg-slate-50 p-3">
                            <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Pending</p>
                            <p id="summaryPending" class="mt-2 text-2xl font-black text-slate-900">0</p>
                        </div>
                        <div class="rounded-2xl bg-slate-50 p-3">
                            <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Approved</p>
                            <p id="summaryApproved" class="mt-2 text-2xl font-black text-slate-900">0</p>
                        </div>
                        <div class="rounded-2xl bg-slate-50 p-3">
                            <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Rejected</p>
                            <p id="summaryRejected" class="mt-2 text-2xl font-black text-slate-900">0</p>
                        </div>
                        <div class="rounded-2xl bg-slate-50 p-3">
                            <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Cancelled</p>
                            <p id="summaryCancelled" class="mt-2 text-2xl font-black text-slate-900">0</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-3xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-100 px-4 py-4">
                        <h2 class="text-sm font-black uppercase tracking-[0.2em] text-slate-500">Event Table</h2>
                    </div>
                    <div class="max-h-[640px] overflow-auto">
                        <table class="min-w-full text-left text-sm">
                            <thead class="sticky top-0 bg-slate-900 text-white">
                                <tr>
                                    <th class="px-4 py-3 text-xs font-black uppercase tracking-[0.15em]">Time</th>
                                    <th class="px-4 py-3 text-xs font-black uppercase tracking-[0.15em]">Event</th>
                                    <th class="px-4 py-3 text-xs font-black uppercase tracking-[0.15em]">Status</th>
                                    <th class="px-4 py-3 text-xs font-black uppercase tracking-[0.15em]">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="eventTableBody" class="divide-y divide-slate-100 bg-white">
                                <tr>
                                    <td colspan="4" class="px-4 py-10 text-center text-slate-400">Loading events...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</div>

<div id="eventModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/55 p-4">
    <div class="w-full max-w-4xl overflow-hidden rounded-[28px] bg-white shadow-2xl">
        <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.25em] text-slate-400">Calendar Form</p>
                <h3 id="modalTitle" class="mt-1 text-xl font-black text-slate-900">New Event</h3>
            </div>
            <button id="closeEventModal" class="rounded-2xl border border-slate-200 px-3 py-2 text-slate-500 transition hover:bg-slate-50">
                <span class="iconify" data-icon="mdi:close"></span>
            </button>
        </div>

        <form id="eventForm" class="grid gap-0 lg:grid-cols-[1.2fr_0.8fr]">
            <input type="hidden" id="eventUuid">
            <div class="space-y-4 p-5">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label class="mb-1 block text-xs font-bold uppercase tracking-[0.2em] text-slate-400">Title</label>
                        <input id="title" required class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm font-semibold" placeholder="Quarterly planning meeting">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-[0.2em] text-slate-400">Event Type</label>
                        <select id="eventType" required class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm font-semibold">
                            <option value="">Choose type</option>
                            <option value="holiday">Holiday</option>
                            <option value="shift">Shift</option>
                            <option value="leave">Leave</option>
                            <option value="meeting">Meeting</option>
                            <option value="reminder">Reminder</option>
                            <option value="task">Task</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-[0.2em] text-slate-400">Status</label>
                        <select id="status" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm font-semibold">
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-[0.2em] text-slate-400">Start</label>
                        <input id="startAt" type="datetime-local" required class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm font-semibold">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-[0.2em] text-slate-400">End</label>
                        <input id="endAt" type="datetime-local" required class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm font-semibold">
                    </div>
                    <div class="md:col-span-2">
                        <label class="mb-1 block text-xs font-bold uppercase tracking-[0.2em] text-slate-400">Description</label>
                        <textarea id="description" rows="4" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm font-semibold" placeholder="Notes for the team..."></textarea>
                    </div>
                </div>

                <div class="rounded-3xl border border-slate-100 bg-slate-50 p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-black text-slate-900">Assignments</p>
                            <p class="text-xs text-slate-500">Assign to company-wide, employees, departments, branches, or teams.</p>
                        </div>
                        <button type="button" id="addTargetBtn" class="rounded-2xl bg-white px-3 py-2 text-xs font-black text-indigo-600 shadow-sm">Add target</button>
                    </div>
                    <div id="targetsWrap" class="mt-4 space-y-3"></div>
                </div>
            </div>

            <div class="space-y-4 border-t border-slate-100 bg-slate-50/60 p-5 lg:border-l lg:border-t-0">
                <div class="rounded-3xl bg-white p-4 shadow-sm">
                    <p class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Recurrence</p>
                    <div class="mt-3 space-y-3">
                        <div>
                            <label class="mb-1 block text-xs font-bold text-slate-500">Frequency</label>
                            <select id="recurrenceFrequency" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm font-semibold">
                                <option value="none">No recurrence</option>
                                <option value="daily">Daily</option>
                                <option value="weekly">Weekly</option>
                                <option value="monthly">Monthly</option>
                                <option value="yearly">Yearly</option>
                            </select>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="mb-1 block text-xs font-bold text-slate-500">Interval</label>
                                <input id="recurrenceInterval" type="number" min="1" value="1" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm font-semibold">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-bold text-slate-500">Until</label>
                                <input id="recurrenceUntil" type="date" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm font-semibold">
                            </div>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-bold text-slate-500">Weekly days</label>
                            <div class="grid grid-cols-7 gap-2 text-[11px] font-black uppercase tracking-[0.15em] text-slate-500">
                                <button type="button" data-weekday="mon" class="weekday-btn rounded-2xl border border-slate-200 px-2 py-2">Mon</button>
                                <button type="button" data-weekday="tue" class="weekday-btn rounded-2xl border border-slate-200 px-2 py-2">Tue</button>
                                <button type="button" data-weekday="wed" class="weekday-btn rounded-2xl border border-slate-200 px-2 py-2">Wed</button>
                                <button type="button" data-weekday="thu" class="weekday-btn rounded-2xl border border-slate-200 px-2 py-2">Thu</button>
                                <button type="button" data-weekday="fri" class="weekday-btn rounded-2xl border border-slate-200 px-2 py-2">Fri</button>
                                <button type="button" data-weekday="sat" class="weekday-btn rounded-2xl border border-slate-200 px-2 py-2">Sat</button>
                                <button type="button" data-weekday="sun" class="weekday-btn rounded-2xl border border-slate-200 px-2 py-2">Sun</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="rounded-3xl bg-white p-4 shadow-sm">
                    <p class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Event Actions</p>
                    <div class="mt-3 space-y-2">
                        <button type="submit" class="w-full rounded-2xl bg-slate-950 px-4 py-3 text-sm font-black text-white transition hover:bg-slate-800">Save Event</button>
                        <button type="button" id="deleteEventBtn" class="hidden w-full rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-black text-rose-600 transition hover:bg-rose-100">Delete Event</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<template id="targetRowTemplate">
    <div class="target-row grid gap-2 rounded-2xl border border-slate-200 bg-white p-3 md:grid-cols-[160px_minmax(0,1fr)_auto]">
        <select class="target-type rounded-xl border border-slate-200 px-3 py-2 text-sm font-semibold">
            <option value="company">Company-wide</option>
            <option value="employee">Employee</option>
            <option value="department">Department</option>
            <option value="branch">Branch</option>
            <option value="team">Team</option>
        </select>
        <input class="target-value rounded-xl border border-slate-200 px-3 py-2 text-sm font-semibold" placeholder="Target value">
        <button type="button" class="remove-target rounded-xl border border-slate-200 px-3 py-2 text-sm font-bold text-rose-600">Remove</button>
    </div>
</template>

<script>
(() => {
    const state = {
        view: 'month',
        current: new Date(`${window.__calendarInitialDate || '<?= $today ?>'}T12:00:00`),
        filters: {
            employee_id: '',
            department: '',
            branch: '',
            event_type: '',
            status: '',
        },
        events: [],
        options: {
            employees: [],
            departments: [],
            branches: [],
            event_types: [],
            statuses: [],
        },
        selectedWeekdays: new Set(),
        editing: null,
    };

    const els = {
        calendarPanel: document.getElementById('calendarPanel'),
        calendarHeading: document.getElementById('calendarHeading'),
        eventTableBody: document.getElementById('eventTableBody'),
        summaryPending: document.getElementById('summaryPending'),
        summaryApproved: document.getElementById('summaryApproved'),
        summaryRejected: document.getElementById('summaryRejected'),
        summaryCancelled: document.getElementById('summaryCancelled'),
        eventCountBadge: document.getElementById('eventCountBadge'),
        employeeFilter: document.getElementById('employeeFilter'),
        departmentFilter: document.getElementById('departmentFilter'),
        branchFilter: document.getElementById('branchFilter'),
        eventTypeFilter: document.getElementById('eventTypeFilter'),
        statusFilter: document.getElementById('statusFilter'),
        dateJump: document.getElementById('dateJump'),
        currentFocusDate: document.getElementById('currentFocusDate'),
        modal: document.getElementById('eventModal'),
        form: document.getElementById('eventForm'),
        modalTitle: document.getElementById('modalTitle'),
        eventUuid: document.getElementById('eventUuid'),
        title: document.getElementById('title'),
        eventType: document.getElementById('eventType'),
        status: document.getElementById('status'),
        startAt: document.getElementById('startAt'),
        endAt: document.getElementById('endAt'),
        description: document.getElementById('description'),
        recurrenceFrequency: document.getElementById('recurrenceFrequency'),
        recurrenceInterval: document.getElementById('recurrenceInterval'),
        recurrenceUntil: document.getElementById('recurrenceUntil'),
        targetsWrap: document.getElementById('targetsWrap'),
        targetRowTemplate: document.getElementById('targetRowTemplate'),
    };

    function toLocalInputValue(value) {
        if (!value) return '';
        const dt = parseApiDate(value);
        if (Number.isNaN(dt.getTime())) return '';
        const pad = (n) => String(n).padStart(2, '0');
        return `${dt.getFullYear()}-${pad(dt.getMonth()+1)}-${pad(dt.getDate())}T${pad(dt.getHours())}:${pad(dt.getMinutes())}`;
    }

    function parseApiDate(value) {
        if (!value) return new Date(NaN);
        return new Date(String(value).replace(' ', 'T'));
    }

    function localDateKey(date) {
        const pad = (n) => String(n).padStart(2, '0');
        return `${date.getFullYear()}-${pad(date.getMonth()+1)}-${pad(date.getDate())}`;
    }

    function toDateOnly(value) {
        return value ? String(value).slice(0, 10) : '';
    }

    function formatTimeRange(event) {
        if (event.all_day) {
            return 'All day';
        }
        const start = parseApiDate(event.start);
        const end = parseApiDate(event.end);
        return `${start.toLocaleTimeString([], {hour: '2-digit', minute: '2-digit'})} - ${end.toLocaleTimeString([], {hour: '2-digit', minute: '2-digit'})}`;
    }

    function badge(status) {
        const map = {
            pending: 'bg-amber-100 text-amber-700',
            approved: 'bg-emerald-100 text-emerald-700',
            rejected: 'bg-rose-100 text-rose-700',
            cancelled: 'bg-slate-100 text-slate-700',
        };
        return `<span class="inline-flex rounded-full px-2 py-1 text-[11px] font-black ${map[status] || map.cancelled}">${status}</span>`;
    }

    function typeBadge(type) {
        const map = {
            holiday: 'bg-indigo-100 text-indigo-700',
            shift: 'bg-sky-100 text-sky-700',
            leave: 'bg-amber-100 text-amber-700',
            meeting: 'bg-violet-100 text-violet-700',
            reminder: 'bg-emerald-100 text-emerald-700',
            task: 'bg-slate-100 text-slate-700',
        };
        return `<span class="inline-flex rounded-full px-2 py-1 text-[11px] font-black ${map[type] || map.task}">${type}</span>`;
    }

    function monthName(date) {
        return date.toLocaleDateString([], { month: 'long', year: 'numeric' });
    }

    function weekRange(date) {
        const start = new Date(date);
        const day = (start.getDay() + 6) % 7;
        start.setDate(start.getDate() - day);
        const end = new Date(start);
        end.setDate(start.getDate() + 6);
        return `${start.toLocaleDateString([], {month: 'short', day: 'numeric'})} - ${end.toLocaleDateString([], {month: 'short', day: 'numeric', year: 'numeric'})}`;
    }

    function dayLabel(date) {
        return date.toLocaleDateString([], { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' });
    }

    function getRange() {
        const date = new Date(state.current);
        const start = new Date(date);
        const end = new Date(date);

        if (state.view === 'month') {
            start.setDate(1);
            end.setMonth(end.getMonth() + 1, 0);
        } else if (state.view === 'week') {
            const day = (date.getDay() + 6) % 7;
            start.setDate(date.getDate() - day);
            end.setDate(start.getDate() + 6);
        } else {
            start.setHours(0, 0, 0, 0);
            end.setHours(23, 59, 59, 999);
        }

        const pad = (n) => String(n).padStart(2, '0');
        return {
            start: `${start.getFullYear()}-${pad(start.getMonth()+1)}-${pad(start.getDate())}`,
            end: `${end.getFullYear()}-${pad(end.getMonth()+1)}-${pad(end.getDate())}`,
        };
    }

    async function loadOptions() {
        const res = await fetch('/api/calendar/filters');
        const result = await res.json();
        if (!result.success) return;
        state.options = result.data || state.options;

        els.employeeFilter.innerHTML = '<option value="">All employees</option>' + (state.options.employees || []).map(e => `<option value="${e.id}">${escapeHtml(e.full_name)}${e.department ? ` - ${escapeHtml(e.department)}` : ''}</option>`).join('');
        els.departmentFilter.innerHTML = '<option value="">All departments</option>' + (state.options.departments || []).map(v => `<option value="${escapeAttr(v)}">${escapeHtml(v)}</option>`).join('');
        els.branchFilter.innerHTML = '<option value="">All branches</option>' + (state.options.branches || []).map(v => `<option value="${escapeAttr(v)}">${escapeHtml(v)}</option>`).join('');
        els.eventTypeFilter.innerHTML = '<option value="">All types</option>' + (state.options.event_types || []).map(v => `<option value="${v}">${v}</option>`).join('');
        els.statusFilter.innerHTML = '<option value="">All status</option>' + (state.options.statuses || []).map(v => `<option value="${v}">${v}</option>`).join('');
    }

    async function loadEvents() {
        const range = getRange();
        const params = new URLSearchParams({
            start: range.start,
            end: range.end,
            employee_id: state.filters.employee_id,
            department: state.filters.department,
            branch: state.filters.branch,
            event_type: state.filters.event_type,
            status: state.filters.status,
        });
        const res = await fetch(`/api/calendar/events?${params.toString()}`);
        const result = await res.json();
        if (!result.success) {
            throw new Error(result.message || 'Unable to load calendar');
        }
        state.events = result.data?.events || [];
        renderSummary(result.data?.summary || {});
        renderCalendar();
        renderTable();
    }

    function renderSummary(summary) {
        els.eventCountBadge.textContent = `${summary.total || 0} events`;
        els.summaryPending.textContent = summary.pending || 0;
        els.summaryApproved.textContent = summary.approved || 0;
        els.summaryRejected.textContent = summary.rejected || 0;
        els.summaryCancelled.textContent = summary.cancelled || 0;
    }

    function groupedByDate() {
        const groups = {};
        state.events.forEach(event => {
            const key = String(event.start || '').slice(0, 10);
            if (!groups[key]) groups[key] = [];
            groups[key].push(event);
        });
        return groups;
    }

    function renderCalendar() {
        const date = new Date(state.current);
        els.currentFocusDate.textContent = localDateKey(date);
        if (state.view === 'month') {
            els.calendarHeading.textContent = monthName(date);
            renderMonthView();
        } else if (state.view === 'week') {
            els.calendarHeading.textContent = `Week view • ${weekRange(date)}`;
            renderWeekView();
        } else {
            els.calendarHeading.textContent = `Day view • ${dayLabel(date)}`;
            renderDayView();
        }
    }

    function renderMonthView() {
        const date = new Date(state.current);
        const year = date.getFullYear();
        const month = date.getMonth();
        const first = new Date(year, month, 1);
        const startOffset = (first.getDay() + 6) % 7;
        const daysInMonth = new Date(year, month + 1, 0).getDate();
        const groups = groupedByDate();

        let html = `
            <div class="grid grid-cols-7 gap-px overflow-hidden rounded-[24px] border border-slate-100 bg-slate-100">
                ${['Mon','Tue','Wed','Thu','Fri','Sat','Sun'].map(day => `<div class="bg-slate-50 px-2 py-3 text-center text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">${day}</div>`).join('')}
        `;

        const totalCells = 42;
        for (let cell = 0; cell < totalCells; cell++) {
            const dayNumber = cell - startOffset + 1;
            const inMonth = dayNumber > 0 && dayNumber <= daysInMonth;
            const cellDate = new Date(year, month, dayNumber);
            const key = localDateKey(cellDate);
            const items = groups[key] || [];
            const isToday = key === localDateKey(new Date());

            html += `
                <div class="min-h-[120px] bg-white p-2 transition hover:bg-slate-50 ${inMonth ? '' : 'opacity-50'}">
                    <div class="mb-2 flex items-center justify-between">
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-xl text-sm font-black ${isToday ? 'bg-slate-950 text-white' : 'text-slate-700'}">${inMonth ? dayNumber : ''}</span>
                    </div>
                    <div class="space-y-1">
                        ${items.slice(0, 3).map(renderChip).join('')}
                        ${items.length > 3 ? `<button class="text-[11px] font-black text-indigo-600" onclick="window.__calendarJump('${key}')">+${items.length - 3} more</button>` : ''}
                    </div>
                </div>
            `;
        }

        html += '</div>';
        els.calendarPanel.innerHTML = html;
    }

    function renderWeekView() {
        const date = new Date(state.current);
        const day = (date.getDay() + 6) % 7;
        const start = new Date(date);
        start.setDate(date.getDate() - day);
        const groups = groupedByDate();
        const days = Array.from({length: 7}, (_, i) => {
            const d = new Date(start);
            d.setDate(start.getDate() + i);
            return d;
        });

        els.calendarPanel.innerHTML = `
            <div class="grid grid-cols-1 gap-3 xl:grid-cols-7">
                ${days.map(dayDate => {
                    const key = localDateKey(dayDate);
                    const items = groups[key] || [];
                    return `
                        <div class="rounded-[22px] border border-slate-100 bg-slate-50/60 p-3">
                            <div class="mb-3 flex items-center justify-between">
                                <div>
                                    <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">${dayDate.toLocaleDateString([], { weekday: 'short' })}</p>
                                    <p class="mt-1 text-lg font-black text-slate-900">${dayDate.getDate()}</p>
                                </div>
                                <button class="rounded-xl bg-white px-3 py-2 text-xs font-black text-indigo-600 shadow-sm" onclick="window.__calendarJump('${key}')">Open</button>
                            </div>
                            <div class="space-y-2">
                                ${items.length ? items.map(renderBlock).join('') : '<div class="rounded-2xl border border-dashed border-slate-200 bg-white px-3 py-8 text-center text-xs font-semibold text-slate-400">No events</div>'}
                            </div>
                        </div>
                    `;
                }).join('')}
            </div>
        `;
    }

    function renderDayView() {
        const key = localDateKey(state.current);
        const items = groupedByDate()[key] || [];
        els.calendarPanel.innerHTML = `
            <div class="rounded-[26px] border border-slate-100 bg-slate-50 p-4">
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Agenda</p>
                        <h3 class="mt-1 text-2xl font-black text-slate-900">${dayLabel(state.current)}</h3>
                    </div>
                    <button class="rounded-2xl bg-white px-4 py-2 text-sm font-black text-indigo-600 shadow-sm" onclick="openEventModal()">Create</button>
                </div>
                <div class="space-y-3">
                    ${items.length ? items.map(event => `
                        <div class="rounded-[22px] border border-slate-100 bg-white p-4 shadow-sm">
                            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                                <div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        ${typeBadge(event.event_type)}
                                        ${badge(event.status)}
                                    </div>
                                    <h4 class="mt-2 text-lg font-black text-slate-900">${escapeHtml(event.title)}</h4>
                                    <p class="mt-1 text-sm text-slate-500">${escapeHtml(event.description || '')}</p>
                                    <p class="mt-2 text-xs font-bold uppercase tracking-[0.15em] text-slate-400">${formatTimeRange(event)}${event.scope?.label ? ` • ${escapeHtml(event.scope.label)}` : ''}</p>
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    ${actionButtons(event)}
                                </div>
                            </div>
                        </div>
                    `).join('') : '<div class="rounded-[22px] border border-dashed border-slate-200 bg-white px-4 py-16 text-center text-sm font-semibold text-slate-400">No events on this date.</div>'}
                </div>
            </div>
        `;
    }

    function renderChip(event) {
        return `
            <button type="button" class="w-full rounded-xl border border-slate-200 px-2 py-1 text-left text-[11px] font-bold ${colorClass(event)}" onclick="openEventFromView('${event.uuid}', '${event.source_type}')">
                <span class="block truncate">${escapeHtml(event.title)}</span>
            </button>
        `;
    }

    function renderBlock(event) {
        return `
            <div class="rounded-2xl border border-slate-100 bg-white p-3 shadow-sm">
                <div class="flex items-center gap-2">${typeBadge(event.event_type)}${badge(event.status)}</div>
                <p class="mt-2 truncate text-sm font-black text-slate-900">${escapeHtml(event.title)}</p>
                <p class="mt-1 text-[11px] font-semibold text-slate-500">${formatTimeRange(event)}</p>
                <div class="mt-3 flex gap-2">${actionButtons(event, true)}</div>
            </div>
        `;
    }

    function colorClass(event) {
        const map = {
            holiday: 'bg-indigo-50 text-indigo-700',
            shift: 'bg-sky-50 text-sky-700',
            leave: 'bg-amber-50 text-amber-700',
            meeting: 'bg-violet-50 text-violet-700',
            reminder: 'bg-emerald-50 text-emerald-700',
            task: 'bg-slate-50 text-slate-700',
        };
        return map[event.event_type] || map.task;
    }

    function actionButtons(event, compact = false) {
        const base = compact ? 'text-[11px] px-2 py-1' : 'px-3 py-2 text-xs';
        const buttons = [
            `<button class="rounded-xl border border-slate-200 ${base} font-black text-slate-700" onclick="openEventFromView('${event.uuid}', '${event.source_type}')">View</button>`,
        ];
        if (event.source_type === 'leave') {
            buttons.push(`<button class="rounded-xl border border-emerald-200 bg-emerald-50 ${base} font-black text-emerald-700" onclick="approveLeaveRequest('${event.uuid}')">Approve</button>`);
            buttons.push(`<button class="rounded-xl border border-rose-200 bg-rose-50 ${base} font-black text-rose-700" onclick="rejectLeaveRequest('${event.uuid}')">Reject</button>`);
        } else {
            buttons.push(`<button class="rounded-xl border border-indigo-200 bg-indigo-50 ${base} font-black text-indigo-700" onclick="editEvent('${event.uuid}')">Edit</button>`);
        }
        return buttons.join('');
    }

    function renderTable() {
        if (!state.events.length) {
            els.eventTableBody.innerHTML = '<tr><td colspan="4" class="px-4 py-10 text-center text-slate-400">No events found</td></tr>';
            return;
        }

        els.eventTableBody.innerHTML = state.events.map(event => `
            <tr class="hover:bg-slate-50">
                <td class="px-4 py-3 align-top text-xs font-bold text-slate-500">${formatTimeRange(event)}</td>
                <td class="px-4 py-3 align-top">
                    <div class="font-black text-slate-900">${escapeHtml(event.title)}</div>
                    <div class="mt-1 flex flex-wrap items-center gap-2">${typeBadge(event.event_type)}<span class="text-xs text-slate-500">${escapeHtml(event.scope?.label || 'Company-wide')}</span></div>
                </td>
                <td class="px-4 py-3 align-top">${badge(event.status)}</td>
                <td class="px-4 py-3 align-top">
                    <div class="flex flex-wrap gap-2">${actionButtons(event, true)}</div>
                </td>
            </tr>
        `).join('');
    }

    function openEventModal(event = null) {
        state.editing = event;
        els.modal.classList.remove('hidden');
        els.modal.classList.add('flex');
        document.body.style.overflow = 'hidden';
        els.form.reset();
        els.targetsWrap.innerHTML = '';
        state.selectedWeekdays = new Set();
        updateWeekdayButtons();
        addTargetRow();
        if (event) {
            els.modalTitle.textContent = 'Edit Event';
            els.eventUuid.value = event.uuid;
            els.title.value = event.title || '';
            els.eventType.value = event.event_type || '';
            els.status.value = event.status || 'pending';
            els.startAt.value = toLocalInputValue(event.start);
            els.endAt.value = toLocalInputValue(event.end);
            els.description.value = event.description || '';
            if (event.recurrence_rule) {
                els.recurrenceFrequency.value = event.recurrence_rule.frequency || 'none';
                els.recurrenceInterval.value = event.recurrence_rule.interval || 1;
                els.recurrenceUntil.value = event.recurrence_rule.until || '';
                state.selectedWeekdays = new Set((event.recurrence_rule.days || []).map(day => String(day).slice(0, 3)));
                updateWeekdayButtons();
            }
            els.targetsWrap.innerHTML = '';
            (event.targets || []).forEach(target => addTargetRow(target));
            if (!(event.targets || []).length) addTargetRow();
            document.getElementById('deleteEventBtn').classList.remove('hidden');
        } else {
            els.modalTitle.textContent = 'New Event';
            els.eventUuid.value = '';
            els.status.value = 'pending';
            els.startAt.value = toLocalInputValue(state.current.toISOString());
            els.endAt.value = toLocalInputValue(new Date(state.current.getTime() + 60 * 60 * 1000).toISOString());
            document.getElementById('deleteEventBtn').classList.add('hidden');
        }
    }

    function openEventFromView(uuid, sourceType) {
        const event = state.events.find(item => item.uuid === uuid && item.source_type === sourceType);
        if (!event) return;
        if (sourceType === 'leave') {
            alert(`${event.title}\n\n${event.description || ''}\n\nUse Approve/Reject to process this leave request.`);
            return;
        }
        openEventModal(event);
    }

    function editEvent(uuid) {
        const event = state.events.find(item => item.uuid === uuid && item.source_type === 'calendar');
        if (!event) return;
        openEventModal(event);
    }

    function addTargetRow(target = null) {
        const node = els.targetRowTemplate.content.cloneNode(true);
        const row = node.querySelector('.target-row');
        const type = row.querySelector('.target-type');
        const value = row.querySelector('.target-value');
        type.value = target?.type || target?.target_type || 'company';
        value.value = target?.value || target?.target_value || '';
        if (target?.label || target?.target_label) {
            value.placeholder = target.label || target.target_label;
        }
        type.addEventListener('change', () => {
            value.placeholder = placeholderForType(type.value);
        });
        value.placeholder = placeholderForType(type.value);
        row.querySelector('.remove-target').addEventListener('click', () => row.remove());
        els.targetsWrap.appendChild(node);
    }

    function placeholderForType(type) {
        return {
            company: 'Company-wide',
            employee: 'Employee ID',
            department: 'Department name',
            branch: 'Branch name',
            team: 'Team name',
        }[type] || 'Target value';
    }

    function updateWeekdayButtons() {
        document.querySelectorAll('.weekday-btn').forEach(btn => {
            const active = state.selectedWeekdays.has(btn.dataset.weekday);
            btn.classList.toggle('bg-slate-950', active);
            btn.classList.toggle('text-white', active);
            btn.classList.toggle('border-slate-950', active);
        });
    }

    function gatherTargets() {
        return [...document.querySelectorAll('.target-row')].map(row => ({
            type: row.querySelector('.target-type').value,
            value: row.querySelector('.target-value').value,
            label: row.querySelector('.target-value').value,
        })).filter(item => item.value || item.type === 'company');
    }

    function gatherRecurrence() {
        const frequency = els.recurrenceFrequency.value;
        if (frequency === 'none') {
            return null;
        }
        return {
            frequency,
            interval: Math.max(1, parseInt(els.recurrenceInterval.value || '1', 10)),
            until: els.recurrenceUntil.value || null,
            days: [...state.selectedWeekdays],
        };
    }

    async function saveEvent(event) {
        event.preventDefault();
        const payload = {
            title: els.title.value,
            event_type: els.eventType.value,
            status: els.status.value,
            start_at: els.startAt.value,
            end_at: els.endAt.value,
            description: els.description.value,
            recurrence: gatherRecurrence(),
            targets: gatherTargets(),
        };

        const uuid = els.eventUuid.value;
        const method = uuid ? 'PUT' : 'POST';
        const url = uuid ? `/api/calendar/events/${uuid}` : '/api/calendar/events';

        const res = await fetch(url, {
            method,
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(payload),
        });
        const result = await res.json();
        if (!result.success) {
            throw new Error(result.message || 'Unable to save event');
        }
        closeModal();
        await loadEvents();
    }

    function closeModal() {
        els.modal.classList.add('hidden');
        els.modal.classList.remove('flex');
        document.body.style.overflow = '';
    }

    async function deleteEvent() {
        const uuid = els.eventUuid.value;
        if (!uuid) return;
        if (!confirm('Delete this calendar event?')) return;
        const res = await fetch(`/api/calendar/events/${uuid}`, { method: 'DELETE' });
        const result = await res.json();
        if (!result.success) {
            throw new Error(result.message || 'Unable to delete event');
        }
        closeModal();
        await loadEvents();
    }

    async function approveLeaveRequest(uuid) {
        if (!confirm('Approve this leave request?')) return;
        const res = await fetch(`/api/calendar/leaves/${uuid}/approve`, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({uuid}),
        });
        const result = await res.json();
        if (!result.success) throw new Error(result.message || 'Unable to approve');
        await loadEvents();
    }

    async function rejectLeaveRequest(uuid) {
        const remark = prompt('Enter reject reason:');
        if (remark === null) return;
        if (!remark.trim()) return alert('Reject reason is required.');
        const res = await fetch(`/api/calendar/leaves/${uuid}/reject`, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({uuid, remark: remark.trim()}),
        });
        const result = await res.json();
        if (!result.success) throw new Error(result.message || 'Unable to reject');
        await loadEvents();
    }

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function escapeAttr(value) {
        return escapeHtml(value).replace(/`/g, '&#96;');
    }

    window.__calendarJump = (date) => {
        state.current = new Date(`${date}T12:00:00`);
        els.dateJump.value = date;
        loadEvents().catch(console.error);
    };

    window.openEventFromView = openEventFromView;
    window.openEventModal = openEventModal;
    window.editEvent = editEvent;
    window.approveLeaveRequest = approveLeaveRequest;
    window.rejectLeaveRequest = rejectLeaveRequest;

    document.querySelectorAll('.view-switch').forEach(btn => {
        btn.addEventListener('click', () => {
            state.view = btn.dataset.viewSwitch;
            document.querySelectorAll('.view-switch').forEach(item => {
                const active = item === btn;
                item.className = active
                    ? 'view-switch rounded-2xl bg-white px-4 py-2 text-sm font-bold text-slate-900 shadow-lg shadow-slate-950/20'
                    : 'view-switch rounded-2xl border border-white/10 bg-white/10 px-4 py-2 text-sm font-bold text-white transition hover:bg-white/15';
            });
            loadEvents().catch(console.error);
        });
    });

    document.getElementById('prevPeriod').addEventListener('click', () => {
        if (state.view === 'month') state.current.setMonth(state.current.getMonth() - 1);
        else if (state.view === 'week') state.current.setDate(state.current.getDate() - 7);
        else state.current.setDate(state.current.getDate() - 1);
        els.dateJump.value = state.current.toISOString().slice(0, 10);
        loadEvents().catch(console.error);
    });
    document.getElementById('nextPeriod').addEventListener('click', () => {
        if (state.view === 'month') state.current.setMonth(state.current.getMonth() + 1);
        else if (state.view === 'week') state.current.setDate(state.current.getDate() + 7);
        else state.current.setDate(state.current.getDate() + 1);
        els.dateJump.value = state.current.toISOString().slice(0, 10);
        loadEvents().catch(console.error);
    });
    document.getElementById('todayPeriod').addEventListener('click', () => {
        state.current = new Date();
        els.dateJump.value = localDateKey(state.current);
        loadEvents().catch(console.error);
    });
    document.getElementById('jumpToday').addEventListener('click', () => {
        state.current = new Date();
        els.dateJump.value = localDateKey(state.current);
        loadEvents().catch(console.error);
    });
    document.getElementById('reloadCalendar').addEventListener('click', () => loadEvents().catch(console.error));
    document.getElementById('openCreateEvent').addEventListener('click', () => openEventModal());
    document.getElementById('closeEventModal').addEventListener('click', closeModal);
    document.getElementById('clearFilters').addEventListener('click', () => {
        state.filters = { employee_id: '', department: '', branch: '', event_type: '', status: '' };
        els.employeeFilter.value = '';
        els.departmentFilter.value = '';
        els.branchFilter.value = '';
        els.eventTypeFilter.value = '';
        els.statusFilter.value = '';
        loadEvents().catch(console.error);
    });
    document.getElementById('addTargetBtn').addEventListener('click', () => addTargetRow());
    document.getElementById('deleteEventBtn').addEventListener('click', () => deleteEvent().catch(console.error));
    document.getElementById('dateJump').addEventListener('change', (e) => {
        state.current = new Date(`${e.target.value}T12:00:00`);
        loadEvents().catch(console.error);
    });

    ['employeeFilter', 'departmentFilter', 'branchFilter', 'eventTypeFilter', 'statusFilter'].forEach(id => {
        document.getElementById(id).addEventListener('change', (e) => {
            state.filters[id.replace('Filter', '')] = e.target.value;
            loadEvents().catch(console.error);
        });
    });

    document.querySelectorAll('.weekday-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            if (state.selectedWeekdays.has(btn.dataset.weekday)) {
                state.selectedWeekdays.delete(btn.dataset.weekday);
            } else {
                state.selectedWeekdays.add(btn.dataset.weekday);
            }
            updateWeekdayButtons();
        });
    });

    els.form.addEventListener('submit', (e) => saveEvent(e).catch(err => alert(err.message)));

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !els.modal.classList.contains('hidden')) {
            closeModal();
        }
    });

    loadOptions()
        .then(() => loadEvents())
        .catch(err => {
            console.error(err);
            els.calendarPanel.innerHTML = '<div class="rounded-3xl border border-dashed border-rose-200 bg-rose-50 px-4 py-16 text-center text-rose-600">Failed to load calendar data.</div>';
        });
})();
</script>
