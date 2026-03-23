<?php
require_once '../config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>FAQs – LexLearnAI</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= ASSETS_URL ?>/css/style.css">
<style>
.faq-item{border:1px solid #E5E7EB;border-radius:12px;overflow:hidden;margin-bottom:10px;}
.faq-q{display:flex;justify-content:space-between;align-items:center;padding:18px 20px;cursor:pointer;background:#fff;font-weight:600;font-size:15px;color:#0B1D3A;user-select:none;}
.faq-q:hover{background:#F8F9FC;}
.faq-a{display:none;padding:0 20px 18px;font-size:14px;color:#4A5568;line-height:1.7;background:#fff;}
.faq-item.open .faq-a{display:block;}
.faq-item.open .faq-toggle{transform:rotate(45deg);}
.faq-toggle{font-size:20px;transition:.2s;flex-shrink:0;}
</style>
</head>
<body>
<?php require_once '../includes/header.php'; ?>

<section style="background:linear-gradient(135deg,#0B1D3A,#142850);padding:56px 0;text-align:center;color:#fff;">
    <div class="container"><h1 style="font-family:'Playfair Display',serif;font-size:34px;margin-bottom:10px;">Frequently Asked Questions</h1><p style="color:rgba(255,255,255,.7);">Everything you need to know about LexLearnAI</p></div>
</section>

<section style="padding:56px 0;background:#F8F9FC;">
<div class="container" style="max-width:780px;margin:0 auto;">

<?php
$faqs = [
    ['General','About LexLearnAI',[
        ['What is LexLearnAI?', 'LexLearnAI is a specialised legal education platform that trains law students, lawyers, and legal professionals in AI tools, legal research automation, contract drafting with AI, and emerging legal technologies.'],
        ['Who are these courses designed for?', 'Our courses are designed for law students, practising advocates, legal associates, in-house counsel, judges, legal academics, and anyone in the legal profession who wants to leverage AI in their practice.'],
        ['Are the courses live or recorded?', 'Our courses feature live online classes conducted via Zoom/Google Meet, supplemented by recorded sessions, study materials, quizzes, and assignments.'],
    ]],
    ['Enrollment','Enrollment & Payment',[
        ['How do I enroll in a course?', 'Register on our platform, browse the available courses, and click "Enroll Now". You will be directed to a secure payment page. Once payment is confirmed, you get instant access to course materials.'],
        ['What payment methods are accepted?', 'We accept all major debit/credit cards, UPI (Google Pay, PhonePe, Paytm), net banking, and EMI options through our secure PayU payment gateway.'],
        ['Can I get a discount?', 'Yes! We run periodic promotions and offer discount coupons. Apply a coupon code at checkout to avail the discount. Follow us on social media to stay updated on offers.'],
        ['Is there a refund policy?', 'We offer refunds within 7 days of purchase if you have not attended more than 2 live classes. Please review our full Refund Policy for details.'],
    ]],
    ['Certificates','Certificates',[
        ['Will I get a certificate after completing the course?', 'Yes, every student who completes the course receives a digital Certificate of Completion from LexLearnAI. The certificate includes a unique verification ID.'],
        ['Can the certificate be verified?', 'Yes, all our certificates are digitally verifiable. Anyone can verify a certificate at our website using the unique verification ID printed on the certificate.'],
        ['Is the certificate recognised?', 'Our certificates are issued by LexLearnAI as a professional development credential. While they are not bar-council or university degrees, they demonstrate specialised competency in legal AI tools.'],
    ]],
    ['Technical','Technical',[
        ['What do I need to attend the live classes?', 'You need a stable internet connection, a laptop or desktop (recommended), and Zoom or Google Meet installed. A webcam and microphone are helpful but not mandatory.'],
        ['How do I access study materials?', 'After enrolling, log in to your student dashboard and go to the "Materials" section to access all PDFs, notes, and resources for your course.'],
        ['What if I miss a live class?', 'Recorded sessions are uploaded to your student portal within 24 hours after each class so you never miss out.'],
    ]],
];
foreach ($faqs as [$cat,$title,$items]):
?>
<h2 style="font-family:'Playfair Display',serif;font-size:20px;color:#0B1D3A;margin:32px 0 14px;padding-left:4px;"><?= $title ?></h2>
<?php foreach ($items as [$q,$a]): ?>
<div class="faq-item" onclick="this.classList.toggle('open')">
    <div class="faq-q"><?= $q ?> <span class="faq-toggle">+</span></div>
    <div class="faq-a"><?= $a ?></div>
</div>
<?php endforeach; ?>
<?php endforeach; ?>

<div style="background:#fff;border-radius:16px;padding:28px;text-align:center;margin-top:40px;border:1px solid #E5E7EB;">
    <p style="font-size:15px;color:#4A5568;margin-bottom:14px;">Still have questions? We're happy to help.</p>
    <a href="contact.php" style="display:inline-flex;align-items:center;gap:8px;background:#0B1D3A;color:#fff;padding:12px 24px;border-radius:10px;font-weight:700;text-decoration:none;">✉ Contact Us</a>
</div>
</div>
</section>

<?php require_once '../includes/footer.php'; ?>
</body>
</html>
