<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>

<div class="w-full h-full">
    <div class="bg-white rounded-lg shadow-sm p-3 border border-slate-100">
        <div class="flex justify-between items-center mb-3">
            <h1 class="text-sm font-bold text-slate-800">Summary Report</h1>
            <div class="flex gap-1">
                <button onclick="exportToExcel()" class="bg-green-600 text-white px-2 py-1 rounded-lg text-[10px] font-bold hover:bg-green-700 transition">Excel</button>
                <button onclick="exportToPDF()" class="bg-red-600 text-white px-2 py-1 rounded-lg text-[10px] font-bold hover:bg-red-700 transition">PDF</button>
            </div>
        </div>

        <!-- Date Range -->
        <div class="bg-slate-50 rounded-lg p-2 mb-3">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-2">
                <div class="flex flex-col gap-0.5">
                    <label class="text-[9px] font-bold text-slate-500 uppercase">From:</label>
                    <input type="date" id="fromDate" class="p-1 border border-gray-200 rounded-lg text-[10px] focus:ring-1 focus:ring-blue-500">
                </div>
                <div class="flex flex-col gap-0.5">
                    <label class="text-[9px] font-bold text-slate-500 uppercase">To:</label>
                    <input type="date" id="toDate" class="p-1 border border-gray-200 rounded-lg text-[10px] focus:ring-1 focus:ring-blue-500">
                </div>
                <div class="flex flex-col gap-0.5">
                    <label class="text-[9px] font-bold text-slate-500 uppercase">Dept:</label>
                    <select id="departmentFilter" class="p-1 border border-gray-200 rounded-lg text-[10px] focus:ring-1 focus:ring-blue-500">
                        <option value="">All Departments</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button onclick="generateReport()" class="w-full bg-blue-600 text-white px-3 py-1.5 rounded-lg text-[10px] font-bold hover:bg-blue-700 transition">Generate</button>
                </div>
            </div>
        </div>

        <!-- Employee Summary Table -->
        <h3 class="text-[11px] font-bold text-slate-800 mb-2">👥 Attendance Summary</h3>
        <div class="overflow-x-auto border border-gray-200 rounded-lg">
            <table class="min-w-full bg-white text-[10px]">
                <thead class="bg-slate-800 text-white">
                    <tr>
                        <th class="p-2 text-left">Employee</th>
                        <th class="p-2 text-center">ID</th>
                        <th class="p-2 text-center">Dept</th>
                        <th class="p-2 text-center">Total</th>
                        <th class="p-2 text-center">Pres</th>
                        <th class="p-2 text-center">Late</th>
                        <th class="p-2 text-center">Leave</th>
                        <th class="p-2 text-center">Off</th>
                        <th class="p-2 text-center">Abs</th>
                        <th class="p-2 text-center">%</th>
                        <th class="p-2 text-center">Perf</th>
                    </tr>
                </thead>
                <tbody id="summaryTableBody">
                    <tr>
                        <td colspan="11" class="p-3 text-center text-gray-500">⏳ Loading...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    // Store report data globally for export
    let reportData = [];

    function getCurrentDateString() {
        const now = new Date();
        const offset = now.getTimezoneOffset();
        const localDate = new Date(now.getTime() - offset * 60000);
        return localDate.toISOString().split('T')[0];
    }

    function setDefaultDates() {
        const today = new Date();

        // fromDate = 1st day of THIS month
        const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);

        // toDate = TODAY (using local timezone)
        document.getElementById('fromDate').value = firstDay.toISOString().split('T')[0];
        document.getElementById('toDate').value = getCurrentDateString();

        // Auto load table on page load
        generateReport();
    }

    // Auto refresh at midnight (for 24/7 dashboards)
    function scheduleAutoRefresh() {
        const now = new Date();
        const midnight = new Date(now.getFullYear(), now.getMonth(), now.getDate() + 1, 0, 0, 0);
        const msUntilMidnight = midnight - now;

        setTimeout(() => {
            setDefaultDates();
            scheduleAutoRefresh();
        }, msUntilMidnight);
    }

    // When fromDate changes, toDate auto-follows to last day of that month
    document.getElementById('fromDate').addEventListener('change', function () {
        const selected = new Date(this.value + 'T00:00:00');
        const lastDay = new Date(selected.getFullYear(), selected.getMonth() + 1, 0);
        document.getElementById('toDate').value = lastDay.toISOString().split('T')[0];
    });

    // Run on page load
    setDefaultDates();
    scheduleAutoRefresh();
    fetchDepartments();

    async function fetchDepartments() {
        try {
            const res = await fetch('/api/employees/departments');
            const json = await res.json();
            if (json.success) {
                const select = document.getElementById('departmentFilter');
                // Keep the "All Departments" option
                select.innerHTML = '<option value="">All Departments</option>';
                json.data.forEach(dept => {
                    const opt = document.createElement('option');
                    opt.value = dept;
                    opt.textContent = dept + ' Department';
                    select.appendChild(opt);
                });
            }
        } catch (err) {
            console.error('Failed to fetch departments:', err);
        }
    }

    function generateReport() {
        const from = document.getElementById('fromDate').value;
        const to = document.getElementById('toDate').value;
        const department = document.getElementById('departmentFilter').value;
        const summaryBody = document.getElementById('summaryTableBody');

        if (!from || !to) {
            alert('Please select both dates');
            return;
        }

        summaryBody.innerHTML = `
            <tr>
                <td colspan="12" class="p-4 text-center text-gray-500">⏳ Loading...</td>
            </tr>
        `;

        fetch(`/api/report/summary?from=${encodeURIComponent(from)}&to=${encodeURIComponent(to)}${department ? `&department=${encodeURIComponent(department)}` : ''}`)
            .then(res => res.json())
            .then(res => {
                if (!res.success) {
                    alert(res.message || 'Failed to load report');
                    return;
                }
                reportData = res.data; // save for export
                renderTable(res.data);
            })
            .catch(err => {
                console.error(err);
                alert('Error: ' + err.message);
            });
    }

    function renderTable(rows) {
        const tbody = document.getElementById('summaryTableBody');
        tbody.innerHTML = '';

        if (!rows || rows.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="12" class="p-4 text-center text-gray-500">No data found for selected period</td>
                </tr>
            `;
            return;
        }

        rows.forEach(r => {
            const total   = Number(r.total_days   || 0);
            const present = Number(r.present_days || 0);
            const late    = Number(r.late_days    || 0);
            const absent  = Number(r.absent_days  || 0);
            const leave   = Number(r.leave_days   || 0);
            const dayOff  = Number(r.day_off_days  || 0);
            const workdays = present + absent;
            const percent = workdays > 0 ? ((present / workdays) * 100).toFixed(1) : '0.0';

            let stars = '⭐⭐';
            if (percent >= 95)      stars = '⭐⭐⭐⭐⭐';
            else if (percent >= 90) stars = '⭐⭐⭐⭐';
            else if (percent >= 80) stars = '⭐⭐⭐';

            tbody.innerHTML += `
                <tr class="border-b hover:bg-gray-50">
                    <td class="p-3 font-medium">${r.full_name ?? 'N/A'}</td>
                    <td class="p-3 text-center text-gray-600">${r.id ?? '-'}</td>
                    <td class="p-3 text-center">
                        <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs">${r.department ?? 'N/A'}</span>
                    </td>
                    <td class="p-3 text-center font-semibold">${total}</td>
                    <td class="p-3 text-center text-green-600 font-semibold">${present}</td>
                    <td class="p-3 text-center text-yellow-600">${late}</td>
                    <td class="p-3 text-center text-gray-600">${leave}</td>
                    <td class="p-3 text-center text-slate-600">${dayOff}</td>
                    <td class="p-3 text-center text-red-600">${absent}</td>
                    <td class="p-3 text-center">
                        <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-bold">${percent}%</span>
                    </td>
                    <td class="p-3 text-center">${stars}</td>
                </tr>
            `;
        });
    }

    // ============ EXPORT EXCEL ============
    function exportToExcel() {
        if (!reportData || reportData.length === 0) {
            alert('No data to export. Please generate report first.');
            return;
        }

        const from = document.getElementById('fromDate').value;
        const to   = document.getElementById('toDate').value;

        // Build rows
        const rows = reportData.map(r => {
            const total   = Number(r.total_days   || 0);
            const present = Number(r.present_days || 0);
            const absent  = Number(r.absent_days  || 0);
            const workdays = present + absent;
            const percent = workdays > 0 ? ((present / workdays) * 100).toFixed(1) : '0.0';
            return {
                'Employee Name' : r.full_name ?? 'N/A',
                'ID'            : r.id ?? '-',
                'Department'    : r.department ?? 'N/A',
                'Total Days'    : total,
                'Present'       : present,
                'Late'          : Number(r.late_days    || 0),
                'Leave'         : Number(r.leave_days   || 0),
                'Day Off'       : Number(r.day_off_days || 0),
                'Absent'        : Number(r.absent_days  || 0),
                'Attendance %'  : percent + '%',
            };
        });

        const ws = XLSX.utils.json_to_sheet(rows);
        const wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, 'Summary Report');

        // Auto column width
        const colWidths = Object.keys(rows[0]).map(key => ({ wch: Math.max(key.length, 15) }));
        ws['!cols'] = colWidths;

        XLSX.writeFile(wb, `attendance_summary_${from}_to_${to}.xlsx`);
    }

    // ============ EXPORT PDF ============
    function exportToPDF() {
        if (!reportData || reportData.length === 0) {
            alert('No data to export. Please generate report first.');
            return;
        }

        const from = document.getElementById('fromDate').value;
        const to   = document.getElementById('toDate').value;
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF('l', 'mm', 'a4'); // landscape A4

        // Title
        doc.setFontSize(16);
        doc.setFont('helvetica', 'bold');
        doc.text('Summary Attendance Report', 14, 15);

        // Date range
        doc.setFontSize(10);
        doc.setFont('helvetica', 'normal');
        doc.text(`Period: ${from} to ${to}`, 14, 23);

        // Table
        const headers = [['Employee Name', 'ID', 'Department', 'Total Days', 'Present', 'Absent', 'Late', 'Leave', 'Attendance %']];
        const body = reportData.map(r => {
            const total   = Number(r.total_days   || 0);
            const present = Number(r.present_days || 0);
            const absent  = Number(r.absent_days  || 0);
            const workdays = present + absent;
            const percent = workdays > 0 ? ((present / workdays) * 100).toFixed(1) + '%' : '0.0%';
            return [
                r.full_name    ?? 'N/A',
                r.id           ?? '-',
                r.department   ?? 'N/A',
                total,
                present,
                absent,
                Number(r.late_days   || 0),
                Number(r.leave_days  || 0),
                percent
            ];
        });

        doc.autoTable({
            head: headers,
            body: body,
            startY: 28,
            styles: { fontSize: 9, cellPadding: 3 },
            headStyles: { fillColor: [30, 41, 59], textColor: 255, fontStyle: 'bold' },
            alternateRowStyles: { fillColor: [248, 250, 252] },
            columnStyles: {
                0: { cellWidth: 45 },
                1: { cellWidth: 15, halign: 'center' },
                2: { cellWidth: 30, halign: 'center' },
                3: { cellWidth: 25, halign: 'center' },
                4: { cellWidth: 22, halign: 'center' },
                5: { cellWidth: 20, halign: 'center' },
                6: { cellWidth: 18, halign: 'center' },
                7: { cellWidth: 18, halign: 'center' },
                8: { cellWidth: 28, halign: 'center' },
            }
        });

        doc.save(`attendance_summary_${from}_to_${to}.pdf`);
    }
</script>

