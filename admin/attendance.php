<?php
require_once '../config.php';
requireAdminLogin();
$db = getDB();
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
        die('Invalid CSRF token');
    }
    $studentId = (int)($_POST['student_id'] ?? 0);
    $classId = (int)($_POST['class_id'] ?? 0);
    $courseId = (int)($_POST['course_id'] ?? 0);
    $status = sanitize($_POST['status'] ?? 'present');
    if ($studentId && $classId && $courseId && in_array($status, ['present','absent','excused'], true)) {
        $db->prepare("INSERT INTO attendance (student_id, class_id, course_id, status) VALUES (?,?,?,?) ON DUPLICATE KEY UPDATE status=VALUES(status), marked_at=CURRENT_TIMESTAMP")
           ->execute([$studentId, $classId, $courseId, $status]);
        $success = 'Attendance saved successfully.';
    }
}
$classes = $db->query("SELECT cs.id, cs.title, cs.course_id, c.title AS course_title, cs.class_date FROM class_schedules cs JOIN courses c ON c.id = cs.course_id ORDER BY cs.class_date DESC, cs.class_time DESC")->fetchAll();
$students = $db->query("SELECT id, full_name, email FROM students WHERE status='active' ORDER BY full_name ASC")->fetchAll();
$attendance = $db->query("SELECT a.*, s.full_name, cs.title AS class_title, c.title AS course_title FROM attendance a JOIN students s ON s.id=a.student_id JOIN class_schedules cs ON cs.id=a.class_id JOIN courses c ON c.id=a.course_id ORDER BY a.marked_at DESC LIMIT 50")->fetchAll();
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Attendance | LexLearnAI Admin</title><link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css"><link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/admin.css"></head><body>
<div class="dashboard-layout"><div class="main-content" style="width:100%;"><div class="topbar"><div class="topbar-title">Attendance Tracking</div><a href="<?= SITE_URL ?>/admin/dashboard.php" class="btn btn-outline btn-sm">Back to Dashboard</a></div><div class="page-body"><div class="container" style="max-width:1200px;padding:0;">
<?php if($success): ?><div class="alert alert-success" style="margin-bottom:16px;"><?= xss($success) ?></div><?php endif; ?>
<div style="display:grid;grid-template-columns:380px 1fr;gap:20px;">
<div class="card"><div class="card-header"><div class="card-title">Mark Attendance</div></div><div class="card-body"><form method="post"><?= csrfField() ?><label class="form-label">Class</label><select name="class_id" id="class_id" class="form-control" required onchange="syncCourse()"><option value="">Select class</option><?php foreach($classes as $class): ?><option value="<?= $class['id'] ?>" data-course="<?= $class['course_id'] ?>"><?= xss($class['course_title'].' — '.$class['title'].' ('.formatDate($class['class_date']).')') ?></option><?php endforeach; ?></select><input type="hidden" name="course_id" id="course_id"><label class="form-label" style="margin-top:12px;">Student</label><select name="student_id" class="form-control" required><option value="">Select student</option><?php foreach($students as $student): ?><option value="<?= $student['id'] ?>"><?= xss($student['full_name'].' — '.$student['email']) ?></option><?php endforeach; ?></select><label class="form-label" style="margin-top:12px;">Status</label><select name="status" class="form-control"><option value="present">Present</option><option value="absent">Absent</option><option value="excused">Excused</option></select><button class="btn btn-primary" style="margin-top:16px;">Save Attendance</button></form></div></div>
<div class="card"><div class="card-header"><div class="card-title">Recent Attendance Records</div></div><div class="card-body"><?php if(!$attendance): ?><p style="color:var(--grey-500);">No attendance records yet.</p><?php else: ?><div class="table-wrap"><table class="data-table"><thead><tr><th>Student</th><th>Course</th><th>Class</th><th>Status</th><th>Marked</th></tr></thead><tbody><?php foreach($attendance as $row): ?><tr><td><?= xss($row['full_name']) ?></td><td><?= xss($row['course_title']) ?></td><td><?= xss($row['class_title']) ?></td><td><span class="badge <?= $row['status']==='present' ? 'badge-success' : ($row['status']==='excused' ? 'badge-info' : 'badge-warning') ?>"><?= xss(ucfirst($row['status'])) ?></span></td><td><?= formatDateTime($row['marked_at']) ?></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?></div></div>
</div></div></div></div></div>
<script>function syncCourse(){const select=document.getElementById('class_id'); const option=select.options[select.selectedIndex]; document.getElementById('course_id').value=option ? option.getAttribute('data-course') || '' : '';}</script>
</body></html>
