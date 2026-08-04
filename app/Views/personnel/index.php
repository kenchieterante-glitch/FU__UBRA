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
$showStatusTabs = in_array($title, ['Drivers', 'Janitors', 'Carpentries Shop', 'Maintenance', 'Construction Workers'], true);
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
<div class="stat-cards" id="personnelStatCards">
  <div class="stat-card stat-card-clickable" onclick="window.location.href='<?= base_url('personnel') ?>'" role="button" tabindex="0">
    <h3>Total Personnel</h3>
    <div class="value"><?= esc((string) ((int) ($total_personnel_count ?? 0))) ?></div>
  </div>
  <div class="stat-card stat-card-clickable" onclick="window.location.href='<?= base_url('personnel/drivers') ?>'" role="button" tabindex="0">
    <h3>Drivers</h3>
    <div class="value"><?= esc((string) ((int) ($drivers_count ?? 0))) ?></div>
  </div>
  <div class="stat-card stat-card-clickable" onclick="window.location.href='<?= base_url('personnel/janitors') ?>'" role="button" tabindex="0">
    <h3>Janitors</h3>
    <div class="value"><?= esc((string) ((int) ($janitors_count ?? 0))) ?></div>
  </div>
  <div class="stat-card stat-card-clickable" onclick="window.location.href='<?= base_url('personnel/carpentries') ?>'" role="button" tabindex="0">
    <h3>Carpentries Shop</h3>
    <div class="value"><?= esc((string) ((int) ($carpentries_count ?? 0))) ?></div>
  </div>
  <div class="stat-card stat-card-clickable" onclick="window.location.href='<?= base_url('personnel/maintenance') ?>'" role="button" tabindex="0">
    <h3>Maintenance</h3>
    <div class="value"><?= esc((string) ((int) ($maintenance_count ?? 0))) ?></div>
  </div>
  <div class="stat-card stat-card-clickable" onclick="window.location.href='<?= base_url('personnel/construction-workers') ?>'" role="button" tabindex="0">
    <h3>Construction Workers</h3>
    <div class="value"><?= esc((string) ((int) ($construction_count ?? 0))) ?></div>
  </div>
  <div class="stat-card stat-card-clickable" onclick="filterPersonnelByStat('Active')" role="button" tabindex="0">
    <h3>Active</h3>
    <div class="value"><?= esc((string) ((int) ($active_count ?? 0))) ?></div>
  </div>
  <div class="stat-card stat-card-clickable" onclick="filterPersonnelByStat('On Leave')" role="button" tabindex="0">
    <h3>On Leave</h3>
    <div class="value"><?= esc((string) ((int) ($on_leave_count ?? 0))) ?></div>
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
        ?>
        <tr data-search="<?= esc(strtolower((string) ($p['full_name'] ?? '') . ' ' . ($p['emp_id'] ?? '') . ' ' . ($p['email'] ?? '') . ' ' . ($p['assigned_task'] ?? '') . ' ' . $departmentName)) ?>"
            data-department="<?= esc(strtolower($departmentName)) ?>"
            data-status="<?= esc(strtolower((string) ($statusValue ?? '')) ) ?>">
          <td><?= esc($p['full_name']) ?><br><small><?= esc($p['email']) ?></small></td>
          <td><?= esc($p['emp_id']) ?></td>
          <td><?= esc($departmentName) ?></td>
          <td><?= esc($p['assigned_task'] ?? 'No current assignment') ?></td>
          <td><span class="status-badge status-<?= esc($statusClass) ?>"><?= esc($statusValue) ?></span></td>
          <td>
            <div class="action-buttons">
              <button class="icon-btn" onclick="document.getElementById('editModal<?= $p['id'] ?>')  .style.display='flex'" title="Edit"><i class="fa-solid fa-pen"></i></button>
              <form method="post" action="<?= base_url('personnel/delete/'.$p['id']) ?>" onsubmit="return confirm('Archive this personnel record?')" style="display:contents;">
                <?= csrf_field() ?>
                <button type="submit" class="icon-btn delete" title="Archive"><i class="fa-solid fa-archive"></i></button>
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
              <label>Employee ID</label>
              <input type="text" name="emp_id" value="<?= esc($p['emp_id']) ?>" required>
              <label>Full Name</label>
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

      <?php endforeach; ?>
    <?php else: ?>
      <tr><td colspan="6">No personnel records yet.</td></tr>
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
      <label>Employee ID</label>
      <input type="text" name="emp_id" required>
      <label>Full Name</label>
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

