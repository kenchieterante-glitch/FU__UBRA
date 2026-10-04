<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
helper('facilities');
$subtitles = [
  'fire-safety' => 'Fire extinguishers, fire alarms, smoke detectors, and exit signs.',
  'guard'       => 'Key borrowing with QR scanning, guard records, and vehicle entry and exit.',
  'inspection'  => 'Monthly inspection form, building safety status, and history.',
];
?>
<div class="page-header">
  <div>
    <h1><?= esc($title) ?></h1>
    <p class="page-subtitle">Safety and Security Department — <?= esc($subtitles[$section] ?? '') ?></p>
  </div>
  <?php if ($section === 'fire-safety'): ?>
  <button type="button" class="btn-add" onclick="openFsAdd()">+ Add Equipment</button>
  <?php elseif ($section === 'inspection'): ?>
  <button type="button" class="btn-add" onclick="openInspForm()">+ Record Inspection</button>
  <?php endif; ?>
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

<?php
// Shared toolbar pieces -------------------------------------------------
$alertBtns = function (string $key, string $redTitle, string $yellowTitle) {
    return '<button type="button" class="fac-alert-icon fac-alert-btn alert-red fac-blink" id="' . $key . 'AlertRed" onclick="toggleAlertList(\'' . $key . '\',\'red\')" title="' . $redTitle . '" style="display:none"><i class="bi bi-exclamation-triangle-fill"></i> <strong id="' . $key . 'AlertRedCount"></strong></button>'
         . '<button type="button" class="fac-alert-icon fac-alert-btn alert-yellow" id="' . $key . 'AlertYellow" onclick="toggleAlertList(\'' . $key . '\',\'yellow\')" title="' . $yellowTitle . '" style="display:none"><i class="bi bi-exclamation-triangle-fill"></i> <strong id="' . $key . 'AlertYellowCount"></strong></button>';
};
$zoomSelect = function (string $svgId) use ($buildings) {
    $o = '<div class="map-zoom-row"><select class="fac-select" onchange="zoomMapTo(\'' . $svgId . '\', this.value)" aria-label="Zoom to a building"><option value="">— Select a Building —</option>';
    foreach ($buildings as $b) {
        $o .= '<option value="' . esc($b) . '">' . esc($b) . '</option>';
    }
    return $o . '</select></div>';
};
?>

<?php /* ===================== FIRE SAFETY ===================== */ ?>
<?php if ($section === 'fire-safety'): ?>
<div class="fac-pane">
  <div class="fe-toolbar-row">
    <div class="toolbar-search">
      <input type="text" id="fsSearch" class="search-box" placeholder="Search code, building, or type…" oninput="applyFsFilters()">
      <i class="bi bi-search search-icon"></i>
    </div>
    <div class="fe-toolbar-actions">
      <?= $alertBtns('fs', 'Equipment needing attention', 'Checks due soon') ?>
      <button type="button" class="filter-btn" id="fsMapBtn" onclick="toggleSecMap('fs')" title="Map" aria-label="Map"><i class="bi bi-map"></i></button>
      <button type="button" class="filter-btn" id="fpBtn" onclick="toggleFloorPlans()" title="Floor Plans" aria-label="Floor Plans"><i class="bi bi-layers"></i></button>
      <div class="filter-menu-wrapper">
        <button type="button" class="filter-btn" onclick="toggleFacFilter(this)" aria-label="Open filters"><i class="bi bi-funnel"></i></button>
        <div class="filter-popup">
          <div class="filter-popup-title">Filter</div>
          <div class="filter-row">
            <label for="fsStatusFilter">Status</label>
            <select id="fsStatusFilter" onchange="applyFsFilters()">
              <option value="">All statuses</option>
              <option>Good</option><option>Needs Repair</option><option>Needs Refill</option>
              <option>Defective</option><option>Missing</option>
              <option value="Overdue">Check Overdue</option><option value="Due in 7 Days">Check Due in 7 Days</option>
            </select>
          </div>
          <div class="filter-row">
            <label for="fsSort">Sort by</label>
            <select id="fsSort" onchange="sortFsRows()">
              <option value="code">Code (A–Z)</option>
              <option value="building">Building (A–Z)</option>
              <option value="next">Next check (soonest)</option>
            </select>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div id="fsAlertList" class="guard-card" style="display:none;padding:18px;margin-bottom:16px;"></div>

  <div id="fsMapPanel" class="guard-card" style="display:none;padding:18px;margin-bottom:16px;">
    <div class="gc-title"><i class="bi bi-map"></i> Fire Safety Map</div>
    <?= $zoomSelect('fsMapSVG') ?>
    <div class="fac-map-legend">
      <span><i class="map-pass"></i> All equipment Good</span>
      <span><i style="background:#ffc400"></i> Yellow alert = check due soon</span>
      <span><i class="map-alert"></i> Red warning = needs repair or missing</span>
    </div>
    <div class="fac-map-wrap"><svg id="fsMapSVG" xmlns="http://www.w3.org/2000/svg" style="width:100%;height:auto;max-height:640px;background:#ffffff;"></svg></div>
  </div>

  <div id="fpPanel" class="guard-card" style="display:none;padding:18px;margin-bottom:16px;">
    <div class="gc-title"><i class="bi bi-layers"></i> Floor Plans
      <button type="button" class="fac-alert-icon fac-alert-btn alert-red fac-blink" id="fpAlertBadge" style="display:none;margin-left:10px;" onclick="toggleAlertList('fs','red')" title="Equipment needing attention"><i class="bi bi-exclamation-triangle-fill"></i> <strong id="fpAlertCount"></strong></button>
    </div>
    <div class="fp-controls">
      <select id="fpBuilding" onchange="fpPickBuilding(this.value)"></select>
      <div class="sub-tabs" id="fpFloors"></div>
      <button type="button" class="fp-btn" id="fpPlaceBtn" onclick="fpTogglePlace()"><i class="bi bi-geo-alt"></i> Place equipment</button>
    </div>
    <div id="fpPlaceBar" class="fp-placebar" style="display:none;">
      <select id="fpType" onchange="fpTypeChanged()"></select>
      <input type="text" id="fpLabel" placeholder="Label (optional)">
      <label id="fpExpWrap" class="fp-hint" style="display:flex;align-items:center;gap:6px;">Expires <input type="date" id="fpExpiry"></label>
      <select id="fpStatus"><option>Working</option><option>Needs Repair</option><option>Missing</option></select>
      <span class="fp-hint">Click on the plan to drop the marker.</span>
    </div>
        <div id="fpEmpty" class="text-muted" style="display:none;">No floor plans found in the Floor Plans folder.</div>
    <div id="fpWrap" style="position:relative;display:inline-block;max-width:100%;line-height:0;">
      <img id="fpImg" alt="Floor plan" style="max-width:100%;height:auto;display:block;border:1px solid #ddd;">
      <div id="fpLayer" style="position:absolute;inset:0;"></div>
    </div>
    <div class="gc-title" style="margin-top:18px;"><i class="bi bi-table"></i> Equipment placed on floor plans</div>
    <div class="table-wrap"><table class="sj-table">
      <thead><tr><th>Type</th><th>Label</th><th>Building</th><th>Floor</th><th>Expires</th><th>Status</th><th>Added by</th></tr></thead>
      <tbody id="fpTableBody"></tbody>
    </table></div>
  </div>

  <div class="sub-tabs" id="fsTabs">
    <?php foreach ($equipment_types as $i => $t): ?>
      <button class="sub-tab<?= $i === 0 ? ' active' : '' ?>" data-fs="<?= $i ?>" onclick="switchFsTab(<?= $i ?>)"><?= esc($t) ?></button>
    <?php endforeach; ?>
  </div>

  <?php foreach ($equipment_types as $i => $t): ?>
    <div class="fs-pane" id="fs-pane-<?= $i ?>" style="<?= $i === 0 ? '' : 'display:none' ?>">
      <div class="table-wrap">
        <table class="sj-table">
          <thead><tr><th>Code</th><th>Building</th><th>Floor</th><th>Details</th><th>Status</th><th>Next Check</th></tr></thead>
          <tbody class="fs-body">
            <?php $list = $equipment_by_type[$t] ?? []; if (empty($list)): ?>
              <tr><td colspan="6" class="empty-row">No <?= esc(strtolower($t)) ?> recorded yet.</td></tr>
            <?php else: foreach ($list as $r): $shown = $r['status'] !== 'Working' ? $r['status'] : ($r['due'] === 'OK' ? 'Good' : $r['due']); ?>
              <tr class="fs-row" style="cursor:pointer" onclick="showFsDetail(this)"
                  data-type="<?= esc($r['type']) ?>" data-code="<?= esc($r['code']) ?>" data-building="<?= esc($r['building']) ?>" data-floor="<?= esc($r['floor'] ?? '') ?>"
                  data-status="<?= esc($r['status']) ?>" data-due="<?= esc($r['due']) ?>" data-shown="<?= esc($shown) ?>" data-next="<?= esc((string) $r['next']) ?>"
                  data-installed="<?= esc((string) ($r['installed'] ?? '')) ?>" data-expires="<?= esc((string) ($r['expires'] ?? '')) ?>" data-detail="<?= esc((string) $r['detail']) ?>" data-remarks="<?= esc((string) ($r['remarks'] ?? '')) ?>">
                <td><strong><?= esc($r['code']) ?></strong></td>
                <td><?= esc($r['building']) ?></td>
                <td><?= esc($r['floor'] ?? '—') ?></td>
                <td><?= esc($r['detail'] ?: '—') ?></td>
                <?= fac_cell($shown) ?>
                <td><?= !empty($r['next']) ? date('M d, Y', strtotime($r['next'])) : '—' ?></td>
              </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<div class="modal" id="fsAddModal">
  <div class="modal-box modal-box-wide">
    <h3>Add Fire Safety Equipment</h3>
    <form method="post" action="<?= base_url('security-dept/equipment') ?>">
      <?= csrf_field() ?>
      <div class="form-grid2">
        <div class="fg">
          <label>Equipment Type <span class="required-mark">*</span></label>
          <select name="equipment_type" id="fsAddType" onchange="fsAddToggle()" required>
            <?php foreach ($equipment_types as $t): ?><option><?= esc($t) ?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="fg">
          <label>Code / Unit ID <span class="required-mark">*</span></label>
          <input type="text" name="code" placeholder="e.g. FA-ADM-02" required>
        </div>
        <div class="fg">
          <label>Building <span class="required-mark">*</span></label>
          <select name="building" required>
            <option value="">— Select a Building —</option>
            <?php foreach ($buildings as $b): ?><option value="<?= esc($b) ?>"><?= esc($b) ?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="fg">
          <label>Floor</label>
          <select name="floor"><option>Ground Floor</option><option>2nd Floor</option><option>3rd Floor</option><option>4th Floor</option></select>
        </div>
        <div class="fg ext-only">
          <label>Extinguisher Type</label>
          <select name="ext_type"><option>CO2</option><option>Dry Chemical</option><option>Foam</option><option>Wet Chemical</option></select>
        </div>
        <div class="fg ext-only">
          <label>Weight (kg)</label>
          <input type="number" step="0.1" min="0" name="weight_kg" value="6.0">
        </div>
        <div class="fg other-only">
          <label>Status</label>
          <select name="status"><option>Working</option><option>Needs Repair</option><option>Missing</option></select>
        </div>
        <div class="fg other-only">
          <label>Specific Place</label>
          <input type="text" name="location_note" placeholder="e.g. Main lobby">
        </div>
        <div class="fg dated-only">
          <label>Date Installed</label>
          <input type="date" name="installed_on">
        </div>
        <div class="fg dated-only">
          <label>Expiry Date</label>
          <input type="date" name="expires_on">
        </div>
        <div class="fg">
          <label>Last Checked</label>
          <input type="date" name="last_checked">
        </div>
        <div class="fg">
          <label>Next Check</label>
          <input type="date" name="next_check">
        </div>
        <div class="fg fg-full other-only">
          <label>Remarks</label>
          <textarea name="remarks" rows="2" placeholder="Anything worth noting"></textarea>
        </div>
      </div>
      <div class="modal-actions">
        <button type="button" onclick="closeFsAdd()">Cancel</button>
        <button type="submit" class="btn-maroon">Add</button>
      </div>
    </form>
  </div>
</div>

<div id="fsDetailModal" class="sj-modal-overlay" style="display:none">
  <div class="sj-modal">
    <div class="sj-modal-header" style="display:flex;justify-content:space-between;align-items:center;">
      <h3 id="fsDetailTitle"></h3>
      <button type="button" class="dp-close" onclick="document.getElementById('fsDetailModal').style.display='none'" aria-label="Close"><i class="bi bi-x-lg"></i></button>
    </div>
    <div class="sj-modal-body" id="fsDetailBody"></div>
  </div>
</div>
<?php endif; ?>

<?php /* ===================== GUARD / SECURITY MONITORING ===================== */ ?>
<?php if ($section === 'guard'): ?>
<div class="fac-pane">
  <div class="fe-toolbar-row">
    <div class="toolbar-search">
      <input type="text" id="gSearch" class="search-box" placeholder="Search borrower, key, trip, or guard…" oninput="applyGFilters()">
      <i class="bi bi-search search-icon"></i>
    </div>
    <div class="fe-toolbar-actions">
      <?= $alertBtns('g', 'Keys out over 8 hours', 'Trips awaiting dispatch') ?>
      <div class="filter-menu-wrapper">
        <button type="button" class="filter-btn" onclick="toggleFacFilter(this)" aria-label="Open filters"><i class="bi bi-funnel"></i></button>
        <div class="filter-popup">
          <div class="filter-popup-title">Filter</div>
          <div class="filter-row">
            <label for="gStatusFilter">Key status</label>
            <select id="gStatusFilter" onchange="applyGFilters()">
              <option value="">All</option><option value="Borrowed">Borrowed</option><option value="Returned">Returned</option>
            </select>
          </div>
          <div class="filter-row">
            <label for="gSort">Sort by</label>
            <select id="gSort" onchange="sortGRows()">
              <option value="newest">Newest</option><option value="oldest">Oldest</option><option value="latest">Latest borrowed</option>
            </select>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div id="gAlertList" class="guard-card" style="display:none;padding:18px;margin-bottom:16px;"></div>

  <div class="sub-tabs" id="gTabs">
    <button class="sub-tab active" data-g="keys" onclick="switchGTab('keys')"><i class="bi bi-key-fill"></i> Key Borrowing</button>
    <button class="sub-tab" data-g="history" onclick="switchGTab('history')">Borrowing History</button>
    <button class="sub-tab" data-g="records" onclick="switchGTab('records')">Guard Records</button>
    <button class="sub-tab" data-g="vehicles" onclick="switchGTab('vehicles')">Vehicle Entry / Exit</button>
    <button class="sub-tab" data-g="monitor" onclick="switchGTab('monitor')">Security Monitoring</button>
  </div>

  <div class="g-pane" id="g-keys">
    <div class="gc-title" style="margin:6px 0 10px;"><i class="bi bi-key-fill"></i> Keys Currently Out</div>
    <div class="table-wrap">
      <table class="sj-table">
        <thead><tr><th>Log #</th><th>Borrower</th><th>Department</th><th>Key Item</th><th>Date Borrowed</th><th>Status</th></tr></thead>
        <tbody class="g-body">
          <?php if (empty($active_keys)): ?>
            <tr><td colspan="6" class="empty-row">No keys are out right now.</td></tr>
          <?php else: foreach ($active_keys as $l): ?>
            <tr class="g-row" style="cursor:pointer" onclick="showKeyDetail(<?= (int) $l['id'] ?>)" data-id="<?= (int) $l['id'] ?>" data-status="Borrowed" data-time="<?= esc($l['scan_in']) ?>">
              <td><strong><?= esc($l['log_number']) ?></strong></td>
              <td><?= esc($l['full_name']) ?></td>
              <td><?= esc($l['department']) ?></td>
              <td><?= esc($l['key_item']) ?></td>
              <td><?= date('M d, Y g:i A', strtotime($l['scan_in'])) ?></td>
              <?= fac_cell('Borrowed') ?>
            </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="g-pane" id="g-history" style="display:none">
    <div class="table-wrap">
      <table class="sj-table">
        <thead><tr><th>Log #</th><th>Borrower</th><th>Key Item</th><th>Date Borrowed</th><th>Returned</th><th>Status</th></tr></thead>
        <tbody class="g-body">
          <?php if (empty($key_logs)): ?>
            <tr><td colspan="6" class="empty-row">No borrowing history yet.</td></tr>
          <?php else: foreach ($key_logs as $l): $st = $l['status'] === 'Active' ? 'Borrowed' : 'Returned'; ?>
            <tr class="g-row" style="cursor:pointer" onclick="showKeyDetail(<?= (int) $l['id'] ?>)" data-id="<?= (int) $l['id'] ?>" data-status="<?= $st ?>" data-time="<?= esc($l['scan_in']) ?>">
              <td><strong><?= esc($l['log_number']) ?></strong></td>
              <td><?= esc($l['full_name']) ?></td>
              <td><?= esc($l['key_item']) ?></td>
              <td><?= date('M d, Y g:i A', strtotime($l['scan_in'])) ?></td>
              <td><?= !empty($l['scan_out']) ? date('M d, Y g:i A', strtotime($l['scan_out'])) : '—' ?></td>
              <?= fac_cell($st) ?>
            </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="g-pane" id="g-records" style="display:none">
    <div class="table-wrap">
      <table class="sj-table">
        <thead><tr><th>Date</th><th>Time</th><th>Guard</th><th>Activity</th></tr></thead>
        <tbody class="g-body">
          <?php if (empty($events)): ?>
            <tr><td colspan="4" class="empty-row">No guard records yet.</td></tr>
          <?php else: foreach ($events as $e): ?>
            <tr class="g-row" data-status="" data-time="<?= esc($e['time']) ?>">
              <td><?= date('M d, Y', strtotime($e['time'])) ?></td>
              <td><?= date('g:i A', strtotime($e['time'])) ?></td>
              <td><?= esc($e['guard']) ?></td>
              <td><?= esc($e['action']) ?></td>
            </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="g-pane" id="g-vehicles" style="display:none">
    <div class="table-wrap">
      <table class="sj-table">
        <thead><tr><th>Trip ID</th><th>Requester</th><th>Destination</th><th>Assigned Driver</th><th>Departure</th><th>Gate Status</th><th>Action</th></tr></thead>
        <tbody class="g-body">
          <?php if (empty($gate_trips)): ?>
            <tr><td colspan="7" class="empty-row">No approved trips waiting at the gate.</td></tr>
          <?php else: foreach ($gate_trips as $t): ?>
            <tr class="g-row" data-status="" data-time="<?= esc($t['travel_date'] . ' ' . $t['departure_time']) ?>">
              <td><strong><?= esc($t['trip_id']) ?></strong></td>
              <td><?= esc($t['requester_name'] ?? 'Unknown') ?></td>
              <td><?= esc($t['destination']) ?></td>
              <td><?= esc($t['driver_name'] ?? 'Unassigned') ?><br><small class="text-muted"><?= esc($t['plate_no'] ?? 'No vehicle') ?></small></td>
              <td><?= date('M d, h:i A', strtotime($t['travel_date'] . ' ' . $t['departure_time'])) ?></td>
              <?= fac_cell($t['status'] === 'In Transit' ? 'Vehicle Out' : 'Awaiting Dispatch') ?>
              <td>
                <form method="post" action="<?= base_url(($t['status'] === 'In Transit' ? 'travel/checkout/' : 'travel/checkin/') . $t['id']) ?>" style="display:inline;">
                  <?= csrf_field() ?>
                  <button type="submit" class="status-badge <?= $t['status'] === 'In Transit' ? 'status-completed' : 'status-pending' ?> fac-action-pill"><?= $t['status'] === 'In Transit' ? 'Verify Return' : 'Verify Exit' ?></button>
                </form>
              </td>
            </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="g-pane" id="g-monitor" style="display:none">
    <div class="guard-card" style="padding:18px;">
      <div class="gc-title"><i class="bi bi-eye"></i> Security Monitoring — what needs the guard's attention</div>
      <div class="table-wrap">
        <table class="sj-table">
          <thead><tr><th>Name</th><th>Details</th><th>Reference</th><th>Alert</th></tr></thead>
          <tbody id="monitorBody"></tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<div id="keyDetailModal" class="sj-modal-overlay" style="display:none">
  <div class="sj-modal">
    <div class="sj-modal-header" style="display:flex;justify-content:space-between;align-items:center;">
      <h3 id="keyDetailTitle"></h3>
      <button type="button" class="dp-close" onclick="document.getElementById('keyDetailModal').style.display='none'" aria-label="Close"><i class="bi bi-x-lg"></i></button>
    </div>
    <div class="sj-modal-body" id="keyDetailBody"></div>
  </div>
</div>
<?php endif; ?>

<?php /* ===================== SAFETY INSPECTION ===================== */ ?>
<?php if ($section === 'inspection'): ?>
<div class="fac-pane">
  <div class="fe-toolbar-row">
    <div class="toolbar-search">
      <input type="text" id="inSearch" class="search-box" placeholder="Search building, inspector, or remarks…" oninput="applyInFilters()">
      <i class="bi bi-search search-icon"></i>
    </div>
    <div class="fe-toolbar-actions">
      <?= $alertBtns('in', 'Buildings needing attention', 'Buildings not inspected yet') ?>
      <button type="button" class="filter-btn" id="inMapBtn" onclick="toggleSecMap('in')" title="Map" aria-label="Map"><i class="bi bi-map"></i></button>
      <div class="filter-menu-wrapper">
        <button type="button" class="filter-btn" onclick="toggleFacFilter(this)" aria-label="Open filters"><i class="bi bi-funnel"></i></button>
        <div class="filter-popup">
          <div class="filter-popup-title">Filter</div>
          <div class="filter-row">
            <label for="inStatusFilter">Safety status</label>
            <select id="inStatusFilter" onchange="applyInFilters()">
              <option value="">All</option><option>Safe</option><option>Needs Attention</option><option>Unsafe</option><option value="Not inspected">Not inspected</option>
            </select>
          </div>
          <div class="filter-row">
            <label for="inSort">Sort by</label>
            <select id="inSort" onchange="sortInRows()">
              <option value="building">Building (A–Z)</option><option value="oldest">Oldest</option><option value="newest">Newest</option><option value="latest">Latest inspection</option>
            </select>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div id="inAlertList" class="guard-card" style="display:none;padding:18px;margin-bottom:16px;"></div>

  <div id="inMapPanel" class="guard-card" style="display:none;padding:18px;margin-bottom:16px;">
    <div class="gc-title"><i class="bi bi-map"></i> Safety Map — <?= esc($inspection_month) ?></div>
    <?= $zoomSelect('inMapSVG') ?>
    <div class="fac-map-legend">
      <span><i class="map-pass"></i> Safe</span>
      <span><i style="background:#ffc400"></i> Yellow alert = not inspected yet</span>
      <span><i class="map-alert"></i> Red warning = needs attention or unsafe</span>
    </div>
    <div class="fac-map-wrap"><svg id="inMapSVG" xmlns="http://www.w3.org/2000/svg" style="width:100%;height:auto;max-height:640px;background:#ffffff;"></svg></div>
  </div>

  <div class="sub-tabs" id="inTabs">
    <button class="sub-tab active" data-in="status" onclick="switchInTab('status')">Safety Status</button>
    <button class="sub-tab" data-in="history" onclick="switchInTab('history')">Inspection History</button>
  </div>

  <div class="in-pane" id="in-status">
    <div class="table-wrap">
      <table class="sj-table">
        <thead><tr><th>Building</th><th>Safety Status (<?= esc($inspection_month) ?>)</th><th>Inspected By</th><th>Date</th><th>Remarks</th></tr></thead>
        <tbody id="inBody">
          <?php foreach ($current as $idx => $i): $st = $i['safety_status'] ?? 'Not inspected'; ?>
            <tr class="in-row" style="cursor:pointer" onclick="showInspDetail(this.dataset.name)" data-idx="<?= (int) $idx ?>" data-name="<?= esc($i['building']) ?>" data-status="<?= esc($st) ?>" data-last="<?= esc((string) $i['inspected_at']) ?>">
              <td><strong><?= esc($i['building']) ?></strong></td>
              <?= fac_cell($st) ?>
              <td><?= esc($i['inspected_by'] ?? '—') ?></td>
              <td><?= !empty($i['inspected_at']) ? date('M d, Y', strtotime($i['inspected_at'])) : '—' ?></td>
              <td><?= esc($i['remarks'] ?? '—') ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="in-pane" id="in-history" style="display:none">
    <div class="table-wrap">
      <table class="sj-table">
        <thead><tr><th>Building</th><th>Month</th><th>Safety Status</th><th>Inspected By</th><th>Date</th><th>Remarks</th></tr></thead>
        <tbody>
          <?php if (empty($history)): ?>
            <tr><td colspan="6" class="empty-row">No inspections recorded yet.</td></tr>
          <?php else: foreach ($history as $h): ?>
            <tr>
              <td><strong><?= esc($h['building']) ?></strong></td>
              <td><?= date('F Y', strtotime($h['inspection_month'] . '-01')) ?></td>
              <?= fac_cell($h['safety_status']) ?>
              <td><?= esc($h['inspected_by']) ?></td>
              <td><?= date('M d, Y', strtotime($h['inspected_at'])) ?></td>
              <td><?= esc($h['remarks'] ?? '—') ?></td>
            </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<div class="modal" id="inspModal">
  <div class="modal-box modal-box-wide">
    <h3>Monthly Safety Inspection — <?= esc($inspection_month) ?></h3>
    <form method="post" action="<?= base_url('security-dept/inspections') ?>">
      <?= csrf_field() ?>
      <div class="form-grid2">
        <div class="fg">
          <label>Building <span class="required-mark">*</span></label>
          <select name="building" id="inspBuilding" required>
            <option value="">— Select a Building —</option>
            <?php foreach ($buildings as $b): ?><option value="<?= esc($b) ?>"><?= esc($b) ?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="fg">
          <label>Safety Status <span class="required-mark">*</span></label>
          <select name="safety_status" required><option>Safe</option><option>Needs Attention</option><option>Unsafe</option></select>
        </div>
        <div class="fg fg-full">
          <label>Remarks</label>
          <textarea name="remarks" rows="2" placeholder="What was found during this month's inspection"></textarea>
        </div>
      </div>
      <div class="modal-actions">
        <button type="button" onclick="closeInspForm()">Cancel</button>
        <button type="submit" class="btn-maroon">Save</button>
      </div>
    </form>
  </div>
</div>

<div id="inspDetailModal" class="sj-modal-overlay" style="display:none">
  <div class="sj-modal">
    <div class="sj-modal-header" style="display:flex;justify-content:space-between;align-items:center;">
      <h3 id="inspDetailTitle"></h3>
      <button type="button" class="dp-close" onclick="document.getElementById('inspDetailModal').style.display='none'" aria-label="Close"><i class="bi bi-x-lg"></i></button>
    </div>
    <div class="sj-modal-body" id="inspDetailBody"></div>
  </div>
</div>
<?php endif; ?>

<script src="<?= base_url('Assets/js/campus-map.js') ?>?v=<?= @filemtime(FCPATH . 'Assets/js/campus-map.js') ?>"></script>
<script>
function esc(s) {
  return String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
}

function toggleFacFilter(button) {
  const popup = button.parentElement.querySelector('.filter-popup');
  const open = !popup.classList.contains('visible');
  document.querySelectorAll('.filter-popup.visible').forEach(p => p.classList.remove('visible'));
  popup.classList.toggle('visible', open);
}
document.addEventListener('click', e => {
  if (!e.target.closest('.filter-menu-wrapper')) {
    document.querySelectorAll('.filter-popup.visible').forEach(p => p.classList.remove('visible'));
  }
});

const alertBadges = {
  'Overdue': 'zb-overdue badge-blink', 'Needs Repair': 'zb-overdue badge-blink', 'Missing': 'zb-overdue badge-blink',
  'Defective': 'zb-overdue badge-blink', 'Unsafe': 'zb-overdue badge-blink', 'Needs Attention': 'zb-overdue badge-blink',
  'Key out over 8 hours': 'zb-overdue badge-blink',
  'Due in 7 Days': 'zb-needs', 'Not inspected': 'zb-needs', 'Awaiting dispatch': 'zb-needs',
};
function alertCell(c) {
  const cls = alertBadges[c];
  return cls ? `<td><span class="tt-badge ${cls}">${esc(c)}</span></td>` : `<td>${esc(c)}</td>`;
}
const openAlert = {};
function toggleAlertList(key, level) {
  const panel = document.getElementById(key + 'AlertList');
  const d = alertData[key];
  const same = openAlert[key] === level && panel.style.display !== 'none';
  document.querySelectorAll(`[id^="${key}Alert"][id$="Count"]`).forEach(c => c.parentElement.classList.remove('active'));
  if (same) { panel.style.display = 'none'; openAlert[key] = null; return; }
  openAlert[key] = level;
  const items = d.items.filter(i => i.level === level);
  const btn = document.getElementById(key + 'Alert' + level.charAt(0).toUpperCase() + level.slice(1));
  if (btn) btn.classList.add('active');
  panel.style.display = '';
  panel.innerHTML = `
    <div class="gc-title"><i class="bi bi-exclamation-triangle-fill"></i> ${esc(d.titles[level])} <span class="text-muted">(${items.length})</span></div>
    <div class="table-wrap"><table class="sj-table"><thead><tr>${d.cols.map(c => `<th>${esc(c)}</th>`).join('')}</tr></thead>
    <tbody>${items.length ? items.map(i => `<tr>${i.cols.map(alertCell).join('')}</tr>`).join('') : `<tr><td colspan="${d.cols.length}" class="empty-row">Nothing here.</td></tr>`}</tbody></table></div>`;
}
function initAlertIcons() {
  Object.keys(alertData).forEach(key => {
    ['red', 'yellow'].forEach(level => {
      const n = alertData[key].items.filter(i => i.level === level).length;
      const id = key + 'Alert' + level.charAt(0).toUpperCase() + level.slice(1);
      const btn = document.getElementById(id);
      if (!btn) return;
      document.getElementById(id + 'Count').textContent = n;
      btn.style.display = n ? '' : 'none';
    });
  });
}

function toggleSecMap(key) {
  const panel = document.getElementById(key + 'MapPanel');
  const open = panel.style.display === 'none';
  panel.style.display = open ? '' : 'none';
  document.getElementById(key + 'MapBtn').classList.toggle('active', open);
}

function detailGrid(fields) {
  return `<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:12px 18px;">${fields.map(([k, v]) => `<div><div class="text-muted">${esc(k)}</div><strong>${esc(v)}</strong></div>`).join('')}</div>`;
}

<?php if ($section === 'fire-safety'): ?>
const alertData = { fs: { items: <?= $alerts_json ?>, titles: { red: 'Equipment needing attention', yellow: 'Checks due soon' }, cols: ['Type', 'Code', 'Building', 'Status'] } };
initAlertIcons();
renderMapImage('fsMapSVG', { imageUrl: '<?= base_url('images/MAP.jpg') ?>', stateByName: <?= $map_state_json ?>, hideFloorText: true,
  onSelect: name => {
    if (fpPlans.some(p => p.campus === name)) { fpOpenCampus(name); return; }
    document.getElementById('fsSearch').value = name; applyFsFilters();
  } });

// ---- Floor plans (Safety & Security only; the campus map above is untouched) ----
const fpPlans = <?= $plans_json ?>;
let fpMarkers = <?= $markers_json ?>;
const fpTypes = <?= $marker_types_json ?>;
const fpCsrf = { name: '<?= csrf_token() ?>', hash: '<?= csrf_hash() ?>' };
const fpBase = '<?= site_url('security-dept/markers') ?>';
const fpLetter = { 'Fire Extinguisher': 'E', 'Fire Alarm': 'A', 'Smoke Detector': 'S', 'Emergency Exit Sign': 'X' };
let fpBuilding = null, fpPlan = null, fpPlacing = false;

function fpBuildingNames() { return [...new Set(fpPlans.map(p => p.building))]; }
function toggleFloorPlans(forceOpen) {
  const el = document.getElementById('fpPanel');
  const show = forceOpen === true ? true : el.style.display === 'none';
  el.style.display = show ? '' : 'none';
  if (show && !fpBuilding) fpInit();
}
function fpInit() {
  const names = fpBuildingNames();
  document.getElementById('fpBuilding').innerHTML = names.map(n => `<option value="${esc(n)}">${esc(n)}</option>`).join('');
  document.getElementById('fpType').innerHTML = fpTypes.map(t => `<option>${esc(t)}</option>`).join('');
  fpTypeChanged();
  const empty = !names.length;
  document.getElementById('fpEmpty').style.display = empty ? '' : 'none';
  document.getElementById('fpWrap').style.display = empty ? 'none' : 'inline-block';
  if (!empty) fpPickBuilding(names[0]);
}
function fpOpenCampus(campus) {
  const p = fpPlans.find(x => x.campus === campus);
  toggleFloorPlans(true);
  if (!fpBuilding) fpInit();
  document.getElementById('fpBuilding').value = p.building;
  fpPickBuilding(p.building);
}
function fpPickBuilding(name) {
  fpBuilding = name;
  const floors = fpPlans.filter(p => p.building === name);
  document.getElementById('fpFloors').innerHTML = floors.map((p, i) => `<button type="button" class="sub-tab" data-file="${esc(p.file)}" onclick="fpPickPlan('${esc(p.file).replace(/'/g, '&#39;')}')">${esc(p.floor)}</button>`).join('');
  fpPickPlan(floors[0].file);
  fpTabAlerts();
}
function fpPickPlan(file) {
  fpPlan = fpPlans.find(p => p.file === file);
  document.querySelectorAll('#fpFloors .sub-tab').forEach(b => b.classList.toggle('active', b.dataset.file === file));
  document.getElementById('fpImg').src = fpPlan.url;
  fpDraw();
}
const fpBaseAlerts = alertData.fs.items.filter(i => !i.src);
const fpRegister = <?= $rows_json ?>.filter(r => r.src !== 'plan');
const fpDated = ['Fire Extinguisher', 'Smoke Detector'];
function fpTypeChanged() { document.getElementById('fpExpWrap').style.display = fpDated.includes(document.getElementById('fpType').value) ? 'flex' : 'none'; }
// Effective state: a working item past its expiry date is "Expired"; within 30 days it is "Due Soon".
function fpState(m) {
  if (m.status !== 'Working') return m.status;
  if (!m.exp || !fpDated.includes(m.type)) return 'Working';
  const days = Math.floor((new Date(m.exp + 'T00:00:00') - new Date(new Date().toDateString())) / 86400000);
  return days < 0 ? 'Expired' : (days <= 30 ? 'Due Soon' : 'Working');
}
const fpIsRed = st => st !== 'Working' && st !== 'Due Soon';
function fpFmtDate(d) { return d ? new Date(d + 'T00:00:00').toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' }) : '—'; }
function fpPlanOf(file) { return fpPlans.find(p => p.file === file) || { building: '—', floor: '—' }; }
function fpTabAlerts() {
  const regCount = p => fpRegister.filter(r => p.campus && r.building === p.campus && (r.floor || 'Ground Floor') === p.floor && (r.status !== 'Working' || r.due !== 'OK')).length;
  const badCount = file => { const p = fpPlans.find(x => x.file === file); return fpMarkers.filter(m => m.plan === file && fpState(m) !== 'Working').length + (p ? regCount(p) : 0); };
  document.querySelectorAll('#fpFloors .sub-tab').forEach(b => {
    const n = badCount(b.dataset.file);
    b.querySelector('.fp-tab-alert')?.remove();
    if (n) b.insertAdjacentHTML('beforeend', ' <span class="fp-tab-alert fac-blink" title="' + n + ' need attention"><i class="bi bi-exclamation-triangle-fill"></i></span>');
  });
  document.querySelectorAll('#fpBuilding option').forEach(o => {
    const files = fpPlans.filter(p => p.building === o.value).map(p => p.file);
    const n = fpMarkers.filter(m => files.includes(m.plan) && fpState(m) !== 'Working').length + fpPlans.filter(p => p.building === o.value).reduce((a, p) => a + regCount(p), 0);
    o.textContent = o.value + (n ? '  ⚠' : '');
  });
}
function fpSync() {
  fpTabAlerts();
  const bad = fpMarkers.filter(m => fpState(m) !== 'Working');
  alertData.fs.items = fpBaseAlerts.concat(bad.map(m => ({
    cols: [m.type, m.label || 'Floor plan marker', fpPlanOf(m.plan).building + ' — ' + fpPlanOf(m.plan).floor, fpState(m)], level: fpIsRed(fpState(m)) ? 'red' : 'yellow',
  })));
  initAlertIcons();
  const badge = document.getElementById('fpAlertBadge');
  const redN = alertData.fs.items.filter(i => i.level === 'red').length;
  badge.style.display = redN ? '' : 'none';
  document.getElementById('fpAlertCount').textContent = redN;
  const cls = { 'Working': 'tt-badge zb-done', 'Needs Repair': 'tt-badge zb-overdue badge-blink', 'Missing': 'tt-badge zb-overdue badge-blink', 'Expired': 'tt-badge zb-overdue badge-blink', 'Due Soon': 'tt-badge zb-needs' };
  document.getElementById('fpTableBody').innerHTML = fpMarkers.length ? fpMarkers.slice().sort((a, b) => b.id - a.id).map(m => {
    const p = fpPlanOf(m.plan);
    return '<tr style="cursor:pointer" onclick="fpGoto(' + m.id + ')"><td><strong>' + esc(m.type) + '</strong></td><td>' + esc(m.label || '—') + '</td><td>' + esc(p.building) + '</td><td>' + esc(p.floor) + '</td><td>' + fpFmtDate(m.exp) + '</td><td><span class="' + (cls[fpState(m)] || 'tt-badge') + '">' + esc(fpState(m)) + '</span></td><td>' + esc(m.by || '—') + '</td></tr>';
  }).join('') : '<tr><td colspan="7" class="empty-row">Nothing placed on the floor plans yet.</td></tr>';
}
function fpGoto(id) {
  const m = fpMarkers.find(x => x.id === id); if (!m) return;
  const p = fpPlanOf(m.plan);
  document.getElementById('fpBuilding').value = p.building;
  fpPickBuilding(p.building); fpPickPlan(p.file);
}
function fpDraw() {
  fpSync();
  const layer = document.getElementById('fpLayer');
  layer.innerHTML = '';

  // One-line legend inside the plan frame (top strip), sized from the picture width.
  const dot = c => '<span style="display:inline-block;width:.85em;height:.85em;border-radius:50%;background:' + c + ';vertical-align:-1px;margin-right:.4em;"></span>';
  const legend = document.createElement('div');
  legend.style.cssText = 'position:absolute;left:1px;right:1px;top:1px;padding:.6em .8em;background:#fff;pointer-events:none;white-space:nowrap;overflow:hidden;line-height:1.3;font-family:Arial,sans-serif;color:#111;font-weight:600;';
  legend.innerHTML = [dot('#2e7d32') + 'Working', dot('#f9a825') + 'Due soon', dot('#d32f2f') + 'Needs repair / missing / expired']
    .concat(fpTypes.map(t => '<b>' + fpLetter[t] + '</b> = ' + esc(t))).join('<span style="margin:0 .7em;color:#bbb;">|</span>');
  layer.appendChild(legend);
  // Shrink the text until the whole line fits the frame width (never cropped).
  const fit = () => {
    const img = document.getElementById('fpImg');
    let px = Math.min(18, Math.max(9, img.clientWidth * 0.012));
    legend.style.fontSize = px + 'px';
    while (legend.scrollWidth > legend.clientWidth && px > 7) { px -= 0.5; legend.style.fontSize = px + 'px'; }
  };
  fit(); document.getElementById('fpImg').onload = fit; window.onresize = fit;
  fpMarkers.filter(m => m.plan === fpPlan.file).forEach(m => {
    const st = fpState(m), bad = st !== 'Working', red = fpIsRed(st);
    const b = document.createElement('button');
    b.type = 'button';
    b.textContent = fpLetter[m.type] || '?';
    b.title = `${m.type}${m.label ? ' — ' + m.label : ''} (${st}${m.exp ? ', expires ' + fpFmtDate(m.exp) : ''})`;
    b.className = red ? 'badge-blink' : '';
    b.style.cssText = `position:absolute;left:${m.x}%;top:${m.y}%;transform:translate(-50%,-50%);width:24px;height:24px;border-radius:50%;border:2px solid #fff;color:#fff;font-weight:700;font-size:12px;line-height:20px;padding:0;cursor:pointer;background:${red ? '#d32f2f' : (bad ? '#f9a825' : '#2e7d32')};box-shadow:0 1px 4px rgba(0,0,0,.5);`;
    if (bad) b.insertAdjacentHTML('beforeend', `<span class="fp-mark-alert${red ? ' fac-blink' : ''}" style="color:${red ? '#d32f2f' : '#e6a100'}"><i class="bi bi-exclamation-triangle-fill"></i></span>`);
    b.onclick = e => { e.stopPropagation(); fpShowMarker(m); };
    layer.appendChild(b);
  });
  layer.style.cursor = fpPlacing ? 'crosshair' : 'default';
}
function fpTogglePlace() {
  fpPlacing = !fpPlacing;
  document.getElementById('fpPlaceBar').style.display = fpPlacing ? 'flex' : 'none';
  document.getElementById('fpPlaceBtn').innerHTML = fpPlacing ? '<i class="bi bi-check2"></i> Done placing' : '<i class="bi bi-geo-alt"></i> Place equipment';
  fpDraw();
}
function fpPost(url, data) {
  const body = new URLSearchParams(Object.assign({ [fpCsrf.name]: fpCsrf.hash }, data));
  return fetch(url, { method: 'POST', body, headers: { 'X-Requested-With': 'XMLHttpRequest' } }).then(r => {
    const t = r.headers.get('X-CSRF-TOKEN'); if (t) fpCsrf.hash = t;
    return r.json().then(j => { if (!r.ok) throw new Error(j.error || 'Request failed'); return j; });
  });
}
document.getElementById('fpLayer').addEventListener('click', e => {
  if (!fpPlacing || e.target !== e.currentTarget) return;
  const r = e.currentTarget.getBoundingClientRect();
  fpPost(fpBase, {
    plan_file: fpPlan.file, equipment_type: document.getElementById('fpType').value,
    label: document.getElementById('fpLabel').value, expires_on: fpDated.includes(document.getElementById('fpType').value) ? document.getElementById('fpExpiry').value : '', status: document.getElementById('fpStatus').value,
    x: ((e.clientX - r.left) / r.width * 100).toFixed(3), y: ((e.clientY - r.top) / r.height * 100).toFixed(3),
  }).then(m => { fpMarkers.push(m); fpDraw(); fpToast('Marker added'); }).catch(err => fpToast(err.message, true));
});
fpSync(); // red alert icon + table reflect floor-plan markers as soon as the page loads
function fpShowMarker(m) {
  const opt = (list, cur) => list.map(v => '<option' + (v === cur ? ' selected' : '') + '>' + esc(v) + '</option>').join('');
  document.getElementById('fsDetailTitle').textContent = (m.label ? m.label + ' — ' : '') + m.type;
  document.getElementById('fsDetailBody').innerHTML = detailGrid([
    ['Building', fpPlan.building], ['Floor', fpPlan.floor], ['Added by', m.by || '—'], ['Current state', fpState(m)],
  ]) + `<div class="fp-edit">
      <div class="fp-field"><label>Equipment type</label><select id="fpMType" onchange="document.getElementById('fpMExpWrap').style.display = fpDated.includes(this.value) ? '' : 'none'">${opt(fpTypes, m.type)}</select></div>
      <div class="fp-field" id="fpMExpWrap" style="display:${fpDated.includes(m.type) ? '' : 'none'};"><label>Expiry date</label><input type="date" id="fpMExp" value="${esc(m.exp || '')}"></div>
      <div class="fp-field"><label>Status</label><select id="fpMStatus">${opt(['Working', 'Needs Repair', 'Missing'], m.status)}</select></div>
    </div>
    <div class="fp-actions">
      <button type="button" class="fp-btn" onclick="fpSaveStatus(${m.id})">Save changes</button>
      <button type="button" class="fp-btn secondary" onclick="fpRemove(${m.id})">Remove marker</button>
    </div>`;
  document.getElementById('fsDetailModal').style.display = 'flex';
}
function fpSaveStatus(id) {
  fpPost(`${fpBase}/${id}/status`, { status: document.getElementById('fpMStatus').value, equipment_type: document.getElementById('fpMType').value, expires_on: fpDated.includes(document.getElementById('fpMType').value) ? document.getElementById('fpMExp').value : '' }).then(m => {
    fpMarkers = fpMarkers.map(x => x.id === id ? m : x); fpDraw(); document.getElementById('fsDetailModal').style.display = 'none'; fpToast('Marker updated');
  }).catch(err => fpToast(err.message, true));
}
function fpConfirm(title, text, okLabel) {
  return new Promise(res => {
    const ov = document.createElement('div');
    ov.className = 'fp-dialog-overlay';
    ov.innerHTML = '<div class="fp-dialog"><div class="fp-dialog-icon"><i class="bi bi-trash3"></i></div><h4>' + esc(title) + '</h4><p>' + esc(text) + '</p><div class="fp-dialog-actions"><button type="button" class="fp-btn secondary" data-v="0">Cancel</button><button type="button" class="fp-btn" data-v="1">' + esc(okLabel) + '</button></div></div>';
    ov.addEventListener('click', ev => { const v = ev.target.dataset && ev.target.dataset.v; if (v !== undefined || ev.target === ov) { ov.remove(); res(v === '1'); } });
    document.body.appendChild(ov);
  });
}
function fpToast(msg, bad) { uiToast(msg, !!bad); }
function fpRemove(id) {
  fpConfirm('Remove this marker?', 'It will be taken off the floor plan.', 'Remove').then(ok => {
    if (!ok) return;
    fpPost(`${fpBase}/${id}/delete`, {}).then(() => {
      fpMarkers = fpMarkers.filter(x => x.id !== id); fpDraw(); document.getElementById('fsDetailModal').style.display = 'none'; fpToast('Marker removed');
    }).catch(err => fpToast(err.message, true));
  });
}

function switchFsTab(i) {
  document.querySelectorAll('#fsTabs .sub-tab').forEach(b => b.classList.toggle('active', Number(b.dataset.fs) === i));
  document.querySelectorAll('.fs-pane').forEach(p => p.style.display = p.id === 'fs-pane-' + i ? '' : 'none');
}
function applyFsFilters() {
  const q = document.getElementById('fsSearch').value.trim().toLowerCase();
  const st = document.getElementById('fsStatusFilter').value;
  document.querySelectorAll('.fs-row').forEach(r => {
    const okQ = !q || r.textContent.toLowerCase().includes(q) || (r.dataset.building || '').toLowerCase().includes(q);
    const okS = !st || r.dataset.status === st || r.dataset.due === st || r.dataset.shown === st;
    r.style.display = okQ && okS ? '' : 'none';
  });
}
function sortFsRows() {
  const mode = document.getElementById('fsSort').value;
  document.querySelectorAll('.fs-body').forEach(body => {
    const rows = Array.from(body.querySelectorAll('.fs-row'));
    rows.sort((a, b) => {
      if (mode === 'building') return a.dataset.building.localeCompare(b.dataset.building);
      if (mode === 'next') return (a.dataset.next || '9999').localeCompare(b.dataset.next || '9999');
      return a.dataset.code.localeCompare(b.dataset.code);
    });
    rows.forEach(r => body.appendChild(r));
  });
}
function showFsDetail(row) {
  const d = row.dataset;
  document.getElementById('fsDetailTitle').textContent = `${d.type} — ${d.code}`;
  document.getElementById('fsDetailBody').innerHTML = detailGrid([
    ['Building', d.building], ['Floor', d.floor || '—'], ['Details', d.detail || '—'],
    ['Status', d.shown], ['Installed', fpFmtDate(d.installed)], ['Next check', d.next || '—'], ['Expires', fpFmtDate(d.expires)],
  ]) + `<div style="margin-top:14px;"><div class="text-muted">Remarks</div>${esc(d.remarks || '—')}</div>`;
  document.getElementById('fsDetailModal').style.display = 'flex';
}
function openFsAdd() { fsAddToggle(); document.getElementById('fsAddModal').style.display = 'flex'; }
function closeFsAdd() { document.getElementById('fsAddModal').style.display = 'none'; }
function fsAddToggle() {
  const ext = document.getElementById('fsAddType').value === 'Fire Extinguisher';
  document.querySelectorAll('#fsAddModal .ext-only').forEach(e => e.style.display = ext ? '' : 'none');
  document.querySelectorAll('#fsAddModal .other-only').forEach(e => e.style.display = ext ? 'none' : '');
  const dated = ext || document.getElementById('fsAddType').value === 'Smoke Detector';
  document.querySelectorAll('#fsAddModal .dated-only').forEach(e => e.style.display = dated ? '' : 'none');
}
<?php endif; ?>

<?php if ($section === 'guard'): ?>
const alertData = { g: { items: <?= $alerts_json ?>, titles: { red: 'Keys out over 8 hours', yellow: 'Trips awaiting dispatch' }, cols: ['Name', 'Details', 'Reference', 'Alert'] } };
initAlertIcons();
const keyLogsData = <?= $key_logs_json ?>;

(function fillMonitor() {
  const items = alertData.g.items;
  document.getElementById('monitorBody').innerHTML = items.length
    ? items.map(i => `<tr>${i.cols.map(alertCell).join('')}</tr>`).join('')
    : '<tr><td colspan="4" class="empty-row">Nothing needs the guard\'s attention right now.</td></tr>';
})();

function switchGTab(key) {
  document.querySelectorAll('#gTabs .sub-tab').forEach(b => b.classList.toggle('active', b.dataset.g === key));
  document.querySelectorAll('.g-pane').forEach(p => p.style.display = p.id === 'g-' + key ? '' : 'none');
}
function applyGFilters() {
  const q = document.getElementById('gSearch').value.trim().toLowerCase();
  const st = document.getElementById('gStatusFilter').value;
  document.querySelectorAll('.g-row').forEach(r => {
    const okQ = !q || r.textContent.toLowerCase().includes(q);
    const okS = !st || !r.dataset.status || r.dataset.status === st;
    r.style.display = okQ && okS ? '' : 'none';
  });
}
function sortGRows() {
  const mode = document.getElementById('gSort').value;
  document.querySelectorAll('.g-body').forEach(body => {
    const rows = Array.from(body.querySelectorAll('.g-row'));
    rows.sort((a, b) => {
      const d = (a.dataset.time || '').localeCompare(b.dataset.time || '');
      return mode === 'oldest' ? d : -d;
    });
    rows.forEach(r => body.appendChild(r));
  });
}
function showKeyDetail(id) {
  const l = keyLogsData.find(x => x.id === id);
  if (!l) return;
  document.getElementById('keyDetailTitle').textContent = `${l.log} — ${l.key}`;
  document.getElementById('keyDetailBody').innerHTML = detailGrid([
    ['Borrower', l.borrower], ['Borrower ID', l.borrower_id || '—'], ['Department', l.dept || '—'],
    ['Date Borrowed', l.borrowed], ['Returned', l.returned], ['Status', l.status], ['Guard on duty', l.guard],
  ]);
  document.getElementById('keyDetailModal').style.display = 'flex';
}
<?php endif; ?>

<?php if ($section === 'inspection'): ?>
const alertData = { in: { items: <?= $alerts_json ?>, titles: { red: 'Buildings needing attention', yellow: 'Buildings not inspected yet' }, cols: ['Building', 'Status', 'Remarks'] } };
initAlertIcons();
const currentInsp = <?= $current_json ?>;
renderMapImage('inMapSVG', { imageUrl: '<?= base_url('images/MAP.jpg') ?>', stateByName: <?= $map_state_json ?>, legend: 'building',
  onSelect: name => showInspDetail(name) });

function switchInTab(key) {
  document.querySelectorAll('#inTabs .sub-tab').forEach(b => b.classList.toggle('active', b.dataset.in === key));
  document.querySelectorAll('.in-pane').forEach(p => p.style.display = p.id === 'in-' + key ? '' : 'none');
}
function applyInFilters() {
  const q = document.getElementById('inSearch').value.trim().toLowerCase();
  const st = document.getElementById('inStatusFilter').value;
  document.querySelectorAll('#inBody .in-row').forEach(r => {
    r.style.display = (!q || r.textContent.toLowerCase().includes(q)) && (!st || r.dataset.status === st) ? '' : 'none';
  });
}
function sortInRows() {
  const mode = document.getElementById('inSort').value;
  const body = document.getElementById('inBody');
  const rows = Array.from(body.querySelectorAll('.in-row'));
  rows.sort((a, b) => {
    if (mode === 'latest') return (b.dataset.last || '').localeCompare(a.dataset.last || '');
    if (mode === 'building') return a.dataset.name.localeCompare(b.dataset.name);
    const d = Number(a.dataset.idx) - Number(b.dataset.idx);
    return mode === 'newest' ? -d : d;
  });
  rows.forEach(r => body.appendChild(r));
}
function showInspDetail(name) {
  const r = currentInsp.find(x => x.building === name) || {};
  document.getElementById('inspDetailTitle').textContent = name;
  document.getElementById('inspDetailBody').innerHTML = detailGrid([
    ['Safety status', r.safety_status || 'Not inspected'], ['Inspected by', r.inspected_by || '—'],
    ['Date', r.inspected_at ? r.inspected_at.slice(0, 10) : '—'],
  ]) + `<div style="margin-top:14px;"><div class="text-muted">Remarks</div>${esc(r.remarks || '—')}</div>
    <div style="margin-top:16px;"><button type="button" class="btn-maroon-sm" data-building="${esc(name)}" onclick="openInspForm(this.dataset.building)">Record an inspection for this building</button></div>`;
  document.getElementById('inspDetailModal').style.display = 'flex';
}
function openInspForm(name) {
  document.getElementById('inspDetailModal').style.display = 'none';
  document.getElementById('inspBuilding').value = name || '';
  document.getElementById('inspModal').style.display = 'flex';
}
function closeInspForm() { document.getElementById('inspModal').style.display = 'none'; }
<?php endif; ?>
</script>
<script src="<?= base_url('Assets/js/table-tools.js') ?>?v=<?= @filemtime(FCPATH . 'Assets/js/table-tools.js') ?>"></script>
<script>const _si = document.getElementById('statInline'); if (_si) attachTableTools(_si);</script>
<?= $this->endSection() ?>
