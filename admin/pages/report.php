<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Attendance Detail Report</title>

    <!-- Tailwind -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-slate-100">

    <div class="mx-auto p-6 ">

        <!-- HEADER -->
        <div class="flex justify-between items-center mb-4 bg-white p-4 rounded-lg shadow">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Attendance Detail Report</h1>
                <p class="text-gray-600">Employee: <span class="font-semibold">Sophep Chan</span></p>
            </div>

            <div class="flex gap-2">
                <button onclick="window.print()"
                    class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded">
                    Print
                </button>
                <button
                    class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">
                    Export
                </button>
            </div>
        </div>

        <!-- TABLE -->
        <div class="bg-white rounded-lg shadow overflow-x-auto">
            <table class="min-w-full border border-gray-300 text-sm">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="border px-3 py-2">DAY</th>
                        <th class="border px-3 py-2">DATE</th>
                        <th class="border px-3 py-2">TYPE</th>
                        <th class="border px-3 py-2">CHECK-IN</th>
                        <th class="border px-3 py-2">CHECK-OUT</th>
                        <th class="border px-3 py-2">CHECK-IN 2</th>
                        <th class="border px-3 py-2">CHECK-OUT 2</th>
                        <th class="border px-3 py-2">NOTE</th>
                    </tr>
                </thead>

                <tbody id="reportBody"></tbody>
            </table>
        </div>

    </div>

    <script>
        /* =========================
        MOCK DATA (API READY)
        ========================= */
        const attendanceData = [
            {
                day: "Sat",
                date: "01-03-2025",
                type: "Attend",
                checkin: "",
                checkout: "",
                checkin2: "12:37 PM",
                checkout2: "",
                status: "early",
                note: ""
            },
            {
                day: "Sun",
                date: "02-03-2025",
                type: "Attend (Absent 0.5)",
                checkin: "05:05 PM",
                checkout: "05:05 PM",
                checkin2: "",
                checkout2: "",
                status: "late",
                note: "Checkin2: Sorry I am late"
            },
            {
                day: "Mon",
                date: "03-03-2025",
                type: "Attend",
                checkin: "08:08 AM",
                checkout: "12:00 PM",
                checkin2: "01:00 PM",
                checkout2: "05:16 PM",
                status: "normal",
                note: ""
            },
            {
                day: "Tue",
                date: "04-03-2025",
                type: "Attend",
                checkin: "08:17 AM",
                checkout: "12:00 PM",
                checkin2: "01:00 PM",
                checkout2: "05:25 PM",
                status: "late",
                note: "Checkin: Sorry I am late"
            },
            {
                day: "Thu",
                date: "06-03-2025",
                type: "Attend",
                checkin: "08:21 AM",
                checkout: "12:00 PM",
                checkin2: "01:00 PM",
                checkout2: "05:09 PM",
                status: "late",
                note: "Checkin: Sorry I am late"
            }
        ];

        /* =========================
        HELPERS
        ========================= */
        function badge(type) {
            if (type.includes("Absent")) {
                return `<span class="text-red-600 font-semibold">${type}</span>`;
            }
            return `<span class="text-green-600 font-semibold">${type}</span>`;
        }

        function timeStyle(time, status) {
            if (!time) return "";
            if (status === "late") {
                return `<span class="text-red-600 font-semibold">${time} (Late)</span>`;
            }
            if (status === "early") {
                return `<span class="text-green-600 font-semibold">${time} (Early)</span>`;
            }
            return time;
        }

        /* =========================
        RENDER TABLE
        ========================= */
        function renderTable() {
            const tbody = document.getElementById("reportBody");
            tbody.innerHTML = "";

            attendanceData.forEach(row => {
                tbody.innerHTML += `
                    <tr class="hover:bg-gray-50">
                        <td class="border px-3 py-2 text-center">${row.day}</td>
                        <td class="border px-3 py-2 text-center">${row.date}</td>
                        <td class="border px-3 py-2">${badge(row.type)}</td>
                        <td class="border px-3 py-2 text-center">${timeStyle(row.checkin, row.status)}</td>
                        <td class="border px-3 py-2 text-center">${row.checkout || ""}</td>
                        <td class="border px-3 py-2 text-center">${row.checkin2 || ""}</td>
                        <td class="border px-3 py-2 text-center">${row.checkout2 || ""}</td>
                        <td class="border px-3 py-2">${row.note || ""}</td>
                    </tr>
                `;
            });
        }

        document.addEventListener("DOMContentLoaded", renderTable);
    </script>

</body>
</html>
