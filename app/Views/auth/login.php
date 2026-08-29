<?= $this->extend('layouts/auth') ?>

<?= $this->section('content') ?>

<style>
.auth-badge {
    width: 88px;
    margin: 0 auto 18px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.auth-badge img {
    width: 100%;
    height: auto;
    display: block;
}

.auth-title-block {
    text-align: center;
    margin-bottom: 32px;
}

.auth-card h1 {
    font-size: 32px;
    margin: 0 0 6px;
}

.auth-subtitle {
    margin: 0;
    font-size: 14.5px;
}

.auth-error {
    background: #fdecea;
    color: #b3261e;
    padding: 12px 14px;
    border-radius: 10px;
    margin-bottom: 20px;
    font-size: 13.5px;
}

.auth-field {
    margin-bottom: 20px;
}

.auth-field-label-row {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
    margin-bottom: 8px;
}

.auth-field label {
    font-size: 13.5px;
    color: #333;
    font-weight: 500;
}

.auth-forgot-link {
    font-size: 13px;
    color: #800000;
    text-decoration: none;
    font-weight: 500;
}

.auth-forgot-link:hover {
    text-decoration: underline;
}

.auth-input-wrap {
    position: relative;
    display: flex;
    align-items: center;
}

.auth-input-wrap i.auth-input-icon {
    position: absolute;
    left: 14px;
    color: #999;
    font-size: 15px;
    pointer-events: none;
}

.auth-input-wrap input {
    width: 100%;
    padding: 12px 14px 12px 42px;
    border: 1px solid #e2e2e2;
    border-radius: 10px;
    font-size: 14px;
    font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
    color: #222;
    box-sizing: border-box;
    transition: border-color .15s ease, box-shadow .15s ease;
}

.auth-input-wrap.has-toggle input {
    padding-right: 44px;
}

.auth-input-wrap input:focus {
    outline: none;
    border-color: #800000;
    box-shadow: 0 0 0 3px rgba(128, 0, 0, .08);
}

.auth-toggle-pw {
    position: absolute;
    right: 12px;
    background: none;
    border: none;
    color: #999;
    cursor: pointer;
    font-size: 15px;
    padding: 4px;
    display: flex;
    align-items: center;
}

.auth-toggle-pw:hover {
    color: #800000;
}

.auth-remember-row {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 24px;
}

.auth-remember-row input[type="checkbox"] {
    width: 16px;
    height: 16px;
    accent-color: #800000;
    cursor: pointer;
}

.auth-remember-row label {
    font-size: 13.5px;
    color: #444;
    cursor: pointer;
}

.auth-submit {
    width: 100%;
    padding: 14px;
    background: #800000;
    color: #fff;
    border: none;
    border-radius: 10px;
    font-size: 15.5px;
    font-weight: 700;
    font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
    cursor: pointer;
    transition: background .15s ease;
}

.auth-submit:hover {
    background: #650000;
}

.auth-footer {
    text-align: center;
    margin-top: 24px;
    font-size: 12.5px;
    color: #999;
}
</style>

<div class="auth-badge"><img src="<?= base_url('images/' . rawurlencode('UBRA LOGO (no background).png')) ?>" alt="UBRA logo"></div>
<div class="auth-title-block">
    <h1>UBRA</h1>
    <p class="auth-subtitle">Sign in to your account</p>
</div>

<?php if (session()->getFlashdata('error')): ?>
    <div class="auth-error"><?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<form action="<?= base_url('login') ?>" method="post">
    <?= csrf_field() ?>

    <div class="auth-field">
        <label for="employee_id">Employee ID</label>
        <div class="auth-input-wrap">
            <i class="bi bi-person-vcard auth-input-icon"></i>
            <input type="text" id="employee_id" name="employee_id" required placeholder="e.g. 20230251" autocomplete="username">
        </div>
    </div>

    <div class="auth-field">
        <div class="auth-field-label-row">
            <label for="password">Password</label>
            <a href="#" class="auth-forgot-link" onclick="event.preventDefault(); alert('Please contact your system administrator to reset your password.');">Forgot password?</a>
        </div>
        <div class="auth-input-wrap has-toggle">
            <i class="bi bi-lock auth-input-icon"></i>
            <input type="password" id="password" name="password" required autocomplete="current-password">
            <button type="button" class="auth-toggle-pw" onclick="togglePasswordVisibility()" aria-label="Show password">
                <i class="bi bi-eye" id="pwToggleIcon"></i>
            </button>
        </div>
    </div>

    <div class="auth-remember-row">
        <input type="checkbox" id="remember" name="remember">
        <label for="remember">Remember me</label>
    </div>

    <button type="submit" class="auth-submit">Sign In</button>
</form>

<div class="auth-footer"><span style="font-family:'Times New Roman', Times, serif;">Foundation University</span> &middot; Operations Portal</div>

<script>
function togglePasswordVisibility() {
    const input = document.getElementById('password');
    const icon  = document.getElementById('pwToggleIcon');
    const show  = input.type === 'password';
    input.type  = show ? 'text' : 'password';
    icon.className = show ? 'bi bi-eye-slash' : 'bi bi-eye';
    document.querySelector('.auth-toggle-pw').setAttribute('aria-label', show ? 'Hide password' : 'Show password');
}
</script>

<?= $this->endSection() ?>
