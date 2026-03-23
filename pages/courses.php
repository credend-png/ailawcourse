<?php
require_once '../config.php';
$courses = $pdo->query("SELECT c.*, (SELECT COUNT(*) FROM enrollments e WHERE e.course_id=c.id AND e.status='active') as enrolled FROM courses c WHERE c.status='active' ORDER BY c.created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Courses – LexLearnAI</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700;800&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= ASSETS_URL ?>/css/style.css">
</head>
<body>
<?php require_once '../includes/header.php'; ?>

<section style="background:linear-gradient(135deg,#0B1D3A,#142850);padding:70px 0 50px;text-align:center;color:#fff;">
    <div class="container">
        <h1 style="font-family:'Playfair Display',serif;font-size:38px;margin-bottom:12px;">Our Courses</h1>
        <p style="color:rgba(255,255,255,.7);font-size:16px;max-width:500px;margin:0 auto;">Expert-designed programmes to master legal AI skills</p>
    </div>
</section>

<section style="padding:56px 0;background:#F8F9FC;">
<div class="container">
<?php if (empty($courses)): ?>
<div style="text-align:center;padding:60px 20px;color:#8A94A6;">
    <div style="font-size:48px;margin-bottom:16px;">📚</div>
    <h3>No courses available yet. Check back soon!</h3>
</div>
<?php else: ?>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:24px;">
<?php foreach ($courses as $c): ?>
<div style="background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,.06);border:1px solid #E5E7EB;transition:.25s;" onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 12px 40px rgba(0,0,0,.12)'" onmouseout="this.style.transform='';this.style.boxShadow='0 2px 12px rgba(0,0,0,.06)'">
    <div style="height:180px;background:linear-gradient(135deg,#0B1D3A,#1a3a6b);position:relative;overflow:hidden;">
        <?php if ($c['thumbnail']): ?>
        <img src="<?= UPLOAD_URL.'/'.$c['thumbnail'] ?>" style="width:100%;height:100%;object-fit:cover;" alt="<?= xss($c['title']) ?>">
        <?php else: ?>
        <div style="display:flex;align-items:center;justify-content:center;height:100%;font-size:48px;">⚖️</div>
        <?php endif; ?>
        <div style="position:absolute;top:12px;right:12px;background:rgba(11,29,58,.8);color:#C9A84C;padding:4px 10px;border-radius:20px;font-size:11px;font-weight:700;"><?= $c['enrolled'] ?> enrolled</div>
    </div>
    <div style="padding:20px;">
        <h3 style="font-family:'Playfair Display',serif;font-size:18px;color:#0B1D3A;margin-bottom:8px;"><?= xss($c['title']) ?></h3>
        <p style="font-size:13px;color:#6B7280;margin-bottom:14px;line-height:1.5;"><?= xss($c['short_description'] ?? substr($c['description'],0,100).'…') ?></p>
        <div style="display:flex;gap:14px;font-size:12px;color:#8A94A6;margin-bottom:16px;">
            <?php if ($c['instructor']): ?><span>👨‍🏫 <?= xss($c['instructor']) ?></span><?php endif; ?>
            <?php if ($c['duration']): ?><span>⏱ <?= xss($c['duration']) ?></span><?php endif; ?>
        </div>
        <div style="display:flex;align-items:center;justify-content:space-between;">
            <div style="font-size:22px;font-weight:800;color:#0B1D3A;"><?= formatCurrency($c['fee']) ?></div>
            <a href="course-detail.php?slug=<?= xss($c['slug']) ?>" style="display:inline-flex;align-items:center;gap:6px;background:#0B1D3A;color:#fff;padding:9px 18px;border-radius:10px;font-size:13px;font-weight:700;text-decoration:none;transition:.2s;" onmouseover="this.style.background='#C9A84C';this.style.color='#0B1D3A'" onmouseout="this.style.background='#0B1D3A';this.style.color='#fff'">View Details →</a>
        </div>
    </div>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>
</div>
</section>

<?php require_once '../includes/footer.php'; ?>
<script src="<?= ASSETS_URL ?>/js/main.js"></script>
</body>
</html>
