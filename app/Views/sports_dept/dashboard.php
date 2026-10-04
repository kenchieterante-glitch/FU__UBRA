<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
// Values the controller passes in — given safe defaults here so the page (and the editor) always know them.
$stats = $stats ?? [];
$details = $details ?? [];
$sections = $sections ?? [];
?>
<div class="page-header">
  <div>
    <h1>Sports Equipment Monitoring</h1>
    <p class="page-subtitle">The whole department at a glance.</p>
  </div>
</div>

<div class="stat-cards">
  <?php foreach ($stats as $s): ?>
    <button type="button" class="stat-card status-pick" data-key="<?= esc($s['key']) ?>" onclick="pickDash('<?= esc($s['key'], 'js') ?>')">
      <span class="stat-icon tone-<?= esc($s['tone']) ?>"><i class="bi <?= esc($s['icon']) ?>"></i></span>
      <h3><?= esc($s['label']) ?></h3>
      <div class="value"><?= esc((string) $s['value']) ?></div>
    </button>
  <?php endforeach; ?>
</div>

<?php helper('facilities'); ?>
<div class="status-tables" style="margin-top:16px;">
  <?php foreach ($stats as $c): $d = $details[$c['key']]; ?>
    <div class="status-table guard-card" id="st-<?= esc($c['key']) ?>" style="display:none;">
      <div class="gc-title"><i class="bi <?= esc($c['icon']) ?>"></i> <?= esc($d['title']) ?></div>
      <div class="table-wrap">
        <table class="sj-table">
          <thead><tr><?php foreach ($d['columns'] as $col): ?><th><?= esc($col) ?></th><?php endforeach; ?></tr></thead>
          <tbody>
            <?php if (empty($d['rows'])): ?>
              <tr><td colspan="<?= count($d['columns']) ?>" class="empty-row">Nothing here right now.</td></tr>
            <?php else: foreach ($d['rows'] as $row): ?>
              <tr><?php foreach ($row as $cell): ?><?= fac_cell($cell) ?><?php endforeach; ?></tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<div class="gc-title" style="margin:22px 0 12px;"><i class="bi bi-grid"></i> Go to</div>
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:12px;">
  <?php foreach ($sections as $sec): ?>
    <a href="<?= base_url($sec['url']) ?>" class="guard-card" style="display:flex;gap:12px;align-items:flex-start;padding:16px;text-decoration:none;color:inherit;">
      <i class="bi <?= esc($sec['icon']) ?>" style="font-size:20px;color:var(--m);"></i>
      <div>
        <div style="font-weight:700;"><?= esc($sec['label']) ?></div>
        <div class="text-muted" style="font-size:13px;"><?= esc($sec['desc']) ?></div>
      </div>
    </a>
  <?php endforeach; ?>
</div>
<script>
function pickDash(key) {
  const panel = document.getElementById('st-' + key);
  const wasOpen = panel.style.display !== 'none';
  document.querySelectorAll('.status-table').forEach(t => t.style.display = 'none');
  document.querySelectorAll('.status-pick').forEach(b => b.classList.toggle('active', !wasOpen && b.dataset.key === key));
  if (!wasOpen) { panel.style.display = ''; }
}
</script>
<script src="<?= base_url('Assets/js/table-tools.js') ?>?v=<?= @filemtime(FCPATH . 'Assets/js/table-tools.js') ?>"></script>
<script>document.querySelectorAll('.status-table').forEach(attachTableTools);</script>
<?= $this->endSection() ?>
