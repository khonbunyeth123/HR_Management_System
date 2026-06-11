<div class="w-full h-full">
    <div class="bg-white rounded-lg shadow-sm p-3 border border-slate-100">
        <h1 class="text-sm font-bold text-slate-800 mb-3">Daily Attendance Report</h1>

        <!-- Date Picker -->
        <div class="mb-3 flex items-center gap-2">
            <label class="text-[10px] font-medium text-gray-500">Select Date:</label>
            <input type="date" id="selectedDate" class="p-1 border border-gray-200 rounded-lg text-[10px] focus:ring-1 focus:ring-blue-500" value="">
            <button onclick="loadReport()" class="bg-blue-600 text-white px-3 py-1 rounded-lg text-[10px] font-bold hover:bg-blue-700 transition">Load</button>
        </div>

        <!-- Attendance Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border border-gray-200 rounded-lg text-[10px]">
                <thead class="bg-slate-800 text-white">
                    <tr>
                        <th class="p-2 text-left">Employee</th>
                        <th class="p-2 text-center">Emp ID</th>
                        <th class="p-2 text-center">In 1</th>
                        <th class="p-2 text-center">Out 1</th>
                        <th class="p-2 text-center">In 2</th>
                        <th class="p-2 text-center">Out 2</th>
                        <th class="p-2 text-center">Status</th>
                    </tr>
                </thead>
                <tbody id="attendanceTableBody">
                    <tr>
                        <td colspan="7" class="text-center p-3 text-gray-500">No data loaded</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Summary Stats -->
        <div class="grid grid-cols-5 gap-2 mt-3 text-[10px]">
            <div class="bg-green-50 p-2 rounded-lg border border-green-200 text-center">
                <p class="text-green-600 font-medium">On Time</p>
                <p class="text-lg font-bold text-green-800" id="summaryOnTime">0</p>
            </div>
            <div class="bg-yellow-50 p-2 rounded-lg border border-yellow-200 text-center">
                <p class="text-yellow-600 font-medium">Late</p>
                <p class="text-lg font-bold text-yellow-800" id="summaryLate">0</p>
            </div>
            <div class="bg-red-50 p-2 rounded-lg border border-red-200 text-center">
                <p class="text-red-600 font-medium">Absent</p>
                <p class="text-lg font-bold text-red-800" id="summaryAbsent">0</p>
            </div>
            <div class="bg-orange-50 p-2 rounded-lg border border-orange-200 text-center">
                <p class="text-orange-600 font-medium">Inc.</p>
                <p class="text-lg font-bold text-orange-800" id="summaryIncomplete">0</p>
            </div>
            <div class="bg-blue-50 p-2 rounded-lg border border-blue-200 text-center">
                <p class="text-blue-600 font-medium">Total</p>
                <p class="text-lg font-bold text-blue-800" id="summaryTotal">0</p>
            </div>
        </div>
    </div>
</div>

<script>
function getCurrentDateString() {
    const now = new Date();
    const offset = now.getTimezoneOffset();
    const localDate = new Date(now.getTime() - offset * 60000);
    return localDate.toISOString().split('T')[0];
}

async function loadReport() {
    const selectedDate = document.getElementById('selectedDate').value;

    try {
        const response = await fetch(`/api/report/daily?date=${selectedDate}`);
        if (!response.ok) throw new Error('Network response was not ok');

        const apiResponse = await response.json();
        
        // Extract the data array from the response object
        const data = apiResponse.data;

        const tbody = document.getElementById('attendanceTableBody');
        tbody.innerHTML = ''; // Clear previous rows

        // Summary counters
        let onTime = 0, late = 0, absent = 0, incomplete = 0;

        if (data.length === 0) {
            tbody.innerHTML = `<tr>
                <td colspan="7" class="text-center p-4 text-gray-500">No records found for ${selectedDate}</td>
            </tr>`;
        } else {
            data.forEach(emp => {
                const status = emp.status;

                if (status === 'On Time') onTime++;
                else if (status === 'Late') late++;
                else if (status === 'Absent') absent++;
                else if (status === 'Incomplete') incomplete++;

                const colorMap = {
                    'On Time': 'green',
                    'Late': 'yellow',
                    'Absent': 'red',
                    'Incomplete': 'orange'
                };
                const color = colorMap[status] || 'blue';
                const punch1In  = renderPunchCell(emp.check_in_1, emp.check_in_1_note);
                const punch1Out = renderPunchCell(emp.check_out_1, emp.check_out_1_note);
                const punch2In  = renderPunchCell(emp.check_in_2, emp.check_in_2_note);
                const punch2Out = renderPunchCell(emp.check_out_2, emp.check_out_2_note);

                tbody.innerHTML += `
                    <tr class="border-b hover:bg-gray-50">
                        <td class="p-3">${emp.name}</td>
                        <td class="p-3 text-center">${emp.employee_id}</td>
                        <td class="p-3 text-center">${punch1In}</td>
                        <td class="p-3 text-center">${punch1Out}</td>
                        <td class="p-3 text-center">${punch2In}</td>
                        <td class="p-3 text-center">${punch2Out}</td>
                        <td class="p-3 text-center">
                            <span class="bg-${color}-100 text-${color}-800 px-3 py-1 rounded-full text-sm font-medium">${status}</span>
                        </td>
                    </tr>
                `;
            });
        }

        // Update summary stats
        document.getElementById('summaryOnTime').innerText = onTime;
        document.getElementById('summaryLate').innerText = late;
        document.getElementById('summaryAbsent').innerText = absent;
        document.getElementById('summaryIncomplete').innerText = incomplete;
        document.getElementById('summaryTotal').innerText = data.length;

    } catch (error) {
        console.error('Error fetching report:', error);
        alert('Failed to load report. Check console for details.');
    }
}

function renderPunchCell(timeValue, note) {
    const safeTime = timeValue || '--:--';
    const safeNote = note || 'No record';
    const tone = punchToneClass(safeNote);

    return `
        <div class="flex flex-col items-center gap-1">
            <span class="font-semibold ${tone.text}">${safeTime}</span>
            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-widest ${tone.badge}">
                ${safeNote}
            </span>
        </div>
    `;
}

function punchToneClass(note) {
    const normalized = String(note || '').toLowerCase();

    if (normalized.includes('late')) {
        return {
            text: 'text-amber-600',
            badge: 'bg-amber-100 text-amber-700'
        };
    }

    if (normalized.includes('early')) {
        return {
            text: 'text-orange-600',
            badge: 'bg-orange-100 text-orange-700'
        };
    }

    if (normalized.includes('on time')) {
        return {
            text: 'text-emerald-600',
            badge: 'bg-emerald-100 text-emerald-700'
        };
    }

    if (normalized.includes('overtime')) {
        return {
            text: 'text-amber-600',
            badge: 'bg-amber-100 text-amber-700'
        };
    }

    if (normalized.includes('recorded')) {
        return {
            text: 'text-slate-600',
            badge: 'bg-slate-100 text-slate-600'
        };
    }

    return {
        text: 'text-slate-500',
        badge: 'bg-slate-100 text-slate-500'
    };
}

// Optional: load today by default
document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('selectedDate').value = getCurrentDateString();
    loadReport();
});
</script>
