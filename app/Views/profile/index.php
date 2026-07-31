<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php $user = $user ?? []; ?>

<link rel="stylesheet" href="<?= base_url('Assets/css/settings.css') ?>">

<div class="page-header">
    <div>
        <h1>My Profile</h1>
        <p class="page-subtitle">View and update your profile information.</p>
    </div>
</div>

<div class="profile-row">
    <div class="card profile-card">
        <div class="card-head">
            <h3>Profile Information</h3>
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
                <button type="submit" class="btn-maroon">Update Profile</button>
            </div>
        </form>
    </div>

    <div class="card profile-card">
        <div class="card-head">
            <h3>Change Password</h3>
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
                <button type="submit" class="btn-maroon">Change Password</button>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>
