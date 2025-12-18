<?php
// Get date range
$start_date = isset($_GET['start']) ? $_GET['start'] : date('Y-m-01');
$end_date = isset($_GET['end']) ? $_GET['end'] : date('Y-m-d');
?>

<div class="p-6">
  <div class="mb-6">
    <h2 class="text-2xl font-bold text-slate-800">Summary Report</h2>
    <p class="text-slate-600">Monthly attendance summary</p>
  </div>

  <div class="bg-white rounded-lg shadow p-6 mb-6">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-2">Start Date</label>
        <input type="date" id="start_date" value="<?= $start_date ?>" class="border rounded px-3 py-2 w-full">
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-2">End Date</label>
        <input type="date" id="end_date" value="<?= $end_date ?>" class="border rounded px-3 py-2 w-full">
      </div>
      <div class="flex items-end">
        <button class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 w-full">Generate Report</button>
      </div>
    </div>
  </div>

  <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <?php
    $query = "SELECT 
                COUNT(DISTINCT eid) as total_employees,
                COUNT(CASE WHEN status = 'present' THEN 1 END) as total_present,
                COUNT(CASE WHEN status = 'absent' THEN 1 END) as total_absent,
                COUNT(CASE WHEN status = 'late' THEN 1 END) as total_late
              FROM attendance 
              WHERE DATE(check_in) BETWEEN ? AND ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $start_date, $end_date);
    $stmt->execute();
    $stats = $stmt->get_result()->fetch_assoc();
    ?>
    
    <div class="bg-blue-50 rounded-lg p-4">
      <div class="text-blue-600 text-sm font-medium">Total Employees</div>
      <div class="text-2xl font-bold text-blue-900"><?= $stats['total_employees'] ?></div>
    </div>
    <div class="bg-green-50 rounded-lg p-4">
      <div class="text-green-600 text-sm font-medium">Present</div>
      <div class="text-2xl font-bold text-green-900"><?= $stats['total_present'] ?></div>
    </div>
    <div class="bg-red-50 rounded-lg p-4">
      <div class="text-red-600 text-sm font-medium">Absent</div>
      <div class="text-2xl font-bold text-red-900"><?= $stats['total_absent'] ?></div>
    </div>
    <div class="bg-yellow-50 rounded-lg p-4">
      <div class="text-yellow-600 text-sm font-medium">Late</div>
      <div class="text-2xl font-bold text-yellow-900"><?= $stats['total_late'] ?></div>
    </div>
  </div>

  <div class="bg-white rounded-lg shadow p-6">
    <h3 class="text-lg font-semibold mb-4">Employee Summary</h3>
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-slate-200">
        <thead class="bg-slate-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Employee</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Present</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Absent</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Late</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Attendance %</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-slate-200">
          <?php
          $query = "SELECT 
                      e.name,
                      COUNT(CASE WHEN a.status = 'present' THEN 1 END) as present_days,
                      COUNT(CASE WHEN a.status = 'absent' THEN 1 END) as absent_days,
                      COUNT(CASE WHEN a.status = 'late' THEN 1 END) as late_days,
                      COUNT(*) as total_days
                    FROM employees e
                    LEFT JOIN attendance a ON e.eid = a.eid AND DATE(a.check_in) BETWEEN ? AND ?
                    GROUP BY e.eid, e.name
                    ORDER BY present_days DESC";
          $stmt = $conn->prepare($query);
          $stmt->bind_param("ss", $start_date, $end_date);
          $stmt->execute();
          $result = $stmt->get_result();

          while($row = $result->fetch_assoc()) {
            $attendance_pct = $row['total_days'] > 0 ? round(($row['present_days'] / $row['total_days']) * 100, 1) : 0;
            echo '<tr>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-900">' . htmlspecialchars($row['name']) . '</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900">' . $row['present_days'] . '</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900">' . $row['absent_days'] . '</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900">' . $row['late_days'] . '</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900">
                <div class="flex items-center gap-2">
                  <div class="w-20 bg-slate-200 rounded-full h-2">
                    <div class="bg-indigo-600 h-2 rounded-full" style="width: ' . $attendance_pct . '%"></div>
                  </div>
                  <span>' . $attendance_pct . '%</span>
                </div>
              </td>
            </tr>';
          }
          ?>
        </tbody>
      </table>
    </div>
  </div>
</div>