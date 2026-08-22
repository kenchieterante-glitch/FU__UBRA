<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php
  $totalAlerts = (int) $maintenance_tools + (int) $disposal_tools + (int) $low_stock_items;
  $categoryRoutes = [
      'Electronic Devices' => 'tools/electronic-devices',
      'Tools Equipment'    => 'tools/equipment',
      'Consumable'         => 'tools/consumable',
  ];
  $categoryIcons = [
      'Electronic Devices' => 'fa-laptop',
      'Tools Equipment'    => 'fa-toolbox',
      'Consumable'         => 'fa-box',
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
        <div class="stat-card stat-card-clickable" onclick="window.location.href='<?= base_url('tools') ?>'" role="button" tabindex="0">
            <span class="stat-icon tone-maroon"><i class="fa-solid fa-toolbox"></i></span>
            <h3>Total Tools</h3>
            <div class="value"><?= (int) $total_tools ?></div>
        </div>
        <div class="stat-card stat-card-clickable" onclick="window.location.href='<?= base_url('tools') ?>'" role="button" tabindex="0">
            <span class="stat-icon tone-green"><i class="fa-solid fa-circle-check"></i></span>
            <h3>Available Tools</h3>
            <div class="value"><?= (int) $available_tools ?></div>
        </div>
        <div class="stat-card stat-card-clickable" onclick="window.location.href='<?= base_url('tools') ?>'" role="button" tabindex="0">
            <span class="stat-icon tone-neutral"><i class="fa-solid fa-hand-holding"></i></span>
            <h3>Borrowed Tools</h3>
            <div class="value"><?= (int) $borrowed_tools ?></div>
        </div>
        <div class="stat-card stat-card-clickable" onclick="window.location.href='<?= base_url('tools') ?>'" role="button" tabindex="0">
            <span class="stat-icon tone-gold"><i class="fa-solid fa-screwdriver-wrench"></i></span>
            <h3>Needs Maintenance</h3>
            <div class="value"><?= (int) $maintenance_tools ?></div>
        </div>
        <div class="stat-card stat-card-clickable" onclick="window.location.href='<?= base_url('tools') ?>'" role="button" tabindex="0">
            <span class="stat-icon tone-red"><i class="fa-solid fa-trash"></i></span>
            <h3>Disposal</h3>
            <div class="value"><?= (int) $disposal_tools ?></div>
        </div>
        <div class="stat-card stat-card-clickable" onclick="window.location.href='<?= base_url('tools/consumable') ?>'" role="button" tabindex="0">
            <span class="stat-icon tone-red"><i class="fa-solid fa-box-open"></i></span>
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
              $icon = $categoryIcons[$cat] ?? 'fa-cube';
            ?>
            <div class="stat-card stat-card-clickable" onclick="window.location.href='<?= base_url($route) ?>'" role="button" tabindex="0">
                <span class="stat-icon tone-neutral"><i class="fa-solid <?= esc($icon, 'attr') ?>"></i></span>
                <h3><?= esc($cat) ?></h3>
                <div class="value"><?= (int) $row['count'] ?></div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<?= $this->endSection() ?>
