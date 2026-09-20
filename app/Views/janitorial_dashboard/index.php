<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php
  $totalAlerts = (int) $pending_zones + (int) $low_stock + (int) $out_of_stock;
?>

<div class="role-dash-hero">
    <div class="role-dash-hero-text">
        <h1><?= esc($greeting) ?><?= !empty($full_name) ? ', ' . esc($full_name) : '' ?></h1>
        <p>Janitorial overview — zone cleaning progress, active shifts, and supply stock.</p>
        <span class="role-dash-hero-time"><i class="bi bi-clock-history"></i> <?= esc($last_updated) ?></span>
    </div>
    <div class="role-dash-hero-status <?= $totalAlerts > 0 ? 'is-alert' : 'is-clear' ?>">
        <i class="bi <?= $totalAlerts > 0 ? 'bi-exclamation-triangle-fill' : 'bi-shield-check' ?>"></i>
        <div>
            <strong><?= $totalAlerts > 0 ? $totalAlerts . ' Item' . ($totalAlerts === 1 ? '' : 's') . ' Need Attention' : 'All Systems Normal' ?></strong>
            <small><?= $totalAlerts > 0 ? 'Pending zones or low/out of stock supplies' : 'No issues right now' ?></small>
        </div>
    </div>
</div>

<div class="role-dash-panel">
    <div class="role-dash-panel-title"><i class="bi bi-brush"></i> Janitorial Overview</div>
    <div class="stat-cards">
        <div class="stat-card stat-card-clickable" onclick="toggleKpiBanner(this)" role="button" tabindex="0" data-label="Cleaning Completion" data-url="<?= base_url('janitorial') ?>">
            <span class="stat-icon tone-green"><i class="bi bi-check-circle-fill"></i></span>
            <h3>Cleaning Completion</h3>
            <div class="value"><?= (int) $cleaned_zones ?>/<?= (int) $total_zones ?> areas</div>
        </div>
        <div class="stat-card stat-card-clickable" onclick="toggleKpiBanner(this)" role="button" tabindex="0" data-label="Pending Zones" data-url="<?= base_url('janitorial?filter=pending') ?>">
            <span class="stat-icon tone-gold"><i class="bi bi-exclamation-circle-fill"></i></span>
            <h3>Pending Zones</h3>
            <div class="value"><?= (int) $pending_zones ?></div>
        </div>
        <div class="stat-card stat-card-clickable" onclick="toggleKpiBanner(this)" role="button" tabindex="0" data-label="Active Shifts" data-url="<?= base_url('janitorial') ?>">
            <span class="stat-icon tone-neutral"><i class="bi bi-people-fill"></i></span>
            <h3>Active Shifts</h3>
            <div class="value"><?= (int) $active_shifts ?></div>
        </div>
        <div class="stat-card stat-card-clickable" onclick="toggleKpiBanner(this)" role="button" tabindex="0" data-label="Low Stock Supplies" data-url="<?= base_url('janitorial') ?>">
            <span class="stat-icon tone-red"><i class="bi bi-box2"></i></span>
            <h3>Low Stock Supplies</h3>
            <div class="value"><?= (int) $low_stock ?></div>
        </div>
        <div class="stat-card stat-card-clickable" onclick="toggleKpiBanner(this)" role="button" tabindex="0" data-label="Out of Stock" data-url="<?= base_url('janitorial') ?>">
            <span class="stat-icon tone-red"><i class="bi bi-trash3-fill"></i></span>
            <h3>Out of Stock</h3>
            <div class="value"><?= (int) $out_of_stock ?></div>
        </div>
    </div>
</div>

<!-- Click-to-reveal banner shared by every stat card above, same pattern as
     the other role dashboards (Security/Tools/Facilities). -->
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
