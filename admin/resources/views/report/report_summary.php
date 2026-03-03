<!-- Add these CDN scripts in your <head> or before </body> -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>

<div class="w-full h-full">
    <div class="bg-white rounded-lg shadow-lg p-4">
        <div class="flex justify-between items-center mb-4">
            <h1 class="text-3xl font-bold text-slate-800">Summary Attendance Report</h1>
            <div class="flex gap-2">
                <button onclick="exportToExcel()" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition flex items-center gap-2">
                    <span>📊</span> Export Excel
                </button>
                <button onclick="exportToPDF()" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition flex items-center gap-2">
                    <span>📄</span> Export PDF
                </button>
            </div>
        </div>

        <!-- Date Range -->
        <div class="bg-gray-50 rounded-lg p-4 mb-4">
            <h2 class="text-lg font-semibold text-gray-700 mb-4">📅 Select Report Period</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div class="flex gap-4">
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700 mb-2">From Date:</label>
                        <input type="date" id="fromDate" class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700 mb-2">To Date:</label>
                        <input type="date" id="toDate" class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                </div>

                <div class="flex gap-4">
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Department:</label>
                        <select id="departmentFilter" class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">All Departments</option>
                            <option value="IT">IT Department</option>
                            <option value="HR">HR Department</option>
                            <option value="Sales">Sales Department</option>
                            <option value="Finance">Finance Department</option>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button onclick="generateReport()" class="bg-blue-600 text-white px-8 py-2 rounded-lg hover:bg-blue-700 transition font-medium">
                            Generate Report
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Employee Summary Table -->
        <h3 class="text-lg font-semibold text-gray-700 mb-4">👥 Employee Attendance Summary</h3>
        <div class="overflow-x-auto shadow-md rounded-lg mb-6">
            <table class="min-w-full bg-white border border-gray-200">
                <thead class="bg-slate-800 text-white">
                    <tr>
                        <th class="p-3 text-left">Employee Name</th>
                        <th class="p-3 text-center">ID</th>
                        <th class="p-3 text-center">Department</th>
                        <th class="p-3 text-center">Total Days</th>
                        <th class="p-3 text-center">Present</th>
                        <th class="p-3 text-center">Absent</th>
                        <th class="p-3 text-center">Late</th>
                        <th class="p-3 text-center">Leave</th>
                        <th class="p-3 text-center">Attendance %</th>
                        <th class="p-3 text-center">Performance</th>
                    </tr>
                </thead>
                <tbody id="summaryTableBody">
                    <tr>
                        <td colspan="10" class="p-4 text-center text-gray-500">⏳ Loading...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    // Store report data globally for export
    let reportData = [];

    function setDefaultDates() {
        const today = new Date();

        // fromDate = 1st day of THIS month
        const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);

        // toDate = TODAY
        document.getElementById('fromDate').value = firstDay.toISOString().split('T')[0];
        document.getElementById('toDate').value = today.toISOString().split('T')[0];

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

    function generateReport() {
        const from = document.getElementById('fromDate').value;
        const to = document.getElementById('toDate').value;
        const department = document.getElementById('departmentFilter').value;

        if (!from || !to) {
            alert('Please select both dates');
            return;
        }

        document.getElementById('summaryTableBody').innerHTML = `
            <tr>
                <td colspan="10" class="p-4 text-center text-gray-500">⏳ Loading...</td>
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
                    <td colspan="10" class="p-4 text-center text-gray-500">No data found for selected period</td>
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
            const percent = total > 0 ? ((present / total) * 100).toFixed(1) : '0.0';

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
                    <td class="p-3 text-center text-red-600">${absent}</td>
                    <td class="p-3 text-center text-yellow-600">${late}</td>
                    <td class="p-3 text-center text-gray-600">${leave}</td>
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
            const percent = total > 0 ? ((present / total) * 100).toFixed(1) : '0.0';
            return {
                'Employee Name' : r.full_name ?? 'N/A',
                'ID'            : r.id ?? '-',
                'Department'    : r.department ?? 'N/A',
                'Total Days'    : total,
                'Present'       : present,
                'Absent'        : Number(r.absent_days  || 0),
                'Late'          : Number(r.late_days    || 0),
                'Leave'         : Number(r.leave_days   || 0),
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
            const percent = total > 0 ? ((present / total) * 100).toFixed(1) + '%' : '0.0%';
            return [
                r.full_name    ?? 'N/A',
                r.id           ?? '-',
                r.department   ?? 'N/A',
                total,
                present,
                Number(r.absent_days || 0),
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