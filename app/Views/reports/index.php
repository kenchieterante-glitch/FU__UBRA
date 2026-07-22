<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<link rel="stylesheet" href="<?= base_url('Assets/css/reports.css') ?>">

<div class="rpt-wrapper">

    <!-- ── PAGE HEADER ──────────────────────────────────────────── -->
    <div class="page-header">
        <div>
            <h1 class="page-title"><i class="bi bi-folder-closed"></i> Records, Archiving &amp; Reports</h1>
            <p class="page-subtitle">Automatic archiving with disposal tracking. Each module feeds into a shared dashboard that gives the B&amp;G Head a real-time view of the entire department’s operation.</p>
        </div>
        <div class="header-actions">
            <button class="btn-primary" onclick="openGenerateModal()">
                <i class="bi bi-file-earmark-bar-graph"></i> Generate Report
            </button>
            <a href="<?= base_url('reports/export/trips') ?>" class="btn-outline">
                <i class="bi bi-filetype-pdf"></i> PDF
            </a>
            <a href="<?= base_url('reports/export/trips') ?>" class="btn-outline">
                <i class="bi bi-file-earmark-excel"></i> Excel
            </a>
            <button class="btn-outline" onclick="window.print()">
                <i class="bi bi-printer"></i> Print
            </button>
        </div>
    </div>

    <?php if (!empty($flash_success)): ?>
        <div class="flash flash-success"><i class="bi bi-check-circle-fill"></i> <?= esc($flash_success) ?></div>
    <?php endif; ?>

    <!-- ── FILTERS ───────────────────────────────────────────────── -->
    <div class="filter-bar">
        <div class="filter-group">
            <label>Date Range</label>
            <select id="dateRange">
                <option>Last 30 Days</option>
                <option>Last 90 Days</option>
                <option>Last 6 Months</option>
                <option>This Year</option>
            </select>
        </div>
        <div class="filter-group">
            <label>Department</label>
            <select id="deptFilter">
                <option>All Departments</option>
                <option>Facilities</option>
                <option>Logistics</option>
                <option>Security</option>
                <option>Housekeeping</option>
            </select>
        </div>
        <div class="filter-group">
            <label>Vehicle</label>
            <select id="vehicleFilter">
                <option>All Vehicles</option>
                <?php foreach ($vehicle_util as $v): ?>
                    <option><?= esc($v['model']) ?> — <?= esc($v['plate']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="filter-group">
            <label>Asset Category</label>
            <select id="assetFilter">
                <option>All Asset Categories</option>
                <?php foreach ($asset_dist as $a): ?>
                    <option><?= esc($a['category']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="filter-group">
            <label>Personnel</label>
            <select id="personnelFilter"><option>All Personnel</option></select>
        </div>
        <button class="filter-reset" onclick="resetFilters()">Reset Filters</button>
    </div>

    <!-- ── REPORT TABS ───────────────────────────────────────────── -->
    <div class="report-tab-strip" aria-label="Report sections">
        <button class="report-tab active" type="button" data-target="overview-section"><i class="bi bi-grid-1x2-fill"></i> Overview</button>
        <button class="report-tab" type="button" data-target="trip-metrics-section"><i class="bi bi-clipboard-data-fill"></i> Trip Metrics</button>
        <button class="report-tab" type="button" data-target="fleet-analytics-section"><i class="bi bi-truck-front-fill"></i> Fleet Analytics</button>
        <button class="report-tab" type="button" data-target="maintenance-section"><i class="bi bi-tools"></i> Maintenance</button>
    </div>

    <!-- ── Tab Descriptions ─────────────────────────────────────── -->
    <div style="margin-bottom: 20px; padding: 10px; background: #f0f2f5; border-radius: 8px;">
        <p style="margin: 0; color: #444;"><strong>Section Guide:</strong></p>
        <ul style="margin: 8px 0 0 20px; color: #555;">
            <li><strong>Overview:</strong> High-level summary of key operational metrics (assets, vehicles, trips, staff)</li>
            <li><strong>Trip Metrics:</strong> Detailed travel request data, trip statuses, and trends</li>
            <li><strong>Fleet Analytics:</strong> Vehicle performance, utilization, and GPS status</li>
            <li><strong>Maintenance:</strong> Equipment health, pending maintenance, and work order statuses</li>
        </ul>
    </div>

    <!-- ── KPI SUMMARY CARDS ─────────────────────────────────────── -->
    <div class="stat-cards" id="overview-section">
        <div class="stat-card">
            <div class="label"><i class="fa-solid fa-boxes-stacked"></i> Total Assets</div>
            <div class="value"><?= number_format($total_assets) ?></div>
            <div class="trend neutral">Asset inventory</div>
        </div>
        <div class="stat-card">
            <div class="label"><i class="fa-solid fa-truck"></i> Vehicle Fleet</div>
            <div class="value"><?= $total_vehicles ?></div>
            <div class="trend neutral">Registered vehicles</div>
        </div>
        <div class="stat-card">
            <div class="label"><i class="fa-solid fa-route"></i> Scheduled Trips</div>
            <div class="value"><?= number_format($completed_trips) ?></div>
            <div class="trend neutral">Active requests</div>
        </div>
        <div class="stat-card">
            <div class="label"><i class="fa-solid fa-users"></i> Active Personnel</div>
            <div class="value"><?= $on_duty ?></div>
            <div class="trend neutral">On duty today</div>
        </div>
        <div class="stat-card accent-red">
            <div class="label"><i class="fa-solid fa-fire"></i> Pending Maint.</div>
            <div class="value"><?= $pending_maint ?></div>
            <div class="trend down">3 need attention</div>
        </div>
        <div class="stat-card accent-amber">
            <div class="label"><i class="fa-solid fa-broom"></i> Alerts Active</div>
            <div class="value"><?= $active_alerts ?></div>
            <div class="trend neutral">View Registry →</div>
        </div>
    </div>

    <!-- ── SECTION: TRIP METRICS ─────────────────────────────────────── -->
    <div class="rpt-body" id="trip-metrics-section">
        <div class="table-panel">
            <div class="table-toolbar">
                <h2 class="panel-title">Trip Metrics</h2>
                <a href="<?= base_url('reports/export/trips') ?>" class="btn-outline-sm">
                    <i class="bi bi-download"></i> Export Trips CSV
                </a>
            </div>
            <p style="padding: 10px 20px; color: #555;">Track all travel requests, approvals, completions, and trip trends over time.</p>
        </div>
    </div>

    <!-- ── SECTION: FLEET ANALYTICS ─────────────────────────────────── -->
    <div class="rpt-body" id="fleet-analytics-section">
        <div class="table-panel">
            <div class="table-toolbar">
                <h2 class="panel-title">Fleet Analytics</h2>
                <a href="<?= base_url('reports/export/vehicles') ?>" class="btn-outline-sm">
                    <i class="bi bi-truck"></i> Vehicles CSV
                </a>
            </div>
            <p style="padding: 10px 20px; color: #555;">Monitor vehicle utilization, GPS status, maintenance schedules, and fleet performance.</p>
        </div>
    </div>

    <!-- ── SECTION: MAINTENANCE ──────────────────────────────────────── -->
    <div class="rpt-body" id="maintenance-section">
        <div class="table-panel">
            <div class="table-toolbar">
                <h2 class="panel-title">Maintenance</h2>
                <a href="<?= base_url('reports/export/assets') ?>" class="btn-outline-sm">
                    <i class="bi bi-box-seam"></i> Assets CSV
                </a>
            </div>
            <p style="padding: 10px 20px; color: #555;">Manage equipment maintenance, work orders, and asset health tracking.</p>
        </div>
    </div>

    <!-- ── BODY: RECENT REPORTS TABLE ─────────────────────────────────── -->
    <div class="rpt-body">
        <!-- Recent Reports Table -->
        <div class="table-panel">
            <div class="table-toolbar">
                <h2 class="panel-title">Generated Reports</h2>
                <button class="btn-primary" onclick="openGenerateModal()">
                    <i class="bi bi-plus-circle"></i> Generate New Report
                </button>
            </div>
            <table class="rpt-table">
                <thead>
                    <tr>
                        <th>Report Name</th>
                        <th>Generated By</th>
                        <th>Generated Date</th>
                        <th>Type / Module</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recent_reports)): ?>
                        <tr><td colspan="6" class="empty-row">
                            No reports generated yet. Click <strong>Generate New Report</strong> to create one.
                        </td></tr>
                    <?php else: ?>
                        <?php foreach ($recent_reports as $r): ?>
                        <tr>
                            <td class="rpt-name"><?= esc($r['report_name']) ?></td>
                            <td><?= esc($r['generated_by']) ?></td>
                            <td>
                                <div><?= date('M j, Y', strtotime($r['created_at'])) ?></div>
                                <div class="time-muted"><?= date('h:i A', strtotime($r['created_at'])) ?></div>
                            </td>
                            <td>
                                <?php
                                $typeClass = match($r['type_module'] ?? 'General') {
                                    'Travel Operations'   => 'type-travel',
                                    'Facilities Management' => 'type-facilities',
                                    default => 'type-general',
                                };
                                ?>
                                <span class="type-badge <?= $typeClass ?>"><?= esc($r['type_module'] ?? 'General') ?></span>
                            </td>
                            <td><span class="status-badge st-completed">Completed</span></td>
                            <td>
                                <div class="report-action-stack" style="gap: 8px;">
                                    <button type="button" class="action-btn action-btn-secondary open-report-view"
                                        data-id="<?= esc($r['id']) ?>"
                                        data-report_name="<?= esc($r['report_name']) ?>"
                                        data-generated_by="<?= esc($r['generated_by']) ?>"
                                        data-type_module="<?= esc($r['type_module'] ?? 'General') ?>"
                                        data-date_range="<?= esc($r['date_range'] ?? 'Last 30 Days') ?>">
                                        <i class="bi bi-eye"></i> View Details
                                    </button>
                                    <a href="<?= base_url('reports/download/' . (int)$r['id']) ?>" class="action-btn action-btn-download" style="background: #28a745; color: white;">
                                        <i class="bi bi-download"></i> Download CSV
                                    </a>
                                    <a href="<?= base_url('reports/delete/' . (int)$r['id']) ?>" class="action-btn" style="background: #dc3545; color: white;" onclick="return confirm('Delete this report?')">
                                        <i class="bi bi-trash"></i> Delete
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>


</div>

<!-- ═══════════════════════════════════════════════════════════════
     GENERATE REPORT MODAL
════════════════════════════════════════════════════════════════ -->
<div id="generateModal" class="modal-overlay" style="display:none;">
    <div class="modal-box modal-sm">
        <div class="modal-header">
            <h3><i class="bi bi-file-earmark-plus"></i> Generate New Report</h3>
            <button class="modal-close" onclick="closeGenerateModal()"><i class="bi bi-x-lg"></i></button>
        </div>
        <form method="post" action="<?= base_url('reports/generate') ?>">
            <?= csrf_field() ?>
            <div class="modal-body">
                <div class="form-group">
                    <label>Report Type <span class="req">*</span></label>
                    <select name="report_type" required>
                        <option value="Travel Operations">Travel Operations</option>
                        <option value="Facilities Management">Facilities Management</option>
                        <option value="Asset Inventory">Asset Inventory</option>
                        <option value="Vehicle Fleet">Vehicle Fleet</option>
                        <option value="Personnel">Personnel</option>
                        <option value="Maintenance Compliance">Maintenance Compliance</option>
                        <option value="Janitorial Performance">Janitorial Performance</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Date Range</label>
                    <select name="date_range">
                        <option>Last 30 Days</option>
                        <option>Last 90 Days</option>
                        <option>Last 6 Months</option>
                        <option>This Year</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeGenerateModal()">Cancel</button>
                <button type="submit" class="btn-submit">
                    <i class="bi bi-file-earmark-bar-graph"></i> Generate
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════
     REPORT EDITOR MODAL
════════════════════════════════════════════════════════════════ -->
<div id="reportEditorModal" class="modal-overlay" style="display:none;">
    <div class="modal-box modal-md">
        <div class="modal-header">
            <h3 id="reportEditorTitle"><i class="bi bi-pencil-square"></i> Edit Recent Report</h3>
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
                    <label>Type / Module</label>
                    <select name="type_module" id="editorTypeModule" required>
                        <option>Travel Operations</option>
                        <option>Facilities Management</option>
                        <option>Asset Inventory</option>
                        <option>Vehicle Fleet</option>
                        <option>Personnel</option>
                        <option>Maintenance Compliance</option>
                        <option>Janitorial Performance</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Date Range</label>
                    <select name="date_range" id="editorDateRange" required>
                        <option>Last 30 Days</option>
                        <option>Last 90 Days</option>
                        <option>Last 6 Months</option>
                        <option>This Year</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeReportEditor()">Cancel</button>
                <button type="submit" class="btn-submit" id="reportSaveButton"><i class="bi bi-floppy"></i> Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>

// ── Modal helpers ──────────────────────────────────────────────
function openGenerateModal()  { document.getElementById('generateModal').style.display = 'flex'; }
function closeGenerateModal() { document.getElementById('generateModal').style.display = 'none'; }
function openReportEditor(button) {
    const editor = document.getElementById('reportEditorModal');
    const title = document.getElementById('reportEditorTitle');
    const saveButton = document.getElementById('reportSaveButton');

    document.getElementById('editorReportId').value = button.dataset.id || '';
    document.getElementById('editorReportName').value = button.dataset.report_name || '';
    document.getElementById('editorGeneratedBy').value = button.dataset.generated_by || '';
    document.getElementById('editorTypeModule').value = button.dataset.type_module || 'General';
    document.getElementById('editorDateRange').value = button.dataset.date_range || 'Last 30 Days';

    title.innerHTML = '<i class="bi bi-pencil-square"></i> Edit Recent Report';
    document.getElementById('editorReportName').removeAttribute('readonly');
    document.getElementById('editorGeneratedBy').removeAttribute('readonly');
    document.getElementById('editorTypeModule').removeAttribute('disabled');
    document.getElementById('editorDateRange').removeAttribute('disabled');
    saveButton.style.display = 'inline-flex';
    editor.style.display = 'flex';
}
function openReportView(button) {
    const editor = document.getElementById('reportEditorModal');
    const title = document.getElementById('reportEditorTitle');
    const saveButton = document.getElementById('reportSaveButton');

    document.getElementById('editorReportId').value = button.dataset.id || '';
    document.getElementById('editorReportName').value = button.dataset.report_name || '';
    document.getElementById('editorGeneratedBy').value = button.dataset.generated_by || '';
    document.getElementById('editorTypeModule').value = button.dataset.type_module || 'General';
    document.getElementById('editorDateRange').value = button.dataset.date_range || 'Last 30 Days';

    title.innerHTML = '<i class="bi bi-eye"></i> View Recent Report';
    document.getElementById('editorReportName').removeAttribute('readonly');
    document.getElementById('editorGeneratedBy').removeAttribute('readonly');
    document.getElementById('editorTypeModule').removeAttribute('disabled');
    document.getElementById('editorDateRange').removeAttribute('disabled');
    saveButton.style.display = 'inline-flex';
    editor.style.display = 'flex';
}
function closeReportEditor() { document.getElementById('reportEditorModal').style.display = 'none'; }
function resetFilters() {
    document.querySelectorAll('.filter-bar select').forEach(s => s.selectedIndex = 0);
}

document.querySelectorAll('.report-tab').forEach(tab => {
    tab.addEventListener('click', () => {
        // Remove active from all tabs
        document.querySelectorAll('.report-tab').forEach(btn => btn.classList.remove('active'));
        // Add active to clicked tab
        tab.classList.add('active');

        // Hide all report sections except Generated Reports
        const sections = [
            'overview-section',
            'trip-metrics-section',
            'fleet-analytics-section',
            'maintenance-section'
        ];
        sections.forEach(sectionId => {
            const el = document.getElementById(sectionId);
            if (el) el.style.display = 'none';
        });

        // Show the target section
        const target = document.getElementById(tab.dataset.target);
        if (target) {
            target.style.display = 'block';
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    });
});

// Initialize visibility on page load
document.addEventListener('DOMContentLoaded', () => {
    // Show only overview section by default
    const sections = [
        'trip-metrics-section',
        'fleet-analytics-section',
        'maintenance-section'
    ];
    sections.forEach(sectionId => {
            const el = document.getElementById(sectionId);
            if (el) el.style.display = 'none';
        });
    const overviewSection = document.getElementById('overview-section');
    if (overviewSection) overviewSection.style.display = 'block';
});

document.querySelectorAll('.open-report-editor').forEach(btn => {
    btn.addEventListener('click', () => openReportEditor(btn));
});

document.querySelectorAll('.open-report-view').forEach(btn => {
    btn.addEventListener('click', () => openReportView(btn));
});

document.querySelectorAll('.modal-overlay').forEach(o => {
    o.addEventListener('click', e => { if (e.target === o) o.style.display = 'none'; });
});

setTimeout(() => {
    document.querySelectorAll('.flash').forEach(el => el.style.opacity = '0');
}, 4000);
</script>

<?= $this->endSection() ?>