<?php
require_once '../config.php';
$pageTitle = 'Educational Disclaimer';
$pageDesc = 'Important legal and educational disclaimer for LexLearnAI courses, certificates and platform content.';
include '../includes/header.php';
?>
<section class="page-hero compact">
  <div class="container text-center">
    <div class="section-tag" style="display:inline-flex;">Disclaimer</div>
    <h1 class="page-hero-title">Important educational and certificate disclaimer</h1>
    <p class="page-hero-sub">Please read this page carefully before enrolling. It clarifies the purpose of the courses, the limits of the training, and the role of certificates issued through this platform.</p>
    <div class="breadcrumb"><a href="<?= SITE_URL ?>/">Home</a><span class="sep">/</span><span>Disclaimer</span></div>
  </div>
</section>
<section class="section" style="background:var(--grey-50);">
  <div class="container">
    <div class="content-shell legal-richtext">
      <h2>Educational use only</h2>
      <p>All courses, materials, live classes, assignments, templates and demonstrations available on LexLearnAI are provided solely for education, training and professional development. They are not a substitute for legal advice, legal representation, compliance review, court strategy or licensed professional judgement.</p>
      <h2>No legal advice</h2>
      <p>Nothing on this website should be construed as legal advice. Students and visitors should obtain independent legal or professional advice before acting on any information, sample, prompt, workflow or tool demonstration shared through the platform.</p>
      <h2>No guarantee of outcome</h2>
      <p>Enrollment in a course does not guarantee employment, internship placement, income, admission, academic rank, publication, litigation success, or any specific professional outcome.</p>
      <h2>Certificate limitation</h2>
      <p>Certificates issued by LexLearnAI reflect successful completion of the relevant course requirements as determined by the platform. They do not amount to a government license, university degree, statutory recognition, Bar Council enrolment, or permission to practise law in any jurisdiction.</p>
      <h2>AI tool responsibility</h2>
      <p>Students are responsible for reviewing all AI-generated outputs independently. AI tools may produce incomplete, outdated, inaccurate or biased results. Any professional use of such outputs must be checked against applicable law, facts and ethical standards.</p>
    </div>
  </div>
</section>
<?php include '../includes/footer.php'; ?>
