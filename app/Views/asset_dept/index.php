<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
helper('facilities');
$subtitles = [
  'vehicles'     => 'Vehicle records, driver information, vehicle maintenance, and mechanical equipment.',
  'work-orders'  => 'Vehicle repair requests and mechanical equipment work orders, kept separate from Facilities.',
  'trip-tickets' => 'Trip ticket records with driver, vehicle, destination, and schedule.',
];
// Which "+ Add" button each tab shows: [label, js function] (none = no button)
$addByTab = [
  'vehicles' => ['records' => ['+ Add Vehicle', 'openAt(\'atVehicleModal\')'], 'maintenance' => ['+ Add Maintenance', 'openAt(\'atMaintModal\')'], 'equipment' => ['+ Add Equipment', 'openAt(\'atEquipModal\')']],
  'work-orders' => ['vehicle' => ['+ New Work Order', 'openAt(\'atWoModal\')'], 'mechanical' => ['+ New Work Order', 'openAt(\'atWoModal\')'], 'history' => ['+ New Work Order', 'openAt(\'atWoModal\')'], 'log' => ['+ New Work Order', 'openAt(\'atWoModal\')']],
  'trip-tickets' => ['all' => ['+ New Trip Ticket', 'openAt(\'atTripModal\')'], 'upcoming' => ['+ New Trip Ticket', 'openAt(\'atTripModal\')'], 'completed' => ['+ New Trip Ticket', 'openAt(\'atTripModal\')']],
][$section];
$first = $tabs[0]['key'];
?>
<div class="page-header">
  <div>
    <h1><?= esc($title) ?></h1>
    <p class="page-subtitle">Asset Acquisition and Monitoring Department — <?= esc($subtitles[$section] ?? '') ?></p>
  </div>
  <button type="button" class="btn-add" id="atAddBtn" style="<?= isset($addByTab[$first]) ? '' : 'display:none' ?>" onclick="<?= isset($addByTab[$first]) ? $addByTab[$first][1] : '' ?>"><?= isset($addByTab[$first]) ? esc($addByTab[$first][0]) : '' ?></button>
</div>

<div class="stat-cards">
  <?php foreach ($status as $s): ?>
    <?php $isOpen = ($active_stat ?? null) === $s['key']; ?>
    <a class="stat-card stat-card-clickable<?= $isOpen ? ' active' : '' ?>" href="<?= $isOpen ? '?' : '?stat=' . esc($s['key']) ?>">
      <span class="stat-icon tone-<?= esc($s['tone']) ?>"><i class="bi <?= esc($s['icon']) ?>"></i></span>
      <h3><?= esc($s['label']) ?></h3>
      <div class="value"><?= esc((string) $s['value']) ?></div>
    </a>
  <?php endforeach; ?>
</div>
<?php if (!empty($stat_rows)): ?>
<div id="statInline" class="guard-card" style="margin-bottom:16px;">
  <div class="gc-title"><i class="bi bi-list-ul"></i> <?= esc($stat_rows['title']) ?></div>
  <div class="table-wrap">
    <table class="sj-table">
      <thead><tr><?php foreach ($stat_rows['columns'] as $col): ?><th><?= esc($col) ?></th><?php endforeach; ?></tr></thead>
      <tbody>
        <?php if (empty($stat_rows['rows'])): ?>
          <tr><td colspan="<?= count($stat_rows['columns']) ?>" class="empty-row">Nothing here right now.</td></tr>
        <?php else: foreach ($stat_rows['rows'] as $row): ?>
          <tr><?php foreach ($row as $cell): ?><?= fac_cell($cell) ?><?php endforeach; ?></tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<div class="fe-toolbar-row" style="justify-content:flex-end;">
  <div class="fe-toolbar-actions">
    <button type="button" class="fac-alert-icon fac-alert-btn alert-red fac-blink" id="atAlertRed" onclick="toggleAtAlerts('red')" title="<?= esc($alert_titles['red']) ?>" style="display:none"><i class="bi bi-exclamation-triangle-fill"></i> <strong id="atAlertRedCount"></strong></button>
    <button type="button" class="fac-alert-icon fac-alert-btn alert-yellow" id="atAlertYellow" onclick="toggleAtAlerts('yellow')" title="<?= esc($alert_titles['yellow']) ?>" style="display:none"><i class="bi bi-exclamation-triangle-fill"></i> <strong id="atAlertYellowCount"></strong></button>
  </div>
</div>
<div id="atAlertList" class="guard-card" style="display:none;padding:18px;margin-bottom:16px;"></div>

<div class="sub-tabs" id="atTabs">
  <?php foreach ($tabs as $i => $t): ?>
    <button type="button" class="sub-tab<?= $i === 0 ? ' active' : '' ?>" data-tab="<?= esc($t['key']) ?>" onclick="switchAtTab('<?= esc($t['key'], 'js') ?>')"><?= esc($t['label']) ?></button>
  <?php endforeach; ?>
</div>

<?php foreach ($tabs as $i => $t): ?>
  <div class="at-pane" id="at-pane-<?= esc($t['key']) ?>" style="<?= $i === 0 ? '' : 'display:none' ?>">
    <div class="table-wrap">
      <table class="sj-table">
        <thead><tr><?php foreach ($t['columns'] as $col): ?><th><?= esc($col) ?></th><?php endforeach; ?></tr></thead>
        <tbody>
          <?php if (empty($t['rows'])): ?>
            <tr><td colspan="<?= count($t['columns']) ?>" class="empty-row"><?= esc($t['empty']) ?></td></tr>
          <?php else: foreach ($t['rows'] as $row): ?>
            <tr><?php foreach ($row as $cell): ?><?= fac_cell($cell) ?><?php endforeach; ?></tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
<?php endforeach; ?>

<?php /* ===================== MODALS ===================== */ ?>
<?php if ($section === 'vehicles'): ?>
<div class="modal" id="atVehicleModal">
  <div class="modal-box modal-box-wide">
    <h3>Add Vehicle</h3>
    <form method="post" action="<?= base_url('assets-dept/vehicles') ?>">
      <?= csrf_field() ?>
      <div class="form-grid2">
        <div class="fg"><label>Vehicle Name <span class="required-mark">*</span></label><input type="text" name="vehicle_name" placeholder="e.g. Toyota Hiace" required></div>
        <div class="fg"><label>Plate Number <span class="required-mark">*</span></label><input type="text" name="plate_no" placeholder="e.g. ABC 1234" required></div>
        <div class="fg"><label>Type</label><input type="text" name="type" placeholder="e.g. Van, Motorcycle, Truck"></div>
        <div class="fg"><label>Driver</label>
          <select name="driver_id"><option value="">— Not assigned —</option><?php foreach ($driver_options as $o): ?><option value="<?= (int) $o['id'] ?>"><?= esc($o['label']) ?></option><?php endforeach; ?></select>
        </div>
        <div class="fg"><label>Availability</label>
          <select name="availability"><option>Available</option><option>In Use</option><option>Maintenance</option><option>Reserved</option><option>Inactive</option></select>
        </div>
        <div class="fg"><label>Inspection</label>
          <select name="inspection_status"><option>Completed</option><option selected>Due Soon</option><option>Expired</option></select>
        </div>
      </div>
      <div class="modal-actions"><button type="button" onclick="closeAt('atVehicleModal')">Cancel</button><button type="submit" class="btn-maroon">Add</button></div>
    </form>
  </div>
</div>

<div class="modal" id="atMaintModal">
  <div class="modal-box modal-box-wide">
    <h3>Add Vehicle Maintenance</h3>
    <form method="post" action="<?= base_url('assets-dept/maintenance') ?>">
      <?= csrf_field() ?>
      <div class="form-grid2">
        <div class="fg"><label>Vehicle <span class="required-mark">*</span></label>
          <select name="vehicle_id" required><option value="">— Select a Vehicle —</option><?php foreach ($vehicle_options as $o): ?><option value="<?= (int) $o['id'] ?>"><?= esc($o['label']) ?></option><?php endforeach; ?></select>
        </div>
        <div class="fg"><label>Service <span class="required-mark">*</span></label>
          <input type="text" name="service_type" list="atServices" placeholder="e.g. Change Oil" required>
          <datalist id="atServices"><option>Change Oil</option><option>Tire Rotation</option><option>Brake Inspection</option><option>Battery Check</option><option>Engine Tune-up</option><option>Aircon Cleaning</option></datalist>
        </div>
        <div class="fg"><label>Date Done <span class="required-mark">*</span></label><input type="date" name="serviced_on" value="<?= date('Y-m-d') ?>" required></div>
        <div class="fg"><label>Next Due</label><input type="date" name="next_due"></div>
        <div class="fg"><label>Odometer (km)</label><input type="number" step="0.1" min="0" name="odometer_km"></div>
        <div class="fg"><label>Done By</label><input type="text" name="performed_by" placeholder="Mechanic or shop"></div>
        <div class="fg fg-full"><label>Notes</label><textarea name="notes" rows="2" placeholder="Anything worth noting"></textarea></div>
      </div>
      <div class="modal-actions"><button type="button" onclick="closeAt('atMaintModal')">Cancel</button><button type="submit" class="btn-maroon">Save</button></div>
    </form>
  </div>
</div>

<div class="modal" id="atEquipModal">
  <div class="modal-box modal-box-wide">
    <h3>Add Mechanical Equipment</h3>
    <form method="post" action="<?= base_url('assets-dept/equipment') ?>">
      <?= csrf_field() ?>
      <div class="form-grid2">
        <div class="fg"><label>Code <span class="required-mark">*</span></label><input type="text" name="code" placeholder="e.g. ME-GEN-03" required></div>
        <div class="fg"><label>Name <span class="required-mark">*</span></label><input type="text" name="name" placeholder="e.g. Diesel Generator" required></div>
        <div class="fg"><label>Type</label><input type="text" name="equipment_type" list="atEqTypes" placeholder="e.g. Generator">
          <datalist id="atEqTypes"><option>Generator</option><option>Water Pump</option><option>Compressor</option><option>Grounds Equipment</option><option>Workshop Equipment</option></datalist>
        </div>
        <div class="fg"><label>Location</label><input type="text" name="location" placeholder="e.g. Motor Pool Shop"></div>
        <div class="fg"><label>Condition</label>
          <select name="status"><?php foreach ($equipment_statuses as $s): ?><option><?= esc($s) ?></option><?php endforeach; ?></select>
        </div>
        <div class="fg"><label>Last Service</label><input type="date" name="last_service"></div>
        <div class="fg"><label>Next Service</label><input type="date" name="next_service"></div>
        <div class="fg fg-full"><label>Remarks</label><textarea name="remarks" rows="2"></textarea></div>
      </div>
      <div class="modal-actions"><button type="button" onclick="closeAt('atEquipModal')">Cancel</button><button type="submit" class="btn-maroon">Add</button></div>
    </form>
  </div>
</div>
<?php endif; ?>

<?php if ($section === 'work-orders'): ?>
<div class="modal" id="atWoModal">
  <div class="modal-box modal-box-wide">
    <h3>New Motor Pool Work Order</h3>
    <form method="post" action="<?= base_url('assets-dept/work-orders') ?>">
      <?= csrf_field() ?>
      <div class="form-grid2">
        <div class="fg"><label>Kind <span class="required-mark">*</span></label>
          <select name="wo_type" id="atWoType" onchange="atWoToggle()"><option value="Vehicle Repair">Vehicle Repair</option><option value="Mechanical Equipment">Mechanical Equipment</option></select>
        </div>
        <div class="fg"><label>Priority</label><select name="priority"><option>Routine</option><option>Urgent</option></select></div>
        <div class="fg" id="atWoVehicle"><label>Vehicle <span class="required-mark">*</span></label>
          <select name="vehicle_id"><option value="">— Select a Vehicle —</option><?php foreach ($vehicle_options as $o): ?><option value="<?= (int) $o['id'] ?>"><?= esc($o['label']) ?></option><?php endforeach; ?></select>
        </div>
        <div class="fg" id="atWoEquip" style="display:none"><label>Equipment <span class="required-mark">*</span></label>
          <select name="equipment_id"><option value="">— Select Equipment —</option><?php foreach ($equipment_options as $o): ?><option value="<?= (int) $o['id'] ?>"><?= esc($o['label']) ?></option><?php endforeach; ?></select>
        </div>
        <div class="fg fg-full"><label>Problem <span class="required-mark">*</span></label><textarea name="issue" rows="3" placeholder="What is wrong?" required></textarea></div>
      </div>
      <div class="modal-actions"><button type="button" onclick="closeAt('atWoModal')">Cancel</button><button type="submit" class="btn-maroon">Create</button></div>
    </form>
  </div>
</div>
<?php endif; ?>

<?php if ($section === 'trip-tickets'): ?>
<div class="modal" id="atTripModal">
  <div class="modal-box modal-box-wide">
    <h3>New Trip Ticket</h3>
    <form method="post" action="<?= base_url('assets-dept/trip-tickets') ?>">
      <?= csrf_field() ?>
      <div class="form-grid2">
        <div class="fg"><label>Requested By <span class="required-mark">*</span></label>
          <select name="requester_id" required><option value="">— Select a Person —</option>
            <?php foreach ($people_options as $o): ?><option value="<?= (int) $o['id'] ?>"<?= $o['emp'] === $me_emp_id ? ' selected' : '' ?>><?= esc($o['label']) ?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="fg"><label>Destination <span class="required-mark">*</span></label><input type="text" name="destination" placeholder="Where to?" required></div>
        <div class="fg"><label>Driver</label>
          <select name="driver_id"><option value="">— Assign later —</option><?php foreach ($driver_options as $o): ?><option value="<?= (int) $o['id'] ?>"><?= esc($o['label']) ?></option><?php endforeach; ?></select>
        </div>
        <div class="fg"><label>Vehicle</label>
          <select name="vehicle_id"><option value="">— Assign later —</option><?php foreach ($vehicle_options as $o): ?><option value="<?= (int) $o['id'] ?>"><?= esc($o['label']) ?></option><?php endforeach; ?></select>
        </div>
        <div class="fg"><label>Travel Date <span class="required-mark">*</span></label><input type="date" name="travel_date" value="<?= date('Y-m-d') ?>" required></div>
        <div class="fg" style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
          <div><label>Departure <span class="required-mark">*</span></label><input type="time" name="departure_time" value="08:00" required></div>
          <div><label>Return <span class="required-mark">*</span></label><input type="time" name="return_time" value="17:00" required></div>
        </div>
        <div class="fg fg-full"><label>Purpose <span class="required-mark">*</span></label><textarea name="purpose" rows="2" placeholder="What is the trip for?" required></textarea></div>
      </div>
      <p class="text-muted" style="font-size:12px;margin:6px 0 0;">With both a driver and a vehicle the ticket is created as Approved; otherwise it waits as Submitted.</p>
      <div class="modal-actions"><button type="button" onclick="closeAt('atTripModal')">Cancel</button><button type="submit" class="btn-maroon">Create</button></div>
    </form>
  </div>
</div>
<?php endif; ?>

<script src="<?= base_url('Assets/js/table-tools.js') ?>?v=<?= @filemtime(FCPATH . 'Assets/js/table-tools.js') ?>"></script>
<script>
function esc(s) {
  return String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
}
const addByTab = <?= json_encode($addByTab) ?>;
const atAlerts = <?= $alerts_json ?>;
const atAlertCols = <?= json_encode($alert_cols) ?>;
const atAlertTitles = <?= json_encode($alert_titles) ?>;
let atOpenAlert = null;

function openAt(id) { document.getElementById(id).style.display = 'flex'; }
function closeAt(id) { document.getElementById(id).style.display = 'none'; }
function atWoToggle() {
  const veh = document.getElementById('atWoType').value === 'Vehicle Repair';
  document.getElementById('atWoVehicle').style.display = veh ? '' : 'none';
  document.getElementById('atWoEquip').style.display = veh ? 'none' : '';
}

function switchAtTab(key) {
  document.querySelectorAll('#atTabs .sub-tab').forEach(b => b.classList.toggle('active', b.dataset.tab === key));
  document.querySelectorAll('.at-pane').forEach(p => p.style.display = p.id === 'at-pane-' + key ? '' : 'none');
  const btn = document.getElementById('atAddBtn');
  const add = addByTab[key];
  btn.style.display = add ? '' : 'none';
  if (add) { btn.textContent = add[0]; btn.setAttribute('onclick', add[1]); }
}

function atBadge(text, level) {
  const cls = level === 'red' ? 'tt-badge zb-overdue badge-blink' : 'tt-badge zb-needs';
  return `<span class="${cls}">${esc(text)}</span>`;
}
function toggleAtAlerts(level) {
  const panel = document.getElementById('atAlertList');
  const same = atOpenAlert === level && panel.style.display !== 'none';
  document.querySelectorAll('.fac-alert-btn').forEach(b => b.classList.remove('active'));
  if (same) { panel.style.display = 'none'; atOpenAlert = null; return; }
  atOpenAlert = level;
  document.getElementById(level === 'red' ? 'atAlertRed' : 'atAlertYellow').classList.add('active');
  const items = atAlerts.filter(i => i.level === level);
  panel.style.display = '';
  panel.innerHTML = `<div class="gc-title"><i class="bi bi-exclamation-triangle-fill"></i> ${esc(atAlertTitles[level])} <span class="text-muted">(${items.length})</span></div>
    <div class="table-wrap"><table class="sj-table"><thead><tr>${atAlertCols.map(c => `<th>${esc(c)}</th>`).join('')}</tr></thead>
    <tbody>${items.map(i => `<tr>${i.cols.map((c, n) => n === i.cols.length - 1 ? `<td>${atBadge(c, level)}</td>` : `<td>${esc(c)}</td>`).join('')}</tr>`).join('')}</tbody></table></div>`;
}
['red', 'yellow'].forEach(level => {
  const n = atAlerts.filter(i => i.level === level).length;
  if (!n) return;
  const id = level === 'red' ? 'atAlertRed' : 'atAlertYellow';
  document.getElementById(id + 'Count').textContent = n;
  document.getElementById(id).style.display = '';
});

document.querySelectorAll('.at-pane').forEach(attachTableTools);
const _si = document.getElementById('statInline'); if (_si) attachTableTools(_si);
</script>
<?= $this->endSection() ?>
