<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php $totalAlerts = (int) $on_leave_personnel + (int) $maintenance_tools + (int) $disposal_tools; ?>

<div class="role-dash-hero">
    <div class="role-dash-hero-text">
        <h1><?= esc($greeting) ?><?= !empty($full_name) ? ', ' . esc($full_name) : '' ?></h1>
        <p>Facilities Administration and General Services overview — personnel and tools.</p>
        <span class="role-dash-hero-time"><i class="bi bi-clock-history"></i> <?= esc($last_updated) ?></span>
    </div>
    <div class="role-dash-hero-status <?= $totalAlerts > 0 ? 'is-alert' : 'is-clear' ?>">
        <i class="bi <?= $totalAlerts > 0 ? 'bi-exclamation-triangle-fill' : 'bi-shield-check' ?>"></i>
        <div>
            <strong><?= $totalAlerts > 0 ? $totalAlerts . ' Item' . ($totalAlerts === 1 ? '' : 's') . ' Need Attention' : 'All Systems Normal' ?></strong>
            <small><?= $totalAlerts > 0 ? 'Personnel on leave or tools needing action' : 'No issues right now' ?></small>
        </div>
    </div>
</div>

<div class="role-dash-panel">
    <div class="role-dash-panel-title"><i class="bi bi-people"></i> Personnel Management</div>
    <div class="stat-cards">
        <div class="stat-card stat-card-clickable" onclick="window.location.href='<?= base_url('personnel') ?>'" role="button" tabindex="0">
            <span class="stat-icon tone-maroon"><i class="fa-solid fa-users"></i></span>
            <h3>Total Personnel</h3>
            <div class="value"><?= (int) $total_personnel ?></div>
        </div>
        <div class="stat-card stat-card-clickable" onclick="window.location.href='<?= base_url('personnel') ?>'" role="button" tabindex="0">
            <span class="stat-icon tone-green"><i class="fa-solid fa-circle-check"></i></span>
            <h3>Active</h3>
            <div class="value"><?= (int) $active_personnel ?></div>
        </div>
        <div class="stat-card stat-card-clickable" onclick="window.location.href='<?= base_url('personnel') ?>'" role="button" tabindex="0">
            <span class="stat-icon tone-gold"><i class="fa-solid fa-calendar-day"></i></span>
            <h3>On Leave</h3>
            <div class="value"><?= (int) $on_leave_personnel ?></div>
        </div>
    </div>
</div>

<div class="role-dash-panel">
    <div class="role-dash-panel-title"><i class="bi bi-tools"></i> Tools Management</div>
    <div class="stat-cards">
        <div class="stat-card stat-card-clickable" onclick="window.location.href='<?= base_url('tools') ?>'" role="button" tabindex="0">
            <span class="stat-icon tone-maroon"><i class="fa-solid fa-toolbox"></i></span>
            <h3>Total Tools</h3>
            <div class="value"><?= (int) $total_tools ?></div>
        </div>
        <div class="stat-card stat-card-clickable" onclick="window.location.href='<?= base_url('tools') ?>'" role="button" tabindex="0">
            <span class="stat-icon tone-green"><i class="fa-solid fa-circle-check"></i></span>
            <h3>Available</h3>
            <div class="value"><?= (int) $available_tools ?></div>
        </div>
        <div class="stat-card stat-card-clickable" onclick="window.location.href='<?= base_url('tools') ?>'" role="button" tabindex="0">
            <span class="stat-icon tone-neutral"><i class="fa-solid fa-hand-holding"></i></span>
            <h3>Borrowed</h3>
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
    </div>
</div>

<?= $this->endSection() ?>
