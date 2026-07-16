/* ============================================================
   Integrated Engineers Point — Main JS
   Features: AOS, Counter animation, AJAX forms, Testimonials,
             Star rating, Navbar scroll, Hamburger, Scroll-top
   ============================================================ */

document.addEventListener('DOMContentLoaded', function () {

  // ── AOS Init ──────────────────────────────────────────────
  if (typeof AOS !== 'undefined') {
    AOS.init({ duration: 700, easing: 'ease-out-quad', once: true, offset: 60 });
  }

  // ── Hamburger Menu ────────────────────────────────────────
  const hamburger = document.getElementById('hamburger');
  const navLinks  = document.getElementById('navLinks');
  if (hamburger && navLinks) {
    hamburger.addEventListener('click', function () {
      navLinks.classList.toggle('open');
      hamburger.classList.toggle('open');
    });
  }

  // ── Active nav link on scroll ─────────────────────────────
  const sections = document.querySelectorAll('section[id], div[id]');
  const navAnchors = document.querySelectorAll('.nav-link');
  window.addEventListener('scroll', function () {
    let current = '';
    sections.forEach(s => {
      if (window.scrollY >= s.offsetTop - 120) current = s.getAttribute('id');
    });
    navAnchors.forEach(a => {
      a.classList.remove('active');
      if (a.getAttribute('href') === '#' + current) a.classList.add('active');
    });
  }, { passive: true });

  // ── Header shrink on scroll ───────────────────────────────
  window.addEventListener('scroll', function () {
    document.getElementById('header').classList.toggle('scrolled', window.scrollY > 50);
  }, { passive: true });

  // ── Scroll to top button ──────────────────────────────────
  const scrollTopBtn = document.getElementById('scrollTop');
  window.addEventListener('scroll', function () {
    if (scrollTopBtn) scrollTopBtn.classList.toggle('visible', window.scrollY > 400);
  }, { passive: true });
  if (scrollTopBtn) {
    scrollTopBtn.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
  }

  // ── Counter animation ─────────────────────────────────────
  function animateCounter(el) {
    const target = parseInt(el.dataset.target, 10);
    const duration = 1800;
    const start = performance.now();
    function step(now) {
      const elapsed = now - start;
      const progress = Math.min(elapsed / duration, 1);
      const eased = 1 - Math.pow(1 - progress, 3);
      el.textContent = Math.floor(eased * target);
      if (progress < 1) requestAnimationFrame(step);
    }
    requestAnimationFrame(step);
  }

  const counterObserver = new IntersectionObserver(function (entries) {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        animateCounter(entry.target);
        counterObserver.unobserve(entry.target);
      }
    });
  }, { threshold: 0.5 });

  document.querySelectorAll('.stat-num').forEach(el => counterObserver.observe(el));

  // ── Load Testimonials (AJAX or static fallback) ───────────
  const testimonialsGrid = document.getElementById('testimonialsGrid');
  if (testimonialsGrid) {
    fetch('ajax/get_testimonials.php')
      .then(r => r.json())
      .then(data => {
        if (data.success && data.data.length > 0) {
          renderTestimonials(data.data);
        } else {
          renderTestimonials(staticTestimonials);
        }
      })
      .catch(() => renderTestimonials(staticTestimonials));
  }

  function renderTestimonials(list) {
    if (!testimonialsGrid) return;
    testimonialsGrid.innerHTML = list.map(t => {
      const stars = Array.from({ length: 5 }, (_, i) =>
        `<i class="${i < t.rating ? 'fa-solid' : 'fa-regular'} fa-star"></i>`
      ).join('');
      const initials = t.customer_name.split(' ').map(w => w[0]).join('').toUpperCase().slice(0, 2);
      const serviceLabel = t.service_used ? `<span>${t.service_used}</span>` : '';
      return `
        <div class="testimonial-card" data-aos="fade-up">
          <div class="testimonial-stars">${stars}</div>
          <blockquote>${t.message}</blockquote>
          <div class="testimonial-footer">
            <div class="testimonial-avatar">${initials}</div>
            <div class="testimonial-info">
              <strong>${escapeHtml(t.customer_name)}</strong>
              ${serviceLabel}
            </div>
          </div>
        </div>`;
    }).join('');
    if (typeof AOS !== 'undefined') AOS.refreshHard();
  }

  // Static fallback testimonials (mirrors DB seed data)
  const staticTestimonials = [
    { customer_name: 'Rajesh Kumar', service_used: 'Split AC Services', rating: 5, message: 'Excellent service! The technician arrived on time and fixed my AC within an hour. Highly recommended.' },
    { customer_name: 'Priya Sharma', service_used: 'AMC / Maintenance', rating: 5, message: 'Been using their AMC for 2 years now. Always prompt, professional, and thorough. Great team!' },
    { customer_name: 'Amit Patel', service_used: 'Chiller Unit Services', rating: 4, message: 'Very professional team. They handled our industrial chiller unit expertly. Will definitely use again.' },
    { customer_name: 'Sunita Verma', service_used: 'HVAC Services', rating: 5, message: 'Integrated Engineers Point installed complete HVAC for our office. Superb quality and on-time delivery.' },
    { customer_name: 'Vikram Singh', service_used: 'AC Breakdown & Repair', rating: 5, message: 'Called them at 9 PM for emergency AC repair. They came within the hour. Truly 24/7 support!' },
    { customer_name: 'Meena Joshi', service_used: 'Cassette AC Services', rating: 4, message: 'Good service, genuine spare parts used. Pricing is transparent with no hidden charges.' },
  ];

  // ── Enquiry Form AJAX ─────────────────────────────────────
  const enquiryForm = document.getElementById('enquiryForm');
  if (enquiryForm) {
    enquiryForm.addEventListener('submit', function (e) {
      e.preventDefault();
      const btn     = document.getElementById('enquirySubmitBtn');
      const msgEl   = document.getElementById('enquiryMsg');
      const formData = new FormData(enquiryForm);

      setLoading(btn, true);
      clearMsg(msgEl);

      fetch('ajax/submit_enquiry.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
          showMsg(msgEl, data.message, data.success ? 'success' : 'error');
          if (data.success) enquiryForm.reset();
        })
        .catch(() => showMsg(msgEl, 'Network error. Please try again or call us directly.', 'error'))
        .finally(() => setLoading(btn, false));
    });
  }

  // ── Feedback Form AJAX ────────────────────────────────────
  const feedbackForm = document.getElementById('feedbackForm');
  if (feedbackForm) {
    feedbackForm.addEventListener('submit', function (e) {
      e.preventDefault();
      const btn    = document.getElementById('feedbackSubmitBtn');
      const msgEl  = document.getElementById('feedbackMsg');
      const rating = parseInt(document.getElementById('ratingInput').value, 10);

      if (!rating || rating < 1) {
        showMsg(msgEl, 'Please select a star rating.', 'error');
        return;
      }

      const formData = new FormData(feedbackForm);
      setLoading(btn, true);
      clearMsg(msgEl);

      fetch('ajax/submit_feedback.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
          showMsg(msgEl, data.message, data.success ? 'success' : 'error');
          if (data.success) { feedbackForm.reset(); resetStars(); }
        })
        .catch(() => showMsg(msgEl, 'Network error. Please try again.', 'error'))
        .finally(() => setLoading(btn, false));
    });
  }

  // ── Star Rating Interaction ───────────────────────────────
  const stars = document.querySelectorAll('#starRating .star');
  const ratingInput = document.getElementById('ratingInput');

  stars.forEach(star => {
    star.addEventListener('mouseenter', function () {
      const val = parseInt(this.dataset.val, 10);
      stars.forEach((s, i) => {
        s.classList.toggle('hovered', i < val);
        s.className = i < val ? 'fa-solid fa-star star hovered' : 'fa-regular fa-star star';
      });
    });
    star.addEventListener('mouseleave', function () {
      const current = ratingInput ? parseInt(ratingInput.value, 10) : 0;
      stars.forEach((s, i) => {
        s.className = i < current ? 'fa-solid fa-star star active' : 'fa-regular fa-star star';
      });
    });
    star.addEventListener('click', function () {
      const val = parseInt(this.dataset.val, 10);
      if (ratingInput) ratingInput.value = val;
      stars.forEach((s, i) => {
        s.className = i < val ? 'fa-solid fa-star star active' : 'fa-regular fa-star star';
      });
    });
  });

  function resetStars() {
    if (ratingInput) ratingInput.value = 0;
    stars.forEach(s => { s.className = 'fa-regular fa-star star'; });
  }

  // ── Smooth scroll for all anchor links ───────────────────
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
      const target = document.querySelector(this.getAttribute('href'));
      if (target) {
        e.preventDefault();
        const offset = 80;
        const top = target.getBoundingClientRect().top + window.scrollY - offset;
        window.scrollTo({ top, behavior: 'smooth' });
        if (navLinks) navLinks.classList.remove('open');
      }
    });
  });

  // ── WhatsApp float button (inject) ───────────────────────
  const waBtn = document.createElement('a');
  waBtn.href = 'https://wa.me/918925025255';
  waBtn.target = '_blank';
  waBtn.className = 'wa-float';
  waBtn.innerHTML = '<i class="fa-brands fa-whatsapp"></i>';
  waBtn.setAttribute('aria-label', 'Chat on WhatsApp');
  document.body.appendChild(waBtn);

  // ── Helpers ───────────────────────────────────────────────
  function setLoading(btn, loading) {
    const text   = btn.querySelector('.btn-text');
    const loader = btn.querySelector('.btn-loader');
    btn.disabled = loading;
    if (text)   text.style.display   = loading ? 'none' : '';
    if (loader) loader.style.display = loading ? 'inline-flex' : 'none';
  }

  function showMsg(el, msg, type) {
    if (!el) return;
    el.textContent = msg;
    el.className = `form-msg ${type}`;
    el.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    setTimeout(() => clearMsg(el), 7000);
  }

  function clearMsg(el) {
    if (!el) return;
    el.textContent = '';
    el.className = 'form-msg';
  }

  function escapeHtml(str) {
    return str.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
  }

});
