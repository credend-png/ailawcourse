<?php
require_once '../config.php';
requireAdminLogin();
$pageTitle = 'Certificate Management';

$errors = []; $success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    $fa = $_POST['form_action'] ?? '';
    if ($fa === 'issue_cert') {
        $sid = (int)$_POST['student_id'];
        $cid = (int)$_POST['course_id'];
        $vid = strtoupper(sanitize($_POST['verification_id']));
        $issueDate = sanitize($_POST['issue_date']);
        if (!$vid) $vid = generateVerificationId();
        
        // Handle file upload
        $certFile = '';
        if (!empty($_FILES['certificate']['name'])) {
            $uploadDir = UPLOAD_PATH . '/certificates/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            $ext = strtolower(pathinfo($_FILES['certificate']['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, ['pdf','jpg','jpeg','png'])) {
                $errors[] = 'Invalid file type. Upload PDF or image.';
            } elseif ($_FILES['certificate']['size'] > 5 * 1024 * 1024) {
                $errors[] = 'File too large. Max 5MB.';
            } else {
                $fn = 'cert_' . $sid . '_' . time() . '.' . $ext;
                if (move_uploaded_file($_FILES['certificate']['tmp_name'], $uploadDir . $fn)) {
                    $certFile = 'certificates/' . $fn;
                }
            }
        }
        
        if (empty($errors)) {
            // Check existing
            $exists = $pdo->prepare("SELECT id FROM certificates WHERE student_id=? AND course_id=?");
            $exists->execute([$sid, $cid]);
            $existing = $exists->fetch();
            if ($existing) {
                $pdo->prepare("UPDATE certificates SET verification_id=?, issue_date=?, certificate_file=COALESCE(NULLIF(?,''), certificate_file), updated_at=NOW() WHERE id=?")->execute([$vid,$issueDate,$certFile,$existing['id']]);
                $success = 'Certificate updated.';
            } else {
                $pdo->prepare("INSERT INTO certificates (student_id,course_id,verification_id,issue_date,certificate_file,created_at) VALUES (?,?,?,?,?,NOW())")->execute([$sid,$cid,$vid,$issueDate,$certFile]);
                $success = 'Certificate issued successfully.';
            }
        }
    } elseif ($fa === 'delete_cert') {
        $cid = (int)$_POST['cert_id'];
        $pdo->prepare("DELETE FROM certificates WHERE id=?")->execute([$cid]);
        $success = 'Certificate deleted.';
    }
}

$certs = $pdo->query("SELECT cert.*, s.full_name, s.email, c.title as course_title FROM certificates cert JOIN students s ON s.id=cert.student_id JOIN courses c ON c.id=cert.course_id ORDER BY cert.issue_date DESC")->fetchAll();
$enrolledStudents = $pdo->query("SELECT s.id as sid, s.full_name, s.email, en.course_id, c.title as course_title FROM students s JOIN enrollments en ON en.student_id=s.id AND en.status='active' JOIN courses c ON c.id=en.course_id ORDER BY s.full_name")->fetchAll();
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
        <div class="page-title">Certificates</div>
        <div class="page-subtitle"><?= count($certs) ?> issued</div>
    </div>
    <div class="page-header-right">
        <button class="btn btn-primary" onclick="document.getElementById('issueModal').classList.add('open')">+ Issue Certificate</button>
    </div>
</div>

<div class="card">
<div class="card-body" style="padding:0;">
<div class="table-responsive">
<table>
    <thead>
        <tr><th>#</th><th>Student</th><th>Course</th><th>Verification ID</th><th>Issue Date</th><th>Certificate</th><th>Actions</th></tr>
    </thead>
    <tbody>
    <?php if (empty($certs)): ?>
    <tr><td colspan="7"><div class="empty-state"><div class="empty-icon">🎓</div><h3>No certificates issued yet</h3></div></td></tr>
    <?php else: foreach ($certs as $i => $cert): ?>
    <tr>
        <td><?= $i+1 ?></td>
        <td>
            <div style="font-weight:600;"><?= xss($cert['full_name']) ?></div>
            <div style="font-size:12px;color:var(--text-light);"><?= xss($cert['email']) ?></div>
        </td>
        <td><?= xss($cert['course_title']) ?></td>
        <td><code style="font-size:12px;background:var(--light-grey);padding:3px 8px;border-radius:5px;"><?= xss($cert['verification_id']) ?></code></td>
        <td><?= formatDate($cert['issue_date']) ?></td>
        <td>
            <?php if ($cert['certificate_file']): ?>
            <a href="<?= UPLOAD_URL . '/' . $cert['certificate_file'] ?>" target="_blank" class="btn btn-outline btn-sm">📄 View</a>
            <?php else: ?>
            <span style="color:var(--text-light);font-size:12px;">No file</span>
            <?php endif; ?>
        </td>
        <td>
            <form method="POST" onsubmit="return confirm('Delete certificate?')" style="display:inline;">
                <?= csrfField() ?>
                <input type="hidden" name="form_action" value="delete_cert">
                <input type="hidden" name="cert_id" value="<?= $cert['id'] ?>">
                <button type="submit" class="btn btn-danger btn-sm">🗑️</button>
            </form>
        </td>
    </tr>
    <?php endforeach; endif; ?>
    </tbody>
</table>
</div>
</div>
</div>

<!-- Issue Certificate Modal -->
<div class="modal-overlay" id="issueModal">
<div class="modal">
    <div class="modal-header">
        <div class="modal-title">Issue Certificate</div>
        <button class="modal-close" onclick="document.getElementById('issueModal').classList.remove('open')">×</button>
    </div>
    <form method="POST" enctype="multipart/form-data">
        <?= csrfField() ?>
        <input type="hidden" name="form_action" value="issue_cert">
        <div class="modal-body">
            <div class="form-group" style="margin-bottom:14px;">
                <label>Select Student & Course <span class="required">*</span></label>
                <select name="student_id" id="studentSelect" onchange="fillCourse(this)" required>
                    <option value="">— Select Student —</option>
                    <?php foreach ($enrolledStudents as $es): ?>
                    <option value="<?= $es['sid'] ?>" data-course="<?= $es['course_id'] ?>"><?= xss($es['full_name']) ?> — <?= xss($es['course_title']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <input type="hidden" name="course_id" id="courseIdField">
            <div class="form-group" style="margin-bottom:14px;">
                <label>Verification ID</label>
                <input type="text" name="verification_id" placeholder="Leave blank to auto-generate" style="text-transform:uppercase;">
            </div>
            <div class="form-group" style="margin-bottom:14px;">
                <label>Issue Date <span class="required">*</span></label>
                <input type="date" name="issue_date" value="<?= date('Y-m-d') ?>" required>
            </div>
            <div class="form-group">
                <label>Certificate File (PDF or Image)</label>
                <label class="upload-area" for="certFile">
                    <div class="upload-icon">📄</div>
                    <p>Click to upload certificate</p>
                    <small>PDF, JPG, PNG · Max 5MB</small>
                    <input type="file" id="certFile" name="certificate" accept=".pdf,.jpg,.jpeg,.png">
                </label>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="document.getElementById('issueModal').classList.remove('open')">Cancel</button>
            <button type="submit" class="btn btn-primary">Issue Certificate</button>
        </div>
    </form>
</div>
</div>

</div></div></div>
<script>
function toggleSidebar(){document.getElementById('adminSidebar').classList.toggle('open');}
function toggleDropdown(id){document.getElementById(id).classList.toggle('open');}
function fillCourse(sel){
    const opt=sel.options[sel.selectedIndex];
    document.getElementById('courseIdField').value=opt.dataset.course||'';
}
document.addEventListener('click',function(e){if(!e.target.closest('.topbar-admin')&&!e.target.closest('.dropdown-menu'))document.querySelectorAll('.dropdown-menu').forEach(m=>m.classList.remove('open'));});
</script>
</body></html>
