<?php // includes/footer.php ?>
<footer class="footer">
  <div class="container">
    <div class="footer-grid">
      <div class="footer-brand">
        <a href="<?= SITE_URL ?>/" class="navbar-brand" style="display:inline-flex;">
          <div class="brand-logo">⚖</div>
          <div class="brand-text">
            <span class="brand-name" style="color:var(--white)">LexLearnAI</span>
            <span class="brand-tag">Legal AI Education</span>
          </div>
        </a>
        <p class="brand-desc">LexLearnAI helps law students, advocates, associates and legal teams build practical AI capability through live classes, guided assignments, verified certificates and structured study resources.</p>
        <div class="footer-pills">
          <span>Weekly live classes</span>
          <span>One-time payment</span>
          <span>Verifiable certificates</span>
        </div>
      </div>

      <div>
        <h4 class="footer-heading">Platform</h4>
        <div class="footer-links">
          <a href="<?= SITE_URL ?>/pages/courses.php" class="footer-link">Browse Courses</a>
          <a href="<?= SITE_URL ?>/pages/how-it-works.php" class="footer-link">How It Works</a>
          <a href="<?= SITE_URL ?>/pages/certificate-info.php" class="footer-link">Certificate Information</a>
          <a href="<?= SITE_URL ?>/pages/faqs.php" class="footer-link">FAQs</a>
          <a href="<?= SITE_URL ?>/pages/contact.php" class="footer-link">Contact Admissions</a>
        </div>
      </div>

      <div>
        <h4 class="footer-heading">Policies</h4>
        <div class="footer-links">
          <a href="<?= SITE_URL ?>/pages/terms.php" class="footer-link">Terms &amp; Conditions</a>
          <a href="<?= SITE_URL ?>/pages/privacy.php" class="footer-link">Privacy Policy</a>
          <a href="<?= SITE_URL ?>/pages/refund.php" class="footer-link">Refund Policy</a>
          <a href="<?= SITE_URL ?>/pages/disclaimer.php" class="footer-link">Educational Disclaimer</a>
          <a href="<?= SITE_URL ?>/pages/verify-certificate.php" class="footer-link">Verify Certificate</a>
        </div>
      </div>

      <div>
        <h4 class="footer-heading">Support</h4>
        <div class="footer-links">
          <span class="footer-link">Email: <?= xss(SITE_EMAIL) ?></span>
          <span class="footer-link">Support window: Mon–Sat, 10:00 AM – 6:00 PM IST</span>
          <span class="footer-link">Location: New Delhi, India</span>
          <span class="footer-link">Secure payments through PayU integration</span>
        </div>
      </div>
    </div>
  </div>
  <div class="footer-bottom">
    <div class="container footer-bottom-inner">
      <p class="footer-copy">© <?= date('Y') ?> LexLearnAI. All rights reserved.</p>
      <p class="footer-copy">Courses are for training and professional development only. They do not constitute legal advice, university credit, or a statutory professional qualification.</p>
    </div>
  </div>
</footer>
<script src="<?= SITE_URL ?>/assets/js/main.js"></script>
<?php if(isset($extraJs)) echo $extraJs; ?>
</body>
</html>
