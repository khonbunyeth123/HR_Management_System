<?php
// resources/views/report/report_detail.php
?>
<body class="bg-[#FBFCFD] min-h-screen p-4 md:p-10 transition-colors duration-200 font-sans">
<div class="max-w-7xl mx-auto">
  
  <!-- Header Section -->
  <div class="flex flex-col md:flex-row justify-between items-end mb-1 gap-6 border-b border-slate-100 pb-8">
    <div>
      <!-- <div class="flex items-center gap-2 mb-2">
        <span class="w-2 h-2 rounded-full bg-indigo-500 animate-pulse"></span>
        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em]">Live Analysis</span>
      </div> -->
      <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Detailed Attendance</h1>
      <p class="text-sm text-slate-500 mt-2 font-medium">Detailed audit of daily shift performance and employee punctuality.</p>
    </div>
    <div class="flex gap-3 w-full md:w-auto">
      <button onclick="exportExcel()" class="flex-1 md:flex-none flex items-center justify-center gap-2 bg-white border border-slate-200 text-slate-600 text-[11px] font-bold px-6 py-3 rounded-2xl hover:bg-slate-50 transition shadow-sm group">
        <span class="iconify text-emerald-500 text-lg group-hover:scale-110 transition-transform" data-icon="mdi:file-excel"></span> EXCEL
      </button>
      <button onclick="exportPDF()" class="flex-1 md:flex-none flex items-center justify-center gap-2 bg-white border border-slate-200 text-slate-600 text-[11px] font-bold px-6 py-3 rounded-2xl hover:bg-slate-50 transition shadow-sm group">
        <span class="iconify text-rose-500 text-lg group-hover:scale-110 transition-transform" data-icon="mdi:file-pdf-box"></span> PDF
      </button>
    </div>
  </div>

  <!-- Summary Metric Cards -->
  <div class="grid grid-cols-2 lg:grid-cols-4 gap-8 mb-6">
    <div class="bg-white p-6 rounded-[2rem] border border-slate-100 shadow-[0_8px_30px_rgb(0,0,0,0.02)] hover:border-indigo-100 transition-colors group">
      <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 group-hover:text-indigo-400 transition-colors">Total Staff</p>
      <div class="text-4xl font-bold text-slate-900" id="statTotalEmployees">0</div>
    </div>
    <div class="bg-white p-6 rounded-[2rem] border border-slate-100 shadow-[0_8px_30px_rgb(0,0,0,0.02)] hover:border-teal-100 transition-colors group">
      <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 group-hover:text-teal-500 transition-colors">Present</p>
      <div class="text-4xl font-bold text-slate-900" id="statTotalPresent">0</div>
    </div>
    <div class="bg-white p-6 rounded-[2rem] border border-slate-100 shadow-[0_8px_30px_rgb(0,0,0,0.02)] hover:border-rose-100 transition-colors group">
      <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 group-hover:text-rose-500 transition-colors">Absent</p>
      <div class="text-4xl font-bold text-slate-900" id="statTotalAbsent">0</div>
    </div>
    <div class="bg-white p-6 rounded-[2rem] border border-slate-100 shadow-[0_8px_30px_rgb(0,0,0,0.02)] hover:border-orange-100 transition-colors group">
      <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 group-hover:text-orange-500 transition-colors">Late Arrivals</p>
      <div class="text-4xl font-bold text-slate-900" id="statTotalLate">0</div>
    </div>
  </div>

  <!-- Filter Bar -->
  <div class="bg-white border border-slate-100 p-2 rounded-[2rem] shadow-sm mb-6 flex flex-wrap items-center gap-2">
    <div class="flex items-center px-6 py-2">
      <span class="text-[10px] font-black text-slate-300 uppercase tracking-widest mr-4">Period</span>
      <input type="date" id="fromDate" oninput="fetchData()" class="bg-transparent text-sm font-bold text-slate-700 outline-none">
      <span class="mx-3 text-slate-200">/</span>
      <input type="date" id="toDate" oninput="fetchData()" class="bg-transparent text-sm font-bold text-slate-700 outline-none">
    </div>
    
    <div class="h-8 w-px bg-slate-100 mx-2"></div>

    <div class="flex items-center px-6 py-2">
      <span class="text-[10px] font-black text-slate-300 uppercase tracking-widest mr-4">Department</span>
      <select id="deptFilter" onchange="fetchData()" class="text-sm font-bold text-slate-700 outline-none border-none cursor-pointer bg-transparent">
        <option value="">All Departments</option>
      </select>
    </div>

    <div class="relative flex-1 min-w-[280px] ml-auto">
      <span class="iconify absolute left-5 top-1/2 -translate-y-1/2 text-slate-300 text-lg" data-icon="mdi:magnify"></span>
      <input type="text" id="searchInput" placeholder="Search name or employee ID..." oninput="render()" 
             class="w-full bg-slate-50 text-sm font-semibold text-slate-600 pl-14 pr-6 py-4 rounded-3xl outline-none border border-transparent focus:bg-white focus:border-indigo-100 focus:ring-4 focus:ring-indigo-500/5 transition-all">
    </div>

    <div id="loadingIndicator" class="hidden px-6">
      <span class="iconify animate-spin text-indigo-400 text-xl" data-icon="mdi:loading"></span>
    </div>
  </div>

  <!-- START: Employee Accordion -->
  <div id="accordionContainer" class="space-y-6">
    <!-- Content dynamically rendered -->
  </div>
  <!-- END: Employee Accordion -->

  <!-- Empty State -->
  <div id="emptyState" class="hidden py-40 text-center bg-white rounded-[3rem] border border-dashed border-slate-200">
    <div class="w-24 h-24 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-2 shadow-inner">
      <span class="iconify text-5xl text-slate-200" data-icon="mdi:database-search-outline"></span>
    </div>
    <h3 class="text-xl font-bold text-slate-900">No records found</h3>
    <p class="text-sm text-slate-400 mt-2 font-medium">Try adjusting your filters or search keywords.</p>
  </div>

</div>

<!-- Scripts -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>

<script>
// ── Core Data ──
let RAW_DATA = [];
let GROUPED_DATA = [];

// ── App Lifecycle ──
(function init() {
  const today = new Date();
  const firstDay = new Date(today.getFullYear(), today.getMonth(), 1).toISOString().split('T')[0];
  const lastDay  = today.toISOString().split('T')[0];
  document.getElementById('fromDate').value = firstDay;
  document.getElementById('toDate').value   = lastDay;
  
  fetchDepartments();
  fetchData();
})();

async function fetchDepartments() {
  try {
    const res = await fetch('/api/employees/departments');
    const json = await res.json();
    if (json.success) {
      const select = document.getElementById('deptFilter');
      json.data.forEach(dept => {
        const opt = document.createElement('option');
        opt.value = dept; opt.textContent = dept;
        select.appendChild(opt);
      });
    }
  } catch (err) { console.error('Dept Fetch Error:', err); }
}

async function fetchData() {
  const from = document.getElementById('fromDate').value;
  const to = document.getElementById('toDate').value;
  const dept = document.getElementById('deptFilter').value;
  
  if (!from || !to) return;
  document.getElementById('loadingIndicator').classList.remove('hidden');

  try {
    const params = new URLSearchParams({ from, to });
    if (dept) params.append('department', dept);
    
    const res = await fetch(`/api/report/detailed?${params.toString()}`);
    const json = await res.json();

    if (json.success) {
      RAW_DATA = json.data;
      processAndRender();
    }
  } catch (err) { console.error('Data Fetch Error:', err); } 
  finally { document.getElementById('loadingIndicator').classList.add('hidden'); }
}

function processAndRender() {
  if (RAW_DATA.length > 0 && RAW_DATA[0].days) {
    GROUPED_DATA = RAW_DATA.map(emp => ({
      id: emp.employee_id,
      name: emp.name,
      department: emp.department,
      days: emp.days,
    })).sort((a, b) => a.name.localeCompare(b.name));
  }

  updateStats();
  render();
}

function updateStats() {
  let present = 0, late = 0, absent = 0;
  GROUPED_DATA.forEach(emp => {
    Object.values(emp.days).forEach(d => {
      if (d.status === 'Absent') absent++;
      else if (d.status === 'Late') { present++; late++; }
      else if (d.status === 'Present' || d.status === 'Leave' || d.status === 'Public Holiday' || d.status === 'Day Off' || d.status === 'Missing Checkout') present++;
    });
  });
  document.getElementById('statTotalEmployees').textContent = GROUPED_DATA.length;
  document.getElementById('statTotalPresent').textContent = present;
  document.getElementById('statTotalAbsent').textContent = absent;
  document.getElementById('statTotalLate').textContent = late;
}

function render() {
  const search = document.getElementById('searchInput').value.toLowerCase();
  const container = document.getElementById('accordionContainer');
  const empty = document.getElementById('emptyState');
  
  const filtered = GROUPED_DATA.filter(emp => 
    emp.name.toLowerCase().includes(search) || 
    String(emp.id).includes(search)
  );

  if (filtered.length === 0) {
    container.innerHTML = ''; empty.classList.remove('hidden'); return;
  }

  empty.classList.add('hidden');
  container.innerHTML = filtered.map(emp => `
    <div class="bg-white rounded-[2.5rem] border border-slate-100 shadow-[0_2px_15px_-3px_rgba(0,0,0,0.03)] overflow-hidden transition-all duration-300">
      <button onclick="toggleAccordion('${emp.id}')" class="w-full flex items-center justify-between p-2 hover:bg-slate-50/30 transition-colors group">
        <div class="flex items-center gap-6">
          <div class="w-14 h-14 rounded-[1.25rem] bg-slate-50 flex items-center justify-center text-slate-400 font-bold text-xl border border-slate-100 shadow-inner">
            ${emp.name.charAt(0)}
          </div>
          <div class="text-left">
            <h4 class="font-bold text-slate-800 text-xl tracking-tight">${emp.name}</h4>
            <p class="text-[10px] font-black text-slate-300 uppercase tracking-widest mt-1">${emp.department} • ID: ${emp.id}</p>
          </div>
        </div>
        <div class="flex items-center gap-6">
          <span class="px-5 py-2 rounded-2xl bg-slate-50 text-[10px] font-black text-slate-400 uppercase tracking-widest border border-slate-100">
            ${Object.keys(emp.days).length} Records
          </span>
          <div id="icon-${emp.id}" class="w-10 h-10 rounded-full flex items-center justify-center bg-slate-50 text-slate-300 group-hover:text-indigo-400 transition-all duration-300 border border-slate-100">
            <span class="iconify text-2xl" data-icon="mdi:chevron-down"></span>
          </div>
        </div>
      </button>

      <div id="content-${emp.id}" class="hidden border-t border-slate-50 p-4 bg-slate-50/[0.2]">
        <div class="overflow-x-auto rounded-3xl border border-slate-100 bg-white shadow-inner">
          <table class="w-full text-left text-xs min-w-[800px]">
            <thead>
              <tr class="text-slate-300 font-bold uppercase tracking-widest bg-black border-b border-slate-50">
                <th class="px-4 py-4">Date</th>
                <th class="px-4 py-4 text-center">In 1</th>
                <th class="px-4 py-4 text-center">Out 1</th>
                <th class="px-4 py-4 text-center">In 2</th>
                <th class="px-4 py-4 text-center">Out 2</th>
                <th class="px-4 py-4 text-center">Status</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
              ${Object.values(emp.days).sort((a,b) => b.date.localeCompare(a.date)).map(d => `
                <tr class="hover:bg-slate-50/50 transition-colors">
                  <td class="px-8 py-2">
                    <div class="font-bold text-slate-700 text-sm">${d.date}</div>
                    <div class="text-[10px] text-slate-300 font-bold uppercase tracking-tight">${d.day}</div>
                  </td>
                  <td class="px-8 py-5 text-center">${renderPunchCell(d.c1, d.c1Note)}</td>
                  <td class="px-8 py-5 text-center">${renderPunchCell(d.o1, d.o1Note)}</td>
                  <td class="px-8 py-5 text-center">${renderPunchCell(d.c2, d.c2Note)}</td>
                  <td class="px-8 py-5 text-center">${renderPunchCell(d.o2, d.o2Note)}</td>
                  <td class="px-8 py-5 text-center">${getStatusBadge(d)}</td>
                </tr>
              `).join('')}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  `).join('');
}

// ── UI Helpers ──
function toggleAccordion(id) {
  const content = document.getElementById(`content-${id}`);
  const icon = document.getElementById(`icon-${id}`);
  content.classList.toggle('hidden');
  icon.classList.toggle('rotate-180');
}

function getStatusBadge(d) {
  const base = "px-4 py-1.5 rounded-xl font-bold text-[9px] uppercase tracking-wider shadow-sm border";
  const map = {
    'Present': 'bg-emerald-50 text-emerald-700 border-emerald-100',
    'Late': 'bg-orange-50 text-orange-600 border-orange-100',
    'Absent': 'bg-rose-50 text-rose-600 border-rose-100',
    'Leave': 'bg-sky-50 text-sky-700 border-sky-100',
    'Public Holiday': 'bg-violet-50 text-violet-700 border-violet-100',
    'Day Off': 'bg-slate-100 text-slate-600 border-slate-200',
    'Missing Checkout': 'bg-amber-50 text-amber-700 border-amber-100',
  };
  const label = d.status || 'Present';
  return `<span class="${base} ${map[label] || map.Present}">${label}</span>`;
}

function renderPunchCell(timeValue, note) {
  const safeTime = timeValue || '--:--';
  const tone = punchToneClass(note);

  return `
    <div class="flex flex-col items-center gap-1">
      <span class="font-mono font-bold text-sm ${tone.text}">${safeTime}</span>
      <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-black uppercase tracking-widest ${tone.badge}">
        ${note || 'No record'}
      </span>
    </div>
  `;
}

function punchToneClass(note) {
  const normalized = String(note || '').toLowerCase();

  if (normalized.includes('late')) {
    return { text: 'text-orange-600', badge: 'bg-orange-100 text-orange-700' };
  }

  if (normalized.includes('early')) {
    return { text: 'text-amber-600', badge: 'bg-amber-100 text-amber-700' };
  }

  if (normalized.includes('on time')) {
    return { text: 'text-emerald-600', badge: 'bg-emerald-100 text-emerald-700' };
  }

  if (normalized.includes('overtime')) {
    return { text: 'text-amber-600', badge: 'bg-amber-100 text-amber-700' };
  }

  return { text: 'text-slate-300', badge: 'bg-slate-100 text-slate-500' };
}

function getDateRange(start, end) {
  const dates = [];
  let curr = new Date(start);
  const stop = new Date(end);
  while (curr <= stop) { dates.push(curr.toISOString().split('T')[0]); curr.setDate(curr.getDate() + 1); }
  return dates;
}

// ── Export Logic ──
function exportExcel() {
  const data = [];
  GROUPED_DATA.forEach(emp => {
    Object.values(emp.days).forEach(d => {
      data.push({ 'ID': emp.id, 'Name': emp.name, 'Date': d.date, 'Day': d.day, 'In 1': d.c1, 'Out 1': d.o1, 'In 2': d.c2, 'Out 2': d.o2, 'Status': d.status });
    });
  });
  const ws = XLSX.utils.json_to_sheet(data);
  const wb = XLSX.utils.book_new();
  XLSX.utils.book_append_sheet(wb, 'Report', ws);
  XLSX.writeFile(wb, `Attendance_Detailed_${new Date().toISOString().split('T')[0]}.xlsx`);
}

function exportPDF() {
  const { jsPDF } = window.jspdf;
  const doc = new jsPDF('l', 'mm', 'a4');
  doc.setFontSize(18); doc.setTextColor(15, 23, 42);
  doc.text('Attendance Audit Report', 14, 20);
  const tableData = [];
  GROUPED_DATA.forEach(emp => {
    Object.values(emp.days).forEach(d => {
      tableData.push([emp.name, d.date, d.day, d.c1, d.o1, d.c2, d.o2, d.status]);
    });
  });
  doc.autoTable({
    startY: 30,
    head: [['Name', 'Date', 'Day', 'In 1', 'Out 1', 'In 2', 'Out 2', 'Status']],
    body: tableData,
    theme: 'plain',
    headStyles: { fontStyle: 'bold', textColor: [148, 163, 184] },
    styles: { fontSize: 7 }
  });
  doc.save(`Attendance_Audit.pdf`);
}
</script>
</body>
