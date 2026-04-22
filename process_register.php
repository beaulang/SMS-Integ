<?php
session_start();
require 'cryptograph_process.php';

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
    header("Location: register.php");
    exit();
}

// Sanitize inputs
$rawPhone        = trim($_POST['phonenumber'] ?? '');
$fullname        = trim($_POST['fullname'] ?? '');
$newUser         = trim($_POST['username'] ?? '');
$newEmail        = trim($_POST['email'] ?? '');
$newPassword     = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

function redirectWithError($message) {
    $_SESSION['msg']      = $message;
    $_SESSION['msg_type'] = "error";
    header("Location: register.php");
    exit();
}

// 1. Check for empty fields
if (empty($fullname) || empty($rawPhone) || empty($newUser) || empty($newEmail) || empty($newPassword)) {
    redirectWithError("All fields are required.");
}

// 2. Full Name: Letters, spaces, hyphens, and apostrophes. Length: 4-16 chars.
if (!preg_match("/^[a-zA-Z\s\-\']{4,16}$/", $fullname)) {
    redirectWithError("Full name must be between 4 and 16 characters and contain only letters.");
}

// 3. Phone Number: Exactly 11 digits
if (!preg_match("/^[0-9]{11}$/", $rawPhone)) {
    redirectWithError("Phone number must be exactly 11 digits.");
}

// 4. Email: Standard format validation
if (!preg_match("/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/", $newEmail)) {
    redirectWithError("Please enter a valid email address.");
}

// 5. Username: 3-16 chars, alphanumeric and underscores only
if (!preg_match("/^[a-zA-Z0-9_]{3,16}$/", $newUser)) {
    redirectWithError("Username must be 3–16 characters (letters, numbers, underscores).");
}

// 6. Password Complexity: 8-16 chars, 1 Upper, 1 Lower, 1 Number, 1 Special
if (!preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,16}$/", $newPassword)) {
    redirectWithError("Password must be 8–16 characters with an uppercase, lowercase, number, and special character.");
}

// 7. Password Match
if ($newPassword !== $confirmPassword) {
    redirectWithError("Passwords do not match.");
}

// 8. Check for duplicate username
foreach ($data['users'] as $user) {
    if (isset($user['username']) && strcasecmp($user['username'], $newUser) === 0) {
        redirectWithError("Username '{$newUser}' is already taken.");
    }
}

// 9. Encryption and Hashing
$encryptedFullname = encryptData($fullname);
$encryptedPhone    = encryptData($rawPhone);
$encryptedEmail    = encryptData($newEmail);
$hashedPassword    = password_hash($newPassword, PASSWORD_DEFAULT);

if (!$encryptedFullname || !$encryptedPhone || !$encryptedEmail) {
    redirectWithError("System error: Encryption failed.");
}

// 10. Prepare Data
$data['users'][] = [
    'fullname'    => $encryptedFullname,
    'phonenumber' => $encryptedPhone,
    'username'    => $newUser,
    'email'       => $encryptedEmail,
    'password'    => $hashedPassword,
    'role'        => 'student',
    'status'      => 'pending',
    'registered'  => date('Y-m-d H:i:s')
];

// 11. Atomic Write with File Locking
$fp = fopen($jsonFile, 'w');
if (flock($fp, LOCK_EX)) {
    if (fwrite($fp, json_encode($data, JSON_PRETTY_PRINT))) {
        $_SESSION['msg']      = "Registration successful! Pending approval.";
        $_SESSION['msg_type'] = "success";
    } else {
        $_SESSION['msg']      = "System error: Could not save data.";
        $_SESSION['msg_type'] = "error";
    }
    flock($fp, LOCK_UN);
} else {
    $_SESSION['msg'] = "System busy, please try again.";
    $_SESSION['msg_type'] = "error";
}
fclose($fp);

header("Location: register.php");
exit();
?>