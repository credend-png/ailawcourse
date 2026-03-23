<?php
require_once '../config.php';
requireAdminLogin();
$pageTitle = 'Coupons';
$errors=[]; $success='';

if ($_SERVER['REQUEST_METHOD']==='POST'&&verifyCsrfToken($_POST['csrf_token']??'')) {
    $fa=$_POST['form_action']??'';
    if ($fa==='save') {
        $code=strtoupper(sanitize($_POST['code']));
        $type=sanitize($_POST['discount_type']);
        $val=(float)$_POST['discount_value'];
        $min=(float)($_POST['min_amount']??0);
        $maxUse=(int)($_POST['max_uses']??0);
        $validFrom=sanitize($_POST['valid_from']??'');
        $validTo=sanitize($_POST['valid_to']??'');
        $cid=(int)($_POST['course_id']??0);
        $id=(int)($_POST['coupon_id']??0);
        $status=sanitize($_POST['status']??'active');
        if (!$code) { $errors[]='Code required.'; }
        else {
            $params=[$code,$type,$val,$min,$maxUse,$validFrom?:null,$validTo?:null,$cid?:null,$status];
            if ($id>0) {
                $pdo->prepare("UPDATE coupons SET code=?,discount_type=?,discount_value=?,min_amount=?,max_uses=?,valid_from=?,valid_to=?,course_id=?,status=? WHERE id=?")->execute([...$params,$id]);
                $success='Coupon updated.';
            } else {
                $pdo->prepare("INSERT INTO coupons (code,discount_type,discount_value,min_amount,max_uses,valid_from,valid_to,course_id,status,used_count,created_at) VALUES (?,?,?,?,?,?,?,?,?,0,NOW())")->execute($params);
                $success='Coupon created.';
            }
        }
    } elseif ($fa==='delete') { $pdo->prepare("DELETE FROM coupons WHERE id=?")->execute([(int)$_POST['coupon_id']]); $success='Deleted.'; }
    elseif ($fa==='toggle') { $pdo->prepare("UPDATE coupons SET status=IF(status='active','inactive','active') WHERE id=?")->execute([(int)$_POST['coupon_id']]); $success='Status updated.'; }
}
$coupons=$pdo->query("SELECT cp.*, c.title as course_title FROM coupons cp LEFT JOIN courses c ON c.id=cp.course_id ORDER BY cp.created_at DESC")->fetchAll();
$courses=$pdo->query("SELECT id,title FROM courses WHERE status='active'")->fetchAll();
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
    <div class="page-header-left"><div class="page-title">Discount Coupons</div></div>
    <div class="page-header-right"><button class="btn btn-primary" onclick="openModal()">+ Add Coupon</button></div>
</div>
<div class="card"><div class="card-body" style="padding:0;"><div class="table-responsive">
<table>
    <thead><tr><th>#</th><th>Code</th><th>Discount</th><th>Min Amount</th><th>Course</th><th>Uses</th><th>Valid</th><th>Status</th><th>Actions</th></tr></thead>
    <tbody>
    <?php if (empty($coupons)): ?><tr><td colspan="9"><div class="empty-state"><div class="empty-icon">🏷️</div><h3>No coupons yet</h3></div></td></tr>
    <?php else: foreach ($coupons as $i=>$cp): ?>
    <tr>
        <td><?= $i+1 ?></td>
        <td><code style="font-size:13px;background:var(--light-grey);padding:3px 8px;border-radius:5px;font-weight:700;"><?= xss($cp['code']) ?></code></td>
        <td><?= $cp['discount_type']==='percent' ? $cp['discount_value'].'%' : '₹'.formatCurrency($cp['discount_value']) ?></td>
        <td><?= $cp['min_amount']>0 ? '₹'.$cp['min_amount'] : '—' ?></td>
        <td><?= $cp['course_title'] ?? '<span style="color:var(--text-light)">All courses</span>' ?></td>
        <td><?= $cp['used_count'] ?><?= $cp['max_uses']>0 ? '/'.$cp['max_uses'] : '' ?></td>
        <td><?= $cp['valid_from']?date('d M',strtotime($cp['valid_from'])):'—' ?><?= $cp['valid_to']?' – '.date('d M Y',strtotime($cp['valid_to'])):'—' ?></td>
        <td><span class="badge <?= $cp['status']==='active'?'badge-success':'badge-danger' ?>"><?= ucfirst($cp['status']) ?></span></td>
        <td>
            <div class="table-actions">
                <button class="btn btn-outline btn-sm" onclick="editCoupon(<?= htmlspecialchars(json_encode($cp)) ?>)">✏️</button>
                <form method="POST" style="display:inline;"><?= csrfField() ?><input type="hidden" name="form_action" value="toggle"><input type="hidden" name="coupon_id" value="<?= $cp['id'] ?>"><button type="submit" class="btn btn-outline btn-sm">🔄</button></form>
                <form method="POST" onsubmit="return confirm('Delete?')" style="display:inline;"><?= csrfField() ?><input type="hidden" name="form_action" value="delete"><input type="hidden" name="coupon_id" value="<?= $cp['id'] ?>"><button type="submit" class="btn btn-danger btn-sm">🗑️</button></form>
            </div>
        </td>
    </tr>
    <?php endforeach; endif; ?>
    </tbody>
</table>
</div></div></div>

<div class="modal-overlay" id="cpModal">
<div class="modal">
    <div class="modal-header"><div class="modal-title" id="cpModalTitle">Add Coupon</div><button class="modal-close" onclick="closeModal()">×</button></div>
    <form method="POST"><?= csrfField() ?><input type="hidden" name="form_action" value="save"><input type="hidden" name="coupon_id" id="cpId" value="0">
    <div class="modal-body">
        <div class="form-grid form-grid-2">
            <div class="form-group full"><label>Coupon Code <span class="required">*</span></label><input type="text" name="code" id="cpCode" required style="text-transform:uppercase;" placeholder="e.g. LAUNCH50"></div>
            <div class="form-group"><label>Discount Type</label><select name="discount_type" id="cpType"><option value="percent">Percentage (%)</option><option value="flat">Flat Amount (₹)</option></select></div>
            <div class="form-group"><label>Discount Value</label><input type="number" name="discount_value" id="cpVal" step="0.01" min="0" required></div>
            <div class="form-group"><label>Min Order Amount</label><input type="number" name="min_amount" id="cpMin" step="0.01" min="0" value="0"></div>
            <div class="form-group"><label>Max Uses (0=unlimited)</label><input type="number" name="max_uses" id="cpMax" min="0" value="0"></div>
            <div class="form-group full"><label>Applicable Course (leave blank for all)</label><select name="course_id" id="cpCourse"><option value="">All Courses</option><?php foreach ($courses as $c): ?><option value="<?= $c['id'] ?>"><?= xss($c['title']) ?></option><?php endforeach; ?></select></div>
            <div class="form-group"><label>Valid From</label><input type="date" name="valid_from" id="cpFrom"></div>
            <div class="form-group"><label>Valid To</label><input type="date" name="valid_to" id="cpTo"></div>
            <div class="form-group full"><label>Status</label><select name="status" id="cpStatus"><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
        </div>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-outline" onclick="closeModal()">Cancel</button><button type="submit" class="btn btn-primary">Save</button></div>
    </form>
</div></div>

</div></div></div>
<script>
function toggleSidebar(){document.getElementById('adminSidebar').classList.toggle('open');}
function toggleDropdown(id){document.getElementById(id).classList.toggle('open');}
function openModal(){document.getElementById('cpModal').classList.add('open');}
function closeModal(){document.getElementById('cpModal').classList.remove('open');document.getElementById('cpId').value=0;}
function editCoupon(c){
    document.getElementById('cpId').value=c.id;
    document.getElementById('cpCode').value=c.code;
    document.getElementById('cpType').value=c.discount_type;
    document.getElementById('cpVal').value=c.discount_value;
    document.getElementById('cpMin').value=c.min_amount;
    document.getElementById('cpMax').value=c.max_uses;
    document.getElementById('cpCourse').value=c.course_id||'';
    document.getElementById('cpFrom').value=c.valid_from||'';
    document.getElementById('cpTo').value=c.valid_to||'';
    document.getElementById('cpStatus').value=c.status;
    document.getElementById('cpModalTitle').textContent='Edit Coupon';
    document.getElementById('cpModal').classList.add('open');
}
document.addEventListener('click',function(e){if(!e.target.closest('.topbar-admin')&&!e.target.closest('.dropdown-menu'))document.querySelectorAll('.dropdown-menu').forEach(m=>m.classList.remove('open'));});
</script>
</body></html>
