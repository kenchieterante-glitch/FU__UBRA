<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php
  $settings = $settings ?? [];
  $users = $users ?? [];
  $logs = $logs ?? [];
  $sys_info = $sys_info ?? [];
  $canManageArchive = $canManageArchive ?? false;
  $retentionByDept = $retentionByDept ?? [];
  $expiredByDept = $expiredByDept ?? [];
?>
<link rel="stylesheet" href="<?= base_url('Assets/css/settings.css') . '?v=' . @filemtime(FCPATH.'Assets/css/settings.css') ?>">

<div class="stg-wrapper">

    <!-- ── PAGE HEADER ──────────────────────────────────────────── -->
    <header class="stg-hero">
        <div class="stg-hero-text">
            <h1 class="stg-hero-title">Settings</h1>
            <p class="stg-hero-sub">Manage how UBRA looks, who can use it, and what it connects to.</p>
        </div>
        <div class="stg-status" role="status">
            <span class="stg-pill ok"><span class="dot"></span> All systems operational</span>
            <span class="stg-pill"><i class="bi bi-hdd-stack"></i> Server <strong><?= esc($sys_info['server'] ?? '—') ?></strong></span>
            <span class="stg-pill"><i class="bi bi-database"></i> Database <strong><?= esc($sys_info['database'] ?? '—') ?></strong></span>
            <span class="stg-pill"><i class="bi bi-tag"></i> <strong>v<?= esc(ltrim((string) ($sys_info['version'] ?? '—'), 'v')) ?></strong></span>
        </div>
    </header>

    <!-- ── LAYOUT: NAV + CONTENT + SIDEBAR ──────────────────────── -->
    <div class="stg-body">

        <!-- Left nav -->
        <nav class="stg-nav" aria-label="Settings sections">
            <div class="stg-search">
                <i class="bi bi-search"></i>
                <input type="search" id="stgSearch" placeholder="Search settings" autocomplete="off" aria-label="Search settings">
            </div>
            <?php
            $navGroups = [
                'Preferences'    => [['general', 'bi-sliders', 'General']],
                'Access'         => [['users', 'bi-people-fill', 'Users'], ['roles', 'bi-shield-lock-fill', 'Roles'], ['permissions', 'bi-key-fill', 'Permissions']],
                'Communication'  => [['email', 'bi-envelope-fill', 'Email'], ['notifications', 'bi-bell-fill', 'Notifications']],
                'Integrations'   => [['google', 'bi-calendar3', 'Google Calendar'], ['gps', 'bi-broadcast', 'GPS API'], ['ai', 'bi-robot', 'AI Configuration']],
                'Security & Data'=> [['security', 'bi-shield-fill-check', 'Security'], ['audit', 'bi-journal-text', 'Audit Logs'], ['archive', 'bi-archive-fill', 'Archive & Retention']],
            ];
            foreach ($navGroups as $groupName => $items):
                $visible = array_filter($items, fn($i) => !($i[0] === 'archive' && !$canManageArchive));
                if (!$visible) continue;
            ?>
            <div class="stg-nav-group" data-group>
                <div class="stg-nav-label"><?= esc($groupName) ?></div>
                <?php foreach ($visible as [$id, $icon, $label]): ?>
                <button type="button" class="stg-nav-btn" data-tab="<?= $id ?>" data-label="<?= esc($label) ?>" data-icon="<?= $icon ?>" onclick="switchTab('<?= $id ?>')">
                    <span class="stg-nav-ico"><i class="bi <?= $icon ?>"></i></span>
                    <span><?= esc($label) ?></span>
                </button>
                <?php endforeach; ?>
            </div>
            <?php endforeach; ?>
            <div class="stg-nav-empty" id="stgNavEmpty" style="display:none">No settings match your search.</div>
        </nav>

        <!-- Tab content -->
        <div class="stg-content">
            <div class="stg-content-head">
                <span class="stg-content-ico"><i class="bi bi-sliders" id="stgHeadIcon"></i></span>
                <div>
                    <h2 class="stg-content-title" id="stgHeadTitle">General</h2>
                    <p class="stg-content-desc" id="stgHeadDesc"></p>
                </div>
            </div>

            <!-- ── GENERAL ────────────────────────────────────────── -->
            <div id="tab-general" class="tab-pane active">
                <div class="tab-title">General Configuration</div>

                <div class="toggle-list" style="margin-bottom:1.4rem;">
                    <div class="toggle-row">
                        <div class="toggle-info">
                            <div class="toggle-label"><i class="bi bi-moon-stars-fill"></i> Dark Mode</div>
                            <div class="toggle-sub">Switch the whole system to a dark color scheme. Saved on this device only.</div>
                        </div>
                        <label class="switch">
                            <input type="checkbox" data-theme-toggle>
                            <span class="slider"></span>
                        </label>
                    </div>
                    <div class="toggle-row">
                        <div class="toggle-info">
                            <div class="toggle-label"><i class="bi bi-volume-up-fill"></i> Voice Reminders</div>
                            <div class="toggle-sub">Reads urgent (critical) alerts aloud and repeats every 30 minutes until they are dealt with. Works on the Notifications page only. Saved on this device only.</div>
                        </div>
                        <label class="switch">
                            <input type="checkbox" id="voiceSetting">
                            <span class="slider"></span>
                        </label>
                    </div>
                </div>

                <form method="post" action="<?= base_url('settings/saveGeneral') ?>">
                    <?= csrf_field() ?>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>System Name</label>
                            <input type="text" name="system_name"
                                value="<?= esc($settings['system_name'] ?? '') ?>"
                                placeholder="UBRA Operational Portal">
                        </div>
                        <div class="form-group">
                            <label>University Name</label>
                            <input type="text" name="university"
                                value="<?= esc($settings['university'] ?? '') ?>"
                                placeholder="Foundation University">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Google Calendar API Key</label>
                        <input type="text" name="api_key"
                            value="<?= esc($settings['api_key'] ?? '') ?>"
                            placeholder="Enter your Google Calendar API key">
                        <div class="field-hint">Used for the Google Calendar sync. Mr. UBRA's key is set separately under AI Configuration — GPS doesn't need one, devices POST directly to this system.</div>
                    </div>
                    <button type="submit" class="btn-save">
                        Save Changes
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
                                        <span class="user-avatar"><?= strtoupper(substr((string) (($u['email'] ?? '') !== '' ? $u['email'] : ($u['emp_id'] ?? '?')), 0, 1)) ?></span>
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
                                                <i class="bi bi-trash3-fill"></i>
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
                    ['name'=>'Motorpool Driver','access'=>'Vehicle assignments, pre-trip inspection checklist','color'=>'role-driver'],
                    ['name'=>'Janitorial Staff','access'=>'Daily task checklists, work evaluation view','color'=>'role-jan'],
                    ['name'=>'Borrower (Faculty/Staff)','access'=>'Asset borrowing, borrow history','color'=>'role-borrower'],
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
                <div class="tab-title">Category Permissions</div>
                <p class="tab-desc">Which roles can access each module. Read-only reference — enforced at controller level.</p>
                <?php
                $perms = [
                    ['module'=>'Dashboard',       'roles'=>['Admin','B&G Head','All Roles']],
                    ['module'=>'Asset Management','roles'=>['Admin','B&G Head','Borrower']],
                    ['module'=>'Vehicle Mgmt',    'roles'=>['Admin','B&G Head','Driver']],
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
                            <input type="text" name="smtp_name" value="<?= esc($settings['smtp_name'] ?? '') ?>" placeholder="UBRA System">
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
                        <div class="int-desc">Maintenance events and cleaning cycles automatically sync to the shared B&G Google Calendar. Events update in real time when schedules change.</div>
                    </div>
                </div>
                <form method="post" action="<?= base_url('settings/saveGeneral') ?>">
                    <?= csrf_field() ?>
                    <div class="form-group" style="max-width:480px;margin-top:1.2rem;">
                        <label>Google Calendar API Key / OAuth Client ID</label>
                        <input type="text" name="api_key" value="<?= esc($settings['api_key'] ?? '') ?>" placeholder="Enter your Google Calendar API key">
                        <div class="field-hint">Set up at <strong>console.cloud.google.com</strong> → APIs & Services → Credentials.</div>
                    </div>
                    <button type="submit" class="btn-save">
                        Save Changes
                    </button>
                </form>
                <div class="int-note">
                    <i class="bi bi-info-circle"></i>
                    Confirmed with B&G staff that Google Calendar is already used for scheduling. UBRA builds on this habit by automatically syncing all events.
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
                    GPS devices must be fitted on official vehicles and configured to POST to the endpoint above. Signal data is stored in <code>gps_logs</code> and displayed on the GPS Tracker page. No API key is needed here — devices push directly to this system rather than this system calling out to one.
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
                <form method="post" action="<?= base_url('settings/saveAI') ?>">
                    <?= csrf_field() ?>
                    <div class="form-group" style="max-width:480px;margin-top:1.2rem;">
                        <label>AI API Key</label>
                        <input type="text" name="ai_api_key" value="<?= esc($settings['ai_api_key'] ?? '') ?>" placeholder="sk-ant-... or gsk_... or sk-or-... or AIzaSy...">
                        <div class="field-hint">Powers Mr. UBRA's responses. Provider is auto-detected from the key: <strong>Anthropic</strong> (console.anthropic.com, starts with <code>sk-ant-</code>, paid), <strong>Groq</strong> (console.groq.com, starts with <code>gsk_</code>, free tier), <strong>OpenRouter</strong> (openrouter.ai/keys, starts with <code>sk-or-</code>, has free models), or <strong>Gemini</strong> (aistudio.google.com/apikey, starts with <code>AIzaSy</code>, free tier). Kept separate from the Google Calendar key on the General tab.</div>
                    </div>
                    <button type="submit" class="btn-save">
                        Save Changes
                    </button>
                </form>
                <div class="int-note" style="margin-top:1rem;">
                    <i class="bi bi-robot"></i>
                    Mr. UBRA supports Anthropic Claude, Groq, OpenRouter, or Gemini. The AI sidebar panel appears on Dashboard, Personnel, Calendar, Notifications, Reports, GPS Tracker, and Vehicle Management.
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
                        ['icon'=>'bi-pencil-fill',     'label'=>'Append-Only Records','value'=>'approved records are locked', 'status'=>'active'],
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

            <!-- ── ARCHIVE & RETENTION ────────────────────────────── -->
            <?php if ($canManageArchive): ?>
            <div id="tab-archive" class="tab-pane">
                <div class="tab-title">Archive &amp; Retention</div>
                <p style="margin:0 0 14px;color:var(--text);font-size:13px;max-width:560px;">Archived records are kept for the number of months set by each department. After that they can be permanently deleted (archive first, then delete).</p>
                <form method="post" action="<?= base_url('records/retention') ?>" id="retForm" style="max-width:560px;">
                    <?= csrf_field() ?>
                    <?php foreach ($retentionByDept as $dept => $months): $slug2 = \App\Libraries\DepartmentScope::CATEGORIES[$dept]['slug']; $due = (int) ($expiredByDept[$dept] ?? 0); ?>
                    <div style="display:flex;align-items:center;gap:12px;padding:10px 0;border-bottom:1px solid rgba(0,0,0,.08);">
                        <label for="ret_<?= esc($slug2) ?>" style="flex:1;font-weight:600;margin:0;"><?= esc($dept) ?></label>
                        <input type="number" id="ret_<?= esc($slug2) ?>" name="months[<?= esc($slug2) ?>]" min="1" max="120" value="<?= (int) $months ?>" style="width:76px;padding:6px 8px;border:1px solid rgba(0,0,0,.2);border-radius:8px;text-align:center;font-weight:600;">
                        <span style="font-size:13px;color:var(--text);width:62px;">month(s)</span>
                        <span style="font-size:11px;font-weight:600;padding:3px 10px;border-radius:999px;white-space:nowrap;<?= $due > 0 ? 'background:#fdecea;color:#c62828;' : 'background:#e8f5e9;color:#2e7d32;' ?>"><?= $due > 0 ? $due . ' ready to delete' : 'Nothing expired' ?></span>
                    </div>
                    <?php endforeach; ?>
                </form>
                <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;margin-top:16px;">
                    <button type="submit" form="retForm" class="btn-save"><i class="bi bi-check2-circle"></i> Save retention</button>
                    <form method="post" action="<?= base_url('records/purge-archive') ?>" style="margin:0;" onsubmit="return confirm('Permanently delete every archived record that is past its department retention period? This cannot be undone.');">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn-save" style="background:#2b2b33;"><i class="bi bi-trash3-fill"></i> Delete expired archives</button>
                    </form>
                </div>
                <p style="margin:10px 0 0;font-size:12px;color:var(--text);">Deleting cannot be undone. Archive records from the Reports page first.</p>
            </div>
            <?php endif; ?>

            <!-- ── AUDIT LOGS ──────────────────────────────────────── -->
            <div id="tab-audit" class="tab-pane">
                <div class="tab-title">Audit Log</div>
                <div class="table-scroll">
                    <table class="stg-table">
                        <thead>
                            <tr><th>Category</th><th>Activity</th><th>Date &amp; Time</th></tr>
                        </thead>
                        <tbody>
                            <?php if (empty($logs)): ?>
                                <tr><td colspan="3" class="empty-row">No activity logged yet.</td></tr>
                            <?php else: ?>
                                <?php foreach ($logs as $log): ?>
                                <tr>
                                    <td><span class="log-action"><?= esc($log['module'] ?? '—') ?></span></td>
                                    <td class="log-desc"><?= esc($log['action'] ?? '—') ?></td>
                                    <td>
                                        <?php if (!empty($log['logged_at'])): ?>
                                            <?= date('M j, Y', strtotime($log['logged_at'])) ?>
                                            <span class="time-muted"><?= date('h:i A', strtotime($log['logged_at'])) ?></span>
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
                        <option value="Security">Security Head</option>
                        <option value="Tools">Tools Head</option>
                        <option value="Facilities">Facilities Supervisor</option>
                        <option value="Janitorial">Janitorial Supervisor</option>
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
// Voice reminders switch (read by the Notifications page)
(function () {
    const box = document.getElementById('voiceSetting');
    if (!box) return;
    try { box.checked = localStorage.getItem('fu_voice') === '1'; } catch (e) {}
    box.addEventListener('change', () => {
        try { localStorage.setItem('fu_voice', box.checked ? '1' : '0'); } catch (e) {}
        if ('speechSynthesis' in window) {
            speechSynthesis.cancel();
            if (box.checked) speechSynthesis.speak(new SpeechSynthesisUtterance('Voice reminders are on.'));
        }
    });
})();
// ── Tab switching ──────────────────────────────────────────────
const STG_META = {
    general:       ['General', 'Appearance, voice reminders and the names shown across the system.'],
    users:         ['Users', 'Everyone who can sign in, and the role each person has.'],
    roles:         ['Roles', 'System roles and their access levels. Assign roles to users in the Users section.'],
    permissions:   ['Permissions', 'Which roles can open each module. Read-only reference; enforced by the system.'],
    email:         ['Email', 'Outgoing mail through Gmail SMTP (PHPMailer).'],
    notifications: ['Notifications', 'Choose which alerts are sent and how.'],
    google:        ['Google Calendar', 'Sync maintenance events and cleaning cycles to the shared calendar.'],
    gps:           ['GPS API', 'Connection settings for the vehicle trackers.'],
    ai:            ['AI Configuration', 'Settings for Mr. UBRA, the operations assistant.'],
    security:      ['Security', 'How accounts, sessions and records are protected.'],
    audit:         ['Audit Logs', 'A trail of what happened in the system and when.'],
    archive:       ['Archive & Retention', 'How long archived records are kept before they can be cleared.'],
};
function switchTab(id) {
    document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.stg-nav-btn').forEach(b => b.classList.remove('active'));
    const pane = document.getElementById('tab-' + id);
    if (pane) pane.classList.add('active');
    const btn = document.querySelector(`.stg-nav-btn[data-tab="${id}"]`);
    if (btn) {
        btn.classList.add('active');
        document.getElementById('stgHeadIcon').className = 'bi ' + btn.dataset.icon;
    }
    const m = STG_META[id] || [btn ? btn.dataset.label : '', ''];
    document.getElementById('stgHeadTitle').textContent = m[0];
    document.getElementById('stgHeadDesc').textContent = m[1];
    history.replaceState(null, '', '#' + id);
}

// ── Search the settings list ─────────────────────────────────────
document.getElementById('stgSearch')?.addEventListener('input', function () {
    const q = this.value.trim().toLowerCase();
    let shown = 0;
    document.querySelectorAll('.stg-nav-group').forEach(g => {
        let any = 0;
        g.querySelectorAll('.stg-nav-btn').forEach(b => {
            const hit = !q || b.dataset.label.toLowerCase().includes(q) || (STG_META[b.dataset.tab] || ['', ''])[1].toLowerCase().includes(q);
            b.style.display = hit ? '' : 'none';
            if (hit) any++;
        });
        g.style.display = any ? '' : 'none';
        shown += any;
    });
    document.getElementById('stgNavEmpty').style.display = shown ? 'none' : '';
});

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