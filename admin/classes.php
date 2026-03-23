<?php
require_once '../config.php';
requireAdminLogin();
$pageTitle = 'Class Schedules';
$errors = []; $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    $fa = $_POST['form_action'] ?? '';
    if ($fa === 'save') {
        $cid    = (int)$_POST['course_id'];
        $title  = sanitize($_POST['title']);
        $date   = sanitize($_POST['class_date']);
        $time   = sanitize($_POST['class_time']);
        $link   = sanitize($_POST['meeting_link']);
        $desc   = sanitize($_POST['description']);
        $status = sanitize($_POST['status'] ?? 'upcoming');
        $id     = (int)($_POST['class_id'] ?? 0);
        if (!$cid || !$title || !$date || !$time) { $errors[] = 'Course, title, date and time are required.'; }
        if (empty($errors)) {
            if ($id > 0) {
                $pdo->prepare("UPDATE class_schedules SET course_id=?,title=?,class_date=?,class_time=?,meeting_link=?,description=?,status=? WHERE id=?")->execute([$cid,$title,$date,$time,$link,$desc,$status,$id]);
                $success = 'Class updated.';
            } else {
                $pdo->prepare("INSERT INTO class_schedules (course_id,title,class_date,class_time,meeting_link,description,status,created_at) VALUES (?,?,?,?,?,?,?,NOW())")->execute([$cid,$title,$date,$time,$link,$desc,$status]);
                $success = 'Class scheduled.';
            }
        }
    } elseif ($fa === 'delete') {
        $pdo->prepare("DELETE FROM class_schedules WHERE id=?")->execute([(int)$_POST['class_id']]);
        $success = 'Class deleted.';
    }
}

$classes = $pdo->query("SELECT cs.*, c.title as course_title FROM class_schedules cs JOIN courses c ON c.id=cs.course_id ORDER BY cs.class_date DESC, cs.class_time DESC")->fetchAll();
$courses = $pdo->query("SELECT id, title FROM courses WHERE status='active' ORDER BY title")->fetchAll();
$editClass = null;
if (isset($_GET['edit'])) {
    $st = $pdo->prepare("SELECT * FROM class_schedules WHERE id=?");
    $st->execute([(int)$_GET['edit']]);
    $editClass = $st->fetch();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= $pageTitle ?> – LexLearnAI Admin</title>
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

<div class="page-header">
    <div class="page-header-left">
        <div class="page-title">Class Schedules</div>
        <div class="page-subtitle">Manage all live classes</div>
    </div>
    <div class="page-header-right">
        <button class="btn btn-primary" onclick="document.getElementById('classModal').classList.add('open')">+ Add Class</button>
    </div>
</div>

<div class="card">
<div class="card-body" style="padding:0;">
<div class="table-responsive">
<table>
    <thead>
        <tr><th>#</th><th>Title</th><th>Course</th><th>Date & Time</th><th>Meeting Link</th><th>Status</th><th>Actions</th></tr>
    </thead>
    <tbody>
    <?php if (empty($classes)): ?>
    <tr><td colspan="7"><div class="empty-state"><div class="empty-icon">📅</div><h3>No classes scheduled</h3></div></td></tr>
    <?php else: foreach ($classes as $i => $cl): ?>
    <tr>
        <td><?= $i+1 ?></td>
        <td style="font-weight:600;"><?= xss($cl['title']) ?></td>
        <td><?= xss($cl['course_title']) ?></td>
        <td>
            <div><?= date('d M Y', strtotime($cl['class_date'])) ?></div>
            <div style="font-size:12px;color:var(--text-light);"><?= date('h:i A', strtotime($cl['class_time'])) ?></div>
        </td>
        <td><?= $cl['meeting_link'] ? '<a href="'.xss($cl['meeting_link']).'" target="_blank" style="color:var(--info);">🔗 Join</a>' : '—' ?></td>
        <td>
            <?php $sc=['upcoming'=>'badge-info','live'=>'badge-success','completed'=>'badge-grey','cancelled'=>'badge-danger']; ?>
            <span class="badge <?= $sc[$cl['status']] ?? 'badge-grey' ?>"><?= ucfirst($cl['status']) ?></span>
        </td>
        <td>
            <div class="table-actions">
                <button class="btn btn-outline btn-sm" onclick="editClass(<?= htmlspecialchars(json_encode($cl)) ?>)">✏️ Edit</button>
                <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this class?')">
                    <?= csrfField() ?>
                    <input type="hidden" name="form_action" value="delete">
                    <input type="hidden" name="class_id" value="<?= $cl['id'] ?>">
                    <button type="submit" class="btn btn-danger btn-sm">🗑️</button>
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

<!-- Add/Edit Modal -->
<div class="modal-overlay" id="classModal">
<div class="modal">
    <div class="modal-header">
        <div class="modal-title" id="modalTitle">Schedule a Class</div>
        <button class="modal-close" onclick="closeClassModal()">×</button>
    </div>
    <form method="POST" id="classForm">
        <?= csrfField() ?>
        <input type="hidden" name="form_action" value="save">
        <input type="hidden" name="class_id" id="classIdField" value="0">
        <div class="modal-body">
            <div class="form-grid form-grid-2">
                <div class="form-group full">
                    <label>Course <span class="required">*</span></label>
                    <select name="course_id" id="mCourse" required>
                        <option value="">— Select —</option>
                        <?php foreach ($courses as $c): ?>
                        <option value="<?= $c['id'] ?>"><?= xss($c['title']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group full">
                    <label>Class Title <span class="required">*</span></label>
                    <input type="text" name="title" id="mTitle" required>
                </div>
                <div class="form-group">
                    <label>Date <span class="required">*</span></label>
                    <input type="date" name="class_date" id="mDate" required>
                </div>
                <div class="form-group">
                    <label>Time <span class="required">*</span></label>
                    <input type="time" name="class_time" id="mTime" required>
                </div>
                <div class="form-group full">
                    <label>Meeting Link (Zoom/Meet)</label>
                    <input type="url" name="meeting_link" id="mLink" placeholder="https://...">
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status" id="mStatus">
                        <option value="upcoming">Upcoming</option>
                        <option value="live">Live</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                <div class="form-group full">
                    <label>Description</label>
                    <textarea name="description" id="mDesc" rows="3"></textarea>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="closeClassModal()">Cancel</button>
            <button type="submit" class="btn btn-primary">Save Class</button>
        </div>
    </form>
</div>
</div>

</div></div></div>
<script>
function toggleSidebar(){document.getElementById('adminSidebar').classList.toggle('open');}
function toggleDropdown(id){document.getElementById(id).classList.toggle('open');}
function closeClassModal(){
    document.getElementById('classModal').classList.remove('open');
    document.getElementById('classIdField').value='0';
    document.getElementById('classForm').reset();
    document.getElementById('modalTitle').textContent='Schedule a Class';
}
function editClass(cl){
    document.getElementById('classIdField').value=cl.id;
    document.getElementById('mCourse').value=cl.course_id;
    document.getElementById('mTitle').value=cl.title;
    document.getElementById('mDate').value=cl.class_date;
    document.getElementById('mTime').value=cl.class_time;
    document.getElementById('mLink').value=cl.meeting_link||'';
    document.getElementById('mStatus').value=cl.status;
    document.getElementById('mDesc').value=cl.description||'';
    document.getElementById('modalTitle').textContent='Edit Class';
    document.getElementById('classModal').classList.add('open');
}
document.addEventListener('click',function(e){if(!e.target.closest('.topbar-admin')&&!e.target.closest('.dropdown-menu'))document.querySelectorAll('.dropdown-menu').forEach(m=>m.classList.remove('open'));});
</script>
</body></html>
