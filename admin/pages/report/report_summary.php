<div class="p-6">
  <h2 class="text-2xl font-bold mb-2">Summary Report</h2>
  <p class="text-slate-600 mb-4">Monthly attendance summary</p>
  

  <div class="bg-white rounded-lg shadow p-6 mb-6">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <div>
        <label>Start Date</label>
        <input type="date" class="border rounded px-3 py-2 w-full">
      </div>
      <div>
        <label>End Date</label>
        <input type="date" class="border rounded px-3 py-2 w-full">
      </div>
      <div class="flex items-end">
        <button class="bg-indigo-600 text-white px-4 py-2 rounded w-full">Generate Report</button>
      </div>
    </div>
  </div>

  <div class="bg-white rounded-lg shadow overflow-x-auto">
    <table class="min-w-full divide-y divide-slate-200">
      <thead class="bg-slate-50">
        <tr><th>Employee</th><th>Present</th><th>Absent</th><th>Late</th></tr>
      </thead>
      <tbody>
        <tr class="text-center text-slate-500"><td colspan="4">No data yet</td></tr>
      </tbody>
    </table>
  </div>
</div>
