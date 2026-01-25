
    <div class="w-full h-full">
        <div class="bg-white rounded-lg shadow-lg p-4">
            <div class="flex justify-between items-center mb-">
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
            
            <!-- Date Range & Quick Presets -->
            <div class="bg-gray-50 rounded-lg p-4 mb-4">
                <h2 class="text-lg font-semibold text-gray-700 mb-4">📅 Select Report Period</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div class="flex gap-4">
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-700 mb-2">From Date:</label>
                            <input type="date" id="fromDate" class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" value="2025-12-01">
                        </div>
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-700 mb-2">To Date:</label>
                            <input type="date" id="toDate" class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" value="2025-12-24">
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

            <!-- Overall Summary Cards -->
            <!-- <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
                <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg p-4 text-white shadow-lg">
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-blue-100 text-sm font-medium">Total Employees</p>
                        <span class="text-3xl">👥</span>
                    </div>
                    <p class="text-3xl font-bold">25</p>
                    <p class="text-blue-100 text-xs mt-1">Active in period</p>
                </div>
                <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg p-4 text-white shadow-lg">
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-green-100 text-sm font-medium">Avg Attendance</p>
                        <span class="text-3xl">📊</span>
                    </div>
                    <p class="text-3xl font-bold">89.2%</p>
                    <p class="text-green-100 text-xs mt-1">↑ 2.5% from last month</p>
                </div>
                <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg p-4 text-white shadow-lg">
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-purple-100 text-sm font-medium">Total Present</p>
                        <span class="text-3xl">✓</span>
                    </div>
                    <p class="text-3xl font-bold">534</p>
                    <p class="text-purple-100 text-xs mt-1">Working days recorded</p>
                </div>
                <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-lg p-4 text-white shadow-lg">
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-red-100 text-sm font-medium">Total Absent</p>
                        <span class="text-3xl">✗</span>
                    </div>
                    <p class="text-3xl font-bold">66</p>
                    <p class="text-red-100 text-xs mt-1">11% absence rate</p>
                </div>
                <div class="bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-lg p-4 text-white shadow-lg">
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-yellow-100 text-sm font-medium">Late Check-ins</p>
                        <span class="text-3xl">⚠</span>
                    </div>
                    <p class="text-3xl font-bold">47</p>
                    <p class="text-yellow-100 text-xs mt-1">8.8% late rate</p>
                </div>
            </div> -->

            <!-- Charts Section -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <!-- Attendance Trend Chart -->
                <div class="bg-white rounded-lg border border-gray-200 p-4 shadow">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4">📈 Daily Attendance Trend</h3>
                    <canvas id="trendChart" height="200"></canvas>
                </div>
                <!-- Status Distribution Chart -->
                <div class="bg-white rounded-lg border border-gray-200 p-4 shadow">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4">📊 Status Distribution</h3>
                    <canvas id="statusChart" height="200"></canvas>
                </div>
            </div>

            <!-- Department-wise Summary -->
            <div class="bg-gray-50 rounded-lg p-4 mb-6">
                <h3 class="text-lg font-semibold text-gray-700 mb-4">🏢 Department-wise Summary</h3>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="bg-white rounded-lg p-4 border border-gray-200">
                        <div class="flex items-center justify-between mb-2">
                            <h4 class="font-semibold text-gray-700">IT Department</h4>
                            <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs font-medium">8 Emp</span>
                        </div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-gray-600">Attendance:</span>
                            <span class="font-semibold text-green-600">92.5%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-green-500 h-2 rounded-full" style="width: 92.5%"></div>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg p-4 border border-gray-200">
                        <div class="flex items-center justify-between mb-2">
                            <h4 class="font-semibold text-gray-700">HR Department</h4>
                            <span class="bg-purple-100 text-purple-800 px-2 py-1 rounded text-xs font-medium">5 Emp</span>
                        </div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-gray-600">Attendance:</span>
                            <span class="font-semibold text-green-600">88.0%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-green-500 h-2 rounded-full" style="width: 88%"></div>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg p-4 border border-gray-200">
                        <div class="flex items-center justify-between mb-2">
                            <h4 class="font-semibold text-gray-700">Sales Department</h4>
                            <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs font-medium">7 Emp</span>
                        </div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-gray-600">Attendance:</span>
                            <span class="font-semibold text-yellow-600">85.3%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-yellow-500 h-2 rounded-full" style="width: 85.3%"></div>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg p-4 border border-gray-200">
                        <div class="flex items-center justify-between mb-2">
                            <h4 class="font-semibold text-gray-700">Finance Dept</h4>
                            <span class="bg-orange-100 text-orange-800 px-2 py-1 rounded text-xs font-medium">5 Emp</span>
                        </div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-gray-600">Attendance:</span>
                            <span class="font-semibold text-green-600">90.0%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-green-500 h-2 rounded-full" style="width: 90%"></div>
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
                        <!-- Row 1 -->
                        <tr class="border-b hover:bg-gray-50">
                            <td class="p-3 font-medium">Mike Johnson</td>
                            <td class="p-3 text-center text-gray-600">EMP003</td>
                            <td class="p-3 text-center">
                                <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs">IT</span>
                            </td>
                            <td class="p-3 text-center font-semibold">24</td>
                            <td class="p-3 text-center text-green-600 font-semibold">24</td>
                            <td class="p-3 text-center text-red-600">0</td>
                            <td class="p-3 text-center text-yellow-600">0</td>
                            <td class="p-3 text-center text-gray-600">0</td>
                            <td class="p-3 text-center">
                                <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-bold">100%</span>
                            </td>
                            <td class="p-3 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <span class="text-yellow-400">⭐⭐⭐⭐⭐</span>
                                </div>
                            </td>
                        </tr>
                        <!-- Row 2 -->
                        <tr class="border-b hover:bg-gray-50">
                            <td class="p-3 font-medium">David Brown</td>
                            <td class="p-3 text-center text-gray-600">EMP005</td>
                            <td class="p-3 text-center">
                                <span class="bg-purple-100 text-purple-800 px-2 py-1 rounded text-xs">HR</span>
                            </td>
                            <td class="p-3 text-center font-semibold">24</td>
                            <td class="p-3 text-center text-green-600 font-semibold">23</td>
                            <td class="p-3 text-center text-red-600">1</td>
                            <td class="p-3 text-center text-yellow-600">1</td>
                            <td class="p-3 text-center text-gray-600">0</td>
                            <td class="p-3 text-center">
                                <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-bold">95.8%</span>
                            </td>
                            <td class="p-3 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <span class="text-yellow-400">⭐⭐⭐⭐⭐</span>
                                </div>
                            </td>
                        </tr>
                        <!-- Row 3 -->
                        <tr class="border-b hover:bg-gray-50">
                            <td class="p-3 font-medium">John Doe</td>
                            <td class="p-3 text-center text-gray-600">EMP001</td>
                            <td class="p-3 text-center">
                                <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs">IT</span>
                            </td>
                            <td class="p-3 text-center font-semibold">24</td>
                            <td class="p-3 text-center text-green-600 font-semibold">22</td>
                            <td class="p-3 text-center text-red-600">2</td>
                            <td class="p-3 text-center text-yellow-600">1</td>
                            <td class="p-3 text-center text-gray-600">1</td>
                            <td class="p-3 text-center">
                                <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-bold">91.7%</span>
                            </td>
                            <td class="p-3 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <span class="text-yellow-400">⭐⭐⭐⭐</span>
                                </div>
                            </td>
                        </tr>
                        <!-- Row 4 -->
                        <tr class="border-b hover:bg-gray-50">
                            <td class="p-3 font-medium">Emily Chen</td>
                            <td class="p-3 text-center text-gray-600">EMP006</td>
                            <td class="p-3 text-center">
                                <span class="bg-orange-100 text-orange-800 px-2 py-1 rounded text-xs">Finance</span>
                            </td>
                            <td class="p-3 text-center font-semibold">24</td>
                            <td class="p-3 text-center text-green-600 font-semibold">21</td>
                            <td class="p-3 text-center text-red-600">3</td>
                            <td class="p-3 text-center text-yellow-600">2</td>
                            <td class="p-3 text-center text-gray-600">1</td>
                            <td class="p-3 text-center">
                                <span class="bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-sm font-bold">87.5%</span>
                            </td>
                            <td class="p-3 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <span class="text-yellow-400">⭐⭐⭐⭐</span>
                                </div>
                            </td>
                        </tr>
                        <!-- Row 5 -->
                        <tr class="border-b hover:bg-gray-50">
                            <td class="p-3 font-medium">Jane Smith</td>
                            <td class="p-3 text-center text-gray-600">EMP002</td>
                            <td class="p-3 text-center">
                                <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs">Sales</span>
                            </td>
                            <td class="p-3 text-center font-semibold">24</td>
                            <td class="p-3 text-center text-green-600 font-semibold">20</td>
                            <td class="p-3 text-center text-red-600">4</td>
                            <td class="p-3 text-center text-yellow-600">3</td>
                            <td class="p-3 text-center text-gray-600">2</td>
                            <td class="p-3 text-center">
                                <span class="bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-sm font-bold">83.3%</span>
                            </td>
                            <td class="p-3 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <span class="text-yellow-400">⭐⭐⭐⭐</span>
                                </div>
                            </td>
                        </tr>
                        <!-- Row 6 -->
                        <tr class="border-b hover:bg-gray-50">
                            <td class="p-3 font-medium">Robert Wilson</td>
                            <td class="p-3 text-center text-gray-600">EMP007</td>
                            <td class="p-3 text-center">
                                <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs">Sales</span>
                            </td>
                            <td class="p-3 text-center font-semibold">24</td>
                            <td class="p-3 text-center text-green-600 font-semibold">19</td>
                            <td class="p-3 text-center text-red-600">5</td>
                            <td class="p-3 text-center text-yellow-600">4</td>
                            <td class="p-3 text-center text-gray-600">2</td>
                            <td class="p-3 text-center">
                                <span class="bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-sm font-bold">79.2%</span>
                            </td>
                            <td class="p-3 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <span class="text-yellow-400">⭐⭐⭐</span>
                                </div>
                            </td>
                        </tr>
                        <!-- Row 7 -->
                        <tr class="border-b hover:bg-gray-50">
                            <td class="p-3 font-medium">Sarah Williams</td>
                            <td class="p-3 text-center text-gray-600">EMP004</td>
                            <td class="p-3 text-center">
                                <span class="bg-purple-100 text-purple-800 px-2 py-1 rounded text-xs">HR</span>
                            </td>
                            <td class="p-3 text-center font-semibold">24</td>
                            <td class="p-3 text-center text-green-600 font-semibold">18</td>
                            <td class="p-3 text-center text-red-600">6</td>
                            <td class="p-3 text-center text-yellow-600">2</td>
                            <td class="p-3 text-center text-gray-600">3</td>
                            <td class="p-3 text-center">
                                <span class="bg-red-100 text-red-800 px-3 py-1 rounded-full text-sm font-bold">75.0%</span>
                            </td>
                            <td class="p-3 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <span class="text-yellow-400">⭐⭐⭐</span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <!-- Key Insights -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded">
                    <h4 class="font-semibold text-green-900 mb-2">✅ Top Performers</h4>
                    <p class="text-green-800 text-sm">3 employees achieved over 95% attendance rate this month. Excellent work!</p>
                </div>
                <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded">
                    <h4 class="font-semibold text-yellow-900 mb-2">⚠ Needs Attention</h4>
                    <p class="text-yellow-800 text-sm">2 employees have attendance below 80%. Consider follow-up meetings.</p>
                </div>
                <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
                    <h4 class="font-semibold text-blue-900 mb-2">📊 Overall Trend</h4>
                    <p class="text-blue-800 text-sm">Department attendance improved by 2.5% compared to last month.</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Attendance Trend Chart
        const trendCtx = document.getElementById('trendChart').getContext('2d');
        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: ['Dec 1-5', 'Dec 6-10', 'Dec 11-15', 'Dec 16-20', 'Dec 21-24'],
                datasets: [{
                    label: 'Present',
                    data: [88, 92, 87, 90, 89],
                    borderColor: 'rgb(34, 197, 94)',
                    backgroundColor: 'rgba(34, 197, 94, 0.1)',
                    tension: 0.4,
                    fill: true
                }, {
                    label: 'Absent',
                    data: [12, 8, 13, 10, 11],
                    borderColor: 'rgb(239, 68, 68)',
                    backgroundColor: 'rgba(239, 68, 68, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100
                    }
                }
            }
        });

        // Status Distribution Chart
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['On Time', 'Late', 'Absent', 'Leave'],
                datasets: [{
                    data: [534, 47, 66, 28],
                    backgroundColor: [
                        'rgb(34, 197, 94)',
                        'rgb(234, 179, 8)',
                        'rgb(239, 68, 68)',
                        'rgb(148, 163, 184)'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                    }
                }
            }
        });

        function generateReport() {
            const fromDate = document.getElementById('fromDate').value;
            const toDate = document.getElementById('toDate').value;
            const department = document.getElementById('departmentFilter').value;
            
            console.log('Generating report:', { fromDate, toDate, department });
            // TODO: Add AJAX call to fetch data from PHP backend
            alert('Report generated! Integration with PHP backend needed.');
        }

        function exportToExcel() {
            alert('Export to Excel - Integration needed');
        }

        function exportToPDF() {
            alert('Export to PDF - Integration needed');
        }
    </script>
