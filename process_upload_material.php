<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'faculty') {
    header("Location: login.php");
    exit();
}

function redirectBack($message, $type = "error") {
    $_SESSION['msg']      = $message;
    $_SESSION['msg_type'] = $type;
    header("Location: dashboard.php?page=upload");
    exit();
}

$jsonFile = "materials.json";

if (!file_exists($jsonFile)) {
    file_put_contents($jsonFile, json_encode(['materials' => []], JSON_PRETTY_PRINT));
}

$data = json_decode(file_get_contents($jsonFile), true);
if (json_last_error() !== JSON_ERROR_NONE) {
    redirectBack("System error: Materials data is corrupted.");
}

$action = $_POST['action'] ?? 'add';

// --- DELETE MATERIAL ---
if ($action === 'delete') {
    $deleteId = (int)($_POST['material_id'] ?? -1);
    $found    = false;

    foreach ($data['materials'] as $key => $mat) {
        if ($mat['id'] === $deleteId && $mat['uploaded_by'] === $_SESSION['username']) {
            unset($data['materials'][$key]);
            $data['materials'] = array_values($data['materials']); // re-index
            $found = true;
            break;
        }
    }

    if (!$found) {
        redirectBack("Material not found or you don't have permission to delete it.");
    }

    if (file_put_contents($jsonFile, json_encode($data, JSON_PRETTY_PRINT))) {
        redirectBack("Material deleted successfully.", "success");
    } else {
        redirectBack("System error: Failed to delete material.");
    }
}

// --- ADD MATERIAL ---
$title       = trim(htmlspecialchars($_POST['title'] ?? ''));
$subject     = trim(htmlspecialchars($_POST['subject'] ?? ''));
$description = trim(htmlspecialchars($_POST['description'] ?? ''));
$content     = trim(htmlspecialchars($_POST['content'] ?? ''));
$link        = trim($_POST['link'] ?? '');

if (empty($title) || empty($subject) || empty($description)) {
    redirectBack("Title, subject, and description are required.");
}

if (!empty($link) && !filter_var($link, FILTER_VALIDATE_URL)) {
    redirectBack("Please enter a valid URL for the resource link.");
}

// Generate unique ID
$maxId = 0;
foreach ($data['materials'] as $mat) {
    if ($mat['id'] > $maxId) $maxId = $mat['id'];
}

$data['materials'][] = [
    'id'          => $maxId + 1,
    'title'       => $title,
    'subject'     => $subject,
    'description' => $description,
    'content'     => $content,
    'link'        => $link,
    'uploaded_by' => $_SESSION['username'],
    'upload_date' => date('Y-m-d H:i:s')
];

if (file_put_contents($jsonFile, json_encode($data, JSON_PRETTY_PRINT))) {
    redirectBack("Material '{$title}' uploaded successfully.", "success");
} else {
    redirectBack("System error: Failed to save material. Check file permissions.");
}
?>