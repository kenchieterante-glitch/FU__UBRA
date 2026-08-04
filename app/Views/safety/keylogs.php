<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
  $title = $title ?? 'Keylogs';
  $key_logs_json = $key_logs_json ?? '[]';
?>

<link rel="stylesheet" href="<?= base_url('Assets/css/safety.css') . '?v=' . @filemtime(FCPATH.'Assets/css/safety.css') ?>">

<div class="page-header">
  <div>
    <h1><?= esc($title) ?></h1>
    <p class="page-subtitle">Record of key check-ins, assignments, and custodianship logs.</p>
  </div>
  <div class="header-actions" style="display:flex;gap:.6rem;">
    <button class="btn-outline" onclick="document.getElementById('scanReturnModal').style.display='flex'"><i class="bi bi-box-arrow-in-left"></i> Scan to Return</button>
    <button class="btn-add" onclick="document.getElementById('scanBorrowModal').style.display='flex'">+ Scan to Borrow</button>
  </div>
</div>

<div class="table-card">
  <div class="table-wrap">
    <table class="sj-table">
      <thead>
        <tr><th>Key Log #</th><th>Name</th><th>Department</th><th>Key</th><th>Issued</th><th>Returned</th><th>Status</th><th>Guard</th></tr>
      </thead>
      <tbody id="keylogBody"></tbody>
    </table>
  </div>
</div>

<!-- SCAN TO BORROW -->
<div class="modal" id="scanBorrowModal">
  <div class="modal-box">
    <h3>Scan to Borrow</h3>
    <form action="<?= base_url('safety/keylogs/scan-borrow') ?>" method="post">
      <?= csrf_field() ?>
      <label>Employee ID (scan or type) <span style="color:var(--maroon)">*</span></label>
      <input type="text" name="borrower_id" id="borrowEmpId" placeholder="Scan badge or type Employee ID" autocomplete="off" required autofocus>
      <label>Full Name <span style="color:var(--maroon)">*</span></label>
      <input type="text" name="full_name" id="borrowFullName" required>
      <label>Department</label>
      <input type="text" name="department" id="borrowDept">
      <label>Key / Item <span style="color:var(--maroon)">*</span></label>
      <input type="text" name="key_item" placeholder="e.g. Room 204 Master Key" required>
      <p id="borrowLookupMsg" style="font-size:12px;margin-top:8px;color:var(--maroon-dark, #5a0909);"></p>
      <div class="modal-actions">
        <button type="button" onclick="document.getElementById('scanBorrowModal').style.display='none'">Cancel</button>
        <button type="submit" class="btn-maroon">Log Borrow</button>
      </div>
    </form>
  </div>
</div>

<!-- SCAN TO RETURN -->
<div class="modal" id="scanReturnModal">
  <div class="modal-box">
    <h3>Scan to Return</h3>
    <form action="<?= base_url('safety/keylogs/scan-return') ?>" method="post">
      <?= csrf_field() ?>
      <label>Borrower ID or Key Log # (scan or type) <span style="color:var(--maroon)">*</span></label>
      <input type="text" name="identifier" placeholder="Scan badge or type e.g. KL-004" autocomplete="off" required>
      <div class="modal-actions">
        <button type="button" onclick="document.getElementById('scanReturnModal').style.display='none'">Cancel</button>
        <button type="submit" class="btn-maroon">Log Return</button>
      </div>
    </form>
  </div>
</div>

<script>
// Auto-fill name/department when a registered Employee ID is scanned/typed
// into the Borrow modal — falls back to manual entry for visitors/guests.
const borrowEmpIdInput = document.getElementById('borrowEmpId');
let lookupTimer = null;
borrowEmpIdInput.addEventListener('input', () => {
  clearTimeout(lookupTimer);
  const empId = borrowEmpIdInput.value.trim();
  const msg = document.getElementById('borrowLookupMsg');
  if (!empId) { msg.textContent = ''; return; }
  lookupTimer = setTimeout(async () => {
    try {
      const res = await fetch('<?= base_url('safety/keylogs/lookup/') ?>' + encodeURIComponent(empId));
      if (!res.ok) { msg.textContent = 'No personnel record found — enter name manually.'; return; }
      const data = await res.json();
      document.getElementById('borrowFullName').value = data.full_name || '';
      document.getElementById('borrowDept').value = data.department || '';
      msg.textContent = 'Matched: ' + data.full_name;
    } catch (e) { /* silent — manual entry still works */ }
  }, 400);
});

// Close modals on overlay click
document.querySelectorAll('.modal').forEach(m => m.addEventListener('click', e => {
  if (e.target === m) m.style.display = 'none';
}));

// Sourced from the database (key_borrow_logs) — see SafetyController::keylogs().
const keyLogs = <?= $key_logs_json ?>;

function renderKeylogs() {
  document.getElementById('keylogBody').innerHTML = keyLogs.map(k => `
    <tr>
      <td><strong>${k.id}</strong></td>
      <td>${k.name}</td>
      <td>${k.dept}</td>
      <td>${k.key}</td>
      <td>${k.issued}</td>
      <td>${k.returned}</td>
      <td><span class="kl-badge ${k.status==='Active'?'kl-active':'kl-done'}">${k.status}</span></td>
      <td>${k.guard}</td>
    </tr>`).join('');
}

window.addEventListener('DOMContentLoaded', renderKeylogs);
</script>

<?= $this->endSection() ?>
