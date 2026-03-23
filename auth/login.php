<?php
require_once '../config.php';

if (isStudentLoggedIn()) redirect(SITE_URL . '/student/dashboard.php');

$error = '';
$redirect = sanitize($_GET['redirect'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
        $error = 'Invalid form token. Please refresh and try again.';
    } else {
        $email    = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $error = 'Email and password are required.';
        } else {
            $db = getDB();
            $stmt = $db->prepare("SELECT * FROM students WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            $student = $stmt->fetch();

            if ($student && password_verify($password, $student['password'])) {
                if ($student['status'] === 'suspended') {
                    $error = 'Your account has been suspended. Contact support.';
                } elseif ($student['status'] === 'inactive') {
                    $error = 'Your account is inactive. Contact support.';
                } else {
                    $_SESSION['student_id']   = $student['id'];
                    $_SESSION['student_name'] = $student['full_name'];
                    $_SESSION['student_email'] = $student['email'];

                    $dest = (!empty($redirect) && filter_var($redirect, FILTER_VALIDATE_URL)) ? $redirect : SITE_URL . '/student/dashboard.php';
                    redirect($dest);
                }
            } else {
                $error = 'Invalid email or password. Please try again.';
            }
        }
    }
}

$pageTitle = 'Student Login';
include '../includes/header.php';
?>
<main class="auth-page" style="padding-top:80px;">
  <div class="container">
    <div class="auth-card" style="max-width:440px;">
      <div class="auth-logo">
        <a href="<?= SITE_URL ?>/" class="navbar-brand" style="display:inline-flex;justify-content:center;">
          <div class="brand-logo">⚖</div>
          <div class="brand-text">
            <span class="brand-name" style="color:var(--navy)">LexLearnAI</span>
            <span class="brand-tag">Legal AI Education</span>
          </div>
        </a>
      </div>
      <h1 class="auth-title text-center">Welcome Back</h1>
      <p class="auth-subtitle text-center mb-24">Sign in to your student account</p>

      <?php if ($error): ?>
        <div class="alert alert-error" data-auto-dismiss><span>⚠</span><div><?= xss($error) ?></div></div>
      <?php endif; ?>

      <form method="POST" data-validate>
        <?= csrfField() ?>
        <?php if ($redirect): ?>
          <input type="hidden" name="redirect" value="<?= xss($redirect) ?>">
        <?php endif; ?>

        <div class="form-group">
          <label class="form-label">Email Address</label>
          <input type="email" name="email" class="form-control" placeholder="you@example.com" value="<?= xss($_POST['email'] ?? '') ?>" required autofocus>
        </div>
        <div class="form-group">
          <label class="form-label" style="display:flex;justify-content:space-between;">
            Password
            <a href="<?= SITE_URL ?>/auth/forgot-password.php" style="font-size:0.8rem;color:var(--navy);font-weight:600;">Forgot Password?</a>
          </label>
          <input type="password" name="password" class="form-control" placeholder="Your password" required>
        </div>

        <button type="submit" class="btn btn-primary btn-block btn-lg">Sign In →</button>

        <p style="text-align:center;margin-top:20px;font-size:0.875rem;color:var(--grey-500);">
          Don't have an account? <a href="<?= SITE_URL ?>/auth/register.php" style="color:var(--navy);font-weight:600;">Register Now</a>
        </p>
      </form>

      <div style="margin-top:32px;padding-top:24px;border-top:1px solid var(--grey-200);">
        <p style="text-align:center;font-size:0.8rem;color:var(--grey-400);">Are you an administrator? <a href="<?= SITE_URL ?>/admin/login.php" style="color:var(--grey-600);font-weight:600;">Admin Login →</a></p>
      </div>
    </div>
  </div>
</main>
<?php include '../includes/footer.php'; ?>
