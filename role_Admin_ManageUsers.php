<?php
// Included inside dashboard.php
$jsonFile = "users.json";
$data  = file_exists($jsonFile) ? json_decode(file_get_contents($jsonFile), true) : ['users' => []];
$users = $data['users'] ?? [];

$filterRole   = $_GET['filter_role']   ?? 'all';
$filterStatus = $_GET['filter_status'] ?? 'all';
$search       = strtolower(trim($_GET['search'] ?? ''));

$filtered = array_filter($users, function($u) use ($filterRole, $filterStatus, $search) {
    $role   = $u['role']   ?? 'student';
    $status = $u['status'] ?? ($role === 'student' ? 'pending' : 'approved');
    if ($filterRole   !== 'all' && $role   !== $filterRole)   return false;
    if ($filterStatus !== 'all' && $status !== $filterStatus) return false;
    if ($search && strpos(strtolower($u['username']), $search) === false && strpos(strtolower($u['fullname']), $search) === false) return false;
    return true;
});

$counts = ['all' => count($users), 'admin' => 0, 'faculty' => 0, 'student' => 0, 'pending' => 0, 'approved' => 0, 'declined' => 0];
foreach ($users as $u) {
    $r = $u['role'] ?? 'student';
    $s = $u['status'] ?? ($r === 'student' ? 'pending' : 'approved');
    if (isset($counts[$r])) $counts[$r]++;
    if (isset($counts[$s])) $counts[$s]++;
}
?>

<style>
    .lup-section-title { font-family: 'Syne', sans-serif; font-size: 1.35rem; font-weight: 800; margin-bottom: 4px; color: #0F2550; }
    .lup-section-sub   { color: #64748b; font-size: 0.875rem; margin-bottom: 24px; }

    /* Stats pills */
    .stats-strip { display: flex; gap: 12px; flex-wrap: wrap; margin-bottom: 24px; }
    .stat-pill {
        background: white; border: 1px solid #e2e8f0;
        border-radius: 12px; padding: 14px 20px; min-width: 90px;
        transition: box-shadow 0.2s, transform 0.2s;
    }
    .stat-pill:hover { transform: translateY(-2px); box-shadow: 0 4px 16px rgba(15,37,80,0.08); }
    .stat-pill .val { font-family: 'Syne', sans-serif; font-size: 1.7rem; font-weight: 800; line-height: 1; }
    .stat-pill .lbl { font-size: 0.7rem; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.8px; margin-top: 4px; }

    /* Filter bar */
    .filter-bar {
        display: flex; flex-wrap: wrap; gap: 10px; align-items: center;
        background: white; padding: 14px 18px; border-radius: 12px;
        border: 1px solid #e2e8f0; margin-bottom: 22px;
        box-shadow: 0 1px 4px rgba(0,0,0,0.04);
    }
    .filter-bar input[type="text"],
    .filter-bar select {
        padding: 8px 14px; border: 1px solid #e2e8f0;
        border-radius: 8px; font-size: 0.875rem;
        font-family: 'DM Sans', sans-serif; outline: none; background: white;
        transition: border-color 0.2s;
    }
    .filter-bar input[type="text"] { width: 220px; }
    .filter-bar input[type="text"]:focus,
    .filter-bar select:focus { border-color: #2563EB; box-shadow: 0 0 0 3px rgba(37,99,235,0.1); }

    .lup-btn {
        padding: 8px 18px; border-radius: 8px; font-size: 0.84rem;
        font-weight: 600; cursor: pointer; font-family: 'DM Sans', sans-serif;
        text-decoration: none; display: inline-block; border: none; transition: all 0.2s;
    }
    .lup-btn-primary { background: #2563EB; color: white; }
    .lup-btn-primary:hover { background: #1d4ed8; transform: translateY(-1px); }
    .lup-btn-reset { background: #f1f5f9; color: #475569; }
    .lup-btn-reset:hover { background: #e2e8f0; }

    /* Table */
    .user-table { width: 100%; border-collapse: collapse; background: white; border-radius: 14px; overflow: hidden; border: 1px solid #e2e8f0; font-size: 0.875rem; box-shadow: 0 1px 6px rgba(0,0,0,0.04); }
    .user-table thead tr { background: #f8fafc; }
    .user-table th { padding: 12px 16px; text-align: left; font-weight: 700; font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.8px; color: #64748b; border-bottom: 1px solid #e2e8f0; }
    .user-table td { padding: 13px 16px; border-bottom: 1px solid #f1f5f9; color: #334155; vertical-align: middle; }
    .user-table tr:last-child td { border-bottom: none; }
    .user-table tr { transition: background 0.15s; }
    .user-table tr:hover td { background: #f8faff; }

    /* Badges */
    .badge { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
    .badge-admin   { background: rgba(249,115,22,0.12); color: #c2410c; }
    .badge-faculty { background: rgba(37,99,235,0.1);   color: #1d4ed8; }
    .badge-student { background: rgba(22,163,74,0.1);   color: #15803d; }
    .badge-pending  { background: #fffbeb; color: #b45309; }
    .badge-approved { background: #f0fdf4; color: #15803d; }
    .badge-declined { background: #fef2f2; color: #dc2626; }

    /* Action buttons — disappear when one is chosen */
    .action-cell { min-width: 160px; }

    .btn-approve, .btn-decline {
        padding: 5px 14px; border-radius: 8px; font-size: 0.77rem;
        font-weight: 700; border: none; cursor: pointer;
        font-family: 'DM Sans', sans-serif; transition: all 0.2s;
    }
    .btn-approve { background: #dcfce7; color: #15803d; margin-right: 6px; }
    .btn-approve:hover { background: #bbf7d0; transform: translateY(-1px); }
    .btn-decline { background: #fee2e2; color: #dc2626; }
    .btn-decline:hover { background: #fecaca; transform: translateY(-1px); }

    /* When one is clicked, the other fades out */
    .action-group.acting .btn-approve,
    .action-group.acting .btn-decline { display: none; }
    .action-group.acting .acting-msg {
        font-size: 0.78rem; color: #94a3b8;
        display: inline-flex; align-items: center; gap: 4px;
    }
    .acting-msg { display: none; }

    .empty-state { text-align: center; padding: 60px 20px; color: #94a3b8; }
    .empty-state span { font-size: 3rem; display: block; margin-bottom: 12px; }
</style>

<div class="lup-section-title">Manage Users</div>
<div class="lup-section-sub">View, approve, or decline student registrations and manage all portal accounts.</div>

<!-- Stats -->
<div class="stats-strip">
    <?php
    $pills = [
        ['label'=>'Total','val'=>$counts['all'],'color'=>'#0F2550'],
        ['label'=>'Admin','val'=>$counts['admin'],'color'=>'#c2410c'],
        ['label'=>'Faculty','val'=>$counts['faculty'],'color'=>'#1d4ed8'],
        ['label'=>'Students','val'=>$counts['student'],'color'=>'#15803d'],
        ['label'=>'Pending','val'=>$counts['pending'],'color'=>'#b45309'],
    ];
    foreach ($pills as $p):
    ?>
    <div class="stat-pill">
        <div class="val" style="color:<?= $p['color'] ?>"><?= $p['val'] ?></div>
        <div class="lbl"><?= $p['label'] ?></div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Filters -->
<form method="GET" action="dashboard.php">
    <input type="hidden" name="page" value="manage_users">
    <div class="filter-bar">
        <input type="text" name="search" placeholder="Search by name or username…" value="<?= htmlspecialchars($search) ?>">
        <select name="filter_role">
            <option value="all" <?= $filterRole==='all'?'selected':'' ?>>All Roles</option>
            <option value="admin"   <?= $filterRole==='admin'?'selected':'' ?>>Admin</option>
            <option value="faculty" <?= $filterRole==='faculty'?'selected':'' ?>>Faculty</option>
            <option value="student" <?= $filterRole==='student'?'selected':'' ?>>Student</option>
        </select>
        <select name="filter_status">
            <option value="all"      <?= $filterStatus==='all'?'selected':'' ?>>All Status</option>
            <option value="pending"  <?= $filterStatus==='pending'?'selected':'' ?>>Pending</option>
            <option value="approved" <?= $filterStatus==='approved'?'selected':'' ?>>Approved</option>
            <option value="declined" <?= $filterStatus==='declined'?'selected':'' ?>>Declined</option>
        </select>
        <button type="submit" class="lup-btn lup-btn-primary">Filter</button>
        <a href="dashboard.php?page=manage_users" class="lup-btn lup-btn-reset">Reset</a>
    </div>
</form>

<?php if (empty($filtered)): ?>
    <div class="empty-state"><span>🔍</span><p>No users match the current filter.</p></div>
<?php else: ?>
<div style="overflow-x:auto;">
<table class="user-table">
    <thead>
        <tr>
            <th>#</th>
            <th>Username</th>
            <th>Full Name</th>
            <th>Role</th>
            <th>Status</th>
            <th>Registered</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
    <?php $i=1; foreach ($filtered as $u):
        $uRole   = $u['role']   ?? 'student';
        $uStatus = $u['status'] ?? ($uRole==='student'?'pending':'approved');
        $isMe    = ($u['username'] === $_SESSION['username']);
    ?>
    <tr>
        <td style="color:#94a3b8;"><?= $i++ ?></td>
        <td><strong><?= htmlspecialchars($u['username']) ?></strong><?php if($isMe): ?> <span style="font-size:0.7rem;color:var(--orange)">(you)</span><?php endif; ?></td>
        <td><?= htmlspecialchars($u['fullname']) ?></td>
        <td><span class="badge badge-<?= $uRole ?>"><?= ucfirst($uRole) ?></span></td>
        <td><span class="badge badge-<?= $uStatus ?>"><?= ucfirst($uStatus) ?></span></td>
        <td style="color:#94a3b8;font-size:0.8rem;"><?= htmlspecialchars($u['registered'] ?? '—') ?></td>
        <td class="action-cell">
            <?php if ($uRole === 'student' && !$isMe): ?>
                <div class="action-group" id="ag-<?= htmlspecialchars($u['username']) ?>">
                    <?php if ($uStatus === 'pending' || $uStatus === 'declined'): ?>
                    <form method="POST" action="process_approve_student.php" style="display:inline;" onsubmit="handleAction(this,'<?= htmlspecialchars(addslashes($u['username'])) ?>')">
                        <input type="hidden" name="username" value="<?= htmlspecialchars($u['username']) ?>">
                        <input type="hidden" name="action" value="approved">
                        <button type="submit" class="btn-approve" onclick="hideOther('<?= htmlspecialchars(addslashes($u['username'])) ?>','approve')">✔ Approve</button>
                    </form>
                    <?php endif; ?>
                    <?php if ($uStatus === 'pending' || $uStatus === 'approved'): ?>
                    <form method="POST" action="process_approve_student.php" style="display:inline;" onsubmit="handleAction(this,'<?= htmlspecialchars(addslashes($u['username'])) ?>')">
                        <input type="hidden" name="username" value="<?= htmlspecialchars($u['username']) ?>">
                        <input type="hidden" name="action" value="declined">
                        <button type="submit" class="btn-decline" onclick="hideOther('<?= htmlspecialchars(addslashes($u['username'])) ?>','decline')">✘ Decline</button>
                    </form>
                    <?php endif; ?>
                    <span class="acting-msg" id="act-<?= htmlspecialchars($u['username']) ?>">⏳ Processing…</span>
                </div>
            <?php elseif ($isMe): ?>
                <span style="color:#94a3b8;font-size:0.78rem;">Current session</span>
            <?php else: ?>
                <span style="color:#94a3b8;font-size:0.78rem;">—</span>
            <?php endif; ?>
        </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
<?php endif; ?>

<script>
// When one action button is clicked, hide all other buttons in that row
function hideOther(username, chosen) {
    const group = document.getElementById('ag-' + username);
    if (!group) return;

    // Hide all buttons in this action group except the one just clicked
    const forms = group.querySelectorAll('form');
    forms.forEach(form => {
        const btn = form.querySelector('button');
        if (btn) {
            const isApprove = btn.classList.contains('btn-approve');
            const isChosen  = (chosen === 'approve' && isApprove) || (chosen === 'decline' && !isApprove);
            if (!isChosen) {
                // Fade out the other button
                btn.style.transition = 'opacity 0.3s, transform 0.3s';
                btn.style.opacity    = '0';
                btn.style.transform  = 'scale(0.8)';
                setTimeout(() => btn.style.display = 'none', 300);
            } else {
                // Loading state on chosen button
                setTimeout(() => {
                    btn.disabled      = true;
                    btn.style.opacity = '0.6';
                    btn.textContent   = chosen === 'approve' ? '⏳ Approving…' : '⏳ Declining…';
                }, 50);
            }
        }
    });
}
</script>