<?php
require_once '../config.php';
requireStudentLogin();

$student = getCurrentStudent();
$courseSlug = sanitize($_GET['course'] ?? '');

if (empty($courseSlug)) redirect(SITE_URL . '/pages/courses.php');

$db = getDB();
$course = $db->prepare("SELECT * FROM courses WHERE slug = ? AND status = 'active'")->execute([$courseSlug]) ? null : null;
$stmt = $db->prepare("SELECT * FROM courses WHERE slug = ? AND status = 'active'");
$stmt->execute([$courseSlug]);
$course = $stmt->fetch();

if (!$course) redirect(SITE_URL . '/pages/courses.php');

// Check already enrolled
$enrolled = $db->prepare("SELECT e.id FROM enrollments e JOIN payments p ON p.student_id=e.student_id AND p.course_id=e.course_id WHERE e.student_id=? AND e.course_id=? AND p.status='success'");
$enrolled->execute([$student['id'], $course['id']]);
if ($enrolled->fetch()) {
    redirect(SITE_URL . '/student/course-view.php?id=' . $course['id'] . '&msg=already_enrolled');
}

$finalAmount = $course['fee'];
$discountAmount = 0;
$couponCode = '';
$couponError = '';

// Handle coupon POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['initiate_payment'])) {
    if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
        die('Invalid token');
    }

    $finalAmount    = floatval($_POST['final_amount'] ?? $course['fee']);
    $discountAmount = floatval($_POST['discount_amount'] ?? 0);
    $couponCode     = sanitize($_POST['coupon_code'] ?? '');

    // Generate transaction ID
    $txnid = generateTxnId();

    // Store pending payment
    $db->prepare("INSERT INTO payments (student_id, course_id, txnid, amount, discount_amount, coupon_code, status) VALUES (?,?,?,?,?,?,'pending')")
       ->execute([$student['id'], $course['id'], $txnid, $finalAmount, $discountAmount, $couponCode]);

    // PayU params
    $payuKey  = PAYU_MERCHANT_KEY;
    $payuSalt = PAYU_MERCHANT_SALT;
    $amount   = number_format($finalAmount, 2, '.', '');
    $productinfo = 'Course: ' . $course['title'];
    $firstname   = $student['full_name'];
    $email       = $student['email'];
    $phone       = $student['mobile'];
    $surl = SITE_URL . '/student/payment-success.php';
    $furl = SITE_URL . '/student/payment-failure.php';
    $udf1 = $course['id'];
    $udf2 = $student['id'];

    $hash = generatePayUHash($payuKey, $txnid, $amount, $productinfo, $firstname, $email, $udf1, $udf2, '', '', '', $payuSalt);
    ?>
    <!DOCTYPE html>
    <html><body>
    <form id="payuForm" method="post" action="<?= PAYU_URL ?>">
      <input type="hidden" name="key" value="<?= xss($payuKey) ?>">
      <input type="hidden" name="txnid" value="<?= xss($txnid) ?>">
      <input type="hidden" name="amount" value="<?= xss($amount) ?>">
      <input type="hidden" name="productinfo" value="<?= xss($productinfo) ?>">
      <input type="hidden" name="firstname" value="<?= xss($firstname) ?>">
      <input type="hidden" name="email" value="<?= xss($email) ?>">
      <input type="hidden" name="phone" value="<?= xss($phone) ?>">
      <input type="hidden" name="surl" value="<?= xss($surl) ?>">
      <input type="hidden" name="furl" value="<?= xss($furl) ?>">
      <input type="hidden" name="udf1" value="<?= xss($udf1) ?>">
      <input type="hidden" name="udf2" value="<?= xss($udf2) ?>">
      <input type="hidden" name="hash" value="<?= xss($hash) ?>">
      <input type="hidden" name="service_provider" value="payu_paisa">
    </form>
    <script>document.getElementById('payuForm').submit();</script>
    </body></html>
    <?php
    exit;
}

$pageTitle = 'Enroll: ' . $course['title'];
include '../includes/header.php';
?>
<style>
.pay-layout { min-height: 100vh; background: var(--grey-50); padding: 100px 0 60px; }
</style>
<main class="pay-layout">
  <div class="container" style="max-width:900px;">
    <div style="display:grid;grid-template-columns:1fr 380px;gap:32px;align-items:start;">

      <!-- LEFT: Course Summary -->
      <div class="card">
        <div class="card-header"><div class="card-title">Course Enrollment</div></div>
        <div class="card-body">
          <div style="display:flex;gap:16px;margin-bottom:24px;padding-bottom:24px;border-bottom:1px solid var(--grey-100);">
            <div style="width:80px;height:80px;background:linear-gradient(135deg,var(--navy),var(--navy-mid));border-radius:var(--radius-md);display:flex;align-items:center;justify-content:center;font-size:2rem;flex-shrink:0;">⚖️</div>
            <div>
              <h2 style="font-size:1.1rem;color:var(--navy);margin-bottom:6px;"><?= xss($course['title']) ?></h2>
              <p style="font-size:0.85rem;color:var(--grey-500);"><?= xss($course['short_description']) ?></p>
            </div>
          </div>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;font-size:0.875rem;">
            <?php if ($course['duration_weeks']): ?>
            <div style="background:var(--grey-50);padding:12px;border-radius:var(--radius);">
              <div style="color:var(--grey-400);font-size:0.75rem;margin-bottom:2px;">Duration</div>
              <div style="font-weight:600;color:var(--navy);"><?= $course['duration_weeks'] ?> Weeks</div>
            </div>
            <?php endif; ?>
            <?php if ($course['total_classes']): ?>
            <div style="background:var(--grey-50);padding:12px;border-radius:var(--radius);">
              <div style="color:var(--grey-400);font-size:0.75rem;margin-bottom:2px;">Live Classes</div>
              <div style="font-weight:600;color:var(--navy);"><?= $course['total_classes'] ?></div>
            </div>
            <?php endif; ?>
            <?php if ($course['start_date']): ?>
            <div style="background:var(--grey-50);padding:12px;border-radius:var(--radius);">
              <div style="color:var(--grey-400);font-size:0.75rem;margin-bottom:2px;">Start Date</div>
              <div style="font-weight:600;color:var(--navy);"><?= formatDate($course['start_date']) ?></div>
            </div>
            <?php endif; ?>
            <div style="background:var(--grey-50);padding:12px;border-radius:var(--radius);">
              <div style="color:var(--grey-400);font-size:0.75rem;margin-bottom:2px;">Certificate</div>
              <div style="font-weight:600;color:var(--success);">✓ Included</div>
            </div>
          </div>
        </div>
        <div class="card-footer">
          <div style="display:flex;align-items:center;gap:8px;font-size:0.82rem;color:var(--grey-500);">
            <span>🔒</span> Payments are secured and processed by PayU. Your financial data is never stored on our servers.
          </div>
        </div>
      </div>

      <!-- RIGHT: Payment Box -->
      <div>
        <div class="card" style="margin-bottom:16px;">
          <div class="card-header"><div class="card-title">💳 Order Summary</div></div>
          <div class="card-body">
            <div style="display:flex;justify-content:space-between;margin-bottom:10px;font-size:0.9rem;">
              <span style="color:var(--grey-600);">Course Fee</span>
              <span style="font-weight:600;" id="baseFee"><?= formatCurrency($course['fee']) ?></span>
            </div>
            <div style="display:flex;justify-content:space-between;margin-bottom:10px;font-size:0.9rem;" id="discountRow" style="display:none;">
              <span style="color:var(--success);">Discount Applied</span>
              <span style="color:var(--success);font-weight:600;" id="discountDisplay">-₹0</span>
            </div>
            <div style="border-top:2px solid var(--grey-200);padding-top:14px;display:flex;justify-content:space-between;align-items:center;">
              <span style="font-weight:700;color:var(--navy);font-size:1.05rem;">Total Payable</span>
              <span style="font-family:var(--font-display);font-size:1.5rem;font-weight:700;color:var(--navy);" id="finalAmountDisplay"><?= formatCurrency($course['fee']) ?></span>
            </div>
          </div>
        </div>

        <!-- Coupon -->
        <div class="card" style="margin-bottom:16px;">
          <div class="card-body">
            <label class="form-label">Have a Coupon Code?</label>
            <div style="display:flex;gap:8px;">
              <input type="text" id="couponInput" class="form-control" placeholder="Enter code" style="text-transform:uppercase;">
              <button type="button" class="btn btn-navy btn-sm" onclick="applyCoupon()">Apply</button>
            </div>
            <div id="couponMsg" style="display:none;margin-top:8px;"></div>
          </div>
        </div>

        <!-- Payment Form -->
        <form method="POST" id="paymentForm">
          <?= csrfField() ?>
          <input type="hidden" name="initiate_payment" value="1">
          <input type="hidden" name="final_amount" id="hiddenFinal" value="<?= $course['fee'] ?>">
          <input type="hidden" name="discount_amount" id="hiddenDiscount" value="0">
          <input type="hidden" name="coupon_code" id="hiddenCoupon" value="">

          <button type="submit" class="btn btn-primary btn-block btn-xl" style="width:100%;">
            🔒 Pay <?= formatCurrency($course['fee']) ?> Securely
          </button>
          <p style="font-size:0.75rem;color:var(--grey-400);text-align:center;margin-top:10px;">
            One-time payment. No hidden charges. Secured by PayU.
          </p>
        </form>
      </div>
    </div>
  </div>
</main>

<script>
const SITE_URL = '<?= SITE_URL ?>';
const courseId = '<?= $course['id'] ?>';
const baseFee = <?= $course['fee'] ?>;
let appliedDiscount = 0;

function applyCoupon() {
  const code = document.getElementById('couponInput').value.trim().toUpperCase();
  if (!code) return;
  const msg = document.getElementById('couponMsg');
  msg.style.display = 'block';
  msg.className = '';
  msg.textContent = 'Checking...';

  fetch(SITE_URL + '/student/apply-coupon.php', {
    method: 'POST',
    headers: {'Content-Type':'application/json'},
    body: JSON.stringify({code, course_id: courseId, amount: baseFee, csrf: '<?= generateCsrfToken() ?>'})
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      appliedDiscount = data.discount;
      const final = data.final_amount;
      msg.className = 'alert alert-success';
      msg.textContent = '✓ ' + data.message;
      document.getElementById('discountRow').style.display = 'flex';
      document.getElementById('discountDisplay').textContent = '-₹' + appliedDiscount.toFixed(2);
      document.getElementById('finalAmountDisplay').textContent = '₹' + parseFloat(final).toFixed(2);
      document.getElementById('hiddenFinal').value = final;
      document.getElementById('hiddenDiscount').value = appliedDiscount;
      document.getElementById('hiddenCoupon').value = code;
      document.querySelector('#paymentForm button[type=submit]').textContent = '🔒 Pay ₹' + parseFloat(final).toFixed(2) + ' Securely';
    } else {
      msg.className = 'alert alert-error';
      msg.textContent = '✗ ' + data.message;
    }
  })
  .catch(() => {
    msg.className = 'alert alert-error';
    msg.textContent = 'Network error. Try again.';
  });
}
</script>
<?php include '../includes/footer.php'; ?>
