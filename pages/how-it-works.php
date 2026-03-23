<?php
require_once '../config.php';
$pageTitle = 'How It Works';
$pageDesc = 'Understand the LexLearnAI learning journey from registration to certificate verification.';
include '../includes/header.php';
?>
<section class="page-hero compact">
  <div class="container text-center">
    <div class="section-tag" style="display:inline-flex;">Student Journey</div>
    <h1 class="page-hero-title">A simple pathway from enrollment to verified certification</h1>
    <p class="page-hero-sub">LexLearnAI is built to be structured, practical and easy to follow. Students register once, pay securely, access learning resources, attend weekly classes, complete assessments and receive a certificate only after completion requirements are met.</p>
    <div class="breadcrumb"><a href="<?= SITE_URL ?>/">Home</a><span class="sep">/</span><span>How It Works</span></div>
  </div>
</section>
<section class="section" style="background:var(--grey-50);">
  <div class="container">
    <div class="metric-strip">
      <div class="metric-card"><span class="metric-value">1</span><span class="metric-label">student account for enrollment, schedules, materials and certificates</span></div>
      <div class="metric-card"><span class="metric-value">4</span><span class="metric-label">clear stages from signup to final certificate issue</span></div>
      <div class="metric-card"><span class="metric-value">100%</span><span class="metric-label">online process with one-time fee model and dashboard-based access</span></div>
    </div>
  </div>
</section>
<section class="section">
  <div class="container content-grid-2">
    <div class="content-shell legal-richtext">
      <h2>How the platform works</h2>
      <div class="info-card">
        <h3>Step 1 — Register your student account</h3>
        <p>Students sign up with their full name, email address, mobile number, institution or profession details, city and state, and password. Profile information supports admissions, communication, certificate generation and student records.</p>
      </div>
      <div class="info-card">
        <h3>Step 2 — Select a course and complete payment</h3>
        <p>Each course is offered on a full-payment basis. Students can apply a valid coupon code where enabled. Once payment is confirmed, course access is activated according to the enrollment rules set by the administrator.</p>
      </div>
      <div class="info-card">
        <h3>Step 3 — Access classes, materials and assessments</h3>
        <p>After enrollment, the student dashboard shows course materials, live class schedules, payment records, progress, announcements and quizzes. Admin can update class timings, share files and publish notices from the back office.</p>
      </div>
      <div class="info-card">
        <h3>Step 4 — Complete requirements and receive certificate</h3>
        <p>Certificates are released after the required completion standard is met. This may include quiz or test completion, attendance, manual verification by admin, or a combination of academic and operational checks.</p>
      </div>
    </div>
    <div class="legal-richtext">
      <div class="info-card">
        <h3>What students get</h3>
        <ul>
          <li>Weekly online classes with scheduled access inside the student panel</li>
          <li>Downloadable learning materials and structured course progression</li>
          <li>Payment history and enrollment status visibility</li>
          <li>Certificate download after admin release</li>
          <li>Public certificate verification through unique verification ID</li>
        </ul>
      </div>
      <div class="info-card">
        <h3>What admin can control</h3>
        <ul>
          <li>Course creation, pricing, featured listing and course metadata</li>
          <li>Student management, enrollments and payment review</li>
          <li>Coupon management and announcement publishing</li>
          <li>Class schedules, study materials and certificate uploads</li>
          <li>Manual completion status and certificate release decisions</li>
        </ul>
      </div>
      <div class="highlight-banner">
        <div>
          <h3 style="margin-bottom:6px;">Ready to join a legal-tech focused cohort?</h3>
          <p style="color:var(--grey-600);">Browse current certificate programs and review course outcomes before enrolling.</p>
        </div>
        <a href="<?= SITE_URL ?>/pages/courses.php" class="btn btn-primary">View Courses</a>
      </div>
    </div>
  </div>
</section>
<?php include '../includes/footer.php'; ?>
