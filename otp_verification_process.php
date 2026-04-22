<?php
session_start();

// Check if the temporary session exists to prevent direct access to this script
if (!isset($_SESSION['temp_user'])) {
    $_SESSION['login_error'] = "Session expired. Please log in again.";
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: otp_verification.php");
    exit();
}

$jsonFile = "users.json";

if (!file_exists($jsonFile)) {
    $_SESSION['otp_error'] = "System error: User data not found.";
    header("Location: otp_verification.php");
    exit();
}

$jsonData = file_get_contents($jsonFile);
$data     = json_decode($jsonData, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    $_SESSION['otp_error'] = "System error: Corrupted user data.";
    header("Location: otp_verification.php");
    exit();
}

$inputOTP = trim($_POST['otp'] ?? '');
$username = $_SESSION['temp_user'];
$role     = $_SESSION['temp_role'];

// Validate OTP input is numeric and 6 digits
if (!ctype_digit($inputOTP) || strlen($inputOTP) !== 6) {
    $_SESSION['otp_error'] = "OTP must be a 6-digit number.";
    header("Location: otp_verification.php");
    exit();
}

$userFound = false;
foreach ($data['users'] as $user) {
    if ($user['username'] === $username) {
        $userFound = true;

        // Check if OTP fields exist
        if (!isset($user['otp']) || !isset($user['otp_expiry'])) {
            $_SESSION['otp_error'] = "No OTP found. Please login again to request a new one.";
            header("Location: login.php");
            exit();
        }

        // Check for expiration
        if (time() > $user['otp_expiry']) {
            $_SESSION['otp_error'] = "OTP has expired. Please log in again to receive a new code.";
            header("Location: otp_verification.php");
            exit();
        }

        // Verify OTP using hash_equals to prevent timing attacks
        if (hash_equals((string)$user['otp'], $inputOTP)) {
            $_SESSION['username'] = $username;
            $_SESSION['role']     = $role;

            // Clean up temporary session data
            unset($_SESSION['temp_user']);
            unset($_SESSION['temp_role']);

            header("Location: dashboard.php");
            exit();
        } else {
            $_SESSION['otp_error'] = "Invalid OTP. Please try again.";
            header("Location: otp_verification.php");
            exit();
        }
    }
}

if (!$userFound) {
    $_SESSION['otp_error'] = "User session not found. Please log in again.";
    header("Location: login.php");
    exit();
}
?>