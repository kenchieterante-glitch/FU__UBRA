<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
$rows = $rows ?? [];
$totals = $totals ?? ['today' => 0, 'haz_today' => 0, 'logged' => 0, 'pending' => 0, 'overdue' => 0];
$log_rows = $log_rows ?? [];
$kg = fn($v) => number_format((float) $v, 1);
$statusBadge = fn($s) => match ($s) {
    'Logged'  => '<span class="tt-badge zb-done">Done</span>',
    'Overdue' => '<span class="tt-badge zb-overdue badge-blink">Missed</span>',
    default   => '<span class="tt-badge zb-needs">To do</span>',
};
$cards = [
    ['key' => 'all',     'label' => 'Waste Stations', 'value' => count($rows),        'icon' => 'bi-recycle',                   'tone' => 'maroon'],
    ['key' => 'logged',  'label' => 'Done Today',     'value' => $totals['logged'],   'icon' => 'bi-check-circle-fill',         'tone' => 'green'],
    ['key' => 'pending', 'label' => 'To Do Today',    'value' => $totals['pending'],  'icon' => 'bi-hourglass-split',           'tone' => 'gold'],
    ['key' => 'overdue', 'label' => 'Missed',         'value' => $totals['overdue'],  'icon' => 'bi-exclamation-triangle-fill', 'tone' => 'red'],
];
// missed first, then to do, then done — so the supervisor sees what needs action at the top
$order = ['Overdue' => 0, 'Pending' => 1, 'Logged' => 2];
usort($rows, fn($x, $y) => ($order[$x['status']] <=> $order[$y['status']]) ?: strcmp($x['building'], $y['building']));
?>

<div class="page-header">
  <div>
    <h1>Waste Watch</h1>
    <p class="page-subtitle"><?= esc($org) ?> — Daily trash bin check, segregation and end-of-day weigh-in.</p>
  </div>
  <button type="button" class="btn-add" onclick="openWeighModal(0)">+ Weigh &amp; Log</button>
</div>

<?php if (!empty($flash_success)): ?><div class="alert alert-success" style="margin-bottom:12px;"><?= esc($flash_success) ?></div><?php endif; ?>
<?php if (!empty($flash_error)): ?><div class="alert alert-error" style="margin-bottom:12px;"><?= esc($flash_error) ?></div><?php endif; ?>

<div class="stat-cards">
  <?php foreach ($cards as $c): $clickable = in_array($c['key'], ['all', 'logged', 'pending', 'overdue'], true); ?>
    <button type="button" class="stat-card status-pick"<?= $clickable ? ' onclick="pickStat(\'' . $c['key'] . '\')"' : '' ?> data-key="<?= esc($c['key']) ?>">
      <span class="stat-icon tone-<?= esc($c['tone']) ?>"><i class="bi <?= esc($c['icon']) ?>"></i></span>
      <h3><?= esc($c['label']) ?></h3>
      <div class="value"><?= esc((string) $c['value']) ?></div>
    </button>
  <?php endforeach; ?>
</div>

<div class="guard-card" style="padding:14px 18px;margin:16px 0;">
  <div class="fe-toolbar-row" style="margin:0;">
    <div class="toolbar-search">
      <input type="text" id="wasteSearch" class="search-box" placeholder="Search building, location, or supervisor…" oninput="applyWasteFilters()">
      <i class="bi bi-search search-icon"></i>
    </div>
    <div class="fe-toolbar-actions">
      <?php if ($totals['overdue'] > 0): ?>
        <button type="button" class="fac-alert-icon fac-alert-btn alert-red fac-blink" onclick="pickStat('overdue')" title="Missed weigh-ins" aria-label="Missed weigh-ins"><i class="bi bi-exclamation-triangle-fill"></i> <strong><?= (int) $totals['overdue'] ?></strong></button>
      <?php endif; ?>
      <?php if ($totals['pending'] > 0): ?>
        <button type="button" class="fac-alert-icon fac-alert-btn alert-yellow" onclick="pickStat('pending')" title="Still to weigh today" aria-label="To do today"><i class="bi bi-exclamation-triangle-fill"></i> <strong><?= (int) $totals['pending'] ?></strong></button>
      <?php endif; ?>
      <button type="button" class="filter-btn active" id="wasteMapBtn" onclick="toggleWasteMap()" title="Show / hide the campus map" aria-label="Show or hide the campus map"><i class="bi bi-map"></i></button>
    </div>
  </div>
</div>

<div id="wasteMapPanel" class="guard-card" style="padding:18px;margin-bottom:16px;">
  <div class="map-head"><div class="gc-title"><i class="bi bi-map"></i> Waste Stations Map — <?= esc(date('M d, Y', strtotime($today))) ?></div></div>
  <div class="map-zoom-row">
    <select class="fac-select" onchange="wastePicked(this.value)" aria-label="Zoom to a building">
      <option value="">— Select a Building —</option>
      <?php foreach ($buildings as $b): ?><option value="<?= esc($b) ?>"><?= esc($b) ?></option><?php endforeach; ?>
    </select>
  </div>
  <div class="map-split">
    <div class="fac-map-wrap">
      <svg id="wasteMapSVG" xmlns="http://www.w3.org/2000/svg" style="width:100%;height:auto;max-height:640px;background:#ffffff;"></svg>
    </div>
    <div id="wasteDrillPanel" class="floor-break fd-wrap" style="display:none"></div>
  </div>
  <div class="fac-map-legend">
    <span><i class="map-pass"></i> Done today</span>
    <span><i style="background:#ffc400"></i> Yellow = to do today</span>
    <span><i class="map-alert"></i> Red = missed</span>
    <span><svg width="14" height="14" viewBox="0 0 14 14" aria-hidden="true" style="vertical-align:middle;margin-right:6px;"><circle cx="7" cy="7" r="7" fill="#1b6b3a"/><rect x="4" y="5" width="6" height="6" rx="1" fill="#fff"/><rect x="3.4" y="3.6" width="7.2" height="1.6" rx=".6" fill="#fff"/></svg> Waste station</span>
  </div>
</div>

<div class="sub-tabs" id="wasteTabs" style="margin-bottom:12px;">
  <button type="button" class="sub-tab active" data-tab="today" onclick="switchWasteTab('today')"><i class="bi bi-clipboard2-check"></i> Today's Check</button>
  <button type="button" class="sub-tab" data-tab="log" onclick="switchWasteTab('log')"><i class="bi bi-journal-text"></i> Daily Log</button>
  <button type="button" class="sub-tab" data-tab="summary" onclick="switchWasteTab('summary')"><i class="bi bi-pie-chart"></i> Segregation Summary</button>
</div>

<!-- ── TODAY'S CHECK ──────────────────────────────────────────── -->
<div class="waste-pane guard-card" id="waste-today" style="padding:18px;">
  <div class="dp-section-title" style="margin-top:0"><i class="bi bi-clipboard2-check"></i> Today — <?= esc(date('l, M d, Y', strtotime($today))) ?></div>
  <p style="margin:4px 0 12px;">At the end of the day, weigh the 3 bins of each building, then press <strong>Weigh &amp; Log</strong>.</p>
  <div class="table-wrap">
    <table class="sj-table" id="wasteTodayTable">
      <thead><tr><th>Building</th><th>Status</th><th>Non-Bio (kg)</th><th>Bio (kg)</th><th>Hazardous (kg)</th><th>Action</th></tr></thead>
      <tbody>
        <?php foreach ($rows as $r): $t = $r['today']; ?>
          <tr class="waste-row" data-status="<?= esc(strtolower($r['status'])) ?>" data-id="<?= (int) $r['id'] ?>">
            <td><strong><?= esc($r['building']) ?></strong></td>
            <td><?= $statusBadge($r['status']) ?></td>
            <td><?= $t ? $kg($t['nb']) : '—' ?></td>
            <td><?= $t ? $kg($t['b']) : '—' ?></td>
            <td><?= $t ? ($t['h'] >= $limit ? '<strong style="color:#c62828">' . $kg($t['h']) . ' (high)</strong>' : $kg($t['h'])) : '—' ?></td>
            <td><button type="button" class="btn-maroon-sm" onclick="openWeighModal(<?= (int) $r['id'] ?>)"><?= $t ? 'Edit' : 'Weigh &amp; Log' ?></button></td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$rows): ?><tr><td colspan="6" class="empty-row">No waste stations yet.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<style>
  /* Daily Log: keep the date and the supervisor's name on one line, let only the notes wrap */
  #waste-log table td:nth-child(1), #waste-log table th:nth-child(1),
  #waste-log table td:nth-child(7), #waste-log table th:nth-child(7) { white-space: nowrap; }
  #waste-log table { table-layout: auto; width: 100%; }
  #waste-log table th, #waste-log table td { padding-left: 8px; padding-right: 8px; }
  #waste-log table td:nth-child(2) { min-width: 170px; }
  #waste-log table td:nth-child(8), #waste-log table th:nth-child(8) { text-align: left; min-width: 130px; max-width: 220px; word-break: break-word; }
  #waste-log table td { padding-top: 11px; padding-bottom: 11px; }
</style>
<!-- ── DAILY LOG ──────────────────────────────────────────────── -->
<div class="waste-pane guard-card" id="waste-log" style="padding:18px;display:none;">
  <div class="dp-section-title" style="margin-top:0"><i class="bi bi-journal-text"></i> Daily weigh-in log (last 30 days)</div>
  <div class="table-wrap">
    <table class="sj-table">
      <thead><tr><th>Date</th><th>Building</th><th>Non-Bio (kg)</th><th>Bio (kg)</th><th>Hazardous (kg)</th><th>Total (kg)</th><th>Checked By</th><th>Notes</th></tr></thead>
      <tbody>
        <?php foreach ($log_rows as $l): ?>
          <tr>
            <td><?= esc(date('M d, Y', strtotime($l['date']))) ?></td>
            <td><?= esc($l['building']) ?></td>
            <td><?= $kg($l['nb']) ?></td>
            <td><?= $kg($l['b']) ?></td>
            <td><?= $l['h'] >= $limit ? '<span class="tt-badge zb-overdue">' . $kg($l['h']) . '</span>' : $kg($l['h']) ?></td>
            <td><strong><?= $kg($l['nb'] + $l['b'] + $l['h']) ?></strong></td>
            <td><?= esc($l['by']) ?></td>
            <td><?= esc($l['notes']) ?></td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$log_rows): ?><tr><td colspan="8" class="empty-row">No weigh-ins recorded yet.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- ── SEGREGATION SUMMARY ────────────────────────────────────── -->
<style>
  .ws-wrap { background: var(--surf, #fff); border: 1px solid var(--bdr, #e7e7ec); border-radius: 18px; padding: 26px 28px; box-shadow: 0 10px 30px rgba(20, 20, 40, .07); }
  .ws-top { display: flex; align-items: center; justify-content: space-between; gap: 14px; flex-wrap: wrap; margin-bottom: 18px; }
  .ws-title { font-size: 20px; font-weight: 800; color: var(--txt, #111); }
  .ws-sub { font-size: 15px; color: var(--txt, #111); margin-top: 2px; }
  .ws-filterwrap { margin: 0 !important; display: block; width: auto; flex: 0 0 auto; }
  .ws-filterwrap .filter-menu-wrapper { position: relative; display: inline-block; width: 36px; height: 36px; }

  .ws-filterwrap .filter-popup { width: 300px; padding: 18px; background: var(--surf, #fff); border: 1px solid var(--bdr, #ddd); border-radius: 14px; box-shadow: 0 18px 44px rgba(20, 20, 40, .28); z-index: 3000; }
  .ws-filterwrap .filter-popup-title { font-size: 14px; color: #7b0f1a; margin-bottom: 14px; }
  .ws-filterwrap .filter-row { margin-bottom: 14px; }
  .ws-filterwrap .filter-row label { font-size: 13px; font-weight: 800; color: var(--txt, #111); }
  .ws-filterwrap .filter-popup input[type=date], .ws-filterwrap .filter-popup input[type=month], .ws-filterwrap .filter-popup select { width: 100%; font-size: 15px; padding: 8px 10px; border: 1px solid var(--bdr, #ddd); border-radius: 8px; background: var(--surf, #fff); color: var(--txt, #111); }
  .ws-filterwrap .filter-btn { width: 36px; height: 36px; font-size: 16px; }
  .ws-showing { margin-top: 0; display: inline-block; font-size: 15px; font-weight: 800; color: #7b0f1a; background: rgba(123, 15, 26, .08); padding: 6px 14px; border-radius: 999px; }
  .ws-toggle { display: inline-flex; background: rgba(123, 15, 26, .08); border-radius: 999px; padding: 4px; }
  .ws-toggle button { border: 0; background: transparent; padding: 10px 20px; border-radius: 999px; font-size: 15px; font-weight: 700; color: var(--txt, #111); cursor: pointer; }
  .ws-toggle button.active { background: #7b0f1a; color: #fff; box-shadow: 0 4px 12px rgba(123, 15, 26, .3); }
  .ws-main { display: grid; grid-template-columns: 300px 1fr; gap: 34px; align-items: center; }
  .ws-donut { position: relative; width: 280px; height: 280px; margin: 0 auto; }
  .ws-donut svg { width: 100%; height: 100%; transform: rotate(-90deg); }
  .ws-donut circle { fill: none; stroke-width: 30; transition: stroke-width .15s, opacity .15s; cursor: pointer; }
  .ws-donut.dim circle:not(.on) { opacity: .35; }
  .ws-donut circle.on { stroke-width: 36; }
  .ws-center { position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; pointer-events: none; }
  .ws-center .big { font-size: 30px; font-weight: 800; line-height: 1.1; color: var(--txt, #111); }
  .ws-center .small { font-size: 15px; font-weight: 600; color: var(--txt, #111); max-width: 150px; }
  .ws-cats { display: flex; flex-direction: column; gap: 14px; }
  .ws-cat { display: flex; align-items: center; gap: 18px; padding: 16px 20px; border-radius: 16px; border: 2px solid transparent; background: rgba(0, 0, 0, .03); cursor: pointer; transition: transform .15s, box-shadow .15s, border-color .15s; }
  .ws-cat:hover, .ws-cat.on { transform: translateX(4px); box-shadow: 0 8px 22px rgba(20, 20, 40, .12); border-color: var(--c); background: var(--surf, #fff); }
  .ws-ico { width: 54px; height: 54px; border-radius: 16px; display: flex; align-items: center; justify-content: center; font-size: 26px; color: #fff; background: var(--c); flex-shrink: 0; }
  .ws-cat .nm { font-size: 18px; font-weight: 800; color: var(--txt, #111); }
  .ws-cat .hint { font-size: 14px; color: var(--txt, #111); opacity: .8; }
  .ws-cat .kg { margin-left: auto; text-align: right; }
  .ws-cat .kg b { display: block; font-size: 28px; line-height: 1.1; color: var(--txt, #111); }
  .ws-cat .kg span { font-size: 14px; color: var(--txt, #111); opacity: .8; }
  .ws-cat .pct { display: none; font-size: 28px; font-weight: 800; color: var(--c); }
  .ws-cat:hover .pct, .ws-cat.on .pct { display: block; }
  .ws-cat:hover .kg b, .ws-cat.on .kg b { display: none; }
  .ws-bldg-title { font-size: 18px; font-weight: 800; margin: 30px 0 12px; color: var(--txt, #111); display: flex; align-items: center; gap: 8px; }
  .ws-row { display: grid; grid-template-columns: 270px 1fr 90px; gap: 16px; align-items: center; padding: 9px 0; border-bottom: 1px solid var(--bdr, #eee); }
  .ws-row .b { font-size: 17px; font-weight: 700; color: var(--txt, #111); display: flex; align-items: center; gap: 12px; }
  .ws-rank { width: 30px; height: 30px; border-radius: 50%; background: #7b0f1a; color: #fff; display: inline-flex; align-items: center; justify-content: center; font-size: 15px; font-weight: 800; flex-shrink: 0; }
  .ws-row .t { font-size: 18px; font-weight: 800; text-align: right; color: var(--txt, #111); }
  .ws-bar { display: flex; height: 22px; border-radius: 999px; overflow: hidden; background: rgba(0, 0, 0, .06); }
  .ws-bar i { display: block; height: 100%; cursor: pointer; transition: filter .15s; }
  .ws-bar i:hover { filter: brightness(1.15); }
  .ws-key { display: flex; gap: 18px; flex-wrap: wrap; font-size: 14px; color: var(--txt, #111); margin-bottom: 4px; }
  .ws-key span { display: inline-flex; align-items: center; gap: 6px; }
  .ws-key i { width: 14px; height: 14px; border-radius: 4px; display: inline-block; }
  #wsTip { position: fixed; z-index: 9999; pointer-events: none; background: #1d1d28; color: #fff; padding: 9px 14px; border-radius: 10px; font-size: 15px; font-weight: 700; box-shadow: 0 8px 24px rgba(0, 0, 0, .3); display: none; }
  @media (max-width: 900px) { .ws-main { grid-template-columns: 1fr; } .ws-row { grid-template-columns: 1fr; } .ws-row .t { text-align: left; } }
</style>
<div class="waste-pane" id="waste-summary" style="display:none">
  <div class="ws-wrap">
    <div class="ws-top">
      <div>
        <div class="ws-showing" id="wsShowing"></div>
      </div>
      <div class="fe-toolbar-row ws-filterwrap"><div class="filter-menu-wrapper">
        <button type="button" class="filter-btn" id="wsFilterBtn" aria-label="Filter the summary" title="Filter by date or month" onclick="wsTogglePopup(event)"><i class="bi bi-funnel"></i></button>
        <div class="filter-popup" id="wsFilterPopup" onclick="event.stopPropagation()">
          <div class="filter-popup-title">Filter</div>
          <div class="filter-row"><label for="wsMode">Show</label>
            <select id="wsMode" onchange="wsModeChanged()">
              <option value="newest">Newest (latest weigh-in day)</option>
              <option value="date">Date</option>
              <option value="month">Month</option>
            </select></div>
          <div class="filter-row" id="wsDateRow" style="display:none"><label for="wsDate">Date</label><input type="date" id="wsDate" max="<?= esc($today) ?>" onchange="wsRender()"></div>
          <div class="filter-row" id="wsMonthRow" style="display:none"><label for="wsMonth">Month</label><input type="month" id="wsMonth" max="<?= esc(substr($today, 0, 7)) ?>" onchange="wsRender()"></div>
        </div>
      </div></div>
    </div>
    <div class="ws-main">
      <div class="ws-donut" id="wsDonut"><svg viewBox="0 0 200 200" id="wsSvg"></svg><div class="ws-center" id="wsCenter"></div></div>
      <div class="ws-cats" id="wsCats"></div>
    </div>
    <div class="ws-bldg-title"><i class="bi bi-trophy-fill" style="color:#7b0f1a"></i> Top 5 buildings with the most waste</div>
    <div id="wsBuildings"></div>
  </div>
  <div id="wsTip"></div>
</div>

<!-- ── WEIGH & LOG POP-UP ─────────────────────────────────────── -->
<div class="modal" id="weighModal">
  <div class="modal-box">
    <h3>Weigh &amp; Log</h3>
    <form action="<?= base_url('facilities/waste/log') ?>" method="post">
      <?= csrf_field() ?>
      <p class="required-note">Weigh each bin at the end of the day. Weights are in kilograms.</p>
      <label>Waste Station <span class="required-mark">*</span></label>
      <select name="station_id" id="weighStation" required onchange="weighFill(this.value)">
        <option value="">— Select a station —</option>
        <?php foreach ($rows as $r): ?><option value="<?= (int) $r['id'] ?>"><?= esc($r['building']) ?> — <?= esc($r['location']) ?></option><?php endforeach; ?>
      </select>
      <label>Date <span class="required-mark">*</span></label>
      <input type="date" name="log_date" id="weighDate" value="<?= esc($today) ?>" max="<?= esc($today) ?>" required>
      <label>Non-Biodegradable (kg) <span class="required-mark">*</span></label>
      <input type="number" name="non_bio_kg" id="weighNb" step="0.1" min="0" placeholder="e.g. 5.5" required>
      <label>Biodegradable (kg) <span class="required-mark">*</span></label>
      <input type="number" name="bio_kg" id="weighB" step="0.1" min="0" placeholder="e.g. 3.2" required>
      <label>Hazardous (kg) <span class="required-mark">*</span></label>
      <input type="number" name="hazardous_kg" id="weighH" step="0.1" min="0" placeholder="e.g. 0.4" required>
      <label>Notes</label>
      <textarea name="notes" id="weighNotes" rows="2" placeholder="Optional — e.g. batteries in the hazardous bin"></textarea>
      <div class="modal-actions" style="margin-top:14px;display:flex;gap:10px;justify-content:flex-end;">
        <button type="button" class="btn-cancel" onclick="document.getElementById('weighModal').style.display='none'">Cancel</button>
        <button type="submit" class="btn-submit">Save Weigh-in</button>
      </div>
    </form>
  </div>
</div>

<script src="<?= base_url('Assets/js/campus-map.js') ?>?v=<?= @filemtime(FCPATH . 'Assets/js/campus-map.js') ?>"></script>
<script src="<?= base_url('Assets/js/drill-panel.js') ?>?v=<?= @filemtime(FCPATH . 'Assets/js/drill-panel.js') ?>"></script>
<script src="<?= base_url('Assets/js/table-tools.js') ?>?v=<?= @filemtime(FCPATH . 'Assets/js/table-tools.js') ?>"></script>
<script>
const wasteRows = <?= json_encode($rows, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
const wsLogs = <?= json_encode($all_logs, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>; // [date, station_id, non_bio, bio, hazardous]
const wsNames = <?= json_encode($station_names, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
const HAZ_LIMIT = <?= json_encode((float) $limit) ?>;
function esc(s) { return String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c])); }
const f1 = n => (Math.round(n * 10) / 10).toFixed(1);

// ---- map: green = weighed today, yellow = still to weigh, red = missed
function wasteMapState() {
  const st = {};
  wasteRows.forEach(r => {
    if (r.status === 'Logged') st[r.building] = { red: null, yellow: null, done: true };
    else if (r.status === 'Overdue') st[r.building] = { red: [''], yellow: null, done: false };
    else st[r.building] = { red: null, yellow: [''], done: false };
  });
  return st;
}
renderMapImage('wasteMapSVG', {
  imageUrl: '<?= base_url('images/MAP.jpg') ?>',
  stateByName: wasteMapState(),
  hideFloorText: true,
  legend: 'waste',
  onSelect: name => wasteShow(name),
});

const CATS = ['Non-Biodegradable', 'Biodegradable', 'Hazardous'];
const catKey = { 'Non-Biodegradable': 'nb', 'Biodegradable': 'b', 'Hazardous': 'h' };
function wasteShow(name) {
  const box = document.getElementById('wasteDrillPanel');
  if (!name) { box.style.display = 'none'; return; }
  const r = wasteRows.find(x => x.building === name);
  if (!r) {
    facDrill('wasteDrillPanel', { title: name, sub: 'Waste Watch', banner: { cls: 'fd-warn', head: 'No waste station', text: 'No waste station is registered at this building yet.' }, floors: CATS, tabsLabel: 'Bin', tabsIcon: 'trash3', close: "wasteShow('')", planLabel: 'bin', detail: () => '<div class="fd-none">Nothing to show.</div>', alerts: [] });
    return;
  }
  const t = r.today;
  const banner = r.status === 'Logged' ? { cls: 'fd-ok', head: 'Done today', text: 'The 3 bins were weighed' + (t && t.by ? ' by ' + t.by + '.' : '.') }
    : r.status === 'Overdue' ? { cls: 'fd-bad', head: 'Missed', text: 'Not weighed since ' + (r.last || 'ever') + '.' }
    : { cls: 'fd-warn', head: 'To do today', text: 'Weigh the 3 bins at the end of the day.' };
  facDrill('wasteDrillPanel', {
    title: r.building, sub: 'Waste Watch — ' + r.location, banner, floors: CATS, tabsLabel: 'Bin', tabsIcon: 'trash3',
    close: "wasteShow('')", planLabel: 'bin', alertsLabel: 'Waste Alerts',
    count: c => t ? f1(t[catKey[c]]) : '—',
    detail: c => {
      const k = catKey[c];
      return '<div class="fd-row"><span>Today</span><strong>' + (t ? f1(t[k]) + ' kg' : 'not weighed yet') + '</strong></div>' +
        '<div class="fd-row"><span>Last 7 days</span><strong>' + f1(r.week[k]) + ' kg</strong></div>' +
        '<div class="fd-row"><span>Last 30 days</span><strong>' + f1(r.month[k]) + ' kg</strong></div>' +
        '<div class="fd-row"><span>Last logged</span><strong>' + esc(r.last || '—') + '</strong></div>';
    },
    alerts: [
      r.status === 'Overdue' ? { cls: 'urgent', html: '<strong>Missed</strong> — last weighed ' + esc(r.last || 'never') + '.' } : null,
      r.status === 'Pending' ? { cls: 'warn', html: '<strong>To do</strong> — not weighed yet today.' } : null,
      r.high_haz ? { cls: 'urgent', html: '<strong>Hazardous bin</strong> — ' + f1(t.h) + ' kg today (limit ' + HAZ_LIMIT + ' kg). Schedule a pick-up.' } : null,
      t && t.notes ? { cls: 'warn', html: esc(t.notes) } : null,
    ].filter(Boolean),
    footer: '<button type="button" class="btn-maroon-sm" onclick="openWeighModal(' + r.id + ')">' + (t ? 'Edit today\'s weigh-in' : 'Weigh &amp; Log this station') + '</button>',
  });
}
function wastePicked(name) { zoomMapTo('wasteMapSVG', name); wasteShow(name); }
function toggleWasteMap() {
  const p = document.getElementById('wasteMapPanel');
  const open = p.style.display === 'none';
  p.style.display = open ? '' : 'none';
  document.getElementById('wasteMapBtn').classList.toggle('active', open);
}

// ---- tabs, status boxes, filters
function switchWasteTab(key) {
  document.querySelectorAll('#wasteTabs .sub-tab').forEach(b => b.classList.toggle('active', b.dataset.tab === key));
  document.querySelectorAll('.waste-pane').forEach(p => p.style.display = p.id === 'waste-' + key ? '' : 'none');
}
let wasteStatus = '';
function pickStat(key) {
  wasteStatus = (key === 'all' || key === wasteStatus) ? '' : key;
  document.querySelectorAll('.stat-card.status-pick').forEach(b => b.classList.toggle('active', !!wasteStatus && b.dataset.key === wasteStatus));
  switchWasteTab('today');
  applyWasteFilters();
}
function applyWasteFilters() {
  const q = document.getElementById('wasteSearch').value.trim().toLowerCase();
  document.querySelectorAll('#wasteTodayTable tbody tr.waste-row').forEach(r => {
    const okS = !wasteStatus || r.dataset.status === wasteStatus;
    const okQ = !q || r.textContent.toLowerCase().includes(q);
    r.style.display = okS && okQ ? '' : 'none';
  });
  document.querySelectorAll('#waste-log tbody tr').forEach(r => { r.style.display = !q || r.textContent.toLowerCase().includes(q) ? '' : 'none'; });
}

// ---- weigh & log pop-up
function weighFill(id) {
  const r = wasteRows.find(x => String(x.id) === String(id));
  const t = r && r.today;
  document.getElementById('weighNb').value = t ? t.nb : '';
  document.getElementById('weighB').value = t ? t.b : '';
  document.getElementById('weighH').value = t ? t.h : '';
  document.getElementById('weighNotes').value = t && t.notes ? t.notes : '';
}
function openWeighModal(id) {
  document.getElementById('weighStation').value = id ? String(id) : '';
  document.getElementById('weighDate').value = '<?= esc($today) ?>';
  weighFill(id || '');
  document.getElementById('weighModal').style.display = 'flex';
}

// ---- segregation summary: donut + big cards, percentage appears on hover
const WS = [
  { k: 'nb', name: 'Non-Biodegradable', hint: 'Plastic, metal, glass', icon: 'bi-bag-fill', color: '#2f6fed' },
  { k: 'b',  name: 'Biodegradable',     hint: 'Food and garden waste', icon: 'bi-flower2', color: '#1b9a4b' },
  { k: 'h',  name: 'Hazardous',         hint: 'Batteries, chemicals', icon: 'bi-radioactive', color: '#e03a3a' },
];
const MONTHS = ['January','February','March','April','May','June','July','August','September','October','November','December'];
const wsTip = () => document.getElementById('wsTip');
function wsTipShow(e, text) { const t = wsTip(); t.textContent = text; t.style.display = 'block'; t.style.left = (e.clientX + 14) + 'px'; t.style.top = (e.clientY + 14) + 'px'; }
function wsTipHide() { wsTip().style.display = 'none'; }
function wsPlacePopup() {
  const btn = document.getElementById('wsFilterBtn'), p = document.getElementById('wsFilterPopup');
  if (!p.classList.contains('visible')) return;
  const b = btn.getBoundingClientRect();
  // The box opens BESIDE the icon (to its left), with its top level with the icon's top — same as the other pages' filter.
  p.style.position = 'fixed';
  p.style.right = 'auto';
  const left = b.left - p.offsetWidth - 8;
  if (left >= 8) { p.style.left = left + 'px'; p.style.top = b.top + 'px'; }
  else { p.style.left = Math.max(8, b.right - p.offsetWidth) + 'px'; p.style.top = (b.bottom + 8) + 'px'; } // narrow screen: fall back to below the icon
}
function wsTogglePopup(e) {
  e.stopPropagation();
  document.getElementById('wsFilterPopup').classList.toggle('visible');
  wsPlacePopup();
}
window.addEventListener('scroll', wsPlacePopup, true);
window.addEventListener('resize', wsPlacePopup);
document.addEventListener('click', () => document.getElementById('wsFilterPopup').classList.remove('visible'));
function wsModeChanged() {
  const m = document.getElementById('wsMode').value;
  document.getElementById('wsDateRow').style.display = m === 'date' ? '' : 'none';
  document.getElementById('wsMonthRow').style.display = m === 'month' ? '' : 'none';
  if (m === 'date' && !document.getElementById('wsDate').value) document.getElementById('wsDate').value = wsLatestDay();
  if (m === 'month' && !document.getElementById('wsMonth').value) document.getElementById('wsMonth').value = wsLatestDay().slice(0, 7);
  wsRender();
}
function wsLatestDay() { return wsLogs.reduce((m, l) => l[0] > m ? l[0] : m, '0000-00-00'); }
// the weigh-ins that match the chosen filter, plus a plain-words label for them
function wsSelected() {
  const mode = document.getElementById('wsMode').value;
  const prettyDay = d => { const [y, m, dd] = d.split('-').map(Number); return MONTHS[m - 1].slice(0, 3) + ' ' + dd + ', ' + y; };
  if (mode === 'date') {
    const d = document.getElementById('wsDate').value || wsLatestDay();
    return { logs: wsLogs.filter(l => l[0] === d), label: 'Showing: ' + prettyDay(d), when: 'on ' + prettyDay(d) };
  }
  if (mode === 'month') {
    const mth = document.getElementById('wsMonth').value || wsLatestDay().slice(0, 7);
    const [y, m] = mth.split('-').map(Number);
    return { logs: wsLogs.filter(l => l[0].slice(0, 7) === mth), label: 'Showing: ' + MONTHS[m - 1] + ' ' + y, when: 'in ' + MONTHS[m - 1] };
  }
  const d = wsLatestDay();
  return { logs: wsLogs.filter(l => l[0] === d), label: 'Showing: newest weigh-in day (' + prettyDay(d) + ')', when: 'on ' + prettyDay(d) };
}
function wsTotals() {
  const sum = { nb: 0, b: 0, h: 0 };
  wsSelected().logs.forEach(l => { sum.nb += l[2]; sum.b += l[3]; sum.h += l[4]; });
  return sum;
}
function wsFocus(k) {
  const sum = wsTotals(), total = (sum.nb + sum.b + sum.h) || 1;
  const c = document.getElementById('wsCenter');
  const d = document.getElementById('wsDonut');
  document.querySelectorAll('#wsSvg circle').forEach(el => el.classList.toggle('on', el.dataset.k === k));
  document.querySelectorAll('.ws-cat').forEach(el => el.classList.toggle('on', el.dataset.k === k));
  d.classList.toggle('dim', !!k);
  if (!k) { c.innerHTML = '<div class="big">' + f1(total) + ' kg</div><div class="small">total waste</div>'; return; }
  const def = WS.find(x => x.k === k);
  c.innerHTML = '<div class="big" style="color:' + def.color + '">' + Math.round(sum[k] / total * 100) + '%</div><div class="small">' + def.name + '</div>';
}
function wsRender() {
  const sel = wsSelected();
  const sum = wsTotals(), total = (sum.nb + sum.b + sum.h) || 1;
  document.getElementById('wsShowing').textContent = sel.label;
  const R = 78, C = 2 * Math.PI * R;
  let offset = 0;
  document.getElementById('wsSvg').innerHTML = '<circle cx="100" cy="100" r="' + R + '" style="stroke:rgba(0,0,0,.06);cursor:default"></circle>' + WS.map(def => {
    const len = sum[def.k] / total * C;
    const el = '<circle data-k="' + def.k + '" cx="100" cy="100" r="' + R + '" stroke="' + def.color + '" stroke-dasharray="' + len + ' ' + (C - len) + '" stroke-dashoffset="' + (-offset) + '" onmouseenter="wsFocus(\'' + def.k + '\')" onmouseleave="wsFocus(\'\')"></circle>';
    offset += len; return el;
  }).join('');
  document.getElementById('wsCats').innerHTML = WS.map(def =>
    '<div class="ws-cat" data-k="' + def.k + '" style="--c:' + def.color + '" onmouseenter="wsFocus(\'' + def.k + '\')" onmouseleave="wsFocus(\'\')">' +
    '<div class="ws-ico"><i class="bi ' + def.icon + '"></i></div>' +
    '<div><div class="nm">' + def.name + '</div><div class="hint">' + def.hint + '</div></div>' +
    '<div class="kg"><b>' + f1(sum[def.k]) + ' kg</b><div class="pct">' + Math.round(sum[def.k] / total * 100) + '%</div><span>' + esc(sel.when) + '</span></div></div>').join('');
  const byB = {};
  sel.logs.forEach(l => { byB[l[1]] = (byB[l[1]] || 0) + l[2] + l[3] + l[4]; });
  const rows = Object.entries(byB).map(([id, t]) => ({ b: wsNames[id] || '—', t })).sort((x, y) => y.t - x.t).slice(0, 5);
  const max = rows.length ? rows[0].t || 1 : 1;
  document.getElementById('wsBuildings').innerHTML = rows.length ? rows.map((r, i) =>
    '<div class="ws-row"><div class="b"><span class="ws-rank">' + (i + 1) + '</span>' + esc(r.b) + '</div><div class="ws-bar"><i style="width:' + (r.t / max * 100) + '%;background:linear-gradient(90deg,#7b0f1a,#b3263a)"></i></div><div class="t">' + f1(r.t) + ' kg</div></div>').join('')
    : '<div class="ws-sub" style="padding:14px 0">No weigh-ins for this date.</div>';
  wsFocus('');
}
wsRender();
document.getElementById('wsDonut').addEventListener('mousemove', e => { const c = e.target.closest('circle[data-k]'); if (c) wsTipShow(e, WS.find(x => x.k === c.dataset.k).name + ': ' + Math.round(wsTotals()[c.dataset.k] / ((wsTotals().nb + wsTotals().b + wsTotals().h) || 1) * 100) + '%'); else wsTipHide(); });
document.getElementById('wsDonut').addEventListener('mouseleave', wsTipHide);

document.querySelectorAll('#waste-log').forEach(attachTableTools);
</script>
<?= $this->endSection() ?>
