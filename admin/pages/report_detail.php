<div class="p-6">
  <div class="mb-6">
    <h2 class="text-2xl font-bold text-slate-800">Detailed Report</h2>
    <p class="text-slate-600">Complete attendance details with working hours</p>
  </div>

  <div class="bg-white rounded-lg shadow p-6 mb-6">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-2">Employee</label>
        <select id="employee_filter" class="border rounded px-3 py-2 w-full">
          <option value="">All Employees</option>
          <?php
          $emp_query = "SELECT eid, name FROM employees ORDER BY name";
          $emp_result = $conn->query($emp_query);
          while($emp = $emp_result->fetch_assoc()) {
            echo '<option value="' . $emp['eid'] . '">' . htmlspecialchars($emp['name']) . '</option>';
          }
          ?>
        </select>
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-2">Start Date</label>
        <input type="date" id="detail_start" value="<?= date('Y-m-01') ?>" class="border rounded px-3 py-2 w-full">
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-2">End Date</label>
        <input type="date" id="detail_end" value="<?= date('Y-m-d') ?>" class="border rounded px-3 py-2 w-full">
      </div>
      <div class="flex items-end">
        <button class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 w-full">Filter</button>
      </div>
    </div>
  </div>

  <div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-slate-200">
        <thead class="bg-slate-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Date</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Employee</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Check In</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Check Out</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Working Hours</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Status</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Location</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-slate-200">
          <?php
          $query = "SELECT 
                      e.name,
                      DATE(a.check_in) as date,
                      a.check_in,
                      a.check_out,
                      a.status,
                      a.location,
                      TIMESTAMPDIFF(HOUR, a.check_in, a.check_out) as hours_worked
                    FROM attendance a
                    JOIN employees e ON a.eid = e.eid
                    WHERE DATE(a.check_in) BETWEEN ? AND ?
                    ORDER BY a.check_in DESC
                    LIMIT 100";
          
          $start = date('Y-m-01');
          $end = date('Y-m-d');
          $stmt = $conn->prepare($query);
          $stmt->bind_param("ss", $start, $end);
          $stmt->execute();
          $result = $stmt->get_result();

          while($row = $result->fetch_assoc()) {
            $status_class = $row['status'] == 'present' ? 'bg-green-100 text-green-800' : 
                           ($row['status'] == 'late' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800');
            
            echo '<tr>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900">' . date('M d, Y', strtotime($row['date'])) . '</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-900">' . htmlspecialchars($row['name']) . '</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900">' . date('h:i A', strtotime($row['check_in'])) . '</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900">' . ($row['check_out'] ? date('h:i A', strtotime($row['check_out'])) : '-') . '</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900">' . ($row['hours_worked'] ? $row['hours_worked'] . ' hrs' : '-') . '</td>
              <td class="px-6 py-4 whitespace-nowrap"><span class="px-2 py-1 text-xs rounded ' . $status_class . '">' . ucfirst($row['status']) . '</span></td>
              <td class="px-6 py-4 text-sm text-slate-500">' . ($row['location'] ? htmlspecialchars($row['location']) : '-') . '</td>
            </tr>';
          }
          ?>
        </tbody>
      </table>
    </div>
  </div>
</div>