/* ============================================
   TECHHUB — Main JavaScript (PHP Backend Edition)
   ============================================ */

'use strict';

// ── PHP API endpoints ───────────────────────────────────────
// Adjust BASE_URL to match your server. On XAMPP: http://localhost/techhub/
const BASE_URL = window.location.hostname === 'localhost'
  ? '/techhub/'
  : '/';

const API = {
  contact:    BASE_URL + 'contact.php',
  newsletter: BASE_URL + 'newsletter.php',
  blog:       BASE_URL + 'api/blog.php',
  team:       BASE_URL + 'api/team.php',
  jobs:       BASE_URL + 'api/jobs.php',
};

// ── Hamburger / Mobile Nav ──────────────────────────────────
function initMobileNav() {
  const hamburger = document.querySelector('.hamburger');
  const mobileNav  = document.querySelector('.mobile-nav');
  if (!hamburger || !mobileNav) return;

  hamburger.addEventListener('click', () => {
    hamburger.classList.toggle('open');
    mobileNav.classList.toggle('open');
    hamburger.setAttribute('aria-expanded', hamburger.classList.contains('open'));
  });

  mobileNav.querySelectorAll('a').forEach(a => {
    a.addEventListener('click', () => {
      hamburger.classList.remove('open');
      mobileNav.classList.remove('open');
      hamburger.setAttribute('aria-expanded', 'false');
    });
  });

  document.addEventListener('click', e => {
    if (!hamburger.contains(e.target) && !mobileNav.contains(e.target)) {
      hamburger.classList.remove('open');
      mobileNav.classList.remove('open');
    }
  });
}

// ── Active Nav Link ─────────────────────────────────────────
function initActiveNav() {
  const page = window.location.pathname.split('/').pop() || 'index.html';
  document.querySelectorAll('.nav-links a, .mobile-nav a').forEach(a => {
    const href = a.getAttribute('href');
    if (href === page || (page === '' && href === 'index.html')) {
      a.classList.add('active');
    }
  });
}

// ── Hero Slider ─────────────────────────────────────────────
function initSlider() {
  const slides  = document.querySelectorAll('.slide');
  const dots    = document.querySelectorAll('.dot');
  const prevBtn = document.querySelector('.slider-prev');
  const nextBtn = document.querySelector('.slider-next');
  if (!slides.length) return;

  let current  = 0;
  let interval = null;

  function goTo(idx) {
    slides[current].classList.remove('active');
    dots[current]?.classList.remove('active');
    current = (idx + slides.length) % slides.length;
    slides[current].classList.add('active');
    dots[current]?.classList.add('active');
  }

  function next() { goTo(current + 1); }
  function prev() { goTo(current - 1); }

  function startAuto() { stopAuto(); interval = setInterval(next, 5000); }
  function stopAuto()  { clearInterval(interval); }

  prevBtn?.addEventListener('click', () => { prev(); startAuto(); });
  nextBtn?.addEventListener('click', () => { next(); startAuto(); });
  dots.forEach((dot, i) => dot.addEventListener('click', () => { goTo(i); startAuto(); }));

  let touchX = 0;
  const slider = document.querySelector('.hero-slider');
  slider?.addEventListener('touchstart', e => { touchX = e.touches[0].clientX; }, { passive: true });
  slider?.addEventListener('touchend', e => {
    const diff = touchX - e.changedTouches[0].clientX;
    if (Math.abs(diff) > 50) { diff > 0 ? next() : prev(); startAuto(); }
  });

  document.addEventListener('keydown', e => {
    if (e.key === 'ArrowLeft')  { prev(); startAuto(); }
    if (e.key === 'ArrowRight') { next(); startAuto(); }
  });

  startAuto();
}

// ── FAQ Accordion ───────────────────────────────────────────
function initFAQ() {
  document.querySelectorAll('.faq-question').forEach(btn => {
    btn.addEventListener('click', () => {
      const item    = btn.closest('.faq-item');
      const wasOpen = item.classList.contains('open');
      document.querySelectorAll('.faq-item.open').forEach(i => i.classList.remove('open'));
      if (!wasOpen) item.classList.add('open');
    });
  });
}

// ── Tabs ────────────────────────────────────────────────────
function initTabs() {
  document.querySelectorAll('.tabs-nav').forEach(nav => {
    const container = nav.closest('[data-tabs]') || nav.parentElement;
    nav.querySelectorAll('.tab-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        const target = btn.dataset.tab;
        nav.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        container.querySelectorAll('.tab-panel').forEach(p => {
          p.classList.toggle('active', p.dataset.panel === target);
        });
      });
    });
  });
}

// ── Toast Notifications ─────────────────────────────────────
function showToast(message, type = 'success') {
  let container = document.querySelector('.toast-container');
  if (!container) {
    container = document.createElement('div');
    container.className = 'toast-container';
    document.body.appendChild(container);
  }
  const icon  = type === 'success' ? '✅' : type === 'error' ? '❌' : 'ℹ️';
  const toast = document.createElement('div');
  toast.className = `toast ${type}`;
  toast.innerHTML = `<span class="toast-icon">${icon}</span><span class="toast-msg">${message}</span>`;
  container.appendChild(toast);
  setTimeout(() => toast.remove(), 3500);
}

// ── Counter Animation ───────────────────────────────────────
function initCounters() {
  const counters = document.querySelectorAll('[data-count]');
  if (!counters.length) return;

  const observer = new IntersectionObserver(entries => {
    entries.forEach(entry => {
      if (!entry.isIntersecting) return;
      const el     = entry.target;
      const target = parseFloat(el.dataset.count);
      const suffix = el.dataset.suffix || '';
      const isFloat = String(target).includes('.');
      const duration = 1800;
      const start  = performance.now();

      function update(now) {
        const progress = Math.min((now - start) / duration, 1);
        const eased    = 1 - Math.pow(1 - progress, 3);
        const val      = target * eased;
        el.textContent = (isFloat ? val.toFixed(1) : Math.floor(val)) + suffix;
        if (progress < 1) requestAnimationFrame(update);
      }
      requestAnimationFrame(update);
      observer.unobserve(el);
    });
  }, { threshold: 0.5 });

  counters.forEach(el => observer.observe(el));
}

// ── Scroll Reveal ───────────────────────────────────────────
function initScrollReveal() {
  const els = document.querySelectorAll('.reveal');
  if (!els.length) return;

  const observer = new IntersectionObserver(entries => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('animate-in');
        observer.unobserve(entry.target);
      }
    });
  }, { threshold: 0.12 });

  els.forEach(el => observer.observe(el));
}

// ── Sticky Header ───────────────────────────────────────────
function initStickyHeader() {
  const header = document.querySelector('.site-header');
  if (!header) return;
  window.addEventListener('scroll', () => {
    header.style.boxShadow = window.scrollY > 30 ? '0 4px 24px rgba(0,0,0,.4)' : '';
  }, { passive: true });
}

// ── Contact Form → PHP Backend ──────────────────────────────
function initContactForm() {
  const form = document.getElementById('contactForm');
  if (!form) return;

  form.addEventListener('submit', async e => {
    e.preventDefault();
    const btn = form.querySelector('button[type="submit"]');
    const originalText = btn.textContent;
    btn.textContent = 'Sending…';
    btn.disabled = true;

    try {
      const formData = new FormData(form);
      const response = await fetch(API.contact, { method: 'POST', body: formData });
      const data     = await response.json();

      if (data.success) {
        showToast(data.message, 'success');
        form.reset();
      } else {
        const msg = data.errors ? data.errors.join(', ') : data.message;
        showToast(msg, 'error');
      }
    } catch (err) {
      showToast('Network error. Please try again.', 'error');
      console.error(err);
    } finally {
      btn.textContent = originalText;
      btn.disabled = false;
    }
  });
}

// ── Newsletter Form → PHP Backend ───────────────────────────
function initNewsletterForms() {
  document.querySelectorAll('.newsletter-form').forEach(form => {
    form.addEventListener('submit', async e => {
      e.preventDefault();
      const input = form.querySelector('input[type="email"]');
      const btn   = form.querySelector('button[type="submit"]');
      if (!input?.value.trim()) return;

      const originalText = btn.textContent;
      btn.textContent = 'Subscribing…';
      btn.disabled    = true;

      try {
        const formData = new FormData();
        formData.append('email', input.value.trim());
        const response = await fetch(API.newsletter, { method: 'POST', body: formData });
        const data     = await response.json();

        showToast(data.message, data.success ? 'success' : 'error');
        if (data.success) input.value = '';
      } catch (err) {
        showToast('Network error. Please try again.', 'error');
      } finally {
        btn.textContent = originalText;
        btn.disabled    = false;
      }
    });
  });
}

// ── Dynamic Blog Posts from PHP/MySQL ───────────────────────
async function loadBlogPosts(category = '') {
  const grid = document.getElementById('blogGrid');
  if (!grid) return;

  grid.innerHTML = '<p style="color:var(--clr-muted);padding:2rem;">Loading articles…</p>';

  try {
    const url      = API.blog + (category ? `?category=${encodeURIComponent(category)}` : '');
    const response = await fetch(url);
    const data     = await response.json();

    if (!data.success || !data.posts.length) {
      grid.innerHTML = '<p style="color:var(--clr-muted);padding:2rem;">No posts found.</p>';
      return;
    }

    grid.innerHTML = data.posts.map((post, i) => `
      <article class="blog-card reveal delay-${(i % 3) + 1}" data-category="${post.category}">
        <div class="blog-thumb" style="background:linear-gradient(135deg,rgba(0,212,255,.15),rgba(124,58,237,.15));">📝</div>
        <div style="padding:1.5rem;">
          <div class="blog-meta">
            <span class="badge badge-cyan">${post.category}</span>
            <span class="blog-date">${new Date(post.published_at).toLocaleDateString('en-GB',{day:'numeric',month:'short',year:'numeric'})}</span>
          </div>
          <h3 class="blog-title">${post.title}</h3>
          <p class="blog-excerpt">${post.excerpt || ''}</p>
          <a href="blog-post.php?slug=${post.slug}" class="read-more">Read More →</a>
        </div>
      </article>
    `).join('');

    // Re-run scroll reveal for newly added elements
    initScrollReveal();

  } catch (err) {
    grid.innerHTML = '<p style="color:var(--clr-muted);padding:2rem;">Could not load posts. Showing static content.</p>';
    console.error(err);
  }
}

// ── Blog Filter Buttons ─────────────────────────────────────
function initBlogFilter() {
  document.querySelectorAll('[onclick^="filterBlog"]').forEach(btn => {
    // Remove old inline handler and replace with fetch-based one
    btn.removeAttribute('onclick');
    const category = btn.textContent.trim();
    btn.addEventListener('click', () => {
      document.querySelectorAll('[onclick^="filterBlog"], .blog-filter-btn').forEach(b => {
        b.className = 'btn btn-ghost btn-sm blog-filter-btn';
      });
      btn.className = 'btn btn-primary btn-sm blog-filter-btn';
      const cat = category === 'All' ? '' : category;
      loadBlogPosts(cat);
    });
  });

  // Initial load from database
  if (document.getElementById('blogGrid')) {
    loadBlogPosts();
  }
}

// ── Search ──────────────────────────────────────────────────
function initSearch() {
  document.querySelectorAll('.search-bar input').forEach(input => {
    input.addEventListener('keydown', e => {
      if (e.key === 'Enter' && input.value.trim()) {
        showToast(`Searching for "${input.value}"…`, 'info');
      }
    });
  });
}

// ── Back to Top ─────────────────────────────────────────────
function initBackToTop() {
  const btn = document.getElementById('backToTop');
  if (!btn) return;
  window.addEventListener('scroll', () => {
    btn.style.opacity       = window.scrollY > 400 ? '1' : '0';
    btn.style.pointerEvents = window.scrollY > 400 ? 'all' : 'none';
  }, { passive: true });
  btn.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
}

// ── Smooth anchor scroll ────────────────────────────────────
function initAnchorScroll() {
  document.querySelectorAll('a[href^="#"]').forEach(a => {
    a.addEventListener('click', e => {
      const target = document.querySelector(a.getAttribute('href'));
      if (target) { e.preventDefault(); target.scrollIntoView({ behavior: 'smooth' }); }
    });
  });
}

// ── Init all ───────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
  initMobileNav();
  initActiveNav();
  initSlider();
  initFAQ();
  initTabs();
  initCounters();
  initScrollReveal();
  initStickyHeader();
  initContactForm();       // ← PHP backend
  initNewsletterForms();   // ← PHP backend
  initBlogFilter();        // ← PHP/MySQL dynamic posts
  initSearch();
  initAnchorScroll();
  initBackToTop();
});
