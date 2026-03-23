<?php
require_once '../config.php';
requireStudentLogin();
$student = getCurrentStudent();
$db = getDB();
$success = $error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
        $error = 'Invalid security token. Please try again.';
    } else {
        $current = $_POST['current_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        if (!password_verify($current, $student['password'])) {
            $error = 'Current password is incorrect.';
        } elseif (strlen($new) < 8) {
            $error = 'New password must be at least 8 characters.';
        } elseif ($new !== $confirm) {
            $error = 'New password and confirm password do not match.';
        } else {
            $hash = password_hash($new, HASH_ALGO, ['cost' => HASH_COST]);
            $db->prepare('UPDATE students SET password = ? WHERE id = ?')->execute([$hash, $student['id']]);
            $success = 'Password updated successfully.';
            $student = getCurrentStudent();
        }
    }
}
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Change Password | LexLearnAI</title>
<link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css"><link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/dashboard.css">
</head><body>
<div class="dashboard-layout">
<?php include '../includes/header.php'; ?>
<div class="main-content" style="width:100%;">
  <div class="topbar"><div class="topbar-title">Change Password</div><a href="<?= SITE_URL ?>/student/dashboard.php" class="btn btn-outline btn-sm">Back to Dashboard</a></div>
  <div class="page-body"><div class="container" style="max-width:640px;padding:0;">
    <div class="card"><div class="card-header"><div class="card-title">Update your login password</div></div><div class="card-body">
      <?php if ($success): ?><div class="alert alert-success" style="margin-bottom:16px;"><?= xss($success) ?></div><?php endif; ?>
      <?php if ($error): ?><div class="alert alert-error" style="margin-bottom:16px;"><?= xss($error) ?></div><?php endif; ?>
      <form method="post">
        <?= csrfField() ?>
        <label class="form-label">Current Password</label><input type="password" name="current_password" class="form-control" required>
        <label class="form-label" style="margin-top:14px;">New Password</label><input type="password" name="new_password" class="form-control" required>
        <label class="form-label" style="margin-top:14px;">Confirm New Password</label><input type="password" name="confirm_password" class="form-control" required>
        <button class="btn btn-primary" style="margin-top:18px;">Save New Password</button>
      </form>
    </div></div>
  </div></div>
</div></div></body></html>
