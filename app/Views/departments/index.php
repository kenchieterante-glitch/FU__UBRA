<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
  $title = $title ?? 'Departments';
  $departments = $departments ?? [];
?>

<div class="page-header">
  <div>
    <h1><?= esc($title) ?></h1>
    <p class="page-subtitle">Manage all university departments.</p>
  </div>
  <button class="btn-add" onclick="document.getElementById('addModal').style.display='flex'">+ Add Department</button>
</div>

<table class="data-table">
  <thead>
    <tr>
      <th>Department Name</th>
      <th>Description</th>
      <th>Actions</th>
    </tr>
  </thead>
  <tbody>
    <?php if (!empty($departments)): ?>
      <?php foreach ($departments as $d): ?>
        <tr>
          <td><?= esc($d['name']) ?></td>
          <td><?= esc($d['description'] ?? 'No description') ?></td>
          <td class="action-cell">
            <button onclick="document.getElementById('editModal<?= $d['id'] ?>').style.display='flex'">Edit</button>
            <a href="<?= base_url('departments/delete/'.$d['id']) ?>" onclick="return confirm('Delete this department?')">Delete</a>
          </td>
        </tr>

        <!-- EDIT MODAL -->
        <div class="modal" id="editModal<?= $d['id'] ?>">
          <div class="modal-box">
            <h3>Edit Department</h3>
            <form action="<?= base_url('departments/edit/'.$d['id']) ?>" method="post">
              <?= csrf_field() ?>
              <label>Department Name</label>
              <input type="text" name="name" value="<?= esc($d['name']) ?>" required>
              <label>Description</label>
              <textarea name="description" rows="2"><?= esc($d['description']) ?></textarea>
              <div class="modal-actions">
                <button type="button" onclick="document.getElementById('editModal<?= $d['id'] ?>').style.display='none'">Cancel</button>
                <button type="submit" class="btn-maroon">Save Changes</button>
              </div>
            </form>
          </div>
        </div>

      <?php endforeach; ?>
    <?php else: ?>
      <tr><td colspan="3">No departments recorded yet.</td></tr>
    <?php endif; ?>
  </tbody>
</table>

<!-- ADD MODAL -->
<div class="modal" id="addModal">
  <div class="modal-box">
    <h3>Add Department</h3>
    <form action="<?= base_url('departments/add') ?>" method="post">
      <?= csrf_field() ?>
      <label>Department Name</label>
      <input type="text" name="name" required>
      <label>Description</label>
      <textarea name="description" rows="2"></textarea>
      <div class="modal-actions">
        <button type="button" onclick="document.getElementById('addModal').style.display='none'">Cancel</button>
        <button type="submit" class="btn-maroon">Save</button>
      </div>
    </form>
  </div>
</div>

<?= $this->endSection() ?>
