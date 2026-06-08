<div class="w-full max-w-7xl mx-auto space-y-6">
    <!-- Header Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-indigo-50 rounded-xl flex items-center justify-center">
                    <span class="iconify text-2xl text-indigo-600" data-icon="mdi:clock-check"></span>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-slate-900">Attendance Records</h1>
                    <p class="text-sm text-slate-500" id="totalCount">0 Records found</p>
                </div>
            </div>
            
            <div class="flex items-center gap-2 w-full md:w-auto">
                <button onclick="openQRModal()"
                    class="flex-1 md:flex-none flex items-center justify-center gap-2 px-4 py-2 text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl transition-all shadow-sm shadow-indigo-100">
                    <span class="iconify text-lg" data-icon="mdi:qrcode"></span>
                    Generate QR
                </button>
            </div>
        </div>

        <!-- Filters -->
        <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 mt-6">
            <div class="relative sm:col-span-4">
                <span class="iconify absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" data-icon="mdi:magnify"></span>
                <input type="text" id="searchInput" placeholder="Search Name, ID or Date..."
                    class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-100 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all outline-none text-sm">
        </div>
            <div class="sm:col-span-3">
                <select id="checkTypeFilter"
                    class="w-full px-4 py-2.5 bg-slate-50 border border-slate-100 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none text-sm transition-all cursor-pointer">
                    <option value="">All Check Types</option>
                    <option value="check-in">Check In Only</option>
                    <option value="check-out">Check Out Only</option>
                </select>
            </div>
            <div class="sm:col-span-3">
                <input type="date" id="dateFilter"
                    class="w-full px-4 py-2.5 bg-slate-50 border border-slate-100 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none text-sm transition-all">
            </div>
            <div class="sm:col-span-2">
                <button onclick="setTodayFilter()"
                    class="w-full h-full flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-bold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 border border-indigo-100 rounded-xl transition-all">
                    <span class="iconify text-lg" data-icon="mdi:calendar-today"></span>
                    Today
                </button>
            </div>
        </div>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gradient-to-b from-slate-900 via-slate-800 to-slate-900 shadow-lg text-white">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold">Employee</th>
                        <th class="px-4 py-3 text-left font-semibold">Date</th>
                        <th class="px-4 py-3 text-left font-semibold">Time</th>
                        <th class="px-4 py-3 text-left font-semibold">Type</th>
                        <th class="px-4 py-3 text-left font-semibold">Status</th>
                        <th class="px-4 py-3 text-left font-semibold">Log</th>
                    </tr>
                </thead>
                <tbody id="attendanceTableBody" class="divide-y divide-slate-100">
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                            <div class="flex flex-col items-center justify-center gap-2">
                                <span class="iconify text-3xl animate-spin" data-icon="mdi:loading"></span>
                                <p class="text-xs font-medium">Loading records...</p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div id="paginationContainer" class="px-6 py-4 bg-slate-50/50 border-t border-slate-100 flex justify-center gap-2"></div>
    </div>

    <!-- QR Modal Overlay -->
    <div id="qrModal" class="fixed inset-0 z-[9999] hidden items-start justify-center overflow-y-auto bg-slate-900/40 backdrop-blur-sm px-4 py-6 md:items-center">
        <div class="bg-white rounded-3xl p-8 w-full max-w-sm text-center shadow-2xl scale-90 transition-transform duration-300 transform max-h-[calc(100vh-3rem)] overflow-y-auto" id="qrModalContent">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-lg font-bold text-slate-900">Attendance QR</h3>
                <button onclick="closeQRModal()" class="w-8 h-8 flex items-center justify-center rounded-full bg-slate-50 text-slate-500 hover:text-slate-900 transition-colors">✕</button>
            </div>

            <div class="bg-indigo-50 p-6 rounded-2xl inline-block mb-4 border border-indigo-100">
                <div id="qrcode" class="rounded-lg overflow-hidden border-4 border-white shadow-sm"></div>
            </div>

            <p class="text-xs text-slate-500 mb-8" id="qrUrlLabel">Scan to record your attendance</p>

            <div class="grid grid-cols-2 gap-3">
                <button onclick="downloadQR()" class="flex items-center justify-center gap-2 py-3 bg-indigo-600 text-white rounded-xl text-sm font-bold hover:bg-indigo-700 transition-all">
                    <span class="iconify" data-icon="mdi:download"></span> Download
                </button>
                <button onclick="printQR()" class="flex items-center justify-center gap-2 py-3 border border-slate-200 text-slate-600 rounded-xl text-sm font-bold hover:bg-slate-50 transition-all">
                    <span class="iconify" data-icon="mdi:printer"></span> Print
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    #qrcode canvas, #qrcode img {
        width: 200px !important;
        height: 200px !important;
        margin: 0 auto;
    }
</style>

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
    let currentPage = 1;
    let totalPages  = 1;
    const perPage   = 12;
    const qrDarkColor = '#0f172a';
    const qrLightColor = '#ffffff';
    const qrContent = 'DOORSTEP_ATTENDANCE';
    const qrcode = document.getElementById('qrcode');

    // Init QR
    new QRCode(qrcode, {
        text: qrContent,
        width: 256,
        height: 256,
        colorDark: qrDarkColor,
        colorLight: qrLightColor,
        correctLevel: QRCode.CorrectLevel.H
    });

    function openQRModal() {
        const modal = document.getElementById('qrModal');
        const content = document.getElementById('qrModalContent');
        if (modal.parentElement !== document.body) {
            document.body.appendChild(modal);
        }
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        setTimeout(() => content.classList.replace('scale-90', 'scale-100'), 10);
    }

    function closeQRModal() {
        const modal = document.getElementById('qrModal');
        const content = document.getElementById('qrModalContent');
        content.classList.replace('scale-100', 'scale-90');
        setTimeout(() => {
            modal.classList.remove('flex');
            modal.classList.add('hidden');
        }, 200);
    }

    function generateQRCard(size, callback) {
        const padding = Math.round(size * 0.08);
        const qrSize  = Math.round(size * 0.65);
        const tempDiv = document.createElement('div');
        tempDiv.style.position = 'absolute'; tempDiv.style.left = '-9999px';
        document.body.appendChild(tempDiv);

        new QRCode(tempDiv, { text: qrContent, width: qrSize, height: qrSize, colorDark: qrDarkColor, colorLight: qrLightColor, correctLevel: QRCode.CorrectLevel.H });

        setTimeout(() => {
            const qrCanvas = tempDiv.querySelector('canvas');
            if (!qrCanvas) { document.body.removeChild(tempDiv); return; }
            const cardW = size; const cardH = Math.round(size * 1.35);
            const canvas  = document.createElement('canvas'); canvas.width  = cardW; canvas.height = cardH;
            const ctx     = canvas.getContext('2d');
            ctx.fillStyle = '#f8fafc'; ctx.fillRect(0, 0, cardW, cardH);
            ctx.fillStyle = '#ffffff'; roundRect(ctx, padding, padding, cardW - padding * 2, cardH - padding * 2, Math.round(size * 0.05)); ctx.fill();
            const circleX = cardW / 2; const circleY = padding * 2.5; const circleR = Math.round(size * 0.09);
            ctx.fillStyle = '#4f46e5'; ctx.beginPath(); ctx.arc(circleX, circleY, circleR, 0, Math.PI * 2); ctx.fill();
            ctx.strokeStyle = '#ffffff'; ctx.lineWidth = Math.round(size * 0.018); ctx.lineCap = 'round'; ctx.lineJoin = 'round';
            ctx.beginPath(); ctx.moveTo(circleX - circleR * 0.4, circleY); ctx.lineTo(circleX - circleR * 0.05, circleY + circleR * 0.38); ctx.lineTo(circleX + circleR * 0.45, circleY - circleR * 0.35); ctx.stroke();
            const titleY = circleY + circleR + Math.round(size * 0.09); const fontSize = Math.round(size * 0.07);
            ctx.fillStyle = '#0f172a'; ctx.font = `bold ${fontSize}px sans-serif`; ctx.textAlign = 'center'; ctx.fillText('ATTENDANCE QR', cardW / 2, titleY);
            const qrX = (cardW - qrSize) / 2; const qrY = titleY + Math.round(size * 0.06); ctx.drawImage(qrCanvas, qrX, qrY, qrSize, qrSize);
            const subY = qrY + qrSize + Math.round(size * 0.07); const subSize = Math.round(size * 0.055);
            ctx.fillStyle = '#64748b'; ctx.font = `${subSize}px sans-serif`; ctx.fillText('Scan to record attendance', cardW / 2, subY);
            document.body.removeChild(tempDiv); callback(canvas);
        }, 200);
    }

    function roundRect(ctx, x, y, w, h, r) {
        ctx.beginPath(); ctx.moveTo(x + r, y); ctx.lineTo(x + w - r, y); ctx.quadraticCurveTo(x + w, y, x + w, y + r);
        ctx.lineTo(x + w, y + h - r); ctx.quadraticCurveTo(x + w, y + h, x + w - r, y + h); ctx.lineTo(x + r, y + h);
        ctx.quadraticCurveTo(x, y + h, x, y + h - r); ctx.lineTo(x, y + r); ctx.quadraticCurveTo(x, y, x + r, y); ctx.closePath();
    }

    function downloadQR() { generateQRCard(800, c => { const a = document.createElement('a'); a.download = 'attendance-qr.png'; a.href = c.toDataURL(); a.click(); }); }
    function printQR() { generateQRCard(800, c => { const w = window.open('', '_blank'); w.document.write(`<body style="margin:0;display:flex;justify-content:center;align-items:center;min-height:100vh;background:#f3f4f6;"><img src="${c.toDataURL()}" style="width:400px;"></body>`); w.print(); }); }

    function setTodayFilter() {
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('dateFilter').value = today;
        loadAttendance(1);
    }

    function loadAttendance(page = 1) {
        const searchInput = document.getElementById("searchInput").value;
        const checkType   = document.getElementById("checkTypeFilter").value;
        const date        = document.getElementById("dateFilter").value;

        const params = new URLSearchParams({
            "paging_options[page]": page,
            "paging_options[per_page]": perPage,
            "filters[status_id]": 1,
            "filters[date]": date,
            "filters[search]": searchInput,
            "filters[check_type]": checkType
        });

        fetch("/api/attendance/show?" + params.toString())
            .then(res => res.json())
            .then(result => {
                if (result.success && result.data) {
                    const pagination = result.pagination || {};
                    currentPage = pagination.page || page;
                    totalPages = pagination.total_pages || 1;

                    renderTable(result.data.attendance_records);
                    document.getElementById("totalCount").textContent = `${pagination.total || 0} Records found`;
                    renderPagination({
                        currentPage,
                        totalPages,
                        totalRecords: pagination.total || 0
                    });
                } else {
                    throw new Error("No data");
                }
            })
            .catch(err => {
                document.getElementById("attendanceTableBody").innerHTML = '<tr><td colspan="6" class="px-6 py-12 text-center text-rose-500 font-medium">Failed to load records</td></tr>';
                window.Toast?.error("Fetch Error", "Could not load attendance data.");
            });
    }

    function renderTable(records) {
        const tbody = document.getElementById("attendanceTableBody");
        if (!records.length) {
            tbody.innerHTML = '<tr><td colspan="6" class="px-6 py-12 text-center text-slate-400">No records matching your filters</td></tr>';
            return;
        }

        tbody.innerHTML = records.map(rec => {
            const checkTypeName = String(rec.check_type_name || '').toLowerCase();
            const isLeave = checkTypeName === 'leave' || rec.check_time === 'Leave';
            const isCheckIn = !isLeave && checkTypeName.includes('in');
            
            let typeClass = '';
            let typeLabel = '';
            
            if (isLeave) {
                typeClass = 'bg-indigo-100 text-indigo-700 border border-indigo-200';
                typeLabel = 'Leave';
            } else if (isCheckIn) {
                typeClass = 'bg-emerald-50 text-emerald-600';
                typeLabel = 'In';
            } else {
                typeClass = 'bg-amber-50 text-amber-600';
                typeLabel = 'Out';
            }
            
            const timeDisplay = isLeave ? '<span class="flex items-center gap-1"><span class="iconify" data-icon="mdi:calendar-clock"></span>Full Day</span>' : rec.check_time;
            
            return `
            <tr class="${isLeave ? 'bg-indigo-50/30' : ''} hover:bg-slate-50 transition-colors group">
                <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg ${isLeave ? 'bg-indigo-100 text-indigo-600' : 'bg-indigo-50 text-indigo-600'} flex items-center justify-center text-xs font-bold">
                            ${(rec.full_name || rec.emp_code || '#').charAt(0)}
                        </div>
                        <div class="flex flex-col">
                            <span class="font-bold text-slate-900 group-hover:text-indigo-600 transition-colors">
                                ${rec.full_name || 'N/A'}
                            </span>
                            <span class="text-[10px] text-slate-400 font-medium">
                                ${rec.emp_code ? '#' + rec.emp_code : ''}
                            </span>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4 text-slate-500">${new Date(rec.date).toLocaleDateString(undefined, {month:'short', day:'numeric', year:'numeric'})}</td>
                <td class="px-6 py-4 font-mono font-bold ${isLeave ? 'text-indigo-600' : 'text-indigo-600'}">${timeDisplay}</td>
                <td class="px-6 py-4">
                    <span class="${typeClass} px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider">
                        ${typeLabel}
                    </span>
                </td>
                <td class="px-6 py-4">
                    <div class="flex items-center gap-1.5 text-xs ${rec.status_id == 1 ? 'text-emerald-600' : 'text-slate-400'}">
                        <span class="w-1.5 h-1.5 rounded-full ${rec.status_id == 1 ? 'bg-emerald-500' : 'bg-slate-400'}"></span>
                        ${rec.status_id == 1 ? 'Active' : 'Archived'}
                    </div>
                </td>
                <td class="px-6 py-4 text-[10px] text-slate-400 uppercase">${new Date(rec.created_at).toLocaleDateString()}</td>
            </tr>
        `;}).join('');
    }

    function getVisiblePages(current, total, maxButtons = 5) {
        if (total <= maxButtons) {
            return Array.from({ length: total }, (_, i) => i + 1);
        }

        const half = Math.floor(maxButtons / 2);
        let start = Math.max(1, current - half);
        let end = start + maxButtons - 1;

        if (end > total) {
            end = total;
            start = Math.max(1, end - maxButtons + 1);
        }

        return Array.from({ length: end - start + 1 }, (_, i) => start + i);
    }

    function paginationButton(page, currentPageNumber, label = null, disabled = false, extraClass = '') {
        const isActive = page === currentPageNumber;
        const isDisabled = disabled || isActive;
        const text = label ?? page;
        const baseClass = 'inline-flex h-9 min-w-9 items-center justify-center rounded-lg border px-3 text-sm font-bold transition-all';
        const activeClass = 'bg-indigo-600 text-white border-indigo-600 shadow-sm';
        const inactiveClass = 'bg-white text-slate-600 border-slate-200 hover:bg-indigo-50 hover:border-indigo-200 hover:text-indigo-700';
        const disabledClass = 'opacity-40 cursor-not-allowed';

        return `
            <button
                class="${baseClass} ${isActive ? activeClass : inactiveClass} ${isDisabled ? disabledClass : ''} ${extraClass}"
                data-page="${page}"
                ${isDisabled ? 'disabled' : ''}
            >${text}</button>
        `;
    }

    function renderPagination({ currentPage, totalPages, totalRecords }) {
        const container = document.getElementById("paginationContainer");
        container.innerHTML = '';

        const safeCurrentPage = Math.min(Math.max(currentPage, 1), Math.max(totalPages, 1));
        const showingFrom = totalRecords > 0 ? ((safeCurrentPage - 1) * perPage) + 1 : 0;
        const showingTo = totalRecords > 0 ? Math.min(safeCurrentPage * perPage, totalRecords) : 0;

        if (totalRecords === 0) {
            container.innerHTML = `
                <div class="w-full flex items-center justify-between gap-4 text-sm text-slate-500">
                    <span>Showing 0 of 0 records</span>
                </div>
            `;
            return;
        }

        if (totalPages <= 1) {
            container.innerHTML = `
                <div class="w-full flex items-center justify-between gap-4 text-sm text-slate-500">
                    <span>Showing ${showingFrom} to ${showingTo} of ${totalRecords} records</span>
                </div>
            `;
            return;
        }

        const visiblePages = getVisiblePages(safeCurrentPage, totalPages, 2);
        let pageButtons = '';

        if (visiblePages[0] > 1) {
            pageButtons += paginationButton(1, safeCurrentPage);
            if (visiblePages[0] > 2) {
                pageButtons += '<span class="px-1 text-slate-400">...</span>';
            }
        }

        visiblePages.forEach(page => {
            pageButtons += paginationButton(page, safeCurrentPage);
        });

        const lastVisiblePage = visiblePages[visiblePages.length - 1];
        if (lastVisiblePage < totalPages) {
            if (lastVisiblePage < totalPages - 1) {
                pageButtons += '<span class="px-1 text-slate-400">...</span>';
            }
            pageButtons += paginationButton(totalPages, safeCurrentPage);
        }

        container.innerHTML = `
            <div class="w-full flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="text-sm text-slate-500">
                    Showing <span class="font-semibold text-slate-700">${showingFrom}</span>
                    to <span class="font-semibold text-slate-700">${showingTo}</span>
                    of <span class="font-semibold text-slate-700">${totalRecords}</span> records
                </div>

                <div class="flex flex-wrap items-center justify-center gap-2">
                    ${paginationButton(safeCurrentPage - 1, safeCurrentPage, 'Prev', safeCurrentPage === 1, 'min-w-16')}
                    ${pageButtons}
                    ${paginationButton(safeCurrentPage + 1, safeCurrentPage, 'Next', safeCurrentPage === totalPages, 'min-w-16')}
                </div>
            </div>
        `;

        container.querySelectorAll('button[data-page]').forEach(btn => {
            btn.addEventListener('click', () => {
                const nextPage = parseInt(btn.dataset.page, 10);
                if (!Number.isNaN(nextPage) && nextPage >= 1 && nextPage <= totalPages && nextPage !== safeCurrentPage) {
                    loadAttendance(nextPage);
                }
            });
        });
    }

    document.getElementById("searchInput").addEventListener("input", () => loadAttendance(1));
    document.getElementById("checkTypeFilter").addEventListener("change", () => loadAttendance(1));
    document.getElementById("dateFilter").addEventListener("change", () => loadAttendance(1));
    
    // Set default filter to today
    document.getElementById('dateFilter').value = new Date().toISOString().split('T')[0];
    loadAttendance();
</script>
