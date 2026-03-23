<?php
require_once '../config.php';

// PayU sends POST to success URL
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(SITE_URL . '/student/dashboard.php');
}

$db = getDB();

$txnid   = sanitize($_POST['txnid'] ?? '');
$status  = sanitize($_POST['status'] ?? '');
$hash    = sanitize($_POST['hash'] ?? '');
$amount  = sanitize($_POST['amount'] ?? '');
$productinfo = sanitize($_POST['productinfo'] ?? '');
$firstname   = sanitize($_POST['firstname'] ?? '');
$email       = sanitize($_POST['email'] ?? '');
$udf1 = sanitize($_POST['udf1'] ?? ''); // course_id
$udf2 = sanitize($_POST['udf2'] ?? ''); // student_id
$payuTxnid = sanitize($_POST['mihpayid'] ?? '');
$paymentMode = sanitize($_POST['mode'] ?? '');

// Verify hash
$isValidHash = verifyPayUHash($hash, $status, PAYU_MERCHANT_KEY, $txnid, $amount, $productinfo, $firstname, $email, $udf1, $udf2, '', '', '', PAYU_MERCHANT_SALT);

// Get our pending payment record
$stmt = $db->prepare("SELECT * FROM payments WHERE txnid = ? AND status = 'pending'");
$stmt->execute([$txnid]);
$payment = $stmt->fetch();

if (!$payment || !$isValidHash) {
    // Log suspicious attempt
    redirect(SITE_URL . '/student/payment-failure.php?reason=verification_failed');
}

$courseId  = intval($payment['course_id']);
$studentId = intval($payment['student_id']);

if ($status === 'success') {
    // Update payment
    $db->prepare("UPDATE payments SET status='success', payu_txnid=?, payment_mode=?, payu_response=?, updated_at=NOW() WHERE txnid=?")
       ->execute([$payuTxnid, $paymentMode, json_encode($_POST), $txnid]);

    // Update coupon usage if applicable
    if (!empty($payment['coupon_code'])) {
        $db->prepare("UPDATE coupons SET used_count = used_count + 1 WHERE code = ?")->execute([$payment['coupon_code']]);
    }

    // Create enrollment (or update if exists)
    $existing = $db->prepare("SELECT id FROM enrollments WHERE student_id=? AND course_id=?");
    $existing->execute([$studentId, $courseId]);
    if ($existing->fetch()) {
        $db->prepare("UPDATE enrollments SET status='active', access_granted_by='payment' WHERE student_id=? AND course_id=?")
           ->execute([$studentId, $courseId]);
    } else {
        $db->prepare("INSERT INTO enrollments (student_id, course_id, status, access_granted_by) VALUES (?,?,'active','payment')")
           ->execute([$studentId, $courseId]);
    }

    // Send confirmation email
    $student = $db->prepare("SELECT * FROM students WHERE id=?")->execute([$studentId]) ? null : null;
    $st2 = $db->prepare("SELECT * FROM students WHERE id=?"); $st2->execute([$studentId]);
    $student = $st2->fetch();
    $courseInfo = $db->prepare("SELECT * FROM courses WHERE id=?");
    $courseInfo->execute([$courseId]);
    $courseInfo = $courseInfo->fetch();

    if ($student && $courseInfo) {
        $emailBody = "
        <html><body style='font-family:sans-serif;max-width:600px;margin:0 auto;'>
        <div style='background:#0B1D3A;padding:32px;text-align:center;border-radius:12px 12px 0 0;'>
            <h1 style='color:#C9A84C;'>LexLearnAI</h1>
        </div>
        <div style='padding:32px;background:#fff;border:1px solid #E2E6EC;border-top:none;'>
            <p style='color:#1A7F5A;font-size:1.1rem;font-weight:700;'>✓ Payment Successful!</p>
            <p>Dear {$student['full_name']},</p>
            <p>Your enrollment in <strong>{$courseInfo['title']}</strong> has been confirmed.</p>
            <table style='width:100%;border-collapse:collapse;margin:20px 0;font-size:14px;'>
                <tr style='background:#F8F9FB;'><td style='padding:10px;font-weight:600;'>Transaction ID</td><td style='padding:10px;'>{$txnid}</td></tr>
                <tr><td style='padding:10px;font-weight:600;'>Amount Paid</td><td style='padding:10px;'>₹{$amount}</td></tr>
                <tr style='background:#F8F9FB;'><td style='padding:10px;font-weight:600;'>Course</td><td style='padding:10px;'>{$courseInfo['title']}</td></tr>
            </table>
            <a href='" . SITE_URL . "/student/dashboard.php' style='display:inline-block;background:#C9A84C;color:#0B1D3A;padding:14px 32px;border-radius:8px;text-decoration:none;font-weight:700;'>Go to Dashboard</a>
        </div>
        </body></html>";
        sendEmail($student['email'], 'Enrollment Confirmed — LexLearnAI', $emailBody);
    }

    $_SESSION['payment_success'] = [
        'txnid'   => $txnid,
        'amount'  => $amount,
        'course'  => $courseInfo['title'] ?? ''
    ];
    redirect(SITE_URL . '/student/dashboard.php?payment=success');

} else {
    // Failed / cancelled
    $db->prepare("UPDATE payments SET status='failed', payu_txnid=?, payu_response=?, updated_at=NOW() WHERE txnid=?")
       ->execute([$payuTxnid, json_encode($_POST), $txnid]);
    redirect(SITE_URL . '/student/payment-failure.php?txnid=' . urlencode($txnid));
}
