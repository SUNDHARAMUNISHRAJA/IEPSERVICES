<?php
// ============================================================
// IEP - AJAX: Submit Feedback
// ============================================================
header('Content-Type: application/json');
require_once '../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Invalid request method.');
}

$customer_name = sanitize($_POST['customer_name'] ?? '');
$phone         = sanitize($_POST['phone'] ?? '');
$email         = sanitize($_POST['email'] ?? '');
$service_used  = sanitize($_POST['service_used'] ?? '');
$rating        = intval($_POST['rating'] ?? 0);
$message       = sanitize($_POST['message'] ?? '');

// Validation
if (empty($customer_name) || strlen($customer_name) < 2) {
    jsonResponse(false, 'Please enter your full name.');
}
if (!empty($phone) && !preg_match('/^[6-9]\d{9}$/', preg_replace('/\s+/', '', $phone))) {
    jsonResponse(false, 'Please enter a valid 10-digit mobile number.');
}
if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    jsonResponse(false, 'Please enter a valid email address.');
}
if ($rating < 1 || $rating > 5) {
    jsonResponse(false, 'Please select a rating between 1 and 5.');
}
if (empty($message) || strlen($message) < 20) {
    jsonResponse(false, 'Please share your feedback (min 20 characters).');
}

try {
    $db = getDB();
    $stmt = $db->prepare("INSERT INTO feedback (customer_name, phone, email, service_used, rating, message) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$customer_name, $phone, $email, $service_used, $rating, $message]);
    jsonResponse(true, 'Thank you for your valuable feedback! It will be published after review.');
} catch (PDOException $e) {
    jsonResponse(false, 'Failed to submit feedback. Please try again.');
}
?>
