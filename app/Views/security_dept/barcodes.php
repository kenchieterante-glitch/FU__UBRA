<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Barcodes for the guard desk</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
  @page { margin: 12mm; }
  * { box-sizing: border-box; }
  body { font-family: Arial, Helvetica, sans-serif; color: #111; margin: 0; padding: 18px 24px; background: #fff; }
  .top { display: flex; align-items: center; justify-content: space-between; gap: 12px; border-bottom: 3px solid #7b0f1a; padding-bottom: 10px; margin-bottom: 16px; }
  h1 { margin: 0; font-size: 20px; color: #7b0f1a; }
  .sub { font-size: 13px; margin-top: 3px; }
  button { background: #7b0f1a; color: #fff; border: 0; border-radius: 8px; padding: 10px 18px; font-size: 15px; font-weight: 700; cursor: pointer; }
  h2 { font-size: 16px; margin: 20px 0 10px; color: #7b0f1a; }
  .grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; }
  .card { border: 1px dashed #777; border-radius: 8px; padding: 10px; text-align: center; page-break-inside: avoid; }
  .card .n { font-weight: 700; font-size: 13px; margin-bottom: 6px; min-height: 32px; }
  .card svg { width: 100%; height: 62px; }
  .card .c { font-size: 12px; letter-spacing: .5px; margin-top: 4px; font-family: monospace; }
  .tip { background: #fbf3f4; border: 1px solid #e3c6c9; border-radius: 8px; padding: 10px 14px; font-size: 13px; margin-bottom: 8px; }
  @media print { button, .tip, #staffFind { display: none; } body { padding: 0; } }
</style>
</head>
<body>
<div class="top">
  <div>
    <h1>Barcodes for the guard desk</h1>
    <div class="sub">Foundation University — UBRA · Code 128, readable by any barcode scanner</div>
  </div>
  <div style="display:flex;gap:8px;"><a href="<?= base_url(session()->get('role') && strtolower((string) session()->get('role')) === 'guard' ? 'guard-station/barcodes/pdf' : 'security-dept/barcodes/pdf') ?>" style="background:#fff;color:#7b0f1a;border:2px solid #7b0f1a;border-radius:8px;padding:8px 16px;font-size:15px;font-weight:700;text-decoration:none;">Download PDF</a><button type="button" onclick="window.print()">Print</button></div>
</div>

<div class="tip">To test: open the <strong>Guard Station</strong> (guard account) or <strong>Safety &amp; Security → Guard Monitoring → Key Borrowing</strong>, choose <strong>Lend a key</strong>, scan a <strong>staff ID</strong> below, then scan a <strong>key</strong> below. You can scan straight from the screen or from this printed sheet.</div>

<h2>Keys</h2>
<div class="grid">
  <?php foreach ($keys as $k): ?>
    <div class="card">
      <div class="n"><?= esc($k['key_name']) ?></div>
      <div class="bc" data-code="<?= esc($k['nfc_uid']) ?>"></div>
      <div class="c"><?= esc($k['nfc_uid']) ?></div>
    </div>
  <?php endforeach; ?>
  <?php if (!$keys): ?><div class="sub">No keys registered yet.</div><?php endif; ?>
</div>

<h2>Staff IDs</h2>
<div class="tip" style="display:block">If a staff member's own ID card cannot be scanned, print their barcode here and stick it on the card. Type a name or ID to find them.</div>
<input type="text" id="staffFind" placeholder="Search a name or ID…" style="width:100%;max-width:420px;padding:10px 14px;font-size:15px;border:1px solid #bbb;border-radius:8px;margin-bottom:12px;">
<div class="grid">
  <?php foreach ($staff as $p): ?>
    <div class="card staff-card" data-find="<?= esc(strtolower($p['full_name'] . ' ' . $p['emp_id'])) ?>">
      <div class="n"><?= esc($p['full_name']) ?></div>
      <div class="bc" data-code="<?= esc($p['emp_id']) ?>"></div>
      <div class="c"><?= esc($p['emp_id']) ?></div>
    </div>
  <?php endforeach; ?>
  <?php if (!$staff): ?><div class="sub">No staff records yet.</div><?php endif; ?>
</div>

<script src="<?= base_url('Assets/js/code128.js') ?>"></script>
<script>
document.getElementById('staffFind').addEventListener('input', e => { const q = e.target.value.trim().toLowerCase(); document.querySelectorAll('.staff-card').forEach(c => { c.style.display = !q || c.dataset.find.includes(q) ? '' : 'none'; }); });
document.querySelectorAll('.bc').forEach(el => { el.innerHTML = code128Svg(el.dataset.code, { module: 2, height: 62 }); });
</script>
</body>
</html>
