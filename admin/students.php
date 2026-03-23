<?php
require_once '../config.php';
requireAdminLogin();
$pageTitle = 'Student Management';

// Handle CSV export
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    $students = $pdo->query("SELECT s.id,s.full_name,s.email,s.mobile,s.law_college,s.year_semester,s.city,s.state,s.profession,s.status,s.created_at, c.title as course FROM students s LEFT JOIN enrollments en ON en.student_id=s.id AND en.status='active' LEFT JOIN courses c ON c.id=en.course_id ORDER BY s.created_at DESC")->fetchAll();
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="students_' . date('Ymd') . '.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['ID','Name','Email','Mobile','College','Year/Sem','City','State','Profession','Course','Status','Registered']);
    foreach ($students as $s) {
        fputcsv($out, [$s['id'],$s['full_name'],$s['email'],$s['mobile'],$s['law_college'],$s['year_semester'],$s['city'],$s['state'],$s['profession'],$s['course'],$s['status'],$s['created_at']]);
    }
    fclose($out);
    exit;
}

$errors = []; $success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request.';
    } else {
        $fa = $_POST['form_action'] ?? '';
        if ($fa === 'update_student') {
            $sid = (int)$_POST['student_id'];
            $status = sanitize($_POST['status']);
            $progress = (int)$_POST['progress'];
            $note = sanitize($_POST['admin_note'] ?? '');
            $pdo->prepare("UPDATE students SET status=?, admin_note=? WHERE id=?")->execute([$status, $note, $sid]);
            // Update enrollment progress
            $pdo->prepare("UPDATE enrollments SET progress=? WHERE student_id=? AND status='active'")->execute([$progress, $sid]);
            $success = 'Student updated.';
        } elseif ($fa === 'assign_course') {
            $sid = (int)$_POST['student_id'];
            $cid = (int)$_POST['course_id'];
            // Check existing
            $exists = $pdo->prepare("SELECT id FROM enrollments WHERE student_id=? AND course_id=?");
            $exists->execute([$sid, $cid]);
            if (!$exists->fetch()) {
                $pdo->prepare("INSERT INTO enrollments (student_id,course_id,status,enrolled_at) VALUES (?,?,'active',NOW())")->execute([$sid,$cid]);
                $success = 'Course assigned.';
            } else {
                $errors[] = 'Student already enrolled in this course.';
            }
        } elseif ($fa === 'toggle_status') {
            $sid = (int)$_POST['student_id'];
            $pdo->prepare("UPDATE students SET status = IF(status='active','inactive','active') WHERE id=?")->execute([$sid]);
            $success = 'Student status updated.';
        }
    }
}

// Filters
$search = sanitize($_GET['search'] ?? '');
$statusFilter = sanitize($_GET['status'] ?? '');
$courseFilter = (int)($_GET['course'] ?? 0);

$where = "WHERE 1=1";
$params = [];
if ($search) {
    $where .= " AND (s.full_name LIKE ? OR s.email LIKE ? OR s.mobile LIKE ?)";
    $params = array_merge($params, ["%$search%", "%$search%", "%$search%"]);
}
if ($statusFilter) {
    $where .= " AND s.status=?";
    $params[] = $statusFilter;
}
if ($courseFilter) {
    $where .= " AND en.course_id=?";
    $params[] = $courseFilter;
}

$stmt = $pdo->prepare("SELECT s.*, en.course_id, en.progress, en.status as enroll_status, c.title as course_title FROM students s LEFT JOIN enrollments en ON en.student_id=s.id AND en.status='active' LEFT JOIN courses c ON c.id=en.course_id $where ORDER BY s.created_at DESC");
$stmt->execute($params);
$students = $stmt->fetchAll();

$courses = $pdo->query("SELECT id, title FROM courses WHERE status='active' ORDER BY title")->fetchAll();
$viewStudent = null;
if (isset($_GET['view'])) {
    $vs = $pdo->prepare("SELECT s.*, en.course_id, en.progress, en.enrolled_at, en.status as enroll_status, c.title as course_title, p.amount, p.txn_id, p.status as pay_status, p.created_at as pay_date FROM students s LEFT JOIN enrollments en ON en.student_id=s.id LEFT JOIN courses c ON c.id=en.course_id LEFT JOIN payments p ON p.student_id=s.id AND p.status='success' WHERE s.id=?");
    $vs->execute([(int)$_GET['view']]);
    $viewStudent = $vs->fetch();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= $pageTitle ?> – LexLearnAI Admin</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= ASSETS_URL ?>/css/admin.css">
</head>
<body>
<div class="admin-layout">
<?php require_once '../includes/admin-sidebar.php'; ?>
<div class="admin-main">
<?php require_once '../includes/admin-topbar.php'; ?>
<div class="admin-content">

<?php foreach ($errors as $e): ?><div class="alert alert-danger"><?= xss($e) ?></div><?php endforeach; ?>
<?php if ($success): ?><div class="alert alert-success"><?= xss($success) ?></div><?php endif; ?>

<?php if ($viewStudent): ?>
<!-- Student Detail View -->
<div class="page-header">
    <div class="page-header-left">
        <div class="page-title"><?= xss($viewStudent['full_name']) ?></div>
        <div class="page-subtitle">Student Profile</div>
    </div>
    <div class="page-header-right">
        <a href="students.php" class="btn btn-outline">← Back</a>
    </div>
</div>

<div class="sidebar-col">
<div>
<div class="card" style="margin-bottom:20px;">
    <div class="card-header"><div class="card-title">Personal Information</div></div>
    <div class="card-body">
        <div style="display:flex;align-items:center;gap:16px;margin-bottom:20px;">
            <?php if (!empty($viewStudent['profile_photo'])): ?>
            <img src="<?= UPLOAD_URL.'/'. $viewStudent['profile_photo'] ?>" class="avatar avatar-lg" alt="">
            <?php else: ?>
            <div class="avatar avatar-lg"><?= strtoupper(substr($viewStudent['full_name'],0,1)) ?></div>
            <?php endif; ?>
            <div>
                <div style="font-size:18px;font-weight:700;"><?= xss($viewStudent['full_name']) ?></div>
                <div style="color:var(--text-light);font-size:13px;"><?= xss($viewStudent['email']) ?></div>
                <span class="badge <?= $viewStudent['status']==='active'?'badge-success':'badge-danger' ?>" style="margin-top:4px;"><?= ucfirst($viewStudent['status']) ?></span>
            </div>
        </div>
        <table style="width:100%;font-size:13.5px;">
            <?php $fields = ['mobile'=>'Mobile','law_college'=>'College','year_semester'=>'Year/Semester','city'=>'City','state'=>'State','profession'=>'Profession']; ?>
            <?php foreach ($fields as $k=>$l): ?>
            <tr><td style="color:var(--text-light);padding:5px 0;width:140px;"><?= $l ?></td><td style="font-weight:500;"><?= xss($viewStudent[$k] ?? '—') ?></td></tr>
            <?php endforeach; ?>
            <tr><td style="color:var(--text-light);padding:5px 0;">Registered</td><td><?= formatDate($viewStudent['created_at']) ?></td></tr>
        </table>
    </div>
</div>

<?php if ($viewStudent['course_title']): ?>
<div class="card" style="margin-bottom:20px;">
    <div class="card-header"><div class="card-title">Enrollment</div></div>
    <div class="card-body">
        <div style="font-weight:600;margin-bottom:8px;"><?= xss($viewStudent['course_title']) ?></div>
        <div style="font-size:12px;color:var(--text-light);margin-bottom:12px;">Enrolled: <?= formatDate($viewStudent['enrolled_at']) ?></div>
        <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:6px;">
            <span>Progress</span><span><?= $viewStudent['progress'] ?>%</span>
        </div>
        <div class="progress"><div class="progress-bar" style="width:<?= $viewStudent['progress'] ?>%"></div></div>
    </div>
</div>
<?php endif; ?>

<?php if ($viewStudent['txn_id']): ?>
<div class="card">
    <div class="card-header"><div class="card-title">Payment</div></div>
    <div class="card-body">
        <table style="width:100%;font-size:13.5px;">
            <tr><td style="color:var(--text-light);padding:5px 0;width:140px;">Amount</td><td><strong><?= formatCurrency($viewStudent['amount']) ?></strong></td></tr>
            <tr><td style="color:var(--text-light);padding:5px 0;">Txn ID</td><td style="font-family:monospace;font-size:12px;"><?= xss($viewStudent['txn_id']) ?></td></tr>
            <tr><td style="color:var(--text-light);padding:5px 0;">Status</td><td><span class="badge badge-success"><?= ucfirst($viewStudent['pay_status']) ?></span></td></tr>
            <tr><td style="color:var(--text-light);padding:5px 0;">Date</td><td><?= formatDate($viewStudent['pay_date']) ?></td></tr>
        </table>
    </div>
</div>
<?php endif; ?>
</div>

<div>
<div class="card" style="margin-bottom:20px;">
    <div class="card-header"><div class="card-title">Update Student</div></div>
    <div class="card-body">
        <form method="POST">
            <?= csrfField() ?>
            <input type="hidden" name="form_action" value="update_student">
            <input type="hidden" name="student_id" value="<?= $viewStudent['id'] ?>">
            <div class="form-group" style="margin-bottom:14px;">
                <label>Status</label>
                <select name="status">
                    <option value="active" <?= $viewStudent['status']==='active'?'selected':'' ?>>Active</option>
                    <option value="inactive" <?= $viewStudent['status']==='inactive'?'selected':'' ?>>Inactive</option>
                </select>
            </div>
            <div class="form-group" style="margin-bottom:14px;">
                <label>Course Progress (%)</label>
                <input type="number" name="progress" min="0" max="100" value="<?= $viewStudent['progress'] ?? 0 ?>">
            </div>
            <div class="form-group" style="margin-bottom:14px;">
                <label>Admin Note</label>
                <textarea name="admin_note" rows="3"><?= xss($viewStudent['admin_note'] ?? '') ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;">Save Changes</button>
        </form>
    </div>
</div>

<?php if (!$viewStudent['course_title']): ?>
<div class="card">
    <div class="card-header"><div class="card-title">Assign Course</div></div>
    <div class="card-body">
        <form method="POST">
            <?= csrfField() ?>
            <input type="hidden" name="form_action" value="assign_course">
            <input type="hidden" name="student_id" value="<?= $viewStudent['id'] ?>">
            <div class="form-group" style="margin-bottom:14px;">
                <label>Select Course</label>
                <select name="course_id" required>
                    <option value="">— Select —</option>
                    <?php foreach ($courses as $c): ?>
                    <option value="<?= $c['id'] ?>"><?= xss($c['title']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-gold" style="width:100%;">Assign Course</button>
        </form>
    </div>
</div>
<?php endif; ?>
</div>
</div>

<?php else: ?>
<!-- Student List -->
<div class="page-header">
    <div class="page-header-left">
        <div class="page-title">Students</div>
        <div class="page-subtitle"><?= count($students) ?> students found</div>
    </div>
    <div class="page-header-right">
        <a href="students.php?export=csv" class="btn btn-outline">⬇ Export CSV</a>
    </div>
</div>

<div class="filters-bar">
    <div class="filter-group">
        <span class="search-icon">🔍</span>
        <input type="text" id="searchInput" placeholder="Search name, email, mobile..." value="<?= xss($search) ?>" oninput="filterTable()">
    </div>
    <select id="statusFilter" onchange="filterTable()">
        <option value="">All Statuses</option>
        <option value="active" <?= $statusFilter==='active'?'selected':'' ?>>Active</option>
        <option value="inactive" <?= $statusFilter==='inactive'?'selected':'' ?>>Inactive</option>
    </select>
    <select id="courseFilter" onchange="filterTable()">
        <option value="">All Courses</option>
        <?php foreach ($courses as $c): ?>
        <option value="<?= $c['id'] ?>" <?= $courseFilter==$c['id']?'selected':'' ?>><?= xss($c['title']) ?></option>
        <?php endforeach; ?>
    </select>
</div>

<div class="card">
<div class="card-body" style="padding:0;">
<div class="table-responsive">
<table id="studentsTable">
    <thead>
        <tr>
            <th>#</th>
            <th>Student</th>
            <th>Mobile</th>
            <th>Course</th>
            <th>Progress</th>
            <th>Status</th>
            <th>Registered</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
    <?php if (empty($students)): ?>
    <tr><td colspan="8"><div class="empty-state"><div class="empty-icon">👥</div><h3>No students found</h3></div></td></tr>
    <?php else: foreach ($students as $i => $s): ?>
    <tr>
        <td><?= $i+1 ?></td>
        <td>
            <div style="display:flex;align-items:center;gap:10px;">
                <div class="avatar"><?= strtoupper(substr($s['full_name'],0,1)) ?></div>
                <div>
                    <div style="font-weight:600;"><?= xss($s['full_name']) ?></div>
                    <div style="font-size:12px;color:var(--text-light);"><?= xss($s['email']) ?></div>
                </div>
            </div>
        </td>
        <td><?= xss($s['mobile']) ?></td>
        <td><?= $s['course_title'] ? xss($s['course_title']) : '<span style="color:var(--text-light);">Not enrolled</span>' ?></td>
        <td>
            <?php if ($s['course_title']): ?>
            <div style="display:flex;align-items:center;gap:8px;min-width:120px;">
                <div class="progress" style="flex:1;"><div class="progress-bar" style="width:<?= $s['progress']??0 ?>%"></div></div>
                <span style="font-size:12px;font-weight:600;"><?= $s['progress']??0 ?>%</span>
            </div>
            <?php else: ?>—<?php endif; ?>
        </td>
        <td><span class="badge <?= $s['status']==='active'?'badge-success':'badge-danger' ?>"><?= ucfirst($s['status']) ?></span></td>
        <td><?= formatDate($s['created_at']) ?></td>
        <td>
            <div class="table-actions">
                <a href="students.php?view=<?= $s['id'] ?>" class="btn btn-outline btn-sm">👁 View</a>
                <form method="POST" style="display:inline;" onsubmit="return confirm('Toggle student status?')">
                    <?= csrfField() ?>
                    <input type="hidden" name="form_action" value="toggle_status">
                    <input type="hidden" name="student_id" value="<?= $s['id'] ?>">
                    <button type="submit" class="btn btn-outline btn-sm">🔄</button>
                </form>
            </div>
        </td>
    </tr>
    <?php endforeach; endif; ?>
    </tbody>
</table>
</div>
</div>
</div>
<?php endif; ?>

</div>
</div>
</div>

<script>
function toggleSidebar() { document.getElementById('adminSidebar').classList.toggle('open'); }
function toggleDropdown(id) { document.getElementById(id).classList.toggle('open'); }
function filterTable() {
    const search = document.getElementById('searchInput')?.value.toLowerCase() || '';
    const rows = document.querySelectorAll('#studentsTable tbody tr');
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(search) ? '' : 'none';
    });
}
document.addEventListener('click', function(e) {
    if (!e.target.closest('.topbar-admin') && !e.target.closest('.dropdown-menu'))
        document.querySelectorAll('.dropdown-menu').forEach(m => m.classList.remove('open'));
});
</script>
</body>
</html>
