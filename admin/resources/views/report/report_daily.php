<div class="w-full h-full">
    <div class="bg-white shadow-lg p-4">
        <h1 class="text-3xl font-bold text-slate-800 mb-6">Daily Attendance Report</h1>

        <!-- Date Picker -->
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Select Date:</label>
            <input type="date" id="selectedDate" class="p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" value="">
            <button onclick="loadReport()" class="ml-3 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">Load Report</button>
        </div>

        <!-- Attendance Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border border-gray-200 rounded-lg">
                <thead class="bg-slate-800 text-white">
                    <tr>
                        <th class="p-3 text-left">Employee Name</th>
                        <th class="p-3 text-center">Employee ID</th>
                        <th class="p-3 text-center">Check-in 1</th>
                        <th class="p-3 text-center">Check-out 1</th>
                        <th class="p-3 text-center">Check-in 2</th>
                        <th class="p-3 text-center">Check-out 2</th>
                        <th class="p-3 text-center">Status</th>
                    </tr>
                </thead>
                <tbody id="attendanceTableBody">
                    <tr>
                        <td colspan="7" class="text-center p-4 text-gray-500">No data loaded</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Summary Stats -->
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mt-6">
            <div class="bg-green-50 p-4 rounded-lg border border-green-200">
                <p class="text-green-600 text-sm font-medium">On Time</p>
                <p class="text-2xl font-bold text-green-800" id="summaryOnTime">0</p>
            </div>
            <div class="bg-yellow-50 p-4 rounded-lg border border-yellow-200">
                <p class="text-yellow-600 text-sm font-medium">Late</p>
                <p class="text-2xl font-bold text-yellow-800" id="summaryLate">0</p>
            </div>
            <div class="bg-red-50 p-4 rounded-lg border border-red-200">
                <p class="text-red-600 text-sm font-medium">Absent</p>
                <p class="text-2xl font-bold text-red-800" id="summaryAbsent">0</p>
            </div>
            <div class="bg-orange-50 p-4 rounded-lg border border-orange-200">
                <p class="text-orange-600 text-sm font-medium">Incomplete</p>
                <p class="text-2xl font-bold text-orange-800" id="summaryIncomplete">0</p>
            </div>
            <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                <p class="text-blue-600 text-sm font-medium">Total Employees</p>
                <p class="text-2xl font-bold text-blue-800" id="summaryTotal">0</p>
            </div>
        </div>
    </div>
</div>

<script>
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

                tbody.innerHTML += `
                    <tr class="border-b hover:bg-gray-50">
                        <td class="p-3">${emp.name}</td>
                        <td class="p-3 text-center">${emp.employee_id}</td>
                        <td class="p-3 text-center text-${color}-600 font-semibold">${emp.check_in_1 || '--:--'}</td>
                        <td class="p-3 text-center text-${color}-600 font-semibold">${emp.check_out_1 || '--:--'}</td>
                        <td class="p-3 text-center text-${color}-600 font-semibold">${emp.check_in_2 || '--:--'}</td>
                        <td class="p-3 text-center text-${color}-600 font-semibold">${emp.check_out_2 || '--:--'}</td>
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

// Optional: load today by default
document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('selectedDate').value = new Date().toISOString().split('T')[0];
    loadReport();
});
</script>