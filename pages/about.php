<?php
require_once '../config.php';
$pageTitle = 'About LexLearnAI';
$pageDesc = 'LexLearnAI is a legal-tech education platform focused on practical AI training for law students and legal professionals.';
include '../includes/header.php';
?>
<section class="page-hero compact">
  <div class="container text-center">
    <div class="section-tag" style="display:inline-flex;">About Us</div>
    <h1 class="page-hero-title">Built for the next generation of AI-enabled legal professionals</h1>
    <p class="page-hero-sub">LexLearnAI is positioned as a legal-tech education platform that combines law-focused instruction, practical AI exposure and student-friendly digital delivery. The objective is simple: help learners understand how AI tools can responsibly support research, drafting, productivity and legal workflows.</p>
    <div class="breadcrumb"><a href="<?= SITE_URL ?>/">Home</a><span class="sep">/</span><span>About</span></div>
  </div>
</section>
<section class="section">
  <div class="container">
    <div class="content-shell legal-richtext">
      <h2>What LexLearnAI stands for</h2>
      <div class="content-grid-2">
        <div class="info-card">
          <h3>Law-first learning</h3>
          <p>The platform focuses on practical use-cases relevant to law students, advocates, associates and legal teams rather than generic AI content detached from real legal work.</p>
        </div>
        <div class="info-card">
          <h3>Structured online delivery</h3>
          <p>Every course is intended to combine weekly live sessions, course materials, student dashboard access, assessments and admin-controlled progress oversight.</p>
        </div>
        <div class="info-card">
          <h3>Professional development focus</h3>
          <p>The curriculum is designed to strengthen legal research workflows, drafting support, responsible AI usage and digital readiness in the legal profession.</p>
        </div>
        <div class="info-card">
          <h3>Verification and trust</h3>
          <p>Certificates, payment records, policies and support pages are structured to make the learner experience more transparent and easier to verify.</p>
        </div>
      </div>
      <div class="highlight-banner" style="margin-top:8px;">
        <div>
          <h3 style="margin-bottom:6px;">Who this platform is for</h3>
          <p style="color:var(--grey-600);">Law students, advocates, associates, in-house teams, legal researchers and professionals exploring responsible AI in legal work.</p>
        </div>
        <a href="<?= SITE_URL ?>/auth/register.php" class="btn btn-primary">Create Student Account</a>
      </div>
    </div>
  </div>
</section>
<?php include '../includes/footer.php'; ?>
