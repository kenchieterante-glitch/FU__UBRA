<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
helper('facilities');
$subtitles = [
  'equipment' => 'Equipment records, equipment status, and where each item is kept.',
  'borrowing' => 'Who has which equipment, when it is due back, and what was returned.',
];
// Which "+ Add" button each tab shows: [label, js function]
$addEquip = ['+ Add Equipment', "openAt('spEquipModal')"];
$addByTab = [
  'equipment' => ['records' => $addEquip, 'status' => $addEquip, 'location' => $addEquip],
  'borrowing' => [],
][$section];
$first = $tabs[0]['key'];
?>
<div class="page-header">
  <div>
    <h1><?= esc($title) ?></h1>
    <p class="page-subtitle">Sports Equipment Monitoring — <?= esc($subtitles[$section] ?? '') ?></p>
  </div>
  <button type="button" class="btn-add" id="atAddBtn" style="<?= isset($addByTab[$first]) ? '' : 'display:none' ?>" onclick="<?= isset($addByTab[$first]) ? $addByTab[$first][1] : '' ?>"><?= isset($addByTab[$first]) ? esc($addByTab[$first][0]) : '' ?></button>
</div>

<div class="stat-cards">
  <?php foreach ($status as $s): ?>
    <?php $isOpen = ($active_stat ?? null) === $s['key']; ?>
    <a class="stat-card stat-card-clickable<?= $isOpen ? ' active' : '' ?>" href="<?= $isOpen ? '?' : '?stat=' . esc($s['key']) ?>">
      <span class="stat-icon tone-<?= esc($s['tone']) ?>"><i class="bi <?= esc($s['icon']) ?>"></i></span>
      <h3><?= esc($s['label']) ?></h3>
      <div class="value"><?= esc((string) $s['value']) ?></div>
    </a>
  <?php endforeach; ?>
</div>
<?php if (!empty($stat_rows)): ?>
<div id="statInline" class="guard-card" style="margin-bottom:16px;">
  <div class="gc-title"><i class="bi bi-list-ul"></i> <?= esc($stat_rows['title']) ?></div>
  <div class="table-wrap">
    <table class="sj-table">
      <thead><tr><?php foreach ($stat_rows['columns'] as $col): ?><th><?= esc($col) ?></th><?php endforeach; ?></tr></thead>
      <tbody>
        <?php if (empty($stat_rows['rows'])): ?>
          <tr><td colspan="<?= count($stat_rows['columns']) ?>" class="empty-row">Nothing here right now.</td></tr>
        <?php else: foreach ($stat_rows['rows'] as $row): ?>
          <tr><?php foreach ($row as $cell): ?><?= fac_cell($cell) ?><?php endforeach; ?></tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<div class="fe-toolbar-row" style="justify-content:flex-end;">
  <div class="fe-toolbar-actions">
    <button type="button" class="fac-alert-icon fac-alert-btn alert-red fac-blink" id="atAlertRed" onclick="toggleAtAlerts('red')" title="<?= esc($alert_titles['red']) ?>" style="display:none"><i class="bi bi-exclamation-triangle-fill"></i> <strong id="atAlertRedCount"></strong></button>
    <button type="button" class="fac-alert-icon fac-alert-btn alert-yellow" id="atAlertYellow" onclick="toggleAtAlerts('yellow')" title="<?= esc($alert_titles['yellow']) ?>" style="display:none"><i class="bi bi-exclamation-triangle-fill"></i> <strong id="atAlertYellowCount"></strong></button>
  </div>
</div>
<div id="atAlertList" class="guard-card" style="display:none;padding:18px;margin-bottom:16px;"></div>

<div class="sub-tabs" id="atTabs">
  <?php foreach ($tabs as $i => $t): ?>
    <button type="button" class="sub-tab<?= $i === 0 ? ' active' : '' ?>" data-tab="<?= esc($t['key']) ?>" onclick="switchAtTab('<?= esc($t['key'], 'js') ?>')"><?= esc($t['label']) ?></button>
  <?php endforeach; ?>
</div>

<?php foreach ($tabs as $i => $t): ?>
  <div class="at-pane" id="at-pane-<?= esc($t['key']) ?>" style="<?= $i === 0 ? '' : 'display:none' ?>">
    <div class="table-wrap">
      <table class="sj-table">
        <thead><tr><?php foreach ($t['columns'] as $col): ?><th><?= esc($col) ?></th><?php endforeach; ?></tr></thead>
        <tbody>
          <?php if (empty($t['rows'])): ?>
            <tr><td colspan="<?= count($t['columns']) ?>" class="empty-row"><?= esc($t['empty']) ?></td></tr>
          <?php else: foreach ($t['rows'] as $row): ?>
            <tr><?php foreach ($row as $cell): ?><?= fac_cell($cell) ?><?php endforeach; ?></tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
<?php endforeach; ?>

<?php /* ===================== MODALS ===================== */ ?>
<?php if ($section === 'equipment'): ?>
<div class="modal" id="spEquipModal">
  <div class="modal-box modal-box-wide">
    <h3>Add Sports Equipment</h3>
    <form method="post" action="<?= base_url('sports-dept/equipment') ?>">
      <?= csrf_field() ?>
      <div class="form-grid2">
        <div class="fg"><label>Equipment Name <span class="required-mark">*</span></label><input type="text" name="asset_name" placeholder="e.g. Basketball (Molten GG7)" required></div>
        <div class="fg"><label>Code <span class="required-mark">*</span></label><input type="text" name="asset_code" placeholder="e.g. AST-41011" required></div>
        <div class="fg"><label>Location</label>
          <select name="location" id="spLocation" onchange="document.getElementById('spLocationOther').style.display = this.value === '__other' ? '' : 'none'">
            <option value="">— Select a Location —</option>
            <?php foreach ($locations as $l): ?><option value="<?= esc($l) ?>"><?= esc($l) ?></option><?php endforeach; ?>
            <option value="__other">Other (type a new location)…</option>
          </select>
          <input type="text" name="location_other" id="spLocationOther" placeholder="New location name" style="display:none;margin-top:6px;">
        </div>
        <div class="fg"><label>Condition</label>
          <select name="condition_status"><?php foreach ($conditions as $c): ?><option<?= $c === 'Good' ? ' selected' : '' ?>><?= esc($c) ?></option><?php endforeach; ?></select>
        </div>
        <div class="fg"><label>Property Custodian</label>
          <select name="custodian">
            <option value="">— Select the Custodian —</option>
            <?php if (!empty($custodians['custodians'])): ?>
            <optgroup label="Property / Equipment Custodians">
              <?php foreach ($custodians['custodians'] as $p): ?><option value="<?= esc($p['name']) ?>"><?= esc($p['label']) ?></option><?php endforeach; ?>
            </optgroup>
            <?php endif; ?>
            <optgroup label="Other Personnel">
              <?php foreach ($custodians['others'] as $p): ?><option value="<?= esc($p['name']) ?>"><?= esc($p['label']) ?></option><?php endforeach; ?>
            </optgroup>
          </select>
        </div>
      </div>
      <div class="modal-actions"><button type="button" onclick="closeAt('spEquipModal')">Cancel</button><button type="submit" class="btn-maroon">Add</button></div>
    </form>
  </div>
</div>
<?php endif; ?>

<script src="<?= base_url('Assets/js/table-tools.js') ?>?v=<?= @filemtime(FCPATH . 'Assets/js/table-tools.js') ?>"></script>
<script>
function esc(s) {
  return String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
}
const addByTab = <?= json_encode($addByTab) ?>;
const atAlerts = <?= $alerts_json ?>;
const atAlertCols = <?= json_encode($alert_cols) ?>;
const atAlertTitles = <?= json_encode($alert_titles) ?>;
let atOpenAlert = null;

function openAt(id) { document.getElementById(id).style.display = 'flex'; }
function closeAt(id) { document.getElementById(id).style.display = 'none'; }
function atWoToggle() {
  const veh = document.getElementById('atWoType').value === 'Vehicle Repair';
  document.getElementById('atWoVehicle').style.display = veh ? '' : 'none';
  document.getElementById('atWoEquip').style.display = veh ? 'none' : '';
}

function switchAtTab(key) {
  document.querySelectorAll('#atTabs .sub-tab').forEach(b => b.classList.toggle('active', b.dataset.tab === key));
  document.querySelectorAll('.at-pane').forEach(p => p.style.display = p.id === 'at-pane-' + key ? '' : 'none');
  const btn = document.getElementById('atAddBtn');
  const add = addByTab[key];
  btn.style.display = add ? '' : 'none';
  if (add) { btn.textContent = add[0]; btn.setAttribute('onclick', add[1]); }
}

function atBadge(text, level) {
  const cls = level === 'red' ? 'tt-badge zb-overdue badge-blink' : 'tt-badge zb-needs';
  return `<span class="${cls}">${esc(text)}</span>`;
}
function toggleAtAlerts(level) {
  const panel = document.getElementById('atAlertList');
  const same = atOpenAlert === level && panel.style.display !== 'none';
  document.querySelectorAll('.fac-alert-btn').forEach(b => b.classList.remove('active'));
  if (same) { panel.style.display = 'none'; atOpenAlert = null; return; }
  atOpenAlert = level;
  document.getElementById(level === 'red' ? 'atAlertRed' : 'atAlertYellow').classList.add('active');
  const items = atAlerts.filter(i => i.level === level);
  panel.style.display = '';
  panel.innerHTML = `<div class="gc-title"><i class="bi bi-exclamation-triangle-fill"></i> ${esc(atAlertTitles[level])} <span class="text-muted">(${items.length})</span></div>
    <div class="table-wrap"><table class="sj-table"><thead><tr>${atAlertCols.map(c => `<th>${esc(c)}</th>`).join('')}</tr></thead>
    <tbody>${items.map(i => `<tr>${i.cols.map((c, n) => n === i.cols.length - 1 ? `<td>${atBadge(c, level)}</td>` : `<td>${esc(c)}</td>`).join('')}</tr>`).join('')}</tbody></table></div>`;
}
['red', 'yellow'].forEach(level => {
  const n = atAlerts.filter(i => i.level === level).length;
  if (!n) return;
  const id = level === 'red' ? 'atAlertRed' : 'atAlertYellow';
  document.getElementById(id + 'Count').textContent = n;
  document.getElementById(id).style.display = '';
});

document.querySelectorAll('.at-pane').forEach(attachTableTools);
const _si = document.getElementById('statInline'); if (_si) attachTableTools(_si);
</script>
<?= $this->endSection() ?>
