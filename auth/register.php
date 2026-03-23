<?php
require_once '../config.php';

if (isStudentLoggedIn()) redirect(SITE_URL . '/student/dashboard.php');

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
        $errors[] = 'Invalid form token. Please try again.';
    } else {
        $data = [
            'full_name'     => sanitize($_POST['full_name'] ?? ''),
            'email'         => filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL),
            'mobile'        => preg_replace('/\D/', '', $_POST['mobile'] ?? ''),
            'password'      => $_POST['password'] ?? '',
            'law_college'   => sanitize($_POST['law_college'] ?? ''),
            'year_semester' => sanitize($_POST['year_semester'] ?? ''),
            'city'          => sanitize($_POST['city'] ?? ''),
            'state'         => sanitize($_POST['state'] ?? ''),
            'profession'    => sanitize($_POST['profession'] ?? 'Student'),
        ];

        // Validation
        if (empty($data['full_name'])) $errors[] = 'Full name is required.';
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required.';
        if (!preg_match('/^[6-9]\d{9}$/', $data['mobile'])) $errors[] = 'Valid 10-digit mobile number is required.';
        if (strlen($data['password']) < 8) $errors[] = 'Password must be at least 8 characters.';
        if ($data['password'] !== ($_POST['confirm_password'] ?? '')) $errors[] = 'Passwords do not match.';
        if (empty($_POST['terms'])) $errors[] = 'You must accept the Terms & Conditions.';

        if (empty($errors)) {
            $db = getDB();

            // Check duplicate email
            $stmt = $db->prepare("SELECT id FROM students WHERE email = ?");
            $stmt->execute([$data['email']]);
            if ($stmt->fetch()) {
                $errors[] = 'This email address is already registered. <a href="' . SITE_URL . '/auth/login.php">Login instead</a>';
            } else {
                // Handle profile photo upload
                $photoPath = null;
                if (!empty($_FILES['profile_photo']['name'])) {
                    $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                    $maxSize = 2 * 1024 * 1024; // 2MB
                    if (!in_array($_FILES['profile_photo']['type'], $allowed)) {
                        $errors[] = 'Profile photo must be JPG, PNG, or WebP.';
                    } elseif ($_FILES['profile_photo']['size'] > $maxSize) {
                        $errors[] = 'Profile photo must be under 2MB.';
                    } else {
                        $ext = pathinfo($_FILES['profile_photo']['name'], PATHINFO_EXTENSION);
                        $filename = 'profile_' . uniqid() . '.' . strtolower($ext);
                        $uploadDir = UPLOAD_PATH . '/profiles/';
                        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                        if (move_uploaded_file($_FILES['profile_photo']['tmp_name'], $uploadDir . $filename)) {
                            $photoPath = 'profiles/' . $filename;
                        }
                    }
                }

                if (empty($errors)) {
                    $hashedPassword = password_hash($data['password'], HASH_ALGO, ['cost' => HASH_COST]);
                    $verifyToken = bin2hex(random_bytes(32));

                    $stmt = $db->prepare("INSERT INTO students (full_name, email, mobile, password, law_college, year_semester, city, state, profession, profile_photo, verification_token) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([
                        $data['full_name'], $data['email'], $data['mobile'],
                        $hashedPassword, $data['law_college'], $data['year_semester'],
                        $data['city'], $data['state'], $data['profession'],
                        $photoPath, $verifyToken
                    ]);
                    $studentId = $db->lastInsertId();

                    // Welcome email
                    $emailBody = "
                    <html><body style='font-family:sans-serif;max-width:600px;margin:0 auto;'>
                    <div style='background:#0B1D3A;padding:32px;text-align:center;border-radius:12px 12px 0 0;'>
                        <h1 style='color:#C9A84C;font-size:28px;margin:0;'>LexLearnAI</h1>
                        <p style='color:rgba(255,255,255,0.6);font-size:13px;margin:8px 0 0;'>Legal AI Education</p>
                    </div>
                    <div style='padding:32px;background:#fff;border:1px solid #E2E6EC;border-top:none;border-radius:0 0 12px 12px;'>
                        <h2 style='color:#0B1D3A;'>Welcome, {$data['full_name']}!</h2>
                        <p style='color:#4A5568;line-height:1.7;'>Your LexLearnAI account has been created successfully. You can now browse our courses and enroll to start your legal AI education journey.</p>
                        <a href='" . SITE_URL . "/pages/courses.php' style='display:inline-block;background:#C9A84C;color:#0B1D3A;padding:14px 32px;border-radius:8px;text-decoration:none;font-weight:700;margin-top:16px;'>Browse Courses</a>
                    </div>
                    </body></html>";
                    sendEmail($data['email'], 'Welcome to LexLearnAI!', $emailBody);

                    // Auto-login
                    $_SESSION['student_id'] = $studentId;
                    $_SESSION['student_name'] = $data['full_name'];
                    redirect(SITE_URL . '/pages/courses.php?welcome=1');
                }
            }
        }
    }
}

$pageTitle = 'Student Registration';
include '../includes/header.php';
?>
<main class="auth-page" style="padding-top:80px;">
  <div class="container">
    <div style="max-width:640px;margin:0 auto;background:var(--white);border-radius:var(--radius-xl);padding:48px;box-shadow:var(--shadow-lg);position:relative;z-index:1;">
      <div class="auth-logo">
        <a href="<?= SITE_URL ?>/" class="navbar-brand" style="display:inline-flex;justify-content:center;">
          <div class="brand-logo">⚖</div>
          <div class="brand-text">
            <span class="brand-name" style="color:var(--navy)">LexLearnAI</span>
            <span class="brand-tag">Legal AI Education</span>
          </div>
        </a>
      </div>
      <h1 class="auth-title text-center">Create Your Account</h1>
      <p class="auth-subtitle text-center mb-24">Join thousands of legal professionals learning with LexLearnAI</p>

      <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
          <span>⚠</span>
          <div><?= implode('<br>', $errors) ?></div>
        </div>
      <?php endif; ?>

      <form method="POST" enctype="multipart/form-data" data-validate>
        <?= csrfField() ?>

        <div class="d-grid-2">
          <div class="form-group">
            <label class="form-label">Full Name <span class="required">*</span></label>
            <input type="text" name="full_name" class="form-control" placeholder="Adv. Full Name" value="<?= xss($_POST['full_name'] ?? '') ?>" required>
          </div>
          <div class="form-group">
            <label class="form-label">Email Address <span class="required">*</span></label>
            <input type="email" name="email" class="form-control" placeholder="you@example.com" value="<?= xss($_POST['email'] ?? '') ?>" required>
          </div>
        </div>

        <div class="d-grid-2">
          <div class="form-group">
            <label class="form-label">Mobile Number <span class="required">*</span></label>
            <input type="tel" name="mobile" class="form-control" placeholder="10-digit mobile" value="<?= xss($_POST['mobile'] ?? '') ?>" required maxlength="10">
          </div>
          <div class="form-group">
            <label class="form-label">Profession <span class="required">*</span></label>
            <select name="profession" class="form-control form-select" required>
              <option value="">Select Profession</option>
              <?php foreach (['Student','Advocate','Associate','Other'] as $p): ?>
                <option value="<?= $p ?>" <?= (($_POST['profession'] ?? '') === $p) ? 'selected' : '' ?>><?= $p ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Law College / University / Firm</label>
          <input type="text" name="law_college" class="form-control" placeholder="E.g. NLU Delhi / Chamber of ..." value="<?= xss($_POST['law_college'] ?? '') ?>">
        </div>

        <div class="d-grid-2">
          <div class="form-group">
            <label class="form-label">Year / Semester</label>
            <input type="text" name="year_semester" class="form-control" placeholder="E.g. 4th Year / Semester 7" value="<?= xss($_POST['year_semester'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label class="form-label">City</label>
            <input type="text" name="city" class="form-control" placeholder="Your City" value="<?= xss($_POST['city'] ?? '') ?>">
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">State</label>
          <select name="state" class="form-control form-select">
            <option value="">Select State</option>
            <?php
            $states = ['Andhra Pradesh','Arunachal Pradesh','Assam','Bihar','Chhattisgarh','Delhi','Goa','Gujarat','Haryana','Himachal Pradesh','Jammu & Kashmir','Jharkhand','Karnataka','Kerala','Ladakh','Lakshadweep','Madhya Pradesh','Maharashtra','Manipur','Meghalaya','Mizoram','Nagaland','Odisha','Puducherry','Punjab','Rajasthan','Sikkim','Tamil Nadu','Telangana','Tripura','Uttar Pradesh','Uttarakhand','West Bengal'];
            foreach ($states as $s):
            ?>
            <option value="<?= $s ?>" <?= (($_POST['state'] ?? '') === $s) ? 'selected' : '' ?>><?= $s ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="d-grid-2">
          <div class="form-group">
            <label class="form-label">Password <span class="required">*</span></label>
            <input type="password" name="password" class="form-control" placeholder="Min. 8 characters" required>
          </div>
          <div class="form-group">
            <label class="form-label">Confirm Password <span class="required">*</span></label>
            <input type="password" name="confirm_password" class="form-control" placeholder="Repeat password" required>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Profile Photo <span style="color:var(--grey-400)">(optional)</span></label>
          <input type="file" name="profile_photo" class="form-control" accept="image/*">
          <div class="form-help">JPG/PNG/WebP, max 2MB</div>
        </div>

        <div class="form-group">
          <div class="form-check">
            <input type="checkbox" name="terms" id="terms" class="form-check-input" required>
            <label for="terms" class="form-check-label">
              I agree to the <a href="<?= SITE_URL ?>/pages/terms.php" target="_blank" style="color:var(--navy);font-weight:600;">Terms & Conditions</a> and <a href="<?= SITE_URL ?>/pages/privacy.php" target="_blank" style="color:var(--navy);font-weight:600;">Privacy Policy</a> of LexLearnAI.
            </label>
          </div>
        </div>

        <button type="submit" class="btn btn-primary btn-block btn-lg">Create Account →</button>

        <p style="text-align:center;margin-top:20px;font-size:0.875rem;color:var(--grey-500);">
          Already have an account? <a href="<?= SITE_URL ?>/auth/login.php" style="color:var(--navy);font-weight:600;">Sign In</a>
        </p>
      </form>
    </div>
  </div>
</main>
<?php include '../includes/footer.php'; ?>
