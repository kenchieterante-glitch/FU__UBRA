<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="page-header">
  <div>
    <h1><?= esc($title) ?></h1>
    <p class="page-subtitle">Manage all university vehicles, drivers, and maintenance status.</p>
  </div>
  <button class="btn-add" onclick="document.getElementById('addModal').style.display='flex'">+ Add Vehicle</button>
</div>

<?php if (session()->getFlashdata('success')): ?>
  <div class="alert-success"><i class="fa-solid fa-circle-check"></i> <?= session()->getFlashdata('success') ?></div>
<?php endif; ?>

<div class="stat-cards">
  <div class="stat-card">
    <h3>Total Vehicles</h3>
    <div class="value"><?= count($vehicles) ?></div>
  </div>
  <div class="stat-card">
    <h3>Available</h3>
    <div class="value"><?= count(array_filter($vehicles, fn($v) => $v['availability'] == 'Available')) ?></div>
  </div>
  <div class="stat-card">
    <h3>In Use</h3>
    <div class="value"><?= count(array_filter($vehicles, fn($v) => $v['availability'] == 'In Use')) ?></div>
  </div>
  <div class="stat-card">
    <h3>Needs Maintenance</h3>
    <div class="value"><?= count(array_filter($vehicles, fn($v) => $v['inspection_status'] == 'Expired' || $v['availability'] == 'Maintenance')) ?></div>
  </div>
</div>

<table class="data-table">
  <thead>
    <tr>
      <th>Vehicle Detail</th>
      <th>Plate Number</th>
      <th>Type</th>
      <th>Driver</th>
      <th>Department</th>
      <th>GPS Status</th>
      <th>Inspection</th>
      <th>Availability</th>
      <th>Actions</th>
    </tr>
  </thead>
  <tbody>
    <?php if (!empty($vehicles)): ?>
      <?php foreach ($vehicles as $v): ?>
        <tr>
          <td><?= esc($v['vehicle_name']) ?><br><small></small></td>
          <td><?= esc($v['plate_no']) ?></td>
          <td><?= esc($v['type']) ?></td>
          <td><?= esc($v['driver_name'] ?? 'Unassigned') ?></td>
          <td><?= esc($v['department_name'] ?? 'Unassigned') ?></td>
          <td><span class="status-badge status-<?= strtolower($v['gps_status']) ?>"><?= esc($v['gps_status']) ?></span></td>
          <td><span class="status-badge status-<?= strtolower($v['inspection_status']) ?>"><?= esc($v['inspection_status']) ?></span></td>
          <td><span class="status-badge status-<?= strtolower($v['availability']) ?>"><?= esc($v['availability']) ?></span></td>
          <td class="action-cell">
            <button onclick="document.getElementById('editModal<?= $v['id'] ?>').style.display='flex'">Edit</button>
            <a href="<?= base_url('vehicles/delete/'.$v['id']) ?>" onclick="return confirm('Archive this vehicle?')">Archive</a>
          </td>
        </tr>

        <!-- EDIT MODAL -->
        <div class="modal" id="editModal<?= $v['id'] ?>">
          <div class="modal-box">
            <h3>Edit Vehicle</h3>
            <form action="<?= base_url('vehicles/edit/'.$v['id']) ?>" method="post">
              <?= csrf_field() ?>
              <label>Vehicle Name / Model</label>
              <input type="text" name="vehicle_name" value="<?= esc($v['vehicle_name']) ?>" required>
              <label>Plate Number</label>
              <input type="text" name="plate_no" value="<?= esc($v['plate_no']) ?>" required>
              <label>Type</label>
              <input type="text" name="type" value="<?= esc($v['type']) ?>">
              <label>Driver</label>
              <select name="driver_id">
                <option value="">— Unassigned —</option>
                <?php foreach ($personnel as $p): ?>
                  <option value="<?= $p['id'] ?>" <?= $p['id']==$v['driver_id']?'selected':'' ?>><?= esc($p['full_name']) ?></option>
                <?php endforeach; ?>
              </select>
              <label>Department</label>
              <select name="department_id">
                <option value="">— Unassigned —</option>
                <?php foreach ($departments as $d): ?>
                  <option value="<?= $d['id'] ?>" <?= $d['id']==$v['department_id']?'selected':'' ?>><?= esc($d['name']) ?></option>
                <?php endforeach; ?>
              </select>
              <label>GPS Status</label>
              <select name="gps_status">
                <option <?= $v['gps_status']=='Online'?'selected':'' ?>>Online</option>
                <option <?= $v['gps_status']=='Offline'?'selected':'' ?>>Offline</option>
              </select>
              <label>Inspection Status</label>
              <select name="inspection_status">
                <option <?= $v['inspection_status']=='Completed'?'selected':'' ?>>Completed</option>
                <option <?= $v['inspection_status']=='Due Soon'?'selected':'' ?>>Due Soon</option>
                <option <?= $v['inspection_status']=='Expired'?'selected':'' ?>>Expired</option>
              </select>
              <label>Availability</label>
              <select name="availability">
                <option <?= $v['availability']=='Available'?'selected':'' ?>>Available</option>
                <option <?= $v['availability']=='In Use'?'selected':'' ?>>In Use</option>
                <option <?= $v['availability']=='Maintenance'?'selected':'' ?>>Maintenance</option>
                <option <?= $v['availability']=='Reserved'?'selected':'' ?>>Reserved</option>
                <option <?= $v['availability']=='Inactive'?'selected':'' ?>>Inactive</option>
              </select>
              <div class="modal-actions">
                <button type="button" onclick="document.getElementById('editModal<?= $v['id'] ?>').style.display='none'">Cancel</button>
                <button type="submit" class="btn-maroon">Save Changes</button>
              </div>
            </form>
          </div>
        </div>

      <?php endforeach; ?>
    <?php else: ?>
      <tr><td colspan="9">No vehicles recorded yet.</td></tr>
    <?php endif; ?>
  </tbody>
</table>

<!-- ADD MODAL -->
<div class="modal" id="addModal">
  <div class="modal-box">
    <h3>Add Vehicle</h3>
    <form action="<?= base_url('vehicles/add') ?>" method="post">
      <?= csrf_field() ?>
      <label>Vehicle Name / Model</label>
      <input type="text" name="vehicle_name" required>
      <label>Plate Number</label>
      <input type="text" name="plate_no" required>
      <label>Type</label>
      <input type="text" name="type">
      <label>Driver</label>
      <select name="driver_id">
        <option value="">— Unassigned —</option>
        <?php foreach ($personnel as $p): ?>
          <option value="<?= $p['id'] ?>"><?= esc($p['full_name']) ?></option>
        <?php endforeach; ?>
      </select>
      <label>Department</label>
      <select name="department_id">
        <option value="">— Unassigned —</option>
        <?php foreach ($departments as $d): ?>
          <option value="<?= $d['id'] ?>"><?= esc($d['name']) ?></option>
        <?php endforeach; ?>
      </select>
      <label>GPS Status</label>
      <select name="gps_status">
        <option>Online</option>
        <option selected>Offline</option>
      </select>
      <label>Inspection Status</label>
      <select name="inspection_status">
        <option>Completed</option>
        <option selected>Due Soon</option>
        <option>Expired</option>
      </select>
      <label>Availability</label>
      <select name="availability">
        <option selected>Available</option>
        <option>In Use</option>
        <option>Maintenance</option>
        <option>Reserved</option>
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
