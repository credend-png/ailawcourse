<?php
require_once '../config.php';
$slug = sanitize($_GET['slug'] ?? '');
if (!$slug) redirect(SITE_URL . '/pages/courses.php');

$course = $pdo->prepare("SELECT * FROM courses WHERE slug=? AND status='active'");
$course->execute([$slug]);
$course = $course->fetch();
if (!$course) redirect(SITE_URL . '/pages/courses.php');

$enrolled = $pdo->query("SELECT COUNT(*) FROM enrollments WHERE course_id={$course['id']} AND status='active'")->fetchColumn();
$upcoming = $pdo->query("SELECT * FROM class_schedules WHERE course_id={$course['id']} AND class_date >= CURDATE() ORDER BY class_date LIMIT 3")->fetchAll();
$highlights = $course['highlights'] ? explode("\n", trim($course['highlights'])) : [];
$isEnrolled = false;
if (isStudentLoggedIn()) {
    $s = getCurrentStudent();
    $check = $pdo->prepare("SELECT id FROM enrollments WHERE student_id=? AND course_id=? AND status='active'");
    $check->execute([$s['id'],$course['id']]);
    $isEnrolled = (bool)$check->fetch();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= xss($course['title']) ?> – LexLearnAI</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700;800&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= ASSETS_URL ?>/css/style.css">
<style>
.course-hero{background:linear-gradient(135deg,#0B1D3A 0%,#142850 60%,#1a3a6b 100%);padding:70px 0 56px;color:#fff;}
.course-meta span{display:inline-flex;align-items:center;gap:6px;font-size:13px;color:rgba(255,255,255,.7);margin-right:16px;}
.sticky-card{background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 12px 40px rgba(0,0,0,.15);position:sticky;top:80px;}
.sticky-card .thumb{height:200px;background:linear-gradient(135deg,#0B1D3A,#1a3a6b);overflow:hidden;}
.sticky-card .thumb img{width:100%;height:100%;object-fit:cover;}
.sticky-card .card-inner{padding:24px;}
.highlight-item{display:flex;align-items:flex-start;gap:10px;padding:8px 0;border-bottom:1px solid #F0F2F7;font-size:14px;}
.highlight-item:last-child{border-bottom:none;}
</style>
</head>
<body>
<?php require_once '../includes/header.php'; ?>

<section class="course-hero">
<div class="container">
    <div style="display:grid;grid-template-columns:1fr 360px;gap:48px;align-items:start;">
    <div>
        <div style="display:inline-block;background:rgba(201,168,76,.2);color:#C9A84C;padding:5px 14px;border-radius:20px;font-size:12px;font-weight:700;margin-bottom:16px;">📚 Course</div>
        <h1 style="font-family:'Playfair Display',serif;font-size:36px;line-height:1.2;margin-bottom:14px;"><?= xss($course['title']) ?></h1>
        <p style="font-size:16px;color:rgba(255,255,255,.75);line-height:1.6;margin-bottom:20px;"><?= xss($course['short_description'] ?? '') ?></p>
        <div class="course-meta">
            <?php if ($course['instructor']): ?><span>👨‍🏫 <?= xss($course['instructor']) ?></span><?php endif; ?>
            <?php if ($course['duration']): ?><span>⏱ <?= xss($course['duration']) ?></span><?php endif; ?>
            <?php if ($course['start_date']): ?><span>📅 <?= formatDate($course['start_date']) ?></span><?php endif; ?>
            <span>👥 <?= $enrolled ?> students</span>
        </div>
    </div>
    <!-- Mobile: card shows below on small screens -->
    </div>
</div>
</section>

<section style="padding:48px 0;background:#F8F9FC;">
<div class="container">
<div style="display:grid;grid-template-columns:1fr 360px;gap:40px;align-items:start;">

<!-- Left -->
<div>
    <?php if ($course['description']): ?>
    <div class="card" style="margin-bottom:24px;">
        <div class="card-header"><h2 style="font-family:'Playfair Display',serif;font-size:18px;color:#0B1D3A;">About This Course</h2></div>
        <div class="card-body" style="line-height:1.7;color:#374151;font-size:15px;"><?= nl2br(xss($course['description'])) ?></div>
    </div>
    <?php endif; ?>

    <?php if (!empty($highlights)): ?>
    <div class="card" style="margin-bottom:24px;">
        <div class="card-header"><h2 style="font-family:'Playfair Display',serif;font-size:18px;color:#0B1D3A;">What You'll Get</h2></div>
        <div class="card-body">
        <?php foreach ($highlights as $h): ?>
        <?php if (trim($h)): ?>
        <div class="highlight-item"><span style="color:#10B981;font-size:16px;">✓</span><span><?= xss(trim($h)) ?></span></div>
        <?php endif; ?>
        <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <?php if (!empty($upcoming)): ?>
    <div class="card">
        <div class="card-header"><h2 style="font-family:'Playfair Display',serif;font-size:18px;color:#0B1D3A;">Upcoming Classes</h2></div>
        <div class="card-body" style="padding:0;">
        <?php foreach ($upcoming as $cl): ?>
        <div style="display:flex;align-items:center;gap:14px;padding:14px 20px;border-bottom:1px solid #F0F2F7;">
            <div style="background:#0B1D3A;color:#fff;border-radius:8px;padding:8px 12px;text-align:center;flex-shrink:0;">
                <div style="font-size:18px;font-weight:700;"><?= date('d',strtotime($cl['class_date'])) ?></div>
                <div style="font-size:10px;text-transform:uppercase;color:rgba(255,255,255,.6);"><?= date('M',strtotime($cl['class_date'])) ?></div>
            </div>
            <div>
                <div style="font-weight:600;font-size:14px;"><?= xss($cl['title']) ?></div>
                <div style="font-size:12px;color:#8A94A6;"><?= date('h:i A',strtotime($cl['class_time'])) ?></div>
            </div>
        </div>
        <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Right: Sticky Card -->
<div>
<div class="sticky-card">
    <div class="thumb">
        <?php if ($course['thumbnail']): ?>
        <img src="<?= UPLOAD_URL.'/'.$course['thumbnail'] ?>" alt="<?= xss($course['title']) ?>">
        <?php else: ?>
        <div style="display:flex;align-items:center;justify-content:center;height:100%;font-size:56px;">⚖️</div>
        <?php endif; ?>
    </div>
    <div class="card-inner">
        <div style="font-size:32px;font-weight:800;color:#0B1D3A;margin-bottom:16px;"><?= formatCurrency($course['fee']) ?></div>
        <?php if ($isEnrolled): ?>
        <a href="<?= STUDENT_URL ?>/my-courses.php" style="display:block;background:#10B981;color:#fff;padding:14px;border-radius:12px;text-align:center;font-size:15px;font-weight:700;text-decoration:none;margin-bottom:12px;">✓ Already Enrolled — Go to Dashboard</a>
        <?php elseif (isStudentLoggedIn()): ?>
        <a href="<?= STUDENT_URL ?>/enroll.php?course=<?= $course['id'] ?>" style="display:block;background:#0B1D3A;color:#fff;padding:14px;border-radius:12px;text-align:center;font-size:15px;font-weight:700;text-decoration:none;margin-bottom:12px;transition:.2s;" onmouseover="this.style.background='#C9A84C';this.style.color='#0B1D3A'" onmouseout="this.style.background='#0B1D3A';this.style.color='#fff'">Enroll Now →</a>
        <?php else: ?>
        <a href="<?= AUTH_URL ?>/register.php?redirect=<?= urlencode(STUDENT_URL.'/enroll.php?course='.$course['id']) ?>" style="display:block;background:#C9A84C;color:#0B1D3A;padding:14px;border-radius:12px;text-align:center;font-size:15px;font-weight:700;text-decoration:none;margin-bottom:12px;">Register & Enroll →</a>
        <a href="<?= AUTH_URL ?>/login.php?redirect=<?= urlencode(STUDENT_URL.'/enroll.php?course='.$course['id']) ?>" style="display:block;background:transparent;color:#0B1D3A;padding:12px;border-radius:12px;text-align:center;font-size:14px;font-weight:600;text-decoration:none;border:2px solid #DDE1EB;">Already have an account? Login</a>
        <?php endif; ?>
        <div style="margin-top:16px;font-size:13px;color:#6B7280;text-align:center;">🔒 Secure payment via PayU</div>
        <div style="margin-top:16px;border-top:1px solid #F0F2F7;padding-top:14px;">
            <?php $details = [['📅','Start Date',$course['start_date']?formatDate($course['start_date']):null],['🏁','End Date',$course['end_date']?formatDate($course['end_date']):null],['⏱','Duration',$course['duration']],['👨‍🏫','Instructor',$course['instructor']]]; ?>
            <?php foreach ($details as [$icon,$label,$val]): ?>
            <?php if ($val): ?>
            <div style="display:flex;justify-content:space-between;padding:6px 0;font-size:13px;border-bottom:1px solid #F9FAFB;">
                <span style="color:#8A94A6;"><?= $icon ?> <?= $label ?></span>
                <span style="font-weight:600;color:#1A2336;"><?= xss($val) ?></span>
            </div>
            <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
</div>
</div>

</div>
</div>
</section>

<?php require_once '../includes/footer.php'; ?>
<script src="<?= ASSETS_URL ?>/js/main.js"></script>
</body>
</html>
