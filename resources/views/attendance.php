<div class="w-full h-full">
    <div class="bg-white shadow-lg p-4">

        <div class="bg-white rounded-lg shadow-sm p-4 mb-4">

            <!-- QR Icon Button -->
            <button onclick="openQRModal()"
                class="flex items-center gap-1 px-3 py-1.5 text-xs font-semibold text-indigo-600 bg-indigo-50 border border-indigo-200 rounded-lg hover:bg-indigo-100 cursor-pointer">
                <iconify-icon icon="mdi:qrcode" style="font-size:16px;"></iconify-icon>
                QR Code
            </button>

            <!-- QR Modal Overlay -->
            <div id="qrModal" onclick="closeQRModal(event)"
                style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center;">
                <div style="background:#fff; border-radius:14px; padding:1.5rem; width:100%; max-width:300px; text-align:center; position:relative;">

                    <!-- Header -->
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
                        <p style="font-size:14px; font-weight:600; color:#111827; margin:0;">Attendance QR Code</p>
                        <button onclick="document.getElementById('qrModal').style.display='none'"
                            style="width:26px; height:26px; border-radius:50%; border:1px solid #e5e7eb; background:#f9fafb; cursor:pointer; font-size:14px; color:#6b7280; display:flex; align-items:center; justify-content:center;">✕</button>
                    </div>

                    <!-- QR -->
                    <div style="background:#f9fafb; border:1px solid #e5e7eb; border-radius:10px; padding:16px; display:inline-block; margin-bottom:10px;">
                        <div id="qrcode"></div>
                    </div>

                    <!-- URL label -->
                    <p style="font-size:11px; color:#9ca3af; margin-bottom:14px;" id="qrUrlLabel"></p>

                    <!-- Buttons -->
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px;">
                        <button onclick="downloadQR()"
                            style="padding:9px; border-radius:8px; font-size:13px; font-weight:600; border:none; cursor:pointer; background:#4f46e5; color:#fff;">
                            ⬇ Download
                        </button>
                        <button onclick="printQR()"
                            style="padding:9px; border-radius:8px; font-size:13px; font-weight:600; border:1px solid #e5e7eb; cursor:pointer; background:#fff; color:#374151;">
                            🖨 Print
                        </button>
                    </div>

                </div>
            </div>

            <style>
                #qrcode canvas, #qrcode img {
                    width: 160px !important;
                    height: 160px !important;
                }
            </style>


        <div class="flex justify-between items-center">
            <div class="flex items-center gap-2">
                <iconify-icon icon="mdi:clock-check" style="font-size: 24px; color: #4f46e5;"></iconify-icon>
                <h1 class="text-lg font-bold text-gray-900">Attendance Records</h1>
            </div>
            <span class="text-xs font-semibold bg-indigo-100 text-indigo-700 px-3 py-1 rounded-full" id="totalCount">0 Records</span>
        </div>

        <!-- Filters -->
        <div class="flex flex-col sm:flex-row gap-2 mt-4">
            <div class="flex-1 relative">
                <iconify-icon icon="mdi:magnify"
                    style="position:absolute; left:10px; top:50%; transform:translateY(-50%); color:#9ca3af; font-size:18px;"></iconify-icon>
                <input type="text" id="searchInput" placeholder="Search by employee ID or date..."
                    class="w-full pl-9 pr-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
            </div>
            <select id="checkTypeFilter"
                class="px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white cursor-pointer">
                <option value="">All Check Types</option>
                <option value="check-in">Check In</option>
                <option value="check-out">Check Out</option>
            </select>
            <input type="date" id="dateFilter"
                class="px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-900 text-white sticky top-0">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold">Employee ID</th>
                        <th class="px-4 py-3 text-left font-semibold">Date</th>
                        <th class="px-4 py-3 text-left font-semibold">Check Time</th>
                        <th class="px-4 py-3 text-left font-semibold">Type</th>
                        <th class="px-4 py-3 text-left font-semibold">Status</th>
                        <th class="px-4 py-3 text-left font-semibold">Created</th>
                    </tr>
                </thead>
                <tbody id="attendanceTableBody" class="divide-y divide-gray-100">
                    <tr>
                        <td colspan="6" class="px-4 py-6 text-center text-gray-400">
                            <div class="flex items-center justify-center gap-2">
                                <iconify-icon icon="mdi:loading" class="animate-spin" style="font-size:20px;"></iconify-icon>
                                Loading...
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div id="paginationContainer" class="px-4 py-3 border-t border-gray-100 bg-gray-50 flex justify-center gap-2"></div>
    </div>
    </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
    let currentPage = 1;
    let totalPages  = 1;
    let allRecords  = [];
    const perPage   = 18;


    const qrContent = 'DOORSTEP_ATTENDANCE';

    document.getElementById('qrUrlLabel').textContent = 'Scan to record your attendance';

    new QRCode(document.getElementById('qrcode'), {
        text: qrContent,  // ✅ just the number e.g. "1"
        width: 256,
        height: 256,
        colorDark: '#000000',
        colorLight: '#ffffff',
        correctLevel: QRCode.CorrectLevel.H
    });
    function openQRModal() {
        document.getElementById('qrModal').style.display = 'flex';
    }

    function closeQRModal(e) {
        if (e.target === document.getElementById('qrModal')) {
            document.getElementById('qrModal').style.display = 'none';
        }
    }

    function generateQRCard(size, callback) {
        const padding = Math.round(size * 0.08);
        const qrSize  = Math.round(size * 0.65);

        const tempDiv = document.createElement('div');
        tempDiv.style.position = 'absolute';
        tempDiv.style.left = '-9999px';
        document.body.appendChild(tempDiv);

        new QRCode(tempDiv, {
            text: qrContent,
            width: qrSize,
            height: qrSize,
            colorDark: '#000000',
            colorLight: '#ffffff',
            correctLevel: QRCode.CorrectLevel.H
        });

        setTimeout(() => {
            const qrCanvas = tempDiv.querySelector('canvas');
            if (!qrCanvas) { document.body.removeChild(tempDiv); return; }

            const cardW = size;
            const cardH = Math.round(size * 1.35);

            const canvas  = document.createElement('canvas');
            canvas.width  = cardW;
            canvas.height = cardH;
            const ctx     = canvas.getContext('2d');

            // outer bg
            ctx.fillStyle = '#f3f4f6';
            ctx.fillRect(0, 0, cardW, cardH);

            // white card
            ctx.fillStyle = '#ffffff';
            roundRect(ctx, padding, padding, cardW - padding * 2, cardH - padding * 2, Math.round(size * 0.05));
            ctx.fill();

            // blue circle
            const circleX = cardW / 2;
            const circleY = padding * 2.5;
            const circleR = Math.round(size * 0.09);
            ctx.fillStyle = '#3b82f6';
            ctx.beginPath();
            ctx.arc(circleX, circleY, circleR, 0, Math.PI * 2);
            ctx.fill();

            // checkmark
            ctx.strokeStyle = '#ffffff';
            ctx.lineWidth   = Math.round(size * 0.018);
            ctx.lineCap     = 'round';
            ctx.lineJoin    = 'round';
            ctx.beginPath();
            ctx.moveTo(circleX - circleR * 0.4, circleY);
            ctx.lineTo(circleX - circleR * 0.05, circleY + circleR * 0.38);
            ctx.lineTo(circleX + circleR * 0.45, circleY - circleR * 0.35);
            ctx.stroke();

            // title
            const titleY  = circleY + circleR + Math.round(size * 0.09);
            const fontSize = Math.round(size * 0.07);
            ctx.fillStyle = '#111827';
            ctx.font      = `bold ${fontSize}px sans-serif`;
            ctx.textAlign = 'center';
            ctx.fillText('QR ATTENDANCE', cardW / 2, titleY);

            // QR image
            const qrX = (cardW - qrSize) / 2;
            const qrY = titleY + Math.round(size * 0.06);
            ctx.drawImage(qrCanvas, qrX, qrY, qrSize, qrSize);

            // subtitle
            const subY    = qrY + qrSize + Math.round(size * 0.07);
            const subSize = Math.round(size * 0.055);
            ctx.fillStyle = '#6b7280';
            ctx.font      = `${subSize}px sans-serif`;
            ctx.fillText('Scan to check in', cardW / 2, subY);

            document.body.removeChild(tempDiv);
            callback(canvas);
        }, 200);
    }

    function roundRect(ctx, x, y, w, h, r) {
        ctx.beginPath();
        ctx.moveTo(x + r, y);
        ctx.lineTo(x + w - r, y);
        ctx.quadraticCurveTo(x + w, y, x + w, y + r);
        ctx.lineTo(x + w, y + h - r);
        ctx.quadraticCurveTo(x + w, y + h, x + w - r, y + h);
        ctx.lineTo(x + r, y + h);
        ctx.quadraticCurveTo(x, y + h, x, y + h - r);
        ctx.lineTo(x, y + r);
        ctx.quadraticCurveTo(x, y, x + r, y);
        ctx.closePath();
    }

    function downloadQR() {
        generateQRCard(600, (canvas) => {
            const a = document.createElement('a');
            a.download = 'attendance-qr.png';
            a.href = canvas.toDataURL('image/png');
            a.click();
        });
    }

    function printQR() {
        generateQRCard(600, (canvas) => {
            const win = window.open('', '_blank');
            win.document.write(`
                <html><body style="margin:0;display:flex;justify-content:center;align-items:center;min-height:100vh;background:#f3f4f6;">
                    <img src="${canvas.toDataURL('image/png')}" style="width:320px;">
                </body></html>
            `);
            win.document.close();
            win.print();
        });
    }



    /**
     * Determine if a check type name is "check-in" or "check-out"
     * Based on the name from tbl_check_types (e.g. "Check-in 1", "Check-in 2")
     */
    function isCheckIn(checkTypeName) {
        return checkTypeName.toLowerCase().includes('check-in');
    }

    function getCheckTypeLabel(checkTypeName) {
        return isCheckIn(checkTypeName) ? 'Check In' : 'Check Out';
    }

    function getCheckTypeColor(checkTypeName) {
        return isCheckIn(checkTypeName)
            ? 'bg-green-100 text-green-700'
            : 'bg-orange-100 text-orange-700';
    }

    function getStatusBadge(statusId) {
        return statusId == 1
            ? '<span class="bg-blue-100 text-blue-700 px-2 py-1 rounded text-xs font-semibold">Active</span>'
            : '<span class="bg-gray-100 text-gray-700 px-2 py-1 rounded text-xs font-semibold">Inactive</span>';
    }

    // fetch attendance
    function loadAttendance(page = 1) {
        const searchInput = document.getElementById("searchInput").value;
        const checkType   = document.getElementById("checkTypeFilter").value; // "check-in" | "check-out" | ""
        const date        = document.getElementById("dateFilter").value;

        const params = new URLSearchParams({
            "paging_options[page]":     page,
            "paging_options[per_page]": perPage,
            "filters[status_id]":       1
        });

        fetch("/api/attendance/show?" + params.toString())
            .then(res => res.json())
            .then(result => {
                const tbody = document.getElementById("attendanceTableBody");

                if (result.success && result.data) {
                    allRecords = result.data.attendance_records;

                    // apply frontend filters
                    let filtered = allRecords.filter(rec => {
                        const matchSearch = (
                            (String(rec.emp_code || '').toLowerCase().includes(searchInput.toLowerCase())) ||
                            rec.date.includes(searchInput)
                        );

                        // filter by check-in or check-out using check_type_name from DB
                        const matchType = !checkType ||
                            rec.check_type_name.toLowerCase().includes(checkType);

                        const matchDate = !date || rec.date === date;

                        return matchSearch && matchType && matchDate;
                    });

                    renderTable(filtered);

                    const total = result.pagination.total;
                    totalPages  = result.pagination.total_pages;
                    currentPage = page;
                    document.getElementById("totalCount").textContent = total + " Records";

                    renderPagination(totalPages, currentPage);
                } else {
                    tbody.innerHTML = '<tr><td colspan="6" class="px-4 py-6 text-center text-gray-400">No attendance records found</td></tr>';
                }
            })
            .catch(err => {
                document.getElementById("attendanceTableBody").innerHTML = `
                    <tr><td colspan="6" class="px-4 py-6 text-center text-red-500">
                        <iconify-icon icon="mdi:alert-circle"></iconify-icon> Error loading data
                    </td></tr>`;
                console.error(err);
            });
    }

    // render table
    function renderTable(records) {
        const tbody = document.getElementById("attendanceTableBody");

        if (!records.length) {
            tbody.innerHTML = '<tr><td colspan="6" class="px-4 py-6 text-center text-gray-400">No attendance records found</td></tr>';
            return;
        }

        tbody.innerHTML = records.map(rec => `
            <tr class="hover:bg-indigo-50 transition-colors">
                <td class="px-4 py-3 font-medium text-gray-900">${rec.emp_code ? '#' + rec.emp_code : '<span class="text-red-400 text-xs">Unknown (#' + rec.employee_id + ')</span>'}</td>
                <td class="px-4 py-3 text-gray-600 text-sm">${new Date(rec.date).toLocaleDateString()}</td>
                <td class="px-4 py-3 font-mono text-sm font-semibold text-indigo-600">${rec.check_time}</td>
                <td class="px-4 py-3">
                    <span class="${getCheckTypeColor(rec.check_type_name)} px-2 py-1 rounded text-xs font-semibold">
                        ${getCheckTypeLabel(rec.check_type_name)}
                    </span>
                </td>
                <td class="px-4 py-3">${getStatusBadge(rec.status_id)}</td>
                <td class="px-4 py-3 text-gray-500 text-xs">${new Date(rec.created_at).toLocaleDateString()}</td>
            </tr>
        `).join('');
    }

    // simple pagination
    function renderPagination(totalPages, currentPage) {
        const container = document.getElementById("paginationContainer");
        container.innerHTML = '';

        if (totalPages <= 1) return;

        for (let i = 1; i <= totalPages; i++) {
            const btn = document.createElement('button');
            btn.textContent = i;
            btn.className = `px-3 py-1 border rounded ${i === currentPage ? 'bg-indigo-500 text-white' : 'bg-white text-gray-700'}`;
            btn.onclick = () => loadAttendance(i);
            container.appendChild(btn);
        }
    }

    // filter events
    document.getElementById("searchInput").addEventListener("input",  () => loadAttendance(1));
    document.getElementById("checkTypeFilter").addEventListener("change", () => loadAttendance(1));
    document.getElementById("dateFilter").addEventListener("change",  () => loadAttendance(1));

    // initial load
    loadAttendance();
</script>
