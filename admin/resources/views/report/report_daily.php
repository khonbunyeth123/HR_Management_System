<div class="w-full h-full">
    <div class="bg-white shadow-lg p-4">
        <h1 class="text-3xl font-bold text-slate-800 mb-6">Daily Attendance Report</h1>
        
        <!-- Date Picker -->
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Select Date:</label>
            <input type="date" id="selectedDate" class="p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" value="2025-12-24">
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
                    <!-- Sample Data Row 1 -->
                    <tr class="border-b hover:bg-gray-50">
                        <td class="p-3">John Doe</td>
                        <td class="p-3 text-center">EMP001</td>
                        <td class="p-3 text-center text-green-600 font-semibold">08:00</td>
                        <td class="p-3 text-center text-green-600 font-semibold">12:00</td>
                        <td class="p-3 text-center text-green-600 font-semibold">13:00</td>
                        <td class="p-3 text-center text-green-600 font-semibold">17:00</td>
                        <td class="p-3 text-center">
                            <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-medium">On Time</span>
                        </td>
                    </tr>
                    <!-- Sample Data Row 2 -->
                    <tr class="border-b hover:bg-gray-50">
                        <td class="p-3">Jane Smith</td>
                        <td class="p-3 text-center">EMP002</td>
                        <td class="p-3 text-center text-yellow-600 font-semibold">08:15</td>
                        <td class="p-3 text-center text-green-600 font-semibold">12:00</td>
                        <td class="p-3 text-center text-green-600 font-semibold">13:00</td>
                        <td class="p-3 text-center text-green-600 font-semibold">17:00</td>
                        <td class="p-3 text-center">
                            <span class="bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-sm font-medium">Late</span>
                        </td>
                    </tr>
                    <!-- Sample Data Row 3 -->
                    <tr class="border-b hover:bg-gray-50">
                        <td class="p-3">Mike Johnson</td>
                        <td class="p-3 text-center">EMP003</td>
                        <td class="p-3 text-center text-red-600 font-semibold">--:--</td>
                        <td class="p-3 text-center text-red-600 font-semibold">--:--</td>
                        <td class="p-3 text-center text-red-600 font-semibold">--:--</td>
                        <td class="p-3 text-center text-red-600 font-semibold">--:--</td>
                        <td class="p-3 text-center">
                            <span class="bg-red-100 text-red-800 px-3 py-1 rounded-full text-sm font-medium">Absent</span>
                        </td>
                    </tr>
                    <!-- Sample Data Row 4 -->
                    <tr class="border-b hover:bg-gray-50">
                        <td class="p-3">Sarah Williams</td>
                        <td class="p-3 text-center">EMP004</td>
                        <td class="p-3 text-center text-green-600 font-semibold">08:00</td>
                        <td class="p-3 text-center text-green-600 font-semibold">12:00</td>
                        <td class="p-3 text-center text-red-600 font-semibold">--:--</td>
                        <td class="p-3 text-center text-green-600 font-semibold">17:00</td>
                        <td class="p-3 text-center">
                            <span class="bg-orange-100 text-orange-800 px-3 py-1 rounded-full text-sm font-medium">Incomplete</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Summary Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-6">
            <div class="bg-green-50 p-4 rounded-lg border border-green-200">
                <p class="text-green-600 text-sm font-medium">On Time</p>
                <p class="text-2xl font-bold text-green-800">1</p>
            </div>
            <div class="bg-yellow-50 p-4 rounded-lg border border-yellow-200">
                <p class="text-yellow-600 text-sm font-medium">Late</p>
                <p class="text-2xl font-bold text-yellow-800">1</p>
            </div>
            <div class="bg-red-50 p-4 rounded-lg border border-red-200">
                <p class="text-red-600 text-sm font-medium">Absent</p>
                <p class="text-2xl font-bold text-red-800">1</p>
            </div>
            <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                <p class="text-blue-600 text-sm font-medium">Total Employees</p>
                <p class="text-2xl font-bold text-blue-800">4</p>
            </div>
        </div>
    </div>
</div>

    <script>
        function loadReport() {
            const selectedDate = document.getElementById('selectedDate').value;
            console.log('Loading report for date:', selectedDate);
            // TODO: Add AJAX call to fetch data from PHP backend
            // Example: fetch(`api/daily-report.php?date=${selectedDate}`)
            alert('Report will be loaded for: ' + selectedDate);
        }
    </script>