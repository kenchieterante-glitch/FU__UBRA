<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php
  $totalAlerts = (int) $maintenance_tools + (int) $disposal_tools + (int) $low_stock_items;
  $categoryRoutes = [
      'Power Tools'        => 'tools/power-tools',
      'Consumable'         => 'tools/consumable',
      'Sports Equipment'   => 'tools/sports-equipment',
  ];
  $categoryIcons = [
      'Power Tools'        => 'bi-lightning-fill',
      'Consumable'         => 'bi-box-seam-fill',
      'Sports Equipment'   => 'bi-trophy-fill',
  ];
?>

<div class="role-dash-hero">
    <div class="role-dash-hero-text">
        <h1><?= esc($greeting) ?><?= !empty($full_name) ? ', ' . esc($full_name) : '' ?></h1>
        <p>Tools and equipment overview — availability, maintenance, and stock.</p>
        <span class="role-dash-hero-time"><i class="bi bi-clock-history"></i> <?= esc($last_updated) ?></span>
    </div>
    <div class="role-dash-hero-status <?= $totalAlerts > 0 ? 'is-alert' : 'is-clear' ?>">
        <i class="bi <?= $totalAlerts > 0 ? 'bi-exclamation-triangle-fill' : 'bi-shield-check' ?>"></i>
        <div>
            <strong><?= $totalAlerts > 0 ? $totalAlerts . ' Item' . ($totalAlerts === 1 ? '' : 's') . ' Need Attention' : 'All Systems Normal' ?></strong>
            <small><?= $totalAlerts > 0 ? 'Maintenance, disposal, or low stock' : 'No issues right now' ?></small>
        </div>
    </div>
</div>

<div class="role-dash-panel">
    <div class="role-dash-panel-title"><i class="bi bi-tools"></i> Tools Overview</div>
    <div class="stat-cards">
        <div class="stat-card stat-card-clickable" onclick="toggleKpiBanner(this)" role="button" tabindex="0" data-label="Total Tools" data-url="<?= base_url('tools') ?>">
            <span class="stat-icon tone-maroon"><i class="bi bi-tools"></i></span>
            <h3>Total Tools</h3>
            <div class="value"><?= (int) $total_tools ?></div>
        </div>
        <div class="stat-card stat-card-clickable" onclick="toggleKpiBanner(this)" role="button" tabindex="0" data-label="Available Tools" data-url="<?= base_url('tools') ?>">
            <span class="stat-icon tone-green"><i class="bi bi-check-circle-fill"></i></span>
            <h3>Available Tools</h3>
            <div class="value"><?= (int) $available_tools ?></div>
        </div>
        <div class="stat-card stat-card-clickable" onclick="toggleKpiBanner(this)" role="button" tabindex="0" data-label="Borrowed Tools" data-url="<?= base_url('tools?filter=borrowed') ?>">
            <span class="stat-icon tone-neutral"><i class="bi bi-hand-index-thumb-fill"></i></span>
            <h3>Borrowed Tools</h3>
            <div class="value"><?= (int) $borrowed_tools ?></div>
        </div>
        <div class="stat-card stat-card-clickable" onclick="toggleKpiBanner(this)" role="button" tabindex="0" data-label="Needs Maintenance" data-url="<?= base_url('tools') ?>">
            <span class="stat-icon tone-gold"><i class="bi bi-wrench-adjustable"></i></span>
            <h3>Needs Maintenance</h3>
            <div class="value"><?= (int) $maintenance_tools ?></div>
        </div>
        <div class="stat-card stat-card-clickable" onclick="toggleKpiBanner(this)" role="button" tabindex="0" data-label="Disposal" data-url="<?= base_url('tools') ?>">
            <span class="stat-icon tone-red"><i class="bi bi-trash3-fill"></i></span>
            <h3>Disposal</h3>
            <div class="value"><?= (int) $disposal_tools ?></div>
        </div>
        <div class="stat-card stat-card-clickable" onclick="toggleKpiBanner(this)" role="button" tabindex="0" data-label="Low Stock Items" data-url="<?= base_url('tools/consumable') ?>">
            <span class="stat-icon tone-red"><i class="bi bi-box2"></i></span>
            <h3>Low Stock Items</h3>
            <div class="value"><?= (int) $low_stock_items ?></div>
        </div>
    </div>
</div>

<?php if (!empty($category_distribution)): ?>
<div class="role-dash-panel">
    <div class="role-dash-panel-title"><i class="bi bi-grid-3x3-gap"></i> By Category</div>
    <div class="stat-cards">
        <?php foreach ($category_distribution as $row): ?>
            <?php
              $cat = $row['category'] ?? 'Uncategorized';
              $route = $categoryRoutes[$cat] ?? 'tools';
              $icon = $categoryIcons[$cat] ?? 'bi-tag-fill';
            ?>
            <div class="stat-card stat-card-clickable" onclick="toggleKpiBanner(this)" role="button" tabindex="0" data-label="<?= esc($cat, 'attr') ?>" data-url="<?= base_url($route) ?>">
                <span class="stat-icon tone-neutral"><i class="bi <?= esc($icon, 'attr') ?>"></i></span>
                <h3><?= esc($cat) ?></h3>
                <div class="value"><?= (int) $row['count'] ?></div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- Click-to-reveal banner shared by every stat card above, instead of
     navigating away instantly — consistent with the dashboard's KPI cards. -->
<section class="panel-card pending-panel" id="kpiBanner" style="display:none" aria-label="Status detail">
    <div class="panel-head">
        <h2 id="kpiBannerTitle"></h2>
    </div>
    <div class="pending-column">
        <a id="kpiBannerLink" class="overview-link" href="#">View details →</a>
    </div>
</section>

<script>
function toggleKpiBanner(card) {
    const panel = document.getElementById('kpiBanner');
    const alreadyOpenForThisCard = panel.style.display !== 'none' && panel.dataset.forLabel === card.dataset.label;

    if (alreadyOpenForThisCard) {
        panel.style.display = 'none';
        panel.dataset.forLabel = '';
        return;
    }

    document.getElementById('kpiBannerTitle').textContent = card.dataset.label;
    const link = document.getElementById('kpiBannerLink');
    link.href = card.dataset.url;
    link.textContent = 'View in ' + card.dataset.label + ' →';

    panel.dataset.forLabel = card.dataset.label;
    panel.style.display = 'block';
}

document.querySelectorAll('.stat-card[role="button"]').forEach(card => {
    card.addEventListener('keydown', e => {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            card.click();
        }
    });
});
</script>

<?= $this->endSection() ?>
