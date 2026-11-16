<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<link rel="stylesheet" href="<?= base_url('Assets/css/notifications.css') . '?v=' . @filemtime(FCPATH.'Assets/css/notifications.css') ?>">

<div class="notif-wrapper">
    <div class="page-header">
        <div>
            <h1 class="page-title"><i class="bi bi-chat-text-fill"></i> SMS Outbox</h1>
            <p class="page-subtitle">
                Every text the system makes for staff, such as trip assignments and tests.
                <?php if ($live): ?>
                    <strong>Live mode:</strong> a gateway is connected, so texts are really sent.
                <?php else: ?>
                    <strong>Simulated mode:</strong> no SMS gateway is connected, so nothing is sent to a phone. This list shows exactly what would be sent.
                <?php endif; ?>
            </p>
        </div>
        <div class="header-actions">
            <a href="<?= base_url('notifications') ?>" class="btn-outline"><i class="bi bi-arrow-left"></i> Back to Notifications</a>
        </div>
    </div>

    <div class="table-card">
        <table class="notif-table" id="smsTable">
            <thead>
                <tr>
                    <th>Date &amp; Time</th>
                    <th>To</th>
                    <th>Number</th>
                    <th>Reason</th>
                    <th>Message</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!$rows): ?>
                <tr><td colspan="6" style="text-align:center;padding:2rem;">No texts yet. Approve a trip ticket with a driver, or use "Send test SMS" on a Personnel record.</td></tr>
            <?php endif; ?>
            <?php foreach ($rows as $r):
                $cls = ['Sent' => 'inv-ok', 'Simulated' => 'inv-low', 'Failed' => 'inv-out', 'No number' => 'inv-out'][$r['mode']] ?? 'inv-low'; ?>
                <tr>
                    <td><?= esc(date('M j, Y g:i A', strtotime($r['created_at']))) ?></td>
                    <td><?= esc($r['recipient_name']) ?></td>
                    <td><?= esc($r['phone'] ?: '—') ?></td>
                    <td><?= esc($r['trigger_label']) ?></td>
                    <td style="max-width:420px;"><?= esc($r['message']) ?></td>
                    <td><span class="inv-badge <?= $cls ?>" title="<?= esc((string) $r['detail']) ?>"><?= esc($r['mode']) ?></span></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?= $this->endSection() ?>
