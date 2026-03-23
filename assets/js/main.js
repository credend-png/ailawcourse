// LexLearnAI Main JavaScript

document.addEventListener('DOMContentLoaded', function () {

  // ============================================================
  // NAVBAR SCROLL BEHAVIOR
  // ============================================================
  const navbar = document.getElementById('mainNav');
  if (navbar) {
    window.addEventListener('scroll', function () {
      if (window.scrollY > 50) {
        navbar.classList.add('scrolled');
      } else {
        navbar.classList.remove('scrolled');
      }
    });
  }

  // ============================================================
  // SMOOTH REVEAL ANIMATIONS
  // ============================================================
  const observeElements = document.querySelectorAll('[data-reveal]');
  if (observeElements.length && 'IntersectionObserver' in window) {
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.style.opacity = '1';
          entry.target.style.transform = 'translateY(0)';
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.1, rootMargin: '0px 0px -60px 0px' });

    observeElements.forEach((el, i) => {
      el.style.opacity = '0';
      el.style.transform = 'translateY(24px)';
      el.style.transition = `opacity 0.5s ease ${i * 0.07}s, transform 0.5s ease ${i * 0.07}s`;
      observer.observe(el);
    });
  }

  // ============================================================
  // COUNTER ANIMATION
  // ============================================================
  const counters = document.querySelectorAll('[data-count]');
  if (counters.length && 'IntersectionObserver' in window) {
    const countObserver = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          animateCount(entry.target);
          countObserver.unobserve(entry.target);
        }
      });
    }, { threshold: 0.5 });
    counters.forEach(c => countObserver.observe(c));
  }

  function animateCount(el) {
    const target = parseInt(el.getAttribute('data-count'));
    const duration = 1800;
    const start = performance.now();
    function update(now) {
      const elapsed = now - start;
      const progress = Math.min(elapsed / duration, 1);
      const ease = 1 - Math.pow(1 - progress, 3);
      el.textContent = Math.floor(ease * target).toLocaleString('en-IN');
      if (progress < 1) requestAnimationFrame(update);
      else el.textContent = target.toLocaleString('en-IN');
    }
    requestAnimationFrame(update);
  }

  // ============================================================
  // COUPON CODE AJAX CHECK
  // ============================================================
  const couponBtn = document.getElementById('applyCoupon');
  if (couponBtn) {
    couponBtn.addEventListener('click', function () {
      const code = document.getElementById('couponCode').value.trim();
      const courseId = document.getElementById('courseId')?.value;
      const amount = document.getElementById('courseAmount')?.value;
      if (!code) return;

      couponBtn.disabled = true;
      couponBtn.textContent = 'Checking...';

      fetch(SITE_URL + '/student/apply-coupon.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ code, course_id: courseId, amount, csrf: csrfToken })
      })
      .then(r => r.json())
      .then(data => {
        const msg = document.getElementById('couponMsg');
        if (data.success) {
          msg.className = 'alert alert-success mt-8';
          msg.textContent = '✓ Coupon applied! Discount: ₹' + data.discount;
          document.getElementById('finalAmount').textContent = '₹' + data.final_amount;
          document.getElementById('hiddenFinalAmount').value = data.final_amount;
          document.getElementById('discountAmount').value = data.discount;
        } else {
          msg.className = 'alert alert-error mt-8';
          msg.textContent = '✗ ' + data.message;
        }
        msg.style.display = 'flex';
      })
      .catch(() => {
        document.getElementById('couponMsg').className = 'alert alert-error mt-8';
        document.getElementById('couponMsg').textContent = 'Network error. Try again.';
      })
      .finally(() => {
        couponBtn.disabled = false;
        couponBtn.textContent = 'Apply';
      });
    });
  }

  // ============================================================
  // FORM VALIDATION HELPERS
  // ============================================================
  const forms = document.querySelectorAll('[data-validate]');
  forms.forEach(form => {
    form.addEventListener('submit', function (e) {
      let isValid = true;
      form.querySelectorAll('[required]').forEach(field => {
        if (!field.value.trim()) {
          markError(field, 'This field is required');
          isValid = false;
        } else {
          clearError(field);
        }
        // Email validation
        if (field.type === 'email' && field.value) {
          const emailRx = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
          if (!emailRx.test(field.value)) {
            markError(field, 'Enter a valid email address');
            isValid = false;
          }
        }
        // Phone validation
        if (field.name === 'mobile' && field.value) {
          if (!/^[6-9]\d{9}$/.test(field.value)) {
            markError(field, 'Enter a valid 10-digit Indian mobile number');
            isValid = false;
          }
        }
        // Password strength
        if (field.type === 'password' && field.name === 'password' && field.value) {
          if (field.value.length < 8) {
            markError(field, 'Password must be at least 8 characters');
            isValid = false;
          }
        }
      });
      // Password confirm match
      const pw = form.querySelector('[name="password"]');
      const pw2 = form.querySelector('[name="confirm_password"]');
      if (pw && pw2 && pw.value && pw2.value && pw.value !== pw2.value) {
        markError(pw2, 'Passwords do not match');
        isValid = false;
      }
      if (!isValid) e.preventDefault();
    });
  });

  function markError(field, msg) {
    field.classList.add('error');
    let err = field.parentElement.querySelector('.form-error');
    if (!err) {
      err = document.createElement('div');
      err.className = 'form-error';
      field.parentElement.appendChild(err);
    }
    err.textContent = msg;
  }

  function clearError(field) {
    field.classList.remove('error');
    const err = field.parentElement.querySelector('.form-error');
    if (err) err.remove();
  }

  // ============================================================
  // AUTO-DISMISS ALERTS
  // ============================================================
  document.querySelectorAll('.alert[data-auto-dismiss]').forEach(alert => {
    setTimeout(() => {
      alert.style.transition = 'opacity 0.4s ease';
      alert.style.opacity = '0';
      setTimeout(() => alert.remove(), 400);
    }, 4000);
  });

  // ============================================================
  // SIDEBAR TOGGLE (MOBILE)
  // ============================================================
  const sidebarToggle = document.getElementById('sidebarToggle');
  const sidebar = document.getElementById('sidebar');
  if (sidebarToggle && sidebar) {
    sidebarToggle.addEventListener('click', function () {
      sidebar.classList.toggle('open');
    });
  }

  // ============================================================
  // TABLE SEARCH FILTER
  // ============================================================
  const tableSearch = document.getElementById('tableSearch');
  if (tableSearch) {
    tableSearch.addEventListener('input', function () {
      const query = this.value.toLowerCase();
      document.querySelectorAll('#searchTable tbody tr').forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(query) ? '' : 'none';
      });
    });
  }

  // ============================================================
  // CONFIRM DIALOG
  // ============================================================
  document.querySelectorAll('[data-confirm]').forEach(el => {
    el.addEventListener('click', function (e) {
      if (!confirm(this.getAttribute('data-confirm'))) {
        e.preventDefault();
      }
    });
  });

  // ============================================================
  // PROGRESS BAR ANIMATION
  // ============================================================
  document.querySelectorAll('.progress-bar[data-width]').forEach(bar => {
    setTimeout(() => {
      bar.style.width = bar.getAttribute('data-width') + '%';
    }, 300);
  });

});

// Global SITE_URL and CSRF for AJAX
const SITE_URL = document.querySelector('meta[name="site-url"]')?.content || '';
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
