<?php
require_once '../config.php';
$certId = strtoupper(sanitize($_GET['id'] ?? ''));
$cert = null;
$error = '';

if ($certId) {
    $stmt = $pdo->prepare("SELECT cert.*, s.full_name, s.email, c.title as course_title, c.instructor FROM certificates cert JOIN students s ON s.id=cert.student_id JOIN courses c ON c.id=cert.course_id WHERE cert.verification_id=?");
    $stmt->execute([$certId]);
    $cert = $stmt->fetch();
    if (!$cert) $error = 'No certificate found with this ID. Please check and try again.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Verify Certificate – LexLearnAI</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700;800&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= ASSETS_URL ?>/css/style.css">
</head>
<body>
<?php require_once '../includes/header.php'; ?>

<section style="background:linear-gradient(135deg,#0B1D3A,#142850);padding:60px 0;text-align:center;color:#fff;">
    <div class="container">
        <div style="font-size:40px;margin-bottom:14px;">🎓</div>
        <h1 style="font-family:'Playfair Display',serif;font-size:34px;margin-bottom:10px;">Certificate Verification</h1>
        <p style="color:rgba(255,255,255,.7);margin-bottom:28px;">Enter a certificate verification ID to check its authenticity</p>
        <form method="GET" style="display:flex;max-width:480px;margin:0 auto;gap:0;">
            <input type="text" name="id" value="<?= xss($certId) ?>" placeholder="e.g. LEX-2024-ABC123" style="flex:1;padding:13px 18px;border:none;border-radius:10px 0 0 10px;font-size:15px;font-family:inherit;text-transform:uppercase;">
            <button type="submit" style="padding:13px 24px;background:#C9A84C;color:#0B1D3A;border:none;border-radius:0 10px 10px 0;font-size:15px;font-weight:700;cursor:pointer;">Verify →</button>
        </form>
    </div>
</section>

<section style="padding:56px 0;background:#F8F9FC;">
<div class="container" style="max-width:700px;margin:0 auto;">

<?php if ($error): ?>
<div style="background:#FEE2E2;border:1.5px solid #FCA5A5;color:#991B1B;padding:20px 24px;border-radius:12px;text-align:center;font-size:15px;">
    ✗ <?= xss($error) ?>
</div>

<?php elseif ($cert): ?>
<div style="background:#D1FAE5;border:2px solid #6EE7B7;border-radius:16px;overflow:hidden;">
    <div style="background:#059669;color:#fff;padding:16px 24px;display:flex;align-items:center;gap:12px;">
        <div style="width:36px;height:36px;background:rgba(255,255,255,.2);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:18px;">✓</div>
        <div>
            <div style="font-size:16px;font-weight:700;">Certificate Verified</div>
            <div style="font-size:13px;opacity:.85;">This is an authentic LexLearnAI certificate</div>
        </div>
    </div>
    <div style="padding:28px;">
        <?php if ($cert['certificate_file']): ?>
        <div style="margin-bottom:24px;border-radius:10px;overflow:hidden;border:2px solid #6EE7B7;">
            <?php $ext = strtolower(pathinfo($cert['certificate_file'], PATHINFO_EXTENSION)); ?>
            <?php if ($ext === 'pdf'): ?>
            <a href="<?= UPLOAD_URL.'/'.$cert['certificate_file'] ?>" target="_blank" style="display:flex;align-items:center;justify-content:center;height:120px;background:#F0FDF4;color:#059669;font-weight:600;gap:8px;text-decoration:none;">📄 View Certificate PDF</a>
            <?php else: ?>
            <img src="<?= UPLOAD_URL.'/'.$cert['certificate_file'] ?>" style="width:100%;display:block;" alt="Certificate">
            <?php endif; ?>
        </div>
        <?php endif; ?>
        <table style="width:100%;border-collapse:collapse;">
            <?php $details = [['Recipient',$cert['full_name']],['Course',$cert['course_title']],['Instructor',$cert['instructor']??'LexLearnAI Team'],['Issue Date',formatDate($cert['issue_date'])],['Verification ID',$cert['verification_id']]]; ?>
            <?php foreach ($details as [$label,$val]): ?>
            <tr>
                <td style="padding:10px 0;color:#065F46;font-size:13px;width:140px;font-weight:600;"><?= $label ?></td>
                <td style="padding:10px 0;font-size:14px;font-weight:700;color:#1A2336;"><?= xss($val) ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
</div>

<?php elseif ($certId === ''): ?>
<div style="text-align:center;padding:40px 20px;color:#8A94A6;">
    <p>Enter a verification ID above to check a certificate.</p>
</div>
<?php endif; ?>

</div>
</section>

<?php require_once '../includes/footer.php'; ?>
<script src="<?= ASSETS_URL ?>/js/main.js"></script>
</body>
</html>
