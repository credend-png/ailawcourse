<?php
require_once '../config.php';
requireAdminLogin();
$pageTitle = 'Announcements';
$errors=[]; $success='';

if ($_SERVER['REQUEST_METHOD']==='POST'&&verifyCsrfToken($_POST['csrf_token']??'')) {
    $fa=$_POST['form_action']??'';
    if ($fa==='save') {
        $title=sanitize($_POST['title']); $body=sanitize($_POST['body']); $type=sanitize($_POST['type']??'info'); $id=(int)($_POST['ann_id']??0);
        if (!$title||!$body) { $errors[]='Title and content required.'; }
        else {
            if ($id>0) { $pdo->prepare("UPDATE announcements SET title=?,body=?,type=?,updated_at=NOW() WHERE id=?")->execute([$title,$body,$type,$id]); $success='Updated.'; }
            else { $pdo->prepare("INSERT INTO announcements (title,body,type,created_at) VALUES (?,?,?,NOW())")->execute([$title,$body,$type]); $success='Announcement posted.'; }
        }
    } elseif ($fa==='delete') { $pdo->prepare("DELETE FROM announcements WHERE id=?")->execute([(int)$_POST['ann_id']]); $success='Deleted.'; }
}
$anns=$pdo->query("SELECT * FROM announcements ORDER BY created_at DESC")->fetchAll();
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
<?php foreach ($errors as $e): ?><div class="alert alert-danger"><?= xss($e) ?></div><?php endforeach; ?>
<?php if ($success): ?><div class="alert alert-success"><?= xss($success) ?></div><?php endif; ?>

<div class="page-header">
    <div class="page-header-left"><div class="page-title">Announcements</div><div class="page-subtitle">Post notices for students</div></div>
    <div class="page-header-right"><button class="btn btn-primary" onclick="openAnnModal()">+ Post Announcement</button></div>
</div>

<div class="card">
<div class="card-body" style="padding:0;"><div class="table-responsive">
<table>
    <thead><tr><th>#</th><th>Title</th><th>Type</th><th>Content</th><th>Posted</th><th>Actions</th></tr></thead>
    <tbody>
    <?php if (empty($anns)): ?><tr><td colspan="6"><div class="empty-state"><div class="empty-icon">📢</div><h3>No announcements</h3></div></td></tr>
    <?php else: foreach ($anns as $i=>$a): ?>
    <tr>
        <td><?= $i+1 ?></td>
        <td style="font-weight:600;"><?= xss($a['title']) ?></td>
        <td><?php $tc=['info'=>'badge-info','warning'=>'badge-warning','success'=>'badge-success','danger'=>'badge-danger']; ?><span class="badge <?= $tc[$a['type']]??'badge-grey' ?>"><?= ucfirst($a['type']) ?></span></td>
        <td style="max-width:300px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= xss(substr($a['body'],0,80)) ?>…</td>
        <td><?= formatDate($a['created_at']) ?></td>
        <td>
            <div class="table-actions">
                <button class="btn btn-outline btn-sm" onclick="editAnn(<?= htmlspecialchars(json_encode($a)) ?>)">✏️</button>
                <form method="POST" onsubmit="return confirm('Delete?')" style="display:inline;"><?= csrfField() ?><input type="hidden" name="form_action" value="delete"><input type="hidden" name="ann_id" value="<?= $a['id'] ?>"><button type="submit" class="btn btn-danger btn-sm">🗑️</button></form>
            </div>
        </td>
    </tr>
    <?php endforeach; endif; ?>
    </tbody>
</table>
</div></div></div>

<div class="modal-overlay" id="annModal">
<div class="modal">
    <div class="modal-header"><div class="modal-title" id="annModalTitle">Post Announcement</div><button class="modal-close" onclick="closeAnnModal()">×</button></div>
    <form method="POST"><?= csrfField() ?><input type="hidden" name="form_action" value="save"><input type="hidden" name="ann_id" id="annId" value="0">
    <div class="modal-body">
        <div class="form-group" style="margin-bottom:14px;"><label>Title <span class="required">*</span></label><input type="text" name="title" id="annTitle" required></div>
        <div class="form-group" style="margin-bottom:14px;"><label>Type</label><select name="type" id="annType"><option value="info">Info</option><option value="warning">Warning</option><option value="success">Success</option><option value="danger">Urgent</option></select></div>
        <div class="form-group"><label>Content <span class="required">*</span></label><textarea name="body" id="annBody" rows="4" required></textarea></div>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-outline" onclick="closeAnnModal()">Cancel</button><button type="submit" class="btn btn-primary">Post</button></div>
    </form>
</div></div>

</div></div></div>
<script>
function toggleSidebar(){document.getElementById('adminSidebar').classList.toggle('open');}
function toggleDropdown(id){document.getElementById(id).classList.toggle('open');}
function openAnnModal(){document.getElementById('annModal').classList.add('open');}
function closeAnnModal(){document.getElementById('annModal').classList.remove('open');document.getElementById('annId').value=0;document.getElementById('annTitle').value='';document.getElementById('annBody').value='';}
function editAnn(a){document.getElementById('annId').value=a.id;document.getElementById('annTitle').value=a.title;document.getElementById('annType').value=a.type;document.getElementById('annBody').value=a.body;document.getElementById('annModalTitle').textContent='Edit Announcement';document.getElementById('annModal').classList.add('open');}
document.addEventListener('click',function(e){if(!e.target.closest('.topbar-admin')&&!e.target.closest('.dropdown-menu'))document.querySelectorAll('.dropdown-menu').forEach(m=>m.classList.remove('open'));});
</script>
</body></html>
