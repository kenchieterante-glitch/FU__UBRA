<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<link rel="stylesheet" href="<?= base_url('Assets/css/safety.css') ?>">

<div class="sj-wrapper">
  <!-- ── SUB-TABS FOR SAFETY ─────────────────────────────────────────── -->
  <div class="sub-tabs">
    <button class="sub-tab active" onclick="switchSubTab('map')">
      <i class="bi bi-map-fill"></i> Campus Map
    </button>
    <button class="sub-tab" onclick="switchSubTab('registry')">
      <i class="bi bi-fire"></i> Equipment Registry
    </button>
    <button class="sub-tab" onclick="switchSubTab('workorders')">
      <i class="bi bi-wrench-adjustable"></i> Work Orders
    </button>
    <button class="sub-tab" onclick="switchSubTab('keylogs')">
      <i class="bi bi-key-fill"></i> Key & ID Logs
    </button>
    <button class="sub-tab guard-dash-btn" onclick="switchSubTab('guard')">
      <i class="bi bi-person-badge-fill"></i> Guard Dashboard
    </button>
    <div class="sub-tab-spacer"></div>
    <button class="btn-report" onclick="openReportModal()">
      <i class="bi bi-file-earmark-bar-graph-fill"></i> View Report
    </button>
  </div>

  <!-- ── SUB-TAB: CAMPUS MAP ─────────────────────────────────────────── -->
  <div id="subtab-map" class="sub-pane active">
    <div class="map-layout" id="mapLayout">
      <div class="map-container" id="mapContainer">
        <div class="map-legend">
          <span class="leg-item"><span class="leg-dot new"></span> New (OK)</span>
          <span class="leg-item"><span class="leg-dot refill"></span> Refillable</span>
          <span class="leg-dot defect"></span> Defective</span>
          <span class="leg-item"><i class="bi bi-exclamation-triangle-fill" style="color:#dc2626;font-size:.7rem"></i> Missing</span>
        </div>

        <svg id="campusSVG" viewBox="0 0 900 560" xmlns="http://www.w3.org/2000/svg">
          <rect width="900" height="560" fill="#f0eded" rx="12"/>
          <rect x="0" y="255" width="900" height="50" fill="#e0d8d8" opacity=".6"/>
          <rect x="420" y="0" width="60" height="560" fill="#e0d8d8" opacity=".6"/>
          <text x="100" y="285" font-size="9" fill="#a08080" text-anchor="middle" font-family="Poppins,sans-serif">MAIN AVENUE</text>
          <text x="450" y="120" font-size="9" fill="#a08080" text-anchor="middle" font-family="Poppins,sans-serif" transform="rotate(-90,450,120)">CENTRAL ROAD</text>

          <!-- Admin Building -->
          <g class="campus-area" id="area-admin" onclick="drillDown('admin')" tabindex="0">
            <rect x="30" y="30" width="170" height="110" rx="10" fill="#fff" stroke="#7a1f2b" stroke-width="2"/>
            <text x="115" y="58" text-anchor="middle" font-size="11" font-weight="700" fill="#7a1f2b" font-family="Poppins,sans-serif">ADMIN BUILDING</text>
            <text x="115" y="72" text-anchor="middle" font-size="9" fill="#888" font-family="Poppins,sans-serif">Click to inspect</text>
            <circle cx="60" cy="100" r="10" fill="#16a34a" stroke="#fff" stroke-width="1.5" class="fe-pin"/>
            <text x="60" cy="100" dy="4" text-anchor="middle" font-size="8" fill="#fff" font-family="Poppins">FE</text>
            <circle cx="90" cy="100" r="10" fill="#c8963e" stroke="#fff" stroke-width="1.5" class="fe-pin"/>
            <text x="90" cy="100" dy="4" text-anchor="middle" font-size="8" fill="#fff" font-family="Poppins">FE</text>
            <circle cx="120" cy="100" r="10" fill="#6b7280" stroke="#fff" stroke-width="1.5" class="fe-pin"/>
            <text x="120" cy="100" dy="4" text-anchor="middle" font-size="8" fill="#fff" font-family="Poppins">FE</text>
            <text x="155" cy="100" dy="4" text-anchor="middle" font-size="9" fill="#dc2626" font-family="Poppins">⚠ Miss.</text>
            <text x="60" y="120" text-anchor="middle" font-size="7" fill="#555" font-family="Poppins">New</text>
            <text x="90" y="120" text-anchor="middle" font-size="7" fill="#555" font-family="Poppins">Refill</text>
            <text x="120" y="120" text-anchor="middle" font-size="7" fill="#555" font-family="Poppins">Defect</text>
          </g>

          <!-- Library -->
          <g class="campus-area" id="area-library" onclick="drillDown('library')" tabindex="0">
            <rect x="220" y="30" width="170" height="110" rx="10" fill="#fff" stroke="#7a1f2b" stroke-width="2"/>
            <text x="305" y="58" text-anchor="middle" font-size="11" font-weight="700" fill="#7a1f2b" font-family="Poppins,sans-serif">LIBRARY</text>
            <text x="305" y="72" text-anchor="middle" font-size="9" fill="#888" font-family="Poppins,sans-serif">Click to inspect</text>
            <circle cx="255" cy="100" r="10" fill="#16a34a" stroke="#fff" stroke-width="1.5" class="fe-pin"/>
            <text x="255" cy="100" dy="4" text-anchor="middle" font-size="8" fill="#fff">FE</text>
            <circle cx="285" cy="100" r="10" fill="#16a34a" stroke="#fff" stroke-width="1.5" class="fe-pin"/>
            <text x="285" cy="100" dy="4" text-anchor="middle" font-size="8" fill="#fff">FE</text>
            <circle cx="315" cy="100" r="10" fill="#c8963e" stroke="#fff" stroke-width="1.5" class="fe-pin"/>
            <text x="315" cy="100" dy="4" text-anchor="middle" font-size="8" fill="#fff">FE</text>
          </g>

          <!-- Science Building -->
          <g class="campus-area" id="area-science" onclick="drillDown('science')" tabindex="0">
            <rect x="500" y="30" width="170" height="110" rx="10" fill="#fff" stroke="#7a1f2b" stroke-width="2"/>
            <text x="585" y="55" text-anchor="middle" font-size="10" font-weight="700" fill="#7a1f2b" font-family="Poppins,sans-serif">SCIENCE BLDG</text>
            <text x="585" y="70" text-anchor="middle" font-size="9" fill="#888" font-family="Poppins,sans-serif">Click to inspect</text>
            <circle cx="535" cy="100" r="10" fill="#16a34a" stroke="#fff" stroke-width="1.5" class="fe-pin"/>
            <text x="535" cy="100" dy="4" text-anchor="middle" font-size="8" fill="#fff">FE</text>
            <circle cx="565" cy="100" r="10" fill="#6b7280" stroke="#fff" stroke-width="1.5" class="fe-pin"/>
            <text x="565" cy="100" dy="4" text-anchor="middle" font-size="8" fill="#fff">FE</text>
            <circle cx="595" cy="100" r="10" fill="#c8963e" stroke="#fff" stroke-width="1.5" class="fe-pin"/>
            <text x="595" cy="100" dy="4" text-anchor="middle" font-size="8" fill="#fff">FE</text>
          </g>

          <!-- Gymnasium -->
          <g class="campus-area" id="area-gym" onclick="drillDown('gym')" tabindex="0">
            <rect x="690" y="30" width="180" height="110" rx="10" fill="#fff" stroke="#7a1f2b" stroke-width="2"/>
            <text x="780" y="58" text-anchor="middle" font-size="11" font-weight="700" fill="#7a1f2b" font-family="Poppins,sans-serif">GYMNASIUM</text>
            <text x="780" y="72" text-anchor="middle" font-size="9" fill="#888" font-family="Poppins,sans-serif">Click to inspect</text>
            <circle cx="730" cy="100" r="10" fill="#16a34a" stroke="#fff" stroke-width="1.5" class="fe-pin"/>
            <text x="730" cy="100" dy="4" text-anchor="middle" font-size="8" fill="#fff">FE</text>
            <circle cx="760" cy="100" r="10" fill="#16a34a" stroke="#fff" stroke-width="1.5" class="fe-pin"/>
            <text x="760" cy="100" dy="4" text-anchor="middle" font-size="8" fill="#fff">FE</text>
            <text x="790" cy="100" dy="4" text-anchor="middle" font-size="9" fill="#dc2626">⚠ Miss.</text>
          </g>

          <!-- Canteen -->
          <g class="campus-area" id="area-canteen" onclick="drillDown('canteen')" tabindex="0">
            <rect x="30" y="320" width="170" height="110" rx="10" fill="#fff" stroke="#7a1f2b" stroke-width="2"/>
            <text x="115" y="348" text-anchor="middle" font-size="11" font-weight="700" fill="#7a1f2b" font-family="Poppins,sans-serif">CANTEEN</text>
            <text x="115" y="362" text-anchor="middle" font-size="9" fill="#888" font-family="Poppins,sans-serif">Click to inspect</text>
            <circle cx="70" cy="390" r="10" fill="#c8963e" stroke="#fff" stroke-width="1.5" class="fe-pin"/>
            <text x="70" cy="390" dy="4" text-anchor="middle" font-size="8" fill="#fff">FE</text>
            <circle cx="100" cy="390" r="10" fill="#c8963e" stroke="#fff" stroke-width="1.5" class="fe-pin"/>
            <text x="100" cy="390" dy="4" text-anchor="middle" font-size="8" fill="#fff">FE</text>
          </g>

          <!-- Engineering Building -->
          <g class="campus-area" id="area-engr" onclick="drillDown('engr')" tabindex="0">
            <rect x="220" y="320" width="170" height="110" rx="10" fill="#fff" stroke="#7a1f2b" stroke-width="2"/>
            <text x="305" y="345" text-anchor="middle" font-size="10" font-weight="700" fill="#7a1f2b" font-family="Poppins,sans-serif">ENGINEERING</text>
            <text x="305" y="362" text-anchor="middle" font-size="9" fill="#888" font-family="Poppins,sans-serif">Click to inspect</text>
            <circle cx="255" cy="390" r="10" fill="#16a34a" stroke="#fff" stroke-width="1.5" class="fe-pin"/>
            <text x="255" cy="390" dy="4" text-anchor="middle" font-size="8" fill="#fff">FE</text>
            <circle cx="285" cy="390" r="10" fill="#6b7280" stroke="#fff" stroke-width="1.5" class="fe-pin"/>
            <text x="285" cy="390" dy="4" text-anchor="middle" font-size="8" fill="#fff">FE</text>
            <circle cx="315" cy="390" r="10" fill="#c8963e" stroke="#fff" stroke-width="1.5" class="fe-pin"/>
            <text x="315" cy="390" dy="4" text-anchor="middle" font-size="8" fill="#fff">FE</text>
          </g>

          <!-- CCS Building -->
          <g class="campus-area" id="area-ccs" onclick="drillDown('ccs')" tabindex="0">
            <rect x="500" y="320" width="170" height="110" rx="10" fill="#fff" stroke="#7a1f2b" stroke-width="2"/>
            <text x="585" y="348" text-anchor="middle" font-size="11" font-weight="700" fill="#7a1f2b" font-family="Poppins,sans-serif">CCS BUILDING</text>
            <text x="585" y="362" text-anchor="middle" font-size="9" fill="#888" font-family="Poppins,sans-serif">Click to inspect</text>
            <circle cx="535" cy="390" r="10" fill="#16a34a" stroke="#fff" stroke-width="1.5" class="fe-pin"/>
            <text x="535" cy="390" dy="4" text-anchor="middle" font-size="8" fill="#fff">FE</text>
            <circle cx="565" cy="390" r="10" fill="#16a34a" stroke="#fff" stroke-width="1.5" class="fe-pin"/>
            <text x="565" cy="390" dy="4" text-anchor="middle" font-size="8" fill="#fff">FE</text>
          </g>

          <!-- Clinic -->
          <g class="campus-area" id="area-clinic" onclick="drillDown('clinic')" tabindex="0">
            <rect x="690" y="320" width="180" height="110" rx="10" fill="#fff" stroke="#7a1f2b" stroke-width="2"/>
            <text x="780" y="348" text-anchor="middle" font-size="11" font-weight="700" fill="#7a1f2b" font-family="Poppins,sans-serif">CLINIC</text>
            <text x="780" y="362" text-anchor="middle" font-size="9" fill="#888" font-family="Poppins,sans-serif">Click to inspect</text>
            <circle cx="730" cy="390" r="10" fill="#16a34a" stroke="#fff" stroke-width="1.5" class="fe-pin"/>
            <text x="730" cy="390" dy="4" text-anchor="middle" font-size="8" fill="#fff">FE</text>
            <circle cx="760" cy="390" r="10" fill="#c8963e" stroke="#fff" stroke-width="1.5" class="fe-pin"/>
            <text x="760" cy="390" dy="4" text-anchor="middle" font-size="8" fill="#fff">FE</text>
          </g>

          <!-- Guard House -->
          <g class="campus-area" id="area-guard-post" onclick="drillDown('guard-post')" tabindex="0">
            <rect x="380" y="460" width="140" height="80" rx="10" fill="#1f1f1f" stroke="#c8963e" stroke-width="2"/>
            <text x="450" y="492" text-anchor="middle" font-size="10" font-weight="700" fill="#c8963e" font-family="Poppins,sans-serif">GUARD HOUSE</text>
            <text x="450" y="508" text-anchor="middle" font-size="8" fill="#888" font-family="Poppins,sans-serif">Security Post</text>
            <circle cx="430" cy="525" r="8" fill="#16a34a" stroke="#fff" stroke-width="1.5" class="fe-pin"/>
            <text x="430" cy="525" dy="3" text-anchor="middle" font-size="7" fill="#fff">FE</text>
          </g>

          <!-- Logo Center -->
          <circle cx="450" cy="280" r="40" fill="#7a1f2b" opacity=".12"/>
          <text x="450" y="276" text-anchor="middle" font-size="13" font-weight="700" fill="#7a1f2b" font-family="Poppins,sans-serif">FU CAMPUS</text>
          <text x="450" y="292" text-anchor="middle" font-size="9" fill="#a08080" font-family="Poppins,sans-serif">Foundation University</text>
        </svg>

        <div class="missing-alert" id="missingAlert" style="display:none">
          <i class="bi bi-exclamation-octagon-fill"></i>
          <span id="missingAlertMsg"></span>
          <button onclick="document.getElementById('missingAlert').style.display='none'"><i class="bi bi-x"></i></button>
        </div>
      </div>

      <div class="drill-panel" id="drillPanel" style="display:none">
        <div class="dp-header">
          <div>
            <div class="dp-title" id="dpTitle">Admin Building</div>
            <div class="dp-sub" id="dpSub">Fire Extinguisher Status</div>
          </div>
          <button class="dp-close" onclick="closeDrill()"><i class="bi bi-x-lg"></i> Back to Map</button>
        </div>
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

  <!-- ── SUB-TAB: EQUIPMENT REGISTRY ─────────────────────────────────── -->
  <div id="subtab-registry" class="sub-pane">
    <div class="pane-header">
      <h2 class="pane-title"><i class="bi bi-fire"></i> Fire Safety Equipment Registry</h2>
      <div class="pane-actions">
        <input type="text" class="pane-search" placeholder="Search unit ID, location..." id="registrySearch" oninput="filterRegistry()">
        <select class="pane-filter" id="registryStatus" onchange="filterRegistry()">
          <option value="">All Status</option>
          <option value="New">New (Green)</option>
          <option value="Refillable">Refillable (Orange)</option>
          <option value="Defective">Defective (Gray)</option>
          <option value="Missing">Missing</option>
        </select>
        <button class="btn-add-record" onclick="openAddFEModal()">
          <i class="bi bi-plus-lg"></i> Add Unit
        </button>
      </div>
    </div>
    <div class="table-wrap">
      <table class="sj-table" id="registryTable">
        <thead>
          <tr>
            <th>Unit ID</th><th>Type</th><th>Location / Building</th>
            <th>Weight (kg)</th><th>Last Inspection</th><th>Next Due</th>
            <th>Status</th><th>Age</th><th>Action</th>
          </tr>
        </thead>
        <tbody id="registryBody"></tbody>
      </table>
    </div>
  </div>

  <!-- ── SUB-TAB: WORK ORDERS ───────────────────────────────────────── -->
  <div id="subtab-workorders" class="sub-pane">
    <div class="pane-header">
      <h2 class="pane-title"><i class="bi bi-wrench-adjustable"></i> Maintenance Work Orders</h2>
      <button class="btn-add-record" onclick="openAddWOModal()"><i class="bi bi-plus-lg"></i> New Work Order</button>
    </div>
    <div class="table-wrap">
      <table class="sj-table">
        <thead>
          <tr><th>WO #</th><th>Issue</th><th>Location</th><th>Reported By</th><th>Date Logged</th><th>Assigned To</th><th>Stage</th><th>Action</th></tr>
        </thead>
        <tbody id="woBody"></tbody>
      </table>
    </div>
  </div>

  <!-- ── SUB-TAB: KEY & ID LOGS ─────────────────────────────────────── -->
  <div id="subtab-keylogs" class="sub-pane">
    <div class="pane-header">
      <h2 class="pane-title"><i class="bi bi-key-fill"></i> Key Borrowing Log (ID Scan)</h2>
      <div class="pane-actions">
        <button class="btn-scan" onclick="openScanModal('in')">
          <i class="bi bi-upc-scan"></i> Scan Borrow
        </button>
        <button class="btn-scan btn-scan-out" onclick="openScanModal('out')">
          <i class="bi bi-box-arrow-right"></i> Scan Return
        </button>
      </div>
    </div>
    <div class="table-wrap">
      <table class="sj-table">
        <thead>
          <tr><th>Log #</th><th>Name</th><th>Department</th><th>Key Borrowed</th><th>Scan In</th><th>Scan Out</th><th>Status</th><th>Guard</th></tr>
        </thead>
        <tbody id="keylogBody"></tbody>
      </table>
    </div>
  </div>

  <!-- ── SUB-TAB: GUARD DASHBOARD ───────────────────────────────────── -->
  <div id="subtab-guard" class="sub-pane">
    <div class="pane-header">
      <h2 class="pane-title"><i class="bi bi-person-badge-fill"></i> Security Guard Dashboard</h2>
      <div class="pane-actions">
        <span class="guard-shift">On Duty: <strong>Guard Santos, J.</strong></span>
        <span class="guard-time" id="guardClock"></span>
      </div>
    </div>
    <div class="guard-grid">
      <div class="guard-card">
        <div class="gc-title"><i class="bi bi-ticket-perforated-fill"></i> Trip Tickets Released Today</div>
        <div class="table-wrap">
          <table class="sj-table">
            <thead>
              <tr><th>Ticket #</th><th>Requester</th><th>Vehicle</th><th>Destination</th><th>Departure</th><th>Approved By</th><th>Status</th></tr>
            </thead>
            <tbody id="tripTicketBody"></tbody>
          </table>
        </div>
      </div>
      <div class="guard-card">
        <div class="gc-title"><i class="bi bi-key-fill"></i> Currently Borrowed Keys</div>
        <div id="activeBorrows" class="borrow-cards"></div>
      </div>
      <div class="guard-card guard-card-narrow">
        <div class="gc-title"><i class="bi bi-activity"></i> Guard Activity Log</div>
        <ul class="act-log" id="guardActLog"></ul>
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

<div id="addFEModal" class="sj-modal-overlay" style="display:none">
  <div class="sj-modal">
    <div class="sj-modal-header">
      <h3><i class="bi bi-fire"></i> Add Fire Extinguisher Unit</h3>
      <button onclick="closeModal('addFEModal')"><i class="bi bi-x-lg"></i></button>
    </div>
    <div class="sj-modal-body">
      <div class="form-grid2">
        <div class="fg"><label>Unit ID</label><input type="text" id="feUnitId" placeholder="e.g. FE-ADM-04"></div>
        <div class="fg"><label>Type</label>
          <select id="feType"><option>CO2</option><option>Dry Chemical</option><option>Wet Chemical</option><option>Foam</option></select>
        </div>
        <div class="fg"><label>Building / Location</label>
          <select id="feLocation">
            <option>Admin Building</option><option>Library</option><option>Science Building</option>
            <option>Gymnasium</option><option>Canteen</option><option>Engineering</option>
            <option>CCS Building</option><option>Clinic</option><option>Guard House</option>
          </select>
        </div>
        <div class="fg"><label>Weight (kg)</label><input type="number" id="feWeight" placeholder="e.g. 10" step="0.1"></div>
        <div class="fg"><label>Last Inspection</label><input type="date" id="feLastInsp"></div>
        <div class="fg"><label>Next Due</label><input type="date" id="feNextDue"></div>
        <div class="fg"><label>Status</label>
          <select id="feStatus">
            <option value="New">New (Green)</option>
            <option value="Refillable">Refillable (Orange)</option>
            <option value="Defective">Defective (Gray)</option>
          </select>
        </div>
        <div class="fg"><label>Year Acquired</label><input type="number" id="feYear" placeholder="e.g. 2022"></div>
      </div>
    </div>
    <div class="sj-modal-footer">
      <button class="btn-cancel" onclick="closeModal('addFEModal')">Cancel</button>
      <button class="btn-maroon-sm" onclick="saveNewFE()"><i class="bi bi-floppy-fill"></i> Save Unit</button>
    </div>
  </div>
</div>

<div id="addWOModal" class="sj-modal-overlay" style="display:none">
  <div class="sj-modal">
    <div class="sj-modal-header">
      <h3><i class="bi bi-wrench-adjustable"></i> New Work Order</h3>
      <button onclick="closeModal('addWOModal')"><i class="bi bi-x-lg"></i></button>
    </div>
    <div class="sj-modal-body">
      <div class="form-grid2">
        <div class="fg fg-full"><label>Issue Description</label><textarea id="woIssue" rows="2" placeholder="Describe the issue..."></textarea></div>
        <div class="fg"><label>Location</label>
          <select id="woLocation"><option>Admin Building</option><option>Library</option><option>Science Building</option><option>Gymnasium</option><option>Canteen</option><option>Engineering</option><option>CCS Building</option><option>Clinic</option></select>
        </div>
        <div class="fg"><label>Reported By</label><input type="text" id="woReporter" placeholder="Full name"></div>
        <div class="fg"><label>Assign To</label><input type="text" id="woAssigned" placeholder="Technician name"></div>
        <div class="fg"><label>Priority</label>
          <select id="woPriority"><option>Low</option><option>Medium</option><option>High</option><option>Critical</option></select>
        </div>
      </div>
    </div>
    <div class="sj-modal-footer">
      <button class="btn-cancel" onclick="closeModal('addWOModal')">Cancel</button>
      <button class="btn-maroon-sm" onclick="saveNewWO()"><i class="bi bi-floppy-fill"></i> Submit Work Order</button>
    </div>
  </div>
</div>

<div id="scanModal" class="sj-modal-overlay" style="display:none">
  <div class="sj-modal sj-modal-sm">
    <div class="sj-modal-header">
      <h3 id="scanModalTitle"><i class="bi bi-upc-scan"></i> Scan ID — Borrow Key</h3>
      <button onclick="closeModal('scanModal')"><i class="bi bi-x-lg"></i></button>
    </div>
    <div class="sj-modal-body" style="text-align:center">
      <div class="scan-zone" id="scanZone">
        <i class="bi bi-upc-scan scan-icon"></i>
        <p>Place ID card near scanner or enter ID manually</p>
        <input type="text" id="scanIdInput" placeholder="Employee / Student ID" class="scan-input" autofocus>
        <div class="fg" style="margin-top:.8rem"><label>Key / Item Borrowed</label>
          <input type="text" id="scanKeyItem" placeholder="e.g. Room 301 Key, Master Key...">
        </div>
        <div class="fg"><label>Department</label>
          <input type="text" id="scanDept" placeholder="e.g. CCS, Library...">
        </div>
        <button class="btn-maroon-sm" style="margin-top:1rem;width:100%" onclick="processScan()">
          <i class="bi bi-check-circle-fill"></i> Confirm Scan
        </button>
      </div>
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

let feRegistry = [
  { id:'FE-ADM-01', type:'CO2', loc:'Admin Building', kg:10, lastInsp:'2025-01-10', nextDue:'2025-07-10', status:'New', year:2024, inspector:'Cruz, M.', assigned:'Guard Santos' },
  { id:'FE-ADM-02', type:'Dry Chemical', loc:'Admin Building', kg:6, lastInsp:'2025-01-10', nextDue:'2025-04-10', status:'Refillable', year:2021, inspector:'Cruz, M.', assigned:'Guard Santos' },
  { id:'FE-ADM-03', type:'CO2', loc:'Admin Building', kg:10, lastInsp:'2024-11-05', nextDue:'2025-02-05', status:'Defective', year:2018, inspector:'Cruz, M.', assigned:'Guard Santos' },
  { id:'FE-LIB-01', type:'Dry Chemical', loc:'Library', kg:10, lastInsp:'2025-02-01', nextDue:'2025-08-01', status:'New', year:2023, inspector:'Reyes, A.', assigned:'Guard Dela Cruz' },
  { id:'FE-LIB-02', type:'CO2', loc:'Library', kg:6, lastInsp:'2025-02-01', nextDue:'2025-08-01', status:'New', year:2024, inspector:'Reyes, A.', assigned:'Guard Dela Cruz' },
  { id:'FE-LIB-03', type:'Foam', loc:'Library', kg:9, lastInsp:'2025-01-15', nextDue:'2025-04-15', status:'Refillable', year:2020, inspector:'Reyes, A.', assigned:'Guard Dela Cruz' },
  { id:'FE-SCI-01', type:'CO2', loc:'Science Building', kg:10, lastInsp:'2025-01-20', nextDue:'2025-07-20', status:'New', year:2023, inspector:'Lim, B.', assigned:'Guard Santos' },
  { id:'FE-SCI-02', type:'Dry Chemical', loc:'Science Building', kg:6, lastInsp:'2024-10-01', nextDue:'2025-01-01', status:'Defective', year:2017, inspector:'Lim, B.', assigned:'Guard Santos' },
  { id:'FE-SCI-03', type:'CO2', loc:'Science Building', kg:10, lastInsp:'2025-03-01', nextDue:'2025-09-01', status:'Refillable', year:2022, inspector:'Lim, B.', assigned:'Guard Santos' },
  { id:'FE-GYM-01', type:'Dry Chemical', loc:'Gymnasium', kg:9, lastInsp:'2025-02-10', nextDue:'2025-08-10', status:'New', year:2024, inspector:'Santos, R.', assigned:'Guard Dela Cruz' },
  { id:'FE-GYM-02', type:'CO2', loc:'Gymnasium', kg:10, lastInsp:'2025-02-10', nextDue:'2025-08-10', status:'New', year:2024, inspector:'Santos, R.', assigned:'Guard Dela Cruz' },
  { id:'FE-CAN-01', type:'Wet Chemical', loc:'Canteen', kg:6, lastInsp:'2025-01-25', nextDue:'2025-04-25', status:'Refillable', year:2021, inspector:'Gomez, T.', assigned:'Guard Santos' },
  { id:'FE-CAN-02', type:'Wet Chemical', loc:'Canteen', kg:6, lastInsp:'2025-01-25', nextDue:'2025-04-25', status:'Refillable', year:2021, inspector:'Gomez, T.', assigned:'Guard Santos' },
  { id:'FE-ENG-01', type:'CO2', loc:'Engineering', kg:10, lastInsp:'2025-03-01', nextDue:'2025-09-01', status:'New', year:2023, inspector:'Flores, C.', assigned:'Guard Santos' },
  { id:'FE-ENG-02', type:'Dry Chemical', loc:'Engineering', kg:6, lastInsp:'2024-09-01', nextDue:'2024-12-01', status:'Defective', year:2016, inspector:'Flores, C.', assigned:'Guard Santos' },
  { id:'FE-ENG-03', type:'CO2', loc:'Engineering', kg:9, lastInsp:'2025-02-15', nextDue:'2025-05-15', status:'Refillable', year:2022, inspector:'Flores, C.', assigned:'Guard Santos' },
  { id:'FE-CCS-01', type:'CO2', loc:'CCS Building', kg:10, lastInsp:'2025-03-10', nextDue:'2025-09-10', status:'New', year:2024, inspector:'Aquino, D.', assigned:'Guard Dela Cruz' },
  { id:'FE-CCS-02', type:'Dry Chemical', loc:'CCS Building', kg:10, lastInsp:'2025-03-10', nextDue:'2025-09-10', status:'New', year:2024, inspector:'Aquino, D.', assigned:'Guard Dela Cruz' },
  { id:'FE-CLI-01', type:'CO2', loc:'Clinic', kg:6, lastInsp:'2025-01-05', nextDue:'2025-07-05', status:'New', year:2023, inspector:'Torres, L.', assigned:'Guard Santos' },
  { id:'FE-CLI-02', type:'Wet Chemical', loc:'Clinic', kg:6, lastInsp:'2025-01-05', nextDue:'2025-04-05', status:'Refillable', year:2020, inspector:'Torres, L.', assigned:'Guard Santos' },
  { id:'FE-GRD-01', type:'Dry Chemical', loc:'Guard House', kg:6, lastInsp:'2025-02-20', nextDue:'2025-08-20', status:'New', year:2023, inspector:'Mendoza, P.', assigned:'Guard Santos' },
];

let workOrders = [
  { id:'WO-001', issue:'FE-ADM-03 defective — pressure gauge broken', loc:'Admin Building', by:'Cruz, M.', date:'2025-07-10', assigned:'Tech Valdez', stage:'In Progress' },
  { id:'WO-002', issue:'FE-ENG-02 past expiry — needs replacement', loc:'Engineering', by:'Flores, C.', date:'2025-07-12', assigned:'Tech Ramos', stage:'Issue Logged' },
  { id:'WO-003', issue:'Missing FE slot — Admin lobby', loc:'Admin Building', by:'Guard Santos', date:'2025-07-14', assigned:'Purchasing', stage:'Pending Parts' },
  { id:'WO-004', issue:'FE-SCI-02 not returning pressure', loc:'Science Bldg', by:'Lim, B.', date:'2025-07-15', assigned:'Tech Valdez', stage:'Completed/Verified' },
];

let keyLogs = [
  { id:'KL-001', name:'Dela Cruz, J.', dept:'Library', key:'Library Storeroom Key', inTime:'07:30 AM', outTime:'12:00 PM', status:'Returned', guard:'Santos, J.' },
  { id:'KL-002', name:'Magsaysay, R.', dept:'CCS', key:'Server Room Key', inTime:'08:15 AM', outTime:'—', status:'Active', guard:'Santos, J.' },
  { id:'KL-003', name:'Torres, F.', dept:'Science', key:'Lab Cabinet Keys', inTime:'09:00 AM', outTime:'10:30 AM', status:'Returned', guard:'Dela Cruz, P.' },
  { id:'KL-004', name:'Reyes, A.', dept:'Admin', key:'Admin Filing Room Key', inTime:'10:45 AM', outTime:'—', status:'Active', guard:'Santos, J.' },
];

let currentScanMode = 'in';

const tripTickets = [
  { no:'TT-2026-090', requester:'Dr. Jose Rizal', vehicle:'Van FUA-8802', dest:'Dumaguete IT Hub', dep:'08:30 AM', approvedBy:'Dir. Santos', status:'Approved' },
  { no:'TT-2026-091', requester:'Prof. Terante, K.', vehicle:'Bus FUA-8801', dest:'Silliman University', dep:'10:00 AM', approvedBy:'VP Gomez', status:'Released' },
  { no:'TT-2026-092', requester:'Dean Samson', vehicle:'Hilux FUA-4311', dest:'Valencia Campus', dep:'01:00 PM', approvedBy:'Dir. Santos', status:'Pending' },
];

const guardActLog = [
  { time:'10:45 AM', action:'Key KL-004 issued to Reyes, A. (Admin)' },
  { time:'09:00 AM', action:'Key KL-003 issued to Torres, F. (Science)' },
  { time:'10:30 AM', action:'Key KL-003 returned by Torres, F.' },
  { time:'08:15 AM', action:'Key KL-002 issued to Magsaysay, R. (CCS)' },
  { time:'07:30 AM', action:'Key KL-001 issued to Dela Cruz, J. (Library)' },
  { time:'12:00 PM', action:'Key KL-001 returned by Dela Cruz, J.' },
  { time:'07:00 AM', action:'Guard Santos, J. clocked in — Day Shift' },
];

function switchSubTab(id) {
  document.querySelectorAll('#module-safety .sub-tab, .sub-tab').forEach(t => t.classList.toggle('active', t.getAttribute('onclick')?.includes("'"+id+"'")));
  document.querySelectorAll('.sub-pane').forEach(p => p.classList.toggle('active', p.id === 'subtab-'+id));
  if (id==='registry') renderRegistry();
  if (id==='workorders') renderWorkOrders();
  if (id==='keylogs') renderKeyLogs();
  if (id==='guard') renderGuardDash();
}

function drillDown(areaKey) {
  const area = AREAS[areaKey];
  if (!area) return;

  document.querySelectorAll('.campus-area').forEach(g => g.classList.remove('area-selected'));
  const el = document.getElementById('area-'+areaKey);
  if (el) el.classList.add('area-selected');

  document.getElementById('mapLayout').classList.add('drilled');

  document.getElementById('dpTitle').textContent = area.name;
  document.getElementById('dpSub').textContent   = area.missing ? '⚠ Missing Fire Extinguisher Detected' : 'Fire Extinguisher Status';

  const units = feRegistry.filter(u => area.units.includes(u.id));
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
      <div class="fec-row"><span>Weight</span><strong>${u.kg} kg</strong></div>
      <div class="fec-row"><span>Age</span><strong>${age} yr${age!==1?'s':''}</strong></div>
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
  if (area.missing) alertHtml += `<div class="dp-alert-item urgent"><i class="bi bi-fire"></i> Missing fire extinguisher slot detected — Admin has been notified.</div>`;
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
      <div class="insp-av">${inspector.charAt(0)}</div>
      <div>
        <div class="insp-name">${inspector}</div>
        <div class="insp-role">Safety Inspector</div>
        <div class="insp-meta"><i class="bi bi-person-badge"></i> ${assigned} &nbsp;|&nbsp; <i class="bi bi-calendar3"></i> ${new Date().toLocaleDateString('en-PH',{month:'short',day:'numeric',year:'numeric'})} &nbsp;|&nbsp; <i class="bi bi-clock"></i> ${new Date().toLocaleTimeString('en-PH',{hour:'2-digit',minute:'2-digit'})}</div>
      </div>
    </div>`;

  if (area.missing) {
    const alertBanner = document.getElementById('missingAlert');
    document.getElementById('missingAlertMsg').textContent = `⚠ Missing fire extinguisher detected in ${area.name}. Admin notified.`;
    alertBanner.style.display = 'flex';
  }

  document.getElementById('drillPanel').style.display = 'block';
}

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

function renderRegistry(data) {
  const rows = data || feRegistry;
  const today = new Date();
  document.getElementById('registryBody').innerHTML = rows.map(u => {
    const d = Math.ceil((new Date(u.nextDue) - today) / 86400000);
    const age = today.getFullYear() - u.year;
    const sc = u.status==='New' ? 'st-new' : u.status==='Refillable' ? 'st-refill' : 'st-defect';
    const dc = d < 0 ? 'text-danger' : d < 30 ? 'text-warn' : '';
    return `<tr>
      <td><strong>${u.id}</strong></td>
      <td>${u.type}</td>
      <td>${u.loc}</td>
      <td>${u.kg} kg</td>
      <td>${u.lastInsp}</td>
      <td class="${dc}">${u.nextDue} ${d<0?'<span class="badge-urgent">OVERDUE</span>':d<30?'<span class="badge-warn">Soon</span>':''}</td>
      <td><span class="fe-status-badge ${sc}">${u.status}</span></td>
      <td>${age} yr${age!==1?'s':''}</td>
      <td><button class="tbl-btn" onclick="drillDown('${Object.keys(AREAS).find(k=>AREAS[k].units.includes(u.id))||''}');switchSubTab('map')">View</button></td>
    </tr>`;
  }).join('');
}

function filterRegistry() {
  const q  = document.getElementById('registrySearch').value.toLowerCase();
  const st = document.getElementById('registryStatus').value;
  renderRegistry(feRegistry.filter(u =>
    (!q  || u.id.toLowerCase().includes(q) || u.loc.toLowerCase().includes(q)) &&
    (!st || u.status === st)
  ));
}

function saveNewFE() {
  const id  = document.getElementById('feUnitId').value.trim();
  if (!id) { alert('Unit ID required'); return; }
  feRegistry.push({
    id, type: document.getElementById('feType').value,
    loc:  document.getElementById('feLocation').value,
    kg:   parseFloat(document.getElementById('feWeight').value)||0,
    lastInsp: document.getElementById('feLastInsp').value,
    nextDue:  document.getElementById('feNextDue').value,
    status:   document.getElementById('feStatus').value,
    year: parseInt(document.getElementById('feYear').value)||new Date().getFullYear(),
    inspector:'—', assigned:'—',
  });
  closeModal('addFEModal');
  renderRegistry();
  showToast('Fire extinguisher unit added.');
}

function renderWorkOrders() {
  const stages = { 'Issue Logged':'st-log', 'In Progress':'st-prog', 'Pending Parts':'st-warn', 'Completed/Verified':'st-done' };
  document.getElementById('woBody').innerHTML = workOrders.map(w => `
    <tr>
      <td><strong>${w.id}</strong></td>
      <td>${w.issue}</td>
      <td>${w.loc}</td>
      <td>${w.by}</td>
      <td>${w.date}</td>
      <td>${w.assigned}</td>
      <td><span class="stage-badge ${stages[w.stage]||'st-log'}">${w.stage}</span></td>
      <td>
        <select class="stage-select" onchange="updateWOStage('${w.id}',this.value)">
          <option ${w.stage==='Issue Logged'?'selected':''}>Issue Logged</option>
          <option ${w.stage==='In Progress'?'selected':''}>In Progress</option>
          <option ${w.stage==='Pending Parts'?'selected':''}>Pending Parts</option>
          <option ${w.stage==='Completed/Verified'?'selected':''}>Completed/Verified</option>
        </select>
      </td>
    </tr>`).join('');
}

function updateWOStage(id, stage) {
  const wo = workOrders.find(w => w.id === id);
  if (wo) { wo.stage = stage; renderWorkOrders(); showToast(`WO ${id} updated to: ${stage}`); }
}

function saveNewWO() {
  workOrders.unshift({
    id:'WO-00'+(workOrders.length+1),
    issue:    document.getElementById('woIssue').value,
    loc:      document.getElementById('woLocation').value,
    by:       document.getElementById('woReporter').value,
    date:     new Date().toISOString().split('T')[0],
    assigned: document.getElementById('woAssigned').value,
    stage:    'Issue Logged',
  });
  closeModal('addWOModal');
  renderWorkOrders();
  showToast('Work order created.');
}

function renderKeyLogs() {
  document.getElementById('keylogBody').innerHTML = keyLogs.map(k => `
    <tr>
      <td><strong>${k.id}</strong></td>
      <td>${k.name}</td>
      <td>${k.dept}</td>
      <td>${k.key}</td>
      <td>${k.inTime}</td>
      <td>${k.outTime}</td>
      <td><span class="kl-badge ${k.status==='Active'?'kl-active':'kl-done'}">${k.status}</span></td>
      <td>${k.guard}</td>
    </tr>`).join('');
}

function openScanModal(mode) {
  currentScanMode = mode;
  document.getElementById('scanModalTitle').innerHTML = mode==='in'
    ? '<i class="bi bi-upc-scan"></i> Scan ID — Borrow Key'
    : '<i class="bi bi-box-arrow-right"></i> Scan ID — Return Key';
  document.getElementById('scanModal').style.display = 'flex';
  setTimeout(()=>document.getElementById('scanIdInput').focus(), 100);
}

function processScan() {
  const id   = document.getElementById('scanIdInput').value.trim();
  const key  = document.getElementById('scanKeyItem').value.trim();
  const dept = document.getElementById('scanDept').value.trim();
  if (!id) { showToast('Please enter or scan an ID.', true); return; }

  const now = new Date().toLocaleTimeString('en-PH',{hour:'2-digit',minute:'2-digit'});
  if (currentScanMode === 'in') {
    const newLog = {
      id: 'KL-00'+(keyLogs.length+1),
      name: id, dept, key: key||'(not specified)',
      inTime: now, outTime:'—', status:'Active', guard:'Santos, J.'
    };
    keyLogs.unshift(newLog);
    showToast(`Key issued to ${id} at ${now}`);
    guardActLog.unshift({ time:now, action:`Key issued to ${id} (${dept}) — ${key}` });
  } else {
    const log = keyLogs.find(l => l.name.toLowerCase().includes(id.toLowerCase()) && l.status==='Active');
    if (log) { log.outTime = now; log.status = 'Returned'; showToast(`Key returned by ${log.name} at ${now}`); }
    else { showToast('No active borrow found for this ID.', true); }
  }

  document.getElementById('scanIdInput').value  = '';
  document.getElementById('scanKeyItem').value  = '';
  document.getElementById('scanDept').value     = '';
  closeModal('scanModal');
  renderKeyLogs();
}

function renderGuardDash() {
  document.getElementById('tripTicketBody').innerHTML = tripTickets.map(t => `
    <tr>
      <td><strong>${t.no}</strong></td>
      <td>${t.requester}</td>
      <td>${t.vehicle}</td>
      <td>${t.dest}</td>
      <td>${t.dep}</td>
      <td>${t.approvedBy}</td>
      <td><span class="tt-badge tt-${t.status.toLowerCase()}">${t.status}</span></td>
    </tr>`).join('');

  const active = keyLogs.filter(k => k.status === 'Active');
  document.getElementById('activeBorrows').innerHTML = active.length
    ? active.map(k=>`
    <div class="borrow-card">
      <div class="bc-av">${k.name.charAt(0)}</div>
      <div class="bc-info">
        <div class="bc-name">${k.name}</div>
        <div class="bc-dept">${k.dept}</div>
        <div class="bc-key"><i class="bi bi-key-fill"></i> ${k.key}</div>
        <div class="bc-time"><i class="bi bi-clock"></i> Since ${k.inTime}</div>
      </div>
    </div>`).join('')
    : '<div class="no-data">No active key borrows.</div>';

  document.getElementById('guardActLog').innerHTML = guardActLog.slice(0,8).map(l=>`
    <li><span class="al-time">${l.time}</span> ${l.action}</li>`).join('');

  updateGuardClock();
}

function updateGuardClock() {
  const el = document.getElementById('guardClock');
  if (el) el.textContent = new Date().toLocaleTimeString('en-PH',{hour:'2-digit',minute:'2-digit',second:'2-digit'});
}
setInterval(updateGuardClock, 1000);

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

function openAddWOModal() { document.getElementById('addWOModal').style.display = 'flex'; }
function openAddFEModal() { document.getElementById('addFEModal').style.display = 'flex'; }
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

renderRegistry();
renderWorkOrders();
renderKeyLogs();
</script>
<?= $this->endSection() ?>