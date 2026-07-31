<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
  $title = $title ?? 'Keylogs';
  $key_logs_json = $key_logs_json ?? '[]';
?>

<link rel="stylesheet" href="<?= base_url('Assets/css/safety.css') ?>">

<div class="page-header">
  <div>
    <h1><?= esc($title) ?></h1>
    <p class="page-subtitle">Record of key check-ins, assignments, and custodianship logs.</p>
  </div>
  <button class="btn-add" onclick="alert('Key logging will be available soon.')">+ Log Key</button>
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

<script>
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
