<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php
$title = $title ?? 'Safety Maintenance';
$coverage_total = $coverage_total ?? 0;
$coverage_attention = $coverage_attention ?? 0;
$coverage_refill = $coverage_refill ?? 0;
$inspection_readiness = $inspection_readiness ?? 0;
$fe_registry_json = $fe_registry_json ?? '[]';
$aircon_registry_json = $aircon_registry_json ?? '[]';
$aircon_total = $aircon_total ?? 0;
$aircon_attention = $aircon_attention ?? 0;
$work_order_registry_json = $work_order_registry_json ?? '[]';
$work_order_total = $work_order_total ?? 0;
?>

<link rel="stylesheet" href="<?= base_url('Assets/css/safety.css') . '?v=' . @filemtime(FCPATH.'Assets/css/safety.css') ?>">

<div class="sj-wrapper">
  <div class="page-header">
    <div>
      <h1><?= esc($title) ?></h1>
      <p class="page-subtitle">Campus fire extinguisher coverage and aircon condition monitoring at a glance.</p>
    </div>
  </div>

  <div class="overview-grid" id="overviewGrid">
    <div class="overview-card overview-card-clickable" onclick="showStatusList('coverage')" role="button" tabindex="0">
      <div class="card-title"><i class="bi bi-fire"></i> Fire Safety Coverage</div>
      <div class="card-value"><?= (int) $coverage_total ?> units</div>
      <div class="card-sub"><?= (int) $coverage_attention ?> require attention • <?= (int) $coverage_refill ?> due for refill</div>
    </div>
    <div class="overview-card overview-card-clickable" onclick="showStatusList('readiness')" role="button" tabindex="0">
      <div class="card-title"><i class="bi bi-clipboard2-check"></i> Inspection Readiness</div>
      <div class="card-value"><?= (int) $inspection_readiness ?>%</div>
      <div class="card-sub">Share of units not past their next inspection due date</div>
    </div>
    <div class="overview-card overview-card-clickable" onclick="showStatusList('critical')" role="button" tabindex="0">
      <div class="card-title"><i class="bi bi-exclamation-octagon"></i> Critical Alerts</div>
      <div class="card-value" id="criticalAlertsValue">0 active</div>
      <div class="card-sub" id="criticalAlertsSub">No critical areas detected</div>
    </div>
    <div class="overview-card overview-card-clickable" onclick="showStatusList('aircon')" role="button" tabindex="0">
      <div class="card-title"><i class="bi bi-snow2"></i> Aircon</div>
      <div class="card-value"><?= (int) $aircon_total ?> units</div>
      <div class="card-sub"><?= (int) $aircon_attention ?> need attention</div>
    </div>
    <div class="overview-card overview-card-clickable" onclick="scrollToMaintenance()" role="button" tabindex="0" style="display:none">
      <div class="card-title"><i class="bi bi-clipboard2-pulse"></i> Work Orders</div>
      <div class="card-value" id="workOrderCount"><?= (int) $work_order_total ?> open</div>
      <div class="card-sub">Reported issues awaiting resolution</div>
    </div>
  </div>

  <!-- ── STATUS BOX LIST: shown when an overview card above is clicked ── -->
  <div class="maintenance-section" id="statusListSection" style="display:none">
    <div class="dp-header">
      <div class="dp-section-title" id="statusListTitle"><i class="bi bi-list-ul"></i> List</div>
      <button class="dp-close" onclick="closeStatusList()"><i class="bi bi-x-lg"></i></button>
    </div>
    <div id="statusListGrid" class="dp-fe-grid"></div>
  </div>

  <!-- ── MAINTENANCE DUE: open work orders + overdue fire extinguishers ── -->
  <div class="maintenance-section" id="maintenanceSection">
    <div style="display:none">
      <div class="dp-section-title"><i class="bi bi-clipboard2-pulse"></i> Open Work Orders</div>
      <div id="workOrderGrid" class="dp-fe-grid"></div>
    </div>

    <div class="dp-section-title"><i class="bi bi-exclamation-triangle"></i> Overdue Fire Extinguishers</div>
    <div id="overdueFeGrid" class="dp-fe-grid"></div>
  </div>

  <!-- ── SUB-TABS: FIRE EXTINGUISHER / AIRCON CONDITION ───────────────── -->
  <div class="sub-tabs">
    <button class="sub-tab active" data-tab="fe" onclick="switchSafetyTab('fe')">
      <i class="bi bi-fire"></i> Fire Extinguisher
    </button>
    <button class="sub-tab" data-tab="aircon" onclick="switchSafetyTab('aircon')">
      <i class="bi bi-snow2"></i> Aircon Condition
    </button>
  </div>

  <div id="subtab-map" class="sub-pane active">
    <div class="map-layout" id="mapLayout">
      <div class="map-container" id="mapContainer">
        <div class="map-legend" id="legendFe">
          <span class="leg-title" title="Fire Extinguisher Legend">🧯</span>
          <span class="leg-item leg-clickable" data-status="normal" onclick="filterMapByStatus('normal')" role="button" tabindex="0">⚪ Normal</span>
          <span class="leg-item leg-clickable" data-status="warning" onclick="filterMapByStatus('warning')" role="button" tabindex="0">⚠️ Warning</span>
          <span class="leg-item leg-clickable" data-status="new" onclick="filterMapByStatus('new')" role="button" tabindex="0">🆕 New Installed</span>
          <span class="leg-item leg-clickable" data-status="expires" onclick="filterMapByStatus('expires')" role="button" tabindex="0">⏳ Expires Soon</span>
        </div>
        <div class="map-legend" id="legendAircon" style="display:none">
          <span class="leg-item leg-clickable" data-status="normal" onclick="filterMapByStatus('normal')" role="button" tabindex="0">⚪ Not Tracked</span>
          <span class="leg-item leg-clickable" data-status="warning" onclick="filterMapByStatus('warning')" role="button" tabindex="0">❌ Not Working</span>
          <span class="leg-item leg-clickable" data-status="new" onclick="filterMapByStatus('new')" role="button" tabindex="0">✅ Operational</span>
          <span class="leg-item leg-clickable" data-status="expires" onclick="filterMapByStatus('expires')" role="button" tabindex="0">🧊 Needs Cleaning</span>
        </div>

        <svg id="campusSVG" viewBox="0 0 950 900" xmlns="http://www.w3.org/2000/svg">
          <rect id="z-main" class="campus-area" x="280" y="175" width="205" height="430" rx="3" fill="transparent" stroke="#2a2a2a" stroke-width="1.4" data-name="Main evacuation open space" onclick="selectMapBuilding(this)" />
          <line x1="382" y1="175" x2="382" y2="605" stroke="#2a2a2a" stroke-width="1" opacity=".6" />
          <line x1="280" y1="390" x2="485" y2="390" stroke="#2a2a2a" stroke-width="1" opacity=".6" />
          <text x="382" y="385" class="num" style="font-size:12px;">MAIN EVACUATION</text>
          <text x="382" y="398" class="num" style="font-size:12px;">OPEN SPACE</text>
          <g id="flows"></g>
          <g id="buildings"></g>
          <g id="extinguishers"></g>
          <g id="airconIcons" style="display:none"></g>
        </svg>
      </div>

      <div class="missing-alert" id="missingAlert" style="display:none">
        <i class="bi bi-exclamation-octagon-fill"></i>
        <span id="missingAlertMsg"></span>
        <button onclick="document.getElementById('missingAlert').style.display='none'"><i class="bi bi-x"></i></button>
      </div>

      <div class="drill-panel" id="drillPanel" style="display:none">
        <div class="dp-header">
          <div>
            <div class="dp-title" id="dpTitle">Admin Building</div>
            <div class="dp-sub" id="dpSub">Fire Extinguisher Status</div>
          </div>
          <button class="dp-close" onclick="closeDrill()"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="dp-status-box" id="dpStatusBox">Status summary will appear here.</div>

        <div class="dp-section-title"><i class="bi bi-building"></i> Floor</div>
        <div class="floor-tabs" id="floorTabs"></div>
        <div class="floor-diagram" id="floorDiagram"></div>
        <div id="floorUnitDetail"></div>

        <div class="dp-section-title"><i class="bi bi-clock-history"></i> Expiry Alerts</div>
        <div id="dpAlerts" class="dp-alerts"></div>
      </div>
    </div>
  </div>

</div>

<!-- Report modal removed; reporting now lives in Records, Archiving & Reports -->
<!-- Keyscan modal removed; Keylogs moved to Safety -> Keylogs page -->

<script>
  // feRegistry and airconRegistry come straight from the database
  // (fire_extinguishers / aircon_units) — see SafetyController::index().
  // Aircon units are registered from the mobile app; this just displays them.
  let feRegistry = <?= $fe_registry_json ?>;
  let airconRegistry = <?= $aircon_registry_json ?>;
  let workOrderRegistry = <?= $work_order_registry_json ?>;

  // Keylogs and Guard data moved to separate Safety pages (sidebar)

  // ── Fire Extinguisher / Aircon Condition tabs ──────────────────────
  // ONE campus map is shared between both categories — switching tabs just
  // recolors the same buildings and changes what the drill panel shows,
  // instead of duplicating the whole map twice.
  let activeSafetyTab = 'fe';
  let currentBuildingName = null;
  let currentFloor = 'Ground Floor';
  const FLOORS = ['Ground Floor', '2nd Floor', '3rd Floor'];

  function switchSafetyTab(tab) {
    activeSafetyTab = tab;
    document.querySelectorAll('.sub-tab').forEach(b => b.classList.toggle('active', b.getAttribute('data-tab') === tab));
    document.getElementById('legendFe').style.display = tab === 'fe' ? 'flex' : 'none';
    document.getElementById('legendAircon').style.display = tab === 'aircon' ? 'flex' : 'none';
    // The 🧯 / ❄️ markers only make sense for the tab they belong to.
    document.getElementById('extinguishers').style.display = tab === 'fe' ? '' : 'none';
    document.getElementById('airconIcons').style.display = tab === 'aircon' ? '' : 'none';
    mapStatusFilter = null;
    closeDrill();
    recolorMap();
  }

  // Legend items act as a toggle filter on the map — click a status to dim
  // every building that isn't that status; click the same one again to clear.
  let mapStatusFilter = null;

  function filterMapByStatus(status) {
    mapStatusFilter = (mapStatusFilter === status) ? null : status;

    document.querySelectorAll('.map-legend .leg-item').forEach(el => {
      el.classList.toggle('leg-active', el.getAttribute('data-status') === mapStatusFilter);
    });

    document.querySelectorAll('#campusSVG .campus-area').forEach(shape => {
      const name = shape.getAttribute('data-name');
      const status2 = computeAreaStatus(name, activeSafetyTab);
      shape.classList.toggle('campus-area-dimmed', !!mapStatusFilter && status2 !== mapStatusFilter);
    });
  }

  // Single source of truth for a zone's status, per category. The map
  // coloring uses whichever tab is active; the Critical Alerts stat card
  // always reads the Fire Extinguisher status specifically, regardless of
  // which tab is open, so that headline number never silently changes.
  function computeAreaStatus(buildingName, tab) {
    if (!buildingName) return 'normal';

    if (tab === 'fe') {
      const units = feRegistry.filter(u => u.loc === buildingName);
      if (units.length === 0) return 'normal';
      const today = new Date();
      const daysLeft = u => Math.ceil((new Date(u.nextDue) - today) / 86400000);
      if (units.some(u => u.status === 'Missing' || u.status === 'Defective' || daysLeft(u) < 0)) return 'warning';
      if (units.some(u => u.status === 'Refillable' || daysLeft(u) < 30)) return 'expires';
      if (units.every(u => u.status === 'New')) return 'new';
      return 'normal';
    }

    const units = airconRegistry.filter(u => u.loc === buildingName);
    if (units.length === 0) return 'normal';
    if (units.some(u => u.condition === 'Not Working')) return 'warning';
    if (units.some(u => u.condition === 'Needs Cleaning')) return 'expires';
    if (units.every(u => u.condition === 'Operational')) return 'new';
    return 'normal';
  }

  function selectMapBuilding(el) {
    document.querySelectorAll('#campusSVG .campus-area').forEach(g => g.classList.remove('area-selected'));
    el.classList.add('area-selected');

    const name = el.getAttribute('data-name') || el.id || 'Campus area';
    currentBuildingName = name;

    const units = getUnitsForCurrentBuilding();
    currentFloor = FLOORS.find(f => units.some(u => (u.floor || 'Ground Floor') === f)) || 'Ground Floor';

    document.getElementById('mapLayout').classList.add('drilled');
    document.getElementById('dpTitle').textContent = name;
    document.getElementById('dpSub').textContent = activeSafetyTab === 'fe' ? 'Fire Extinguisher Status' : 'Aircon Condition';

    const status = computeAreaStatus(name, activeSafetyTab);
    renderStatusBox(status);
    renderFloorTabs();
    renderFloorDiagram();
    renderExpiryAlerts();

    const missing = activeSafetyTab === 'fe' && units.some(u => u.status === 'Missing');
    const alertBanner = document.getElementById('missingAlert');
    if (missing) {
      document.getElementById('missingAlertMsg').textContent = `⚠ Missing fire extinguisher detected in ${name}. Admin notified.`;
      alertBanner.style.display = 'flex';
    } else {
      alertBanner.style.display = 'none';
    }

    document.getElementById('drillPanel').style.display = 'block';
  }

  function renderStatusBox(status) {
    const copy = activeSafetyTab === 'fe'
      ? {
          warning: ['Warning', 'Immediate attention needed for this area.'],
          expires: ['Expires Soon', 'This area has equipment nearing expiry.'],
          new: ['Normal', 'This area has recently installed equipment.'],
          normal: ['Normal', 'No special alert status for this area.'],
        }
      : {
          warning: ['Not Working', 'One or more aircon units in this building need repair.'],
          expires: ['Needs Cleaning', 'Aircon units here are due for cleaning.'],
          new: ['Operational', 'All aircon units in this building are working normally.'],
          normal: ['Not Tracked', 'No aircon unit recorded for this building yet.'],
        };
    const [label, sub] = copy[status];
    document.getElementById('dpStatusBox').innerHTML = `<strong>${label}</strong><div>${sub}</div>`;

    const drillPanelEl = document.getElementById('drillPanel');
    drillPanelEl.classList.remove('st-warning', 'st-new', 'st-expires', 'st-normal');
    drillPanelEl.classList.add(`st-${status}`);
  }

  function getUnitsForCurrentBuilding() {
    return activeSafetyTab === 'fe'
      ? feRegistry.filter(u => u.loc === currentBuildingName)
      : airconRegistry.filter(u => u.loc === currentBuildingName);
  }

  function renderFloorTabs() {
    const units = getUnitsForCurrentBuilding();
    document.getElementById('floorTabs').innerHTML = FLOORS.map(f => {
      const count = units.filter(u => (u.floor || 'Ground Floor') === f).length;
      return `<button type="button" class="floor-tab ${f === currentFloor ? 'active' : ''}" onclick="selectFloor('${f}')">${f} <span class="floor-count">${count}</span></button>`;
    }).join('');
  }

  function selectFloor(floor) {
    currentFloor = floor;
    renderFloorTabs();
    renderFloorDiagram();
    document.getElementById('floorUnitDetail').innerHTML = '';
  }

  // Schematic floor diagram — not a real blueprint (none exists in the data),
  // just a simple floor outline with each unit as a hoverable, clickable icon.
  function renderFloorDiagram() {
    const units = getUnitsForCurrentBuilding().filter(u => (u.floor || 'Ground Floor') === currentFloor);
    const container = document.getElementById('floorDiagram');

    if (!units.length) {
      container.innerHTML = `<div class="floor-plan-empty">No ${activeSafetyTab === 'fe' ? 'fire extinguisher' : 'aircon unit'} recorded on this floor.</div>`;
      return;
    }

    const icon = activeSafetyTab === 'fe' ? 'bi-fire' : 'bi-snow2';
    container.innerHTML = `
      <div class="floor-plan">
        <div class="floor-plan-label">${esc(currentFloor)} Plan</div>
        <div class="floor-plan-icons">
          ${units.map((u, i) => {
            const idKey = activeSafetyTab === 'fe' ? u.id : u.unit;
            const sev = activeSafetyTab === 'fe'
              ? (u.status === 'Missing' || u.status === 'Defective' ? 'fp-critical' : u.status === 'Refillable' ? 'fp-warn' : 'fp-ok')
              : (u.condition === 'Not Working' ? 'fp-critical' : u.condition === 'Needs Cleaning' ? 'fp-warn' : 'fp-ok');
            return `<button type="button" class="floor-plan-icon ${sev}" title="${esc(idKey)}" onclick="selectFloorUnit(${i})">
              <i class="bi ${icon}"></i><span>${esc(idKey)}</span>
            </button>`;
          }).join('')}
        </div>
      </div>`;
  }

  function selectFloorUnit(i) {
    const units = getUnitsForCurrentBuilding().filter(u => (u.floor || 'Ground Floor') === currentFloor);
    const u = units[i];
    if (!u) return;

    document.querySelectorAll('.floor-plan-icon').forEach((el, idx) => el.classList.toggle('selected', idx === i));
    const target = document.getElementById('floorUnitDetail');

    if (activeSafetyTab === 'fe') {
      const today = new Date();
      const daysLeft = Math.ceil((new Date(u.nextDue) - today) / 86400000);
      target.innerHTML = `
        <div class="fe-card ${daysLeft < 0 ? 'fe-urgent' : daysLeft < 30 ? 'fe-warn' : 'fe-ok'}">
          <div class="fec-id">${esc(u.id)}</div>
          <div class="fec-status status-${u.status.toLowerCase().replace(' ', '')}">${esc(u.status)}</div>
          <div class="fec-row"><span>Type</span><strong>${esc(u.type)}</strong></div>
          <div class="fec-row"><span>Floor</span><strong>${esc(u.floor || 'Ground Floor')}</strong></div>
          <div class="fec-row"><span>Weight</span><strong>${u.kg} kg</strong></div>
          <div class="fec-row"><span>Installed</span><strong>${esc(u.year)}</strong></div>
          <div class="fec-row"><span>Installed by</span><strong>${esc(u.inspector)}</strong></div>
          <div class="fec-row"><span>Assigned Guard</span><strong>${esc(u.assigned)}</strong></div>
          <div class="fec-row"><span>Last Insp.</span><strong>${esc(u.lastInsp)}</strong></div>
          <div class="fec-row"><span>Next Due</span><strong class="${daysLeft < 0 ? 'text-danger' : daysLeft < 30 ? 'text-warn' : ''}">${esc(u.nextDue)} (${daysLeft < 0 ? 'OVERDUE' : daysLeft + 'd'})</strong></div>
        </div>`;
    } else {
      const done = u.checklist.filter(t => t.done).length;
      const total = u.checklist.length;
      target.innerHTML = `
        <div class="fe-card ${u.condition === 'Operational' ? 'fe-ok' : u.condition === 'Needs Cleaning' ? 'fe-warn' : 'fe-urgent'}">
          <div class="fec-id">${esc(u.unit)}</div>
          <div class="fec-status">${esc(u.condition)}</div>
          <div class="fec-row"><span>Floor</span><strong>${esc(u.floor || 'Ground Floor')}</strong></div>
          <div class="fec-row"><span>Last Cleaning</span><strong>${esc(u.lastClean) || '—'}</strong></div>
          <div class="fec-row"><span>Next Schedule</span><strong>${esc(u.nextDue) || '—'}</strong></div>
          <div class="fec-row"><span>Assigned Tech</span><strong>${esc(u.tech)}</strong></div>
          <div class="fec-row"><span>Checklist</span><strong>${done}/${total} done</strong></div>
        </div>`;
    }
  }

  function renderExpiryAlerts() {
    const units = getUnitsForCurrentBuilding();
    const target = document.getElementById('dpAlerts');
    let html = '';

    if (activeSafetyTab === 'fe') {
      const today = new Date();
      units.forEach(u => {
        const daysLeft = Math.ceil((new Date(u.nextDue) - today) / 86400000);
        if (daysLeft < 30) {
          html += `<div class="dp-alert-item ${daysLeft < 0 ? 'urgent' : 'warn'}"><i class="bi bi-exclamation-triangle-fill"></i> <strong>${esc(u.id)}</strong> (${esc(u.floor || 'Ground Floor')}) — Next inspection due in ${daysLeft < 0 ? Math.abs(daysLeft) + ' days overdue' : daysLeft + ' days'}.</div>`;
        }
      });
    } else {
      units.forEach(u => {
        if (u.condition !== 'Operational') {
          html += `<div class="dp-alert-item ${u.condition === 'Not Working' ? 'urgent' : 'warn'}"><i class="bi bi-exclamation-triangle-fill"></i> <strong>${esc(u.unit)}</strong> (${esc(u.floor || 'Ground Floor')}) — ${esc(u.condition)}.</div>`;
        }
      });
    }

    target.innerHTML = html || '<div class="no-alert">No active alerts for this area.</div>';
  }

  function esc(s) {
    const d = document.createElement('div');
    d.textContent = String(s ?? '');
    return d.innerHTML;
  }

  const priorityUrgency = { Critical: 'fe-urgent', High: 'fe-warn', Medium: 'fe-ok' };

  function renderWorkOrders() {
    const grid = document.getElementById('workOrderGrid');
    if (!workOrderRegistry.length) {
      grid.innerHTML = `<div class="no-data" style="padding:1rem;">No open work orders.</div>`;
      return;
    }
    grid.innerHTML = workOrderRegistry.map(w => `
      <div class="fe-card ${priorityUrgency[w.priority] || 'fe-ok'}">
        <button type="button" class="fe-card-close" title="Close work order" onclick="closeWorkOrder(${w.dbId})"><i class="bi bi-x-lg"></i></button>
        <div class="fec-id">${esc(w.id)}</div>
        <div class="fec-status">${esc(w.priority)} priority</div>
        <div class="fec-row"><span>Issue</span><strong>${esc(w.issue)}</strong></div>
        <div class="fec-row"><span>Location</span><strong>${esc(w.loc)}</strong></div>
        <div class="fec-row"><span>Stage</span><strong>${esc(w.stage)}</strong></div>
        <div class="fec-row"><span>Reported by</span><strong>${esc(w.reported)}</strong></div>
        <div class="fec-row"><span>Assigned to</span><strong>${esc(w.assigned)}</strong></div>
        <div class="fec-row"><span>Logged</span><strong>${esc(w.logged)}</strong></div>
      </div>`).join('');
  }

  // Hides the work order from view (and updates the Work Orders stat box) —
  // this is display-only for now, not saved, so it reappears on refresh.
  function closeWorkOrder(dbId) {
    workOrderRegistry = workOrderRegistry.filter(w => w.dbId !== dbId);
    renderWorkOrders();
    document.getElementById('workOrderCount').textContent = `${workOrderRegistry.length} open`;
  }

  // Computed client-side from the same feRegistry already loaded for the
  // campus map — no separate query needed, this is just the subset that's
  // past its next_due date, flattened across all buildings.
  function renderOverdueFe() {
    const grid = document.getElementById('overdueFeGrid');
    const today = new Date();
    const overdue = feRegistry.filter(u => u.nextDue && new Date(u.nextDue) < today);
    if (!overdue.length) {
      grid.innerHTML = `<div class="no-data" style="padding:1rem;">No overdue fire extinguishers.</div>`;
      return;
    }
    grid.innerHTML = overdue.map(u => {
      const daysLeft = Math.ceil((new Date(u.nextDue) - today) / 86400000);
      return `
      <div class="fe-card fe-urgent">
        <div class="fec-id">${esc(u.id)}</div>
        <div class="fec-status status-${u.status.toLowerCase().replace(' ','')}">${esc(u.status)}</div>
        <div class="fec-row"><span>Location</span><strong>${esc(u.loc)}</strong></div>
        <div class="fec-row"><span>Next Due</span><strong class="text-danger">${esc(u.nextDue)} (${Math.abs(daysLeft)}d overdue)</strong></div>
        <div class="fec-row"><span>Assigned Guard</span><strong>${esc(u.assigned)}</strong></div>
      </div>`;
    }).join('');
  }

  function scrollToMaintenance() {
    document.getElementById('maintenanceSection').scrollIntoView({ behavior: 'smooth', block: 'start' });
  }

  function feUnitCardHtml(u) {
    const today = new Date();
    const daysLeft = u.nextDue ? Math.ceil((new Date(u.nextDue) - today) / 86400000) : null;
    const sev = daysLeft === null ? 'fe-ok' : daysLeft < 0 ? 'fe-urgent' : daysLeft < 30 ? 'fe-warn' : 'fe-ok';
    return `
      <div class="fe-card ${sev}">
        <div class="fec-id">${esc(u.id)}</div>
        <div class="fec-status status-${u.status.toLowerCase().replace(' ', '')}">${esc(u.status)}</div>
        <div class="fec-row"><span>Location</span><strong>${esc(u.loc)} (${esc(u.floor || 'Ground Floor')})</strong></div>
        <div class="fec-row"><span>Type</span><strong>${esc(u.type)}</strong></div>
        <div class="fec-row"><span>Installed by</span><strong>${esc(u.inspector)}</strong></div>
        <div class="fec-row"><span>Next Due</span><strong class="${daysLeft !== null && daysLeft < 0 ? 'text-danger' : daysLeft !== null && daysLeft < 30 ? 'text-warn' : ''}">${esc(u.nextDue) || '—'}${daysLeft !== null ? ` (${daysLeft < 0 ? 'OVERDUE' : daysLeft + 'd'})` : ''}</strong></div>
      </div>`;
  }

  function airconUnitCardHtml(u) {
    const done = u.checklist.filter(t => t.done).length;
    const total = u.checklist.length;
    const sev = u.condition === 'Operational' ? 'fe-ok' : u.condition === 'Needs Cleaning' ? 'fe-warn' : 'fe-urgent';
    return `
      <div class="fe-card ${sev}">
        <div class="fec-id">${esc(u.unit)}</div>
        <div class="fec-status">${esc(u.condition)}</div>
        <div class="fec-row"><span>Location</span><strong>${esc(u.loc)} (${esc(u.floor || 'Ground Floor')})</strong></div>
        <div class="fec-row"><span>Assigned Tech</span><strong>${esc(u.tech)}</strong></div>
        <div class="fec-row"><span>Checklist</span><strong>${done}/${total} done</strong></div>
      </div>`;
  }

  // Overview cards open a flat list of matching units below them — reuses
  // the same fe-card look as the floor drill-down and the maintenance grids.
  function showStatusList(kind) {
    const today = new Date();
    let title, units, cardFn;

    if (kind === 'coverage') {
      title = 'All Fire Extinguishers';
      units = feRegistry;
      cardFn = feUnitCardHtml;
    } else if (kind === 'readiness') {
      title = 'Overdue Fire Extinguishers';
      units = feRegistry.filter(u => u.nextDue && new Date(u.nextDue) < today);
      cardFn = feUnitCardHtml;
    } else if (kind === 'critical') {
      title = 'Critical Fire Extinguishers';
      units = feRegistry.filter(u => u.status === 'Missing' || u.status === 'Defective' || (u.nextDue && new Date(u.nextDue) < today));
      cardFn = feUnitCardHtml;
    } else {
      title = 'All Aircon Units';
      units = airconRegistry;
      cardFn = airconUnitCardHtml;
    }

    document.getElementById('statusListTitle').innerHTML = `<i class="bi bi-list-ul"></i> ${title}`;
    document.getElementById('statusListGrid').innerHTML = units.length
      ? units.map(cardFn).join('')
      : '<div class="no-data" style="padding:1rem;">No matching units.</div>';

    const section = document.getElementById('statusListSection');
    section.style.display = 'block';
    section.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }

  function closeStatusList() {
    document.getElementById('statusListSection').style.display = 'none';
  }

  renderWorkOrders();
  renderOverdueFe();

  // Arriving from the Dashboard's "Maintenance Due" box — jump straight to
  // this section instead of making the user hunt for it.
  if (new URLSearchParams(window.location.search).get('filter') === 'duework') {
    scrollToMaintenance();
  }

  // Redrawn from the corrected hand-traced campus map (fu_evacuation_map.html)
  // so buildings no longer overlap and match the real layout.
  const rectBuildings = [
    { n: 1, name: 'Main Entrance Gate', x: 836, y: 784, w: 46, h: 24 },
    { n: 2, name: 'University Cafeteria, Bookstore, Sewing', x: 320, y: 782, w: 148, h: 66 },
    { n: 3, name: 'College of Law Building', x: 320, y: 612, w: 155, h: 112 },
    { n: 4, name: 'College of Agriculture and SIE', x: 224, y: 578, w: 54, h: 255 },
    { n: 5, name: 'Museo de Vicente', x: 78, y: 580, w: 135, h: 62 },
    { n: 8, name: 'University Library', x: 95, y: 466, w: 126, h: 50 },
    { n: 9, name: 'Electric Pump House', x: 212, y: 432, w: 38, h: 32 },
    { n: 10, name: 'Executive House', x: 130, y: 346, w: 100, h: 48 },
    { n: 12, name: 'Guest House', x: 165, y: 110, w: 55, h: 90 },
    { n: 13, name: 'HRM Kitchen', x: 222, y: 60, w: 55, h: 48 },
    { n: 14, name: 'College of Education Building', x: 280, y: 114, w: 178, h: 46 },
    // Not in the original reference sketch — placed in the one open gap left
    // between College of Education Building and Animation Lab / ROTC Office,
    // above the main evacuation zone. Move it if this isn't where it actually is.
    { n: 15, name: 'Parade Ground', x: 458, y: 60, w: 57, h: 115 },
    { n: 17, name: 'LG Sinco Computer Center Building', x: 556, y: 222, w: 145, h: 100 },
    { n: 18, name: 'Sofia Soller Sinco Hall', x: 505, y: 328, w: 198, h: 118 },
    { n: 19, name: 'College of Art & Sciences Building', x: 527, y: 472, w: 48, h: 142 },
    { n: 20, name: 'Art & Science Laboratories / Audio Visual Rooms', x: 577, y: 472, w: 248, h: 143 },
    { n: 21, name: 'College of Business Economics and Accountancy', x: 712, y: 300, w: 192, h: 55 },
    { n: 22, name: 'College of Nursing', x: 830, y: 362, w: 52, h: 254 },
    { n: 23, name: 'Administration Building', x: 782, y: 625, w: 100, h: 48 },
    { n: 24, name: 'Rizal Monument / Social Garden', x: 588, y: 648, w: 132, h: 92 },
    { n: 25, name: 'Registrar\'s Office', x: 702, y: 760, w: 130, h: 22 },
    { n: 26, name: 'Business and Finance Office', x: 702, y: 784, w: 130, h: 24 },
    { n: 27, name: 'Old College of Industrial Engineering and Technology', x: 480, y: 795, w: 210, h: 34 },
  ];

  // Irregular / angled outlines that don't fit a plain rectangle.
  const polyBuildings = [
    { n: 6, name: 'Bunk House', points: '42,650 110,650 85,715 42,710' },
    { n: 7, name: 'Service / Exit Gate', points: '78,560 145,521 208,521 208,548 90,572' },
    { n: 16, name: 'Animation Lab / ROTC Office', points: '515,150 700,150 700,218 590,218 515,180' },
  ];

  const circleBuildings = [
    { n: 11, name: 'Water Pump', cx: 173, cy: 289, r: 9 },
    { n: 28, name: 'Overhead Water Supply Tank', cx: 850, cy: 745, r: 7 },
    { n: 29, name: 'Flag Pole', cx: 542, cy: 720, r: 6 },
  ];

  function polyBounds(points) {
    const pts = points.split(' ').map(p => p.split(',').map(Number));
    const xs = pts.map(p => p[0]), ys = pts.map(p => p[1]);
    const minX = Math.min(...xs), minY = Math.min(...ys);
    return { x: minX, y: minY, w: Math.max(...xs) - minX, h: Math.max(...ys) - minY };
  }

  // Every building gets a uniform {name, x, y, w, h} bounding box regardless
  // of its actual shape, so status coloring, labels, badges, and the fire
  // extinguisher icons all work the same way for rects, polygons, and circles.
  const mapBuildings = [
    ...rectBuildings.map(b => ({ ...b, shape: 'rect' })),
    ...polyBuildings.map(b => ({ ...b, shape: 'poly', ...polyBounds(b.points) })),
    ...circleBuildings.map(b => ({ ...b, shape: 'circle', x: b.cx - b.r, y: b.cy - b.r, w: b.r * 2, h: b.r * 2 })),
  ];

  const flowPaths = [
    "M255,90 C240,120 230,150 225,175",
    "M370,100 C350,125 330,150 305,178",
    "M455,120 C495,150 515,175 530,205",
    "M700,180 C670,195 640,215 610,235",
    "M556,265 C540,285 520,295 500,300",
    "M700,290 C725,310 730,330 730,350",
    "M600,330 C570,350 540,360 505,365",
    "M600,410 C570,420 540,430 505,430",
    "M782,420 C755,440 730,455 710,475",
    "M527,520 C505,530 490,535 486,540",
    "M782,635 C750,645 715,650 690,650",
    "M285,300 C265,330 245,360 240,390",
    "M130,300 C165,330 195,355 225,405",
    "M221,466 C250,490 270,510 280,525",
    "M95,466 C135,485 165,505 205,530",
    "M78,548 C120,565 155,580 205,600",
    "M130,588 C165,600 205,615 255,645",
    "M250,700 C265,730 275,760 288,782",
    "M480,650 C495,675 495,700 490,720",
    "M782,660 C730,680 660,700 600,725",
    "M600,660 C580,690 560,715 520,745"
  ];

  const campusBuildingsGroup = document.getElementById('buildings');
  const campusExtGroup = document.getElementById('extinguishers');
  const campusAirconGroup = document.getElementById('airconIcons');
  mapBuildings.forEach(b => {
    const ns = 'http://www.w3.org/2000/svg';
    let shape;
    if (b.shape === 'circle') {
      shape = document.createElementNS(ns, 'circle');
      shape.setAttribute('cx', b.cx);
      shape.setAttribute('cy', b.cy);
      shape.setAttribute('r', b.r);
    } else if (b.shape === 'poly') {
      shape = document.createElementNS(ns, 'polygon');
      shape.setAttribute('points', b.points);
    } else {
      shape = document.createElementNS(ns, 'rect');
      shape.setAttribute('x', b.x);
      shape.setAttribute('y', b.y);
      shape.setAttribute('width', b.w);
      shape.setAttribute('height', b.h);
      shape.setAttribute('rx', 3);
    }
    shape.setAttribute('class', 'bldg campus-area');
    if (b.id) shape.setAttribute('id', b.id);
    shape.setAttribute('data-name', b.name);
    shape.setAttribute('aria-label', b.name);
    shape.setAttribute('tabindex', '0');
    shape.setAttribute('onclick', 'selectMapBuilding(this)');
    shape.setAttribute('fill', '#ffffff');
    const title = document.createElementNS(ns, 'title');
    title.textContent = b.name;
    shape.appendChild(title);
    shape.setAttribute('stroke', '#2a2a2a');
    shape.setAttribute('stroke-width', '1.4');
    shape.setAttribute('cursor', 'pointer');
    campusBuildingsGroup.appendChild(shape);

    // Building name label — sized down for smaller buildings so the text
    // (and its background box) stays within the building instead of
    // spilling into whichever building sits next to it.
    const labelGroup = document.createElementNS(ns, 'g');
    const fontSize = b.w > 90 ? 8 : b.w > 60 ? 7 : b.w > 35 ? 6 : 5.5;
    const maxChars = b.w > 90 ? 26 : b.w > 70 ? 20 : b.w > 45 ? 14 : b.w > 25 ? 10 : 7;
    const words = b.name.split(' ');
    const lines = [];
    let currentLine = '';
    words.forEach(word => {
      const candidate = currentLine ? `${currentLine} ${word}` : word;
      if (candidate.length <= maxChars) {
        currentLine = candidate;
      } else {
        if (currentLine) lines.push(currentLine);
        currentLine = word;
      }
    });
    if (currentLine) lines.push(currentLine);
    const visibleLines = lines.slice(0, 2);
    const lineHeight = fontSize + 2;
    const boxWidth = Math.min(Math.max(b.w - 4, 16), 100);
    const boxHeight = Math.max(visibleLines.length * lineHeight + 4, fontSize + 6);
    const boxX = b.x + (b.w - boxWidth) / 2;
    const boxY = b.y + b.h / 2 - boxHeight / 2;

    const labelBg = document.createElementNS(ns, 'rect');
    labelBg.setAttribute('x', boxX);
    labelBg.setAttribute('y', boxY);
    labelBg.setAttribute('width', boxWidth);
    labelBg.setAttribute('height', boxHeight);
    labelBg.setAttribute('rx', 3);
    labelBg.setAttribute('fill', 'rgba(255,255,255,0.9)');
    labelBg.setAttribute('stroke', 'rgba(0,0,0,0.08)');
    labelGroup.appendChild(labelBg);

    const label = document.createElementNS(ns, 'text');
    label.setAttribute('class', 'campus-bldg-label');
    label.setAttribute('style', `font-size:${fontSize}px`);
    label.setAttribute('x', b.x + b.w / 2);
    label.setAttribute('pointer-events', 'none');
    const firstLineY = boxY + lineHeight - 1;
    visibleLines.forEach((line, idx) => {
      const tspan = document.createElementNS(ns, 'tspan');
      tspan.setAttribute('x', b.x + b.w / 2);
      tspan.setAttribute('y', firstLineY + idx * lineHeight);
      tspan.textContent = line;
      label.appendChild(tspan);
    });
    labelGroup.appendChild(label);
    campusBuildingsGroup.appendChild(labelGroup);

    // Marks exactly where a fire extinguisher is on the campus map — visible
    // at a glance without having to click into the building first, and
    // hoverable for a quick type/installed-year summary without opening the
    // full drill panel. Driven by the real fire_extinguishers rows (not the
    // hardcoded hasExt flag, which doesn't match the actual records for
    // every building).
    const buildingExtinguishers = feRegistry.filter(u => u.loc === b.name);
    if (buildingExtinguishers.length > 0) {
      const ext = document.createElementNS(ns, 'text');
      ext.setAttribute('class', 'ext-icon');
      ext.setAttribute('x', b.x + 6);
      ext.setAttribute('y', b.y + b.h - 6);
      // Use the actual element reference (not a string-built selector) so
      // building names with quotes/apostrophes (e.g. "Registrar's office")
      // can't break the click handler.
      ext.addEventListener('click', () => selectMapBuilding(shape));
      ext.textContent = '🧯';
      const extTitle = document.createElementNS(ns, 'title');
      extTitle.textContent = buildingExtinguishers
        .map(u => `${u.id}: ${u.type} — Installed ${u.year}`)
        .join('\n');
      ext.appendChild(extTitle);
      campusExtGroup.appendChild(ext);
    }

    // Same idea, for the Aircon Condition tab — marks which buildings have
    // a registered aircon unit, hoverable for unit name/condition.
    const buildingAircons = airconRegistry.filter(u => u.loc === b.name);
    if (buildingAircons.length > 0) {
      const ac = document.createElementNS(ns, 'text');
      ac.setAttribute('class', 'ext-icon');
      ac.setAttribute('x', b.x + 6);
      ac.setAttribute('y', b.y + b.h - 6);
      ac.addEventListener('click', () => selectMapBuilding(shape));
      ac.textContent = '❄️';
      const acTitle = document.createElementNS(ns, 'title');
      acTitle.textContent = buildingAircons
        .map(u => `${u.unit}: ${u.condition}`)
        .join('\n');
      ac.appendChild(acTitle);
      campusAirconGroup.appendChild(ac);
    }
  });

  // Recolors every building on the map for the active tab, re-adds the
  // status badges (which differ per tab), and refreshes the Critical Alerts
  // stat card — that card always reflects Fire Extinguisher status
  // specifically, regardless of which tab is open.
  const statusIconsByTab = {
    fe:     { warning: '⚠️', new: '🆕', expires: '⏳', normal: '⚪' },
    aircon: { warning: '❌', new: '✅', expires: '🧊', normal: '⚪' },
  };

  function recolorMap() {
    document.querySelectorAll('#campusSVG .status-icon-badge').forEach(b => b.remove());
    const ns = 'http://www.w3.org/2000/svg';
    const icons = statusIconsByTab[activeSafetyTab];

    document.querySelectorAll('#campusSVG .campus-area').forEach(shape => {
      const name = shape.getAttribute('data-name');
      shape.classList.remove('area-normal', 'area-warning', 'area-new', 'area-expires');
      const status = computeAreaStatus(name, activeSafetyTab);
      shape.classList.add(`area-${status}`);
    });

    mapBuildings.forEach(b => {
      const status = computeAreaStatus(b.name, activeSafetyTab);
      if (!icons[status]) return;
      const badge = document.createElementNS(ns, 'text');
      badge.setAttribute('class', `status-icon-badge status-icon-${status}`);
      badge.setAttribute('x', b.x + b.w - 3);
      badge.setAttribute('y', b.y + 13);
      badge.setAttribute('text-anchor', 'end');
      badge.setAttribute('pointer-events', 'none');
      badge.textContent = icons[status];
      campusBuildingsGroup.appendChild(badge);
    });

    const criticalAreas = mapBuildings.filter(b => computeAreaStatus(b.name, 'fe') === 'warning');
    document.getElementById('criticalAlertsValue').textContent = `${criticalAreas.length} active`;
    document.getElementById('criticalAlertsSub').textContent = criticalAreas.length ?
      `Attention needed: ${criticalAreas.map(b => b.name).join(', ')}` :
      'No critical areas detected';
  }

  recolorMap();

  const campusFlowsGroup = document.getElementById('flows');
  flowPaths.forEach(d => {
    const ns = 'http://www.w3.org/2000/svg';
    const p = document.createElementNS(ns, 'path');
    p.setAttribute('class', 'flow');
    p.setAttribute('d', d);
    p.setAttribute('fill', 'none');
    p.setAttribute('stroke', '#aaa');
    p.setAttribute('stroke-width', '1');
    p.setAttribute('stroke-dasharray', '3 3');
    campusFlowsGroup.appendChild(p);
  });

  function closeDrill() {
    document.getElementById('mapLayout').classList.remove('drilled');
    document.getElementById('drillPanel').style.display = 'none';
    document.querySelectorAll('.campus-area').forEach(g => g.classList.remove('area-selected'));
    document.getElementById('missingAlert').style.display = 'none';
  }


  // Keylogs and Guard dashboard scripts removed; functionality moved to dedicated pages
  // Report generation removed; reporting now lives in Records, Archiving & Reports

  function showToast(msg, isError = false) {
    const t = document.createElement('div');
    t.className = 'sj-toast' + (isError ? ' sj-toast-error' : '');
    t.innerHTML = `<i class="bi bi-${isError?'exclamation-triangle':'check-circle-fill'}"></i> ${msg}`;
    document.body.appendChild(t);
    requestAnimationFrame(() => t.classList.add('show'));
    setTimeout(() => {
      t.classList.remove('show');
      setTimeout(() => t.remove(), 400);
    }, 3500);
  }
</script>
<?= $this->endSection() ?>