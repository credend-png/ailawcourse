<?php
require_once '../config.php';
requireStudentLogin();
$student = getCurrentStudent();

$myEnrollments = $pdo->prepare("SELECT en.course_id FROM enrollments en WHERE en.student_id=? AND en.status='active'");
$myEnrollments->execute([$student['id']]);
$myCourseIds = array_column($myEnrollments->fetchAll(), 'course_id');

$materials = [];
if (!empty($myCourseIds)) {
    $in = implode(',', array_map('intval', $myCourseIds));
    $materials = $pdo->query("SELECT m.*, c.title as course_title FROM study_materials m JOIN courses c ON c.id=m.course_id WHERE m.course_id IN ($in) ORDER BY c.title, m.created_at DESC")->fetchAll();
}

$grouped = [];
foreach ($materials as $m) {
    $grouped[$m['course_title']][] = $m;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Study Materials – LexLearnAI</title>
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
.mat-item{background:#fff;border-radius:10px;border:1px solid #DDE1EB;padding:16px 20px;display:flex;align-items:center;gap:14px;transition:.2s;}
.mat-item:hover{box-shadow:0 3px 12px rgba(0,0,0,.08);}
.mat-icon{font-size:24px;flex-shrink:0;}
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
        <a href="materials.php" class="active">📂 Materials</a>
        <a href="quizzes.php">🧠 Quizzes</a>
        <a href="certificate.php">🎓 Certificate</a>
        <a href="payments.php">💳 Payments</a>
        <a href="profile.php">👤 Profile</a>
        <a href="../auth/logout.php">🚪 Logout</a>
    </nav>
</aside>
<div class="student-main">
<div class="student-topbar">
    <div style="font-weight:600;">Study Materials</div>
    <div style="font-size:13px;color:#8A94A6;"><?= xss($student['full_name']) ?></div>
</div>
<div class="student-content">

<?php if (empty($materials)): ?>
<div style="text-align:center;padding:80px 20px;color:#8A94A6;">
    <div style="font-size:48px;margin-bottom:16px;">📂</div>
    <h3 style="font-size:18px;color:#4A5568;">No materials available yet</h3>
    <p>Materials will appear here once your instructor uploads them.</p>
</div>
<?php else: ?>
<?php foreach ($grouped as $courseTitle => $mats): ?>
<div style="margin-bottom:28px;">
    <h2 style="font-size:16px;font-weight:700;color:#0B1D3A;margin-bottom:14px;padding-bottom:10px;border-bottom:2px solid #DDE1EB;"><?= xss($courseTitle) ?></h2>
    <div style="display:flex;flex-direction:column;gap:10px;">
    <?php foreach ($mats as $m): 
        $ext = $m['file_path'] ? strtolower(pathinfo($m['file_path'], PATHINFO_EXTENSION)) : '';
        $icons = ['pdf'=>'📄','doc'=>'📝','docx'=>'📝','ppt'=>'📊','pptx'=>'📊','zip'=>'🗜️','xls'=>'📊','xlsx'=>'📊'];
        $icon = $icons[$ext] ?? '📎';
    ?>
    <div class="mat-item">
        <div class="mat-icon"><?= $m['type']==='link' ? '🔗' : $icon ?></div>
        <div style="flex:1;">
            <div style="font-weight:600;font-size:14px;"><?= xss($m['title']) ?></div>
            <div style="font-size:12px;color:#8A94A6;margin-top:2px;"><?= $m['type']==='file' ? strtoupper($ext).' File' : 'External Link' ?> · Added <?= formatDate($m['created_at']) ?></div>
        </div>
        <?php if ($m['type']==='file' && $m['file_path']): ?>
        <a href="<?= UPLOAD_URL.'/'.$m['file_path'] ?>" target="_blank" download class="btn btn-primary" style="padding:7px 14px;font-size:13px;">⬇ Download</a>
        <?php elseif ($m['type']==='link' && $m['link']): ?>
        <a href="<?= xss($m['link']) ?>" target="_blank" class="btn btn-outline" style="padding:7px 14px;font-size:13px;">🔗 Open</a>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
    </div>
</div>
<?php endforeach; ?>
<?php endif; ?>

</div>
</div>
</div>
</div>
</body>
</html>
