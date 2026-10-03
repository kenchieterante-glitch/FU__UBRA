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
  ['tone' => 'tone-maroon',  'icon' => 'bi-people-fill',      'label' => 'Total Personnel',   'value' => (int) ($total_personnel_count ?? 0), 'onclick' => "filterPersonnelByStat('total')"],
  ['tone' => 'tone-neutral', 'icon' => 'bi-person-vcard-fill','label' => 'Drivers',            'value' => (int) ($drivers_count ?? 0),         'onclick' => "filterPersonnelByStat('drivers')"],
  ['tone' => 'tone-green',   'icon' => 'bi-brush',            'label' => 'Janitors',           'value' => (int) ($janitors_count ?? 0),        'onclick' => "filterPersonnelByStat('janitors')"],
  ['tone' => 'tone-gold',    'icon' => 'bi-hammer',           'label' => 'Carpentries Shop',   'value' => (int) ($carpentries_count ?? 0),     'onclick' => "filterPersonnelByStat('carpentries')"],
  ['tone' => 'tone-neutral', 'icon' => 'bi-wrench',           'label' => 'Maintenance',        'value' => (int) ($maintenance_count ?? 0),     'onclick' => "filterPersonnelByStat('maintenance')"],
  ['tone' => 'tone-gold',    'icon' => 'bi-cone-striped',     'label' => 'Construction Workers', 'value' => (int) ($construction_count ?? 0),  'onclick' => "filterPersonnelByStat('construction')"],
  ['tone' => 'tone-neutral', 'icon' => 'bi-file-earmark-text','label' => 'Job Order Personnel', 'value' => (int) ($job_order_count ?? 0),      'onclick' => "filterPersonnelByStat('joborder')"],
  ['tone' => 'tone-green',   'icon' => 'bi-check-circle-fill','label' => 'Active',             'value' => (int) ($active_count ?? 0),          'onclick' => "filterPersonnelByStat('Active')"],
  ['tone' => 'tone-gold',    'icon' => 'bi-calendar-day',     'label' => 'On Leave',           'value' => (int) ($on_leave_count ?? 0),        'onclick' => "filterPersonnelByStat('On Leave')"],
];
?>
<!-- Static carousel — a single row that no longer auto-scrolls (that's
     the old marquee this replaced, twice over now). Paging is entirely
     manual: Prev/Next float over the row's left/right edges (not a
     separate control row above it) and scroll one view's worth of cards
     at a time. Prev starts hidden — there's nothing behind you at the
     start — and only appears once Next has been clicked at least once
     (personnelCarouselStep below). Uses native horizontal scroll +
     scroll-snap under the hood, not a hand-rolled transform/animation. -->
<div class="stat-carousel" id="personnelStatCarousel">
  <button type="button" class="carousel-nav-btn carousel-nav-left" id="personnelCarouselPrev" onclick="personnelCarouselStep(-1)" aria-label="Scroll status cards left" style="display:none">
    <i class="bi bi-chevron-left"></i>
  </button>
  <button type="button" class="carousel-nav-btn carousel-nav-right" id="personnelCarouselNext" onclick="personnelCarouselStep(1)" aria-label="Scroll status cards right">
    <i class="bi bi-chevron-right"></i>
  </button>
  <div class="stat-carousel-track" id="personnelStatCarouselTrack">
    <?php foreach ($personnelStatCards as $card): ?>
      <div class="stat-card stat-card-clickable" onclick="<?= esc($card['onclick'], 'attr') ?>" role="button" tabindex="0">
        <span class="stat-icon <?= esc($card['tone'], 'attr') ?>"><i class="bi <?= esc($card['icon'], 'attr') ?>"></i></span>
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
          <label id="personnelSortLabel">Sort By</label>
          <!-- Custom-styled dropdown, not a plain native <select> — the OS's
               own select rendering (grey "currently selected" bar repeated
               at the top of the list) read as unpolished. Same open/close
               interaction as the filter popup itself, just one level in. -->
          <div class="dd-select" id="personnelSortDD" data-onchange="applyPersonnelSort">
            <button type="button" class="dd-select-trigger" onclick="toggleDDSelect('personnelSortDD')" aria-haspopup="listbox" aria-expanded="false">
              <span class="dd-select-value">Default</span>
              <i class="bi bi-chevron-down"></i>
            </button>
            <div class="dd-select-menu" role="listbox">
              <div class="dd-select-option selected" data-value="" role="option">Default</div>
              <div class="dd-select-option" data-value="created-desc" role="option">Newest First</div>
              <div class="dd-select-option" data-value="created-asc" role="option">Oldest First</div>
              <div class="dd-select-option" data-value="0-asc" role="option">Name (A&ndash;Z)</div>
              <div class="dd-select-option" data-value="0-desc" role="option">Name (Z&ndash;A)</div>
              <div class="dd-select-option" data-value="2-asc" role="option">Department (A&ndash;Z)</div>
              <div class="dd-select-option" data-value="2-desc" role="option">Department (Z&ndash;A)</div>
              <div class="dd-select-option" data-value="5-asc" role="option">Status (A&ndash;Z)</div>
              <div class="dd-select-option" data-value="5-desc" role="option">Status (Z&ndash;A)</div>
            </div>
          </div>
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
            data-joborder="<?= $isJobOrder ? '1' : '0' ?>"
            data-created="<?= esc($p['created_at'] ?? '') ?>">
          <td><?= esc($p['full_name']) ?><br><small><?= esc($p['email']) ?></small></td>
          <td><?= esc($p['emp_id']) ?></td>
          <td><?= esc($departmentName) ?></td>
          <td><span class="status-badge <?= $isJobOrder ? 'status-pending' : 'status-active' ?>"><?= $isJobOrder ? 'Job Order' : 'Regular' ?></span></td>
          <td><?= esc($p['assigned_task'] ?? 'No current assignment') ?></td>
          <td><span class="status-badge status-<?= esc($statusClass) ?>"><?= esc($statusValue) ?></span></td>
          <td>
            <div class="action-buttons" onclick="event.stopPropagation()">
              <?php if (!$isJobOrder): ?>
                <button class="icon-btn" onclick="document.getElementById('assignJoModal<?= $p['id'] ?>').style.display='flex'" title="Assign to Job Order" aria-label="Assign <?= esc($p['full_name']) ?> to a Job Order"><i class="bi bi-file-earmark-text"></i></button>
              <?php endif; ?>
              <form method="post" action="<?= base_url('personnel/delete/'.$p['id']) ?>" onsubmit="return confirm('Archive this personnel record?')" style="display:contents;">
                <?= csrf_field() ?>
                <button type="submit" class="icon-btn delete" title="Archive" aria-label="Archive <?= esc($p['full_name']) ?>"><i class="bi bi-archive-fill"></i></button>
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
              <button type="button" class="modal-close-btn" onclick="document.getElementById('editModal<?= $p['id'] ?>').style.display='none'" aria-label="Close"><i class="bi bi-x-lg"></i></button>
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
                    <div class="edit-field">
                      <label>Email</label>
                      <input type="email" name="email" value="<?= esc($p['email']) ?>">
                    </div>
                    <div class="edit-field">
                      <label>Contact Number</label>
                      <input type="tel" name="contact_number" value="<?= esc($p['contact_number'] ?? '') ?>" placeholder="e.g. 0917 123 4567">
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
        <button type="button" class="modal-close-btn" onclick="openEditFromDetail()" aria-label="Edit"><i class="bi bi-pencil-fill"></i></button>
        <button type="button" class="modal-close-btn" onclick="closePersonnelDetail()" aria-label="Close"><i class="bi bi-x-lg"></i></button>
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
        <div class="detail-row"><span>Contact Number</span><strong>${esc(p.contactNumber)}</strong></div>
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

function applyPersonnelSort(value) {
  const tbody = document.querySelector('#personnelTable tbody');
  if (!tbody) return;

  if (!personnelOriginalOrder) {
    personnelOriginalOrder = Array.from(tbody.querySelectorAll('tr[data-search]'));
  }

  if (value === undefined) {
    value = document.querySelector('#personnelSortDD .dd-select-option.selected')?.dataset.value ?? '';
  }
  if (!value) {
    personnelOriginalOrder.forEach(row => tbody.appendChild(row));
    return;
  }

  const rows = Array.from(tbody.querySelectorAll('tr[data-search]'));

  // "Newest/Oldest First" sorts by the record's actual created_at
  // (data-created — an ISO datetime, not shown as its own table column),
  // not by visible column text like every other option here.
  if (value === 'created-desc' || value === 'created-asc') {
    const ascending = value === 'created-asc';
    rows.sort((a, b) => {
      const aTime = Date.parse(a.dataset.created || 0) || 0;
      const bTime = Date.parse(b.dataset.created || 0) || 0;
      return ascending ? aTime - bTime : bTime - aTime;
    });
    rows.forEach(row => tbody.appendChild(row));
    return;
  }

  const [colIndexStr, direction] = value.split('-');
  const colIndex = parseInt(colIndexStr, 10);
  const ascending = direction === 'asc';

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

// Status cards are back to a single-row carousel — but static this time,
// not the old auto-scrolling marquee. It never moves on its own; Prev/Next
// (grouped at the right, above the row) page it via native horizontal
// scrolling (scrollBy), one visible-width's worth of cards at a time.
// Scroll-snap (base.css) means it always settles on a clean card boundary
// rather than stopping mid-card.
function setupPersonnelStatCarousel() {
  const track = document.getElementById('personnelStatCarouselTrack');
  const prevBtn = document.getElementById('personnelCarouselPrev');
  const nextBtn = document.getElementById('personnelCarouselNext');
  if (!track || !prevBtn || !nextBtn) return;

  const cards = Array.from(track.querySelectorAll('.stat-card'));
  if (!cards.length) return;
  const firstCard = cards[0];
  const lastCard = cards[cards.length - 1];

  // scrollLeft-based start/end checks proved unreliable here (arrows stayed
  // stuck visible regardless of tolerance/resets) — this instead watches
  // whether the FIRST and LAST cards are actually on screen, using
  // IntersectionObserver scoped to the track itself as the viewport. That's
  // a direct answer to "is there anything to scroll back/forward to", not
  // an indirect one built on scroll-position arithmetic.
  let firstVisible = true;
  let lastVisible = false;

  function applyArrowState() {
    prevBtn.style.display = firstVisible ? 'none' : 'flex';
    nextBtn.style.display = lastVisible ? 'none' : 'flex';
  }

  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.target === firstCard) firstVisible = entry.isIntersecting;
      if (entry.target === lastCard) lastVisible = entry.isIntersecting;
    });
    applyArrowState();
  }, { root: track, threshold: 0.95 });

  observer.observe(firstCard);
  observer.observe(lastCard);
  applyArrowState();

  window.personnelCarouselStep = function (direction) {
    track.scrollBy({ left: direction * track.clientWidth, behavior: 'smooth' });
  };
}
document.addEventListener('DOMContentLoaded', setupPersonnelStatCarousel);

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
  <div class="modal-box modal-box-wide">
    <h3>Add Personnel</h3>
    <form id="addPersonnelForm" action="<?= site_url('personnel/add') ?>" method="post">
      <?= csrf_field() ?>
      <p class="required-note">Fields marked <span class="required-mark">*</span> are required.</p>
      <div class="form-grid2">
        <div class="fg">
          <label>Employee ID <span class="required-mark">*</span></label>
          <input type="text" name="emp_id" placeholder="e.g. EMP-2026-001" required>
        </div>
        <div class="fg">
          <label>Full Name <span class="required-mark">*</span></label>
          <input type="text" name="full_name" placeholder="e.g. Juan Dela Cruz" required>
        </div>
        <div class="fg">
          <label>Email</label>
          <input type="email" name="email" placeholder="e.g. juan.delacruz@foundation.edu.ph">
        </div>
        <div class="fg">
          <label>Contact Number</label>
          <input type="tel" name="contact_number" placeholder="e.g. 0917 123 4567">
        </div>
        <div class="fg">
          <label>Department</label>
          <select name="department_id">
            <?php foreach ($departments as $d): ?>
              <option value="<?= $d['id'] ?>"><?= esc($d['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="fg">
          <label>Position</label>
          <select name="position">
            <option value="">Select Position</option>
            <?php foreach ($positionOptions as $positionOption): ?>
              <option value="<?= esc($positionOption) ?>"><?= esc($positionOption) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="fg">
          <label><?= esc($taskLabel) ?></label>
          <input type="text" name="assigned_task">
        </div>
        <div class="fg">
          <label>Status</label>
          <select name="status">
            <option>Active</option>
            <option>On Leave</option>
            <option>Inactive</option>
          </select>
        </div>
      </div>
      <div class="modal-actions">
        <button type="button" onclick="document.getElementById('addModal').style.display='none'">Cancel</button>
        <button type="submit" class="btn-maroon">Save</button>
      </div>
    </form>
  </div>
</div>

<?= $this->endSection() ?>

