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
    <span class="stat-icon tone-maroon"><i class="fa-solid fa-truck"></i></span>
    <h3>Total Vehicles</h3>
    <div class="value"><?= (int) $total_vehicles ?></div>
  </div>
  <div class="stat-card stat-card-clickable" onclick="filterVehiclesByStat('available')" role="button" tabindex="0">
    <span class="stat-icon tone-green"><i class="fa-solid fa-circle-check"></i></span>
    <h3>Available</h3>
    <div class="value"><?= (int) $available_vehicles ?></div>
  </div>
  <div class="stat-card stat-card-clickable" onclick="filterVehiclesByStat('inuse')" role="button" tabindex="0">
    <span class="stat-icon tone-neutral"><i class="fa-solid fa-road"></i></span>
    <h3>In Use</h3>
    <div class="value"><?= (int) $inuse_vehicles ?></div>
  </div>
  <div class="stat-card stat-card-clickable" onclick="filterVehiclesByStat('maintenance')" role="button" tabindex="0">
    <span class="stat-icon tone-red"><i class="fa-solid fa-screwdriver-wrench"></i></span>
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
          <?php $gpsClass = $normalize_status($v['gps_status'] ?? 'unknown'); ?>
          <?php $inspectionClass = $normalize_status($v['inspection_status'] ?? 'unknown'); ?>
          <?php $availabilityClass = $normalize_status($v['availability'] ?? 'unknown'); ?>
          <td><span class="status-badge status-<?= esc($gpsClass) ?>"><?= esc($v['gps_status']) ?></span></td>
          <td><span class="status-badge status-<?= esc($inspectionClass) ?>"><?= esc($v['inspection_status']) ?></span></td>
          <td><span class="status-badge status-<?= esc($availabilityClass) ?>"><?= esc($v['availability']) ?></span></td>
          <?php $prediction = $fuel_predictions[$v['id']] ?? ['hasData' => false]; ?>
          <td>
            <?php if (!empty($prediction['hasData'])): ?>
              <strong><?= esc((string) $prediction['predictedLiters30d']) ?> L</strong> / 30 days
              <br><small class="page-subtitle" style="margin:0;"><?= esc((string) $prediction['avgLPer100km']) ?> L per 100km avg</small>
            <?php else: ?>
              <small class="page-subtitle" style="margin:0;">Not enough fuel logs yet</small>
            <?php endif; ?>
          </td>
          <td class="action-cell">
            <div class="action-buttons" onclick="event.stopPropagation()">
              <button type="button" class="icon-btn" onclick="document.getElementById('editModal<?= $v['id'] ?>').style.display='flex'" title="Edit" aria-label="Edit <?= esc($v['vehicle_name']) ?>"><i class="fa-solid fa-pen"></i></button>
              <button type="button" class="icon-btn" onclick="openFuelLogModal(<?= (int) $v['id'] ?>, '<?= esc($v['vehicle_name'], 'js') ?>')" title="Log Fuel" aria-label="Log fuel for <?= esc($v['vehicle_name']) ?>"><i class="fa-solid fa-gas-pump"></i></button>
              <form method="post" action="<?= base_url('vehicles/delete/'.$v['id']) ?>" onsubmit="return confirm('Archive this vehicle?')" style="display:contents;">
                <?= csrf_field() ?>
                <button type="submit" class="icon-btn delete" title="Archive" aria-label="Archive <?= esc($v['vehicle_name']) ?>"><i class="fa-solid fa-archive"></i></button>
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

<?php if (!empty($vehicles)): ?>
  <?php foreach ($vehicles as $v): ?>
    <div class="modal" id="editModal<?= $v['id'] ?>">
      <div class="modal-box">
        <h3>Edit Vehicle</h3>
        <form action="<?= base_url('vehicles/edit/'.$v['id']) ?>" method="post">
          <?= csrf_field() ?>
          <label>Vehicle Name / Model <span class="required-mark">*</span></label>
          <input type="text" name="vehicle_name" value="<?= esc($v['vehicle_name']) ?>" required>
          <label>Plate Number <span class="required-mark">*</span></label>
          <input type="text" name="plate_no" value="<?= esc($v['plate_no']) ?>" required>
          <label>Type</label>
          <input type="text" name="type" value="<?= esc($v['type']) ?>">
          <label>Driver</label>
          <select name="driver_id">
            <option value="">— Unassigned —</option>
            <?php foreach ($personnel as $p): ?>
              <option value="<?= $p['id'] ?>" <?= $p['id']==$v['driver_id']?'selected':'' ?>><?= esc($p['full_name']) ?></option>
            <?php endforeach; ?>
          </select>
          <label>Department</label>
          <select name="department_id">
            <option value="">— Unassigned —</option>
            <?php foreach ($departments as $d): ?>
              <option value="<?= $d['id'] ?>" <?= $d['id']==$v['department_id']?'selected':'' ?>><?= esc($d['name']) ?></option>
            <?php endforeach; ?>
          </select>
          <label>GPS Status</label>
          <select name="gps_status">
            <option <?= $v['gps_status']=='Online'?'selected':'' ?>>Online</option>
            <option <?= $v['gps_status']=='Offline'?'selected':'' ?>>Offline</option>
          </select>
          <label>Inspection Status</label>
          <select name="inspection_status">
            <option <?= $v['inspection_status']=='Completed'?'selected':'' ?>>Completed</option>
            <option <?= $v['inspection_status']=='Due Soon'?'selected':'' ?>>Due Soon</option>
            <option <?= $v['inspection_status']=='Expired'?'selected':'' ?>>Expired</option>
          </select>
          <label>Availability</label>
          <select name="availability">
            <option <?= $v['availability']=='Available'?'selected':'' ?>>Available</option>
            <option <?= $v['availability']=='In Use'?'selected':'' ?>>In Use</option>
            <option <?= $v['availability']=='Maintenance'?'selected':'' ?>>Maintenance</option>
            <option <?= $v['availability']=='Reserved'?'selected':'' ?>>Reserved</option>
            <option <?= $v['availability']=='Inactive'?'selected':'' ?>>Inactive</option>
          </select>
          <div class="modal-actions">
            <button type="button" onclick="document.getElementById('editModal<?= $v['id'] ?>').style.display='none'">Cancel</button>
            <button type="submit" class="btn-maroon">Save Changes</button>
          </div>
        </form>
      </div>
    </div>
  <?php endforeach; ?>
<?php endif; ?>

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

<!-- VEHICLE DETAIL MODAL — view-only: driver, department, fuel, PSI, and
     history in one popup, closed with the × only (no edit/save here). -->
<div class="modal" id="vehicleDetailModal">
  <div class="modal-box">
    <div class="modal-header">
      <h3 id="vdTitle">Vehicle Detail</h3>
      <button type="button" class="modal-close-btn" onclick="closeVehicleDetail()" aria-label="Close"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="modal-body" id="vdBody"></div>
  </div>
</div>

<script>
function esc(s) {
  const d = document.createElement('div');
  d.textContent = String(s ?? '');
  return d.innerHTML;
}

const vehicleDetails = <?= $vehicle_details_json ?? '{}' ?>;

function openVehicleDetail(id) {
  const v = vehicleDetails[id];
  if (!v) return;

  document.getElementById('vdTitle').textContent = `${v.name} (${v.plate})`;

  const pred = v.prediction && v.prediction.hasData
    ? `<div class="detail-row"><span>Predicted need</span><strong>${esc(v.prediction.predictedLiters30d)} L / 30 days</strong></div>
       <div class="detail-row"><span>Avg. consumption</span><strong>${esc(v.prediction.avgLPer100km)} L per 100km</strong></div>`
    : `<div class="detail-row"><span>Predicted need</span><strong>Not enough fuel logs yet</strong></div>`;

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

  document.querySelector('.table-card').scrollIntoView({ behavior: 'smooth', block: 'start' });
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
// (e.g. vehicles?edit=6) — jump straight into that vehicle's edit form.
const vehiclesUrlEdit = new URLSearchParams(window.location.search).get('edit');
if (vehiclesUrlEdit) {
  const editModal = document.getElementById('editModal' + vehiclesUrlEdit);
  if (editModal) editModal.style.display = 'flex';
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
