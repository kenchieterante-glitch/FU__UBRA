<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<?php $toolList = $tools ?? []; $personnelList = $personnel ?? []; ?>

<div class="page-header">
  <div>
    <h1>Tools Equipment Management</h1>
    <p class="page-subtitle">Manage all university operational assets, tools, equipment, and assigned resources.</p>
  </div>
  <button class="btn-add" onclick="document.getElementById('addModal').style.display='flex'">+ Add New Tool</button>
</div>

<div class="stat-cards">
  <div class="stat-card">
    <h3>Total Tools</h3>
    <div class="value"><?= esc((string) ((int) ($total_tools ?? 0))) ?></div>
  </div>
  <div class="stat-card">
    <h3>Available Tools</h3>
    <div class="value"><?= esc((string) ((int) ($available_tools ?? 0))) ?></div>
  </div>
  <div class="stat-card">
    <h3>Borrowed Tools</h3>
    <div class="value"><?= esc((string) ((int) ($borrowed_tools ?? 0))) ?></div>
  </div>
  <div class="stat-card">
    <h3>Needs Maintenance</h3>
    <div class="value"><?= esc((string) ((int) ($maintenance_tools ?? 0))) ?></div>
  </div>
</div>

<div class="table-card">
  <div class="table-toolbar">
    <div class="toolbar-left">
      <div class="toolbar-search">
        <input type="text" id="toolsSearch" class="search-box" placeholder="Search tool, code, category, location" oninput="filterToolsTable()">
        <i class="bi bi-search search-icon"></i>
      </div>
    </div>
    <div class="toolbar-right">
      <div class="filter-menu-wrapper">
        <button type="button" class="filter-btn" onclick="toggleToolsFilterMenu()" aria-label="Open filters">
          <i class="bi bi-funnel"></i>
        </button>
        <div class="filter-popup" id="toolsFilterPopup">
          <div class="filter-popup-title">Filter</div>
          <div class="filter-row">
            <label for="toolsCategory">Category</label>
            <select id="toolsCategory" onchange="filterToolsTable()">
              <option value="">All Categories</option>
              <?php foreach (array_unique(array_column($toolList, 'category')) as $category): ?>
                <?php if (!empty($category)): ?>
                  <option value="<?= esc($category) ?>"><?= esc($category) ?></option>
                <?php endif; ?>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="filter-row">
            <label for="toolsAvailability">Availability</label>
            <select id="toolsAvailability" onchange="filterToolsTable()">
              <option value="">All Availability</option>
              <option value="Available">Available</option>
              <option value="Borrowed">Borrowed</option>
              <option value="Maintenance">Maintenance</option>
            </select>
          </div>
        </div>
      </div>
    </div>
  </div>

  <table id="toolsTable" class="data-table">
  <thead>
    <tr>
      <th>Tools</th>
      <th>Code</th>
      <th>Category</th>
      <th>Location</th>
      <th>Custodian</th>
      <th>Condition</th>
      <th>Status</th>
      <th>Availability</th>
    </tr>
  </thead>
  <tbody>
    <?php if (!empty($toolList)): ?>
      <?php foreach ($toolList as $t): ?>
        <tr>
          <td class="tool-name-cell"><?= esc($t['asset_name']) ?></td>
          <td><?= esc($t['asset_code']) ?></td>
          <td><?= esc($t['category']) ?></td>
          <td><?= esc($t['location']) ?></td>
          <td><?= esc($t['custodian_name'] ?? 'Unassigned') ?></td>
          <td><span class="status-badge status-<?= strtolower($t['condition_status']) ?>"><?= esc($t['condition_status']) ?></span></td>
          <td><span class="status-badge status-<?= strtolower($t['availability']) ?>"><?= esc($t['availability']) ?></span></td>
          <td>
            <?php if (($t['availability'] ?? '') === 'Borrowed'): ?>
              <span class="status-badge status-borrowed">In Use</span>
            <?php elseif (($t['availability'] ?? '') === 'Maintenance'): ?>
              <span class="status-badge status-maintenance">Under Repair</span>
            <?php else: ?>
              <span class="status-badge status-available">Ready</span>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
    <?php else: ?>
      <tr><td colspan="8">No assets recorded yet.</td></tr>
    <?php endif; ?>
  </tbody>
</table>
</div>

<script>
function filterToolsTable() {
  const search = document.getElementById('toolsSearch').value.toLowerCase();
  const category = document.getElementById('toolsCategory').value.toLowerCase();
  const availability = document.getElementById('toolsAvailability').value.toLowerCase();
  document.querySelectorAll('#toolsTable tbody tr').forEach(row => {
    const text = row.innerText.toLowerCase();
    const categoryText = row.children[2]?.innerText.toLowerCase() ?? '';
    const availabilityText = row.children[6]?.innerText.toLowerCase() ?? '';
    const matches = text.includes(search)
      && (!category || categoryText === category)
      && (!availability || availabilityText === availability);
    row.style.display = matches ? '' : 'none';
  });
}

function toggleToolsFilterMenu() {
  const popup = document.getElementById('toolsFilterPopup');
  if (!popup) return;
  popup.classList.toggle('visible');
}

document.addEventListener('click', e => {
  const wrapper = e.target.closest('.filter-menu-wrapper');
  if (!wrapper) {
    document.getElementById('toolsFilterPopup')?.classList.remove('visible');
  }
});
</script>

<!-- ADD MODAL -->
<div class="modal" id="addModal">
  <div class="modal-box">
    <h3>Add New Tool</h3>
    <form action="<?= base_url('tools/add') ?>" method="post">
      <?= csrf_field() ?>
      <label>Tool Name</label>
      <input type="text" name="asset_name" required>
      <label>Tool Code</label>
      <input type="text" name="asset_code">
      <label>Category</label>
      <input type="text" name="category">
      <label>Location</label>
      <input type="text" name="location">
      <label>Custodian</label>
      <select name="custodian">
        <option value="">— Unassigned —</option>
        <?php foreach ($personnelList as $person): ?>
          <option value="<?= esc($person['full_name']) ?>"><?= esc($person['full_name']) ?></option>
        <?php endforeach; ?>
      </select>
      <label>Condition</label>
      <select name="condition_status">
        <option>Excellent</option>
        <option>Good</option>
        <option>Fair</option>
        <option>Poor</option>
      </select>
      <div class="modal-actions">
        <button type="button" onclick="document.getElementById('addModal').style.display='none'">Cancel</button>
        <button type="submit" class="btn-maroon">Save</button>
      </div>
    </form>
  </div>
</div>

<?= $this->endSection() ?>