<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Check-in | Doorstep</title>
    <!-- Modern Font: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.iconify.design/2/2.2.1/iconify.min.js"></script>
    <link rel="stylesheet" href="/assets/css/style.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        indigo: {
                            50: '#f5f7ff',
                            100: '#ebf0fe',
                            600: '#4f46e5',
                            700: '#4338ca',
                        },
                    },
                },
            },
        }
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        .soft-shadow {
            box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.04), 0 4px 10px -5px rgba(0, 0, 0, 0.02);
        }
        select {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2394a3b8'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E");
            background-position: right 1rem center;
            background-repeat: no-repeat;
            background-size: 1.25rem;
        }
    </style>
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center p-6 text-slate-900">

<div id="toast-container"></div>

<div class="w-full max-w-[440px]">
    <!-- Main Card -->
    <div class="bg-white rounded-[2.5rem] soft-shadow p-10 md:p-12 border border-slate-100 relative overflow-hidden">
        
        <!-- Header -->
        <div class="flex flex-col items-center mb-10">
            <div class="w-16 h-16 bg-indigo-600 rounded-2xl flex items-center justify-center shadow-lg shadow-indigo-100 mb-6">
                <span class="iconify text-3xl text-white" data-icon="mdi:clock-check"></span>
            </div>
            <h1 class="text-2xl font-bold text-slate-900 text-center tracking-tight">Check-in</h1>
            <p class="text-[11px] font-semibold text-slate-400 mt-1.5 uppercase tracking-widest">Doorstep Attendance</p>
        </div>

        <!-- Current Slot Badge -->
        <div class="flex justify-center mb-8">
            <?php
            $slotColors = [
                1 => 'bg-blue-50 text-blue-600 border-blue-100',
                2 => 'bg-amber-50 text-amber-600 border-amber-100',
                3 => 'bg-emerald-50 text-emerald-600 border-emerald-100',
                4 => 'bg-purple-50 text-purple-600 border-purple-100',
                0 => 'bg-slate-50 text-slate-400 border-slate-100'
            ];
            $colorClass = $slotColors[$slot['slot']] ?? $slotColors[0];
            ?>
            <div class="px-4 py-1.5 rounded-full border text-[10px] font-bold uppercase tracking-wider <?= $colorClass ?> flex items-center gap-2">
                <span class="w-1.5 h-1.5 rounded-full bg-current animate-pulse"></span>
                <?= htmlspecialchars($slot['label']) ?>
            </div>
        </div>

        <!-- Attendance Form -->
        <form method="POST" class="space-y-6">
            <div>
                <label for="employee_id" class="block text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-2.5 ml-1">Your Name</label>
                <div class="relative">
                    <span class="iconify absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" data-icon="mdi:account-outline"></span>
                    <select name="employee_id" id="employee_id" required
                        class="w-full pl-11 pr-12 py-4 bg-slate-50 border border-slate-200 rounded-2xl outline-none focus:ring-2 focus:ring-indigo-600/10 focus:border-indigo-600 transition-all text-sm font-medium appearance-none">
                        <option value="">-- select your name --</option>
                        <?php foreach ($employees as $emp): ?>
                            <option value="<?= $emp['id'] ?>"
                                <?= (isset($_POST['employee_id']) && $_POST['employee_id'] == $emp['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($emp['full_name']) ?> (#<?= htmlspecialchars((string) $emp['id']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <button type="submit" 
                <?= $slot['slot'] === 0 ? 'disabled' : '' ?>
                class="w-full py-4.5 bg-indigo-600 hover:bg-indigo-700 disabled:bg-slate-100 disabled:text-slate-400 disabled:cursor-not-allowed text-white font-bold rounded-2xl shadow-lg shadow-indigo-100 transition-all active:scale-[0.99] flex items-center justify-center gap-2">
                <span class="iconify text-lg" data-icon="mdi:check-circle"></span>
                Confirm Attendance
            </button>
        </form>

        <!-- Time Slots Grid -->
        <div class="mt-12 grid grid-cols-2 gap-3">
            <?php
            $slotsData = [
                ['time' => '07:00–11:59', 'label' => 'Check-in 1', 'id' => 1],
                ['time' => '12:00–12:59', 'label' => 'Check-out 1', 'id' => 2],
                ['time' => '14:00–17:59', 'label' => 'Check-in 2', 'id' => 3],
                ['time' => '18:00–21:00', 'label' => 'Check-out 2', 'id' => 4],
            ];
            foreach ($slotsData as $s):
                $isActive = $slot['slot'] === $s['id'];
            ?>
                <div class="p-3.5 rounded-2xl border <?= $isActive ? 'bg-indigo-50 border-indigo-200' : 'bg-white border-slate-100' ?> transition-all">
                    <div class="text-[9px] font-bold <?= $isActive ? 'text-indigo-600' : 'text-slate-400' ?> mb-0.5 uppercase tracking-wider"><?= $s['label'] ?></div>
                    <div class="text-[11px] font-semibold <?= $isActive ? 'text-indigo-900' : 'text-slate-500' ?>"><?= $s['time'] ?></div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Minimal Clock -->
        <div class="mt-10 pt-8 border-t border-slate-50 text-center">
            <div id="clock" class="text-lg font-bold text-slate-800 tabular-nums tracking-tight"></div>
            <div class="text-[10px] font-semibold text-slate-400 uppercase tracking-widest mt-1">Server Time</div>
        </div>
    </div>

    <!-- Minimal Footer -->
    <p class="mt-8 text-center text-slate-400 text-[10px] font-bold uppercase tracking-widest">
        &copy; <?= date('Y') ?> Doorstep Technology
    </p>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        // --- Minimalist Clock ---
        function tick() {
            const now = new Date();
            document.getElementById('clock').textContent = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true });
        }
        tick();
        setInterval(tick, 1000);

        // --- Toast Messages from PHP ---
        <?php if ($message): ?>
            const type = '<?= $msgType === "error" ? "error" : ($msgType === "warning" ? "warning" : "success") ?>';
            const title = '<?= $msgType === "error" ? "Action Failed" : ($msgType === "warning" ? "Attention" : "Success") ?>';
            setTimeout(() => {
                if (window.Toast) {
                    window.Toast[type](title, '<?= addslashes($message) ?>');
                }
            }, 500);
        <?php endif; ?>
    });
</script>
</body>
</html>
