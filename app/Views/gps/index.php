<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php
  $title = $title ?? 'GPS Tracker';
  $fleet = $fleet ?? [];
  $online_count = $online_count ?? 0;
  $offline_count = $offline_count ?? 0;
  $transit_count = $transit_count ?? 0;
  $maint_count = $maint_count ?? 0;
  $total = $total ?? 0;
  $vehicles = $vehicles ?? [];
?>

<link rel="stylesheet" href="<?= base_url('Assets/css/gps.css') . '?v=' . @filemtime(FCPATH.'Assets/css/gps.css') ?>">

<!-- Leaflet — renders the vehicle detail modal's live map (satellite/hybrid
     via free Esri tiles, no API key). Same "pull a small library from CDN"
     pattern this app already uses for Chart.js in layouts/main.php. -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.css">
<script src="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.js"></script>

<div class="gps-wrapper">

    <!-- ── PAGE HEADER ──────────────────────────────────────────── -->
    <div class="page-header">
        <div>
            <h1>GPS Tracker</h1>
            <p class="page-subtitle">Live vehicle locations, GPS status, and fleet movement history.</p>
        </div>
        <div class="header-actions">
            <span class="live-badge"><span class="pulse-dot"></span> Live Feed</span>
            <button class="btn-outline" onclick="refreshAll()">
                <i class="bi bi-arrow-clockwise"></i> Refresh All
            </button>
            <button class="btn-outline" onclick="toggleMapView()">
                <i class="bi bi-map"></i> <span id="mapToggleLabel">Show Map</span>
            </button>
        </div>
    </div>

    <!-- ── SUMMARY CARDS ────────────────────────────────────────── -->
    <div class="stat-cards">
        <div class="stat-card stat-card-clickable" onclick="filterGpsByStat('total')" role="button" tabindex="0">
            <span class="stat-icon tone-maroon"><i class="bi bi-truck"></i></span>
            <h3>Total Vehicles</h3>
            <div class="value"><?= $total ?></div>
        </div>
        <div class="stat-card stat-card-clickable" onclick="filterGpsByStat('online')" role="button" tabindex="0">
            <span class="stat-icon tone-green"><i class="bi bi-reception-4"></i></span>
            <h3>GPS Online</h3>
            <div class="value"><?= $online_count ?></div>
        </div>
        <div class="stat-card stat-card-clickable" onclick="filterGpsByStat('offline')" role="button" tabindex="0">
            <span class="stat-icon tone-neutral"><i class="bi bi-broadcast"></i></span>
            <h3>GPS Offline</h3>
            <div class="value"><?= $offline_count ?></div>
        </div>
        <div class="stat-card stat-card-clickable" onclick="filterGpsByStat('transit')" role="button" tabindex="0">
            <span class="stat-icon tone-gold"><i class="bi bi-signpost-split"></i></span>
            <h3>In Transit</h3>
            <div class="value"><?= $transit_count ?></div>
        </div>
        <div class="stat-card stat-card-clickable" onclick="filterGpsByStat('maintenance')" role="button" tabindex="0">
            <span class="stat-icon tone-red"><i class="bi bi-wrench-adjustable"></i></span>
            <h3>Under Maintenance</h3>
            <div class="value"><?= $maint_count ?></div>
        </div>
    </div>

    <!-- ── MAP PANEL (hidden by default, toggled) ──────────────── -->
    <div id="mapPanel" class="map-panel" style="display:none;">
        <div class="map-header">
            <span><i class="bi bi-map-fill"></i> Live Map View</span>
            <span class="map-note">GPS coordinates display — connect Google Maps API key in Settings for full map.</span>
        </div>
        <div class="map-placeholder" id="mapContainer">
            <div class="map-grid-bg"></div>
            <div class="map-center-label">
                <i class="bi bi-geo-alt-fill" style="font-size:2.5rem;color:var(--maroon)"></i>
                <p>Live map renders here once Google Maps API key is configured in <strong>Settings → GPS API</strong>.</p>
                <p class="map-note-sub">Vehicle pins are plotted from GPS log coordinates in real time.</p>
            </div>
            <!-- Vehicle pins overlay -->
            <div id="vehiclePins" class="vehicle-pins"></div>
        </div>
    </div>

    <!-- ── Body: Fleet Table ──────────────────────────────────────── -->
    <div class="gps-body">

        <!-- Fleet Table -->
        <div class="table-panel">
            <div class="table-toolbar">
                <h2 class="panel-title">Fleet GPS Status</h2>
                <div class="toolbar-right">
                    <div class="toolbar-search">
                      <input type="text" id="searchInput" class="search-box" placeholder="Search plate / driver..." oninput="filterTable()">
                      <i class="bi bi-search search-icon"></i>
                    </div>
                    <div class="filter-menu-wrapper">
                      <button type="button" class="filter-btn" onclick="toggleGpsFilterMenu()" aria-label="Open filters">
                        <i class="bi bi-funnel"></i>
                      </button>
                      <div class="filter-popup" id="gpsFilterPopup">
                        <div class="filter-popup-title">Filter</div>
                        <div class="filter-row">
                          <label for="statusFilter">GPS Status</label>
                          <select id="statusFilter" onchange="filterTable()">
                            <option value="">All GPS Status</option>
                            <option value="Online">Online</option>
                            <option value="Offline">Offline</option>
                          </select>
                        </div>
                        <div class="filter-row">
                          <label for="availFilter">Availability</label>
                          <select id="availFilter" onchange="filterTable()">
                            <option value="">All Availability</option>
                            <option value="Available">Available</option>
                            <option value="In Use">In Use</option>
                            <option value="Reserved">Reserved</option>
                            <option value="Maintenance">Maintenance</option>
                          </select>
                        </div>
                        <div class="filter-row">
                          <label id="gpsSortLabel">Sort By</label>
                          <div class="dd-select" id="gpsSortDD" data-onchange="applyGpsSort">
                            <button type="button" class="dd-select-trigger" onclick="toggleDDSelect('gpsSortDD')" aria-haspopup="listbox" aria-expanded="false">
                              <span class="dd-select-value">Default</span>
                              <i class="bi bi-chevron-down"></i>
                            </button>
                            <div class="dd-select-menu" role="listbox">
                              <div class="dd-select-option selected" data-value="" role="option">Default</div>
                              <div class="dd-select-option" data-value="vehicle-asc" role="option">Vehicle (A&ndash;Z)</div>
                              <div class="dd-select-option" data-value="vehicle-desc" role="option">Vehicle (Z&ndash;A)</div>
                              <div class="dd-select-option" data-value="3-asc" role="option">Driver (A&ndash;Z)</div>
                              <div class="dd-select-option" data-value="3-desc" role="option">Driver (Z&ndash;A)</div>
                              <div class="dd-select-option" data-value="5-asc" role="option">GPS Status (A&ndash;Z)</div>
                              <div class="dd-select-option" data-value="5-desc" role="option">GPS Status (Z&ndash;A)</div>
                              <div class="dd-select-option" data-value="7-asc" role="option">Availability (A&ndash;Z)</div>
                              <div class="dd-select-option" data-value="7-desc" role="option">Availability (Z&ndash;A)</div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                </div>
            </div>

            <div class="table-scroll">
                <table class="gps-table" id="gpsTable">
                    <thead>
                        <tr>
                            <th>Vehicle</th>
                            <th>Plate No.</th>
                            <th>Type</th>
                            <th>Driver</th>
                            <th>Dept.</th>
                            <th>GPS</th>
                            <th>Inspection</th>
                            <th>Availability</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($fleet)): ?>
                            <tr><td colspan="9" class="empty-row">No vehicles found. Add vehicles in Vehicle Management.</td></tr>
                        <?php else: ?>
                            <?php foreach ($fleet as $v): ?>
                            <?php
                                $gpsOnline  = $v['gps_status'] === 'Online';
                                $availClass = match($v['availability'] ?? 'Available') {
                                    'In Use'             => 'avail-inuse',
                                    'Reserved'           => 'avail-reserved',
                                    'Under Maintenance'  => 'avail-maint',
                                    default              => 'avail-available',
                                };
                                $inspClass = match($v['inspection_status'] ?? 'Pending') {
                                    'Completed' => 'insp-ok',
                                    'Expired'   => 'insp-expired',
                                    default     => 'insp-pending',
                                };
                            ?>
                            <tr
                                data-gps="<?= esc($v['gps_status']) ?>"
                                data-avail="<?= esc($v['availability'] ?? 'Available') ?>"
                                data-search="<?= strtolower(esc($v['plate_no'] ?? '') . ' ' . esc($v['driver_name'] ?? '')) ?>"
                                onclick="openVehicleModal(<?= $v['id'] ?>)"
                                class="fleet-row"
                            >
                                <td>
                                    <div class="vehicle-cell">
                                        <div class="vehicle-icon"><i class="bi bi-truck-front-fill"></i></div>
                                        <div>
                                            <div class="v-model"><?= esc($v['model'] ?? 'Unknown') ?></div>
                                            <div class="v-sub"><?= esc($v['plate_no'] ?? '—') ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="mono"><?= esc($v['plate_no'] ?? '—') ?></td>
                                <td><?= esc($v['type'] ?? '—') ?></td>
                                <td><?= esc($v['driver_name'] ?? 'Unassigned') ?></td>
                                <td><?= esc($v['department'] ?? '—') ?></td>
                                <td>
                                    <span class="gps-badge <?= $gpsOnline ? 'gps-online' : 'gps-offline' ?>">
                                        <span class="<?= $gpsOnline ? 'pulse-dot' : 'dead-dot' ?>"></span>
                                        <?= $gpsOnline ? 'Online' : 'Offline' ?>
                                    </span>
                                    <?php if ($v['logged_at']): ?>
                                        <div class="gps-time"><?= timeAgo($v['logged_at']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="insp-badge <?= $inspClass ?>">
                                        <?= esc($v['inspection_status'] ?? 'Pending') ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="avail-badge <?= $availClass ?>">
                                        <?= esc($v['availability'] ?? 'Available') ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-btns">
                                        <button class="icon-btn sync" title="Sync GPS"
                                            onclick="event.stopPropagation(); syncVehicle(<?= $v['id'] ?>, this)">
                                            <i class="bi bi-arrow-clockwise"></i>
                                        </button>
                                        <a class="icon-btn map" title="Open in Maps"
                                            href="https://www.google.com/maps?q=<?= $v['latitude'] ?? '9.3164' ?>,<?= $v['longitude'] ?? '123.2885' ?>"
                                            target="_blank" onclick="event.stopPropagation()">
                                            <i class="bi bi-geo-alt"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Vehicle Profile Modal -->
<div class="modal" id="vehicleProfileModal">
    <div class="modal-box vehicle-modal-box">
        <div id="vehicleProfileContent">
            <!-- Content will be injected here -->
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════
     SCRIPTS
════════════════════════════════════════════════════════════════ -->
<script>
// ── PHP helper exposed to JS ───────────────────────────────────
const GPS_AJAX_BASE = '<?= base_url('gps/getVehicle/') ?>';
const SYNC_BASE     = '<?= base_url('gps/sync/') ?>';

// Tracks which vehicle the popup currently shows and its live-refresh timer,
// so a poll response arriving after the user closed the popup (or opened a
// different vehicle) knows to discard itself instead of overwriting the
// wrong content.
let gpsModalVehicleId = null;
let gpsModalPollTimer = null;

function openVehicleModal(id) {
    const modal = document.getElementById('vehicleProfileModal');
    const content = document.getElementById('vehicleProfileContent');

    gpsModalVehicleId = id;
    content.innerHTML = `<div class="sidebar-loading"><i class="bi bi-hourglass-split"></i> Loading GPS profile...</div>`;
    modal.classList.add('open');
    // The popup itself already scrolls internally if it needs to (.modal-body)
    // — without this, the page behind it stays scrollable too, so a second,
    // confusing scrollbar shows up at the edge of the browser window.
    document.body.style.overflow = 'hidden';

    fetchVehicleProfile(id, false);

    // Keep the map/details current while the popup stays open — a tracker
    // can come online or send a new fix at any moment, and without this the
    // popup would just freeze on whatever was true the instant it opened.
    if (gpsModalPollTimer) clearInterval(gpsModalPollTimer);
    gpsModalPollTimer = setInterval(() => fetchVehicleProfile(id, true), 15000);
}

function fetchVehicleProfile(id, isPoll) {
    fetch(GPS_AJAX_BASE + id)
        .then(r => r.json())
        .then(v => {
            // The popup may have been closed, or switched to a different
            // vehicle, while this request was in flight.
            if (gpsModalVehicleId !== id) return;
            if (isPoll) {
                updateModalLiveData(v);
            } else {
                renderModalProfile(v);
            }
        })
        .catch(() => {
            // A poll tick failing silently is fine — a brief network hiccup
            // shouldn't blow away an otherwise-working popup with an error
            // screen. Only the very first load shows the error state.
            if (!isPoll) {
                document.getElementById('vehicleProfileContent').innerHTML =
                    '<div class="sidebar-error"><i class="bi bi-exclamation-triangle-fill"></i> Failed to load vehicle data.</div>';
            }
        });
}

function closeVehicleModal() {
    const modal = document.getElementById('vehicleProfileModal');
    modal.classList.remove('open');
    document.body.style.overflow = '';
    resetRoutePlayback(); // stops its setInterval before the map/layer it points at disappears below
    if (gpsMapInstance) { gpsMapInstance.remove(); gpsMapInstance = null; }
    gpsMapMarker = null;
    gpsRouteLayer = null;
    if (gpsModalPollTimer) { clearInterval(gpsModalPollTimer); gpsModalPollTimer = null; }
    gpsModalVehicleId = null;
}

function renderModalProfile(v) {
    const online     = v.gps_status === 'Online';
    // v.signal already comes formatted (e.g. "Strong (98%)") — it's not a
    // raw number to re-derive a label/percentage from.
    const signalText = v.signal || 'No signal data';

    // Embedded live map — a real Leaflet map (initGpsMap, called after this
    // HTML is inserted below) rather than an iframe. Both Google's and
    // OpenStreetMap's *iframe* embeds only offer plain street tiles; getting
    // satellite/hybrid without an iframe's cross-origin blank-box problems
    // means drawing our own map instead, so it can layer Esri's free,
    // keyless "World Imagery" satellite tiles under road/label tiles.
    const mapLat = parseFloat(v.latitude) || 9.3164;
    const mapLon = parseFloat(v.longitude) || 123.2885;

    const pings = v.recent_pings || [];
    const pingRows = pings.length
        ? pings.map(p => `<tr><td>${p.loggedAt ? timeAgoJS(p.loggedAt) : '—'}</td><td>${esc(p.coords)}</td><td>${esc(p.signal)}</td><td>${esc(p.status)}</td></tr>`).join('')
        : `<tr><td colspan="4">No GPS pings recorded yet.</td></tr>`;

    // Defaults the Route History date pickers to today.
    const pad2 = n => String(n).padStart(2, '0');
    const nowD = new Date();
    const dayStr = `${nowD.getFullYear()}-${pad2(nowD.getMonth() + 1)}-${pad2(nowD.getDate())}`;
    const fromDefault = dayStr + 'T00:00';
    const toDefault = `${dayStr}T${pad2(nowD.getHours())}:${pad2(nowD.getMinutes())}`;

    // Same wide-popup layout as the Vehicle Management / Personnel Management
    // detail popups: maroon header + × only, then labeled sections in a
    // two-column detail grid, plus a history table — kept visually
    // consistent across all three instead of this page having its own look.
    document.getElementById('vehicleProfileContent').innerHTML = `
    <div class="modal-header">
        <h3>${esc(v.model || 'Unknown Vehicle')} (${esc(v.plate_no || '—')})</h3>
        <div class="modal-header-actions">
            <button class="modal-close-btn" onclick="window.location.href='<?= base_url('vehicles') ?>?edit=' + ${v.id}" aria-label="Edit vehicle">
                <i class="bi bi-pencil-fill"></i>
            </button>
            <button class="modal-close-btn" onclick="closeVehicleModal()" aria-label="Close">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
    </div>
    <div class="modal-body">
        <div class="detail-section">
            <div class="detail-section-title">Route History</div>
            <div class="route-history-controls">
                <label>From <input type="datetime-local" id="routeFromDate" value="${fromDefault}" max="${toDefault}"></label>
                <label>To <input type="datetime-local" id="routeToDate" value="${toDefault}" max="${toDefault}"></label>
                <button type="button" class="btn-outline-sm" onclick="showVehicleRoute(${v.id})">
                    <i class="bi bi-signpost-2"></i> Show Route
                </button>
                <button type="button" class="btn-outline-sm" id="gpsRouteClearBtn" onclick="clearVehicleRoute()" style="display:none;">
                    <i class="bi bi-x-lg"></i> Clear
                </button>
            </div>
            <div id="gpsRouteStatus" class="route-history-status"></div>

            <!-- Shown once a route with 2+ points loads (see drawRouteOnMap).
                 Moves a marker point-to-point along the recorded pings in
                 order, so you can watch/scrub where the vehicle was at each
                 exact timestamp instead of just seeing the finished path. -->
            <div id="gpsPlaybackControls" class="route-playback-controls" style="display:none;">
                <button type="button" class="icon-btn" id="gpsPlaybackToggleBtn" onclick="toggleRoutePlayback()" title="Play" aria-label="Play route playback">
                    <i class="bi bi-play-fill"></i>
                </button>
                <input type="range" id="gpsPlaybackSlider" min="0" max="0" value="0" step="1" oninput="scrubRoutePlayback(this.value)">
                <select id="gpsPlaybackSpeed" onchange="setRoutePlaybackSpeed(this.value)" title="Playback speed">
                    <option value="1400">0.5×</option>
                    <option value="700" selected>1×</option>
                    <option value="350">2×</option>
                    <option value="150">4×</option>
                </select>
            </div>
            <div id="gpsPlaybackTimestamp" class="route-playback-timestamp"></div>
        </div>

        <!-- Embedded live map — loads automatically with the vehicle's
             last known coordinates every time this modal opens, instead
             of requiring a click out to a separate Maps tab. Placed first
             so it's the first thing visible on open, above the detail
             sections. Defaults to satellite/hybrid (initGpsMap, called
             right after this HTML is inserted, since Leaflet needs the
             div to already be in the DOM). -->
        <div class="gps-embed-wrap gps-embed-wrap-top">
            <div id="gpsEmbedMap" class="gps-embed-map gps-embed-map-top"></div>
        </div>
        <div class="sidebar-btn-row" style="margin:.6rem 0 1rem;">
            <a id="gpsGoogleMapsLink" class="btn-outline-sm" href="https://www.google.com/maps?q=${mapLat},${mapLon}&t=k" target="_blank">
                <i class="bi bi-box-arrow-up-right"></i> Open in Google Maps
            </a>
        </div>

        <div class="detail-section">
            <div class="detail-section-title">Vehicle Details</div>
            <div class="detail-grid">
                <div class="detail-row"><span>Plate Number</span><strong>${esc(v.plate_no || '—')}</strong></div>
                <div class="detail-row"><span>Type</span><strong>${esc(v.type || '—')}</strong></div>
                <div class="detail-row"><span>Driver</span><strong>${esc(v.driver_name || 'Unassigned')}</strong></div>
                <div class="detail-row"><span>Department</span><strong>${esc(v.department_name || 'Unassigned')}</strong></div>
                <div class="detail-row"><span>Availability</span><strong>${esc(v.availability || 'Available')}</strong></div>
                <div class="detail-row"><span>Inspection</span><strong>${esc(v.inspection_status || '—')}</strong></div>
                <div class="detail-row"><span>Tire Pressure</span><strong>${v.tire_pressure_psi != null && v.tire_pressure_psi !== '' ? esc(v.tire_pressure_psi) + ' PSI' : 'Not recorded'}</strong></div>
            </div>
        </div>

        <div class="detail-section">
            <div class="detail-section-title" id="gpsSectionTitle">GPS Live Tracking — ${online ? 'Connected' : 'Offline'}</div>
            <div class="detail-grid">
                <div class="detail-row"><span>Device ID</span><strong>${esc(v.device_id || 'N/A')}</strong></div>
                <div class="detail-row"><span>Last Updated</span><strong id="gpsLastUpdated">${v.logged_at ? timeAgoJS(v.logged_at) : '—'}</strong></div>
                <div class="detail-row"><span>Current Speed</span><strong id="gpsCurrentSpeed">${v.speed || 0} km/h</strong></div>
                <div class="detail-row"><span>Coordinates</span><strong id="gpsCoordinates">${v.latitude ? v.latitude + ', ' + v.longitude : 'N/A'}</strong></div>
                <div class="detail-row"><span>Signal Strength</span><strong id="gpsSignalStrength">${esc(signalText)}</strong></div>
            </div>
        </div>

        <div class="detail-section">
            <div class="detail-section-title">Recent Pings</div>
            <div class="history-table-wrap">
                <table class="history-table">
                    <thead><tr><th>Logged</th><th>Coordinates</th><th>Signal</th><th>Status</th></tr></thead>
                    <tbody id="gpsPingRows">${pingRows}</tbody>
                </table>
            </div>
        </div>
    </div>
    `;

    // The map div above only just got inserted into the DOM by the
    // innerHTML assignment, so Leaflet can't be initialized until now.
    initGpsMap(mapLat, mapLon, v.plate_no);
}

// ── Live map: satellite/hybrid via Leaflet + Esri's free tiles ─────
// Esri's "World Imagery" service is satellite/aerial imagery with no API
// key required for this kind of light, non-commercial usage. Stacking
// CartoDB's OSM-based labels tiles on top adds the roads/labels, which is
// what turns plain satellite into "hybrid".
let gpsMapInstance = null;
let gpsMapMarker = null; // kept so updateModalLiveData() can move the pin on each poll instead of rebuilding the whole map
let gpsRouteLayer = null; // the currently-drawn Route History path + point markers, if any (see showVehicleRoute)

function initGpsMap(lat, lon, plateNo) {
    const mapEl = document.getElementById('gpsEmbedMap');
    if (!mapEl || typeof L === 'undefined') return;

    // Reusing the same #gpsEmbedMap id across modal opens (different
    // vehicles) — Leaflet throws if you call L.map() on a container that
    // already has a map, so the previous instance must be torn down first.
    if (gpsMapInstance) {
        gpsMapInstance.remove();
        gpsMapInstance = null;
    }
    gpsRouteLayer = null; // belonged to the map instance just destroyed above

    gpsMapInstance = L.map(mapEl, {
        // Zoom 16, not 17 — Esri's free label layer had virtually no
        // road/place data around campus at 17 (a rural stretch of Negros
        // Oriental), so the overlay rendered blank even though it was
        // loading successfully. 16 is where CartoDB's OSM-based labels
        // below actually have content.
        center: [lat, lon],
        zoom: 16,
        attributionControl: false,
    });

    L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
        maxZoom: 19,
        attribution: 'Tiles &copy; Esri',
    }).addTo(gpsMapInstance);

    // Roads/place-name labels on top of the imagery — this combination is
    // the "hybrid" look. Uses CartoDB's transparent-background labels
    // layer (built from OpenStreetMap data) instead of Esri's own
    // reference layer: OSM's community mapping covers rural areas like
    // this one far better than Esri's, which was returning empty tiles
    // here. maxNativeZoom lets Leaflet upscale the zoom-16 tile if the
    // user zooms in further, instead of requesting nonexistent zoom-17+
    // tiles and going blank again.
    L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager_only_labels/{z}/{x}/{y}{r}.png', {
        subdomains: 'abcd',
        maxZoom: 19,
        maxNativeZoom: 16,
        pane: 'overlayPane',
        attribution: '&copy; OpenStreetMap contributors &copy; CARTO',
    }).addTo(gpsMapInstance);

    // Red pin instead of Leaflet's stock blue marker — an inline SVG
    // teardrop (no external image request, so nothing to fail to load)
    // styled after Google Maps' classic red pin.
    const redPinIcon = L.divIcon({
        className: 'gps-pin-icon',
        html: '<svg width="27" height="38" viewBox="0 0 27 38" xmlns="http://www.w3.org/2000/svg">'
            + '<path d="M13.5 0C6.04 0 0 6.04 0 13.5 0 23.6 13.5 38 13.5 38S27 23.6 27 13.5C27 6.04 20.96 0 13.5 0z" fill="#EA4335"/>'
            + '<circle cx="13.5" cy="13.5" r="5.5" fill="#fff"/>'
            + '</svg>',
        iconSize: [27, 38],
        iconAnchor: [13.5, 38],
        popupAnchor: [0, -34],
    });

    gpsMapMarker = L.marker([lat, lon], { icon: redPinIcon }).addTo(gpsMapInstance)
        .bindPopup(esc(plateNo || 'Vehicle location'));

    // The modal (and this map div) render at 0×0 until the CSS transition
    // finishes opening it, so Leaflet's first size read is wrong — this
    // fixes the grey/cut-off tiles that would otherwise show until the
    // user manually pans or resizes.
    setTimeout(() => { if (gpsMapInstance) gpsMapInstance.invalidateSize(); }, 200);
}

// Called every poll tick (see fetchVehicleProfile) instead of
// renderModalProfile, so an open popup's numbers and pin position stay
// current without rebuilding the whole modal — that would flash, and would
// reset the map back to its default zoom/pan if the user had moved it.
function updateModalLiveData(v) {
    const online = v.gps_status === 'Online';
    const signalText = v.signal || 'No signal data';
    const mapLat = parseFloat(v.latitude) || 9.3164;
    const mapLon = parseFloat(v.longitude) || 123.2885;

    const sectionTitle = document.getElementById('gpsSectionTitle');
    if (sectionTitle) sectionTitle.textContent = 'GPS Live Tracking — ' + (online ? 'Connected' : 'Offline');

    const lastUpdatedEl = document.getElementById('gpsLastUpdated');
    if (lastUpdatedEl) lastUpdatedEl.textContent = v.logged_at ? timeAgoJS(v.logged_at) : '—';

    const speedEl = document.getElementById('gpsCurrentSpeed');
    if (speedEl) speedEl.textContent = (v.speed || 0) + ' km/h';

    const coordsEl = document.getElementById('gpsCoordinates');
    if (coordsEl) coordsEl.textContent = v.latitude ? (v.latitude + ', ' + v.longitude) : 'N/A';

    const signalEl = document.getElementById('gpsSignalStrength');
    if (signalEl) signalEl.textContent = signalText;

    const mapsLink = document.getElementById('gpsGoogleMapsLink');
    if (mapsLink) mapsLink.href = 'https://www.google.com/maps?q=' + mapLat + ',' + mapLon + '&t=k';

    const pingRowsEl = document.getElementById('gpsPingRows');
    if (pingRowsEl) {
        const pings = v.recent_pings || [];
        pingRowsEl.innerHTML = pings.length
            ? pings.map(p => `<tr><td>${p.loggedAt ? timeAgoJS(p.loggedAt) : '—'}</td><td>${esc(p.coords)}</td><td>${esc(p.signal)}</td><td>${esc(p.status)}</td></tr>`).join('')
            : '<tr><td colspan="4">No GPS pings recorded yet.</td></tr>';
    }

    // Move the pin, rather than recreating the map (which would flash and
    // undo any zoom/pan the user did by hand). Only auto-pan to it when a
    // Route History path isn't currently on screen — otherwise every poll
    // tick would yank the view back to the live pin and undo the route's
    // fitBounds, right as someone's looking at where the vehicle has been.
    if (gpsMapInstance && gpsMapMarker && v.latitude) {
        gpsMapMarker.setLatLng([mapLat, mapLon]);
        if (!gpsRouteLayer) gpsMapInstance.panTo([mapLat, mapLon], { animate: true });
    }
}

// ── Route History: draw a vehicle's past pings as a path on the map ────
// OSRM's free "driving directions between waypoints, in order" service
// (not its "map matching" one — that needs a dense, closely-spaced GPS
// trace to snap well, and pings here are often minutes to days apart).
// This instead asks "what's the likely road path from ping 1 to ping 2 to
// ping 3…", which is the sane approximation for sparse historical points —
// it's a best-guess route between real recorded fixes, not literally the
// exact road the vehicle drove.
const OSRM_ROUTE_BASE = 'https://router.project-osrm.org/route/v1/driving/';

function showVehicleRoute(id) {
    if (!gpsMapInstance) return;

    const fromEl = document.getElementById('routeFromDate');
    const toEl = document.getElementById('routeToDate');
    const statusEl = document.getElementById('gpsRouteStatus');
    const from = fromEl ? fromEl.value : '';
    const to = toEl ? toEl.value : '';

    if (statusEl) statusEl.innerHTML = '<i class="bi bi-hourglass-split"></i> Loading route…';

    const qs = new URLSearchParams();
    if (from) qs.set('from', from);
    if (to) qs.set('to', to);

    fetch('<?= base_url('gps/route/') ?>' + id + '?' + qs.toString())
        .then(r => r.json())
        .then(res => {
            clearVehicleRoute(); // remove any previously-drawn route first

            const points = res.points || [];
            if (!points.length) {
                if (statusEl) statusEl.textContent = 'No GPS pings recorded in that range.' + (res.note ? ' ' + res.note : '');
                return;
            }
            if (points.length === 1) {
                // Nothing to route between a single point — just show it.
                drawRouteOnMap(points, null, false);
                return;
            }

            // OSRM wants "lon,lat;lon,lat;…" (opposite order from ours/
            // Leaflet's lat,lng), and needs at least 2 waypoints to route.
            const coordStr = points.map(p => p.lng + ',' + p.lat).join(';');

            fetch(OSRM_ROUTE_BASE + coordStr + '?overview=full&geometries=geojson')
                .then(r => r.json())
                .then(osrm => {
                    const geom = osrm && osrm.code === 'Ok' && osrm.routes && osrm.routes[0]
                        ? osrm.routes[0].geometry.coordinates
                        : null;
                    // GeoJSON is [lng, lat] — flip to Leaflet's [lat, lng].
                    const roadLatlngs = geom ? geom.map(c => [c[1], c[0]]) : null;
                    drawRouteOnMap(points, roadLatlngs, !roadLatlngs);
                })
                .catch(() => drawRouteOnMap(points, null, true)); // road lookup failed — fall back to straight lines
        })
        .catch(() => {
            if (statusEl) statusEl.innerHTML = '<i class="bi bi-exclamation-triangle-fill"></i> Failed to load route.';
        });
}

// Draws the already-fetched route: roadLatlngs (from OSRM) if available,
// otherwise a straight line directly between the recorded points. The
// timestamped circle markers always sit at the real recorded coordinates
// either way — only the connecting line differs.
function drawRouteOnMap(points, roadLatlngs, isFallback) {
    const statusEl = document.getElementById('gpsRouteStatus');
    const rawLatlngs = points.map(p => [p.lat, p.lng]);
    const lineLatlngs = roadLatlngs || rawLatlngs;

    gpsRouteLayer = L.layerGroup().addTo(gpsMapInstance);

    if (lineLatlngs.length > 1) {
        L.polyline(lineLatlngs, { color: '#800000', weight: 3, opacity: 0.85 }).addTo(gpsRouteLayer);
    }

    // Start = maroon, in-between stops = red, end = green — same
    // "where did it begin/end" convention as a route on Google/Waze.
    points.forEach((p, i) => {
        const isStart = i === 0;
        const isEnd = i === points.length - 1;
        L.circleMarker([p.lat, p.lng], {
            radius: (isStart || isEnd) ? 6 : 4,
            color: '#fff',
            weight: 1.5,
            fillColor: isEnd ? '#2e7d32' : (isStart ? '#800000' : '#EA4335'),
            fillOpacity: 1,
        }).bindPopup(
            '<strong>' + (isStart ? 'Start' : (isEnd ? 'End' : 'Point ' + (i + 1))) + '</strong><br>'
            + esc(new Date(p.loggedAt).toLocaleString()) + '<br>'
            + 'Signal: ' + esc(p.signal) + '<br>'
            + 'Status: ' + esc(p.status)
        ).addTo(gpsRouteLayer);
    });

    gpsMapInstance.fitBounds(L.polyline(rawLatlngs).getBounds(), { padding: [24, 24] });

    if (statusEl) {
        let text = points.length + ' point' + (points.length === 1 ? '' : 's');
        if (points.length > 1) {
            text += ', ' + new Date(points[0].loggedAt).toLocaleString() + ' → ' + new Date(points[points.length - 1].loggedAt).toLocaleString();
        }
        if (isFallback && points.length > 1) {
            text += ' (straight-line — road route unavailable)';
        }
        statusEl.textContent = text;
    }
    const clearBtn = document.getElementById('gpsRouteClearBtn');
    if (clearBtn) clearBtn.style.display = '';

    initRoutePlayback(points);
}

function clearVehicleRoute() {
    if (gpsRouteLayer && gpsMapInstance) {
        gpsMapInstance.removeLayer(gpsRouteLayer);
    }
    gpsRouteLayer = null;

    const statusEl = document.getElementById('gpsRouteStatus');
    if (statusEl) statusEl.textContent = '';
    const clearBtn = document.getElementById('gpsRouteClearBtn');
    if (clearBtn) clearBtn.style.display = 'none';

    resetRoutePlayback();
}

// ── Route playback: step/scrub a marker through the recorded pings ─────
// gpsPlaybackMarker and gpsPlaybackTraveled are added to gpsRouteLayer (so
// clearVehicleRoute's removeLayer() above wipes them visually too), but
// kept in their own variables as well since — unlike the static route
// markers — these need their position updated on every step.
let gpsPlaybackPoints = [];
let gpsPlaybackIndex = 0;
let gpsPlaybackTimer = null;
let gpsPlaybackIntervalMs = 700;
let gpsPlaybackMarker = null;
let gpsPlaybackTraveled = null;

function initRoutePlayback(points) {
    const controls = document.getElementById('gpsPlaybackControls');
    const slider = document.getElementById('gpsPlaybackSlider');
    const timestampEl = document.getElementById('gpsPlaybackTimestamp');
    if (!controls || !slider) return;

    gpsPlaybackPoints = points;
    gpsPlaybackIndex = 0;

    if (points.length < 2) {
        // Nothing to play through with just one point — hide playback
        // entirely rather than showing a slider that can't move.
        controls.style.display = 'none';
        if (timestampEl) timestampEl.textContent = '';
        return;
    }

    slider.min = 0;
    slider.max = points.length - 1;
    slider.value = 0;
    controls.style.display = 'flex';

    const playbackIcon = document.querySelector('#gpsPlaybackToggleBtn i');
    if (playbackIcon) playbackIcon.className = 'bi bi-play-fill';

    const playbackDot = L.divIcon({
        className: 'gps-playback-icon',
        html: '<span></span>',
        iconSize: [16, 16],
        iconAnchor: [8, 8],
    });
    gpsPlaybackMarker = L.marker([points[0].lat, points[0].lng], { icon: playbackDot, zIndexOffset: 1000 }).addTo(gpsRouteLayer);
    gpsPlaybackTraveled = L.polyline([[points[0].lat, points[0].lng]], { color: '#1c6dd0', weight: 4, opacity: 0.9 }).addTo(gpsRouteLayer);

    updatePlaybackTimestamp(0);
}

function updatePlaybackTimestamp(index) {
    const timestampEl = document.getElementById('gpsPlaybackTimestamp');
    const p = gpsPlaybackPoints[index];
    if (timestampEl && p) {
        timestampEl.innerHTML = '<i class="bi bi-clock-history"></i> ' + esc(new Date(p.loggedAt).toLocaleString());
    }
}

function toggleRoutePlayback() {
    if (gpsPlaybackTimer) {
        pauseRoutePlayback();
        return;
    }
    if (!gpsPlaybackPoints.length) return;

    // Restart from the beginning once it's already run to the end.
    if (gpsPlaybackIndex >= gpsPlaybackPoints.length - 1) {
        gpsPlaybackIndex = 0;
        jumpPlaybackTo(0);
    }

    const icon = document.querySelector('#gpsPlaybackToggleBtn i');
    if (icon) icon.className = 'bi bi-pause-fill';

    gpsPlaybackTimer = setInterval(stepRoutePlayback, gpsPlaybackIntervalMs);
}

function pauseRoutePlayback() {
    if (gpsPlaybackTimer) { clearInterval(gpsPlaybackTimer); gpsPlaybackTimer = null; }
    const icon = document.querySelector('#gpsPlaybackToggleBtn i');
    if (icon) icon.className = 'bi bi-play-fill';
}

function stepRoutePlayback() {
    if (gpsPlaybackIndex >= gpsPlaybackPoints.length - 1) {
        pauseRoutePlayback();
        return;
    }
    gpsPlaybackIndex += 1;
    jumpPlaybackTo(gpsPlaybackIndex);
}

// Manual drag of the slider — jumps straight to that point and pauses
// auto-play, since a scrub is the user taking over control.
function scrubRoutePlayback(index) {
    pauseRoutePlayback();
    gpsPlaybackIndex = parseInt(index, 10) || 0;
    jumpPlaybackTo(gpsPlaybackIndex);
}

function setRoutePlaybackSpeed(ms) {
    gpsPlaybackIntervalMs = parseInt(ms, 10) || 700;
    if (gpsPlaybackTimer) {
        // Apply immediately instead of waiting for the current tick.
        clearInterval(gpsPlaybackTimer);
        gpsPlaybackTimer = setInterval(stepRoutePlayback, gpsPlaybackIntervalMs);
    }
}

function jumpPlaybackTo(index) {
    const p = gpsPlaybackPoints[index];
    if (!p) return;

    const slider = document.getElementById('gpsPlaybackSlider');
    if (slider) slider.value = index;

    if (gpsPlaybackMarker) gpsPlaybackMarker.setLatLng([p.lat, p.lng]);
    if (gpsPlaybackTraveled) {
        gpsPlaybackTraveled.setLatLngs(gpsPlaybackPoints.slice(0, index + 1).map(pt => [pt.lat, pt.lng]));
    }
    updatePlaybackTimestamp(index);
}

function resetRoutePlayback() {
    pauseRoutePlayback();
    gpsPlaybackPoints = [];
    gpsPlaybackIndex = 0;
    gpsPlaybackMarker = null;    // the actual layer was already removed with gpsRouteLayer
    gpsPlaybackTraveled = null;

    const controls = document.getElementById('gpsPlaybackControls');
    if (controls) controls.style.display = 'none';
    const timestampEl = document.getElementById('gpsPlaybackTimestamp');
    if (timestampEl) timestampEl.textContent = '';
    const slider = document.getElementById('gpsPlaybackSlider');
    if (slider) slider.value = 0;
}

// ── Sync one vehicle ───────────────────────────────────────────
function syncVehicle(id, btn) {
    if (btn) { btn.classList.add('spinning'); }
    fetch(SYNC_BASE + id)
        .then(r => r.json())
        .then(res => {
            if (btn) btn.classList.remove('spinning');
            showToast('GPS synced — ' + (res.synced_at || 'now'));
        })
        .catch(() => {
            if (btn) btn.classList.remove('spinning');
            showToast('Sync failed.', true);
        });
}

// ── Refresh all ────────────────────────────────────────────────
function refreshAll() {
    showToast('Refreshing fleet GPS data…');
    setTimeout(() => location.reload(), 800);
}

// ── Map toggle ─────────────────────────────────────────────────
let mapVisible = false;
function toggleMapView() {
    mapVisible = !mapVisible;
    document.getElementById('mapPanel').style.display = mapVisible ? 'block' : 'none';
    document.getElementById('mapToggleLabel').textContent = mapVisible ? 'Hide Map' : 'Show Map';
}

// ── Table filter ───────────────────────────────────────────────
function filterTable() {
    const gps   = document.getElementById('statusFilter').value.toLowerCase();
    const avail = document.getElementById('availFilter').value.toLowerCase();
    const term  = document.getElementById('searchInput').value.toLowerCase();

    document.querySelectorAll('#gpsTable tbody .fleet-row').forEach(row => {
        const rowGps   = row.dataset.gps.toLowerCase();
        const rowAvail = row.dataset.avail.toLowerCase();
        const rowSearch= row.dataset.search;
        const show =
            (!gps   || rowGps   === gps)   &&
            (!avail || rowAvail === avail) &&
            (!term  || rowSearch.includes(term));
        row.style.display = show ? '' : 'none';
    });
}

function toggleGpsFilterMenu() {
    const popup = document.getElementById('gpsFilterPopup');
    popup.classList.toggle('visible');
}

// Stat cards act as quick filters into the fleet table below — same as
// Vehicle Management / Tools Management: the cards stay right where they
// are, the table just filters in place.
function filterGpsByStat(kind) {
    document.getElementById('statusFilter').value = '';
    document.getElementById('availFilter').value = '';
    document.getElementById('searchInput').value = '';

    if (kind === 'online')      document.getElementById('statusFilter').value = 'Online';
    if (kind === 'offline')     document.getElementById('statusFilter').value = 'Offline';
    if (kind === 'transit')     document.getElementById('availFilter').value = 'In Use';
    if (kind === 'maintenance') document.getElementById('availFilter').value = 'Maintenance';

    filterTable();
}

document.querySelectorAll('.stat-card-clickable').forEach(card => {
    card.addEventListener('keydown', e => {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            card.click();
        }
    });
});

let gpsOriginalOrder = null;

function applyGpsSort(value) {
    const tbody = document.querySelector('#gpsTable tbody');
    if (!tbody) return;

    if (!gpsOriginalOrder) {
        gpsOriginalOrder = Array.from(tbody.querySelectorAll('.fleet-row'));
    }

    if (value === undefined) {
        value = document.querySelector('#gpsSortDD .dd-select-option.selected')?.dataset.value ?? '';
    }
    if (!value) {
        gpsOriginalOrder.forEach(row => tbody.appendChild(row));
        return;
    }

    const [key, direction] = value.split('-');
    const ascending = direction === 'asc';
    const colIndex = parseInt(key, 10);

    const rows = Array.from(tbody.querySelectorAll('.fleet-row'));
    rows.sort((a, b) => {
        const aText = (key === 'vehicle')
            ? (a.querySelector('.v-model')?.innerText.trim() ?? '')
            : (a.children[colIndex]?.innerText.trim() ?? '');
        const bText = (key === 'vehicle')
            ? (b.querySelector('.v-model')?.innerText.trim() ?? '')
            : (b.children[colIndex]?.innerText.trim() ?? '');
        const cmp = aText.localeCompare(bText, undefined, { sensitivity: 'base' });
        return ascending ? cmp : -cmp;
    });

    rows.forEach(row => tbody.appendChild(row));
}

document.addEventListener('click', e => {
    const wrapper = document.querySelector('.filter-menu-wrapper');
    const popup = document.getElementById('gpsFilterPopup');
    if (wrapper && popup && !wrapper.contains(e.target)) {
        popup.classList.remove('visible');
    }
});

// ── Helpers ────────────────────────────────────────────────────
function availClass(a) {
    return { 'In Use': 'avail-inuse', 'Reserved': 'avail-reserved',
             'Under Maintenance': 'avail-maint' }[a] || 'avail-available';
}

function esc(s) {
    const d = document.createElement('div');
    d.textContent = String(s ?? '');
    return d.innerHTML;
}

function timeAgoJS(dateStr) {
    // Clock skew between the tracker and this browser can make a fresh fix look
    // slightly "in the future" — clamp so it never shows as e.g. "-2s ago".
    const diff = Math.max(0, Math.floor((Date.now() - new Date(dateStr)) / 1000));
    if (diff < 60)   return diff + 's ago';
    if (diff < 3600) return Math.floor(diff / 60) + 'm ago';
    if (diff < 86400)return Math.floor(diff / 3600) + 'h ago';
    return Math.floor(diff / 86400) + 'd ago';
}

function showToast(msg, isError = false) { uiToast(msg, isError); }

// Flash auto-hide
setTimeout(() => {
    document.querySelectorAll('.flash').forEach(el => el.style.opacity = '0');
}, 4000);

// Close modal on outside click
document.getElementById('vehicleProfileModal').addEventListener('click', (e) => {
    if (e.target === document.getElementById('vehicleProfileModal')) {
        closeVehicleModal();
    }
});
</script>

<?php
// PHP helper: time-ago for server-side rendering
function timeAgo(?string $datetime): string {
    if (!$datetime) return '—';
    // A tracker's GPS timestamp can be a few seconds ahead of this server's own
    // clock — never render that as a negative "-2s ago".
    $diff = max(0, time() - strtotime($datetime));
    if ($diff < 60)    return $diff . 's ago';
    if ($diff < 3600)  return floor($diff / 60) . 'm ago';
    if ($diff < 86400) return floor($diff / 3600) . 'h ago';
    return floor($diff / 86400) . 'd ago';
}
?>

<?= $this->endSection() ?>
