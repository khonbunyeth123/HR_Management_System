<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Attendance Check-in</title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: sans-serif; background: #f3f4f6; display: flex; justify-content: center; align-items: center; min-height: 100vh; }
    .card { background: #fff; border-radius: 14px; padding: 2rem; width: 100%; max-width: 400px; box-shadow: 0 2px 16px rgba(0,0,0,0.08); }
    .logo { display: flex; align-items: center; gap: 8px; margin-bottom: 1.5rem; }
    .logo span { font-size: 18px; font-weight: 700; color: #1e1b4b; }
    h2 { font-size: 15px; color: #6b7280; margin-bottom: 1rem; }
    .slot-badge { display: inline-flex; align-items: center; gap: 6px; font-size: 12px; padding: 4px 12px; border-radius: 20px; margin-bottom: 1.25rem; font-weight: 600; }
    .slot-1 { background: #dbeafe; color: #1e40af; }
    .slot-2 { background: #fef3c7; color: #92400e; }
    .slot-3 { background: #d1fae5; color: #065f46; }
    .slot-4 { background: #ede9fe; color: #5b21b6; }
    .slot-0 { background: #f3f4f6; color: #6b7280; }
    label { font-size: 13px; color: #6b7280; display: block; margin-bottom: 6px; }
    select { width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; margin-bottom: 1rem; background: #fff; color: #111827; }
    button[type=submit] { width: 100%; padding: 12px; border-radius: 8px; font-size: 15px; cursor: pointer; border: none; font-weight: 600; background: #4f46e5; color: #fff; }
    button[type=submit]:disabled { background: #9ca3af; cursor: not-allowed; }
    .msg { padding: 10px 14px; border-radius: 8px; font-size: 13px; margin-bottom: 1rem; font-weight: 500; }
    .msg.success { background: #d1fae5; color: #065f46; }
    .msg.warning { background: #fef3c7; color: #92400e; }
    .msg.error   { background: #fee2e2; color: #991b1b; }
    .time-slots { display: grid; grid-template-columns: 1fr 1fr; gap: 6px; margin-top: 1.25rem; }
    .ts { font-size: 11px; padding: 6px 8px; border-radius: 6px; background: #f9fafb; border: 1px solid #e5e7eb; color: #9ca3af; text-align: center; }
    .ts.active { border-color: #4f46e5; color: #4338ca; background: #eef2ff; font-weight: 700; }
    .ts .type-label { font-size: 10px; display: block; margin-top: 2px; }
    .clock { font-size: 12px; color: #9ca3af; text-align: center; margin-top: 1rem; }
  </style>
</head>
<body>
<div class="card">

  <div class="logo">
    <svg width="28" height="28" viewBox="0 0 28 28" fill="none">
      <rect width="28" height="28" rx="8" fill="#4f46e5"/>
      <path d="M14 9v5l3 3" stroke="#fff" stroke-width="2" stroke-linecap="round"/>
    </svg>
    <span>Doorstep</span>
  </div>

  <h2>Attendance Check-in</h2>
  <span class="slot-badge slot-<?= $slot['slot'] ?>">
    <?= htmlspecialchars($slot['label']) ?>
  </span>

  <?php if ($message): ?>
    <div class="msg <?= htmlspecialchars($msgType) ?>"><?= htmlspecialchars($message) ?></div>
  <?php endif; ?>

  <form method="POST">
    <label for="employee_id">Select your name</label>
    <select name="employee_id" id="employee_id" required>
      <option value="">-- select your name --</option>
      <?php foreach ($employees as $emp): ?>
        <option value="<?= $emp['id'] ?>"
          <?= (isset($_POST['employee_id']) && $_POST['employee_id'] == $emp['id']) ? 'selected' : '' ?>>
          <?= htmlspecialchars($emp['full_name']) ?> (#<?= htmlspecialchars($emp['employee_id']) ?>)
        </option>
      <?php endforeach; ?>
    </select>

    <button type="submit" <?= $slot['slot'] === 0 ? 'disabled' : '' ?>>
      <?= htmlspecialchars($slot['label']) ?>
    </button>
  </form>

  <div class="time-slots">
    <div class="ts <?= $slot['slot'] === 1 ? 'active' : '' ?>">07:00–11:59<span class="type-label">Check-in 1</span></div>
    <div class="ts <?= $slot['slot'] === 2 ? 'active' : '' ?>">12:00–12:59<span class="type-label">Check-out 1</span></div>
    <div class="ts <?= $slot['slot'] === 3 ? 'active' : '' ?>">14:00–17:59<span class="type-label">Check-in 2</span></div>
    <div class="ts <?= $slot['slot'] === 4 ? 'active' : '' ?>">18:00–21:00<span class="type-label">Check-out 2</span></div>
  </div>

  <p class="clock" id="clock"></p>
</div>
<script>
  function tick() { document.getElementById('clock').textContent = new Date().toLocaleString(); }
  tick(); setInterval(tick, 1000);
</script>
</body>
</html>