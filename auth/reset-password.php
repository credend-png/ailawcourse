<?php
require_once '../config.php';

$token = sanitize($_GET['token'] ?? '');
$errors = []; $success = '';

// Validate token
$tokenData = null;
if ($token) {
    $stmt = $pdo->prepare("SELECT * FROM password_resets WHERE token=? AND expires_at > NOW() AND used=0");
    $stmt->execute([$token]);
    $tokenData = $stmt->fetch();
}

if (!$token || !$tokenData) {
    redirect(SITE_URL . '/auth/forgot-password.php?error=invalid');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    $password  = $_POST['password'];
    $confirm   = $_POST['confirm_password'];

    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters.';
    } elseif ($password !== $confirm) {
        $errors[] = 'Passwords do not match.';
    } else {
        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        $pdo->prepare("UPDATE students SET password=? WHERE email=?")->execute([$hash, $tokenData['email']]);
        $pdo->prepare("UPDATE password_resets SET used=1 WHERE token=?")->execute([$token]);
        $success = 'Password reset successful! You can now log in.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Reset Password – LexLearnAI</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= ASSETS_URL ?>/css/style.css">
<style>
.auth-body{min-height:100vh;background:linear-gradient(135deg,#0B1D3A 0%,#142850 100%);display:flex;align-items:center;justify-content:center;padding:20px;}
.auth-card{background:#fff;border-radius:20px;padding:44px 40px;width:100%;max-width:420px;box-shadow:0 20px 60px rgba(0,0,0,.3);}
.auth-brand{text-align:center;margin-bottom:28px;}
.auth-brand .logo-icon{width:52px;height:52px;background:#0B1D3A;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:24px;color:#C9A84C;margin:0 auto 12px;}
.auth-brand h1{font-family:'Playfair Display',serif;font-size:22px;color:#0B1D3A;margin-bottom:4px;}
.auth-brand p{font-size:13px;color:#8A94A6;}
.form-group{display:flex;flex-direction:column;gap:6px;margin-bottom:16px;}
label{font-size:13px;font-weight:600;color:#1A2336;}
input[type=password]{padding:11px 14px;border:1.5px solid #DDE1EB;border-radius:10px;font-size:14px;font-family:'DM Sans',sans-serif;transition:.2s;width:100%;}
input:focus{outline:none;border-color:#0B1D3A;box-shadow:0 0 0 3px rgba(11,29,58,.08);}
.btn-submit{width:100%;padding:13px;background:#0B1D3A;color:#fff;border:none;border-radius:10px;font-size:15px;font-weight:700;cursor:pointer;margin-top:4px;transition:.2s;}
.btn-submit:hover{background:#142850;}
.alert{padding:12px 16px;border-radius:8px;margin-bottom:16px;font-size:13.5px;}
.alert-danger{background:#FEE2E2;color:#991B1B;border:1px solid #FCA5A5;}
.alert-success{background:#D1FAE5;color:#065F46;border:1px solid #6EE7B7;}
a{color:#0B1D3A;}
</style>
</head>
<body>
<div class="auth-body">
<div class="auth-card">
    <div class="auth-brand">
        <div class="logo-icon">⚖</div>
        <h1>Reset Password</h1>
        <p>Set a new password for <?= xss($tokenData['email']) ?></p>
    </div>

    <?php foreach ($errors as $e): ?><div class="alert alert-danger">⚠ <?= xss($e) ?></div><?php endforeach; ?>
    <?php if ($success): ?>
    <div class="alert alert-success">✓ <?= xss($success) ?> <a href="<?= AUTH_URL ?>/login.php">Login now →</a></div>
    <?php else: ?>

    <form method="POST">
        <?= csrfField() ?>
        <div class="form-group">
            <label for="password">New Password</label>
            <input type="password" id="password" name="password" minlength="8" required autocomplete="new-password" placeholder="Min 8 characters">
        </div>
        <div class="form-group">
            <label for="confirm_password">Confirm Password</label>
            <input type="password" id="confirm_password" name="confirm_password" minlength="8" required autocomplete="new-password">
        </div>
        <button type="submit" class="btn-submit">🔒 Reset Password</button>
    </form>
    <?php endif; ?>

    <div style="text-align:center;margin-top:20px;font-size:13px;color:#8A94A6;">
        Remember it? <a href="<?= AUTH_URL ?>/login.php">Sign In</a>
    </div>
</div>
</div>
</body>
</html>
