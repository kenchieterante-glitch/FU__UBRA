<?= $this->extend('layouts/auth') ?>

<?= $this->section('content') ?>

<div style="max-width:400px; margin:0 auto; padding:0;">
    <div style="text-align:center; margin-bottom:30px;">
        <h1>FU-UBRA</h1>
        <p>Sign in to your account</p>
    </div>

    <?php if (session()->getFlashdata('error')): ?>
        <div style="background:#fee; color:#c33; padding:12px; border-radius:8px; margin-bottom:20px;">
            <?= session()->getFlashdata('error') ?>
        </div>
    <?php endif; ?>

    <form action="<?= base_url('login') ?>" method="post" style="background:#fff; padding:25px; border-radius:10px; box-shadow:0 2px 10px rgba(0,0,0,0.1);">
        <?= csrf_field() ?>

        <div style="margin-bottom:15px;">
            <label style="display:block; margin-bottom:5px;">Employee ID</label>
            <input type="text" name="employee_id" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px;" placeholder="Enter your employee ID">
        </div>

        <div style="margin-bottom:20px;">
            <label style="display:block; margin-bottom:5px;">Password</label>
            <input type="password" name="password" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px;">
        </div>

        <button type="submit" style="width:100%; padding:12px; background:#8b0000; color:#fff; border:none; border-radius:6px; font-size:16px; cursor:pointer;">
            Sign In
        </button>
    </form>
</div>

<?= $this->endSection() ?>
