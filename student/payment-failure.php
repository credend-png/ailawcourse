<?php
require_once '../config.php';
requireStudentLogin();
$student = getCurrentStudent();
$txnId = sanitize($_GET['txnid'] ?? '');
$reason = sanitize($_GET['reason'] ?? 'Payment was not completed.');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Payment Failed – LexLearnAI</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= ASSETS_URL ?>/css/style.css">
<style>
body{background:#F8F9FC;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0;}
.fail-card{background:#fff;border-radius:20px;padding:48px 40px;text-align:center;max-width:440px;width:100%;box-shadow:0 8px 40px rgba(0,0,0,.1);}
.fail-icon{width:80px;height:80px;background:#FEE2E2;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:36px;margin:0 auto 20px;}
h1{font-family:'Playfair Display',serif;font-size:26px;color:#0B1D3A;margin-bottom:10px;}
p{color:#4A5568;margin-bottom:6px;font-size:14px;}
.txn{font-family:monospace;font-size:12px;color:#8A94A6;background:#F0F2F7;padding:6px 12px;border-radius:6px;margin:12px 0;display:inline-block;}
.btn{display:inline-flex;align-items:center;gap:8px;padding:11px 22px;border-radius:10px;font-weight:600;font-size:14px;cursor:pointer;border:none;text-decoration:none;margin-top:20px;}
.btn-primary{background:#0B1D3A;color:#fff;}
.btn-outline{background:transparent;color:#0B1D3A;border:2px solid #DDE1EB;margin-left:8px;}
</style>
</head>
<body>
<div class="fail-card">
    <div class="fail-icon">✕</div>
    <h1>Payment Failed</h1>
    <p><?= xss($reason) ?></p>
    <?php if ($txnId): ?><div class="txn">Txn Ref: <?= xss($txnId) ?></div><?php endif; ?>
    <p style="margin-top:16px;font-size:13px;color:#8A94A6;">If money was deducted, it will be refunded within 5–7 business days. Contact us if it doesn't reflect.</p>
    <div>
        <a href="<?= STUDENT_URL ?>/enroll.php" class="btn btn-primary">🔄 Try Again</a>
        <a href="<?= STUDENT_URL ?>/dashboard.php" class="btn btn-outline">← Dashboard</a>
    </div>
</div>
</body>
</html>
