<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<?php $checklistList = $checklists ?? []; ?>

<div class="page-header">
  <div>
    <h1><?= esc($title ?? 'Vehicle Maintenance Inspection Checklist') ?></h1>
    <p class="page-subtitle">F-FAC-PMP-VMI-004 — Interior, exterior, service &amp; operation, and accessories inspection.</p>
  </div>
  <button class="btn-add" onclick="document.getElementById('addModal').style.display='flex'">+ New Inspection</button>
</div>

<div class="table-card">
  <div class="table-toolbar">
    <div class="toolbar-left">
      <div class="toolbar-search">
        <input type="text" id="mfSearch" class="search-box" placeholder="Search inspections…" oninput="filterMfTable('mfTable','mfSearch')">
        <i class="bi bi-search search-icon"></i>
      </div>
    </div>
  </div>

  <div class="tools-table-scroll">
  <table id="mfTable" class="data-table">
    <thead>
      <tr>
        <th>Vehicle Type</th>
        <th>Plate No.</th>
        <th>Mechanic/Inspector</th>
        <th>Date</th>
        <th>Next PM Schedule</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($checklistList)): ?>
        <?php foreach ($checklistList as $c): ?>
          <tr>
            <td><?= esc($c['vehicle_type'] ?? '—') ?></td>
            <td><?= esc($c['plate_no'] ?? '—') ?></td>
            <td><?= esc($c['mechanic_inspector'] ?? '—') ?></td>
            <td><?= !empty($c['inspection_date']) ? esc(date('M j, Y', strtotime($c['inspection_date']))) : '—' ?></td>
            <td><?= !empty($c['next_pm_schedule']) ? esc(date('M j, Y', strtotime($c['next_pm_schedule']))) : '—' ?></td>
            <td>
              <div class="action-buttons">
                <button type="button" class="icon-btn" onclick="document.getElementById('viewModal<?= $c['id'] ?>').style.display='flex'" title="View/Edit" aria-label="View checklist for <?= esc($c['plate_no'] ?? 'this vehicle') ?>"><i class="fa-solid fa-eye"></i></button>
                <form method="post" action="<?= base_url('maintenance-forms/vehicle-checklist/delete/'.$c['id']) ?>" onsubmit="return confirm('Archive this checklist?')" style="display:contents;">
                  <?= csrf_field() ?>
                  <button type="submit" class="icon-btn delete" title="Archive"><i class="fa-solid fa-archive"></i></button>
                </form>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr><td colspan="6">No inspections recorded yet.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
  </div>
</div>

<?php foreach ($checklistList as $c): ?>
  <div class="modal" id="viewModal<?= $c['id'] ?>">
    <div class="modal-box mf-big-box">
      <h3><?= esc($c['vehicle_type'] ?? 'Vehicle') ?> — <?= esc($c['plate_no'] ?? '—') ?></h3>
      <p class="mf-meta">Mechanic/Inspector: <strong><?= esc($c['mechanic_inspector'] ?? '—') ?></strong> &middot; Odometer: <strong><?= esc($c['odometer_reading'] ?? '—') ?></strong></p>

      <form method="post" action="<?= base_url('maintenance-forms/vehicle-checklist/updateItems/'.$c['id']) ?>">
        <?= csrf_field() ?>
        <?php $currentSection = null; ?>
        <?php foreach ($c['items'] as $item): ?>
          <?php if ($item['section'] !== $currentSection): $currentSection = $item['section']; ?>
            <div class="mf-section-title"><?= esc($currentSection) ?></div>
          <?php endif; ?>
          <div class="mf-item-row">
            <div class="mf-item-label"><?= esc($item['item_label']) ?></div>
            <div class="mf-item-controls">
              <select name="items[<?= $item['id'] ?>][response]">
                <option value="">—</option>
                <option value="Yes" <?= $item['response'] === 'Yes' ? 'selected' : '' ?>>Yes</option>
                <option value="No" <?= $item['response'] === 'No' ? 'selected' : '' ?>>No</option>
              </select>
              <input type="text" name="items[<?= $item['id'] ?>][remarks]" value="<?= esc($item['remarks'] ?? '') ?>" placeholder="Remarks">
            </div>
          </div>
        <?php endforeach; ?>
        <div class="modal-actions">
          <button type="button" onclick="document.getElementById('viewModal<?= $c['id'] ?>').style.display='none'">Close</button>
          <button type="submit" class="btn-maroon">Save Items</button>
        </div>
      </form>
    </div>
  </div>
<?php endforeach; ?>

<div class="modal" id="addModal">
  <div class="modal-box">
    <h3>New Vehicle Inspection</h3>
    <form action="<?= base_url('maintenance-forms/vehicle-checklist/add') ?>" method="post">
      <?= csrf_field() ?>
      <label>Vehicle Type</label>
      <input type="text" name="vehicle_type" required>
      <label>Plate No.</label>
      <input type="text" name="plate_no" required>
      <label>Odometer Reading</label>
      <input type="text" name="odometer_reading">
      <label>Mechanic/Inspector</label>
      <input type="text" name="mechanic_inspector">
      <label>Date</label>
      <input type="date" name="inspection_date" value="<?= date('Y-m-d') ?>">
      <label>Next PM Schedule</label>
      <input type="date" name="next_pm_schedule">
      <div class="modal-actions">
        <button type="button" onclick="document.getElementById('addModal').style.display='none'">Cancel</button>
        <button type="submit" class="btn-maroon">Create</button>
      </div>
    </form>
  </div>
</div>

<script>
function filterMfTable(tableId, searchId) {
  const search = document.getElementById(searchId).value.toLowerCase();
  document.querySelectorAll('#' + tableId + ' tbody tr').forEach(row => {
    row.style.display = row.innerText.toLowerCase().includes(search) ? '' : 'none';
  });
}
</script>

<?= $this->endSection() ?>
