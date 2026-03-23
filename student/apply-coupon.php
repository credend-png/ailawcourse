<?php
require_once '../config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$code     = strtoupper(sanitize($input['code'] ?? ''));
$courseId = intval($input['course_id'] ?? 0);
$amount   = floatval($input['amount'] ?? 0);

if (empty($code) || $amount <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

$db = getDB();
$stmt = $db->prepare("SELECT * FROM coupons WHERE code = ? AND status = 'active' AND (course_id IS NULL OR course_id = ?) AND (valid_from IS NULL OR valid_from <= CURDATE()) AND (valid_until IS NULL OR valid_until >= CURDATE()) AND (usage_limit IS NULL OR used_count < usage_limit)");
$stmt->execute([$code, $courseId]);
$coupon = $stmt->fetch();

if (!$coupon) {
    echo json_encode(['success' => false, 'message' => 'Invalid or expired coupon code.']);
    exit;
}

if ($amount < $coupon['min_amount']) {
    echo json_encode(['success' => false, 'message' => 'Minimum order amount of ₹' . number_format($coupon['min_amount'], 2) . ' required for this coupon.']);
    exit;
}

$discount = 0;
if ($coupon['discount_type'] === 'percentage') {
    $discount = ($amount * $coupon['discount_value']) / 100;
    if ($coupon['max_discount']) {
        $discount = min($discount, $coupon['max_discount']);
    }
} else {
    $discount = min($coupon['discount_value'], $amount);
}

$discount = round($discount, 2);
$finalAmount = $amount - $discount;

echo json_encode([
    'success'      => true,
    'discount'     => $discount,
    'final_amount' => $finalAmount,
    'message'      => 'Coupon applied! You save ₹' . number_format($discount, 2)
]);
