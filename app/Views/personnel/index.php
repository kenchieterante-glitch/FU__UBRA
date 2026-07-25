<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="page-header">
  <div>
    <h1><?= esc($title) ?></h1>
    <p class="page-subtitle">Manage university personnel, assignments, and operational responsibilities.</p>
  </div>
  <button class="btn-add" onclick="document.getElementById('addModal').style.display='flex'">+ Add Personnel</button>
</div>

<?php if (session()->getFlashdata('success')): ?>
  <div class="alert-success"><i class="fa-solid fa-circle-check"></i> <?= session()->getFlashdata('success') ?></div>
<?php endif; ?>

<table class="data-table">
  <thead>
    <tr>
      <th>Personnel Detail</th>
      <th>ID & Position</th>
      <th>Department</th>
      <th>Assigned Task</th>
      <th>Status</th>
      <th>Actions</th>
    </tr>
  </thead>
  <tbody>
    <?php if (!empty($personnel)): ?>
      <?php foreach ($personnel as $p): ?>
        <tr>
          <td><?= esc($p['full_name']) ?><br><small><?= esc($p['email']) ?></small></td>
          <td><?= esc($p['emp_id']) ?><br><small><?= esc($p['position']) ?></small></td>
          <td>
            <?php foreach ($departments as $d): if ($d['id'] == $p['department_id']) echo esc($d['name']); endforeach; ?>
          </td>
          <td><?= esc($p['assigned_task']) ?></td>
          <td><span class="status-badge status-<?= strtolower($p['status']) ?>"><?= esc($p['status']) ?></span></td>
          <td>
            <button onclick="document.getElementById('editModal<?= $p['id'] ?>').style.display='flex'">Edit</button>
            <a href="<?= base_url('personnel/delete/'.$p['id']) ?>" onclick="return confirm('Delete this personnel record?')">Delete</a>
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
              <input type="text" name="position" value="<?= esc($p['position']) ?>">
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
      <tr><td colspan="6">No personnel records yet.</td></tr>
    <?php endif; ?>
  </tbody>
</table>

<!-- ADD MODAL -->
<div class="modal" id="addModal">
  <div class="modal-box">
    <h3>Add Personnel</h3>
    <form action="<?= site_url('personnel/add') ?>" method="post">
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
      <input type="text" name="position">
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

