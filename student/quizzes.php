<?php
require_once '../config.php';
requireStudentLogin();
$student = getCurrentStudent();

$myEnrollments = $pdo->prepare("SELECT course_id FROM enrollments WHERE student_id=? AND status='active'");
$myEnrollments->execute([$student['id']]);
$myCourseIds = array_column($myEnrollments->fetchAll(), 'course_id');

$quizzes = [];
if (!empty($myCourseIds)) {
    $in = implode(',', array_map('intval', $myCourseIds));
    $quizzes = $pdo->query("SELECT q.*, c.title as course_title, (SELECT COUNT(*) FROM quiz_questions qq WHERE qq.quiz_id=q.id) as question_count, (SELECT score FROM quiz_attempts qa WHERE qa.quiz_id=q.id AND qa.student_id={$student['id']} ORDER BY qa.created_at DESC LIMIT 1) as last_score FROM quizzes q JOIN courses c ON c.id=q.course_id WHERE q.course_id IN ($in) AND q.status='active' ORDER BY q.created_at DESC")->fetchAll();
}

// Handle quiz submission
$quizResult = null;
$activeQuiz = null;
$questions = [];

if (isset($_GET['take']) && !empty($myCourseIds)) {
    $qid = (int)$_GET['take'];
    $stmt = $pdo->prepare("SELECT q.* FROM quizzes q WHERE q.id=? AND q.course_id IN ($in)");
    $stmt->execute([$qid]);
    $activeQuiz = $stmt->fetch();
    if ($activeQuiz) {
        $questions = $pdo->prepare("SELECT * FROM quiz_questions WHERE quiz_id=? ORDER BY id");
        $questions->execute([$qid]);
        $questions = $questions->fetchAll();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_quiz']) && verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    $qid = (int)$_POST['quiz_id'];
    $questions = $pdo->prepare("SELECT * FROM quiz_questions WHERE quiz_id=?");
    $questions->execute([$qid]);
    $questions = $questions->fetchAll();
    
    $correct = 0;
    $answers = $_POST['answers'] ?? [];
    foreach ($questions as $q) {
        if (isset($answers[$q['id']]) && $answers[$q['id']] == $q['correct_option']) {
            $correct++;
        }
    }
    $total = count($questions);
    $score = $total > 0 ? round(($correct / $total) * 100) : 0;
    
    $pdo->prepare("INSERT INTO quiz_attempts (student_id, quiz_id, score, correct_answers, total_questions, created_at) VALUES (?,?,?,?,?,NOW())")->execute([$student['id'],$qid,$score,$correct,$total]);
    
    $quizResult = ['score'=>$score, 'correct'=>$correct, 'total'=>$total];
    $activeQuiz = null;
    $questions = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Quizzes – LexLearnAI</title>
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
.quiz-option{display:flex;align-items:center;gap:12px;padding:12px 16px;border:2px solid #DDE1EB;border-radius:10px;cursor:pointer;transition:.2s;margin-bottom:8px;}
.quiz-option:hover{border-color:#0B1D3A;background:rgba(11,29,58,0.03);}
.quiz-option input[type=radio]:checked+span{font-weight:600;}
.quiz-option:has(input:checked){border-color:#0B1D3A;background:rgba(11,29,58,0.05);}
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
        <a href="quizzes.php" class="active">🧠 Quizzes</a>
        <a href="certificate.php">🎓 Certificate</a>
        <a href="payments.php">💳 Payments</a>
        <a href="profile.php">👤 Profile</a>
        <a href="../auth/logout.php">🚪 Logout</a>
    </nav>
</aside>
<div class="student-main">
<div class="student-topbar">
    <div style="font-weight:600;">Quizzes</div>
    <div style="font-size:13px;color:#8A94A6;"><?= xss($student['full_name']) ?></div>
</div>
<div class="student-content">

<?php if ($quizResult): ?>
<!-- Quiz Result -->
<div style="max-width:480px;margin:0 auto;text-align:center;padding:40px 20px;">
    <div style="font-size:56px;margin-bottom:16px;"><?= $quizResult['score'] >= 60 ? '🎉' : '😔' ?></div>
    <h2 style="font-size:24px;font-family:'Playfair Display',serif;color:#0B1D3A;margin-bottom:8px;">Quiz Complete!</h2>
    <div style="font-size:48px;font-weight:700;color:<?= $quizResult['score']>=60?'#10B981':'#EF4444' ?>;margin:20px 0;"><?= $quizResult['score'] ?>%</div>
    <p style="color:#4A5568;margin-bottom:24px;"><?= $quizResult['correct'] ?> out of <?= $quizResult['total'] ?> correct</p>
    <?php if ($quizResult['score'] >= 60): ?>
    <div class="alert alert-success">Great job! You passed this quiz.</div>
    <?php else: ?>
    <div class="alert alert-warning">You need 60% to pass. Keep studying and try again!</div>
    <?php endif; ?>
    <a href="quizzes.php" class="btn btn-primary" style="margin-top:16px;">← Back to Quizzes</a>
</div>

<?php elseif ($activeQuiz && !empty($questions)): ?>
<!-- Take Quiz -->
<div style="max-width:700px;">
    <div style="margin-bottom:20px;">
        <a href="quizzes.php" style="color:#8A94A6;font-size:13px;text-decoration:none;">← Back</a>
        <h2 style="font-family:'Playfair Display',serif;font-size:20px;color:#0B1D3A;margin-top:8px;"><?= xss($activeQuiz['title']) ?></h2>
        <p style="color:#8A94A6;font-size:13px;"><?= count($questions) ?> questions</p>
    </div>
    <form method="POST">
        <?= csrfField() ?>
        <input type="hidden" name="submit_quiz" value="1">
        <input type="hidden" name="quiz_id" value="<?= $activeQuiz['id'] ?>">
        <?php foreach ($questions as $qi => $q): ?>
        <div class="card" style="margin-bottom:16px;">
            <div class="card-body">
                <p style="font-weight:600;font-size:15px;margin-bottom:14px;"><?= $qi+1 ?>. <?= xss($q['question']) ?></p>
                <?php foreach (['A'=>$q['option_a'],'B'=>$q['option_b'],'C'=>$q['option_c'],'D'=>$q['option_d']] as $key=>$opt): ?>
                <?php if ($opt): ?>
                <label class="quiz-option">
                    <input type="radio" name="answers[<?= $q['id'] ?>]" value="<?= $key ?>" required>
                    <span><strong><?= $key ?>.</strong> <?= xss($opt) ?></span>
                </label>
                <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>
        <button type="submit" class="btn btn-primary" style="margin-top:8px;" onclick="return confirm('Submit quiz? You cannot change answers after submission.')">Submit Quiz</button>
    </form>
</div>

<?php else: ?>
<!-- Quiz List -->
<?php if (empty($quizzes)): ?>
<div style="text-align:center;padding:80px 20px;color:#8A94A6;">
    <div style="font-size:48px;margin-bottom:16px;">🧠</div>
    <h3 style="font-size:18px;color:#4A5568;">No quizzes available yet</h3>
    <p>Quizzes will appear here once your instructor adds them.</p>
</div>
<?php else: ?>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:16px;">
<?php foreach ($quizzes as $quiz): ?>
<div class="card">
    <div class="card-body">
        <div style="font-weight:700;font-size:15px;margin-bottom:4px;"><?= xss($quiz['title']) ?></div>
        <div style="font-size:12px;color:#8A94A6;margin-bottom:12px;"><?= xss($quiz['course_title']) ?></div>
        <div style="display:flex;gap:12px;font-size:13px;color:#4A5568;margin-bottom:14px;">
            <span>❓ <?= $quiz['question_count'] ?> questions</span>
            <?php if ($quiz['time_limit']): ?><span>⏱ <?= $quiz['time_limit'] ?> min</span><?php endif; ?>
        </div>
        <?php if ($quiz['last_score'] !== null): ?>
        <div style="font-size:13px;margin-bottom:12px;padding:8px 12px;background:<?= $quiz['last_score']>=60?'#D1FAE5':'#FEF3C7' ?>;border-radius:8px;color:<?= $quiz['last_score']>=60?'#065F46':'#92400E' ?>;">
            Last Score: <strong><?= $quiz['last_score'] ?>%</strong> <?= $quiz['last_score']>=60?'✓ Passed':'✗ Failed' ?>
        </div>
        <?php endif; ?>
        <?php if ($quiz['question_count'] > 0): ?>
        <a href="quizzes.php?take=<?= $quiz['id'] ?>" class="btn btn-primary" style="width:100%;justify-content:center;padding:9px;">
            <?= $quiz['last_score'] !== null ? '🔁 Retake Quiz' : '▶ Take Quiz' ?>
        </a>
        <?php else: ?>
        <button class="btn btn-outline" disabled style="width:100%;justify-content:center;">No questions yet</button>
        <?php endif; ?>
    </div>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>
<?php endif; ?>

</div>
</div>
</div>
</div>
</body>
</html>
