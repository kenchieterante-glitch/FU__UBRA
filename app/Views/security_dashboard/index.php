<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php $totalAlerts = (int) $critical_fe + (int) $fleet_maintenance; ?>

<div class="role-dash-hero">
    <div class="role-dash-hero-text">
        <h1><?= esc($greeting) ?><?= !empty($full_name) ? ', ' . esc($full_name) : '' ?></h1>
        <p>Full system overview — fire safety, guard activity, maintenance, and vehicles.</p>
        <span class="role-dash-hero-time"><i class="bi bi-clock-history"></i> <?= esc($last_updated) ?></span>
    </div>
    <div class="role-dash-hero-status <?= $totalAlerts > 0 ? 'is-alert' : 'is-clear' ?>">
        <i class="bi <?= $totalAlerts > 0 ? 'bi-exclamation-triangle-fill' : 'bi-shield-check' ?>"></i>
        <div>
            <strong><?= $totalAlerts > 0 ? $totalAlerts . ' Item' . ($totalAlerts === 1 ? '' : 's') . ' Need Attention' : 'All Systems Normal' ?></strong>
            <small><?= $totalAlerts > 0 ? 'Critical fire safety or fleet issues' : 'No critical alerts right now' ?></small>
        </div>
    </div>
</div>

<div class="role-dash-panel">
    <div class="role-dash-panel-title"><i class="bi bi-fire"></i> Fire Safety &amp; Maintenance</div>
    <div class="stat-cards">
        <div class="stat-card stat-card-clickable" onclick="toggleKpiBanner(this)" role="button" tabindex="0" data-label="Fire Extinguishers" data-url="<?= base_url('safety') ?>">
            <span class="stat-icon tone-maroon"><i class="bi bi-fire"></i></span>
            <h3>Fire Extinguishers</h3>
            <div class="value"><?= (int) $total_extinguishers ?></div>
        </div>
        <div class="stat-card stat-card-clickable" onclick="toggleKpiBanner(this)" role="button" tabindex="0" data-label="Critical Alerts" data-url="<?= base_url('safety') ?>">
            <span class="stat-icon tone-red"><i class="bi bi-exclamation-triangle-fill"></i></span>
            <h3>Critical Alerts</h3>
            <div class="value"><?= (int) $critical_fe ?></div>
        </div>
        <div class="stat-card stat-card-clickable" onclick="toggleKpiBanner(this)" role="button" tabindex="0" data-label="Open Work Orders" data-url="<?= base_url('safety?filter=duework') ?>">
            <span class="stat-icon tone-neutral"><i class="bi bi-wrench-adjustable"></i></span>
            <h3>Open Work Orders</h3>
            <div class="value"><?= (int) $open_work_orders ?></div>
        </div>
        <div class="stat-card stat-card-clickable" onclick="toggleKpiBanner(this)" role="button" tabindex="0" data-label="Aircon Units" data-url="<?= base_url('safety') ?>">
            <span class="stat-icon tone-green"><i class="bi bi-wind"></i></span>
            <h3>Aircon Units</h3>
            <div class="value"><?= (int) $aircon_units ?></div>
        </div>
    </div>
</div>

<div class="role-dash-panel">
    <div class="role-dash-panel-title"><i class="bi bi-shield-check"></i> Guard &amp; Gate Activity</div>
    <div class="stat-cards">
        <div class="stat-card stat-card-clickable" onclick="toggleKpiBanner(this)" role="button" tabindex="0" data-label="Active Key Borrows" data-url="<?= base_url('safety/guard-dashboard') ?>">
            <span class="stat-icon tone-gold"><i class="bi bi-key-fill"></i></span>
            <h3>Active Key Borrows</h3>
            <div class="value"><?= (int) $active_keys ?></div>
        </div>
        <div class="stat-card stat-card-clickable" onclick="toggleKpiBanner(this)" role="button" tabindex="0" data-label="Trips Awaiting Dispatch" data-url="<?= base_url('safety/guard-dashboard') ?>">
            <span class="stat-icon tone-neutral"><i class="bi bi-ticket-perforated"></i></span>
            <h3>Trips Awaiting Dispatch</h3>
            <div class="value"><?= (int) $pending_dispatches ?></div>
        </div>
    </div>
</div>

<div class="role-dash-panel">
    <div class="role-dash-panel-title"><i class="bi bi-truck"></i> Vehicle Fleet</div>
    <div class="stat-cards">
        <div class="stat-card stat-card-clickable" onclick="toggleKpiBanner(this)" role="button" tabindex="0" data-label="Total Vehicles" data-url="<?= base_url('vehicles') ?>">
            <span class="stat-icon tone-maroon"><i class="bi bi-truck"></i></span>
            <h3>Total Vehicles</h3>
            <div class="value"><?= (int) $fleet_total ?></div>
        </div>
        <div class="stat-card stat-card-clickable" onclick="toggleKpiBanner(this)" role="button" tabindex="0" data-label="Available" data-url="<?= base_url('vehicles?filter=available') ?>">
            <span class="stat-icon tone-green"><i class="bi bi-check-circle-fill"></i></span>
            <h3>Available</h3>
            <div class="value"><?= (int) $fleet_available ?></div>
        </div>
        <div class="stat-card stat-card-clickable" onclick="toggleKpiBanner(this)" role="button" tabindex="0" data-label="In Use" data-url="<?= base_url('vehicles?filter=inuse') ?>">
            <span class="stat-icon tone-neutral"><i class="bi bi-signpost-2"></i></span>
            <h3>In Use</h3>
            <div class="value"><?= (int) $fleet_in_use ?></div>
        </div>
        <div class="stat-card stat-card-clickable" onclick="toggleKpiBanner(this)" role="button" tabindex="0" data-label="Needs Maintenance" data-url="<?= base_url('vehicles?filter=maintenance') ?>">
            <span class="stat-icon tone-red"><i class="bi bi-wrench-adjustable"></i></span>
            <h3>Needs Maintenance</h3>
            <div class="value"><?= (int) $fleet_maintenance ?></div>
        </div>
    </div>
</div>

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
