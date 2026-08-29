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
        <div class="stat-card">
            <span class="stat-icon tone-maroon"><i class="fa-solid fa-truck"></i></span>
            <h3>Total Vehicles</h3>
            <div class="value"><?= $total ?></div>
        </div>
        <div class="stat-card">
            <span class="stat-icon tone-green"><i class="fa-solid fa-signal"></i></span>
            <h3>GPS Online</h3>
            <div class="value"><?= $online_count ?></div>
        </div>
        <div class="stat-card">
            <span class="stat-icon tone-neutral"><i class="fa-solid fa-satellite-dish"></i></span>
            <h3>GPS Offline</h3>
            <div class="value"><?= $offline_count ?></div>
        </div>
        <div class="stat-card">
            <span class="stat-icon tone-gold"><i class="fa-solid fa-route"></i></span>
            <h3>In Transit</h3>
            <div class="value"><?= $transit_count ?></div>
        </div>
        <div class="stat-card">
            <span class="stat-icon tone-red"><i class="fa-solid fa-screwdriver-wrench"></i></span>
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
                            <option value="Under Maintenance">Maintenance</option>
                          </select>
                        </div>
                      </div>
                    </div>
                    <div class="toolbar-search">
                      <input type="text" id="searchInput" class="search-box" placeholder="Search plate / driver..." oninput="filterTable()">
                      <i class="bi bi-search search-icon"></i>
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
                                        <button class="icon-btn view" title="View Profile"
                                            onclick="event.stopPropagation(); openVehicleModal(<?= $v['id'] ?>)">
                                            <i class="bi bi-eye"></i>
                                        </button>
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

function openVehicleModal(id) {
    const modal = document.getElementById('vehicleProfileModal');
    const content = document.getElementById('vehicleProfileContent');

    content.innerHTML = `<div class="sidebar-loading"><i class="bi bi-hourglass-split"></i> Loading GPS profile...</div>`;
    modal.classList.add('open');

    fetch(GPS_AJAX_BASE + id)
        .then(r => r.json())
        .then(v => renderModalProfile(v))
        .catch(() => {
            content.innerHTML =
                '<div class="sidebar-error"><i class="bi bi-exclamation-triangle"></i> Failed to load vehicle data.</div>';
        });
}

function closeVehicleModal() {
    const modal = document.getElementById('vehicleProfileModal');
    modal.classList.remove('open');
}

function renderModalProfile(v) {
    const online      = v.gps_status === 'Online';
    const signalPct   = Math.min(100, parseInt(v.signal || 0));
    const signalLabel = signalPct >= 70 ? 'Strong' : signalPct >= 40 ? 'Moderate' : 'Weak';
    const signalClass = signalPct >= 70 ? 'signal-strong' : signalPct >= 40 ? 'signal-mod' : 'signal-weak';

    document.getElementById('vehicleProfileContent').innerHTML = `
    <div class="modal-header">
        <h3>Vehicle Profile</h3>
        <button class="modal-close-btn" onclick="closeVehicleModal()">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>
    <div class="profile-card">
        <div class="profile-vehicle-icon">
            <i class="bi bi-truck-front-fill"></i>
        </div>
        <div class="profile-name">${esc(v.model || 'Unknown Vehicle')}</div>
        <div class="profile-plate">${esc(v.plate_no || '—')}</div>
        <span class="avail-badge ${availClass(v.availability)}" style="margin:0.5rem auto;display:block;width:fit-content;">
            ${esc(v.availability || 'Available')}
        </span>
    </div>

    <div class="sidebar-section">
        <div class="ss-title">Vehicle Details</div>
        <div class="detail-row"><span>Assigned Driver</span><strong>${esc(v.driver_name || 'Unassigned')}</strong></div>
        <div class="detail-row"><span>Insurance Expiry</span><strong>${esc(v.insurance_expiry || '—')}</strong></div>
        <div class="detail-row"><span>Department</span><strong>${esc(v.department || '—')}</strong></div>
        <div class="detail-row"><span>Capacity</span><strong>${esc(v.capacity || '—')}</strong></div>
        <div class="detail-row"><span>Last Service</span><strong>${esc(v.last_service_date || '—')}</strong></div>
        <div class="detail-row"><span>Registration Expiry</span><strong>${esc(v.registration_expiry || '—')}</strong></div>
    </div>

    <div class="sidebar-section gps-section ${online ? '' : 'gps-offline-section'}">
        <div class="ss-title">
            GPS Live Tracking
            <span class="gps-badge ${online ? 'gps-online' : 'gps-offline'}" style="margin-left:.5rem;">
                <span class="${online ? 'pulse-dot' : 'dead-dot'}"></span>
                ${online ? 'Connected' : 'Offline'}
            </span>
        </div>
        <div class="detail-row"><span>Device ID</span><strong>${esc(v.device_id || 'N/A')}</strong></div>
        <div class="detail-row"><span>Last Updated</span><strong>${v.logged_at ? timeAgoJS(v.logged_at) : '—'}</strong></div>
        <div class="detail-row"><span>Current Speed</span><strong>${v.speed || 0} km/h</strong></div>
        <div class="detail-row"><span>Last Location</span><strong>${esc(v.last_location || 'Unknown')}</strong></div>
        <div class="detail-row"><span>Coordinates</span><strong>${v.latitude ? v.latitude + ', ' + v.longitude : 'N/A'}</strong></div>
        <div class="detail-row signal-row">
            <span>Signal Strength</span>
            <div class="signal-wrap">
                <div class="signal-bar-bg">
                    <div class="signal-bar-fill ${signalClass}" style="width:${signalPct}%"></div>
                </div>
                <strong class="${signalClass}">${signalLabel} (${signalPct}%)</strong>
            </div>
        </div>
        <div class="sidebar-btn-row">
            <a class="btn-outline-sm" href="https://www.google.com/maps?q=${v.latitude || '9.3164'},${v.longitude || '123.2885'}" target="_blank">
                <i class="bi bi-map"></i> Open GPS App
            </a>
            <button class="btn-outline-sm" onclick="syncVehicle(${v.id})">
                <i class="bi bi-arrow-clockwise"></i> Sync API
            </button>
        </div>
    </div>

    <div class="ubra-mini">
        <div class="ubra-header">
            <span class="ubra-icon">U</span>
            <div><div class="ubra-name">Mr. UBRA</div><div class="ubra-sub">Fleet Health</div></div>
        </div>
        <div class="ubra-msg">
            <ul class="ubra-list">
                ${online ? '<li><i class="bi bi-check-circle-fill text-success"></i> GPS signal is live.</li>' :
                           '<li><i class="bi bi-exclamation-triangle-fill text-warning"></i> GPS signal lost — check device.</li>'}
                ${v.availability === 'Under Maintenance' ?
                    '<li><i class="bi bi-tools text-warning"></i> Vehicle under maintenance.</li>' : ''}
                <li><i class="bi bi-info-circle"></i> Last known location: ${esc(v.last_location || 'Unknown')}.</li>
            </ul>
        </div>
    </div>
    `;
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
    const diff = Math.floor((Date.now() - new Date(dateStr)) / 1000);
    if (diff < 60)   return diff + 's ago';
    if (diff < 3600) return Math.floor(diff / 60) + 'm ago';
    if (diff < 86400)return Math.floor(diff / 3600) + 'h ago';
    return Math.floor(diff / 86400) + 'd ago';
}

function showToast(msg, isError = false) {
    const t = document.createElement('div');
    t.className = 'gps-toast' + (isError ? ' toast-error' : '');
    t.innerHTML = `<i class="bi bi-${isError ? 'exclamation-triangle' : 'check-circle-fill'}"></i> ${msg}`;
    document.body.appendChild(t);
    setTimeout(() => t.classList.add('toast-show'), 10);
    setTimeout(() => { t.classList.remove('toast-show'); setTimeout(() => t.remove(), 400); }, 3000);
}

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
    $diff = time() - strtotime($datetime);
    if ($diff < 60)    return $diff . 's ago';
    if ($diff < 3600)  return floor($diff / 60) . 'm ago';
    if ($diff < 86400) return floor($diff / 3600) . 'h ago';
    return floor($diff / 86400) . 'd ago';
}
?>

<?= $this->endSection() ?>
