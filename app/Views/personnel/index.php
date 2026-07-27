<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<?php
$title = $title ?? 'Personnel Management';
$departments = $departments ?? [];
$personnel = $personnel ?? [];
$positionOptions = ['Guard', 'Driver', 'Janitor', 'Maintenance', 'Carpenter', 'Security', 'Office Staff', 'Administrator'];
if (!empty($personnel)) {
  foreach ($personnel as $person) {
    if (!empty($person['position'])) {
      $positionOptions[] = $person['position'];
    }
  }
}
$positionOptions = array_values(array_unique(array_filter($positionOptions)));
?>

<div class="page-header">
  <div>
    <h1><?= esc($title) ?></h1>
    <p class="page-subtitle">Manage university personnel, assignments, and operational responsibilities.</p>
  </div>
  <button class="btn-add" onclick="document.getElementById('addModal').style.display='flex'">+ Add Personnel</button>
</div>

<div class="table-card">
  <div class="table-toolbar">
  <div class="toolbar-left">
    <div class="toolbar-search">
      <button type="button" class="search-icon-btn" onclick="togglePersonnelSearch()" aria-label="Search">
        <i class="bi bi-search"></i>
      </button>
      <input type="text" id="personnelSearch" style="display:none;" class="toolbar-search-input hidden" placeholder="Search name, employee ID, position, or task" oninput="filterPersonnelTable()">
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
          <label for="personnelPosition">Position</label>
          <select id="personnelPosition" onchange="filterPersonnelTable()">
            <option value="">All Positions</option>
            <?php foreach (array_unique(array_column($personnel, 'position')) as $position): ?>
              <?php if (!empty($position)): ?>
                <option value="<?= esc($position) ?>"><?= esc($position) ?></option>
              <?php endif; ?>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
    </div>
  </div>
</div>

<table id="personnelTable" class="data-table">
  <thead>
    <tr>
      <th>Personnel Detail</th>
      <th>Employee ID</th>
      <th>Position</th>
      <th>Department</th>
      <th>Assigned Task</th>
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
        <tr data-search="<?= esc(strtolower((string) ($p['full_name'] ?? '') . ' ' . ($p['emp_id'] ?? '') . ' ' . ($p['email'] ?? '') . ' ' . ($p['assigned_task'] ?? ''))) ?>"
            data-department="<?= esc(strtolower($departmentName)) ?>"
            data-position="<?= esc(strtolower((string) ($p['position'] ?? ''))) ?>">
          <td><?= esc($p['full_name']) ?><br><small><?= esc($p['email']) ?></small></td>
          <td><?= esc($p['emp_id']) ?></td>
          <td><?= esc($p['position']) ?></td>
          <td><?= esc($departmentName) ?></td>
          <td><?= esc($p['assigned_task']) ?></td>
          <td><span class="status-badge status-<?= esc($statusClass) ?>"><?= esc($statusValue) ?></span></td>
          <td>
            <div class="action-buttons">
              <button class="icon-btn" onclick="document.getElementById('editModal<?= $p['id'] ?>')  .style.display='flex'" title="Edit"><i class="fa-solid fa-pen"></i></button>
              <a class="icon-btn delete" href="<?= base_url('personnel/delete/'.$p['id']) ?>" onclick="return confirm('Delete this personnel record?')" title="Delete"><i class="fa-solid fa-trash"></i></a>
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
              <label>Assigned Task</label>
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
      <tr><td colspan="7">No personnel records yet.</td></tr>
    <?php endif; ?>
  </tbody>
</table>
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
  const searchValue = document.getElementById('personnelSearch').value.toLowerCase();
  const departmentValue = document.getElementById('personnelDepartment').value.toLowerCase();
  const positionValue = document.getElementById('personnelPosition').value.toLowerCase();
  const rows = document.querySelectorAll('#personnelTable tbody tr[data-search]');

  rows.forEach(row => {
    const rowText = row.dataset.search || '';
    const departmentCell = row.dataset.department || '';
    const positionCell = row.dataset.position || '';

    const matchesSearch = !searchValue || rowText.includes(searchValue);
    const matchesDepartment = !departmentValue || departmentCell.includes(departmentValue);
    const matchesPosition = !positionValue || positionCell.includes(positionValue);

    row.style.display = matchesSearch && matchesDepartment && matchesPosition ? '' : 'none';
  });
}

function togglePersonnelSearch() {
  const input = document.getElementById('personnelSearch');
  if (!input) return;
  const isHidden = input.classList.contains('hidden') || input.style.display === 'none';
  if (isHidden) {
    input.style.display = 'inline-flex';
    input.classList.remove('hidden');
    input.focus();
  } else {
    input.style.display = 'none';
    input.classList.add('hidden');
  }
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
      <label>Assigned Task</label>
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

