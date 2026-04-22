<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit();
}

function redirectBack($message, $type = "error") {
    $_SESSION['msg']      = $message;
    $_SESSION['msg_type'] = $type;
    header("Location: dashboard.php?page=submit");
    exit();
}

$jsonFile = "assignments.json";

if (!file_exists($jsonFile)) {
    file_put_contents($jsonFile, json_encode(['assignments' => []], JSON_PRETTY_PRINT));
}

$data = json_decode(file_get_contents($jsonFile), true);
if (json_last_error() !== JSON_ERROR_NONE) {
    redirectBack("System error: Assignment data is corrupted.");
}

$title       = trim(htmlspecialchars($_POST['title'] ?? ''));
$subject     = trim(htmlspecialchars($_POST['subject'] ?? ''));
$description = trim(htmlspecialchars($_POST['description'] ?? ''));
$content     = trim(htmlspecialchars($_POST['content'] ?? ''));
$link        = trim($_POST['link'] ?? '');

if (empty($title) || empty($subject) || empty($description)) {
    redirectBack("Title, subject, and description are required.");
}

if (empty($content) && empty($link)) {
    redirectBack("Please provide either your assignment content or a submission link.");
}

if (!empty($link) && !filter_var($link, FILTER_VALIDATE_URL)) {
    redirectBack("Please enter a valid URL for your submission link.");
}

// Check for duplicate submission (same student, title, subject)
foreach ($data['assignments'] as $a) {
    if ($a['student'] === $_SESSION['username'] && strtolower($a['title']) === strtolower($title) && strtolower($a['subject']) === strtolower($subject)) {
        redirectBack("You have already submitted an assignment titled '{$title}' for '{$subject}'. Please use a different title or check your submissions.");
    }
}

// Generate ID
$maxId = 0;
foreach ($data['assignments'] as $a) {
    if ($a['id'] > $maxId) $maxId = $a['id'];
}

$data['assignments'][] = [
    'id'             => $maxId + 1,
    'student'        => $_SESSION['username'],
    'title'          => $title,
    'subject'        => $subject,
    'description'    => $description,
    'content'        => $content,
    'link'           => $link,
    'submitted_date' => date('Y-m-d H:i:s'),
    'status'         => 'submitted'
];

if (file_put_contents($jsonFile, json_encode($data, JSON_PRETTY_PRINT))) {
    redirectBack("Assignment '{$title}' submitted successfully!", "success");
} else {
    redirectBack("System error: Failed to save your submission. Check file permissions.");
}
?>