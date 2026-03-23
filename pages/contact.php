<?php
require_once '../config.php';
$errors = []; $success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    $name    = sanitize($_POST['name']);
    $email   = sanitize($_POST['email']);
    $subject = sanitize($_POST['subject']);
    $message = sanitize($_POST['message']);
    if (!$name||!$email||!$subject||!$message) { $errors[]='All fields are required.'; }
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors[]='Invalid email.'; }
    else {
        $pdo->prepare("INSERT INTO contact_messages (name,email,subject,message,created_at) VALUES (?,?,?,?,NOW())")->execute([$name,$email,$subject,$message]);
        $success='Your message has been sent. We\'ll get back to you within 24 hours.';
    }
}
$contactEmail = getSetting('contact_email', 'support@lexlearnai.com');
$contactPhone = getSetting('contact_phone', '+91 98765 43210');
$contactAddress = getSetting('contact_address', 'New Delhi, India');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Contact Us – LexLearnAI</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700;800&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= ASSETS_URL ?>/css/style.css">
</head>
<body>
<?php require_once '../includes/header.php'; ?>

<section style="background:linear-gradient(135deg,#0B1D3A,#142850);padding:56px 0;text-align:center;color:#fff;">
    <div class="container"><h1 style="font-family:'Playfair Display',serif;font-size:36px;margin-bottom:10px;">Get in Touch</h1><p style="color:rgba(255,255,255,.7);">Have questions? We'd love to hear from you.</p></div>
</section>

<section style="padding:56px 0;background:#F8F9FC;">
<div class="container">
<div style="display:grid;grid-template-columns:1fr 1.5fr;gap:48px;align-items:start;">

<div>
    <h2 style="font-family:'Playfair Display',serif;font-size:24px;color:#0B1D3A;margin-bottom:20px;">Contact Information</h2>
    <?php foreach ([['📧','Email',$contactEmail,'mailto:'.$contactEmail],['📞','Phone',$contactPhone,'tel:'.$contactPhone],['📍','Address',$contactAddress,null]] as [$icon,$label,$val,$href]): ?>
    <div style="display:flex;align-items:flex-start;gap:14px;margin-bottom:22px;">
        <div style="width:44px;height:44px;background:#0B1D3A;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0;"><?= $icon ?></div>
        <div><div style="font-size:12px;color:#8A94A6;margin-bottom:2px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;"><?= $label ?></div>
        <?php if ($href): ?><a href="<?= $href ?>" style="color:#0B1D3A;font-weight:600;"><?= xss($val) ?></a><?php else: ?><div style="color:#1A2336;font-weight:600;"><?= xss($val) ?></div><?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<div style="background:#fff;border-radius:16px;padding:32px;box-shadow:0 4px 20px rgba(0,0,0,.07);">
    <?php foreach ($errors as $e): ?><div style="background:#FEE2E2;color:#991B1B;padding:12px 16px;border-radius:8px;margin-bottom:16px;font-size:13.5px;"><?= xss($e) ?></div><?php endforeach; ?>
    <?php if ($success): ?><div style="background:#D1FAE5;color:#065F46;padding:12px 16px;border-radius:8px;margin-bottom:16px;font-size:13.5px;">✓ <?= xss($success) ?></div>
    <?php else: ?>
    <h3 style="font-family:'Playfair Display',serif;font-size:20px;color:#0B1D3A;margin-bottom:20px;">Send a Message</h3>
    <form method="POST">
        <?= csrfField() ?>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px;">
            <div><label style="font-size:13px;font-weight:600;display:block;margin-bottom:5px;">Full Name *</label><input type="text" name="name" required style="width:100%;padding:10px 13px;border:1.5px solid #DDE1EB;border-radius:8px;font-size:14px;font-family:inherit;"></div>
            <div><label style="font-size:13px;font-weight:600;display:block;margin-bottom:5px;">Email *</label><input type="email" name="email" required style="width:100%;padding:10px 13px;border:1.5px solid #DDE1EB;border-radius:8px;font-size:14px;font-family:inherit;"></div>
        </div>
        <div style="margin-bottom:14px;"><label style="font-size:13px;font-weight:600;display:block;margin-bottom:5px;">Subject *</label><input type="text" name="subject" required style="width:100%;padding:10px 13px;border:1.5px solid #DDE1EB;border-radius:8px;font-size:14px;font-family:inherit;"></div>
        <div style="margin-bottom:20px;"><label style="font-size:13px;font-weight:600;display:block;margin-bottom:5px;">Message *</label><textarea name="message" rows="5" required style="width:100%;padding:10px 13px;border:1.5px solid #DDE1EB;border-radius:8px;font-size:14px;font-family:inherit;resize:vertical;"></textarea></div>
        <button type="submit" style="width:100%;background:#0B1D3A;color:#fff;border:none;padding:14px;border-radius:10px;font-size:15px;font-weight:700;cursor:pointer;">Send Message →</button>
    </form>
    <?php endif; ?>
</div>
</div>
</div>
</section>

<?php require_once '../includes/footer.php'; ?>
<script src="<?= ASSETS_URL ?>/js/main.js"></script>
</body>
</html>
