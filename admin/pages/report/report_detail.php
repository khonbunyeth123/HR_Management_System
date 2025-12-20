<div class="p-6">
  <h2 class="text-2xl font-bold mb-2">Detailed Report</h2>
  <div class="flex p-4 bg-white shadow my-4 justify-end border rounded-lg gap-2">
    <button class="bg-blue-500 text-white px-4 py-2 rounded">Print</button>
    <button class="px-4 py-2 bg-green-500 text-white rounded">Export</button>
  </div>
  <div class="bg-white rounded-lg shadow p-6 mb-4">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
      <div>
        <label>Employee</label>
        <select class="border rounded px-3 py-2 w-full">
          <option>All Employees</option>
        </select>
      </div>
      <div>
        <label>Start Date</label>
        <input type="date" class="border rounded px-3 py-2 w-full">
      </div>
      <div>
        <label>End Date</label>
        <input type="date" class="border rounded px-3 py-2 w-full">
      </div>
      <div class="flex items-end">
        <button class="bg-indigo-600 text-white px-4 py-2 rounded w-full">Filter</button>
      </div>
    </div>
  </div>

  <div class="bg-white rounded-lg shadow overflow-x-auto">
    <table class="min-w-full divide-y divide-slate-200">
      <thead class="bg-slate-50">
        <tr><th>Date</th><th>Employee</th><th>Check In</th><th>Check Out</th><th>Status</th></tr>
      </thead>
      <tbody>
        <tr class="text-center text-slate-500"><td colspan="5">No data yet</td></tr>
      </tbody>
    </table>
  </div>
</div>
