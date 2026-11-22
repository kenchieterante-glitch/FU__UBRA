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
            <h1>Report Center</h1>
            <p class="page-subtitle"><?= $scopedToJanitorial ? 'Janitorial records and activity only.' : ($scopeModules ? implode(' and ', $scopeModules) . ' records and activity only.' : 'View, manage and generate reports for all categories in one place.') ?></p>
        </div>
    </div>

    <?php $startView = $startView ?? 'records'; $disposalRows = $disposalRows ?? []; $archivedRows = $archivedRows ?? []; $canArchive = $canArchive ?? false; ?>
    <!-- ── STATS CARDS ──────────────────────────────────────────── -->
    <div class="stat-cards<?= !empty($canManageArchive) ? ' rec-stats-7' : '' ?>">
        <div class="stat-card stat-card-clickable<?= $startView === 'records' ? ' is-active' : '' ?>" id="tabRecords" data-stat="total" onclick="<?= !empty($canArchive) ? "switchRecView('records');" : '' ?>filterRecordsByStat('total')" role="button" tabindex="0">
            <span class="stat-icon tone-maroon"><i class="bi bi-folder2-open"></i></span>
            <h3>Total Records</h3>
            <div class="value"><?= (int) $stats['total_records'] ?></div>
        </div>
        <div class="stat-card stat-card-clickable<?= $startView === 'archived' ? ' is-active' : '' ?>" id="tabArchived" data-stat="archived" onclick="<?= !empty($canArchive) ? "switchRecView('archived')" : "filterRecordsByStat('archived')" ?>" role="button" tabindex="0">
            <span class="stat-icon tone-neutral"><i class="bi bi-archive-fill"></i></span>
            <h3>Archived Records</h3>
            <div class="value"><?= !empty($canArchive) ? count($archivedRows) : (int) $stats['archived_records'] ?></div>
        </div>
        <?php if (!empty($canManageArchive)): ?>
        <div class="stat-card stat-card-clickable<?= $startView === 'disposal' ? ' is-active' : '' ?>" id="tabDisposal" data-stat="disposal" onclick="switchRecView('disposal')" role="button" tabindex="0">
            <span class="stat-icon tone-red"><i class="bi bi-trash3"></i></span>
            <h3>Disposed Records</h3>
            <div class="value"><?= count($disposalRows) ?></div>
        </div>
        <?php endif; ?>
        <div class="stat-card stat-card-clickable" data-stat="reports" onclick="filterRecordsByStat('reports')" role="button" tabindex="0">
            <span class="stat-icon tone-gold"><i class="bi bi-file-earmark-text"></i></span>
            <h3>Reports Generated</h3>
            <div class="value"><?= (int) $stats['reports_generated'] ?></div>
        </div>
        <div class="stat-card stat-card-clickable" data-stat="pending" onclick="filterRecordsByStat('pending')" role="button" tabindex="0" title="Reports waiting to be completed">
            <span class="stat-icon tone-gold"><i class="bi bi-hourglass-split"></i></span>
            <h3>Pending Reports</h3>
            <div class="value"><?= (int) ($stats['reports_pending'] ?? 0) ?></div>
        </div>
        <div class="stat-card stat-card-clickable" data-stat="completed" onclick="filterRecordsByStat('completed')" role="button" tabindex="0" title="Reports that are done">
            <span class="stat-icon tone-green"><i class="bi bi-check2-circle"></i></span>
            <h3>Completed Reports</h3>
            <div class="value"><?= (int) ($stats['reports_completed'] ?? 0) ?></div>
        </div>
        <div class="stat-card stat-card-clickable" data-stat="today" onclick="filterRecordsByStat('today')" role="button" tabindex="0">
            <span class="stat-icon tone-green"><i class="bi bi-calendar-day"></i></span>
            <h3>Today's Activities</h3>
            <div class="value"><?= (int) $stats['today_activities'] ?></div>
        </div>
    </div>

    <style>
      @media (min-width:1100px){.rec-stats-7{display:grid;grid-template-columns:repeat(7,minmax(0,1fr))}.rec-stats-7 .stat-card{flex:none;width:auto;min-width:0;max-width:none;padding-left:8px;padding-right:8px}.rec-stats-7 .stat-card h3{white-space:normal;text-align:center;min-height:2.6em;display:flex;align-items:center;justify-content:center}}
      .stat-card.is-active{border-color:#800000;box-shadow:0 0 0 2px rgba(128,0,0,.25),var(--shadow)}
      /* Category filter: small chips */
      .rec-tabs{display:flex;flex-wrap:wrap;gap:8px;margin-bottom:6px}
      .rec-tabs .rec-tab{border:1px solid #d6d6dd;background:var(--card,#fff);padding:5px 12px;border-radius:999px;font-weight:600;font-size:12.5px;color:var(--text);cursor:pointer;display:inline-flex;align-items:center;gap:6px}
      .rec-tabs .rec-tab:hover{border-color:#800000;color:#800000}
      .rec-tabs .rec-tab.active{background:#800000;border-color:#800000;color:#fff}
      .rec-tabs .tab-count{font-size:11px;padding:0 7px;border-radius:999px;background:rgba(0,0,0,.1)}
      .rec-tabs .rec-tab.active .tab-count{background:rgba(255,255,255,.25)}
      .dsp-empty{padding:34px 12px;text-align:center;color:var(--text)}
      .dsp-empty i{font-size:30px;display:block;margin-bottom:8px;color:#c9a9a9}
    </style>

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
                <div class="rec-table-title"><i class="bi bi-archive-fill"></i> Archived Records <span style="font-weight:400;font-size:13px;color:var(--text);">· all modules</span></div>
            </div>
            <div style="padding:0 1.2rem 10px;">
                <div class="rec-tabs" style="margin:6px 0 4px;" role="tablist" id="arcCatTabs">
                    <button type="button" class="rec-tab active" data-cat="" onclick="filterArchived('')">All <span class="tab-count"><?= count($archivedRows) ?></span></button>
                    <?php foreach ($arcCats as $cn): if (!isset($retentionByDept[$cn])) continue; ?>
                    <button type="button" class="rec-tab" data-cat="<?= esc($cn) ?>" onclick="filterArchived(this.dataset.cat)"><i class="bi <?= esc(\App\Libraries\DepartmentScope::CATEGORIES[$cn]['icon']) ?>"></i> <?= esc($cn) ?> <span class="tab-count"><?= (int) $arcCount[$cn] ?></span></button>
                    <?php endforeach; ?>
                </div>
                <p style="margin:6px 0 0;font-size:12px;color:var(--text);">Tick records to <strong>Restore</strong> them to their module or <strong>Delete permanently</strong>. Records past their department's retention (<?= esc(implode(' · ', array_map(fn($d, $m) => "$d $m mo", array_keys($retentionByDept), $retentionByDept))) ?>) can also be cleared in Settings → Archive &amp; Retention.</p>
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
                <div class="rec-table-title"><i class="bi bi-trash3"></i> Disposal Log <span style="font-weight:400;font-size:13px;color:var(--text);">· all modules</span></div>
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
                <p style="margin:6px 0 0;font-size:12px;color:var(--text);">To dispose of equipment from any module, select it in the <strong>Records</strong> tab and choose <strong>Dispose selected</strong>. It is logged here under its category.</p>
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
                <p style="font-size:12px;color:var(--text);margin:0;">Submitted reports start as Pending and can be tracked here until they are marked Completed.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="document.getElementById('submitReportModal').style.display='none'">Cancel</button>
                <button type="submit" class="btn-submit">Submit</button>
            </div>
        </form>
    </div>
</div>

<style>
  .xp-box{max-width:520px;width:calc(100% - 32px);padding:0;overflow:hidden;border-radius:16px}
  .xp-head{display:flex;align-items:center;gap:14px;padding:20px 24px;border-bottom:1px solid var(--line)}
  .xp-head-icon{width:44px;height:44px;border-radius:12px;background:var(--ubra-accent-soft,#f6ecec);color:var(--maroon);display:flex;align-items:center;justify-content:center;font-size:21px;flex:0 0 auto}
  .xp-head h3{margin:0;font-size:18px;font-weight:700;color:var(--text)}
  .xp-head p{margin:2px 0 0;font-size:13px;color:var(--text)}
  .xp-close{margin-left:auto;border:0;background:transparent;width:34px;height:34px;border-radius:9px;color:var(--text);font-size:16px;cursor:pointer}
  .xp-close:hover{background:var(--bg-subtle)}
  .xp-body{padding:20px 24px;display:grid;gap:20px}
  .xp-label{font-size:12px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--text);margin-bottom:8px;display:block}
  .xp-seg{display:grid;grid-template-columns:1fr 1fr;gap:10px}
  .xp-opt{display:flex;align-items:center;gap:12px;padding:12px 14px;border:1.5px solid var(--line);border-radius:12px;background:var(--card);cursor:pointer;text-align:left;color:var(--text);font-size:14px;font-weight:600;transition:border-color .15s,background .15s,box-shadow .15s}
  .xp-opt:hover{border-color:var(--maroon)}
  .xp-opt.on{border-color:var(--maroon);background:var(--ubra-accent-soft,#f6ecec);box-shadow:0 0 0 3px rgba(128,0,0,.1)}
  .xp-opt i{font-size:20px;color:var(--maroon)}
  .xp-opt small{display:block;font-weight:500;font-size:12px;color:var(--text)}
  .xp-cats{display:grid;grid-template-columns:1fr 1fr;gap:10px}
  .xp-cats .xp-opt{padding:10px 12px;font-size:13.5px}
  .xp-cats .xp-opt i{font-size:17px}
  .xp-range{display:grid;grid-template-columns:1fr 1fr;gap:10px}
  .xp-range input{height:42px;border:1px solid #d4d4dc;border-radius:10px;padding:0 12px;background:var(--card);color:var(--text);font-size:14px;width:100%}
  .xp-range input:focus{outline:none;border-color:var(--maroon);box-shadow:0 0 0 3px rgba(128,0,0,.14)}
  .xp-chips{display:flex;flex-wrap:wrap;gap:8px;margin-top:10px}
  .xp-chip{border:1px solid var(--line);background:var(--card);color:var(--text);border-radius:999px;padding:5px 12px;font-size:12.5px;font-weight:600;cursor:pointer}
  .xp-chip:hover{border-color:var(--maroon);color:var(--maroon)}
  .xp-note{display:flex;gap:10px;align-items:flex-start;padding:11px 14px;border-radius:10px;background:var(--bg-subtle);font-size:13px;color:var(--text)}
  .xp-note i{color:var(--maroon);margin-top:1px}
  .xp-foot{display:flex;justify-content:flex-end;align-items:center;gap:10px;padding:16px 24px;border-top:1px solid var(--line);background:var(--bg-subtle)}
  .xp-btn{display:inline-flex;align-items:center;gap:8px;padding:10px 20px;border-radius:10px;border:1px solid var(--line);background:var(--card);color:var(--text);font-size:14px;font-weight:600;cursor:pointer}
  .xp-btn:hover{background:var(--bg-subtle)}
  .xp-btn.primary{background:var(--maroon);border-color:var(--maroon);color:#fff}
  .xp-btn.primary:hover{background:var(--maroon-dark)}
  @media(max-width:520px){.xp-seg,.xp-cats,.xp-range{grid-template-columns:1fr}}
</style>
<div id="exportModal" class="modal-overlay" style="display:none;" onclick="if(event.target===this)closeExportModal()">
    <div class="modal-box xp-box" role="dialog" aria-labelledby="exportModalTitle">
        <div class="xp-head">
            <span class="xp-head-icon"><i class="bi bi-box-arrow-down"></i></span>
            <div>
                <h3 id="exportModalTitle">Export records</h3>
                <p>Choose what to include, then download.</p>
            </div>
            <button type="button" class="xp-close" onclick="closeExportModal()" aria-label="Close"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="xp-body">
            <div>
                <span class="xp-label">Format</span>
                <div class="xp-seg" id="xpFormat">
                    <button type="button" class="xp-opt" data-format="pdf" onclick="setExportFormat('pdf')"><i class="bi bi-file-earmark-pdf"></i><span>PDF<small>Print-ready report</small></span></button>
                    <button type="button" class="xp-opt" data-format="excel" onclick="setExportFormat('excel')"><i class="bi bi-file-earmark-excel"></i><span>Excel<small>Open in a spreadsheet</small></span></button>
                </div>
            </div>
            <div>
                <span class="xp-label">Category</span>
                <input type="hidden" id="exportModuleSelect" value="">
                <div class="xp-cats" id="xpCats">
                    <button type="button" class="xp-opt on" data-cat="" onclick="setExportCat('')"><i class="bi bi-grid-fill"></i><span><?= $scopeModules ? 'All my categories' : 'All categories' ?></span></button>
                    <?php foreach ($shownCats as $cn): $cc = \App\Libraries\DepartmentScope::CATEGORIES[$cn]; ?>
                    <button type="button" class="xp-opt" data-cat="<?= esc($cc['slug']) ?>" onclick="setExportCat(this.dataset.cat)"><i class="bi <?= esc($cc['icon']) ?>"></i><span><?= esc($cn) ?></span></button>
                    <?php endforeach; ?>
                </div>
            </div>
            <div>
                <span class="xp-label">Date range <span style="text-transform:none;letter-spacing:0;font-weight:500">(optional)</span></span>
                <div class="xp-range">
                    <input type="date" id="exportFrom" aria-label="From date">
                    <input type="date" id="exportTo" aria-label="To date">
                </div>
                <div class="xp-chips">
                    <button type="button" class="xp-chip" onclick="setExportRange('today')">Today</button>
                    <button type="button" class="xp-chip" onclick="setExportRange('week')">Last 7 days</button>
                    <button type="button" class="xp-chip" onclick="setExportRange('month')">This month</button>
                    <button type="button" class="xp-chip" onclick="setExportRange('')">All time</button>
                </div>
            </div>
            <div class="xp-note" id="xpFilters" style="display:none"><i class="bi bi-funnel"></i><span id="xpFiltersText"></span></div>
        </div>
        <div class="xp-foot">
            <button type="button" class="xp-btn" onclick="closeExportModal()">Cancel</button>
            <button type="button" class="xp-btn primary" id="xpGo" onclick="confirmExport()"><i class="bi bi-download"></i> <span id="xpGoText">Export</span></button>
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
    setExportFormat(format);
    setExportCat(getIconFilterValue('moduleFilterGroup') || '');
    document.getElementById('exportFrom').value = '';
    document.getElementById('exportTo').value = '';
    // Remind the user which table filters are also applied.
    const parts = [];
    const st = document.getElementById('statusFilter').value, dt = document.getElementById('dateFilter').value,
          q = document.getElementById('searchInput').value.trim(), kd = getIconFilterValue('kindFilterGroup');
    if (kd) parts.push('type: ' + kd); if (st) parts.push('status: ' + st); if (dt) parts.push('date: ' + dt); if (q) parts.push('search: "' + q + '"');
    const note = document.getElementById('xpFilters');
    note.style.display = parts.length ? 'flex' : 'none';
    document.getElementById('xpFiltersText').textContent = parts.length ? 'Your current filters are also applied (' + parts.join(', ') + ').' : '';
    document.getElementById('exportModal').style.display = 'flex';
}

function setExportFormat(format) {
    exportFormat = format;
    document.querySelectorAll('#xpFormat .xp-opt').forEach(b => b.classList.toggle('on', b.dataset.format === format));
    document.getElementById('xpGoText').textContent = 'Export ' + (format === 'pdf' ? 'PDF' : 'Excel');
}

function setExportCat(cat) {
    document.getElementById('exportModuleSelect').value = cat;
    document.querySelectorAll('#xpCats .xp-opt').forEach(b => b.classList.toggle('on', (b.dataset.cat || '') === cat));
}

function setExportRange(kind) {
    const f = document.getElementById('exportFrom'), t = document.getElementById('exportTo');
    const iso = d => new Date(d.getTime() - d.getTimezoneOffset() * 60000).toISOString().slice(0, 10);
    const now = new Date();
    if (kind === 'today') { f.value = t.value = iso(now); }
    else if (kind === 'week') { const s = new Date(now); s.setDate(s.getDate() - 6); f.value = iso(s); t.value = iso(now); }
    else if (kind === 'month') { f.value = iso(new Date(now.getFullYear(), now.getMonth(), 1)); t.value = iso(now); }
    else { f.value = ''; t.value = ''; }
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
        date_from: document.getElementById('exportFrom').value,
        date_to:   document.getElementById('exportTo').value,
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

    // Keep the highlighted status box in step with what the filters are really showing.
    const today = new Date().toISOString().slice(0, 10);
    let which = null;
    if (!mod && !term) {
        if (!kind && !status && !date) which = 'total';
        else if (kind === 'archive' && !status && !date) which = 'archived';
        else if (kind === 'report' && !status && !date) which = 'reports';
        else if (kind === 'report' && status === 'pending' && !date) which = 'pending';
        else if (kind === 'report' && status === 'completed' && !date) which = 'completed';
        else if (!kind && !status && date === today) which = 'today';
    }
    const recView = document.getElementById('viewRecords');
    if (!recView || recView.style.display !== 'none') setActiveStat(which);
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
        
    });
    setActiveStat(which === 'records' ? 'total' : which);
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

function setActiveStat(kind) {
    document.querySelectorAll('.stat-card[data-stat]').forEach(c => c.classList.toggle('is-active', c.dataset.stat === kind));
}
function filterRecordsByStat(kind) {
    // These filters act on the Records table, so leave the Archived / Disposal view first.
    if (document.getElementById('viewRecords') && document.getElementById('viewRecords').style.display === 'none') switchRecView('records');
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
    setActiveStat(kind);
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
