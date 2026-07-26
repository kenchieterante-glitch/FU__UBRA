<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<link rel="stylesheet" href="<?= base_url('Assets/css/safety.css') ?>">

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
          <span class="leg-item"><span class="leg-dot new"></span> Emergency flow</span>
          <span class="leg-item"><span class="leg-dot refill"></span> Fire extinguisher</span>
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
            <div class="dp-title" id="janDpTitle">Area</div>
            <div class="dp-sub">Janitorial Checklist & Assignment</div>
          </div>
          <button class="dp-close" onclick="closeJanDrill()"><i class="bi bi-x-lg"></i> Back to Map</button>
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
const AREAS = {
  admin:        { name: 'Admin Building', units: ['FE-ADM-01','FE-ADM-02','FE-ADM-03'], missing: true },
  library:      { name: 'Library', units: ['FE-LIB-01','FE-LIB-02','FE-LIB-03'], missing: false },
  science:      { name: 'Science Building', units: ['FE-SCI-01','FE-SCI-02','FE-SCI-03'], missing: false },
  gym:          { name: 'Gymnasium', units: ['FE-GYM-01','FE-GYM-02'], missing: true },
  canteen:      { name: 'Canteen', units: ['FE-CAN-01','FE-CAN-02'], missing: false },
  engr:         { name: 'Engineering', units: ['FE-ENG-01','FE-ENG-02','FE-ENG-03'], missing: false },
  ccs:          { name: 'CCS Building', units: ['FE-CCS-01','FE-CCS-02'], missing: false },
  clinic:       { name: 'Clinic', units: ['FE-CLI-01','FE-CLI-02'], missing: false },
  'guard-post': { name:'Guard House', units: ['FE-GRD-01'], missing: false },
};

const janStaff = [
  { name:'Bautista, M.',  zone:'Admin Building Flr 1', tasks:8, done:6, photo:'B', shift:'7AM-3PM', area:'admin' },
  { name:'Dizon, L.',     zone:'Library',              tasks:6, done:3, photo:'D', shift:'7AM-3PM', area:'library' },
  { name:'Fernandez, G.', zone:'Science Building',     tasks:5, done:1, photo:'F', shift:'6AM-2PM', area:'science' },
  { name:'Hernandez, K.', zone:'Gymnasium',            tasks:4, done:4, photo:'H', shift:'5AM-1PM', area:'gym' },
  { name:'Ignacio, P.',   zone:'Canteen',              tasks:7, done:4, photo:'I', shift:'7AM-3PM', area:'canteen' },
  { name:'Javier, C.',    zone:'Engineering',          tasks:6, done:1, photo:'J', shift:'8AM-4PM', area:'engr' },
  { name:'Lacson, A.',    zone:'CCS Building',         tasks:5, done:5, photo:'L', shift:'7AM-3PM', area:'ccs' },
  { name:'Mendez, R.',    zone:'Clinic',               tasks:4, done:4, photo:'M', shift:'7AM-3PM', area:'clinic' },
];

const janAreaChecklists = {
  admin:   { staff:'Bautista, M.', shift:'7AM-3PM', tasks:[{t:'Sweep & mop corridors',done:true,time:'07:30'},{t:'Clean restrooms — Flr 1',done:true,time:'08:00'},{t:'Empty trash bins',done:true,time:'08:30'},{t:'Wipe window sills',done:true,time:'09:00'},{t:'Mop main lobby',done:true,time:'09:30'},{t:'Replenish soap & tissue',done:true,time:'10:00'},{t:'Clean comfort rooms — Flr 2',done:false,time:null},{t:'General sanitizing',done:false,time:null}] },
  library: { staff:'Dizon, L.',    shift:'7AM-3PM', tasks:[{t:'Dust bookshelves',done:true,time:'07:15'},{t:'Vacuum reading area',done:true,time:'07:45'},{t:'Mop entrance',done:true,time:'08:15'},{t:'Clean restrooms',done:false,time:null},{t:'Empty trash bins',done:false,time:null},{t:'Wipe computer tables',done:false,time:null}] },
  science: { staff:'Fernandez, G.',shift:'6AM-2PM', tasks:[{t:'Sweep lab corridors',done:true,time:'06:30'},{t:'Mop stairs',done:false,time:null},{t:'Empty lab trash',done:false,time:null},{t:'Sanitize lab benches',done:false,time:null},{t:'Clean restrooms',done:false,time:null}] },
  gym:     { staff:'Hernandez, K.',shift:'5AM-1PM', tasks:[{t:'Sweep gym floor',done:true,time:'05:30'},{t:'Mop court',done:true,time:'06:00'},{t:'Clean locker rooms',done:true,time:'06:45'},{t:'Empty trash bins',done:true,time:'07:15'}] },
  canteen: { staff:'Ignacio, P.',  shift:'7AM-3PM', tasks:[{t:'Wipe dining tables',done:true,time:'07:00'},{t:'Sweep floor',done:true,time:'07:20'},{t:'Mop canteen floor',done:true,time:'07:45'},{t:'Clean restrooms',done:true,time:'08:15'},{t:'Empty grease traps',done:false,time:null},{t:'Sanitize counter tops',done:false,time:null},{t:'Replace trash liners',done:false,time:null}] },
  engr:    { staff:'Javier, C.',   shift:'8AM-4PM', tasks:[{t:'Sweep corridors',done:true,time:'08:10'},{t:'Mop workshop floor',done:false,time:null},{t:'Clean restrooms',done:false,time:null},{t:'Empty trash bins',done:false,time:null},{t:'Wipe notice boards',done:false,time:null},{t:'Sanitize door handles',done:false,time:null}] },
  ccs:     { staff:'Lacson, A.',   shift:'7AM-3PM', tasks:[{t:'Sweep corridors',done:true,time:'07:10'},{t:'Mop server room hallway',done:true,time:'07:35'},{t:'Clean restrooms',done:true,time:'08:00'},{t:'Wipe workstations',done:true,time:'08:30'},{t:'Empty trash',done:true,time:'09:00'}] },
  clinic:  { staff:'Mendez, R.',   shift:'7AM-3PM', tasks:[{t:'Sanitize consultation room',done:true,time:'07:05'},{t:'Mop clinic floor',done:true,time:'07:30'},{t:'Clean restroom',done:true,time:'07:55'},{t:'Replace biohazard bags',done:true,time:'08:20'}] },
};

let inventoryItems = [
  { name:'Floor Cleaner (Pine)',  cat:'Cleaning Agent', unit:'Liters',  stock:18, reorder:5,  lastRefill:'2025-07-10' },
  { name:'Toilet Bowl Cleaner',   cat:'Cleaning Agent', unit:'Bottles', stock:7,  reorder:3,  lastRefill:'2025-07-08' },
  { name:'Trash Liners (Large)',  cat:'Disposable',     unit:'Rolls',   stock:4,  reorder:5,  lastRefill:'2025-07-05' },
  { name:'Mop Heads',             cat:'Tools',          unit:'Pieces',  stock:6,  reorder:3,  lastRefill:'2025-07-01' },
  { name:'Disinfectant Spray',    cat:'Cleaning Agent', unit:'Bottles', stock:2,  reorder:4,  lastRefill:'2025-06-28' },
  { name:'Tissue Paper (Rolls)',  cat:'Disposable',     unit:'Rolls',   stock:30, reorder:10, lastRefill:'2025-07-12' },
  { name:'Liquid Hand Soap',      cat:'Cleaning Agent', unit:'Liters',  stock:3,  reorder:4,  lastRefill:'2025-07-09' },
  { name:'Brooms',                cat:'Tools',          unit:'Pieces',  stock:12, reorder:4,  lastRefill:'2025-06-15' },
];

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
  document.getElementById('janDpTitle').textContent = name;

  const done   = data.tasks.filter(t=>t.done).length;
  const total  = data.tasks.length;
  const pct    = Math.round((done/total)*100);
  const barCol = pct===100?'#16a34a':pct>=50?'#c8963e':'#9ca3af';

  let html = `
  <div class="jan-assigned-card">
    <div class="jac-av">${data.staff.charAt(0)}</div>
    <div>
      <div class="jac-name">${data.staff}</div>
      <div class="jac-meta">Shift: ${data.shift} &nbsp;|&nbsp; Zone: ${name}</div>
      <div class="jac-meta"><i class="bi bi-calendar3"></i> ${new Date().toLocaleDateString('en-PH',{month:'short',day:'numeric',year:'numeric'})} &nbsp;|&nbsp; <i class="bi bi-clock"></i> ${new Date().toLocaleTimeString('en-PH',{hour:'2-digit',minute:'2-digit'})}</div>
    </div>
  </div>
  <div class="jan-progress-wrap">
    <div class="jp-label"><span>Task Progress</span><strong>${done}/${total} (${pct}%)</strong></div>
    <div class="jp-bar-bg"><div class="jp-bar-fill" style="width:${pct}%;background:${barCol}"></div></div>
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
  document.getElementById('janDrillPanel').style.display = 'block';
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
  const category = el.getAttribute('data-cat') || 'Campus zone';
  const shortName = name.charAt(0);

  const html = `
  <div class="jan-assigned-card">
    <div class="jac-av">${shortName}</div>
    <div>
      <div class="jac-name">${name}</div>
      <div class="jac-meta">${category}</div>
      <div class="jac-meta"><i class="bi bi-calendar3"></i> ${new Date().toLocaleDateString('en-PH',{month:'short',day:'numeric',year:'numeric'})} &nbsp;|&nbsp; <i class="bi bi-clock"></i> ${new Date().toLocaleTimeString('en-PH',{hour:'2-digit',minute:'2-digit'})}</div>
    </div>
  </div>
  <div class="jan-progress-wrap">
    <div class="jp-label"><span>Selected area</span><strong>${category}</strong></div>
    <div class="jp-bar-bg"><div class="jp-bar-fill" style="width:100%;background:#16a34a"></div></div>
  </div>
  <div class="jan-checklist">
    <p class="placeholder">This map highlights the selected campus building and shows its evacuation zone.</p>
  </div>`;

  document.getElementById('janDpContent').innerHTML = html;
  document.getElementById('janDrillPanel').style.display = 'block';
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
  {n:18, name:'Sofia Soller Sinco Hall', cat:'Pink', x:512, y:288, w:192, h:123, hasExt:true, circle:false},
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
  shape.setAttribute('class', 'bldg jan-area');
  shape.setAttribute('data-name', b.name);
  shape.setAttribute('data-cat', b.cat);
  if (b.areaKey) shape.setAttribute('data-area-key', b.areaKey);
  shape.setAttribute('onclick', 'selectJanMapBuilding(this)');
  shape.setAttribute('fill', '#ffffff');
  shape.setAttribute('stroke', '#2a2a2a');
  shape.setAttribute('stroke-width', '1.4');
  shape.setAttribute('cursor', 'pointer');
  janBuildingsGroup.appendChild(shape);

  const label = document.createElementNS(ns, 'text');
  label.setAttribute('class', 'num');
  label.setAttribute('x', b.x + b.w / 2);
  label.setAttribute('y', b.y + b.h / 2 + 3);
  label.textContent = b.n;
  label.setAttribute('pointer-events', 'none');
  janBuildingsGroup.appendChild(label);

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

function renderShiftCards() {
  document.getElementById('shiftCards').innerHTML = janStaff.map(s => {
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

function renderInventory() {
  document.getElementById('inventoryBody').innerHTML = inventoryItems.map((item,i) => {
    const low  = item.stock <= item.reorder;
    const pct  = Math.min(100, Math.round((item.stock / (item.reorder*3))*100));
    const col  = item.stock === 0 ? 'text-danger' : low ? 'text-warn' : '';
    const st   = item.stock === 0 ? '<span class="inv-badge inv-out">Out of Stock</span>'
             : low ? '<span class="inv-badge inv-low">Low Stock ⚠</span>'
             : '<span class="inv-badge inv-ok">OK</span>';
    if (low) {
      // Simulate notification
    }
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

function refillItem(i) {
  const qty = prompt(`Refill "${inventoryItems[i].name}" — enter quantity to add:`);
  if (qty && !isNaN(qty)) {
    inventoryItems[i].stock += parseInt(qty);
    inventoryItems[i].lastRefill = new Date().toISOString().split('T')[0];
    renderInventory();
    showToast(`${inventoryItems[i].name} refilled by ${qty} ${inventoryItems[i].unit}.`);
  }
}

function saveInventoryItem() {
  const name = document.getElementById('invName').value.trim();
  if (!name) { showToast('Item name required.', true); return; }
  inventoryItems.push({
    name, cat: document.getElementById('invCat').value,
    unit:     document.getElementById('invUnit').value,
    stock:    parseInt(document.getElementById('invStock').value)||0,
    reorder:  parseInt(document.getElementById('invReorder').value)||0,
    lastRefill: new Date().toISOString().split('T')[0],
  });
  closeModal('addInventoryModal');
  renderInventory();
  showToast('Item added to inventory.');
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

renderShiftCards();
renderInventory();
</script>
<?= $this->endSection() ?>