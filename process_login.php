<?php
session_start();
require 'cryptograph_process.php';

// Safely attempt to load PHPMailer
$mailerAvailable = false;
if (file_exists('vendor/autoload.php')) {
    require 'vendor/autoload.php';
    if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        $mailerAvailable = true;
    }
}

function has_internet() {
    $connected = @fsockopen("www.google.com", 80, $errno, $errstr, 3);
    if ($connected) {
        fclose($connected);
        return true;
    }
    return false;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: login.php");
    exit();
}

$inputUser = trim($_POST['username'] ?? '');
$inputPass = $_POST['password'] ?? '';

if (empty($inputUser) || empty($inputPass)) {
    $_SESSION['login_error'] = "Username and password are required.";
    header("Location: login.php");
    exit();
}

$jsonFile = "users.json";

if (!file_exists($jsonFile)) {
    $_SESSION['login_error'] = "System error: User data not found. Contact administrator.";
    header("Location: login.php");
    exit();
}

$jsonData = file_get_contents($jsonFile);
$data     = json_decode($jsonData, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    $_SESSION['login_error'] = "System error: Corrupted user data. Contact administrator.";
    header("Location: login.php");
    exit();
}

$found         = false;
$matchedIndex  = -1;

// FIX 1: Find user by index (no reference) to avoid foreach reference corruption
foreach ($data['users'] as $index => $user) {
    if ($user['username'] === $inputUser && password_verify($inputPass, $user['password'])) {
        $found        = true;
        $matchedIndex = $index;
        break;
    }
}

if (!$found) {
    $_SESSION['login_error'] = "Invalid username or password.";
    header("Location: login.php");
    exit();
}

// Work directly on the array index from here
$userRole   = $data['users'][$matchedIndex]['role']   ?? 'student';
$userStatus = $data['users'][$matchedIndex]['status'] ?? ($userRole === 'student' ? 'pending' : 'approved');

// --- STATUS CHECK for students ---
if ($userRole === 'student') {
    if ($userStatus === 'pending') {
        $_SESSION['login_error'] = "Your account is pending administrator approval. Please wait for confirmation.";
        header("Location: login.php");
        exit();
    }
    if ($userStatus === 'declined') {
        $_SESSION['login_error'] = "Your registration has been declined. Please contact the administrator.";
        header("Location: login.php");
        exit();
    }
}

// --- INTERNET CHECK ---
if (!has_internet()) {
    $_SESSION['login_error'] = "No internet connection detected. An OTP is required to log in. Please check your connection and try again.";
    header("Location: login.php");
    exit();
}

// --- MAILER CHECK (before generating OTP) ---
if (!$mailerAvailable) {
    $_SESSION['login_error'] = "Email system unavailable. PHPMailer not installed. Run: composer require phpmailer/phpmailer";
    header("Location: login.php");
    exit();
}

// --- DECRYPT EMAIL (before generating OTP) ---
$decryptedEmail = decryptData($data['users'][$matchedIndex]['email']);
if (!$decryptedEmail || !filter_var($decryptedEmail, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['login_error'] = "System error: Invalid email on record. Contact administrator.";
    header("Location: login.php");
    exit();
}

// --- GENERATE OTP ---
// FIX 2: Cast to string immediately — otp_verification_process.php uses hash_equals()
//         which requires both sides to be the same type (string). Storing as int caused
//         the comparison to always fail and OTP verification to never pass.
$otp = (string) rand(100000, 999999);

// --- SEND EMAIL FIRST, then save OTP only if mail succeeds ---
// FIX 3: Removed $mail->setTimeout() — that method does not exist in PHPMailer
//         and caused a fatal error, preventing any login from completing.
$mail = new \PHPMailer\PHPMailer\PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'themastero00741@gmail.com';
    $mail->Password   = 'grvm jtcu goxt daqu';
    $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    $mail->setFrom('themastero00741@gmail.com', 'University Portal');
    $mail->addAddress($decryptedEmail);
    $mail->isHTML(false);
    $mail->Subject = "Your University Portal Login OTP";
    $mail->Body    = "Hello {$inputUser},\n\nYour one-time login code is: {$otp}\n\nThis code expires in 5 minutes.\n\nIf you did not request this, please ignore this email.";
    $mail->send();

} catch (\PHPMailer\PHPMailer\Exception $e) {
    $_SESSION['login_error'] = "Failed to send OTP email. Please try again. (Error: " . $mail->ErrorInfo . ")";
    header("Location: login.php");
    exit();
}

// --- SAVE OTP TO FILE only after email is confirmed sent ---
// FIX 4: OTP is now saved AFTER successful email delivery, not before.
//         Previously, a failed email still left a stale OTP written to users.json.
$data['users'][$matchedIndex]['otp']        = $otp;
$data['users'][$matchedIndex]['otp_expiry'] = time() + 300; // 5 minutes

if (!file_put_contents($jsonFile, json_encode($data, JSON_PRETTY_PRINT))) {
    $_SESSION['login_error'] = "System error: Could not save OTP. Check file permissions.";
    header("Location: login.php");
    exit();
}

// --- STORE TEMP SESSION and redirect to OTP page ---
$_SESSION['temp_user'] = $inputUser;
$_SESSION['temp_role'] = $userRole;

header("Location: otp_verification.php");
exit();
?>