<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'faculty') {
    header("Location: login.php");
    exit();
}

function redirectBack($message, $type = "error") {
    $_SESSION['msg']      = $message;
    $_SESSION['msg_type'] = $type;
    header("Location: dashboard.php?page=grades");
    exit();
}

$jsonFile     = "grades.json";
$usersFile    = "users.json";

if (!file_exists($jsonFile)) {
    file_put_contents($jsonFile, json_encode(['grades' => []], JSON_PRETTY_PRINT));
}

$data = json_decode(file_get_contents($jsonFile), true);
if (json_last_error() !== JSON_ERROR_NONE) {
    redirectBack("System error: Grades data is corrupted.");
}

$action = $_POST['action'] ?? 'add';

// --- DELETE GRADE ---
if ($action === 'delete') {
    $deleteId = (int)($_POST['grade_id'] ?? -1);
    $found    = false;

    foreach ($data['grades'] as $key => $grade) {
        if ($grade['id'] === $deleteId && $grade['faculty'] === $_SESSION['username']) {
            unset($data['grades'][$key]);
            $data['grades'] = array_values($data['grades']);
            $found          = true;
            break;
        }
    }

    if (!$found) {
        redirectBack("Grade record not found or you don't have permission to delete it.");
    }

    file_put_contents($jsonFile, json_encode($data, JSON_PRETTY_PRINT));
    redirectBack("Grade record deleted successfully.", "success");
}

// --- UPDATE GRADE ---
if ($action === 'update') {
    $gradeId    = (int)($_POST['grade_id'] ?? -1);
    $newGrade   = trim($_POST['grade'] ?? '');
    $newRemarks = trim(htmlspecialchars($_POST['remarks'] ?? ''));

    $validGrades = ['1.00','1.25','1.50','1.75','2.00','2.25','2.50','2.75','3.00','5.00','INC','W'];
    if (!in_array($newGrade, $validGrades)) {
        redirectBack("Invalid grade value selected.");
    }

    $found = false;
    foreach ($data['grades'] as &$grade) {
        if ($grade['id'] === $gradeId && $grade['faculty'] === $_SESSION['username']) {
            $grade['grade']   = $newGrade;
            $grade['remarks'] = $newRemarks;
            $grade['updated'] = date('Y-m-d H:i:s');
            $found            = true;
            break;
        }
    }

    if (!$found) {
        redirectBack("Grade record not found or permission denied.");
    }

    file_put_contents($jsonFile, json_encode($data, JSON_PRETTY_PRINT));
    redirectBack("Grade updated successfully.", "success");
}

// --- ADD GRADE ---
$studentUsername = trim($_POST['student_username'] ?? '');
$subject         = trim(htmlspecialchars($_POST['subject'] ?? ''));
$grade           = trim($_POST['grade'] ?? '');
$remarks         = trim(htmlspecialchars($_POST['remarks'] ?? ''));

if (empty($studentUsername) || empty($subject) || empty($grade)) {
    redirectBack("Student username, subject, and grade are required.");
}

$validGrades = ['1.00','1.25','1.50','1.75','2.00','2.25','2.50','2.75','3.00','5.00','INC','W'];
if (!in_array($grade, $validGrades)) {
    redirectBack("Invalid grade value selected.");
}

// Verify student exists and is approved
if (!file_exists($usersFile)) {
    redirectBack("System error: User data not found.");
}
$users    = json_decode(file_get_contents($usersFile), true);
$stuFound = false;
foreach ($users['users'] as $u) {
    if ($u['username'] === $studentUsername && ($u['role'] ?? 'student') === 'student') {
        $stuFound = true;
        break;
    }
}
if (!$stuFound) {
    redirectBack("Student '{$studentUsername}' not found. Ensure the username is correct.");
}

// Check for duplicate grade entry (same student, subject, faculty)
foreach ($data['grades'] as $g) {
    if ($g['student'] === $studentUsername && $g['subject'] === $subject && $g['faculty'] === $_SESSION['username']) {
        redirectBack("A grade for '{$studentUsername}' in '{$subject}' already exists. Use the Edit option to update it.");
    }
}

// Generate ID
$maxId = 0;
foreach ($data['grades'] as $g) {
    if ($g['id'] > $maxId) $maxId = $g['id'];
}

$data['grades'][] = [
    'id'      => $maxId + 1,
    'student' => $studentUsername,
    'subject' => $subject,
    'grade'   => $grade,
    'remarks' => $remarks,
    'faculty' => $_SESSION['username'],
    'date'    => date('Y-m-d H:i:s')
];

if (file_put_contents($jsonFile, json_encode($data, JSON_PRETTY_PRINT))) {
    redirectBack("Grade added for student '{$studentUsername}'.", "success");
} else {
    redirectBack("System error: Failed to save grade. Check file permissions.");
}
?>