<?php
session_start();
require_once '../includes/config.php';

if (empty($_SESSION['iep_admin'])) {
    jsonResponse(false, 'Unauthorized.');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Invalid request.');
}

$current_password = $_POST['current_password'] ?? '';
$new_password     = $_POST['new_password'] ?? '';

if (empty($current_password) || empty($new_password)) {
    jsonResponse(false, 'All fields are required.');
}

if (strlen($new_password) < 6) {
    jsonResponse(false, 'New password must be at least 6 characters.');
}

try {
    $db   = getDB();
    $stmt = $db->prepare("SELECT password_hash FROM admin_users WHERE id = ?");
    $stmt->execute([$_SESSION['iep_admin']]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($current_password, $user['password_hash'])) {
        jsonResponse(false, 'Current password is incorrect.');
    }

    $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
    $db->prepare("UPDATE admin_users SET password_hash = ? WHERE id = ?")
       ->execute([$new_hash, $_SESSION['iep_admin']]);

    jsonResponse(true, 'Password updated successfully!');

} catch (PDOException $e) {
    jsonResponse(false, 'Database error. Please try again.');
}
?>
