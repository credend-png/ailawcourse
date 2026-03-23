<?php
require_once '../config.php';
$pageTitle = 'Certificate Information';
$pageDesc = 'Learn how LexLearnAI certificates are issued, verified and shared after course completion.';
include '../includes/header.php';
?>
<section class="page-hero compact">
  <div class="container text-center">
    <div class="section-tag" style="display:inline-flex;">Certification</div>
    <h1 class="page-hero-title">Certificates designed for credibility, verification and easy sharing</h1>
    <p class="page-hero-sub">LexLearnAI certificates are issued only after course completion criteria are satisfied. Each certificate may be uploaded by the admin panel, associated with the relevant student and course, and linked to a unique verification ID for public validation.</p>
    <div class="breadcrumb"><a href="<?= SITE_URL ?>/">Home</a><span class="sep">/</span><span>Certificate Information</span></div>
  </div>
</section>
<section class="section">
  <div class="container content-grid-2">
    <div class="content-shell legal-richtext">
      <h2>How certificate issuance works</h2>
      <div class="info-card">
        <h3>Completion-based release</h3>
        <p>Certificates are not auto-issued merely on registration. Release may depend on quiz or test completion, course progress, attendance requirements and final approval by the administrator.</p>
      </div>
      <div class="info-card">
        <h3>Admin-uploaded certificates</h3>
        <p>The platform supports certificate upload from the admin panel. Once assigned, students can view and download the certificate from their account dashboard without external follow-up.</p>
      </div>
      <div class="info-card">
        <h3>Unique verification ID</h3>
        <p>Every certificate can carry a unique verification reference. Employers, institutions and third parties can use the verification page to confirm authenticity.</p>
      </div>
      <div class="badge-row">
        <span class="badge-soft">Unique verification ID</span>
        <span class="badge-soft">Student dashboard download</span>
        <span class="badge-soft">Public verification page</span>
      </div>
    </div>
    <div class="legal-richtext">
      <div class="info-card">
        <h3>Important certificate disclaimer</h3>
        <p>LexLearnAI certificates are educational or professional-development credentials. They do not represent a university degree, government license, Bar Council recognition, guaranteed employment outcome or legal authorization to practise law in any jurisdiction.</p>
      </div>
      <div class="info-card">
        <h3>Recommended certificate fields</h3>
        <ul>
          <li>Student full name</li>
          <li>Course title</li>
          <li>Completion date</li>
          <li>Verification ID</li>
          <li>Issuing authority or signatory line</li>
        </ul>
      </div>
      <div class="info-card">
        <h3>Verification access</h3>
        <p>Students can share the certificate file and the verification ID together. Verifiers may then confirm the record using the public certificate verification page on this website.</p>
        <a href="<?= SITE_URL ?>/pages/verify-certificate.php" class="btn btn-secondary btn-sm" style="margin-top:14px;">Open Verification Page</a>
      </div>
    </div>
  </div>
</section>
<?php include '../includes/footer.php'; ?>
