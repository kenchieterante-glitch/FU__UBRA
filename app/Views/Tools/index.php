<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="page-header">
  <div>
    <h1>Tools Equipment Management</h1>
    <p class="page-subtitle">Manage all university operational assets, tools, equipment, and assigned resources.</p>
  </div>
  <button class="btn-add" onclick="document.getElementById('addModal').style.display='flex'">+ Add New Tool</button>
</div>

<?php if (session()->getFlashdata('success')): ?>
  <div class="alert-success"><?= session()->getFlashdata('success') ?></div>
<?php endif; ?>

<div class="stat-cards">
  <div class="stat-card">
    <h3>Total Tools</h3>
    <div class="value"><?= esc($total_tools ?? 0) ?></div>
  </div>
  <div class="stat-card">
    <h3>Available Tools</h3>
    <div class="value"><?= esc($available_tools ?? 0) ?></div>
  </div>
  <div class="stat-card">
    <h3>Borrowed Tools</h3>
    <div class="value"><?= esc($borrowed_tools ?? 0) ?></div>
  </div>
  <div class="stat-card">
    <h3>Needs Maintenance</h3>
    <div class="value"><?= esc($maintenance_tools ?? 0) ?></div>
  </div>
</div>

<table class="data-table">
  <thead>
    <tr>
      <th>Tools</th>
      <th>ID / Code</th>
      <th>Category</th>
      <th>Location</th>
      <th>Custodian</th>
      <th>Condition</th>
      <th>Availability</th>
      <th>Actions</th>
    </tr>
  </thead>
  <tbody>
    <?php if (!empty($tools)): ?>
      <?php foreach ($tools as $t): ?>
        <tr>
          <td class="tool-name-cell"><?= esc($t['asset_name']) ?></td>
          <td><?= esc($t['asset_code']) ?></td>
          <td><?= esc($t['category']) ?></td>
          <td><?= esc($t['location']) ?></td>
          <td><?= esc($t['custodian_name'] ?? 'Unassigned') ?></td>
          <td><span class="status-badge status-<?= strtolower($t['condition_status']) ?>"><?= esc($t['condition_status']) ?></span></td>
          <td><span class="status-badge status-<?= strtolower($t['availability']) ?>"><?= esc($t['availability']) ?></span></td>
          <td class="action-cell">
            <button onclick="document.getElementById('editModal<?= $t['id'] ?>').style.display='flex'">Edit</button>
            <a href="<?= base_url('tools/delete/'.$t['id']) ?>" onclick="return confirm('Delete this tool?')">Delete</a>
            <?php if ($t['availability'] == 'Available'): ?>
              <button onclick="document.getElementById('borrowModal<?= $t['id'] ?>').style.display='flex'">Borrow</button>
            <?php else: ?>
              <button onclick="document.getElementById('returnModal<?= $t['id'] ?>').style.display='flex'">Return</button>
            <?php endif; ?>
          </td>
        </tr>

        <!-- EDIT MODAL -->
        <div class="modal" id="editModal<?= $t['id'] ?>">
          <div class="modal-box">
            <h3>Edit Tool</h3>
            <form action="<?= base_url('tools/edit/'.$t['id']) ?>" method="post">
              <?= csrf_field() ?>
              <label>Tool Name</label>
              <input type="text" name="asset_name" value="<?= esc($t['asset_name']) ?>" required>
              <label>Tool Code</label>
              <input type="text" name="asset_code" value="<?= esc($t['asset_code']) ?>">
              <label>Category</label>
              <input type="text" name="category" value="<?= esc($t['category']) ?>">
              <label>Location</label>
              <input type="text" name="location" value="<?= esc($t['location']) ?>">
              <label>Custodian</label>
              <select name="custodian">
                <option value="">— Unassigned —</option>
                <?php foreach ($personnel as $person): ?>
                  <option value="<?= esc($person['full_name']) ?>" <?= ($t['custodian_name'] ?? '') === $person['full_name'] ? 'selected' : '' ?>><?= esc($person['full_name']) ?></option>
                <?php endforeach; ?>
              </select>
              <label>Condition</label>
              <select name="condition_status">
                <option <?= $t['condition_status']=='Excellent'?'selected':'' ?>>Excellent</option>
                <option <?= $t['condition_status']=='Good'?'selected':'' ?>>Good</option>
                <option <?= $t['condition_status']=='Fair'?'selected':'' ?>>Fair</option>
                <option <?= $t['condition_status']=='Poor'?'selected':'' ?>>Poor</option>
              </select>
              <div class="modal-actions">
                <button type="button" onclick="document.getElementById('editModal<?= $t['id'] ?>').style.display='none'">Cancel</button>
                <button type="submit" class="btn-maroon">Save Changes</button>
              </div>
            </form>
          </div>
        </div>

        <!-- BORROW MODAL -->
        <div class="modal" id="borrowModal<?= $t['id'] ?>">
          <div class="modal-box">
            <h3>Borrow Tool</h3>
            <form action="<?= base_url('tools/borrow/'.$t['id']) ?>" method="post">
              <?= csrf_field() ?>
              <label>Borrower</label>
              <select name="borrower" required>
                <option value="">— Select Borrower —</option>
                <?php foreach ($personnel as $person): ?>
                  <option value="<?= esc($person['full_name']) ?>"><?= esc($person['full_name']) ?></option>
                <?php endforeach; ?>
              </select>
              <label>Department</label>
              <input type="text" name="department">
              <label>Expected Return Date</label>
              <input type="date" name="expected_return">
              <label>Condition on Borrow</label>
              <select name="condition_on_borrow">
                <option>Excellent</option>
                <option>Good</option>
                <option>Fair</option>
                <option>Poor</option>
              </select>
              <div class="modal-actions">
                <button type="button" onclick="document.getElementById('borrowModal<?= $t['id'] ?>').style.display='none'">Cancel</button>
                <button type="submit" class="btn-maroon">Confirm Borrow</button>
              </div>
            </form>
          </div>
        </div>

        <!-- RETURN MODAL -->
        <div class="modal" id="returnModal<?= $t['id'] ?>">
          <div class="modal-box">
            <h3>Return Tool</h3>
            <form action="<?= base_url('tools/returnTool/'.$t['id']) ?>" method="post">
              <?= csrf_field() ?>
              <label>Returned By</label>
              <input type="text" name="returned_by" required>
              <label>Condition Upon Return</label>
              <select name="condition_on_return">
                <option>Excellent</option>
                <option>Good</option>
                <option>Fair</option>
                <option>Poor</option>
              </select>
              <label>Remarks</label>
              <input type="text" name="remarks">
              <div class="modal-actions">
                <button type="button" onclick="document.getElementById('returnModal<?= $t['id'] ?>').style.display='none'">Cancel</button>
                <button type="submit" class="btn-maroon">Confirm Return</button>
              </div>
            </form>
          </div>
        </div>

      <?php endforeach; ?>
    <?php else: ?>
      <tr><td colspan="8">No assets recorded yet.</td></tr>
    <?php endif; ?>
  </tbody>
</table>

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
        <?php foreach ($personnel as $person): ?>
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