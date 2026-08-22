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
$taskLabel = $showStatusTabs ? 'Vehicle In Use' : 'Assigned Task';
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
  ['tone' => 'tone-maroon',  'icon' => 'fa-users',         'label' => 'Total Personnel',   'value' => (int) ($total_personnel_count ?? 0), 'onclick' => "window.location.href='" . base_url('personnel') . "'"],
  ['tone' => 'tone-neutral', 'icon' => 'fa-id-badge',      'label' => 'Drivers',            'value' => (int) ($drivers_count ?? 0),         'onclick' => "window.location.href='" . base_url('personnel/drivers') . "'"],
  ['tone' => 'tone-green',   'icon' => 'fa-broom',         'label' => 'Janitors',           'value' => (int) ($janitors_count ?? 0),        'onclick' => "window.location.href='" . base_url('personnel/janitors') . "'"],
  ['tone' => 'tone-gold',    'icon' => 'fa-hammer',        'label' => 'Carpentries Shop',   'value' => (int) ($carpentries_count ?? 0),     'onclick' => "window.location.href='" . base_url('personnel/carpentries') . "'"],
  ['tone' => 'tone-neutral', 'icon' => 'fa-wrench',        'label' => 'Maintenance',        'value' => (int) ($maintenance_count ?? 0),     'onclick' => "window.location.href='" . base_url('personnel/maintenance') . "'"],
  ['tone' => 'tone-gold',    'icon' => 'fa-helmet-safety', 'label' => 'Construction Workers', 'value' => (int) ($construction_count ?? 0),  'onclick' => "window.location.href='" . base_url('personnel/construction-workers') . "'"],
  ['tone' => 'tone-neutral', 'icon' => 'fa-file-contract', 'label' => 'Job Order Personnel', 'value' => (int) ($job_order_count ?? 0),      'onclick' => "window.location.href='" . base_url('personnel/on-job-order') . "'"],
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

<div class="stat-back-bar" id="personnelBackBar" style="display:none">
  <button type="button" class="stat-back-btn" onclick="resetPersonnelOverview()"><i class="bi bi-arrow-left"></i> Back to Overview</button>
  <h2 class="stat-list-title" id="personnelBackLabel"></h2>
</div>
<?php else: ?>
<div class="stat-back-bar">
  <a href="<?= base_url('personnel') ?>" class="stat-back-btn"><i class="bi bi-arrow-left"></i> Back to Overview</a>
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
      </div>
    </div>
  </div>
</div>

<div class="personnel-table-scroll">
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
        ?>
        <tr data-search="<?= esc(strtolower((string) ($p['full_name'] ?? '') . ' ' . ($p['emp_id'] ?? '') . ' ' . ($p['email'] ?? '') . ' ' . ($p['assigned_task'] ?? '') . ' ' . $departmentName)) ?>"
            data-department="<?= esc(strtolower($departmentName)) ?>"
            data-status="<?= esc(strtolower((string) ($statusValue ?? '')) ) ?>">
          <td><?= esc($p['full_name']) ?><br><small><?= esc($p['email']) ?></small></td>
          <td><?= esc($p['emp_id']) ?></td>
          <td><?= esc($departmentName) ?></td>
          <td><span class="status-badge <?= $isJobOrder ? 'status-pending' : 'status-active' ?>"><?= $isJobOrder ? 'Job Order' : 'Regular' ?></span></td>
          <td><?= esc($p['assigned_task'] ?? 'No current assignment') ?></td>
          <td><span class="status-badge status-<?= esc($statusClass) ?>"><?= esc($statusValue) ?></span></td>
          <td>
            <div class="action-buttons">
              <a class="icon-btn" href="<?= base_url('personnel/view/' . $p['id']) ?>" title="View Profile" aria-label="View <?= esc($p['full_name']) ?>"><i class="fa-solid fa-eye"></i></a>
              <button class="icon-btn" onclick="document.getElementById('editModal<?= $p['id'] ?>')  .style.display='flex'" title="Edit" aria-label="Edit <?= esc($p['full_name']) ?>"><i class="fa-solid fa-pen"></i></button>
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

        <!-- EDIT MODAL -->
        <div class="modal" id="editModal<?= $p['id'] ?>">
          <div class="modal-box">
            <h3>Edit Personnel</h3>
            <form action="<?= site_url('personnel/edit/'.$p['id']) ?>" method="post">
              <?= csrf_field() ?>
              <label>Employee ID <span class="required-mark">*</span></label>
              <input type="text" name="emp_id" value="<?= esc($p['emp_id']) ?>" required>
              <label>Full Name <span class="required-mark">*</span></label>
              <input type="text" name="full_name" value="<?= esc($p['full_name']) ?>" required>
              <label>Email</label>
              <input type="email" name="email" value="<?= esc($p['email']) ?>">
              <label>Department</label>
              <select name="department_id">
                <?php foreach ($departments as $d): ?>
                  <option value="<?= $d['id'] ?>" <?= $d['id']==$p['department_id']?'selected':'' ?>><?= esc($d['name']) ?></option>
                <?php endforeach; ?>
              </select>
              <label>Position</label>
              <select name="position">
                <option value="">Select Position</option>
                <?php foreach ($positionOptions as $positionOption): ?>
                  <option value="<?= esc($positionOption) ?>" <?= strtolower(trim((string) $p['position'])) === strtolower(trim((string) $positionOption)) ? 'selected' : '' ?>><?= esc($positionOption) ?></option>
                <?php endforeach; ?>
              </select>
              <label><?= esc($taskLabel) ?></label>
              <input type="text" name="assigned_task" value="<?= esc($p['assigned_task']) ?>">
              <label>Status</label>
              <select name="status">
                <option <?= $p['status']=='Active'?'selected':'' ?>>Active</option>
                <option <?= $p['status']=='On Leave'?'selected':'' ?>>On Leave</option>
                <option <?= $p['status']=='Inactive'?'selected':'' ?>>Inactive</option>
              </select>
              <div class="modal-actions">
                <button type="button" onclick="document.getElementById('editModal<?= $p['id'] ?>').style.display='none'">Cancel</button>
                <button type="submit" class="btn-maroon">Save Changes</button>
              </div>
            </form>
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

<script>
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

const personnelStatLabels = { 'Active': 'Active Personnel', 'On Leave': 'On Leave Personnel' };

// Active / On Leave stat cards act as quick filters into the table below —
// clicking one shows just that list, same as Tools Management, instead of
// leaving the whole stat-cards row sitting on top of the filtered table.
function filterPersonnelByStat(status) {
  document.getElementById('personnelSearch').value = '';
  document.getElementById('personnelDepartment').value = '';
  document.getElementById('personnelStatus').value = status;

  document.getElementById('personnelStatCards')?.style.setProperty('display', 'none');
  const backBar = document.getElementById('personnelBackBar');
  if (backBar) {
    backBar.style.display = 'flex';
    document.getElementById('personnelBackLabel').textContent = personnelStatLabels[status] ?? 'Filtered';
  }

  filterPersonnelTable();
  document.querySelector('.table-card').scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function resetPersonnelOverview() {
  document.getElementById('personnelStatCards')?.style.setProperty('display', '');
  document.getElementById('personnelBackBar').style.display = 'none';
  document.getElementById('personnelSearch').value = '';
  document.getElementById('personnelDepartment').value = '';
  document.getElementById('personnelStatus').value = '';
  filterPersonnelTable();
  document.getElementById('personnelStatCards')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
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
      <input type="text" name="emp_id" required>
      <label>Full Name <span class="required-mark">*</span></label>
      <input type="text" name="full_name" required>
      <label>Email</label>
      <input type="email" name="email">
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

