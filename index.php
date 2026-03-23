<?php
require_once 'config.php';
$pageTitle = 'LexLearnAI - Legal AI Education for the Next Generation of Lawyers';
$pageDesc  = 'Master AI in legal practice with LexLearnAI. Expert-led online certificate courses for law students, advocates & legal professionals. Enroll today.';

$db = getDB();
$courses = $db->query("SELECT * FROM courses WHERE status='active' AND featured=1 ORDER BY sort_order ASC, id DESC LIMIT 3")->fetchAll();

include 'includes/header.php';
?>
<meta name="site-url" content="<?= SITE_URL ?>">
<meta name="csrf-token" content="<?= generateCsrfToken() ?>">

<!-- HERO -->
<section class="hero">
  <div class="hero-grid"></div>
  <div class="container">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:60px;align-items:center;">
      <div class="hero-content">
        <div class="hero-badge">
          <span class="dot"></span>
          Now Enrolling — Cohort 2026
        </div>
        <h1 class="hero-title">
          The Future of Law<br>is <span class="accent">Powered by AI</span>
        </h1>
        <p class="hero-subtitle">
          LexLearnAI is India's premier legal AI education platform. Certificate courses designed for law students, advocates, and legal professionals ready to lead the AI revolution in law.
        </p>
        <div class="hero-actions">
          <a href="<?= SITE_URL ?>/pages/courses.php" class="btn btn-primary btn-xl">Explore Courses →</a>
          <a href="<?= SITE_URL ?>/pages/how-it-works.php" class="btn btn-secondary btn-xl">How It Works</a>
        </div>
        <div class="hero-stats">
          <div class="hero-stat">
            <span class="number" data-count="500">0</span>
            <span class="label">Students Enrolled</span>
          </div>
          <div class="hero-stat">
            <span class="number" data-count="8">0</span>
            <span class="label">Expert Courses</span>
          </div>
          <div class="hero-stat">
            <span class="number" data-count="96">0</span>
            <span class="label">% Satisfaction</span>
          </div>
          <div class="hero-stat">
            <span class="number" data-count="200">0</span>
            <span class="label">Certificates Issued</span>
          </div>
        </div>
      </div>
      <div class="hero-visual">
        <div class="hero-card-stack">
          <div class="hero-card">
            <div class="cert-preview">
              <div class="cert-seal">⚖</div>
              <div class="cert-title">Certificate of Completion</div>
              <div class="cert-name">Adv. Priya Sharma</div>
              <div class="cert-course">AI in Legal Practice: A Comprehensive Certification Program</div>
            </div>
            <div class="hero-card-meta">
              <span style="font-size:0.8rem;color:rgba(255,255,255,0.5);">LLA-A3F8B2C1-2026</span>
              <span class="badge badge-success">Verified ✓</span>
            </div>
          </div>
          <div class="floating-tag tag-1">
            <div class="icon" style="background:var(--gold-pale);">🎓</div>
            <div>
              <div style="font-weight:700;color:var(--navy)">Weekly Live Classes</div>
              <div style="font-size:0.72rem;color:var(--grey-400)">Every Saturday</div>
            </div>
          </div>
          <div class="floating-tag tag-2">
            <div class="icon" style="background:#E6F7F2;">✓</div>
            <div>
              <div style="font-weight:700;color:var(--navy)">Verified Certificate</div>
              <div style="font-size:0.72rem;color:var(--grey-400)">Unique ID + Download</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- TRUSTED BY -->
<section style="background:var(--grey-50);padding:40px 0;border-bottom:1px solid var(--grey-200);">
  <div class="container">
    <p style="text-align:center;font-size:0.8rem;color:var(--grey-400);letter-spacing:0.1em;text-transform:uppercase;font-weight:600;margin-bottom:28px;">Trusted by students from leading law schools across India</p>
    <div style="display:flex;justify-content:center;gap:48px;flex-wrap:wrap;align-items:center;opacity:0.5;filter:grayscale(1);">
      <span style="font-family:var(--font-display);font-weight:700;font-size:1rem;color:var(--grey-600)">NLU Delhi</span>
      <span style="font-family:var(--font-display);font-weight:700;font-size:1rem;color:var(--grey-600)">NALSAR</span>
      <span style="font-family:var(--font-display);font-weight:700;font-size:1rem;color:var(--grey-600)">NLU Mumbai</span>
      <span style="font-family:var(--font-display);font-weight:700;font-size:1rem;color:var(--grey-600)">Symbiosis</span>
      <span style="font-family:var(--font-display);font-weight:700;font-size:1rem;color:var(--grey-600)">Amity Law</span>
      <span style="font-family:var(--font-display);font-weight:700;font-size:1rem;color:var(--grey-600)">Christ University</span>
    </div>
  </div>
</section>

<!-- FEATURED COURSES -->
<section class="section">
  <div class="container">
    <div class="section-header">
      <div class="section-tag">Our Programs</div>
      <h2 class="section-title">Certificate Courses in Legal AI</h2>
      <p class="section-desc">Industry-aligned curriculum taught by practising lawyers, legal technology experts, and AI specialists. Earn a verifiable certificate upon completion.</p>
    </div>
    <div class="courses-grid">
      <?php foreach ($courses as $course): ?>
      <div class="course-card" data-reveal>
        <div class="course-thumb">
          <div class="course-thumb-pattern"></div>
          <div class="course-thumb-icon">⚖️</div>
          <span class="course-level-badge"><?= xss($course['level']) ?></span>
          <div class="course-thumb-overlay">
            <a href="<?= SITE_URL ?>/pages/course-detail.php?slug=<?= xss($course['slug']) ?>" class="btn btn-primary btn-sm">View Course</a>
          </div>
        </div>
        <div class="course-body">
          <h3 class="course-title"><?= xss($course['title']) ?></h3>
          <p class="course-desc"><?= xss($course['short_description']) ?></p>
          <div class="course-meta">
            <?php if ($course['duration_weeks']): ?>
            <div class="course-meta-item"><span class="icon">🗓</span><?= $course['duration_weeks'] ?> Weeks</div>
            <?php endif; ?>
            <?php if ($course['total_classes']): ?>
            <div class="course-meta-item"><span class="icon">🎥</span><?= $course['total_classes'] ?> Live Classes</div>
            <?php endif; ?>
            <?php if ($course['certificate_provided']): ?>
            <div class="course-meta-item"><span class="icon">🏆</span>Certificate</div>
            <?php endif; ?>
          </div>
        </div>
        <div class="course-footer">
          <div class="course-price">
            <span class="price-current"><?= formatCurrency($course['fee']) ?></span>
            <?php if ($course['original_fee']): ?>
            <span class="price-original"><?= formatCurrency($course['original_fee']) ?></span>
            <?php endif; ?>
          </div>
          <a href="<?= SITE_URL ?>/pages/course-detail.php?slug=<?= xss($course['slug']) ?>" class="btn btn-navy btn-sm">Enroll Now</a>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <div style="text-align:center;margin-top:40px;">
      <a href="<?= SITE_URL ?>/pages/courses.php" class="btn btn-outline">View All Courses →</a>
    </div>
  </div>
</section>

<!-- HOW IT WORKS -->
<section class="section" style="background:var(--grey-50);">
  <div class="container">
    <div class="section-header">
      <div class="section-tag">Simple Process</div>
      <h2 class="section-title">Your Path to Certification</h2>
      <p class="section-desc">From enrollment to certificate in four clear steps. Completely online, designed for busy legal professionals.</p>
    </div>
    <div class="steps-container">
      <div class="step-item" data-reveal>
        <div class="step-number">1</div>
        <h3 class="step-title">Register & Enroll</h3>
        <p class="step-desc">Create your student account with your details and choose the course that matches your career goals.</p>
      </div>
      <div class="step-item" data-reveal>
        <div class="step-number">2</div>
        <h3 class="step-title">Complete Payment</h3>
        <p class="step-desc">One-time secure payment via PayU. Instant access activation upon successful payment.</p>
      </div>
      <div class="step-item" data-reveal>
        <div class="step-number">3</div>
        <h3 class="step-title">Attend Live Classes</h3>
        <p class="step-desc">Join weekly live sessions via Zoom/Meet. Access study materials, assignments, and quizzes.</p>
      </div>
      <div class="step-item" data-reveal>
        <div class="step-number">4</div>
        <h3 class="step-title">Earn Certificate</h3>
        <p class="step-desc">Complete the course, pass assessments, and receive your uniquely verified LexLearnAI certificate.</p>
      </div>
    </div>
  </div>
</section>

<!-- FEATURES -->
<section class="section">
  <div class="container">
    <div class="section-header">
      <div class="section-tag">Why LexLearnAI</div>
      <h2 class="section-title">Education Built for Legal Professionals</h2>
    </div>
    <div class="features-grid">
      <div class="feature-card" data-reveal>
        <div class="feature-icon">⚖️</div>
        <h3 class="feature-title">Law-First Curriculum</h3>
        <p class="feature-desc">Every course is designed by practising lawyers and legal technology experts — not generic AI educators. Real-world case studies from Indian courts.</p>
      </div>
      <div class="feature-card" data-reveal>
        <div class="feature-icon">📡</div>
        <h3 class="feature-title">Weekly Live Classes</h3>
        <p class="feature-desc">Interactive Zoom/Meet sessions every week. Ask questions in real time, discuss cases, and network with a cohort of legal professionals.</p>
      </div>
      <div class="feature-card" data-reveal>
        <div class="feature-icon">🏆</div>
        <h3 class="feature-title">Verified Certificates</h3>
        <p class="feature-desc">Each certificate has a unique verification ID. Employers and institutions can verify authenticity online instantly.</p>
      </div>
      <div class="feature-card" data-reveal>
        <div class="feature-icon">📚</div>
        <h3 class="feature-title">Rich Study Materials</h3>
        <p class="feature-desc">Curated PDFs, notes, case studies, and resource links — all accessible in your student dashboard after enrollment.</p>
      </div>
      <div class="feature-card" data-reveal>
        <div class="feature-icon">📊</div>
        <h3 class="feature-title">Progress Tracking</h3>
        <p class="feature-desc">Visual dashboard with your completion percentage, quiz scores, attendance record, and upcoming class schedule.</p>
      </div>
      <div class="feature-card" data-reveal>
        <div class="feature-icon">🔒</div>
        <h3 class="feature-title">Secure & Private</h3>
        <p class="feature-desc">Your data is protected with industry-standard security. Payments processed securely via PayU's enterprise gateway.</p>
      </div>
    </div>
  </div>
</section>

<!-- CERTIFICATE PREVIEW -->
<section class="section" style="background:var(--grey-50);">
  <div class="container">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:64px;align-items:center;">
      <div data-reveal>
        <div class="section-tag">Your Achievement</div>
        <h2 class="section-title" style="text-align:left;margin-bottom:16px;">A Certificate That Carries Weight</h2>
        <p class="lead" style="margin-bottom:28px;">Upon completing your course and assessments, you'll receive a professionally designed certificate with a unique verification ID — shareable, downloadable, and verifiable by anyone.</p>
        <ul style="display:flex;flex-direction:column;gap:12px;margin-bottom:32px;">
          <li style="display:flex;gap:10px;align-items:flex-start;font-size:0.925rem;color:var(--grey-600)"><span style="color:var(--gold-dark);font-weight:700;flex-shrink:0">✓</span>Unique alphanumeric Verification ID for every certificate</li>
          <li style="display:flex;gap:10px;align-items:flex-start;font-size:0.925rem;color:var(--grey-600)"><span style="color:var(--gold-dark);font-weight:700;flex-shrink:0">✓</span>Download in PDF or image format from your dashboard</li>
          <li style="display:flex;gap:10px;align-items:flex-start;font-size:0.925rem;color:var(--grey-600)"><span style="color:var(--gold-dark);font-weight:700;flex-shrink:0">✓</span>Public verification page — share with employers or on LinkedIn</li>
          <li style="display:flex;gap:10px;align-items:flex-start;font-size:0.925rem;color:var(--grey-600)"><span style="color:var(--gold-dark);font-weight:700;flex-shrink:0">✓</span>Issued with student name, course name, and date</li>
        </ul>
        <a href="<?= SITE_URL ?>/pages/verify-certificate.php" class="btn btn-navy">Verify a Certificate →</a>
      </div>
      <div class="certificate-preview-wrap" data-reveal>
        <div class="cert-mock">
          <div class="cert-header">
            <div class="cert-brand">LexLearnAI</div>
            <div class="cert-divider"></div>
            <div style="font-size:0.72rem;color:var(--grey-400);letter-spacing:0.08em;text-transform:uppercase">Certificate of Completion</div>
          </div>
          <div class="cert-body">
            <div class="cert-label">This certifies that</div>
            <div class="cert-student-name">Adv. Priya Sharma</div>
            <div class="cert-label">has successfully completed</div>
            <div class="cert-course-name" style="margin-top:8px;">AI in Legal Practice: A Comprehensive Certification Program</div>
          </div>
          <div class="cert-footer">
            <div>
              <div style="border-top:2px solid var(--navy);width:120px;padding-top:8px;font-size:0.72rem;color:var(--grey-500)">Director, LexLearnAI</div>
            </div>
            <div style="text-align:right">
              <div class="cert-verify-id">LLA-A3F8B2C1-2026</div>
              <div style="font-size:0.68rem;color:var(--grey-400);margin-top:4px">Issued: 06 June 2026</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- TESTIMONIALS -->
<section class="section">
  <div class="container">
    <div class="section-header">
      <div class="section-tag">Student Stories</div>
      <h2 class="section-title">What Our Students Say</h2>
    </div>
    <div class="testimonials-grid">
      <div class="testimonial-card" data-reveal>
        <div class="stars">★★★★★</div>
        <p class="testimonial-text">LexLearnAI gave me a structured understanding of exactly how AI tools apply to legal research. The live sessions were incredibly interactive and the faculty actually practise law — which made all the difference.</p>
        <div class="testimonial-author">
          <div class="author-avatar">PS</div>
          <div>
            <div class="author-name">Priya Sharma</div>
            <div class="author-role">Advocate, Delhi High Court</div>
          </div>
        </div>
      </div>
      <div class="testimonial-card" data-reveal>
        <div class="stars">★★★★★</div>
        <p class="testimonial-text">As a 4th-year law student, this course helped me stand out during internship interviews. The certificate verification feature was immediately impressive to partners at the firm I applied to.</p>
        <div class="testimonial-author">
          <div class="author-avatar">RK</div>
          <div>
            <div class="author-name">Rahul Khanna</div>
            <div class="author-role">Law Student, NALSAR University</div>
          </div>
        </div>
      </div>
      <div class="testimonial-card" data-reveal>
        <div class="stars">★★★★★</div>
        <p class="testimonial-text">The course content was well-structured, practical, and genuinely ahead of what law schools teach. The study materials and weekly classes gave me a complete picture of how AI is transforming Indian legal practice.</p>
        <div class="testimonial-author">
          <div class="author-avatar">AM</div>
          <div>
            <div class="author-name">Ananya Mishra</div>
            <div class="author-role">Corporate Associate, Mumbai</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- CTA -->
<section class="cta-section">
  <div class="container">
    <div class="cta-content">
      <h2 class="cta-title">Ready to Lead the Legal AI Revolution?</h2>
      <p class="cta-desc">Join hundreds of law students and professionals who are building the skills that the future of law demands. Enroll in a certificate course today.</p>
      <div class="cta-actions">
        <a href="<?= SITE_URL ?>/pages/courses.php" class="btn btn-primary btn-xl">Browse Courses →</a>
        <a href="<?= SITE_URL ?>/auth/register.php" class="btn btn-secondary btn-xl">Create Free Account</a>
      </div>
    </div>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
