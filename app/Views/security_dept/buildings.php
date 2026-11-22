<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
$totalUnits = array_sum(array_map(fn($r) => count($r['units']), $rows));
$withUnits  = count(array_filter($rows, fn($r) => count($r['units']) > 0));
$needing    = count(array_filter($rows, fn($r) => $r['attention'] > 0));
$statusLabel = fn($s) => $s === 'New' ? 'Good' : $s;
?>
<div class="page-header">
  <div>
    <h1><?= esc($title) ?></h1>
    <p class="page-subtitle">Safety and Security Department — each campus building, its floors, and the fire extinguishers registered for it.</p>
  </div>
</div>

<div class="stat-cards">
  <div class="stat-card"><span class="stat-icon tone-maroon"><i class="bi bi-buildings"></i></span><h3>Buildings</h3><div class="value"><?= count($rows) ?></div></div>
  <div class="stat-card"><span class="stat-icon tone-green"><i class="bi bi-fire"></i></span><h3>Extinguishers</h3><div class="value"><?= (int) $totalUnits ?></div></div>
  <div class="stat-card"><span class="stat-icon tone-gold"><i class="bi bi-building-check"></i></span><h3>With Extinguisher</h3><div class="value"><?= (int) $withUnits ?>/<?= count($rows) ?></div></div>
  <div class="stat-card"><span class="stat-icon tone-red"><i class="bi bi-exclamation-triangle-fill"></i></span><h3>Need Attention</h3><div class="value"><?= (int) $needing ?></div></div>
</div>

<div class="fac-pane">
  <div class="fe-toolbar-row">
    <div class="toolbar-search">
      <input type="text" id="bldgSearch" class="search-box" placeholder="Search building or floor…" oninput="applyBldgFilters()">
      <i class="bi bi-search search-icon"></i>
    </div>
    <div class="fe-toolbar-actions">
      <?php if ($needing > 0): ?>
      <button type="button" class="fac-alert-icon fac-alert-btn alert-red fac-blink" onclick="setBldgStatus('attention')" title="Buildings needing attention" aria-label="Buildings needing attention"><i class="bi bi-exclamation-triangle-fill"></i> <strong><?= (int) $needing ?></strong></button>
      <?php endif; ?>
      <div class="filter-menu-wrapper">
        <button type="button" class="filter-btn" onclick="toggleBldgFilter(this)" aria-label="Open filters"><i class="bi bi-funnel"></i></button>
        <div class="filter-popup">
          <div class="filter-popup-title">Filter</div>
          <div class="filter-row">
            <label for="bldgStatus">Status</label>
            <select id="bldgStatus" onchange="applyBldgFilters()">
              <option value="">All buildings</option>
              <option value="attention">Need attention</option>
              <option value="ok">All extinguishers good</option>
              <option value="none">No extinguisher</option>
            </select>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="table-wrap">
    <table class="sj-table" id="bldgTable">
      <thead><tr><th>Building</th><th>Floors</th><th>Extinguishers</th><th>Need Attention</th><th>Next Check</th></tr></thead>
      <tbody id="bldgBody">
      <?php foreach ($rows as $i => $r):
          $state = count($r['units']) === 0 ? 'none' : ($r['attention'] > 0 ? 'attention' : 'ok');
      ?>
        <tr class="bldg-row" style="cursor:pointer" data-state="<?= $state ?>"
            data-search="<?= esc(strtolower($r['name'] . ' ' . implode(' ', $r['floors']))) ?>"
            onclick="showBldgDetail(<?= (int) $i ?>)">
          <td><strong><?= esc($r['name']) ?></strong></td>
          <td><?= count($r['floors']) ?> (<?= esc(implode(', ', $r['floors'])) ?>)</td>
          <td><?= count($r['units']) ?></td>
          <td>
            <?php if ($state === 'none'): ?>
              <span class="tt-badge zb-needs">No extinguisher</span>
            <?php elseif ($r['attention'] > 0): ?>
              <span class="tt-badge zb-overdue"><?= (int) $r['attention'] ?> need attention</span>
            <?php else: ?>
              <span class="tt-badge zb-done">All good</span>
            <?php endif; ?>
          </td>
          <td><?= $r['next_due'] ? date('M d, Y', strtotime($r['next_due'])) : '—' ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<div id="bldgDetailModal" class="sj-modal-overlay" style="display:none" onclick="if (event.target === this) closeBldgDetail()">
  <div class="sj-modal">
    <div class="sj-modal-header" style="display:flex;justify-content:space-between;align-items:center;">
      <h3 id="bldgDetailTitle"></h3>
      <button type="button" class="dp-close" onclick="closeBldgDetail()" aria-label="Close"><i class="bi bi-x-lg"></i></button>
    </div>
    <div class="sj-modal-body" id="bldgDetailBody"></div>
  </div>
</div>

<script>
const BLDG = <?= json_encode(array_map(function ($r) use ($statusLabel) {
    $fmt = fn($d) => !empty($d) ? date('M d, Y', strtotime($d)) : '—';
    return [
        'name'      => $r['name'],
        'floors'    => $r['floors'],
        'attention' => $r['attention'],
        'next_due'  => $fmt($r['next_due']),
        'units'     => array_map(fn($e) => [
            'unit' => $e['unit_id'], 'floor' => $e['floor'], 'type' => $e['type'], 'status' => $statusLabel($e['status']),
            'last' => $fmt($e['last_inspection'] ?? null), 'next' => $fmt($e['next_due'] ?? null), 'expires' => $fmt($e['expires_on'] ?? null),
        ], $r['units']),
    ];
}, $rows), JSON_HEX_TAG | JSON_HEX_AMP) ?>;

function esc(s) { return String(s == null ? '' : s).replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c])); }
function detailGrid(fields) {
  return `<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:12px 18px;">${fields.map(([k, v]) => `<div><div class="text-muted">${esc(k)}</div><strong>${esc(v)}</strong></div>`).join('')}</div>`;
}
function showBldgDetail(i) {
  const b = BLDG[i];
  if (!b) return;
  document.getElementById('bldgDetailTitle').textContent = b.name;
  let html = detailGrid([
    ['Floors', b.floors.length + ' (' + b.floors.join(', ') + ')'],
    ['Fire extinguishers', String(b.units.length)],
    ['Need attention', b.attention ? String(b.attention) : 'None'],
    ['Next check', b.next_due],
  ]);
  html += '<div style="margin-top:14px;"><div class="text-muted">Fire extinguishers</div>';
  if (!b.units.length) {
    html += '<div style="margin-top:4px;">None registered for this building yet.</div>';
  } else {
    html += b.units.map(u => `<div style="display:flex;justify-content:space-between;gap:12px;padding:7px 0;border-bottom:1px solid rgba(0,0,0,.07);">
        <span><strong>${esc(u.unit)}</strong> · ${esc(u.floor)} · ${esc(u.type)}</span>
        <span style="text-align:right;">${esc(u.status)}<br><small class="text-muted">Next check ${esc(u.next)} · Expires ${esc(u.expires)}</small></span>
      </div>`).join('');
  }
  html += '</div>';
  document.getElementById('bldgDetailBody').innerHTML = html;
  document.getElementById('bldgDetailModal').style.display = 'flex';
}
function closeBldgDetail() { document.getElementById('bldgDetailModal').style.display = 'none'; }
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeBldgDetail(); });

function toggleBldgFilter(button) {
  const popup = button.parentElement.querySelector('.filter-popup');
  const open = !popup.classList.contains('visible');
  document.querySelectorAll('.filter-popup.visible').forEach(p => p.classList.remove('visible'));
  popup.classList.toggle('visible', open);
}
document.addEventListener('click', e => {
  if (!e.target.closest('.filter-menu-wrapper')) document.querySelectorAll('.filter-popup.visible').forEach(p => p.classList.remove('visible'));
});
function setBldgStatus(v) {
  const sel = document.getElementById('bldgStatus');
  sel.value = sel.value === v ? '' : v;
  applyBldgFilters();
}
function applyBldgFilters() {
  const q = document.getElementById('bldgSearch').value.trim().toLowerCase();
  const st = document.getElementById('bldgStatus').value;
  document.querySelectorAll('#bldgBody .bldg-row').forEach(r => {
    r.style.display = ((!q || r.dataset.search.includes(q)) && (!st || r.dataset.state === st)) ? '' : 'none';
  });
}
</script>
<?= $this->endSection() ?>
