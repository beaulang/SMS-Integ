<?php
// role_home.php — LUP Portal version
$role = $_SESSION['role'] ?? 'student';
$user = $_SESSION['username'];

$userData   = file_exists('users.json')       ? json_decode(file_get_contents('users.json'), true)       : ['users' => []];
$matData    = file_exists('materials.json')   ? json_decode(file_get_contents('materials.json'), true)   : ['materials' => []];
$gradeData  = file_exists('grades.json')      ? json_decode(file_get_contents('grades.json'), true)      : ['grades' => []];
$assignData = file_exists('assignments.json') ? json_decode(file_get_contents('assignments.json'), true) : ['assignments' => []];

$allUsers    = $userData['users']        ?? [];
$materials   = $matData['materials']     ?? [];
$grades      = $gradeData['grades']      ?? [];
$assignments = $assignData['assignments'] ?? [];

$greetings = ['Good morning', 'Good morning', 'Good morning', 'Good morning', 'Good morning', 'Good morning',
              'Good afternoon', 'Good afternoon', 'Good afternoon', 'Good afternoon', 'Good afternoon', 'Good afternoon',
              'Good evening', 'Good evening', 'Good evening', 'Good evening', 'Good evening', 'Good evening',
              'Good evening', 'Good evening', 'Good evening', 'Good evening', 'Good night', 'Good night'];
$greeting = $greetings[(int)date('G')];
?>

<style>
    .home-hero {
        background: linear-gradient(135deg, #0F2550 0%, #1A3F7A 50%, #1E4DB7 100%);
        border-radius: 18px;
        padding: 36px 40px;
        margin-bottom: 28px;
        position: relative;
        overflow: hidden;
        color: white;
        box-shadow: 0 8px 32px rgba(15,37,80,0.2);
    }

    .home-hero::before {
        content: '';
        position: absolute;
        top: -60px; right: -60px;
        width: 280px; height: 280px;
        background: rgba(249,115,22,0.12);
        border-radius: 50%;
        pointer-events: none;
    }

    .home-hero::after {
        content: '';
        position: absolute;
        bottom: -80px; left: 40%;
        width: 200px; height: 200px;
        background: rgba(37,99,235,0.15);
        border-radius: 50%;
        pointer-events: none;
    }

    .hero-greeting {
        font-size: 0.8rem;
        font-weight: 500;
        color: rgba(255,255,255,0.55);
        text-transform: uppercase;
        letter-spacing: 2px;
        margin-bottom: 6px;
    }

    .hero-name {
        font-family: 'Syne', sans-serif;
        font-size: 2rem;
        font-weight: 800;
        margin-bottom: 6px;
    }

    .hero-name span { color: #FB923C; }

    .hero-sub {
        color: rgba(255,255,255,0.5);
        font-size: 0.875rem;
    }

    .hero-badge {
        position: absolute;
        top: 28px; right: 32px;
        background: rgba(255,255,255,0.08);
        border: 1px solid rgba(255,255,255,0.12);
        border-radius: 10px;
        padding: 8px 16px;
        font-size: 0.78rem;
        color: rgba(255,255,255,0.6);
        backdrop-filter: blur(8px);
    }

    /* Stats grid */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
        gap: 16px;
        margin-bottom: 28px;
    }

    .stat-card {
        background: white;
        border-radius: 14px;
        padding: 20px 22px;
        border: 1px solid #e2e8f0;
        position: relative;
        overflow: hidden;
        transition: box-shadow 0.2s, transform 0.2s;
        cursor: default;
    }

    .stat-card:hover { transform: translateY(-3px); box-shadow: 0 8px 24px rgba(15,37,80,0.1); }

    .stat-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 3px;
        background: var(--accent, #2563EB);
    }

    .stat-card .stat-icon {
        font-size: 1.5rem;
        margin-bottom: 10px;
        display: block;
    }

    .stat-card .stat-num {
        font-family: 'Syne', sans-serif;
        font-size: 2rem; font-weight: 800;
        color: #0F2550; line-height: 1;
        margin-bottom: 4px;
        transition: color 0.3s;
    }

    .stat-card:hover .stat-num { color: var(--accent, #2563EB); }

    .stat-card .stat-label {
        font-size: 0.75rem; color: #64748b;
        text-transform: uppercase; letter-spacing: 0.8px;
        font-weight: 600;
    }

    /* Info boxes */
    .info-box {
        background: white;
        border-radius: 14px;
        padding: 22px 26px;
        border: 1px solid #e2e8f0;
        margin-bottom: 18px;
        box-shadow: 0 1px 4px rgba(0,0,0,0.04);
    }

    .info-box h3 {
        font-family: 'Syne', sans-serif;
        font-size: 1rem; font-weight: 800;
        color: #0F2550; margin-bottom: 16px;
        display: flex; align-items: center; gap: 8px;
    }

    .action-cards { display: flex; flex-wrap: wrap; gap: 12px; }
    .action-card {
        display: flex; align-items: center; gap: 12px;
        padding: 14px 20px;
        background: linear-gradient(135deg, #f8faff, #EFF6FF);
        border: 1px solid #DBEAFE;
        border-radius: 12px;
        text-decoration: none;
        color: #1A3F7A;
        font-size: 0.875rem; font-weight: 600;
        transition: all 0.25s;
        min-width: 160px;
    }
    .action-card:hover {
        background: linear-gradient(135deg, #2563EB, #1A3F7A);
        color: white; border-color: transparent;
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(37,99,235,0.3);
    }
    .action-card-icon { font-size: 1.3rem; }

    .action-card.orange {
        background: linear-gradient(135deg, #fff7ed, #FEF3C7);
        border-color: #FED7AA; color: #92400E;
    }
    .action-card.orange:hover {
        background: linear-gradient(135deg, #F97316, #c2580e);
        color: white; border-color: transparent;
        box-shadow: 0 8px 20px rgba(249,115,22,0.3);
    }

    /* Warning banner */
    .warning-banner {
        background: linear-gradient(135deg, #fffbeb, #fef3c7);
        border: 1px solid #fde68a;
        border-left: 4px solid #f59e0b;
        border-radius: 12px;
        padding: 16px 20px;
        margin-bottom: 18px;
        display: flex; align-items: center; gap: 14px;
    }
    .warning-banner .warn-icon { font-size: 1.6rem; }
    .warning-banner p { color: #92400e; font-size: 0.88rem; line-height: 1.5; }
    .warning-banner strong { color: #78350f; }

    /* Grade table */
    .grade-table { width: 100%; border-collapse: collapse; font-size: 0.875rem; }
    .grade-table th { padding: 10px 14px; text-align: left; font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.6px; color: #64748b; font-weight: 700; border-bottom: 1px solid #e2e8f0; }
    .grade-table td { padding: 11px 14px; border-bottom: 1px solid #f1f5f9; color: #334155; }
    .grade-table tr:last-child td { border-bottom: none; }
    .grade-table tr:hover td { background: #f8faff; }
    .grade-val { font-family: 'Syne', sans-serif; font-weight: 800; color: #2563EB; }
</style>

<div class="home-hero">
    <div class="hero-badge"><?= date('D, M j') ?></div>
    <div class="hero-greeting"><?= $greeting ?></div>
    <div class="hero-name">Welcome, <span><?= htmlspecialchars($user) ?></span>!</div>
    <div class="hero-sub">Here's your LUP Portal overview for today.</div>
</div>

<?php if ($role === 'admin'):
    $totalStudents = 0; $pendingStudents = 0; $totalFaculty = 0; $totalAdmins = 0;
    foreach ($allUsers as $u) {
        $r = $u['role'] ?? 'student';
        $s = $u['status'] ?? ($r === 'student' ? 'pending' : 'approved');
        if ($r === 'student') { $totalStudents++; if ($s === 'pending') $pendingStudents++; }
        if ($r === 'faculty') $totalFaculty++;
        if ($r === 'admin') $totalAdmins++;
    }
?>
    <div class="stats-grid">
        <div class="stat-card" style="--accent:#2563EB"><span class="stat-icon">👤</span><div class="stat-num"><?= $totalStudents ?></div><div class="stat-label">Students</div></div>
        <div class="stat-card" style="--accent:#F97316"><span class="stat-icon">⏳</span><div class="stat-num"><?= $pendingStudents ?></div><div class="stat-label">Pending Approval</div></div>
        <div class="stat-card" style="--accent:#7C3AED"><span class="stat-icon">🎓</span><div class="stat-num"><?= $totalFaculty ?></div><div class="stat-label">Faculty</div></div>
        <div class="stat-card" style="--accent:#059669"><span class="stat-icon">🛡️</span><div class="stat-num"><?= $totalAdmins ?></div><div class="stat-label">Admins</div></div>
    </div>

    <?php if ($pendingStudents > 0): ?>
    <div class="warning-banner">
        <div class="warn-icon">⚠️</div>
        <div>
            <p><strong><?= $pendingStudents ?> student<?= $pendingStudents > 1 ? 's' : '' ?></strong> waiting for account approval.</p>
            <a href="dashboard.php?page=manage_users" style="color:#b45309; font-weight:600; font-size:0.82rem;">Review now →</a>
        </div>
    </div>
    <?php endif; ?>

    <div class="info-box">
        <h3>⚡ Quick Actions</h3>
        <div class="action-cards">
            <a href="dashboard.php?page=manage_users"  class="action-card"><span class="action-card-icon">👥</span> Manage Users</a>
            <a href="dashboard.php?page=register_staff" class="action-card orange"><span class="action-card-icon">➕</span> Register Staff</a>
            <a href="dashboard.php?page=settings"       class="action-card"><span class="action-card-icon">⚙️</span> System Settings</a>
        </div>
    </div>

<?php elseif ($role === 'faculty'):
    $myMaterials = array_filter($materials, fn($m) => $m['uploaded_by'] === $user);
    $myGrades    = array_filter($grades,    fn($g) => $g['faculty']     === $user);
    $myStudents  = array_unique(array_column(array_values($myGrades), 'student'));
?>
    <div class="stats-grid">
        <div class="stat-card" style="--accent:#2563EB"><span class="stat-icon">📚</span><div class="stat-num"><?= count($myMaterials) ?></div><div class="stat-label">Materials Uploaded</div></div>
        <div class="stat-card" style="--accent:#F97316"><span class="stat-icon">📝</span><div class="stat-num"><?= count($myGrades) ?></div><div class="stat-label">Grades Recorded</div></div>
        <div class="stat-card" style="--accent:#059669"><span class="stat-icon">🎓</span><div class="stat-num"><?= count($myStudents) ?></div><div class="stat-label">Students Graded</div></div>
    </div>

    <div class="info-box">
        <h3>⚡ Quick Actions</h3>
        <div class="action-cards">
            <a href="dashboard.php?page=upload" class="action-card"><span class="action-card-icon">📚</span> Upload Material</a>
            <a href="dashboard.php?page=grades" class="action-card orange"><span class="action-card-icon">📝</span> Manage Grades</a>
        </div>
    </div>

<?php elseif ($role === 'student'):
    $myGrades      = array_filter($grades,      fn($g) => $g['student'] === $user);
    $myAssignments = array_filter($assignments,  fn($a) => $a['student'] === $user);
?>
    <div class="stats-grid">
        <div class="stat-card" style="--accent:#2563EB"><span class="stat-icon">📖</span><div class="stat-num"><?= count($materials) ?></div><div class="stat-label">Materials Available</div></div>
        <div class="stat-card" style="--accent:#F97316"><span class="stat-icon">🎯</span><div class="stat-num"><?= count($myGrades) ?></div><div class="stat-label">Grades on Record</div></div>
        <div class="stat-card" style="--accent:#059669"><span class="stat-icon">📤</span><div class="stat-num"><?= count($myAssignments) ?></div><div class="stat-label">Assignments Submitted</div></div>
    </div>

    <?php if (!empty($myGrades)): ?>
    <div class="info-box">
        <h3>🎯 Recent Grades</h3>
        <table class="grade-table">
            <thead>
                <tr>
                    <th>Subject</th><th>Grade</th><th>Faculty</th><th>Remarks</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach (array_slice(array_reverse(array_values($myGrades)), 0, 5) as $g): ?>
            <tr>
                <td><?= htmlspecialchars($g['subject']) ?></td>
                <td><span class="grade-val"><?= htmlspecialchars($g['grade']) ?></span></td>
                <td><?= htmlspecialchars($g['faculty']) ?></td>
                <td style="color:#64748b;"><?= htmlspecialchars($g['remarks'] ?: '—') ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <div class="info-box">
        <h3>⚡ Quick Actions</h3>
        <div class="action-cards">
            <a href="dashboard.php?page=view"   class="action-card"><span class="action-card-icon">📖</span> View Materials</a>
            <a href="dashboard.php?page=submit" class="action-card orange"><span class="action-card-icon">📤</span> Submit Assignment</a>
        </div>
    </div>
<?php endif; ?>