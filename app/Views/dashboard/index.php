<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php
  $kpis = $kpis ?? [];
  $alerts = $alerts ?? [];
  $activity = $activity ?? [];
  $pending_tools_json = $pending_tools_json ?? '[]';
  $pending_workorders_json = $pending_workorders_json ?? '[]';
  $active_borrowings_json = $active_borrowings_json ?? '[]';
  $vehicles_inuse_json = $vehicles_inuse_json ?? '[]';
  $maintenance_due_json = $maintenance_due_json ?? '[]';
  $cleaning_incomplete_json = $cleaning_incomplete_json ?? '[]';
  $travel_history = $travel_history ?? [];
?>

<div class="groundworks-shell">
  <div class="groundworks-header">
    <h1>Facilities Administration and General Services</h1>
    <p class="subtle">Monitoring dashboard for the Head of the Department — status at a glance, no data entry.</p>
  </div>

  <section class="stat-cards" aria-label="Key performance indicators">
    <?php foreach ($kpis as $kpi): ?>
      <?php $tone = esc($kpi['tone'] ?? 'tone-maroon', 'attr'); ?>
      <?php if (!empty($kpi['expand'])): ?>
        <div class="stat-card stat-card-clickable" onclick="togglePendingPanel()" role="button" tabindex="0">
          <span class="stat-icon <?= $tone ?>"><i class="bi <?= esc($kpi['icon'] ?? 'bi-bar-chart-fill', 'attr') ?>"></i></span>
          <h3><?= esc($kpi['label']) ?></h3>
          <div class="value"><?= esc($kpi['value']) ?></div>
        </div>
      <?php else: ?>
        <div class="stat-card stat-card-clickable" onclick="toggleKpiBanner(this)" role="button" tabindex="0"
             data-label="<?= esc($kpi['label'], 'attr') ?>"
             data-meta="<?= esc($kpi['meta'] ?? '', 'attr') ?>"
             data-sub="<?= esc($kpi['sub'] ?? '', 'attr') ?>"
             data-url="<?= esc(site_url($kpi['url'] ?? '#')) ?>"
             data-list-key="<?= esc($kpi['listKey'] ?? '', 'attr') ?>">
          <span class="stat-icon <?= $tone ?>"><i class="bi <?= esc($kpi['icon'] ?? 'bi-bar-chart-fill', 'attr') ?>"></i></span>
          <h3><?= esc($kpi['label']) ?></h3>
          <div class="value"><?= esc($kpi['value']) ?></div>
        </div>
      <?php endif; ?>
    <?php endforeach; ?>
  </section>

  <!-- Pending Requests detail — shows both halves of the count (borrowed
       tools + open work orders) right here, since they live on two
       different pages and a single click can't route to both. -->
  <section class="panel-card pending-panel" id="pendingPanel" style="display:none" aria-label="Pending requests detail">
    <div class="panel-head">
      <h2>Pending Requests</h2>
      <p>Everything currently waiting on approval or resolution.</p>
    </div>
    <div class="pending-columns">
      <div class="pending-column">
        <h3><i class="bi bi-hand-index-thumb-fill"></i> Borrowed Tools</h3>
        <div id="pendingToolsList" class="pending-list"></div>
        <a class="overview-link" href="<?= esc(site_url('tools?filter=borrowed')) ?>">View in Tools Management →</a>
      </div>
      <div class="pending-column">
        <h3><i class="bi bi-wrench-adjustable"></i> Open Work Orders</h3>
        <div id="pendingWorkOrdersList" class="pending-list"></div>
        <a class="overview-link" href="<?= esc(site_url('safety?filter=duework')) ?>">View in Maintenance →</a>
      </div>
    </div>
  </section>

  <!-- Generic KPI detail banner — same click-to-reveal-then-route behavior
       as the Pending Requests card above, kept consistent across every
       stat card instead of some cards navigating away instantly. -->
  <section class="panel-card pending-panel" id="kpiBanner" style="display:none" aria-label="Status detail">
    <div class="panel-head">
      <h2 id="kpiBannerTitle"></h2>
      <p id="kpiBannerSub"></p>
    </div>
    <div class="pending-column">
      <p id="kpiBannerMeta"></p>
      <div id="kpiBannerList" class="pending-list"></div>
      <a id="kpiBannerLink" class="overview-link" href="#">View details →</a>
    </div>
  </section>

  <div class="lower-grid">
    <section class="panel-card alerts-panel" aria-label="Alerts">
      <div class="panel-head">
        <h2>Alerts</h2>
        <p>Items needing attention.</p>
      </div>
      <div class="alert-list">
        <?php foreach ($alerts as $alert): ?>
          <a class="alert-item" href="<?= esc(site_url($alert['url'] ?? '#')) ?>">
            <span class="alert-icon <?= esc($alert['tone']) ?>"><i class="bi <?= esc($alert['icon']) ?>"></i></span>
            <div class="alert-copy">
              <div class="alert-title"><?= esc($alert['title']) ?></div>
              <div class="alert-subtitle"><?= esc($alert['subtitle']) ?></div>
            </div>
            <div class="alert-time"><?= esc($alert['time']) ?></div>
          </a>
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

  <section class="panel-card board-panel" aria-label="Travel history" style="margin-top:12px;">
    <div class="panel-head" style="display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap;">
      <div>
        <h2>Travel History</h2>
        <p>Recent driver trip tickets — connected live to Vehicle Management &amp; Guard.</p>
      </div>
      <a class="overview-link" href="<?= esc(site_url('travel')) ?>">View all trip tickets →</a>
    </div>
    <?php if (empty($travel_history)): ?>
      <div class="no-data">No trip tickets recorded yet.</div>
    <?php else: ?>
      <div class="table-wrap">
        <table class="data-table">
          <thead>
            <tr>
              <th>Trip ID</th>
              <th>Date</th>
              <th>Requester</th>
              <th>Destination</th>
              <th>Driver</th>
              <th>Vehicle</th>
              <th>Tire Pressure</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($travel_history as $t): ?>
              <?php $pill = match ($t['status']) {
                'Approved', 'Completed' => 'green',
                'Pending' => 'amber',
                default => 'red',
              }; ?>
              <tr class="board-table-row" onclick="window.location='<?= esc(site_url('travel')) ?>'">
                <td class="mono"><?= esc($t['trip_id']) ?></td>
                <td><?= esc($t['date']) ?></td>
                <td><?= esc($t['requester']) ?></td>
                <td><?= esc($t['destination']) ?></td>
                <td><?= esc($t['driver']) ?></td>
                <td><?= esc($t['vehicle']) ?></td>
                <td><?= esc($t['tire_pressure']) ?></td>
                <td><span class="badge <?= $pill ?>"><?= esc($t['status']) ?></span></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </section>
</div>

<script>
  const pendingTools = <?= $pending_tools_json ?>;
  const pendingWorkOrders = <?= $pending_workorders_json ?>;

  // Real itemized data behind each non-Pending KPI banner, keyed to match
  // each card's data-list-key attribute (see Dashboard::index()).
  const kpiDetailLists = {
    activeBorrowingsList: <?= $active_borrowings_json ?>,
    vehiclesInUseList: <?= $vehicles_inuse_json ?>,
    maintenanceDueList: <?= $maintenance_due_json ?>,
    cleaningIncompleteList: <?= $cleaning_incomplete_json ?>,
  };

  function esc(s) {
    const d = document.createElement('div');
    d.textContent = String(s ?? '');
    return d.innerHTML;
  }

  function togglePendingPanel() {
    const panel = document.getElementById('pendingPanel');
    const opening = panel.style.display === 'none';
    document.getElementById('kpiBanner').style.display = 'none';
    panel.style.display = opening ? 'block' : 'none';
    if (opening) {
      renderPendingLists();
    }
  }

  // Every non-expanding KPI card opens this same banner instead of
  // navigating away immediately — consistent with the Pending Requests
  // card's expand-then-route behavior. Clicking the same card again closes it.
  function toggleKpiBanner(card) {
    const panel = document.getElementById('kpiBanner');
    const alreadyOpenForThisCard = panel.style.display !== 'none' && panel.dataset.forLabel === card.dataset.label;

    document.getElementById('pendingPanel').style.display = 'none';

    if (alreadyOpenForThisCard) {
      panel.style.display = 'none';
      panel.dataset.forLabel = '';
      return;
    }

    document.getElementById('kpiBannerTitle').textContent = card.dataset.label;
    document.getElementById('kpiBannerSub').textContent = card.dataset.sub || '';
    document.getElementById('kpiBannerMeta').textContent = card.dataset.meta || '';

    const items = kpiDetailLists[card.dataset.listKey] || [];
    document.getElementById('kpiBannerList').innerHTML = items.length
      ? items.map(item => `
        <div class="pending-item">
          <strong>${esc(item.title)}</strong>
          <span>${esc(item.subtitle)}</span>
        </div>`).join('')
      : `<div class="no-data">Nothing here right now.</div>`;

    const link = document.getElementById('kpiBannerLink');
    link.href = card.dataset.url;
    link.textContent = 'View in ' + card.dataset.label + ' →';

    panel.dataset.forLabel = card.dataset.label;
    panel.style.display = 'block';
  }

  function renderPendingLists() {
    document.getElementById('pendingToolsList').innerHTML = pendingTools.length
      ? pendingTools.map(t => `
        <div class="pending-item">
          <strong>${esc(t.name)}</strong>
          <span>${esc(t.borrower)} · due ${esc(t.due || '—')}</span>
        </div>`).join('')
      : '<div class="no-data">No tools currently borrowed.</div>';

    document.getElementById('pendingWorkOrdersList').innerHTML = pendingWorkOrders.length
      ? pendingWorkOrders.map(w => `
        <div class="pending-item">
          <strong>${esc(w.id)} — ${esc(w.issue)}</strong>
          <span>${esc(w.loc)} · ${esc(w.priority)} priority</span>
        </div>`).join('')
      : '<div class="no-data">No open work orders.</div>';
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