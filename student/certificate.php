<?php
require_once '../config.php';
requireStudentLogin();
$student = getCurrentStudent();

$certs = $pdo->prepare("SELECT cert.*, c.title as course_title FROM certificates cert JOIN courses c ON c.id=cert.course_id WHERE cert.student_id=? ORDER BY cert.issue_date DESC");
$certs->execute([$student['id']]);
$certs = $certs->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>My Certificates – LexLearnAI</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= ASSETS_URL ?>/css/style.css">
<link rel="stylesheet" href="<?= ASSETS_URL ?>/css/admin.css">
<style>
.student-layout{display:flex;min-height:100vh;}
.student-sidebar{width:240px;background:#0B1D3A;position:fixed;top:0;left:0;height:100vh;overflow-y:auto;z-index:100;padding-top:20px;}
.student-sidebar .brand{padding:16px 20px 24px;border-bottom:1px solid rgba(255,255,255,.1);}
.student-sidebar .brand-name{font-size:16px;font-weight:700;color:#fff;}
.student-sidebar .brand-sub{font-size:11px;color:rgba(255,255,255,.4);}
.student-sidebar nav a{display:flex;align-items:center;gap:10px;padding:11px 20px;color:rgba(255,255,255,.7);font-size:13.5px;font-weight:500;border-left:3px solid transparent;transition:.2s;text-decoration:none;}
.student-sidebar nav a:hover,.student-sidebar nav a.active{color:#fff;background:rgba(255,255,255,.07);border-left-color:#C9A84C;}
.student-main{margin-left:240px;flex:1;background:#F8F9FC;}
.student-topbar{background:#fff;border-bottom:1px solid #DDE1EB;padding:0 24px;height:60px;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:99;}
.student-content{padding:28px;}
.cert-card{background:#fff;border-radius:14px;border:1px solid #DDE1EB;overflow:hidden;transition:.2s;}
.cert-card:hover{box-shadow:0 8px 32px rgba(0,0,0,.1);}
.cert-card .cert-preview{background:linear-gradient(135deg,#0B1D3A 0%,#142850 60%,#1a3a6b 100%);padding:40px 30px;text-align:center;position:relative;overflow:hidden;}
.cert-card .cert-preview::before{content:'';position:absolute;inset:8px;border:2px solid rgba(201,168,76,.4);border-radius:8px;}
.cert-card .cert-preview .cert-seal{width:60px;height:60px;background:rgba(201,168,76,.2);border:2px solid #C9A84C;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:28px;margin:0 auto 14px;}
.cert-card .cert-preview h3{font-family:'Playfair Display',serif;color:#fff;font-size:18px;margin-bottom:6px;}
.cert-card .cert-preview p{color:rgba(255,255,255,.6);font-size:12px;}
.cert-card .cert-preview .verify-badge{display:inline-flex;align-items:center;gap:6px;background:rgba(201,168,76,.15);border:1px solid rgba(201,168,76,.4);color:#C9A84C;padding:5px 12px;border-radius:20px;font-size:11px;font-weight:600;margin-top:12px;}
.cert-card .cert-body{padding:20px;}
@media(max-width:768px){.student-sidebar{transform:translateX(-100%)}.student-main{margin-left:0}}
</style>
</head>
<body>
<div class="student-layout">
<aside class="student-sidebar">
    <div class="brand"><div class="brand-name">⚖ LexLearnAI</div><div class="brand-sub">Student Portal</div></div>
    <nav>
        <a href="dashboard.php">📊 Dashboard</a>
        <a href="my-courses.php">📚 My Courses</a>
        <a href="schedule.php">📅 Schedule</a>
        <a href="materials.php">📂 Materials</a>
        <a href="quizzes.php">🧠 Quizzes</a>
        <a href="certificate.php" class="active">🎓 Certificate</a>
        <a href="payments.php">💳 Payments</a>
        <a href="profile.php">👤 Profile</a>
        <a href="../auth/logout.php">🚪 Logout</a>
    </nav>
</aside>
<div class="student-main">
<div class="student-topbar">
    <div style="font-weight:600;">My Certificates</div>
    <div style="font-size:13px;color:#8A94A6;"><?= xss($student['full_name']) ?></div>
</div>
<div class="student-content">

<?php if (empty($certs)): ?>
<div style="text-align:center;padding:80px 20px;color:#8A94A6;">
    <div style="font-size:56px;margin-bottom:16px;">🎓</div>
    <h3 style="font-size:18px;color:#4A5568;margin-bottom:8px;">No certificates yet</h3>
    <p>Complete your course to earn your certificate. Your certificates will appear here once issued by the admin.</p>
</div>
<?php else: ?>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:20px;">
<?php foreach ($certs as $cert): ?>
<div class="cert-card">
    <div class="cert-preview">
        <?php if ($cert['certificate_file']): ?>
        <img src="<?= UPLOAD_URL.'/'.$cert['certificate_file'] ?>" style="width:100%;border-radius:6px;position:relative;z-index:1;" alt="Certificate">
        <?php else: ?>
        <div class="cert-seal">🏛️</div>
        <h3><?= xss($cert['course_title']) ?></h3>
        <p>Certificate of Completion</p>
        <p style="margin-top:6px;font-family:'Playfair Display',serif;font-size:20px;color:#C9A84C;"><?= xss($student['full_name']) ?></p>
        <div class="verify-badge">✓ Verified · <?= xss($cert['verification_id']) ?></div>
        <?php endif; ?>
    </div>
    <div class="cert-body">
        <h3 style="font-size:15px;font-weight:700;color:#0B1D3A;margin-bottom:6px;"><?= xss($cert['course_title']) ?></h3>
        <div style="font-size:13px;color:#8A94A6;margin-bottom:12px;">
            📅 Issued: <?= formatDate($cert['issue_date']) ?><br>
            🔐 ID: <code style="font-size:12px;background:#F0F2F7;padding:2px 6px;border-radius:4px;"><?= xss($cert['verification_id']) ?></code>
        </div>
        <div style="display:flex;gap:8px;">
            <?php if ($cert['certificate_file']): ?>
            <a href="<?= UPLOAD_URL.'/'.$cert['certificate_file'] ?>" download target="_blank" class="btn btn-primary" style="flex:1;justify-content:center;padding:8px;font-size:13px;">⬇ Download</a>
            <?php endif; ?>
            <a href="<?= SITE_URL ?>/pages/verify-certificate.php?id=<?= urlencode($cert['verification_id']) ?>" target="_blank" class="btn btn-outline" style="padding:8px 12px;font-size:13px;">🔍 Verify</a>
        </div>
    </div>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>

</div>
</div>
</div>
</div>
</body>
</html>
