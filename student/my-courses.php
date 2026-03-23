<?php
require_once '../config.php';
requireStudentLogin();
$student = getCurrentStudent();
$pageTitle = 'My Courses';

$enrollments = $pdo->prepare("SELECT en.*, c.title, c.description, c.thumbnail, c.instructor, c.start_date, c.end_date, c.duration, p.status as pay_status, p.amount FROM enrollments en JOIN courses c ON c.id=en.course_id LEFT JOIN payments p ON p.student_id=en.student_id AND p.course_id=en.course_id AND p.status='success' WHERE en.student_id=? ORDER BY en.enrolled_at DESC");
$enrollments->execute([$student['id']]);
$enrollments = $enrollments->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= $pageTitle ?> – LexLearnAI</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= ASSETS_URL ?>/css/style.css">
<link rel="stylesheet" href="<?= ASSETS_URL ?>/css/admin.css">
<style>
.student-layout{display:flex;min-height:100vh;}
.student-sidebar{width:240px;background:var(--navy);position:fixed;top:0;left:0;height:100vh;overflow-y:auto;z-index:100;padding-top:20px;}
.student-sidebar .brand{padding:16px 20px 24px;border-bottom:1px solid rgba(255,255,255,.1);}
.student-sidebar .brand-name{font-size:16px;font-weight:700;color:#fff;}
.student-sidebar .brand-sub{font-size:11px;color:rgba(255,255,255,.4);}
.student-sidebar nav a{display:flex;align-items:center;gap:10px;padding:11px 20px;color:rgba(255,255,255,.7);font-size:13.5px;font-weight:500;border-left:3px solid transparent;transition:.2s;}
.student-sidebar nav a:hover,.student-sidebar nav a.active{color:#fff;background:rgba(255,255,255,.07);border-left-color:var(--gold);}
.student-main{margin-left:240px;flex:1;background:var(--off-white,#F8F9FC);}
.student-topbar{background:#fff;border-bottom:1px solid #DDE1EB;padding:0 24px;height:60px;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:99;}
.student-content{padding:28px;}
.course-card{background:#fff;border-radius:12px;border:1px solid #DDE1EB;overflow:hidden;transition:.2s;}
.course-card:hover{box-shadow:0 6px 24px rgba(0,0,0,.1);}
.course-card .thumb{height:160px;background:linear-gradient(135deg,#0B1D3A,#142850);position:relative;}
.course-card .thumb img{width:100%;height:100%;object-fit:cover;}
.course-card .thumb .badge{position:absolute;top:12px;right:12px;}
.course-card .body{padding:18px;}
.progress{background:#F0F2F7;border-radius:20px;height:8px;overflow:hidden;margin:10px 0 4px;}
.progress-bar{height:100%;background:linear-gradient(90deg,#0B1D3A,#1a3a6b);border-radius:20px;}
@media(max-width:768px){.student-sidebar{transform:translateX(-100%)}.student-main{margin-left:0}}
</style>
</head>
<body>
<div class="student-layout">
<aside class="student-sidebar" id="studentSidebar">
    <div class="brand"><div class="brand-name">⚖ LexLearnAI</div><div class="brand-sub">Student Portal</div></div>
    <nav>
        <a href="dashboard.php">📊 Dashboard</a>
        <a href="my-courses.php" class="active">📚 My Courses</a>
        <a href="schedule.php">📅 Schedule</a>
        <a href="materials.php">📂 Materials</a>
        <a href="quizzes.php">🧠 Quizzes</a>
        <a href="certificate.php">🎓 Certificate</a>
        <a href="payments.php">💳 Payments</a>
        <a href="profile.php">👤 Profile</a>
        <a href="../auth/logout.php">🚪 Logout</a>
    </nav>
</aside>
<div class="student-main">
<div class="student-topbar">
    <div style="font-weight:600;">My Courses</div>
    <div style="font-size:13px;color:#8A94A6;">Welcome, <?= xss($student['full_name']) ?></div>
</div>
<div class="student-content">

<?php if (empty($enrollments)): ?>
<div style="text-align:center;padding:80px 20px;">
    <div style="font-size:48px;margin-bottom:16px;">📚</div>
    <h3 style="font-size:20px;margin-bottom:8px;color:#0B1D3A;">No courses enrolled yet</h3>
    <p style="color:#8A94A6;margin-bottom:24px;">Browse our courses and enroll to start your legal AI journey.</p>
    <a href="../pages/courses.php" class="btn btn-primary">Browse Courses</a>
</div>
<?php else: ?>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:20px;">
<?php foreach ($enrollments as $en): ?>
<div class="course-card">
    <div class="thumb">
        <?php if ($en['thumbnail']): ?>
        <img src="<?= UPLOAD_URL.'/'.$en['thumbnail'] ?>" alt="<?= xss($en['title']) ?>">
        <?php endif; ?>
        <span class="badge <?= $en['status']==='active'?'badge-success':'badge-warning' ?>" style="position:absolute;top:12px;right:12px;padding:4px 10px;border-radius:20px;font-size:11px;font-weight:700;<?= $en['status']==='active'?'background:#D1FAE5;color:#065F46':'background:#FEF3C7;color:#92400E' ?>"><?= ucfirst($en['status']) ?></span>
    </div>
    <div class="body">
        <h3 style="font-size:16px;font-weight:700;color:#0B1D3A;margin-bottom:6px;"><?= xss($en['title']) ?></h3>
        <?php if ($en['instructor']): ?><p style="font-size:12px;color:#8A94A6;margin-bottom:10px;">👨‍🏫 <?= xss($en['instructor']) ?></p><?php endif; ?>
        <div style="font-size:12px;color:#4A5568;margin-bottom:12px;">
            <?php if ($en['start_date']): ?><span>📅 <?= formatDate($en['start_date']) ?></span><?php endif; ?>
            <?php if ($en['duration']): ?><span style="margin-left:10px;">⏱ <?= xss($en['duration']) ?></span><?php endif; ?>
        </div>
        <div>
            <div style="display:flex;justify-content:space-between;font-size:12px;margin-bottom:4px;"><span>Progress</span><strong><?= $en['progress'] ?>%</strong></div>
            <div class="progress"><div class="progress-bar" style="width:<?= $en['progress'] ?>%"></div></div>
        </div>
        <div style="margin-top:14px;display:flex;gap:8px;">
            <a href="course-view.php?id=<?= $en['course_id'] ?>" class="btn btn-primary" style="flex:1;justify-content:center;padding:8px;">View Course</a>
            <a href="materials.php?course=<?= $en['course_id'] ?>" class="btn btn-outline" style="padding:8px 12px;">📂</a>
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
