<?php
require_once '../config.php';
requireStudentLogin();
$student = getCurrentStudent();
$courseId = (int)($_GET['id'] ?? 0);
if (!$courseId) redirect(SITE_URL . '/student/my-courses.php');
$db = getDB();
$stmt = $db->prepare("SELECT e.*, c.* FROM enrollments e JOIN courses c ON c.id=e.course_id WHERE e.student_id=? AND e.course_id=?");
$stmt->execute([$student['id'], $courseId]);
$course = $stmt->fetch();
if (!$course) redirect(SITE_URL . '/student/my-courses.php');
$classes = $db->prepare("SELECT * FROM class_schedules WHERE course_id=? ORDER BY class_date ASC, class_time ASC");
$classes->execute([$courseId]);
$classes = $classes->fetchAll();
$materials = $db->prepare("SELECT * FROM study_materials WHERE course_id=? AND status='active' ORDER BY sort_order ASC, id DESC");
$materials->execute([$courseId]);
$materials = $materials->fetchAll();
$quizzes = $db->prepare("SELECT q.*, qa.percentage, qa.passed FROM quizzes q LEFT JOIN quiz_attempts qa ON qa.quiz_id=q.id AND qa.student_id=? WHERE q.course_id=? AND q.status='active' ORDER BY q.week_number ASC, q.id ASC");
$quizzes->execute([$student['id'], $courseId]);
$quizzes = $quizzes->fetchAll();
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title><?= xss($course['title']) ?> | LexLearnAI</title><link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css"><link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/dashboard.css"></head><body>
<?php include '../includes/header.php'; ?>
<main class="section" style="padding-top:120px;background:var(--grey-50);min-height:100vh;"><div class="container">
<div class="card" style="margin-bottom:20px;"><div class="card-body"><div style="display:flex;justify-content:space-between;gap:20px;flex-wrap:wrap;"><div><div class="section-tag" style="display:inline-flex;">My Course</div><h1 style="font-size:2rem;margin:8px 0;"><?= xss($course['title']) ?></h1><p style="color:var(--grey-500);max-width:780px;"><?= xss($course['short_description']) ?></p></div><div style="min-width:220px;"><div style="font-size:.85rem;color:var(--grey-500);margin-bottom:8px;">Completion Progress</div><div class="progress-wrap" style="height:10px;"><div class="progress-bar" style="width:<?= (int)$course['progress_percentage'] ?>%"></div></div><div style="margin-top:8px;font-weight:700;color:var(--navy);"><?= (int)$course['progress_percentage'] ?>%</div></div></div></div></div>
<div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;">
<div>
<div class="card" style="margin-bottom:20px;"><div class="card-header"><div class="card-title">Weekly Class Schedule</div></div><div class="card-body"><?php if(!$classes): ?><p style="color:var(--grey-500);">No classes scheduled yet.</p><?php else: foreach($classes as $class): ?><div style="padding:14px 0;border-bottom:1px solid var(--grey-100);"><div style="display:flex;justify-content:space-between;gap:14px;flex-wrap:wrap;"><div><div style="font-weight:700;color:var(--navy);"><?= xss($class['title']) ?></div><div style="font-size:.9rem;color:var(--grey-500);"><?= formatDate($class['class_date']) ?> · <?= date('h:i A', strtotime($class['class_time'])) ?> IST</div><div style="font-size:.85rem;color:var(--grey-600);margin-top:4px;"><?= xss($class['description']) ?></div></div><?php if($class['meeting_link']): ?><a href="<?= xss($class['meeting_link']) ?>" target="_blank" class="btn btn-primary btn-sm">Join Class</a><?php endif; ?></div></div><?php endforeach; endif; ?></div></div>
<div class="card" style="margin-bottom:20px;"><div class="card-header"><div class="card-title">Study Materials</div></div><div class="card-body"><?php if(!$materials): ?><p style="color:var(--grey-500);">No study materials uploaded yet.</p><?php else: ?><div class="table-wrap"><table class="data-table"><thead><tr><th>Title</th><th>Type</th><th>Access</th></tr></thead><tbody><?php foreach($materials as $item): ?><tr><td><strong><?= xss($item['title']) ?></strong><div style="font-size:.82rem;color:var(--grey-500)"><?= xss($item['description']) ?></div></td><td><?= xss(strtoupper($item['type'])) ?></td><td><?php if($item['external_link']): ?><a class="btn btn-sm btn-outline" target="_blank" href="<?= xss($item['external_link']) ?>">Open</a><?php elseif($item['file_path']): ?><a class="btn btn-sm btn-outline" target="_blank" href="<?= UPLOAD_URL . '/' . xss($item['file_path']) ?>">Download</a><?php else: ?>—<?php endif; ?></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?></div></div>
</div>
<div>
<div class="card" style="margin-bottom:20px;"><div class="card-header"><div class="card-title">Course Access</div></div><div class="card-body"><div style="display:grid;gap:12px;">
<div><div style="font-size:.78rem;color:var(--grey-500);">Status</div><div class="badge badge-success"><?= xss(ucfirst($course['status'])) ?></div></div>
<div><div style="font-size:.78rem;color:var(--grey-500);">Start Date</div><div><?= $course['start_date'] ? formatDate($course['start_date']) : 'TBA' ?></div></div>
<div><div style="font-size:.78rem;color:var(--grey-500);">End Date</div><div><?= $course['end_date'] ? formatDate($course['end_date']) : 'TBA' ?></div></div>
<div><div style="font-size:.78rem;color:var(--grey-500);">Duration</div><div><?= (int)$course['duration_weeks'] ?> weeks</div></div>
</div></div></div>
<div class="card"><div class="card-header"><div class="card-title">Quiz Progress</div></div><div class="card-body"><?php if(!$quizzes): ?><p style="color:var(--grey-500);">No quizzes assigned yet.</p><?php else: foreach($quizzes as $quiz): ?><div style="padding:12px 0;border-bottom:1px solid var(--grey-100);"><div style="font-weight:600;color:var(--navy);"><?= xss($quiz['title']) ?></div><div style="font-size:.83rem;color:var(--grey-500);">Passing: <?= (int)$quiz['passing_marks'] ?>%</div><div style="margin-top:6px;"><?php if($quiz['percentage'] !== null): ?><span class="badge <?= $quiz['passed'] ? 'badge-success' : 'badge-warning' ?>">Score: <?= xss($quiz['percentage']) ?>%</span><?php else: ?><span class="badge badge-info">Pending</span><?php endif; ?></div></div><?php endforeach; endif; ?></div></div>
</div></div>
</div></main></body></html>
