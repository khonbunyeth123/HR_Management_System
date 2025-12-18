<div class="p-6">
  <div class="mb-6">
    <h2 class="text-2xl font-bold text-slate-800">Top Employees</h2>
    <p class="text-slate-600">Best performing employees based on attendance</p>
  </div>

  <div class="bg-white rounded-lg shadow p-6 mb-6">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-2">Period</label>
        <select id="period" class="border rounded px-3 py-2 w-full">
          <option value="week">This Week</option>
          <option value="month" selected>This Month</option>
          <option value="quarter">This Quarter</option>
          <option value="year">This Year</option>
        </select>
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-2">Metric</label>
        <select id="metric" class="border rounded px-3 py-2 w-full">
          <option value="attendance">Attendance Rate</option>
          <option value="punctuality">Punctuality</option>
          <option value="hours">Working Hours</option>
        </select>
      </div>
      <div class="flex items-end">
        <button class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 w-full">Update</button>
      </div>
    </div>
  </div>

  <?php
  $start_date = date('Y-m-01');
  $end_date = date('Y-m-d');
  
  $query = "SELECT 
              e.eid,
              e.name,
              e.department,
              COUNT(CASE WHEN a.status = 'present' THEN 1 END) as present_days,
              COUNT(CASE WHEN a.status = 'late' THEN 1 END) as late_days,
              COUNT(*) as total_days,
              SUM(TIMESTAMPDIFF(HOUR, a.check_in, a.check_out)) as total_hours
            FROM employees e
            LEFT JOIN attendance a ON e.eid = a.eid AND DATE(a.check_in) BETWEEN ? AND ?
            GROUP BY e.eid, e.name, e.department
            HAVING total_days > 0
            ORDER BY present_days DESC, late_days ASC
            LIMIT 10";
  
  $stmt = $conn->prepare($query);
  $stmt->bind_param("ss", $start_date, $end_date);
  $stmt->execute();
  $result = $stmt->get_result();
  ?>

  <div class="grid grid-cols-1 gap-4">
    <?php 
    $rank = 1;
    while($row = $result->fetch_assoc()) {
      $attendance_rate = round(($row['present_days'] / $row['total_days']) * 100, 1);
      $medal_class = $rank == 1 ? 'bg-yellow-400' : ($rank == 2 ? 'bg-gray-300' : ($rank == 3 ? 'bg-orange-400' : 'bg-slate-200'));
      $medal_icon = $rank <= 3 ? '🏆' : '⭐';
    ?>
      <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6 hover:shadow-md transition-shadow">
        <div class="flex items-center gap-4">
          <div class="<?= $medal_class ?> w-16 h-16 rounded-full flex items-center justify-center text-3xl font-bold">
            <?= $rank ?>
          </div>
          
          <div class="flex-1">
            <div class="flex items-center gap-2 mb-1">
              <h3 class="text-lg font-semibold text-slate-900"><?= htmlspecialchars($row['name']) ?></h3>
              <?php if($rank <= 3) echo '<span class="text-2xl">' . $medal_icon . '</span>'; ?>
            </div>
            <p class="text-sm text-slate-600"><?= htmlspecialchars($row['department']) ?></p>
          </div>

          <div class="grid grid-cols-3 gap-6 text-center">
            <div>
              <div class="text-2xl font-bold text-indigo-600"><?= $attendance_rate ?>%</div>
              <div class="text-xs text-slate-500">Attendance</div>
            </div>
            <div>
              <div class="text-2xl font-bold text-green-600"><?= $row['present_days'] ?></div>
              <div class="text-xs text-slate-500">Present Days</div>
            </div>
            <div>
              <div class="text-2xl font-bold text-blue-600"><?= $row['total_hours'] ?? 0 ?></div>
              <div class="text-xs text-slate-500">Total Hours</div>
            </div>
          </div>
        </div>

        <div class="mt-4">
          <div class="w-full bg-slate-200 rounded-full h-2">
            <div class="bg-indigo-600 h-2 rounded-full transition-all" style="width: <?= $attendance_rate ?>%"></div>
          </div>
        </div>
      </div>
    <?php 
      $rank++;
    } 
    ?>
  </div>
</div>