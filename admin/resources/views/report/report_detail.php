
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
        <input type="date" id="fromDate" value="2025-12-01" oninput="applyFilters()" class="text-sm border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
      </div>
      <div class="flex flex-col gap-1">
        <label class="text-xs font-semibold text-slate-500">To Date</label>
        <input type="date" id="toDate" value="2025-12-24" oninput="applyFilters()" class="text-sm border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
      </div>
      <div class="flex flex-col gap-1">
        <label class="text-xs font-semibold text-slate-500">Search</label>
        <input type="text" id="searchInput" placeholder="Name or ID…" oninput="applyFilters()" class="text-sm border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
      </div>
      <div class="flex flex-col gap-1">
        <label class="text-xs font-semibold text-slate-500">Department</label>
        <select id="deptFilter" onchange="applyFilters()" class="text-sm border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
          <option value="">All</option>
          <option>IT</option><option>HR</option><option>Sales</option><option>Finance</option>
        </select>
      </div>
      <div class="flex flex-col gap-1">
        <label class="text-xs font-semibold text-slate-500">Status</label>
        <select id="statusFilter" onchange="applyFilters()" class="text-sm border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
          <option value="">All</option>
          <option>On Time</option><option>Late</option><option>Early</option><option>Missing</option>
        </select>
      </div>
    </div>
    <div class="flex items-center gap-3 mt-3">
      <span id="recordCount" class="text-xs text-slate-400"></span>
    </div>
  </div>

  <!-- Table -->
  <div class="overflow-x-auto rounded-xl border border-slate-200 shadow-sm">
    <table class="w-full min-w-[900px] text-sm">
      <thead>
        <tr class="bg-slate-800 text-white text-xs uppercase tracking-wide">
          <th class="text-left px-4 py-3 cursor-pointer hover:bg-slate-700 transition" onclick="sortBy('name')">Employee <span id="sort_name"></span></th>
          <th class="px-4 py-3 cursor-pointer hover:bg-slate-700 transition" onclick="sortBy('id')">ID <span id="sort_id"></span></th>
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
  <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden animate-none">
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

<script>
const RECORDS = [
  {id:'EMP001',name:'John Doe',      dept:'IT',     date:'2025-12-24',day:'Tuesday', checkType:'Check-in 1', std:'08:00 AM',actual:'08:00 AM',diff:0,  status:'On Time',notes:'Scanned at main entrance',device:'Door A - Biometric',location:'Head Office'},
  {id:'EMP001',name:'John Doe',      dept:'IT',     date:'2025-12-24',day:'Tuesday', checkType:'Check-out 1',std:'12:00 PM',actual:'12:00 PM',diff:0,  status:'On Time',notes:'Lunch break checkout',    device:'Door A - Biometric',location:'Head Office'},
  {id:'EMP001',name:'John Doe',      dept:'IT',     date:'2025-12-24',day:'Tuesday', checkType:'Check-in 2', std:'01:00 PM',actual:'01:03 PM',diff:3,  status:'Late',   notes:'Slight delay after lunch', device:'Door A - Biometric',location:'Head Office'},
  {id:'EMP001',name:'John Doe',      dept:'IT',     date:'2025-12-24',day:'Tuesday', checkType:'Check-out 2',std:'05:00 PM',actual:'05:00 PM',diff:0,  status:'On Time',notes:'End of day checkout',      device:'Door A - Biometric',location:'Head Office'},
  {id:'EMP002',name:'Jane Smith',    dept:'HR',     date:'2025-12-24',day:'Tuesday', checkType:'Check-in 1', std:'08:00 AM',actual:'08:15 AM',diff:15, status:'Late',   notes:'Traffic delay reported',   device:'Door B - Card',     location:'Head Office'},
  {id:'EMP002',name:'Jane Smith',    dept:'HR',     date:'2025-12-24',day:'Tuesday', checkType:'Check-out 1',std:'12:00 PM',actual:'12:00 PM',diff:0,  status:'On Time',notes:'',                        device:'Door B - Card',     location:'Head Office'},
  {id:'EMP002',name:'Jane Smith',    dept:'HR',     date:'2025-12-24',day:'Tuesday', checkType:'Check-in 2', std:'01:00 PM',actual:'01:12 PM',diff:12, status:'Late',   notes:'Extended lunch',           device:'Door B - Card',     location:'Head Office'},
  {id:'EMP002',name:'Jane Smith',    dept:'HR',     date:'2025-12-24',day:'Tuesday', checkType:'Check-out 2',std:'05:00 PM',actual:'05:00 PM',diff:0,  status:'On Time',notes:'',                        device:'Door B - Card',     location:'Head Office'},
  {id:'EMP003',name:'Mike Johnson',  dept:'Sales',  date:'2025-12-24',day:'Tuesday', checkType:'Check-in 1', std:'08:00 AM',actual:'07:50 AM',diff:-10,status:'Early',  notes:'Arrived early for meeting',device:'Door A - Biometric',location:'Head Office'},
  {id:'EMP003',name:'Mike Johnson',  dept:'Sales',  date:'2025-12-24',day:'Tuesday', checkType:'Check-out 1',std:'12:00 PM',actual:'12:00 PM',diff:0,  status:'On Time',notes:'',                        device:'Door A - Biometric',location:'Head Office'},
  {id:'EMP003',name:'Mike Johnson',  dept:'Sales',  date:'2025-12-24',day:'Tuesday', checkType:'Check-in 2', std:'01:00 PM',actual:'01:05 PM',diff:5,  status:'Late',   notes:'',                        device:'Door A - Biometric',location:'Head Office'},
  {id:'EMP003',name:'Mike Johnson',  dept:'Sales',  date:'2025-12-24',day:'Tuesday', checkType:'Check-out 2',std:'05:00 PM',actual:'05:00 PM',diff:0,  status:'On Time',notes:'',                        device:'Door A - Biometric',location:'Head Office'},
  {id:'EMP004',name:'Sarah Williams',dept:'Finance',date:'2025-12-23',day:'Monday',  checkType:'Check-in 1', std:'08:00 AM',actual:'--:--',   diff:null,status:'Missing',notes:'No record found',         device:'N/A',               location:'N/A'},
  {id:'EMP004',name:'Sarah Williams',dept:'Finance',date:'2025-12-23',day:'Monday',  checkType:'Check-out 1',std:'12:00 PM',actual:'--:--',   diff:null,status:'Missing',notes:'No record found',         device:'N/A',               location:'N/A'},
  {id:'EMP004',name:'Sarah Williams',dept:'Finance',date:'2025-12-24',day:'Tuesday', checkType:'Check-in 1', std:'08:00 AM',actual:'08:05 AM',diff:5,  status:'Late',   notes:'',                        device:'Door C - Card',     location:'Branch B'},
  {id:'EMP004',name:'Sarah Williams',dept:'Finance',date:'2025-12-24',day:'Tuesday', checkType:'Check-out 2',std:'05:00 PM',actual:'05:00 PM',diff:0,  status:'On Time',notes:'',                        device:'Door C - Card',     location:'Branch B'},
  {id:'EMP005',name:'David Brown',   dept:'IT',     date:'2025-12-23',day:'Monday',  checkType:'Check-in 1', std:'08:00 AM',actual:'07:45 AM',diff:-15,status:'Early',  notes:'Arrived early',            device:'Door A - Biometric',location:'Head Office'},
  {id:'EMP005',name:'David Brown',   dept:'IT',     date:'2025-12-23',day:'Monday',  checkType:'Check-out 1',std:'12:00 PM',actual:'12:00 PM',diff:0,  status:'On Time',notes:'',                        device:'Door A - Biometric',location:'Head Office'},
  {id:'EMP005',name:'David Brown',   dept:'IT',     date:'2025-12-23',day:'Monday',  checkType:'Check-in 2', std:'01:00 PM',actual:'01:00 PM',diff:0,  status:'On Time',notes:'',                        device:'Door A - Biometric',location:'Head Office'},
  {id:'EMP005',name:'David Brown',   dept:'IT',     date:'2025-12-23',day:'Monday',  checkType:'Check-out 2',std:'05:00 PM',actual:'06:10 PM',diff:70, status:'Late',   notes:'Overtime approved',        device:'Door A - Biometric',location:'Head Office'},
  {id:'EMP006',name:'Emily Davis',   dept:'Finance',date:'2025-12-22',day:'Sunday',  checkType:'Check-in 1', std:'08:00 AM',actual:'08:00 AM',diff:0,  status:'On Time',notes:'',                        device:'Door C - Card',     location:'Branch B'},
  {id:'EMP006',name:'Emily Davis',   dept:'Finance',date:'2025-12-22',day:'Sunday',  checkType:'Check-out 2',std:'05:00 PM',actual:'04:45 PM',diff:-15,status:'Early',  notes:'Left early with approval', device:'Door C - Card',     location:'Branch B'},
  {id:'EMP007',name:'Chris Lee',     dept:'Sales',  date:'2025-12-22',day:'Sunday',  checkType:'Check-in 1', std:'08:00 AM',actual:'08:30 AM',diff:30, status:'Late',   notes:'Car trouble',              device:'Door B - Card',     location:'Head Office'},
  {id:'EMP007',name:'Chris Lee',     dept:'Sales',  date:'2025-12-22',day:'Sunday',  checkType:'Check-out 2',std:'05:00 PM',actual:'05:30 PM',diff:30, status:'Late',   notes:'Made up missed time',      device:'Door B - Card',     location:'Head Office'},
  {id:'EMP008',name:'Anna Wilson',   dept:'HR',     date:'2025-12-21',day:'Saturday',checkType:'Check-in 1', std:'08:00 AM',actual:'07:55 AM',diff:-5, status:'Early',  notes:'',                        device:'Door A - Biometric',location:'Head Office'},
  {id:'EMP008',name:'Anna Wilson',   dept:'HR',     date:'2025-12-21',day:'Saturday',checkType:'Check-out 2',std:'05:00 PM',actual:'05:00 PM',diff:0,  status:'On Time',notes:'',                        device:'Door A - Biometric',location:'Head Office'},
  {id:'EMP009',name:'Tom Harris',    dept:'IT',     date:'2025-12-21',day:'Saturday',checkType:'Check-in 1', std:'08:00 AM',actual:'--:--',   diff:null,status:'Missing',notes:'No record found',         device:'N/A',               location:'N/A'},
  {id:'EMP009',name:'Tom Harris',    dept:'IT',     date:'2025-12-21',day:'Saturday',checkType:'Check-out 2',std:'05:00 PM',actual:'--:--',   diff:null,status:'Missing',notes:'No record found',         device:'N/A',               location:'N/A'},
  {id:'EMP010',name:'Lisa Chen',     dept:'Sales',  date:'2025-12-20',day:'Friday',  checkType:'Check-in 1', std:'08:00 AM',actual:'08:02 AM',diff:2,  status:'Late',   notes:'',                        device:'Door B - Card',     location:'Head Office'},
  {id:'EMP010',name:'Lisa Chen',     dept:'Sales',  date:'2025-12-20',day:'Friday',  checkType:'Check-out 2',std:'05:00 PM',actual:'05:00 PM',diff:0,  status:'On Time',notes:'',                        device:'Door B - Card',     location:'Head Office'},
];

const PER_PAGE = 10;
let filtered = [...RECORDS], sortCol = 'date', sortDir = -1, page = 1;

// ── Helpers ──
const checkBadgeClass = t => ({'Check-in 1':'bg-blue-100 text-blue-700','Check-out 1':'bg-purple-100 text-purple-700','Check-in 2':'bg-orange-100 text-orange-700','Check-out 2':'bg-red-100 text-red-700'}[t]||'bg-slate-100 text-slate-600');

function statusPill(s) {
  const map = {'On Time':'bg-green-100 text-green-700','Late':'bg-yellow-100 text-yellow-700','Missing':'bg-red-100 text-red-700','Early':'bg-blue-100 text-blue-700'};
  const icon = {'On Time':'✓','Late':'⚠','Missing':'✗','Early':'⚡'}[s]||'';
  return `<span class="inline-block px-3 py-0.5 rounded-full text-xs font-semibold ${map[s]||''}">${icon} ${s}</span>`;
}
function diffCell(d) {
  if (d===null) return `<span class="text-red-500 font-bold text-xs">N/A</span>`;
  if (d===0)    return `<span class="text-green-600 text-xs">0 min</span>`;
  if (d>0)      return `<span class="text-yellow-600 font-bold text-xs">+${d} min</span>`;
  return `<span class="text-blue-600 font-bold text-xs">${d} min</span>`;
}
function actualCell(a, s) {
  const col = {'On Time':'text-green-600','Late':'text-yellow-600','Missing':'text-red-500','Early':'text-blue-600'}[s]||'text-slate-700';
  return `<span class="font-semibold ${col}">${a}</span>`;
}

// ── Render Table ──
function render() {
  const start = (page-1)*PER_PAGE, end = start+PER_PAGE, rows = filtered.slice(start,end);
  const tbody = document.getElementById('tableBody');
  if (!rows.length) {
    tbody.innerHTML = `<tr><td colspan="10" class="text-center py-12 text-slate-400 text-sm">No records found.</td></tr>`;
  } else {
    tbody.innerHTML = rows.map((r,i) => `
      <tr class="border-b border-slate-100 hover:bg-slate-50 transition ${r.status==='Missing'?'bg-red-50':''} text-sm">
        <td class="px-4 py-3 font-semibold text-slate-800 whitespace-nowrap">${r.name}</td>
        <td class="px-4 py-3 text-center text-slate-500">${r.id}</td>
        <td class="px-4 py-3 text-center text-slate-600">${r.date}</td>
        <td class="px-4 py-3 text-center text-slate-400 text-xs">${r.day}</td>
        <td class="px-4 py-3 text-center"><span class="inline-block px-2 py-0.5 rounded text-xs font-semibold ${checkBadgeClass(r.checkType)}">${r.checkType}</span></td>
        <td class="px-4 py-3 text-center text-slate-500">${r.std}</td>
        <td class="px-4 py-3 text-center">${actualCell(r.actual,r.status)}</td>
        <td class="px-4 py-3 text-center">${diffCell(r.diff)}</td>
        <td class="px-4 py-3 text-center">${statusPill(r.status)}</td>
        <td class="px-4 py-3 text-center">
          ${r.status==='Missing'
            ? `<button onclick="openIssue(${start+i})" class="text-xs font-semibold text-red-500 hover:text-red-700 underline transition">Report Issue</button>`
            : `<button onclick="openDetail(${start+i})" class="text-xs font-semibold text-blue-500 hover:text-blue-700 underline transition">View Details</button>`}
        </td>
      </tr>`).join('');
  }
  document.getElementById('recordCount').textContent = `Showing ${start+1}–${Math.min(end,filtered.length)} of ${filtered.length} records`;
  renderPagination();
}

function renderPagination() {
  const total = Math.ceil(filtered.length/PER_PAGE)||1;
  document.getElementById('pageInfo').textContent = `Page ${page} of ${total}`;
  const base = 'text-xs font-semibold px-3 py-1.5 rounded-lg transition';
  const active = 'bg-blue-600 text-white';
  const normal = 'bg-slate-200 hover:bg-slate-300 text-slate-600';
  const dis    = 'bg-slate-100 text-slate-300 cursor-not-allowed';
  let html = `<button class="${base} ${page===1?dis:normal}" onclick="goPage(1)" ${page===1?'disabled':''}>« First</button>
              <button class="${base} ${page===1?dis:normal}" onclick="goPage(${page-1})" ${page===1?'disabled':''}>‹ Prev</button>`;
  for (let p2=1; p2<=total; p2++) {
    if (p2===1||p2===total||Math.abs(p2-page)<=1)
      html += `<button class="${base} ${p2===page?active:normal}" onclick="goPage(${p2})">${p2}</button>`;
    else if (Math.abs(p2-page)===2)
      html += `<span class="text-xs text-slate-400 px-1 self-center">…</span>`;
  }
  html += `<button class="${base} ${page===total?dis:normal}" onclick="goPage(${page+1})" ${page===total?'disabled':''}>Next ›</button>
           <button class="${base} ${page===total?dis:normal}" onclick="goPage(${total})" ${page===total?'disabled':''}>Last »</button>`;
  document.getElementById('pageBtns').innerHTML = html;
}
function goPage(p) { const t=Math.ceil(filtered.length/PER_PAGE)||1; page=Math.max(1,Math.min(p,t)); render(); }

// ── Filters ──
function applyFilters() {
  const from=document.getElementById('fromDate').value, to=document.getElementById('toDate').value;
  const q=document.getElementById('searchInput').value.toLowerCase();
  const dept=document.getElementById('deptFilter').value, 
  st=document.getElementById('statusFilter').value;
  filtered = RECORDS.filter(r=>
    (!from||r.date>=from)&&(!to||r.date<=to)&&
    (!q||r.name.toLowerCase().includes(q)||r.id.toLowerCase().includes(q))&&
    (!dept||r.dept===dept)&&(!st||r.status===st)
  );
  sortData(); page=1; render();
}
function resetFilters() {
  ['fromDate','toDate','searchInput','deptFilter','statusFilter'].forEach(id=>{
    const el=document.getElementById(id); el.value=id==='fromDate'?'2025-12-01':id==='toDate'?'2025-12-24':'';
  });
  applyFilters();
}

// ── Sort ──
function sortBy(col) {
  if(sortCol===col) sortDir*=-1; else { sortCol=col; sortDir=1; }
  ['name','id','date'].forEach(c=>document.getElementById('sort_'+c).textContent=c===col?(sortDir===1?'▲':'▼'):'');
  sortData(); render();
}
function sortData() {
  const key=sortCol==='name'?'name':sortCol==='id'?'id':'date';
  filtered.sort((a,b)=>a[key]<b[key]?-sortDir:a[key]>b[key]?sortDir:0);
}

// ── View Details Modal ──
function openDetail(idx) {
  const r = filtered[idx];
  document.getElementById('modalTitle').textContent = `${r.name} — ${r.checkType} (${r.date})`;
  const statusBg = {'On Time':'bg-green-100 text-green-700','Late':'bg-yellow-100 text-yellow-700','Early':'bg-blue-100 text-blue-700','Missing':'bg-red-100 text-red-700'}[r.status]||'';
  const diffStr = r.diff===null?'N/A':r.diff===0?'0 min':r.diff>0?`+${r.diff} min`:`${r.diff} min`;
  const dotColor = s=>({'On Time':'bg-green-500','Late':'bg-yellow-400','Missing':'bg-red-500','Early':'bg-blue-500'}[s]||'bg-slate-400');
  const dayRecs = RECORDS.filter(x=>x.id===r.id&&x.date===r.date).sort((a,b)=>a.checkType.localeCompare(b.checkType));

  document.getElementById('modalBody').innerHTML = `
    <div class="mb-5">
      <p class="text-xs font-bold uppercase tracking-widest text-slate-400 mb-3 border-b pb-1">Employee Info</p>
      <div class="grid grid-cols-2 gap-3">
        ${[['Full Name',r.name],['Employee ID',r.id],['Department',r.dept],['Location',r.location]].map(([l,v])=>`
        <div><p class="text-xs text-slate-400 font-semibold">${l}</p><p class="text-sm font-bold text-slate-700">${v}</p></div>`).join('')}
      </div>
    </div>
    <div class="mb-5">
      <p class="text-xs font-bold uppercase tracking-widest text-slate-400 mb-3 border-b pb-1">Check Record</p>
      <div class="grid grid-cols-2 gap-3">
        ${[['Date',`${r.date} (${r.day})`],['Check Type',`<span class="text-xs font-semibold px-2 py-0.5 rounded ${checkBadgeClass(r.checkType)}">${r.checkType}</span>`],['Standard Time',r.std],['Actual Time',r.actual],['Difference',diffStr],['Status',`<span class="text-xs font-semibold px-2 py-0.5 rounded-full ${statusBg}">${r.status}</span>`],['Device',r.device],['Notes',r.notes||'—']].map(([l,v])=>`
        <div><p class="text-xs text-slate-400 font-semibold">${l}</p><p class="text-sm font-semibold text-slate-700">${v}</p></div>`).join('')}
      </div>
    </div>
    <div>
      <p class="text-xs font-bold uppercase tracking-widest text-slate-400 mb-3 border-b pb-1">Day Timeline — ${r.date}</p>
      <div class="relative pl-5 border-l-2 border-slate-200 space-y-4">
        ${dayRecs.map(x=>`
        <div class="relative">
          <div class="absolute -left-[1.35rem] top-1 w-3 h-3 rounded-full border-2 border-white ${dotColor(x.status)}"></div>
          <p class="text-xs text-slate-400">${x.checkType}</p>
          <p class="text-sm font-bold text-slate-700">${x.actual} <span class="text-xs font-normal text-slate-400">(std: ${x.std})</span></p>
          <div class="mt-0.5">${statusPill(x.status)}</div>
        </div>`).join('')}
      </div>
    </div>`;
  document.getElementById('detailModal').classList.remove('hidden');
}

// ── Report Issue ──
function openIssue(idx) {
  const r = filtered[idx];
  document.getElementById('issueEmp').value   = `${r.name} (${r.id})`;
  document.getElementById('issueDate').value  = `${r.date} — ${r.day}`;
  document.getElementById('issueCheck').value = r.checkType;
  document.getElementById('issueNotes').value = '';
  document.getElementById('issueModal').classList.remove('hidden');
}
function submitIssue() {
  alert('✅ Report submitted!\nHR will review within 24 hours.');
  closeModal('issueModal');
}
function closeModal(id) { document.getElementById(id).classList.add('hidden'); }
document.querySelectorAll('#detailModal,#issueModal').forEach(m=>m.addEventListener('click',e=>{ if(e.target===m) m.classList.add('hidden'); }));

// ── Export ──
// ✅ REPLACE with this:
function exportExcel() {
  const cols=['Name','ID','Department','Date','Day','Check Type','Standard Time','Actual Time','Diff (min)','Status','Notes'];
  const rows=filtered.map(r=>[r.name,r.id,r.dept,r.date,r.day,r.checkType,r.std,r.actual,r.diff??'N/A',r.status,r.notes||'']);
  const wsData=[cols,...rows];
  const ws=XLSX.utils.aoa_to_sheet(wsData);
  ws['!cols']=[{wch:18},{wch:10},{wch:12},{wch:12},{wch:10},{wch:13},{wch:14},{wch:14},{wch:10},{wch:10},{wch:25}];
  const wb=XLSX.utils.book_new();
  XLSX.utils.book_append_sheet(wb,'Attendance',ws);
  XLSX.writeFile(wb,'attendance_report.xlsx');
}
function exportPDF() {
  const {jsPDF}=window.jspdf; const doc=new jsPDF({orientation:'landscape'});
  doc.setFontSize(14); doc.text('Detailed Attendance Report',14,16);
  doc.setFontSize(8);
  const headers=['Name','ID','Date','Check Type','Std','Actual','Diff','Status'];
  let y=26; doc.setFillColor(30,41,59); doc.setTextColor(255,255,255);
  doc.rect(10,y-5,277,8,'F'); headers.forEach((h,i)=>doc.text(h,12+i*35,y));
  doc.setTextColor(0,0,0); y+=8;
  filtered.forEach(r=>{ if(y>185){doc.addPage();y=20;} [r.name,r.id,r.date,r.checkType,r.std,r.actual,r.diff??'N/A',r.status].forEach((v,i)=>doc.text(String(v),12+i*35,y)); y+=7; });
  doc.save('attendance.pdf');
}

sortData(); render();
</script>
</body>
</html>