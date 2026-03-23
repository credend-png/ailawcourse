<?php
require_once '../config.php';
requireStudentLogin();
$student = getCurrentStudent();

$payments = $pdo->prepare("SELECT p.*, c.title as course_title FROM payments p LEFT JOIN courses c ON c.id=p.course_id WHERE p.student_id=? ORDER BY p.created_at DESC");
$payments->execute([$student['id']]);
$payments = $payments->fetchAll();

$totalPaid = array_sum(array_column(array_filter($payments, fn($p) => $p['status']==='success'), 'amount'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>My Payments – LexLearnAI</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= ASSETS_URL ?>/css/style.css">
<link rel="stylesheet" href="<?= ASSETS_URL ?>/css/admin.css">
<style>
.student-layout{display:flex;min-height:100vh;}
.student-sidebar{width:240px;background:#0B1D3A;position:fixed;top:0;left:0;height:100vh;overflow-y:auto;z-index:100;padding-top:20px;}
.student-sidebar .brand{padding:16px 20px 24px;border-bottom:1px solid rgba(255,255,255,.1);}
.student-sidebar .brand-name{font-size:16px;font-weight:700;color:#fff;}
.student-sidebar .brand-sub{font-size:11px;color:rgba(255,255,255,.4);}
.student-sidebar nav a{display:flex;align-items:center;gap:10px;padding:11px 20px;color:rgba(255,255,255,.7);font-size:13.5px;font-weight:500;border-left:3px solid transparent;transition:.2s;text-decoration:none;}
.student-sidebar nav a:hover,.student-sidebar nav a.active{color:#fff;background:rgba(255,255,255,.07);border-left-color:#C9A84C;}
.student-main{margin-left:240px;flex:1;background:#F8F9FC;}
.student-topbar{background:#fff;border-bottom:1px solid #DDE1EB;padding:0 24px;height:60px;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:99;}
.student-content{padding:28px;}
@media(max-width:768px){.student-sidebar{transform:translateX(-100%)}.student-main{margin-left:0}}
</style>
</head>
<body>
<div class="student-layout">
<aside class="student-sidebar">
    <div class="brand"><div class="brand-name">⚖ LexLearnAI</div><div class="brand-sub">Student Portal</div></div>
    <nav>
        <a href="dashboard.php">📊 Dashboard</a>
        <a href="my-courses.php">📚 My Courses</a>
        <a href="schedule.php">📅 Schedule</a>
        <a href="materials.php">📂 Materials</a>
        <a href="quizzes.php">🧠 Quizzes</a>
        <a href="certificate.php">🎓 Certificate</a>
        <a href="payments.php" class="active">💳 Payments</a>
        <a href="profile.php">👤 Profile</a>
        <a href="../auth/logout.php">🚪 Logout</a>
    </nav>
</aside>
<div class="student-main">
<div class="student-topbar">
    <div style="font-weight:600;">Payment History</div>
    <div style="font-size:13px;color:#8A94A6;"><?= xss($student['full_name']) ?></div>
</div>
<div class="student-content">

<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:16px;margin-bottom:28px;">
    <div class="stat-card"><div class="stat-icon success">💰</div><div class="stat-info"><div class="stat-value"><?= formatCurrency($totalPaid) ?></div><div class="stat-label">Total Paid</div></div></div>
    <div class="stat-card"><div class="stat-icon navy">📋</div><div class="stat-info"><div class="stat-value"><?= count($payments) ?></div><div class="stat-label">Transactions</div></div></div>
</div>

<div class="card">
<div class="card-header"><div class="card-title">Transactions</div></div>
<div class="card-body" style="padding:0;"><div class="table-responsive">
<table>
    <thead><tr><th>#</th><th>Course</th><th>Amount</th><th>Discount</th><th>Txn ID</th><th>Status</th><th>Date</th></tr></thead>
    <tbody>
    <?php if (empty($payments)): ?>
    <tr><td colspan="7"><div class="empty-state"><div class="empty-icon">💳</div><h3>No transactions yet</h3></div></td></tr>
    <?php else: foreach ($payments as $i=>$p): ?>
    <tr>
        <td><?= $i+1 ?></td>
        <td style="font-weight:600;"><?= xss($p['course_title'] ?? '—') ?></td>
        <td><strong><?= formatCurrency($p['amount']) ?></strong></td>
        <td><?= $p['discount']>0 ? '-'.formatCurrency($p['discount']) : '—' ?></td>
        <td style="font-family:monospace;font-size:12px;"><?= xss($p['txn_id']) ?></td>
        <td>
            <?php $sc=['success'=>'badge-success','pending'=>'badge-warning','failed'=>'badge-danger']; ?>
            <span class="badge <?= $sc[$p['status']]??'badge-grey' ?>"><?= ucfirst($p['status']) ?></span>
        </td>
        <td><?= formatDate($p['created_at']) ?></td>
    </tr>
    <?php endforeach; endif; ?>
    </tbody>
</table>
</div></div>
</div>

</div>
</div>
</div>
</div>
</body>
</html>
