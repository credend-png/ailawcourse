<?php
require_once '../config.php';
requireStudentLogin();
$student = getCurrentStudent();

// Get enrollments for filter
$myEnrollments = $pdo->prepare("SELECT en.course_id, c.title FROM enrollments en JOIN courses c ON c.id=en.course_id WHERE en.student_id=? AND en.status='active'");
$myEnrollments->execute([$student['id']]);
$myEnrollments = $myEnrollments->fetchAll();
$myCourseIds = array_column($myEnrollments, 'course_id');

// Upcoming and past classes
$upcoming = $past = [];
if (!empty($myCourseIds)) {
    $in = implode(',', array_map('intval', $myCourseIds));
    $upcoming = $pdo->query("SELECT cs.*, c.title as course_title FROM class_schedules cs JOIN courses c ON c.id=cs.course_id WHERE cs.course_id IN ($in) AND cs.class_date >= CURDATE() ORDER BY cs.class_date, cs.class_time LIMIT 20")->fetchAll();
    $past = $pdo->query("SELECT cs.*, c.title as course_title FROM class_schedules cs JOIN courses c ON c.id=cs.course_id WHERE cs.course_id IN ($in) AND cs.class_date < CURDATE() ORDER BY cs.class_date DESC LIMIT 20")->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Class Schedule – LexLearnAI</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= ASSETS_URL ?>/css/style.css">
<link rel="stylesheet" href="<?= ASSETS_URL ?>/css/admin.css">
<style>
.student-layout{display:flex;min-height:100vh;}
.student-sidebar{width:240px;background:var(--navy,#0B1D3A);position:fixed;top:0;left:0;height:100vh;overflow-y:auto;z-index:100;padding-top:20px;}
.student-sidebar .brand{padding:16px 20px 24px;border-bottom:1px solid rgba(255,255,255,.1);}
.student-sidebar .brand-name{font-size:16px;font-weight:700;color:#fff;}
.student-sidebar .brand-sub{font-size:11px;color:rgba(255,255,255,.4);}
.student-sidebar nav a{display:flex;align-items:center;gap:10px;padding:11px 20px;color:rgba(255,255,255,.7);font-size:13.5px;font-weight:500;border-left:3px solid transparent;transition:.2s;text-decoration:none;}
.student-sidebar nav a:hover,.student-sidebar nav a.active{color:#fff;background:rgba(255,255,255,.07);border-left-color:#C9A84C;}
.student-main{margin-left:240px;flex:1;background:#F8F9FC;}
.student-topbar{background:#fff;border-bottom:1px solid #DDE1EB;padding:0 24px;height:60px;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:99;}
.student-content{padding:28px;}
.class-card{background:#fff;border-radius:12px;border:1px solid #DDE1EB;padding:20px;display:flex;align-items:flex-start;gap:16px;transition:.2s;}
.class-card:hover{box-shadow:0 4px 16px rgba(0,0,0,.08);}
.class-date-box{background:#0B1D3A;color:#fff;border-radius:10px;padding:10px 14px;text-align:center;min-width:60px;flex-shrink:0;}
.class-date-box .day{font-size:22px;font-weight:700;line-height:1;}
.class-date-box .mon{font-size:11px;text-transform:uppercase;letter-spacing:1px;color:rgba(255,255,255,.6);}
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
        <a href="schedule.php" class="active">📅 Schedule</a>
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
    <div style="font-weight:600;">Class Schedule</div>
    <div style="font-size:13px;color:#8A94A6;"><?= xss($student['full_name']) ?></div>
</div>
<div class="student-content">

<div style="margin-bottom:28px;">
    <h2 style="font-size:18px;font-weight:700;color:#0B1D3A;margin-bottom:16px;">Upcoming Classes</h2>
    <?php if (empty($upcoming)): ?>
    <div style="text-align:center;padding:40px;color:#8A94A6;">No upcoming classes scheduled.</div>
    <?php else: ?>
    <div style="display:flex;flex-direction:column;gap:12px;">
    <?php foreach ($upcoming as $cl): ?>
    <div class="class-card">
        <div class="class-date-box">
            <div class="day"><?= date('d', strtotime($cl['class_date'])) ?></div>
            <div class="mon"><?= date('M', strtotime($cl['class_date'])) ?></div>
        </div>
        <div style="flex:1;">
            <div style="font-weight:700;font-size:15px;margin-bottom:4px;"><?= xss($cl['title']) ?></div>
            <div style="font-size:12px;color:#8A94A6;margin-bottom:8px;"><?= xss($cl['course_title']) ?> · <?= date('h:i A', strtotime($cl['class_time'])) ?></div>
            <?php if ($cl['description']): ?><p style="font-size:13px;color:#4A5568;margin-bottom:10px;"><?= xss($cl['description']) ?></p><?php endif; ?>
            <?php if ($cl['meeting_link']): ?>
            <a href="<?= xss($cl['meeting_link']) ?>" target="_blank" class="btn btn-primary" style="padding:7px 16px;font-size:13px;">🔗 Join Class</a>
            <?php else: ?><span style="font-size:12px;color:#8A94A6;">Meeting link will be shared soon</span><?php endif; ?>
        </div>
        <span class="badge <?= $cl['status']==='live'?'badge-success':'badge-info' ?>"><?= ucfirst($cl['status']) ?></span>
    </div>
    <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<div>
    <h2 style="font-size:18px;font-weight:700;color:#0B1D3A;margin-bottom:16px;">Past Classes</h2>
    <?php if (empty($past)): ?>
    <div style="text-align:center;padding:40px;color:#8A94A6;">No past classes.</div>
    <?php else: ?>
    <div style="display:flex;flex-direction:column;gap:12px;">
    <?php foreach ($past as $cl): ?>
    <div class="class-card" style="opacity:.7;">
        <div class="class-date-box" style="background:#8A94A6;">
            <div class="day"><?= date('d', strtotime($cl['class_date'])) ?></div>
            <div class="mon"><?= date('M', strtotime($cl['class_date'])) ?></div>
        </div>
        <div>
            <div style="font-weight:700;font-size:15px;"><?= xss($cl['title']) ?></div>
            <div style="font-size:12px;color:#8A94A6;"><?= xss($cl['course_title']) ?> · <?= date('h:i A', strtotime($cl['class_time'])) ?></div>
        </div>
        <span class="badge badge-grey">Completed</span>
    </div>
    <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

</div>
</div>
</div>
</div>
</body>
</html>
