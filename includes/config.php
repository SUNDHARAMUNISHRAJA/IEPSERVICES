<?php
// ============================================================
// IEP - Database Configuration
// ============================================================

define('DB_HOST', 'localhost');
define('DB_USER', 'root');       
define('DB_PASS', '');           
define('DB_NAME', 'iep_db');
define('DB_CHARSET', 'utf8mb4');

// Site Configuration
define('SITE_NAME', 'Integrated Engineers Point');
define('SITE_PHONE', '+91 8925025255');
define('SITE_EMAIL', 'admin.integratedengineerspoint@gmail.com');
define('SITE_ADDRESS', 'Chennai, Tamilnadu, India');
define('SITE_HOURS', 'Mon - Sat: 9:00 AM – 6:00 PM');

function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            http_response_code(500);
            die(json_encode(['success' => false, 'message' => 'Database connection failed.']));
        }
    }
    return $pdo;
}

function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

function jsonResponse($success, $message, $data = []) {
    header('Content-Type: application/json');
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $data));
    exit;
}
?>
