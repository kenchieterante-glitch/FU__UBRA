    <!-- Guard desk: lend or receive a key — scan with the wired 2D barcode scanner, or type / pick by hand -->
    <style>
      .gd-card { background: var(--surf, #fff); border: 1px solid var(--bdr, #e7e7ec); border-radius: 16px; padding: 20px 22px; margin: 4px 0 18px; box-shadow: 0 8px 24px rgba(20, 20, 40, .06); }
      .gd-row { display: flex; gap: 12px; flex-wrap: wrap; align-items: center; margin-bottom: 14px; }
      .gd-seg { display: inline-flex; background: rgba(123, 15, 26, .08); border-radius: 12px; padding: 4px; }
      .gd-seg button { border: 0; background: transparent; padding: 10px 18px; border-radius: 9px; font-size: 15px; font-weight: 800; color: var(--txt, #111); cursor: pointer; }
      .gd-seg button.on { background: #7b0f1a; color: #fff; box-shadow: 0 4px 12px rgba(123, 15, 26, .28); }
      .gd-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
      .gd-field label { display: block; font-size: 14px; font-weight: 800; margin-bottom: 6px; color: var(--txt, #111); }
      .gd-field input, .gd-field select { width: 100%; box-sizing: border-box; font-size: 17px; padding: 12px 14px; border: 1px solid var(--bdr, #d8d8df); border-radius: 10px; background: var(--surf, #fff); color: var(--txt, #111); }
      .gd-field input:focus, .gd-field select:focus { outline: 3px solid rgba(123, 15, 26, .25); border-color: #7b0f1a; }
      .gd-hint { font-size: 14px; color: var(--txt, #111); margin-top: 6px; min-height: 20px; }
      .gd-who { display: inline-block; background: #e6f4ea; color: #1e7e34; font-weight: 800; padding: 4px 12px; border-radius: 999px; }
      .gd-who.bad { background: #fde7e7; color: #c62828; }
      .gd-go { background: #7b0f1a; color: #fff; border: 0; border-radius: 12px; padding: 13px 26px; font-size: 16px; font-weight: 800; cursor: pointer; }
      .gd-msg { margin-top: 14px; padding: 12px 16px; border-radius: 12px; font-size: 16px; font-weight: 800; display: none; }
      .gd-msg.ok { display: block; background: #e6f4ea; color: #1e7e34; }
      .gd-msg.err { display: block; background: #fde7e7; color: #c62828; }
      .gd-link { margin-left: auto; font-size: 14px; font-weight: 800; color: #7b0f1a; text-decoration: none; }
      .gd-link:hover { text-decoration: underline; }
      .gd-ready { font-size: 14px; font-weight: 700; display: inline-flex; align-items: center; gap: 6px; }
      .gd-ready i { font-size: 9px; }
      .gd-ready.wait { color: #6b7280; }   /* nothing scanned yet */
      .gd-ready.click { color: #b45309; }  /* the scan box is not selected */
      .gd-ready.ok { color: #1e7e34; }     /* a real scanner has been detected */
      @media (max-width: 800px) { .gd-grid { grid-template-columns: 1fr; } }
    </style>
    <div class="gd-card" id="gdCard">
      <div class="gd-row">
        <div class="gd-seg" id="gdMode"><button type="button" class="on" data-m="borrow" onclick="gdSetMode('borrow')"><i class="bi bi-box-arrow-up-right"></i> Lend a key</button><button type="button" data-m="return" onclick="gdSetMode('return')"><i class="bi bi-box-arrow-in-down-left"></i> Receive a key back</button></div>
        <div class="gd-seg" id="gdMethod"><button type="button" class="on" data-t="scan" onclick="gdSetMethod('scan')"><i class="bi bi-upc-scan"></i> Scan with barcode scanner</button><button type="button" data-t="type" onclick="gdSetMethod('type')"><i class="bi bi-keyboard"></i> Type / pick by hand</button></div>
        <span class="gd-ready" id="gdReady"></span>
        <a href="<?= base_url('guard-station/scanner-test') ?>" target="_blank" class="gd-link" style="margin-left:auto"><i class="bi bi-activity"></i> Scanner test</a>
        <a href="<?= base_url($barcodeUrl) ?>" target="_blank" class="gd-link" style="margin-left:14px"><i class="bi bi-printer"></i> Print barcodes to test the scanner</a>
      </div>

      <div class="gd-grid">
        <div class="gd-field" id="gdBorrowerBox">
          <label id="gdBorrowerLabel" for="gdBorrower">1. Borrower's ID</label>
          <input type="text" id="gdBorrower" autocomplete="off" placeholder="Scan the ID card barcode">
          <div class="gd-hint" id="gdWho"></div>
        </div>
        <div class="gd-field" id="gdKeyBox">
          <label id="gdKeyLabel" for="gdKey">2. The key's tag</label>
          <input type="text" id="gdKey" autocomplete="off" placeholder="Scan the key's barcode or QR tag">
          <select id="gdKeyPick" style="display:none"></select>
          <div class="gd-hint" id="gdKeyHint"></div>
        </div>
      </div>

      <div class="gd-row" style="margin:14px 0 0;">
        <button type="button" class="gd-go" id="gdGo" onclick="gdSubmit()">Lend key</button>
        <span class="gd-hint" id="gdTip"></span>
      </div>
      <div class="gd-msg" id="gdMsg"></div>
    </div>
<script>
// ---- Guard desk: lend / receive keys (wired 2D barcode scanner or manual)
function gdEsc(s) { return String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c])); }
let gdKeys = <?= json_encode(array_map(fn($k) => ['id' => (int) $k['id'], 'name' => $k['key_name'], 'place' => $k['location'], 'status' => $k['status'], 'uid' => $k['nfc_uid']], $guard_keys ?? []), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
let gdMode = 'borrow', gdMethod = 'scan', gdLookupTimer = null, gdBorrowerOk = false;
const gdEl = id => document.getElementById(id);
const gdCsrf = <?= json_encode(csrf_hash()) ?>;
const gdPost = (url, data) => fetch(url, { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/x-www-form-urlencoded' }, body: new URLSearchParams(Object.assign({ csrf_test_name: gdCsrf }, data)) }).then(r => r.json().then(j => Object.assign({ _status: r.status }, j)));

function gdMsg(text, ok) { const m = gdEl('gdMsg'); m.className = 'gd-msg ' + (ok ? 'ok' : 'err'); m.textContent = text; }
function gdClearMsg() { gdEl('gdMsg').className = 'gd-msg'; }
function gdSetMode(m) { gdMode = m; document.querySelectorAll('#gdMode button').forEach(b => b.classList.toggle('on', b.dataset.m === m)); gdLayout(); }
function gdSetMethod(t) { gdMethod = t; document.querySelectorAll('#gdMethod button').forEach(b => b.classList.toggle('on', b.dataset.t === t)); gdLayout(); }
function gdFillPick() {
  const list = gdMode === 'borrow' ? gdKeys.filter(k => k.status !== 'Borrowed') : gdKeys.filter(k => k.status === 'Borrowed');
  gdEl('gdKeyPick').innerHTML = '<option value="">— ' + (gdMode === 'borrow' ? 'Pick an available key' : 'Pick a key that is out') + ' —</option>' + list.map(k => '<option value="' + k.id + '">' + gdEsc(k.name) + (k.place ? ' — ' + gdEsc(k.place) : '') + '</option>').join('');
}
function gdLayout() {
  const scan = gdMethod === 'scan', borrow = gdMode === 'borrow';
  gdEl('gdBorrowerBox').style.display = borrow ? '' : 'none';
  gdEl('gdKeyBox').style.gridColumn = borrow ? '' : '1 / -1';
  gdEl('gdBorrowerLabel').textContent = scan ? "1. Scan the borrower's ID" : "1. Borrower's ID (type it)";
  gdEl('gdBorrower').placeholder = scan ? 'Scan the ID card barcode' : 'Type the employee ID';
  gdEl('gdKeyLabel').textContent = (borrow ? '2. ' : '1. ') + (scan ? "Scan the key's tag" : 'Pick the key (or type its name / tag)');
  gdEl('gdKey').placeholder = scan ? "Scan the key's barcode or QR tag" : 'Or type the key name / tag';
  gdEl('gdKeyPick').style.display = scan ? 'none' : '';
  gdEl('gdGo').textContent = borrow ? 'Lend key' : 'Receive key';
  gdEl('gdGo').style.display = scan ? 'none' : '';
  gdEl('gdTip').textContent = scan ? (borrow ? 'The scanner types the code and presses Enter — scan the ID, then scan the key.' : 'Scan the key tag. It is received as soon as the scanner presses Enter.') : '';
  gdUpdateReady();
  gdFillPick(); gdResetFields(); gdClearMsg();
}
function gdResetFields() {
  gdEl('gdBorrower').value = ''; gdEl('gdKey').value = ''; gdEl('gdKeyPick').value = '';
  gdEl('gdWho').innerHTML = ''; gdEl('gdKeyHint').textContent = ''; gdBorrowerOk = false;
  gdFocusFirst();
}
// A USB barcode scanner is just a keyboard to the browser, so it cannot be "detected" by plugging in.
// What can be seen: a real scan types a whole code within a few milliseconds and ends with Enter.
// So the label stays grey until the first real scan arrives, and amber while the scan box is not selected.
let gdScannerSeen = false, gdLastScan = '', gdKeyTimes = [];
function gdUpdateReady() {
  const el = gdEl('gdReady');
  if (gdMethod !== 'scan') { el.className = 'gd-ready'; el.innerHTML = ''; return; }
  const active = document.activeElement && (document.activeElement.id === 'gdBorrower' || document.activeElement.id === 'gdKey');
  if (gdScannerSeen) { el.className = 'gd-ready ok'; el.innerHTML = '<i class="bi bi-circle-fill"></i> Scanner detected — last scan ' + gdEsc(gdLastScan); }
  else if (!active) { el.className = 'gd-ready click'; el.innerHTML = '<i class="bi bi-circle-fill"></i> Click the scan box first, then scan'; }
  else { el.className = 'gd-ready wait'; el.innerHTML = '<i class="bi bi-circle-fill"></i> Waiting for the scanner — scan any barcode to check it'; }
}
function gdNoteKey(e) {
  if (e.key === 'Enter') {
    const n = gdKeyTimes.length;
    if (n >= 6 && (gdKeyTimes[n - 1] - gdKeyTimes[0]) / (n - 1) < 50) { // a person types ~150-300 ms per key; a scanner ~1-10 ms
      gdScannerSeen = true;
      gdLastScan = new Date().toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' });
    }
    gdKeyTimes = [];
    gdUpdateReady();
  } else if (e.key.length === 1) {
    gdKeyTimes.push(performance.now());
  }
}
let gdIgnoreKeyUntil = 0;
async function gdBorrowerDone() {
  const v = gdEl('gdBorrower').value.trim(); if (!v) return;
  clearTimeout(gdLookupTimer); clearTimeout(gdPause);
  if (await gdLookup(v)) {
    gdIgnoreKeyUntil = Date.now() + 700; // anything the same card scan sends right after is not a key
    gdEl('gdKey').value = '';
    gdEl('gdKey').focus();
  } else gdMsg('That ID is not a registered staff or faculty member.', false);
}
function gdKeyDone() {
  clearTimeout(gdPause);
  if (Date.now() < gdIgnoreKeyUntil) { gdEl('gdKey').value = ''; return; } // second code from the same card scan
  if (gdEl('gdKey').value.trim()) gdSubmit();
}
let gdPause = null;
function gdArmPause(id) {
  clearTimeout(gdPause);
  if (gdMethod !== 'scan') return;
  const n = gdKeyTimes.length;
  // a scan types many characters a few milliseconds apart; if it then goes quiet, treat it as finished
  if (n >= 4 && (gdKeyTimes[n - 1] - gdKeyTimes[0]) / (n - 1) < 40) {
    gdPause = setTimeout(() => {
      gdKeyTimes = [];
      gdScannerSeen = true; gdLastScan = new Date().toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' }); gdUpdateReady();
      if (id === 'gdBorrower') gdBorrowerDone(); else gdKeyDone();
    }, 250);
  }
}
function gdFocusFirst() { setTimeout(() => (gdMode === 'borrow' ? gdEl('gdBorrower') : gdEl('gdKey')).focus(), 30); }

// Answers can arrive out of order (typing "20230407" also looks up "20" and "2023040" on the way).
// Only the answer for the newest lookup may change what is shown.
let gdLookupSeq = 0;
async function gdLookup(id) {
  const who = gdEl('gdWho');
  const seq = ++gdLookupSeq;
  if (!id) { who.innerHTML = ''; gdBorrowerOk = false; return false; }
  try {
    const res = await fetch('<?= base_url($deskBase . '/lookup') ?>?code=' + encodeURIComponent(id));
    if (seq !== gdLookupSeq) return gdBorrowerOk; // a newer lookup has started — ignore this late answer
    if (!res.ok) { who.innerHTML = '<span class="gd-who bad">No staff or faculty found for this ID</span>'; gdBorrowerOk = false; return false; }
    const d = await res.json();
    if (seq !== gdLookupSeq) return gdBorrowerOk;
    if (d.emp_id && gdEl('gdBorrower').value.trim() === id) gdEl('gdBorrower').value = d.emp_id;
    who.innerHTML = '<span class="gd-who"><i class="bi bi-person-check-fill"></i> ' + gdEsc(d.full_name) + (d.department ? ' — ' + gdEsc(d.department) : '') + '</span>';
    gdBorrowerOk = true; return true;
  } catch (e) { return false; }
}
async function gdSubmit() {
  gdClearMsg();
  const data = { key_code: gdEl('gdKey').value.trim(), key_id: gdEl('gdKeyPick').value };
  if (gdMode === 'borrow') data.borrower_id = gdEl('gdBorrower').value.trim();
  if (gdMode === 'borrow' && !data.borrower_id) { gdMsg("Scan or type the borrower's ID first.", false); gdEl('gdBorrower').focus(); return; }
  if (!data.key_code && !data.key_id) { gdMsg(gdMode === 'borrow' ? 'Now scan the key tag, or pick the key from the list.' : 'Scan the key tag, or pick the key from the list.', false); gdEl('gdKey').focus(); return; }
  const res = await gdPost('<?= base_url($deskBase . '/') ?>' + (gdMode === 'borrow' ? 'borrow' : 'return'), data);
  if (res.ok) {
    gdMsg(res.message, true);
    if (typeof uiToast === 'function') uiToast(res.message);
    gdKeys = res.keys; gdRenderOut(res.active);
    gdFillPick(); gdEl('gdBorrower').value = ''; gdEl('gdKey').value = ''; gdEl('gdKeyPick').value = ''; gdEl('gdWho').innerHTML = ''; gdBorrowerOk = false;
    gdFocusFirst();
  } else {
    gdMsg(res.message || 'Something went wrong.', false);
    gdEl('gdKey').value = ''; gdEl('gdKeyPick').value = '';
    (gdMode === 'borrow' && !gdBorrowerOk ? gdEl('gdBorrower') : gdEl('gdKey')).focus();
  }
}
function gdRenderOut(active) {
  const body = gdEl('gKeysOutBody');
  if (!body) return;
  const clickable = typeof showKeyDetail === 'function'; // the key-detail pop-up only exists on the Safety & Security page
  body.innerHTML = active.length ? active.map(l =>
    '<tr class="g-row"' + (clickable ? ' style="cursor:pointer" onclick="showKeyDetail(' + l.id + ')"' : '') + ' data-id="' + l.id + '" data-status="Borrowed" data-time="' + gdEsc(l.time) + '">' +
    '<td><strong>' + gdEsc(l.log) + '</strong></td><td>' + gdEsc(l.name) + '</td><td>' + gdEsc(l.dept) + '</td><td>' + gdEsc(l.key) + '</td><td>' + gdEsc(l.since) + '</td><td><span class="tt-badge zb-needs">Borrowed</span></td></tr>').join('')
    : '<tr><td colspan="6" class="empty-row">No keys are out right now.</td></tr>';
}
document.addEventListener('DOMContentLoaded', () => {
  if (!gdEl('gdCard')) return;
  ['gdBorrower', 'gdKey'].forEach(id => {
    gdEl(id).addEventListener('keydown', gdNoteKey, true);
    gdEl(id).addEventListener('focus', gdUpdateReady);
    gdEl(id).addEventListener('blur', () => setTimeout(gdUpdateReady, 60));
  });
  gdEl('gdBorrower').addEventListener('input', () => { gdLookupSeq++; gdEl('gdWho').innerHTML = ''; gdBorrowerOk = false; clearTimeout(gdLookupTimer); const v = gdEl('gdBorrower').value.trim(); gdLookupTimer = setTimeout(() => gdLookup(v), gdMethod === 'scan' ? 80 : 400); });
  // A scanner normally ends a scan with Enter. Some end with Tab, and some send nothing at all,
  // so all three are accepted: Enter, Tab, or a short pause right after fast typing.
  gdEl('gdBorrower').addEventListener('keydown', e => { if (e.key === 'Enter' || (e.key === 'Tab' && gdMethod === 'scan' && gdEl('gdBorrower').value.trim())) { e.preventDefault(); gdBorrowerDone(); } });
  gdEl('gdKey').addEventListener('keydown', e => { if (e.key === 'Enter' || (e.key === 'Tab' && gdMethod === 'scan' && gdEl('gdKey').value.trim())) { e.preventDefault(); gdKeyDone(); } });
  ['gdBorrower', 'gdKey'].forEach(id => gdEl(id).addEventListener('input', () => gdArmPause(id)));
  gdEl('gdKeyPick').addEventListener('change', () => { gdEl('gdKeyHint').textContent = ''; });
  // Scan anywhere on the page: if the scan box isn't selected when a scan starts, send it to the right box.
  document.addEventListener('keydown', e => {
    if (gdMethod !== 'scan' || e.ctrlKey || e.altKey || e.metaKey) return;
    const t = e.target;
    if (t && /^(INPUT|TEXTAREA|SELECT)$/.test(t.tagName)) return;
    if (e.key.length !== 1) return;
    const key = gdEl('gdKey'), who = gdEl('gdBorrower');
    (gdMode === 'borrow' && !(gdBorrowerOk && who.value) ? who : key).focus();
  }, true);
  gdLayout();
});
</script>
