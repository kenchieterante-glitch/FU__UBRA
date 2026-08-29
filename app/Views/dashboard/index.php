<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php
  $kpis = $kpis ?? [];
  $alerts = $alerts ?? [];
  $activity = $activity ?? [];
  $pending_tools_json = $pending_tools_json ?? '[]';
  $pending_workorders_json = $pending_workorders_json ?? '[]';
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
          <span class="stat-icon <?= $tone ?>"><i class="fa-solid <?= esc($kpi['icon'] ?? 'fa-chart-simple', 'attr') ?>"></i></span>
          <h3><?= esc($kpi['label']) ?></h3>
          <div class="value"><?= esc($kpi['value']) ?></div>
        </div>
      <?php else: ?>
        <a class="stat-card stat-card-clickable" href="<?= esc(site_url($kpi['url'] ?? '#')) ?>">
          <span class="stat-icon <?= $tone ?>"><i class="fa-solid <?= esc($kpi['icon'] ?? 'fa-chart-simple', 'attr') ?>"></i></span>
          <h3><?= esc($kpi['label']) ?></h3>
          <div class="value"><?= esc($kpi['value']) ?></div>
        </a>
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
        <h3><i class="fa-solid fa-hand-holding"></i> Borrowed Tools</h3>
        <div id="pendingToolsList" class="pending-list"></div>
        <a class="overview-link" href="<?= esc(site_url('tools?filter=borrowed')) ?>">View in Tools Management →</a>
      </div>
      <div class="pending-column">
        <h3><i class="fa-solid fa-screwdriver-wrench"></i> Open Work Orders</h3>
        <div id="pendingWorkOrdersList" class="pending-list"></div>
        <a class="overview-link" href="<?= esc(site_url('safety?filter=duework')) ?>">View in Maintenance →</a>
      </div>
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
            <span class="alert-icon <?= esc($alert['tone']) ?>"><i class="fa-solid <?= esc($alert['icon']) ?>"></i></span>
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
        <p>Recent driver trip tickets — connected live to Vehicle Management &amp; the Guard Dashboard.</p>
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

  function esc(s) {
    const d = document.createElement('div');
    d.textContent = String(s ?? '');
    return d.innerHTML;
  }

  function togglePendingPanel() {
    const panel = document.getElementById('pendingPanel');
    const opening = panel.style.display === 'none';
    panel.style.display = opening ? 'block' : 'none';
    if (opening) {
      renderPendingLists();
      panel.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
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