<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<link rel="stylesheet" href="<?= base_url('Assets/css/settings.css') ?>">

<div class="stg-wrapper">

    <!-- ── PAGE HEADER ──────────────────────────────────────────── -->
    <div class="page-header">
        <div>
            <h1 class="page-title"><i class="bi bi-gear-fill"></i> System Settings</h1>
            <p class="page-subtitle">Manage application preferences and integrations.</p>
        </div>
    </div>

    <!-- ── LAYOUT: NAV + CONTENT + SIDEBAR ──────────────────────── -->
    <div class="stg-body">

        <!-- Left nav -->
        <nav class="stg-nav">
            <?php
            $navItems = [
                ['id'=>'general',       'icon'=>'bi-sliders',          'label'=>'General'],
                ['id'=>'users',         'icon'=>'bi-people-fill',      'label'=>'Users'],
                ['id'=>'roles',         'icon'=>'bi-shield-lock-fill', 'label'=>'Roles'],
                ['id'=>'permissions',   'icon'=>'bi-key-fill',         'label'=>'Permissions'],
                ['id'=>'email',         'icon'=>'bi-envelope-fill',    'label'=>'Email Settings'],
                ['id'=>'notifications', 'icon'=>'bi-bell-fill',        'label'=>'Notification Settings'],
                ['id'=>'google',        'icon'=>'bi-calendar3',        'label'=>'Google Calendar'],
                ['id'=>'gps',           'icon'=>'bi-broadcast',        'label'=>'GPS API'],
                ['id'=>'ai',            'icon'=>'bi-robot',            'label'=>'AI Configuration'],
                ['id'=>'security',      'icon'=>'bi-shield-fill-check','label'=>'Security'],
                ['id'=>'audit',         'icon'=>'bi-journal-text',     'label'=>'Audit Logs'],
            ];
            foreach ($navItems as $item):
            ?>
            <button class="stg-nav-btn" data-tab="<?= $item['id'] ?>" onclick="switchTab('<?= $item['id'] ?>')">
                <i class="bi <?= $item['icon'] ?>"></i>
                <?= $item['label'] ?>
            </button>
            <?php endforeach; ?>
        </nav>

        <!-- Tab content -->
        <div class="stg-content">

            <!-- ── GENERAL ────────────────────────────────────────── -->
            <div id="tab-general" class="tab-pane active">
                <div class="tab-title">General Configuration</div>
                <form method="post" action="<?= base_url('settings/saveGeneral') ?>">
                    <?= csrf_field() ?>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>System Name</label>
                            <input type="text" name="system_name"
                                value="<?= esc($settings['system_name'] ?? '') ?>"
                                placeholder="FU-UBRA Operational Portal">
                        </div>
                        <div class="form-group">
                            <label>University Name</label>
                            <input type="text" name="university"
                                value="<?= esc($settings['university'] ?? '') ?>"
                                placeholder="Foundation University">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>API Integration Keys</label>
                        <input type="text" name="api_key"
                            value="<?= esc($settings['api_key'] ?? '') ?>"
                            placeholder="Google Calendar API / GPS API / OpenAI Key">
                        <div class="field-hint">Used by Google Calendar, GPS integration, and Mr. UBRA AI.</div>
                    </div>
                    <button type="submit" class="btn-save">
                        <i class="bi bi-floppy-fill"></i> Save Changes
                    </button>
                </form>
            </div>

            <!-- ── USERS ───────────────────────────────────────────── -->
            <div id="tab-users" class="tab-pane">
                <div class="tab-title-row">
                    <div class="tab-title">User Management</div>
                    <button class="btn-add-user" onclick="openAddUserModal()">
                        <i class="bi bi-person-plus-fill"></i> Add User
                    </button>
                </div>
                <table class="stg-table">
                    <thead>
                        <tr><th>Employee ID</th><th>Email</th><th>Role</th><th>Created</th><th>Action</th></tr>
                    </thead>
                    <tbody>
                        <?php if (empty($users)): ?>
                            <tr><td colspan="5" class="empty-row">No users found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($users as $u): ?>
                            <tr>
                                <td>
                                    <div class="user-cell">
                                        <span class="user-avatar"><?= strtoupper(substr((string)($u['emp_id'] ?? ''), 0, 1)) ?></span>
                                        <strong><?= esc($u['emp_id']) ?></strong>
                                    </div>
                                </td>
                                <td><?= esc($u['email'] ?? '—') ?></td>
                                <td><span class="role-badge"><?= esc($u['role'] ?? 'Staff') ?></span></td>
                                <td><?= isset($u['created_at']) ? date('M j, Y', strtotime($u['created_at'])) : '—' ?></td>
                                <td>
                                    <?php $userId = $u['department_id'] ?? null; ?>
                                    <?php if ($userId): ?>
                                        <form method="post"
                                            action="<?= base_url('settings/deleteUser/' . $userId) ?>"
                                            onsubmit="return confirm('Delete user <?= esc($u['emp_id'] ?? $u['username']) ?>?')">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn-delete"
                                                <?= $userId == session()->get('user_id') ? 'disabled title="Cannot delete yourself"' : '' ?>>
                                                <i class="bi bi-trash3"></i>
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <span class="time-muted">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- ── ROLES ───────────────────────────────────────────── -->
            <div id="tab-roles" class="tab-pane">
                <div class="tab-title">Role Management</div>
                <p class="tab-desc">System roles and their access levels. Assign roles to users in the Users tab.</p>
                <?php
                $roles = [
                    ['name'=>'System Administrator','access'=>'Full system access — all modules, settings, user management','color'=>'role-admin'],
                    ['name'=>'B&G Head / Facilities Head','access'=>'Dashboard, all modules, reports, approvals, digital signatures','color'=>'role-head'],
                    ['name'=>'Maintenance Technician','access'=>'Maintenance module, asset inspection, task completion logging','color'=>'role-tech'],
                    ['name'=>'Motorpool Driver','access'=>'Trip schedule, pre-trip inspection checklist, trip logbook','color'=>'role-driver'],
                    ['name'=>'Janitorial Staff','access'=>'Daily task checklists, work evaluation view','color'=>'role-jan'],
                    ['name'=>'Borrower (Faculty/Staff)','access'=>'Asset borrowing, trip requests, borrow history','color'=>'role-borrower'],
                ];
                foreach ($roles as $r):
                ?>
                <div class="role-card">
                    <div class="role-name-wrap">
                        <span class="role-dot <?= $r['color'] ?>"></span>
                        <strong><?= $r['name'] ?></strong>
                    </div>
                    <div class="role-access"><?= $r['access'] ?></div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- ── PERMISSIONS ─────────────────────────────────────── -->
            <div id="tab-permissions" class="tab-pane">
                <div class="tab-title">Module Permissions</div>
                <p class="tab-desc">Which roles can access each module. Read-only reference — enforced at controller level.</p>
                <?php
                $perms = [
                    ['module'=>'Dashboard',       'roles'=>['Admin','B&G Head','All Roles']],
                    ['module'=>'Asset Management','roles'=>['Admin','B&G Head','Borrower']],
                    ['module'=>'Vehicle Mgmt',    'roles'=>['Admin','B&G Head','Driver']],
                    ['module'=>'Travel Mgmt',     'roles'=>['Admin','B&G Head','Borrower','Driver']],
                    ['module'=>'GPS Tracker',     'roles'=>['Admin','B&G Head']],
                    ['module'=>'Maintenance',     'roles'=>['Admin','B&G Head','Technician']],
                    ['module'=>'Janitorial',      'roles'=>['Admin','B&G Head','Janitorial Staff']],
                    ['module'=>'Notifications',   'roles'=>['Admin','B&G Head','All Roles']],
                    ['module'=>'Reports',         'roles'=>['Admin','B&G Head']],
                    ['module'=>'Settings',        'roles'=>['Admin']],
                ];
                foreach ($perms as $p):
                ?>
                <div class="perm-row">
                    <div class="perm-module"><?= $p['module'] ?></div>
                    <div class="perm-roles">
                        <?php foreach ($p['roles'] as $r): ?>
                            <span class="perm-chip"><?= $r ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- ── EMAIL SETTINGS ──────────────────────────────────── -->
            <div id="tab-email" class="tab-pane">
                <div class="tab-title">Email Settings (PHPMailer + Gmail SMTP)</div>
                <form method="post" action="<?= base_url('settings/saveEmail') ?>">
                    <?= csrf_field() ?>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>SMTP Host</label>
                            <input type="text" name="smtp_host" value="<?= esc($settings['smtp_host'] ?? '') ?>" placeholder="smtp.gmail.com">
                        </div>
                        <div class="form-group">
                            <label>SMTP Port</label>
                            <input type="number" name="smtp_port" value="<?= esc($settings['smtp_port'] ?? '') ?>" placeholder="587">
                        </div>
                    </div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Gmail Address (SMTP Username)</label>
                            <input type="email" name="smtp_user" value="<?= esc($settings['smtp_user'] ?? '') ?>" placeholder="youremail@gmail.com">
                        </div>
                        <div class="form-group">
                            <label>App Password</label>
                            <input type="password" name="smtp_pass" placeholder="Leave blank to keep current">
                            <div class="field-hint">Use a Gmail App Password, not your regular Gmail password.</div>
                        </div>
                    </div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>From Email</label>
                            <input type="email" name="smtp_from" value="<?= esc($settings['smtp_from'] ?? '') ?>" placeholder="noreply@foundation.edu.ph">
                        </div>
                        <div class="form-group">
                            <label>From Name</label>
                            <input type="text" name="smtp_name" value="<?= esc($settings['smtp_name'] ?? '') ?>" placeholder="FU-UBRA System">
                        </div>
                    </div>
                    <button type="submit" class="btn-save">
                        <i class="bi bi-floppy-fill"></i> Save Email Settings
                    </button>
                </form>
            </div>

            <!-- ── NOTIFICATION SETTINGS ───────────────────────────── -->
            <div id="tab-notifications" class="tab-pane">
                <div class="tab-title">Notification Settings</div>
                <form method="post" action="<?= base_url('settings/saveNotifications') ?>">
                    <?= csrf_field() ?>
                    <div class="toggle-list">
                        <?php
                        $toggles = [
                            ['key'=>'notif_maintenance','label'=>'Maintenance Reminders',    'sub'=>'Email alerts before maintenance tasks are due'],
                            ['key'=>'notif_vehicle',    'label'=>'Vehicle Alerts',           'sub'=>'Pre-trip inspection reminders and overdue alerts'],
                            ['key'=>'notif_janitorial', 'label'=>'Janitorial Notifications', 'sub'=>'Daily checklist reminders for janitorial staff'],
                            ['key'=>'notif_asset',      'label'=>'Asset Overdue Alerts',     'sub'=>'Notify admin when borrowed assets are not returned'],
                            ['key'=>'notif_travel',     'label'=>'Travel Reminders',         'sub'=>'Driver assignment alerts and trip departure reminders'],
                        ];
                        foreach ($toggles as $t):
                            $on = ($settings[$t['key']] ?? '1') === '1';
                        ?>
                        <div class="toggle-row">
                            <div class="toggle-info">
                                <div class="toggle-label"><?= $t['label'] ?></div>
                                <div class="toggle-sub"><?= $t['sub'] ?></div>
                            </div>
                            <label class="switch">
                                <input type="checkbox" name="<?= $t['key'] ?>" value="1" <?= $on ? 'checked' : '' ?>>
                                <span class="slider"></span>
                            </label>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="form-group" style="margin-top:1.2rem;max-width:260px;">
                        <label>Reminder Lead Days</label>
                        <input type="number" name="reminder_days" value="<?= esc($settings['reminder_days'] ?? '') ?>" min="1" max="30" placeholder="5">
                        <div class="field-hint">How many days before due date to send reminders.</div>
                    </div>
                    <button type="submit" class="btn-save">
                        <i class="bi bi-floppy-fill"></i> Save Notification Settings
                    </button>
                </form>
            </div>

            <!-- ── GOOGLE CALENDAR ─────────────────────────────────── -->
            <div id="tab-google" class="tab-pane">
                <div class="tab-title">Google Calendar Integration</div>
                <div class="integration-card">
                    <div class="int-icon"><i class="bi bi-calendar3" style="color:var(--info)"></i></div>
                    <div class="int-body">
                        <div class="int-name">Google Calendar API</div>
                        <div class="int-status connected"><i class="bi bi-circle-fill"></i> Connected</div>
                        <div class="int-desc">Maintenance events, vehicle trips, and cleaning cycles automatically sync to the shared B&G Google Calendar. Events update in real time when schedules change.</div>
                    </div>
                </div>
                <div class="form-group" style="max-width:480px;margin-top:1.2rem;">
                    <label>Google Calendar API Key / OAuth Client ID</label>
                    <input type="text" name="api_key" value="<?= esc($settings['api_key'] ?? '') ?>" placeholder="Enter your Google Calendar API key">
                    <div class="field-hint">Set up at <strong>console.cloud.google.com</strong> → APIs & Services → Credentials.</div>
                </div>
                <div class="int-note">
                    <i class="bi bi-info-circle"></i>
                    Confirmed with B&G staff that Google Calendar is already used for scheduling. GroundWorks builds on this habit by automatically syncing all events.
                </div>
            </div>

            <!-- ── GPS API ─────────────────────────────────────────── -->
            <div id="tab-gps" class="tab-pane">
                <div class="tab-title">GPS API Configuration</div>
                <div class="integration-card">
                    <div class="int-icon"><i class="bi bi-broadcast" style="color:var(--success)"></i></div>
                    <div class="int-body">
                        <div class="int-name">GPS Connection</div>
                        <div class="int-status connected"><i class="bi bi-circle-fill"></i> Connected</div>
                        <div class="int-desc">Real-time vehicle GPS tracking. Devices POST to <code><?= base_url('gps/logPing') ?></code> on each ping.</div>
                    </div>
                </div>
                <div class="int-note" style="margin-top:1rem;">
                    <i class="bi bi-info-circle"></i>
                    GPS devices must be fitted on official vehicles and configured to POST to the endpoint above. Signal data is stored in <code>gps_logs</code> and displayed on the GPS Tracker page.
                </div>
                <div class="code-block">
                    <div class="code-label">GPS Device POST Endpoint</div>
                    <code><?= base_url('gps/logPing') ?></code><br>
                    <code>POST fields: vehicle_id, latitude, longitude, speed, signal, last_location, device_id</code>
                </div>
            </div>

            <!-- ── AI CONFIGURATION ────────────────────────────────── -->
            <div id="tab-ai" class="tab-pane">
                <div class="tab-title">AI Configuration (Mr. UBRA)</div>
                <div class="integration-card">
                    <div class="int-icon ubra-ai-icon">U</div>
                    <div class="int-body">
                        <div class="int-name">Mr. UBRA — Intelligent Operations Assistant</div>
                        <div class="int-status connected"><i class="bi bi-circle-fill"></i> Active</div>
                        <div class="int-desc">Mr. UBRA provides AI-powered operational insights, smart scheduling suggestions, dispatch recommendations, and automated report summaries across all modules.</div>
                    </div>
                </div>
                <div class="int-note" style="margin-top:1rem;">
                    <i class="bi bi-robot"></i>
                    Mr. UBRA is powered by the Anthropic Claude API. Enter your API key in General → API Integration Keys. The AI sidebar panel appears on Dashboard, Personnel, Travel, Calendar, Notifications, Reports, GPS Tracker, and Vehicle Management.
                </div>
            </div>

            <!-- ── SECURITY ────────────────────────────────────────── -->
            <div id="tab-security" class="tab-pane">
                <div class="tab-title">Security Configuration</div>
                <div class="security-grid">
                    <?php
                    $secItems = [
                        ['icon'=>'bi-lock-fill',        'label'=>'Password Hashing',    'value'=>'bcrypt via password_hash()',   'status'=>'active'],
                        ['icon'=>'bi-shield-fill-check', 'label'=>'Session Security',   'value'=>'30-minute timeout, IP-bound', 'status'=>'active'],
                        ['icon'=>'bi-database-fill-lock','label'=>'SQL Injection',      'value'=>'PDO prepared statements',     'status'=>'active'],
                        ['icon'=>'bi-filetype-html',     'label'=>'XSS Prevention',     'value'=>'server-side input sanitization','status'=>'active'],
                        ['icon'=>'bi-person-lock',       'label'=>'Role-Based Access',  'value'=>'controller-level enforcement','status'=>'active'],
                        ['icon'=>'bi-pencil-square',     'label'=>'Append-Only Records','value'=>'approved records are locked', 'status'=>'active'],
                        ['icon'=>'bi-pen-fill',          'label'=>'Digital Signatures', 'value'=>'Canvas + hash + timestamp',   'status'=>'active'],
                        ['icon'=>'bi-journal-check',     'label'=>'Activity Logging',   'value'=>'user + IP + timestamp',       'status'=>'active'],
                        ['icon'=>'bi-image',             'label'=>'Media Upload Validation','value'=>'MIME-type checked server-side','status'=>'active'],
                        ['icon'=>'bi-lock-fill',         'label'=>'HTTPS / TLS',        'value'=>'All traffic encrypted',       'status'=>'active'],
                    ];
                    foreach ($secItems as $s):
                    ?>
                    <div class="sec-item">
                        <i class="bi <?= $s['icon'] ?> sec-icon"></i>
                        <div class="sec-body">
                            <div class="sec-label"><?= $s['label'] ?></div>
                            <div class="sec-value"><?= $s['value'] ?></div>
                        </div>
                        <span class="sec-status <?= $s['status'] ?>"><i class="bi bi-check-circle-fill"></i></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- ── AUDIT LOGS ──────────────────────────────────────── -->
            <div id="tab-audit" class="tab-pane">
                <div class="tab-title">Audit Log</div>
                <div class="table-scroll">
                    <table class="stg-table">
                        <thead>
                            <tr><th>User ID</th><th>Action</th><th>Description</th><th>IP Address</th><th>Date &amp; Time</th></tr>
                        </thead>
                        <tbody>
                            <?php if (empty($logs)): ?>
                                <tr><td colspan="5" class="empty-row">No activity logged yet.</td></tr>
                            <?php else: ?>
                                <?php foreach ($logs as $log): ?>
                                <tr>
                                    <td><?= esc($log['user_id'] ?? '—') ?></td>
                                    <td><span class="log-action"><?= esc($log['action'] ?? '—') ?></span></td>
                                    <td class="log-desc"><?= esc($log['description'] ?? $log['action'] ?? '—') ?></td>
                                    <td><code><?= esc($log['ip_address'] ?? '—') ?></code></td>
                                    <td>
                                        <?php if (!empty($log['created_at'])): ?>
                                            <?= date('M j, Y', strtotime($log['created_at'])) ?>
                                            <span class="time-muted"><?= date('h:i A', strtotime($log['created_at'])) ?></span>
                                        <?php else: ?>
                                            <span class="time-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div><!-- end stg-content -->

        <!-- Right: System Info -->
        <div class="stg-sysinfo">
            <div class="sysinfo-card">
                <div class="si-title">System Info</div>
                <div class="si-row"><span>Version</span><strong><?= esc($sys_info['version'] ?? '—') ?></strong></div>
                <div class="si-row"><span>Server</span><strong class="text-success"><?= esc($sys_info['server'] ?? '—') ?></strong></div>
                <div class="si-row"><span>Database</span><strong class="text-success"><?= esc($sys_info['database'] ?? '—') ?></strong></div>
                <div class="si-divider"></div>
                <div class="si-label">Connections</div>
                <div class="si-conn"><span class="conn-dot online"></span> GPS Connection</div>
                <div class="si-conn"><span class="conn-dot online"></span> Calendar Connection</div>
                <div class="si-conn"><span class="conn-dot online"></span> AI Connection</div>
            </div>

            <div class="sysinfo-card">
                <div class="si-title">Session</div>
                <a href="<?= site_url('logout') ?>" class="btn-save" style="display:inline-flex;align-items:center;justify-content:center;gap:.5rem;text-decoration:none;margin-top:.25rem;">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </a>
            </div>

            <div class="ubra-mini">
                <div class="ubra-header">
                    <span class="ubra-icon">U</span>
                    <div>
                        <div class="ubra-name">MR. UBRA Health</div>
                    </div>
                </div>
                <div class="ubra-health-msg">
                    <i class="bi bi-check-circle-fill text-success"></i>
                    Everything operational.<br>No critical issues detected.
                </div>
            </div>
        </div>

    </div><!-- end stg-body -->
</div>

<!-- ── ADD USER MODAL ──────────────────────────────────────────── -->
<div id="addUserModal" class="modal-overlay" style="display:none;">
    <div class="modal-box modal-sm">
        <div class="modal-header">
            <h3><i class="bi bi-person-plus-fill"></i> Add New User</h3>
            <button class="modal-close" onclick="closeAddUserModal()"><i class="bi bi-x-lg"></i></button>
        </div>
        <form method="post" action="<?= base_url('settings/addUser') ?>">
            <?= csrf_field() ?>
            <div class="modal-body">
                <div class="form-group">
                    <label>Full Name <span class="req">*</span></label>
                    <input type="text" name="full_name" placeholder="e.g. Juan Dela Cruz" required>
                </div>
                <div class="form-group">
                    <label>Employee ID <span class="req">*</span></label>
                    <input type="text" name="emp_id" placeholder="e.g. 12345" required>
                </div>
                <div class="form-group">
                    <label>Email <span class="req">*</span></label>
                    <input type="email" name="email" placeholder="jdoe@foundation.edu.ph" required>
                </div>
                <div class="form-group">
                    <label>Role</label>
                    <select name="role">
                        <option>System Administrator</option>
                        <option selected>Staff</option>
                        <option>B&G Head</option>
                        <option>Technician</option>
                        <option>Driver</option>
                        <option>Janitorial Staff</option>
                        <option>Borrower</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Password <span class="req">*</span></label>
                    <input type="password" name="password" placeholder="Minimum 8 characters" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeAddUserModal()">Cancel</button>
                <button type="submit" class="btn-save"><i class="bi bi-person-plus-fill"></i> Create User</button>
            </div>
        </form>
    </div>
</div>

<script>
// ── Tab switching ──────────────────────────────────────────────
function switchTab(id) {
    document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.stg-nav-btn').forEach(b => b.classList.remove('active'));
    const pane = document.getElementById('tab-' + id);
    if (pane) pane.classList.add('active');
    document.querySelector(`[data-tab="${id}"]`)?.classList.add('active');
    history.replaceState(null, '', '#' + id);
}

// Restore tab from URL hash
const hash = location.hash.replace('#','');
if (hash) switchTab(hash);
else       switchTab('general');

// ── Modal helpers ──────────────────────────────────────────────
function openAddUserModal()  { document.getElementById('addUserModal').style.display = 'flex'; }
function closeAddUserModal() { document.getElementById('addUserModal').style.display = 'none'; }

document.querySelectorAll('.modal-overlay').forEach(o => {
    o.addEventListener('click', e => { if (e.target === o) o.style.display = 'none'; });
});

setTimeout(() => {
    document.querySelectorAll('.flash').forEach(el => el.style.opacity = '0');
}, 4000);
</script>

<?= $this->endSection() ?>