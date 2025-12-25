  <div class="container mx-auto p-6">
      <div class="bg-white rounded-lg shadow-lg p-6">
          <div class="flex justify-between items-center mb-6">
              <h1 class="text-3xl font-bold text-slate-800">Detailed Attendance Report</h1>
              <div class="flex gap-2">
                  <button onclick="printReport()" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition flex items-center gap-2">
                      <span>🖨️</span> Print
                  </button>
                  <button onclick="exportToExcel()" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition flex items-center gap-2">
                      <span>📊</span> Export Excel
                  </button>
                  <button onclick="exportToPDF()" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition flex items-center gap-2">
                      <span>📄</span> Export PDF
                  </button>
              </div>
          </div>
          
          <!-- Advanced Filters -->
          <div class="bg-gray-50 rounded-lg p-4 mb-6">
              <h2 class="text-lg font-semibold text-gray-700 mb-4">🔍 Filters & Search</h2>
              <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
                  <div>
                      <label class="block text-sm font-medium text-gray-700 mb-2">From Date:</label>
                      <input type="date" id="fromDate" class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" value="2025-12-01">
                  </div>
                  <div>
                      <label class="block text-sm font-medium text-gray-700 mb-2">To Date:</label>
                      <input type="date" id="toDate" class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" value="2025-12-24">
                  </div>
                  <div>
                      <label class="block text-sm font-medium text-gray-700 mb-2">Employee:</label>
                      <select id="employeeFilter" class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                          <option value="">All Employees</option>
                          <option value="EMP001">John Doe (EMP001)</option>
                          <option value="EMP002">Jane Smith (EMP002)</option>
                          <option value="EMP003">Mike Johnson (EMP003)</option>
                          <option value="EMP004">Sarah Williams (EMP004)</option>
                          <option value="EMP005">David Brown (EMP005)</option>
                      </select>
                  </div>
                  <div>
                      <label class="block text-sm font-medium text-gray-700 mb-2">Department:</label>
                      <select id="departmentFilter" class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                          <option value="">All Departments</option>
                          <option value="IT">IT Department</option>
                          <option value="HR">HR Department</option>
                          <option value="Sales">Sales Department</option>
                          <option value="Finance">Finance Department</option>
                      </select>
                  </div>
              </div>
              
              <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                  <div>
                      <label class="block text-sm font-medium text-gray-700 mb-2">Check Type:</label>
                      <select id="checkTypeFilter" class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                          <option value="">All Check Types</option>
                          <option value="1">Check-in 1 (Morning)</option>
                          <option value="2">Check-out 1 (Lunch)</option>
                          <option value="3">Check-in 2 (Afternoon)</option>
                          <option value="4">Check-out 2 (Evening)</option>
                      </select>
                  </div>
                  <div>
                      <label class="block text-sm font-medium text-gray-700 mb-2">Status:</label>
                      <select id="statusFilter" class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                          <option value="">All Status</option>
                          <option value="on-time">On Time</option>
                          <option value="late">Late</option>
                          <option value="early">Early</option>
                          <option value="missing">Missing</option>
                      </select>
                  </div>
                  <div>
                      <label class="block text-sm font-medium text-gray-700 mb-2">Search Employee:</label>
                      <input type="text" id="searchInput" placeholder="Search by name or ID..." class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                  </div>
                  <div class="flex items-end gap-2">
                      <button onclick="applyFilters()" class="flex-1 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition font-medium">
                          Apply Filters
                      </button>
                      <button onclick="resetFilters()" class="flex-1 bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition font-medium">
                          Reset
                      </button>
                  </div>
              </div>
          </div>

          <!-- Quick Stats -->
          <div class="grid grid-cols-2 md:grid-cols-6 gap-4 mb-6">
              <div class="bg-blue-50 p-3 rounded-lg border border-blue-200">
                  <p class="text-blue-600 text-xs font-medium">Total Records</p>
                  <p class="text-2xl font-bold text-blue-800">156</p>
              </div>
              <div class="bg-green-50 p-3 rounded-lg border border-green-200">
                  <p class="text-green-600 text-xs font-medium">On Time</p>
                  <p class="text-2xl font-bold text-green-800">128</p>
              </div>
              <div class="bg-yellow-50 p-3 rounded-lg border border-yellow-200">
                  <p class="text-yellow-600 text-xs font-medium">Late</p>
                  <p class="text-2xl font-bold text-yellow-800">18</p>
              </div>
              <div class="bg-red-50 p-3 rounded-lg border border-red-200">
                  <p class="text-red-600 text-xs font-medium">Missing</p>
                  <p class="text-2xl font-bold text-red-800">10</p>
              </div>
              <div class="bg-purple-50 p-3 rounded-lg border border-purple-200">
                  <p class="text-purple-600 text-xs font-medium">Early</p>
                  <p class="text-2xl font-bold text-purple-800">8</p>
              </div>
              <div class="bg-indigo-50 p-3 rounded-lg border border-indigo-200">
                  <p class="text-indigo-600 text-xs font-medium">Avg Punctuality</p>
                  <p class="text-2xl font-bold text-indigo-800">82%</p>
              </div>
          </div>

          <!-- Results Info -->
          <div class="flex justify-between items-center mb-4">
              <div>
                  <p class="text-gray-600">
                      Showing <span class="font-bold text-gray-800">1-20</span> of <span class="font-bold text-gray-800">156</span> records
                      <span class="ml-2 text-sm text-gray-500">(Filtered from 240 total records)</span>
                  </p>
              </div>
              <div>
                  <label class="text-sm text-gray-600 mr-2">Show per page:</label>
                  <select id="perPageSelect" class="p-2 border border-gray-300 rounded-lg text-sm">
                      <option value="20">20</option>
                      <option value="50">50</option>
                      <option value="100">100</option>
                      <option value="all">All</option>
                  </select>
              </div>
          </div>

          <!-- Detailed Records Table -->
          <div class="overflow-x-auto shadow-md rounded-lg">
              <table class="min-w-full bg-white border border-gray-200">
                  <thead class="bg-slate-800 text-white sticky top-0">
                      <tr>
                          <th class="p-3 text-left cursor-pointer hover:bg-slate-700" onclick="sortTable('employee')">
                              <div class="flex items-center gap-2">
                                  Employee Name
                                  <span class="text-xs">↕️</span>
                              </div>
                          </th>
                          <th class="p-3 text-center cursor-pointer hover:bg-slate-700" onclick="sortTable('id')">
                              <div class="flex items-center justify-center gap-2">
                                  ID
                                  <span class="text-xs">↕️</span>
                              </div>
                          </th>
                          <th class="p-3 text-center cursor-pointer hover:bg-slate-700" onclick="sortTable('date')">
                              <div class="flex items-center justify-center gap-2">
                                  Date
                                  <span class="text-xs">↕️</span>
                              </div>
                          </th>
                          <th class="p-3 text-center">Day</th>
                          <th class="p-3 text-center">Check Type</th>
                          <th class="p-3 text-center">Standard Time</th>
                          <th class="p-3 text-center">Actual Time</th>
                          <th class="p-3 text-center">Difference</th>
                          <th class="p-3 text-center">Status</th>
                          <th class="p-3 text-center">Actions</th>
                      </tr>
                  </thead>
                  <tbody id="detailedTableBody">
                      <!-- Row 1 -->
                      <tr class="border-b hover:bg-gray-50 transition">
                          <td class="p-3 font-medium">John Doe</td>
                          <td class="p-3 text-center text-gray-600">EMP001</td>
                          <td class="p-3 text-center">2025-12-24</td>
                          <td class="p-3 text-center text-gray-600 text-sm">Tuesday</td>
                          <td class="p-3 text-center">
                              <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs font-medium">Check-in 1</span>
                          </td>
                          <td class="p-3 text-center text-gray-600">08:00 AM</td>
                          <td class="p-3 text-center font-semibold text-green-600">08:00 AM</td>
                          <td class="p-3 text-center text-green-600 text-sm">0 min</td>
                          <td class="p-3 text-center">
                              <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-xs font-medium">✓ On Time</span>
                          </td>
                          <td class="p-3 text-center">
                              <button class="text-blue-600 hover:text-blue-800 text-sm underline">View Details</button>
                          </td>
                      </tr>
                      <!-- Row 2 -->
                      <tr class="border-b hover:bg-gray-50 transition">
                          <td class="p-3 font-medium">John Doe</td>
                          <td class="p-3 text-center text-gray-600">EMP001</td>
                          <td class="p-3 text-center">2025-12-24</td>
                          <td class="p-3 text-center text-gray-600 text-sm">Tuesday</td>
                          <td class="p-3 text-center">
                              <span class="bg-purple-100 text-purple-800 px-2 py-1 rounded text-xs font-medium">Check-out 1</span>
                          </td>
                          <td class="p-3 text-center text-gray-600">12:00 PM</td>
                          <td class="p-3 text-center font-semibold text-green-600">12:00 PM</td>
                          <td class="p-3 text-center text-green-600 text-sm">0 min</td>
                          <td class="p-3 text-center">
                              <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-xs font-medium">✓ On Time</span>
                          </td>
                          <td class="p-3 text-center">
                              <button class="text-blue-600 hover:text-blue-800 text-sm underline">View Details</button>
                          </td>
                      </tr>
                      <!-- Row 3 -->
                      <tr class="border-b hover:bg-gray-50 transition">
                          <td class="p-3 font-medium">Jane Smith</td>
                          <td class="p-3 text-center text-gray-600">EMP002</td>
                          <td class="p-3 text-center">2025-12-24</td>
                          <td class="p-3 text-center text-gray-600 text-sm">Tuesday</td>
                          <td class="p-3 text-center">
                              <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs font-medium">Check-in 1</span>
                          </td>
                          <td class="p-3 text-center text-gray-600">08:00 AM</td>
                          <td class="p-3 text-center font-semibold text-yellow-600">08:15 AM</td>
                          <td class="p-3 text-center text-yellow-600 text-sm font-bold">+15 min</td>
                          <td class="p-3 text-center">
                              <span class="bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-xs font-medium">⚠ Late</span>
                          </td>
                          <td class="p-3 text-center">
                              <button class="text-blue-600 hover:text-blue-800 text-sm underline">View Details</button>
                          </td>
                      </tr>
                      <!-- Row 4 -->
                      <tr class="border-b hover:bg-gray-50 transition">
                          <td class="p-3 font-medium">Jane Smith</td>
                          <td class="p-3 text-center text-gray-600">EMP002</td>
                          <td class="p-3 text-center">2025-12-24</td>
                          <td class="p-3 text-center text-gray-600 text-sm">Tuesday</td>
                          <td class="p-3 text-center">
                              <span class="bg-orange-100 text-orange-800 px-2 py-1 rounded text-xs font-medium">Check-in 2</span>
                          </td>
                          <td class="p-3 text-center text-gray-600">01:00 PM</td>
                          <td class="p-3 text-center font-semibold text-yellow-600">01:12 PM</td>
                          <td class="p-3 text-center text-yellow-600 text-sm font-bold">+12 min</td>
                          <td class="p-3 text-center">
                              <span class="bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-xs font-medium">⚠ Late</span>
                          </td>
                          <td class="p-3 text-center">
                              <button class="text-blue-600 hover:text-blue-800 text-sm underline">View Details</button>
                          </td>
                      </tr>
                      <!-- Row 5 -->
                      <tr class="border-b hover:bg-gray-50 transition">
                          <td class="p-3 font-medium">Mike Johnson</td>
                          <td class="p-3 text-center text-gray-600">EMP003</td>
                          <td class="p-3 text-center">2025-12-24</td>
                          <td class="p-3 text-center text-gray-600 text-sm">Tuesday</td>
                          <td class="p-3 text-center">
                              <span class="bg-orange-100 text-orange-800 px-2 py-1 rounded text-xs font-medium">Check-in 2</span>
                          </td>
                          <td class="p-3 text-center text-gray-600">01:00 PM</td>
                          <td class="p-3 text-center font-semibold text-yellow-600">01:05 PM</td>
                          <td class="p-3 text-center text-yellow-600 text-sm font-bold">+5 min</td>
                          <td class="p-3 text-center">
                              <span class="bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-xs font-medium">⚠ Late</span>
                          </td>
                          <td class="p-3 text-center">
                              <button class="text-blue-600 hover:text-blue-800 text-sm underline">View Details</button>
                          </td>
                      </tr>
                      <!-- Row 6 -->
                      <tr class="border-b hover:bg-gray-50 transition">
                          <td class="p-3 font-medium">Mike Johnson</td>
                          <td class="p-3 text-center text-gray-600">EMP003</td>
                          <td class="p-3 text-center">2025-12-24</td>
                          <td class="p-3 text-center text-gray-600 text-sm">Tuesday</td>
                          <td class="p-3 text-center">
                              <span class="bg-red-100 text-red-800 px-2 py-1 rounded text-xs font-medium">Check-out 2</span>
                          </td>
                          <td class="p-3 text-center text-gray-600">05:00 PM</td>
                          <td class="p-3 text-center font-semibold text-green-600">05:00 PM</td>
                          <td class="p-3 text-center text-green-600 text-sm">0 min</td>
                          <td class="p-3 text-center">
                              <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-xs font-medium">✓ On Time</span>
                          </td>
                          <td class="p-3 text-center">
                              <button class="text-blue-600 hover:text-blue-800 text-sm underline">View Details</button>
                          </td>
                      </tr>
                      <!-- Row 7 - Missing Record -->
                      <tr class="border-b hover:bg-gray-50 transition bg-red-50">
                          <td class="p-3 font-medium">Sarah Williams</td>
                          <td class="p-3 text-center text-gray-600">EMP004</td>
                          <td class="p-3 text-center">2025-12-23</td>
                          <td class="p-3 text-center text-gray-600 text-sm">Monday</td>
                          <td class="p-3 text-center">
                              <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs font-medium">Check-in 1</span>
                          </td>
                          <td class="p-3 text-center text-gray-600">08:00 AM</td>
                          <td class="p-3 text-center font-semibold text-red-600">--:--</td>
                          <td class="p-3 text-center text-red-600 text-sm font-bold">N/A</td>
                          <td class="p-3 text-center">
                              <span class="bg-red-100 text-red-800 px-3 py-1 rounded-full text-xs font-medium">✗ Missing</span>
                          </td>
                          <td class="p-3 text-center">
                              <button class="text-red-600 hover:text-red-800 text-sm underline">Report Issue</button>
                          </td>
                      </tr>
                      <!-- Row 8 - Early Check-in -->
                      <tr class="border-b hover:bg-gray-50 transition">
                          <td class="p-3 font-medium">David Brown</td>
                          <td class="p-3 text-center text-gray-600">EMP005</td>
                          <td class="p-3 text-center">2025-12-23</td>
                          <td class="p-3 text-center text-gray-600 text-sm">Monday</td>
                          <td class="p-3 text-center">
                              <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs font-medium">Check-in 1</span>
                          </td>
                          <td class="p-3 text-center text-gray-600">08:00 AM</td>
                          <td class="p-3 text-center font-semibold text-blue-600">07:45 AM</td>
                          <td class="p-3 text-center text-blue-600 text-sm font-bold">-15 min</td>
                          <td class="p-3 text-center">
                              <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-xs font-medium">⚡ Early</span>
                          </td>
                          <td class="p-3 text-center">
                              <button class="text-blue-600 hover:text-blue-800 text-sm underline">View Details</button>
                          </td>
                      </tr>
                  </tbody>
              </table>
          </div>

          <!-- Pagination -->
          <div class="flex flex-col md:flex-row justify-between items-center mt-6 gap-4">
              <div class="text-gray-600 text-sm">
                  Page <span class="font-bold">1</span> of <span class="font-bold">8</span>
              </div>
              <div class="flex gap-2">
                  <button class="px-3 py-2 bg-gray-300 text-gray-600 rounded-lg cursor-not-allowed text-sm" disabled>
                      « First
                  </button>
                  <button class="px-3 py-2 bg-gray-300 text-gray-600 rounded-lg cursor-not-allowed text-sm" disabled>
                      ‹ Previous
                  </button>
                  <button class="px-3 py-2 bg-blue-600 text-white rounded-lg font-bold text-sm">1</button>
                  <button class="px-3 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 text-sm">2</button>
                  <button class="px-3 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 text-sm">3</button>
                  <button class="px-3 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 text-sm">...</button>
                  <button class="px-3 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 text-sm">8</button>
                  <button class="px-3 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm">
                      Next ›
                  </button>
                  <button class="px-3 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm">
                      Last »
                  </button>
              </div>
          </div>
      </div>
  </div>

  <script>
      function applyFilters() {
          const filters = {
              fromDate: document.getElementById('fromDate').value,
              toDate: document.getElementById('toDate').value,
              employee: document.getElementById('employeeFilter').value,
              department: document.getElementById('departmentFilter').value,
              checkType: document.getElementById('checkTypeFilter').value,
              status: document.getElementById('statusFilter').value,
              search: document.getElementById('searchInput').value
          };
          
          console.log('Applying filters:', filters);
          // TODO: Add AJAX call to fetch filtered data
          alert('Filters applied! Integration with PHP backend needed.');
      }

      function resetFilters() {
          document.getElementById('fromDate').value = '2025-12-01';
          document.getElementById('toDate').value = '2025-12-24';
          document.getElementById('employeeFilter').value = '';
          document.getElementById('departmentFilter').value = '';
          document.getElementById('checkTypeFilter').value = '';
          document.getElementById('statusFilter').value = '';
          document.getElementById('searchInput').value = '';
          applyFilters();
      }

      function sortTable(column) {
          console.log('Sorting by:', column);
          alert('Sort functionality - integrate with backend');
      }

      function printReport() {
          window.print();
      }

      function exportToExcel() {
          alert('Export to Excel - Integration needed');
      }

      function exportToPDF() {
          alert('Export to PDF - Integration needed');
      }
  </script>
