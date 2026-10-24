<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="page-header">
  <div>
    <h1>Scanner Test</h1>
    <p class="page-subtitle">Scan any barcode and see exactly what the scanner sends to the computer.</p>
  </div>
  <a class="btn-add" href="<?= base_url('guard-station') ?>" style="background:#fff;color:#7b0f1a;border:1px solid #7b0f1a;text-decoration:none;">Back to the Guard Station</a>
</div>

<style>
  .st-card { background: var(--surf, #fff); border: 1px solid var(--bdr, #e7e7ec); border-radius: 16px; padding: 22px; margin-bottom: 16px; box-shadow: 0 8px 24px rgba(20,20,40,.06); }
  .st-input { width: 100%; box-sizing: border-box; font-size: 22px; padding: 16px 18px; border: 2px solid #7b0f1a; border-radius: 12px; background: var(--surf, #fff); color: var(--txt, #111); }
  .st-big { font-size: 28px; font-weight: 800; font-family: monospace; word-break: break-all; color: var(--txt, #111); }
  .st-row { display: flex; gap: 10px; align-items: baseline; padding: 8px 0; border-bottom: 1px solid var(--bdr, #eee); font-size: 16px; color: var(--txt, #111); }
  .st-row b { min-width: 190px; }
  .st-chars { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 8px; }
  .st-chars span { border: 1px solid #bbb; border-radius: 6px; padding: 4px 8px; font-family: monospace; font-size: 15px; text-align: center; color: var(--txt, #111); }
  .st-chars small { display: block; font-size: 11px; opacity: .7; }
  .st-verdict { margin-top: 14px; padding: 14px 16px; border-radius: 12px; font-size: 17px; font-weight: 800; }
  .st-ok { background: #e6f4ea; color: #1e7e34; } .st-warn { background: #fff4cc; color: #8a6100; } .st-bad { background: #fde7e7; color: #c62828; }
  .st-list li { font-size: 16px; margin: 6px 0; color: var(--txt, #111); }
</style>

<div class="st-card">
  <label for="stInput" style="font-weight:800;font-size:16px;display:block;margin-bottom:8px;">Click this box, then scan a barcode</label>
  <input type="text" id="stInput" class="st-input" autocomplete="off" autofocus placeholder="Scan here…">
  <div id="stResult" style="margin-top:18px;display:none;">
    <div class="st-row"><b>What was received</b><span class="st-big" id="stRaw"></span></div>
    <div class="st-row"><b>Number of characters</b><span id="stLen"></span></div>
    <div class="st-row"><b>Enter key sent at the end</b><span id="stEnter"></span></div>
    <div class="st-row"><b>Speed</b><span id="stSpeed"></span></div>
    <div class="st-row"><b>Looks like a number only</b><span id="stNum"></span></div>
    <div class="st-chars" id="stChars"></div>
    <div class="st-verdict" id="stVerdict"></div>
  </div>
</div>

<div class="st-card">
  <div class="dp-section-title" style="margin-top:0"><i class="bi bi-life-preserver"></i> If nothing appears when you scan</div>
  <ol class="st-list">
    <li><b>The scanner may not be in keyboard mode.</b> In the scanner's manual, find "USB keyboard / HID" and scan that setup barcode.</li>
    <li><b>The scanner may not read this type of barcode.</b> Some ID cards use a QR code or a 2D barcode (PDF417). A plain 1D scanner can't read those. Try scanning one of the barcodes from the printed test sheet to compare.</li>
    <li><b>The barcode may be hard to read.</b> Hold the scanner 5 to 15 cm from the card, keep the card flat, and avoid glare from the plastic.</li>
    <li><b>Something else is selected.</b> Click the big box above first.</li>
  </ol>
</div>

<script>
let times = [], buf = '';
const el = id => document.getElementById(id);
const box = el('stInput');
box.addEventListener('keydown', e => {
  if (e.key === 'Enter') { e.preventDefault(); show(true); return; }
  if (e.key.length === 1) { times.push(performance.now()); buf += e.key; }
});
// some scanners send no Enter: show the result after a short pause
box.addEventListener('input', () => { clearTimeout(window.__stT); window.__stT = setTimeout(() => { if (box.value.length >= 4) show(false); }, 600); });

function show(enter) {
  const raw = box.value || buf;
  if (!raw) return;
  const n = times.length;
  const avg = n > 1 ? (times[n - 1] - times[0]) / (n - 1) : 0;
  el('stResult').style.display = '';
  el('stRaw').textContent = '"' + raw + '"';
  el('stLen').textContent = raw.length;
  el('stEnter').textContent = enter ? 'Yes' : 'No — nothing was pressed after the last character';
  el('stSpeed').textContent = n > 1 ? avg.toFixed(1) + ' ms per character (' + (avg < 50 ? 'scanner speed' : 'typing speed') + ')' : '—';
  el('stNum').textContent = /^\d+$/.test(raw) ? 'Yes' : 'No — it has letters or symbols';
  el('stChars').innerHTML = raw.split('').map(c => '<span>' + (c === ' ' ? '␠' : c.replace(/&/g, '&amp;').replace(/</g, '&lt;')) + '<small>' + c.charCodeAt(0) + '</small></span>').join('');
  const v = el('stVerdict');
  if (n > 1 && avg >= 50) { v.className = 'st-verdict st-warn'; v.textContent = 'This was typed by hand, not scanned. Scan the barcode with the scanner.'; }
  else if (!enter) { v.className = 'st-verdict st-warn'; v.textContent = 'The scanner reads the barcode but does not press Enter. Tell the developer — the desk can be set to accept it without Enter.'; }
  else { v.className = 'st-verdict st-ok'; v.textContent = 'Good: the scanner works. The desk will receive "' + raw + '". If that is not the ID number printed on the card, tell the developer what it shows.'; }
  box.value = ''; buf = ''; times = [];
  box.focus();
}
</script>
<?= $this->endSection() ?>
