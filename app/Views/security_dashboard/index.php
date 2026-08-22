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
        <div class="stat-card stat-card-clickable" onclick="window.location.href='<?= base_url('safety') ?>'" role="button" tabindex="0">
            <span class="stat-icon tone-maroon"><i class="fa-solid fa-fire-extinguisher"></i></span>
            <h3>Fire Extinguishers</h3>
            <div class="value"><?= (int) $total_extinguishers ?></div>
        </div>
        <div class="stat-card stat-card-clickable" onclick="window.location.href='<?= base_url('safety') ?>'" role="button" tabindex="0">
            <span class="stat-icon tone-red"><i class="fa-solid fa-triangle-exclamation"></i></span>
            <h3>Critical Alerts</h3>
            <div class="value"><?= (int) $critical_fe ?></div>
        </div>
        <div class="stat-card stat-card-clickable" onclick="window.location.href='<?= base_url('safety') ?>'" role="button" tabindex="0">
            <span class="stat-icon tone-neutral"><i class="fa-solid fa-screwdriver-wrench"></i></span>
            <h3>Open Work Orders</h3>
            <div class="value"><?= (int) $open_work_orders ?></div>
        </div>
        <div class="stat-card stat-card-clickable" onclick="window.location.href='<?= base_url('safety') ?>'" role="button" tabindex="0">
            <span class="stat-icon tone-green"><i class="fa-solid fa-wind"></i></span>
            <h3>Aircon Units</h3>
            <div class="value"><?= (int) $aircon_units ?></div>
        </div>
    </div>
</div>

<div class="role-dash-panel">
    <div class="role-dash-panel-title"><i class="bi bi-shield-check"></i> Guard &amp; Gate Activity</div>
    <div class="stat-cards">
        <div class="stat-card stat-card-clickable" onclick="window.location.href='<?= base_url('safety/guard-dashboard') ?>'" role="button" tabindex="0">
            <span class="stat-icon tone-gold"><i class="fa-solid fa-key"></i></span>
            <h3>Active Key Borrows</h3>
            <div class="value"><?= (int) $active_keys ?></div>
        </div>
        <div class="stat-card stat-card-clickable" onclick="window.location.href='<?= base_url('safety/guard-dashboard') ?>'" role="button" tabindex="0">
            <span class="stat-icon tone-neutral"><i class="fa-solid fa-ticket"></i></span>
            <h3>Trips Awaiting Dispatch</h3>
            <div class="value"><?= (int) $pending_dispatches ?></div>
        </div>
    </div>
</div>

<div class="role-dash-panel">
    <div class="role-dash-panel-title"><i class="bi bi-truck"></i> Vehicle Fleet</div>
    <div class="stat-cards">
        <div class="stat-card stat-card-clickable" onclick="window.location.href='<?= base_url('vehicles') ?>'" role="button" tabindex="0">
            <span class="stat-icon tone-maroon"><i class="fa-solid fa-truck"></i></span>
            <h3>Total Vehicles</h3>
            <div class="value"><?= (int) $fleet_total ?></div>
        </div>
        <div class="stat-card stat-card-clickable" onclick="window.location.href='<?= base_url('vehicles') ?>'" role="button" tabindex="0">
            <span class="stat-icon tone-green"><i class="fa-solid fa-circle-check"></i></span>
            <h3>Available</h3>
            <div class="value"><?= (int) $fleet_available ?></div>
        </div>
        <div class="stat-card stat-card-clickable" onclick="window.location.href='<?= base_url('vehicles') ?>'" role="button" tabindex="0">
            <span class="stat-icon tone-neutral"><i class="fa-solid fa-road"></i></span>
            <h3>In Use</h3>
            <div class="value"><?= (int) $fleet_in_use ?></div>
        </div>
        <div class="stat-card stat-card-clickable" onclick="window.location.href='<?= base_url('vehicles') ?>'" role="button" tabindex="0">
            <span class="stat-icon tone-red"><i class="fa-solid fa-screwdriver-wrench"></i></span>
            <h3>Needs Maintenance</h3>
            <div class="value"><?= (int) $fleet_maintenance ?></div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
