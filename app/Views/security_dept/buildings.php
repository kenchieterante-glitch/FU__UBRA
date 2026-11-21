<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
$totalUnits = array_sum(array_map(fn($r) => count($r['units']), $rows));
$withUnits  = count(array_filter($rows, fn($r) => count($r['units']) > 0));
$needing    = count(array_filter($rows, fn($r) => $r['attention'] > 0));
$statusLabel = fn($s) => $s === 'New' ? 'Good' : $s;
?>
<div class="page-header">
  <div>
    <h1>Building Information</h1>
    <p class="page-subtitle">Safety and Security Department — each campus building, its floors, and the fire extinguishers registered for it.</p>
  </div>
  <a class="btn-add" href="<?= base_url('evacuation-map') ?>" target="_blank" rel="noopener"><i class="bi bi-map"></i> Evacuation Map</a>
</div>

<div class="stat-cards">
  <div class="stat-card"><span class="stat-icon tone-maroon"><i class="bi bi-buildings"></i></span><h3>Buildings</h3><div class="value"><?= count($rows) ?></div></div>
  <div class="stat-card"><span class="stat-icon tone-green"><i class="bi bi-fire"></i></span><h3>Extinguishers</h3><div class="value"><?= (int) $totalUnits ?></div></div>
  <div class="stat-card"><span class="stat-icon tone-gold"><i class="bi bi-building-check"></i></span><h3>With Extinguisher</h3><div class="value"><?= (int) $withUnits ?>/<?= count($rows) ?></div></div>
  <div class="stat-card"><span class="stat-icon tone-red"><i class="bi bi-exclamation-triangle-fill"></i></span><h3>Need Attention</h3><div class="value"><?= (int) $needing ?></div></div>
</div>

<div class="table-wrap">
  <table class="sj-table" id="bldgTable">
    <thead><tr><th>Building</th><th>Floors</th><th>Extinguishers</th><th>Need Attention</th><th>Next Check</th></tr></thead>
    <tbody>
    <?php foreach ($rows as $i => $r): ?>
      <tr style="cursor:pointer" onclick="document.getElementById('bldg<?= $i ?>').style.display = document.getElementById('bldg<?= $i ?>').style.display === 'none' ? '' : 'none'">
        <td><strong><?= esc($r['name']) ?></strong></td>
        <td><?= count($r['floors']) ?> (<?= esc(implode(', ', $r['floors'])) ?>)</td>
        <td><?= count($r['units']) ?></td>
        <td><?= $r['attention'] > 0 ? '<span class="status-badge status-pending">' . (int) $r['attention'] . '</span>' : '—' ?></td>
        <td><?= $r['next_due'] ? date('M d, Y', strtotime($r['next_due'])) : '—' ?></td>
      </tr>
      <tr id="bldg<?= $i ?>" style="display:none">
        <td colspan="5" style="background:rgba(0,0,0,.02)">
          <?php if (empty($r['units'])): ?>
            <em>No fire extinguisher is registered for this building yet.</em>
          <?php else: ?>
            <table class="sj-table" style="margin:6px 0">
              <thead><tr><th>Unit</th><th>Floor</th><th>Type</th><th>Status</th><th>Last Inspection</th><th>Next Check</th><th>Expires</th></tr></thead>
              <tbody>
              <?php foreach ($r['units'] as $e): ?>
                <tr>
                  <td><strong><?= esc($e['unit_id']) ?></strong></td>
                  <td><?= esc($e['floor']) ?></td>
                  <td><?= esc($e['type']) ?></td>
                  <td><?= esc($statusLabel($e['status'])) ?></td>
                  <td><?= !empty($e['last_inspection']) ? date('M d, Y', strtotime($e['last_inspection'])) : '—' ?></td>
                  <td><?= !empty($e['next_due']) ? date('M d, Y', strtotime($e['next_due'])) : '—' ?></td>
                  <td><?= !empty($e['expires_on']) ? date('M d, Y', strtotime($e['expires_on'])) : '—' ?></td>
                </tr>
              <?php endforeach; ?>
              </tbody>
            </table>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?= $this->endSection() ?>
