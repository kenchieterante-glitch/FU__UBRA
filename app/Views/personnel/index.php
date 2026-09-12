<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<?php
$title = $title ?? 'Personnel Management';
$departments = $departments ?? [];
$personnel = $personnel ?? [];
$positionOptions = ['Guard', 'Driver', 'Janitor', 'Maintenance', 'Carpenter', 'Construction Worker', 'Security', 'Office Staff', 'Administrator'];
if (!empty($personnel)) {
  foreach ($personnel as $person) {
    if (!empty($person['position'])) {
      $positionOptions[] = $person['position'];
    }
  }
}
$positionOptions = array_values(array_unique(array_filter($positionOptions)));
$showStatusTabs = in_array($title, ['Drivers', 'Janitors', 'Carpentries Shop', 'Maintenance', 'Construction Workers', 'Job Order Personnel'], true);
$jobOrders = $jobOrders ?? [];
// Only Drivers actually have a vehicle — every other category (Janitors,
// Carpentries Shop, Maintenance, Construction Workers, Job Order Personnel)
// shares $showStatusTabs for its status-tab UI, but should still say
// "Assigned Task" like the main Personnel list does.
$taskLabel = ($title === 'Drivers') ? 'Vehicle In Use' : 'Assigned Task';
?>

<div class="page-header">
  <div>
    <h1><?= esc($title) ?></h1>
    <p class="page-subtitle">Manage university personnel, assignments, and operational responsibilities.</p>
  </div>
  <button class="btn-add" onclick="document.getElementById('addModal').style.display='flex'">+ Add Personnel</button>
</div>

<?php if (!$showStatusTabs): ?>
<?php
$personnelStatCards = [
  ['tone' => 'tone-maroon',  'icon' => 'fa-users',         'label' => 'Total Personnel',   'value' => (int) ($total_personnel_count ?? 0), 'onclick' => "filterPersonnelByStat('total')"],
  ['tone' => 'tone-neutral', 'icon' => 'fa-id-badge',      'label' => 'Drivers',            'value' => (int) ($drivers_count ?? 0),         'onclick' => "filterPersonnelByStat('drivers')"],
  ['tone' => 'tone-green',   'icon' => 'fa-broom',         'label' => 'Janitors',           'value' => (int) ($janitors_count ?? 0),        'onclick' => "filterPersonnelByStat('janitors')"],
  ['tone' => 'tone-gold',    'icon' => 'fa-hammer',        'label' => 'Carpentries Shop',   'value' => (int) ($carpentries_count ?? 0),     'onclick' => "filterPersonnelByStat('carpentries')"],
  ['tone' => 'tone-neutral', 'icon' => 'fa-wrench',        'label' => 'Maintenance',        'value' => (int) ($maintenance_count ?? 0),     'onclick' => "filterPersonnelByStat('maintenance')"],
  ['tone' => 'tone-gold',    'icon' => 'fa-helmet-safety', 'label' => 'Construction Workers', 'value' => (int) ($construction_count ?? 0),  'onclick' => "filterPersonnelByStat('construction')"],
  ['tone' => 'tone-neutral', 'icon' => 'fa-file-contract', 'label' => 'Job Order Personnel', 'value' => (int) ($job_order_count ?? 0),      'onclick' => "filterPersonnelByStat('joborder')"],
  ['tone' => 'tone-green',   'icon' => 'fa-circle-check',  'label' => 'Active',             'value' => (int) ($active_count ?? 0),          'onclick' => "filterPersonnelByStat('Active')"],
  ['tone' => 'tone-gold',    'icon' => 'fa-calendar-day',  'label' => 'On Leave',           'value' => (int) ($on_leave_count ?? 0),        'onclick' => "filterPersonnelByStat('On Leave')"],
];
?>
<!-- Continuous horizontal auto-scroll ("marquee") of the status boxes —
     the card set is rendered twice back-to-back and the track animates
     exactly one set-width (-50%) on a loop, so the seam is invisible.
     Hovering pauses via animation-play-state (native browser behavior
     resumes from the same position, not a restart). The second copy is
     aria-hidden/untabbable since it's a purely visual duplicate. -->
<div class="stat-marquee" id="personnelStatCards">
  <button type="button" id="personnelMarqueeLeft" class="marquee-nav marquee-nav-left" onclick="marqueeStep(-1)" aria-label="Scroll status cards left" style="display:none"><i class="bi bi-chevron-left"></i></button>
  <button type="button" class="marquee-nav marquee-nav-right" onclick="marqueeStep(1)" aria-label="Scroll status cards right"><i class="bi bi-chevron-right"></i></button>
  <div class="stat-marquee-track">
    <?php foreach ($personnelStatCards as $card): ?>
      <div class="stat-card stat-card-clickable" onclick="<?= esc($card['onclick'], 'attr') ?>" role="button" tabindex="0">
        <span class="stat-icon <?= esc($card['tone'], 'attr') ?>"><i class="fa-solid <?= esc($card['icon'], 'attr') ?>"></i></span>
        <h3><?= esc($card['label']) ?></h3>
        <div class="value"><?= esc((string) $card['value']) ?></div>
      </div>
    <?php endforeach; ?>
    <?php foreach ($personnelStatCards as $card): ?>
      <div class="stat-card stat-card-clickable" onclick="<?= esc($card['onclick'], 'attr') ?>" aria-hidden="true" tabindex="-1">
        <span class="stat-icon <?= esc($card['tone'], 'attr') ?>"><i class="fa-solid <?= esc($card['icon'], 'attr') ?>"></i></span>
        <h3><?= esc($card['label']) ?></h3>
        <div class="value"><?= esc((string) $card['value']) ?></div>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<?php endif; ?>

<div class="table-card">
  <div class="table-toolbar">
  <div class="toolbar-left">
    <div class="toolbar-search">
      <input type="text" id="personnelSearch" class="search-box" placeholder="Search personnel..." oninput="filterPersonnelTable()">
      <i class="bi bi-search search-icon"></i>
    </div>
  </div>
  <div class="toolbar-right">
    <div class="filter-menu-wrapper">
      <button type="button" class="filter-btn" onclick="togglePersonnelFilterMenu()" aria-label="Open filters">
        <i class="bi bi-funnel"></i>
      </button>
      <div class="filter-popup" id="personnelFilterPopup">
        <div class="filter-popup-title">Filter</div>
        <div class="filter-row">
          <label for="personnelDepartment">Department</label>
          <select id="personnelDepartment" onchange="filterPersonnelTable()">
            <option value="">All Departments</option>
            <?php foreach ($departments as $d): ?>
              <option value="<?= esc($d['name']) ?>"><?= esc($d['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="filter-row">
          <label for="personnelStatus">Status</label>
          <select id="personnelStatus" onchange="filterPersonnelTable()">
            <option value="">All Statuses</option>
            <option value="Active">Active</option>
            <option value="On Leave">On Leave</option>
            <option value="Inactive">Inactive</option>
          </select>
        </div>
        <div class="filter-row">
          <label for="personnelSort">Sort By</label>
          <select id="personnelSort" onchange="applyPersonnelSort()">
            <option value="">Default</option>
            <option value="0-asc">Name (A&ndash;Z)</option>
            <option value="0-desc">Name (Z&ndash;A)</option>
            <option value="2-asc">Department (A&ndash;Z)</option>
            <option value="2-desc">Department (Z&ndash;A)</option>
            <option value="5-asc">Status (A&ndash;Z)</option>
            <option value="5-desc">Status (Z&ndash;A)</option>
          </select>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="personnel-table-scroll personnel-list-scroll">
<table id="personnelTable" class="data-table">
  <thead>
    <tr>
      <th>Personnel Detail</th>
      <th>Employee ID</th>
      <th>Department</th>
      <th>Type</th>
      <th><?= esc($taskLabel) ?></th>
      <th>Status</th>
      <th>Actions</th>
    </tr>
  </thead>
  <tbody>
    <?php if (!empty($personnel)): ?>
      <?php foreach ($personnel as $p): ?>
        <?php
          $departmentName = '';
          foreach ($departments as $d) {
            if ((int) $d['id'] === (int) ($p['department_id'] ?? 0)) {
              $departmentName = (string) $d['name'];
              break;
            }
          }
          $statusValue = $p['status'] ?? 'Active';
          $statusClass = (strtolower((string) $statusValue) === 'active') ? 'active' : 'pending';
          $isJobOrder = ($p['employment_type'] ?? 'Regular') === 'JobOrder';

          // Same position-keyword grouping as the dedicated Drivers/Janitors/
          // etc. routes (PersonnelController), just computed per row so the
          // Total Personnel overview's own stat cards can filter in place
          // instead of navigating to those separate pages.
          $positionLower = strtolower((string) ($p['position'] ?? ''));
          $category = '';
          if (str_contains($positionLower, 'driver')) {
            $category = 'drivers';
          } elseif (str_contains($positionLower, 'janitor') || str_contains($positionLower, 'cleaning')) {
            $category = 'janitors';
          } elseif (str_contains($positionLower, 'carpenter')) {
            $category = 'carpentries';
          } elseif (str_contains($positionLower, 'maintenance') || str_contains($positionLower, 'physical plant')) {
            $category = 'maintenance';
          } elseif (str_contains($positionLower, 'construction')) {
            $category = 'construction';
          }
        ?>
        <tr class="personnel-row" onclick="openPersonnelDetail(<?= (int) $p['id'] ?>)"
            data-search="<?= esc(strtolower((string) ($p['full_name'] ?? '') . ' ' . ($p['emp_id'] ?? '') . ' ' . ($p['email'] ?? '') . ' ' . ($p['assigned_task'] ?? '') . ' ' . $departmentName)) ?>"
            data-department="<?= esc(strtolower($departmentName)) ?>"
            data-status="<?= esc(strtolower((string) ($statusValue ?? '')) ) ?>"
            data-category="<?= esc($category, 'attr') ?>"
            data-joborder="<?= $isJobOrder ? '1' : '0' ?>">
          <td><?= esc($p['full_name']) ?><br><small><?= esc($p['email']) ?></small></td>
          <td><?= esc($p['emp_id']) ?></td>
          <td><?= esc($departmentName) ?></td>
          <td><span class="status-badge <?= $isJobOrder ? 'status-pending' : 'status-active' ?>"><?= $isJobOrder ? 'Job Order' : 'Regular' ?></span></td>
          <td><?= esc($p['assigned_task'] ?? 'No current assignment') ?></td>
          <td><span class="status-badge status-<?= esc($statusClass) ?>"><?= esc($statusValue) ?></span></td>
          <td>
            <div class="action-buttons" onclick="event.stopPropagation()">
              <?php if (!$isJobOrder): ?>
                <button class="icon-btn" onclick="document.getElementById('assignJoModal<?= $p['id'] ?>').style.display='flex'" title="Assign to Job Order" aria-label="Assign <?= esc($p['full_name']) ?> to a Job Order"><i class="fa-solid fa-file-contract"></i></button>
              <?php endif; ?>
              <form method="post" action="<?= base_url('personnel/delete/'.$p['id']) ?>" onsubmit="return confirm('Archive this personnel record?')" style="display:contents;">
                <?= csrf_field() ?>
                <button type="submit" class="icon-btn delete" title="Archive" aria-label="Archive <?= esc($p['full_name']) ?>"><i class="fa-solid fa-archive"></i></button>
              </form>
            </div>
          </td>
        </tr>

        <!-- EDIT MODAL — same wide popup styling as the Personnel Detail
             view, so editing looks like a continuation of that view instead
             of a different, smaller UI. -->
        <div class="modal personnel-edit-modal" id="editModal<?= $p['id'] ?>">
          <div class="modal-box">
            <div class="modal-header">
              <h3>Edit Personnel</h3>
              <button type="button" class="modal-close-btn" onclick="document.getElementById('editModal<?= $p['id'] ?>').style.display='none'" aria-label="Close"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="modal-body">
              <form action="<?= site_url('personnel/edit/'.$p['id']) ?>" method="post">
                <?= csrf_field() ?>
                <div class="detail-section">
                  <div class="detail-section-title">Personnel Details</div>
                  <div class="detail-grid">
                    <div class="edit-field">
                      <label>Employee ID <span class="required-mark">*</span></label>
                      <input type="text" name="emp_id" value="<?= esc($p['emp_id']) ?>" required>
                    </div>
                    <div class="edit-field">
                      <label>Full Name <span class="required-mark">*</span></label>
                      <input type="text" name="full_name" value="<?= esc($p['full_name']) ?>" required>
                    </div>
                    <div class="edit-field span-2">
                      <label>Email</label>
                      <input type="email" name="email" value="<?= esc($p['email']) ?>">
                    </div>
                    <div class="edit-field">
                      <label>Department</label>
                      <select name="department_id">
                        <?php foreach ($departments as $d): ?>
                          <option value="<?= $d['id'] ?>" <?= $d['id']==$p['department_id']?'selected':'' ?>><?= esc($d['name']) ?></option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                    <div class="edit-field">
                      <label>Position</label>
                      <select name="position">
                        <option value="">Select Position</option>
                        <?php foreach ($positionOptions as $positionOption): ?>
                          <option value="<?= esc($positionOption) ?>" <?= strtolower(trim((string) $p['position'])) === strtolower(trim((string) $positionOption)) ? 'selected' : '' ?>><?= esc($positionOption) ?></option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                    <div class="edit-field">
                      <label><?= esc($taskLabel) ?></label>
                      <input type="text" name="assigned_task" value="<?= esc($p['assigned_task']) ?>">
                    </div>
                    <div class="edit-field">
                      <label>Status</label>
                      <select name="status">
                        <option <?= $p['status']=='Active'?'selected':'' ?>>Active</option>
                        <option <?= $p['status']=='On Leave'?'selected':'' ?>>On Leave</option>
                        <option <?= $p['status']=='Inactive'?'selected':'' ?>>Inactive</option>
                      </select>
                    </div>
                  </div>
                </div>
                <div class="modal-actions">
                  <button type="button" onclick="document.getElementById('editModal<?= $p['id'] ?>').style.display='none'">Cancel</button>
                  <button type="submit" class="btn-maroon">Save Changes</button>
                </div>
              </form>
            </div>
          </div>
        </div>

        <?php if (!$isJobOrder): ?>
        <!-- ASSIGN TO JOB ORDER MODAL -->
        <div class="modal" id="assignJoModal<?= $p['id'] ?>">
          <div class="modal-box">
            <h3>Assign <?= esc($p['full_name']) ?> to a Job Order</h3>
            <?php if (empty($jobOrders)): ?>
              <p>No Job Orders exist yet. <a href="<?= base_url('personnel/job-orders') ?>">Create one first</a>.</p>
              <div class="modal-actions">
                <button type="button" onclick="document.getElementById('assignJoModal<?= $p['id'] ?>').style.display='none'">Close</button>
              </div>
            <?php else: ?>
            <form action="<?= site_url('personnel/assign-job-order/' . $p['id']) ?>" method="post">
              <?= csrf_field() ?>
              <label>Job Order <span class="required-mark">*</span></label>
              <select name="job_order_id" required>
                <option value="">Select Job Order</option>
                <?php foreach ($jobOrders as $jo): ?>
                  <option value="<?= $jo['id'] ?>"><?= esc($jo['job_order_number']) ?> &mdash; <?= esc($jo['job_order_title']) ?></option>
                <?php endforeach; ?>
              </select>
              <label>Position</label>
              <input type="text" name="position" placeholder="Leave blank to use the Job Order's position">
              <label>Assignment Location</label>
              <input type="text" name="assignment_location" placeholder="Leave blank to use the Job Order's location">
              <label>Assignment Start Date</label>
              <input type="date" name="assignment_start_date" value="<?= date('Y-m-d') ?>">
              <label>Assignment End Date</label>
              <input type="date" name="assignment_end_date">
              <label>Contract Number</label>
              <input type="text" name="contract_number" placeholder="Optional">
              <label style="display:flex;align-items:center;gap:8px;margin-top:8px;">
                <input type="checkbox" name="override_expired" value="1" style="width:auto;"> Assign even if the Job Order has expired
              </label>
              <div class="modal-actions">
                <button type="button" onclick="document.getElementById('assignJoModal<?= $p['id'] ?>').style.display='none'">Cancel</button>
                <button type="submit" class="btn-maroon">Assign</button>
              </div>
            </form>
            <?php endif; ?>
          </div>
        </div>
        <?php endif; ?>

      <?php endforeach; ?>
    <?php else: ?>
      <tr><td colspan="7">No personnel records yet.</td></tr>
    <?php endif; ?>
  </tbody>
</table>
</div>
</div>

<!-- PERSONNEL DETAIL MODAL — view-only: assignment, documents, and history
     in one popup, closed with the × only (no edit/save here). -->
<div class="modal" id="personnelDetailModal">
  <div class="modal-box">
    <div class="modal-header">
      <h3 id="pdTitle">Personnel Detail</h3>
      <div class="modal-header-actions">
        <button type="button" class="modal-close-btn" onclick="openEditFromDetail()" aria-label="Edit"><i class="fa-solid fa-pen"></i></button>
        <button type="button" class="modal-close-btn" onclick="closePersonnelDetail()" aria-label="Close"><i class="fa-solid fa-xmark"></i></button>
      </div>
    </div>
    <div class="modal-body" id="pdBody"></div>
  </div>
</div>

<script>
function esc(s) {
  const d = document.createElement('div');
  d.textContent = String(s ?? '');
  return d.innerHTML;
}

let currentDetailPersonId = null;

function openPersonnelDetail(id) {
  currentDetailPersonId = id;
  const modal = document.getElementById('personnelDetailModal');
  const body = document.getElementById('pdBody');
  document.getElementById('pdTitle').textContent = 'Loading...';
  body.innerHTML = `<div class="no-data">Loading personnel detail...</div>`;
  modal.style.display = 'flex';
  // The popup already scrolls internally (.modal-body) if it needs to —
  // without this, the page behind it stays scrollable too, showing a
  // second, confusing scrollbar at the edge of the browser window.
  document.body.style.overflow = 'hidden';

  fetch(`<?= base_url('personnel/detail/') ?>${id}`)
    .then(r => r.json())
    .then(p => renderPersonnelDetail(p))
    .catch(() => {
      body.innerHTML = `<div class="no-data">Could not load personnel detail. Please try again.</div>`;
    });
}

function closePersonnelDetail() {
  document.getElementById('personnelDetailModal').style.display = 'none';
  document.body.style.overflow = '';
}

// The Edit form itself still lives in each row's own #editModal<id> — the
// popup just closes itself and opens that same modal instead of duplicating
// the form.
function openEditFromDetail() {
  if (currentDetailPersonId === null) return;
  closePersonnelDetail();
  const editModal = document.getElementById('editModal' + currentDetailPersonId);
  if (editModal) editModal.style.display = 'flex';
}

function renderPersonnelDetail(p) {
  document.getElementById('pdTitle').textContent = p.name;

  const assignment = p.activeAssignment
    ? `<div class="detail-row"><span>Job Order</span><strong>${esc(p.activeAssignment.jobOrder)}</strong></div>
       <div class="detail-row"><span>Location</span><strong>${esc(p.activeAssignment.location)}</strong></div>
       <div class="detail-row"><span>Supervisor</span><strong>${esc(p.activeAssignment.supervisor)}</strong></div>
       <div class="detail-row"><span>Period</span><strong>${esc(p.activeAssignment.period)}</strong></div>`
    : `<div class="detail-row"><span>Job Order</span><strong>Not currently assigned</strong></div>`;

  const historyRows = p.assignmentHistory.length
    ? p.assignmentHistory.map(h => `<tr><td>${esc(h.jobOrder)}</td><td>${esc(h.location)}</td><td>${esc(h.period)}</td><td>${esc(h.status)}</td></tr>`).join('')
    : `<tr><td colspan="4">No past assignments recorded yet.</td></tr>`;

  const docRows = p.documents.length
    ? p.documents.map(d => `<tr><td>${esc(d.type)}</td><td>${esc(d.status)}</td><td>${esc(d.expiry)}</td></tr>`).join('')
    : `<tr><td colspan="3">No documents on file yet.</td></tr>`;

  document.getElementById('pdBody').innerHTML = `
    <div class="detail-section">
      <div class="detail-section-title">Personnel Details</div>
      <div class="detail-grid">
        <div class="detail-row"><span>Employee ID</span><strong>${esc(p.empId)}</strong></div>
        <div class="detail-row"><span>Department</span><strong>${esc(p.department)}</strong></div>
        <div class="detail-row"><span>Position</span><strong>${esc(p.position)}</strong></div>
        <div class="detail-row"><span>Employment Type</span><strong>${esc(p.employmentType)}</strong></div>
        <div class="detail-row"><span>Status</span><strong>${esc(p.status)}</strong></div>
        <div class="detail-row"><span>Email</span><strong>${esc(p.email)}</strong></div>
        <div class="detail-row"><span>Current Task</span><strong>${esc(p.assignedTask)}</strong></div>
      </div>
    </div>

    <div class="detail-section">
      <div class="detail-section-title">Current Assignment</div>
      ${assignment}
    </div>

    <div class="detail-section">
      <div class="detail-section-title">Assignment History</div>
      <div class="history-table-wrap">
        <table class="history-table">
          <thead><tr><th>Job Order</th><th>Location</th><th>Period</th><th>Status</th></tr></thead>
          <tbody>${historyRows}</tbody>
        </table>
      </div>
    </div>

    <div class="detail-section">
      <div class="detail-section-title">Documents — ${esc(p.documentCompleteness)}</div>
      <div class="history-table-wrap">
        <table class="history-table">
          <thead><tr><th>Type</th><th>Status</th><th>Expiry</th></tr></thead>
          <tbody>${docRows}</tbody>
        </table>
      </div>
    </div>`;
}

const personnelLookup = <?= json_encode(array_values(array_filter(array_map(function($person) {
  return [
    'emp_id' => (string) ($person['emp_id'] ?? ''),
    'full_name' => (string) ($person['full_name'] ?? ''),
    'email' => (string) ($person['email'] ?? ''),
  ];
}, $personnel ?? []), function($person) {
  return !empty($person['emp_id']);
})), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

function filterPersonnelTable() {
  const searchValue = document.getElementById('personnelSearch').value.toLowerCase().trim();
  const departmentValue = document.getElementById('personnelDepartment').value.toLowerCase().trim();
  const statusValue = document.getElementById('personnelStatus').value.toLowerCase().trim();
  const rows = document.querySelectorAll('#personnelTable tbody tr[data-search]');

  rows.forEach(row => {
    const rowText = (row.dataset.search || '').toLowerCase().trim();
    const departmentCell = (row.dataset.department || '').toLowerCase().trim();
    const statusCell = (row.dataset.status || '').toLowerCase().trim();

    const matchesSearch = !searchValue || rowText.includes(searchValue);
    const matchesDepartment = !departmentValue || departmentCell === departmentValue;
    const matchesStatus = !statusValue || statusCell === statusValue;

    row.style.display = (matchesSearch && matchesDepartment && matchesStatus) ? '' : 'none';
  });
}

let personnelOriginalOrder = null;

function applyPersonnelSort() {
  const tbody = document.querySelector('#personnelTable tbody');
  if (!tbody) return;

  if (!personnelOriginalOrder) {
    personnelOriginalOrder = Array.from(tbody.querySelectorAll('tr[data-search]'));
  }

  const value = document.getElementById('personnelSort').value;
  if (!value) {
    personnelOriginalOrder.forEach(row => tbody.appendChild(row));
    return;
  }

  const [colIndexStr, direction] = value.split('-');
  const colIndex = parseInt(colIndexStr, 10);
  const ascending = direction === 'asc';

  const rows = Array.from(tbody.querySelectorAll('tr[data-search]'));
  rows.sort((a, b) => {
    const aText = a.children[colIndex]?.innerText.trim() ?? '';
    const bText = b.children[colIndex]?.innerText.trim() ?? '';
    const cmp = aText.localeCompare(bText, undefined, { sensitivity: 'base' });
    return ascending ? cmp : -cmp;
  });

  rows.forEach(row => tbody.appendChild(row));
}

// Stat cards act as quick filters into the table below — same as Vehicle
// Management: the cards stay right where they are, the table just filters
// in place, no navigating to a separate page and no "Back to Overview" bar.
function filterPersonnelByStat(kind) {
  document.getElementById('personnelSearch').value = '';
  document.getElementById('personnelDepartment').value = '';
  document.getElementById('personnelStatus').value = '';

  if (kind === 'total') {
    filterPersonnelTable();
  } else if (kind === 'Active' || kind === 'On Leave') {
    document.getElementById('personnelStatus').value = kind;
    filterPersonnelTable();
  } else if (kind === 'joborder') {
    document.querySelectorAll('#personnelTable tbody tr[data-search]').forEach(row => {
      row.style.display = row.dataset.joborder === '1' ? '' : 'none';
    });
  } else {
    // drivers / janitors / carpentries / maintenance / construction
    document.querySelectorAll('#personnelTable tbody tr[data-search]').forEach(row => {
      row.style.display = row.dataset.category === kind ? '' : 'none';
    });
  }
}

// Left/right arrows for the status-card marquee. Rather than pausing or
// swapping out .stat-marquee-track's CSS animation, this seeks the SAME
// running animation forward/back by one card's worth of time (Web
// Animations API — Animation.currentTime) — the loop never stops, it just
// jumps to a different point in its own cycle and keeps playing from there.
// Tracks how many steps forward (right) the user has moved from the
// starting position, purely to decide whether the back arrow should be
// shown — the marquee animation itself still loops continuously.
let marqueeStepsFromStart = 0;

function marqueeStep(direction) {
  const wrap = document.getElementById('personnelStatCards');
  const track = wrap?.querySelector('.stat-marquee-track');
  const anim = track?.getAnimations()[0];
  if (!anim) return;

  const cardStep = 179; // 165px card width + 14px gap, per .stat-marquee-track .stat-card
  const totalDistance = (track.scrollWidth / 2) + 7; // matches translateX(calc(-50% - 7px))
  const duration = Number(anim.effect.getTiming().duration); // ms
  const timeStep = (cardStep / totalDistance) * duration;

  anim.currentTime = (Number(anim.currentTime) + (direction * timeStep) + duration) % duration;

  marqueeStepsFromStart = Math.max(0, marqueeStepsFromStart + direction);
  const leftArrow = document.getElementById('personnelMarqueeLeft');
  if (leftArrow) leftArrow.style.display = marqueeStepsFromStart > 0 ? '' : 'none';
}

function togglePersonnelFilterMenu() {
  const popup = document.getElementById('personnelFilterPopup');
  popup.classList.toggle('visible');
}

document.addEventListener('click', e => {
  const wrapper = document.querySelector('.filter-menu-wrapper');
  const popup = document.getElementById('personnelFilterPopup');
  if (!wrapper.contains(e.target)) {
    popup.classList.remove('visible');
  }
});

document.addEventListener('DOMContentLoaded', function () {
  const addForm = document.getElementById('addPersonnelForm');
  if (!addForm) return;

  const empIdInput = addForm.querySelector('input[name="emp_id"]');
  const fullNameInput = addForm.querySelector('input[name="full_name"]');
  const emailInput = addForm.querySelector('input[name="email"]');

  if (!empIdInput || !fullNameInput || !emailInput) return;

  empIdInput.addEventListener('blur', function () {
    const enteredId = this.value.trim().toLowerCase();
    if (!enteredId) return;

    const match = personnelLookup.find(function (person) {
      return String(person.emp_id).trim().toLowerCase() === enteredId;
    });

    if (match) {
      fullNameInput.value = match.full_name || '';
      emailInput.value = match.email || '';
    }
  });
});
</script>

<!-- ADD MODAL -->
<div class="modal" id="addModal">
  <div class="modal-box">
    <h3>Add Personnel</h3>
    <form id="addPersonnelForm" action="<?= site_url('personnel/add') ?>" method="post">
      <?= csrf_field() ?>
      <p class="required-note">Fields marked <span class="required-mark">*</span> are required.</p>
      <label>Employee ID <span class="required-mark">*</span></label>
      <input type="text" name="emp_id" placeholder="e.g. EMP-2026-001" required>
      <label>Full Name <span class="required-mark">*</span></label>
      <input type="text" name="full_name" placeholder="e.g. Juan Dela Cruz" required>
      <label>Email</label>
      <input type="email" name="email" placeholder="e.g. juan.delacruz@foundation.edu.ph">
      <label>Department</label>
      <select name="department_id">
        <?php foreach ($departments as $d): ?>
          <option value="<?= $d['id'] ?>"><?= esc($d['name']) ?></option>
        <?php endforeach; ?>
      </select>
      <label>Position</label>
      <select name="position">
        <option value="">Select Position</option>
        <?php foreach ($positionOptions as $positionOption): ?>
          <option value="<?= esc($positionOption) ?>"><?= esc($positionOption) ?></option>
        <?php endforeach; ?>
      </select>
      <label><?= esc($taskLabel) ?></label>
      <input type="text" name="assigned_task">
      <label>Status</label>
      <select name="status">
        <option>Active</option>
        <option>On Leave</option>
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

