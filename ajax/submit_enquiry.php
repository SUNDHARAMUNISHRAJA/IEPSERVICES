<?php
// ============================================================
// IEP - AJAX: Submit Enquiry
// ============================================================
header('Content-Type: application/json');
require_once '../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Invalid request method.');
}

$full_name   = sanitize($_POST['full_name'] ?? '');
$phone       = sanitize($_POST['phone'] ?? '');
$email       = sanitize($_POST['email'] ?? '');
$service     = sanitize($_POST['service'] ?? '');
$requirement = sanitize($_POST['requirement'] ?? '');

// Validation
if (empty($full_name) || strlen($full_name) < 2) {
    jsonResponse(false, 'Please enter your full name (min 2 characters).');
}
if (empty($phone) || !preg_match('/^[6-9]\d{9}$/', preg_replace('/\s+/', '', $phone))) {
    jsonResponse(false, 'Please enter a valid 10-digit Indian mobile number.');
}
if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    jsonResponse(false, 'Please enter a valid email address.');
}
if (empty($service)) {
    jsonResponse(false, 'Please select a service.');
}
if (empty($requirement) || strlen($requirement) < 10) {
    jsonResponse(false, 'Please describe your requirement (min 10 characters).');
}

try {
    $db = getDB();
    $stmt = $db->prepare("INSERT INTO enquiries (full_name, phone, email, service, requirement) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$full_name, $phone, $email, $service, $requirement]);
    jsonResponse(true, 'Thank you! Your enquiry has been submitted successfully. We will contact you shortly.');
} catch (PDOException $e) {
    jsonResponse(false, 'Failed to submit enquiry. Please try again or call us directly.');
}
?>
