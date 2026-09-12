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
        <div class="stat-card stat-card-clickable" onclick="toggleKpiBanner(this)" role="button" tabindex="0" data-label="Total Personnel" data-url="<?= base_url('personnel') ?>">
            <span class="stat-icon tone-maroon"><i class="fa-solid fa-users"></i></span>
            <h3>Total Personnel</h3>
            <div class="value"><?= (int) $total_personnel ?></div>
        </div>
        <div class="stat-card stat-card-clickable" onclick="toggleKpiBanner(this)" role="button" tabindex="0" data-label="Active" data-url="<?= base_url('personnel') ?>">
            <span class="stat-icon tone-green"><i class="fa-solid fa-circle-check"></i></span>
            <h3>Active</h3>
            <div class="value"><?= (int) $active_personnel ?></div>
        </div>
        <div class="stat-card stat-card-clickable" onclick="toggleKpiBanner(this)" role="button" tabindex="0" data-label="On Leave" data-url="<?= base_url('personnel') ?>">
            <span class="stat-icon tone-gold"><i class="fa-solid fa-calendar-day"></i></span>
            <h3>On Leave</h3>
            <div class="value"><?= (int) $on_leave_personnel ?></div>
        </div>
    </div>
</div>

<div class="role-dash-panel">
    <div class="role-dash-panel-title"><i class="bi bi-tools"></i> Tools Management</div>
    <div class="stat-cards">
        <div class="stat-card stat-card-clickable" onclick="toggleKpiBanner(this)" role="button" tabindex="0" data-label="Total Tools" data-url="<?= base_url('tools') ?>">
            <span class="stat-icon tone-maroon"><i class="fa-solid fa-toolbox"></i></span>
            <h3>Total Tools</h3>
            <div class="value"><?= (int) $total_tools ?></div>
        </div>
        <div class="stat-card stat-card-clickable" onclick="toggleKpiBanner(this)" role="button" tabindex="0" data-label="Available" data-url="<?= base_url('tools') ?>">
            <span class="stat-icon tone-green"><i class="fa-solid fa-circle-check"></i></span>
            <h3>Available</h3>
            <div class="value"><?= (int) $available_tools ?></div>
        </div>
        <div class="stat-card stat-card-clickable" onclick="toggleKpiBanner(this)" role="button" tabindex="0" data-label="Borrowed" data-url="<?= base_url('tools?filter=borrowed') ?>">
            <span class="stat-icon tone-neutral"><i class="fa-solid fa-hand-holding"></i></span>
            <h3>Borrowed</h3>
            <div class="value"><?= (int) $borrowed_tools ?></div>
        </div>
        <div class="stat-card stat-card-clickable" onclick="toggleKpiBanner(this)" role="button" tabindex="0" data-label="Needs Maintenance" data-url="<?= base_url('tools') ?>">
            <span class="stat-icon tone-gold"><i class="fa-solid fa-screwdriver-wrench"></i></span>
            <h3>Needs Maintenance</h3>
            <div class="value"><?= (int) $maintenance_tools ?></div>
        </div>
        <div class="stat-card stat-card-clickable" onclick="toggleKpiBanner(this)" role="button" tabindex="0" data-label="Disposal" data-url="<?= base_url('tools') ?>">
            <span class="stat-icon tone-red"><i class="fa-solid fa-trash"></i></span>
            <h3>Disposal</h3>
            <div class="value"><?= (int) $disposal_tools ?></div>
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
