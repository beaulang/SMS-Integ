<?php
session_start();

// 1. Consistency Check: Your login process uses $_SESSION['role'] and $_SESSION['username']
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'student') { 
    header('Location: login.php');
    exit;
}

$student_name = $_SESSION['username']; 

// 2. Page Navigation Logic
$current_page = $_GET['page'] ?? 'view_materials'; 
$content_file = '';

switch ($current_page) {
    case 'submit':
        $content_file = 'role_student_SubmitAssignment.php';
        $page_title = 'Submit Assignment';
        break;
    case 'viewing_details':
        $content_file = 'role_student_Viewing.php'; 
        $page_title = 'Material Details';
        break;
    case 'view_materials':
    default:
        $content_file = 'role_student_viewMaterials.php';
        $page_title = 'View Materials';
        break;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - <?php echo htmlspecialchars($page_title); ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; background-color: #f4f4f4; display: flex; }
        
        /* Sidebar Styling */
        .sidebar { width: 250px; height: 100vh; background-color: #2c3e50; color: white; padding: 20px 0; box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1); position: fixed; }
        .sidebar h2 { text-align: center; margin-bottom: 30px; font-size: 1.5em; color: #ecf0f1; border-bottom: 1px solid #34495e; padding-bottom: 20px; }
        .sidebar ul { list-style: none; padding: 0; margin: 0; }
        .sidebar ul li a {
            display: block; padding: 15px 20px; text-decoration: none; color: white; font-size: 1.1em; border-left: 5px solid transparent; transition: 0.3s;
        }
        
        /* Highlight Active Page */
        .sidebar ul li a.active {
            background-color: #34495e; 
            border-left-color: #3b5998; 
        }
        .sidebar ul li a:hover { background-color: #34495e; }

        /* Logout Button Styles */
        .sidebar .logout-link { position: absolute; bottom: 30px; width: 100%; text-align: center; }
        .sidebar .logout-link a { 
            display: inline-block; padding: 10px 20px; width: 80%;
            background-color: #e74c3c; color: white; border-radius: 5px; 
            text-decoration: none; font-weight: bold; transition: 0.3s;
        }
        .sidebar .logout-link a:hover { background-color: #c0392b; }

        /* Content Area */
        .content { margin-left: 250px; flex-grow: 1; padding: 40px; }
        .header-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        h1 { color: #3b5998; margin: 0; }
        .welcome-text { color: #666; font-style: italic; }
        
        .content-box { 
            background-color: white; 
            padding: 30px; 
            border-radius: 8px; 
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
            min-height: 400px;
        }
    </style>
</head>
<body>

    <div class="sidebar">
        <h2>Student Panel</h2>
        
        <ul>
            <li>
                <a href="dashboard_student.php?page=view_materials" 
                   class="<?php echo ($current_page == 'view_materials') ? 'active' : ''; ?>">
                   View Materials
                </a>
            </li> 
            <li>
                <a href="dashboard_student.php?page=submit" 
                   class="<?php echo ($current_page == 'submit') ? 'active' : ''; ?>">
                   Submit Assignment
                </a>
            </li>
            <li>
                <a href="dashboard_student.php?page=viewing_details" 
                   class="<?php echo ($current_page == 'viewing_details') ? 'active' : ''; ?>">
                   Material Details
                </a>
            </li>
        </ul>

        <div class="logout-link">
            <a href="logout.php">Log Out</a>
        </div>
    </div>

    <div class="content">
        <div class="header-bar">
            <h1><?php echo htmlspecialchars($page_title); ?></h1>
            <span class="welcome-text">Logged in as: <strong><?php echo htmlspecialchars($student_name); ?></strong></span>
        </div>
        
        <div class="content-box">
            <?php
            // Dynamically include the sub-files you created
            if ($content_file && file_exists($content_file)) {
                include $content_file;
            } else {
                echo '<h2>File Missing</h2><p>The file ' . htmlspecialchars($content_file) . ' was not found.</p>';
            }
            ?>
        </div>
    </div>

</body>
</html>