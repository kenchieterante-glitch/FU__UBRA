<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php
  $areas_json = $areas_json ?? '{}';
  $staff_json = $staff_json ?? '[]';
  $checklists_json = $checklists_json ?? '{}';
  $inventory_json = $inventory_json ?? '[]';
  $zone_total = $zone_total ?? 0;
  $zone_cleaned = $zone_cleaned ?? 0;
?>

<div class="page-header">
  <div>
    <h1>Janitorial Monitoring</h1>
    <p class="page-subtitle">Campus janitorial readiness, active shifts, and consumable stock overview.</p>
  </div>
</div>

<div class="kpi-row" id="janitorialSummary"></div>

<div class="sj-wrapper">
  <!-- ── SUB-TABS FOR JANITORIAL ─────────────────────────────────────────── -->
  <div class="sub-tabs">
    <button class="sub-tab active" onclick="switchJanTab('janmap')">
      <i class="bi bi-map-fill"></i> Campus Map
    </button>
    <button class="sub-tab" onclick="switchJanTab('shifts')">
      <i class="bi bi-people-fill"></i> Active Shifts
    </button>
    <button class="sub-tab" onclick="switchJanTab('inventory')">
      <i class="bi bi-box-seam-fill"></i> Consumable Inventory
    </button>
  </div>

  <!-- ── JANITORIAL MAP ───────────────────────────────────────── -->
  <div id="janitorial-janmap" class="sub-pane active">
    <div class="map-layout" id="janMapLayout">
      <div class="map-container">
        <div class="map-legend">
          <span class="leg-item leg-clickable" data-status="clean" onclick="filterMapByStatus('clean')" role="button" tabindex="0">✅ Clean</span>
          <span class="leg-item leg-clickable" data-status="pending" onclick="filterMapByStatus('pending')" role="button" tabindex="0">🕒 Pending</span>
          <span class="leg-item leg-clickable" data-status="needs" onclick="filterMapByStatus('needs')" role="button" tabindex="0">🧹 Needs to Clean</span>
          <span class="leg-item leg-clickable" data-status="untracked" onclick="filterMapByStatus('untracked')" role="button" tabindex="0">⚪ Not a Janitorial Zone</span>
        </div>

        <svg id="janSVG" viewBox="0 0 900 860" xmlns="http://www.w3.org/2000/svg">
          <path d="M55,40 C15,300 15,600 60,760" fill="none" stroke="#aaa" stroke-width="1" stroke-dasharray="3 3"/>
          <rect id="z-main" class="campus-area jan-area" x="278" y="134" width="205" height="431" rx="4" fill="transparent" stroke="#2a2a2a" stroke-width="1.4" data-name="Main evacuation open space" data-cat="Assembly zone — no building number" onclick="selectJanMapBuilding(this)"/>
          <text x="380" y="345" class="num" style="font-size:12px;">MAIN EVACUATION</text>
          <text x="380" y="360" class="num" style="font-size:12px;">OPEN SPACE</text>
          <g id="flows"></g>
          <g id="buildings"></g>
          <g id="extinguishers"></g>
        </svg>
      </div>

      <!-- Janitorial Drill Panel -->
      <div class="drill-panel jan-drill" id="janDrillPanel" style="display:none">
        <div class="dp-header">
          <div>
            <div class="dp-title" id="janDpTitle">Select an area</div>
            <div class="dp-sub">Janitorial Checklist & Assignment</div>
          </div>
          <button class="dp-close" onclick="closeJanDrill()"><i class="bi bi-x-lg"></i></button>
        </div>
        <div id="janDpContent"></div>
      </div>
    </div>
  </div>

  <!-- ── ACTIVE SHIFTS ───────────────────────────────────────── -->
  <div id="janitorial-shifts" class="sub-pane">
    <div class="pane-header">
      <h2 class="pane-title"><i class="bi bi-people-fill"></i> Active Shift Assignments</h2>
      <button class="btn-add-record" onclick="openAddShiftModal()"><i class="bi bi-plus-lg"></i> Assign Staff</button>
    </div>
    <div class="shift-filter-row" id="shiftFilterRow">
      <button class="shift-filter-chip" data-kind="" onclick="filterShiftsByStat('')">All</button>
      <button class="shift-filter-chip" data-kind="done" onclick="filterShiftsByStat('done')">Completed</button>
      <button class="shift-filter-chip" data-kind="pending" onclick="filterShiftsByStat('pending')">Pending</button>
    </div>
    <div class="shift-cards" id="shiftCards"></div>
  </div>

  <!-- ── CONSUMABLE INVENTORY ─────────────────────────────────── -->
  <div id="janitorial-inventory" class="sub-pane">
    <div class="pane-header">
      <h2 class="pane-title"><i class="bi bi-box-seam-fill"></i> Consumable Inventory & Refill Log</h2>
      <button class="btn-add-record" onclick="openAddInventoryModal()"><i class="bi bi-plus-lg"></i> Add Item</button>
    </div>
    <div class="table-wrap">
      <table class="sj-table">
        <thead>
          <tr><th>Item</th><th>Category</th><th>Unit</th><th>Current Stock</th><th>Reorder At</th><th>Last Refill</th><th>Status</th><th>Action</th></tr>
        </thead>
        <tbody id="inventoryBody"></tbody>
      </table>
    </div>
  </div>
</div>

<!-- ── MODALS ───────────────────────────────────────────────────────── -->
<div id="addInventoryModal" class="sj-modal-overlay" style="display:none">
  <div class="sj-modal">
    <div class="sj-modal-header">
      <h3><i class="bi bi-box-seam-fill"></i> Add Inventory Item</h3>
      <button onclick="closeModal('addInventoryModal')"><i class="bi bi-x-lg"></i></button>
    </div>
    <div class="sj-modal-body">
      <div class="form-grid2">
        <div class="fg"><label>Item Name</label><input type="text" id="invName" placeholder="e.g. Floor Cleaner"></div>
        <div class="fg"><label>Category</label>
          <select id="invCat"><option>Cleaning Agent</option><option>Tools</option><option>Disposable</option><option>Equipment</option></select>
        </div>
        <div class="fg"><label>Unit</label><input type="text" id="invUnit" placeholder="Liters / Pieces / Rolls"></div>
        <div class="fg"><label>Current Stock</label><input type="number" id="invStock" placeholder="e.g. 20"></div>
        <div class="fg"><label>Reorder Threshold</label><input type="number" id="invReorder" placeholder="e.g. 5"></div>
      </div>
    </div>
    <div class="sj-modal-footer">
      <button class="btn-cancel" onclick="closeModal('addInventoryModal')">Cancel</button>
      <button class="btn-maroon-sm" onclick="saveInventoryItem()"><i class="bi bi-floppy-fill"></i> Add Item</button>
    </div>
  </div>
</div>

<script>
// All zone, staff, checklist, and inventory data below comes straight from the
// database (janitorial_assignments, janitorial_tasks, consumable_inventory) —
// see JanitorialController::index(). No hardcoded demo values.
const AREAS = <?= $areas_json ?>;
const janAreaStatuses = {};
const janStaff = <?= $staff_json ?>;
const janAreaChecklists = <?= $checklists_json ?>;
let inventoryItems = <?= $inventory_json ?>;
// Zone counts (not per-shift) — a zone with two staff assigned only counts
// as "cleaned" once every assignment mapped to it is done. See
// JanitorialController::index() for the aggregation.
const zoneTotal = <?= (int) $zone_total ?>;
const zoneCleaned = <?= (int) $zone_cleaned ?>;

function getJanStatusValue(areaKey) {
  const explicit = janAreaStatuses[areaKey];
  if (explicit === 'clean' || explicit === 'pending' || explicit === 'needs') {
    return explicit;
  }

  if (areaKey && janAreaChecklists[areaKey]) {
    const data = janAreaChecklists[areaKey];
    const done = data.tasks.filter(t => t.done).length;
    const total = data.tasks.length;
    if (total === 0) return 'untracked';
    const pct = Math.round((done / total) * 100);
    if (pct === 100) return 'clean';
    if (pct >= 50) return 'pending';
    return 'needs';
}

  // Most buildings on this map aren't assigned janitorial zones at all
  // (only the 8 real ones are) — those must read as neutral/untracked,
  // not amber "pending", or every unassigned building looks unfinished.
  return 'untracked';
}

function getJanStatusDisplay(areaKey) {
  const value = getJanStatusValue(areaKey);
  if (value === 'clean') return { label: 'Clean', value: 'clean' };
  if (value === 'pending') return { label: 'Pending', value: 'pending' };
  if (value === 'untracked') return { label: 'Not a Janitorial Zone', value: 'untracked' };
  return { label: 'Needs to Clean', value: 'needs' };
}

function switchJanTab(id) {
  document.querySelectorAll('.sub-tab').forEach(t => t.classList.toggle('active', t.getAttribute('onclick').includes("'"+id+"'")));
  document.querySelectorAll('.sub-pane').forEach(p => p.classList.toggle('active', p.id === 'janitorial-'+id));
  if (id==='shifts') renderShiftCards();
  if (id==='inventory') renderInventory();
}

function janDrillDown(area) {
  document.querySelectorAll('.jan-area').forEach(g => g.classList.remove('area-selected'));
  const el = document.querySelector(`.jan-area[data-area-key="${area}"]`);
  if (el) el.classList.add('area-selected');

  document.getElementById('janMapLayout').classList.add('drilled');

  const data = janAreaChecklists[area];
  if (!data) return;

  const name = AREAS[area]?.name || area;
  const status = getJanStatusDisplay(area);
  document.getElementById('janDpTitle').textContent = name;

  const done   = data.tasks.filter(t=>t.done).length;
  const total  = data.tasks.length;
  const pct    = Math.round((done/total)*100);
  const barCol = pct===100?'#16a34a':pct>=50?'#c8963e':'#9ca3af';

  let html = `
  <div class="jan-assigned-card">
    <div class="jac-av st-${status.value}">${data.staff.charAt(0)}</div>
    <div>
      <div class="jac-name">${data.staff}</div>
      <div class="jac-meta">Cleaner: <strong>${data.staff}</strong></div>
      <div class="jac-meta">Status: <strong>${status.label}</strong></div>
      <div class="jac-meta">Shift: ${data.shift} &nbsp;|&nbsp; Zone: ${name}</div>
      <div class="jac-meta"><i class="bi bi-calendar3"></i> ${new Date().toLocaleDateString('en-PH',{month:'short',day:'numeric',year:'numeric'})} &nbsp;|&nbsp; <i class="bi bi-clock"></i> ${new Date().toLocaleTimeString('en-PH',{hour:'2-digit',minute:'2-digit'})}</div>
    </div>
  </div>
  <div class="jan-checklist">`;

  data.tasks.forEach((t,i) => {
    html += `
    <label class="jcl-item ${t.done?'done':''}">
      <input type="checkbox" ${t.done?'checked':''}>
      <span class="jcl-text">${t.t}</span>
      ${t.done ? `<span class="jcl-time"><i class="bi bi-check-circle-fill"></i> ${t.time}</span>` : '<span class="jcl-time pending">Pending</span>'}
    </label>`;
  });

  html += `</div>`;
  document.getElementById('janDpContent').innerHTML = html;
  const drillPanel = document.getElementById('janDrillPanel');
  drillPanel.classList.remove('st-clean', 'st-pending', 'st-needs');
  drillPanel.classList.add(`st-${status.value}`);
  drillPanel.style.display = 'block';
}

function selectJanMapBuilding(el) {
  document.querySelectorAll('#janSVG .jan-area').forEach(g => g.classList.remove('area-selected'));
  el.classList.add('area-selected');
  document.getElementById('janMapLayout').classList.add('drilled');

  const areaKey = el.getAttribute('data-area-key');
  if (areaKey && janAreaChecklists[areaKey]) {
    janDrillDown(areaKey);
    return;
  }

  const name = el.getAttribute('data-name') || el.id || 'Campus area';
  const shortName = name.charAt(0);
  const status = getJanStatusDisplay(el.getAttribute('data-area-key'));

  document.getElementById('janDpTitle').textContent = name;

  const html = `
  <div class="jan-assigned-card">
    <div class="jac-av st-${status.value}">${shortName}</div>
    <div>
      <div class="jac-meta">Cleaner: <strong>No assigned cleaner</strong></div>
      <div class="jac-meta">Status: <strong>${status.label}</strong></div>
      <div class="jac-meta"><i class="bi bi-calendar3"></i> ${new Date().toLocaleDateString('en-PH',{month:'short',day:'numeric',year:'numeric'})} &nbsp;|&nbsp; <i class="bi bi-clock"></i> ${new Date().toLocaleTimeString('en-PH',{hour:'2-digit',minute:'2-digit'})}</div>
    </div>
  </div>
  <div class="jan-checklist">
    <p class="placeholder">This map highlights the selected campus building and shows its evacuation zone.</p>
  </div>`;

  document.getElementById('janDpContent').innerHTML = html;
  const drillPanel = document.getElementById('janDrillPanel');
  drillPanel.classList.remove('st-clean', 'st-pending', 'st-needs');
  drillPanel.classList.add(`st-${status.value}`);
  drillPanel.style.display = 'block';
}

const janMapBuildings = [
  {n:1, name:'Main entrance gate', cat:'NA', x:745, y:802, w:144, h:31, hasExt:false, circle:false},
  {n:2, name:'University cafeteria / bookstore / sewing', cat:'Blue', x:321, y:720, w:142, h:82, hasExt:true, circle:false, areaKey:'canteen'},
  {n:3, name:'College of Law building', cat:'Blue', x:314, y:591, w:149, h:72, hasExt:true, circle:false},
  {n:4, name:'College of Agriculture and SIE', cat:'Blue', x:262, y:591, w:57, h:211, hasExt:true, circle:false},
  {n:5, name:'Museo de Vicente', cat:'Green', x:129, y:529, w:82, h:78, hasExt:true, circle:false},
  {n:6, name:'Bunk house', cat:'Green', x:57, y:529, w:61, h:103, hasExt:false, circle:false},
  {n:7, name:'Service / exit gate', cat:'NA', x:67, y:488, w:51, h:41, hasExt:true, circle:false},
  {n:8, name:'University library', cat:'Green', x:90, y:406, w:131, h:72, hasExt:true, circle:false, areaKey:'library'},
  {n:9, name:'Electric pump house', cat:'Green', x:206, y:380, w:41, h:31, hasExt:false, circle:false},
  {n:10, name:'Executive house', cat:'Violet', x:129, y:272, w:108, h:62, hasExt:true, circle:false},
  {n:11, name:'Water pump', cat:'NA', x:167, y:216, w:20, h:20, hasExt:false, circle:true},
  {n:12, name:'Guest house', cat:'Violet', x:170, y:62, w:67, h:87, hasExt:true, circle:false},
  {n:13, name:'HRM kitchen', cat:'Violet', x:221, y:15, w:67, h:62, hasExt:true, circle:false},
  {n:14, name:'College of Education building', cat:'Violet', x:278, y:62, w:185, h:72, hasExt:true, circle:false},
  {n:16, name:'Animation Lab / ROTC office', cat:'Pink', x:514, y:98, w:180, h:123, hasExt:false, circle:false},
  {n:17, name:'LG Sinco Computer Center building', cat:'Pink', x:553, y:221, w:141, h:98, hasExt:true, circle:false, areaKey:'ccs'},
  {n:18, name:'Sofia Soller Sinco Hall', cat:'Pink', x:512, y:288, w:192, h:123, hasExt:true, circle:false, areaKey:'gym'},
  {n:19, name:'College of Art & Sciences building', cat:'Yellow', x:519, y:406, w:67, h:185, hasExt:true, circle:false},
  {n:20, name:'Art & Science laboratories / audio visual rooms', cat:'Yellow', x:586, y:411, w:226, h:149, hasExt:true, circle:false, areaKey:'science'},
  {n:21, name:'College of Business Economics and Accountancy', cat:'Pink', x:704, y:237, w:185, h:92, hasExt:true, circle:false},
  {n:22, name:'College of Nursing', cat:'Yellow', x:812, y:360, w:77, h:236, hasExt:true, circle:false},
  {n:23, name:'Administration building', cat:'Yellow', x:745, y:555, w:144, h:103, hasExt:true, circle:false, areaKey:'admin'},
  {n:24, name:'Rizal monument / social garden', cat:'NA', x:596, y:591, w:149, h:93, hasExt:false, circle:false},
  {n:25, name:'Registrar\'s office', cat:'Orange', x:694, y:720, w:195, h:36, hasExt:true, circle:false, areaKey:'clinic'},
  {n:26, name:'Business and Finance office', cat:'Orange', x:694, y:756, w:195, h:46, hasExt:false, circle:false},
  {n:27, name:'Old College of Industrial Engineering and Technology', cat:'Orange', x:496, y:746, w:234, h:56, hasExt:true, circle:false, areaKey:'engr'},
  {n:28, name:'Overhead water supply tank', cat:'NA', x:894, y:722, w:16, h:16, hasExt:false, circle:true},
  {n:29, name:'Flag pole', cat:'NA', x:508, y:611, w:12, h:12, hasExt:false, circle:true}
];

const janFlowPaths = [
  "M255,77 C240,110 230,140 225,170",
  "M370,90 C350,120 330,150 300,180",
  "M460,110 C500,140 520,170 540,210",
  "M694,170 C670,190 640,210 610,225",
  "M553,270 C540,290 520,300 500,300",
  "M694,280 C720,300 730,320 730,340",
  "M596,320 C570,340 540,350 510,355",
  "M596,400 C570,410 540,420 510,420",
  "M745,410 C720,430 700,450 690,470",
  "M519,500 C500,510 480,515 483,520",
  "M745,600 C720,610 690,615 660,615",
  "M280,300 C260,330 240,360 237,380",
  "M129,300 C160,330 190,350 220,400",
  "M221,442 C250,470 270,500 280,520",
  "M90,442 C130,460 160,480 200,510",
  "M67,508 C110,530 150,550 200,570",
  "M129,568 C160,590 200,610 250,640",
  "M262,700 C280,730 290,760 300,780",
  "M463,650 C480,670 490,690 490,710",
  "M745,660 C700,680 640,700 590,720",
  "M596,650 C580,680 560,710 520,740"
];

const janBuildingsGroup = document.getElementById('buildings');
const janExtGroup = document.getElementById('extinguishers');
janMapBuildings.forEach(b => {
  const ns = 'http://www.w3.org/2000/svg';
  let shape;
  if (b.circle) {
    shape = document.createElementNS(ns, 'circle');
    shape.setAttribute('cx', b.x + b.w / 2);
    shape.setAttribute('cy', b.y + b.h / 2);
    shape.setAttribute('r', b.w / 2);
  } else {
    shape = document.createElementNS(ns, 'rect');
    shape.setAttribute('x', b.x);
    shape.setAttribute('y', b.y);
    shape.setAttribute('width', b.w);
    shape.setAttribute('height', b.h);
    shape.setAttribute('rx', 3);
  }
  const status = getJanStatusValue(b.areaKey);
  shape.setAttribute('class', `bldg jan-area ${status}`);
  shape.setAttribute('data-name', b.name);
  shape.setAttribute('data-cat', b.cat);
  shape.setAttribute('data-status', status);
  if (b.areaKey) shape.setAttribute('data-area-key', b.areaKey);
  shape.setAttribute('aria-label', b.name);
  shape.setAttribute('tabindex', '0');
  shape.setAttribute('onclick', 'selectJanMapBuilding(this)');
  const title = document.createElementNS(ns, 'title');
  title.textContent = b.name;
  shape.appendChild(title);
  shape.setAttribute('fill', '#ffffff');
  shape.setAttribute('stroke', '#2a2a2a');
  shape.setAttribute('stroke-width', '1.4');
  shape.setAttribute('cursor', 'pointer');
  janBuildingsGroup.appendChild(shape);

  const labelGroup = document.createElementNS(ns, 'g');
  const maxChars = b.w > 90 ? 24 : b.w > 70 ? 18 : b.w > 45 ? 12 : 8;
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
  const boxWidth = Math.min(Math.max(b.w - 8, 24), 90);
  const boxHeight = Math.max(visibleLines.length * 10 + 6, 16);
  const boxX = b.x + (b.w - boxWidth) / 2;
  const boxY = b.y + b.h / 2 - (visibleLines.length * 5) - 4;

  const labelBg = document.createElementNS(ns, 'rect');
  labelBg.setAttribute('x', boxX);
  labelBg.setAttribute('y', boxY);
  labelBg.setAttribute('width', boxWidth);
  labelBg.setAttribute('height', boxHeight);
  labelBg.setAttribute('rx', 4);
  labelBg.setAttribute('fill', 'rgba(255,255,255,0.9)');
  labelBg.setAttribute('stroke', 'rgba(0,0,0,0.08)');
  labelGroup.appendChild(labelBg);

  const label = document.createElementNS(ns, 'text');
  label.setAttribute('class', 'jan-map-label');
  label.setAttribute('x', b.x + b.w / 2);
  label.setAttribute('y', b.y + b.h / 2 - (visibleLines.length > 1 ? 4 : 0));
  label.setAttribute('pointer-events', 'none');
  visibleLines.forEach((line, idx) => {
    const tspan = document.createElementNS(ns, 'tspan');
    tspan.setAttribute('x', b.x + b.w / 2);
    tspan.setAttribute('y', b.y + b.h / 2 + (idx * 10) - (visibleLines.length > 1 ? 4 : 0));
    tspan.textContent = line;
    label.appendChild(tspan);
  });
  labelGroup.appendChild(label);
  janBuildingsGroup.appendChild(labelGroup);

  const janStatusIcons = { clean: '✅', pending: '🕒', needs: '🧹' };
  if (janStatusIcons[status]) {
    const janBadge = document.createElementNS(ns, 'text');
    janBadge.setAttribute('class', `status-icon-badge status-icon-${status}`);
    janBadge.setAttribute('x', b.x + b.w - 3);
    janBadge.setAttribute('y', b.y + 13);
    janBadge.setAttribute('text-anchor', 'end');
    janBadge.setAttribute('pointer-events', 'none');
    janBadge.textContent = janStatusIcons[status];
    janBuildingsGroup.appendChild(janBadge);
  }

  if (b.hasExt) {
    const ext = document.createElementNS(ns, 'rect');
    ext.setAttribute('class', 'ext');
    ext.setAttribute('x', b.x + 4);
    ext.setAttribute('y', b.y + 4);
    ext.setAttribute('width', 8);
    ext.setAttribute('height', 8);
    janExtGroup.appendChild(ext);
  }
});

const janFlowsGroup = document.getElementById('flows');
janFlowPaths.forEach(d => {
  const ns = 'http://www.w3.org/2000/svg';
  const p = document.createElementNS(ns, 'path');
  p.setAttribute('class', 'flow');
  p.setAttribute('d', d);
  p.setAttribute('fill', 'none');
  p.setAttribute('stroke', '#aaa');
  p.setAttribute('stroke-width', '1');
  p.setAttribute('stroke-dasharray', '3 3');
  janFlowsGroup.appendChild(p);
});

function closeJanDrill() {
  document.getElementById('janMapLayout').classList.remove('drilled');
  document.getElementById('janDrillPanel').style.display = 'none';
  document.querySelectorAll('.jan-area').forEach(g => g.classList.remove('area-selected'));
}

// Legend items act as a toggle filter on the map itself — click a status to
// dim every building that isn't that status; click it again to clear.
let mapStatusFilter = null;

function filterMapByStatus(status) {
  mapStatusFilter = (mapStatusFilter === status) ? null : status;

  document.querySelectorAll('.map-legend .leg-item').forEach(el => {
    el.classList.toggle('leg-active', el.getAttribute('data-status') === mapStatusFilter);
  });

  document.querySelectorAll('#janSVG .jan-area').forEach(el => {
    const match = !mapStatusFilter || el.getAttribute('data-status') === mapStatusFilter;
    el.classList.toggle('jan-area-dimmed', !match);
  });
}

function clearMapFilter() {
  mapStatusFilter = null;
  document.querySelectorAll('.map-legend .leg-item').forEach(el => el.classList.remove('leg-active'));
  document.querySelectorAll('#janSVG .jan-area').forEach(el => el.classList.remove('jan-area-dimmed'));
}

let shiftsFilter = null;

// Called from the Dashboard's "Cleaning Completion" box and the filter chips
// on the Active Shifts tab — 'done' shows only fully-completed shifts,
// 'pending' shows only shifts still in progress, '' shows everything.
function filterShiftsByStat(kind) {
  shiftsFilter = kind || null;
  switchJanTab('shifts');
  document.querySelectorAll('#shiftFilterRow .shift-filter-chip').forEach(chip => {
    chip.classList.toggle('active', chip.getAttribute('data-kind') === (shiftsFilter || ''));
  });
}

function renderShiftCards() {
  const list = shiftsFilter === 'pending' ? janStaff.filter(s => s.done !== s.tasks)
             : shiftsFilter === 'done'    ? janStaff.filter(s => s.tasks > 0 && s.done === s.tasks)
             : janStaff;
  if (!list.length) {
    const msg = shiftsFilter === 'pending' ? 'All shifts are fully completed.'
              : shiftsFilter === 'done'    ? 'No shifts are fully completed yet.'
              : 'No shift assignments recorded yet.';
    document.getElementById('shiftCards').innerHTML = `<div class="no-data">${msg}</div>`;
    return;
  }
  document.getElementById('shiftCards').innerHTML = list.map(s => {
    const pct = Math.round((s.done/s.tasks)*100);
    const col = pct===100?'#16a34a':pct>=50?'#c8963e':'#9ca3af';
    return `
    <div class="shift-card">
      <div class="sc-top">
        <div class="sc-av">${s.photo}</div>
        <div class="sc-info">
          <div class="sc-name">${s.name}</div>
          <div class="sc-zone"><i class="bi bi-geo-alt-fill"></i> ${s.zone}</div>
          <div class="sc-shift"><i class="bi bi-clock"></i> ${s.shift}</div>
        </div>
        <span class="sc-pct-badge" style="background:${col}">${pct}%</span>
      </div>
      <div class="sc-bar-bg"><div class="sc-bar-fill" style="width:${pct}%;background:${col}"></div></div>
      <div class="sc-task-count">${s.done} of ${s.tasks} tasks done</div>
      <button class="tbl-btn" onclick="switchJanTab('janmap');janDrillDown('${s.area}')">View Checklist</button>
    </div>`;
  }).join('');
}

// 'low' and 'out' are mutually exclusive: an item at 0 stock is Out of
// Stock, not Low Stock, even though 0 also satisfies "<= reorder threshold".
function inventoryStatus(item) {
  if (item.stock === 0) return 'out';
  if (item.stock <= item.reorder) return 'low';
  return 'ok';
}

let inventoryFilter = null;

// Called from the summary cards — jump to Consumable Inventory and show
// only items in that stock status.
function filterInventoryByStat(kind) {
  inventoryFilter = kind || null;
  switchJanTab('inventory');
}

function renderInventory() {
  const indexed = inventoryItems.map((item, i) => ({ item, i }));
  const list = inventoryFilter ? indexed.filter(({ item }) => inventoryStatus(item) === inventoryFilter) : indexed;

  if (!list.length) {
    document.getElementById('inventoryBody').innerHTML = `<tr><td colspan="8"><div class="no-data">No items match this filter.</div></td></tr>`;
    return;
  }

  document.getElementById('inventoryBody').innerHTML = list.map(({ item, i }) => {
    const status = inventoryStatus(item);
    const col = status === 'out' ? 'text-danger' : status === 'low' ? 'text-warn' : '';
    const st  = status === 'out' ? '<span class="inv-badge inv-out">Out of Stock</span>'
              : status === 'low' ? '<span class="inv-badge inv-low">Low Stock ⚠</span>'
              : '<span class="inv-badge inv-ok">OK</span>';

    return `<tr>
      <td><strong>${item.name}</strong></td>
      <td>${item.cat}</td>
      <td>${item.unit}</td>
      <td class="${col}"><strong>${item.stock}</strong></td>
      <td>${item.reorder}</td>
      <td>${item.lastRefill}</td>
      <td>${st}</td>
      <td>
        <button class="tbl-btn" onclick="refillItem(${i})"><i class="bi bi-arrow-up-circle"></i> Refill</button>
      </td>
    </tr>`;
  }).join('');
}

function renderJanitorialSummary() {
  const totalZones = Object.keys(AREAS).length;
  const activeShifts = janStaff.length;
  const lowStock = inventoryItems.filter(item => inventoryStatus(item) === 'low').length;
  const outOfStock = inventoryItems.filter(item => inventoryStatus(item) === 'out').length;
  const pendingZones = zoneTotal - zoneCleaned;

  document.getElementById('janitorialSummary').innerHTML = `
    <div class="kpi-card kpi-card-clickable" onclick="clearMapFilter();switchJanTab('janmap')" role="button" tabindex="0">
      <div class="card-title"><i class="bi bi-shield-check"></i> Janitorial Zones</div>
      <div class="card-value">${totalZones}</div>
      <div class="card-note">Monitored campus areas</div>
    </div>
    <div class="kpi-card kpi-card-clickable" onclick="filterShiftsByStat('')" role="button" tabindex="0">
      <div class="card-title"><i class="bi bi-people-fill"></i> Active Shifts</div>
      <div class="card-value">${activeShifts}</div>
      <div class="card-note">Staff currently on duty</div>
    </div>
    <div class="kpi-card kpi-card-clickable" onclick="filterShiftsByStat('pending')" role="button" tabindex="0">
      <div class="card-title"><i class="bi bi-broom"></i> Janitorial Completion</div>
      <div class="card-value">${zoneCleaned}/${zoneTotal} cleaned</div>
      <div class="card-note">${pendingZones} pending follow-up</div>
    </div>
    <div class="kpi-card kpi-card-clickable" onclick="filterInventoryByStat('low')" role="button" tabindex="0">
      <div class="card-title"><i class="bi bi-box-seam"></i> Low Stock Items</div>
      <div class="card-value">${lowStock}</div>
      <div class="card-note">Need restock soon</div>
    </div>
    <div class="kpi-card kpi-card-clickable" onclick="filterInventoryByStat('out')" role="button" tabindex="0">
      <div class="card-title"><i class="bi bi-exclamation-circle"></i> Out of Stock</div>
      <div class="card-value">${outOfStock}</div>
      <div class="card-note">Immediate replenishment</div>
    </div>
  `;

  document.querySelectorAll('#janitorialSummary .kpi-card-clickable').forEach(card => {
    card.addEventListener('keydown', e => {
      if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        card.click();
      }
    });
  });
}

function refillItem(i) {
  const item = inventoryItems[i];
  const qty = prompt(`Refill "${item.name}" — enter quantity to add:`);
  if (!qty || isNaN(qty) || Number(qty) <= 0) return;

  const fd = new FormData();
  fd.append('quantity', qty);
  fetch(`<?= base_url('janitorial/refillInventory/') ?>${item.id}`, { method: 'POST', headers: csrfHeaders(), body: fd })
    .then(() => window.location.reload());
}

function saveInventoryItem() {
  const name = document.getElementById('invName').value.trim();
  if (!name) { showToast('Item name required.', true); return; }

  const fd = new FormData();
  fd.append('item_name', name);
  fd.append('category', document.getElementById('invCat').value);
  fd.append('unit', document.getElementById('invUnit').value);
  fd.append('current_stock', document.getElementById('invStock').value || 0);
  fd.append('reorder_threshold', document.getElementById('invReorder').value || 0);

  fetch('<?= base_url('janitorial/addInventoryItem') ?>', { method: 'POST', headers: csrfHeaders(), body: fd })
    .then(() => window.location.reload());
}

function openAddShiftModal()     { showToast('Staff assignment form — connect to PersonnelController.'); }
function openAddInventoryModal() { document.getElementById('addInventoryModal').style.display = 'flex'; }

function closeModal(id) { document.getElementById(id).style.display = 'none'; }
document.querySelectorAll('.sj-modal-overlay').forEach(o => {
  o.addEventListener('click', e => { if (e.target === o) o.style.display = 'none'; });
});

function showToast(msg, isError=false) {
  const t = document.createElement('div');
  t.className = 'sj-toast' + (isError?' sj-toast-error':'');
  t.innerHTML = `<i class="bi bi-${isError?'exclamation-triangle':'check-circle-fill'}"></i> ${msg}`;
  document.body.appendChild(t);
  requestAnimationFrame(()=>t.classList.add('show'));
  setTimeout(()=>{ t.classList.remove('show'); setTimeout(()=>t.remove(),400); }, 3500);
}

renderJanitorialSummary();
renderShiftCards();
renderInventory();
document.querySelector('#shiftFilterRow .shift-filter-chip[data-kind=""]')?.classList.add('active');

// Arriving from the Dashboard's "Cleaning Completion" box.
if (new URLSearchParams(window.location.search).get('filter') === 'pending') {
  filterShiftsByStat('pending');
}
</script>
<?= $this->endSection() ?>