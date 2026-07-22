
<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<link rel="stylesheet" href="<?= base_url('Assets/css/base.css') ?>">

<div class="guard-wrapper">
    <div class="page-header compact-header">
        <div>
            <h1 class="page-title">Guard Dashboard</h1>
            <p class="page-subtitle">Scan and verify digital trip tickets</p>
        </div>
    </div>

    <!-- Flash messages -->
    <?php if (session()->getFlashdata('success')): ?>
        <div class="flash flash-success"><i class="bi bi-check-circle-fill"></i> <?= session()->getFlashdata('success') ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="flash flash-error"><i class="bi bi-exclamation-triangle-fill"></i> <?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

    <!-- Today's Trips Table -->
    <div class="table-panel">
        <div class="table-toolbar">
            <h2 class="panel-title">Today's Approved Trips</h2>
        </div>
        <div class="table-scroll">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Trip ID</th>
                        <th>Requester</th>
                        <th>Destination</th>
                        <th>Driver</th>
                        <th>Vehicle</th>
                        <th>Departure Time</th>
                        <th>Status</th>
                        <th>Check In</th>
                        <th>Check Out</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($trips)): ?>
                        <tr><td colspan="9" class="empty-row">No trips found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($trips as $trip): ?>
                            <?php if ($trip['status'] === 'Approved' || $trip['status'] === 'Completed'): ?>
                                <tr>
                                    <td class="trip-id"><?= esc($trip['trip_id']) ?></td>
                                    <td><?= esc($trip['requester_name']) ?></td>
                                    <td><?= esc($trip['destination']) ?></td>
                                    <td><?= esc($trip['driver_name']) ?></td>
                                    <td><?= esc($trip['vehicle_plate']) ?></td>
                                    <td><?= esc($trip['departure_time']) ?></td>
                                    <td>
                                        <span class="status-badge 
                                            <?= $trip['status'] === 'Approved' ? 'badge-approved' : 'badge-completed' ?>">
                                            <?= esc($trip['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if (empty($trip['check_in_time'])): ?>
                                            <form method="post" action="<?= base_url('travel/checkIn/' . $trip['id']) ?>">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="btn-primary btn-sm">Check In</button>
                                            </form>
                                        <?php else: ?>
                                            <span class="text-success"><?= date('M d, h:i A', strtotime($trip['check_in_time'])) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($trip['check_in_time']) && empty($trip['check_out_time'])): ?>
                                            <form method="post" action="<?= base_url('travel/checkOut/' . $trip['id']) ?>">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="btn-submit btn-sm">Check Out</button>
                                            </form>
                                        <?php elseif (!empty($trip['check_out_time'])): ?>
                                            <span class="text-success"><?= date('M d, h:i A', strtotime($trip['check_out_time'])) ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
