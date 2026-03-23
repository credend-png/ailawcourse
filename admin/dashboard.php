<?php
require_once '../config.php';
requireAdminLogin();

$db = getDB();

// Stats
$stats = [
    'total_students'  => $db->query("SELECT COUNT(*) FROM students")->fetchColumn(),
    'active_students' => $db->query("SELECT COUNT(*) FROM students WHERE status='active'")->fetchColumn(),
    'total_courses'   => $db->query("SELECT COUNT(*) FROM courses")->fetchColumn(),
    'total_payments'  => $db->query("SELECT COUNT(*) FROM payments WHERE status='success'")->fetchColumn(),
    'total_revenue'   => $db->query("SELECT COALESCE(SUM(amount),0) FROM payments WHERE status='success'")->fetchColumn(),
    'certificates'    => $db->query("SELECT COUNT(*) FROM certificates WHERE status='active'")->fetchColumn(),
    'enrollments'     => $db->query("SELECT COUNT(*) FROM enrollments WHERE status='active'")->fetchColumn(),
    'upcoming_classes'=> $db->query("SELECT COUNT(*) FROM class_schedules WHERE class_date >= CURDATE() AND status='upcoming'")->fetchColumn(),
];

// Recent students
$recentStudents = $db->query("SELECT * FROM students ORDER BY created_at DESC LIMIT 6")->fetchAll();

// Recent payments
$recentPayments = $db->query("SELECT p.*, s.full_name, c.title as course_title FROM payments p JOIN students s ON p.student_id=s.id JOIN courses c ON p.course_id=c.id ORDER BY p.created_at DESC LIMIT 8")->fetchAll();

// Upcoming classes
$upcomingClasses = $db->query("SELECT cs.*, c.title as course_title FROM class_schedules cs JOIN courses c ON cs.course_id=c.id WHERE cs.class_date >= CURDATE() ORDER BY cs.class_date ASC, cs.class_time ASC LIMIT 5")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard | LexLearnAI</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=DM+Sans:wght@400;500;600;700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
  <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/admin.css">
</head>
<body>
<div class="dashboard-layout">

  <!-- ADMIN SIDEBAR -->
  <aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
      <div style="display:flex;align-items:center;gap:10px;">
        <div class="brand-logo">⚖</div>
        <div class="brand-text">
          <span class="brand-name">LexLearnAI</span>
          <span class="brand-tag">Admin Panel</span>
        </div>
      </div>
    </div>
    <nav class="sidebar-nav">
      <div class="sidebar-section">
        <div class="sidebar-section-label">Overview</div>
        <a href="<?= SITE_URL ?>/admin/dashboard.php" class="sidebar-link active"><span class="icon">📊</span> Dashboard</a>
      </div>
      <div class="sidebar-section">
        <div class="sidebar-section-label">Academic</div>
        <a href="<?= SITE_URL ?>/admin/courses.php" class="sidebar-link"><span class="icon">📚</span> Courses</a>
        <a href="<?= SITE_URL ?>/admin/classes.php" class="sidebar-link"><span class="icon">📅</span> Class Schedules</a>
        <a href="<?= SITE_URL ?>/admin/materials.php" class="sidebar-link"><span class="icon">📂</span> Study Materials</a>
        <a href="<?= SITE_URL ?>/admin/quizzes.php" class="sidebar-link"><span class="icon">📝</span> Quizzes</a>
      </div>
      <div class="sidebar-section">
        <div class="sidebar-section-label">Students</div>
        <a href="<?= SITE_URL ?>/admin/students.php" class="sidebar-link"><span class="icon">👥</span> All Students</a>
        <a href="<?= SITE_URL ?>/admin/enrollments.php" class="sidebar-link"><span class="icon">📋</span> Enrollments</a>
        <a href="<?= SITE_URL ?>/admin/certificates.php" class="sidebar-link"><span class="icon">🏆</span> Certificates</a>
        <a href="<?= SITE_URL ?>/admin/attendance.php" class="sidebar-link"><span class="icon">✅</span> Attendance</a>
      </div>
      <div class="sidebar-section">
        <div class="sidebar-section-label">Finance</div>
        <a href="<?= SITE_URL ?>/admin/payments.php" class="sidebar-link"><span class="icon">💳</span> Payments</a>
        <a href="<?= SITE_URL ?>/admin/coupons.php" class="sidebar-link"><span class="icon">🏷</span> Coupons</a>
      </div>
      <div class="sidebar-section">
        <div class="sidebar-section-label">Communication</div>
        <a href="<?= SITE_URL ?>/admin/announcements.php" class="sidebar-link"><span class="icon">📣</span> Announcements</a>
        <a href="<?= SITE_URL ?>/admin/contact-messages.php" class="sidebar-link"><span class="icon">💬</span> Contact Messages</a>
      </div>
      <div class="sidebar-section">
        <div class="sidebar-section-label">System</div>
        <a href="<?= SITE_URL ?>/admin/settings.php" class="sidebar-link"><span class="icon">⚙</span> Settings</a>
        <a href="<?= SITE_URL ?>/" target="_blank" class="sidebar-link"><span class="icon">🌐</span> View Website</a>
        <a href="<?= SITE_URL ?>/admin/logout.php" class="sidebar-link" style="color:rgba(255,100,100,0.7);" data-confirm="Logout?"><span class="icon">↩</span> Logout</a>
      </div>
    </nav>
  </aside>

  <!-- MAIN CONTENT -->
  <div class="main-content">
    <div class="topbar">
      <div style="display:flex;align-items:center;gap:12px;">
        <button id="sidebarToggle" style="display:none;background:none;border:none;cursor:pointer;padding:8px;font-size:1.25rem;">☰</button>
        <div class="topbar-title">Dashboard Overview</div>
      </div>
      <div class="topbar-actions">
        <span style="font-size:0.85rem;color:var(--grey-500);">👤 <?= xss($_SESSION['admin_name']) ?></span>
        <span class="badge badge-gold"><?= xss($_SESSION['admin_role']) ?></span>
      </div>
    </div>

    <div class="page-body">

      <!-- STATS GRID -->
      <div class="stats-grid" style="grid-template-columns:repeat(4,1fr);margin-bottom:28px;">
        <div class="stat-card">
          <div class="stat-icon gold">👥</div>
          <div><div class="stat-value"><?= number_format($stats['total_students']) ?></div><div class="stat-label">Total Students</div></div>
        </div>
        <div class="stat-card">
          <div class="stat-icon green">💰</div>
          <div><div class="stat-value">₹<?= number_format($stats['total_revenue']) ?></div><div class="stat-label">Total Revenue</div></div>
        </div>
        <div class="stat-card">
          <div class="stat-icon navy">📚</div>
          <div><div class="stat-value"><?= $stats['total_courses'] ?></div><div class="stat-label">Active Courses</div></div>
        </div>
        <div class="stat-card">
          <div class="stat-icon blue">🏆</div>
          <div><div class="stat-value"><?= $stats['certificates'] ?></div><div class="stat-label">Certificates Issued</div></div>
        </div>
        <div class="stat-card">
          <div class="stat-icon gold">✅</div>
          <div><div class="stat-value"><?= $stats['total_payments'] ?></div><div class="stat-label">Successful Payments</div></div>
        </div>
        <div class="stat-card">
          <div class="stat-icon green">📋</div>
          <div><div class="stat-value"><?= $stats['enrollments'] ?></div><div class="stat-label">Active Enrollments</div></div>
        </div>
        <div class="stat-card">
          <div class="stat-icon navy">👤</div>
          <div><div class="stat-value"><?= $stats['active_students'] ?></div><div class="stat-label">Active Students</div></div>
        </div>
        <div class="stat-card">
          <div class="stat-icon blue">📅</div>
          <div><div class="stat-value"><?= $stats['upcoming_classes'] ?></div><div class="stat-label">Upcoming Classes</div></div>
        </div>
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-bottom:24px;">

        <!-- RECENT STUDENTS -->
        <div class="card">
          <div class="card-header">
            <div class="card-title">👥 Recent Registrations</div>
            <a href="<?= SITE_URL ?>/admin/students.php" class="btn btn-sm btn-outline">View All</a>
          </div>
          <div style="overflow-x:auto;">
            <table class="data-table">
              <thead><tr><th>Student</th><th>Profession</th><th>Joined</th><th>Status</th></tr></thead>
              <tbody>
                <?php foreach ($recentStudents as $st): ?>
                <tr>
                  <td>
                    <div style="font-weight:600;font-size:0.85rem;color:var(--navy);"><?= xss($st['full_name']) ?></div>
                    <div style="font-size:0.75rem;color:var(--grey-400)"><?= xss($st['email']) ?></div>
                  </td>
                  <td><span class="badge badge-navy"><?= xss($st['profession']) ?></span></td>
                  <td style="font-size:0.82rem;color:var(--grey-500)"><?= date('d M', strtotime($st['created_at'])) ?></td>
                  <td><span class="badge badge-<?= $st['status']==='active' ? 'success' : 'error' ?>"><?= $st['status'] ?></span></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>

        <!-- UPCOMING CLASSES -->
        <div class="card">
          <div class="card-header">
            <div class="card-title">📅 Upcoming Classes</div>
            <a href="<?= SITE_URL ?>/admin/classes.php" class="btn btn-sm btn-outline">Manage</a>
          </div>
          <div class="card-body">
            <?php if (empty($upcomingClasses)): ?>
              <p style="color:var(--grey-400);text-align:center;padding:20px;">No upcoming classes. <a href="<?= SITE_URL ?>/admin/classes.php" style="color:var(--navy);">Add one</a></p>
            <?php else: ?>
              <?php foreach ($upcomingClasses as $cls): ?>
              <div style="display:flex;gap:12px;padding:12px 0;border-bottom:1px solid var(--grey-100);align-items:center;">
                <div style="background:var(--gold-pale);border-radius:var(--radius);padding:8px 10px;text-align:center;min-width:46px;flex-shrink:0;">
                  <div style="font-weight:700;color:var(--navy);font-size:1rem;"><?= date('d', strtotime($cls['class_date'])) ?></div>
                  <div style="font-size:0.65rem;color:var(--grey-500);text-transform:uppercase;"><?= date('M', strtotime($cls['class_date'])) ?></div>
                </div>
                <div style="flex:1;min-width:0;">
                  <div style="font-weight:600;font-size:0.875rem;color:var(--navy);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= xss($cls['title']) ?></div>
                  <div style="font-size:0.77rem;color:var(--grey-400)"><?= xss($cls['course_title']) ?> · <?= date('h:i A', strtotime($cls['class_time'])) ?></div>
                </div>
                <span class="badge badge-info"><?= ucfirst($cls['status']) ?></span>
              </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- RECENT PAYMENTS -->
      <div class="card">
        <div class="card-header">
          <div class="card-title">💳 Recent Payments</div>
          <a href="<?= SITE_URL ?>/admin/payments.php" class="btn btn-sm btn-outline">View All</a>
        </div>
        <div class="table-wrap">
          <table class="data-table">
            <thead><tr><th>Student</th><th>Course</th><th>Amount</th><th>Txn ID</th><th>Mode</th><th>Date</th><th>Status</th></tr></thead>
            <tbody>
              <?php foreach ($recentPayments as $pay): ?>
              <tr>
                <td>
                  <div style="font-weight:600;font-size:0.85rem"><?= xss($pay['full_name']) ?></div>
                </td>
                <td style="font-size:0.82rem;color:var(--grey-600);max-width:180px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= xss($pay['course_title']) ?></td>
                <td style="font-weight:700;color:var(--navy)">₹<?= number_format($pay['amount'], 2) ?></td>
                <td style="font-family:var(--font-mono);font-size:0.75rem;color:var(--grey-500)"><?= xss($pay['txnid']) ?></td>
                <td><span class="badge badge-navy"><?= xss($pay['payment_mode'] ?: 'N/A') ?></span></td>
                <td style="font-size:0.82rem;color:var(--grey-500)"><?= formatDate($pay['created_at']) ?></td>
                <td><span class="badge badge-<?= $pay['status']==='success' ? 'success' : ($pay['status']==='pending' ? 'warning' : 'error') ?>"><?= $pay['status'] ?></span></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- QUICK ACTIONS -->
      <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-top:24px;">
        <a href="<?= SITE_URL ?>/admin/courses.php?action=new" class="card" style="text-decoration:none;padding:20px;text-align:center;transition:var(--transition);" onmouseover="this.style.transform='translateY(-4px)'" onmouseout="this.style.transform=''">
          <div style="font-size:2rem;margin-bottom:8px;">📚</div>
          <div style="font-weight:600;color:var(--navy);font-size:0.875rem;">Add New Course</div>
        </a>
        <a href="<?= SITE_URL ?>/admin/classes.php?action=new" class="card" style="text-decoration:none;padding:20px;text-align:center;transition:var(--transition);" onmouseover="this.style.transform='translateY(-4px)'" onmouseout="this.style.transform=''">
          <div style="font-size:2rem;margin-bottom:8px;">📅</div>
          <div style="font-weight:600;color:var(--navy);font-size:0.875rem;">Schedule Class</div>
        </a>
        <a href="<?= SITE_URL ?>/admin/certificates.php?action=new" class="card" style="text-decoration:none;padding:20px;text-align:center;transition:var(--transition);" onmouseover="this.style.transform='translateY(-4px)'" onmouseout="this.style.transform=''">
          <div style="font-size:2rem;margin-bottom:8px;">🏆</div>
          <div style="font-weight:600;color:var(--navy);font-size:0.875rem;">Issue Certificate</div>
        </a>
        <a href="<?= SITE_URL ?>/admin/announcements.php?action=new" class="card" style="text-decoration:none;padding:20px;text-align:center;transition:var(--transition);" onmouseover="this.style.transform='translateY(-4px)'" onmouseout="this.style.transform=''">
          <div style="font-size:2rem;margin-bottom:8px;">📣</div>
          <div style="font-weight:600;color:var(--navy);font-size:0.875rem;">Post Announcement</div>
        </a>
      </div>

    </div>
  </div>
</div>

<style>
@media(max-width:768px){
  #sidebarToggle{display:block!important;}
  .stats-grid{grid-template-columns:repeat(2,1fr)!important;}
  .page-body>div[style*="grid-template-columns:1fr 1fr"]{grid-template-columns:1fr!important;}
  .page-body>div[style*="grid-template-columns:repeat(4"]{grid-template-columns:repeat(2,1fr)!important;}
}
</style>
<script src="<?= SITE_URL ?>/assets/js/main.js"></script>
</body>
</html>
