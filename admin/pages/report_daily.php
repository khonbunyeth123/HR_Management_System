<?php
// Fetch daily attendance data
$today = date('Y-m-d');
?>

<div class="p-6">
  <div class="mb-6">
    <h2 class="text-2xl font-bold text-slate-800">Daily Report</h2>
    <p class="text-slate-600">Attendance report for today</p>
  </div>

  <div class="bg-white rounded-lg shadow p-6">
    <div class="mb-4">
      <label class="block text-sm font-medium text-slate-700 mb-2">Select Date</label>
      <input type="date" id="report_date" value="<?= $today ?>" class="border rounded px-3 py-2">
      <button class="ml-2 bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">Generate</button>
    </div>

    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-slate-200">
        <thead class="bg-slate-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Employee</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Check In</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Check Out</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Status</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-slate-200">
          <?php
          $query = "SELECT e.name, a.check_in, a.check_out, a.status 
                    FROM attendance a 
                    JOIN employees e ON a.eid = e.eid 
                    WHERE DATE(a.check_in) = ? 
                    ORDER BY a.check_in DESC";
          $stmt = $conn->prepare($query);
          $stmt->bind_param("s", $today);
          $stmt->execute();
          $result = $stmt->get_result();

          if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
              $status_class = $row['status'] == 'present' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
              echo '<tr>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900">' . htmlspecialchars($row['name']) . '</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900">' . date('h:i A', strtotime($row['check_in'])) . '</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900">' . ($row['check_out'] ? date('h:i A', strtotime($row['check_out'])) : '-') . '</td>
                <td class="px-6 py-4 whitespace-nowrap"><span class="px-2 py-1 text-xs rounded ' . $status_class . '">' . ucfirst($row['status']) . '</span></td>
              </tr>';
            }
          } else {
            echo '<tr><td colspan="4" class="px-6 py-4 text-center text-slate-500">No attendance records for today</td></tr>';
          }
          ?>
        </tbody>
      </table>
    </div>
  </div>
</div>