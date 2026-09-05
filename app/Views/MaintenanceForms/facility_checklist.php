<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<?php $checklistList = $checklists ?? []; ?>

<div class="page-header">
  <div>
    <h1><?= esc($title ?? 'Facility Maintenance Checklist') ?></h1>
    <p class="page-subtitle">F-FAC-PMP-FMC-001 — General building, fire &amp; emergency, lab, and electrical safety inspections.</p>
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
        <th>Inspector</th>
        <th>Building/Area</th>
        <th>Date</th>
        <th>Inspection Type</th>
        <th>Overall Condition</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($checklistList)): ?>
        <?php foreach ($checklistList as $c): ?>
          <?php
            $condClass = match ($c['overall_condition'] ?? '') {
                'Excellent'     => 'badge green',
                'Satisfactory'  => 'badge amber',
                'Unsatisfactory'=> 'badge red',
                default         => 'badge blue',
            };
          ?>
          <tr>
            <td><?= esc($c['inspector'] ?? '—') ?></td>
            <td><?= esc($c['building_area'] ?? '—') ?></td>
            <td><?= !empty($c['inspection_date']) ? esc(date('M j, Y', strtotime($c['inspection_date']))) : '—' ?></td>
            <td><?= esc($c['inspection_type'] ?? '—') ?></td>
            <td><span class="status-badge <?= $condClass ?>"><?= esc($c['overall_condition'] ?? 'Not yet rated') ?></span></td>
            <td>
              <div class="action-buttons">
                <button type="button" class="icon-btn" onclick="document.getElementById('viewModal<?= $c['id'] ?>').style.display='flex'" title="View/Edit" aria-label="View checklist for <?= esc($c['building_area'] ?? 'this area') ?>"><i class="fa-solid fa-eye"></i></button>
                <form method="post" action="<?= base_url('maintenance-forms/facility/delete/'.$c['id']) ?>" onsubmit="return confirm('Archive this checklist?')" style="display:contents;">
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
      <h3><?= esc($c['building_area'] ?? 'Facility Inspection') ?> — <?= !empty($c['inspection_date']) ? esc(date('M j, Y', strtotime($c['inspection_date']))) : '—' ?></h3>
      <p class="mf-meta">Inspector: <strong><?= esc($c['inspector'] ?? '—') ?></strong> &middot; Type: <strong><?= esc($c['inspection_type'] ?? '—') ?></strong></p>

      <form method="post" action="<?= base_url('maintenance-forms/facility/updateItems/'.$c['id']) ?>">
        <?= csrf_field() ?>
        <?php $currentSection = null; ?>
        <?php foreach ($c['items'] as $item): ?>
          <?php if ($item['section'] !== $currentSection): $currentSection = $item['section']; ?>
            <div class="mf-section-title"><?= esc($currentSection) ?></div>
          <?php endif; ?>
          <div class="mf-item-row">
            <div class="mf-item-label"><strong><?= esc($item['item_code']) ?>.</strong> <?= esc($item['item_label']) ?></div>
            <div class="mf-item-controls">
              <select name="items[<?= $item['id'] ?>][rating]">
                <option value="">—</option>
                <?php foreach (['C', 'MI', 'MJ', 'N/A'] as $r): ?>
                  <option value="<?= $r ?>" <?= $item['rating'] === $r ? 'selected' : '' ?>><?= $r ?></option>
                <?php endforeach; ?>
              </select>
              <input type="text" name="items[<?= $item['id'] ?>][corrective_action]" value="<?= esc($item['corrective_action'] ?? '') ?>" placeholder="Corrective action / comments">
            </div>
          </div>
        <?php endforeach; ?>
        <div class="modal-actions">
          <button type="button" onclick="document.getElementById('viewModal<?= $c['id'] ?>').style.display='none'">Close</button>
          <button type="submit" class="btn-maroon">Save Items</button>
        </div>
      </form>

      <div class="mf-section-title">Inspection Summary &amp; Action Plan</div>
      <form method="post" action="<?= base_url('maintenance-forms/facility/updateHeader/'.$c['id']) ?>">
        <?= csrf_field() ?>
        <label>Overall Condition</label>
        <select name="overall_condition">
          <option value="">— Select —</option>
          <?php foreach (['Excellent', 'Satisfactory', 'Unsatisfactory'] as $cond): ?>
            <option value="<?= $cond ?>" <?= ($c['overall_condition'] ?? '') === $cond ? 'selected' : '' ?>><?= $cond ?></option>
          <?php endforeach; ?>
        </select>
        <label>Summary of Findings</label>
        <textarea name="summary_findings" rows="2"><?= esc($c['summary_findings'] ?? '') ?></textarea>
        <label>Corrective Action Plan</label>
        <textarea name="corrective_action_plan" rows="2"><?= esc($c['corrective_action_plan'] ?? '') ?></textarea>
        <label>Reviewed by (Maintenance Head)</label>
        <input type="text" name="reviewed_by" value="<?= esc($c['reviewed_by'] ?? '') ?>">
        <label>Review Date</label>
        <input type="date" name="reviewed_date" value="<?= esc($c['reviewed_date'] ?? '') ?>">
        <label>Approved by (Facilities Director)</label>
        <input type="text" name="approved_by" value="<?= esc($c['approved_by'] ?? '') ?>">
        <div class="modal-actions">
          <button type="submit" class="btn-maroon">Save Summary</button>
        </div>
      </form>
    </div>
  </div>
<?php endforeach; ?>

<div class="modal" id="addModal">
  <div class="modal-box">
    <h3>New Facility Inspection</h3>
    <form action="<?= base_url('maintenance-forms/facility/add') ?>" method="post">
      <?= csrf_field() ?>
      <label>Inspector</label>
      <input type="text" name="inspector" required>
      <label>Building/Area</label>
      <input type="text" name="building_area" required>
      <label>Date of Inspection</label>
      <input type="date" name="inspection_date" value="<?= date('Y-m-d') ?>">
      <label>Inspection Type</label>
      <input type="text" name="inspection_type" placeholder="e.g. Routine, Follow-up">
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
