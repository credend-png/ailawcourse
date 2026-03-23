<?php
require_once '../config.php';
requireAdminLogin();
$pageTitle = 'Study Materials';
$errors = []; $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    $fa = $_POST['form_action'] ?? '';
    if ($fa === 'upload') {
        $cid    = (int)$_POST['course_id'];
        $title  = sanitize($_POST['title']);
        $type   = sanitize($_POST['type']);
        $link   = sanitize($_POST['link'] ?? '');
        $visible= (int)($_POST['visible_after_payment'] ?? 1);
        $filePath = '';
        if ($type === 'file' && !empty($_FILES['material_file']['name'])) {
            $uploadDir = UPLOAD_PATH . '/materials/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            $ext = strtolower(pathinfo($_FILES['material_file']['name'], PATHINFO_EXTENSION));
            $allowed = ['pdf','doc','docx','ppt','pptx','xls','xlsx','zip'];
            if (!in_array($ext, $allowed)) { $errors[] = 'Invalid file type.'; }
            elseif ($_FILES['material_file']['size'] > 20*1024*1024) { $errors[] = 'Max 20MB.'; }
            else {
                $fn = 'mat_' . time() . '_' . rand(100,999) . '.' . $ext;
                if (move_uploaded_file($_FILES['material_file']['tmp_name'], $uploadDir.$fn)) $filePath='materials/'.$fn;
            }
        }
        if (empty($errors)) {
            $pdo->prepare("INSERT INTO study_materials (course_id,title,type,file_path,link,visible_after_payment,created_at) VALUES (?,?,?,?,?,?,NOW())")->execute([$cid,$title,$type,$filePath,$link,$visible]);
            $success='Material added.';
        }
    } elseif ($fa === 'delete') {
        $pdo->prepare("DELETE FROM study_materials WHERE id=?")->execute([(int)$_POST['mat_id']]);
        $success='Material deleted.';
    }
}
$materials = $pdo->query("SELECT m.*, c.title as course_title FROM study_materials m JOIN courses c ON c.id=m.course_id ORDER BY m.created_at DESC")->fetchAll();
$courses = $pdo->query("SELECT id,title FROM courses WHERE status='active'")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= $pageTitle ?> – Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
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
    <div class="page-header-left"><div class="page-title">Study Materials</div><div class="page-subtitle">Upload PDFs, notes and links</div></div>
    <div class="page-header-right"><button class="btn btn-primary" onclick="document.getElementById('matModal').classList.add('open')">+ Add Material</button></div>
</div>

<div class="card">
<div class="card-body" style="padding:0;"><div class="table-responsive">
<table>
    <thead><tr><th>#</th><th>Title</th><th>Course</th><th>Type</th><th>Gated</th><th>Added</th><th>Actions</th></tr></thead>
    <tbody>
    <?php if (empty($materials)): ?>
    <tr><td colspan="7"><div class="empty-state"><div class="empty-icon">📂</div><h3>No materials yet</h3></div></td></tr>
    <?php else: foreach ($materials as $i=>$m): ?>
    <tr>
        <td><?= $i+1 ?></td>
        <td style="font-weight:600;"><?= xss($m['title']) ?></td>
        <td><?= xss($m['course_title']) ?></td>
        <td>
            <?php if ($m['type']==='file'&&$m['file_path']): ?>
            <a href="<?= UPLOAD_URL.'/'.$m['file_path'] ?>" target="_blank" class="badge badge-navy">📄 File</a>
            <?php elseif ($m['type']==='link'&&$m['link']): ?>
            <a href="<?= xss($m['link']) ?>" target="_blank" class="badge badge-info">🔗 Link</a>
            <?php else: ?><span class="badge badge-grey">N/A</span><?php endif; ?>
        </td>
        <td><?= $m['visible_after_payment'] ? '<span class="badge badge-warning">After Payment</span>' : '<span class="badge badge-success">Public</span>' ?></td>
        <td><?= formatDate($m['created_at']) ?></td>
        <td>
            <form method="POST" onsubmit="return confirm('Delete?')" style="display:inline;">
                <?= csrfField() ?>
                <input type="hidden" name="form_action" value="delete">
                <input type="hidden" name="mat_id" value="<?= $m['id'] ?>">
                <button type="submit" class="btn btn-danger btn-sm">🗑️</button>
            </form>
        </td>
    </tr>
    <?php endforeach; endif; ?>
    </tbody>
</table>
</div></div></div>

<!-- Modal -->
<div class="modal-overlay" id="matModal">
<div class="modal">
    <div class="modal-header"><div class="modal-title">Add Study Material</div><button class="modal-close" onclick="document.getElementById('matModal').classList.remove('open')">×</button></div>
    <form method="POST" enctype="multipart/form-data">
        <?= csrfField() ?>
        <input type="hidden" name="form_action" value="upload">
        <div class="modal-body">
            <div class="form-group" style="margin-bottom:14px;">
                <label>Course <span class="required">*</span></label>
                <select name="course_id" required>
                    <option value="">— Select —</option>
                    <?php foreach ($courses as $c): ?><option value="<?= $c['id'] ?>"><?= xss($c['title']) ?></option><?php endforeach; ?>
                </select>
            </div>
            <div class="form-group" style="margin-bottom:14px;"><label>Title <span class="required">*</span></label><input type="text" name="title" required></div>
            <div class="form-group" style="margin-bottom:14px;">
                <label>Type</label>
                <select name="type" id="matType" onchange="toggleMatType(this.value)">
                    <option value="file">File Upload</option>
                    <option value="link">External Link</option>
                </select>
            </div>
            <div id="fileSection" class="form-group" style="margin-bottom:14px;">
                <label>Upload File (PDF, DOC, PPT, ZIP · Max 20MB)</label>
                <input type="file" name="material_file" accept=".pdf,.doc,.docx,.ppt,.pptx,.zip">
            </div>
            <div id="linkSection" class="form-group" style="margin-bottom:14px;display:none;">
                <label>External URL</label>
                <input type="url" name="link" placeholder="https://...">
            </div>
            <div class="form-group">
                <label>Visibility</label>
                <select name="visible_after_payment">
                    <option value="1">Only after payment</option>
                    <option value="0">Public</option>
                </select>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="document.getElementById('matModal').classList.remove('open')">Cancel</button>
            <button type="submit" class="btn btn-primary">Upload</button>
        </div>
    </form>
</div>
</div>

</div></div></div>
<script>
function toggleSidebar(){document.getElementById('adminSidebar').classList.toggle('open');}
function toggleDropdown(id){document.getElementById(id).classList.toggle('open');}
function toggleMatType(v){
    document.getElementById('fileSection').style.display=v==='file'?'':'none';
    document.getElementById('linkSection').style.display=v==='link'?'':'none';
}
document.addEventListener('click',function(e){if(!e.target.closest('.topbar-admin')&&!e.target.closest('.dropdown-menu'))document.querySelectorAll('.dropdown-menu').forEach(m=>m.classList.remove('open'));});
</script>
</body></html>
