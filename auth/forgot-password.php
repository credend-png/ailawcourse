<?php
require_once '../config.php';

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
        $message = 'Invalid token. Please refresh.';
        $messageType = 'error';
    } else {
        $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = 'Please enter a valid email address.';
            $messageType = 'error';
        } else {
            $db = getDB();
            $stmt = $db->prepare("SELECT id, full_name FROM students WHERE email = ? AND status = 'active'");
            $stmt->execute([$email]);
            $student = $stmt->fetch();

            if ($student) {
                $token = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
                $db->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)")
                   ->execute([$email, $token, $expires]);

                $resetLink = SITE_URL . '/auth/reset-password.php?token=' . $token;
                $emailBody = "
                <html><body style='font-family:sans-serif;max-width:600px;margin:0 auto;'>
                <div style='background:#0B1D3A;padding:32px;text-align:center;'>
                    <h1 style='color:#C9A84C;'>LexLearnAI</h1>
                </div>
                <div style='padding:32px;background:#fff;'>
                    <p>Hi {$student['full_name']},</p>
                    <p>We received a password reset request for your account. Click the button below to set a new password:</p>
                    <a href='{$resetLink}' style='display:inline-block;background:#C9A84C;color:#0B1D3A;padding:14px 32px;border-radius:8px;text-decoration:none;font-weight:700;margin:20px 0;'>Reset Password</a>
                    <p>This link expires in 1 hour. If you didn't request this, ignore this email.</p>
                </div>
                </body></html>";
                sendEmail($email, 'Reset Your LexLearnAI Password', $emailBody);
            }

            // Show success regardless (security)
            $message = 'If this email is registered, a reset link has been sent. Check your inbox.';
            $messageType = 'success';
        }
    }
}

$pageTitle = 'Forgot Password';
include '../includes/header.php';
?>
<main class="auth-page" style="padding-top:80px;">
  <div class="container">
    <div class="auth-card" style="max-width:440px;">
      <div class="auth-logo">
        <div class="brand-logo" style="width:56px;height:56px;font-size:1.5rem;margin:0 auto 16px;">⚖</div>
      </div>
      <h1 class="auth-title text-center">Forgot Password?</h1>
      <p class="auth-subtitle text-center mb-24">Enter your email and we'll send you a reset link.</p>

      <?php if ($message): ?>
        <div class="alert alert-<?= $messageType ?>"><span><?= $messageType === 'success' ? '✓' : '⚠' ?></span><div><?= xss($message) ?></div></div>
      <?php endif; ?>

      <form method="POST" data-validate>
        <?= csrfField() ?>
        <div class="form-group">
          <label class="form-label">Email Address</label>
          <input type="email" name="email" class="form-control" placeholder="your@email.com" required>
        </div>
        <button type="submit" class="btn btn-primary btn-block btn-lg">Send Reset Link</button>
        <p style="text-align:center;margin-top:20px;font-size:0.875rem;">
          <a href="<?= SITE_URL ?>/auth/login.php" style="color:var(--navy);font-weight:600;">← Back to Login</a>
        </p>
      </form>
    </div>
  </div>
</main>
<?php include '../includes/footer.php'; ?>
