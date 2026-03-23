<?php
require_once '../config.php';
requireAdminLogin();

$pageTitle = 'Course Management';
$action = $_GET['action'] ?? 'list';
$courseId = (int)($_GET['id'] ?? 0);
$errors = [];
$success = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        $formAction = $_POST['form_action'] ?? '';
        
        if ($formAction === 'save') {
            $title       = sanitize($_POST['title'] ?? '');
            $description = sanitize($_POST['description'] ?? '');
            $fee         = (float)($_POST['fee'] ?? 0);
            $duration    = sanitize($_POST['duration'] ?? '');
            $startDate   = sanitize($_POST['start_date'] ?? '');
            $endDate     = sanitize($_POST['end_date'] ?? '');
            $maxStudents = (int)($_POST['max_students'] ?? 0);
            $instructor  = sanitize($_POST['instructor'] ?? '');
            $status      = sanitize($_POST['status'] ?? 'active');
            $short       = sanitize($_POST['short_description'] ?? '');
            $highlights  = sanitize($_POST['highlights'] ?? '');
            
            if (empty($title)) $errors[] = 'Course title is required.';
            if ($fee < 0) $errors[] = 'Fee cannot be negative.';
            
            // Handle image upload
            $thumbnail = $_POST['existing_thumbnail'] ?? '';
            if (!empty($_FILES['thumbnail']['name'])) {
                $uploadDir = UPLOAD_PATH . '/courses/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                $ext = strtolower(pathinfo($_FILES['thumbnail']['name'], PATHINFO_EXTENSION));
                if (!in_array($ext, ['jpg','jpeg','png','webp'])) {
                    $errors[] = 'Invalid image format. Use JPG, PNG or WebP.';
                } elseif ($_FILES['thumbnail']['size'] > 2 * 1024 * 1024) {
                    $errors[] = 'Image too large. Max 2MB.';
                } else {
                    $filename = 'course_' . time() . '_' . rand(100,999) . '.' . $ext;
                    if (move_uploaded_file($_FILES['thumbnail']['tmp_name'], $uploadDir . $filename)) {
                        $thumbnail = 'courses/' . $filename;
                    }
                }
            }
            
            if (empty($errors)) {
                $slug = slugify($title);
                if ($courseId > 0) {
                    // Update
                    $stmt = $pdo->prepare("UPDATE courses SET title=?, slug=?, description=?, short_description=?, fee=?, duration=?, start_date=?, end_date=?, max_students=?, instructor=?, highlights=?, thumbnail=?, status=?, updated_at=NOW() WHERE id=?");
                    $stmt->execute([$title,$slug,$description,$short,$fee,$duration,$startDate?:null,$endDate?:null,$maxStudents,$instructor,$highlights,$thumbnail,$status,$courseId]);
                    $success = 'Course updated successfully.';
                } else {
                    // Insert
                    $stmt = $pdo->prepare("INSERT INTO courses (title,slug,description,short_description,fee,duration,start_date,end_date,max_students,instructor,highlights,thumbnail,status,created_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,NOW())");
                    $stmt->execute([$title,$slug,$description,$short,$fee,$duration,$startDate?:null,$endDate?:null,$maxStudents,$instructor,$highlights,$thumbnail,$status]);
                    $success = 'Course created successfully.';
                    $courseId = $pdo->lastInsertId();
                }
                $action = 'list';
            }
        } elseif ($formAction === 'delete') {
            $did = (int)$_POST['delete_id'];
            $pdo->prepare("DELETE FROM courses WHERE id=?")->execute([$did]);
            $success = 'Course deleted.';
        } elseif ($formAction === 'toggle') {
            $did = (int)$_POST['toggle_id'];
            $pdo->prepare("UPDATE courses SET status = IF(status='active','inactive','active') WHERE id=?")->execute([$did]);
            $success = 'Course status updated.';
        }
    }
}

// Fetch data
$courses = $pdo->query("SELECT c.*, (SELECT COUNT(*) FROM enrollments e WHERE e.course_id=c.id AND e.status='active') as student_count FROM courses c ORDER BY c.created_at DESC")->fetchAll();

$editCourse = null;
if ($action === 'edit' && $courseId > 0) {
    $editCourse = $pdo->prepare("SELECT * FROM courses WHERE id=?");
    $editCourse->execute([$courseId]);
    $editCourse = $editCourse->fetch();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= $pageTitle ?> – LexLearnAI Admin</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= ASSETS_URL ?>/css/admin.css">
</head>
<body>
<div class="admin-layout">
<?php require_once '../includes/admin-sidebar.php'; ?>
<div class="admin-main">
<?php require_once '../includes/admin-topbar.php'; ?>
<div class="admin-content">

<?php if ($success): ?>
<div class="alert alert-success"><?= xss($success) ?> <button class="alert-close" onclick="this.parentElement.remove()">×</button></div>
<?php endif; ?>
<?php foreach ($errors as $e): ?>
<div class="alert alert-danger"><?= xss($e) ?> <button class="alert-close" onclick="this.parentElement.remove()">×</button></div>
<?php endforeach; ?>

<?php if ($action === 'add' || $action === 'edit'): ?>
<!-- Add / Edit Form -->
<div class="page-header">
    <div class="page-header-left">
        <div class="page-title"><?= $action === 'edit' ? 'Edit Course' : 'Add New Course' ?></div>
        <div class="page-subtitle"><?= $action === 'edit' ? 'Update course details' : 'Create a new course for students' ?></div>
    </div>
    <div class="page-header-right">
        <a href="courses.php" class="btn btn-outline">← Back to Courses</a>
    </div>
</div>

<form method="POST" enctype="multipart/form-data">
<?= csrfField() ?>
<input type="hidden" name="form_action" value="save">
<?php if ($editCourse): ?><input type="hidden" name="existing_thumbnail" value="<?= xss($editCourse['thumbnail']) ?>"><?php endif; ?>

<div class="sidebar-col">
<div>
<div class="card" style="margin-bottom:20px;">
    <div class="card-header"><div class="card-title">Basic Information</div></div>
    <div class="card-body">
        <div class="form-grid">
            <div class="form-group full">
                <label>Course Title <span class="required">*</span></label>
                <input type="text" name="title" value="<?= xss($editCourse['title'] ?? '') ?>" required>
            </div>
            <div class="form-group full">
                <label>Short Description</label>
                <input type="text" name="short_description" value="<?= xss($editCourse['short_description'] ?? '') ?>" placeholder="One-line description for listing page">
            </div>
            <div class="form-group full">
                <label>Full Description</label>
                <textarea name="description" rows="5"><?= xss($editCourse['description'] ?? '') ?></textarea>
            </div>
            <div class="form-group full">
                <label>Course Highlights</label>
                <textarea name="highlights" rows="3" placeholder="e.g. 12 live classes&#10;Certificate included&#10;Expert instructors"><?= xss($editCourse['highlights'] ?? '') ?></textarea>
                <span class="form-hint">One highlight per line</span>
            </div>
        </div>
    </div>
</div>

<div class="card" style="margin-bottom:20px;">
    <div class="card-header"><div class="card-title">Schedule & Capacity</div></div>
    <div class="card-body">
        <div class="form-grid form-grid-2">
            <div class="form-group">
                <label>Duration</label>
                <input type="text" name="duration" value="<?= xss($editCourse['duration'] ?? '') ?>" placeholder="e.g. 3 Months">
            </div>
            <div class="form-group">
                <label>Instructor Name</label>
                <input type="text" name="instructor" value="<?= xss($editCourse['instructor'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Start Date</label>
                <input type="date" name="start_date" value="<?= $editCourse['start_date'] ?? '' ?>">
            </div>
            <div class="form-group">
                <label>End Date</label>
                <input type="date" name="end_date" value="<?= $editCourse['end_date'] ?? '' ?>">
            </div>
            <div class="form-group">
                <label>Max Students (0 = unlimited)</label>
                <input type="number" name="max_students" value="<?= $editCourse['max_students'] ?? 0 ?>" min="0">
            </div>
        </div>
    </div>
</div>
</div>

<div>
<div class="card" style="margin-bottom:20px;">
    <div class="card-header"><div class="card-title">Pricing & Status</div></div>
    <div class="card-body">
        <div class="form-group" style="margin-bottom:16px;">
            <label>Course Fee (₹) <span class="required">*</span></label>
            <input type="number" name="fee" value="<?= $editCourse['fee'] ?? '' ?>" step="0.01" min="0" required>
        </div>
        <div class="form-group">
            <label>Status</label>
            <select name="status">
                <option value="active" <?= ($editCourse['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active</option>
                <option value="inactive" <?= ($editCourse['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                <option value="draft" <?= ($editCourse['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Draft</option>
            </select>
        </div>
    </div>
</div>

<div class="card" style="margin-bottom:20px;">
    <div class="card-header"><div class="card-title">Thumbnail Image</div></div>
    <div class="card-body">
        <?php if (!empty($editCourse['thumbnail'])): ?>
        <img src="<?= UPLOAD_URL . '/' . $editCourse['thumbnail'] ?>" style="width:100%;border-radius:8px;margin-bottom:12px;" alt="Thumbnail">
        <?php endif; ?>
        <label class="upload-area" for="thumbInput">
            <div class="upload-icon">🖼️</div>
            <p>Click to upload thumbnail</p>
            <small>JPG, PNG, WebP · Max 2MB</small>
            <input type="file" name="thumbnail" id="thumbInput" accept="image/*" onchange="previewThumb(this)">
        </label>
        <img id="thumbPreview" src="" style="display:none;width:100%;border-radius:8px;margin-top:12px;" alt="Preview">
    </div>
</div>

<button type="submit" class="btn btn-primary" style="width:100%;">
    💾 <?= $action === 'edit' ? 'Update Course' : 'Create Course' ?>
</button>
</div>
</div>
</form>

<?php else: ?>
<!-- Courses List -->
<div class="page-header">
    <div class="page-header-left">
        <div class="page-title">Courses</div>
        <div class="page-subtitle"><?= count($courses) ?> total courses</div>
    </div>
    <div class="page-header-right">
        <a href="courses.php?action=add" class="btn btn-primary">+ Add Course</a>
    </div>
</div>

<div class="card">
    <div class="card-body" style="padding:0;">
    <div class="table-responsive">
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Course</th>
                <th>Fee</th>
                <th>Students</th>
                <th>Duration</th>
                <th>Start Date</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($courses)): ?>
        <tr><td colspan="8"><div class="empty-state"><div class="empty-icon">📚</div><h3>No courses yet</h3><p>Create your first course to get started.</p></div></td></tr>
        <?php else: foreach ($courses as $i => $c): ?>
        <tr>
            <td><?= $i+1 ?></td>
            <td>
                <div style="font-weight:600;"><?= xss($c['title']) ?></div>
                <div style="font-size:12px;color:var(--text-light);"><?= xss(substr($c['short_description'] ?? '', 0, 50)) ?></div>
            </td>
            <td><strong><?= formatCurrency($c['fee']) ?></strong></td>
            <td><?= $c['student_count'] ?> enrolled</td>
            <td><?= xss($c['duration'] ?? '—') ?></td>
            <td><?= $c['start_date'] ? formatDate($c['start_date']) : '—' ?></td>
            <td>
                <?php if ($c['status'] === 'active'): ?>
                <span class="badge badge-success">Active</span>
                <?php elseif ($c['status'] === 'inactive'): ?>
                <span class="badge badge-danger">Inactive</span>
                <?php else: ?>
                <span class="badge badge-grey">Draft</span>
                <?php endif; ?>
            </td>
            <td>
                <div class="table-actions">
                    <a href="courses.php?action=edit&id=<?= $c['id'] ?>" class="btn btn-outline btn-sm">✏️ Edit</a>
                    <form method="POST" style="display:inline;" onsubmit="return confirm('Toggle course status?')">
                        <?= csrfField() ?>
                        <input type="hidden" name="form_action" value="toggle">
                        <input type="hidden" name="toggle_id" value="<?= $c['id'] ?>">
                        <button type="submit" class="btn btn-outline btn-sm">🔄 Toggle</button>
                    </form>
                    <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this course? This cannot be undone.')">
                        <?= csrfField() ?>
                        <input type="hidden" name="form_action" value="delete">
                        <input type="hidden" name="delete_id" value="<?= $c['id'] ?>">
                        <button type="submit" class="btn btn-danger btn-sm">🗑️</button>
                    </form>
                </div>
            </td>
        </tr>
        <?php endforeach; endif; ?>
        </tbody>
    </table>
    </div>
    </div>
</div>
<?php endif; ?>

</div><!-- /admin-content -->
</div><!-- /admin-main -->
</div><!-- /admin-layout -->

<script>
function toggleSidebar() {
    document.getElementById('adminSidebar').classList.toggle('open');
}
function toggleDropdown(id) {
    document.getElementById(id).classList.toggle('open');
}
document.addEventListener('click', function(e) {
    if (!e.target.closest('.topbar-admin') && !e.target.closest('.dropdown-menu')) {
        document.querySelectorAll('.dropdown-menu').forEach(m => m.classList.remove('open'));
    }
});
function previewThumb(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            const prev = document.getElementById('thumbPreview');
            prev.src = e.target.result;
            prev.style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
</body>
</html>
