<?php
session_start();
require 'cryptograph_process.php';

// Only admins can access this script
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: dashboard.php?page=register_staff");
    exit();
}

$jsonFile = "users.json";

// Initialize file if it doesn't exist
if (!file_exists($jsonFile)) {
    file_put_contents($jsonFile, json_encode(['users' => []], JSON_PRETTY_PRINT));
}

$jsonData = file_get_contents($jsonFile);
$data     = json_decode($jsonData, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    $_SESSION['msg']      = "System error: User data is corrupted.";
    $_SESSION['msg_type'] = "error";
    header("Location: dashboard.php?page=register_staff");
    exit();
}

// Redirect helper — always goes back to the register_staff page inside the dashboard
function redirectWithError($message) {
    $_SESSION['msg']      = $message;
    $_SESSION['msg_type'] = "error";
    header("Location: dashboard.php?page=register_staff");
    exit();
}

// Collect and trim inputs
$fullname    = trim($_POST['fullname']    ?? '');
$username    = trim($_POST['username']    ?? '');
$role        = trim($_POST['role']        ?? '');
$email       = trim($_POST['email']       ?? '');
$phonenumber = trim($_POST['phonenumber'] ?? '');
$password    = $_POST['password']         ?? '';

// 1. Required fields
if (empty($fullname) || empty($username) || empty($role) || empty($email) || empty($phonenumber) || empty($password)) {
    redirectWithError("All fields are required.");
}

// 2. Role must be faculty or admin (admins cannot create student accounts via this form)
$allowedRoles = ['faculty', 'admin'];
if (!in_array($role, $allowedRoles)) {
    redirectWithError("Invalid role selected. Only 'faculty' or 'admin' are allowed.");
}

// 3. Full name: letters, spaces, hyphens, apostrophes — 4 to 50 chars
if (!preg_match("/^[a-zA-Z\s\-\']{4,50}$/", $fullname)) {
    redirectWithError("Full name must be 4–50 characters and contain only letters, spaces, hyphens, or apostrophes.");
}

// 4. Username: 3–16 chars, alphanumeric + underscores
if (!preg_match("/^[a-zA-Z0-9_]{3,16}$/", $username)) {
    redirectWithError("Username must be 3–16 characters (letters, numbers, underscores only).");
}

// 5. Email: standard format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirectWithError("Please enter a valid email address.");
}

// 6. Phone: exactly 11 digits
if (!preg_match("/^[0-9]{11}$/", $phonenumber)) {
    redirectWithError("Phone number must be exactly 11 digits.");
}

// 7. Password complexity: 8–16 chars, uppercase, lowercase, number, special char
if (!preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,16}$/", $password)) {
    redirectWithError("Password must be 8–16 characters and include an uppercase letter, lowercase letter, number, and special character.");
}

// 8. Check for duplicate username (case-insensitive)
foreach ($data['users'] as $u) {
    if (isset($u['username']) && strcasecmp($u['username'], $username) === 0) {
        redirectWithError("Username '{$username}' is already taken. Please choose a different one.");
    }
}

// 9. Encrypt sensitive fields and hash the password
$encryptedFullname = encryptData($fullname);
$encryptedEmail    = encryptData($email);
$encryptedPhone    = encryptData($phonenumber);
$hashedPassword    = password_hash($password, PASSWORD_DEFAULT);

if (!$encryptedFullname || !$encryptedEmail || !$encryptedPhone) {
    redirectWithError("System error: Encryption failed. Contact the system administrator.");
}

// 10. Build the new user record
//     Staff/admin accounts are immediately active — no 'pending' status needed.
$newUser = [
    'fullname'    => $encryptedFullname,
    'phonenumber' => $encryptedPhone,
    'username'    => $username,
    'email'       => $encryptedEmail,
    'password'    => $hashedPassword,
    'role'        => $role,
    'registered'  => date('Y-m-d H:i:s'),
    // No 'status' field — faculty/admin don't go through approval flow.
    // process_login.php checks: status defaults to 'approved' for non-student roles.
];

$data['users'][] = $newUser;

// 11. Atomic write with exclusive file lock
$fp = fopen($jsonFile, 'w');
if ($fp === false) {
    redirectWithError("System error: Could not open user data file. Check file permissions.");
}

if (flock($fp, LOCK_EX)) {
    $written = fwrite($fp, json_encode($data, JSON_PRETTY_PRINT));
    flock($fp, LOCK_UN);
    fclose($fp);

    if ($written !== false) {
        $_SESSION['msg']      = ucfirst($role) . " account '{$username}' created successfully. They can log in immediately.";
        $_SESSION['msg_type'] = "success";
    } else {
        $_SESSION['msg']      = "System error: Could not write to user data file.";
        $_SESSION['msg_type'] = "error";
    }
} else {
    fclose($fp);
    $_SESSION['msg']      = "System is busy. Please try again in a moment.";
    $_SESSION['msg_type'] = "error";
}

header("Location: dashboard.php?page=register_staff");
exit();
?>