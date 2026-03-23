<?php
require_once '../config.php';
requireAdminLogin();
$pageTitle='Contact Messages';
$success='';
if ($_SERVER['REQUEST_METHOD']==='POST'&&verifyCsrfToken($_POST['csrf_token']??'')) {
    if ($_POST['form_action']==='mark_read') { $pdo->prepare("UPDATE contact_messages SET is_read=1 WHERE id=?")->execute([(int)$_POST['msg_id']]); $success='Marked as read.'; }
    elseif ($_POST['form_action']==='delete') { $pdo->prepare("DELETE FROM contact_messages WHERE id=?")->execute([(int)$_POST['msg_id']]); $success='Deleted.'; }
}
$msgs=$pdo->query("SELECT * FROM contact_messages ORDER BY created_at DESC")->fetchAll();
$unread=$pdo->query("SELECT COUNT(*) FROM contact_messages WHERE is_read=0")->fetchColumn();
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
    <div class="page-header-left"><div class="page-title">Contact Messages</div><div class="page-subtitle"><?= $unread ?> unread</div></div>
</div>
<div class="card"><div class="card-body" style="padding:0;"><div class="table-responsive">
<table>
    <thead><tr><th>#</th><th>Name</th><th>Email</th><th>Subject</th><th>Message</th><th>Date</th><th>Actions</th></tr></thead>
    <tbody>
    <?php if (empty($msgs)): ?><tr><td colspan="7"><div class="empty-state"><div class="empty-icon">✉️</div><h3>No messages</h3></div></td></tr>
    <?php else: foreach ($msgs as $i=>$m): ?>
    <tr style="<?= !$m['is_read']?'font-weight:600;background:rgba(201,168,76,0.05);':'' ?>">
        <td><?= $i+1 ?><?= !$m['is_read']?' <span class="badge badge-warning" style="font-size:9px;">NEW</span>':'' ?></td>
        <td><?= xss($m['name']) ?></td>
        <td><?= xss($m['email']) ?></td>
        <td><?= xss($m['subject']) ?></td>
        <td style="max-width:250px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= xss(substr($m['message'],0,80)) ?>…</td>
        <td><?= formatDate($m['created_at']) ?></td>
        <td>
            <div class="table-actions">
                <?php if (!$m['is_read']): ?>
                <form method="POST" style="display:inline;"><?= csrfField() ?><input type="hidden" name="form_action" value="mark_read"><input type="hidden" name="msg_id" value="<?= $m['id'] ?>"><button type="submit" class="btn btn-success btn-sm">✓ Read</button></form>
                <?php endif; ?>
                <a href="mailto:<?= xss($m['email']) ?>?subject=Re: <?= rawurlencode($m['subject']) ?>" class="btn btn-outline btn-sm">↩ Reply</a>
                <form method="POST" onsubmit="return confirm('Delete?')" style="display:inline;"><?= csrfField() ?><input type="hidden" name="form_action" value="delete"><input type="hidden" name="msg_id" value="<?= $m['id'] ?>"><button type="submit" class="btn btn-danger btn-sm">🗑️</button></form>
            </div>
        </td>
    </tr>
    <?php endforeach; endif; ?>
    </tbody>
</table>
</div></div></div>
</div></div></div>
<script>function toggleSidebar(){document.getElementById('adminSidebar').classList.toggle('open');}function toggleDropdown(id){document.getElementById(id).classList.toggle('open');}document.addEventListener('click',function(e){if(!e.target.closest('.topbar-admin')&&!e.target.closest('.dropdown-menu'))document.querySelectorAll('.dropdown-menu').forEach(m=>m.classList.remove('open'));});</script>
</body></html>
