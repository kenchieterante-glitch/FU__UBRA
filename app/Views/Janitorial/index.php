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

<div class="stat-cards" id="janitorialSummary"></div>

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
      <div class="map-container" id="janMapContainer">
        <div class="map-search-row">
          <div class="map-search-box">
            <i class="bi bi-search"></i>
            <input type="text" id="janMapSearchInput" placeholder="Search building…" oninput="onJanMapSearch(this.value)" autocomplete="off">
            <button type="button" class="map-search-clear" id="janMapSearchClearBtn" onclick="clearJanMapSearch()" style="display:none" aria-label="Clear search"><i class="bi bi-x"></i></button>
          </div>
          <div class="map-search-results" id="janMapSearchResults" style="display:none"></div>
        </div>
        <div class="map-legend-toggle-wrap">
          <button type="button" class="map-legend-btn" id="janMapLegendBtn" onclick="toggleJanMapLegend()" aria-label="Toggle legend">
            <i class="bi bi-funnel"></i> Legend
          </button>
          <div class="map-legend-popup" id="janMapLegendPopup">
            <div class="map-legend">
              <span class="leg-title">Zone Status</span>
              <span class="leg-item leg-clickable" data-status="clean" onclick="filterMapByStatus('clean')" role="button" tabindex="0"><span class="leg-dot leg-dot-green"></span> Clean</span>
              <span class="leg-item leg-clickable" data-status="pending" onclick="filterMapByStatus('pending')" role="button" tabindex="0"><span class="leg-dot leg-dot-amber"></span> Pending</span>
              <span class="leg-item leg-clickable" data-status="needs" onclick="filterMapByStatus('needs')" role="button" tabindex="0"><span class="leg-dot leg-dot-red"></span> Needs to Clean</span>
              <span class="leg-item leg-clickable" data-status="untracked" onclick="filterMapByStatus('untracked')" role="button" tabindex="0"><span class="leg-dot leg-dot-gray"></span> Not a Janitorial Zone</span>
            </div>
          </div>
        </div>

        <svg id="janSVG" viewBox="0 0 950 900" xmlns="http://www.w3.org/2000/svg">
          <rect id="z-main" class="campus-area jan-area" x="280" y="175" width="205" height="430" rx="4" fill="transparent" stroke="#2a2a2a" stroke-width="1.4" data-name="Main evacuation open space" data-cat="Assembly zone — no building number" onclick="selectJanMapBuilding(this)"/>
          <line x1="382" y1="175" x2="382" y2="605" stroke="#2a2a2a" stroke-width="1" opacity=".6" />
          <line x1="280" y1="390" x2="485" y2="390" stroke="#2a2a2a" stroke-width="1" opacity=".6" />
          <text x="382" y="385" class="num" style="font-size:12px;">MAIN EVACUATION</text>
          <text x="382" y="398" class="num" style="font-size:12px;">OPEN SPACE</text>
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
          <tr><th>Item</th><th>Category</th><th>Unit</th><th>Current Stock</th><th>Last Refill</th><th>Status</th><th>Action</th></tr>
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

// Coordinates copied straight from Safety's corrected/hand-traced campus
// map (fu_evacuation_map.html) so both pages show the exact same building
// layout — Janitorial's own cat/hasExt/areaKey metadata is kept per building.
const janMapBuildingsRaw = [
  {n:1, name:'Main Entrance Gate', cat:'NA', x:836, y:784, w:46, h:24, hasExt:false},
  {n:2, name:'University Cafeteria, Bookstore, Sewing', cat:'Blue', x:320, y:782, w:148, h:66, hasExt:true, areaKey:'canteen'},
  {n:3, name:'College of Law Building', cat:'Blue', x:320, y:612, w:155, h:112, hasExt:true},
  {n:4, name:'College of Agriculture and SIE', cat:'Blue', x:224, y:578, w:54, h:255, hasExt:true},
  {n:5, name:'Museo de Vicente', cat:'Green', x:78, y:580, w:135, h:62, hasExt:true},
  {n:6, name:'Bunk House', cat:'Green', points:'42,650 110,650 85,715 42,710', hasExt:false},
  {n:7, name:'Service / Exit Gate', cat:'NA', points:'78,560 145,521 208,521 208,548 90,572', hasExt:true},
  {n:8, name:'University Library', cat:'Green', x:95, y:466, w:126, h:50, hasExt:true, areaKey:'library'},
  {n:9, name:'Electric Pump House', cat:'Green', x:212, y:432, w:38, h:32, hasExt:false},
  {n:10, name:'Executive House', cat:'Violet', x:130, y:346, w:100, h:48, hasExt:true},
  {n:11, name:'Water Pump', cat:'NA', cx:173, cy:289, r:9, hasExt:false},
  {n:12, name:'Guest House', cat:'Violet', x:165, y:110, w:55, h:90, hasExt:true},
  {n:13, name:'HRM Kitchen', cat:'Violet', x:222, y:60, w:55, h:48, hasExt:true},
  {n:14, name:'College of Education Building', cat:'Violet', x:280, y:114, w:178, h:46, hasExt:true},
  {n:15, name:'Parade Ground', cat:'NA', x:458, y:60, w:57, h:115, hasExt:false},
  {n:16, name:'Animation Lab / ROTC Office', cat:'Pink', points:'515,150 700,150 700,218 590,218 515,180', hasExt:false},
  {n:17, name:'LG Sinco Computer Center Building', cat:'Pink', x:556, y:222, w:145, h:100, hasExt:true, areaKey:'ccs'},
  {n:18, name:'Sofia Soller Sinco Hall', cat:'Pink', x:505, y:328, w:198, h:118, hasExt:true, areaKey:'gym'},
  {n:19, name:'College of Art & Sciences Building', cat:'Yellow', x:527, y:472, w:48, h:142, hasExt:true},
  {n:20, name:'Art & Science Laboratories / Audio Visual Rooms', cat:'Yellow', x:577, y:472, w:248, h:143, hasExt:true, areaKey:'science'},
  {n:21, name:'College of Business Economics and Accountancy', cat:'Pink', x:712, y:300, w:192, h:55, hasExt:true},
  {n:22, name:'College of Nursing', cat:'Yellow', x:830, y:362, w:52, h:254, hasExt:true},
  {n:23, name:'Administration Building', cat:'Yellow', x:782, y:625, w:100, h:48, hasExt:true, areaKey:'admin'},
  {n:24, name:'Rizal Monument / Social Garden', cat:'NA', x:588, y:648, w:132, h:92, hasExt:false},
  {n:25, name:'Registrar\'s Office', cat:'Orange', x:702, y:760, w:130, h:22, hasExt:true, areaKey:'clinic'},
  {n:26, name:'Business and Finance Office', cat:'Orange', x:702, y:784, w:130, h:24, hasExt:false},
  {n:27, name:'Old College of Industrial Engineering and Technology', cat:'Orange', x:480, y:795, w:210, h:34, hasExt:true, areaKey:'engr'},
  {n:28, name:'Overhead Water Supply Tank', cat:'NA', cx:850, cy:745, r:7, hasExt:false},
  {n:29, name:'Flag Pole', cat:'NA', cx:542, cy:720, r:6, hasExt:false},
];

function janPolyBounds(points) {
  const pts = points.split(' ').map(p => p.split(',').map(Number));
  const xs = pts.map(p => p[0]), ys = pts.map(p => p[1]);
  const minX = Math.min(...xs), minY = Math.min(...ys);
  return { x: minX, y: minY, w: Math.max(...xs) - minX, h: Math.max(...ys) - minY };
}

// Every entry ends up with a uniform {x,y,w,h} bounding box (plus its raw
// points/cx/cy/r when relevant) regardless of shape, so label placement and
// status badges work the same way for rects, polygons, and circles.
const janMapBuildings = janMapBuildingsRaw.map(b => {
  if (b.points) return { ...b, poly: true, circle: false, ...janPolyBounds(b.points) };
  if (b.r !== undefined) return { ...b, poly: false, circle: true, x: b.cx - b.r, y: b.cy - b.r, w: b.r * 2, h: b.r * 2 };
  return { ...b, poly: false, circle: false };
});

const janFlowPaths = [
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
  } else if (b.poly) {
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
  if (lines.length > visibleLines.length) {
    visibleLines[visibleLines.length - 1] += '…';
  }
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
  resetJanMapZoom();
}

// ── Search-to-zoom: typing a building name pans/zooms the SVG to it and
// opens the same full-detail drill panel a click on the shape would. ──
const JAN_CAMPUS_VIEWBOX = '0 0 950 900';

function escapeHtmlAttr(str) {
  return String(str)
    .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
}

function onJanMapSearch(query) {
  query = query.trim().toLowerCase();
  const resultsEl = document.getElementById('janMapSearchResults');
  document.getElementById('janMapSearchClearBtn').style.display = query ? 'inline-flex' : 'none';

  if (!query) {
    resultsEl.style.display = 'none';
    resultsEl.innerHTML = '';
    return;
  }

  const matches = janMapBuildings.filter(b => b.name.toLowerCase().includes(query)).slice(0, 8);
  resultsEl.style.display = 'block';
  resultsEl.innerHTML = matches.length
    ? matches.map(b => `<div class="map-search-item" data-building="${escapeHtmlAttr(b.name)}">${escapeHtmlAttr(b.name)}</div>`).join('')
    : '<div class="map-search-empty">No matching building.</div>';
}

document.getElementById('janMapSearchResults').addEventListener('click', (e) => {
  const item = e.target.closest('.map-search-item');
  if (item) zoomToJanBuilding(item.dataset.building);
});

function zoomToJanBuilding(name) {
  const b = janMapBuildings.find(x => x.name === name);
  if (!b) return;

  const pad = 50;
  const vx = Math.max(0, b.x - pad);
  const vy = Math.max(0, b.y - pad);
  const vw = b.w + pad * 2;
  const vh = b.h + pad * 2;
  document.getElementById('janSVG').setAttribute('viewBox', `${vx} ${vy} ${vw} ${vh}`);
  document.getElementById('janMapContainer').classList.add('map-zoomed');

  const shape = Array.from(document.querySelectorAll('#janSVG .jan-area'))
    .find(el => el.getAttribute('data-name') === name);
  if (shape) selectJanMapBuilding(shape);

  document.getElementById('janMapSearchInput').value = name;
  document.getElementById('janMapSearchResults').style.display = 'none';
}

function resetJanMapZoom() {
  document.getElementById('janSVG').setAttribute('viewBox', JAN_CAMPUS_VIEWBOX);
  document.getElementById('janMapContainer').classList.remove('map-zoomed');
}

function clearJanMapSearch() {
  document.getElementById('janMapSearchInput').value = '';
  document.getElementById('janMapSearchClearBtn').style.display = 'none';
  document.getElementById('janMapSearchResults').style.display = 'none';
  document.getElementById('janMapSearchResults').innerHTML = '';
  resetJanMapZoom();
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
    document.getElementById('inventoryBody').innerHTML = `<tr><td colspan="7"><div class="no-data">No items match this filter.</div></td></tr>`;
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
    <div class="stat-card stat-card-clickable" onclick="clearMapFilter();switchJanTab('janmap')" role="button" tabindex="0">
      <span class="stat-icon tone-maroon"><i class="fa-solid fa-map-location-dot"></i></span>
      <h3>Janitorial Zones</h3>
      <div class="value">${totalZones}</div>
    </div>
    <div class="stat-card stat-card-clickable" onclick="filterShiftsByStat('')" role="button" tabindex="0">
      <span class="stat-icon tone-neutral"><i class="fa-solid fa-broom"></i></span>
      <h3>Active Shifts</h3>
      <div class="value">${activeShifts}</div>
    </div>
    <div class="stat-card stat-card-clickable" onclick="filterShiftsByStat('pending')" role="button" tabindex="0">
      <span class="stat-icon tone-green"><i class="fa-solid fa-circle-check"></i></span>
      <h3>Janitorial Completion</h3>
      <div class="value">${zoneCleaned}/${zoneTotal}</div>
    </div>
    <div class="stat-card stat-card-clickable" onclick="filterInventoryByStat('low')" role="button" tabindex="0">
      <span class="stat-icon tone-gold"><i class="fa-solid fa-box"></i></span>
      <h3>Low Stock Items</h3>
      <div class="value">${lowStock}</div>
    </div>
    <div class="stat-card stat-card-clickable" onclick="filterInventoryByStat('out')" role="button" tabindex="0">
      <span class="stat-icon tone-red"><i class="fa-solid fa-triangle-exclamation"></i></span>
      <h3>Out of Stock</h3>
      <div class="value">${outOfStock}</div>
    </div>
  `;

  document.querySelectorAll('#janitorialSummary .stat-card-clickable').forEach(card => {
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
    .then(r => {
      if (!r.ok) throw new Error('Refill failed');
      showToast(`"${item.name}" refilled successfully — added ${qty} ${item.unit || ''}.`);
      setTimeout(() => window.location.reload(), 900);
    })
    .catch(() => showToast('Could not refill this item. Please try again.', true));
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

document.addEventListener('click', (e) => {
  if (!e.target.closest('.map-search-row')) {
    document.getElementById('janMapSearchResults').style.display = 'none';
  }
  if (!e.target.closest('.map-legend-toggle-wrap')) {
    document.getElementById('janMapLegendPopup').classList.remove('visible');
    document.getElementById('janMapLegendBtn').classList.remove('active');
  }
});

function toggleJanMapLegend() {
  document.getElementById('janMapLegendPopup').classList.toggle('visible');
  document.getElementById('janMapLegendBtn').classList.toggle('active');
}

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