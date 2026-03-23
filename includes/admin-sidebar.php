<?php
// includes/admin-sidebar.php
$currentPage = basename($_SERVER['PHP_SELF']);
function isActive($page) {
    global $currentPage;
    if (is_array($page)) return in_array($currentPage, $page) ? 'active' : '';
    return $currentPage === $page ? 'active' : '';
}
?>
<aside class="admin-sidebar" id="adminSidebar">
    <div class="sidebar-brand">
        <div class="brand-logo">⚖</div>
        <div>
            <div class="brand-text">LexLearnAI</div>
            <div class="brand-sub">Admin Panel</div>
        </div>
    </div>
    <nav class="sidebar-nav">
        <div class="nav-section-label">Overview</div>
        <a href="<?= ADMIN_URL ?>/dashboard.php" class="<?= isActive('dashboard.php') ?>">
            <span class="nav-icon">📊</span> Dashboard
        </a>

        <div class="nav-section-label">Academics</div>
        <a href="<?= ADMIN_URL ?>/courses.php" class="<?= isActive('courses.php') ?>">
            <span class="nav-icon">📚</span> Courses
        </a>
        <a href="<?= ADMIN_URL ?>/classes.php" class="<?= isActive('classes.php') ?>">
            <span class="nav-icon">📅</span> Class Schedules
        </a>
        <a href="<?= ADMIN_URL ?>/materials.php" class="<?= isActive('materials.php') ?>">
            <span class="nav-icon">📂</span> Study Materials
        </a>
        <a href="<?= ADMIN_URL ?>/quizzes.php" class="<?= isActive('quizzes.php') ?>">
            <span class="nav-icon">🧠</span> Quizzes
        </a>

        <div class="nav-section-label">Students</div>
        <a href="<?= ADMIN_URL ?>/students.php" class="<?= isActive('students.php') ?>">
            <span class="nav-icon">👥</span> All Students
        </a>
        <a href="<?= ADMIN_URL ?>/enrollments.php" class="<?= isActive('enrollments.php') ?>">
            <span class="nav-icon">📋</span> Enrollments
        </a>
        <a href="<?= ADMIN_URL ?>/attendance.php" class="<?= isActive('attendance.php') ?>">
            <span class="nav-icon">✅</span> Attendance
        </a>

        <div class="nav-section-label">Finance</div>
        <a href="<?= ADMIN_URL ?>/payments.php" class="<?= isActive('payments.php') ?>">
            <span class="nav-icon">💳</span> Payments
        </a>
        <a href="<?= ADMIN_URL ?>/coupons.php" class="<?= isActive('coupons.php') ?>">
            <span class="nav-icon">🏷️</span> Coupons
        </a>

        <div class="nav-section-label">Content</div>
        <a href="<?= ADMIN_URL ?>/certificates.php" class="<?= isActive('certificates.php') ?>">
            <span class="nav-icon">🎓</span> Certificates
        </a>
        <a href="<?= ADMIN_URL ?>/announcements.php" class="<?= isActive('announcements.php') ?>">
            <span class="nav-icon">📢</span> Announcements
        </a>
        <a href="<?= ADMIN_URL ?>/contact-messages.php" class="<?= isActive('contact-messages.php') ?>">
            <span class="nav-icon">✉️</span> Contact Messages
        </a>

        <div class="nav-section-label">System</div>
        <a href="<?= ADMIN_URL ?>/settings.php" class="<?= isActive('settings.php') ?>">
            <span class="nav-icon">⚙️</span> Settings
        </a>
        <a href="<?= ADMIN_URL ?>/logout.php">
            <span class="nav-icon">🚪</span> Logout
        </a>
    </nav>
    <div class="sidebar-footer">LexLearnAI &copy; <?= date('Y') ?></div>
</aside>
