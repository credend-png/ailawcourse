<?php require_once '../config.php'; ?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Privacy Policy – LexLearnAI</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= ASSETS_URL ?>/css/style.css">
<style>.policy-body{max-width:780px;margin:0 auto;padding:48px 20px;}.policy-body h2{font-family:'Playfair Display',serif;font-size:20px;color:#0B1D3A;margin:28px 0 10px;}.policy-body p,.policy-body li{font-size:15px;color:#374151;line-height:1.7;margin-bottom:10px;}.policy-body ul{padding-left:20px;}</style>
</head><body>
<?php require_once '../includes/header.php'; ?>
<section style="background:linear-gradient(135deg,#0B1D3A,#142850);padding:48px 0;text-align:center;color:#fff;"><div class="container"><h1 style="font-family:'Playfair Display',serif;font-size:32px;">Privacy Policy</h1><p style="color:rgba(255,255,255,.7);margin-top:8px;">Last updated: January 2024</p></div></section>
<div class="policy-body">
<p>LexLearnAI ("we", "us", "our") is committed to protecting your privacy. This policy explains how we collect, use, and safeguard your personal information.</p>
<h2>1. Information We Collect</h2>
<ul>
    <li><strong>Registration data:</strong> Name, email, mobile, law college, city, state, profession.</li>
    <li><strong>Payment data:</strong> Transaction IDs (we do not store card details).</li>
    <li><strong>Usage data:</strong> Pages visited, session duration, browser type.</li>
    <li><strong>Course data:</strong> Progress, quiz scores, attendance records.</li>
</ul>
<h2>2. How We Use Your Information</h2>
<ul>
    <li>To process enrollment and payments.</li>
    <li>To deliver course content and certificates.</li>
    <li>To send important notifications about your course.</li>
    <li>To improve our platform and offerings.</li>
    <li>To respond to your enquiries.</li>
</ul>
<h2>3. Data Sharing</h2>
<p>We do not sell or rent your personal data. We may share data with trusted third-party service providers (payment processors, email services) under strict confidentiality agreements.</p>
<h2>4. Data Security</h2>
<p>We implement industry-standard security measures including SSL encryption, hashed passwords, and restricted database access. However, no method of internet transmission is 100% secure.</p>
<h2>5. Cookies</h2>
<p>We use session cookies for authentication and analytics cookies to improve our services. You can disable cookies in your browser settings, though this may affect platform functionality.</p>
<h2>6. Your Rights</h2>
<p>You have the right to access, correct, or delete your personal data. To exercise these rights, contact us at: <?= xss(getSetting('contact_email','privacy@lexlearnai.com')) ?></p>
<h2>7. Retention</h2>
<p>We retain your data for as long as your account is active and as required by applicable laws. Certificate records are retained indefinitely for verification purposes.</p>
<h2>8. Children's Privacy</h2>
<p>Our platform is not intended for individuals under 18 years of age. We do not knowingly collect data from minors.</p>
</div>
<?php require_once '../includes/footer.php'; ?>
</body></html>
