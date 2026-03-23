<?php
require_once '../config.php';
requireAdminLogin();
$pageTitle = 'Enrollments';
$success='';

if ($_SERVER['REQUEST_METHOD']==='POST'&&verifyCsrfToken($_POST['csrf_token']??'')) {
    if ($_POST['form_action']==='update_progress') {
        $pdo->prepare("UPDATE enrollments SET progress=? WHERE id=?")->execute([(int)$_POST['progress'],(int)$_POST['enroll_id']]);
        $success='Progress updated.';
    } elseif ($_POST['form_action']==='toggle_enroll') {
        $pdo->prepare("UPDATE enrollments SET status=IF(status='active','inactive','active') WHERE id=?")->execute([(int)$_POST['enroll_id']]);
        $success='Status updated.';
    }
}

$enrollments=$pdo->query("SELECT en.*, s.full_name, s.email, c.title as course_title, p.amount, p.status as pay_status FROM enrollments en JOIN students s ON s.id=en.student_id JOIN courses c ON c.id=en.course_id LEFT JOIN payments p ON p.student_id=en.student_id AND p.course_id=en.course_id AND p.status='success' ORDER BY en.enrolled_at DESC")->fetchAll();
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= $pageTitle ?> – Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= ASSETS_URL ?>/css/admin.css">
</head><body>
<div class="admin-layout">
<?php require_once '../includes/admin-sidebar.php'; ?>
<div class="admin-main">
<?php require_once '../includes/admin-topbar.php'; ?>
<div class="admin-content">
<?php if ($success): ?><div class="alert alert-success"><?= xss($success) ?></div><?php endif; ?>
<div class="page-header">
    <div class="page-header-left"><div class="page-title">Enrollments</div><div class="page-subtitle"><?= count($enrollments) ?> total</div></div>
</div>
<div class="card"><div class="card-body" style="padding:0;"><div class="table-responsive">
<table>
    <thead><tr><th>#</th><th>Student</th><th>Course</th><th>Progress</th><th>Payment</th><th>Status</th><th>Enrolled</th><th>Actions</th></tr></thead>
    <tbody>
    <?php if (empty($enrollments)): ?><tr><td colspan="8"><div class="empty-state"><div class="empty-icon">📋</div><h3>No enrollments</h3></div></td></tr>
    <?php else: foreach ($enrollments as $i=>$en): ?>
    <tr>
        <td><?= $i+1 ?></td>
        <td><div style="font-weight:600;"><?= xss($en['full_name']) ?></div><div style="font-size:12px;color:var(--text-light);"><?= xss($en['email']) ?></div></td>
        <td><?= xss($en['course_title']) ?></td>
        <td>
            <form method="POST" style="display:flex;align-items:center;gap:8px;">
                <?= csrfField() ?><input type="hidden" name="form_action" value="update_progress"><input type="hidden" name="enroll_id" value="<?= $en['id'] ?>">
                <input type="number" name="progress" value="<?= $en['progress'] ?>" min="0" max="100" style="width:60px;padding:4px 8px;">
                <button type="submit" class="btn btn-outline btn-sm">✓</button>
            </form>
        </td>
        <td><?= $en['amount'] ? formatCurrency($en['amount']) : '—' ?></td>
        <td><span class="badge <?= $en['status']==='active'?'badge-success':'badge-warning' ?>"><?= ucfirst($en['status']) ?></span></td>
        <td><?= formatDate($en['enrolled_at']) ?></td>
        <td>
            <form method="POST" style="display:inline;"><?= csrfField() ?><input type="hidden" name="form_action" value="toggle_enroll"><input type="hidden" name="enroll_id" value="<?= $en['id'] ?>"><button type="submit" class="btn btn-outline btn-sm">🔄</button></form>
        </td>
    </tr>
    <?php endforeach; endif; ?>
    </tbody>
</table>
</div></div></div>
</div></div></div>
<script>function toggleSidebar(){document.getElementById('adminSidebar').classList.toggle('open');}function toggleDropdown(id){document.getElementById(id).classList.toggle('open');}document.addEventListener('click',function(e){if(!e.target.closest('.topbar-admin')&&!e.target.closest('.dropdown-menu'))document.querySelectorAll('.dropdown-menu').forEach(m=>m.classList.remove('open'));});</script>
</body></html>
