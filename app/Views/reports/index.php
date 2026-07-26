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
    </div>

    <?php if (!empty($flash_success)): ?>
        <div class="flash flash-success"><i class="bi bi-check-circle-fill"></i> <?= esc($flash_success) ?></div>
    <?php endif; ?>

    <div class="reports-shell">
        <div class="reports-tabbar">
            <div class="reports-tab-group">
                <button class="reports-tab active" type="button" data-target="records-panel"><i class="bi bi-file-earmark-text"></i> Records</button>
                <button class="reports-tab" type="button" data-target="archive-panel"><i class="bi bi-archive"></i> Archive</button>
                <button class="reports-tab" type="button" data-target="reports-panel"><i class="bi bi-bar-chart-line"></i> Reports</button>
            </div>
            <button class="reports-action-btn" onclick="openGenerateModal()">
                <i class="bi bi-file-earmark-arrow-down"></i> View report
            </button>
        </div>

        <div class="reports-toolbar-meta">
            <span class="reports-pill"><span class="dot green"></span>Active records</span>
            <span class="reports-pill"><span class="dot amber"></span>Pending review</span>
            <span class="reports-pill"><span class="dot gray"></span>Archived</span>
        </div>

        <div id="records-panel" class="reports-panel active">
            <div class="reports-summary-grid">
                <div class="reports-summary-card">
                    <div class="reports-summary-label">Total records</div>
                    <div class="reports-summary-value">4,812</div>
                </div>
                <div class="reports-summary-card">
                    <div class="reports-summary-label">Archived</div>
                    <div class="reports-summary-value">1,209</div>
                </div>
                <div class="reports-summary-card accent">
                    <div class="reports-summary-label">Added this month</div>
                    <div class="reports-summary-value">76</div>
                </div>
            </div>

            <div class="reports-list-panel">
                <table class="reports-list-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Date</th>
                            <th>Owner</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Q2 invoice batch</td>
                            <td>Jul 12</td>
                            <td>R. Cruz</td>
                            <td><span class="reports-status active">Active</span></td>
                        </tr>
                        <tr>
                            <td>Vendor contract 2025</td>
                            <td>May 03</td>
                            <td>L. Tan</td>
                            <td><span class="reports-status archived">Archived</span></td>
                        </tr>
                        <tr>
                            <td>Onboarding packet</td>
                            <td>Jul 20</td>
                            <td>M. Reyes</td>
                            <td><span class="reports-status pending">Pending</span></td>
                        </tr>
                        <tr>
                            <td>Audit report FY24</td>
                            <td>Jan 09</td>
                            <td>R. Cruz</td>
                            <td><span class="reports-status archived">Archived</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div id="archive-panel" class="reports-panel" style="display:none;">
            <div class="reports-summary-grid">
                <div class="reports-summary-card">
                    <div class="reports-summary-label">Archived files</div>
                    <div class="reports-summary-value">1,209</div>
                </div>
                <div class="reports-summary-card">
                    <div class="reports-summary-label">Pending archive</div>
                    <div class="reports-summary-value">42</div>
                </div>
                <div class="reports-summary-card accent">
                    <div class="reports-summary-label">Last archived</div>
                    <div class="reports-summary-value">Today</div>
                </div>
            </div>
            <div class="reports-list-panel">
                <table class="reports-list-table">
                    <thead>
                        <tr><th>Archive name</th><th>Stored</th><th>Owner</th><th>State</th></tr>
                    </thead>
                    <tbody>
                        <tr><td>Vendor contract 2025</td><td>May 03</td><td>L. Tan</td><td><span class="reports-status archived">Stored</span></td></tr>
                        <tr><td>Audit report FY24</td><td>Jan 09</td><td>R. Cruz</td><td><span class="reports-status archived">Stored</span></td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div id="reports-panel" class="reports-panel" style="display:none;">
            <div class="reports-summary-grid">
                <div class="reports-summary-card">
                    <div class="reports-summary-label">Generated reports</div>
                    <div class="reports-summary-value">18</div>
                </div>
                <div class="reports-summary-card">
                    <div class="reports-summary-label">Ready to export</div>
                    <div class="reports-summary-value">6</div>
                </div>
                <div class="reports-summary-card accent">
                    <div class="reports-summary-label">Last generated</div>
                    <div class="reports-summary-value">2h ago</div>
                </div>
            </div>
            <div class="reports-list-panel">
                <div class="reports-list-header">
                    <span class="reports-list-title">Submitted reports</span>
                    <div class="reports-export-actions">
                        <button type="button" class="reports-export-btn" onclick="openExportModal('pdf')">
                            <i class="bi bi-filetype-pdf"></i> PDF
                        </button>
                        <button type="button" class="reports-export-btn" onclick="openExportModal('excel')">
                            <i class="bi bi-file-earmark-excel"></i> Excel
                        </button>
                    </div>
                </div>
                <div class="reports-filter-row">
                    <div class="filter-menu-wrapper">
                        <button type="button" class="filter-btn" onclick="toggleReportsMonthFilter()" aria-label="Open month filter">
                            <i class="bi bi-funnel"></i>
                        </button>
                        <div class="filter-popup" id="reportsMonthFilterPopup">
                            <div class="filter-popup-title">Filter</div>
                            <div class="filter-row">
                                <label for="monthFilter">Month</label>
                                <select id="monthFilter" class="reports-filter-select">
                                    <option value="all">All months</option>
                                    <option value="jan">January</option>
                                    <option value="feb">February</option>
                                    <option value="mar">March</option>
                                    <option value="apr">April</option>
                                    <option value="may">May</option>
                                    <option value="jun">June</option>
                                    <option value="jul">July</option>
                                    <option value="aug">August</option>
                                    <option value="sep">September</option>
                                    <option value="oct">October</option>
                                    <option value="nov">November</option>
                                    <option value="dec">December</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <table class="reports-list-table">
                    <thead>
                        <tr><th>Report</th><th>Type</th><th>Owner</th><th>Status</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        <tr data-month="jul" data-report-name="Travel summary">
                            <td>Travel summary</td>
                            <td>Operations</td>
                            <td>Admin</td>
                            <td><span class="reports-status active">Ready</span></td>
                            <td>
                                <div class="reports-row-actions">
                                    <button type="button" class="reports-inline-btn review" onclick="openReviewModal('Travel summary', 'Travel summary', 'Travel summary report submitted by the user. The figures below can be edited before approval.', 'pending', this.closest('tr'))">Review</button>
                                </div>
                            </td>
                        </tr>
                        <tr data-month="may" data-report-name="Vehicle utilization">
                            <td>Vehicle utilization</td>
                            <td>Fleet</td>
                            <td>Admin</td>
                            <td><span class="reports-status pending">Review</span></td>
                            <td>
                                <div class="reports-row-actions">
                                    <button type="button" class="reports-inline-btn review" onclick="openReviewModal('Vehicle utilization', 'Vehicle utilization', 'Fleet utilization report submitted by the user. Review the summary values and confirm the latest month data.', 'pending', this.closest('tr'))">Review</button>
                                </div>
                            </td>
                        </tr>
                        <tr data-month="jan" data-report-name="Maintenance overview">
                            <td>Maintenance overview</td>
                            <td>Maintenance</td>
                            <td>Admin</td>
                            <td><span class="reports-status archived">Archived</span></td>
                            <td>
                                <div class="reports-row-actions">
                                    <button type="button" class="reports-inline-btn review" onclick="openReviewModal('Maintenance overview', 'Maintenance overview', 'Archived maintenance report submitted by the user. Review the details and decide whether it should be approved or kept pending.', 'archived', this.closest('tr'))">Review</button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
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
<div id="exportModal" class="modal-overlay" style="display:none;">
    <div class="modal-box modal-sm">
        <div class="modal-header">
            <h3><i class="bi bi-download"></i> Export report</h3>
            <button class="modal-close" type="button" onclick="closeExportModal()"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label>Choose report</label>
                <select id="exportReportType">
                    <option value="trips">Travel Operations</option>
                    <option value="vehicles">Vehicle Fleet</option>
                    <option value="assets">Asset Inventory</option>
                    <option value="personnel">Personnel</option>
                </select>
            </div>
            <input type="hidden" id="exportFormat" value="pdf">
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-cancel" onclick="closeExportModal()">Cancel</button>
            <button type="button" class="btn-submit" onclick="submitExportSelection()"><i class="bi bi-download"></i> Export</button>
        </div>
    </div>
</div>

<div id="reviewModal" class="modal-overlay" style="display:none;">
    <div class="modal-box modal-lg">
        <div class="modal-header">
            <h3 id="reviewModalTitle"><i class="bi bi-eye"></i> Report review</h3>
            <button class="modal-close" type="button" onclick="closeReviewModal()"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="modal-body">
            <div class="review-sheet">
                <div class="review-sheet-header">
                    <div>
                        <div class="review-sheet-label">Submitted report</div>
                        <div id="reviewModalSubtitle" class="review-sheet-title"></div>
                    </div>
                    <div id="reviewStatusBadge" class="review-status-badge pending">Pending review</div>
                </div>
                <div class="form-group">
                    <label>Report summary</label>
                    <textarea id="reviewSummaryInput" rows="4"></textarea>
                </div>
                <div class="review-grid">
                    <div class="form-group">
                        <label>Total submitted</label>
                        <input type="text" id="reviewTotalInput" value="0">
                    </div>
                    <div class="form-group">
                        <label>Approved by</label>
                        <input type="text" id="reviewApproverInput" value="Admin">
                    </div>
                </div>
                <div class="form-group">
                    <label>Admin note</label>
                    <textarea id="reviewNotesInput" rows="3">The submitted report is awaiting review and approval.</textarea>
                </div>
                <div class="review-alert" id="reviewAlertBox">
                    This report has not yet been reviewed and approved by the admin.
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-cancel" onclick="closeReviewModal()">Close</button>
            <button type="button" class="btn-submit" onclick="approveReportFromModal()"><i class="bi bi-check2-circle"></i> Approve</button>
        </div>
    </div>
</div>

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
function openExportModal(format) {
    document.getElementById('exportFormat').value = format || 'pdf';
    document.getElementById('exportModal').style.display = 'flex';
}
function closeExportModal() { document.getElementById('exportModal').style.display = 'none'; }
function submitExportSelection() {
    const type = document.getElementById('exportReportType').value;
    const format = document.getElementById('exportFormat').value;
    window.location.href = '<?= base_url('reports/export') ?>/' + type + '?format=' + format;
    closeExportModal();
}
let currentReviewRow = null;

function openReviewModal(title, subtitle, body, status, row) {
    currentReviewRow = row || null;
    document.getElementById('reviewModalTitle').innerHTML = '<i class="bi bi-eye"></i> ' + title;
    document.getElementById('reviewModalSubtitle').textContent = subtitle;
    document.getElementById('reviewSummaryInput').value = body;
    document.getElementById('reviewNotesInput').value = status === 'archived'
        ? 'This archived report has been reviewed and kept for reference.'
        : 'The submitted report is awaiting review and approval.';
    const badge = document.getElementById('reviewStatusBadge');
    const rowStatus = currentReviewRow ? currentReviewRow.querySelector('.reports-status') : null;
    const isApproved = rowStatus && rowStatus.textContent.trim() === 'Approved';
    badge.textContent = isApproved ? 'Approved' : (status === 'archived' ? 'Archived' : 'Pending review');
    badge.className = 'review-status-badge ' + (isApproved ? 'approved' : (status === 'archived' ? 'archived' : 'pending'));
    document.getElementById('reviewAlertBox').textContent = isApproved
        ? 'Approved by admin. The report is now ready for sharing.'
        : (status === 'archived'
            ? 'This report is archived and does not require admin approval.'
            : 'This report has not yet been reviewed and approved by the admin.');
    document.getElementById('reviewModal').style.display = 'flex';
}
function closeReviewModal() { document.getElementById('reviewModal').style.display = 'none'; }
function toggleReportsMonthFilter() {
    const popup = document.getElementById('reportsMonthFilterPopup');
    if (popup) {
        popup.classList.toggle('visible');
    }
}

function filterReportsByMonth() {
    const selectedMonth = document.getElementById('monthFilter').value;
    const rows = document.querySelectorAll('#reports-panel tbody tr[data-month]');

    rows.forEach(row => {
        const rowMonth = row.getAttribute('data-month') || '';
        row.style.display = (selectedMonth === 'all' || rowMonth === selectedMonth) ? '' : 'none';
    });
}

document.addEventListener('click', function (event) {
    const wrapper = document.querySelector('#reports-panel .filter-menu-wrapper');
    const popup = document.getElementById('reportsMonthFilterPopup');
    if (wrapper && popup && !wrapper.contains(event.target)) {
        popup.classList.remove('visible');
    }
});

document.getElementById('monthFilter').addEventListener('change', filterReportsByMonth);

function approveReportFromModal() {
    const badge = document.getElementById('reviewStatusBadge');
    badge.textContent = 'Approved';
    badge.className = 'review-status-badge approved';
    document.getElementById('reviewAlertBox').textContent = 'Approved by admin. The report is now ready for sharing.';

    if (currentReviewRow) {
        const statusEl = currentReviewRow.querySelector('.reports-status');
        if (statusEl) {
            statusEl.textContent = 'Approved';
            statusEl.className = 'reports-status approved';
        }
    }
}
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

document.querySelectorAll('.reports-tab').forEach(tab => {
    tab.addEventListener('click', () => {
        document.querySelectorAll('.reports-tab').forEach(btn => btn.classList.remove('active'));
        tab.classList.add('active');

        document.querySelectorAll('.reports-panel').forEach(panel => {
            panel.style.display = 'none';
            panel.classList.remove('active');
        });

        const target = document.getElementById(tab.dataset.target);
        if (target) {
            target.style.display = 'block';
            target.classList.add('active');
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    });
});

document.getElementById('monthFilter')?.addEventListener('change', function () {
    const month = this.value;
    document.querySelectorAll('#reports-panel tbody tr').forEach(row => {
        const rowMonth = row.getAttribute('data-month') || 'all';
        row.style.display = (month === 'all' || rowMonth === month) ? '' : 'none';
    });
});

// Initialize visibility on page load
document.addEventListener('DOMContentLoaded', () => {
    const firstPanel = document.getElementById('records-panel');
    if (firstPanel) {
        firstPanel.style.display = 'block';
        firstPanel.classList.add('active');
    }
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