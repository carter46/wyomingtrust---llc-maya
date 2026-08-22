<?php

require_once __DIR__ . '/../helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json(['success' => false, 'message' => 'Method not allowed'], 405);
}

$userId = require_user_auth();
$trustId = isset($_POST['trust_id']) ? (int) $_POST['trust_id'] : 0;

if ($trustId <= 0) {
    send_json(['success' => false, 'message' => 'Invalid trust ID'], 400);
}

if (!isset($_FILES['document']) || !is_uploaded_file($_FILES['document']['tmp_name'])) {
    send_json(['success' => false, 'message' => 'No document uploaded'], 400);
}

$file = $_FILES['document'];
if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
    send_json(['success' => false, 'message' => 'Upload failed'], 400);
}

$maxSize = 10 * 1024 * 1024;
if (($file['size'] ?? 0) > $maxSize) {
    send_json(['success' => false, 'message' => 'File must be 10MB or smaller'], 400);
}

$allowed = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];
$ext = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
if (!in_array($ext, $allowed, true)) {
    send_json(['success' => false, 'message' => 'Allowed file types: PDF, JPG, PNG, DOC, DOCX'], 400);
}

$db = getDatabase();
$stmt = $db->prepare('SELECT id FROM user_trusts WHERE id = :id AND user_id = :user_id LIMIT 1');
$stmt->execute([':id' => $trustId, ':user_id' => $userId]);
if (!$stmt->fetch()) {
    send_json(['success' => false, 'message' => 'Trust not found'], 404);
}

$uploadDir = dirname(__DIR__, 2) . '/uploads/trust-assets/' . $userId . '/' . $trustId;
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$safeName = preg_replace('/[^a-zA-Z0-9._-]/', '_', pathinfo($file['name'], PATHINFO_FILENAME));
$filename = $safeName . '_' . time() . '.' . $ext;
$filepath = $uploadDir . '/' . $filename;

if (!move_uploaded_file($file['tmp_name'], $filepath)) {
    send_json(['success' => false, 'message' => 'Failed to save file'], 500);
}

$relativePath = 'uploads/trust-assets/' . $userId . '/' . $trustId . '/' . $filename;

send_json([
    'success' => true,
    'document' => [
        'filename' => $file['name'],
        'path' => $relativePath,
    ],
]);
