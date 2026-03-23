<?php
/**
 * LexLearnAI Configuration File
 * Edit this file with your hosting details before deployment
 */

// ============================================================
// DATABASE CONFIGURATION
// ============================================================
define('DB_HOST', 'localhost');
define('DB_NAME', 'your_db_name');       // Change this
define('DB_USER', 'your_db_user');       // Change this
define('DB_PASS', 'your_db_password');   // Change this
define('DB_CHARSET', 'utf8mb4');

// ============================================================
// SITE CONFIGURATION
// ============================================================
define('SITE_URL', 'https://yourdomain.com');   // Change this - no trailing slash
define('SITE_NAME', 'LexLearnAI');
define('SITE_TAGLINE', 'Legal AI Education for the Next Generation of Lawyers');
define('SITE_EMAIL', 'info@lexlearnai.com');

// ============================================================
// PATH CONFIGURATION
// ============================================================
define('ROOT_PATH', dirname(__FILE__));
define('UPLOAD_PATH', ROOT_PATH . '/uploads');
define('UPLOAD_URL', SITE_URL . '/uploads');
define('ASSETS_URL', SITE_URL . '/assets');
define('ADMIN_URL', SITE_URL . '/admin');
define('STUDENT_URL', SITE_URL . '/student');
define('AUTH_URL', SITE_URL . '/auth');

// ============================================================
// PAYU PAYMENT GATEWAY
// ============================================================
define('PAYU_MERCHANT_KEY', 'YOUR_PAYU_KEY');   // From PayU Dashboard
define('PAYU_MERCHANT_SALT', 'YOUR_PAYU_SALT'); // From PayU Dashboard
define('PAYU_MODE', 'test');  // 'test' or 'live'

if (PAYU_MODE === 'live') {
    define('PAYU_URL', 'https://secure.payu.in/_payment');
} else {
    define('PAYU_URL', 'https://test.payu.in/_payment');
}

// ============================================================
// EMAIL (SMTP) CONFIGURATION
// ============================================================
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'your@gmail.com');
define('SMTP_PASS', 'your_app_password');
define('SMTP_FROM_NAME', 'LexLearnAI');
define('SMTP_FROM_EMAIL', 'noreply@lexlearnai.com');

// ============================================================
// SESSION & SECURITY
// ============================================================
define('SESSION_NAME', 'lexlearnai_session');
define('CSRF_TOKEN_NAME', '_csrf_token');
define('HASH_ALGO', PASSWORD_BCRYPT);
define('HASH_COST', 12);
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_MINUTES', 15);

// ============================================================
// TIMEZONE
// ============================================================
date_default_timezone_set('Asia/Kolkata');

// ============================================================
// ERROR REPORTING (set to 0 in production)
// ============================================================
error_reporting(E_ALL);
ini_set('display_errors', 1); // Set to 0 in production

// ============================================================
// DATABASE CONNECTION
// ============================================================
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
            die('Database connection failed: ' . $e->getMessage());
        }
    }
    return $pdo;
}

// ============================================================
// SESSION START
// ============================================================
if (session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_set_cookie_params([
        'lifetime' => 86400,
        'path'     => '/',
        'secure'   => isset($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

// ============================================================
// CSRF HELPERS
// ============================================================
function generateCsrfToken() {
    if (empty($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

function verifyCsrfToken($token) {
    return isset($_SESSION[CSRF_TOKEN_NAME]) && hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
}

function csrfField() {
    return '<input type="hidden" name="' . CSRF_TOKEN_NAME . '" value="' . generateCsrfToken() . '">';
}

// ============================================================
// SECURITY HELPERS
// ============================================================
function sanitize($input) {
    if (is_array($input)) {
        return array_map('sanitize', $input);
    }
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

function xss($data) {
    return htmlspecialchars($data ?? '', ENT_QUOTES, 'UTF-8');
}

function redirect($url) {
    header("Location: $url");
    exit;
}

function slugify($text) {
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/[\s-]+/', '-', $text);
    return trim($text, '-');
}

function formatCurrency($amount) {
    return '₹' . number_format($amount, 2);
}

function formatDate($date) {
    return date('d M Y', strtotime($date));
}

function formatDateTime($datetime) {
    return date('d M Y, h:i A', strtotime($datetime));
}

function generateVerificationId() {
    return 'LLA-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8)) . '-' . date('Y');
}

function generateTxnId() {
    return 'TXN' . time() . rand(1000, 9999);
}

// ============================================================
// AUTH HELPERS
// ============================================================
function isStudentLoggedIn() {
    return isset($_SESSION['student_id']) && !empty($_SESSION['student_id']);
}

function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

function requireStudentLogin() {
    if (!isStudentLoggedIn()) {
        redirect(SITE_URL . '/auth/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    }
}

function requireAdminLogin() {
    if (!isAdminLoggedIn()) {
        redirect(SITE_URL . '/admin/login.php');
    }
}

function getCurrentStudent() {
    if (!isStudentLoggedIn()) return null;
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM students WHERE id = ? AND status = 'active'");
    $stmt->execute([$_SESSION['student_id']]);
    return $stmt->fetch();
}

function getCurrentAdmin() {
    if (!isAdminLoggedIn()) return null;
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM admins WHERE id = ?");
    $stmt->execute([$_SESSION['admin_id']]);
    return $stmt->fetch();
}

// ============================================================
// SETTINGS HELPER
// ============================================================
function getSetting($key, $default = '') {
    $db = getDB();
    $stmt = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
    $stmt->execute([$key]);
    $row = $stmt->fetch();
    return $row ? $row['setting_value'] : $default;
}

// ============================================================
// EMAIL FUNCTION (PHPMailer-compatible fallback using mail())
// ============================================================
function sendEmail($to, $subject, $body, $isHtml = true) {
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: " . ($isHtml ? "text/html" : "text/plain") . "; charset=UTF-8\r\n";
    $headers .= "From: " . SMTP_FROM_NAME . " <" . SMTP_FROM_EMAIL . ">\r\n";
    $headers .= "Reply-To: " . SMTP_FROM_EMAIL . "\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();
    return mail($to, $subject, $body, $headers);
}

// ============================================================
// PAYU HASH GENERATION
// ============================================================
function generatePayUHash($key, $txnid, $amount, $productinfo, $firstname, $email, $udf1='', $udf2='', $udf3='', $udf4='', $udf5='', $salt='') {
    $hashString = $key . '|' . $txnid . '|' . $amount . '|' . $productinfo . '|' . $firstname . '|' . $email . '|' . $udf1 . '|' . $udf2 . '|' . $udf3 . '|' . $udf4 . '|' . $udf5 . '||||||' . $salt;
    return strtolower(hash('sha512', $hashString));
}

function verifyPayUHash($hash, $status, $key, $txnid, $amount, $productinfo, $firstname, $email, $udf1='', $udf2='', $udf3='', $udf4='', $udf5='', $salt='') {
    $hashString = $salt . '|' . $status . '||||||' . $udf5 . '|' . $udf4 . '|' . $udf3 . '|' . $udf2 . '|' . $udf1 . '|' . $email . '|' . $firstname . '|' . $productinfo . '|' . $amount . '|' . $txnid . '|' . $key;
    $generatedHash = strtolower(hash('sha512', $hashString));
    return hash_equals($generatedHash, strtolower($hash));
}
