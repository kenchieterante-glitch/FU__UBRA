<?php
// Shared row template — included by vehicles/index.php on first load AND
// rendered standalone (as a plain HTML string, not a full page) by
// VehicleController::refreshRows() for the page's polling script. Keeping
// this in one file means the badge/prediction logic only ever needs to be
// right in one place, instead of a PHP version and a hand-kept-in-sync JS
// version drifting apart over time.
$normalize_status = fn($value) => strtolower(preg_replace('/[^a-z0-9]+/', '-', trim((string) $value)));
?>
<?php if (!empty($vehicles)): ?>
  <?php foreach ($vehicles as $v): ?>
    <tr class="vehicle-row" onclick="openVehicleDetail(<?= (int) $v['id'] ?>)">
      <td><?= esc($v['vehicle_name']) ?><br><small></small></td>
      <td><?= esc($v['plate_no']) ?></td>
      <td><?= esc($v['type']) ?></td>
      <td><?= esc($v['driver_name'] ?? 'Unassigned') ?></td>
      <td><?= esc($v['department_name'] ?? 'Unassigned') ?></td>
      <?php $inspectionClass = $normalize_status($v['inspection_status'] ?? 'unknown'); ?>
      <?php $gpsOnline = ($v['gps_status'] ?? '') === 'Online'; ?>
      <?php $availClass = match ($v['availability'] ?? 'Available') {
          'In Use'      => 'avail-inuse',
          'Reserved'    => 'avail-reserved',
          'Maintenance' => 'avail-maint',
          'Inactive'    => 'avail-inactive',
          default       => 'avail-available',
      }; ?>
      <td>
        <span class="gps-badge <?= $gpsOnline ? 'gps-online' : 'gps-offline' ?>">
          <span class="<?= $gpsOnline ? 'pulse-dot' : 'dead-dot' ?>"></span>
          <?= esc($v['gps_status']) ?>
        </span>
      </td>
      <td><span class="status-badge status-<?= esc($inspectionClass) ?>"><?= esc($v['inspection_status']) ?></span></td>
      <td><span class="avail-badge <?= esc($availClass) ?>"><?= esc($v['availability']) ?></span></td>
      <?php $prediction = $fuel_predictions[$v['id']] ?? ['hasData' => false]; ?>
      <td>
        <?php if (!empty($prediction['hasData'])): ?>
          <strong><?= esc((string) $prediction['predictedLiters30d']) ?> L</strong> / 30 days
          <br><small class="page-subtitle" style="margin:0;"><?= esc((string) $prediction['avgLPer100km']) ?> L per 100km avg</small>
        <?php elseif (($prediction['logsCount'] ?? 0) >= 1): ?>
          <small class="page-subtitle" style="margin:0;">Log 1 more fill-up to enable predictions</small>
        <?php else: ?>
          <small class="page-subtitle" style="margin:0;">No fuel logs yet</small>
        <?php endif; ?>
      </td>
      <td class="action-cell">
        <div class="action-buttons" onclick="event.stopPropagation()">
          <button type="button" class="icon-btn" onclick="openFuelLogModal(<?= (int) $v['id'] ?>, '<?= esc($v['vehicle_name'], 'js') ?>')" title="Log Fuel" aria-label="Log fuel for <?= esc($v['vehicle_name']) ?>"><i class="bi bi-fuel-pump-fill"></i></button>
          <form method="post" action="<?= base_url('vehicles/delete/'.$v['id']) ?>" onsubmit="return confirm('Archive this vehicle?')" style="display:contents;">
            <?= csrf_field() ?>
            <button type="submit" class="icon-btn delete" title="Archive" aria-label="Archive <?= esc($v['vehicle_name']) ?>"><i class="bi bi-archive-fill"></i></button>
          </form>
        </div>
      </td>
    </tr>
  <?php endforeach; ?>
<?php else: ?>
  <tr><td colspan="10">No vehicles recorded yet.</td></tr>
<?php endif; ?>
