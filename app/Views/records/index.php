<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php
$stats = $stats ?? ['total_records' => 0, 'archived_records' => 0, 'reports_generated' => 0, 'reports_pending' => 0, 'reports_completed' => 0, 'today_activities' => 0];
$activities = $activities ?? [];
$scopedToJanitorial = $scopedToJanitorial ?? false;
$scopeModules = $scopeModules ?? null;
$modButtons = ['Tools' => ['tools', 'bi-wrench-adjustable'], 'Vehicle' => ['vehicle', 'bi-truck'], 'Motor Pool' => ['motor-pool', 'bi-gear-wide-connected'], 'Sports' => ['sports', 'bi-trophy-fill'], 'Security' => ['security', 'bi-shield-fill-check'], 'Facilities' => ['facilities', 'bi-building-gear'], 'Safety' => ['safety', 'bi-shield-check'], 'Janitorial' => ['janitorial', 'bi-brush'], 'Waste Watch' => ['waste-watch', 'bi-recycle'], 'Personnel' => ['personnel', 'bi-people-fill']];
$slug = fn($s) => strtolower(str_replace(' ', '-', trim((string) $s)));
?>

<div class="rec-wrapper">

    <!-- ── PAGE HEADER ──────────────────────────────────────────── -->
    <div class="page-header">
        <div>
            <h1>Reports</h1>
            <p class="page-subtitle"><?= $scopedToJanitorial ? 'Janitorial records and activity only.' : ($scopeModules ? implode(' and ', $scopeModules) . ' records and activity only.' : 'View, manage and generate reports for all categories in one place.') ?></p>
        </div>
    </div>

    <!-- ── STATS CARDS ──────────────────────────────────────────── -->
    <div class="stat-cards">
        <div class="stat-card stat-card-clickable" onclick="filterRecordsByStat('total')" role="button" tabindex="0">
            <span class="stat-icon tone-maroon"><i class="bi bi-folder2-open"></i></span>
            <h3>Total Records</h3>
            <div class="value"><?= (int) $stats['total_records'] ?></div>
        </div>
        <div class="stat-card stat-card-clickable" onclick="<?= !empty($canArchive) ? "switchRecView('archived')" : "filterRecordsByStat('archived')" ?>" role="button" tabindex="0">
            <span class="stat-icon tone-neutral"><i class="bi bi-archive-fill"></i></span>
            <h3>Archived Records</h3>
            <div class="value"><?= (int) $stats['archived_records'] ?></div>
        </div>
        <div class="stat-card stat-card-clickable" onclick="filterRecordsByStat('reports')" role="button" tabindex="0">
            <span class="stat-icon tone-gold"><i class="bi bi-file-earmark-text"></i></span>
            <h3>Reports Generated</h3>
            <div class="value"><?= (int) $stats['reports_generated'] ?></div>
        </div>
        <div class="stat-card stat-card-clickable" onclick="filterRecordsByStat('pending')" role="button" tabindex="0" title="Reports waiting to be completed">
            <span class="stat-icon tone-gold"><i class="bi bi-hourglass-split"></i></span>
            <h3>Pending Reports</h3>
            <div class="value"><?= (int) ($stats['reports_pending'] ?? 0) ?></div>
        </div>
        <div class="stat-card stat-card-clickable" onclick="filterRecordsByStat('completed')" role="button" tabindex="0" title="Reports that are done">
            <span class="stat-icon tone-green"><i class="bi bi-check2-circle"></i></span>
            <h3>Completed Reports</h3>
            <div class="value"><?= (int) ($stats['reports_completed'] ?? 0) ?></div>
        </div>
        <div class="stat-card stat-card-clickable" onclick="filterRecordsByStat('today')" role="button" tabindex="0">
            <span class="stat-icon tone-green"><i class="bi bi-calendar-day"></i></span>
            <h3>Today's Activities</h3>
            <div class="value"><?= (int) $stats['today_activities'] ?></div>
        </div>
    </div>

    <?php $startView = $startView ?? 'records'; $disposalRows = $disposalRows ?? []; $archivedRows = $archivedRows ?? []; $canArchive = $canArchive ?? false; ?>
    <style>
      .rec-tabs{display:inline-flex;gap:4px;padding:4px;border-radius:12px;background:rgba(128,0,0,.07);margin-bottom:16px}
      .rec-tab{border:0;background:transparent;padding:8px 18px;border-radius:9px;font-weight:600;font-size:14px;color:#555;cursor:pointer;display:inline-flex;align-items:center;gap:8px}
      .rec-tab.active{background:#800000;color:#fff;box-shadow:0 2px 6px rgba(128,0,0,.25)}
      .rec-tab .tab-count{font-size:11px;padding:1px 8px;border-radius:999px;background:rgba(0,0,0,.12)}
      .rec-tab.active .tab-count{background:rgba(255,255,255,.25)}
      .dsp-empty{padding:34px 12px;text-align:center;color:#888}
      .dsp-empty i{font-size:30px;display:block;margin-bottom:8px;color:#c9a9a9}
    </style>
    <?php if (!empty($canArchive)): ?>
    <div class="rec-tabs" role="tablist">
        <button type="button" class="rec-tab<?= $startView === 'records' ? ' active' : '' ?>" id="tabRecords" role="tab" onclick="switchRecView('records')"><i class="bi bi-list-task"></i> Records</button>
        <button type="button" class="rec-tab<?= $startView === 'archived' ? ' active' : '' ?>" id="tabArchived" role="tab" onclick="switchRecView('archived')"><i class="bi bi-archive"></i> Archived <span class="tab-count"><?= count($archivedRows) ?></span></button>
        <?php if (!empty($canManageArchive)): ?>
        <button type="button" class="rec-tab<?= $startView === 'disposal' ? ' active' : '' ?>" id="tabDisposal" role="tab" onclick="switchRecView('disposal')"><i class="bi bi-trash3"></i> Disposal <span class="tab-count"><?= count($disposalRows) ?></span></button>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <div id="viewRecords"<?= $startView !== 'records' ? ' style="display:none"' : '' ?>>
    <!-- ── FILTER BAR ───────────────────────────────────────────── -->
    <div class="rec-filter-bar">
        <div class="rec-filter-fields">
            <?php
            $shownCats = $scopeModules
                ? array_values(array_unique(array_map(fn($m) => \App\Libraries\DepartmentScope::categoryFor($m), $scopeModules)))
                : array_keys(\App\Libraries\DepartmentScope::CATEGORIES);
            if (count($shownCats) > 1): ?>
            <div class="rec-icon-filter" id="moduleFilterGroup" data-target="module">
                <button type="button" class="rec-icon-btn active" data-value="" title="All Categories"><i class="bi bi-grid-fill"></i></button>
                <?php foreach ($shownCats as $cn): $cc = \App\Libraries\DepartmentScope::CATEGORIES[$cn]; ?>
                <button type="button" class="rec-icon-btn" data-value="<?= esc($cc['slug']) ?>" title="<?= esc($cn) ?>"><i class="bi <?= esc($cc['icon']) ?>"></i></button>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="rec-icon-filter" id="moduleFilterGroup" data-target="module" style="display:none"><button type="button" class="rec-icon-btn active" data-value=""></button></div>
            <?php endif; ?>
            <div class="rec-icon-filter" id="kindFilterGroup" data-target="kind">
                <button type="button" class="rec-icon-btn active" data-value="" title="All Types"><i class="bi bi-collection"></i></button>
                <button type="button" class="rec-icon-btn" data-value="record" title="Records"><i class="bi bi-file-earmark-text"></i></button>
                <button type="button" class="rec-icon-btn" data-value="archive" title="Archived"><i class="bi bi-archive-fill"></i></button>
                <button type="button" class="rec-icon-btn" data-value="report" title="Reports"><i class="bi bi-file-earmark-bar-graph"></i></button>
            </div>
            <select id="statusFilter" onchange="filterTable()">
                <option value="">All Status</option>
                <option value="borrowed">Borrowed</option>
                <option value="returned">Returned</option>
                <option value="pending">Pending</option>
                <option value="approved">Approved</option>
                <option value="completed">Completed</option>
                <option value="cancelled">Cancelled</option>
                <option value="in-progress">In Progress</option>
                <option value="good">Good</option>
                <option value="safe">Safe</option>
                <option value="unsafe">Unsafe</option>
                <option value="needs-attention">Urgent</option>
                <option value="low-stock">Low Stock</option>
                <option value="in-transit">In Transit</option>
                <option value="submitted">Submitted</option>
                <option value="operational">Operational</option>
                <option value="needs-repair">Needs Repair</option>
                <option value="draft">Draft</option>
                <option value="archived">Archived</option>
                <option value="disposed">Disposed</option>
            </select>
            <div class="rec-date-range">
                <input type="date" id="dateFilter" onchange="filterTable()" aria-label="Date">
            </div>
        </div>
        <div class="rec-filter-actions">
            <div class="rec-search-wrap">
                <input type="text" id="searchInput" placeholder="Search records…" oninput="filterTable()">
                <i class="bi bi-search search-icon"></i>
            </div>
            <button type="button" class="rec-btn-outline" onclick="resetFilters()"><i class="bi bi-arrow-clockwise"></i> Reset</button>
        </div>
    </div>

    <!-- ── RECENT ACTIVITY HISTORY ──────────────────────────────── -->
    <div class="rec-table-card">
        <div class="rec-table-head">
            <div class="rec-table-title"><i class="bi bi-list-ul"></i> Recent Activity History</div>
            <div class="rec-table-actions">
                <button type="button" class="rec-btn-dark" onclick="document.getElementById('submitReportModal').style.display='flex'"><i class="bi bi-send-fill"></i> Submit Report</button>
                <button type="button" class="rec-btn-outline" onclick="openExportModal('excel')"><i class="bi bi-file-earmark-excel"></i> Export Excel</button>
                <button type="button" class="rec-btn-outline" onclick="openExportModal('pdf')"><i class="bi bi-file-earmark-pdf"></i> Export PDF</button>
                <button type="button" class="rec-btn-dark" onclick="switchRecView('archived')"><i class="bi bi-archive-fill"></i> Archived</button>
            </div>
        </div>

        <div class="table-scroll">
            <table class="rec-table" id="activityTable" <?= !empty($canArchive) ? 'data-bulk-endpoint="' . base_url('records/bulk') . '" data-bulk-actions="archive,dispose"' : '' ?>>
                <thead>
                    <tr>
                        <th>Date &amp; Time</th>
                        <th>Category</th>
                        <th>Record</th>
                        <th>Action</th>
                        <th>Performed By</th>
                        <th>Status</th>
                        <th>View</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($activities)): ?>
                        <tr><td colspan="7" class="empty-row">No activity recorded yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($activities as $act):
                            $modSlug    = $slug($act['category'] ?? $act['module']);
                            $kindSlug   = $slug($act['kind']);
                            $actionSlug = $slug($act['action']);
                            $statusSlug = $slug($act['status']);
                            $dateVal    = $act['date'] ?? null;
                            $dateKey    = $dateVal ? date('Y-m-d', strtotime($dateVal)) : '';
                            $searchBlob = strtolower($act['module'] . ' ' . $act['record'] . ' ' . $act['record_sub'] . ' ' . $act['performed_by'] . ' ' . $act['action'] . ' ' . $act['status']);
                            $modIcon = \App\Libraries\DepartmentScope::CATEGORIES[$act['category'] ?? '']['icon'] ?? 'bi-folder-fill';
                            $modIconUnused = match ($act['module']) {
                                'Tools'      => 'bi-wrench-adjustable',
                                'Vehicle'    => 'bi-truck',
                                'Motor Pool' => 'bi-gear-wide-connected',
                                'Sports'     => 'bi-trophy-fill',
                                'Security'   => 'bi-shield-fill-check',
                                'Facilities' => 'bi-building-gear',
                                'Safety'     => 'bi-shield-check',
                                'Janitorial' => 'bi-brush',
                                'Personnel'  => 'bi-people-fill',
                                default      => 'bi-folder-fill',
                            };
                            $kindIcon = match ($act['kind']) {
                                'Archive' => 'bi-archive-fill',
                                'Report'  => 'bi-file-earmark-bar-graph-fill',
                                default   => 'bi-file-earmark-text-fill',
                            };
                        ?>
                        <tr
                            <?php if (in_array($act['type'], \App\Controllers\RecordsController::archivableTypes(), true)): ?>data-bulk-key="<?= esc($act['type'] . ':' . $act['id']) ?>"<?php endif; ?>
                            data-type="<?= esc($act['type']) ?>"
                            data-module="<?= esc($modSlug) ?>"
                            data-subtype="<?= esc($slug($act['module'])) ?>"
                            data-kind="<?= esc($kindSlug) ?>"
                            data-status="<?= esc($statusSlug) ?>"
                            data-date="<?= esc($dateKey) ?>"
                            data-search="<?= esc($searchBlob) ?>"
                        >
                            <td>
                                <div class="datetime-cell">
                                    <span><?= $dateVal ? date('M j, Y', strtotime($dateVal)) : '—' ?></span>
                                    <span class="time-muted"><?= $dateVal ? date('h:i A', strtotime($dateVal)) : '' ?></span>
                                </div>
                            </td>
                            <td>
                                <span class="mod-pill mod-<?= esc($slug($act['module'])) ?>"><i class="bi <?= $modIcon ?>"></i> <?= esc($act['category'] ?? $act['module']) ?></span>
                                <div class="rec-record-sub"><?= esc($act['type_label'] ?? '') ?></div>
                            </td>
                            <td>
                                <div class="rec-record-name"><?= esc($act['record']) ?></div>
                                <div class="rec-record-sub"><?= esc($act['record_sub']) ?></div>
                            </td>
                            <td><span class="act-text act-<?= esc($actionSlug) ?>"><?= esc($act['action']) ?></span></td>
                            <td><?= esc($act['performed_by']) ?></td>
                            <td><span class="stat-pill stat-<?= esc($statusSlug) ?>"><?= esc($act['status']) ?></span></td>
                            <td>
                                <?php if ($act['type'] === 'report'): ?>
                                    <button type="button" class="rec-view-btn open-report-view"
                                        data-id="<?= esc($act['id']) ?>"
                                        data-report_name="<?= esc($act['record']) ?>"
                                        data-generated_by="<?= esc($act['performed_by']) ?>"
                                        data-type_module="<?= esc($act['record_sub']) ?>"
                                        data-details="<?= esc($act['details'] ?? '') ?>">
                                        <i class="bi bi-eye-fill"></i>
                                    </button>
                                <?php else: ?>
                                    <button type="button" class="rec-view-btn open-record-detail"
                                        data-type="<?= esc($act['type']) ?>"
                                        data-id="<?= esc($act['id']) ?>"
                                        data-date="<?= $dateVal ? esc(date('M j, Y', strtotime($dateVal))) : '—' ?>"
                                        data-module="<?= esc(($act['category'] ?? $act['module']) . ' — ' . ($act['type_label'] ?? '')) ?>"
                                        data-kind="<?= esc($act['kind']) ?>"
                                        data-record="<?= esc($act['record']) ?>"
                                        data-record_sub="<?= esc($act['record_sub']) ?>"
                                        data-action="<?= esc($act['action']) ?>"
                                        data-performed_by="<?= esc($act['performed_by']) ?>"
                                        data-status="<?= esc($act['status']) ?>"
                                        data-is_archived="<?= $act['is_archived'] ? '1' : '0' ?>"
                                        data-disposal_status="<?= esc($act['disposal_status']) ?>">
                                        <i class="bi bi-eye-fill"></i>
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="rec-table-footer">
            <div id="entriesLabel">Showing 0 of 0 entries</div>
            <div class="rec-pagination" id="paginationButtons"></div>
        </div>
    </div>
    </div><!-- /viewRecords -->

    <?php if (!empty($canArchive)): ?>
    <div id="viewArchived"<?= $startView === 'archived' ? '' : ' style="display:none"' ?>>
        <?php
        $arcCats  = array_keys(\App\Libraries\DepartmentScope::CATEGORIES);
        $arcCount = array_fill_keys($arcCats, 0);
        foreach ($archivedRows as $r) { $arcCount[$r['category']] = ($arcCount[$r['category']] ?? 0) + 1; }
        ?>
        <div class="rec-table-card">
            <div class="rec-table-head">
                <div class="rec-table-title"><i class="bi bi-archive-fill"></i> Archived Records <span style="font-weight:400;font-size:13px;color:#888;">· all modules</span></div>
            </div>
            <div style="padding:0 1.2rem 10px;">
                <div class="rec-tabs" style="margin:6px 0 4px;" role="tablist" id="arcCatTabs">
                    <button type="button" class="rec-tab active" data-cat="" onclick="filterArchived('')">All <span class="tab-count"><?= count($archivedRows) ?></span></button>
                    <?php foreach ($arcCats as $cn): if (!isset($retentionByDept[$cn])) continue; ?>
                    <button type="button" class="rec-tab" data-cat="<?= esc($cn) ?>" onclick="filterArchived(this.dataset.cat)"><i class="bi <?= esc(\App\Libraries\DepartmentScope::CATEGORIES[$cn]['icon']) ?>"></i> <?= esc($cn) ?> <span class="tab-count"><?= (int) $arcCount[$cn] ?></span></button>
                    <?php endforeach; ?>
                </div>
                <p style="margin:6px 0 0;font-size:12px;color:#8a6d6f;">Tick records to <strong>Restore</strong> them to their module or <strong>Delete permanently</strong>. Records past their department's retention (<?= esc(implode(' · ', array_map(fn($d, $m) => "$d $m mo", array_keys($retentionByDept), $retentionByDept))) ?>) can also be cleared in Settings → Archive &amp; Retention.</p>
            </div>
            <div class="table-scroll">
                <table class="rec-table" id="archivedTable" data-bulk-endpoint="<?= base_url('records/bulk') ?>" data-bulk-actions="restore,delete">
                    <thead><tr><th>Archived on</th><th>Category</th><th>Type</th><th>Record</th><th>Details</th></tr></thead>
                    <tbody>
                    <?php if (empty($archivedRows)): ?>
                        <tr><td colspan="5"><div class="dsp-empty"><i class="bi bi-archive"></i>Nothing is archived yet.</div></td></tr>
                    <?php else: foreach ($archivedRows as $r): ?>
                        <tr data-bulk-key="<?= esc($r['key']) ?>" data-cat="<?= esc($r['category']) ?>">
                            <td><?= $r['archived_at'] ? date('M j, Y', strtotime($r['archived_at'])) : '—' ?></td>
                            <td><i class="bi <?= esc(\App\Libraries\DepartmentScope::CATEGORIES[$r['category']]['icon'] ?? 'bi-folder') ?>"></i> <?= esc($r['category']) ?></td>
                            <td><?= esc($r['type']) ?></td>
                            <td><strong><?= esc($r['title'] !== '' ? $r['title'] : '—') ?></strong></td>
                            <td><?= esc($r['sub'] !== '' ? $r['sub'] : '—') ?></td>
                        </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if (!empty($canManageArchive)): ?>
    <div id="viewDisposal"<?= $startView === 'disposal' ? '' : ' style="display:none"' ?>>
        <?php
        $dspCats  = array_keys(\App\Libraries\DepartmentScope::CATEGORIES);
        $dspCount = array_fill_keys($dspCats, 0);
        foreach ($disposalRows as $r) { $dspCount[$r['category']] = ($dspCount[$r['category']] ?? 0) + 1; }
        ?>
        <div class="rec-table-card">
            <div class="rec-table-head">
                <div class="rec-table-title"><i class="bi bi-trash3"></i> Disposal Log <span style="font-weight:400;font-size:13px;color:#888;">· all modules</span></div>
                <div class="rec-table-actions">
                    <a class="rec-btn-outline" href="<?= base_url('facilities/aircon') ?>" style="text-decoration:none;"><i class="bi bi-snow2"></i> Dispose aircon units</a>
                </div>
            </div>
            <div style="padding:0 1.2rem 10px;">
                <div class="rec-tabs" style="margin:6px 0 4px;" role="tablist" id="dspCatTabs">
                    <button type="button" class="rec-tab active" data-cat="" onclick="filterDisposal('')">All <span class="tab-count"><?= count($disposalRows) ?></span></button>
                    <?php foreach ($dspCats as $cn): ?>
                    <button type="button" class="rec-tab" data-cat="<?= esc($cn) ?>" onclick="filterDisposal(this.dataset.cat)"><i class="bi <?= esc(\App\Libraries\DepartmentScope::CATEGORIES[$cn]['icon']) ?>"></i> <?= esc($cn) ?> <span class="tab-count"><?= (int) $dspCount[$cn] ?></span></button>
                    <?php endforeach; ?>
                </div>
                <p style="margin:6px 0 0;font-size:12px;color:#8a6d6f;">To dispose of equipment from any module, select it in the <strong>Records</strong> tab and choose <strong>Dispose selected</strong>. It is logged here under its category.</p>
            </div>
            <div class="table-scroll">
                <table class="rec-table" id="disposalTable">
                    <thead><tr><th>Date</th><th>Category</th><th>Type</th><th>Record</th><th>Handled By</th><th>Remarks</th></tr></thead>
                    <tbody>
                    <?php if (empty($disposalRows)): ?>
                        <tr><td colspan="6"><div class="dsp-empty"><i class="bi bi-trash3"></i>Nothing has been disposed of yet.</div></td></tr>
                    <?php else: foreach ($disposalRows as $r): ?>
                        <tr data-cat="<?= esc($r['category']) ?>">
                            <td><?= $r['date'] ? date('M j, Y', strtotime($r['date'])) : '—' ?></td>
                            <td><i class="bi <?= esc(\App\Libraries\DepartmentScope::CATEGORIES[$r['category']]['icon'] ?? 'bi-folder') ?>"></i> <?= esc($r['category']) ?></td>
                            <td><?= esc($r['type']) ?></td>
                            <td><?= esc($r['record']) ?></td>
                            <td><?= esc($r['by']) ?></td>
                            <td><?= esc($r['remarks']) ?></td>
                        </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- ═══════════════════════════════════════════════════════════════
     MODAL: RECORD DETAIL (Tools / Vehicle / Disposal rows)
════════════════════════════════════════════════════════════════ -->
<div id="recordDetailModal" class="modal-overlay" style="display:none;">
    <div class="modal-box modal-sm">
        <div class="modal-header">
            <h3><i class="bi bi-info-circle"></i> Record Details</h3>
            <button class="modal-close" onclick="closeRecordDetail()"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="modal-body">
            <div class="rec-detail-grid" id="recordDetailBody"></div>
        </div>
        <div class="modal-footer" id="recordDetailActions"></div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════
     MODAL: AUTHORIZE DISPOSAL
════════════════════════════════════════════════════════════════ -->
<form id="markDisposalForm" method="post" action="" style="display:none;">
    <?= csrf_field() ?>
</form>

<div id="authorizeModal" class="modal-overlay" style="display:none;">
    <div class="modal-box modal-sm">
        <div class="modal-header">
            <h3><i class="bi bi-shield-check"></i> Authorize Disposal</h3>
            <button class="modal-close" onclick="closeAuthorizeModal()"><i class="bi bi-x-lg"></i></button>
        </div>
        <form id="authorizeForm" method="post" action="<?= base_url('records/authorizeDisposal') ?>">
            <?= csrf_field() ?>
            <div class="modal-body">
                <input type="hidden" id="authType" name="type">
                <input type="hidden" id="authId" name="id">
                <div class="form-group">
                    <label>Disposal Date <span class="req">*</span></label>
                    <input type="date" name="disposal_date" required value="<?= date('Y-m-d') ?>">
                </div>
                <div class="form-group">
                    <label>Notes</label>
                    <textarea name="notes" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label>Digital Signature (Draw Below) <span class="req">*</span></label>
                    <canvas id="signatureCanvas" style="border:1px solid #ddd; border-radius:8px; width:100%; height:150px; background:#fff;"></canvas>
                    <input type="hidden" id="signatureData" name="signature" required>
                    <button type="button" onclick="clearSignature()" style="margin-top:10px; padding:5px 10px;">Clear</button>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeAuthorizeModal()">Cancel</button>
                <button type="submit" class="btn-maroon">Authorize</button>
            </div>
        </form>
    </div>
</div>


<div id="submitReportModal" class="modal-overlay" style="display:none;">
    <div class="modal-box modal-md">
        <div class="modal-header">
            <h3><i class="bi bi-send-fill"></i> Submit Report</h3>
            <button class="modal-close" type="button" onclick="document.getElementById('submitReportModal').style.display='none'"><i class="bi bi-x-lg"></i></button>
        </div>
        <form method="post" action="<?= base_url('reports/submit') ?>">
            <?= csrf_field() ?>
            <div class="modal-body">
                <div class="form-group"><label>Title</label><input type="text" name="report_name" maxlength="255" required></div>
                <div class="form-group"><label>Category</label>
                    <select id="subCategory" required onchange="fillSubTypes()">
                        <?php foreach ($shownCats as $cn): ?><option><?= esc($cn) ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group"><label>Type</label>
                    <select name="category" id="subType" required></select>
                </div>
                <div class="form-group"><label>Details</label><textarea name="details" rows="5" required></textarea></div>
                <p style="font-size:12px;color:#666;margin:0;">Submitted reports start as Pending and can be tracked here until they are marked Completed.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="document.getElementById('submitReportModal').style.display='none'">Cancel</button>
                <button type="submit" class="btn-submit">Submit</button>
            </div>
        </form>
    </div>
</div>

<div id="exportModal" class="modal-overlay" style="display:none;">
    <div class="modal-box modal-sm">
        <div class="modal-header">
            <h3 id="exportModalTitle"><i class="bi bi-download"></i> Export</h3>
            <button class="modal-close" onclick="closeExportModal()"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label>Which category do you want to export?</label>
                <select id="exportModuleSelect">
                    <option value=""><?= $scopeModules ? 'All my categories' : 'All Categories' ?></option>
                    <?php foreach ($shownCats as $cn): $cc = \App\Libraries\DepartmentScope::CATEGORIES[$cn]; ?>
                        <option value="<?= esc($cc['slug']) ?>"><?= esc($cn) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-cancel" onclick="closeExportModal()">Cancel</button>
            <button type="button" class="btn-maroon" onclick="confirmExport()">Export</button>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════
     MODAL: REPORT VIEW/EDIT
════════════════════════════════════════════════════════════════ -->
<div id="reportEditorModal" class="modal-overlay" style="display:none;">
    <div class="modal-box modal-md">
        <div class="modal-header">
            <h3 id="reportEditorTitle"><i class="bi bi-pencil-fill"></i> View Report</h3>
            <button class="modal-close" type="button" onclick="closeReportEditor()"><i class="bi bi-x-lg"></i></button>
        </div>
        <form method="post" action="<?= base_url('reports/update') ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="report_id" id="editorReportId">
            <div class="modal-body">
                <div class="form-group">
                    <label>Report Name</label>
                    <input type="text" name="report_name" id="editorReportName" required>
                </div>
                <div class="form-group">
                    <label>Generated By</label>
                    <input type="text" name="generated_by" id="editorGeneratedBy" required>
                </div>
                <div class="form-group">
                    <label>Category</label>
                    <input type="text" id="editorTypeModule" readonly>
                </div>
                <div class="form-group">
                    <label>Details</label>
                    <textarea id="editorDetails" rows="4" readonly></textarea>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status" id="editorStatus">
                        <option value="">Keep current</option>
                        <option>Pending</option>
                        <option>Completed</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeReportEditor()">Cancel</button>
                <button type="submit" class="btn-submit">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
const BASE_URL = '<?= base_url() ?>';

const CAT_TYPES = <?= json_encode(array_map(fn($c) => $c['types'], \App\Libraries\DepartmentScope::CATEGORIES), JSON_HEX_TAG) ?>;
const ALLOWED_TYPES = <?= json_encode($scopeModules ?: null) ?>;
function fillSubTypes() {
    const cat = document.getElementById('subCategory').value;
    const sel = document.getElementById('subType');
    sel.innerHTML = '';
    Object.entries(CAT_TYPES[cat] || {}).forEach(([value, label]) => {
        if (value === 'Personnel' || (ALLOWED_TYPES && !ALLOWED_TYPES.includes(value))) return;
        const o = document.createElement('option'); o.value = value; o.textContent = label; sel.appendChild(o);
    });
}
fillSubTypes();

// ── Filtering ────────────────────────────────────────────────────
let currentPage = 1;
const PAGE_SIZE = 10;

function getIconFilterValue(groupId) {
    const active = document.querySelector(`#${groupId} .rec-icon-btn.active`);
    return active ? active.dataset.value : '';
}

function setIconFilterValue(groupId, value) {
    document.querySelectorAll(`#${groupId} .rec-icon-btn`).forEach(btn => {
        btn.classList.toggle('active', btn.dataset.value === value);
    });
}

document.querySelectorAll('.rec-icon-filter').forEach(group => {
    group.addEventListener('click', e => {
        const btn = e.target.closest('.rec-icon-btn');
        if (!btn) return;
        group.querySelectorAll('.rec-icon-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        filterTable();
    });
});

// Export asks explicitly which module to export, rather than silently
// reusing whatever filter chip happens to be active on screen — defaults
// to whatever's currently selected (if any) so it's a sensible starting
// point, not a blank pick every time.
let exportFormat = 'pdf';

function openExportModal(format) {
    exportFormat = format;
    document.getElementById('exportModuleSelect').value = getIconFilterValue('moduleFilterGroup');
    document.getElementById('exportModalTitle').textContent = 'Export ' + (format === 'pdf' ? 'PDF' : 'Excel');
    document.getElementById('exportModal').style.display = 'flex';
}

function closeExportModal() {
    document.getElementById('exportModal').style.display = 'none';
}

function confirmExport() {
    const params = new URLSearchParams({
        module: document.getElementById('exportModuleSelect').value,
        kind:   getIconFilterValue('kindFilterGroup'),
        status: document.getElementById('statusFilter').value,
        date:   document.getElementById('dateFilter').value,
        search: document.getElementById('searchInput').value.trim(),
    });
    window.location.href = `<?= base_url('records/export/') ?>${exportFormat}?${params.toString()}`;
    closeExportModal();
}

function filterTable() {
    const mod    = getIconFilterValue('moduleFilterGroup');
    const kind   = getIconFilterValue('kindFilterGroup');
    const status = document.getElementById('statusFilter').value;
    const date   = document.getElementById('dateFilter').value;
    const term   = document.getElementById('searchInput').value.toLowerCase().trim();

    document.querySelectorAll('#activityTable tbody tr[data-type]').forEach(row => {
        const matchMod    = !mod || row.dataset.module === mod;
        const matchKind   = !kind || row.dataset.kind === kind;
        const matchStatus = !status || row.dataset.status === status;
        const matchDate   = !date || row.dataset.date === date;
        const matchTerm   = !term || row.dataset.search.includes(term);
        const matches = matchMod && matchKind && matchStatus && matchDate && matchTerm;
        row.classList.toggle('filtered-out', !matches);
    });

    currentPage = 1;
    applyPagination();
}

function resetFilters() {
    setIconFilterValue('moduleFilterGroup', '');
    setIconFilterValue('kindFilterGroup', '');
    document.getElementById('statusFilter').value = '';
    document.getElementById('dateFilter').value = '';
    document.getElementById('searchInput').value = '';
    filterTable();
}

function filterArchivedOnly() {
    filterRecordsByStat('archived');
}

// Stat cards act as quick filters into the table below — same as Vehicle
// Management / Equipment Management / Notification Center: the cards stay
// right where they are, the table just filters in place.
function switchRecView(which) {
    const views = { records: 'viewRecords', archived: 'viewArchived', disposal: 'viewDisposal' };
    const tabs  = { records: 'tabRecords', archived: 'tabArchived', disposal: 'tabDisposal' };
    Object.keys(views).forEach(k => {
        const v = document.getElementById(views[k]), tb = document.getElementById(tabs[k]);
        if (v) v.style.display = k === which ? '' : 'none';
        if (tb) tb.classList.toggle('active', k === which);
    });
    history.replaceState(null, '', which === 'records' ? location.pathname : '?view=' + which);
}

function filterArchived(cat) {
    document.querySelectorAll('#arcCatTabs .rec-tab').forEach(b => b.classList.toggle('active', (b.dataset.cat || '') === cat));
    document.querySelectorAll('#archivedTable tbody tr[data-cat]').forEach(r => { r.style.display = (!cat || r.dataset.cat === cat) ? '' : 'none'; });
}

function filterDisposal(cat) {
    document.querySelectorAll('#dspCatTabs .rec-tab').forEach(b => b.classList.toggle('active', (b.dataset.cat || '') === cat));
    document.querySelectorAll('#disposalTable tbody tr[data-cat]').forEach(r => { r.style.display = (!cat || r.dataset.cat === cat) ? '' : 'none'; });
}

function filterRecordsByStat(kind) {
    setIconFilterValue('moduleFilterGroup', '');
    setIconFilterValue('kindFilterGroup', '');
    document.getElementById('statusFilter').value = '';
    document.getElementById('dateFilter').value = '';
    document.getElementById('searchInput').value = '';

    if (kind === 'archived') setIconFilterValue('kindFilterGroup', 'archive');
    if (kind === 'reports')  setIconFilterValue('kindFilterGroup', 'report');
    if (kind === 'pending' || kind === 'completed') {
        setIconFilterValue('kindFilterGroup', 'report');
        document.getElementById('statusFilter').value = kind;
    }
    if (kind === 'today')    document.getElementById('dateFilter').value = new Date().toISOString().slice(0, 10);

    filterTable();
}

document.querySelectorAll('.stat-card-clickable').forEach(card => {
    card.addEventListener('keydown', e => {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            card.click();
        }
    });
});

// ── Pagination (client-side, over already-filtered rows) ─────────
function applyPagination() {
    const rows = Array.from(document.querySelectorAll('#activityTable tbody tr[data-type]'))
        .filter(r => !r.classList.contains('filtered-out'));
    const total = rows.length;
    const totalPages = Math.max(1, Math.ceil(total / PAGE_SIZE));
    if (currentPage > totalPages) currentPage = totalPages;

    rows.forEach((row, i) => {
        const page = Math.floor(i / PAGE_SIZE) + 1;
        row.style.display = (page === currentPage) ? '' : 'none';
    });

    document.querySelectorAll('#activityTable tbody tr[data-type].filtered-out').forEach(row => {
        row.style.display = 'none';
    });

    const start = total === 0 ? 0 : (currentPage - 1) * PAGE_SIZE + 1;
    const end   = Math.min(currentPage * PAGE_SIZE, total);
    document.getElementById('entriesLabel').textContent =
        total === 0 ? 'No entries found' : `Showing ${start} to ${end} of ${total} entries`;

    renderPagination(totalPages);
}

function renderPagination(totalPages) {
    const wrap = document.getElementById('paginationButtons');
    wrap.innerHTML = '';

    const addBtn = (label, page, opts = {}) => {
        const b = document.createElement('button');
        b.type = 'button';
        b.className = 'pg-btn' + (opts.active ? ' active' : '');
        b.textContent = label;
        b.disabled = !!opts.disabled;
        b.onclick = () => { currentPage = page; applyPagination(); };
        wrap.appendChild(b);
    };
    const addEllipsis = () => {
        const s = document.createElement('span');
        s.className = 'pg-ellipsis';
        s.textContent = '…';
        wrap.appendChild(s);
    };

    addBtn('«', Math.max(1, currentPage - 1), { disabled: currentPage === 1 });

    const maxButtons = 5;
    let start = Math.max(1, currentPage - 2);
    let end = Math.min(totalPages, start + maxButtons - 1);
    start = Math.max(1, end - maxButtons + 1);

    if (start > 1) { addBtn('1', 1); if (start > 2) addEllipsis(); }
    for (let p = start; p <= end; p++) addBtn(String(p), p, { active: p === currentPage });
    if (end < totalPages) { if (end < totalPages - 1) addEllipsis(); addBtn(String(totalPages), totalPages); }

    addBtn('»', Math.min(totalPages, currentPage + 1), { disabled: currentPage === totalPages });
}

// ── Record detail modal (Tools / Vehicle / Disposal) ──────────────
function openRecordDetail(btn) {
    const d = btn.dataset;
    const rows = [
        ['Date & Time', d.date],
        ['Category', d.module],
        ['Record', d.record],
        ['Reference', d.record_sub],
        ['Action', d.action],
        ['Performed By', d.performed_by],
        ['Status', d.status],
    ];
    document.getElementById('recordDetailBody').innerHTML = rows.map(([label, val]) =>
        `<div class="rec-detail-row"><span>${label}</span><strong>${val || '—'}</strong></div>`
    ).join('');

    const actions = document.getElementById('recordDetailActions');
    actions.innerHTML = '';

    // Where each record type is edited (its own module page).
    const EDIT_PAGES = {
        borrow: 'tools/borrowing', tool: 'tools', vehicle: 'vehicles', trip: 'travel', maintenance: 'vehicles',
        motorpool: 'assets-dept', equipment: 'assets-dept', sports: 'sports-dept/equipment', sportsborrow: 'sports-dept/borrowing',
        personnel: 'personnel', fire: 'security-dept/fire-safety', safetyeq: 'security-dept/fire-safety', inspection: 'security-dept/inspection',
        keylog: 'security-dept/guard', aircon: 'facilities/aircon', buildingcheck: 'facilities/buildings', cleaning: 'facilities/janitorial',
        facwo: 'facilities/work-orders', supply: 'facilities/aircon'
    };
    if (EDIT_PAGES[d.type] && d.type !== 'disposal') {
        const edit = document.createElement('a');
        edit.className = 'btn-maroon';
        edit.style.textDecoration = 'none';
        edit.href = BASE_URL + EDIT_PAGES[d.type];
        edit.innerHTML = '<i class="bi bi-pencil-fill"></i> Open to edit';
        actions.appendChild(edit);
    }

    // Disposal marking is only supported by borrow records (reports have no disposal columns).
    if (d.type === 'borrow') {
        if (d.is_archived !== '1' && d.disposal_status === 'None') {
            const btn1 = document.createElement('button');
            btn1.type = 'button';
            btn1.className = 'btn-cancel';
            btn1.textContent = 'Mark for Disposal';
            btn1.onclick = () => {
                const form = document.getElementById('markDisposalForm');
                form.action = `${BASE_URL}records/markForDisposal/${d.type}/${d.id}`;
                form.submit();
            };
            actions.appendChild(btn1);
        } else if (d.disposal_status === 'For Disposal') {
            const btn2 = document.createElement('button');
            btn2.type = 'button';
            btn2.className = 'btn-maroon';
            btn2.innerHTML = '<i class="bi bi-shield-check"></i> Authorize Disposal';
            btn2.onclick = () => { closeRecordDetail(); openAuthorizeModal(d.type, d.id); };
            actions.appendChild(btn2);
        }
    }

    const closeBtn = document.createElement('button');
    closeBtn.type = 'button';
    closeBtn.className = 'btn-cancel';
    closeBtn.textContent = 'Close';
    closeBtn.onclick = closeRecordDetail;
    actions.appendChild(closeBtn);

    document.getElementById('recordDetailModal').style.display = 'flex';
}

function closeRecordDetail() {
    document.getElementById('recordDetailModal').style.display = 'none';
}

document.querySelectorAll('.open-record-detail').forEach(btn => {
    btn.addEventListener('click', () => openRecordDetail(btn));
});

// ── Authorize Disposal modal + signature pad ──────────────────────
let isDrawing = false;
let lastX = 0;
let lastY = 0;
const canvas = document.getElementById('signatureCanvas');
const ctx = canvas.getContext('2d');

function openAuthorizeModal(type, id) {
    document.getElementById('authType').value = type;
    document.getElementById('authId').value = id;
    document.getElementById('authorizeModal').style.display = 'flex';
    clearSignature();
    initCanvas();
}

function closeAuthorizeModal() {
    document.getElementById('authorizeModal').style.display = 'none';
}

function initCanvas() {
    canvas.width = canvas.offsetWidth;
    canvas.height = canvas.offsetHeight;
    ctx.strokeStyle = '#1a1a1a';
    ctx.lineWidth = 2;
}

canvas.addEventListener('mousedown', startDraw);
canvas.addEventListener('mousemove', draw);
canvas.addEventListener('mouseup', stopDraw);
canvas.addEventListener('mouseout', stopDraw);

canvas.addEventListener('touchstart', (e) => {
    const touch = e.touches[0];
    const rect = canvas.getBoundingClientRect();
    isDrawing = true;
    lastX = touch.clientX - rect.left;
    lastY = touch.clientY - rect.top;
});
canvas.addEventListener('touchmove', (e) => {
    e.preventDefault();
    const touch = e.touches[0];
    const rect = canvas.getBoundingClientRect();
    draw({ clientX: touch.clientX - rect.left, clientY: touch.clientY - rect.top });
});
canvas.addEventListener('touchend', stopDraw);

function startDraw(e) {
    isDrawing = true;
    const rect = canvas.getBoundingClientRect();
    lastX = e.clientX - rect.left;
    lastY = e.clientY - rect.top;
}

function draw(e) {
    if (!isDrawing) return;
    const rect = canvas.getBoundingClientRect();
    const x = (e.clientX || e.offsetX) - rect.left;
    const y = (e.clientY || e.offsetY) - rect.top;

    ctx.beginPath();
    ctx.moveTo(lastX, lastY);
    ctx.lineTo(x, y);
    ctx.stroke();

    lastX = x;
    lastY = y;

    document.getElementById('signatureData').value = canvas.toDataURL('image/png');
}

function stopDraw() {
    isDrawing = false;
}

function clearSignature() {
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    document.getElementById('signatureData').value = '';
}

// ── Report view/edit modal ────────────────────────────────────────
function openReportView(button) {
    const editor = document.getElementById('reportEditorModal');
    document.getElementById('editorReportId').value = button.dataset.id || '';
    document.getElementById('editorReportName').value = button.dataset.report_name || '';
    document.getElementById('editorGeneratedBy').value = button.dataset.generated_by || '';
    document.getElementById('editorTypeModule').value = button.dataset.type_module || 'General';
    document.getElementById('editorDetails').value = button.dataset.details || '';
    document.getElementById('editorStatus').value = '';
    document.getElementById('reportEditorTitle').innerHTML = '<i class="bi bi-pencil-fill"></i> View / Edit Report';
    editor.style.display = 'flex';
}

function closeReportEditor() {
    document.getElementById('reportEditorModal').style.display = 'none';
}

document.querySelectorAll('.open-report-view').forEach(btn => {
    btn.addEventListener('click', () => openReportView(btn));
});

document.querySelectorAll('.modal-overlay').forEach(o => {
    o.addEventListener('click', e => { if (e.target === o) o.style.display = 'none'; });
});

window.addEventListener('load', () => {
    if (document.readyState === 'complete') initCanvas();
});

// Flash auto-hide
setTimeout(() => {
    document.querySelectorAll('.flash').forEach(el => el.style.opacity = '0');
}, 4000);

// Initial pagination render
applyPagination();
</script>

<?= $this->endSection() ?>
