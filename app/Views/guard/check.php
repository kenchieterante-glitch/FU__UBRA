
<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<link rel="stylesheet" href="<?= base_url('Assets/css/base.css') ?>">

<div class="guard-wrapper">
    <div class="page-header compact-header">
        <div>
            <h1 class="page-title">Verify Trip Ticket</h1>
            <p class="page-subtitle">Trip ID: <?= esc($trip['trip_id']) ?></p>
        </div>
        <div>
            <a href="<?= base_url('/guard') ?>" class="btn-cancel">Back to Dashboard</a>
        </div>
    </div>

    <div class="table-panel">
        <div class="ticket-details">
            <h3 class="panel-title">Trip Information</h3>
            <div class="detail-grid">
                <div class="detail-item"><span class="label">Requester:</span> <span class="value"><?= esc($trip['requester_name']) ?></span></div>
                <div class="detail-item"><span class="label">Destination:</span> <span class="value"><?= esc($trip['destination']) ?></span></div>
                <div class="detail-item"><span class="label">Purpose:</span> <span class="value"><?= esc($trip['purpose']) ?></span></div>
                <div class="detail-item"><span class="label">Travel Date:</span> <span class="value"><?= date('M d, Y', strtotime($trip['travel_date'])) ?></span></div>
                <div class="detail-item"><span class="label">Driver:</span> <span class="value"><?= esc($trip['driver_name']) ?></span></div>
                <div class="detail-item"><span class="label">Vehicle:</span> <span class="value"><?= esc($trip['plate_no'] ?? '') ?></span></div>
                <div class="detail-item"><span class="label">Status:</span> <span class="status-badge badge-approved"><?= esc($trip['status']) ?></span></div>
            </div>

            <div class="actions">
                <?php if (empty($trip['check_in_time'])): ?>
                    <form method="post" action="<?= base_url('travel/checkIn/' . $trip['id']) ?>">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn-primary">Check In</button>
                    </form>
                <?php elseif (empty($trip['check_out_time'])): ?>
                    <div class="check-in-info"><i class="bi bi-check-circle-fill"></i> Checked In: <?= date('M d, h:i A', strtotime($trip['check_in_time'])) ?></div>
                    <form method="post" action="<?= base_url('travel/checkOut/' . $trip['id']) ?>">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn-submit">Check Out</button>
                    </form>
                <?php else: ?>
                    <div class="check-in-info"><i class="bi bi-check-circle-fill"></i> Checked In: <?= date('M d, h:i A', strtotime($trip['check_in_time'])) ?></div>
                    <div class="check-in-info"><i class="bi bi-check-circle-fill"></i> Checked Out: <?= date('M d, h:i A', strtotime($trip['check_out_time'])) ?></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
    .detail-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px,1fr));
        gap: 15px;
        margin: 20px 0;
    }
    .detail-item {
        padding:10px;
        border:1px solid var(--border-color);
        border-radius:8px;
    }
    .detail-item .label {
        font-weight: bold;
        display:block;
    }
    .actions {
        display:flex;
        gap:15px;
        margin-top:20px;
        align-items:center;
    }
    .check-in-info {
        padding:8px 15px;
        background-color: var(--success-bg);
        color:var(--success-text);
        border-radius:8px;
    }
</style>

<?= $this->endSection() ?>
