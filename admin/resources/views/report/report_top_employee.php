
    <div class="container mx-auto p-6">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h1 class="text-3xl font-bold text-slate-800 mb-6">Top Employees - Attendance Leaderboard</h1>
            
            <!-- Period Selector -->
            <div class="mb-6 flex flex-wrap gap-4 items-end">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Select Period:</label>
                    <select id="periodFilter" class="p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="week">This Week</option>
                        <option value="month" selected>This Month</option>
                        <option value="quarter">This Quarter</option>
                        <option value="year">This Year</option>
                        <option value="custom">Custom Range</option>
                    </select>
                </div>
                <div id="customRange" class="hidden">
                    <label class="block text-sm font-medium text-gray-700 mb-2">From:</label>
                    <input type="date" id="fromDate" class="p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <div id="customRangeTo" class="hidden">
                    <label class="block text-sm font-medium text-gray-700 mb-2">To:</label>
                    <input type="date" id="toDate" class="p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <button onclick="loadReport()" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition">Load Report</button>
            </div>

            <!-- Top 3 Highlight Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <!-- 1st Place -->
                <div class="bg-gradient-to-br from-yellow-400 to-yellow-600 rounded-xl p-6 text-white shadow-xl transform hover:scale-105 transition">
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-5xl font-bold">🥇</span>
                        <span class="text-6xl font-bold opacity-50">1</span>
                    </div>
                    <h3 class="text-2xl font-bold mb-2">Mike Johnson</h3>
                    <p class="text-yellow-100 mb-4">EMP003</p>
                    <div class="bg-white bg-opacity-20 rounded-lg p-3">
                        <p class="text-sm mb-1">Attendance Score</p>
                        <p class="text-3xl font-bold">100%</p>
                    </div>
                    <div class="mt-4 grid grid-cols-2 gap-2 text-sm">
                        <div class="bg-white bg-opacity-20 rounded p-2">
                            <p class="text-yellow-100">Present</p>
                            <p class="font-bold">24 days</p>
                        </div>
                        <div class="bg-white bg-opacity-20 rounded p-2">
                            <p class="text-yellow-100">Late</p>
                            <p class="font-bold">0 days</p>
                        </div>
                    </div>
                </div>

                <!-- 2nd Place -->
                <div class="bg-gradient-to-br from-gray-300 to-gray-500 rounded-xl p-6 text-white shadow-xl transform hover:scale-105 transition">
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-5xl font-bold">🥈</span>
                        <span class="text-6xl font-bold opacity-50">2</span>
                    </div>
                    <h3 class="text-2xl font-bold mb-2">David Brown</h3>
                    <p class="text-gray-200 mb-4">EMP005</p>
                    <div class="bg-white bg-opacity-20 rounded-lg p-3">
                        <p class="text-sm mb-1">Attendance Score</p>
                        <p class="text-3xl font-bold">95.8%</p>
                    </div>
                    <div class="mt-4 grid grid-cols-2 gap-2 text-sm">
                        <div class="bg-white bg-opacity-20 rounded p-2">
                            <p class="text-gray-200">Present</p>
                            <p class="font-bold">23 days</p>
                        </div>
                        <div class="bg-white bg-opacity-20 rounded p-2">
                            <p class="text-gray-200">Late</p>
                            <p class="font-bold">1 day</p>
                        </div>
                    </div>
                </div>

                <!-- 3rd Place -->
                <div class="bg-gradient-to-br from-amber-600 to-amber-800 rounded-xl p-6 text-white shadow-xl transform hover:scale-105 transition">
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-5xl font-bold">🥉</span>
                        <span class="text-6xl font-bold opacity-50">3</span>
                    </div>
                    <h3 class="text-2xl font-bold mb-2">John Doe</h3>
                    <p class="text-amber-200 mb-4">EMP001</p>
                    <div class="bg-white bg-opacity-20 rounded-lg p-3">
                        <p class="text-sm mb-1">Attendance Score</p>
                        <p class="text-3xl font-bold">91.7%</p>
                    </div>
                    <div class="mt-4 grid grid-cols-2 gap-2 text-sm">
                        <div class="bg-white bg-opacity-20 rounded p-2">
                            <p class="text-amber-200">Present</p>
                            <p class="font-bold">22 days</p>
                        </div>
                        <div class="bg-white bg-opacity-20 rounded p-2">
                            <p class="text-amber-200">Late</p>
                            <p class="font-bold">1 day</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Full Leaderboard Table -->
            <h2 class="text-2xl font-bold text-slate-800 mb-4">Complete Leaderboard</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white border border-gray-200 rounded-lg">
                    <thead class="bg-slate-800 text-white">
                        <tr>
                            <th class="p-3 text-center">Rank</th>
                            <th class="p-3 text-left">Employee Name</th>
                            <th class="p-3 text-center">Employee ID</th>
                            <th class="p-3 text-center">Total Present</th>
                            <th class="p-3 text-center">Total Late</th>
                            <th class="p-3 text-center">Total Absent</th>
                            <th class="p-3 text-center">Attendance Score</th>
                            <th class="p-3 text-center">Rating</th>
                        </tr>
                    </thead>
                    <tbody id="leaderboardTableBody">
                        <!-- 1st Place -->
                        <tr class="border-b bg-yellow-50 hover:bg-yellow-100">
                            <td class="p-3 text-center">
                                <span class="text-2xl">🥇</span>
                                <span class="font-bold text-lg ml-2">1</span>
                            </td>
                            <td class="p-3 font-bold">Mike Johnson</td>
                            <td class="p-3 text-center">EMP003</td>
                            <td class="p-3 text-center text-green-600 font-semibold">24</td>
                            <td class="p-3 text-center text-yellow-600 font-semibold">0</td>
                            <td class="p-3 text-center text-red-600 font-semibold">0</td>
                            <td class="p-3 text-center">
                                <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-bold">100%</span>
                            </td>
                            <td class="p-3 text-center text-2xl">⭐⭐⭐⭐⭐</td>
                        </tr>
                        <!-- 2nd Place -->
                        <tr class="border-b bg-gray-50 hover:bg-gray-100">
                            <td class="p-3 text-center">
                                <span class="text-2xl">🥈</span>
                                <span class="font-bold text-lg ml-2">2</span>
                            </td>
                            <td class="p-3 font-bold">David Brown</td>
                            <td class="p-3 text-center">EMP005</td>
                            <td class="p-3 text-center text-green-600 font-semibold">23</td>
                            <td class="p-3 text-center text-yellow-600 font-semibold">1</td>
                            <td class="p-3 text-center text-red-600 font-semibold">1</td>
                            <td class="p-3 text-center">
                                <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-bold">95.8%</span>
                            </td>
                            <td class="p-3 text-center text-2xl">⭐⭐⭐⭐⭐</td>
                        </tr>
                        <!-- 3rd Place -->
                        <tr class="border-b bg-amber-50 hover:bg-amber-100">
                            <td class="p-3 text-center">
                                <span class="text-2xl">🥉</span>
                                <span class="font-bold text-lg ml-2">3</span>
                            </td>
                            <td class="p-3 font-bold">John Doe</td>
                            <td class="p-3 text-center">EMP001</td>
                            <td class="p-3 text-center text-green-600 font-semibold">22</td>
                            <td class="p-3 text-center text-yellow-600 font-semibold">1</td>
                            <td class="p-3 text-center text-red-600 font-semibold">2</td>
                            <td class="p-3 text-center">
                                <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-bold">91.7%</span>
                            </td>
                            <td class="p-3 text-center text-2xl">⭐⭐⭐⭐</td>
                        </tr>
                        <!-- 4th Place -->
                        <tr class="border-b hover:bg-gray-50">
                            <td class="p-3 text-center">
                                <span class="font-bold text-lg">4</span>
                            </td>
                            <td class="p-3">Jane Smith</td>
                            <td class="p-3 text-center">EMP002</td>
                            <td class="p-3 text-center text-green-600 font-semibold">20</td>
                            <td class="p-3 text-center text-yellow-600 font-semibold">3</td>
                            <td class="p-3 text-center text-red-600 font-semibold">4</td>
                            <td class="p-3 text-center">
                                <span class="bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-sm font-bold">83.3%</span>
                            </td>
                            <td class="p-3 text-center text-2xl">⭐⭐⭐⭐</td>
                        </tr>
                        <!-- 5th Place -->
                        <tr class="border-b hover:bg-gray-50">
                            <td class="p-3 text-center">
                                <span class="font-bold text-lg">5</span>
                            </td>
                            <td class="p-3">Sarah Williams</td>
                            <td class="p-3 text-center">EMP004</td>
                            <td class="p-3 text-center text-green-600 font-semibold">18</td>
                            <td class="p-3 text-center text-yellow-600 font-semibold">2</td>
                            <td class="p-3 text-center text-red-600 font-semibold">6</td>
                            <td class="p-3 text-center">
                                <span class="bg-red-100 text-red-800 px-3 py-1 rounded-full text-sm font-bold">75.0%</span>
                            </td>
                            <td class="p-3 text-center text-2xl">⭐⭐⭐</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Recognition Section -->
            <div class="mt-8 bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
                <h3 class="text-lg font-bold text-blue-900 mb-2">🏆 Recognition</h3>
                <p class="text-blue-800">Congratulations to <strong>Mike Johnson</strong> for achieving perfect attendance this month! Keep up the excellent work! 🎉</p>
            </div>
        </div>
    </div>

    <script>
        // Show/hide custom date range based on period selection
        document.getElementById('periodFilter').addEventListener('change', function() {
            const customRange = document.getElementById('customRange');
            const customRangeTo = document.getElementById('customRangeTo');
            if (this.value === 'custom') {
                customRange.classList.remove('hidden');
                customRangeTo.classList.remove('hidden');
            } else {
                customRange.classList.add('hidden');
                customRangeTo.classList.add('hidden');
            }
        });

        function loadReport() {
            const period = document.getElementById('periodFilter').value;
            let params = { period };
            
            if (period === 'custom') {
                params.fromDate = document.getElementById('fromDate').value;
                params.toDate = document.getElementById('toDate').value;
            }
            
            console.log('Loading top employees report with params:', params);
            // TODO: Add AJAX call to fetch data from PHP backend
            // Example: fetch(`api/top-employees.php?period=${period}`)
            alert('Top employees report will be loaded for: ' + period);
        }
    </script>
