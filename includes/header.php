<?php
$pageTitle = isset($pageTitle) ? $pageTitle . ' | LexLearnAI' : 'LexLearnAI - Legal AI Education';
$pageDesc  = isset($pageDesc)  ? $pageDesc  : 'Master AI in law with LexLearnAI. Online certificate courses for law students and legal professionals in India.';
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
function navIsActive($url, $currentPath) {
    $target = parse_url($url, PHP_URL_PATH) ?: '/';
    if ($target === '/') return $currentPath === '/' || $currentPath === '/index.php';
    return str_starts_with($currentPath, $target);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= xss($pageTitle) ?></title>
  <meta name="description" content="<?= xss($pageDesc) ?>">
  <meta name="robots" content="index, follow">
  <meta property="og:title" content="<?= xss($pageTitle) ?>">
  <meta property="og:description" content="<?= xss($pageDesc) ?>">
  <meta property="og:type" content="website">
  <meta property="og:url" content="<?= SITE_URL . ($_SERVER['REQUEST_URI'] ?? '') ?>">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700;900&family=DM+Sans:wght@300;400;500;600;700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
  <?php if(isset($extraCss)) echo $extraCss; ?>
</head>
<body>
<?php
$navLinks = [
  'Home'        => SITE_URL . '/',
  'Courses'     => SITE_URL . '/pages/courses.php',
  'How It Works'=> SITE_URL . '/pages/how-it-works.php',
  'Certificate' => SITE_URL . '/pages/certificate-info.php',
  'About'       => SITE_URL . '/pages/about.php',
  'Contact'     => SITE_URL . '/pages/contact.php',
];
?>
<div class="topbar">
  <div class="container topbar-inner">
    <span>Admissions open for legal AI certificate courses</span>
    <div class="topbar-links">
      <a href="<?= SITE_URL ?>/pages/verify-certificate.php">Verify Certificate</a>
      <span class="divider">•</span>
      <a href="<?= SITE_URL ?>/pages/faqs.php">Student FAQs</a>
    </div>
  </div>
</div>
<nav class="navbar" id="mainNav">
  <div class="container">
    <div class="navbar-inner">
      <a href="<?= SITE_URL ?>/" class="navbar-brand">
        <div class="brand-logo">⚖</div>
        <div class="brand-text">
          <span class="brand-name">LexLearnAI</span>
          <span class="brand-tag">Legal AI Education</span>
        </div>
      </a>

      <nav class="navbar-nav" id="navMenu">
        <?php foreach ($navLinks as $label => $url): ?>
          <a href="<?= $url ?>" class="nav-link <?= navIsActive($url, $currentPath) ? 'active' : '' ?>"><?= $label ?></a>
        <?php endforeach; ?>
        <div class="nav-mobile-actions">
          <?php if (isStudentLoggedIn()): ?>
            <a href="<?= SITE_URL ?>/student/dashboard.php" class="btn btn-secondary btn-sm">Dashboard</a>
            <a href="<?= SITE_URL ?>/auth/logout.php" class="btn btn-primary btn-sm">Logout</a>
          <?php else: ?>
            <a href="<?= SITE_URL ?>/auth/login.php" class="btn btn-secondary btn-sm">Student Login</a>
            <a href="<?= SITE_URL ?>/auth/register.php" class="btn btn-primary btn-sm">Enroll Now</a>
          <?php endif; ?>
        </div>
      </nav>

      <div class="navbar-actions">
        <?php if (isStudentLoggedIn()): ?>
          <a href="<?= SITE_URL ?>/student/dashboard.php" class="btn-nav-login">Dashboard</a>
          <a href="<?= SITE_URL ?>/auth/logout.php" class="btn btn-primary btn-sm">Logout</a>
        <?php else: ?>
          <a href="<?= SITE_URL ?>/auth/login.php" class="btn-nav-login">Student Login</a>
          <a href="<?= SITE_URL ?>/auth/register.php" class="btn btn-primary btn-sm">Enroll Now</a>
        <?php endif; ?>
      </div>

      <button class="hamburger" onclick="toggleNav()" aria-label="Toggle navigation" aria-expanded="false" aria-controls="navMenu">
        <span></span><span></span><span></span>
      </button>
    </div>
  </div>
</nav>
<script>
function toggleNav() {
  const menu = document.getElementById('navMenu');
  const burger = document.querySelector('.hamburger');
  menu.classList.toggle('open');
  burger.setAttribute('aria-expanded', menu.classList.contains('open') ? 'true' : 'false');
}
document.addEventListener('click', function (event) {
  const menu = document.getElementById('navMenu');
  const burger = document.querySelector('.hamburger');
  if (!menu || !burger) return;
  if (window.innerWidth > 768) return;
  if (!menu.contains(event.target) && !burger.contains(event.target)) {
    menu.classList.remove('open');
    burger.setAttribute('aria-expanded', 'false');
  }
});
</script>
