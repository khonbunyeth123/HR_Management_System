<?php
// resources/views/report/report_detail.php
?>
<body class="bg-slate-100 min-h-screen p-6">
<div class="max-w-7xl mx-auto bg-white rounded-2xl shadow-xl p-6">
  <div class="flex flex-wrap justify-between items-center mb-6 gap-3">
    <h1 class="text-2xl font-bold text-slate-800">📋 Detailed Attendance Report</h1>
    <div class="flex gap-2">
      <button onclick="exportExcel()" class="flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold px-4 py-2 rounded-lg transition">📊 Export Excel</button>
      <button onclick="exportPDF()"   class="flex items-center gap-2 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold px-4 py-2 rounded-lg transition">📄 Export PDF</button>
    </div>
  </div>

  <!-- Filters -->
  <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 mb-6">
    <h2 class="text-sm font-bold text-slate-600 uppercase tracking-wide mb-3">🔍 Filters &amp; Search</h2>
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3">
      <div class="flex flex-col gap-1">
        <label class="text-xs font-semibold text-slate-500">From Date</label>
        <input type="date" id="fromDate" oninput="fetchData()" class="text-sm border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
      </div>
      <div class="flex flex-col gap-1">
        <label class="text-xs font-semibold text-slate-500">To Date</label>
        <input type="date" id="toDate" oninput="fetchData()" class="text-sm border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
      </div>
      <div class="flex flex-col gap-1">
        <label class="text-xs font-semibold text-slate-500">Search</label>
        <input type="text" id="searchInput" placeholder="Name, ID or Check Type…" oninput="applyFilters()" class="text-sm border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
      </div>
      <div class="flex flex-col gap-1">
        <label class="text-xs font-semibold text-slate-500">Department</label>
        <select id="deptFilter" onchange="fetchData()" class="text-sm border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
          <option value="">All</option>
          <option>IT</option><option>HR</option><option>Sales</option><option>Finance</option>
        </select>
      </div>
      <div class="flex flex-col gap-1">
        <label class="text-xs font-semibold text-slate-500">Status</label>
        <select id="statusFilter" onchange="fetchData()" class="text-sm border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
          <option value="">All</option>
          <option>On Time</option><option>Late</option><option>Early</option><option>Missing</option>
        </select>
      </div>
    </div>
    <div class="flex items-center gap-3 mt-3">
      <span id="recordCount" class="text-xs text-slate-400"></span>
      <span id="loadingIndicator" class="hidden text-xs text-blue-500 animate-pulse">⏳ Loading...</span>
    </div>
  </div>

  <!-- Table -->
  <div class="overflow-x-auto rounded-xl border border-slate-200 shadow-sm">
    <table class="w-full min-w-[900px] text-sm">
      <thead>
        <tr class="bg-slate-800 text-white text-xs uppercase tracking-wide">
          <th class="text-left px-4 py-3 cursor-pointer hover:bg-slate-700 transition" onclick="sortBy('name')">Employee <span id="sort_name"></span></th>
          <th class="px-4 py-3 cursor-pointer hover:bg-slate-700 transition" onclick="sortBy('employee_id')">ID <span id="sort_employee_id"></span></th>
          <th class="px-4 py-3 cursor-pointer hover:bg-slate-700 transition" onclick="sortBy('date')">Date <span id="sort_date"></span></th>
          <th class="px-4 py-3">Day</th>
          <th class="px-4 py-3">Check Type</th>
          <th class="px-4 py-3">Standard</th>
          <th class="px-4 py-3">Actual</th>
          <th class="px-4 py-3">Diff</th>
          <th class="px-4 py-3">Status</th>
          <th class="px-4 py-3">Action</th>
        </tr>
      </thead>
      <tbody id="tableBody"></tbody>
    </table>
  </div>

  <!-- Pagination -->
  <div class="flex flex-wrap justify-between items-center mt-5 gap-3">
    <p class="text-sm text-slate-500" id="pageInfo"></p>
    <div class="flex flex-wrap gap-1.5" id="pageBtns"></div>
  </div>
</div>

<!-- ── View Details Modal ── -->
<div id="detailModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
  <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden">
    <div class="bg-slate-800 text-white px-6 py-4 flex justify-between items-center">
      <h3 class="font-bold text-base" id="modalTitle">Attendance Details</h3>
      <button onclick="closeModal('detailModal')" class="text-slate-300 hover:text-red-400 text-xl leading-none transition">✕</button>
    </div>
    <div class="p-6 overflow-y-auto max-h-[70vh]" id="modalBody"></div>
    <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex justify-end gap-2">
      <button onclick="closeModal('detailModal')" class="text-sm bg-slate-200 hover:bg-slate-300 text-slate-700 font-semibold px-4 py-2 rounded-lg transition">Close</button>
      <button onclick="window.print()" class="text-sm bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded-lg transition">🖨 Print</button>
    </div>
  </div>
</div>

<!-- ── Report Issue Modal ── -->
<div id="issueModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
  <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden">
    <div class="bg-red-600 text-white px-6 py-4 flex justify-between items-center">
      <h3 class="font-bold text-base">🚨 Report Missing Record</h3>
      <button onclick="closeModal('issueModal')" class="text-red-200 hover:text-white text-xl leading-none transition">✕</button>
    </div>
    <div class="p-6 space-y-3">
      <p class="text-xs text-slate-500">Submit a report for the missing attendance record below.</p>
      <div class="space-y-1"><label class="text-xs font-semibold text-slate-500">Employee</label><input id="issueEmp" readonly class="w-full text-sm border border-slate-200 rounded-lg px-3 py-2 bg-slate-50"></div>
      <div class="space-y-1"><label class="text-xs font-semibold text-slate-500">Date</label><input id="issueDate" readonly class="w-full text-sm border border-slate-200 rounded-lg px-3 py-2 bg-slate-50"></div>
      <div class="space-y-1"><label class="text-xs font-semibold text-slate-500">Check Type</label><input id="issueCheck" readonly class="w-full text-sm border border-slate-200 rounded-lg px-3 py-2 bg-slate-50"></div>
      <div class="space-y-1"><label class="text-xs font-semibold text-slate-500">Issue Type</label>
        <select id="issueType" class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-red-400">
          <option>Forgot to check in/out</option><option>Device malfunction</option>
          <option>Was absent (sick)</option><option>Was absent (leave)</option><option>Other</option>
        </select>
      </div>
      <div class="space-y-1"><label class="text-xs font-semibold text-slate-500">Notes</label>
        <textarea id="issueNotes" rows="3" placeholder="Additional details…" class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-400 resize-none"></textarea>
      </div>
    </div>
    <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex justify-end gap-2">
      <button onclick="closeModal('issueModal')" class="text-sm bg-slate-200 hover:bg-slate-300 text-slate-700 font-semibold px-4 py-2 rounded-lg transition">Cancel</button>
      <button onclick="submitIssue()" class="text-sm bg-red-600 hover:bg-red-700 text-white font-semibold px-4 py-2 rounded-lg transition">Submit Report</button>
    </div>
  </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

<script>
// ── State ──
let RECORDS = [];
let filtered = [];
let sortCol = 'date', sortDir = -1, page = 1;
const PER_PAGE = 10;

// ── Init: set default dates then fetch ──
(function init() {
  const today = new Date();
  const firstDay = new Date(today.getFullYear(), today.getMonth(), 1).toISOString().split('T')[0];
  const lastDay  = today.toISOString().split('T')[0];
  document.getElementById('fromDate').value = firstDay;
  document.getElementById('toDate').value   = lastDay;
  fetchData();
})();

// ── Fetch from API ──
async function fetchData() {
  const from   = document.getElementById('fromDate').value;
  const to     = document.getElementById('toDate').value;
  const dept   = document.getElementById('deptFilter').value;
  const status = document.getElementById('statusFilter').value;

  if (!from || !to) return;

  // Build query params
  const params = new URLSearchParams({ from, to });
  if (dept)   params.append('department', dept);
  if (status) params.append('status', status);

  document.getElementById('loadingIndicator').classList.remove('hidden');

  try {
    const res  = await fetch(`/api/report/detailed?${params.toString()}`);
    const json = await res.json();

    if (json.success) {
      RECORDS  = json.data;
      filtered = [...RECORDS];
      applyFilters();
    } else {
      showError(json.message || 'Failed to load data.');
    }
  } catch (err) {
    showError('Network error. Please try again.');
    console.error(err);
  } finally {
    document.getElementById('loadingIndicator').classList.add('hidden');
  }
}

function showError(msg) {
  document.getElementById('tableBody').innerHTML =
    `<tr><td colspan="10" class="text-center py-12 text-red-400 text-sm">⚠ ${msg}</td></tr>`;
}

// ── Helpers ──
const checkBadgeClass = t => ({
  'Check-in 1' :'bg-blue-100 text-blue-700',
  'Check-out 1':'bg-purple-100 text-purple-700',
  'Check-in 2' :'bg-orange-100 text-orange-700',
  'Check-out 2':'bg-red-100 text-red-700'
}[t] || 'bg-slate-100 text-slate-600');

function statusPill(s) {
  const map  = {'On Time':'bg-green-100 text-green-700','Late':'bg-yellow-100 text-yellow-700','Missing':'bg-red-100 text-red-700','Early':'bg-blue-100 text-blue-700'};
  const icon = {'On Time':'✓','Late':'⚠','Missing':'✗','Early':'⚡'}[s] || '';
  return `<span class="inline-block px-3 py-0.5 rounded-full text-xs font-semibold ${map[s]||''}">${icon} ${s}</span>`;
}
function diffCell(d) {
  if (d === null || d === undefined) return `<span class="text-red-500 font-bold text-xs">N/A</span>`;
  if (d === 0)  return `<span class="text-green-600 text-xs">0 min</span>`;
  if (d > 0)    return `<span class="text-yellow-600 font-bold text-xs">+${d} min</span>`;
  return `<span class="text-blue-600 font-bold text-xs">${d} min</span>`;
}
function actualCell(a, s) {
  const col = {'On Time':'text-green-600','Late':'text-yellow-600','Missing':'text-red-500','Early':'text-blue-600'}[s] || 'text-slate-700';
  return `<span class="font-semibold ${col}">${a ?? '--:--'}</span>`;
}

// ── Render Table ──
function render() {
  const start = (page - 1) * PER_PAGE;
  const end   = start + PER_PAGE;
  const rows  = filtered.slice(start, end);
  const tbody = document.getElementById('tableBody');

  if (!rows.length) {
    tbody.innerHTML = `<tr><td colspan="10" class="text-center py-12 text-slate-400 text-sm">No records found.</td></tr>`;
  } else {
    tbody.innerHTML = rows.map((r, i) => `
      <tr class="border-b border-slate-100 hover:bg-slate-50 transition ${r.status === 'Missing' ? 'bg-red-50' : ''} text-sm">
        <td class="px-4 py-3 font-semibold text-slate-800 whitespace-nowrap">${r.name}</td>
        <td class="px-4 py-3 text-center text-slate-500">${r.employee_id}</td>
        <td class="px-4 py-3 text-center text-slate-600">${r.date}</td>
        <td class="px-4 py-3 text-center text-slate-400 text-xs">${r.day}</td>
        <td class="px-4 py-3 text-center">
          <span class="inline-block px-2 py-0.5 rounded text-xs font-semibold ${checkBadgeClass(r.check_type)}">${r.check_type}</span>
        </td>
        <td class="px-4 py-3 text-center text-slate-500">${r.standard_time}</td>
        <td class="px-4 py-3 text-center">${actualCell(r.actual_time, r.status)}</td>
        <td class="px-4 py-3 text-center">${diffCell(r.diff)}</td>
        <td class="px-4 py-3 text-center">${statusPill(r.status)}</td>
        <td class="px-4 py-3 text-center">
          ${r.status === 'Missing'
            ? `<button onclick="openIssue(${start + i})" class="text-xs font-semibold text-red-500 hover:text-red-700 underline transition">Report Issue</button>`
            : `<button onclick="openDetail(${start + i})" class="text-xs font-semibold text-blue-500 hover:text-blue-700 underline transition">View Details</button>`}
        </td>
      </tr>`).join('');
  }

  document.getElementById('recordCount').textContent =
    `Showing ${Math.min(start + 1, filtered.length)}–${Math.min(end, filtered.length)} of ${filtered.length} records`;
  renderPagination();
}

function renderPagination() {
  const total = Math.ceil(filtered.length / PER_PAGE) || 1;
  document.getElementById('pageInfo').textContent = `Page ${page} of ${total}`;
  const base   = 'text-xs font-semibold px-3 py-1.5 rounded-lg transition';
  const active = 'bg-blue-600 text-white';
  const normal = 'bg-slate-200 hover:bg-slate-300 text-slate-600';
  const dis    = 'bg-slate-100 text-slate-300 cursor-not-allowed';
  let html = `
    <button class="${base} ${page===1?dis:normal}" onclick="goPage(1)" ${page===1?'disabled':''}>« First</button>
    <button class="${base} ${page===1?dis:normal}" onclick="goPage(${page-1})" ${page===1?'disabled':''}>‹ Prev</button>`;
  for (let p2 = 1; p2 <= total; p2++) {
    if (p2 === 1 || p2 === total || Math.abs(p2 - page) <= 1)
      html += `<button class="${base} ${p2===page?active:normal}" onclick="goPage(${p2})">${p2}</button>`;
    else if (Math.abs(p2 - page) === 2)
      html += `<span class="text-xs text-slate-400 px-1 self-center">…</span>`;
  }
  html += `
    <button class="${base} ${page===total?dis:normal}" onclick="goPage(${page+1})" ${page===total?'disabled':''}>Next ›</button>
    <button class="${base} ${page===total?dis:normal}" onclick="goPage(${total})" ${page===total?'disabled':''}>Last »</button>`;
  document.getElementById('pageBtns').innerHTML = html;
}
function goPage(p) {
  const t = Math.ceil(filtered.length / PER_PAGE) || 1;
  page = Math.max(1, Math.min(p, t));
  render();
}

// ── Client-side Filter (search only — dept/status sent to API) ──
function applyFilters() {
  const q = document.getElementById('searchInput').value.toLowerCase();
  filtered = RECORDS.filter(r =>
    !q ||
    r.name.toLowerCase().includes(q) ||
    String(r.employee_id).toLowerCase().includes(q) ||
    r.check_type.toLowerCase().includes(q)
  );
  sortData();
  page = 1;
  render();
}

// ── Sort ──
function sortBy(col) {
  if (sortCol === col) sortDir *= -1; else { sortCol = col; sortDir = 1; }
  ['name','employee_id','date'].forEach(c =>
    document.getElementById('sort_' + c).textContent = c === col ? (sortDir === 1 ? '▲' : '▼') : ''
  );
  sortData(); render();
}
function sortData() {
  filtered.sort((a, b) => {
    const av = a[sortCol] ?? '', bv = b[sortCol] ?? '';
    return av < bv ? -sortDir : av > bv ? sortDir : 0;
  });
}

// ── View Details Modal ──
function openDetail(idx) {
  const r = filtered[idx];
  document.getElementById('modalTitle').textContent = `${r.name} — ${r.check_type} (${r.date})`;
  const statusBg = {
    'On Time':'bg-green-100 text-green-700',
    'Late'   :'bg-yellow-100 text-yellow-700',
    'Early'  :'bg-blue-100 text-blue-700',
    'Missing':'bg-red-100 text-red-700'
  }[r.status] || '';
  const diffStr  = r.diff === null ? 'N/A' : r.diff === 0 ? '0 min' : r.diff > 0 ? `+${r.diff} min` : `${r.diff} min`;
  const dotColor = s => ({'On Time':'bg-green-500','Late':'bg-yellow-400','Missing':'bg-red-500','Early':'bg-blue-500'}[s] || 'bg-slate-400');

  // All check types for this employee on this date (from loaded RECORDS)
  const dayRecs = RECORDS
    .filter(x => x.employee_id === r.employee_id && x.date === r.date)
    .sort((a, b) => a.check_type.localeCompare(b.check_type));

  document.getElementById('modalBody').innerHTML = `
    <div class="mb-5">
      <p class="text-xs font-bold uppercase tracking-widest text-slate-400 mb-3 border-b pb-1">Employee Info</p>
      <div class="grid grid-cols-2 gap-3">
        ${[['Full Name',r.name],['Employee ID',r.employee_id],['Department',r.department]].map(([l,v])=>`
        <div><p class="text-xs text-slate-400 font-semibold">${l}</p><p class="text-sm font-bold text-slate-700">${v}</p></div>`).join('')}
      </div>
    </div>
    <div class="mb-5">
      <p class="text-xs font-bold uppercase tracking-widest text-slate-400 mb-3 border-b pb-1">Check Record</p>
      <div class="grid grid-cols-2 gap-3">
        ${[
          ['Date',`${r.date} (${r.day})`],
          ['Check Type',`<span class="text-xs font-semibold px-2 py-0.5 rounded ${checkBadgeClass(r.check_type)}">${r.check_type}</span>`],
          ['Standard Time', r.standard_time],
          ['Actual Time',   r.actual_time ?? '--:--'],
          ['Difference',    diffStr],
          ['Status',`<span class="text-xs font-semibold px-2 py-0.5 rounded-full ${statusBg}">${r.status}</span>`]
        ].map(([l,v])=>`
        <div><p class="text-xs text-slate-400 font-semibold">${l}</p><p class="text-sm font-semibold text-slate-700">${v}</p></div>`).join('')}
      </div>
    </div>
    <div>
      <p class="text-xs font-bold uppercase tracking-widest text-slate-400 mb-3 border-b pb-1">Day Timeline — ${r.date}</p>
      <div class="relative pl-5 border-l-2 border-slate-200 space-y-4">
        ${dayRecs.map(x => `
        <div class="relative">
          <div class="absolute -left-[1.35rem] top-1 w-3 h-3 rounded-full border-2 border-white ${dotColor(x.status)}"></div>
          <p class="text-xs text-slate-400">${x.check_type}</p>
          <p class="text-sm font-bold text-slate-700">${x.actual_time ?? '--:--'} <span class="text-xs font-normal text-slate-400">(std: ${x.standard_time})</span></p>
          <div class="mt-0.5">${statusPill(x.status)}</div>
        </div>`).join('')}
      </div>
    </div>`;
  document.getElementById('detailModal').classList.remove('hidden');
}

// ── Report Issue ──
function openIssue(idx) {
  const r = filtered[idx];
  document.getElementById('issueEmp').value   = `${r.name} (${r.employee_id})`;
  document.getElementById('issueDate').value  = `${r.date} — ${r.day}`;
  document.getElementById('issueCheck').value = r.check_type;
  document.getElementById('issueNotes').value = '';
  document.getElementById('issueModal').classList.remove('hidden');
}
function submitIssue() {
  alert('✅ Report submitted!\nHR will review within 24 hours.');
  closeModal('issueModal');
}
function closeModal(id) { document.getElementById(id).classList.add('hidden'); }
document.querySelectorAll('#detailModal,#issueModal').forEach(m =>
  m.addEventListener('click', e => { if (e.target === m) m.classList.add('hidden'); })
);

// ── Export ──
function exportExcel() {
  const cols = ['Name','ID','Department','Date','Day','Check Type','Standard Time','Actual Time','Diff (min)','Status'];
  const rows = filtered.map(r => [
    r.name, r.employee_id, r.department, r.date, r.day,
    r.check_type, r.standard_time, r.actual_time ?? '--:--',
    r.diff ?? 'N/A', r.status
  ]);
  const ws = XLSX.utils.aoa_to_sheet([cols, ...rows]);
  ws['!cols'] = [{wch:18},{wch:10},{wch:12},{wch:12},{wch:10},{wch:13},{wch:14},{wch:14},{wch:10},{wch:10}];
  const wb = XLSX.utils.book_new();
  XLSX.utils.book_append_sheet(wb, 'Attendance', ws);
  XLSX.writeFile(wb, 'attendance_report.xlsx');
}
function exportPDF() {
  const {jsPDF} = window.jspdf;
  const doc = new jsPDF({orientation:'landscape'});
  doc.setFontSize(14); doc.text('Detailed Attendance Report', 14, 16);
  doc.setFontSize(8);
  const headers = ['Name','ID','Date','Check Type','Std','Actual','Diff','Status'];
  let y = 26;
  doc.setFillColor(30,41,59); doc.setTextColor(255,255,255);
  doc.rect(10, y-5, 277, 8, 'F');
  headers.forEach((h, i) => doc.text(h, 12 + i*35, y));
  doc.setTextColor(0,0,0); y += 8;
  filtered.forEach(r => {
    if (y > 185) { doc.addPage(); y = 20; }
    [r.name, r.employee_id, r.date, r.check_type, r.standard_time, r.actual_time ?? '--:--', r.diff ?? 'N/A', r.status]
      .forEach((v, i) => doc.text(String(v), 12 + i*35, y));
    y += 7;
  });
  doc.save('attendance.pdf');
}
</script>
</body>