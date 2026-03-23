<?php require_once '../config.php'; ?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Refund Policy – LexLearnAI</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= ASSETS_URL ?>/css/style.css">
<style>.policy-body{max-width:780px;margin:0 auto;padding:48px 20px;}.policy-body h2{font-family:'Playfair Display',serif;font-size:20px;color:#0B1D3A;margin:28px 0 10px;}.policy-body p,.policy-body li{font-size:15px;color:#374151;line-height:1.7;margin-bottom:10px;}.policy-body ul{padding-left:20px;}</style>
</head><body>
<?php require_once '../includes/header.php'; ?>
<section style="background:linear-gradient(135deg,#0B1D3A,#142850);padding:48px 0;text-align:center;color:#fff;"><div class="container"><h1 style="font-family:'Playfair Display',serif;font-size:32px;">Refund Policy</h1><p style="color:rgba(255,255,255,.7);margin-top:8px;">Last updated: January 2024</p></div></section>
<div class="policy-body">
<p>We want you to be completely satisfied with your LexLearnAI experience. Please read our refund policy carefully before making a purchase.</p>
<h2>Eligibility for Refund</h2>
<p>You are eligible for a full refund if:</p>
<ul>
    <li>You request a refund within <strong>7 days</strong> of the date of purchase.</li>
    <li>You have attended <strong>fewer than 2 live classes</strong>.</li>
    <li>No certificate has been issued to you for the course.</li>
</ul>
<h2>Non-Refundable Situations</h2>
<ul>
    <li>Refund requests made after 7 days from purchase.</li>
    <li>Students who have attended 2 or more live classes.</li>
    <li>Students who have already downloaded or accessed substantial course materials.</li>
    <li>Certificates that have already been issued.</li>
    <li>Courses purchased during special discounts or promotional offers (unless stated otherwise).</li>
</ul>
<h2>How to Request a Refund</h2>
<p>To request a refund, email us at <strong><?= xss(getSetting('contact_email','refund@lexlearnai.com')) ?></strong> with:</p>
<ul>
    <li>Your registered email address</li>
    <li>Transaction ID / Order reference</li>
    <li>Reason for refund request</li>
</ul>
<h2>Processing Time</h2>
<p>Once approved, refunds are processed within <strong>5–7 business days</strong>. The refund will be credited to the original payment source. Banks may take additional time to reflect the amount.</p>
<h2>Payment Failures</h2>
<p>If your payment fails but money is deducted from your account, it will be automatically reversed within 5–7 business days. Contact your bank or our support team if it does not reflect.</p>
<h2>Course Cancellation by LexLearnAI</h2>
<p>If LexLearnAI cancels a course for any reason, you will receive a full refund within 7 business days.</p>
</div>
<?php require_once '../includes/footer.php'; ?>
</body></html>
