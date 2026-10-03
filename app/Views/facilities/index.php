<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
  $title = $title ?? 'Facilities Administration & General Services';
?>

<?php
  $subtitles = [
    'work-orders' => 'Submit, track, and close facilities work orders by building.',
    'aircon'      => 'Aircon units by building, with maintenance dates and upcoming alerts.',
    'janitorial'  => 'Daily checklists, zone monitoring, and supplies.',
    'buildings'   => 'Monthly building checks and the building map.',
  ];
?>
<div class="page-header">
  <div>
    <h1><?= esc($title) ?></h1>
    <p class="page-subtitle">Facilities Administration &amp; General Services — <?= esc($subtitles[$section] ?? '') ?></p>
  </div>
  <?php if ($section === 'buildings'): ?>
  <button type="button" class="btn-add" onclick="openInspectionModal()">+ Record Inspection</button>
  <?php endif; ?>
  <?php if ($section === 'work-orders'): ?>
  <button type="button" class="btn-add" onclick="openWoForm()">+ New Request</button>
  <?php endif; ?>
  <?php if ($section === 'aircon'): ?>
  <button type="button" class="btn-add" onclick="openAirconForm()">+ Add Aircon Unit</button>
  <?php endif; ?>
  <?php if ($section === 'janitorial'): ?>
  <button type="button" class="btn-add" id="supAddBtn" onclick="openSupplyForm()" style="display:none">+ Add Supply</button>
  <?php endif; ?>
</div>

<div class="stat-cards">
  <?php foreach ($status as $s): ?>
    <?php if (!empty($s['key'])): ?>
      <?php $isOpen = ($active_stat ?? null) === $s['key']; ?>
      <a class="stat-card stat-card-clickable<?= $isOpen ? ' active' : '' ?>" href="<?= $isOpen ? '?' : '?stat=' . esc($s['key']) ?>">
    <?php else: ?>
      <div class="stat-card">
    <?php endif; ?>
      <span class="stat-icon tone-<?= esc($s['tone']) ?>"><i class="bi <?= esc($s['icon']) ?>"></i></span>
      <h3><?= esc($s['label']) ?></h3>
      <div class="value"><?= esc((string) $s['value']) ?></div>
    <?php if (!empty($s['key'])): ?></a><?php else: ?></div><?php endif; ?>
  <?php endforeach; ?>
</div>
<?php if (!empty($stat_rows)): ?>
<div id="statInline" class="guard-card" style="margin-bottom:16px;">
  <div class="gc-title"><i class="bi bi-list-ul"></i> <?= esc($stat_rows['title']) ?></div>
  <div class="table-wrap">
    <table class="sj-table">
      <thead><tr><?php foreach ($stat_rows['columns'] as $col): ?><th><?= esc($col) ?></th><?php endforeach; ?></tr></thead>
      <tbody>
        <?php if (empty($stat_rows['rows'])): ?>
          <tr><td colspan="<?= count($stat_rows['columns']) ?>" class="empty-row">Nothing here right now.</td></tr>
        <?php else: foreach ($stat_rows['rows'] as $row): ?>
          <?php helper('facilities'); ?>
          <tr><?php foreach ($row as $cell): ?><?= fac_cell($cell) ?><?php endforeach; ?></tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<?php if ($section === 'work-orders'): ?>
<div id="fac-a" class="fac-pane">


  <div class="fe-toolbar-row" id="woToolbar">
    <div class="toolbar-search">
      <input type="text" id="woSearch" class="search-box" placeholder="Search request, building, or requester…" oninput="applyWoFilters()">
      <i class="bi bi-search search-icon"></i>
    </div>
    <div class="fe-toolbar-actions">
      <div class="filter-menu-wrapper">
        <button type="button" class="filter-btn" onclick="toggleFacFilter(this)" aria-label="Open filters">
          <i class="bi bi-funnel"></i>
        </button>
        <div class="filter-popup">
          <div class="filter-popup-title">Filter</div>
          <div class="filter-row">
            <label for="woPriorityFilter">Priority</label>
            <select id="woPriorityFilter" onchange="applyWoFilters()">
              <option value="">All priorities</option>
              <option value="Routine">Routine</option>
              <option value="Urgent">Urgent</option>
            </select>
          </div>
          <div class="filter-row">
            <label for="woStatusFilter">Status</label>
            <select id="woStatusFilter" onchange="applyWoFilters()">
              <option value="">All statuses</option>
              <option value="Pending">Pending</option>
              <option value="In Progress">In Progress</option>
            </select>
          </div>
          <div class="filter-row">
            <label for="woSort">Sort by</label>
            <select id="woSort" onchange="sortWoRows()">
              <option value="oldest">Oldest</option>
              <option value="newest">Newest</option>
              <option value="latest">Latest</option>
            </select>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="sub-tabs" id="facWoTabs">
    <button class="sub-tab active" data-wo="pending" onclick="switchWoTab('pending')">Waiting</button>
    <button class="sub-tab" data-wo="history" onclick="switchWoTab('history')">Finished</button>
    <button class="sub-tab" data-wo="buildings" onclick="switchWoTab('buildings')">Buildings</button>
  </div>

  <div class="wo-pane" id="wo-pending">
    <div class="table-wrap">
      <table class="sj-table">
        <thead><tr><th>No.</th><th>Request</th><th>Building</th><th>Priority</th><th>Requested By</th><th>Date</th><th>Status</th><th>Action</th></tr></thead>
        <tbody>
          <?php if (empty($pending)): ?>
            <tr><td colspan="8" class="empty-row">No pending work orders.</td></tr>
          <?php else: foreach ($pending as $w): ?>
            <tr class="wo-row" data-id="<?= (int) $w['id'] ?>" data-created="<?= esc($w['created_at']) ?>" style="cursor:pointer" onclick="showWorkOrder(<?= (int) $w['id'] ?>)">
              <td><strong>WO-<?= str_pad((string) $w['id'], 4, '0', STR_PAD_LEFT) ?></strong></td>
              <td><?= esc($w['title']) ?><?php if (!empty($w['details'])): ?><br><small class="text-muted"><?= esc($w['details']) ?></small><?php endif; ?></td>
              <td><?= esc($w['building']) ?><?= !empty($w['floor']) ? ', ' . esc($w['floor']) : '' ?></td>
              <td><?= $w['priority'] === 'Urgent' ? '<span class="status-badge status-pending">Urgent</span>' : '<span class="status-badge status-available">Routine</span>' ?></td>
              <td><?= esc($w['requested_by']) ?></td>
              <td><?= date('M d, Y', strtotime($w['created_at'])) ?></td>
              <td><span class="tt-badge <?= $w['status'] === 'In Progress' ? 'tt-released' : 'tt-pending' ?>"><?= esc($w['status']) ?></span></td>
              <td onclick="event.stopPropagation()">
                <form method="post" action="<?= base_url('facilities/work-orders/' . $w['id'] . '/status') ?>" style="display:inline;">
                  <?= csrf_field() ?>
                  <?php if ($w['status'] === 'Pending'): ?>
                    <input type="hidden" name="status" value="In Progress">
                    <button type="submit" class="status-badge status-pending fac-action-pill">Start</button>
                  <?php else: ?>
                    <input type="hidden" name="status" value="Completed">
                    <button type="submit" class="status-badge status-completed fac-action-pill">Complete</button>
                  <?php endif; ?>
                </form>
              </td>
            </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="wo-pane" id="wo-history" style="display:none">
    <div class="table-wrap">
      <table class="sj-table">
        <thead><tr><th>No.</th><th>Request</th><th>Building</th><th>Priority</th><th>Requested By</th><th>Submitted</th><th>Completed</th></tr></thead>
        <tbody>
          <?php if (empty($history)): ?>
            <tr><td colspan="7" class="empty-row">No completed work orders yet.</td></tr>
          <?php else: foreach ($history as $w): ?>
            <tr class="wo-row" data-id="<?= (int) $w['id'] ?>" data-created="<?= esc($w['created_at']) ?>" style="cursor:pointer" onclick="showWorkOrder(<?= (int) $w['id'] ?>)">
              <td><strong>WO-<?= str_pad((string) $w['id'], 4, '0', STR_PAD_LEFT) ?></strong></td>
              <td><?= esc($w['title']) ?></td>
              <td><?= esc($w['building']) ?></td>
              <td><?= $w['priority'] === 'Urgent' ? '<span class="status-badge status-pending">Urgent</span>' : '<span class="status-badge status-available">Routine</span>' ?></td>
              <td><?= esc($w['requested_by']) ?></td>
              <td><?= date('M d, Y', strtotime($w['created_at'])) ?></td>
              <td><?= !empty($w['completed_at']) ? date('M d, Y', strtotime($w['completed_at'])) : '—' ?></td>
            </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="wo-pane" id="wo-buildings" style="display:none">
    <div class="table-wrap">
      <table class="sj-table">
        <thead><tr><th>Building</th><th>Open Work Orders</th><th>Last Completed</th></tr></thead>
        <tbody>
          <?php foreach ($building_rows as $row): ?>
            <tr>
              <td><strong><?= esc($row['name']) ?></strong></td>
              <td><?= (int) $row['open'] ?></td>
              <td><?= !empty($row['last_done']) ? date('M d, Y', strtotime($row['last_done'])) : '—' ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php endif; ?>

<?php if ($section === 'aircon'): ?>
<div id="fac-b" class="fac-pane">
  <div class="fe-toolbar-row">
    <div class="toolbar-search">
      <input type="text" id="acSearch" class="search-box" placeholder="Search building, floor, unit, or tech…" oninput="applyAirconFilters()">
      <i class="bi bi-search search-icon"></i>
    </div>
    <div class="fe-toolbar-actions">
    <button type="button" class="fac-alert-icon fac-alert-btn alert-red fac-blink" id="acAlertRed" onclick="toggleAlertList('ac','red')" title="Overdue aircon units" aria-label="Overdue aircon units" style="display:none"><i class="bi bi-exclamation-triangle-fill"></i> <strong id="acAlertRedCount"></strong></button>
      <button type="button" class="fac-alert-icon fac-alert-btn alert-red fac-blink" id="acAlertCircle" onclick="toggleAlertList('ac','circle')" title="Aircon units overdue in 7 days" aria-label="Aircon units expiring soon" style="display:none"><i class="bi bi-exclamation-circle-fill"></i> <strong id="acAlertCircleCount"></strong></button>
    <button type="button" class="filter-btn" id="acMapBtn" onclick="toggleAirconMap()" title="Show map" aria-label="Show map">
      <i class="bi bi-map"></i>
    </button>
    <div class="filter-menu-wrapper">
      <button type="button" class="filter-btn" onclick="toggleFacFilter(this)" aria-label="Open filters">
        <i class="bi bi-funnel"></i>
      </button>
      <div class="filter-popup">
        <div class="filter-popup-title">Filter</div>
        <div class="filter-row">
          <label for="acBuildingFilter">Building</label>
          <select id="acBuildingFilter" onchange="filterAirconBuilding(this.value)">
            <option value="">All buildings</option>
            <?php foreach ($buildings as $b): ?>
              <option value="<?= esc($b) ?>"><?= esc($b) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="filter-row">
          <label for="acStatusFilter">Schedule</label>
          <select id="acStatusFilter" onchange="applyAirconFilters()">
            <option value="">All schedules</option>
            <option value="Overdue">Overdue</option>
            <option value="Due Soon">Overdue in 7 Days</option>
            <option value="Scheduled">Scheduled</option>
            <option value="No Date">No Date</option>
          </select>
        </div>
        <div class="filter-row">
          <label for="acSort">Sort by</label>
          <select id="acSort" onchange="sortAirconRows()">
            <option value="oldest">Oldest</option>
            <option value="newest">Newest</option>
            <option value="latest">Latest (last cleaning)</option>
          </select>
        </div>
      </div>
    </div>
      </div>
  </div>

  <div id="acAlertList" class="guard-card" style="display:none;padding:18px;margin-bottom:16px;"></div>

  <div id="acMapPanel" class="guard-card" style="display:none;padding:18px;margin-bottom:16px;">
    <div class="gc-title"><i class="bi bi-map"></i> Aircon Alerts by Floor</div>
<?php $zoomId = 'acMapSVG'; ?>
    <div class="map-zoom-row">
      <select class="fac-select" onchange="zoomMapTo('<?= $zoomId ?>', this.value)" aria-label="Zoom to a building">
        <option value="">— Select a Building —</option>
        <?php foreach ($buildings as $zb): ?>
          <option value="<?= esc($zb) ?>"><?= esc($zb) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="fac-map-legend">
      <span><i class="map-alert"></i> Blinking = overdue or due within 7 days (floor shown)</span>
      <span><svg width="14" height="14" viewBox="0 0 14 14" aria-hidden="true" style="vertical-align:middle;margin-right:6px;"><line x1="0" y1="7" x2="14" y2="7" stroke="#c62828" stroke-width="2" stroke-dasharray="4 3"/></svg> Emergency flow</span>
      <span><svg width="14" height="14" viewBox="0 0 14 14" aria-hidden="true" style="vertical-align:middle;margin-right:6px;"><circle cx="7" cy="7" r="7" fill="#1c6dd0"/><g stroke="#fff" stroke-width="1.6" stroke-linecap="round"><line x1="7" y1="2.5" x2="7" y2="11.5"/><line x1="3.1" y1="4.7" x2="10.9" y2="9.3"/><line x1="3.1" y1="9.3" x2="10.9" y2="4.7"/></g></svg> Aircon units</span>
    </div>
    <div class="fac-map-wrap">
      <svg id="acMapSVG" xmlns="http://www.w3.org/2000/svg" style="width:100%;height:auto;max-height:640px;background:#ffffff;"></svg>
    </div>
  </div>

  <div id="acDetail" class="guard-card" style="margin-bottom:16px;display:none;"></div>
  <div class="table-wrap">
    <table class="sj-table">
      <thead><tr><th>Building</th><th>Floor</th><th>Unit</th><th>Condition</th><th>Last Cleaning</th><th>Next Schedule</th><th>Schedule Status</th><th>Assigned Tech</th></tr></thead>
      <tbody id="acBody">
        <?php if (empty($aircon)): ?>
          <tr><td colspan="8" class="empty-row">No aircon units registered yet.</td></tr>
        <?php else: foreach ($aircon as $u): ?>
          <tr class="ac-row" data-id="<?= (int) $u['id'] ?>" data-last="<?= esc($u['last_cleaning'] ?? '') ?>" data-building="<?= esc($u['building']) ?>" data-schedule="<?= esc($u['schedule']) ?>" onclick="showAirconDetail(<?= (int) $u['id'] ?>)" style="cursor:pointer;">
            <td><strong><?= esc($u['building']) ?></strong></td>
            <td><?= esc($u['floor']) ?></td>
            <td><?= esc($u['unit']) ?></td>
            <td><?= esc($u['condition']) ?></td>
            <td><?= !empty($u['last_cleaning']) ? date('M d, Y', strtotime($u['last_cleaning'])) : '—' ?></td>
            <td><?= !empty($u['next_schedule']) ? date('M d, Y', strtotime($u['next_schedule'])) : '—' ?></td>
            <td>
              <?php
                $cls = match ($u['schedule']) {
                    'Overdue'  => 'zb-overdue',
                    'Due Soon' => 'zb-overdue',
                    default    => 'tt-completed',
                };
              ?>
              <span class="tt-badge <?= $cls ?><?= in_array($u['schedule'], ['Overdue', 'Due Soon'], true) ? ' badge-blink' : '' ?>"><?= $u['schedule'] === 'Due Soon' ? '<i class="bi bi-exclamation-circle-fill fac-expire-icon"></i>Overdue in 7 Days' : esc($u['schedule']) ?></span>
            </td>
            <td><?= esc($u['tech'] ?? 'Unassigned') ?></td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>

</div>
<?php endif; ?>

<?php if ($section === 'janitorial'): ?>
<div id="fac-d" class="fac-pane">
  <div class="fe-toolbar-row">
    <div class="toolbar-search">
      <input type="text" id="janSearch" class="search-box" placeholder="Search zone, staff, or supply…" oninput="filterCleaning()">
      <i class="bi bi-search search-icon"></i>
    </div>
    <div class="fe-toolbar-actions">
      <button type="button" class="fac-alert-icon fac-alert-btn alert-red fac-blink" id="cleanAlertRed" onclick="toggleAlertList('clean','red')" title="Overdue zones" aria-label="Overdue zones" style="display:none"><i class="bi bi-exclamation-triangle-fill"></i> <strong id="cleanAlertRedCount"></strong></button>
      <button type="button" class="fac-alert-icon fac-alert-btn alert-yellow" id="cleanAlertYellow" onclick="toggleAlertList('clean','yellow')" title="Zones that need cleaning" aria-label="Zones that need cleaning" style="display:none"><i class="bi bi-exclamation-triangle-fill"></i> <strong id="cleanAlertYellowCount"></strong></button>
    <button type="button" class="filter-btn" id="janMapBtn" onclick="toggleCleaningMap()" title="Show map" aria-label="Show map">
      <i class="bi bi-map"></i>
    </button>
    <div class="filter-menu-wrapper">
      <button type="button" class="filter-btn" onclick="toggleFacFilter(this)" aria-label="Open filters">
        <i class="bi bi-funnel"></i>
      </button>
      <div class="filter-popup">
        <div class="filter-popup-title">Filter</div>
        <div id="zoneFilterFields"><div class="filter-row">
          <label for="janStatusFilter">Status</label>
          <select id="janStatusFilter" onchange="filterCleaning()">
            <option value="">All statuses</option>
            <option value="Completed">Completed</option>
            <option value="In Progress">In Progress</option>
            <option value="Needs Cleaning">Needs Cleaning</option>
            <option value="Overdue">Overdue</option>
          </select>
        </div>
        <div class="filter-row">
          <label for="janSort">Sort by</label>
          <select id="janSort" onchange="sortCleaningRows()">
            <option value="oldest">Oldest</option>
            <option value="newest">Newest</option>
            <option value="latest">Latest activity</option>
          </select>
        </div></div>
        <div id="supFilterFields" style="display:none">
          <div class="filter-row">
            <label for="supCatFilter">Category</label>
            <select id="supCatFilter" onchange="applySupplyFilters()">
              <option value="">All categories</option>
              <?php foreach (array_keys($consumable_groups) as $cat): if ($cat === 'Tools') continue; ?>
                <option value="<?= esc($cat) ?>"><?= esc($cat) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="filter-row">
            <label for="supStatusFilter">Stock</label>
            <select id="supStatusFilter" onchange="applySupplyFilters()">
              <option value="">All</option>
              <option value="OK">OK</option>
              <option value="Out of Stock">Out of Stock</option>
            </select>
          </div>
          <div class="filter-row">
            <label for="supSort">Sort by</label>
            <select id="supSort" onchange="sortSupplyRows()">
              <option value="name-asc">Name (A–Z)</option>
              <option value="name-desc">Name (Z–A)</option>
              <option value="stock-asc">Stock (low to high)</option>
              <option value="stock-desc">Stock (high to low)</option>
            </select>
          </div>
        </div>
      </div>
    </div>
    </div>
  </div>

  <div id="cleanAlertList" class="guard-card" style="display:none;padding:18px;margin-bottom:16px;"></div>
  <div id="cleanMapPanel" class="guard-card" style="display:none;padding:18px;margin-bottom:16px;">
    <div class="gc-title"><i class="bi bi-map"></i> Cleaning Map</div>
<?php $zoomId = 'cleanMapSVG'; ?>
    <div class="map-zoom-row">
      <select class="fac-select" onchange="zoomMapTo('<?= $zoomId ?>', this.value)" aria-label="Zoom to a building">
        <option value="">— Select a Building —</option>
        <?php foreach ($buildings as $zb): ?>
          <option value="<?= esc($zb) ?>"><?= esc($zb) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="fac-map-wrap">
      <svg id="cleanMapSVG" xmlns="http://www.w3.org/2000/svg" style="width:100%;height:auto;max-height:640px;background:#ffffff;"></svg>
    </div>
    <div class="fac-map-legend">
      <span><i class="map-pass"></i> Cleaned (steady)</span>
      <span><i style="background:#ffc400"></i> Yellow alert = needs cleaning (floor shown)</span>
      <span><i class="map-alert"></i> Red warning = overdue (shift ended, not finished)</span>
      <span><svg width="14" height="14" viewBox="0 0 14 14" aria-hidden="true" style="vertical-align:middle;margin-right:6px;"><circle cx="7" cy="7" r="7" fill="#2e7d32"/><line x1="4" y1="3" x2="8" y2="8" stroke="#fff" stroke-width="1.6" stroke-linecap="round"/><polygon points="7.5,8 10.5,8 11.5,12 6,12" fill="#fff"/></svg> Cleaning zone</span>
    </div>
  </div>

  <div class="sub-tabs" id="janTabs">
    <button class="sub-tab active" data-jan="daily" onclick="switchJanSub('daily')"><i class="bi bi-sunrise"></i> Daily Checks</button>
    <button class="sub-tab" data-jan="monitor" onclick="switchJanSub('monitor')"><i class="bi bi-table"></i> All Zones</button>
    <button class="sub-tab" data-jan="consumables" onclick="switchJanSub('consumables')"><i class="bi bi-box-seam-fill"></i> Supplies</button>
  </div>

  <div class="jan-pane" id="jan-daily">
    <div class="guard-card" style="padding:18px;margin-bottom:16px;">
      <div class="gc-title"><i class="bi bi-sunrise"></i> Morning Checklist</div>
      <div class="table-wrap">
        <table class="sj-table">
          <thead><tr><th>Zone</th><th>Floor</th><th>Staff</th><th>Shift</th><th>Tasks</th><th>Progress</th></tr></thead>
          <tbody>
            <?php if (empty($morning_zones)): ?>
              <tr><td colspan="6" class="empty-row">No morning shifts assigned.</td></tr>
            <?php else: foreach ($morning_zones as $z): ?>
              <tr data-id="<?= (int) $z['id'] ?>" data-last="<?= esc($z['last'] ?? '') ?>" style="cursor:pointer;" onclick="showZoneDetail(<?= (int) $z['id'] ?>)">
                <td><strong><?= esc($z['zone']) ?></strong></td>
                <td><?= esc($z['floor']) ?></td>
                <td><?= esc($z['staff']) ?></td>
                <td><?= esc($z['shift']) ?></td>
                <td><?= (int) $z['done'] ?> / <?= (int) $z['total'] ?></td>
                <td><span class="tt-badge <?= $z['badge'][1] ?><?= in_array($z['badge'][0], ['Overdue', 'Needs Cleaning'], true) ? ' badge-blink' : '' ?>"><?= esc($z['badge'][0]) ?></span></td>
              </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <div class="guard-card" style="padding:18px;">
      <div class="gc-title"><i class="bi bi-sunset"></i> Afternoon Checklist</div>
      <div class="table-wrap">
        <table class="sj-table">
          <thead><tr><th>Zone</th><th>Floor</th><th>Staff</th><th>Shift</th><th>Tasks</th><th>Progress</th></tr></thead>
          <tbody>
            <?php if (empty($afternoon_zones)): ?>
              <tr><td colspan="6" class="empty-row">No afternoon shifts assigned.</td></tr>
            <?php else: foreach ($afternoon_zones as $z): ?>
              <tr data-id="<?= (int) $z['id'] ?>" data-last="<?= esc($z['last'] ?? '') ?>" style="cursor:pointer;" onclick="showZoneDetail(<?= (int) $z['id'] ?>)">
                <td><strong><?= esc($z['zone']) ?></strong></td>
                <td><?= esc($z['floor']) ?></td>
                <td><?= esc($z['staff']) ?></td>
                <td><?= esc($z['shift']) ?></td>
                <td><?= (int) $z['done'] ?> / <?= (int) $z['total'] ?></td>
                <td><span class="tt-badge <?= $z['badge'][1] ?><?= in_array($z['badge'][0], ['Overdue', 'Needs Cleaning'], true) ? ' badge-blink' : '' ?>"><?= esc($z['badge'][0]) ?></span></td>
              </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>


  <div class="jan-pane" id="jan-monitor" style="display:none">
    <div class="table-wrap">
      <table class="sj-table">
        <thead><tr><th>Zone</th><th>Floor</th><th>Staff</th><th>Shift</th><th>Priority</th><th>Assigned Status</th><th>Tasks</th><th>Progress</th></tr></thead>
        <tbody>
          <?php if (empty($zones)): ?>
            <tr><td colspan="8" class="empty-row">No janitorial assignments yet.</td></tr>
          <?php else: foreach ($zones as $z): ?>
            <tr data-id="<?= (int) $z['id'] ?>" data-last="<?= esc($z['last'] ?? '') ?>" style="cursor:pointer;" onclick="showZoneDetail(<?= (int) $z['id'] ?>)">
              <td><strong><?= esc($z['zone']) ?></strong></td>
              <td><?= esc($z['floor']) ?></td>
              <td><?= esc($z['staff']) ?></td>
              <td><?= esc($z['shift']) ?></td>
              <td><?= $z['priority'] === 'Urgent' ? '<span class="tt-badge tt-pending">Urgent</span>' : esc($z['priority']) ?></td>
              <td><?= esc($z['status']) ?></td>
              <td><?= (int) $z['done'] ?> / <?= (int) $z['total'] ?></td>
                <td><span class="tt-badge <?= $z['badge'][1] ?><?= in_array($z['badge'][0], ['Overdue', 'Needs Cleaning'], true) ? ' badge-blink' : '' ?>"><?= esc($z['badge'][0]) ?></span></td>
            </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="jan-pane" id="jan-consumables" style="display:none">
    <?php if (empty($consumable_groups)): ?>
      <div class="no-data">No consumable items recorded yet.</div>
    <?php else: foreach ($consumable_groups as $category => $items): ?>
      <div class="guard-card" style="padding:18px;margin-bottom:16px;">
        <div class="gc-title"><i class="bi bi-box-seam"></i> <?= esc($category) ?></div>
        <div class="table-wrap">
          <table class="sj-table">
            <thead><tr><th>Item</th><th>Location</th><th>Stock</th><th>Status</th></tr></thead>
            <tbody>
              <?php foreach ($items as $it): ?>
                <tr class="sup-row" data-cat="<?= esc($category) ?>" data-name="<?= esc($it['name']) ?>" data-stock="<?= esc((string) $it['stock']) ?>" data-status="<?= esc($it['status']) ?>">
                  <td><strong><?= esc($it['name']) ?></strong></td>
                  <td><?= esc($it['building'] ?? '—') ?><?= !empty($it['floor']) ? ', ' . esc($it['floor']) : '' ?><?= !empty($it['place']) ? '<br><small class="text-muted">' . esc($it['place']) . '</small>' : '' ?></td>
                  <td><?= esc((string) $it['stock']) ?> <?= esc($it['unit']) ?></td>
                  <td>
                    <?php if ($it['status'] === 'OK'): ?>
                      <span class="inv-badge inv-ok">OK</span>
                    <?php else: ?>
                      <span class="inv-badge inv-out">Out of Stock</span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    <?php endforeach; endif; ?>
  </div>
</div>

<?php endif; ?>

<?php if ($section === 'buildings'): ?>
<div class="bld-page">
  <div class="fe-toolbar-row">
    <div class="toolbar-search">
      <input type="text" id="bldSearch" class="search-box" placeholder="Search building, inspector, or notes…" oninput="applyBuildingFilters()">
      <i class="bi bi-search search-icon"></i>
    </div>
    <div class="fe-toolbar-actions">
      <button type="button" class="fac-alert-icon fac-alert-btn alert-red fac-blink" id="bldAlertRed" onclick="toggleAlertList('bld','red')" title="Buildings that need attention" aria-label="Buildings that need attention" style="display:none"><i class="bi bi-exclamation-triangle-fill"></i> <strong id="bldAlertRedCount"></strong></button>
      <button type="button" class="fac-alert-icon fac-alert-btn alert-yellow" id="bldAlertYellow" onclick="toggleAlertList('bld','yellow')" title="Buildings to be inspected" aria-label="Buildings to be inspected" style="display:none"><i class="bi bi-exclamation-triangle-fill"></i> <strong id="bldAlertYellowCount"></strong></button>
      <button type="button" class="filter-btn" id="bldMapBtn" onclick="toggleBuildingMap()" title="Show map" aria-label="Show map">
        <i class="bi bi-map"></i>
      </button>
      <div class="filter-menu-wrapper">
        <button type="button" class="filter-btn" onclick="toggleFacFilter(this)" aria-label="Open filters">
          <i class="bi bi-funnel"></i>
        </button>
        <div class="filter-popup">
          <div class="filter-popup-title">Filter</div>
          <div class="filter-row">
            <label for="bldResultFilter">Result</label>
            <select id="bldResultFilter" onchange="applyBuildingFilters()">
              <option value="">All results</option>
              <option value="Passed">Passed</option>
              <option value="Needs Attention">Needs Attention</option>
              <option value="Not checked">Not checked</option>
            </select>
          </div>
          <div class="filter-row">
            <label for="bldSort">Sort by</label>
            <select id="bldSort" onchange="sortBuildingRows()">
              <option value="oldest">Oldest</option>
              <option value="newest">Newest</option>
              <option value="latest">Latest check</option>
            </select>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div id="bldAlertList" class="guard-card" style="display:none;padding:18px;margin-bottom:16px;"></div>
  <div id="janMapPanel" class="guard-card" style="display:none;padding:18px;margin-bottom:16px;">
    <div class="gc-title"><i class="bi bi-map"></i> Building Map — <?= esc($inspection_month) ?></div>
<?php $zoomId = 'facMapSVG'; ?>
    <div class="map-zoom-row">
      <select class="fac-select" onchange="zoomMapTo('<?= $zoomId ?>', this.value)" aria-label="Zoom to a building">
        <option value="">— Select a Building —</option>
        <?php foreach ($buildings as $zb): ?>
          <option value="<?= esc($zb) ?>"><?= esc($zb) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="fac-map-wrap">
      <svg id="facMapSVG" viewBox="0 0 950 900" xmlns="http://www.w3.org/2000/svg" style="width:100%;height:auto;max-height:640px;background:#ffffff;"></svg>
    </div>
    <div class="fac-map-legend">
      <span><i class="map-pass"></i> Passed (steady)</span>
      <span><i class="map-alert"></i> Red warning = needs attention</span>
      <span><i style="background:#ffc400"></i> Yellow alert = needs to be inspected</span>
      <span><svg width="14" height="14" viewBox="0 0 14 14" aria-hidden="true" style="vertical-align:middle;margin-right:6px;"><circle cx="7" cy="7" r="7" fill="#800000"/><polyline points="3.5,7 6,9.5 10.5,4.5" fill="none" stroke="#fff" stroke-width="1.6" stroke-linecap="round"/></svg> Building check point</span>
    </div>
  </div>

  <div class="bld-monthly">

    <div class="table-wrap">
      <table class="sj-table">
        <thead><tr><th>Building</th><th>Result (<?= esc($inspection_month) ?>)</th><th>Inspected By</th><th>Date</th><th>Notes</th></tr></thead>
        <tbody id="bldBody">
          <?php foreach ($inspections as $idx => $i): ?>
            <tr class="bld-row" data-idx="<?= (int) $idx ?>" data-name="<?= esc($i['building']) ?>" data-result="<?= esc($i['result'] ?? 'Not checked') ?>" data-last="<?= esc($i['inspected_at'] ?? '') ?>" style="cursor:pointer;" onclick="showBuildingCheckDetail(this.dataset.name)">
              <td><strong><?= esc($i['building']) ?></strong></td>
              <td>
                <?php if ($i['result'] === null): ?>
                  <span class="tt-badge zb-needs badge-blink">Not Inspected</span>
                <?php elseif ($i['result'] === 'Passed'): ?>
                  <span class="tt-badge zb-done">Passed</span>
                <?php else: ?>
                  <span class="tt-badge zb-overdue badge-blink">Needs Attention</span>
                <?php endif; ?>
              </td>
              <td><?= esc($i['inspected_by'] ?? '—') ?></td>
              <td><?= !empty($i['inspected_at']) ? date('M d, Y', strtotime($i['inspected_at'])) : '—' ?></td>
              <td><?= esc($i['notes'] ?? '—') ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php endif; ?>

<?php if ($section === 'buildings'): ?>
<div class="modal" id="inspectionModal">
  <div class="modal-box modal-box-wide">
    <h3>Record Inspection — <?= esc($inspection_month) ?></h3>
    <form method="post" action="<?= base_url('facilities/inspections') ?>">
      <?= csrf_field() ?>
      <div class="form-grid2">
        <div class="fg">
          <label>Building <span class="required-mark">*</span></label>
          <select name="building" id="bldSelect" required>
            <option value="">— Select a Building —</option>
            <?php foreach ($buildings as $b): ?>
              <option value="<?= esc($b) ?>"><?= esc($b) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="fg">
          <label>Result <span class="required-mark">*</span></label>
          <select name="result" required>
            <option value="Passed">Passed</option>
            <option value="Needs Attention">Needs Attention</option>
          </select>
        </div>
        <div class="fg fg-full">
          <label>Notes</label>
          <textarea name="notes" rows="2" placeholder="Observations from this month's inspection"></textarea>
        </div>
      </div>
      <div class="modal-actions">
        <button type="button" onclick="closeInspectionModal()">Cancel</button>
        <button type="submit" class="btn-maroon">Save</button>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>

<?php if ($section === 'aircon'): ?>
<div class="modal" id="airconFormModal">
  <div class="modal-box modal-box-wide">
    <h3>Add Aircon Unit</h3>
    <form method="post" action="<?= base_url('facilities/aircon-units') ?>">
      <?= csrf_field() ?>
      <div class="form-grid2">
        <div class="fg fg-full">
          <label>Unit Name / Model <span class="required-mark">*</span></label>
          <input type="text" name="unit_name" placeholder="e.g. Daikin Split-Type 1.5HP" required>
        </div>
        <div class="fg">
          <label>Building <span class="required-mark">*</span></label>
          <select name="building" required>
            <option value="">— Select a Building —</option>
            <?php foreach ($buildings as $b): ?>
              <option value="<?= esc($b) ?>"><?= esc($b) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="fg">
          <label>Floor</label>
          <select name="floor">
            <option>Ground Floor</option>
            <option>2nd Floor</option>
            <option>3rd Floor</option>
            <option>4th Floor</option>
          </select>
        </div>
        <div class="fg">
          <label>Condition</label>
          <select name="condition">
            <option>Operational</option>
            <option>Needs Cleaning</option>
            <option>Not Working</option>
          </select>
        </div>
        <div class="fg">
          <label>Assigned Tech</label>
          <input type="text" name="assigned_tech" placeholder="e.g. Cardo Garcia">
        </div>
        <div class="fg">
          <label>Last Cleaning</label>
          <input type="date" name="last_cleaning">
        </div>
        <div class="fg">
          <label>Next Schedule</label>
          <input type="date" name="next_schedule">
        </div>
        <div class="fg fg-full">
          <label>Installed By</label>
          <input type="text" name="installed_by" placeholder="e.g. Fernando Reyes">
        </div>
      </div>
      <div class="modal-actions">
        <button type="button" onclick="closeAirconForm()">Cancel</button>
        <button type="submit" class="btn-maroon">Add</button>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>

<?php if ($section === 'janitorial'): ?>
<div class="modal" id="supplyModal">
  <div class="modal-box modal-box-wide">
    <h3>Add Supply — Facilities</h3>
    <form method="post" action="<?= base_url('facilities/supplies') ?>">
      <?= csrf_field() ?>
      <div class="form-grid2">
        <div class="fg fg-full">
          <label>Item Name <span class="required-mark">*</span></label>
          <input type="text" name="item_name" placeholder="e.g. Floor Cleaner" required>
        </div>
        <div class="fg">
          <label>Category</label>
          <select name="category">
            <option>Cleaning Detergent</option>
            <option>Disposable</option>
            <option>Equipment</option>
          </select>
        </div>
        <div class="fg">
          <label>Unit</label>
          <input type="text" name="unit" placeholder="Liters / Pieces / Rolls">
        </div>
        <div class="fg">
          <label>Current Stock</label>
          <input type="number" name="current_stock" min="0" step="0.01" value="0">
        </div>
        <div class="fg">
          <label>Building</label>
          <select name="building">
            <option value="">— Unspecified —</option>
            <?php foreach ($buildings as $b): ?>
              <option value="<?= esc($b) ?>"><?= esc($b) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="fg">
          <label>Floor</label>
          <input type="text" name="floor" placeholder="e.g. 2nd Floor">
        </div>
        <div class="fg">
          <label>Specific Place</label>
          <input type="text" name="location_note" placeholder="e.g. Janitor's closet">
        </div>
      </div>
      <div class="modal-actions">
        <button type="button" onclick="closeSupplyForm()">Cancel</button>
        <button type="submit" class="btn-maroon">Add</button>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>

<?php if ($section === 'work-orders'): ?>
<div class="modal" id="woFormModal">
  <div class="modal-box modal-box-wide">
    <h3>New Repair Request</h3>
    <form method="post" action="<?= base_url('facilities/work-orders') ?>">
      <?= csrf_field() ?>
      <div class="form-grid2">
        <div class="fg fg-full">
          <label>Request <span class="required-mark">*</span></label>
          <input type="text" name="title" placeholder="e.g. Leaking faucet in comfort room" required>
        </div>
        <div class="fg">
          <label>Building <span class="required-mark">*</span></label>
          <select name="building" required>
            <option value="">— Select a Building —</option>
            <?php foreach ($buildings as $b): ?>
              <option value="<?= esc($b) ?>"><?= esc($b) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="fg">
          <label>Floor</label>
          <input type="text" name="floor" placeholder="e.g. 2nd Floor">
        </div>
        <div class="fg">
          <label>Priority</label>
          <select name="priority">
            <option value="Routine">Routine</option>
            <option value="Urgent">Urgent</option>
          </select>
        </div>
        <div class="fg fg-full">
          <label>Details</label>
          <textarea name="details" rows="2" placeholder="What needs to be fixed and where exactly"></textarea>
        </div>
      </div>
      <div class="modal-actions">
        <button type="button" onclick="closeWoForm()">Cancel</button>
        <button type="submit" class="btn-maroon">Submit</button>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>

<div id="woDetailModal" class="sj-modal-overlay" style="display:none">
  <div class="sj-modal">
    <div class="sj-modal-header" style="display:flex;justify-content:space-between;align-items:center;">
      <h3 id="woDetailTitle"></h3>
      <button type="button" class="dp-close" onclick="closeWoDetail()" aria-label="Close"><i class="bi bi-x-lg"></i></button>
    </div>
    <div class="sj-modal-body" id="woDetailBody"></div>
  </div>
</div>

<div id="unitDetailModal" class="sj-modal-overlay" style="display:none">
  <div class="sj-modal">
    <div class="sj-modal-header" style="display:flex;justify-content:space-between;align-items:center;">
      <h3 id="unitDetailTitle"></h3>
      <button type="button" class="dp-close" onclick="closeUnitDetail()" aria-label="Close"><i class="bi bi-x-lg"></i></button>
    </div>
    <div class="sj-modal-body" id="unitDetailBody"></div>
  </div>
</div>

<div id="bldDetailModal" class="sj-modal-overlay" style="display:none">
  <div class="sj-modal">
    <div class="sj-modal-header" style="display:flex;justify-content:space-between;align-items:center;">
      <h3 id="bldDetailTitle"></h3>
      <button type="button" class="dp-close" onclick="closeBldDetail()" aria-label="Close"><i class="bi bi-x-lg"></i></button>
    </div>
    <div class="sj-modal-body" id="bldDetailBody"></div>
  </div>
</div>

<div id="zoneDetailModal" class="sj-modal-overlay" style="display:none">
  <div class="sj-modal">
    <div class="sj-modal-header" style="display:flex;justify-content:space-between;align-items:center;">
      <h3 id="zoneDetailTitle"></h3>
      <button type="button" class="dp-close" onclick="closeZoneDetail()" aria-label="Close"><i class="bi bi-x-lg"></i></button>
    </div>
    <div class="sj-modal-body" id="zoneDetailBody"></div>
  </div>
</div>

<script src="<?= base_url('Assets/js/campus-map.js') ?>?v=<?= @filemtime(FCPATH . 'Assets/js/campus-map.js') ?>"></script>
<script>
if (document.getElementById('cleanMapSVG')) {
  renderMapImage('cleanMapSVG', {
    imageUrl: '<?= base_url('images/MAP.jpg') ?>',
    stateByName: <?= $clean_state_json ?>,
    legend: 'cleaning',
  });
}
if (document.getElementById('facMapSVG')) {
  renderMapImage('facMapSVG', {
    imageUrl: '<?= base_url('images/MAP.jpg') ?>',
    stateByName: <?= json_encode(array_combine(array_column($inspections, 'building'), array_map(fn($i) => ['state' => $i['result'] === 'Passed' ? 'done' : ($i['result'] === null ? 'needs' : 'overdue'), 'floors' => []], $inspections)), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>,
    legend: 'building',
    onSelect: name => showBuildingCheckDetail(name),
  });
}
const buildingInspections = <?= json_encode($inspections, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;

function showBuildingCheckDetail(name) {
  const r = buildingInspections.find(b => b.building === name) || {};
  document.getElementById('bldDetailTitle').textContent = name;
  const fields = [
    ['This month', r.result || 'Not checked'],
    ['Checked by', r.inspected_by || '—'],
    ['Date', r.inspected_at ? r.inspected_at.slice(0, 10) : '—'],
  ];
  document.getElementById('bldDetailBody').innerHTML = `
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:12px 18px;">
      ${fields.map(([k, v]) => `<div><div class="text-muted">${esc(k)}</div><strong>${esc(v)}</strong></div>`).join('')}
    </div>
    <div style="margin-top:14px;"><div class="text-muted">Notes</div>${esc(r.notes || '—')}</div>
    <div style="margin-top:16px;"><button type="button" class="btn-maroon-sm" data-building="${esc(name)}" onclick="pickBuildingForCheck(this.dataset.building)">Record a check for this building</button></div>`;
  document.getElementById('bldDetailModal').style.display = 'flex';
}

function closeBldDetail() {
  document.getElementById('bldDetailModal').style.display = 'none';
}

function applyBuildingFilters() {
  const q = document.getElementById('bldSearch').value.trim().toLowerCase();
  const result = document.getElementById('bldResultFilter').value;
  document.querySelectorAll('#bldBody .bld-row').forEach(r => {
    const okResult = !result || r.dataset.result === result;
    const okSearch = !q || r.textContent.toLowerCase().includes(q);
    r.style.display = okResult && okSearch ? '' : 'none';
  });
}

function sortBuildingRows() {
  const body = document.getElementById('bldBody');
  const mode = document.getElementById('bldSort').value;
  const rows = Array.from(body.querySelectorAll('.bld-row'));
  rows.sort((a, b) => {
    if (mode === 'latest') return (b.dataset.last || '').localeCompare(a.dataset.last || '');
    const diff = Number(a.dataset.idx) - Number(b.dataset.idx);
    return mode === 'newest' ? -diff : diff;
  });
  rows.forEach(r => body.appendChild(r));
}

function openInspectionModal(name) {
  const s = document.getElementById('bldSelect');
  if (!s) return;
  s.value = name || '';
  document.getElementById('inspectionModal').style.display = 'flex';
}

function closeInspectionModal() {
  document.getElementById('inspectionModal').style.display = 'none';
}

function pickBuildingForCheck(name) {
  closeBldDetail();
  openInspectionModal(name);
}

function showAirconBuildingDetail(name) {
  const s = document.getElementById('acBuildingFilter');
  if (s) s.value = name;
  filterAirconBuilding(name);
  document.getElementById('acDetail').scrollIntoView({ behavior: 'smooth', block: 'center' });
}

const workOrdersData = <?= $wo_json ?>;

function showWorkOrder(id) {
  const w = workOrdersData.find(x => x.id === id);
  if (!w) return;
  document.getElementById('woDetailTitle').textContent = `WO-${String(w.id).padStart(4, '0')} — ${w.title}`;
  const fields = [
    ['Building', w.building],
    ['Floor', w.floor || '—'],
    ['Priority', w.priority],
    ['Status', w.status],
    ['Requested By', w.requested_by],
    ['Submitted', w.created_at ? w.created_at.slice(0, 16) : '—'],
    ['Completed', w.completed_at ? w.completed_at.slice(0, 16) : '—'],
  ];
  document.getElementById('woDetailBody').innerHTML = `
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:12px 18px;">
      ${fields.map(([k, v]) => `<div><div class="text-muted">${esc(k)}</div><strong>${esc(v)}</strong></div>`).join('')}
    </div>
    <div style="margin-top:14px;"><div class="text-muted">Details</div>${esc(w.details || '—')}</div>`;
  document.getElementById('woDetailModal').style.display = 'flex';
}

function closeWoDetail() {
  document.getElementById('woDetailModal').style.display = 'none';
}

const zonesData = <?= $zones_json ?>;

function showZoneDetail(id) {
  const z = zonesData.find(x => x.id === id);
  if (!z) return;
  document.getElementById('zoneDetailTitle').textContent = `${z.zone} — ${z.floor}`;
  const fields = [
    ['Staff', z.staff],
    ['Shift', z.shift],
    ['Priority', z.priority],
    ['Assignment Status', z.status],
    ['Progress', z.progress],
    ['Tasks Done', `${z.done} / ${z.total}`],
  ];
  document.getElementById('zoneDetailBody').innerHTML = `
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:12px 18px;">
      ${fields.map(([k, v]) => `<div><div class="text-muted">${esc(k)}</div><strong>${esc(v)}</strong></div>`).join('')}
    </div>
    <div style="margin-top:14px;"><div class="text-muted">Checklist</div>
      <ul style="margin:6px 0 0 18px;">${z.tasks.length ? z.tasks.map(t => `<li>${t.done ? '✔' : '○'} ${esc(t.task)}</li>`).join('') : '<li>No tasks assigned.</li>'}</ul>
    </div>`;
  document.getElementById('zoneDetailModal').style.display = 'flex';
}

function closeZoneDetail() {
  document.getElementById('zoneDetailModal').style.display = 'none';
}



function toggleAirconMap() {
  const panel = document.getElementById('acMapPanel');
  const open = panel.style.display === 'none';
  panel.style.display = open ? '' : 'none';
  document.getElementById('acMapBtn').classList.toggle('active', open);
}

function toggleFacFilter(button) {
  const popup = button.parentElement.querySelector('.filter-popup');
  const open = !popup.classList.contains('visible');
  document.querySelectorAll('.filter-popup.visible').forEach(p => p.classList.remove('visible'));
  popup.classList.toggle('visible', open);
}

document.addEventListener('click', e => {
  if (!e.target.closest('.filter-menu-wrapper')) {
    document.querySelectorAll('.filter-popup.visible').forEach(p => p.classList.remove('visible'));
  }
});


const alertData = {
  clean: { items: <?= $clean_alerts_json ?>, titles: { red: 'Overdue zones', yellow: 'Zones that need cleaning' }, cols: ['Zone', 'Floor', 'Staff', 'Shift', 'Status'] },
  bld:   { items: <?= $bld_alerts_json ?>, titles: { red: 'Buildings that need attention', yellow: 'Buildings to be inspected' }, cols: ['Building', 'Result', 'Checked By'] },
  ac:    { items: <?= $ac_alerts_json ?>, titles: { red: 'Overdue aircon units', circle: 'Aircon units overdue in 7 days' }, cols: ['Building', 'Floor', 'Unit', 'Next Schedule', 'Status'] },
};
const openAlert = {};

const alertBadges = {
  'Overdue': 'zb-overdue badge-blink',
  'Needs Attention': 'zb-overdue badge-blink',
  'Needs Cleaning': 'zb-needs badge-blink',
  'Overdue in 7 Days': 'zb-overdue badge-blink',
  'In Progress': 'zb-needs',
  'Not checked': 'zb-needs badge-blink',
};

function alertCell(c) {
  const cls = alertBadges[c];
  return cls ? `<td><span class="tt-badge ${cls}">${esc(c)}</span></td>` : `<td>${esc(c)}</td>`;
}

function toggleAlertList(key, level) {
  const panel = document.getElementById(key + 'AlertList');
  const same = openAlert[key] === level && panel.style.display !== 'none';
  document.querySelectorAll(`[id^="${key}Alert"][id$="Count"]`).forEach(c => c.parentElement.classList.remove('active'));
  if (same) { panel.style.display = 'none'; openAlert[key] = null; return; }
  openAlert[key] = level;
  const d = alertData[key];
  const items = d.items.filter(i => i.level === level);
  const btn = document.getElementById(key + 'Alert' + level.charAt(0).toUpperCase() + level.slice(1));
  if (btn) btn.classList.add('active');
  panel.style.display = '';
  panel.innerHTML = `
    <div class="gc-title"><i class="bi bi-exclamation-triangle-fill"></i> ${esc(d.titles[level])} <span class="text-muted">(${items.length})</span></div>
    <div class="table-wrap"><table class="sj-table"><thead><tr>${d.cols.map(c => `<th>${esc(c)}</th>`).join('')}</tr></thead>
    <tbody>${items.length ? items.map(i => `<tr>${i.cols.map(alertCell).join('')}</tr>`).join('') : `<tr><td colspan="${d.cols.length}" class="empty-row">Nothing here.</td></tr>`}</tbody></table></div>`;
}

Object.keys(alertData).forEach(key => {
  const d = alertData[key];
  ['red', 'yellow', 'circle'].forEach(level => {
    const n = d.items.filter(i => i.level === level).length;
    const btn = document.getElementById(key + 'Alert' + level.charAt(0).toUpperCase() + level.slice(1));
    if (!btn || !n) return;
    document.getElementById(key + 'Alert' + level.charAt(0).toUpperCase() + level.slice(1) + 'Count').textContent = n;
    btn.style.display = '';
  });
});

const airconUnits = <?= $aircon_json ?>;

function airconAlertFloors() {
  const byBuilding = {};
  airconUnits
    .filter(u => u.schedule === 'Overdue' || u.schedule === 'Due Soon')
    .forEach(u => {
      byBuilding[u.building] ??= new Set();
      byBuilding[u.building].add(u.floor);
    });
  const result = {};
  Object.keys(byBuilding).forEach(b => result[b] = [...byBuilding[b]]);
  return result;
}

if (document.getElementById('acMapSVG')) {
  renderMapImage('acMapSVG', {
    imageUrl: '<?= base_url('images/MAP.jpg') ?>',
    alertFloorsByName: airconAlertFloors(),
    legend: 'aircon',
    onSelect: name => showAirconBuildingDetail(name),
  });
}

function sortAirconRows() {
  const body = document.getElementById('acBody');
  const mode = document.getElementById('acSort').value;
  const rows = Array.from(body.querySelectorAll('.ac-row'));
  rows.sort((a, b) => {
    if (mode === 'latest') return (b.dataset.last || '').localeCompare(a.dataset.last || '');
    const diff = Number(a.dataset.id) - Number(b.dataset.id);
    return mode === 'newest' ? -diff : diff;
  });
  rows.forEach(r => body.appendChild(r));
}

function applyAirconFilters() {
  const building = document.getElementById('acBuildingFilter').value;
  const status = document.getElementById('acStatusFilter').value;
  const q = document.getElementById('acSearch').value.trim().toLowerCase();
  document.querySelectorAll('#acBody .ac-row').forEach(r => {
    const matchesBuilding = !building || r.dataset.building === building;
    const matchesStatus = !status || r.dataset.schedule === status;
    const matchesSearch = !q || r.textContent.toLowerCase().includes(q);
    r.style.display = matchesBuilding && matchesStatus && matchesSearch ? '' : 'none';
  });
}

function filterAirconBuilding(building) {
  document.getElementById('acBuildingFilter').value = building || '';
  applyAirconFilters();
  const detail = document.getElementById('acDetail');
  if (!building) { detail.style.display = 'none'; return; }
  showBuildingDetail(building);
}

function closeAirconDetail() {
  document.getElementById('acDetail').style.display = 'none';
}

function showBuildingDetail(building) {
  const units = airconUnits.filter(u => u.building === building);
  const detail = document.getElementById('acDetail');
  detail.style.display = '';
  detail.innerHTML = `
    <div style="display:flex;justify-content:space-between;align-items:center;"><div class="gc-title"><i class="bi bi-building"></i> ${esc(building)} — Maintenance Details</div><button type="button" class="dp-close" onclick="closeAirconDetail()" aria-label="Close"><i class="bi bi-x-lg"></i></button></div>
    ${units.length ? units.map(u => `
      <div style="margin:12px 0;">
        <strong>${esc(u.unit)}</strong> <span class="text-muted">(${esc(u.floor)})</span>
        — checklist ${u.tasks_done}/${u.tasks_total}
        <ul style="margin:6px 0 0 18px;">
          ${u.tasks.map(t => `<li>${t.done ? '✔' : '○'} ${esc(t.task)}</li>`).join('')}
        </ul>
      </div>`).join('') : '<div class="no-data">No aircon units in this building.</div>'}`;
}

function showAirconDetail(id) {
  const u = airconUnits.find(x => x.id === id);
  if (!u) return;
  document.getElementById('unitDetailTitle').textContent = `${u.unit} — ${u.building}`;
  const fields = [
    ['Building', u.building],
    ['Floor', u.floor],
    ['Condition', u.condition],
    ['Schedule Status', u.schedule],
    ['Last Cleaning', u.last_cleaning || '—'],
    ['Next Schedule', u.next_schedule || '—'],
    ['Assigned Tech', u.tech || 'Unassigned'],
    ['Installed By', u.installed_by || '—'],
  ];
  document.getElementById('unitDetailBody').innerHTML = `
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:12px 18px;">
      ${fields.map(([k, v]) => `<div><div class="text-muted">${esc(k)}</div><strong>${esc(v)}</strong></div>`).join('')}
    </div>
    <div style="margin-top:14px;"><div class="text-muted">Checklist ${u.tasks_done} / ${u.tasks_total}</div>
      <ul style="margin:6px 0 0 18px;">${u.tasks.map(t => `<li>${t.done ? '✔' : '○'} ${esc(t.task)}</li>`).join('')}</ul>
    </div>`;
  document.getElementById('unitDetailModal').style.display = 'flex';
}

function closeUnitDetail() {
  document.getElementById('unitDetailModal').style.display = 'none';
}

function esc(s) {
  return String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
}

function toggleCleaningMap() {
  const panel = document.getElementById('cleanMapPanel');
  const open = panel.style.display === 'none';
  panel.style.display = open ? '' : 'none';
  document.getElementById('janMapBtn').classList.toggle('active', open);
}

function sortCleaningRows() {
  const mode = document.getElementById('janSort').value;
  document.querySelectorAll('#jan-daily tbody, #jan-monitor tbody').forEach(body => {
    const rows = Array.from(body.querySelectorAll('tr[data-id]'));
    rows.sort((a, b) => {
      if (mode === 'latest') return (b.dataset.last || '').localeCompare(a.dataset.last || '');
      const diff = Number(a.dataset.id) - Number(b.dataset.id);
      return mode === 'newest' ? -diff : diff;
    });
    rows.forEach(r => body.appendChild(r));
  });
}

function filterCleaning() {
  if (document.getElementById('jan-consumables').style.display !== 'none') { applySupplyFilters(); return; }
  const q = document.getElementById('janSearch').value.trim().toLowerCase();
  const status = document.getElementById('janStatusFilter').value;

  document.querySelectorAll('#fac-d tbody tr').forEach(row => {
    if (row.querySelector('.empty-row')) return;
    const text = row.textContent.toLowerCase();
    const inZoneTable = row.closest('#jan-daily, #jan-monitor');
    const matchesSearch = !q || text.includes(q);
    const matchesStatus = !status || !inZoneTable || text.includes(status.toLowerCase());
    row.style.display = matchesSearch && matchesStatus ? '' : 'none';
  });
}

function toggleBuildingMap() {
  const panel = document.getElementById('janMapPanel');
  const open = panel.style.display === 'none';
  panel.style.display = open ? '' : 'none';
  document.getElementById('bldMapBtn').classList.toggle('active', open);
}

function switchJanSub(key) {
  document.querySelectorAll('#janTabs .sub-tab').forEach(b => b.classList.toggle('active', b.dataset.jan === key));
  document.querySelectorAll('.jan-pane').forEach(p => p.style.display = p.id === 'jan-' + key ? '' : 'none');
  const onSupplies = key === 'consumables';
  document.getElementById('zoneFilterFields').style.display = onSupplies ? 'none' : '';
  document.getElementById('supFilterFields').style.display = onSupplies ? '' : 'none';
  const add = document.getElementById('supAddBtn');
  if (add) add.style.display = onSupplies ? '' : 'none';
}

function openAirconForm() {
  document.getElementById('airconFormModal').style.display = 'flex';
}

function closeAirconForm() {
  document.getElementById('airconFormModal').style.display = 'none';
}

function openSupplyForm() {
  document.getElementById('supplyModal').style.display = 'flex';
}

function closeSupplyForm() {
  document.getElementById('supplyModal').style.display = 'none';
}

(function () {
  if (new URLSearchParams(location.search).get('tab') === 'supplies' && document.getElementById('jan-consumables')) switchJanSub('consumables');
})();

function applySupplyFilters() {
  const cat = document.getElementById('supCatFilter').value;
  const st = document.getElementById('supStatusFilter').value;
  const q = document.getElementById('janSearch').value.trim().toLowerCase();
  document.querySelectorAll('#jan-consumables .sup-row').forEach(r => {
    const ok = (!cat || r.dataset.cat === cat) && (!st || r.dataset.status === st) && (!q || r.textContent.toLowerCase().includes(q));
    r.style.display = ok ? '' : 'none';
  });
  document.querySelectorAll('#jan-consumables .guard-card').forEach(card => {
    const any = [...card.querySelectorAll('.sup-row')].some(r => r.style.display !== 'none');
    card.style.display = any ? '' : 'none';
  });
}

function sortSupplyRows() {
  const mode = document.getElementById('supSort').value;
  document.querySelectorAll('#jan-consumables tbody').forEach(body => {
    const rows = Array.from(body.querySelectorAll('.sup-row'));
    rows.sort((a, b) => {
      if (mode.startsWith('name')) {
        const d = a.dataset.name.localeCompare(b.dataset.name);
        return mode === 'name-desc' ? -d : d;
      }
      const d = Number(a.dataset.stock) - Number(b.dataset.stock);
      return mode === 'stock-desc' ? -d : d;
    });
    rows.forEach(r => body.appendChild(r));
  });
}

function applyWoFilters() {
  const q = document.getElementById('woSearch').value.trim().toLowerCase();
  const pr = document.getElementById('woPriorityFilter').value;
  const st = document.getElementById('woStatusFilter').value;
  document.querySelectorAll('.wo-row').forEach(r => {
    const t = r.textContent;
    const ok = (!q || t.toLowerCase().includes(q)) && (!pr || t.includes(pr)) && (!st || t.includes(st));
    r.style.display = ok ? '' : 'none';
  });
}

function sortWoRows() {
  const mode = document.getElementById('woSort').value;
  document.querySelectorAll('#wo-pending tbody, #wo-history tbody').forEach(body => {
    const rows = Array.from(body.querySelectorAll('.wo-row'));
    rows.sort((a, b) => {
      if (mode === 'latest') return (b.dataset.created || '').localeCompare(a.dataset.created || '');
      const diff = Number(a.dataset.id) - Number(b.dataset.id);
      return mode === 'newest' ? -diff : diff;
    });
    rows.forEach(r => body.appendChild(r));
  });
}

function openWoForm() {
  document.getElementById('woFormModal').style.display = 'flex';
}

function closeWoForm() {
  document.getElementById('woFormModal').style.display = 'none';
}

function switchWoTab(key) {
  const tb = document.getElementById('woToolbar');
  if (tb) tb.style.display = key === 'buildings' ? 'none' : '';
  document.querySelectorAll('#facWoTabs .sub-tab').forEach(b => b.classList.toggle('active', b.dataset.wo === key));
  document.querySelectorAll('.wo-pane').forEach(p => p.style.display = p.id === 'wo-' + key ? '' : 'none');
}
(function () {
  const t = new URLSearchParams(location.search).get('tab');
  if (t && document.getElementById('wo-' + t)) switchWoTab(t);
})();

</script>
<script src="<?= base_url('Assets/js/table-tools.js') ?>?v=<?= @filemtime(FCPATH . 'Assets/js/table-tools.js') ?>"></script>
<script>const _si = document.getElementById('statInline'); if (_si) attachTableTools(_si);</script>
<?= $this->endSection() ?>
