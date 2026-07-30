<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<link rel="stylesheet" href="<?= base_url('Assets/css/safety.css') ?>">

<div class="sj-wrapper">
  <div class="page-header">
    <div>
      <h1><?= esc($title) ?></h1>
      <p class="page-subtitle">Campus fire safety coverage and inspection readiness at a glance.</p>
    </div>
  </div>

  <div class="sub-tabs">
    <div class="sub-tab-spacer"></div>
    <button class="btn-report" onclick="openReportModal()">
      <i class="bi bi-file-earmark-bar-graph-fill"></i> View Report
    </button>
  </div>

  <div class="overview-grid">
    <div class="overview-card">
      <div class="card-title"><i class="bi bi-fire"></i> Fire Safety Coverage</div>
      <div class="card-value"><?= (int) $coverage_total ?> units</div>
      <div class="card-sub"><?= (int) $coverage_attention ?> require attention • <?= (int) $coverage_refill ?> due for refill</div>
    </div>
    <div class="overview-card">
      <div class="card-title"><i class="bi bi-clipboard2-check"></i> Inspection Readiness</div>
      <div class="card-value"><?= (int) $inspection_readiness ?>%</div>
      <div class="card-sub">Share of units not past their next inspection due date</div>
    </div>
    <div class="overview-card">
      <div class="card-title"><i class="bi bi-exclamation-octagon"></i> Critical Alerts</div>
      <div class="card-value" id="criticalAlertsValue">0 active</div>
      <div class="card-sub" id="criticalAlertsSub">No critical areas detected</div>
    </div>
  </div>

  <!-- ── SUB-TAB: CAMPUS MAP ─────────────────────────────────────────── -->
  <div id="subtab-map" class="sub-pane active">
    <div class="map-layout" id="mapLayout">
      <div class="map-container" id="mapContainer">
        <div class="map-legend">
          <span class="leg-item"><span class="leg-dot normal"></span> ⚪ Normal</span>
          <span class="leg-item"><span class="leg-dot warning"></span> ⚠️ Warning</span>
          <span class="leg-item"><span class="leg-dot new"></span> 🆕 New Installed</span>
          <span class="leg-item"><span class="leg-dot refill"></span> ⏳ Expires Soon</span>
        </div>

        <svg id="campusSVG" viewBox="0 0 900 860" xmlns="http://www.w3.org/2000/svg">
          <path d="M55,40 C15,300 15,600 60,760" fill="none" stroke="#aaa" stroke-width="1" stroke-dasharray="3 3"/>
          <rect id="z-main" class="campus-area" x="278" y="134" width="205" height="431" rx="4" fill="transparent" stroke="#2a2a2a" stroke-width="1.4" data-name="Main evacuation open space" data-cat="Assembly zone — no building number" onclick="selectMapBuilding(this)"/>
          <text x="380" y="345" class="num" style="font-size:12px;">MAIN EVACUATION</text>
          <text x="380" y="360" class="num" style="font-size:12px;">OPEN SPACE</text>
          <g id="flows"></g>
          <g id="buildings"></g>
          <g id="extinguishers"></g>
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
        <div class="dp-fe-grid" id="dpFeGrid"></div>
        <div class="dp-section-title"><i class="bi bi-clock-history"></i> Expiry Alerts</div>
        <div id="dpAlerts" class="dp-alerts"></div>
        <div class="dp-section-title"><i class="bi bi-clipboard2-check"></i> Inspection Checklist</div>
        <div id="dpChecklist" class="dp-checklist"></div>
        <div class="dp-section-title"><i class="bi bi-person-badge"></i> Assigned Inspector</div>
        <div class="dp-inspector" id="dpInspector"></div>
      </div>
    </div>
  </div>

  </div>
</div>

<!-- ── MODALS ───────────────────────────────────────────────────────── -->
<div id="reportModal" class="sj-modal-overlay" style="display:none">
  <div class="sj-modal">
    <div class="sj-modal-header">
      <h3><i class="bi bi-file-earmark-bar-graph-fill"></i> Safety Inspection Report</h3>
      <button onclick="closeModal('reportModal')"><i class="bi bi-x-lg"></i></button>
    </div>
    <div class="sj-modal-body">
      <div class="report-filters">
        <div class="rf-group"><label>View By</label>
          <select id="rptView"><option>Day</option><option>Month</option><option>Year</option></select>
        </div>
        <div class="rf-group"><label>Date</label>
          <input type="date" id="rptDate" value="<?= date('Y-m-d') ?>">
        </div>
        <div class="rf-group"><label>Building</label>
          <select id="rptBuilding">
            <option value="">All</option>
            <option>Admin Building</option><option>Library</option>
            <option>Science Building</option><option>Gymnasium</option>
            <option>Canteen</option><option>Engineering</option>
            <option>CCS Building</option><option>Clinic</option>
          </select>
        </div>
        <button class="btn-maroon-sm" onclick="generateReport()"><i class="bi bi-search"></i> Generate</button>
      </div>
      <div id="reportOutput" class="report-output">
        <div class="report-empty">Select filters and click Generate to view report.</div>
      </div>
    </div>
  </div>
</div>

<!-- Keyscan modal removed; Keylogs moved to Safety -> Keylogs page -->

<script>
// AREAS and feRegistry come straight from the database (fire_extinguishers) —
// see SafetyController::index(). No hardcoded demo values.
const AREAS = <?= $areas_json ?>;
let feRegistry = <?= $fe_registry_json ?>;

// Keylogs and Guard data moved to separate Safety pages (sidebar)

function drillDown(areaKey) {
  const area = AREAS[areaKey];
  if (!area) return;

  document.querySelectorAll('.campus-area').forEach(g => g.classList.remove('area-selected'));
  const el = document.getElementById('area-'+areaKey);
  if (el) el.classList.add('area-selected');

  document.getElementById('mapLayout').classList.add('drilled');

  const units = feRegistry.filter(u => u.loc === area.name);
  const missing = units.some(u => u.status === 'Missing');

  document.getElementById('dpTitle').textContent = area.name;
  document.getElementById('dpSub').textContent   = missing ? '⚠ Missing Fire Extinguisher Detected' : 'Fire Extinguisher Status';

  const areaStatus = getAreaStatus({ areaKey });
  const drillPanelEl = document.getElementById('drillPanel');
  drillPanelEl.classList.remove('st-warning', 'st-new', 'st-expires', 'st-normal');
  drillPanelEl.classList.add(`st-${areaStatus}`);

  const today = new Date();
  let feHtml = '';
  units.forEach(u => {
    const daysLeft = Math.ceil((new Date(u.nextDue) - today) / 86400000);
    const urgency  = daysLeft < 0 ? 'fe-urgent' : daysLeft < 30 ? 'fe-warn' : 'fe-ok';
    const age      = today.getFullYear() - u.year;
    feHtml += `
    <div class="fe-card ${urgency}">
      <div class="fec-id">${u.id}</div>
      <div class="fec-status status-${u.status.toLowerCase().replace(' ','')}">
        ${u.status === 'New' ? '🟢' : u.status === 'Refillable' ? '🟠' : '⚫'} ${u.status}
      </div>
      <div class="fec-row"><span>Type</span><strong>${u.type}</strong></div>
      <div class="fec-row"><span>Installed</span><strong>${u.year}</strong></div>
      <div class="fec-row"><span>Weight</span><strong>${u.kg} kg</strong></div>
      <div class="fec-row"><span>Installed by</span><strong>${u.inspector}</strong></div>
      <div class="fec-row"><span>Assigned Guard</span><strong>${u.assigned}</strong></div>
      <div class="fec-row"><span>Last Insp.</span><strong>${u.lastInsp}</strong></div>
      <div class="fec-row"><span>Next Due</span><strong class="${daysLeft<0?'text-danger':daysLeft<30?'text-warn':''}">${u.nextDue} (${daysLeft<0?'OVERDUE':daysLeft+'d'})</strong></div>
    </div>`;
  });
  document.getElementById('dpFeGrid').innerHTML = feHtml || '<div class="no-data">No units registered for this area.</div>';

  let alertHtml = '';
  units.forEach(u => {
    const d = Math.ceil((new Date(u.nextDue) - today) / 86400000);
    if (d < 0)  alertHtml += `<div class="dp-alert-item urgent"><i class="bi bi-exclamation-octagon-fill"></i> <strong>${u.id}</strong> — OVERDUE by ${Math.abs(d)} days. Immediate action required.</div>`;
    else if (d < 30) alertHtml += `<div class="dp-alert-item warn"><i class="bi bi-exclamation-triangle-fill"></i> <strong>${u.id}</strong> — Inspection due in ${d} days (${u.nextDue}).</div>`;
  });
  if (missing) alertHtml += `<div class="dp-alert-item urgent"><i class="bi bi-fire"></i> Missing fire extinguisher slot detected — Admin has been notified.</div>`;
  document.getElementById('dpAlerts').innerHTML = alertHtml || '<div class="no-alert">No alerts for this area.</div>';

  const checklists = {
    all: ['Inspect pressure gauge','Check for physical damage','Verify pin and seal intact','Check weight (kg)','Record inspection date','Update digital registry','Confirm location accessibility']
  };
  let clHtml = '';
  checklists.all.forEach((item,i) => {
    const done = i < 5;
    clHtml += `<label class="cl-item ${done?'done':''}"><input type="checkbox" ${done?'checked':''} onchange="logCheck(this,'${areaKey}','${item}')"> ${item}</label>`;
  });
  document.getElementById('dpChecklist').innerHTML = clHtml;

  const inspector = units[0]?.inspector || '—';
  const assigned  = units[0]?.assigned || '—';
  document.getElementById('dpInspector').innerHTML = `
    <div class="inspector-card">
      <div class="insp-av st-${areaStatus}">${inspector.charAt(0)}</div>
      <div>
        <div class="insp-name">${inspector}</div>
        <div class="insp-role">Safety Inspector</div>
        <div class="insp-meta"><i class="bi bi-person-badge"></i> ${assigned} &nbsp;|&nbsp; <i class="bi bi-calendar3"></i> ${new Date().toLocaleDateString('en-PH',{month:'short',day:'numeric',year:'numeric'})} &nbsp;|&nbsp; <i class="bi bi-clock"></i> ${new Date().toLocaleTimeString('en-PH',{hour:'2-digit',minute:'2-digit'})}</div>
      </div>
    </div>`;

  if (missing) {
    const alertBanner = document.getElementById('missingAlert');
    document.getElementById('missingAlertMsg').textContent = `⚠ Missing fire extinguisher detected in ${area.name}. Admin notified.`;
    alertBanner.style.display = 'flex';
  }

  document.getElementById('drillPanel').style.display = 'block';
}

function selectMapBuilding(el) {
  document.querySelectorAll('#campusSVG .campus-area').forEach(g => g.classList.remove('area-selected'));
  el.classList.add('area-selected');

  const name = el.getAttribute('data-name') || el.id || 'Campus area';
  const category = el.getAttribute('data-cat') || 'Campus zone';
  const areaKey = el.getAttribute('data-area-key');
  const area = areaKey ? AREAS[areaKey] : { name };

  const units = feRegistry.filter(u => u.loc === area.name);
  const missing = units.some(u => u.status === 'Missing');

  document.getElementById('mapLayout').classList.add('drilled');
  document.getElementById('dpTitle').textContent = area.name;
  document.getElementById('dpSub').textContent = missing ? '⚠ Missing Fire Extinguisher Detected' : 'Fire Extinguisher Status';
  const statusBox = document.getElementById('dpStatusBox');
  const activeStatus = document.querySelector('.campus-area.area-selected')?.classList.contains('area-warning') ? 'Warning' : document.querySelector('.campus-area.area-selected')?.classList.contains('area-new') ? 'New Installed' : document.querySelector('.campus-area.area-selected')?.classList.contains('area-expires') ? 'Expires Soon' : 'Normal';
  statusBox.innerHTML = `<strong>${activeStatus}</strong><div>${activeStatus === 'Warning' ? 'Immediate attention needed for this area.' : activeStatus === 'New Installed' ? 'This area has recently installed equipment.' : activeStatus === 'Expires Soon' ? 'This area has equipment nearing expiry.' : 'No special alert status for this area.'}</div>`;

  const statusSlug = activeStatus === 'Warning' ? 'warning' : activeStatus === 'New Installed' ? 'new' : activeStatus === 'Expires Soon' ? 'expires' : 'normal';
  const drillPanelEl = document.getElementById('drillPanel');
  drillPanelEl.classList.remove('st-warning', 'st-new', 'st-expires', 'st-normal');
  drillPanelEl.classList.add(`st-${statusSlug}`);

  const today = new Date();
  if (units.length === 0) {
    document.getElementById('dpFeGrid').innerHTML = `<div class="no-data" style="padding:1rem;">No fire extinguisher records available for ${name}.</div>`;
    document.getElementById('dpAlerts').innerHTML = `<div class="no-alert">Tap a unit row to see more details.</div>`;
    document.getElementById('dpChecklist').innerHTML = '';
    document.getElementById('dpInspector').innerHTML = `<div class="inspector-card"><div class="insp-av st-${statusSlug}">${name.charAt(0)}</div><div><div class="insp-name">${name}</div><div class="insp-role">${category}</div><div class="insp-meta"><i class="bi bi-calendar3"></i> ${new Date().toLocaleDateString('en-PH',{month:'short',day:'numeric',year:'numeric'})} &nbsp;|&nbsp; <i class="bi bi-clock"></i> ${new Date().toLocaleTimeString('en-PH',{hour:'2-digit',minute:'2-digit'})}</div></div></div>`;
  } else {
    let feHtml = '';
    let alertHtml = '';
    units.forEach(u => {
      const daysLeft = Math.ceil((new Date(u.nextDue) - today) / 86400000);
      const urgency  = daysLeft < 0 ? 'fe-urgent' : daysLeft < 30 ? 'fe-warn' : 'fe-ok';
      const age      = today.getFullYear() - u.year;
      feHtml += `
      <div class="fe-card ${urgency}">
        <div class="fec-id">${u.id}</div>
        <div class="fec-status status-${u.status.toLowerCase().replace(' ','')}">
          ${u.status === 'New' ? '🟢' : u.status === 'Refillable' ? '🟠' : '⚫'} ${u.status}
        </div>
        <div class="fec-row"><span>Type</span><strong>${u.type}</strong></div>
        <div class="fec-row"><span>Installed</span><strong>${u.year}</strong></div>
        <div class="fec-row"><span>Weight</span><strong>${u.kg} kg</strong></div>
        <div class="fec-row"><span>Installed by</span><strong>${u.inspector}</strong></div>
        <div class="fec-row"><span>Assigned Guard</span><strong>${u.assigned}</strong></div>
        <div class="fec-row"><span>Last Insp.</span><strong>${u.lastInsp}</strong></div>
        <div class="fec-row"><span>Next Due</span><strong class="${daysLeft<0?'text-danger':daysLeft<30?'text-warn':''}">${u.nextDue} (${daysLeft<0?'OVERDUE':daysLeft+'d'})</strong></div>
      </div>`;
      if (daysLeft < 30) {
        alertHtml += `<div class="dp-alert-item ${daysLeft<0?'urgent':'warn'}"><i class="bi bi-exclamation-triangle-fill"></i> <strong>${u.id}</strong> — Next inspection due in ${daysLeft<0?Math.abs(daysLeft)+' days overdue':daysLeft+' days'}.</div>`;
      }
    });
    document.getElementById('dpFeGrid').innerHTML = feHtml;
    document.getElementById('dpAlerts').innerHTML = alertHtml || '<div class="no-alert">No active alerts for this area.</div>';

    const checklists = {
      all: ['Inspect pressure gauge','Check for physical damage','Verify pin and seal intact','Check weight (kg)','Record inspection date','Update digital registry','Confirm location accessibility']
    };
    let clHtml = '';
    checklists.all.forEach((item,i) => {
      const done = i < 5;
      clHtml += `<label class="cl-item ${done?'done':''}"><input type="checkbox" ${done?'checked':''} onchange="logCheck(this,'${areaKey}','${item}')"> ${item}</label>`;
    });
    document.getElementById('dpChecklist').innerHTML = clHtml;

    const inspector = units[0]?.inspector || '—';
    const assigned  = units[0]?.assigned || '—';
    document.getElementById('dpInspector').innerHTML = `
      <div class="inspector-card">
        <div class="insp-av st-${statusSlug}">${inspector.charAt(0)}</div>
        <div>
          <div class="insp-name">${inspector}</div>
          <div class="insp-role">Safety Inspector</div>
          <div class="insp-meta"><i class="bi bi-person-badge"></i> ${assigned} &nbsp;|&nbsp; <i class="bi bi-calendar3"></i> ${new Date().toLocaleDateString('en-PH',{month:'short',day:'numeric',year:'numeric'})} &nbsp;|&nbsp; <i class="bi bi-clock"></i> ${new Date().toLocaleTimeString('en-PH',{hour:'2-digit',minute:'2-digit'})}</div>
        </div>
      </div>`;
  }

  if (missing) {
    const alertBanner = document.getElementById('missingAlert');
    document.getElementById('missingAlertMsg').textContent = `⚠ Missing fire extinguisher detected in ${area.name}. Admin notified.`;
    alertBanner.style.display = 'flex';
  }

  document.getElementById('drillPanel').style.display = 'block';
}

const mapBuildings = [
  {n:1, name:'Main entrance gate', cat:'NA', x:745, y:802, w:144, h:31, hasExt:false, circle:false},
  {n:2, name:'University cafeteria / bookstore / sewing', cat:'Blue', x:321, y:720, w:142, h:82, hasExt:true, circle:false},
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
  {n:13, id:'area-hrmKitchen', name:'HRM kitchen', cat:'Violet', x:221, y:15, w:67, h:62, hasExt:true, circle:false},
  {n:14, name:'College of Education building', cat:'Violet', x:278, y:62, w:185, h:72, hasExt:true, circle:false},
  {n:16, name:'Animation Lab / ROTC office', cat:'Pink', x:514, y:98, w:180, h:123, hasExt:false, circle:false},
  {n:17, name:'LG Sinco Computer Center building', cat:'Pink', x:553, y:221, w:141, h:98, hasExt:true, circle:false},
  {n:18, name:'Sofia Soller Sinco Hall', cat:'Pink', x:512, y:288, w:192, h:123, hasExt:true, circle:false},
  {n:19, name:'College of Art & Sciences building', cat:'Yellow', x:519, y:406, w:67, h:185, hasExt:true, circle:false},
  {n:20, name:'Art & Science laboratories / audio visual rooms', cat:'Yellow', x:586, y:411, w:226, h:149, hasExt:true, circle:false},
  {n:21, name:'College of Business Economics and Accountancy', cat:'Pink', x:704, y:237, w:185, h:92, hasExt:true, circle:false},
  {n:22, name:'College of Nursing', cat:'Yellow', x:812, y:360, w:77, h:236, hasExt:true, circle:false},
  {n:23, name:'Administration building', cat:'Yellow', x:745, y:555, w:144, h:103, hasExt:true, circle:false, areaKey:'admin'},
  {n:24, name:'Rizal monument / social garden', cat:'NA', x:596, y:591, w:149, h:93, hasExt:false, circle:false},
  {n:25, name:'Registrar\'s office', cat:'Orange', x:694, y:720, w:195, h:36, hasExt:true, circle:false},
  {n:26, name:'Business and Finance office', cat:'Orange', x:694, y:756, w:195, h:46, hasExt:false, circle:false},
  {n:27, name:'Old College of Industrial Engineering and Technology', cat:'Orange', x:496, y:746, w:234, h:56, hasExt:true, circle:false},
  {n:28, name:'Overhead water supply tank', cat:'NA', x:894, y:722, w:16, h:16, hasExt:false, circle:true},
  {n:29, name:'Flag pole', cat:'NA', x:508, y:611, w:12, h:12, hasExt:false, circle:true}
];

const flowPaths = [
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

// Single source of truth for a zone's status — driven entirely by its real
// fire_extinguishers rows, so the map badge and the Critical Alerts count
// (computed the same way, below) can never disagree.
function getAreaStatus(building) {
  const areaKey = building?.areaKey;
  const area = areaKey ? AREAS[areaKey] : null;
  if (!area) return 'normal';

  const units = feRegistry.filter(u => u.loc === area.name);
  if (units.length === 0) return 'normal';

  const today = new Date();
  const daysLeft = u => Math.ceil((new Date(u.nextDue) - today) / 86400000);

  const isCritical = units.some(u => u.status === 'Missing' || u.status === 'Defective' || daysLeft(u) < 0);
  if (isCritical) return 'warning';

  const expiresSoon = units.some(u => u.status === 'Refillable' || daysLeft(u) < 30);
  if (expiresSoon) return 'expires';

  if (units.every(u => u.status === 'New')) return 'new';

  return 'normal';
}

const campusBuildingsGroup = document.getElementById('buildings');
const campusExtGroup = document.getElementById('extinguishers');
mapBuildings.forEach(b => {
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
  shape.setAttribute('class', 'bldg campus-area');
  const areaStatus = getAreaStatus(b);
  if (areaStatus) shape.classList.add(`area-${areaStatus}`);
  if (b.id) shape.setAttribute('id', b.id);
  if (b.areaKey) shape.setAttribute('data-area-key', b.areaKey);
  shape.setAttribute('data-name', b.name);
  shape.setAttribute('data-cat', b.cat);
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
  campusBuildingsGroup.appendChild(labelGroup);

  const statusIcons = { warning: '⚠️', new: '🆕', expires: '⏳', normal: '⚪' };
  if (areaStatus && statusIcons[areaStatus]) {
    const badge = document.createElementNS(ns, 'text');
    badge.setAttribute('class', `status-icon-badge status-icon-${areaStatus}`);
    badge.setAttribute('x', b.x + b.w - 3);
    badge.setAttribute('y', b.y + 13);
    badge.setAttribute('text-anchor', 'end');
    badge.setAttribute('pointer-events', 'none');
    badge.textContent = statusIcons[areaStatus];
    campusBuildingsGroup.appendChild(badge);
  }

  if (b.hasExt) {
    const ext = document.createElementNS(ns, 'rect');
    ext.setAttribute('class', 'ext');
    ext.setAttribute('x', b.x + 4);
    ext.setAttribute('y', b.y + 4);
    ext.setAttribute('width', 8);
    ext.setAttribute('height', 8);
    campusExtGroup.appendChild(ext);
  }
});

// Critical Alerts stat card — counts the SAME buildings just colored "warning"
// above, using the same getAreaStatus() call, so the number always matches
// exactly what's flagged red on the map.
const criticalAreas = mapBuildings.filter(b => b.areaKey && getAreaStatus(b) === 'warning');
document.getElementById('criticalAlertsValue').textContent = `${criticalAreas.length} active`;
document.getElementById('criticalAlertsSub').textContent = criticalAreas.length
  ? `Attention needed: ${criticalAreas.map(b => b.name).join(', ')}`
  : 'No critical areas detected';

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

function logCheck(cb, area, item) {
  const t = new Date().toLocaleTimeString('en-PH',{hour:'2-digit',minute:'2-digit'});
  console.log(`[${t}] ${area} — ${item}: ${cb.checked ? 'Checked' : 'Unchecked'}`);
}


// Keylogs and Guard dashboard scripts removed; functionality moved to dedicated pages

function openReportModal() { document.getElementById('reportModal').style.display = 'flex'; }

function generateReport() {
  const bldg  = document.getElementById('rptBuilding').value;
  const date  = document.getElementById('rptDate').value;
  const view  = document.getElementById('rptView').value;
  const today = new Date();

  const filtered = feRegistry.filter(u => !bldg || u.loc === bldg);
  const overdue  = filtered.filter(u => new Date(u.nextDue) < today);
  const soon     = filtered.filter(u => { const d = Math.ceil((new Date(u.nextDue)-today)/86400000); return d>=0&&d<30; });

  let html = `
  <div class="rpt-header">
    <div><h3>Safety Inspection Report</h3><p>${bldg||'All Buildings'} — ${view}: ${date}</p></div>
    <div class="rpt-summary">
      <span class="rpt-stat"><i class="bi bi-fire"></i> ${filtered.length} Units</span>
      <span class="rpt-stat danger"><i class="bi bi-exclamation-octagon"></i> ${overdue.length} Overdue</span>
      <span class="rpt-stat warn"><i class="bi bi-clock"></i> ${soon.length} Due Soon</span>
    </div>
  </div>
  <table class="sj-table" style="font-size:.78rem">
    <thead><tr><th>Unit ID</th><th>Type</th><th>Location</th><th>Weight</th><th>Status</th><th>Last Insp.</th><th>Next Due</th><th>Inspector</th><th>Assigned Guard</th></tr></thead>
    <tbody>${filtered.map(u=>{
      const d = Math.ceil((new Date(u.nextDue)-today)/86400000);
      return `<tr>
        <td><strong>${u.id}</strong></td><td>${u.type}</td><td>${u.loc}</td><td>${u.kg}kg</td>
        <td><span class="fe-status-badge st-${u.status.toLowerCase().replace(' ','')}">${u.status}</span></td>
        <td>${u.lastInsp}</td>
        <td class="${d<0?'text-danger':d<30?'text-warn':''}">${u.nextDue}</td>
        <td>${u.inspector}</td><td>${u.assigned}</td>
      </tr>`;
    }).join('')}</tbody>
  </table>`;
  document.getElementById('reportOutput').innerHTML = html;
}

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

</script>
<?= $this->endSection() ?>