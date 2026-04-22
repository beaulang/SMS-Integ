<?php
session_start();

// Only admins can perform this action
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: dashboard.php?page=manage_users");
    exit();
}

$jsonFile = "users.json";
$targetUser = trim($_POST['username'] ?? '');
$action     = trim($_POST['action'] ?? ''); // 'approved' or 'declined'

$allowedActions = ['approved', 'declined'];
if (!in_array($action, $allowedActions) || empty($targetUser)) {
    $_SESSION['msg']      = "Invalid action or username.";
    $_SESSION['msg_type'] = "error";
    header("Location: dashboard.php?page=manage_users");
    exit();
}

if (!file_exists($jsonFile)) {
    $_SESSION['msg']      = "System error: User data not found.";
    $_SESSION['msg_type'] = "error";
    header("Location: dashboard.php?page=manage_users");
    exit();
}

$data = json_decode(file_get_contents($jsonFile), true);

if (json_last_error() !== JSON_ERROR_NONE) {
    $_SESSION['msg']      = "System error: Corrupted user data.";
    $_SESSION['msg_type'] = "error";
    header("Location: dashboard.php?page=manage_users");
    exit();
}

$updated = false;
foreach ($data['users'] as &$user) {
    if ($user['username'] === $targetUser) {
        // Only students can be approved/declined this way
        if (($user['role'] ?? 'student') !== 'student') {
            $_SESSION['msg']      = "Only student accounts can be approved or declined.";
            $_SESSION['msg_type'] = "error";
            header("Location: dashboard.php?page=manage_users");
            exit();
        }
        $user['status'] = $action;
        $updated = true;
        break;
    }
}

if (!$updated) {
    $_SESSION['msg']      = "User '{$targetUser}' not found.";
    $_SESSION['msg_type'] = "error";
    header("Location: dashboard.php?page=manage_users");
    exit();
}

if (file_put_contents($jsonFile, json_encode($data, JSON_PRETTY_PRINT))) {
    $verb = ($action === 'approved') ? 'approved' : 'declined';
    $_SESSION['msg']      = "Student '{$targetUser}' has been {$verb} successfully.";
    $_SESSION['msg_type'] = ($action === 'approved') ? "success" : "warning";
} else {
    $_SESSION['msg']      = "System error: Failed to update user status. Check file permissions.";
    $_SESSION['msg_type'] = "error";
}

header("Location: dashboard.php?page=manage_users");
exit();
?>