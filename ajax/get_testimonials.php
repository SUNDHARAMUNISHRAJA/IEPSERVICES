<?php
// ============================================================
// IEP - AJAX: Get Approved Testimonials
// ============================================================
header('Content-Type: application/json');
require_once '../includes/config.php';

try {
    $db = getDB();
    $stmt = $db->prepare("SELECT customer_name, service_used, rating, message, created_at FROM feedback WHERE is_approved = 1 ORDER BY created_at DESC LIMIT 9");
    $stmt->execute();
    $testimonials = $stmt->fetchAll();
    echo json_encode(['success' => true, 'data' => $testimonials]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'data' => []]);
}
?>
