<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php
  $user = $user ?? [];
  $fullName = $user['full_name'] ?? 'System User';
  $parts = preg_split('/\s+/', trim($fullName));
  $initials = strtoupper(substr($parts[0] ?? '', 0, 1) . (count($parts) > 1 ? substr(end($parts), 0, 1) : ''));
  $photoPath = !empty($user['photo']) ? base_url('uploads/' . $user['photo']) : null;
  $memberSince = !empty($user['created_at']) ? date('F Y', strtotime($user['created_at'])) : null;
  $role = $user['role'] ?? 'Staff';
?>

<div class="pf">

  <!-- ── Cover ──────────────────────────────────────────────── -->
  <section class="pf-cover">
    <div class="pf-banner"></div>
    <div class="pf-head">
      <div class="pf-avatar" id="pfAvatar">
        <?php if ($photoPath): ?><img src="<?= esc($photoPath) ?>" alt="<?= esc($fullName) ?>"><?php else: ?><span><?= esc($initials) ?></span><?php endif; ?>
      </div>
      <div class="pf-id">
        <h1 class="pf-name"><?= esc($fullName) ?></h1>
        <div class="pf-sub">
          <span class="pf-role"><i class="bi bi-shield-fill-check"></i> <?= esc($role) ?></span>
          <span class="pf-status">Active account</span>
        </div>
      </div>
      <div class="pf-actions">
        <button type="button" class="pf-btn" data-goto="info"><i class="bi bi-pencil-square"></i> Edit profile</button>
        <button type="button" class="pf-btn" data-goto="security"><i class="bi bi-key"></i> Change password</button>
      </div>
    </div>
  </section>

  <div class="pf-grid">

    <!-- ── Summary sidebar ─────────────────────────────────── -->
    <aside class="pf-side">
      <div class="pf-card">
        <div class="pf-card-h">About</div>
        <ul class="pf-list">
          <li><i class="bi bi-person-vcard"></i><div><small>Employee ID</small><span><?= esc($user['emp_id'] ?? '—') ?></span></div></li>
          <li><i class="bi bi-envelope"></i><div><small>Email</small><span><?= esc($user['email'] ?? '—') ?></span></div></li>
          <li><i class="bi bi-person-gear"></i><div><small>Role</small><span><?= esc($role) ?></span></div></li>
          <?php if (!empty($user['department'])): ?>
          <li><i class="bi bi-building"></i><div><small>Department</small><span><?= esc($user['department']) ?></span></div></li>
          <?php endif; ?>
          <?php if ($memberSince): ?>
          <li><i class="bi bi-calendar3"></i><div><small>Member since</small><span><?= esc($memberSince) ?></span></div></li>
          <?php endif; ?>
        </ul>
      </div>
      <div class="pf-card">
        <div class="pf-tip"><strong><i class="bi bi-shield-lock"></i> Keep your account safe</strong>Use a password you don't use anywhere else, and sign out when you leave a shared computer.</div>
      </div>
    </aside>

    <!-- ── Main panel ──────────────────────────────────────── -->
    <main class="pf-card">
      <div class="pf-tabs" role="tablist">
        <button type="button" class="pf-tab active" role="tab" data-tab="info"><i class="bi bi-person"></i> Personal information</button>
        <button type="button" class="pf-tab" role="tab" data-tab="security"><i class="bi bi-lock"></i> Security</button>
      </div>

      <!-- Personal information -->
      <section class="pf-panel active" id="pf-info" role="tabpanel">
        <h2 class="pf-title">Personal information</h2>
        <p class="pf-desc">Your name, employee ID and email as they appear across UBRA.</p>
        <form method="post" action="<?= base_url('profile/updateProfile') ?>" enctype="multipart/form-data" id="pfInfoForm" novalidate>
          <?= csrf_field() ?>
          <div class="pf-fields">
            <div class="pf-field full">
              <label>Profile picture</label>
              <div class="pf-photo">
                <div class="pf-avatar-sm" id="pfPhotoPreview"><?php if ($photoPath): ?><img src="<?= esc($photoPath) ?>" alt=""><?php else: ?><span><?= esc($initials) ?></span><?php endif; ?></div>
                <div class="pf-photo-text">
                  <strong id="pfPhotoName">Upload a new photo</strong>
                  <small>JPG, PNG or WEBP, up to 2&nbsp;MB.</small>
                </div>
                <div style="margin-left:auto;display:flex;gap:8px;">
                  <label class="pf-btn" for="pfPhoto" style="margin:0;cursor:pointer;"><i class="bi bi-camera"></i> Choose photo</label>
                </div>
                <input type="file" name="photo" id="pfPhoto" accept="image/png,image/jpeg,image/webp">
              </div>
              <span class="pf-err" id="pfPhotoErr">Please choose a JPG, PNG or WEBP image under 2 MB.</span>
            </div>
            <div class="pf-field">
              <label for="pfName">Full name</label>
              <div class="pf-input"><i class="bi bi-person lead"></i><input type="text" id="pfName" name="name" value="<?= esc($user['full_name'] ?? '') ?>" required></div>
              <span class="pf-err">Full name is required.</span>
            </div>
            <div class="pf-field">
              <label for="pfEmp">Employee ID</label>
              <div class="pf-input"><i class="bi bi-person-vcard lead"></i><input type="text" id="pfEmp" name="emp_id" value="<?= esc($user['emp_id'] ?? '') ?>" required></div>
              <span class="pf-help">This is also your sign-in ID.</span>
              <span class="pf-err">Employee ID is required.</span>
            </div>
            <div class="pf-field full">
              <label for="pfMail">Email address</label>
              <div class="pf-input"><i class="bi bi-envelope lead"></i><input type="email" id="pfMail" name="email" value="<?= esc($user['email'] ?? '') ?>" required></div>
              <span class="pf-err">Enter a valid email address.</span>
            </div>
          </div>
          <div class="pf-footer">
            <button type="submit" class="pf-btn primary" id="pfInfoSave" disabled><i class="bi bi-check2-circle"></i> Save changes</button>
            <button type="button" class="pf-btn link" id="pfInfoReset">Discard</button>
            <span class="pf-dirty" id="pfDirty"><i class="bi bi-exclamation-circle"></i> You have unsaved changes</span>
          </div>
        </form>
      </section>

      <!-- Security -->
      <section class="pf-panel" id="pf-security" role="tabpanel">
        <h2 class="pf-title">Change password</h2>
        <p class="pf-desc">Choose a strong password you don't use on other sites.</p>
        <form method="post" action="<?= base_url('profile/changePassword') ?>" id="pfPwForm" novalidate>
          <?= csrf_field() ?>
          <div class="pf-fields">
            <div class="pf-field full">
              <label for="pfCur">Current password</label>
              <div class="pf-input"><i class="bi bi-lock lead"></i><input type="password" id="pfCur" name="current_password" class="has-toggle" autocomplete="current-password" required><button type="button" class="pf-eye" data-eye="pfCur" aria-label="Show password"><i class="bi bi-eye"></i></button></div>
              <span class="pf-err">Enter your current password.</span>
            </div>
            <div class="pf-field">
              <label for="pfNew">New password</label>
              <div class="pf-input"><i class="bi bi-key lead"></i><input type="password" id="pfNew" name="new_password" class="has-toggle" autocomplete="new-password" minlength="8" required><button type="button" class="pf-eye" data-eye="pfNew" aria-label="Show password"><i class="bi bi-eye"></i></button></div>
              <div class="pf-meter"><b id="pfMeter"></b></div>
              <div class="pf-meter-label" id="pfMeterLabel">Enter a new password</div>
            </div>
            <div class="pf-field">
              <label for="pfConf">Confirm new password</label>
              <div class="pf-input"><i class="bi bi-check2-square lead"></i><input type="password" id="pfConf" name="confirm_password" class="has-toggle" autocomplete="new-password" required><button type="button" class="pf-eye" data-eye="pfConf" aria-label="Show password"><i class="bi bi-eye"></i></button></div>
              <span class="pf-err" id="pfConfErr">The passwords do not match.</span>
            </div>
            <div class="pf-field full">
              <ul class="pf-rules" id="pfRules">
                <li data-rule="len"><i class="bi bi-circle"></i> At least 8 characters</li>
                <li data-rule="case"><i class="bi bi-circle"></i> Upper and lower case letters</li>
                <li data-rule="num"><i class="bi bi-circle"></i> At least one number</li>
                <li data-rule="sym"><i class="bi bi-circle"></i> At least one symbol</li>
              </ul>
            </div>
          </div>
          <div class="pf-footer">
            <button type="submit" class="pf-btn primary" id="pfPwSave"><i class="bi bi-shield-check"></i> Update password</button>
            <span class="pf-help">You will stay signed in after changing it.</span>
          </div>
        </form>
      </section>
    </main>
  </div>
</div>

<script>
(function () {
  // ── Tabs (remembers #security in the address) ──
  function show(name) {
    document.querySelectorAll('.pf-tab').forEach(function (t) { t.classList.toggle('active', t.dataset.tab === name); });
    document.querySelectorAll('.pf-panel').forEach(function (p) { p.classList.toggle('active', p.id === 'pf-' + name); });
    try { history.replaceState(null, '', '#' + name); } catch (e) {}
  }
  document.querySelectorAll('[data-tab],[data-goto]').forEach(function (b) { b.addEventListener('click', function () { show(b.dataset.tab || b.dataset.goto); }); });
  var h = location.hash.replace('#', ''); if (h === 'security' || h === 'info') show(h);

  // ── Personal information: dirty tracking, validation, photo preview ──
  var form = document.getElementById('pfInfoForm'), save = document.getElementById('pfInfoSave'), dirty = document.getElementById('pfDirty');
  var initial = {}; ['pfName', 'pfEmp', 'pfMail'].forEach(function (id) { initial[id] = document.getElementById(id).value; });
  var photo = document.getElementById('pfPhoto');
  function isDirty() { return ['pfName', 'pfEmp', 'pfMail'].some(function (id) { return document.getElementById(id).value !== initial[id]; }) || (photo.files && photo.files.length > 0); }
  function refresh() { var d = isDirty(); save.disabled = !d; dirty.classList.toggle('show', d); }
  form.addEventListener('input', refresh);
  function setInvalid(input, bad) { input.closest('.pf-field').classList.toggle('invalid', bad); }
  form.addEventListener('submit', function (e) {
    var ok = true;
    [['pfName', function (v) { return v.trim() !== ''; }], ['pfEmp', function (v) { return v.trim() !== ''; }], ['pfMail', function (v) { return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v.trim()); }]].forEach(function (r) {
      var el = document.getElementById(r[0]), good = r[1](el.value); setInvalid(el, !good); if (!good) ok = false;
    });
    if (!ok) e.preventDefault();
  });
  document.getElementById('pfInfoReset').addEventListener('click', function () {
    form.reset(); document.getElementById('pfPhotoName').textContent = 'Upload a new photo'; refresh();
    form.querySelectorAll('.invalid').forEach(function (f) { f.classList.remove('invalid'); });
  });
  photo.addEventListener('change', function () {
    var f = photo.files[0], err = document.getElementById('pfPhotoErr'); err.style.display = 'none';
    if (!f) { refresh(); return; }
    if (!/^image\/(jpeg|png|webp)$/.test(f.type) || f.size > 2 * 1024 * 1024) { err.style.display = 'block'; photo.value = ''; refresh(); return; }
    document.getElementById('pfPhotoName').textContent = f.name;
    var r = new FileReader();
    r.onload = function (ev) {
      var src = ev.target.result;
      document.getElementById('pfPhotoPreview').innerHTML = '<img src="' + src + '" alt="">';
      document.getElementById('pfAvatar').innerHTML = '<img src="' + src + '" alt="">';
    };
    r.readAsDataURL(f); refresh();
  });

  // ── Password: show/hide, strength, match ──
  document.querySelectorAll('[data-eye]').forEach(function (b) {
    b.addEventListener('click', function () {
      var i = document.getElementById(b.dataset.eye), show = i.type === 'password'; i.type = show ? 'text' : 'password';
      b.querySelector('i').className = show ? 'bi bi-eye-slash' : 'bi bi-eye';
    });
  });
  var nw = document.getElementById('pfNew'), conf = document.getElementById('pfConf');
  function score(v) {
    var r = { len: v.length >= 8, 'case': /[a-z]/.test(v) && /[A-Z]/.test(v), num: /\d/.test(v), sym: /[^A-Za-z0-9]/.test(v) };
    Object.keys(r).forEach(function (k) {
      var li = document.querySelector('#pfRules [data-rule="' + k + '"]'); li.classList.toggle('ok', r[k]); li.querySelector('i').className = r[k] ? 'bi bi-check-circle-fill' : 'bi bi-circle';
    });
    return Object.keys(r).filter(function (k) { return r[k]; }).length;
  }
  nw.addEventListener('input', function () {
    var s = nw.value ? score(nw.value) : (score(''), 0), bar = document.getElementById('pfMeter'), lab = document.getElementById('pfMeterLabel');
    var names = ['Enter a new password', 'Weak', 'Fair', 'Good', 'Strong'], cols = ['#b3261e', '#b3261e', '#b26a00', '#2e7d32', '#2e7d32'];
    bar.style.width = (s * 25) + '%'; bar.style.background = cols[s]; lab.textContent = nw.value ? names[Math.max(s, 1)] : names[0];
    if (conf.value) check();
  });
  function check() { var bad = conf.value !== '' && conf.value !== nw.value; conf.closest('.pf-field').classList.toggle('invalid', bad); return !bad; }
  conf.addEventListener('input', check);
  document.getElementById('pfPwForm').addEventListener('submit', function (e) {
    var ok = true, cur = document.getElementById('pfCur');
    var curBad = cur.value === ''; cur.closest('.pf-field').classList.toggle('invalid', curBad); if (curBad) ok = false;
    if (nw.value.length < 8) { ok = false; nw.focus(); }
    if (conf.value !== nw.value) { conf.closest('.pf-field').classList.add('invalid'); ok = false; }
    if (!ok) e.preventDefault();
  });
})();
</script>

<?= $this->endSection() ?>
