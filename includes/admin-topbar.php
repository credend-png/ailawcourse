<?php
// includes/admin-topbar.php
$admin = getCurrentAdmin();
$initials = strtoupper(substr($admin['name'] ?? 'A', 0, 1));
?>
<header class="admin-topbar">
    <button class="topbar-toggle" onclick="toggleSidebar()">☰</button>
    <span class="topbar-title"><?= $pageTitle ?? 'Dashboard' ?></span>
    <div class="topbar-spacer"></div>
    <div class="topbar-actions">
        <a href="<?= SITE_URL ?>" target="_blank" class="topbar-btn" title="View Website">🌐</a>
        <div class="topbar-admin" onclick="toggleDropdown('adminDropdown')">
            <div class="admin-avatar"><?= $initials ?></div>
            <div class="admin-info">
                <div class="admin-name"><?= xss($admin['name'] ?? 'Admin') ?></div>
                <div class="admin-role">Super Admin</div>
            </div>
            <span style="font-size:11px;color:var(--text-light);margin-left:4px;">▼</span>
        </div>
        <div class="dropdown-menu" id="adminDropdown" style="right:0;top:55px;position:fixed;">
            <div class="dropdown-item">👤 Profile</div>
            <div class="dropdown-divider"></div>
            <a href="<?= ADMIN_URL ?>/logout.php" class="dropdown-item danger">🚪 Logout</a>
        </div>
    </div>
</header>
