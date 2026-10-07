<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
// Values the controller passes in — given safe defaults here so the page (and the editor) always know them.
$columns = $columns ?? [];
$rows = $rows ?? [];
$status = $status ?? [];
$details = $details ?? [];
$buildings = $buildings ?? [];
$floors_json = $floors_json ?? '{}';
$qr_keys_json = $qr_keys_json ?? '[]'; helper('facilities'); ?>
<div class="page-header">
  <div>
    <h1>List of Keys</h1>
    <p class="page-subtitle">Safety and Security Department — every key on record, where it is now, and who has it.</p>
  </div>
  <div style="display:flex;gap:10px;flex-wrap:wrap;">
    <button type="button" class="btn-add" style="background:#fff;color:#7b0f1a;border:1px solid #7b0f1a;" onclick="openSheetModal()"><i class="bi bi-file-earmark-pdf"></i> Print All QR Codes</button>
    <button type="button" class="btn-add" onclick="document.getElementById('keyAddModal').style.display='flex'">+ Add Key</button>
  </div>
</div>

<div class="stat-cards">
  <?php foreach ($status as $s): ?>
    <button type="button" class="stat-card status-pick" data-key="<?= esc($s['key']) ?>" onclick="pickKeyBox('<?= esc($s['key'], 'js') ?>')">
      <span class="stat-icon tone-<?= esc($s['tone']) ?>"><i class="bi <?= esc($s['icon']) ?>"></i></span>
      <h3><?= esc($s['label']) ?></h3>
      <div class="value"><?= esc((string) $s['value']) ?></div>
    </button>
  <?php endforeach; ?>
</div>

<div class="status-tables" style="margin-top:16px;">
  <?php foreach ($status as $c): $d = $details[$c['key']]; ?>
    <div class="status-table guard-card" id="st-<?= esc($c['key']) ?>" style="display:none;">
      <div class="gc-title"><i class="bi <?= esc($c['icon']) ?>"></i> <?= esc($d['title']) ?></div>
      <div class="table-wrap">
        <table class="sj-table">
          <thead><tr><?php foreach ($columns as $col): ?><th><?= esc($col) ?></th><?php endforeach; ?></tr></thead>
          <tbody>
            <?php if (empty($d['rows'])): ?>
              <tr><td colspan="<?= count($columns) ?>" class="empty-row">Nothing here right now.</td></tr>
            <?php else: foreach ($d['rows'] as $row): ?>
              <tr><?php foreach ($row as $cell): ?><?= fac_cell($cell) ?><?php endforeach; ?></tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<div class="at-pane">
  <div class="table-wrap">
    <table class="sj-table">
      <thead><tr><?php foreach ($columns as $col): ?><th><?= esc($col) ?></th><?php endforeach; ?></tr></thead>
      <tbody>
        <?php if (empty($rows)): ?>
          <tr><td colspan="<?= count($columns) ?>" class="empty-row">No keys on record yet.</td></tr>
        <?php else: foreach ($rows as $row): ?>
          <tr><?php foreach ($row as $cell): ?><?= fac_cell($cell) ?><?php endforeach; ?></tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="modal" id="keyAddModal">
  <div class="modal-box modal-box-wide">
    <h3>Add Key</h3>
    <form method="post" action="<?= base_url('security-dept/keys') ?>">
      <?= csrf_field() ?>
      <div class="form-grid2">
        <div class="fg"><label>Key Name <span class="required-mark">*</span></label><input type="text" name="key_name" placeholder="e.g. Gymnasium Storage Key" required></div>
        <div class="fg"><label>Location</label>
          <select name="location" onchange="fillKeyFloors(this.value)"><option value="">— Select a Building —</option><?php foreach ($buildings as $b): ?><option value="<?= esc($b) ?>"><?= esc($b) ?></option><?php endforeach; ?></select>
        </div>
        <div class="fg"><label>Floor</label><select name="floor" id="keyFloor"><option value="">— Select a Floor —</option></select></div>
        <div class="fg"><label>Tag ID (from the key's tag)</label><input type="text" name="nfc_uid" placeholder="Leave blank to generate one"></div>
      </div>
      <div class="modal-actions">
        <button type="button" onclick="document.getElementById('keyAddModal').style.display='none'">Cancel</button>
        <button type="submit" class="btn-maroon">Add</button>
      </div>
    </form>
  </div>
</div>

<div id="keyDeleteModal" class="sj-modal-overlay" style="display:none">
  <div class="sj-modal" style="max-width:380px;text-align:center;">
    <div class="sj-modal-body" style="padding:26px 24px 22px;">
      <div style="width:52px;height:52px;margin:0 auto 12px;border-radius:50%;background:#fdeaea;color:#c62828;display:flex;align-items:center;justify-content:center;font-size:24px;"><i class="bi bi-trash3"></i></div>
      <h4 style="margin:0 0 6px;font-size:18px;">Delete this key?</h4>
      <p id="keyDeleteName" style="margin:0 0 4px;font-weight:600;"></p>
      <p class="text-muted" style="margin:0 0 20px;font-size:13px;">It will be removed from the list. Its past borrowing records stay in the history.</p>
      <form id="keyDeleteForm" method="post" action="" style="display:flex;gap:10px;justify-content:center;">
        <?= csrf_field() ?>
        <button type="button" class="fp-btn secondary" onclick="document.getElementById('keyDeleteModal').style.display='none'">Cancel</button>
        <button type="submit" class="fp-btn">Delete</button>
      </form>
    </div>
  </div>
</div>

<div id="keySheetModal" class="sj-modal-overlay" style="display:none">
  <div class="sj-modal" style="max-width:420px;">
    <div class="sj-modal-header" style="display:flex;justify-content:space-between;align-items:center;">
      <h3>Print All QR Codes</h3>
      <button type="button" class="dp-close" onclick="document.getElementById('keySheetModal').style.display='none'" aria-label="Close"><i class="bi bi-x-lg"></i></button>
    </div>
    <div class="sj-modal-body">
      <div class="fg" style="margin-bottom:12px;"><label>Bond paper</label>
        <select id="sheetPaper" onchange="updateSheetInfo()">
          <option value="letter">Short bond — 8.5 × 11 in</option>
          <option value="a4">A4 — 210 × 297 mm</option>
          <option value="long">Long bond — 8.5 × 13 in</option>
        </select>
      </div>
      <div class="fg" style="margin-bottom:12px;"><label>QR codes on one sheet</label>
        <select id="sheetCount" onchange="updateSheetInfo()">
          <option value="6">6 (large)</option><option value="8">8</option><option value="12" selected>12</option><option value="15">15</option>
          <option value="20">20</option><option value="24">24</option><option value="30">30</option><option value="35">35</option><option value="48">48 (small)</option>
        </select>
      </div>
      <p id="sheetInfo" class="text-muted" style="font-size:13px;margin:0 0 14px;"></p>
      <div style="display:flex;gap:8px;justify-content:flex-end;">
        <button type="button" class="fp-btn secondary" onclick="document.getElementById('keySheetModal').style.display='none'">Cancel</button>
        <button type="button" class="fp-btn" onclick="makeSheetPdf()"><i class="bi bi-file-earmark-pdf"></i> Create PDF</button>
      </div>
      <p class="text-muted" style="font-size:12px;margin:12px 0 0;">The PDF opens in a new tab so you can check it, save it, and then print it. To print just one key, use that key's <strong>QR Code</strong> button instead.</p>
    </div>
  </div>
</div>

<div id="keyQrModal" class="sj-modal-overlay" style="display:none">
  <div class="sj-modal" style="max-width:380px;">
    <div class="sj-modal-header" style="display:flex;justify-content:space-between;align-items:center;">
      <h3>Key QR Code</h3>
      <button type="button" class="dp-close" onclick="document.getElementById('keyQrModal').style.display='none'" aria-label="Close"><i class="bi bi-x-lg"></i></button>
    </div>
    <div class="sj-modal-body" style="text-align:center;">
      <div id="keyQrBox" style="display:inline-block;padding:10px;background:#fff;"></div>
      <div id="keyQrName" style="font-weight:700;font-size:16px;margin-top:8px;"></div>
      <div id="keyQrPlace" class="text-muted" style="font-size:13px;"></div>
      <div id="keyQrUid" class="text-muted" style="font-size:11px;margin-top:2px;"></div>
      <div style="display:flex;gap:8px;justify-content:center;margin-top:16px;flex-wrap:wrap;">
        <button type="button" class="fp-btn" onclick="printKeyQr()"><i class="bi bi-printer"></i> Print this QR code</button>
      </div>
      <p class="text-muted" style="font-size:12px;margin:12px 0 0;">Prints just this key's QR code (30 × 38 mm). Cut along the dashed line and attach it to the key. Scanning it in the guard app identifies this key.</p>
    </div>
  </div>
</div>

<script src="<?= base_url('Assets/js/qrcode.min.js') ?>"></script>
<script>
const keyFloors = <?= $floors_json ?>;
const qrKeys = <?= $qr_keys_json ?>;
function esc(s) { return String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c])); }
function fillKeyFloors(building) {
  const sel = document.getElementById('keyFloor');
  const floors = building ? (keyFloors[building] || []) : [];
  sel.innerHTML = '<option value="">— Select a Floor —</option>' + floors.map(f => '<option>' + esc(f) + '</option>').join('');
}
// QR image (data URL) of a key's tag id — what the guard app reads when it scans the keychain.
function qrDataUrl(text, size) {
  const holder = document.createElement('div');
  new QRCode(holder, { text, width: size, height: size, correctLevel: QRCode.CorrectLevel.M });
  const canvas = holder.querySelector('canvas');
  return canvas ? canvas.toDataURL('image/png') : holder.querySelector('img').src;
}
let currentKey = null;
function showKeyQr(btn) {
  currentKey = { name: btn.dataset.name, uid: btn.dataset.uid, place: btn.dataset.place };
  const box = document.getElementById('keyQrBox');
  box.innerHTML = '<img alt="QR code" width="220" height="220" src="' + qrDataUrl(currentKey.uid, 220) + '">';
  document.getElementById('keyQrName').textContent = currentKey.name;
  document.getElementById('keyQrPlace').textContent = currentKey.place || '';
  document.getElementById('keyQrUid').textContent = 'Tag ID: ' + currentKey.uid;
  document.getElementById('keyQrModal').style.display = 'flex';
}
// One key QR code: 30 x 38 mm with a 26 mm QR code, printed at true size.
function printKeyQr() {
  if (!currentKey) return;
  const k = currentKey;
  const w = window.open('', '_blank', 'width=520,height=520');
  if (!w) { alert('Allow pop-ups for this site to print the QR code.'); return; }
  w.document.write('<!doctype html><title>Key QR code</title><style>' +
    '@page{size:auto;margin:8mm}body{margin:0;font-family:Arial,sans-serif}' +
    '.lb{width:30mm;height:38mm;box-sizing:border-box;border:0.25mm dashed #777;padding:1.5mm;text-align:center;overflow:hidden}' +
    '.lb img{width:26mm;height:26mm;display:block;margin:0 auto}' +
    '.n{font-weight:700;font-size:7pt;line-height:1.1;margin-top:1mm;max-height:8mm;overflow:hidden}' +
    '.p{font-size:5pt;color:#333;line-height:1.1;margin-top:.5mm;max-height:4mm;overflow:hidden}' +
    '</style><div class="lb"><img src="' + qrDataUrl(k.uid, 260) + '"><div class="n">' + esc(k.name) + '</div><div class="p">' + esc(k.place || '') + '</div></div>');
  w.document.close();
  setTimeout(() => { w.focus(); w.print(); }, 400);
}
// ---- Print all QR codes on bond paper, N to a sheet
function openSheetModal() { updateSheetInfo(); document.getElementById('keySheetModal').style.display = 'flex'; }
function updateSheetInfo() {
  const per = Number(document.getElementById('sheetCount').value);
  const pages = Math.max(1, Math.ceil(qrKeys.length / per));
  document.getElementById('sheetInfo').textContent = qrKeys.length
    ? qrKeys.length + ' key' + (qrKeys.length === 1 ? '' : 's') + ' with a QR code → ' + pages + ' sheet' + (pages === 1 ? '' : 's') + ' at ' + per + ' per sheet.'
    : 'No keys with a QR code yet — add keys to the list first.';
}
// Builds the PDF on the server (university header + N QR codes to a sheet) and opens it in a new tab.
function makeSheetPdf() {
  if (!qrKeys.length) return;
  const images = {};
  qrKeys.forEach(k => { images[k.uid] = qrDataUrl(k.uid, 300); });
  const form = document.createElement('form');
  form.method = 'post';
  form.action = '<?= base_url('security-dept/keys/pdf') ?>';
  form.target = '_blank';
  const add = (name, value) => { const i = document.createElement('input'); i.type = 'hidden'; i.name = name; i.value = value; form.appendChild(i); };
  add('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
  add('paper', document.getElementById('sheetPaper').value);
  add('per', document.getElementById('sheetCount').value);
  add('images', JSON.stringify(images));
  document.body.appendChild(form);
  form.submit();
  form.remove();
  document.getElementById('keySheetModal').style.display = 'none';
}

function askDeleteKey(btn) {
  document.getElementById('keyDeleteName').textContent = btn.dataset.name;
  document.getElementById('keyDeleteForm').action = '<?= base_url('security-dept/keys') ?>/' + btn.dataset.id + '/delete';
  document.getElementById('keyDeleteModal').style.display = 'flex';
}

function pickKeyBox(key) {
  const panel = document.getElementById('st-' + key);
  const wasOpen = panel.style.display !== 'none';
  document.querySelectorAll('.status-table').forEach(t => t.style.display = 'none');
  document.querySelectorAll('.status-pick').forEach(b => b.classList.toggle('active', !wasOpen && b.dataset.key === key));
  if (!wasOpen) panel.style.display = '';
}
</script>
<script src="<?= base_url('Assets/js/table-tools.js') ?>?v=<?= @filemtime(FCPATH . 'Assets/js/table-tools.js') ?>"></script>
<script>
document.querySelectorAll('.status-table').forEach(attachTableTools);
document.querySelectorAll('.at-pane').forEach(attachTableTools);
</script>
<?= $this->endSection() ?>
