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
          <span class="leg-item"><span class="leg-dot jan-done"></span> Cleaned</span>
          <span class="leg-item"><span class="leg-dot jan-prog"></span> In Progress</span>
          <span class="leg-item"><span class="leg-dot jan-pend"></span> Pending</span>
        </div>

        <svg id="janSVG" viewBox="0 0 900 560" xmlns="http://www.w3.org/2000/svg">
          <rect width="900" height="560" fill="#f0eded" rx="12"/>
          <rect x="0" y="255" width="900" height="50" fill="#e0d8d8" opacity=".6"/>
          <rect x="420" y="0" width="60" height="560" fill="#e0d8d8" opacity=".6"/>

          <!-- Jan area: Admin -->
          <g class="campus-area jan-area" id="jan-admin" onclick="janDrillDown('admin')" tabindex="0">
            <rect x="30" y="30" width="170" height="110" rx="10" fill="#fff" stroke="#16a34a" stroke-width="2.5"/>
            <text x="115" y="58" text-anchor="middle" font-size="11" font-weight="700" fill="#16a34a" font-family="Poppins,sans-serif">ADMIN BUILDING</text>
            <rect x="50" y="75" width="130" height="10" rx="5" fill="#e5e7eb"/>
            <rect x="50" y="75" width="104" height="10" rx="5" fill="#16a34a"/>
            <text x="115" y="100" text-anchor="middle" font-size="9" fill="#555" font-family="Poppins,sans-serif">80% Complete</text>
            <circle cx="115" cy="120" r="6" fill="#16a34a"/>
          </g>

          <!-- Jan area: Library -->
          <g class="campus-area jan-area" id="jan-library" onclick="janDrillDown('library')" tabindex="0">
            <rect x="220" y="30" width="170" height="110" rx="10" fill="#fff" stroke="#c8963e" stroke-width="2.5"/>
            <text x="305" y="58" text-anchor="middle" font-size="11" font-weight="700" fill="#c8963e" font-family="Poppins,sans-serif">LIBRARY</text>
            <rect x="240" y="75" width="130" height="10" rx="5" fill="#e5e7eb"/>
            <rect x="240" y="75" width="65" height="10" rx="5" fill="#c8963e"/>
            <text x="305" y="100" text-anchor="middle" font-size="9" fill="#555" font-family="Poppins,sans-serif">50% Complete</text>
            <circle cx="305" cy="120" r="6" fill="#c8963e"/>
          </g>

          <!-- Jan area: Science -->
          <g class="campus-area jan-area" id="jan-science" onclick="janDrillDown('science')" tabindex="0">
            <rect x="500" y="30" width="170" height="110" rx="10" fill="#fff" stroke="#e5e7eb" stroke-width="2.5"/>
            <text x="585" y="55" text-anchor="middle" font-size="10" font-weight="700" fill="#6b7280" font-family="Poppins,sans-serif">SCIENCE BLDG</text>
            <rect x="520" y="75" width="130" height="10" rx="5" fill="#e5e7eb"/>
            <rect x="520" y="75" width="13" height="10" rx="5" fill="#6b7280"/>
            <text x="585" y="100" text-anchor="middle" font-size="9" fill="#555" font-family="Poppins,sans-serif">10% Complete</text>
            <circle cx="585" cy="120" r="6" fill="#9ca3af"/>
          </g>

          <!-- Jan area: Gymnasium -->
          <g class="campus-area jan-area" id="jan-gym" onclick="janDrillDown('gym')" tabindex="0">
            <rect x="690" y="30" width="180" height="110" rx="10" fill="#fff" stroke="#16a34a" stroke-width="2.5"/>
            <text x="780" y="58" text-anchor="middle" font-size="11" font-weight="700" fill="#16a34a" font-family="Poppins,sans-serif">GYMNASIUM</text>
            <rect x="710" y="75" width="140" height="10" rx="5" fill="#e5e7eb"/>
            <rect x="710" y="75" width="140" height="10" rx="5" fill="#16a34a"/>
            <text x="780" y="100" text-anchor="middle" font-size="9" fill="#555" font-family="Poppins,sans-serif">100% Complete</text>
            <circle cx="780" cy="120" r="6" fill="#16a34a"/>
          </g>

          <!-- Jan area: Canteen -->
          <g class="campus-area jan-area" id="jan-canteen" onclick="janDrillDown('canteen')" tabindex="0">
            <rect x="30" y="320" width="170" height="110" rx="10" fill="#fff" stroke="#c8963e" stroke-width="2.5"/>
            <text x="115" y="348" text-anchor="middle" font-size="11" font-weight="700" fill="#c8963e" font-family="Poppins,sans-serif">CANTEEN</text>
            <rect x="50" y="365" width="130" height="10" rx="5" fill="#e5e7eb"/>
            <rect x="50" y="365" width="78" height="10" rx="5" fill="#c8963e"/>
            <text x="115" y="390" text-anchor="middle" font-size="9" fill="#555" font-family="Poppins,sans-serif">60% Complete</text>
            <circle cx="115" cy="410" r="6" fill="#c8963e"/>
          </g>

          <!-- Jan area: Engineering -->
          <g class="campus-area jan-area" id="jan-engr" onclick="janDrillDown('engr')" tabindex="0">
            <rect x="220" y="320" width="170" height="110" rx="10" fill="#fff" stroke="#e5e7eb" stroke-width="2.5"/>
            <text x="305" y="345" text-anchor="middle" font-size="10" font-weight="700" fill="#6b7280" font-family="Poppins,sans-serif">ENGINEERING</text>
            <rect x="240" y="362" width="130" height="10" rx="5" fill="#e5e7eb"/>
            <rect x="240" y="362" width="26" height="10" rx="5" fill="#9ca3af"/>
            <text x="305" y="387" text-anchor="middle" font-size="9" fill="#555" font-family="Poppins,sans-serif">20% Complete</text>
            <circle cx="305" cy="407" r="6" fill="#9ca3af"/>
          </g>

          <!-- Jan area: CCS -->
          <g class="campus-area jan-area" id="jan-ccs" onclick="janDrillDown('ccs')" tabindex="0">
            <rect x="500" y="320" width="170" height="110" rx="10" fill="#fff" stroke="#16a34a" stroke-width="2.5"/>
            <text x="585" y="348" text-anchor="middle" font-size="11" font-weight="700" fill="#16a34a" font-family="Poppins,sans-serif">CCS BUILDING</text>
            <rect x="520" y="365" width="130" height="10" rx="5" fill="#e5e7eb"/>
            <rect x="520" y="365" width="117" height="10" rx="5" fill="#16a34a"/>
            <text x="585" y="390" text-anchor="middle" font-size="9" fill="#555" font-family="Poppins,sans-serif">90% Complete</text>
            <circle cx="585" cy="407" r="6" fill="#16a34a"/>
          </g>

          <!-- Jan area: Clinic -->
          <g class="campus-area jan-area" id="jan-clinic" onclick="janDrillDown('clinic')" tabindex="0">
            <rect x="690" y="320" width="180" height="110" rx="10" fill="#fff" stroke="#16a34a" stroke-width="2.5"/>
            <text x="780" y="348" text-anchor="middle" font-size="11" font-weight="700" fill="#16a34a" font-family="Poppins,sans-serif">CLINIC</text>
            <rect x="710" y="365" width="140" height="10" rx="5" fill="#e5e7eb"/>
            <rect x="710" y="365" width="140" height="10" rx="5" fill="#16a34a"/>
            <text x="780" y="390" text-anchor="middle" font-size="9" fill="#555" font-family="Poppins,sans-serif">100% Complete</text>
            <circle cx="780" cy="407" r="6" fill="#16a34a"/>
          </g>

          <circle cx="450" cy="280" r="40" fill="#7a1f2b" opacity=".08"/>
          <text x="450" y="276" text-anchor="middle" font-size="13" font-weight="700" fill="#7a1f2b" font-family="Poppins,sans-serif">FU CAMPUS</text>
          <text x="450" y="292" text-anchor="middle" font-size="9" fill="#a08080" font-family="Poppins,sans-serif">Janitorial View</text>
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
  { name:'Bautista, M.',  zone:'Admin Building Flr 1', tasks:8, done:6, photo:'B', shift:'7AM-3PM' },
  { name:'Dizon, L.',     zone:'Library',              tasks:6, done:3, photo:'D', shift:'7AM-3PM' },
  { name:'Fernandez, G.', zone:'Science Building',     tasks:5, done:1, photo:'F', shift:'6AM-2PM' },
  { name:'Hernandez, K.', zone:'Gymnasium',            tasks:4, done:4, photo:'H', shift:'5AM-1PM' },
  { name:'Ignacio, P.',   zone:'Canteen',              tasks:7, done:4, photo:'I', shift:'7AM-3PM' },
  { name:'Javier, C.',    zone:'Engineering',          tasks:6, done:1, photo:'J', shift:'8AM-4PM' },
  { name:'Lacson, A.',    zone:'CCS Building',         tasks:5, done:5, photo:'L', shift:'7AM-3PM' },
  { name:'Mendez, R.',    zone:'Clinic',               tasks:4, done:4, photo:'M', shift:'7AM-3PM' },
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
  const el = document.getElementById('jan-'+area);
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
      <button class="tbl-btn" onclick="switchJanTab('janmap');janDrillDown('${janStaff.indexOf(s)<2?['admin','library'][janStaff.indexOf(s)]:'ccs'}')">View Checklist</button>
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