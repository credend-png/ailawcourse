<?php
require_once '../config.php';

if (isAdminLoggedIn()) redirect(SITE_URL . '/admin/dashboard.php');

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
        $error = 'Invalid token.';
    } else {
        $email    = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'] ?? '';

        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM admins WHERE email = ?");
        $stmt->execute([$email]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_id']   = $admin['id'];
            $_SESSION['admin_name'] = $admin['name'];
            $_SESSION['admin_role'] = $admin['role'];
            redirect(SITE_URL . '/admin/dashboard.php');
        } else {
            $error = 'Invalid email or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login | LexLearnAI</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
</head>
<body>
<main class="auth-page">
  <div class="container">
    <div class="auth-card" style="max-width:400px;">
      <div class="auth-logo">
        <div class="brand-logo" style="width:56px;height:56px;font-size:1.5rem;margin:0 auto 16px;background:linear-gradient(135deg,var(--navy),var(--navy-mid));color:var(--gold);">⚖</div>
        <h1 style="font-family:var(--font-display);font-size:1.5rem;color:var(--navy);margin-bottom:4px;">Admin Panel</h1>
        <p style="font-size:0.85rem;color:var(--grey-400);">LexLearnAI Administration</p>
      </div>

      <?php if ($error): ?>
        <div class="alert alert-error"><span>⚠</span><div><?= xss($error) ?></div></div>
      <?php endif; ?>

      <form method="POST" data-validate>
        <?= csrfField() ?>
        <div class="form-group">
          <label class="form-label">Admin Email</label>
          <input type="email" name="email" class="form-control" placeholder="admin@lexlearnai.com" required autofocus>
        </div>
        <div class="form-group">
          <label class="form-label">Password</label>
          <input type="password" name="password" class="form-control" placeholder="Your password" required>
        </div>
        <button type="submit" class="btn btn-navy btn-block btn-lg">Sign In to Admin Panel</button>
      </form>

      <p style="text-align:center;margin-top:24px;font-size:0.8rem;color:var(--grey-400);">
        <a href="<?= SITE_URL ?>/" style="color:var(--grey-400);">← Return to Website</a>
      </p>
    </div>
  </div>
</main>
<script src="<?= SITE_URL ?>/assets/js/main.js"></script>
</body>
</html>
