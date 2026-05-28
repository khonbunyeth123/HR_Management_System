<!-- <body class="bg-gradient-to-br from-slate-50 to-slate-100 min-h-screen"></body> -->
<div class="w-full h-full"> 
    <div class="p-2">
        <!-- Header with Filters -->
        <div class="bg-white rounded-lg shadow-sm p-4 mb-4">
            <div class="flex flex-col gap-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <iconify-icon icon="mdi:calendar-check" style="font-size: 24px; color: #4f46e5;"></iconify-icon>
                        <h1 class="text-lg font-bold text-gray-900">Leave Applications</h1>
                    </div>
                    <span class="text-xs font-semibold bg-indigo-100 text-indigo-700 px-3 py-1 rounded-full" id="totalCount">0 Applications</span>
                </div>

                <!-- Search & Filter Bar -->
                <div class="flex flex-col sm:flex-row gap-2">
                    <div class="flex-1 relative">
                        <iconify-icon icon="mdi:magnify" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: #9ca3af; font-size: 18px;"></iconify-icon>
                        <input type="text" id="searchInput" placeholder="Search by employee name..." class="w-full pl-9 pr-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    </div>
                    <select id="leaveTypeFilter" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white cursor-pointer">
                        <option value="">All Leave Types</option>
                    </select>
                    <select id="statusFilter" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white cursor-pointer">
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
            <div class="overflow-x-auto flex flex-col">
                <table class="w-full text-sm flex-1">
                    <thead class="bg-gradient-to-b from-slate-900 via-slate-800 to-slate-900 shadow-lg text-white sticky top-0">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold">Employee</th>
                            <th class="px-4 py-3 text-left font-semibold">Leave Type</th>
                            <th class="px-4 py-3 text-left font-semibold">Start Date</th>
                            <th class="px-4 py-3 text-left font-semibold">End Date</th>
                            <th class="px-4 py-3 text-left font-semibold">Reason</th>
                            <th class="px-4 py-3 text-left font-semibold">Status</th>
                            <th class="px-4 py-3 text-left font-semibold">Created</th>
                            <th class="px-4 py-3 text-left font-semibold">Action</th>
                        </tr>
                    </thead>
                    <tbody id="leaveTableBody" class="divide-y divide-gray-100">
                        <tr>
                            <td colspan="8" class="px-4 py-6 text-center text-gray-400">
                                <div class="flex items-center justify-center gap-2">
                                    <iconify-icon icon="mdi:loading" style="font-size: 20px;" class="animate-spin"></iconify-icon>
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
    <div id="leaveDecisionModal" class="hidden fixed inset-0 z-50 items-center justify-center bg-slate-950/50 p-4">
        <div class="w-full max-w-md rounded-lg bg-white shadow-xl">
            <div class="border-b border-gray-100 px-5 py-4">
                <h2 id="leaveDecisionTitle" class="text-lg font-bold text-gray-900">Review Leave</h2>
                <p id="leaveDecisionText" class="mt-1 text-sm text-gray-600">Confirm this action.</p>
            </div>
            <div class="space-y-4 px-5 py-4">
                <div id="rejectReasonWrap" class="hidden">
                    <label for="rejectReason" class="mb-2 block text-sm font-semibold text-gray-700">Reject reason *</label>
                    <textarea id="rejectReason" rows="4"
                        class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-transparent focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        placeholder="Explain why this request is rejected"></textarea>
                    <p id="rejectReasonError" class="mt-2 hidden text-xs font-semibold text-red-600">Please enter a reject reason.</p>
                </div>
                <p id="leaveDecisionError" class="hidden rounded-lg bg-red-50 px-3 py-2 text-sm font-semibold text-red-700"></p>
            </div>
            <div class="flex justify-end gap-2 border-t border-gray-100 px-5 py-4">
                <button type="button" onclick="closeLeaveDecisionModal()"
                    class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">
                    Cancel
                </button>
                <button type="button" id="confirmLeaveDecision" onclick="submitLeaveDecision()"
                    class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-60">
                    <iconify-icon icon="mdi:check"></iconify-icon>
                    <span id="confirmLeaveDecisionText">Confirm</span>
                </button>
            </div>
        </div>
    </div>

    <div id="leaveFeedback" class="hidden fixed right-4 top-20 z-50 max-w-sm rounded-lg border-l-4 bg-white p-4 shadow-lg">
        <p id="leaveFeedbackTitle" class="font-semibold text-gray-900"></p>
        <p id="leaveFeedbackMessage" class="mt-1 text-sm text-gray-600"></p>
    </div>

    <script src="/assets/js/pagination.js"></script>
    <script>
        const perPage = 5;
        let currentPage = 1;
        let totalPages = 1;
        let pendingLeaveDecision = null;

        // Helper: Status badge
        function getStatusBadge(statusId) {
            if (statusId == 0) return '<span class="bg-yellow-100 text-yellow-700 px-2 py-1 rounded text-xs font-semibold">Pending</span>';
            if (statusId == 1) return '<span class="bg-green-100 text-green-700 px-2 py-1 rounded text-xs font-semibold">Approved</span>';
            if (statusId == 2) return '<span class="bg-red-100 text-red-700 px-2 py-1 rounded text-xs font-semibold">Rejected</span>';
            return '<span class="bg-gray-100 text-gray-700 px-2 py-1 rounded text-xs font-semibold">Unknown</span>';
        }

        function showFeedback(title, message, type = 'success') {
            const box = document.getElementById('leaveFeedback');
            document.getElementById('leaveFeedbackTitle').textContent = title;
            document.getElementById('leaveFeedbackMessage').textContent = message;
            box.className = `fixed right-4 top-20 z-50 max-w-sm rounded-lg border-l-4 bg-white p-4 shadow-lg ${
                type === 'success' ? 'border-green-500' : 'border-red-500'
            }`;
            box.classList.remove('hidden');
            setTimeout(() => box.classList.add('hidden'), 3000);
        }

        function setDecisionLoading(isLoading) {
            const btn = document.getElementById('confirmLeaveDecision');
            const label = document.getElementById('confirmLeaveDecisionText');
            const isReject = pendingLeaveDecision?.action === 'reject';
            btn.disabled = isLoading;
            label.textContent = isLoading ? 'Processing...' : (isReject ? 'Reject Leave' : 'Approve Leave');
        }

        function openLeaveDecisionModal(action, uuid) {
            pendingLeaveDecision = { action, uuid };
            const isReject = action === 'reject';

            document.getElementById('leaveDecisionTitle').textContent = isReject ? 'Reject Leave Request' : 'Approve Leave Request';
            document.getElementById('leaveDecisionText').textContent = isReject
                ? 'Add a clear reason so the employee understands the decision.'
                : 'Confirm that this employee can take the requested leave.';
            document.getElementById('rejectReasonWrap').classList.toggle('hidden', !isReject);
            document.getElementById('rejectReason').value = '';
            document.getElementById('rejectReasonError').classList.add('hidden');
            document.getElementById('leaveDecisionError').classList.add('hidden');
            document.getElementById('confirmLeaveDecision').className = `inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold text-white transition disabled:cursor-not-allowed disabled:opacity-60 ${
                isReject ? 'bg-red-600 hover:bg-red-700' : 'bg-green-600 hover:bg-green-700'
            }`;
            document.getElementById('confirmLeaveDecisionText').textContent = isReject ? 'Reject Leave' : 'Approve Leave';
            document.getElementById('leaveDecisionModal').classList.remove('hidden');
            document.getElementById('leaveDecisionModal').classList.add('flex');

            if (isReject) {
                setTimeout(() => document.getElementById('rejectReason').focus(), 50);
            }
        }

        function closeLeaveDecisionModal() {
            if (document.getElementById('confirmLeaveDecision').disabled) return;
            pendingLeaveDecision = null;
            document.getElementById('leaveDecisionModal').classList.add('hidden');
            document.getElementById('leaveDecisionModal').classList.remove('flex');
        }

        // Load leave applications from server
        function loadLeaveApplications(page = 1) {
            const search = document.getElementById("searchInput").value;
            const leaveType = document.getElementById("leaveTypeFilter").value;
            const status = document.getElementById("statusFilter").value;

            const params = new URLSearchParams({
                "paging_options[page]": page,
                "paging_options[per_page]": perPage,
                "filters[employee_name]": search,
                "filters[leave_type]": leaveType,
                "filters[status_id]": status
            });

            fetch("/api/leave/list?" + params.toString())
                .then(res => res.json())
                .then(result => {
                    if (result.success && result.data) {
                        renderTable(result.data.leave_applications);

                        currentPage = result.pagination.page;
                        totalPages = result.pagination.total_pages;
                        document.getElementById("totalCount").textContent = result.pagination.total + " Applications";

                        // Populate leave type filter once
                        const typeSelect = document.getElementById("leaveTypeFilter");
                        typeSelect.innerHTML = '<option value="">All Leave Types</option>';
                        result.data.leave_types?.forEach(type => {
                            const opt = document.createElement("option");
                            opt.value = type;
                            opt.textContent = type;
                            typeSelect.appendChild(opt);
                        });

                        renderPagination({
                            currentPage,
                            totalPages,
                            showingFrom: (currentPage-1)*perPage +1,
                            showingTo: Math.min(currentPage*perPage, result.pagination.total),
                            totalRecords: result.pagination.total,
                            showPageNumbers: true,
                            onPrevious: () => currentPage > 1 && loadLeaveApplications(currentPage -1),
                            onNext: () => currentPage < totalPages && loadLeaveApplications(currentPage +1),
                            onPageClick: (p) => loadLeaveApplications(p)
                        });
                    } else {
                        document.getElementById("leaveTableBody").innerHTML = '<tr><td colspan="8" class="px-4 py-6 text-center text-gray-400">No leave applications found</td></tr>';
                    }
                })
                .catch(err => {
                    console.error(err);
                    document.getElementById("leaveTableBody").innerHTML = '<tr><td colspan="8" class="px-4 py-6 text-center text-red-500">Error loading data</td></tr>';
                });
        }

        // Render table rows
        function renderTable(records) {
            const tbody = document.getElementById("leaveTableBody");
            if (!records.length) {
                tbody.innerHTML = '<tr><td colspan="8" class="px-4 py-6 text-center text-gray-400">No leave applications found</td></tr>';
                return;
            }

            tbody.innerHTML = records.map(rec => `
                <tr class="hover:bg-indigo-50 transition-colors">
                    <td class="px-4 py-3 font-medium text-gray-900">${rec.employee_name}</td>
                    <td class="px-4 py-3"><span class="bg-indigo-100 text-indigo-700 px-2 py-1 rounded text-xs font-semibold">${rec.leave_type}</span></td>
                    <td class="px-4 py-3 text-gray-600 text-sm">${new Date(rec.start_date).toLocaleDateString('en-US',{month:'short',day:'numeric',year:'numeric'})}</td>
                    <td class="px-4 py-3 text-gray-600 text-sm">${new Date(rec.end_date).toLocaleDateString('en-US',{month:'short',day:'numeric',year:'numeric'})}</td>
                    <td class="px-4 py-3 text-gray-600 text-xs max-w-xs truncate">${rec.reason || '-'}</td>
                    <td class="px-4 py-3">${getStatusBadge(rec.status_id)}</td>
                    <td class="px-4 py-3 text-gray-500 text-xs">${new Date(rec.created_at).toLocaleDateString('en-US',{month:'short',day:'numeric'})}</td>
                    <td class="px-4 py-3">
                    ${rec.status_id == 0 ? `
                            <div class="flex items-center gap-2">
                                <button
                                    onclick="approveLeave('${rec.uuid}')"
                                    class="inline-flex items-center gap-1 rounded-md bg-green-100 px-3 py-1.5 text-xs font-semibold text-green-700 hover:bg-green-200"
                                    title="Approve"
                                >
                                    <span>Approve</span>
                                </button>

                                <button
                                    onclick="rejectLeave('${rec.uuid}')"
                                    class="inline-flex items-center gap-1 rounded-md bg-red-100 px-3 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-200"
                                    title="Reject"
                                >
                                    <span>Reject</span>
                                </button>
                            </div>
                        ` : `
                            <span class="text-gray-400">—</span>
                        `}
                    </td>

                </tr>
            `).join('');
        }

        // Approve / Reject leave
        function approveLeave(uuid) {
            openLeaveDecisionModal('approve', uuid);
        }

        function rejectLeave(uuid) {
            openLeaveDecisionModal('reject', uuid);
        }

        function submitLeaveDecision() {
            if (!pendingLeaveDecision) return;

            const isReject = pendingLeaveDecision.action === 'reject';
            const remark = document.getElementById('rejectReason').value.trim();
            const reasonError = document.getElementById('rejectReasonError');
            const modalError = document.getElementById('leaveDecisionError');

            reasonError.classList.add('hidden');
            modalError.classList.add('hidden');

            if (isReject && !remark) {
                reasonError.classList.remove('hidden');
                document.getElementById('rejectReason').focus();
                return;
            }

            setDecisionLoading(true);

            fetch(isReject ? "/api/leave/reject" : "/api/leave/approve", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify(isReject
                    ? { uuid: pendingLeaveDecision.uuid, remark }
                    : { uuid: pendingLeaveDecision.uuid })
            })
            .then(res => res.json())
            .then(data => {
                setDecisionLoading(false);

                if (data.success) {
                    closeLeaveDecisionModal();
                    showFeedback('Leave updated', data.message || 'The leave request was updated.');
                    loadLeaveApplications(currentPage);
                } else {
                    modalError.textContent = data.message || 'Unable to update this leave request.';
                    modalError.classList.remove('hidden');
                }
            })
            .catch(err => {
                setDecisionLoading(false);
                modalError.textContent = 'Server error. Please try again.';
                modalError.classList.remove('hidden');
                console.error(err);
            });
        }

        document.getElementById('leaveDecisionModal').addEventListener('click', (event) => {
            if (event.target.id === 'leaveDecisionModal') {
                closeLeaveDecisionModal();
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closeLeaveDecisionModal();
            }
        });


        // Event listeners
        document.getElementById("searchInput").addEventListener("input", ()=>loadLeaveApplications(1));
        document.getElementById("leaveTypeFilter").addEventListener("change", ()=>loadLeaveApplications(1));
        document.getElementById("statusFilter").addEventListener("change", ()=>loadLeaveApplications(1));

        // Initial load
        loadLeaveApplications(1);
    </script>

