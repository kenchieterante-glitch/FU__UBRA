<?php
/**
 * @var string $title
 * @var array<int, array<string, mixed>> $vehicles
 * @var int $total_vehicles
 * @var int $available_vehicles
 * @var int $inuse_vehicles
 * @var int $maintenance_due
 * @var array<int, array<string, mixed>> $personnel
 * @var array<int, array<string, mixed>> $departments
 * @var array<int, array<string, mixed>> $fuel_predictions
 * @var string $vehicle_details_json
 */
$fuel_predictions = $fuel_predictions ?? [];
$vehicle_details_json = $vehicle_details_json ?? '{}';
?>
<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<?php $normalize_status = fn($value) => strtolower(preg_replace('/[^a-z0-9]+/', '-', trim((string)$value))); ?>

<div class="page-header">
  <div>
    <h1><?= esc($title) ?></h1>
    <p class="page-subtitle">Manage all university vehicles, drivers, and maintenance status.</p>
  </div>
  <button class="btn-add" onclick="document.getElementById('addModal').style.display='flex'">+ Add Vehicle</button>
</div>

<div class="stat-cards">
  <div class="stat-card stat-card-clickable" onclick="filterVehiclesByStat('')" role="button" tabindex="0">
    <span class="stat-icon tone-maroon"><i class="bi bi-truck"></i></span>
    <h3>Total Vehicles</h3>
    <div class="value"><?= (int) $total_vehicles ?></div>
  </div>
  <div class="stat-card stat-card-clickable" onclick="filterVehiclesByStat('available')" role="button" tabindex="0">
    <span class="stat-icon tone-green"><i class="bi bi-check-circle-fill"></i></span>
    <h3>Available</h3>
    <div class="value"><?= (int) $available_vehicles ?></div>
  </div>
  <div class="stat-card stat-card-clickable" onclick="filterVehiclesByStat('inuse')" role="button" tabindex="0">
    <span class="stat-icon tone-neutral"><i class="bi bi-signpost-2"></i></span>
    <h3>In Use</h3>
    <div class="value"><?= (int) $inuse_vehicles ?></div>
  </div>
  <div class="stat-card stat-card-clickable" onclick="filterVehiclesByStat('maintenance')" role="button" tabindex="0">
    <span class="stat-icon tone-red"><i class="bi bi-wrench-adjustable"></i></span>
    <h3>Needs Maintenance</h3>
    <div class="value"><?= count(array_filter($vehicles, fn($v) => $v['inspection_status'] == 'Expired' || $v['availability'] == 'Maintenance')) ?></div>
  </div>
</div>

<div class="table-card">
  <div class="table-toolbar">
    <div class="toolbar-left">
      <div class="toolbar-search">
        <input type="text" id="vehiclesSearch" class="search-box" placeholder="Search vehicles…" title="Search by vehicle, plate, driver, or department" oninput="filterVehiclesTable()">
        <i class="bi bi-search search-icon"></i>
      </div>
    </div>
    <div class="toolbar-right">
      <div class="filter-menu-wrapper">
        <button type="button" class="filter-btn" onclick="toggleVehicleFilterMenu()" aria-label="Open filters">
          <i class="bi bi-funnel"></i>
        </button>
        <div class="filter-popup" id="vehicleFilterPopup">
          <div class="filter-popup-title">Filter</div>
          <div class="filter-row">
            <label for="vehiclesType">Type</label>
            <select id="vehiclesType" onchange="filterVehiclesTable()">
              <option value="">All Types</option>
              <?php foreach (array_unique(array_column($vehicles, 'type')) as $type): ?>
                <?php if (!empty($type)): ?>
                  <option value="<?= esc($type) ?>"><?= esc($type) ?></option>
                <?php endif; ?>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="filter-row">
            <label for="vehiclesAvailability">Availability</label>
            <select id="vehiclesAvailability" onchange="filterVehiclesTable()">
              <option value="">All Statuses</option>
              <option value="Available">Available</option>
              <option value="In Use">In Use</option>
              <option value="Maintenance">Maintenance</option>
              <option value="Reserved">Reserved</option>
              <option value="Inactive">Inactive</option>
            </select>
          </div>
          <div class="filter-row">
            <label for="vehiclesSort">Sort By</label>
            <select id="vehiclesSort" onchange="applyVehiclesSort()">
              <option value="">Default</option>
              <option value="0-asc">Vehicle (A&ndash;Z)</option>
              <option value="0-desc">Vehicle (Z&ndash;A)</option>
              <option value="3-asc">Driver (A&ndash;Z)</option>
              <option value="3-desc">Driver (Z&ndash;A)</option>
              <option value="5-asc">GPS Status (A&ndash;Z)</option>
              <option value="5-desc">GPS Status (Z&ndash;A)</option>
              <option value="7-asc">Availability (A&ndash;Z)</option>
              <option value="7-desc">Availability (Z&ndash;A)</option>
            </select>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="vehicles-table-scroll">
  <table id="vehiclesTable" class="data-table">
  <thead>
    <tr>
      <th>Vehicle Detail</th>
      <th>Plate Number</th>
      <th>Type</th>
      <th>Driver</th>
      <th>Department</th>
      <th>GPS Status</th>
      <th>Inspection</th>
      <th>Availability</th>
      <th>Predicted Fuel Need</th>
      <th>Actions</th>
    </tr>
  </thead>
  <tbody>
    <?php if (!empty($vehicles)): ?>
      <?php foreach ($vehicles as $v): ?>
        <tr class="vehicle-row" onclick="openVehicleDetail(<?= (int) $v['id'] ?>)">
          <td><?= esc($v['vehicle_name']) ?><br><small></small></td>
          <td><?= esc($v['plate_no']) ?></td>
          <td><?= esc($v['type']) ?></td>
          <td><?= esc($v['driver_name'] ?? 'Unassigned') ?></td>
          <td><?= esc($v['department_name'] ?? 'Unassigned') ?></td>
          <?php $inspectionClass = $normalize_status($v['inspection_status'] ?? 'unknown'); ?>
          <?php $gpsOnline = ($v['gps_status'] ?? '') === 'Online'; ?>
          <?php $availClass = match ($v['availability'] ?? 'Available') {
              'In Use'      => 'avail-inuse',
              'Reserved'    => 'avail-reserved',
              'Maintenance' => 'avail-maint',
              'Inactive'    => 'avail-inactive',
              default       => 'avail-available',
          }; ?>
          <td>
            <span class="gps-badge <?= $gpsOnline ? 'gps-online' : 'gps-offline' ?>">
              <span class="<?= $gpsOnline ? 'pulse-dot' : 'dead-dot' ?>"></span>
              <?= esc($v['gps_status']) ?>
            </span>
          </td>
          <td><span class="status-badge status-<?= esc($inspectionClass) ?>"><?= esc($v['inspection_status']) ?></span></td>
          <td><span class="avail-badge <?= esc($availClass) ?>"><?= esc($v['availability']) ?></span></td>
          <?php $prediction = $fuel_predictions[$v['id']] ?? ['hasData' => false]; ?>
          <td>
            <?php if (!empty($prediction['hasData'])): ?>
              <strong><?= esc((string) $prediction['predictedLiters30d']) ?> L</strong> / 30 days
              <br><small class="page-subtitle" style="margin:0;"><?= esc((string) $prediction['avgLPer100km']) ?> L per 100km avg</small>
            <?php elseif (($prediction['logsCount'] ?? 0) >= 1): ?>
              <small class="page-subtitle" style="margin:0;">Log 1 more fill-up to enable predictions</small>
            <?php else: ?>
              <small class="page-subtitle" style="margin:0;">No fuel logs yet</small>
            <?php endif; ?>
          </td>
          <td class="action-cell">
            <div class="action-buttons" onclick="event.stopPropagation()">
              <button type="button" class="icon-btn" onclick="openFuelLogModal(<?= (int) $v['id'] ?>, '<?= esc($v['vehicle_name'], 'js') ?>')" title="Log Fuel" aria-label="Log fuel for <?= esc($v['vehicle_name']) ?>"><i class="bi bi-fuel-pump-fill"></i></button>
              <form method="post" action="<?= base_url('vehicles/delete/'.$v['id']) ?>" onsubmit="return confirm('Archive this vehicle?')" style="display:contents;">
                <?= csrf_field() ?>
                <button type="submit" class="icon-btn delete" title="Archive" aria-label="Archive <?= esc($v['vehicle_name']) ?>"><i class="bi bi-archive-fill"></i></button>
              </form>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
    <?php else: ?>
      <tr><td colspan="10">No vehicles recorded yet.</td></tr>
    <?php endif; ?>
  </tbody>
</table>
</div>
</div>

<!-- LOG FUEL MODAL -->
<div class="modal" id="fuelLogModal">
  <div class="modal-box">
    <h3>Log Fuel</h3>
    <p class="page-subtitle">Recording a refuel for "<span id="fuelLogVehicleName"></span>"</p>
    <form id="fuelLogForm" method="post">
      <?= csrf_field() ?>
      <label>Odometer Reading (km) <span class="required-mark">*</span></label>
      <input type="number" step="0.1" min="0" name="odometer_km" required>
      <label>Liters Filled <span class="required-mark">*</span></label>
      <input type="number" step="0.01" min="0.01" name="liters_filled" required>
      <label>Date</label>
      <input type="date" name="logged_at" value="<?= date('Y-m-d') ?>">
      <label>Notes</label>
      <input type="text" name="notes" placeholder="Optional">
      <div class="modal-actions">
        <button type="button" onclick="document.getElementById('fuelLogModal').style.display='none'">Cancel</button>
        <button type="submit" class="btn-maroon">Save</button>
      </div>
    </form>
  </div>
</div>

<!-- VEHICLE DETAIL / EDIT MODAL — one shared popup, one shared size. The
     pencil icon swaps #vdBody (view) for #vdEditForm (edit) in place rather
     than closing this popup and opening a separate, differently-sized one. -->
<div class="modal" id="vehicleDetailModal">
  <div class="modal-box">
    <div class="modal-header">
      <h3 id="vdTitle">Vehicle Detail</h3>
      <div class="modal-header-actions">
        <button type="button" class="modal-close-btn" id="vdEditBtn" onclick="toggleVehicleEditMode(true)" aria-label="Edit"><i class="bi bi-pencil-fill"></i></button>
        <button type="button" class="modal-close-btn" onclick="closeVehicleDetail()" aria-label="Close"><i class="bi bi-x-lg"></i></button>
      </div>
    </div>
    <div class="modal-body" id="vdBody"></div>
    <form class="modal-body" id="vdEditForm" method="post" style="display:none;">
      <?= csrf_field() ?>
      <!-- Same two-column .detail-grid the view mode uses (Driver/Department
           side by side, etc.) so editing fills the same wide box instead of
           a plain single-column stacked form. -->
      <div class="detail-grid">
        <div class="edit-field">
          <label>Vehicle Name / Model <span class="required-mark">*</span></label>
          <input type="text" name="vehicle_name" id="vdEditName" required>
        </div>
        <div class="edit-field">
          <label>Plate Number <span class="required-mark">*</span></label>
          <input type="text" name="plate_no" id="vdEditPlate" required>
        </div>
        <div class="edit-field">
          <label>Type</label>
          <input type="text" name="type" id="vdEditType">
        </div>
        <div class="edit-field">
          <label>Driver</label>
          <select name="driver_id" id="vdEditDriver">
            <option value="">— Unassigned —</option>
            <?php foreach ($personnel as $p): ?>
              <option value="<?= $p['id'] ?>"><?= esc($p['full_name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="edit-field">
          <label>Department</label>
          <select name="department_id" id="vdEditDept">
            <option value="">— Unassigned —</option>
            <?php foreach ($departments as $d): ?>
              <option value="<?= $d['id'] ?>"><?= esc($d['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="edit-field">
          <label>GPS Status</label>
          <select name="gps_status" id="vdEditGps">
            <option>Online</option>
            <option>Offline</option>
          </select>
        </div>
        <div class="edit-field">
          <label>Inspection Status</label>
          <select name="inspection_status" id="vdEditInspection">
            <option>Completed</option>
            <option>Due Soon</option>
            <option>Expired</option>
          </select>
        </div>
        <div class="edit-field">
          <label>Availability</label>
          <select name="availability" id="vdEditAvailability">
            <option>Available</option>
            <option>In Use</option>
            <option>Maintenance</option>
            <option>Reserved</option>
            <option>Inactive</option>
          </select>
        </div>
      </div>
      <div class="modal-actions">
        <button type="button" onclick="toggleVehicleEditMode(false)">Cancel</button>
        <button type="submit" class="btn-maroon">Save Changes</button>
      </div>
    </form>
  </div>
</div>

<script>
function esc(s) {
  const d = document.createElement('div');
  d.textContent = String(s ?? '');
  return d.innerHTML;
}

const vehicleDetails = <?= $vehicle_details_json ?? '{}' ?>;

let currentDetailVehicleId = null;

function openVehicleDetail(id) {
  const v = vehicleDetails[id];
  if (!v) return;

  currentDetailVehicleId = id;
  document.getElementById('vdTitle').textContent = `${v.name} (${v.plate})`;

  // Always reopen in view mode, even if a previous vehicle was left mid-edit.
  document.getElementById('vdEditForm').style.display = 'none';
  document.getElementById('vdBody').style.display = 'block';
  document.getElementById('vdEditBtn').style.display = '';

  const pred = v.prediction && v.prediction.hasData
    ? `<div class="detail-row"><span>Predicted need</span><strong>${esc(v.prediction.predictedLiters30d)} L / 30 days</strong></div>
       <div class="detail-row"><span>Avg. consumption</span><strong>${esc(v.prediction.avgLPer100km)} L per 100km</strong></div>`
    : `<div class="detail-row"><span>Predicted need</span><strong>${(v.prediction && v.prediction.logsCount >= 1) ? 'Log 1 more fill-up to enable predictions' : 'No fuel logs yet'}</strong></div>`;

  const fuelRows = v.fuelLogs.length
    ? v.fuelLogs.map(f => `<tr><td>${esc(f.date)}</td><td>${esc(f.odo)} km</td><td>${esc(f.liters)} L</td><td>${esc(f.by || '—')}</td></tr>`).join('')
    : `<tr><td colspan="4">No fuel logs recorded yet.</td></tr>`;

  const tripRows = v.trips.length
    ? v.trips.map(t => `<tr><td>${esc(t.date)}</td><td>${esc(t.destination)}</td><td>${esc(t.driver)}</td><td>${esc(t.status)}</td></tr>`).join('')
    : `<tr><td colspan="4">No trip history recorded yet.</td></tr>`;

  document.getElementById('vdBody').innerHTML = `
    <div class="detail-section">
      <div class="detail-section-title">Vehicle Details</div>
      <div class="detail-grid">
        <div class="detail-row"><span>Driver</span><strong>${esc(v.driver)}</strong></div>
        <div class="detail-row"><span>Department</span><strong>${esc(v.department)}</strong></div>
        <div class="detail-row"><span>Type</span><strong>${esc(v.type)}</strong></div>
        <div class="detail-row"><span>Tire Pressure</span><strong>${esc(v.tirePressure)}</strong></div>
        <div class="detail-row"><span>GPS Status</span><strong>${esc(v.gpsStatus)}</strong></div>
        <div class="detail-row"><span>Inspection</span><strong>${esc(v.inspection)}</strong></div>
        <div class="detail-row"><span>Availability</span><strong>${esc(v.availability)}</strong></div>
      </div>
    </div>

    <div class="detail-section">
      <div class="detail-section-title">Fuel</div>
      ${pred}
    </div>

    <div class="detail-section">
      <div class="detail-section-title">Fuel Log History</div>
      <div class="history-table-wrap">
        <table class="history-table">
          <thead><tr><th>Date</th><th>Odometer</th><th>Filled</th><th>Logged By</th></tr></thead>
          <tbody>${fuelRows}</tbody>
        </table>
      </div>
    </div>

    <div class="detail-section">
      <div class="detail-section-title">Trip History</div>
      <div class="history-table-wrap">
        <table class="history-table">
          <thead><tr><th>Date</th><th>Destination</th><th>Driver</th><th>Status</th></tr></thead>
          <tbody>${tripRows}</tbody>
        </table>
      </div>
    </div>`;

  document.getElementById('vehicleDetailModal').style.display = 'flex';
  // The popup already scrolls internally (.modal-body) if it needs to —
  // without this, the page behind it stays scrollable too, showing a
  // second, confusing scrollbar at the edge of the browser window.
  document.body.style.overflow = 'hidden';
}

function closeVehicleDetail() {
  document.getElementById('vehicleDetailModal').style.display = 'none';
  document.body.style.overflow = '';
}

// Swaps the view (#vdBody) and edit (#vdEditForm) content in place inside
// the same popup — same modal element, same size — instead of closing this
// one and opening a separate, differently-sized Edit modal.
function toggleVehicleEditMode(editing) {
  if (currentDetailVehicleId === null) return;
  const v = vehicleDetails[currentDetailVehicleId];
  if (!v) return;

  const body = document.getElementById('vdBody');
  const form = document.getElementById('vdEditForm');
  const editBtn = document.getElementById('vdEditBtn');

  if (editing) {
    form.action = '<?= base_url('vehicles/edit/') ?>' + currentDetailVehicleId;
    document.getElementById('vdEditName').value = v.name;
    document.getElementById('vdEditPlate').value = v.plate;
    document.getElementById('vdEditType').value = v.type;
    document.getElementById('vdEditDriver').value = v.driverId ?? '';
    document.getElementById('vdEditDept').value = v.departmentId ?? '';
    document.getElementById('vdEditGps').value = v.gpsStatus;
    document.getElementById('vdEditInspection').value = v.inspection;
    document.getElementById('vdEditAvailability').value = v.availability;

    document.getElementById('vdTitle').textContent = `Edit ${v.name} (${v.plate})`;
    body.style.display = 'none';
    form.style.display = 'block';
    editBtn.style.display = 'none';
  } else {
    document.getElementById('vdTitle').textContent = `${v.name} (${v.plate})`;
    form.style.display = 'none';
    body.style.display = 'block';
    editBtn.style.display = '';
  }
}

function openFuelLogModal(vehicleId, vehicleName) {
  document.getElementById('fuelLogVehicleName').textContent = vehicleName;
  document.getElementById('fuelLogForm').action = `<?= base_url('vehicles/logFuel/') ?>${vehicleId}`;
  document.getElementById('fuelLogModal').style.display = 'flex';
}

function filterVehiclesTable() {
  const search = document.getElementById('vehiclesSearch').value.toLowerCase();
  const type = document.getElementById('vehiclesType').value.toLowerCase();
  const availability = document.getElementById('vehiclesAvailability').value.toLowerCase();
  document.querySelectorAll('#vehiclesTable tbody tr').forEach(row => {
    const text = row.innerText.toLowerCase();
    const typeText = row.children[2].innerText.toLowerCase();
    const availabilityText = row.children[7].innerText.toLowerCase();
    const matches = text.includes(search)
      && (!type || typeText === type)
      && (!availability || availabilityText === availability);
    row.style.display = matches ? '' : 'none';
  });
}

let vehiclesOriginalOrder = null;

function applyVehiclesSort() {
  const tbody = document.querySelector('#vehiclesTable tbody');
  if (!tbody) return;

  if (!vehiclesOriginalOrder) {
    vehiclesOriginalOrder = Array.from(tbody.querySelectorAll('tr'));
  }

  const value = document.getElementById('vehiclesSort').value;
  if (!value) {
    vehiclesOriginalOrder.forEach(row => tbody.appendChild(row));
    return;
  }

  const [colIndexStr, direction] = value.split('-');
  const colIndex = parseInt(colIndexStr, 10);
  const ascending = direction === 'asc';

  const rows = Array.from(tbody.querySelectorAll('tr')).filter(r => r.children.length > 1);
  rows.sort((a, b) => {
    const aText = a.children[colIndex]?.innerText.trim() ?? '';
    const bText = b.children[colIndex]?.innerText.trim() ?? '';
    const cmp = aText.localeCompare(bText, undefined, { sensitivity: 'base' });
    return ascending ? cmp : -cmp;
  });

  rows.forEach(row => tbody.appendChild(row));
}

function toggleVehicleFilterMenu() {
  const popup = document.getElementById('vehicleFilterPopup');
  popup.classList.toggle('visible');
}

document.addEventListener('click', e => {
  const wrapper = document.querySelector('.filter-menu-wrapper');
  const popup = document.getElementById('vehicleFilterPopup');
  if (!wrapper.contains(e.target)) {
    popup.classList.remove('visible');
  }
});

// Stat cards act as quick filters into the table below.
function filterVehiclesByStat(kind) {
  document.getElementById('vehiclesSearch').value = '';
  document.getElementById('vehiclesType').value = '';
  document.getElementById('vehiclesAvailability').value = '';

  if (kind === 'available') document.getElementById('vehiclesAvailability').value = 'Available';
  if (kind === 'inuse')     document.getElementById('vehiclesAvailability').value = 'In Use';

  if (kind === 'maintenance') {
    // "Needs Maintenance" combines two different columns (Inspection = Expired
    // OR Availability = Maintenance), so it can't be expressed as a single
    // dropdown value — filter rows directly instead.
    document.querySelectorAll('#vehiclesTable tbody tr').forEach(row => {
      const inspectionText = row.children[6]?.innerText.toLowerCase() ?? '';
      const availabilityText = row.children[7]?.innerText.toLowerCase() ?? '';
      const matches = inspectionText === 'expired' || availabilityText === 'maintenance';
      row.style.display = matches ? '' : 'none';
    });
  } else {
    filterVehiclesTable();
  }
}

document.querySelectorAll('.stat-card-clickable').forEach(card => {
  card.addEventListener('keydown', e => {
    if (e.key === 'Enter' || e.key === ' ') {
      e.preventDefault();
      card.click();
    }
  });
});

// Arriving from the Dashboard's stat boxes (e.g. vehicles?filter=inuse).
const vehiclesUrlFilter = new URLSearchParams(window.location.search).get('filter');
if (vehiclesUrlFilter) filterVehiclesByStat(vehiclesUrlFilter);

// Arriving from the GPS Tracker's Vehicle Profile popup's edit pencil
// (e.g. vehicles?edit=6) — jump straight into that vehicle's detail popup,
// already switched to edit mode.
const vehiclesUrlEdit = new URLSearchParams(window.location.search).get('edit');
if (vehiclesUrlEdit) {
  openVehicleDetail(Number(vehiclesUrlEdit));
  toggleVehicleEditMode(true);
}
</script>

<!-- ADD MODAL -->
<div class="modal" id="addModal">
  <div class="modal-box">
    <h3>Add Vehicle</h3>
    <form action="<?= base_url('vehicles/add') ?>" method="post">
      <?= csrf_field() ?>
      <p class="required-note">Fields marked <span class="required-mark">*</span> are required.</p>
      <label>Vehicle Name / Model <span class="required-mark">*</span></label>
      <input type="text" name="vehicle_name" placeholder="e.g. Toyota Hiace" required>
      <label>Plate Number <span class="required-mark">*</span></label>
      <input type="text" name="plate_no" placeholder="e.g. ABC-1234" required>
      <label>Type</label>
      <input type="text" name="type" placeholder="e.g. Van">
      <label>Driver</label>
      <select name="driver_id">
        <option value="">— Unassigned —</option>
        <?php foreach ($personnel as $p): ?>
          <option value="<?= $p['id'] ?>"><?= esc($p['full_name']) ?></option>
        <?php endforeach; ?>
      </select>
      <label>Department</label>
      <select name="department_id">
        <option value="">— Unassigned —</option>
        <?php foreach ($departments as $d): ?>
          <option value="<?= $d['id'] ?>"><?= esc($d['name']) ?></option>
        <?php endforeach; ?>
      </select>
      <label>GPS Status</label>
      <select name="gps_status">
        <option>Online</option>
        <option selected>Offline</option>
      </select>
      <label>Inspection Status</label>
      <select name="inspection_status">
        <option>Completed</option>
        <option selected>Due Soon</option>
        <option>Expired</option>
      </select>
      <label>Availability</label>
      <select name="availability">
        <option selected>Available</option>
        <option>In Use</option>
        <option>Maintenance</option>
        <option>Reserved</option>
        <option>Inactive</option>
      </select>
      <div class="modal-actions">
        <button type="button" onclick="document.getElementById('addModal').style.display='none'">Cancel</button>
        <button type="submit" class="btn-maroon">Save</button>
      </div>
    </form>
  </div>
</div>

<?= $this->endSection() ?>
