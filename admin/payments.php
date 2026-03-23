<?php
require_once '../config.php';
requireAdminLogin();
$pageTitle = 'Payments';

$errors = []; $success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    $fa = $_POST['form_action'] ?? '';
    if ($fa === 'manual_verify') {
        $pid = (int)$_POST['payment_id'];
        $pdo->prepare("UPDATE payments SET status='success', notes='Manually verified by admin', updated_at=NOW() WHERE id=?")->execute([$pid]);
        // Also activate enrollment
        $payment = $pdo->prepare("SELECT * FROM payments WHERE id=?");
        $payment->execute([$pid]);
        $payment = $payment->fetch();
        if ($payment) {
            $exists = $pdo->prepare("SELECT id FROM enrollments WHERE student_id=? AND course_id=?");
            $exists->execute([$payment['student_id'], $payment['course_id']]);
            if (!$exists->fetch()) {
                $pdo->prepare("INSERT INTO enrollments (student_id,course_id,status,enrolled_at) VALUES (?,?,'active',NOW())")->execute([$payment['student_id'],$payment['course_id']]);
            } else {
                $pdo->prepare("UPDATE enrollments SET status='active' WHERE student_id=? AND course_id=?")->execute([$payment['student_id'],$payment['course_id']]);
            }
        }
        $success = 'Payment manually verified and enrollment activated.';
    }
}

// Filters
$statusFilter = sanitize($_GET['status'] ?? '');
$search = sanitize($_GET['search'] ?? '');
$where = "WHERE 1=1";
$params = [];
if ($statusFilter) { $where .= " AND p.status=?"; $params[] = $statusFilter; }
if ($search) { $where .= " AND (s.full_name LIKE ? OR s.email LIKE ? OR p.txn_id LIKE ?)"; $params = array_merge($params, ["%$search%","%$search%","%$search%"]); }

$stmt = $pdo->prepare("SELECT p.*, s.full_name, s.email, c.title as course_title FROM payments p JOIN students s ON s.id=p.student_id LEFT JOIN courses c ON c.id=p.course_id $where ORDER BY p.created_at DESC");
$stmt->execute($params);
$payments = $stmt->fetchAll();

$totalRevenue = $pdo->query("SELECT SUM(amount) FROM payments WHERE status='success'")->fetchColumn();
$pendingCount = $pdo->query("SELECT COUNT(*) FROM payments WHERE status='pending'")->fetchColumn();
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

<?php if ($success): ?><div class="alert alert-success"><?= xss($success) ?></div><?php endif; ?>
<?php foreach ($errors as $e): ?><div class="alert alert-danger"><?= xss($e) ?></div><?php endforeach; ?>

<div class="page-header">
    <div class="page-header-left">
        <div class="page-title">Payments</div>
        <div class="page-subtitle">All transactions</div>
    </div>
</div>

<div class="stats-grid" style="margin-bottom:24px;">
    <div class="stat-card">
        <div class="stat-icon success">💰</div>
        <div class="stat-info"><div class="stat-value"><?= formatCurrency($totalRevenue ?? 0) ?></div><div class="stat-label">Total Revenue</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon warning">⏳</div>
        <div class="stat-info"><div class="stat-value"><?= $pendingCount ?></div><div class="stat-label">Pending Payments</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon navy">📊</div>
        <div class="stat-info"><div class="stat-value"><?= count($payments) ?></div><div class="stat-label">Total Transactions</div></div>
    </div>
</div>

<div class="filters-bar">
    <div class="filter-group">
        <span class="search-icon">🔍</span>
        <input type="text" placeholder="Search name, email, txn ID..." value="<?= xss($search) ?>" oninput="filterRows(this.value)">
    </div>
    <select onchange="window.location='payments.php?status='+this.value">
        <option value="">All Statuses</option>
        <option value="success" <?= $statusFilter==='success'?'selected':'' ?>>Success</option>
        <option value="pending" <?= $statusFilter==='pending'?'selected':'' ?>>Pending</option>
        <option value="failed" <?= $statusFilter==='failed'?'selected':'' ?>>Failed</option>
    </select>
</div>

<div class="card">
<div class="card-body" style="padding:0;">
<div class="table-responsive">
<table id="paymentsTable">
    <thead>
        <tr>
            <th>#</th><th>Student</th><th>Course</th><th>Amount</th><th>Txn ID</th><th>PayU ID</th><th>Status</th><th>Date</th><th>Action</th>
        </tr>
    </thead>
    <tbody>
    <?php if (empty($payments)): ?>
    <tr><td colspan="9"><div class="empty-state"><div class="empty-icon">💳</div><h3>No payments found</h3></div></td></tr>
    <?php else: foreach ($payments as $i => $p): ?>
    <tr>
        <td><?= $i+1 ?></td>
        <td>
            <div style="font-weight:600;"><?= xss($p['full_name']) ?></div>
            <div style="font-size:12px;color:var(--text-light);"><?= xss($p['email']) ?></div>
        </td>
        <td><?= xss($p['course_title'] ?? '—') ?></td>
        <td><strong><?= formatCurrency($p['amount']) ?></strong>
            <?php if ($p['discount'] > 0): ?><br><small style="color:var(--success);">-<?= formatCurrency($p['discount']) ?> discount</small><?php endif; ?>
        </td>
        <td style="font-family:monospace;font-size:12px;"><?= xss($p['txn_id']) ?></td>
        <td style="font-family:monospace;font-size:12px;"><?= xss($p['payu_txn_id'] ?? '—') ?></td>
        <td>
            <?php $sc = ['success'=>'badge-success','pending'=>'badge-warning','failed'=>'badge-danger']; ?>
            <span class="badge <?= $sc[$p['status']] ?? 'badge-grey' ?>"><?= ucfirst($p['status']) ?></span>
        </td>
        <td><?= formatDate($p['created_at']) ?></td>
        <td>
            <?php if ($p['status'] === 'pending'): ?>
            <form method="POST" onsubmit="return confirm('Manually verify this payment?')">
                <?= csrfField() ?>
                <input type="hidden" name="form_action" value="manual_verify">
                <input type="hidden" name="payment_id" value="<?= $p['id'] ?>">
                <button type="submit" class="btn btn-success btn-sm">✓ Verify</button>
            </form>
            <?php else: ?>—<?php endif; ?>
        </td>
    </tr>
    <?php endforeach; endif; ?>
    </tbody>
</table>
</div>
</div>
</div>

</div></div></div>
<script>
function toggleSidebar(){document.getElementById('adminSidebar').classList.toggle('open');}
function toggleDropdown(id){document.getElementById(id).classList.toggle('open');}
function filterRows(q){
    q=q.toLowerCase();
    document.querySelectorAll('#paymentsTable tbody tr').forEach(r=>{r.style.display=r.textContent.toLowerCase().includes(q)?'':'none';});
}
document.addEventListener('click',function(e){if(!e.target.closest('.topbar-admin')&&!e.target.closest('.dropdown-menu'))document.querySelectorAll('.dropdown-menu').forEach(m=>m.classList.remove('open'));});
</script>
</body></html>
