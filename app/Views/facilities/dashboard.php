<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="page-header">
  <div>
    <h1>Facilities Administration and General Services</h1>
    <p class="page-subtitle">The whole department at a glance.</p>
  </div>
</div>

<div class="stat-cards">
  <?php foreach ($stats as $s): ?>
    <div class="stat-card">
      <span class="stat-icon tone-<?= esc($s['tone']) ?>"><i class="bi <?= esc($s['icon']) ?>"></i></span>
      <h3><?= esc($s['label']) ?></h3>
      <div class="value"><?= esc((string) $s['value']) ?></div>
    </div>
  <?php endforeach; ?>
</div>

<div class="gc-title" style="margin:22px 0 12px;"><i class="bi bi-grid"></i> Go to</div>
<div class="portal-links" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(230px,1fr));gap:12px;">
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
<?= $this->endSection() ?>
