<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="portal-dept">
  <a class="portal-back" href="<?= base_url('portals') ?>"><i class="bi bi-arrow-left"></i> All Portals</a>

  <div class="portal-dept-head">
    <div class="portal-eyebrow">Portal <?= esc($portal['box']) ?></div>
    <h1><?= esc($portal['name']) ?></h1>
    <p><?= esc($portal['description']) ?></p>
  </div>

  <div class="portal-stats">
    <?php foreach ($stats as $s): ?>
      <div class="portal-stat">
        <span class="portal-stat-icon tone-<?= esc($s['tone']) ?>"><i class="bi <?= esc($s['icon']) ?>"></i></span>
        <div class="portal-stat-label"><?= esc($s['label']) ?></div>
        <div class="portal-stat-value"><?= esc((string) $s['value']) ?></div>
      </div>
    <?php endforeach; ?>
  </div>

  <div class="portal-section-title"><i class="bi bi-link-45deg"></i> Quick Access</div>
  <div class="portal-links">
    <?php foreach ($links as $l): ?>
      <a class="portal-link" href="<?= base_url($l['url']) ?>">
        <i class="bi <?= esc($l['icon']) ?>"></i>
        <span><?= esc($l['label']) ?></span>
        <i class="bi bi-chevron-right portal-link-arrow"></i>
      </a>
    <?php endforeach; ?>
  </div>
</div>
<?= $this->endSection() ?>
