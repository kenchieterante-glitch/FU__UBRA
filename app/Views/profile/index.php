<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php
  $user = $user ?? [];
  $fullName = $user['full_name'] ?? 'System User';
  $initials = strtoupper(substr($fullName, 0, 1) . (strpos($fullName, ' ') !== false ? substr($fullName, strpos($fullName, ' ') + 1, 1) : ''));
  $photoPath = !empty($user['photo']) ? base_url('uploads/' . $user['photo']) : null;
  $memberSince = !empty($user['created_at']) ? date('F Y', strtotime($user['created_at'])) : null;
?>

<link rel="stylesheet" href="<?= base_url('Assets/css/settings.css') . '?v=' . @filemtime(FCPATH.'Assets/css/settings.css') ?>">

<div class="page-header">
    <div>
        <h1>My Profile</h1>
        <p class="page-subtitle">View and update your profile information.</p>
    </div>
</div>

<?php if (!empty($flash_success)): ?>
    <div class="alert-success"><i class="fa-solid fa-circle-check"></i> <?= esc($flash_success) ?></div>
<?php endif; ?>
<?php if (!empty($flash_error)): ?>
    <div class="alert-error"><i class="fa-solid fa-circle-exclamation"></i> <?= esc($flash_error) ?></div>
<?php endif; ?>

<div class="profile-hero">
    <div class="profile-hero-banner"></div>
    <div class="profile-hero-body">
        <div class="profile-avatar">
            <?php if ($photoPath): ?>
                <img src="<?= esc($photoPath) ?>" alt="<?= esc($fullName) ?>">
            <?php else: ?>
                <span><?= esc($initials) ?></span>
            <?php endif; ?>
        </div>
        <div class="profile-hero-info">
            <h2><?= esc($fullName) ?></h2>
            <span class="profile-role-badge"><i class="fa-solid fa-shield-halved"></i> <?= esc($user['role'] ?? 'Staff') ?></span>
            <div class="profile-meta-row">
                <span><i class="fa-solid fa-id-badge"></i> <?= esc($user['emp_id'] ?? '—') ?></span>
                <span><i class="fa-solid fa-building"></i> <?= esc($user['department'] ?? '—') ?></span>
                <span><i class="fa-solid fa-envelope"></i> <?= esc($user['email'] ?? '—') ?></span>
                <?php if ($memberSince): ?>
                    <span><i class="fa-solid fa-calendar"></i> Member since <?= esc($memberSince) ?></span>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="profile-row">
    <div class="card profile-card">
        <div class="card-head">
            <h3><i class="fa-solid fa-user-pen"></i> Profile Information</h3>
        </div>
        <form method="post" action="<?= base_url('profile/updateProfile') ?>">
            <?= csrf_field() ?>
            <div class="form-grid">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="name" value="<?= esc($user['full_name'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label>Employee ID</label>
                    <input type="text" name="emp_id" value="<?= esc($user['emp_id'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" value="<?= esc($user['email'] ?? '') ?>" required>
                </div>
            </div>
            <div class="modal-actions" style="justify-content: flex-start;">
                <button type="submit" class="btn-maroon"><i class="fa-solid fa-floppy-disk"></i> Update Profile</button>
            </div>
        </form>
    </div>

    <div class="card profile-card">
        <div class="card-head">
            <h3><i class="fa-solid fa-lock"></i> Change Password</h3>
        </div>
        <form method="post" action="<?= base_url('profile/changePassword') ?>">
            <?= csrf_field() ?>
            <div class="form-grid">
                <div class="form-group">
                    <label>Current Password</label>
                    <input type="password" name="current_password" required>
                </div>
                <div class="form-group">
                    <label>New Password</label>
                    <input type="password" name="new_password" required minlength="8">
                </div>
            </div>
            <div class="modal-actions" style="justify-content: flex-start;">
                <button type="submit" class="btn-maroon"><i class="fa-solid fa-key"></i> Change Password</button>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>
