<?php
require_once '../config.php';
requireAdminLogin();
$pageTitle = 'Quiz Management';
$errors=[]; $success='';

if ($_SERVER['REQUEST_METHOD']==='POST'&&verifyCsrfToken($_POST['csrf_token']??'')) {
    $fa=$_POST['form_action']??'';
    if ($fa==='save_quiz') {
        $cid=(int)$_POST['course_id']; $title=sanitize($_POST['title']); $tl=(int)($_POST['time_limit']??0); $status=sanitize($_POST['status']??'active'); $id=(int)($_POST['quiz_id']??0);
        if (!$cid||!$title){$errors[]='Course and title required.';}
        else {
            if ($id>0){$pdo->prepare("UPDATE quizzes SET course_id=?,title=?,time_limit=?,status=? WHERE id=?")->execute([$cid,$title,$tl,$status,$id]);$success='Quiz updated.';}
            else{$pdo->prepare("INSERT INTO quizzes (course_id,title,time_limit,status,created_at) VALUES (?,?,?,?,NOW())")->execute([$cid,$title,$tl,$status]);$success='Quiz created.';$_GET['quiz']=$pdo->lastInsertId();}
        }
    } elseif ($fa==='add_question') {
        $qid=(int)$_POST['quiz_id']; $question=sanitize($_POST['question']); $oa=sanitize($_POST['option_a']); $ob=sanitize($_POST['option_b']); $oc=sanitize($_POST['option_c']); $od=sanitize($_POST['option_d']); $correct=sanitize($_POST['correct_option']);
        if (!$question||!$oa||!$ob||!$correct){$errors[]='Question, Option A, Option B and correct answer are required.';}
        else{$pdo->prepare("INSERT INTO quiz_questions (quiz_id,question,option_a,option_b,option_c,option_d,correct_option,created_at) VALUES (?,?,?,?,?,?,?,NOW())")->execute([$qid,$question,$oa,$ob,$oc,$od,$correct]);$success='Question added.';}
    } elseif ($fa==='delete_question'){$pdo->prepare("DELETE FROM quiz_questions WHERE id=?")->execute([(int)$_POST['question_id']]);$success='Question deleted.';}
    elseif ($fa==='delete_quiz'){$pdo->prepare("DELETE FROM quizzes WHERE id=?")->execute([(int)$_POST['quiz_id']]);$pdo->prepare("DELETE FROM quiz_questions WHERE quiz_id=?")->execute([(int)$_POST['quiz_id']]);$success='Quiz deleted.';}
}

$activeQuizId=(int)($_GET['quiz']??0);
$quizzes=$pdo->query("SELECT q.*,c.title as course_title,(SELECT COUNT(*) FROM quiz_questions qq WHERE qq.quiz_id=q.id) as q_count FROM quizzes q JOIN courses c ON c.id=q.course_id ORDER BY q.created_at DESC")->fetchAll();
$courses=$pdo->query("SELECT id,title FROM courses WHERE status='active'")->fetchAll();
$questions=[]; $activeQuiz=null;
if ($activeQuizId) {
    $st=$pdo->prepare("SELECT * FROM quizzes WHERE id=?");$st->execute([$activeQuizId]);$activeQuiz=$st->fetch();
    if ($activeQuiz){$qt=$pdo->prepare("SELECT * FROM quiz_questions WHERE quiz_id=? ORDER BY id");$qt->execute([$activeQuizId]);$questions=$qt->fetchAll();}
}
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= $pageTitle ?> – Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= ASSETS_URL ?>/css/admin.css">
</head><body>
<div class="admin-layout">
<?php require_once '../includes/admin-sidebar.php'; ?>
<div class="admin-main">
<?php require_once '../includes/admin-topbar.php'; ?>
<div class="admin-content">
<?php foreach ($errors as $e): ?><div class="alert alert-danger"><?= xss($e) ?></div><?php endforeach; ?>
<?php if ($success): ?><div class="alert alert-success"><?= xss($success) ?></div><?php endif; ?>

<?php if ($activeQuiz): ?>
<!-- Manage questions for a quiz -->
<div class="page-header">
    <div class="page-header-left"><div class="page-title"><?= xss($activeQuiz['title']) ?></div><div class="page-subtitle">Manage Questions</div></div>
    <div class="page-header-right"><a href="quizzes.php" class="btn btn-outline">← All Quizzes</a></div>
</div>
<div class="sidebar-col">
<div>
<div class="card">
<div class="card-header"><div class="card-title">Questions (<?= count($questions) ?>)</div></div>
<div class="card-body" style="padding:0;">
<?php if (empty($questions)): ?><div class="empty-state" style="padding:40px;"><div class="empty-icon">❓</div><h3>No questions yet</h3></div>
<?php else: foreach ($questions as $i=>$q): ?>
<div style="padding:16px 20px;border-bottom:1px solid #DDE1EB;">
    <div style="font-weight:600;margin-bottom:8px;"><?= $i+1 ?>. <?= xss($q['question']) ?></div>
    <?php foreach(['A'=>$q['option_a'],'B'=>$q['option_b'],'C'=>$q['option_c'],'D'=>$q['option_d']] as $k=>$v): ?>
    <?php if ($v): ?><div style="font-size:13px;padding:3px 0;<?= $k==$q['correct_option']?'color:#059669;font-weight:700;':'' ?>"><strong><?= $k ?>.</strong> <?= xss($v) ?> <?= $k==$q['correct_option']?' ✓':'' ?></div><?php endif; ?>
    <?php endforeach; ?>
    <form method="POST" onsubmit="return confirm('Delete?')" style="display:inline;margin-top:8px;"><?= csrfField() ?><input type="hidden" name="form_action" value="delete_question"><input type="hidden" name="question_id" value="<?= $q['id'] ?>"><button type="submit" class="btn btn-danger btn-sm">🗑️</button></form>
</div>
<?php endforeach; endif; ?>
</div>
</div>
</div>
<div>
<div class="card">
<div class="card-header"><div class="card-title">Add Question</div></div>
<div class="card-body">
<form method="POST"><?= csrfField() ?><input type="hidden" name="form_action" value="add_question"><input type="hidden" name="quiz_id" value="<?= $activeQuiz['id'] ?>">
    <div class="form-group" style="margin-bottom:14px;"><label>Question <span class="required">*</span></label><textarea name="question" rows="3" required></textarea></div>
    <?php foreach(['a'=>'A','b'=>'B','c'=>'C','d'=>'D'] as $k=>$l): ?>
    <div class="form-group" style="margin-bottom:12px;"><label>Option <?= $l ?><?= in_array($k,['a','b'])?'<span class="required">*</span>':'' ?></label><input type="text" name="option_<?= $k ?>" <?= in_array($k,['a','b'])?'required':'' ?>></div>
    <?php endforeach; ?>
    <div class="form-group" style="margin-bottom:16px;"><label>Correct Answer <span class="required">*</span></label><select name="correct_option" required><option value="">— Select —</option><option value="A">A</option><option value="B">B</option><option value="C">C</option><option value="D">D</option></select></div>
    <button type="submit" class="btn btn-primary" style="width:100%;">+ Add Question</button>
</form>
</div>
</div>
</div>
</div>

<?php else: ?>
<!-- Quiz List -->
<div class="page-header">
    <div class="page-header-left"><div class="page-title">Quizzes</div></div>
    <div class="page-header-right"><button class="btn btn-primary" onclick="document.getElementById('quizModal').classList.add('open')">+ Create Quiz</button></div>
</div>
<div class="card"><div class="card-body" style="padding:0;"><div class="table-responsive">
<table><thead><tr><th>#</th><th>Title</th><th>Course</th><th>Questions</th><th>Time Limit</th><th>Status</th><th>Actions</th></tr></thead>
<tbody>
<?php if (empty($quizzes)): ?><tr><td colspan="7"><div class="empty-state"><div class="empty-icon">🧠</div><h3>No quizzes yet</h3></div></td></tr>
<?php else: foreach ($quizzes as $i=>$q): ?>
<tr>
    <td><?= $i+1 ?></td>
    <td style="font-weight:600;"><?= xss($q['title']) ?></td>
    <td><?= xss($q['course_title']) ?></td>
    <td><span class="badge badge-navy"><?= $q['q_count'] ?> Qs</span></td>
    <td><?= $q['time_limit']>0?$q['time_limit'].' min':'Unlimited' ?></td>
    <td><span class="badge <?= $q['status']==='active'?'badge-success':'badge-grey' ?>"><?= ucfirst($q['status']) ?></span></td>
    <td>
        <div class="table-actions">
            <a href="quizzes.php?quiz=<?= $q['id'] ?>" class="btn btn-outline btn-sm">❓ Questions</a>
            <form method="POST" onsubmit="return confirm('Delete quiz and all questions?')" style="display:inline;"><?= csrfField() ?><input type="hidden" name="form_action" value="delete_quiz"><input type="hidden" name="quiz_id" value="<?= $q['id'] ?>"><button type="submit" class="btn btn-danger btn-sm">🗑️</button></form>
        </div>
    </td>
</tr>
<?php endforeach; endif; ?>
</tbody></table>
</div></div></div>

<div class="modal-overlay" id="quizModal">
<div class="modal">
    <div class="modal-header"><div class="modal-title">Create Quiz</div><button class="modal-close" onclick="document.getElementById('quizModal').classList.remove('open')">×</button></div>
    <form method="POST"><?= csrfField() ?><input type="hidden" name="form_action" value="save_quiz">
    <div class="modal-body">
        <div class="form-group" style="margin-bottom:14px;"><label>Course <span class="required">*</span></label><select name="course_id" required><option value="">— Select —</option><?php foreach ($courses as $c): ?><option value="<?= $c['id'] ?>"><?= xss($c['title']) ?></option><?php endforeach; ?></select></div>
        <div class="form-group" style="margin-bottom:14px;"><label>Quiz Title <span class="required">*</span></label><input type="text" name="title" required></div>
        <div class="form-group" style="margin-bottom:14px;"><label>Time Limit (minutes, 0=unlimited)</label><input type="number" name="time_limit" value="0" min="0"></div>
        <div class="form-group"><label>Status</label><select name="status"><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-outline" onclick="document.getElementById('quizModal').classList.remove('open')">Cancel</button><button type="submit" class="btn btn-primary">Create Quiz</button></div>
    </form>
</div></div>
<?php endif; ?>

</div></div></div>
<script>
function toggleSidebar(){document.getElementById('adminSidebar').classList.toggle('open');}
function toggleDropdown(id){document.getElementById(id).classList.toggle('open');}
document.addEventListener('click',function(e){if(!e.target.closest('.topbar-admin')&&!e.target.closest('.dropdown-menu'))document.querySelectorAll('.dropdown-menu').forEach(m=>m.classList.remove('open'));});
</script>
</body></html>
