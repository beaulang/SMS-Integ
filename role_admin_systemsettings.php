<?php
// Included inside dashboard.php
$settingsFile = "settings.json";
$settings = ['university_name' => 'UNIVERSITY PORTAL', 'tagline' => 'Knowledge • Integrity • Excellence', 'otp_validity_minutes' => 5, 'max_login_attempts' => 5];
if (file_exists($settingsFile)) {
    $s = json_decode(file_get_contents($settingsFile), true);
    if (json_last_error() === JSON_ERROR_NONE) $settings = array_merge($settings, $s);
}

// Handle settings update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_settings'])) {
    $settings['university_name']      = trim(htmlspecialchars($_POST['university_name'] ?? $settings['university_name']));
    $settings['tagline']              = trim(htmlspecialchars($_POST['tagline'] ?? $settings['tagline']));
    $settings['otp_validity_minutes'] = max(1, min(60, (int)($_POST['otp_validity_minutes'] ?? 5)));

    if (file_put_contents($settingsFile, json_encode($settings, JSON_PRETTY_PRINT))) {
        $saveMsg = ['type' => 'success', 'text' => 'Settings updated successfully.'];
    } else {
        $saveMsg = ['type' => 'error', 'text' => 'Failed to save settings. Check file permissions.'];
    }
}

// System stats
$userData   = file_exists('users.json')       ? json_decode(file_get_contents('users.json'), true)       : ['users' => []];
$matData    = file_exists('materials.json')   ? json_decode(file_get_contents('materials.json'), true)   : ['materials' => []];
$gradeData  = file_exists('grades.json')      ? json_decode(file_get_contents('grades.json'), true)      : ['grades' => []];
$assignData = file_exists('assignments.json') ? json_decode(file_get_contents('assignments.json'), true) : ['assignments' => []];

$totalUsers   = count($userData['users'] ?? []);
$totalMats    = count($matData['materials'] ?? []);
$totalGrades  = count($gradeData['grades'] ?? []);
$totalAssigns = count($assignData['assignments'] ?? []);

$jsonSize = file_exists('users.json') ? round(filesize('users.json') / 1024, 2) : 0;
?>

<style>
    .section-title { font-family: 'Playfair Display', serif; font-size: 1.4rem; margin-bottom: 6px; color: #0f1923; }
    .section-sub   { color: #6b7280; font-size: 0.87rem; margin-bottom: 28px; }

    .settings-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; align-items: start; }

    .settings-card {
        background: white;
        border-radius: 12px;
        padding: 28px 30px;
        border: 1px solid #e5e7eb;
    }

    .settings-card h3 {
        font-family: 'Playfair Display', serif;
        font-size: 1.05rem;
        margin-bottom: 20px;
        color: #0f1923;
        padding-bottom: 12px;
        border-bottom: 1px solid #f3f4f6;
    }

    .form-group { margin-bottom: 16px; }
    .form-group label { display: block; font-size: 0.8rem; font-weight: 600; color: #374151; margin-bottom: 7px; text-transform: uppercase; letter-spacing: 0.4px; }
    .form-group input { width: 100%; padding: 10px 13px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 0.88rem; font-family: 'Inter', sans-serif; transition: border-color 0.2s; box-sizing: border-box; }
    .form-group input:focus { outline: none; border-color: #D4AF37; box-shadow: 0 0 0 3px rgba(212,175,55,0.12); }

    .save-btn { padding: 10px 22px; background: #D4AF37; color: white; border: none; border-radius: 8px; font-size: 0.88rem; font-weight: 700; cursor: pointer; font-family: 'Inter', sans-serif; transition: background 0.2s; }
    .save-btn:hover { background: #a8891e; }

    .stat-row { display: flex; justify-content: space-between; align-items: center; padding: 11px 0; border-bottom: 1px solid #f3f4f6; font-size: 0.875rem; }
    .stat-row:last-child { border-bottom: none; }
    .stat-row .stat-val { font-weight: 700; color: #D4AF37; font-family: 'Playfair Display', serif; font-size: 1.1rem; }
    .stat-row .stat-key { color: #6b7280; }

    .info-row { display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid #f3f4f6; font-size: 0.82rem; }
    .info-row:last-child { border-bottom: none; }
    .info-row .key   { color: #6b7280; }
    .info-row .val   { font-weight: 500; color: #0f1923; }
    .info-row .val.ok  { color: #15803d; }
    .info-row .val.err { color: #dc2626; }

    .alert-inline { padding: 10px 14px; border-radius: 8px; font-size: 0.85rem; margin-bottom: 16px; }
    .alert-inline.success { background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }
    .alert-inline.error   { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
</style>

<div class="section-title">System Settings</div>
<div class="section-sub">Configure the university portal and view system information.</div>

<?php if (isset($saveMsg)): ?>
<div class="alert-inline <?= $saveMsg['type'] ?>"><?= $saveMsg['type']==='success'?'✅':'❌' ?> <?= htmlspecialchars($saveMsg['text']) ?></div>
<?php endif; ?>

<div class="settings-grid">
    <!-- Portal Settings -->
    <div class="settings-card">
        <h3>⚙️ Portal Configuration</h3>
        <form method="POST" action="dashboard.php?page=settings">
            <div class="form-group">
                <label>University / Portal Name</label>
                <input type="text" name="university_name" value="<?= htmlspecialchars($settings['university_name']) ?>" required>
            </div>
            <div class="form-group">
                <label>Tagline</label>
                <input type="text" name="tagline" value="<?= htmlspecialchars($settings['tagline']) ?>">
            </div>
            <div class="form-group">
                <label>OTP Validity (minutes)</label>
                <input type="number" name="otp_validity_minutes" value="<?= (int)$settings['otp_validity_minutes'] ?>" min="1" max="60">
            </div>
            <input type="hidden" name="update_settings" value="1">
            <button type="submit" class="save-btn">💾 Save Settings</button>
        </form>
    </div>

    <!-- System Stats -->
    <div>
        <div class="settings-card" style="margin-bottom:20px;">
            <h3>📊 Database Overview</h3>
            <div class="stat-row"><span class="stat-key">Total Users</span><span class="stat-val"><?= $totalUsers ?></span></div>
            <div class="stat-row"><span class="stat-key">Materials Uploaded</span><span class="stat-val"><?= $totalMats ?></span></div>
            <div class="stat-row"><span class="stat-key">Grade Records</span><span class="stat-val"><?= $totalGrades ?></span></div>
            <div class="stat-row"><span class="stat-key">Assignments Submitted</span><span class="stat-val"><?= $totalAssigns ?></span></div>
        </div>

        <div class="settings-card">
            <h3>🖥️ System Information</h3>
            <div class="info-row"><span class="key">PHP Version</span><span class="val"><?= phpversion() ?></span></div>
            <div class="info-row"><span class="key">Server Time</span><span class="val"><?= date('Y-m-d H:i:s') ?></span></div>
            <div class="info-row"><span class="key">users.json size</span><span class="val"><?= $jsonSize ?> KB</span></div>
            <div class="info-row">
                <span class="key">OpenSSL (encryption)</span>
                <span class="val <?= extension_loaded('openssl') ? 'ok' : 'err' ?>"><?= extension_loaded('openssl') ? '✔ Available' : '✘ Not loaded' ?></span>
            </div>
            <div class="info-row">
                <span class="key">Session Active</span>
                <span class="val ok">✔ Yes</span>
            </div>
            <div class="info-row">
                <span class="key">File Write Permission</span>
                <span class="val <?= is_writable('users.json') ? 'ok' : 'err' ?>"><?= is_writable('users.json') ? '✔ Writable' : '✘ Read-only' ?></span>
            </div>
        </div>
    </div>
</div>