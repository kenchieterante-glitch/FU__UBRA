<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php
  $kpis = $kpis ?? [];
  $alerts = $alerts ?? [];
  $activity = $activity ?? [];
?>

<div class="groundworks-shell">
  <div class="groundworks-header">
    <h1>Facilities Administration and General Services</h1>
    <p class="subtle">Monitoring dashboard for the Head of the Department — status at a glance, no data entry.</p>
    <p class="subtle" style="font-size:.75rem;opacity:.7;">Last updated: <?= esc($last_updated ?? date('M j, Y g:i A')) ?></p>
  </div>

  <section class="kpi-grid" aria-label="Key performance indicators">
    <?php foreach ($kpis as $kpi): ?>
      <article class="kpi-card <?= esc($kpi['tone'] ?? 'tone-neutral') ?>">
        <div class="kpi-top">
          <span class="kpi-icon"><i class="fa-solid <?= esc($kpi['icon']) ?>"></i></span>
          <span class="kpi-label"><?= esc($kpi['label']) ?></span>
        </div>
        <div class="kpi-value"><?= esc($kpi['value']) ?></div>
        <div class="kpi-meta"><?= esc($kpi['meta']) ?></div>
        <div class="kpi-sub"><?= esc($kpi['sub']) ?></div>
      </article>
    <?php endforeach; ?>
  </section>

  <div class="lower-grid">
    <section class="panel-card alerts-panel" aria-label="Alerts">
      <div class="panel-head">
        <h2>Alerts</h2>
        <p>Items needing attention.</p>
      </div>
      <div class="alert-list">
        <?php foreach ($alerts as $alert): ?>
          <div class="alert-item">
            <span class="alert-icon <?= esc($alert['tone']) ?>"><i class="fa-solid <?= esc($alert['icon']) ?>"></i></span>
            <div class="alert-copy">
              <div class="alert-title"><?= esc($alert['title']) ?></div>
              <div class="alert-subtitle"><?= esc($alert['subtitle']) ?></div>
            </div>
            <div class="alert-time"><?= esc($alert['time']) ?></div>
          </div>
        <?php endforeach; ?>
      </div>
    </section>

    <section class="panel-card activity-panel" aria-label="Recent activity">
      <div class="panel-head">
        <h2>Recent Activity</h2>
        <p>Latest operational updates.</p>
      </div>
      <div class="activity-list">
        <?php foreach ($activity as $item): ?>
          <div class="activity-item">
            <div class="activity-time"><?= esc($item['time']) ?></div>
            <span class="activity-tag"><?= esc($item['tag']) ?></span>
            <div class="activity-text"><?= esc($item['text']) ?></div>
          </div>
        <?php endforeach; ?>
      </div>
    </section>
  </div>
</div>

<?= $this->endSection() ?>