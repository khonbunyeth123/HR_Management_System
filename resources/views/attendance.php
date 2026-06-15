<div class="w-full h-full"> 
    <div class="p-2 space-y-2">
        <!-- Header & Filters -->
        <?php 
            $title = 'Attendance';
            $icon = 'mdi:clock-check text-indigo-500';
            ob_start();
        ?>
            <div class="flex items-center gap-2">
                <span class="text-[10px] font-black normal-case tracking-wider bg-indigo-50 text-indigo-600 px-2 py-0.5 rounded-md" id="totalCount">0 Records</span>
                <?php 
                    $label = 'QR'; $type = 'primary'; $size = 'xs'; $icon = 'mdi:qrcode'; $attr = 'onclick="openQRModal()"'; $id = null;
                    include 'component/button.php'; 
                    $label = null; $attr = null; // Important: Reset
                ?>
            </div>
        <?php 
            $headerRight = ob_get_clean();
            ob_start();
        ?>
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-2">
                <div class="sm:col-span-2">
                    <?php 
                        $id = 'searchInput'; $placeholder = 'Search employee...'; $icon = 'mdi:magnify'; $label = null;
                        include 'component/input.php'; 
                        $id = null; $icon = null; // Reset
                    ?>
                </div>
                <div>
                    <?php 
                        $id = 'checkTypeFilter'; $placeholder = 'All Types';
                        $options = ['check-in' => 'Check In', 'check-out' => 'Check Out'];
                        include 'component/select.php'; 
                        $id = null; $options = []; // Reset
                    ?>
                </div>
                <div class="flex gap-1.5">
                    <div class="flex-1">
                        <?php 
                            $id = 'dateFilter'; $type = 'date';
                            include 'component/input.php'; 
                            $id = null; $type = null; // Reset
                        ?>
                    </div>
                    <?php 
                        $label = 'Today'; $type = 'secondary'; $size = 'xs'; $attr = 'onclick="setTodayFilter()"'; $id = null;
                        include 'component/button.php';
                        $attr = null; // Reset
                    ?>
                </div>
            </div>
        <?php 
            $content = ob_get_clean();
            $id = null; $class = ''; $padding = true; $footer = null; $bodyClass = '';
            include 'component/card.php'; 
        ?>

        <!-- Table Card -->
        <?php 
            ob_start();
        ?>
            <div class="sticky-table-wrapper overflow-x-auto">
                <table class="w-full text-left text-[11px]">
                    <thead class="bg-slate-900 text-white sticky top-0 z-10">
                        <tr>
                            <th class="px-3 py-2 font-black normal-case tracking-wider">Employee</th>
                            <th class="px-3 py-2 font-black normal-case tracking-wider">Date</th>
                            <th class="px-3 py-2 font-black normal-case tracking-wider">Time</th>
                            <th class="px-3 py-2 font-black normal-case tracking-wider">Type</th>
                            <th class="px-3 py-2 font-black normal-case tracking-wider">Status</th>
                            <th class="px-3 py-2 font-black normal-case tracking-wider">Log</th>
                        </tr>
                    </thead>
                    <tbody id="attendanceTableBody" class="divide-y divide-slate-100 bg-white">
                        <tr>
                            <td colspan="6" class="px-3 py-12 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <span class="iconify text-2xl animate-spin opacity-50" data-icon="mdi:loading"></span>
                                    <p class="text-[10px] font-black normal-case tracking-widest">Loading...</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        <?php 
            $content = ob_get_clean();
            ob_start();
        ?>
            <div id="paginationContainer"></div>
        <?php 
            $footer = ob_get_clean();
            $padding = false; $title = null; $icon = null; $headerRight = null; $id = null; $class = ''; $bodyClass = '';
            include 'component/card.php'; 
        ?>
    </div>
</div>

<!-- QR Modal Overlay -->
<div id="qrModal" class="fixed inset-0 z-[9999] hidden items-start justify-center overflow-y-auto bg-slate-900/40 backdrop-blur-sm px-4 py-6 md:items-center">
    <div class="w-full max-w-sm">
        <?php 
            $title = 'Attendance QR';
            $icon = null;
            ob_start();
        ?>
            <button onclick="closeQRModal()" class="w-6 h-6 flex items-center justify-center rounded-full bg-slate-50 text-slate-500 hover:text-slate-900 transition-colors text-xs">✕</button>
        <?php 
            $headerRight = ob_get_clean();
            ob_start();
        ?>
            <div class="text-center space-y-4">
                <div class="bg-indigo-50 p-4 rounded-2xl inline-block border border-indigo-100">
                    <div id="qrcode" class="rounded-lg overflow-hidden border-4 border-white shadow-sm"></div>
                </div>
                <p class="text-[10px] text-slate-500" id="qrUrlLabel">Scan to record your attendance</p>
            </div>
        <?php 
            $content = ob_get_clean();
            ob_start();
        ?>
            <div class="grid grid-cols-2 gap-2">
                <?php 
                    $label = 'Download'; $type = 'primary'; $size = 'sm'; $icon = 'mdi:download'; $attr = 'onclick="downloadQR()"'; $id = null;
                    include 'component/button.php';
                    $label = 'Print'; $type = 'secondary'; $size = 'sm'; $icon = 'mdi:printer'; $attr = 'onclick="printQR()"'; $id = null;
                    include 'component/button.php';
                    $attr = null; // Reset
                ?>
            </div>
        <?php 
            $footer = ob_get_clean();
            $id = 'qrModalContent';
            $class = 'scale-90 transition-transform duration-300 transform';
            $padding = true;
            include 'component/card.php'; 
            $id = null; $class = ''; // Reset
        ?>
    </div>
</div>

<style>
    #qrcode canvas, #qrcode img {
        width: 180px !important;
        height: 180px !important;
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

    // Helper for date
    function getCurrentDateString() {
        const now = new Date();
        const offset = now.getTimezoneOffset();
        const localDate = new Date(now.getTime() - offset * 60000);
        return localDate.toISOString().split('T')[0];
    }

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
        document.getElementById('dateFilter').value = getCurrentDateString();
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
            tbody.innerHTML = '<tr><td colspan="6" class="px-3 py-12 text-center text-slate-400 font-medium">No records matching your filters</td></tr>';
            return;
        }

        tbody.innerHTML = records.map(rec => {
            const checkTypeName = String(rec.check_type_name || '').toLowerCase();
            const isLeave = checkTypeName === 'leave' || rec.check_time === 'Leave';
            const isCheckIn = !isLeave && checkTypeName.includes('in');
            
            let typeStyle = '';
            let typeLabel = '';
            
            if (isLeave) {
                typeStyle = 'bg-indigo-50 text-indigo-600 border-indigo-100';
                typeLabel = 'Leave';
            } else if (isCheckIn) {
                typeStyle = 'bg-emerald-50 text-emerald-600 border-emerald-100';
                typeLabel = 'In';
            } else {
                typeStyle = 'bg-amber-50 text-amber-600 border-amber-100';
                typeLabel = 'Out';
            }
            
            const timeDisplay = isLeave 
                ? '<span class="flex items-center gap-1"><span class="iconify" data-icon="mdi:calendar-clock"></span>Full</span>' 
                : `<span class="font-black text-slate-700">${rec.check_time}</span>`;
            
            const statusBadge = rec.status_id == 1 
                ? '<span class="bg-emerald-50 text-emerald-600 border-emerald-100 px-1.5 py-0.5 rounded text-[9px] font-black normal-case tracking-wider border">Active</span>'
                : '<span class="bg-slate-50 text-slate-400 border-slate-100 px-1.5 py-0.5 rounded text-[9px] font-black normal-case tracking-wider border">Archived</span>';

            return `
            <tr class="${isLeave ? 'bg-indigo-50/20' : ''} hover:bg-slate-50 transition-colors group">
                <td class="px-3 py-2">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-[10px] font-black normal-case shadow-sm">
                            ${(rec.full_name || rec.emp_code || '#').charAt(0)}
                        </div>
                        <div class="flex flex-col">
                            <span class="text-[11px] font-black text-slate-800 group-hover:text-indigo-600 transition-colors">
                                ${rec.full_name || 'N/A'}
                            </span>
                            <span class="text-[9px] text-slate-400 font-bold normal-case tracking-tight">
                                ${rec.emp_code ? '#' + rec.emp_code : ''}
                            </span>
                        </div>
                    </div>
                </td>
                <td class="px-3 py-2">
                    <span class="text-[10px] font-black text-slate-600">${new Date(rec.date).toLocaleDateString(undefined, {month:'short', day:'numeric'})}</span>
                    <span class="text-[9px] font-bold text-slate-400 block normal-case tracking-tight">${new Date(rec.date).toLocaleDateString(undefined, {year:'2-digit'})}</span>
                </td>
                <td class="px-3 py-2 text-[10px]">${timeDisplay}</td>
                <td class="px-3 py-2">
                    <span class="${typeStyle} px-1.5 py-0.5 rounded text-[9px] font-black normal-case tracking-wider border">
                        ${typeLabel}
                    </span>
                </td>
                <td class="px-3 py-2">${statusBadge}</td>
                <td class="px-3 py-2">
                    <span class="text-[9px] font-black text-slate-400 normal-case tracking-tight">
                        ${new Date(rec.created_at).toLocaleDateString(undefined, {month:'short', day:'numeric'})}
                    </span>
                </td>
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
        const baseClass = 'inline-flex h-6 min-w-6 items-center justify-center rounded-md border px-1.5 text-[9px] font-bold transition-all';
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
                <div class="w-full flex items-center justify-between gap-4 text-[9px] text-slate-500">
                    <span>Showing 0 of 0 records</span>
                </div>
            `;
            return;
        }

        if (totalPages <= 1) {
            container.innerHTML = `
                <div class="w-full flex items-center justify-between gap-4 text-[9px] text-slate-500">
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
            <div class="w-full flex flex-col sm:flex-row items-center justify-between gap-2 px-1">
                <div class="text-[9px] text-slate-500">
                    Showing <span class="font-semibold text-slate-700">${showingFrom}</span>
                    to <span class="font-semibold text-slate-700">${showingTo}</span>
                    of <span class="font-semibold text-slate-700">${totalRecords}</span> records
                </div>

                <div class="flex flex-wrap items-center justify-center gap-1">
                    ${paginationButton(safeCurrentPage - 1, safeCurrentPage, 'Prev', safeCurrentPage === 1, 'min-w-[40px]')}
                    ${pageButtons}
                    ${paginationButton(safeCurrentPage + 1, safeCurrentPage, 'Next', safeCurrentPage === totalPages, 'min-w-[40px]')}
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

    // Backdrop click to close
    document.getElementById('qrModal').addEventListener('click', (e) => {
        if (e.target.id === 'qrModal') closeQRModal();
    });
    
    // Set default filter to today
    document.getElementById('dateFilter').value = getCurrentDateString();
    loadAttendance();
</script>
