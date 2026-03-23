<?php
require_once '../config.php';
requireStudentLogin();

$student = getCurrentStudent();
if (!$student) { session_destroy(); redirect(SITE_URL . '/auth/login.php'); }

$db = getDB();

// Get enrollments with course data
$enrollments = $db->prepare("
    SELECT e.*, c.title, c.slug, c.duration_weeks, c.total_classes, c.start_date, c.end_date,
           c.thumbnail, c.fee
    FROM enrollments e
    JOIN courses c ON e.course_id = c.id
    WHERE e.student_id = ?
    ORDER BY e.enrolled_at DESC
");
$enrollments->execute([$student['id']]);
$enrollments = $enrollments->fetchAll();

// Get announcements
$announcements = $db->query("SELECT * FROM announcements WHERE status='active' ORDER BY is_pinned DESC, created_at DESC LIMIT 5")->fetchAll();

// Get upcoming classes for enrolled courses
$upcomingClasses = [];
if (!empty($enrollments)) {
    $courseIds = array_column($enrollments, 'course_id');
    $placeholders = implode(',', array_fill(0, count($courseIds), '?'));
    $stmt = $db->prepare("
        SELECT cs.*, c.title as course_title
        FROM class_schedules cs
        JOIN courses c ON cs.course_id = c.id
        WHERE cs.course_id IN ($placeholders)
        AND cs.class_date >= CURDATE()
        AND cs.status IN ('upcoming','live')
        ORDER BY cs.class_date ASC, cs.class_time ASC
        LIMIT 5
    ");
    $stmt->execute($courseIds);
    $upcomingClasses = $stmt->fetchAll();
}

// Get certificate
$certificates = $db->prepare("SELECT cert.*, c.title as course_title FROM certificates cert JOIN courses c ON cert.course_id = c.id WHERE cert.student_id = ? AND cert.status='active'")->execute([$student['id']]);

$pageTitle = 'My Dashboard';
$isDashboard = true;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard | LexLearnAI</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700;900&family=DM+Sans:wght@300;400;500;600;700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
  <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/dashboard.css">
</head>
<body>
<div class="dashboard-layout">

  <!-- SIDEBAR -->
  <aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
      <a href="<?= SITE_URL ?>/" style="display:flex;align-items:center;gap:10px;text-decoration:none;">
        <div class="brand-logo">⚖</div>
        <div class="brand-text">
          <span class="brand-name">LexLearnAI</span>
          <span class="brand-tag">Student Portal</span>
        </div>
      </a>
    </div>
    <nav class="sidebar-nav">
      <div class="sidebar-section">
        <div class="sidebar-section-label">Main</div>
        <a href="<?= SITE_URL ?>/student/dashboard.php" class="sidebar-link active">
          <span class="icon">📊</span> Dashboard
        </a>
        <a href="<?= SITE_URL ?>/student/my-courses.php" class="sidebar-link">
          <span class="icon">📚</span> My Courses
        </a>
        <a href="<?= SITE_URL ?>/student/schedule.php" class="sidebar-link">
          <span class="icon">📅</span> Class Schedule
        </a>
        <a href="<?= SITE_URL ?>/student/materials.php" class="sidebar-link">
          <span class="icon">📂</span> Study Materials
        </a>
        <a href="<?= SITE_URL ?>/student/quizzes.php" class="sidebar-link">
          <span class="icon">📝</span> Quizzes & Tests
        </a>
      </div>
      <div class="sidebar-section">
        <div class="sidebar-section-label">Account</div>
        <a href="<?= SITE_URL ?>/student/certificate.php" class="sidebar-link">
          <span class="icon">🏆</span> My Certificate
        </a>
        <a href="<?= SITE_URL ?>/student/payments.php" class="sidebar-link">
          <span class="icon">💳</span> Payments
        </a>
        <a href="<?= SITE_URL ?>/student/profile.php" class="sidebar-link">
          <span class="icon">👤</span> My Profile
        </a>
        <a href="<?= SITE_URL ?>/student/change-password.php" class="sidebar-link">
          <span class="icon">🔒</span> Change Password
        </a>
      </div>
      <div class="sidebar-section">
        <a href="<?= SITE_URL ?>/pages/courses.php" class="sidebar-link">
          <span class="icon">➕</span> Browse More Courses
        </a>
        <a href="<?= SITE_URL ?>/auth/logout.php" class="sidebar-link" style="color:rgba(255,100,100,0.7);" data-confirm="Are you sure you want to logout?">
          <span class="icon">↩</span> Logout
        </a>
      </div>
    </nav>
  </aside>

  <!-- MAIN CONTENT -->
  <div class="main-content">
    <!-- TOPBAR -->
    <div class="topbar">
      <div style="display:flex;align-items:center;gap:12px;">
        <button id="sidebarToggle" style="display:none;background:none;border:none;cursor:pointer;padding:8px;" onclick="document.getElementById('sidebar').classList.toggle('open')">☰</button>
        <div class="topbar-title">Dashboard</div>
      </div>
      <div class="topbar-actions">
        <a href="<?= SITE_URL ?>/pages/courses.php" class="btn btn-primary btn-sm">+ Enroll New Course</a>
        <div class="topbar-user">
          <div class="user-avatar">
            <?php if ($student['profile_photo']): ?>
              <img src="<?= UPLOAD_URL . '/' . xss($student['profile_photo']) ?>" style="width:36px;height:36px;border-radius:50%;object-fit:cover;">
            <?php else: ?>
              <?= strtoupper(substr($student['full_name'], 0, 2)) ?>
            <?php endif; ?>
          </div>
          <div>
            <div style="font-size:0.875rem;font-weight:600;color:var(--grey-700)"><?= xss($student['full_name']) ?></div>
            <div style="font-size:0.75rem;color:var(--grey-400)"><?= xss($student['profession']) ?></div>
          </div>
        </div>
      </div>
    </div>

    <div class="page-body">

      <!-- WELCOME -->
      <div style="background:linear-gradient(135deg,var(--navy),var(--navy-mid));border-radius:var(--radius-lg);padding:28px 32px;margin-bottom:24px;position:relative;overflow:hidden;">
        <div style="position:absolute;inset:0;background:linear-gradient(rgba(201,168,76,0.04) 1px,transparent 1px),linear-gradient(90deg,rgba(201,168,76,0.04) 1px,transparent 1px);background-size:32px 32px;"></div>
        <div style="position:relative;z-index:1;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px;">
          <div>
            <h2 style="color:var(--white);font-family:var(--font-display);font-size:1.5rem;margin-bottom:6px;">Welcome back, <?= xss(explode(' ', $student['full_name'])[0]) ?>! 👋</h2>
            <p style="color:rgba(255,255,255,0.65);font-size:0.9rem;">Continue your legal AI education journey.</p>
          </div>
          <div style="display:flex;gap:24px;">
            <div style="text-align:center;">
              <div style="font-family:var(--font-display);font-size:1.5rem;font-weight:700;color:var(--gold)"><?= count($enrollments) ?></div>
              <div style="font-size:0.75rem;color:rgba(255,255,255,0.5)">Enrolled</div>
            </div>
            <div style="text-align:center;">
              <div style="font-family:var(--font-display);font-size:1.5rem;font-weight:700;color:var(--gold)"><?= count($upcomingClasses) ?></div>
              <div style="font-size:0.75rem;color:rgba(255,255,255,0.5)">Upcoming Classes</div>
            </div>
          </div>
        </div>
      </div>

      <!-- ANNOUNCEMENTS -->
      <?php if (!empty($announcements)): ?>
      <div style="margin-bottom:24px;">
        <?php foreach (array_slice($announcements, 0, 2) as $ann): ?>
        <div class="alert alert-<?= $ann['type'] === 'urgent' ? 'error' : ($ann['type'] === 'warning' ? 'warning' : ($ann['type'] === 'success' ? 'success' : 'info')) ?>" style="margin-bottom:8px;">
          <span><?= $ann['is_pinned'] ? '📌' : 'ℹ' ?></span>
          <div>
            <strong><?= xss($ann['title']) ?></strong><br>
            <span style="font-size:0.85rem;"><?= xss($ann['content']) ?></span>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

      <div style="display:grid;grid-template-columns:2fr 1fr;gap:24px;">

        <!-- LEFT COLUMN -->
        <div>
          <!-- MY COURSES -->
          <div class="card" style="margin-bottom:24px;">
            <div class="card-header">
              <div class="card-title">📚 My Enrolled Courses</div>
              <a href="<?= SITE_URL ?>/student/my-courses.php" class="btn btn-sm btn-outline">View All</a>
            </div>
            <div class="card-body">
              <?php if (empty($enrollments)): ?>
                <div style="text-align:center;padding:40px;color:var(--grey-400);">
                  <div style="font-size:3rem;margin-bottom:16px;">📚</div>
                  <p style="margin-bottom:16px;">You haven't enrolled in any courses yet.</p>
                  <a href="<?= SITE_URL ?>/pages/courses.php" class="btn btn-primary">Browse Courses</a>
                </div>
              <?php else: ?>
                <?php foreach ($enrollments as $enr): ?>
                <div style="display:flex;gap:16px;padding:16px 0;border-bottom:1px solid var(--grey-100);align-items:flex-start;" class="last-no-border">
                  <div style="width:56px;height:56px;background:linear-gradient(135deg,var(--navy),var(--navy-mid));border-radius:var(--radius-md);flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:1.5rem;">⚖️</div>
                  <div style="flex:1;min-width:0;">
                    <div style="font-weight:600;color:var(--navy);margin-bottom:4px;font-size:0.925rem;"><?= xss($enr['title']) ?></div>
                    <div style="font-size:0.8rem;color:var(--grey-400);margin-bottom:10px;">
                      <?php if ($enr['start_date']): ?>Starts: <?= formatDate($enr['start_date']) ?> | <?php endif; ?>
                      Status: <span class="badge badge-<?= $enr['status'] === 'active' ? 'success' : ($enr['status'] === 'completed' ? 'info' : 'warning') ?>"><?= ucfirst($enr['status']) ?></span>
                    </div>
                    <div style="display:flex;align-items:center;gap:10px;">
                      <div class="progress-wrap" style="flex:1;height:6px;">
                        <div class="progress-bar" data-width="<?= $enr['progress_percentage'] ?>"></div>
                      </div>
                      <span style="font-size:0.75rem;color:var(--grey-500);flex-shrink:0;"><?= $enr['progress_percentage'] ?>%</span>
                    </div>
                  </div>
                  <a href="<?= SITE_URL ?>/student/course-view.php?id=<?= $enr['course_id'] ?>" class="btn btn-sm btn-navy">Access</a>
                </div>
                <?php endforeach; ?>
              <?php endif; ?>
            </div>
          </div>

          <!-- UPCOMING CLASSES -->
          <div class="card">
            <div class="card-header">
              <div class="card-title">📅 Upcoming Live Classes</div>
              <a href="<?= SITE_URL ?>/student/schedule.php" class="btn btn-sm btn-outline">Full Schedule</a>
            </div>
            <div class="card-body">
              <?php if (empty($upcomingClasses)): ?>
                <p style="color:var(--grey-400);text-align:center;padding:24px;">No upcoming classes scheduled yet.</p>
              <?php else: ?>
                <?php foreach ($upcomingClasses as $cls): ?>
                <div style="display:flex;gap:16px;padding:14px 0;border-bottom:1px solid var(--grey-100);align-items:center;">
                  <div style="background:var(--gold-pale);border-radius:var(--radius);padding:10px 14px;text-align:center;flex-shrink:0;min-width:52px;">
                    <div style="font-family:var(--font-display);font-weight:700;color:var(--navy);font-size:1.1rem;"><?= date('d', strtotime($cls['class_date'])) ?></div>
                    <div style="font-size:0.68rem;color:var(--grey-500);text-transform:uppercase;"><?= date('M', strtotime($cls['class_date'])) ?></div>
                  </div>
                  <div style="flex:1;">
                    <div style="font-weight:600;font-size:0.9rem;color:var(--navy);"><?= xss($cls['title']) ?></div>
                    <div style="font-size:0.8rem;color:var(--grey-400);"><?= xss($cls['course_title']) ?> · <?= date('h:i A', strtotime($cls['class_time'])) ?> IST</div>
                  </div>
                  <?php if ($cls['meeting_link']): ?>
                    <a href="<?= xss($cls['meeting_link']) ?>" target="_blank" class="btn btn-primary btn-sm">Join →</a>
                  <?php endif; ?>
                </div>
                <?php endforeach; ?>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <!-- RIGHT COLUMN -->
        <div>
          <!-- PROFILE QUICK INFO -->
          <div class="card" style="margin-bottom:20px;">
            <div class="card-body" style="text-align:center;">
              <div style="width:72px;height:72px;background:linear-gradient(135deg,var(--navy),var(--navy-mid));border-radius:50%;display:flex;align-items:center;justify-content:center;color:var(--gold);font-family:var(--font-display);font-weight:700;font-size:1.5rem;margin:0 auto 12px;">
                <?= strtoupper(substr($student['full_name'], 0, 2)) ?>
              </div>
              <div style="font-weight:700;color:var(--navy);font-size:1rem;"><?= xss($student['full_name']) ?></div>
              <div style="font-size:0.8rem;color:var(--grey-400);margin-bottom:16px;"><?= xss($student['email']) ?></div>
              <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;font-size:0.78rem;text-align:left;background:var(--grey-50);padding:12px;border-radius:var(--radius);margin-bottom:16px;">
                <div><span style="color:var(--grey-400)">Profession:</span><br><strong style="color:var(--grey-700)"><?= xss($student['profession']) ?></strong></div>
                <div><span style="color:var(--grey-400)">Member since:</span><br><strong style="color:var(--grey-700)"><?= date('M Y', strtotime($student['created_at'])) ?></strong></div>
              </div>
              <a href="<?= SITE_URL ?>/student/profile.php" class="btn btn-outline btn-sm btn-block">Edit Profile</a>
            </div>
          </div>

          <!-- CERTIFICATE STATUS -->
          <?php
          $certStmt = $db->prepare("SELECT cert.*, c.title as course_title FROM certificates cert JOIN courses c ON cert.course_id = c.id WHERE cert.student_id = ? AND cert.status='active' LIMIT 1");
          $certStmt->execute([$student['id']]);
          $cert = $certStmt->fetch();
          ?>
          <div class="card" style="margin-bottom:20px;">
            <div class="card-header"><div class="card-title">🏆 My Certificate</div></div>
            <div class="card-body" style="text-align:center;">
              <?php if ($cert): ?>
                <div style="background:var(--gold-pale);border:2px solid var(--gold);border-radius:var(--radius);padding:16px;margin-bottom:16px;">
                  <div style="font-size:2rem;margin-bottom:8px;">🎓</div>
                  <div style="font-size:0.8rem;color:var(--grey-600);margin-bottom:4px;"><?= xss($cert['course_title']) ?></div>
                  <div style="font-family:var(--font-mono);font-size:0.75rem;color:var(--navy);font-weight:600;"><?= xss($cert['verification_id']) ?></div>
                </div>
                <a href="<?= UPLOAD_URL . '/' . xss($cert['certificate_file']) ?>" download class="btn btn-primary btn-sm btn-block" style="margin-bottom:8px;">⬇ Download Certificate</a>
                <a href="<?= SITE_URL ?>/pages/verify-certificate.php?id=<?= xss($cert['verification_id']) ?>" class="btn btn-outline btn-sm btn-block">🔗 View Public Page</a>
              <?php else: ?>
                <div style="color:var(--grey-400);padding:20px 0;">
                  <div style="font-size:2rem;margin-bottom:8px;">🏅</div>
                  <p style="font-size:0.85rem;">Complete your course to receive your certificate.</p>
                </div>
              <?php endif; ?>
            </div>
          </div>

          <!-- QUICK LINKS -->
          <div class="card">
            <div class="card-header"><div class="card-title">Quick Links</div></div>
            <div class="card-body" style="display:flex;flex-direction:column;gap:8px;">
              <a href="<?= SITE_URL ?>/student/materials.php" style="display:flex;align-items:center;gap:10px;padding:10px;border-radius:var(--radius);background:var(--grey-50);font-size:0.875rem;color:var(--grey-700);transition:var(--transition);" class="quick-link">
                <span>📂</span> Study Materials
              </a>
              <a href="<?= SITE_URL ?>/student/quizzes.php" style="display:flex;align-items:center;gap:10px;padding:10px;border-radius:var(--radius);background:var(--grey-50);font-size:0.875rem;color:var(--grey-700);transition:var(--transition);" class="quick-link">
                <span>📝</span> Quizzes & Tests
              </a>
              <a href="<?= SITE_URL ?>/student/payments.php" style="display:flex;align-items:center;gap:10px;padding:10px;border-radius:var(--radius);background:var(--grey-50);font-size:0.875rem;color:var(--grey-700);transition:var(--transition);" class="quick-link">
                <span>💳</span> Payment History
              </a>
              <a href="<?= SITE_URL ?>/pages/contact.php" style="display:flex;align-items:center;gap:10px;padding:10px;border-radius:var(--radius);background:var(--grey-50);font-size:0.875rem;color:var(--grey-700);transition:var(--transition);" class="quick-link">
                <span>💬</span> Contact Support
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<style>
.last-no-border:last-child { border-bottom: none; }
.quick-link:hover { background: var(--grey-100) !important; color: var(--navy) !important; }
@media (max-width: 768px) {
  #sidebarToggle { display: block !important; }
  .page-body > div:last-child { grid-template-columns: 1fr !important; }
}
</style>
<script src="<?= SITE_URL ?>/assets/js/main.js"></script>
</body>
</html>
