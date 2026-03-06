<div class="w-full h-full">
    <div class="bg-white shadow-lg p-4">
        <h1 class="text-3xl font-bold text-slate-800 mb-6">Top Employees - Attendance Leaderboard</h1>

        <!-- Period Selector -->
        <div class="mb-6 flex flex-wrap gap-4 items-end">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Select Period:</label>
                <select id="periodFilter" onchange="handlePeriodChange()" class="p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="week">This Week</option>
                    <option value="month" selected>This Month</option>
                    <option value="quarter">This Quarter</option>
                    <option value="year">This Year</option>
                    <option value="custom">Custom Range</option>
                </select>
            </div>
            <div id="customRange" class="hidden">
                <label class="block text-sm font-medium text-gray-700 mb-2">From:</label>
                <input type="date" id="fromDate" class="p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>
            <div id="customRangeTo" class="hidden">
                <label class="block text-sm font-medium text-gray-700 mb-2">To:</label>
                <input type="date" id="toDate" class="p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>
            <button onclick="loadReport()" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition">Load Report</button>
        </div>

        <!-- Loading -->
        <div id="loadingMsg" class="hidden text-center py-6 text-blue-500 animate-pulse">⏳ Loading...</div>

        <!-- Leaderboard Table -->
        <h2 class="text-2xl font-bold text-slate-800 mb-4">Complete Leaderboard</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border border-gray-200 rounded-lg">
                <thead class="bg-slate-800 text-white">
                    <tr>
                        <th class="p-3 text-center">Rank</th>
                        <th class="p-3 text-left">Employee Name</th>
                        <th class="p-3 text-center">Department</th>
                        <th class="p-3 text-center">Total Present</th>
                        <th class="p-3 text-center">Total Late</th>
                        <th class="p-3 text-center">Total Absent</th>
                        <th class="p-3 text-center">Attendance Score</th>
                        <th class="p-3 text-center">Rating</th>
                    </tr>
                </thead>
                <tbody id="leaderboardTableBody">
                    <tr>
                        <td colspan="8" class="text-center p-4 text-gray-500">Select a period and click Load Report</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Recognition Section -->
        <div id="recognitionBox" class="hidden mt-8 bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
            <h3 class="text-lg font-bold text-blue-900 mb-2">🏆 Recognition</h3>
            <p class="text-blue-800" id="recognitionText"></p>
        </div>
    </div>
</div>

<script>
// ── Get date range based on period ──
function getDateRange(period) {
    const today = new Date();
    let from, to = today.toISOString().split('T')[0];

    if (period === 'week') {
        const day  = today.getDay();
        const diff = today.getDate() - day + (day === 0 ? -6 : 1);
        from = new Date(today.setDate(diff)).toISOString().split('T')[0];
        to   = new Date().toISOString().split('T')[0];
    } else if (period === 'month') {
        from = new Date(today.getFullYear(), today.getMonth(), 1).toISOString().split('T')[0];
    } else if (period === 'quarter') {
        const q = Math.floor(today.getMonth() / 3);
        from = new Date(today.getFullYear(), q * 3, 1).toISOString().split('T')[0];
    } else if (period === 'year') {
        from = new Date(today.getFullYear(), 0, 1).toISOString().split('T')[0];
    } else if (period === 'custom') {
        from = document.getElementById('fromDate').value;
        to   = document.getElementById('toDate').value;
    }

    return { from, to };
}

function handlePeriodChange() {
    const period = document.getElementById('periodFilter').value;
    document.getElementById('customRange').classList.toggle('hidden', period !== 'custom');
    document.getElementById('customRangeTo').classList.toggle('hidden', period !== 'custom');
}

// ── Rank badge ──
function rankBadge(rank) {
    if (rank === 1) return `<span class="text-2xl">🥇</span><span class="font-bold text-lg ml-2">1</span>`;
    if (rank === 2) return `<span class="text-2xl">🥈</span><span class="font-bold text-lg ml-2">2</span>`;
    if (rank === 3) return `<span class="text-2xl">🥉</span><span class="font-bold text-lg ml-2">3</span>`;
    return `<span class="font-bold text-lg">${rank}</span>`;
}

// ── Row background ──
function rowBg(rank) {
    if (rank === 1) return 'bg-yellow-50 hover:bg-yellow-100';
    if (rank === 2) return 'bg-gray-50 hover:bg-gray-100';
    if (rank === 3) return 'bg-amber-50 hover:bg-amber-100';
    return 'hover:bg-gray-50';
}

// ── Star rating ──
function stars(percent) {
    if (percent >= 95) return '⭐⭐⭐⭐⭐';
    if (percent >= 85) return '⭐⭐⭐⭐';
    if (percent >= 75) return '⭐⭐⭐';
    if (percent >= 60) return '⭐⭐';
    return '⭐';
}

// ── Score badge color ──
function scoreBadge(percent) {
    if (percent >= 90) return 'bg-green-100 text-green-800';
    if (percent >= 75) return 'bg-yellow-100 text-yellow-800';
    return 'bg-red-100 text-red-800';
}

// ── Load Report ──
async function loadReport() {
    const period = document.getElementById('periodFilter').value;
    const { from, to } = getDateRange(period);

    if (!from || !to) {
        alert('Please select both From and To dates.');
        return;
    }

    document.getElementById('loadingMsg').classList.remove('hidden');
    document.getElementById('leaderboardTableBody').innerHTML = '';
    document.getElementById('recognitionBox').classList.add('hidden');

    try {
        const res  = await fetch(`http://localhost:8080/api/report/top-employees?from=${from}&to=${to}`);
        const json = await res.json();

        if (!json.success) {
            alert(json.message || 'Failed to load report.');
            return;
        }

        renderTable(json.data);

    } catch (err) {
        console.error(err);
        alert('Network error. Please try again.');
    } finally {
        document.getElementById('loadingMsg').classList.add('hidden');
    }
}

// ── Render Table ──
function renderTable(data) {
    const tbody = document.getElementById('leaderboardTableBody');

    if (!data || data.length === 0) {
        tbody.innerHTML = `<tr><td colspan="8" class="text-center p-4 text-gray-500">No data found.</td></tr>`;
        return;
    }

    tbody.innerHTML = data.map((r, i) => {
        const rank    = i + 1;
        const percent = r.attendance_percent;
        return `
        <tr class="border-b ${rowBg(rank)}">
            <td class="p-3 text-center">${rankBadge(rank)}</td>
            <td class="p-3 ${rank <= 3 ? 'font-bold' : ''}">${r.full_name}</td>
            <td class="p-3 text-center">
                <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs">${r.department}</span>
            </td>
            <td class="p-3 text-center text-green-600 font-semibold">${r.present_days}</td>
            <td class="p-3 text-center text-yellow-600 font-semibold">${r.late_days}</td>
            <td class="p-3 text-center text-red-600 font-semibold">${r.absent_days}</td>
            <td class="p-3 text-center">
                <span class="${scoreBadge(percent)} px-3 py-1 rounded-full text-sm font-bold">${percent}%</span>
            </td>
            <td class="p-3 text-center text-2xl">${stars(percent)}</td>
        </tr>`;
    }).join('');

    // Show recognition for #1
    if (data[0]) {
        document.getElementById('recognitionText').innerHTML =
            `Congratulations to <strong>${data[0].full_name}</strong> for achieving the highest attendance score this period! Keep up the excellent work! 🎉`;
        document.getElementById('recognitionBox').classList.remove('hidden');
    }
}

// ── Auto load on page ready ──
document.addEventListener('DOMContentLoaded', () => loadReport());
</script>