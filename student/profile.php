<?php
require_once '../config.php';
requireStudentLogin();
$student = getCurrentStudent();
$errors = []; $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    $fa = $_POST['form_action'] ?? '';

    if ($fa === 'update_profile') {
        $fullName = sanitize($_POST['full_name']);
        $mobile   = sanitize($_POST['mobile']);
        $city     = sanitize($_POST['city']);
        $state    = sanitize($_POST['state']);
        $college  = sanitize($_POST['law_college']);
        $year     = sanitize($_POST['year_semester']);
        $profession = sanitize($_POST['profession']);

        if (empty($fullName)) $errors[] = 'Full name is required.';

        // Profile photo upload
        $photo = $student['profile_photo'];
        if (!empty($_FILES['profile_photo']['name'])) {
            $uploadDir = UPLOAD_PATH . '/profiles/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            $ext = strtolower(pathinfo($_FILES['profile_photo']['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, ['jpg','jpeg','png','webp'])) {
                $errors[] = 'Invalid image type.';
            } elseif ($_FILES['profile_photo']['size'] > 2*1024*1024) {
                $errors[] = 'Max 2MB.';
            } else {
                $fn = 'profile_' . $student['id'] . '_' . time() . '.' . $ext;
                if (move_uploaded_file($_FILES['profile_photo']['tmp_name'], $uploadDir.$fn)) {
                    $photo = 'profiles/' . $fn;
                }
            }
        }

        if (empty($errors)) {
            $pdo->prepare("UPDATE students SET full_name=?,mobile=?,city=?,state=?,law_college=?,year_semester=?,profession=?,profile_photo=?,updated_at=NOW() WHERE id=?")
                ->execute([$fullName,$mobile,$city,$state,$college,$year,$profession,$photo,$student['id']]);
            $_SESSION['student_name'] = $fullName;
            $student = getCurrentStudent();
            $success = 'Profile updated successfully.';
        }
    } elseif ($fa === 'change_password') {
        $current = $_POST['current_password'];
        $new     = $_POST['new_password'];
        $confirm = $_POST['confirm_password'];

        if (!password_verify($current, $student['password'])) {
            $errors[] = 'Current password is incorrect.';
        } elseif (strlen($new) < 8) {
            $errors[] = 'New password must be at least 8 characters.';
        } elseif ($new !== $confirm) {
            $errors[] = 'Passwords do not match.';
        } else {
            $hash = password_hash($new, PASSWORD_BCRYPT, ['cost'=>12]);
            $pdo->prepare("UPDATE students SET password=? WHERE id=?")->execute([$hash,$student['id']]);
            $success = 'Password changed successfully.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>My Profile – LexLearnAI</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= ASSETS_URL ?>/css/style.css">
<link rel="stylesheet" href="<?= ASSETS_URL ?>/css/admin.css">
<style>
.student-layout{display:flex;min-height:100vh;}
.student-sidebar{width:240px;background:#0B1D3A;position:fixed;top:0;left:0;height:100vh;overflow-y:auto;z-index:100;padding-top:20px;}
.student-sidebar .brand{padding:16px 20px 24px;border-bottom:1px solid rgba(255,255,255,.1);}
.student-sidebar .brand-name{font-size:16px;font-weight:700;color:#fff;}
.student-sidebar .brand-sub{font-size:11px;color:rgba(255,255,255,.4);}
.student-sidebar nav a{display:flex;align-items:center;gap:10px;padding:11px 20px;color:rgba(255,255,255,.7);font-size:13.5px;font-weight:500;border-left:3px solid transparent;transition:.2s;text-decoration:none;}
.student-sidebar nav a:hover,.student-sidebar nav a.active{color:#fff;background:rgba(255,255,255,.07);border-left-color:#C9A84C;}
.student-main{margin-left:240px;flex:1;background:#F8F9FC;}
.student-topbar{background:#fff;border-bottom:1px solid #DDE1EB;padding:0 24px;height:60px;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:99;}
.student-content{padding:28px;}
.profile-avatar{width:96px;height:96px;border-radius:20px;object-fit:cover;background:#DDE1EB;display:flex;align-items:center;justify-content:center;font-size:36px;font-weight:700;color:#0B1D3A;}
@media(max-width:768px){.student-sidebar{transform:translateX(-100%)}.student-main{margin-left:0}}
</style>
</head>
<body>
<div class="student-layout">
<aside class="student-sidebar">
    <div class="brand"><div class="brand-name">⚖ LexLearnAI</div><div class="brand-sub">Student Portal</div></div>
    <nav>
        <a href="dashboard.php">📊 Dashboard</a>
        <a href="my-courses.php">📚 My Courses</a>
        <a href="schedule.php">📅 Schedule</a>
        <a href="materials.php">📂 Materials</a>
        <a href="quizzes.php">🧠 Quizzes</a>
        <a href="certificate.php">🎓 Certificate</a>
        <a href="payments.php">💳 Payments</a>
        <a href="profile.php" class="active">👤 Profile</a>
        <a href="../auth/logout.php">🚪 Logout</a>
    </nav>
</aside>
<div class="student-main">
<div class="student-topbar">
    <div style="font-weight:600;">My Profile</div>
    <div style="font-size:13px;color:#8A94A6;"><?= xss($student['full_name']) ?></div>
</div>
<div class="student-content">

<?php foreach ($errors as $e): ?><div class="alert alert-danger"><?= xss($e) ?></div><?php endforeach; ?>
<?php if ($success): ?><div class="alert alert-success"><?= xss($success) ?></div><?php endif; ?>

<div class="tabs" style="margin-bottom:24px;">
    <button type="button" class="tab-btn active" onclick="switchTab('profile',this)">Edit Profile</button>
    <button type="button" class="tab-btn" onclick="switchTab('password',this)">Change Password</button>
</div>

<div id="tab-profile" class="tab-content active">
<form method="POST" enctype="multipart/form-data">
<?= csrfField() ?>
<input type="hidden" name="form_action" value="update_profile">
<div class="sidebar-col">
<div class="card">
    <div class="card-header"><div class="card-title">Personal Information</div></div>
    <div class="card-body">
        <div class="form-grid form-grid-2">
            <div class="form-group full">
                <label>Full Name <span class="required">*</span></label>
                <input type="text" name="full_name" value="<?= xss($student['full_name']) ?>" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" value="<?= xss($student['email']) ?>" disabled style="background:#F0F2F7;cursor:not-allowed;">
            </div>
            <div class="form-group">
                <label>Mobile</label>
                <input type="tel" name="mobile" value="<?= xss($student['mobile']) ?>">
            </div>
            <div class="form-group">
                <label>City</label>
                <input type="text" name="city" value="<?= xss($student['city'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>State</label>
                <input type="text" name="state" value="<?= xss($student['state'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Law College</label>
                <input type="text" name="law_college" value="<?= xss($student['law_college'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Year / Semester</label>
                <input type="text" name="year_semester" value="<?= xss($student['year_semester'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Profession</label>
                <select name="profession">
                    <?php foreach (['Law Student','Practicing Lawyer','Judge','Legal Academic','Other'] as $p): ?>
                    <option value="<?= $p ?>" <?= ($student['profession']??'') === $p ? 'selected':'' ?>><?= $p ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div style="margin-top:20px;">
            <button type="submit" class="btn btn-primary">💾 Save Profile</button>
        </div>
    </div>
</div>
<div>
<div class="card">
    <div class="card-header"><div class="card-title">Profile Photo</div></div>
    <div class="card-body" style="text-align:center;">
        <?php if (!empty($student['profile_photo'])): ?>
        <img src="<?= UPLOAD_URL.'/'.$student['profile_photo'] ?>" id="photoPreview" style="width:96px;height:96px;border-radius:20px;object-fit:cover;margin-bottom:14px;display:block;margin-inline:auto;" alt="">
        <?php else: ?>
        <div id="photoPreview" style="width:96px;height:96px;background:#DDE1EB;border-radius:20px;display:flex;align-items:center;justify-content:center;font-size:36px;font-weight:700;color:#0B1D3A;margin:0 auto 14px;"><?= strtoupper(substr($student['full_name'],0,1)) ?></div>
        <?php endif; ?>
        <label class="upload-area" for="photoInput" style="padding:16px;">
            <div>📷 Upload Photo</div>
            <small>JPG, PNG, WebP · Max 2MB</small>
            <input type="file" id="photoInput" name="profile_photo" accept="image/*" onchange="previewPhoto(this)">
        </label>
    </div>
</div>
</div>
</div>
</form>
</div>

<div id="tab-password" class="tab-content">
<div class="card" style="max-width:480px;">
    <div class="card-header"><div class="card-title">Change Password</div></div>
    <div class="card-body">
        <form method="POST">
            <?= csrfField() ?>
            <input type="hidden" name="form_action" value="change_password">
            <div class="form-group" style="margin-bottom:14px;">
                <label>Current Password</label>
                <input type="password" name="current_password" required autocomplete="current-password">
            </div>
            <div class="form-group" style="margin-bottom:14px;">
                <label>New Password</label>
                <input type="password" name="new_password" required minlength="8" autocomplete="new-password">
                <span class="form-hint">Minimum 8 characters</span>
            </div>
            <div class="form-group" style="margin-bottom:20px;">
                <label>Confirm New Password</label>
                <input type="password" name="confirm_password" required autocomplete="new-password">
            </div>
            <button type="submit" class="btn btn-primary">🔒 Change Password</button>
        </form>
    </div>
</div>
</div>

</div>
</div>
</div>
</div>

<script>
function switchTab(name, btn) {
    document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.getElementById('tab-'+name).classList.add('active');
    btn.classList.add('active');
}
function previewPhoto(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            const prev = document.getElementById('photoPreview');
            if (prev.tagName === 'IMG') { prev.src = e.target.result; }
            else {
                const img = document.createElement('img');
                img.src = e.target.result;
                img.id = 'photoPreview';
                img.style = 'width:96px;height:96px;border-radius:20px;object-fit:cover;margin:0 auto 14px;display:block;';
                prev.replaceWith(img);
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
</body>
</html>
