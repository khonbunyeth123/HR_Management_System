<?php
$requestedDate = $_GET['date'] ?? date('Y-m-d');
$today = preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $requestedDate)
    ? (string) $requestedDate
    : date('Y-m-d');
?>
<script>window.__calendarInitialDate = <?= json_encode($today) ?>;</script>

<style>
/* ── Toast ── */
#toastContainer {
    position: fixed; bottom: 24px; right: 24px; z-index: 9999;
    display: flex; flex-direction: column; gap: 10px; pointer-events: none;
}
.toast {
    display: flex; align-items: center; gap: 10px;
    padding: 12px 18px; border-radius: 16px;
    font-size: 13px; font-weight: 700; color: #fff;
    opacity: 0; transform: translateY(12px);
    transition: opacity .25s, transform .25s;
    pointer-events: auto; max-width: 320px;
    box-shadow: 0 4px 20px rgba(0,0,0,.18);
}
.toast.show { opacity: 1; transform: translateY(0); }
.toast.success { background: #059669; }
.toast.error   { background: #dc2626; }
.toast.info    { background: #4f46e5; }

/* ── Confirm / Reject modal ── */
#confirmOverlay {
    position: fixed; inset: 0; z-index: 8000;
    background: rgba(15,23,42,.55);
    display: flex; align-items: center; justify-content: center;
    padding: 16px; opacity: 0; pointer-events: none;
    transition: opacity .2s;
}
#confirmOverlay.open { opacity: 1; pointer-events: auto; }
#confirmBox {
    background: #fff; border-radius: 24px;
    padding: 28px 28px 22px; width: 100%; max-width: 420px;
    transform: scale(.96); transition: transform .2s;
    box-shadow: 0 24px 64px rgba(0,0,0,.18);
}
#confirmOverlay.open #confirmBox { transform: scale(1); }
#confirmBox h4 { margin: 0 0 6px; font-size: 17px; font-weight: 900; color: #0f172a; }
#confirmBox p  { margin: 0 0 18px; font-size: 13px; color: #64748b; line-height: 1.6; }
#rejectRemarkWrap { display: none; margin-bottom: 16px; }
#rejectRemarkWrap label { display: block; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: .18em; color: #64748b; margin-bottom: 6px; }
#rejectRemark { width: 100%; border: 1.5px solid #e2e8f0; border-radius: 12px; padding: 10px 14px; font-size: 13px; font-family: inherit; resize: vertical; min-height: 80px; box-sizing: border-box; }
#rejectRemark:focus { outline: none; border-color: #6366f1; }
.confirm-btns { display: flex; gap: 8px; justify-content: flex-end; }
.confirm-btns button { border-radius: 14px; padding: 10px 20px; font-size: 13px; font-weight: 800; border: none; cursor: pointer; transition: opacity .15s; }
.confirm-btns button:hover { opacity: .85; }
#confirmCancelBtn { background: #f1f5f9; color: #334155; }
#confirmOkBtn { background: #0f172a; color: #fff; }
#confirmOkBtn.danger { background: #dc2626; }
#confirmOkBtn.success-btn { background: #059669; }

/* ── Event modal ── */
#eventModal {
    position: fixed; inset: 0; z-index: 7000;
    background: rgba(15,23,42,.55);
    display: flex; align-items: center; justify-content: center;
    padding: 16px; opacity: 0; pointer-events: none;
    transition: opacity .2s;
}
#eventModal.open { opacity: 1; pointer-events: auto; }
#eventModalBox {
    width: 100%; max-width: 860px;
    background: #fff; border-radius: 28px;
    overflow: hidden; transform: scale(.97);
    transition: transform .2s;
    box-shadow: 0 24px 64px rgba(0,0,0,.18);
    max-height: 92vh; overflow-y: auto;
}
#eventModal.open #eventModalBox { transform: scale(1); }

/* ── Loading overlay on calendar panel ── */
#calendarLoadMask {
    position: absolute; inset: 0; border-radius: 24px;
    background: rgba(255,255,255,.75);
    display: flex; align-items: center; justify-content: center;
    z-index: 10; opacity: 0; pointer-events: none;
    transition: opacity .2s;
}
#calendarLoadMask.show { opacity: 1; pointer-events: auto; }
.spinner {
    width: 32px; height: 32px; border: 3px solid #e2e8f0;
    border-top-color: #6366f1; border-radius: 50%;
    animation: spin .7s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* ── Debounce visual for filters ── */
.filter-loading select { opacity: .6; pointer-events: none; }

/* ── Weekday buttons ── */
.weekday-btn.active { background: #0f172a !important; color: #fff !important; border-color: #0f172a !important; }

/* ── Scrollbar in right panel table ── */
.events-table-wrap::-webkit-scrollbar { width: 5px; }
.events-table-wrap::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 99px; }
</style>

<div id="toastContainer" aria-live="polite" aria-atomic="true"></div>

<!-- ── Confirm/Reject overlay ── -->
<div id="confirmOverlay" role="dialog" aria-modal="true" aria-labelledby="confirmTitle">
    <div id="confirmBox">
        <h4 id="confirmTitle">Confirm action</h4>
        <p id="confirmMessage">Are you sure?</p>
        <div id="rejectRemarkWrap">
            <label for="rejectRemark">Rejection reason <span style="color:#dc2626">*</span></label>
            <textarea id="rejectRemark" placeholder="Enter reason for rejection..."></textarea>
            <p id="remarkError" style="font-size:12px; color:#dc2626; margin:4px 0 0; display:none;">Reason is required.</p>
        </div>
        <div class="confirm-btns">
            <button id="confirmCancelBtn" type="button">Cancel</button>
            <button id="confirmOkBtn" type="button">Confirm</button>
        </div>
    </div>
</div>

<div class="w-full min-h-full">
    <div class="p-3 lg:p-4 space-y-4">

        <!-- Header -->
        <div class="rounded-3xl bg-gradient-to-r from-slate-950 via-slate-900 to-indigo-950 px-5 py-5 text-white shadow-xl shadow-slate-200/60">
            <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
                <div class="max-w-3xl">
                    <div class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-3 py-1 text-[10px] font-black uppercase tracking-[0.25em] text-indigo-200">
                        <span class="iconify text-sm" data-icon="mdi:calendar-multiselect" aria-hidden="true"></span>
                        HRM Admin Calendar
                    </div>
                    <!-- <h1 class="mt-3 text-2xl font-black tracking-tight sm:text-3xl">Plan schedules, leave approvals, and reminders in one place</h1> -->
                    <p class="mt-2 max-w-2xl text-sm text-slate-300">Schedules · leave approvals · reminders.</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <button data-view-switch="month" class="view-switch rounded-2xl bg-white px-4 py-2 text-sm font-bold text-slate-900 shadow-lg shadow-slate-950/20" aria-pressed="true">Month</button>
                    <button data-view-switch="week"  class="view-switch rounded-2xl border border-white/10 bg-white/10 px-4 py-2 text-sm font-bold text-white transition hover:bg-white/15" aria-pressed="false">Week</button>
                    <button data-view-switch="day"   class="view-switch rounded-2xl border border-white/10 bg-white/10 px-4 py-2 text-sm font-bold text-white transition hover:bg-white/15" aria-pressed="false">Day</button>
                    <button id="openCreateEvent" class="rounded-2xl bg-indigo-500 px-4 py-2 text-sm font-black text-white shadow-lg shadow-indigo-500/30 transition hover:bg-indigo-400">+ New Event</button>
                </div>
            </div>
        </div>

        <!-- 3-col layout -->
        <div class="grid grid-cols-1 gap-4 xl:grid-cols-[280px_minmax(0,1fr)_340px] xl:items-stretch">

            <!-- Left: Filters + Quick Actions -->
            <aside class="space-y-4">
                <div class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm">
                    <div class="flex items-center justify-between">
                        <h2 class="text-sm font-black uppercase tracking-[0.2em] text-slate-500">Filters</h2>
                        <button id="clearFilters" class="text-xs font-bold text-indigo-600 hover:text-indigo-700">Reset</button>
                    </div>
                    <div id="filterPanel" class="mt-4 space-y-3">
                        <div>
                            <label for="employeeFilter" class="mb-1 block text-xs font-bold text-slate-500">Employee</label>
                            <select id="employeeFilter" class="w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm">
                                <option value="">All employees</option>
                            </select>
                        </div>
                        <div>
                            <label for="departmentFilter" class="mb-1 block text-xs font-bold text-slate-500">Department</label>
                            <select id="departmentFilter" class="w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm">
                                <option value="">All departments</option>
                            </select>
                        </div>
                        <div>
                            <label for="branchFilter" class="mb-1 block text-xs font-bold text-slate-500">Branch</label>
                            <select id="branchFilter" class="w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm">
                                <option value="">All branches</option>
                            </select>
                        </div>
                        <div>
                            <label for="eventTypeFilter" class="mb-1 block text-xs font-bold text-slate-500">Event Type</label>
                            <select id="eventTypeFilter" class="w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm">
                                <option value="">All types</option>
                            </select>
                        </div>
                        <div>
                            <label for="statusFilter" class="mb-1 block text-xs font-bold text-slate-500">Status</label>
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
                            <span class="iconify" data-icon="mdi:calendar-today" aria-hidden="true"></span>
                        </button>
                        <button id="reloadCalendar" class="flex w-full items-center justify-between rounded-2xl bg-indigo-500 px-4 py-3 font-black transition hover:bg-indigo-400">
                            <span>Refresh data</span>
                            <span class="iconify" data-icon="mdi:refresh" aria-hidden="true"></span>
                        </button>
                    </div>
                    <div class="mt-4 rounded-2xl border border-white/10 bg-white/5 p-4">
                        <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Current date</p>
                        <p id="currentFocusDate" class="mt-2 text-2xl font-black"><?= htmlspecialchars($today) ?></p>
                        <p class="mt-1 text-xs text-slate-300">Use the view controls to switch between month, week, and day.</p>
                    </div>
                </div>
            </aside>

            <!-- Center: Calendar -->
            <section class="min-w-0 space-y-4 xl:flex xl:flex-col">
                <div class="rounded-3xl border border-slate-200 bg-white shadow-sm xl:flex xl:flex-1 xl:flex-col">
                    <div class="flex flex-col gap-3 border-b border-slate-100 px-5 py-4 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <p class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Calendar Panel</p>
                            <h2 id="calendarHeading" class="mt-1 text-xl font-black text-slate-900">Month view</h2>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <button id="prevPeriod" aria-label="Previous period" class="rounded-2xl border border-slate-200 px-3 py-2 text-sm font-bold text-slate-700 transition hover:bg-slate-50">
                                <span class="iconify" data-icon="mdi:chevron-left" aria-hidden="true"></span>
                            </button>
                            <button id="todayPeriod" class="rounded-2xl border border-slate-200 px-4 py-2 text-sm font-bold text-slate-700 transition hover:bg-slate-50">Today</button>
                            <button id="nextPeriod" aria-label="Next period" class="rounded-2xl border border-slate-200 px-3 py-2 text-sm font-bold text-slate-700 transition hover:bg-slate-50">
                                <span class="iconify" data-icon="mdi:chevron-right" aria-hidden="true"></span>
                            </button>
                            <label for="dateJump" class="sr-only">Jump to date</label>
                            <input id="dateJump" type="date" value="<?= htmlspecialchars($today) ?>" class="rounded-2xl border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700">
                        </div>
                    </div>
                    <div style="position:relative;" class="p-4 lg:p-5 xl:flex-1 xl:min-h-0">
                        <div id="calendarLoadMask" aria-label="Loading calendar" role="status">
                            <div class="spinner"></div>
                        </div>
                        <div id="calendarPanel" class="min-h-[560px] xl:min-h-0 xl:h-full">
                            <div class="flex h-full min-h-[520px] items-center justify-center rounded-3xl border border-dashed border-slate-200 bg-slate-50 py-24 text-slate-400">
                                <div class="text-center">
                                    <div class="spinner mx-auto"></div>
                                    <p class="mt-3 text-sm font-semibold">Loading calendar...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Right: Summary + Event table -->
            <aside class="space-y-4 xl:flex xl:flex-col">
                <div class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm">
                    <div class="flex items-center justify-between">
                        <h2 class="text-sm font-black uppercase tracking-[0.2em] text-slate-500">Live Summary</h2>
                        <span id="eventCountBadge" class="rounded-full bg-indigo-100 px-3 py-1 text-xs font-black text-indigo-700" aria-live="polite">0 events</span>
                    </div>
                    <div id="summaryGrid" class="mt-4 grid grid-cols-2 gap-3">
                        <div class="rounded-2xl bg-slate-50 p-3">
                            <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Pending</p>
                            <p id="summaryPending" class="mt-2 text-2xl font-black text-slate-900">0</p>
                        </div>
                        <div class="rounded-2xl bg-emerald-50 p-3">
                            <p class="text-[10px] font-black uppercase tracking-[0.2em] text-emerald-500">Approved</p>
                            <p id="summaryApproved" class="mt-2 text-2xl font-black text-emerald-700">0</p>
                        </div>
                        <div class="rounded-2xl bg-rose-50 p-3">
                            <p class="text-[10px] font-black uppercase tracking-[0.2em] text-rose-400">Rejected</p>
                            <p id="summaryRejected" class="mt-2 text-2xl font-black text-rose-700">0</p>
                        </div>
                        <div class="rounded-2xl bg-slate-50 p-3">
                            <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Cancelled</p>
                            <p id="summaryCancelled" class="mt-2 text-2xl font-black text-slate-900">0</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-3xl border border-slate-200 bg-white shadow-sm xl:flex xl:flex-1 xl:flex-col">
                    <div class="border-b border-slate-100 px-4 py-4 flex items-center justify-between">
                        <h2 class="text-sm font-black uppercase tracking-[0.2em] text-slate-500">Event List</h2>
                        <span id="eventListCount" class="text-xs font-bold text-slate-400"></span>
                    </div>
                    <div class="events-table-wrap max-h-[420px] overflow-y-auto overscroll-contain xl:flex-1 xl:min-h-0 xl:max-h-[calc(100vh-320px)]">
                        <table class="min-w-full text-left text-sm" role="table">
                            <thead class="sticky top-0 bg-slate-900 text-white">
                                <tr>
                                    <th scope="col" class="px-4 py-3 text-xs font-black uppercase tracking-[0.15em]">Time</th>
                                    <th scope="col" class="px-4 py-3 text-xs font-black uppercase tracking-[0.15em]">Event</th>
                                    <th scope="col" class="px-4 py-3 text-xs font-black uppercase tracking-[0.15em]">Status</th>
                                    <th scope="col" class="px-4 py-3 text-xs font-black uppercase tracking-[0.15em]">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="eventTableBody" class="divide-y divide-slate-100 bg-white">
                                <tr><td colspan="4" class="px-4 py-10 text-center text-slate-400">Loading events...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</div>

<!-- ── Event modal ── -->
<div id="eventModal" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
    <div id="eventModalBox">
        <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.25em] text-slate-400">Calendar Form</p>
                <h3 id="modalTitle" class="mt-1 text-xl font-black text-slate-900">New Event</h3>
            </div>
            <button id="closeEventModal" aria-label="Close modal" class="rounded-2xl border border-slate-200 px-3 py-2 text-slate-500 transition hover:bg-slate-50">
                <span class="iconify" data-icon="mdi:close" aria-hidden="true"></span>
            </button>
        </div>

        <form id="eventForm" class="grid gap-0 lg:grid-cols-[1.2fr_0.8fr]" novalidate>
            <input type="hidden" id="eventUuid">
            <div class="space-y-4 p-5">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label for="evTitle" class="mb-1 block text-xs font-bold uppercase tracking-[0.2em] text-slate-400">Title <span class="text-rose-500">*</span></label>
                        <input id="evTitle" required class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm font-semibold focus:border-indigo-400 focus:outline-none" placeholder="Quarterly planning meeting">
                        <p class="field-error hidden mt-1 text-xs text-rose-500">Title is required.</p>
                    </div>
                    <div>
                        <label for="evEventType" class="mb-1 block text-xs font-bold uppercase tracking-[0.2em] text-slate-400">Event Type <span class="text-rose-500">*</span></label>
                        <select id="evEventType" required class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm font-semibold focus:border-indigo-400 focus:outline-none">
                            <option value="">Choose type</option>
                            <option value="holiday">Holiday</option>
                            <option value="shift">Shift</option>
                            <option value="leave">Leave</option>
                            <option value="meeting">Meeting</option>
                            <option value="reminder">Reminder</option>
                            <option value="task">Task</option>
                        </select>
                        <p class="field-error hidden mt-1 text-xs text-rose-500">Event type is required.</p>
                    </div>
                    <div>
                        <label for="evStatus" class="mb-1 block text-xs font-bold uppercase tracking-[0.2em] text-slate-400">Status</label>
                        <select id="evStatus" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm font-semibold focus:border-indigo-400 focus:outline-none">
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div>
                        <label for="evStartAt" class="mb-1 block text-xs font-bold uppercase tracking-[0.2em] text-slate-400">Start <span class="text-rose-500">*</span></label>
                        <input id="evStartAt" type="datetime-local" required class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm font-semibold focus:border-indigo-400 focus:outline-none">
                        <p class="field-error hidden mt-1 text-xs text-rose-500">Start date is required.</p>
                    </div>
                    <div>
                        <label for="evEndAt" class="mb-1 block text-xs font-bold uppercase tracking-[0.2em] text-slate-400">End <span class="text-rose-500">*</span></label>
                        <input id="evEndAt" type="datetime-local" required class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm font-semibold focus:border-indigo-400 focus:outline-none">
                        <p class="field-error hidden mt-1 text-xs text-rose-500">End date is required.</p>
                    </div>
                    <div class="md:col-span-2">
                        <label for="evDescription" class="mb-1 block text-xs font-bold uppercase tracking-[0.2em] text-slate-400">Description</label>
                        <textarea id="evDescription" rows="3" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm font-semibold focus:border-indigo-400 focus:outline-none" placeholder="Notes for the team..."></textarea>
                    </div>
                </div>

                <div class="rounded-3xl border border-slate-100 bg-slate-50 p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-black text-slate-900">Assignments</p>
                            <p class="text-xs text-slate-500">Company-wide, employees, departments, branches, or teams.</p>
                        </div>
                        <button type="button" id="addTargetBtn" class="rounded-2xl bg-white px-3 py-2 text-xs font-black text-indigo-600 shadow-sm hover:bg-indigo-50 transition">Add target</button>
                    </div>
                    <div id="targetsWrap" class="mt-4 space-y-3"></div>
                </div>
            </div>

            <div class="space-y-4 border-t border-slate-100 bg-slate-50/60 p-5 lg:border-l lg:border-t-0">
                <div class="rounded-3xl bg-white p-4 shadow-sm">
                    <p class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Recurrence</p>
                    <div class="mt-3 space-y-3">
                        <div>
                            <label for="recurrenceFrequency" class="mb-1 block text-xs font-bold text-slate-500">Frequency</label>
                            <select id="recurrenceFrequency" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm font-semibold focus:border-indigo-400 focus:outline-none">
                                <option value="none">No recurrence</option>
                                <option value="daily">Daily</option>
                                <option value="weekly">Weekly</option>
                                <option value="monthly">Monthly</option>
                                <option value="yearly">Yearly</option>
                            </select>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label for="recurrenceInterval" class="mb-1 block text-xs font-bold text-slate-500">Interval</label>
                                <input id="recurrenceInterval" type="number" min="1" value="1" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm font-semibold focus:border-indigo-400 focus:outline-none">
                            </div>
                            <div>
                                <label for="recurrenceUntil" class="mb-1 block text-xs font-bold text-slate-500">Until</label>
                                <input id="recurrenceUntil" type="date" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm font-semibold focus:border-indigo-400 focus:outline-none">
                            </div>
                        </div>
                        <div>
                            <p class="mb-1 text-xs font-bold text-slate-500">Weekly days</p>
                            <div class="grid grid-cols-7 gap-1 text-[10px] font-black uppercase tracking-[0.12em]">
                                <?php foreach(['mon','tue','wed','thu','fri','sat','sun'] as $d): ?>
                                <button type="button" data-weekday="<?= $d ?>" class="weekday-btn rounded-xl border border-slate-200 bg-white px-1 py-2 text-slate-500 transition hover:bg-slate-100" aria-pressed="false"><?= ucfirst($d) ?></button>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="rounded-3xl bg-white p-4 shadow-sm">
                    <p class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Event Actions</p>
                    <div class="mt-3 space-y-2">
                        <button type="submit" id="saveEventBtn" class="relative w-full rounded-2xl bg-slate-950 px-4 py-3 text-sm font-black text-white transition hover:bg-slate-800 disabled:opacity-60">
                            <span id="saveEventLabel">Save Event</span>
                            <span id="saveEventSpinner" class="hidden absolute right-4 top-1/2 -translate-y-1/2"><span class="spinner" style="width:18px;height:18px;border-width:2px;border-top-color:#fff;border-color:rgba(255,255,255,.3)"></span></span>
                        </button>
                        <button type="button" id="deleteEventBtn" class="hidden w-full rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-black text-rose-600 transition hover:bg-rose-100">Delete Event</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<template id="targetRowTemplate">
    <div class="target-row grid gap-2 rounded-2xl border border-slate-200 bg-white p-3 md:grid-cols-[160px_minmax(0,1fr)_auto]">
        <select class="target-type rounded-xl border border-slate-200 px-3 py-2 text-sm font-semibold" aria-label="Target type">
            <option value="company">Company-wide</option>
            <option value="employee">Employee</option>
            <option value="department">Department</option>
            <option value="branch">Branch</option>
            <option value="team">Team</option>
        </select>
        <input class="target-value rounded-xl border border-slate-200 px-3 py-2 text-sm font-semibold" placeholder="Target value" aria-label="Target value">
        <button type="button" class="remove-target rounded-xl border border-slate-200 px-3 py-2 text-sm font-bold text-rose-600 hover:bg-rose-50 transition" aria-label="Remove target">Remove</button>
    </div>
</template>

<script>
(() => {
    /* ── State ── */
    const state = {
        view: 'month',
        current: new Date(`${window.__calendarInitialDate || '<?= $today ?>'}T12:00:00`),
        filters: { employee_id:'', department:'', branch:'', event_type:'', status:'' },
        events: [],
        options: { employees:[], departments:[], branches:[], event_types:[], statuses:[] },
        selectedWeekdays: new Set(),
        editing: null,
        filterDebounce: null,
        fetchAbort: null,
    };

    /* ── Element refs ── */
    const el = (id) => document.getElementById(id);
    const els = {
        calendarPanel: el('calendarPanel'),
        calendarHeading: el('calendarHeading'),
        calendarLoadMask: el('calendarLoadMask'),
        eventTableBody: el('eventTableBody'),
        eventListCount: el('eventListCount'),
        summaryPending: el('summaryPending'),
        summaryApproved: el('summaryApproved'),
        summaryRejected: el('summaryRejected'),
        summaryCancelled: el('summaryCancelled'),
        eventCountBadge: el('eventCountBadge'),
        employeeFilter: el('employeeFilter'),
        departmentFilter: el('departmentFilter'),
        branchFilter: el('branchFilter'),
        eventTypeFilter: el('eventTypeFilter'),
        statusFilter: el('statusFilter'),
        filterPanel: el('filterPanel'),
        dateJump: el('dateJump'),
        currentFocusDate: el('currentFocusDate'),
        modal: el('eventModal'),
        form: el('eventForm'),
        modalTitle: el('modalTitle'),
        eventUuid: el('eventUuid'),
        evTitle: el('evTitle'),
        evEventType: el('evEventType'),
        evStatus: el('evStatus'),
        evStartAt: el('evStartAt'),
        evEndAt: el('evEndAt'),
        evDescription: el('evDescription'),
        recurrenceFrequency: el('recurrenceFrequency'),
        recurrenceInterval: el('recurrenceInterval'),
        recurrenceUntil: el('recurrenceUntil'),
        targetsWrap: el('targetsWrap'),
        targetRowTemplate: el('targetRowTemplate'),
        saveEventBtn: el('saveEventBtn'),
        saveEventLabel: el('saveEventLabel'),
        saveEventSpinner: el('saveEventSpinner'),
        confirmOverlay: el('confirmOverlay'),
        confirmTitle: el('confirmTitle'),
        confirmMessage: el('confirmMessage'),
        confirmOkBtn: el('confirmOkBtn'),
        confirmCancelBtn: el('confirmCancelBtn'),
        rejectRemarkWrap: el('rejectRemarkWrap'),
        rejectRemark: el('rejectRemark'),
        remarkError: el('remarkError'),
    };

    /* ── Toast ── */
    function showToast(msg, type = 'info', duration = 3500) {
        const container = el('toastContainer');
        const t = document.createElement('div');
        t.className = `toast ${type}`;
        t.setAttribute('role', 'alert');
        t.textContent = msg;
        container.appendChild(t);
        requestAnimationFrame(() => { requestAnimationFrame(() => t.classList.add('show')); });
        setTimeout(() => {
            t.classList.remove('show');
            setTimeout(() => t.remove(), 300);
        }, duration);
    }

    /* ── Confirm dialog ── */
    function openConfirm({ title, message, okLabel = 'Confirm', okClass = '', withRemark = false }) {
        return new Promise((resolve) => {
            els.confirmTitle.textContent = title;
            els.confirmMessage.textContent = message;
            els.confirmOkBtn.textContent = okLabel;
            els.confirmOkBtn.className = `${okClass}`;
            els.rejectRemarkWrap.style.display = withRemark ? 'block' : 'none';
            els.rejectRemark.value = '';
            els.remarkError.style.display = 'none';
            els.confirmOverlay.classList.add('open');

            const cleanup = () => {
                els.confirmOverlay.classList.remove('open');
                els.confirmOkBtn.removeEventListener('click', onOk);
                els.confirmCancelBtn.removeEventListener('click', onCancel);
            };
            const onOk = () => {
                if (withRemark && !els.rejectRemark.value.trim()) {
                    els.remarkError.style.display = 'block';
                    els.rejectRemark.focus();
                    return;
                }
                cleanup();
                resolve({ confirmed: true, remark: els.rejectRemark.value.trim() });
            };
            const onCancel = () => { cleanup(); resolve({ confirmed: false }); };
            els.confirmOkBtn.addEventListener('click', onOk);
            els.confirmCancelBtn.addEventListener('click', onCancel);
        });
    }

    /* ── Loading mask ── */
    function setLoading(on) {
        els.calendarLoadMask.classList.toggle('show', on);
    }

    /* ── Helpers ── */
    function escapeHtml(v) {
        return String(v ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
    }
    function toLocalInputValue(value) {
        if (!value) return '';
        const dt = parseApiDate(value);
        if (Number.isNaN(dt.getTime())) return '';
        const pad = (n) => String(n).padStart(2,'0');
        return `${dt.getFullYear()}-${pad(dt.getMonth()+1)}-${pad(dt.getDate())}T${pad(dt.getHours())}:${pad(dt.getMinutes())}`;
    }
    function parseApiDate(v) { return new Date(String(v || '').replace(' ','T')); }
    function localDateKey(date) {
        const pad = (n) => String(n).padStart(2,'0');
        return `${date.getFullYear()}-${pad(date.getMonth()+1)}-${pad(date.getDate())}`;
    }
    function formatTimeRange(ev) {
        if (ev.all_day) return 'All day';
        const s = parseApiDate(ev.start), e = parseApiDate(ev.end);
        return `${s.toLocaleTimeString([],{hour:'2-digit',minute:'2-digit'})} – ${e.toLocaleTimeString([],{hour:'2-digit',minute:'2-digit'})}`;
    }
    function badge(status) {
        const map = { pending:'bg-amber-100 text-amber-700', approved:'bg-emerald-100 text-emerald-700', rejected:'bg-rose-100 text-rose-700', cancelled:'bg-slate-100 text-slate-700' };
        return `<span class="inline-flex rounded-full px-2 py-1 text-[11px] font-black ${map[status]||map.cancelled}">${escapeHtml(status)}</span>`;
    }
    function typeBadge(type) {
        const map = { holiday:'bg-indigo-100 text-indigo-700', shift:'bg-sky-100 text-sky-700', leave:'bg-amber-100 text-amber-700', meeting:'bg-violet-100 text-violet-700', reminder:'bg-emerald-100 text-emerald-700', task:'bg-slate-100 text-slate-700' };
        return `<span class="inline-flex rounded-full px-2 py-1 text-[11px] font-black ${map[type]||map.task}">${escapeHtml(type)}</span>`;
    }
    function colorClass(ev) {
        const map = { holiday:'bg-indigo-50 text-indigo-700', shift:'bg-sky-50 text-sky-700', leave:'bg-amber-50 text-amber-700', meeting:'bg-violet-50 text-violet-700', reminder:'bg-emerald-50 text-emerald-700', task:'bg-slate-50 text-slate-700' };
        return map[ev.event_type]||map.task;
    }
    function monthName(d) { return d.toLocaleDateString([],{month:'long',year:'numeric'}); }
    function weekRange(d) {
        const s = new Date(d); s.setDate(d.getDate()-((d.getDay()+6)%7));
        const e = new Date(s); e.setDate(s.getDate()+6);
        return `${s.toLocaleDateString([],{month:'short',day:'numeric'})} – ${e.toLocaleDateString([],{month:'short',day:'numeric',year:'numeric'})}`;
    }
    function dayLabel(d) { return d.toLocaleDateString([],{weekday:'long',month:'long',day:'numeric',year:'numeric'}); }

    /* ── Date range for current view ── */
    function getRange() {
        const d = new Date(state.current), s = new Date(d), e = new Date(d);
        const pad = (n) => String(n).padStart(2,'0');
        if (state.view === 'month') {
            s.setDate(1); e.setMonth(e.getMonth()+1,0);
        } else if (state.view === 'week') {
            const off = (d.getDay()+6)%7; s.setDate(d.getDate()-off); e.setDate(s.getDate()+6);
        } else { s.setHours(0,0,0,0); e.setHours(23,59,59,999); }
        return {
            start: `${s.getFullYear()}-${pad(s.getMonth()+1)}-${pad(s.getDate())}`,
            end:   `${e.getFullYear()}-${pad(e.getMonth()+1)}-${pad(e.getDate())}`,
        };
    }

    /* ── Load filter options ── */
    async function loadOptions() {
        const res = await fetch('/api/calendar/filters');
        const result = await res.json();
        if (!result.success) return;
        state.options = result.data || state.options;
        els.employeeFilter.innerHTML = '<option value="">All employees</option>' + (state.options.employees||[]).map(e=>`<option value="${e.id}">${escapeHtml(e.full_name)}${e.department?` – ${escapeHtml(e.department)}`:''}</option>`).join('');
        els.departmentFilter.innerHTML = '<option value="">All departments</option>' + (state.options.departments||[]).map(v=>`<option value="${escapeHtml(v)}">${escapeHtml(v)}</option>`).join('');
        els.branchFilter.innerHTML = '<option value="">All branches</option>' + (state.options.branches||[]).map(v=>`<option value="${escapeHtml(v)}">${escapeHtml(v)}</option>`).join('');
        els.eventTypeFilter.innerHTML = '<option value="">All types</option>' + (state.options.event_types||[]).map(v=>`<option value="${escapeHtml(v)}">${escapeHtml(v)}</option>`).join('');
        els.statusFilter.innerHTML = '<option value="">All status</option>' + (state.options.statuses||[]).map(v=>`<option value="${escapeHtml(v)}">${escapeHtml(v)}</option>`).join('');
    }

    /* ── Load events (with abort + loading mask) ── */
    async function loadEvents() {
        if (state.fetchAbort) state.fetchAbort.abort();
        state.fetchAbort = new AbortController();
        setLoading(true);
        els.eventTableBody.innerHTML = '<tr><td colspan="4" class="px-4 py-10 text-center text-slate-400">Loading events...</td></tr>';
        const range = getRange();
        const params = new URLSearchParams({
            start: range.start, end: range.end,
            employee_id: state.filters.employee_id,
            department: state.filters.department,
            branch: state.filters.branch,
            event_type: state.filters.event_type,
            status: state.filters.status,
        });
        try {
            const res = await fetch(`/api/calendar/events?${params}`, { signal: state.fetchAbort.signal });
            const result = await res.json();
            if (!result.success) throw new Error(result.message || 'Unable to load calendar');
            state.events = result.data?.events || [];
            renderSummary(result.data?.summary || {});
            renderCalendar();
            renderTable();
        } catch (err) {
            if (err.name === 'AbortError') return;
            state.events = [];
            renderSummary({});
            els.eventTableBody.innerHTML = '<tr><td colspan="4" class="px-4 py-10 text-center text-rose-500">Failed to load events</td></tr>';
            els.calendarPanel.innerHTML = `<div class="rounded-3xl border border-dashed border-rose-200 bg-rose-50 px-4 py-16 text-center text-rose-600 font-semibold">${escapeHtml(err.message)}</div>`;
            showToast(err.message || 'Failed to load events', 'error');
        } finally {
            setLoading(false);
        }
    }

    /* ── Debounced filter reload ── */
    function scheduleReload() {
        clearTimeout(state.filterDebounce);
        els.filterPanel.classList.add('filter-loading');
        state.filterDebounce = setTimeout(async () => {
            els.filterPanel.classList.remove('filter-loading');
            await loadEvents();
        }, 350);
    }

    /* ── Summary ── */
    function renderSummary(summary) {
        const total = summary.total || 0;
        els.eventCountBadge.textContent = `${total} event${total !== 1 ? 's' : ''}`;
        els.summaryPending.textContent   = summary.pending   || 0;
        els.summaryApproved.textContent  = summary.approved  || 0;
        els.summaryRejected.textContent  = summary.rejected  || 0;
        els.summaryCancelled.textContent = summary.cancelled || 0;
    }

    /* ── Grouped events by date ── */
    function groupedByDate() {
        const g = {};
        state.events.forEach(ev => {
            const key = String(ev.start||'').slice(0,10);
            if (!g[key]) g[key] = [];
            g[key].push(ev);
        });
        return g;
    }

    /* ── Render dispatch ── */
    function renderCalendar() {
        const d = new Date(state.current);
        els.currentFocusDate.textContent = localDateKey(d);
        if (state.view === 'month')      { els.calendarHeading.textContent = monthName(d); renderMonthView(); }
        else if (state.view === 'week')  { els.calendarHeading.textContent = `Week · ${weekRange(d)}`; renderWeekView(); }
        else                             { els.calendarHeading.textContent = `Day · ${dayLabel(d)}`; renderDayView(); }
    }

    /* ── Month view ── */
    function renderMonthView() {
        const d = new Date(state.current), year = d.getFullYear(), month = d.getMonth();
        const first = new Date(year, month, 1);
        const startOff = (first.getDay()+6)%7;
        const daysInMonth = new Date(year, month+1, 0).getDate();
        const groups = groupedByDate();
        const todayKey = localDateKey(new Date());
        const totalCells = Math.ceil((startOff + daysInMonth) / 7) * 7;
        let html = `<div class="grid grid-cols-7 gap-px overflow-hidden rounded-[24px] border border-slate-100 bg-slate-100">`;
        ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'].forEach(day => {
            html += `<div class="bg-slate-50 px-2 py-3 text-center text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">${day}</div>`;
        });
        for (let cell = 0; cell < totalCells; cell++) {
            const dayNum = cell - startOff + 1;
            const inMonth = dayNum > 0 && dayNum <= daysInMonth;
            const cellDate = new Date(year, month, dayNum);
            const key = localDateKey(cellDate);
            const items = groups[key] || [];
            const isToday = key === todayKey;
            html += `<div class="min-h-[110px] bg-white p-2 transition hover:bg-slate-50 ${inMonth?'':'opacity-40'}">
                <div class="mb-1 flex items-center justify-between">
                    <span class="inline-flex h-7 w-7 items-center justify-center rounded-xl text-sm font-black ${isToday?'bg-slate-950 text-white':'text-slate-700'}">${inMonth?dayNum:''}</span>
                    ${items.length>0&&inMonth?`<span class="text-[10px] font-bold text-slate-400">${items.length}</span>`:''}
                </div>
                <div class="space-y-1">
                    ${items.slice(0,3).map(renderChip).join('')}
                    ${items.length>3?`<button class="text-[11px] font-black text-indigo-600 hover:underline" onclick="window.__calendarJump('${key}')">+${items.length-3} more</button>`:''}
                </div>
            </div>`;
        }
        html += '</div>';
        els.calendarPanel.innerHTML = html;
    }

    /* ── Week view ── */
    function renderWeekView() {
        const d = new Date(state.current);
        const start = new Date(d); start.setDate(d.getDate()-((d.getDay()+6)%7));
        const groups = groupedByDate();
        const days = Array.from({length:7},(_,i)=>{ const x=new Date(start); x.setDate(start.getDate()+i); return x; });
        const todayKey = localDateKey(new Date());

        els.calendarPanel.innerHTML = `
            <div class="overflow-x-auto -mx-1 pb-2">
                <div class="grid min-w-[700px] grid-cols-7 gap-2 px-1">
                    ${days.map(dd => {
                        const key = localDateKey(dd);
                        const items = groups[key] || [];
                        const isToday = key === todayKey;
                        return `
                        <div class="flex flex-col rounded-[20px] border ${isToday ? 'border-indigo-300 ring-2 ring-indigo-100' : 'border-slate-100'} bg-white overflow-hidden">
                            <!-- Day header -->
                            <button
                                onclick="window.__calendarJump('${key}')"
                                class="flex flex-col items-center gap-0.5 px-2 py-3 transition
                                    ${isToday ? 'bg-indigo-500 text-white hover:bg-indigo-400' : 'bg-slate-50 text-slate-700 hover:bg-slate-100'}"
                                aria-label="Open ${dd.toLocaleDateString()} day view"
                            >
                                <span class="text-[10px] font-black uppercase tracking-[0.18em] ${isToday ? 'text-indigo-100' : 'text-slate-400'}">
                                    ${dd.toLocaleDateString([], {weekday:'short'})}
                                </span>
                                <span class="text-xl font-black leading-none">${dd.getDate()}</span>
                                ${items.length ? `<span class="mt-1 rounded-full px-2 py-0.5 text-[9px] font-black ${isToday ? 'bg-white/20 text-white' : 'bg-indigo-100 text-indigo-600'}">${items.length}</span>` : '<span class="mt-1 h-4"></span>'}
                            </button>
                            <!-- Events -->
                            <div class="flex flex-col gap-1.5 overflow-y-auto p-2" style="max-height:420px; min-height:80px;">
                                ${items.length
                                    ? items.map(ev => `
                                        <button
                                            type="button"
                                            onclick="openEventFromView('${ev.uuid}','${ev.source_type}')"
                                            class="group w-full rounded-xl border border-slate-100 p-2 text-left transition hover:shadow-sm ${colorClass(ev)}"
                                        >
                                            <p class="truncate text-[11px] font-black leading-tight">${escapeHtml(ev.title)}</p>
                                            <p class="mt-0.5 text-[10px] font-semibold opacity-70">${formatTimeRange(ev)}</p>
                                        </button>`).join('')
                                    : `<div class="flex flex-1 items-center justify-center py-6 text-[11px] font-semibold text-slate-300">—</div>`
                                }
                            </div>
                        </div>`;
                    }).join('')}
                </div>
            </div>`;
    }

    /* ── Day view ── */
    function renderDayView() {
        const key = localDateKey(state.current);
        const items = groupedByDate()[key]||[];
        els.calendarPanel.innerHTML = `<div class="rounded-[26px] border border-slate-100 bg-slate-50 p-4">
            <div class="mb-4 flex items-center justify-between">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Agenda</p>
                    <h3 class="mt-1 text-2xl font-black text-slate-900">${dayLabel(state.current)}</h3>
                </div>
                <button class="rounded-2xl bg-indigo-500 px-4 py-2 text-sm font-black text-white shadow-sm hover:bg-indigo-400 transition" onclick="openEventModal()">+ New Event</button>
            </div>
            <div class="space-y-3">
                ${items.length?items.map(ev=>`
                    <div class="rounded-[22px] border border-slate-100 bg-white p-4 shadow-sm">
                        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                            <div>
                                <div class="flex flex-wrap items-center gap-2">${typeBadge(ev.event_type)}${badge(ev.status)}</div>
                                <h4 class="mt-2 text-base font-black text-slate-900">${escapeHtml(ev.title)}</h4>
                                ${ev.description?`<p class="mt-1 text-sm text-slate-500">${escapeHtml(ev.description)}</p>`:''}
                                <p class="mt-2 text-xs font-bold uppercase tracking-[0.15em] text-slate-400">${formatTimeRange(ev)}${ev.scope?.label?` · ${escapeHtml(ev.scope.label)}`:''}</p>
                            </div>
                            <div class="flex flex-wrap gap-2">${actionButtons(ev)}</div>
                        </div>
                    </div>`).join('')
                :'<div class="rounded-[22px] border border-dashed border-slate-200 bg-white px-4 py-16 text-center text-sm font-semibold text-slate-400">No events on this date.</div>'}
            </div>
        </div>`;
    }

    /* ── Chips & blocks ── */
    function renderChip(ev) {
        return `<button type="button" class="w-full rounded-xl border border-slate-200 px-2 py-1 text-left text-[11px] font-bold ${colorClass(ev)} hover:opacity-80 transition" onclick="openEventFromView('${ev.uuid}','${ev.source_type}')" aria-label="View ${escapeHtml(ev.title)}">
            <span class="block truncate">${escapeHtml(ev.title)}</span>
        </button>`;
    }
    function renderBlock(ev) {
        return `<div class="rounded-2xl border border-slate-100 bg-white p-3 shadow-sm">
            <div class="flex items-center gap-2">${typeBadge(ev.event_type)}${badge(ev.status)}</div>
            <p class="mt-2 truncate text-sm font-black text-slate-900">${escapeHtml(ev.title)}</p>
            <p class="mt-1 text-[11px] font-semibold text-slate-500">${formatTimeRange(ev)}</p>
            <div class="mt-3 flex gap-2">${actionButtons(ev, true)}</div>
        </div>`;
    }

    /* ── Action buttons ── */
    function actionButtons(ev, compact = false) {
        const b = compact ? 'text-[11px] px-2 py-1' : 'px-3 py-2 text-xs';
        const btns = [`<button class="rounded-xl border border-slate-200 ${b} font-black text-slate-700 hover:bg-slate-50 transition" onclick="openEventFromView('${ev.uuid}','${ev.source_type}')">View</button>`];
        if (ev.source_type === 'leave') {
            btns.push(`<button class="rounded-xl border border-emerald-200 bg-emerald-50 ${b} font-black text-emerald-700 hover:bg-emerald-100 transition" onclick="approveLeaveRequest('${ev.uuid}')">Approve</button>`);
            btns.push(`<button class="rounded-xl border border-rose-200 bg-rose-50 ${b} font-black text-rose-700 hover:bg-rose-100 transition" onclick="rejectLeaveRequest('${ev.uuid}')">Reject</button>`);
        } else {
            btns.push(`<button class="rounded-xl border border-indigo-200 bg-indigo-50 ${b} font-black text-indigo-700 hover:bg-indigo-100 transition" onclick="editEvent('${ev.uuid}')">Edit</button>`);
        }
        return btns.join('');
    }

    /* ── Event table ── */
    function renderTable() {
        const count = state.events.length;
        els.eventListCount.textContent = count ? `${count} event${count!==1?'s':''}` : '';
        if (!count) {
            els.eventTableBody.innerHTML = '<tr><td colspan="4" class="px-4 py-10 text-center text-slate-400">No events found</td></tr>';
            return;
        }
        els.eventTableBody.innerHTML = state.events.map(ev=>`
            <tr class="hover:bg-slate-50 transition">
                <td class="px-4 py-3 align-top text-xs font-bold text-slate-500 whitespace-nowrap">${formatTimeRange(ev)}</td>
                <td class="px-4 py-3 align-top">
                    <div class="font-black text-slate-900 text-sm">${escapeHtml(ev.title)}</div>
                    <div class="mt-1 flex flex-wrap items-center gap-2">${typeBadge(ev.event_type)}<span class="text-xs text-slate-500">${escapeHtml(ev.scope?.label||'Company-wide')}</span></div>
                </td>
                <td class="px-4 py-3 align-top">${badge(ev.status)}</td>
                <td class="px-4 py-3 align-top"><div class="flex flex-wrap gap-2">${actionButtons(ev, true)}</div></td>
            </tr>`).join('');
    }

    /* ── Modal helpers ── */
    function validateForm() {
        let valid = true;
        [['evTitle','Title is required.'],['evEventType','Event type is required.'],['evStartAt','Start date is required.'],['evEndAt','End date is required.']].forEach(([id, msg]) => {
            const input = el(id);
            const err = input.parentElement.querySelector('.field-error');
            if (!input.value.trim()) {
                if (err) { err.textContent = msg; err.classList.remove('hidden'); }
                input.classList.add('border-rose-300');
                valid = false;
            } else {
                if (err) err.classList.add('hidden');
                input.classList.remove('border-rose-300');
            }
        });
        if (els.evStartAt.value && els.evEndAt.value && els.evEndAt.value < els.evStartAt.value) {
            const err = els.evEndAt.parentElement.querySelector('.field-error');
            if (err) { err.textContent = 'End must be after start.'; err.classList.remove('hidden'); }
            els.evEndAt.classList.add('border-rose-300');
            valid = false;
        }
        return valid;
    }

    function openEventModal(event = null) {
        state.editing = event;
        els.form.reset();
        els.targetsWrap.innerHTML = '';
        state.selectedWeekdays = new Set();
        updateWeekdayButtons();
        addTargetRow();
        els.evStatus.value = 'pending';
        el('deleteEventBtn').classList.add('hidden');

        if (event) {
            els.modalTitle.textContent = 'Edit Event';
            els.eventUuid.value = event.uuid;
            els.evTitle.value = event.title || '';
            els.evEventType.value = event.event_type || '';
            els.evStatus.value = event.status || 'pending';
            els.evStartAt.value = toLocalInputValue(event.start);
            els.evEndAt.value = toLocalInputValue(event.end);
            els.evDescription.value = event.description || '';
            if (event.recurrence_rule) {
                els.recurrenceFrequency.value = event.recurrence_rule.frequency || 'none';
                els.recurrenceInterval.value = event.recurrence_rule.interval || 1;
                els.recurrenceUntil.value = event.recurrence_rule.until || '';
                state.selectedWeekdays = new Set((event.recurrence_rule.days||[]).map(d=>String(d).slice(0,3)));
                updateWeekdayButtons();
            }
            els.targetsWrap.innerHTML = '';
            (event.targets||[]).forEach(t => addTargetRow(t));
            if (!(event.targets||[]).length) addTargetRow();
            el('deleteEventBtn').classList.remove('hidden');
        } else {
            els.modalTitle.textContent = 'New Event';
            els.eventUuid.value = '';
            els.evStartAt.value = toLocalInputValue(state.current.toISOString());
            els.evEndAt.value = toLocalInputValue(new Date(state.current.getTime()+60*60*1000).toISOString());
        }
        // clear field errors
        els.form.querySelectorAll('.field-error').forEach(e => e.classList.add('hidden'));
        els.form.querySelectorAll('input, select').forEach(e => e.classList.remove('border-rose-300'));
        els.modal.classList.add('open');
        els.evTitle.focus();
    }

    function openEventFromView(uuid, sourceType) {
        const ev = state.events.find(e => e.uuid === uuid && e.source_type === sourceType);
        if (!ev) return;
        if (sourceType === 'leave') { openEventModal(ev); return; }
        openEventModal(ev);
    }

    function editEvent(uuid) {
        const ev = state.events.find(e => e.uuid === uuid);
        if (ev) openEventModal(ev);
    }

    function closeModal() {
        els.modal.classList.remove('open');
    }

    /* ── Target rows ── */
    function addTargetRow(target = null) {
        const node = els.targetRowTemplate.content.cloneNode(true);
        const row = node.querySelector('.target-row');
        const type = row.querySelector('.target-type');
        const value = row.querySelector('.target-value');
        type.value = target?.type || target?.target_type || 'company';
        value.value = target?.value || target?.target_value || '';
        value.placeholder = placeholderForType(type.value);
        type.addEventListener('change', () => { value.placeholder = placeholderForType(type.value); });
        row.querySelector('.remove-target').addEventListener('click', () => row.remove());
        els.targetsWrap.appendChild(node);
    }

    function placeholderForType(type) {
        return { company:'Company-wide', employee:'Employee ID', department:'Department name', branch:'Branch name', team:'Team name' }[type] || 'Target value';
    }

    function updateWeekdayButtons() {
        document.querySelectorAll('.weekday-btn').forEach(btn => {
            const active = state.selectedWeekdays.has(btn.dataset.weekday);
            btn.classList.toggle('active', active);
            btn.setAttribute('aria-pressed', String(active));
        });
    }

    function gatherTargets() {
        return [...document.querySelectorAll('.target-row')].map(row => ({
            type: row.querySelector('.target-type').value,
            value: row.querySelector('.target-value').value,
            label: row.querySelector('.target-value').value,
        })).filter(t => t.value || t.type === 'company');
    }

    function gatherRecurrence() {
        const freq = els.recurrenceFrequency.value;
        if (freq === 'none') return null;
        return { frequency: freq, interval: Math.max(1, parseInt(els.recurrenceInterval.value||'1',10)), until: els.recurrenceUntil.value || null, days: [...state.selectedWeekdays] };
    }

    /* ── Save event ── */
    async function saveEvent(e) {
        e.preventDefault();
        if (!validateForm()) return;

        els.saveEventBtn.disabled = true;
        els.saveEventLabel.textContent = 'Saving...';
        els.saveEventSpinner.classList.remove('hidden');

        const payload = {
            title: els.evTitle.value.trim(),
            event_type: els.evEventType.value,
            status: els.evStatus.value,
            start_at: els.evStartAt.value,
            end_at: els.evEndAt.value,
            description: els.evDescription.value.trim(),
            recurrence: gatherRecurrence(),
            targets: gatherTargets(),
        };
        const uuid = els.eventUuid.value;
        try {
            const res = await fetch(uuid ? `/api/calendar/events/${uuid}` : '/api/calendar/events', {
                method: uuid ? 'PUT' : 'POST',
                headers: {'Content-Type':'application/json'},
                body: JSON.stringify(payload),
            });
            const result = await res.json();
            if (!result.success) throw new Error(result.message || 'Unable to save event');
            closeModal();
            showToast(uuid ? 'Event updated successfully.' : 'Event created successfully.', 'success');
            await loadEvents();
        } catch (err) {
            showToast(err.message || 'Save failed', 'error');
        } finally {
            els.saveEventBtn.disabled = false;
            els.saveEventLabel.textContent = 'Save Event';
            els.saveEventSpinner.classList.add('hidden');
        }
    }

    /* ── Delete event ── */
    async function deleteEvent() {
        const uuid = els.eventUuid.value;
        if (!uuid) return;
        const { confirmed } = await openConfirm({ title:'Delete event', message:'This will permanently delete the event. This action cannot be undone.', okLabel:'Delete', okClass:'danger' });
        if (!confirmed) return;
        try {
            const res = await fetch(`/api/calendar/events/${uuid}`, { method:'DELETE' });
            const result = await res.json();
            if (!result.success) throw new Error(result.message || 'Unable to delete event');
            closeModal();
            showToast('Event deleted.', 'info');
            await loadEvents();
        } catch (err) {
            showToast(err.message || 'Delete failed', 'error');
        }
    }

    /* ── Leave approve/reject ── */
    async function approveLeaveRequest(uuid) {
        const { confirmed } = await openConfirm({ title:'Approve leave request', message:'Are you sure you want to approve this leave request?', okLabel:'Approve', okClass:'success-btn' });
        if (!confirmed) return;
        try {
            const res = await fetch(`/api/calendar/leaves/${uuid}/approve`, { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({uuid}) });
            const result = await res.json();
            if (!result.success) throw new Error(result.message || 'Unable to approve');
            showToast('Leave request approved.', 'success');
            await loadEvents();
        } catch (err) {
            showToast(err.message || 'Approval failed', 'error');
        }
    }

    async function rejectLeaveRequest(uuid) {
        const { confirmed, remark } = await openConfirm({ title:'Reject leave request', message:'Provide a reason for rejecting this leave request.', okLabel:'Reject', okClass:'danger', withRemark:true });
        if (!confirmed) return;
        try {
            const res = await fetch(`/api/calendar/leaves/${uuid}/reject`, { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({uuid, remark}) });
            const result = await res.json();
            if (!result.success) throw new Error(result.message || 'Unable to reject');
            showToast('Leave request rejected.', 'info');
            await loadEvents();
        } catch (err) {
            showToast(err.message || 'Rejection failed', 'error');
        }
    }

    /* ── Global handlers (called from inline onclick) ── */
    window.__calendarJump = (date) => {
        state.view = 'day';
        state.current = new Date(`${date}T12:00:00`);
        els.dateJump.value = date;
        document.querySelectorAll('.view-switch').forEach(btn => {
            const active = btn.dataset.viewSwitch === 'day';
            btn.className = active
                ? 'view-switch rounded-2xl bg-white px-4 py-2 text-sm font-bold text-slate-900 shadow-lg shadow-slate-950/20'
                : 'view-switch rounded-2xl border border-white/10 bg-white/10 px-4 py-2 text-sm font-bold text-white transition hover:bg-white/15';
            btn.setAttribute('aria-pressed', String(active));
        });
        loadEvents().catch(console.error);
    };
    window.openEventFromView = openEventFromView;
    window.openEventModal = openEventModal;
    window.editEvent = editEvent;
    window.approveLeaveRequest = approveLeaveRequest;
    window.rejectLeaveRequest = rejectLeaveRequest;

    /* ── Event listeners ── */
    document.querySelectorAll('.view-switch').forEach(btn => {
        btn.addEventListener('click', () => {
            state.view = btn.dataset.viewSwitch;
            document.querySelectorAll('.view-switch').forEach(item => {
                const active = item === btn;
                item.className = active
                    ? 'view-switch rounded-2xl bg-white px-4 py-2 text-sm font-bold text-slate-900 shadow-lg shadow-slate-950/20'
                    : 'view-switch rounded-2xl border border-white/10 bg-white/10 px-4 py-2 text-sm font-bold text-white transition hover:bg-white/15';
                item.setAttribute('aria-pressed', String(active));
            });
            loadEvents().catch(console.error);
        });
    });

    el('prevPeriod').addEventListener('click', () => {
        if (state.view==='month') state.current.setMonth(state.current.getMonth()-1);
        else if (state.view==='week') state.current.setDate(state.current.getDate()-7);
        else state.current.setDate(state.current.getDate()-1);
        els.dateJump.value = localDateKey(state.current);
        loadEvents().catch(console.error);
    });
    el('nextPeriod').addEventListener('click', () => {
        if (state.view==='month') state.current.setMonth(state.current.getMonth()+1);
        else if (state.view==='week') state.current.setDate(state.current.getDate()+7);
        else state.current.setDate(state.current.getDate()+1);
        els.dateJump.value = localDateKey(state.current);
        loadEvents().catch(console.error);
    });
    el('todayPeriod').addEventListener('click', () => { state.current = new Date(); els.dateJump.value = localDateKey(state.current); loadEvents().catch(console.error); });
    el('jumpToday').addEventListener('click', () => { state.current = new Date(); els.dateJump.value = localDateKey(state.current); loadEvents().catch(console.error); });
    el('reloadCalendar').addEventListener('click', () => loadEvents().catch(console.error));
    el('openCreateEvent').addEventListener('click', () => openEventModal());
    el('closeEventModal').addEventListener('click', closeModal);
    el('clearFilters').addEventListener('click', () => {
        state.filters = { employee_id:'', department:'', branch:'', event_type:'', status:'' };
        ['employeeFilter','departmentFilter','branchFilter','eventTypeFilter','statusFilter'].forEach(id => { el(id).value = ''; });
        loadEvents().catch(console.error);
    });
    el('addTargetBtn').addEventListener('click', () => addTargetRow());
    el('deleteEventBtn').addEventListener('click', () => deleteEvent().catch(console.error));
    el('dateJump').addEventListener('change', (e) => { state.current = new Date(`${e.target.value}T12:00:00`); loadEvents().catch(console.error); });

    ['employeeFilter','departmentFilter','branchFilter','eventTypeFilter','statusFilter'].forEach(id => {
        el(id).addEventListener('change', (e) => {
            const key = id.replace('Filter','');
            state.filters[key === 'eventType' ? 'event_type' : key] = e.target.value;
            scheduleReload();
        });
    });

    document.querySelectorAll('.weekday-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const d = btn.dataset.weekday;
            if (state.selectedWeekdays.has(d)) state.selectedWeekdays.delete(d);
            else state.selectedWeekdays.add(d);
            updateWeekdayButtons();
        });
    });

    els.form.addEventListener('submit', (e) => saveEvent(e).catch(err => showToast(err.message,'error')));

    /* ── Keyboard: Escape closes modals ── */
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            if (els.confirmOverlay.classList.contains('open')) { els.confirmCancelBtn.click(); return; }
            if (els.modal.classList.contains('open')) { closeModal(); }
        }
    });

    /* ── Backdrop click closes modals ── */
    els.modal.addEventListener('click', (e) => { if (e.target === els.modal) closeModal(); });
    els.confirmOverlay.addEventListener('click', (e) => { if (e.target === els.confirmOverlay) els.confirmCancelBtn.click(); });

    /* ── Init ── */
    loadOptions()
        .then(() => loadEvents())
        .catch(err => {
            console.error(err);
            showToast('Failed to initialise calendar.', 'error');
        });
})();
</script>
