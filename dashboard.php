<?php
session_start();
require 'cryptograph_process.php';

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$role = $_SESSION['role'] ?? 'student';
$user = $_SESSION['username'];
$page = $_GET['page'] ?? 'home';

$siteSettings = ['university_name' => 'LUP PORTAL', 'tagline' => 'Learn · Unite · Progress'];
if (file_exists('settings.json')) {
    $s = json_decode(file_get_contents('settings.json'), true);
    if (json_last_error() === JSON_ERROR_NONE) $siteSettings = array_merge($siteSettings, $s);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($siteSettings['university_name']) ?> — Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --blue: #1A3F7A;
            --blue-light: #2563EB;
            --blue-mid: #1E4DB7;
            --blue-dark: #0F2550;
            --orange: #F97316;
            --orange-light: #FB923C;
            --white: #FFFFFF;
            --off-white: #F8FAFC;
            --border: #E2E8F0;
            --sidebar-w: 280px;
            --text-main: #1E293B;
            --text-muted: #64748B;
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--off-white);
            color: var(--text-main);
            display: flex;
            min-height: 100vh;
        }

        /* ── SIDEBAR ── */
        .sidebar {
            width: var(--sidebar-w);
            background: var(--blue-dark);
            min-height: 100vh;
            position: fixed;
            top: 0; left: 0;
            display: flex;
            flex-direction: column;
            z-index: 100;
        }

        .sidebar-brand {
            padding: 30px 24px;
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }

        .brand-name {
            font-family: 'Syne', sans-serif;
            font-size: 1.4rem;
            font-weight: 800;
            color: white;
            letter-spacing: -0.5px;
        }

        .brand-name span { color: var(--orange); }

        .sidebar-user {
            padding: 20px 24px;
            display: flex;
            align-items: center;
            gap: 12px;
            background: rgba(255,255,255,0.03);
            margin: 15px;
            border-radius: 16px;
        }

        .user-avatar {
            width: 42px; height: 42px;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--blue-light), var(--orange));
            display: flex; align-items: center; justify-content: center;
            font-family: 'Syne', sans-serif;
            font-size: 1.1rem; font-weight: 800; color: white;
        }

        .user-name { font-size: 0.95rem; font-weight: 600; color: white; }
        .user-role {
            font-size: 0.7rem; font-weight: 700;
            padding: 2px 8px; border-radius: 6px;
            text-transform: uppercase; margin-top: 4px; display: inline-block;
        }
        .role-admin   { background: #f9731633; color: #fb923c; }
        .role-faculty { background: #2563eb33; color: #93c5fd; }
        .role-student { background: #16a34a33; color: #86efac; }

        .sidebar nav { flex: 1; padding: 10px 15px; }

        .nav-section {
            padding: 20px 10px 8px;
            font-size: 0.7rem;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: rgba(255,255,255,0.3);
            font-weight: 700;
        }

        .sidebar a {
            display: flex; align-items: center; gap: 12px;
            padding: 12px 16px;
            color: rgba(255,255,255,0.6);
            text-decoration: none;
            font-size: 0.9rem; font-weight: 500;
            transition: 0.2s;
            border-radius: 12px;
            margin-bottom: 4px;
        }

        .sidebar a:hover { background: rgba(255,255,255,0.05); color: white; }

        .sidebar a.active {
            background: var(--blue-light);
            color: white;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }

        .logout-btn {
            display: flex; align-items: center; gap: 10px;
            width: 100%; padding: 12px;
            background: #ef444415;
            color: #fca5a5;
            border: 1px solid #ef444433;
            border-radius: 12px;
            font-size: 0.9rem; font-weight: 600;
            cursor: pointer; transition: 0.2s;
        }

        .logout-btn:hover { background: #ef444425; color: white; }

        /* ── MAIN CONTENT ── */
        .main {
            margin-left: var(--sidebar-w);
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .topbar {
            background: white;
            padding: 0 40px;
            height: 80px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0; z-index: 50;
            border-bottom: 1px solid var(--border);
        }

        .topbar h1 {
            font-family: 'Syne', sans-serif;
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--blue-dark);
        }

        .content { padding: 40px; max-width: 1400px; margin: 0 auto; width: 100%; }

        /* ── SHARED COMPONENTS ── */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 24px; margin-bottom: 40px; }
        .stat-card {
            background: white; padding: 24px; border-radius: 20px;
            border: 1px solid var(--border); box-shadow: var(--shadow);
        }
        .stat-label { font-size: 0.8rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase; letter-spacing: 1px; }
        .stat-value { font-size: 2rem; font-family: 'Syne', sans-serif; font-weight: 800; color: var(--blue-dark); margin-top: 8px; }

        .card {
            background: white; border-radius: 20px; border: 1px solid var(--border);
            box-shadow: var(--shadow); overflow: hidden;
        }
        .card-header { padding: 24px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; }
        
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 16px 24px; background: #F8FAFC; font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); letter-spacing: 1px; font-weight: 700; }
        td { padding: 18px 24px; border-bottom: 1px solid var(--border); font-size: 0.95rem; }

        .btn-primary {
            background: var(--blue-light); color: white; padding: 12px 24px; border-radius: 12px;
            border: none; font-weight: 600; cursor: pointer; transition: 0.2s;
        }
        .btn-primary:hover { background: var(--blue-mid); transform: translateY(-2px); }

        .alert {
            padding: 16px 24px; border-radius: 16px; margin-bottom: 30px;
            display: flex; align-items: center; gap: 12px; font-weight: 500;
        }
        .alert-success { background: #DCFCE7; color: #166534; border: 1px solid #BBF7D0; }
        .alert-error   { background: #FEE2E2; color: #991B1B; border: 1px solid #FECACA; }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="sidebar-brand">
        <div class="brand-name"><span>LUP</span> PORTAL</div>
    </div>

    <div class="sidebar-user">
        <div class="user-avatar"><?= strtoupper(substr($user, 0, 1)) ?></div>
        <div class="user-info">
            <div class="user-name"><?= htmlspecialchars($user) ?></div>
            <span class="user-role role-<?= $role ?>"><?= $role ?></span>
        </div>
    </div>

    <nav>
        <div class="nav-section">General</div>
        <a href="dashboard.php?page=home" class="<?= $page==='home'?'active':'' ?>">🏠 Home</a>

        <?php if ($role === 'admin'): ?>
            <div class="nav-section">Administration</div>
            <a href="dashboard.php?page=manage_users"  class="<?= $page==='manage_users'?'active':'' ?>">👥 Manage Users</a>
            <a href="dashboard.php?page=register_staff" class="<?= $page==='register_staff'?'active':'' ?>">➕ Register Staff</a>
            <a href="dashboard.php?page=settings"       class="<?= $page==='settings'?'active':'' ?>">⚙️ System Settings</a>

        <?php elseif ($role === 'faculty'): ?>
            <div class="nav-section">Teaching</div>
            <a href="dashboard.php?page=upload" class="<?= $page==='upload'?'active':'' ?>">📚 Course Materials</a>
            <a href="dashboard.php?page=grades" class="<?= $page==='grades'?'active':'' ?>">📝 Gradebook</a>

        <?php elseif ($role === 'student'): ?>
            <div class="nav-section">Learning</div>
            <a href="dashboard.php?page=view"   class="<?= $page==='view'?'active':'' ?>">📖 My Courses</a>
            <a href="dashboard.php?page=submit" class="<?= $page==='submit'?'active':'' ?>">📤 Submit Tasks</a>
        <?php endif; ?>
    </nav>

    <div style="padding: 20px;">
        <form method="POST" action="logout.php">
            <button type="submit" class="logout-btn">🚪 Sign Out</button>
        </form>
    </div>
</div>

<div class="main">
    <div class="topbar">
        <div>
            <div style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">Welcome back, <?= htmlspecialchars($user) ?></div>
            <h1>
                <?php
                    $titles = [
                        'home'           => 'Dashboard Overview',
                        'manage_users'   => 'User Management',
                        'register_staff' => 'Onboard Staff',
                        'settings'       => 'System Settings',
                        'upload'         => 'Faculty Uploads',
                        'grades'         => 'Grade Management',
                        'view'           => 'Course Resources',
                        'submit'         => 'Assignments',
                    ];
                    echo $titles[$page] ?? 'Dashboard';
                ?>
            </h1>
        </div>
        <div style="font-weight: 600; background: var(--off-white); padding: 10px 20px; border-radius: 12px; border: 1px solid var(--border);">
            📅 <?= date('M j, Y') ?>
        </div>
    </div>

    <div class="content">
        <?php
        if (isset($_SESSION['msg'])):
            $msgType = $_SESSION['msg_type'] ?? 'success';
        ?>
            <div class="alert alert-<?= htmlspecialchars($msgType) ?>">
                <?= htmlspecialchars($_SESSION['msg']) ?>
            </div>
        <?php
            unset($_SESSION['msg'], $_SESSION['msg_type']);
        endif;
        ?>

        <?php
        switch ($page) {
            case 'manage_users':
                if ($role === 'admin') include 'role_Admin_ManageUsers.php';
                else echo '<div class="card" style="padding:40px; text-align:center;"><h3>Access Denied</h3></div>';
                break;
            case 'register_staff':
                if ($role === 'admin') include 'role_admin_register_staff.php';
                else echo '<p>Access denied.</p>';
                break;
            case 'settings':
                if ($role === 'admin') include 'role_admin_systemsettings.php';
                else echo '<p>Access denied.</p>';
                break;
            case 'upload':
                if ($role === 'faculty') include 'role_faculty_uploadMaterials.php';
                else echo '<p>Access denied.</p>';
                break;
            case 'grades':
                if ($role === 'faculty') include 'role_faculty_ManageGrades.php';
                else echo '<p>Access denied.</p>';
                break;
            case 'view':
                if ($role === 'student') include 'role_student_viewMaterials.php';
                else echo '<p>Access denied.</p>';
                break;
            case 'submit':
                if ($role === 'student') include 'role_student_SubmitAssignment.php';
                else echo '<p>Access denied.</p>';
                break;
            default:
                include 'role_home.php';
                break;
        }
        ?>
    </div>
</div>

</body>
</html> 