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
                <?php if (!function_exists('hasPermissionSlug') || hasPermissionSlug('attendance.manage_location')): ?>
                    <?php
                        $label = 'Location'; $type = 'secondary'; $size = 'xs'; $icon = 'mdi:map-marker-radius'; $attr = 'onclick="openLocationModal()"'; $id = null;
                        include 'component/button.php';
                        $label = null; $attr = null; $icon = null;
                    ?>
                <?php endif; ?>
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

<!-- Location Modal -->
<div id="locationModal" class="fixed inset-0 z-[9999] hidden items-start justify-center overflow-y-auto bg-slate-900/40 backdrop-blur-sm px-4 py-6 md:items-center">
    <div class="w-full max-w-5xl">
        <div id="locationModalContent" class="scale-90 transform transition-transform duration-300">
            <div class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-2xl shadow-slate-950/10 max-h-[85vh] flex flex-col">
                <div class="sticky top-0 z-10 flex items-center justify-between border-b border-slate-100 bg-white px-4 py-3 shrink-0">
                    <div>
                        <h3 class="text-sm font-black text-slate-800">Attendance Location</h3>
                        <p class="text-[10px] font-medium text-slate-500">Manage the active geofence used for attendance verification.</p>
                    </div>
                    <button onclick="closeLocationModal()" class="w-6 h-6 flex items-center justify-center rounded-full bg-slate-50 text-slate-500 hover:text-slate-900 transition-colors text-xs">✕</button>
                </div>

                <div class="overflow-y-auto grow">
                    <div class="grid gap-4 p-4 lg:grid-cols-[1.15fr_0.85fr]">
                        <div class="space-y-4">
                            <div class="rounded-xl border border-indigo-100 bg-indigo-50/60 p-3">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="text-[9px] font-black uppercase tracking-[0.2em] text-indigo-500">Current Attendance Location</p>
                                        <h4 id="currentLocationName" class="mt-1 text-sm font-black text-slate-900">No active location</h4>
                                        <p id="currentLocationMeta" class="mt-0.5 text-[10px] font-medium text-slate-500">Load the location list to see the active geofence.</p>
                                    </div>
                                    <span id="currentLocationStatus" class="inline-flex items-center rounded-full border px-2 py-0.5 text-[9px] font-black uppercase tracking-wider border-slate-200 bg-white text-slate-500">Inactive</span>
                                </div>
                                <div class="mt-3 grid grid-cols-3 gap-2 text-[10px]">
                                    <div class="rounded-lg bg-white/90 p-2 border border-slate-100">
                                        <div class="text-slate-400 font-black uppercase tracking-wider">Latitude</div>
                                        <div id="currentLocationLatitude" class="mt-1 font-black text-slate-700">-</div>
                                    </div>
                                    <div class="rounded-lg bg-white/90 p-2 border border-slate-100">
                                        <div class="text-slate-400 font-black uppercase tracking-wider">Longitude</div>
                                        <div id="currentLocationLongitude" class="mt-1 font-black text-slate-700">-</div>
                                    </div>
                                    <div class="rounded-lg bg-white/90 p-2 border border-slate-100">
                                        <div class="text-slate-400 font-black uppercase tracking-wider">Radius</div>
                                        <div id="currentLocationRadius" class="mt-1 font-black text-slate-700">-</div>
                                    </div>
                                </div>
                            </div>

                            <form id="locationForm" class="space-y-4">
                                <input type="hidden" id="locationId">
                                <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                                    <?php
                                        $label = 'Location Name'; $id = 'locationName'; $placeholder = 'Sangkat Srah Chak'; $required = true; $icon = 'mdi:map-marker-outline';
                                        include 'component/input.php';
                                        $label = 'Location Coordinates'; $id = 'locationCoordinates'; $placeholder = '11.580914, 104.909832'; $required = true; $icon = 'mdi:map-marker-radius';
                                        include 'component/input.php';
                                        $label = null; $icon = null;
                                    ?>
                                </div>

                                <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                                    <?php
                                        $label = 'Allowed Radius (meters)'; $id = 'locationRadius'; $type = 'number'; $placeholder = '100'; $required = true; $icon = 'mdi:ruler-square-compass';
                                        include 'component/input.php';
                                        $label = 'Status'; $id = 'locationStatus'; $required = true; $placeholder = 'Select Status';
                                        $options = ['active' => 'Active', 'inactive' => 'Inactive'];
                                        include 'component/select.php';
                                        $label = null; $icon = null; $options = [];
                                    ?>
                                </div>
                                <div class="space-y-1">
                                    <p class="text-[10px] font-medium text-slate-400">Example: 11.580914, 104.909832</p>
                                    <div class="flex flex-wrap gap-2">
                                        <?php
                                            $label = 'Use Current Location'; $type = 'secondary'; $size = 'xs'; $icon = 'mdi:crosshairs-gps'; $attr = 'onclick="fillCurrentLocation()"'; $id = null;
                                            include 'component/button.php';
                                            $label = null; $icon = null; $attr = null;
                                        ?>
                                    </div>
                                </div>

                                <div class="space-y-1.5">
                                    <p id="locationFormError" class="hidden rounded-lg border border-rose-100 bg-rose-50 px-3 py-2 text-[11px] font-bold text-rose-600"></p>
                                </div>
                            </form>

                            <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50/80 p-4">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-[9px] font-black uppercase tracking-[0.2em] text-slate-400">Map Preview</p>
                                        <p id="locationPreviewSubtitle" class="mt-1 text-[10px] font-medium text-slate-500">Live geofence preview based on the form values.</p>
                                    </div>
                                    <span id="locationPreviewStatusBadge" class="inline-flex items-center rounded-full border border-slate-200 bg-white px-2 py-0.5 text-[9px] font-black uppercase tracking-wider text-slate-500">Empty</span>
                                </div>
                                <div class="mt-3 rounded-xl border border-slate-200 overflow-hidden">
                                    <div id="locationPreviewMap" class="h-56 w-full"></div>
                                </div>
                                <div class="mt-2 flex items-center justify-between text-[9px] font-black uppercase tracking-[0.2em] text-slate-400">
                                    <span id="locationPreviewName">No location selected</span>
                                    <span id="locationPreviewRadiusLabel">-- m</span>
                                </div>
                                <p id="locationPreviewCoordinates" class="mt-1 text-[10px] font-black uppercase tracking-wider text-slate-700 text-center">Enter coordinates to preview</p>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-[9px] font-black uppercase tracking-[0.2em] text-slate-400">Saved Locations</p>
                                    <p class="mt-1 text-[10px] font-medium text-slate-500">Edit, activate, or deactivate stored locations.</p>
                                </div>
                                <button type="button" onclick="loadAttendanceLocations()"
                                    class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-2 py-1 text-[10px] font-black text-slate-600 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700">
                                    <span class="iconify text-[12px]" data-icon="mdi:refresh"></span>
                                    Refresh
                                </button>
                            </div>

                            <div id="locationList" class="max-h-[440px] space-y-2 overflow-y-auto pr-1">
                                <div class="rounded-xl border border-slate-100 bg-slate-50 px-3 py-8 text-center text-[10px] font-medium text-slate-400">
                                    Loading...
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 border-t border-slate-100 bg-slate-50 px-4 py-3">
                    <?php
                        $label = 'Cancel'; $type = 'secondary'; $size = 'sm'; $attr = 'onclick="closeLocationModal()"';
                        include 'component/button.php';
                        $label = 'Save Location'; $type = 'primary'; $size = 'sm'; $icon = 'mdi:content-save-outline'; $attr = 'onclick="submitLocationForm()"'; $id = 'saveLocationButton';
                        include 'component/button.php';
                        $label = null; $icon = null; $attr = null; $id = null;
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    #qrcode canvas, #qrcode img {
        width: 180px !important;
        height: 180px !important;
        margin: 0 auto;
    }
</style>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>F
<script>
    let currentPage = 1;
    let totalPages  = 1;
    const perPage   = 12;
    const qrDarkColor = '#0f172a';
    const qrLightColor = '#ffffff';
    const qrContent = 'DOORSTEP_ATTENDANCE';
    const qrcode = document.getElementById('qrcode');
    const locationState = {
        locations: [],
        currentLocation: null,
        loading: false,
    };

    let previewMap = null;
    let previewMarker = null;
    let previewCircle = null;
    const defaultMapCenter = [11.5564, 104.9282]; // Phnom Penh fallback

    function ensurePreviewMap() {
        if (previewMap) return previewMap;

        previewMap = L.map('locationPreviewMap', { zoomControl: true }).setView(defaultMapCenter, 13);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors',
        }).addTo(previewMap);

        previewMap.on('click', (e) => {
            document.getElementById('locationCoordinates').value = `${e.latlng.lat.toFixed(6)}, ${e.latlng.lng.toFixed(6)}`;
            renderLocationPreview();
        });

        return previewMap;
    }

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

    function openLocationModal() {
        const modal = document.getElementById('locationModal');
        const content = document.getElementById('locationModalContent');
        if (modal.parentElement !== document.body) {
            document.body.appendChild(modal);
        }
        resetLocationForm();
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        setTimeout(() => {
            content.classList.replace('scale-90', 'scale-100');
            ensurePreviewMap().invalidateSize();
        }, 10);
        loadAttendanceLocations();
    }

    function closeLocationModal() {
        const modal = document.getElementById('locationModal');
        const content = document.getElementById('locationModalContent');
        content.classList.replace('scale-100', 'scale-90');
        setTimeout(() => {
            modal.classList.remove('flex');
            modal.classList.add('hidden');
        }, 200);
    }

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function statusBadge(status) {
        const isActive = String(status || '').toLowerCase() === 'active';
        return isActive
            ? '<span class="inline-flex items-center rounded-full border border-emerald-100 bg-emerald-50 px-2 py-0.5 text-[9px] font-black uppercase tracking-wider text-emerald-600">Active</span>'
            : '<span class="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-2 py-0.5 text-[9px] font-black uppercase tracking-wider text-slate-500">Inactive</span>';
    }

    function formatCoordinate(value) {
        if (value === null || value === undefined || value === '') {
            return '-';
        }

        const num = Number(value);
        return Number.isFinite(num) ? num.toFixed(8) : String(value);
    }

    function formatCoordinateForInput(value) {
        const num = Number(value);
        if (!Number.isFinite(num)) {
            return '';
        }

        return Number(num.toFixed(8)).toString();
    }

    function normalizeCoordinatePair(rawValue) {
        const raw = String(rawValue ?? '').trim();
        if (!raw) {
            return { valid: false, error: 'Please enter valid coordinates. Example: 11.580914, 104.909832' };
        }

        const parts = raw.split(',');
        if (parts.length !== 2) {
            return { valid: false, error: 'Please enter valid coordinates. Example: 11.580914, 104.909832' };
        }

        const latitude = Number(parts[0].trim());
        const longitude = Number(parts[1].trim());

        if (!Number.isFinite(latitude) || !Number.isFinite(longitude)) {
            return { valid: false, error: 'Please enter valid coordinates. Example: 11.580914, 104.909832' };
        }

        if (latitude < -90 || latitude > 90 || longitude < -180 || longitude > 180) {
            return { valid: false, error: 'Please enter valid coordinates. Example: 11.580914, 104.909832' };
        }

        return {
            valid: true,
            latitude,
            longitude,
            value: `${latitude.toFixed(6)}, ${longitude.toFixed(6)}`,
        };
    }

    function getLocationPreviewData() {
        const name = String(document.getElementById('locationName')?.value ?? '').trim();
        const coordinates = normalizeCoordinatePair(document.getElementById('locationCoordinates')?.value ?? '');
        const radiusValue = Number(document.getElementById('locationRadius')?.value ?? 0);
        const status = String(document.getElementById('locationStatus')?.value ?? 'active').toLowerCase();

        return {
            name,
            coordinates,
            radius: Number.isFinite(radiusValue) && radiusValue > 0 ? radiusValue : 0,
            status,
        };
    }

    function renderLocationPreview() {
        const previewName = document.getElementById('locationPreviewName');
        const previewCoords = document.getElementById('locationPreviewCoordinates');
        const previewRadius = document.getElementById('locationPreviewRadiusLabel');
        const previewBadge = document.getElementById('locationPreviewStatusBadge');
        const previewSubtitle = document.getElementById('locationPreviewSubtitle');
        const emptyOverlay = document.getElementById('locationPreviewEmptyOverlay');

        if (!previewName || !previewCoords || !previewRadius || !previewBadge || !previewSubtitle) {
            return;
        }

        const data = getLocationPreviewData();
        const hasValidCoordinates = data.coordinates.valid;
        const isActive = data.status === 'active';
        const radius = data.radius > 0 ? data.radius : 0;

        previewName.textContent = data.name || 'No location selected';
        previewCoords.textContent = hasValidCoordinates
            ? `${data.coordinates.latitude.toFixed(6)}, ${data.coordinates.longitude.toFixed(6)}`
            : 'Enter coordinates to preview';
        previewRadius.textContent = radius > 0 ? `${radius} m` : '-- m';
        previewBadge.textContent = isActive ? 'Active' : 'Inactive';
        previewBadge.className = isActive
            ? 'inline-flex items-center rounded-full border border-emerald-100 bg-emerald-50 px-2 py-0.5 text-[9px] font-black uppercase tracking-wider text-emerald-600'
            : 'inline-flex items-center rounded-full border border-slate-200 bg-white px-2 py-0.5 text-[9px] font-black uppercase tracking-wider text-slate-500';

        const map = ensurePreviewMap();

        if (!hasValidCoordinates) {
            previewSubtitle.textContent = 'Live geofence preview based on the form values.';
            if (previewMarker) { map.removeLayer(previewMarker); previewMarker = null; }
            if (previewCircle) { map.removeLayer(previewCircle); previewCircle = null; }
            if (emptyOverlay) emptyOverlay.classList.remove('hidden');
            return;
        }

        if (emptyOverlay) emptyOverlay.classList.add('hidden');

        const latLng = [data.coordinates.latitude, data.coordinates.longitude];
        previewSubtitle.textContent = `Geofence centered at ${data.coordinates.latitude.toFixed(6)}, ${data.coordinates.longitude.toFixed(6)}.`;

        const markerColor = isActive ? '#4f46e5' : '#64748b';

        if (!previewMarker) {
            previewMarker = L.marker(latLng, { draggable: true }).addTo(map);
            previewMarker.on('dragend', () => {
                const pos = previewMarker.getLatLng();
                document.getElementById('locationCoordinates').value = `${pos.lat.toFixed(6)}, ${pos.lng.toFixed(6)}`;
                renderLocationPreview();
            });
        } else {
            previewMarker.setLatLng(latLng);
        }

        if (!previewCircle) {
            previewCircle = L.circle(latLng, {
                radius: radius || 50,
                color: markerColor,
                fillColor: markerColor,
                fillOpacity: 0.15,
                weight: 2,
            }).addTo(map);
        } else {
            previewCircle.setLatLng(latLng);
            previewCircle.setRadius(radius || 50);
            previewCircle.setStyle({ color: markerColor, fillColor: markerColor });
        }

        map.setView(latLng, map.getZoom() < 14 ? 15 : map.getZoom());
    }

    function setLocationFormError(message = '', errors = null) {
        const box = document.getElementById('locationFormError');
        const parts = [];

        if (message) {
            parts.push(escapeHtml(message));
        }

        if (errors && typeof errors === 'object') {
            Object.values(errors).forEach((value) => {
                if (value) {
                    parts.push(escapeHtml(value));
                }
            });
        }

        if (!parts.length) {
            box.classList.add('hidden');
            box.innerHTML = '';
            return;
        }

        box.innerHTML = parts.length > 1
            ? `<ul class="list-disc space-y-1 pl-4">${parts.map((item) => `<li>${item}</li>`).join('')}</ul>`
            : parts[0];
        box.classList.remove('hidden');
    }

    function resetLocationForm() {
        document.getElementById('locationId').value = '';
        document.getElementById('locationName').value = '';
        document.getElementById('locationCoordinates').value = '';
        document.getElementById('locationRadius').value = '';
        document.getElementById('locationStatus').value = 'active';
        setLocationFormError();
        document.getElementById('saveLocationButton').innerHTML = '<span class="iconify text-[14px] shrink-0" data-icon="mdi:content-save-outline"></span> Save Location';
        renderLocationPreview();
    }

    function populateLocationForm(location) {
        if (!location) {
            resetLocationForm();
            return;
        }

        document.getElementById('locationId').value = location.id ?? '';
        document.getElementById('locationName').value = location.name ?? '';
        document.getElementById('locationCoordinates').value = `${formatCoordinateForInput(location.latitude)}, ${formatCoordinateForInput(location.longitude)}`;
        document.getElementById('locationRadius').value = location.radius ?? '';
        document.getElementById('locationStatus').value = String(location.status || 'inactive').toLowerCase();
        setLocationFormError();
        document.getElementById('saveLocationButton').innerHTML = '<span class="iconify text-[14px] shrink-0" data-icon="mdi:content-save-outline"></span> Update Location';
        renderLocationPreview();
    }

    function setCurrentLocation(location) {
        const nameEl = document.getElementById('currentLocationName');
        const metaEl = document.getElementById('currentLocationMeta');
        const statusEl = document.getElementById('currentLocationStatus');
        const latEl = document.getElementById('currentLocationLatitude');
        const lngEl = document.getElementById('currentLocationLongitude');
        const radiusEl = document.getElementById('currentLocationRadius');

        if (!location) {
            nameEl.textContent = 'No active location';
            metaEl.textContent = 'Create and activate a location to enable GPS attendance later.';
            statusEl.innerHTML = 'Inactive';
            statusEl.className = 'inline-flex items-center rounded-full border px-2 py-0.5 text-[9px] font-black uppercase tracking-wider border-slate-200 bg-white text-slate-500';
            latEl.textContent = '-';
            lngEl.textContent = '-';
            radiusEl.textContent = '-';
            return;
        }

        nameEl.textContent = location.name || 'Unnamed location';
        metaEl.textContent = `Allowed radius ${location.radius ?? '-'} meters`;
        statusEl.innerHTML = (String(location.status || '').toLowerCase() === 'active')
            ? 'Active'
            : 'Inactive';
        statusEl.className = String(location.status || '').toLowerCase() === 'active'
            ? 'inline-flex items-center rounded-full border px-2 py-0.5 text-[9px] font-black uppercase tracking-wider border-emerald-100 bg-emerald-50 text-emerald-600'
            : 'inline-flex items-center rounded-full border px-2 py-0.5 text-[9px] font-black uppercase tracking-wider border-slate-200 bg-white text-slate-500';
        latEl.textContent = formatCoordinate(location.latitude);
        lngEl.textContent = formatCoordinate(location.longitude);
        radiusEl.textContent = location.radius ? `${location.radius} m` : '-';
    }

    function renderLocationList(locations) {
        const list = document.getElementById('locationList');
        if (!Array.isArray(locations) || locations.length === 0) {
            list.innerHTML = `
                <div class="rounded-xl border border-slate-100 bg-slate-50 px-3 py-8 text-center text-[10px] font-medium text-slate-400">
                    No attendance locations found.
                </div>
            `;
            return;
        }

        list.innerHTML = locations.map((location) => {
            const isActive = String(location.status || '').toLowerCase() === 'active';
            const toggleLabel = isActive ? 'Deactivate' : 'Activate';
            const toggleClass = isActive
                ? 'border-rose-100 bg-rose-50 text-rose-600 hover:border-rose-200 hover:bg-rose-100'
                : 'border-emerald-100 bg-emerald-50 text-emerald-600 hover:border-emerald-200 hover:bg-emerald-100';

            return `
                <div class="rounded-xl border ${isActive ? 'border-indigo-100 bg-indigo-50/50' : 'border-slate-100 bg-white'} p-3 shadow-sm">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <p class="truncate text-[11px] font-black text-slate-800">${escapeHtml(location.name || 'Unnamed location')}</p>
                                ${statusBadge(location.status)}
                            </div>
                            <p class="mt-1 text-[10px] font-medium text-slate-500">
                                ${formatCoordinate(location.latitude)}, ${formatCoordinate(location.longitude)}
                            </p>
                            <p class="text-[10px] font-bold text-slate-400">Radius ${escapeHtml(location.radius ?? '-') } meters</p>
                        </div>
                        <div class="flex shrink-0 flex-col gap-1">
                            <button type="button"
                                class="inline-flex items-center justify-center gap-1 rounded-md border border-slate-200 bg-white px-2 py-1 text-[10px] font-black text-slate-600 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700"
                                onclick="editLocation(${location.id})">
                                <span class="iconify text-[12px]" data-icon="mdi:pencil-outline"></span>
                                Edit
                            </button>
                            <button type="button"
                                class="inline-flex items-center justify-center gap-1 rounded-md border px-2 py-1 text-[10px] font-black transition ${toggleClass}"
                                onclick="toggleLocationStatus(${location.id}, '${isActive ? 'inactive' : 'active'}')">
                                <span class="iconify text-[12px]" data-icon="${isActive ? 'mdi:pause-circle-outline' : 'mdi:play-circle-outline'}"></span>
                                ${toggleLabel}
                            </button>
                        </div>
                    </div>
                </div>
            `;
        }).join('');
    }

    function loadAttendanceLocations() {
        const list = document.getElementById('locationList');
        list.innerHTML = `
            <div class="rounded-xl border border-slate-100 bg-slate-50 px-3 py-8 text-center text-[10px] font-medium text-slate-400">
                <span class="iconify mx-auto mb-2 text-2xl animate-spin opacity-50" data-icon="mdi:loading"></span>
                Loading locations...
            </div>
        `;

        fetch('/api/attendance/locations', { credentials: 'same-origin' })
            .then((response) => response.json())
            .then((result) => {
                if (!result.success) {
                    throw new Error(result.message || 'Unable to load locations');
                }

                const payload = result.data || {};
                locationState.locations = Array.isArray(payload.locations) ? payload.locations : [];
                locationState.currentLocation = payload.current_location || null;
                renderLocationList(locationState.locations);
                setCurrentLocation(locationState.currentLocation);

                // Auto-show the active zone on the map if one exists and the form is untouched
                const idField = document.getElementById('locationId');
                if (locationState.currentLocation && !idField.value) {
                    populateLocationForm(locationState.currentLocation);
                } else {
                    renderLocationPreview();
                }
            })
            .catch((error) => {
                list.innerHTML = `
                    <div class="rounded-xl border border-rose-100 bg-rose-50 px-3 py-8 text-center text-[10px] font-bold text-rose-600">
                        ${escapeHtml(error.message || 'Could not load locations.')}
                    </div>
                `;
                setCurrentLocation(null);
            });
    }

    function editLocation(id) {
        const location = locationState.locations.find((item) => String(item.id) === String(id));
        if (!location) {
            window.Toast?.error('Location not found', 'The selected location could not be loaded.');
            return;
        }

        populateLocationForm(location);
        renderLocationPreview();
        const nameInput = document.getElementById('locationName');
        nameInput.focus();
    }

    async function submitLocationForm() {
        const saveButton = document.getElementById('saveLocationButton');
        const id = document.getElementById('locationId').value.trim();
        const isEditMode = Boolean(id);
        const coordinates = normalizeCoordinatePair(document.getElementById('locationCoordinates').value);

        if (!coordinates.valid) {
            setLocationFormError(coordinates.error);
            return;
        }

        const payload = {
            name: document.getElementById('locationName').value.trim(),
            latitude: coordinates.latitude,
            longitude: coordinates.longitude,
            radius: document.getElementById('locationRadius').value.trim(),
            status: document.getElementById('locationStatus').value.trim(),
        };

        saveButton.disabled = true;
        saveButton.classList.add('opacity-50', 'pointer-events-none');
        saveButton.innerHTML = '<span class="iconify text-[14px] shrink-0 animate-spin" data-icon="mdi:loading"></span> Saving...';
        setLocationFormError();

        try {
            const response = await fetch(
                id ? `/api/attendance/locations/${id}` : '/api/attendance/locations',
                {
                    method: id ? 'PUT' : 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify(payload),
                }
            );

            const result = await response.json();
            if (!result.success) {
                const errors = result.errors || {};
                setLocationFormError(result.message || 'Unable to save location.', errors);
                return;
            }

            window.Toast?.success('Success', result.message || 'Attendance location saved.');
            resetLocationForm();
            renderLocationPreview();
            await loadAttendanceLocations();
        } catch (error) {
            setLocationFormError(error.message || 'Server error. Please try again.');
            window.Toast?.error('Error', 'Unable to save attendance location.');
        } finally {
            saveButton.disabled = false;
            saveButton.classList.remove('opacity-50', 'pointer-events-none');
            saveButton.innerHTML = isEditMode
                ? '<span class="iconify text-[14px] shrink-0" data-icon="mdi:content-save-outline"></span> Update Location'
                : '<span class="iconify text-[14px] shrink-0" data-icon="mdi:content-save-outline"></span> Save Location';
        }
    }

    function fillCurrentLocation() {
        if (!navigator.geolocation) {
            setLocationFormError('Your browser does not support current location. Please enter valid coordinates. Example: 11.580914, 104.909832');
            return;
        }

        navigator.geolocation.getCurrentPosition(
            (position) => {
                const latitude = Number(position.coords.latitude);
                const longitude = Number(position.coords.longitude);
                if (!Number.isFinite(latitude) || !Number.isFinite(longitude)) {
                    setLocationFormError('Could not read your current location. Please enter valid coordinates. Example: 11.580914, 104.909832');
                    return;
                }

                const formattedLat = latitude.toFixed(6);
                const formattedLng = longitude.toFixed(6);

                document.getElementById('locationCoordinates').value = `${formattedLat}, ${formattedLng}`;
                document.getElementById('locationName').value = `Location ${formattedLat}, ${formattedLng}`;
                setLocationFormError();
                renderLocationPreview();
                window.Toast?.success('Location added', 'Current coordinates filled into the form.');
            },
            () => {
                setLocationFormError('Could not access your current location. Please enter valid coordinates. Example: 11.580914, 104.909832');
            },
            { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
        );
    }

    async function toggleLocationStatus(id, nextStatus) {
        const location = locationState.locations.find((item) => String(item.id) === String(id));
        if (!location) {
            window.Toast?.error('Location not found', 'Please refresh the location list and try again.');
            return;
        }

        const confirmMessage = nextStatus === 'inactive'
            ? `Deactivate ${location.name || 'this location'}?`
            : `Activate ${location.name || 'this location'}?`;

        if (!window.confirm(confirmMessage)) {
            return;
        }

        try {
            const options = {
                method: nextStatus === 'inactive' ? 'DELETE' : 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                credentials: 'same-origin',
            };

            if (nextStatus !== 'inactive') {
                options.body = JSON.stringify({ status: 'active' });
            }

            const response = await fetch(`/api/attendance/locations/${id}`, options);

            const result = await response.json();
            if (!result.success) {
                throw new Error(result.message || 'Unable to update status');
            }

            window.Toast?.success('Success', result.message || 'Location status updated.');
            await loadAttendanceLocations();
        } catch (error) {
            window.Toast?.error('Error', error.message || 'Unable to update location status.');
        }
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

    function downloadQR() { generateQRCard(800, c => {  const a = document.createElement('a'); a.download = 'attendance-qr.png'; a.href = c.toDataURL(); a.click(); }); }
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
    ['locationName', 'locationCoordinates', 'locationRadius'].forEach((fieldId) => {
        const field = document.getElementById(fieldId);
        if (field) {
            field.addEventListener('input', renderLocationPreview);
            field.addEventListener('change', renderLocationPreview);
        }
    });

    const locationStatus = document.getElementById('locationStatus');
    if (locationStatus) {
        locationStatus.addEventListener('change', renderLocationPreview);
    }

    // Backdrop click to close
    document.getElementById('qrModal').addEventListener('click', (e) => {
        if (e.target.id === 'qrModal') closeQRModal();
    });
    document.getElementById('locationModal').addEventListener('click', (e) => {
        if (e.target.id === 'locationModal') closeLocationModal();
    });

    document.addEventListener('keydown', (event) => {
        if (event.key !== 'Escape') {
            return;
        }

        const qrModal = document.getElementById('qrModal');
        const locationModal = document.getElementById('locationModal');

        if (!qrModal.classList.contains('hidden')) {
            closeQRModal();
        }

        if (!locationModal.classList.contains('hidden')) {
            closeLocationModal();
        }
    });
    
    // Set default filter to today
    document.getElementById('dateFilter').value = getCurrentDateString();
    loadAttendance();
    renderLocationPreview();
</script>
