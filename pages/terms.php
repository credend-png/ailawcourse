<?php require_once '../config.php'; ?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Terms & Conditions – LexLearnAI</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= ASSETS_URL ?>/css/style.css">
<style>.policy-body{max-width:780px;margin:0 auto;padding:48px 20px;}.policy-body h2{font-family:'Playfair Display',serif;font-size:20px;color:#0B1D3A;margin:28px 0 10px;}.policy-body p,.policy-body li{font-size:15px;color:#374151;line-height:1.7;margin-bottom:10px;}.policy-body ul{padding-left:20px;}</style>
</head><body>
<?php require_once '../includes/header.php'; ?>
<section style="background:linear-gradient(135deg,#0B1D3A,#142850);padding:48px 0;text-align:center;color:#fff;"><div class="container"><h1 style="font-family:'Playfair Display',serif;font-size:32px;">Terms & Conditions</h1><p style="color:rgba(255,255,255,.7);margin-top:8px;">Last updated: January 2024</p></div></section>
<div class="policy-body">
<p>Welcome to LexLearnAI. By accessing our platform and enrolling in our courses, you agree to be bound by the following terms and conditions.</p>
<h2>1. Acceptance of Terms</h2>
<p>By using LexLearnAI, you confirm that you are at least 18 years old and legally capable of entering into a binding agreement. If you are accessing on behalf of an organisation, you warrant that you have authority to bind that organisation to these terms.</p>
<h2>2. Course Access</h2>
<p>Upon successful payment and enrollment, you will be granted access to course materials, live sessions, and resources for the duration of the course. Access is personal and non-transferable.</p>
<ul>
    <li>You may not share login credentials with others.</li>
    <li>Recording of live sessions without explicit permission is prohibited.</li>
    <li>Course materials are for personal educational use only and may not be redistributed.</li>
</ul>
<h2>3. Payment & Fees</h2>
<p>All fees are listed in Indian Rupees (INR) and are inclusive of applicable taxes. Payments are processed securely through PayU payment gateway. LexLearnAI does not store payment card details.</p>
<h2>4. Intellectual Property</h2>
<p>All course content, materials, logos, and branding are the intellectual property of LexLearnAI. You are granted a limited, non-exclusive licence to use the materials for personal education only.</p>
<h2>5. Conduct</h2>
<p>You agree not to disrupt live sessions, post inappropriate content, or engage in any behaviour that could harm other students or the platform's reputation.</p>
<h2>6. Limitation of Liability</h2>
<p>LexLearnAI's total liability shall not exceed the fees paid for the specific course giving rise to the claim. We are not liable for indirect, consequential, or incidental damages.</p>
<h2>7. Changes to Terms</h2>
<p>We reserve the right to update these terms. Continued use after changes constitutes acceptance of the updated terms.</p>
<h2>8. Contact</h2>
<p>For questions about these terms, contact us at: <?= xss(getSetting('contact_email','legal@lexlearnai.com')) ?></p>
</div>
<?php require_once '../includes/footer.php'; ?>
</body></html>
